<?php

namespace App\Http\Controllers\Responden;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Services\SurveyEligibilityService;
use App\Services\SurveyFillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use RuntimeException;

class SurveyController extends Controller
{
    public function __construct(
        private SurveyEligibilityService $surveyEligibilityService,
        private SurveyFillingService $surveyFillingService
    ) {}

    /**
     * Display a paginated list of available surveys for the authenticated responden.
     */
    public function index(): View
    {
        $user = Auth::user();

        $surveys = $this->surveyEligibilityService->getAvailableSurveys($user)
            ->latest()
            ->paginate(10);

        return view('responden.surveys.index', compact('surveys'));
    }

    /**
     * Display the detail page for a specific survey.
     */
    public function show(Survey $survey): View
    {
        $user = Auth::user();

        $isEligible = $this->surveyEligibilityService->isEligible($user, $survey);

        // Fallback: if survey.form_link is empty, get it from responses table
        if (empty($survey->form_link)) {
            $userResponse = $survey->responses()->whereNull('input_by_admin_id')->latest()->first();
            if ($userResponse?->google_form_link) {
                $survey->form_link = $userResponse->google_form_link;
                // Persist it so we don't need the fallback next time
                $survey->saveQuietly();
            }
        }

        return view('responden.surveys.show', compact('survey', 'isEligible'));
    }

    /**
     * Start a survey filling for the authenticated responden.
     *
     * Creates a SurveyFilling record and redirects to the upload proof page.
     * The Google Form link is displayed in the view for the user to open in a new tab.
     */
    public function start(Survey $survey): RedirectResponse
    {
        $user = Auth::user();

        try {
            $filling = $this->surveyFillingService->startFilling($user, $survey);

            return redirect()->route('responden.fillings.upload', $filling);
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
