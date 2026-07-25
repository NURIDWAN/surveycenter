<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RejectionReason extends Model
{
    protected $fillable = ['label'];

    public function surveyFillings(): HasMany
    {
        return $this->hasMany(SurveyFilling::class);
    }
}
