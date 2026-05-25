<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\PaymentFailedMail;
use App\Mail\PaymentSuccessMail;
use App\Models\Transaction;
use App\Services\FaspayService;
use App\Services\SingaPayService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PaymentController extends Controller
{

    /**
     * Show payment page for a transaction
     */
    public function show(Transaction $transaction)
    {
        // Ensure user owns this transaction
        if ($transaction->user_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Don't allow payment if already paid
        if ($transaction->status === Transaction::STATUS_PAID) {
            return redirect()->route('user.transactions.show', $transaction)
                ->with('info', 'Transaksi ini sudah dibayar.');
        }

        $depositBalance = Auth::user()->deposit_balance;

        return view('user.payments.show', compact('transaction', 'depositBalance'));
    }

    public function process(Request $request, Transaction $transaction)
    {
        // Ensure user owns this transaction
        if ($transaction->user_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Don't process if already paid
        if ($transaction->status === Transaction::STATUS_PAID) {
            return back()->with('warning', 'Transaksi ini sudah dibayar.');
        }

        $depositBalance = Auth::user()->deposit_balance;

        if ($depositBalance < $transaction->amount) {
            return back()->with('error', 'Saldo tidak mencukupi. Silakan lakukan Top Up terlebih dahulu.');
        }

        try {
            app(WalletService::class)->payTransactionWithWallet($transaction, Auth::user());
            $transaction->refresh();
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        Log::info('Payment processed with balance', [
            'transaction_id' => $transaction->id,
            'user_id' => Auth::id(),
            'amount' => $transaction->amount,
        ]);

        // Send success email
        try {
            Mail::to($transaction->user->email)->queue(new PaymentSuccessMail($transaction));
        } catch (\Exception $e) {
            Log::error('Failed to Queue Payment Success Email', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);
        }

        return redirect()->route('user.payments.success', $transaction)
            ->with('success', 'Pembayaran berhasil menggunakan Saldo.');
    }

    /**
     * Payment success callback
     */
    public function success(Transaction $transaction)
    {
        // Ensure user owns this transaction
        if ($transaction->user_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }

        return view('user.payments.success', compact('transaction'));
    }

    /**
     * Payment failed callback
     */
    public function failed(Transaction $transaction)
    {
        // Ensure user owns this transaction
        if ($transaction->user_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }

        return view('user.payments.failed', compact('transaction'));
    }

    /**
     * Handle webhook from Singapay - called when payment is confirmed
     * This should be called from the webhook handler, not directly by users
     */
    public function handleWebhook($transactionId, $status)
    {
        $transaction = Transaction::find($transactionId);
        
        if (!$transaction) {
            Log::warning('Webhook: Transaction not found', ['transaction_id' => $transactionId]);
            return false;
        }

        $oldStatus = $transaction->status;
        
        // Update transaction status
        if ($status === Transaction::STATUS_PAID) {
            $transaction->update(['status' => Transaction::STATUS_PAID]);
            
            Log::info('Payment Confirmed via Webhook', [
                'transaction_id' => $transaction->id,
                'user_id' => $transaction->user_id,
                'old_status' => $oldStatus
            ]);
            
            // Send success email
            try {
                Mail::to($transaction->user->email)->queue(new PaymentSuccessMail($transaction));
                Log::info('Payment Success Email Queued', ['transaction_id' => $transaction->id]);
            } catch (\Exception $e) {
                Log::error('Failed to Queue Payment Success Email', [
                    'transaction_id' => $transaction->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            return true;
        } elseif ($status === Transaction::STATUS_FAILED || $status === 'expired' || $status === 'cancelled') {
            $transaction->update(['status' => Transaction::STATUS_FAILED]);
            
            Log::info('Payment Failed via Webhook', [
                'transaction_id' => $transaction->id,
                'user_id' => $transaction->user_id,
                'reason' => $status
            ]);
            
            // Send failed email
            try {
                Mail::to($transaction->user->email)->queue(new PaymentFailedMail($transaction));
                Log::info('Payment Failed Email Queued', ['transaction_id' => $transaction->id]);
            } catch (\Exception $e) {
                Log::error('Failed to Queue Payment Failed Email', [
                    'transaction_id' => $transaction->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            return true;
        }
        
        return false;
    }


}
