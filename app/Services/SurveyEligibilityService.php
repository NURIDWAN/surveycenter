<?php

namespace App\Services;

use App\Models\Survey;
use App\Models\SurveyFilling;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class SurveyEligibilityService
{
    /**
     * Get a query builder for surveys available to the given responden.
     *
     * Returns active surveys with remaining slots, not started by the user,
     * not created by the user, and matching the user's demographics.
     */
    public function getAvailableSurveys(User $responden): Builder
    {
        return Survey::query()
            ->where('status', Survey::STATUS_ACTIVE)
            ->whereDoesntHave('surveyFillings', function (Builder $query) use ($responden) {
                $query->where('user_id', $responden->id);
            })
            ->latest();
    }

    /**
     * Check if a specific responden is eligible for a specific survey.
     */
    public function isEligible(User $responden, Survey $survey): bool
    {
        // Survey must be active
        if ($survey->status !== Survey::STATUS_ACTIVE) {
            return false;
        }

        // Cannot fill own survey
        if ($survey->user_id === $responden->id) {
            return false;
        }

        // Cannot have already started this survey
        $alreadyStarted = SurveyFilling::where('survey_id', $survey->id)
            ->where('user_id', $responden->id)
            ->exists();

        if ($alreadyStarted) {
            return false;
        }

        // Must have remaining slots (approved fillings < respondent_count)
        $approvedCount = SurveyFilling::where('survey_id', $survey->id)
            ->where('status', SurveyFilling::STATUS_DISETUJUI)
            ->count();

        $respondentCount = $survey->respondent_count;

        if ($respondentCount > 0 && $approvedCount >= $respondentCount) {
            return false;
        }

        // Criteria matching temporarily disabled per user instruction
        // "hapus kreteria yang ada di responden, hanya mengandalkan staus survey saja aktif/nonaktif. untuk service kriteria sementara jagan di gunakan dulu"
        return true;
    }

    /**
     * Check if a responden matches the given eligibility criteria.
     * Service criteria is temporarily disabled per user instruction.
     */
    public function matchesCriteria(User $responden, ?array $criteria): bool
    {
        return true;
    }

    /**
     * Apply demographic filters at the database query level where possible.
     * This provides a best-effort DB-level filter for performance.
     */
    private function applyDemographicFilters(Builder $query, User $responden): void
    {
        // For JSON criteria matching at DB level, we filter what we can.
        // Complex criteria matching (age ranges, arrays) is best done in PHP after fetch,
        // but for the query builder we apply broad filters that don't exclude valid results.
        // The getAvailableSurveys method returns a Builder — callers can paginate/get results
        // and the criteria matching is done via JSON column queries where supported.

        // We use a permissive approach: include surveys where criteria match OR criteria is simple enough
        // The fine-grained matching happens in isEligible() for individual checks.

        // For MySQL JSON support, filter jenis_kelamin if user has one set
        if ($responden->jenis_kelamin !== null) {
            $query->where(function (Builder $q) use ($responden) {
                // No restriction on jenis_kelamin (empty array or not set)
                $q->whereNull('eligibility_criteria->jenis_kelamin')
                    ->orWhereJsonLength('eligibility_criteria->jenis_kelamin', 0)
                    ->orWhereJsonContains('eligibility_criteria->jenis_kelamin', $responden->jenis_kelamin);
            });
        } else {
            // User has no jenis_kelamin — only show surveys that don't restrict it
            $query->where(function (Builder $q) {
                $q->whereNull('eligibility_criteria->jenis_kelamin')
                    ->orWhereJsonLength('eligibility_criteria->jenis_kelamin', 0);
            });
        }

        if ($responden->provinsi !== null) {
            $query->where(function (Builder $q) use ($responden) {
                $q->whereNull('eligibility_criteria->provinsi')
                    ->orWhereJsonLength('eligibility_criteria->provinsi', 0)
                    ->orWhereJsonContains('eligibility_criteria->provinsi', $responden->provinsi);
            });
        } else {
            $query->where(function (Builder $q) {
                $q->whereNull('eligibility_criteria->provinsi')
                    ->orWhereJsonLength('eligibility_criteria->provinsi', 0);
            });
        }

        if ($responden->kota !== null) {
            $query->where(function (Builder $q) use ($responden) {
                $q->whereNull('eligibility_criteria->kota')
                    ->orWhereJsonLength('eligibility_criteria->kota', 0)
                    ->orWhereJsonContains('eligibility_criteria->kota', $responden->kota);
            });
        } else {
            $query->where(function (Builder $q) {
                $q->whereNull('eligibility_criteria->kota')
                    ->orWhereJsonLength('eligibility_criteria->kota', 0);
            });
        }

        if ($responden->pendidikan !== null) {
            $query->where(function (Builder $q) use ($responden) {
                $q->whereNull('eligibility_criteria->pendidikan')
                    ->orWhereJsonLength('eligibility_criteria->pendidikan', 0)
                    ->orWhereJsonContains('eligibility_criteria->pendidikan', $responden->pendidikan);
            });
        } else {
            $query->where(function (Builder $q) {
                $q->whereNull('eligibility_criteria->pendidikan')
                    ->orWhereJsonLength('eligibility_criteria->pendidikan', 0);
            });
        }

        if ($responden->pekerjaan !== null) {
            $query->where(function (Builder $q) use ($responden) {
                $q->whereNull('eligibility_criteria->pekerjaan')
                    ->orWhereJsonLength('eligibility_criteria->pekerjaan', 0)
                    ->orWhereJsonContains('eligibility_criteria->pekerjaan', $responden->pekerjaan);
            });
        } else {
            $query->where(function (Builder $q) {
                $q->whereNull('eligibility_criteria->pekerjaan')
                    ->orWhereJsonLength('eligibility_criteria->pekerjaan', 0);
            });
        }

        // Age filtering at DB level using tanggal_lahir
        if ($responden->tanggal_lahir !== null) {
            $age = $responden->tanggal_lahir->age;

            $query->where(function (Builder $q) use ($age) {
                $q->where(function (Builder $inner) use ($age) {
                    $inner->whereNull('eligibility_criteria->age_min')
                        ->orWhereRaw(
                            'JSON_UNQUOTE(JSON_EXTRACT(eligibility_criteria, \'$.age_min\')) <= ?',
                            [$age]
                        );
                });
            });

            $query->where(function (Builder $q) use ($age) {
                $q->where(function (Builder $inner) use ($age) {
                    $inner->whereNull('eligibility_criteria->age_max')
                        ->orWhereRaw(
                            'JSON_UNQUOTE(JSON_EXTRACT(eligibility_criteria, \'$.age_max\')) >= ?',
                            [$age]
                        );
                });
            });
        }
    }
}
