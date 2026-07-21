<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\SingaPayService;
use App\Services\FormLinkValidationService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Response;
use App\Helpers\VolumePricing;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    private SingaPayService $singaPay;
    private FormLinkValidationService $formLinkValidationService;

    public function __construct(SingaPayService $singaPay, FormLinkValidationService $formLinkValidationService)
    {
        $this->singaPay = $singaPay;
        $this->formLinkValidationService = $formLinkValidationService;
    }

    public function create(Survey $survey)
    {
        // Tampilkan form transaksi berdasarkan survey yang dipilih
        return view('transactions.create', compact('survey'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'question_count' => 'required|integer|min:1',
            'respondent_count' => 'required|integer|min:1',
            'items' => 'required|string',
            'link' => 'required|url|max:2048',
        ]);

        // Volume pricing berdasarkan jumlah responden
        $finalPrice = VolumePricing::calculateTotal($validated['question_count'], $validated['respondent_count']);

        $minOrder = VolumePricing::getMinOrder();
        if ($finalPrice < $minOrder) {
            abort(403, 'Minimal total pembayaran adalah Rp ' . number_format($minOrder, 0, ',', '.'));
        }

        $survey = Survey::create([
            'title' => $validated['title'],
            'question_count' => $validated['question_count'],
            'respondent_count' => $validated['respondent_count'],
            'user_id' => Auth::id(),
        ]);

        Response::create([
            'survey_id' => $survey->id,
            'user_id' => Auth::id(),
            'respond_count' => $validated['respondent_count'],
            'google_form_link' => $validated['link'] ?? null,
        ]);

        if ((bool) config('payment_gateways.mock_mode', false)) {
            $mockStatus = $this->resolveMockStatus();

            $transaction = Transaction::create([
                'survey_id' => $survey->id,
                'user_id' => Auth::id(),
                'amount' => $finalPrice,
                'status' => $mockStatus,
                'singapay_ref' => 'MOCK-' . Str::upper(Str::random(12)),
            ]);

            return redirect()->route('transactions.progress', $transaction)
                ->with('success', 'Mock payment dibuat dengan status: ' . $mockStatus . '.');
        }

        $billNo = $this->generateBillNo();

        $invoice = $this->singaPay->createInvoice(
            $finalPrice,
            [['name' => $validated['title'] ?? 'Survey Payment', 'quantity' => 1, 'unit_price' => $finalPrice]],
            route('transactions.progress', $transaction ?? ''),
            $billNo
        );

        if (!$invoice['success']) {
            Log::error('Transaction Failed (SingaPay)', [
                'user_id' => Auth::id(),
                'error' => $invoice['message'] ?? 'Unknown error',
            ]);
            return back()->with('error', 'Gagal membuat pembayaran: ' . ($invoice['message'] ?? 'Kesalahan tidak diketahui.'));
        }

        $transaction = Transaction::create([
            'survey_id' => $survey->id,
            'user_id' => Auth::id(),
            'amount' => $finalPrice,
            'status' => Transaction::STATUS_PROCESSING,
            'bill_no' => $billNo,
            'singapay_ref' => $invoice['reff_no'],
        ]);

        return redirect($invoice['payment_url']);
    }

    public function handleInvoice(Request $request)
    {
        Log::info('SingaPay Invoice Webhook', $request->all());

        $service = app(\App\Services\SingaPayService::class);
        $result = $service->webhook($request);

        Log::info('SingaPay Invoice Webhook Result', $result);

        // If real transaction not found, try test transactions
        if (isset($result['handled']) && !$result['handled']) {
            $reffNo = data_get($request->all(), 'data.payment.additional_info.payment_link.reff_no');
            if ($reffNo) {
                $testTrx = \App\Models\SingaPayTestTransaction::where('singapay_ref', $reffNo)->first();
                if ($testTrx) {
                    $status = data_get($request->all(), 'data.transaction.status');
                    $method = data_get($request->all(), 'data.payment.method');
                    if (in_array($status, ['paid', 'settlement', 'success'])) {
                        $testTrx->markAsPaid(array_merge($request->all(), ['payment_method' => $method]));
                        Log::info('SingaPay Invoice Webhook: Test transaction marked as paid', ['id' => $testTrx->id]);
                    } elseif (in_array($status, ['failed', 'cancelled', 'expired'])) {
                        $testTrx->update([
                            'status' => \App\Models\SingaPayTestTransaction::STATUS_FAILED,
                            'webhook_payload' => $request->all(),
                        ]);
                    }
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }

    public function history()
    {
        $transactions = Transaction::with('survey')
            ->where('user_id', auth::id())
            ->latest()
            ->paginate(10);

        return view('transactions.history', compact('transactions'));
    }

    public function payment(Transaction $transaction)
    {
        return view('transactions.payment', [
            'transaction' => $transaction,
        ]);
    }

    public function processPayment(Request $request, Transaction $transaction)
    {
        $request->validate([
            'payment_method' => 'required|in:qris,transfer,gopay,virtual_account,e_wallet',
        ]);

        $transaction->update([
            'payment_method' => $request->payment_method,
        ]);

        if ((bool) config('payment_gateways.mock_mode', false)) {
            $transaction->update([
                'status' => $this->resolveMockStatus(),
            ]);

            return redirect()->route('transactions.progress', $transaction)
                ->with('success', 'Mock payment diproses tanpa gateway eksternal.');
        }

        $billNo = $this->generateBillNo($transaction->id);

        $invoice = $this->singaPay->createInvoice(
            $transaction->amount,
            [['name' => $transaction->survey->title ?? 'Survey Payment', 'quantity' => 1, 'unit_price' => $transaction->amount]],
            route('transactions.progress', $transaction),
            $billNo
        );

        if (!$invoice['success']) {
            Log::error('Transaction Failed (SingaPay)', [
                'transaction_id' => $transaction->id,
                'error' => $invoice['message'] ?? 'Unknown error',
            ]);

            return back()->with('error', 'Gagal membuat pembayaran: ' . ($invoice['message'] ?? 'Kesalahan tidak diketahui.'));
        }

        $transaction->update([
            'bill_no' => $billNo,
            'singapay_ref' => $invoice['reff_no'],
            'status' => Transaction::STATUS_PROCESSING,
        ]);

        return redirect($invoice['payment_url']);
    }

    private function resolveMockStatus(): string
    {
        $defaultStatus = (string) config('payment_gateways.mock_default_status', Transaction::STATUS_PAID);
        $allowedStatuses = [
            Transaction::STATUS_PENDING,
            Transaction::STATUS_PROCESSING,
            Transaction::STATUS_PAID,
            Transaction::STATUS_FAILED,
        ];

        return in_array($defaultStatus, $allowedStatuses, true)
            ? $defaultStatus
            : Transaction::STATUS_PAID;
    }

    private function generateBillNo(?int $transactionId = null): string
    {
        $prefix = config('payment_gateways.invoice_prefix', 'TRX');

        if ($transactionId !== null) {
            return $prefix . '-' . $transactionId . '-' . now()->format('YmdHis');
        }

        return $prefix . '-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6));
    }

    public function showTransfer(Transaction $transaction)
    {
        return view('transactions.transfer', compact('transaction'));
    }

    public function invoice(Transaction $transaction)
    {
        return view('transactions.invoice', compact('transaction'));
    }

    public function download(Transaction $transaction)
    {
        $pdf = Pdf::loadView('transactions.invoice_pdf', compact('transaction'));
        return $pdf->download("invoice-{$transaction->id}.pdf");
    }

    public function cart()
    {
        $transactions = Transaction::with('survey')
            ->where('user_id', auth::id())
            ->latest()
            ->get();

        return view('cart.index', compact('transactions'));
    }

    public function show(Transaction $transaction)
    {
        $qrImage = QrCode::size(300)->generate($transaction->qr_data);
        return view('transactions.show', compact('transaction', 'qrImage'));
    }
}
