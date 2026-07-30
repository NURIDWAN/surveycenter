<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\SurveyFilling;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PublicSurveyController extends Controller
{
    /**
     * Display a public listing of questionnaires/surveys with availability status filtering.
     */
    public function index(Request $request)
    {
        $statusFilter = $request->query('status', 'all'); // 'all', 'available', 'unavailable'
        $search = trim($request->query('search', ''));

        // Define availability condition logic
        $availableCondition = function (Builder $q) {
            $q->where('status', Survey::STATUS_ACTIVE)
                ->whereNull('completed_at')
                ->where(function (Builder $sub) {
                    $sub->whereNull('deadline')
                        ->orWhere('deadline', '>=', now());
                })
                ->where(function (Builder $sub) {
                    $sub->whereNull('respondent_count')
                        ->orWhere('respondent_count', 0)
                        ->orWhereRaw(
                            'respondent_count > (SELECT COUNT(*) FROM survey_fillings WHERE survey_fillings.survey_id = surveys.id AND survey_fillings.status = ?)',
                            [SurveyFilling::STATUS_DISETUJUI]
                        );
                });
        };

        // Query counts for tabs badge
        $totalCount = Survey::count();
        $availableCount = Survey::query()->where($availableCondition)->count();
        $unavailableCount = max(0, $totalCount - $availableCount);

        // Build survey collection query
        $query = Survey::query()
            ->withCount(['surveyFillings as approved_fillings_count' => function ($q) {
                $q->where('status', SurveyFilling::STATUS_DISETUJUI);
            }]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($statusFilter === 'available') {
            $query->where($availableCondition);
        } elseif ($statusFilter === 'unavailable') {
            $query->whereNot($availableCondition);
        }

        $surveys = $query->latest()->paginate(9)->withQueryString();

        return view('pages.kuisioner', compact(
            'surveys',
            'statusFilter',
            'search',
            'totalCount',
            'availableCount',
            'unavailableCount'
        ));
    }
}
