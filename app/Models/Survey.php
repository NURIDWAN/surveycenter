<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_DRAFT = 'draft';

    protected $fillable = ['user_id', 'title', 'question_count', 'respondent_count', 'form_link', 'description', 'status', 'completed_at'];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    public function responses()
    {
        return $this->hasMany(Response::class);
    }

    public function adminResponses()
    {
        return $this->hasMany(Response::class)->whereNotNull('input_by_admin_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getRespondentCountAttribute($value)
    {
        if ($value !== null) {
            return $value;
        }
        $transaction = $this->transactions()->first();
        if ($transaction) {
            $respondentCost = $transaction->amount - ($this->question_count * 1000);
            return max(0, intval($respondentCost / 1000));
        }
        return 0;
    }
}
