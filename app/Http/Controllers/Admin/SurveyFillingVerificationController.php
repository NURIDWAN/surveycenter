<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RejectionReason;
use App\Models\SurveyFilling;
use App\Services\SurveyFillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class SurveyFillingVerificationController extends Controller
{
    public function __construct(
        private SurveyFillingService $surveyFillingService
    ) {}

    /**
     * Display a paginated list of survey fillings with optional status filter.
     */
    public function index(Request $request): View
    {
        $query = SurveyFilling::with(['survey', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $fillings = $query->orderBy('created_at', 'desc')->paginate(15);

        $rejectionReasons = RejectionReason::all();

        return view('admin.survey-fillings.index', compact('fillings', 'rejectionReasons'));
    }

    /**
     * Display the detail of a specific survey filling.
     */
    public function show(SurveyFilling $filling): View
    {
        $filling->load(['survey', 'user', 'rejectionReason']);

        $rejectionReasons = RejectionReason::all();

        return view('admin.survey-fillings.show', compact('filling', 'rejectionReasons'));
    }

    /**
     * Approve a survey filling.
     */
    public function approve(SurveyFilling $filling): RedirectResponse
    {
        try {
            $this->surveyFillingService->approve($filling);

            return redirect()->back()->with('success', 'Pengisian survey berhasil disetujui.');
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject a survey filling with a rejection reason.
     */
    public function reject(Request $request, SurveyFilling $filling): RedirectResponse
    {
        $request->validate([
            'rejection_reason_id' => 'required|exists:rejection_reasons,id',
            'notes' => 'nullable|string',
        ]);

        try {
            $this->surveyFillingService->reject(
                $filling,
                (int) $request->input('rejection_reason_id'),
                $request->input('notes')
            );

            return redirect()->back()->with('success', 'Pengisian survey berhasil ditolak.');
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
