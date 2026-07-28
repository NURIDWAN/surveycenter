<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RespondentWithdrawal;
use Illuminate\Http\Request;

class RespondentWithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $query = RespondentWithdrawal::with('user:id,name,email')->latest();

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search by name/email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $withdrawals = $query->paginate(20)->withQueryString();

        $pendingCount = RespondentWithdrawal::where('status', RespondentWithdrawal::STATUS_PENDING)->count();
        $totalApproved = RespondentWithdrawal::where('status', RespondentWithdrawal::STATUS_APPROVED)->sum('amount');

        return view('admin.respondent-withdrawals.index', compact('withdrawals', 'pendingCount', 'totalApproved'));
    }

    public function approve(RespondentWithdrawal $withdrawal)
    {
        if ($withdrawal->status !== RespondentWithdrawal::STATUS_PENDING) {
            return back()->with('error', 'Withdrawal ini sudah diproses.');
        }

        $withdrawal->update([
            'status' => RespondentWithdrawal::STATUS_APPROVED,
            'processed_at' => now(),
        ]);

        return back()->with('success', 'Withdrawal Rp ' . number_format($withdrawal->amount, 0, ',', '.') . ' untuk ' . ($withdrawal->user->name ?? '-') . ' berhasil disetujui.');
    }

    public function reject(Request $request, RespondentWithdrawal $withdrawal)
    {
        if ($withdrawal->status !== RespondentWithdrawal::STATUS_PENDING) {
            return back()->with('error', 'Withdrawal ini sudah diproses.');
        }

        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $withdrawal->update([
            'status' => RespondentWithdrawal::STATUS_REJECTED,
            'notes' => $request->notes,
            'processed_at' => now(),
        ]);

        return back()->with('success', 'Withdrawal berhasil ditolak.');
    }
}
