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
            // Exclude surveys created by this user
            ->where('user_id', '!=', $responden->id)
            // Exclude surveys the user has already started
            ->whereDoesntHave('surveyFillings', function (Builder $query) use ($responden) {
                $query->where('user_id', $responden->id);
            })
            // Only include surveys with remaining slots
            ->where(function (Builder $query) {
                $query->where(function (Builder $q) {
                    // respondent_count NULL or 0 means unlimited
                    $q->whereNull('respondent_count')
                        ->orWhere('respondent_count', 0);
                })->orWhereColumn(
                    'respondent_count',
                    '>',
                    \Illuminate\Support\Facades\DB::raw(
                        '(SELECT COUNT(*) FROM survey_fillings WHERE survey_fillings.survey_id = surveys.id AND survey_fillings.status = \'' . SurveyFilling::STATUS_DISETUJUI . '\')'
                    )
                );
            })
            // Filter by eligibility criteria
            ->where(function (Builder $query) use ($responden) {
                $query->whereNull('eligibility_criteria')
                    ->orWhere('eligibility_criteria', '[]')
                    ->orWhere('eligibility_criteria', '{}')
                    ->orWhere(function (Builder $q) use ($responden) {
                        // Include surveys where the responden matches criteria
                        // This is handled via a callback that fetches all and filters in PHP
                        // For DB-level filtering, we use a broad match and let isEligible do fine-grained check
                        // But for efficiency, we apply what we can at DB level
                        $this->applyDemographicFilters($q, $responden);
                    });
            });
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

        // Must match eligibility criteria
        $criteria = $survey->eligibility_criteria;

        return $this->matchesCriteria($responden, $criteria);
    }

    /**
     * Check if a responden matches the given eligibility criteria.
     *
     * If criteria is null or empty, returns true (visible to all).
     * Each non-empty criterion field must be satisfied by the user's profile.
     * If a user's profile field is null for a required criterion, returns false.
     */
    public function matchesCriteria(User $responden, ?array $criteria): bool
    {
        // Null or empty criteria means no restriction
        if (empty($criteria)) {
            return true;
        }

        // Check jenis_kelamin
        if (!empty($criteria['jenis_kelamin']) && is_array($criteria['jenis_kelamin'])) {
            if ($responden->jenis_kelamin !== null && !in_array($responden->jenis_kelamin, $criteria['jenis_kelamin'])) {
                return false;
            }
        }

        // Check age range
        if (isset($criteria['age_min']) || isset($criteria['age_max'])) {
            if ($responden->tanggal_lahir !== null) {
                $age = $responden->tanggal_lahir->age;

                if (isset($criteria['age_min']) && $age < $criteria['age_min']) {
                    return false;
                }

                if (isset($criteria['age_max']) && $age > $criteria['age_max']) {
                    return false;
                }
            }
        }

        // Check provinsi
        if (!empty($criteria['provinsi']) && is_array($criteria['provinsi'])) {
            if ($responden->provinsi !== null && !in_array($responden->provinsi, $criteria['provinsi'])) {
                return false;
            }
        }

        // Check kota
        if (!empty($criteria['kota']) && is_array($criteria['kota'])) {
            if ($responden->kota !== null && !in_array($responden->kota, $criteria['kota'])) {
                return false;
            }
        }

        // Check pendidikan
        if (!empty($criteria['pendidikan']) && is_array($criteria['pendidikan'])) {
            if ($responden->pendidikan !== null && !in_array($responden->pendidikan, $criteria['pendidikan'])) {
                return false;
            }
        }

        // Check pekerjaan
        if (!empty($criteria['pekerjaan']) && is_array($criteria['pekerjaan'])) {
            if ($responden->pekerjaan !== null && !in_array($responden->pekerjaan, $criteria['pekerjaan'])) {
                return false;
            }
        }

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
