<?php

namespace App\Services;

use App\Models\Survey;
use App\Models\SurveyFilling;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class SurveyFillingService
{
    public function __construct(
        private WalletService $walletService
    ) {}

    /**
     * Start a new survey filling for the given user and survey.
     *
     * Validates uniqueness (user can only fill a survey once) and capacity
     * (approved fillings must be less than survey's respondent_count).
     */
    public function startFilling(User $user, Survey $survey): SurveyFilling
    {
        // Check uniqueness: no existing filling for this user+survey
        $existingFilling = SurveyFilling::where('survey_id', $survey->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingFilling) {
            throw new RuntimeException('Anda sudah mengisi survey ini.');
        }

        // Check capacity: approved fillings count < survey.respondent_count
        $approvedCount = SurveyFilling::where('survey_id', $survey->id)
            ->where('status', SurveyFilling::STATUS_DISETUJUI)
            ->count();

        if ($approvedCount >= $survey->respondent_count) {
            throw new RuntimeException('Survey sudah penuh.');
        }

        return SurveyFilling::create([
            'survey_id' => $survey->id,
            'user_id' => $user->id,
            'status' => SurveyFilling::STATUS_SEDANG_DIKERJAKAN,
        ]);
    }

    /**
     * Upload proof screenshot for a survey filling.
     *
     * Stores the file with a unique name and transitions status to 'menunggu_verifikasi'.
     */
    public function uploadProof(SurveyFilling $filling, UploadedFile $file, ?string $catatan = null): SurveyFilling
    {
        $disk = config('responden.proof_disk');
        $path = config('responden.proof_path');

        // Generate unique filename using UUID and original extension
        $extension = $file->getClientOriginalExtension();
        $uniqueName = Str::uuid()->toString() . '.' . $extension;

        // Store the file
        $filePath = $file->storeAs($path, $uniqueName, $disk);

        // Update filling record
        $filling->update([
            'proof_file_path' => $filePath,
            'catatan' => $catatan,
            'status' => SurveyFilling::STATUS_MENUNGGU_VERIFIKASI,
        ]);

        return $filling->fresh();
    }

    /**
     * Approve a survey filling.
     *
     * Within a DB transaction: update status, set approved_at, credit wallet,
     * and create a WalletTransaction.
     */
    public function approve(SurveyFilling $filling): SurveyFilling
    {
        if ($filling->status !== SurveyFilling::STATUS_MENUNGGU_VERIFIKASI) {
            throw new RuntimeException('Hanya pengisian dengan status menunggu verifikasi yang dapat disetujui.');
        }

        return DB::transaction(function () use ($filling) {
            // Lock the user's wallet for update
            $wallet = Wallet::where('user_id', $filling->user_id)
                ->lockForUpdate()
                ->first();

            if (!$wallet) {
                // Create wallet if it doesn't exist
                $wallet = Wallet::create([
                    'user_id' => $filling->user_id,
                    'balance' => 0,
                    'deposit_balance' => 0,
                    'reward_balance' => 0,
                ]);
                $wallet = Wallet::where('user_id', $filling->user_id)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            // Update filling status
            $filling->update([
                'status' => SurveyFilling::STATUS_DISETUJUI,
                'approved_at' => now(),
            ]);

            // Credit wallet
            $survey = $filling->survey;
            $rewardAmount = (float) $survey->reward_amount;
            $balanceBefore = (float) $wallet->balance;

            // Credit reward_balance
            $wallet->reward_balance = bcadd($wallet->reward_balance, $rewardAmount, 2);
            $wallet->syncTotalBalance();
            $balanceAfter = (float) $wallet->balance;

            $wallet->save();

            // Create wallet transaction
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $filling->user_id,
                'type' => WalletTransaction::TYPE_CREDIT,
                'amount' => $rewardAmount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => WalletTransaction::REF_SURVEY_FILLING,
                'reference_id' => $filling->id,
                'description' => 'Reward survey: ' . $survey->title,
                'meta' => [
                    'survey_id' => $survey->id,
                    'survey_title' => $survey->title,
                ],
            ]);

            return $filling->fresh();
        });
    }

    /**
     * Reject a survey filling.
     *
     * Validates status is 'menunggu_verifikasi', then updates status to 'ditolak'
     * with rejection reason and optional notes.
     */
    public function reject(SurveyFilling $filling, int $rejectionReasonId, ?string $notes = null): SurveyFilling
    {
        if ($filling->status !== SurveyFilling::STATUS_MENUNGGU_VERIFIKASI) {
            throw new RuntimeException('Hanya pengisian dengan status menunggu verifikasi yang dapat ditolak.');
        }

        $filling->update([
            'status' => SurveyFilling::STATUS_DITOLAK,
            'rejected_at' => now(),
            'rejection_reason_id' => $rejectionReasonId,
            'rejection_notes' => $notes,
        ]);

        return $filling->fresh();
    }
}
