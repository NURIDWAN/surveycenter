<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Response;
use App\Models\Survey;
use App\Models\Transaction;
use App\Services\FormLinkValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SurveyManagementController extends Controller
{
    public function __construct(private FormLinkValidationService $formLinkValidationService)
    {
    }

    public function index(Request $request)
    {
        $allowedFilters = ['needs_results', 'completed', 'all_paid'];
        $filter = (string) $request->input('filter', 'all_paid');

        if (!in_array($filter, $allowedFilters, true)) {
            $filter = 'all_paid';
        }

        $query = Survey::query()
            ->withSum('adminResponses', 'respond_count')
            ->with([
                'user',
                'transactions' => function ($transactionQuery) {
                    $transactionQuery->where('status', Transaction::STATUS_PAID)
                        ->latest('updated_at');
                },
                'adminResponses' => function ($responseQuery) {
                    $responseQuery->whereNotNull('input_by_admin_id')
                        ->latest('updated_at');
                },
                'responses' => function ($responseQuery) {
                    $responseQuery->whereNull('input_by_admin_id')
                        ->latest('updated_at');
                },
                'adminResponses.inputByAdmin',
            ])
            ->whereHas('transactions', function ($transactionQuery) {
                $transactionQuery->where('status', Transaction::STATUS_PAID);
            });

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(function ($surveyQuery) use ($search) {
                $surveyQuery->where('title', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $adminResponseSubquery = "COALESCE((SELECT SUM(respond_count) FROM responses WHERE responses.survey_id = surveys.id AND responses.input_by_admin_id IS NOT NULL), 0)";
        $targetRespondentColumn = 'COALESCE(surveys.respondent_count, 0)';
        $latestPaidProgressSubquery = "COALESCE((SELECT progress FROM transactions WHERE transactions.survey_id = surveys.id AND transactions.status = 'paid' ORDER BY updated_at DESC, id DESC LIMIT 1), 0)";

        if ($filter === 'needs_results') {
            $query->whereRaw("((surveys.respondent_count IS NOT NULL AND {$adminResponseSubquery} < {$targetRespondentColumn}) OR (surveys.respondent_count IS NULL AND {$latestPaidProgressSubquery} < 100))");
        } elseif ($filter === 'completed') {
            $query->whereRaw("((surveys.respondent_count IS NOT NULL AND {$adminResponseSubquery} >= {$targetRespondentColumn}) OR (surveys.respondent_count IS NULL AND {$latestPaidProgressSubquery} >= 100))");
        }

        $query->orderByDesc(
            Transaction::select('updated_at')
                ->whereColumn('transactions.survey_id', 'surveys.id')
                ->where('status', Transaction::STATUS_PAID)
                ->latest('updated_at')
                ->limit(1)
        );

        $surveys = $query->paginate(10)->withQueryString();

        return view('admin.surveys.manage', compact('surveys', 'filter'));
    }

    public function toggleStatus(Request $request, Survey $survey)
    {
        $newStatus = $survey->status === Survey::STATUS_ACTIVE
            ? Survey::STATUS_DRAFT
            : Survey::STATUS_ACTIVE;

        $data = ['status' => $newStatus];

        // When activating, optionally update reward_amount
        if ($newStatus === Survey::STATUS_ACTIVE && $request->has('reward_amount')) {
            $data['reward_amount'] = max(0, (int) $request->input('reward_amount', 0));
        }

        $survey->update($data);

        $label = $newStatus === Survey::STATUS_ACTIVE ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Survey \"{$survey->title}\" berhasil {$label} untuk responden.");
    }

    public function storeRespondent(Request $request, Survey $survey)
    {
        $validated = $request->validate([
            'respond_count' => 'required|integer|min:1',
            'google_form_link' => 'required|url|max:2048',
        ]);

        $formLinkError = $this->formLinkValidationService->validate(
            $validated['google_form_link'] ?? null,
            $survey->title
        );

        if ($formLinkError !== null) {
            throw ValidationException::withMessages(['google_form_link' => $formLinkError]);
        }

        $paidTransaction = $survey->transactions()
            ->where('status', Transaction::STATUS_PAID)
            ->latest('updated_at')
            ->first();

        if (!$paidTransaction) {
            return back()->with('error', 'Survey ini belum memiliki transaksi berstatus paid.');
        }

        $ownerUserId = $survey->user_id ?? $paidTransaction->user_id;

        if (!$ownerUserId) {
            return back()->with('error', 'User pemilik survey tidak ditemukan.');
        }

        Response::create([
            'survey_id' => $survey->id,
            'user_id' => $ownerUserId,
            'input_by_admin_id' => Auth::id(),
            'respond_count' => $validated['respond_count'],
            'google_form_link' => $validated['google_form_link'] ?? null,
        ]);

        if (!empty($validated['google_form_link']) && empty($survey->form_link)) {
            $survey->update(['form_link' => $validated['google_form_link']]);
        }

        return back()->with('success', 'Responden berhasil ditambahkan. Progress survey dapat diperbarui manual dari menu Progress CRM.');
    }

    public function updateRespondent(Request $request, Survey $survey, Response $response)
    {
        if ((int) $response->survey_id !== (int) $survey->id) {
            return back()->with('error', 'Data responden tidak cocok dengan survey yang dipilih.');
        }

        if (!$response->input_by_admin_id) {
            return back()->with('error', 'Hanya responden yang diinput admin yang bisa diedit dari halaman ini.');
        }

        $validated = $request->validate([
            'respond_count' => 'required|integer|min:1',
            'google_form_link' => 'required|url|max:2048',
        ]);

        $formLinkError = $this->formLinkValidationService->validate(
            $validated['google_form_link'] ?? null,
            $survey->title
        );

        if ($formLinkError !== null) {
            throw ValidationException::withMessages(['google_form_link' => $formLinkError]);
        }

        $response->update([
            'respond_count' => $validated['respond_count'],
            'google_form_link' => $validated['google_form_link'] ?? null,
            'input_by_admin_id' => Auth::id(),
        ]);

        if (!empty($validated['google_form_link']) && empty($survey->form_link)) {
            $survey->update(['form_link' => $validated['google_form_link']]);
        }

        return back()->with('success', 'Data responden admin berhasil diperbarui.');
    }
}
