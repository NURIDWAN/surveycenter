<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\Transaction;
use App\Services\FormLinkValidationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\VolumePricing;
use Illuminate\Validation\ValidationException;

class SurveyController extends Controller
{
    public function __construct(private FormLinkValidationService $formLinkValidationService)
    {
    }

    /**
     * Display a listing of user's surveys.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Survey::where('user_id', $user->id)
            ->withSum('adminResponses', 'respond_count')
            ->with(['transactions' => function($q) {
                $q->latest()->limit(1);
            }]);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            // Filter based on main stage progress and payment status
            if ($request->status === 'completed') {
                $query->whereHas('transactions', function($q) {
                    $q->where('progress', '>=', 100);
                });
            } elseif ($request->status === 'in_progress') {
                $query->whereHas('transactions', function($q) {
                    $q->whereIn('status', [Transaction::STATUS_PROCESSING, Transaction::STATUS_PAID])
                      ->where('progress', '<', 100);
                });
            } elseif ($request->status === 'pending') {
                $query->where(function($subQuery) {
                    $subQuery->whereDoesntHave('transactions')
                        ->orWhereHas('transactions', function($q) {
                            $q->where('status', Transaction::STATUS_PENDING);
                        });
                });
            }
        }

        // Search
        if ($request->has('search') && $request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $surveys = $query->latest()->paginate(10);

        return view('user.surveys.index', compact('surveys'));
    }

    /**
     * Show the form for creating a new survey.
     */
    public function create()
    {
        $terms = \App\Models\Setting::where('key', 'terms_content')->value('value') ?? '';
        return view('user.surveys.create', compact('terms'));
    }

    /**
     * Store a newly created survey.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'                  => 'required|string|max:255',
            'question_count'         => 'required|integer|min:1|max:100',
            'respondent_count'       => 'required|integer|min:1|max:10000',
            'form_link'              => 'required|url|max:2048',
            'description'            => 'nullable|string|max:1000',
            'reward_amount'          => 'nullable|integer|min:0',
            'deadline'               => 'nullable|date|after:now',
            'estimated_time_minutes' => 'nullable|integer|min:1',
            'ec_jenis_kelamin'       => 'nullable|array',
            'ec_jenis_kelamin.*'     => 'in:Laki-laki,Perempuan',
            'ec_age_min'             => 'nullable|integer|min:0',
            'ec_age_max'             => 'nullable|integer|min:0',
            'ec_provinsi'            => 'nullable|string|max:1000',
            'ec_kota'                => 'nullable|string|max:1000',
            'ec_pendidikan'          => 'nullable|string|max:1000',
            'ec_pekerjaan'           => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();

        // Calculate cost — volume pricing berdasarkan jumlah responden
        $totalCost = VolumePricing::calculateTotal($validated['question_count'], $validated['respondent_count']);

        // Enforce minimum order
        $minOrder = VolumePricing::getMinOrder();
        if ($totalCost < $minOrder) {
            throw ValidationException::withMessages([
                'respondent_count' => 'Total biaya minimal Rp ' . number_format($minOrder, 0, ',', '.') . ' per survey. Tambah jumlah pertanyaan atau responden.',
            ]);
        }

        // Build eligibility criteria JSON
        $eligibilityCriteria = $this->buildEligibilityCriteria($validated);

        // Create survey
        $survey = Survey::create([
            'user_id'                => $user->id,
            'title'                  => $validated['title'],
            'question_count'         => $validated['question_count'],
            'respondent_count'       => $validated['respondent_count'],
            'reward_amount'          => $validated['reward_amount'] ?? 0,
            'deadline'               => $validated['deadline'] ?? null,
            'estimated_time_minutes' => $validated['estimated_time_minutes'] ?? null,
            'eligibility_criteria'   => $eligibilityCriteria,
        ]);

        // Store form link in Response
        \App\Models\Response::create([
            'survey_id'        => $survey->id,
            'user_id'          => $user->id,
            'respond_count'    => $validated['respondent_count'],
            'google_form_link' => $validated['form_link'],
        ]);

        // Also store form_link directly on the survey for easy access
        $survey->update(['form_link' => $validated['form_link']]);

        // Create transaction
        Transaction::create([
            'survey_id' => $survey->id,
            'user_id'   => $user->id,
            'amount'    => $totalCost,
            'status'    => Transaction::STATUS_PENDING,
            'progress'  => 0,
        ]);

        return redirect()->route('user.surveys.show', $survey)
            ->with('success', 'Survey berhasil dibuat! Silakan lakukan pembayaran untuk memulai.');
    }

    /**
     * Display the specified survey.
     */
    public function show(Survey $survey)
    {
        // Ensure user owns this survey
        if ($survey->user_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $survey->load(['responses', 'adminResponses', 'transactions' => function($q) {
            $q->latest();
        }]);

        $latestTransaction = $survey->transactions->first();

        return view('user.surveys.show', compact('survey', 'latestTransaction'));
    }

    /**
     * Show the form for editing the survey.
     */
    public function edit(Survey $survey)
    {
        if ($survey->user_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }

        return view('user.surveys.edit', compact('survey'));
    }

    /**
     * Update the specified survey.
     */
    public function update(Request $request, Survey $survey)
    {
        if ($survey->user_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'title'                  => 'required|string|max:255',
            'reward_amount'          => 'nullable|integer|min:0',
            'deadline'               => 'nullable|date|after:now',
            'estimated_time_minutes' => 'nullable|integer|min:1',
            'ec_jenis_kelamin'       => 'nullable|array',
            'ec_jenis_kelamin.*'     => 'in:Laki-laki,Perempuan',
            'ec_age_min'             => 'nullable|integer|min:0',
            'ec_age_max'             => 'nullable|integer|min:0',
            'ec_provinsi'            => 'nullable|string|max:1000',
            'ec_kota'                => 'nullable|string|max:1000',
            'ec_pendidikan'          => 'nullable|string|max:1000',
            'ec_pekerjaan'           => 'nullable|string|max:1000',
        ]);

        // Build eligibility criteria JSON
        $eligibilityCriteria = $this->buildEligibilityCriteria($validated);

        $survey->update([
            'title'                  => $validated['title'],
            'reward_amount'          => $validated['reward_amount'] ?? 0,
            'deadline'               => $validated['deadline'] ?? null,
            'estimated_time_minutes' => $validated['estimated_time_minutes'] ?? null,
            'eligibility_criteria'   => $eligibilityCriteria,
        ]);

        return redirect()->route('user.surveys.show', $survey)
            ->with('success', 'Survey berhasil diperbarui!');
    }

    /**
     * Remove the specified survey.
     */
    public function destroy(Survey $survey)
    {
        if ($survey->user_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Only allow delete if no transactions or all transactions are pending
        $hasPaidTransactions = $survey->transactions()->where('status', Transaction::STATUS_PAID)->exists();
        
        if ($hasPaidTransactions) {
            return back()->with('error', 'Tidak dapat menghapus survey yang sudah dibayar.');
        }

        $survey->transactions()->delete();
        $survey->delete();

        return redirect()->route('user.surveys.index')
            ->with('success', 'Survey berhasil dihapus!');
    }

    /**
     * Export survey details to PDF
     */
    public function exportPdf(Survey $survey)
    {
        // Ensure user owns this survey
        if ($survey->user_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Load relationships
        $survey->load(['responses', 'adminResponses', 'transactions' => function($q) {
            $q->latest();
        }]);

        // Generate PDF
        $pdf = Pdf::loadView('user.surveys.export-pdf', [
            'survey' => $survey,
            'transactions' => $survey->transactions,
            'responses' => $survey->responses,
            'adminResponses' => $survey->adminResponses,
            'user' => Auth::user(),
            'generatedAt' => now(),
        ])
        ->setPaper('a4')
        ->setOption('margin-top', 10)
        ->setOption('margin-bottom', 10)
        ->setOption('margin-left', 10)
        ->setOption('margin-right', 10);

        return $pdf->download("survey-{$survey->id}-" . now()->format('Y-m-d-His') . ".pdf");
    }

    /**
     * Export survey responses to PDF
     */
    public function exportResponsesPdf(Survey $survey)
    {
        // Ensure user owns this survey
        if ($survey->user_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Load responses
        $survey->load('responses');

        if ($survey->responses->isEmpty()) {
            return back()->with('error', 'Tidak ada respons untuk diekspor.');
        }

        // Generate PDF
        $pdf = Pdf::loadView('user.surveys.export-responses-pdf', [
            'survey' => $survey,
            'responses' => $survey->responses,
            'user' => Auth::user(),
            'generatedAt' => now(),
        ])
        ->setPaper('a4', 'landscape')
        ->setOption('margin-top', 10)
        ->setOption('margin-bottom', 10)
        ->setOption('margin-left', 10)
        ->setOption('margin-right', 10);

        return $pdf->download("survey-responses-{$survey->id}-" . now()->format('Y-m-d-His') . ".pdf");
    }

    /**
     * Build eligibility criteria JSON from form input fields.
     */
    private function buildEligibilityCriteria(array $validated): ?array
    {
        $criteria = [];

        if (!empty($validated['ec_jenis_kelamin'])) {
            $criteria['jenis_kelamin'] = $validated['ec_jenis_kelamin'];
        } else {
            $criteria['jenis_kelamin'] = [];
        }

        $criteria['age_min'] = isset($validated['ec_age_min']) ? (int) $validated['ec_age_min'] : null;
        $criteria['age_max'] = isset($validated['ec_age_max']) ? (int) $validated['ec_age_max'] : null;

        $criteria['provinsi'] = !empty($validated['ec_provinsi'])
            ? array_map('trim', explode(',', $validated['ec_provinsi']))
            : [];

        $criteria['kota'] = !empty($validated['ec_kota'])
            ? array_map('trim', explode(',', $validated['ec_kota']))
            : [];

        $criteria['pendidikan'] = !empty($validated['ec_pendidikan'])
            ? array_map('trim', explode(',', $validated['ec_pendidikan']))
            : [];

        $criteria['pekerjaan'] = !empty($validated['ec_pekerjaan'])
            ? array_map('trim', explode(',', $validated['ec_pekerjaan']))
            : [];

        // Return null if all criteria are empty/null (no restrictions)
        $hasAnyCriteria = !empty($criteria['jenis_kelamin'])
            || $criteria['age_min'] !== null
            || $criteria['age_max'] !== null
            || !empty($criteria['provinsi'])
            || !empty($criteria['kota'])
            || !empty($criteria['pendidikan'])
            || !empty($criteria['pekerjaan']);

        return $hasAnyCriteria ? $criteria : null;
    }

    /**
     * Display the responden survey management page.
     * Shows surveys with toggle to activate/deactivate for respondents.
     */
    public function respondenManagement()
    {
        $surveys = Survey::where('user_id', Auth::id())
            ->whereHas('transactions', function ($q) {
                $q->where('status', Transaction::STATUS_PAID);
            })
            ->withCount(['surveyFillings as approved_count' => function ($q) {
                $q->where('status', 'disetujui');
            }])
            ->withCount(['surveyFillings as pending_count' => function ($q) {
                $q->where('status', 'menunggu_verifikasi');
            }])
            ->latest()
            ->paginate(15);

        return view('user.surveys.responden', compact('surveys'));
    }

    /**
     * Toggle a survey's status between active and draft.
     */
    public function toggleStatus(Survey $survey)
    {
        if ($survey->user_id !== Auth::id()) {
            abort(403);
        }

        $newStatus = $survey->status === Survey::STATUS_ACTIVE
            ? Survey::STATUS_DRAFT
            : Survey::STATUS_ACTIVE;

        $survey->update(['status' => $newStatus]);

        $label = $newStatus === Survey::STATUS_ACTIVE ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Survey \"{$survey->title}\" berhasil {$label}.");
    }
}
