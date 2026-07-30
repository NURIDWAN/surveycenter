<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyFilling extends Model
{
    use HasFactory;

    public const STATUS_SEDANG_DIKERJAKAN = 'sedang_dikerjakan';
    public const STATUS_MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';
    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_DITOLAK = 'ditolak';

    protected $fillable = [
        'survey_id',
        'user_id',
        'status',
        'proof_file_path',
        'catatan',
        'rejection_reason_id',
        'rejection_notes',
        'approved_at',
        'rejected_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rejectionReason(): BelongsTo
    {
        return $this->belongsTo(RejectionReason::class);
    }

    /**
     * Get the accessible URL of the proof screenshot file.
     */
    public function getProofUrlAttribute(): ?string
    {
        if (empty($this->proof_file_path)) {
            return null;
        }

        if (str_starts_with($this->proof_file_path, 'http://') || str_starts_with($this->proof_file_path, 'https://')) {
            return $this->proof_file_path;
        }

        $disk = config('responden.proof_disk', 'public');

        if (\Illuminate\Support\Facades\Storage::disk($disk)->exists($this->proof_file_path)) {
            return \Illuminate\Support\Facades\Storage::disk($disk)->url($this->proof_file_path);
        }

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($this->proof_file_path)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->url($this->proof_file_path);
        }

        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($this->proof_file_path)) {
            return route('admin.survey-fillings.proof', $this);
        }

        return asset('storage/' . ltrim($this->proof_file_path, '/'));
    }
}
