<?php

namespace App\Http\Controllers\Responden;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadProofRequest;
use App\Models\SurveyFilling;
use App\Services\SurveyFillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class SurveyFillingController extends Controller
{
    public function __construct(
        private SurveyFillingService $surveyFillingService
    ) {}

    /**
     * List all user's survey fillings with status, rejection reasons, and reward amounts.
     */
    public function index(): View
    {
        $fillings = Auth::user()
            ->surveyFillings()
            ->with(['survey', 'rejectionReason'])
            ->latest()
            ->get();

        return view('responden.fillings.index', compact('fillings'));
    }

    /**
     * Show the upload proof form for a SurveyFilling in 'sedang_dikerjakan' status.
     */
    public function showUploadForm(SurveyFilling $filling): View
    {
        // Verify filling belongs to authenticated user
        abort_unless(
            $filling->user_id === Auth::id(),
            Response::HTTP_FORBIDDEN,
            'Anda tidak memiliki akses ke pengisian ini.'
        );

        // Verify filling status is 'sedang_dikerjakan'
        abort_unless(
            $filling->status === SurveyFilling::STATUS_SEDANG_DIKERJAKAN,
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Bukti hanya dapat diunggah untuk pengisian dengan status sedang dikerjakan.'
        );

        $filling->load('survey');

        return view('responden.fillings.upload', compact('filling'));
    }

    /**
     * Upload proof screenshot for a survey filling.
     */
    public function uploadProof(UploadProofRequest $request, SurveyFilling $filling): RedirectResponse
    {
        // Verify filling belongs to authenticated user
        abort_unless(
            $filling->user_id === Auth::id(),
            Response::HTTP_FORBIDDEN,
            'Anda tidak memiliki akses ke pengisian ini.'
        );

        $this->surveyFillingService->uploadProof(
            $filling,
            $request->file('proof_file'),
            $request->input('catatan')
        );

        return redirect()
            ->route('responden.fillings.index')
            ->with('success', 'Bukti pengisian berhasil diunggah. Status telah diperbarui menjadi Menunggu Verifikasi.');
    }
}
