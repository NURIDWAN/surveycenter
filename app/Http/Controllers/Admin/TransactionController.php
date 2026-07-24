<?php

// app/Http/Controllers/Admin/TransactionController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with(['survey', 'user'])->latest()->paginate(10);
        return view('admin.transactions.index', compact('transactions'));
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['user', 'survey']);
        return view('admin.transactions.show', compact('transaction'));
    }

    public function create()
    {
        try {
            $surveys = Survey::select(['id', 'user_id', 'title'])->orderBy('title')->get();
            $users = User::select(['id', 'name'])->orderBy('name')->get();
            Log::info('Create page accessed', ['surveys' => $surveys->count(), 'users' => $users->count()]);
            return view('admin.transactions.create', compact('surveys', 'users'));
        } catch (\Exception $e) {
            Log::error('Create page error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|integer',
            'status' => 'required|in:' . implode(',', [
                Transaction::STATUS_PENDING,
                Transaction::STATUS_PROCESSING,
                Transaction::STATUS_PAID,
                Transaction::STATUS_FAILED,
            ]),
            'survey_id' => 'nullable|exists:surveys,id',
            'survey_title' => 'nullable|string|max:255',
            'question_count' => 'nullable|integer|min:0',
        ]);

        // Jika survey_id tidak dikirim, buat survey baru
        if (!$request->survey_id) {
            $survey = \App\Models\Survey::create([
                'user_id' => $request->user_id,
                'title' => $request->survey_title ?? 'Survey baru',
                'question_count' => $request->question_count ?? 0,
            ]);
            $survey_id = $survey->id;
        } else {
            $survey_id = $request->survey_id;
        }

        // Buat transaksi
        \App\Models\Transaction::create([
            'survey_id' => $survey_id,
            'user_id' => $request->user_id,
            'amount' => $request->amount,
            'status' => $request->status,
            'singapay_ref' => $request->singapay_ref,
            'payment_method' => $request->payment_method,
        ]);

        return redirect()->route('admin.transactions.index')
            ->with('success', 'Transaction berhasil dibuat!');
    }


    public function edit(Transaction $transaction)
    {
        $surveys = Survey::select(['id', 'user_id', 'title'])->orderBy('title')->get();
        $users = User::select(['id', 'name'])->orderBy('name')->get();
        return view('admin.transactions.edit', compact('transaction', 'surveys', 'users'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'survey_id' => 'required|exists:surveys,id',
            'user_id' => 'nullable|exists:users,id',
            'amount' => 'required|integer|min:0',
            'payment_method' => 'nullable|string|max:50',
            'status' => 'required|in:' . implode(',', [
                Transaction::STATUS_PENDING,
                Transaction::STATUS_PROCESSING,
                Transaction::STATUS_PAID,
                Transaction::STATUS_FAILED,
            ]),
            'singapay_ref' => 'nullable|string',
        ]);

        $transaction->update($validated);

        return redirect()->route('admin.transactions.index')
            ->with('success', 'Transaction updated successfully.');
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return redirect()->route('admin.transactions.index')
            ->with('success', 'Transaction deleted successfully.');
    }
}
