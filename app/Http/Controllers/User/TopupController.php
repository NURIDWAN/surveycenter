<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\TopupTransaction;
use App\Services\FaspayService;
use App\Services\SingaPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TopupController extends Controller
{
    private SingaPayService $singaPay;

    public function __construct(SingaPayService $singaPay)
    {
        $this->singaPay = $singaPay;
    }

    public function index()
    {
        $transactions = Auth::user()->topupTransactions()->latest()->paginate(10);
        return view('user.topups.index', compact('transactions'));
    }

    public function create()
    {
        $gatewayOptions = $this->getGatewayOptions();
        $defaultGateway = $this->resolveDefaultGateway($gatewayOptions);
        
        return view('user.topups.create', compact('gatewayOptions', 'defaultGateway'));
    }

    public function store(Request $request)
    {
        $gatewayOptions = $this->getGatewayOptions();
        $availableGatewayKeys = collect($gatewayOptions)
            ->filter(fn (array $gateway) => ($gateway['enabled'] ?? false) && ($gateway['configured'] ?? false))
            ->keys()
            ->all();

        $defaultGateway = $this->resolveDefaultGateway($gatewayOptions);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_gateway' => 'nullable|string',
            'payment_method' => 'required|in:qris,virtual_account,e_wallet',
        ]);

        $selectedGateway = $validated['payment_gateway'] ?? $defaultGateway;

        if (!in_array($selectedGateway, $availableGatewayKeys, true)) {
            return back()->withInput()->withErrors([
                'payment_gateway' => 'Gateway pembayaran yang dipilih tidak tersedia.',
            ]);
        }

        $transaction = Auth::user()->topupTransactions()->create([
            'amount' => $validated['amount'],
            'status' => TopupTransaction::STATUS_PENDING,
            'payment_method' => $validated['payment_method'],
        ]);

        if ((bool) config('payment_gateways.mock_mode', false)) {
            return $this->processMockPayment($transaction, $validated['payment_method'], $selectedGateway);
        }

        try {
            if ($selectedGateway === 'faspay') {
                return $this->processFaspayPayment($transaction, $validated['payment_method']);
            }

            return $this->processSingaPayPayment($transaction, $validated['payment_method']);
        } catch (\Exception $e) {
            Log::error('Topup Processing Exception', [
                'topup_id' => $transaction->id,
                'user_id' => Auth::id(),
                'gateway' => $selectedGateway,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Terjadi kesalahan saat memproses top up. Silakan coba lagi.');
        }
    }

    private function processMockPayment(TopupTransaction $transaction, string $paymentMethod, string $gateway)
    {
        $defaultStatus = (string) config('payment_gateways.mock_default_status', TopupTransaction::STATUS_PAID);
        $allowedStatuses = [
            TopupTransaction::STATUS_PENDING,
            TopupTransaction::STATUS_PROCESSING,
            TopupTransaction::STATUS_PAID,
            TopupTransaction::STATUS_FAILED,
        ];

        $nextStatus = in_array($defaultStatus, $allowedStatuses, true)
            ? $defaultStatus
            : TopupTransaction::STATUS_PAID;

        $mockReference = 'MOCK-TOPUP-' . Str::upper(Str::random(12));

        $transaction->update([
            'singapay_ref' => $mockReference,
            'status' => $nextStatus,
        ]);

        Log::info('Mock topup processed', [
            'topup_id' => $transaction->id,
            'user_id' => Auth::id(),
            'status' => $nextStatus,
        ]);

        if ($nextStatus === TopupTransaction::STATUS_PAID) {
            return redirect()->route('user.topups.index')->with('success', 'Top up berhasil (mode development).');
        }

        return redirect()->route('user.topups.index')->with('info', 'Top up dibuat dengan status: ' . $nextStatus);
    }

    private function processSingaPayPayment(TopupTransaction $transaction, string $paymentMethod)
    {
        // For Singapay we need a success redirect URL
        $redirectUrl = route('user.topups.index'); 

        $billNo = $this->generateBillNo();

        $invoice = $this->singaPay->createInvoice(
            $transaction->amount,
            [
                [
                    'name' => 'Top Up Saldo',
                    'quantity' => 1,
                    'unit_price' => $transaction->amount,
                ],
            ],
            $redirectUrl,
            $billNo
        );

        if (!isset($invoice['success']) || !$invoice['success']) {
            return back()->with('error', 'Gagal membuat pembayaran: ' . ($invoice['message'] ?? 'Kesalahan tidak diketahui.'));
        }

        $transaction->update([
            'singapay_ref' => $invoice['reff_no'] ?? $billNo,
            'bill_no' => $billNo,
            'payment_ref' => $billNo,
            'status' => TopupTransaction::STATUS_PROCESSING,
        ]);

        return redirect($invoice['payment_url']);
    }

    private function processFaspayPayment(TopupTransaction $transaction, string $paymentMethod)
    {
        /** @var FaspayService $faspayService */
        $faspayService = app(FaspayService::class);

        if (!$faspayService->isConfigured()) {
            return back()->withInput()->withErrors([
                'payment_gateway' => 'Faspay belum dikonfigurasi.',
            ]);
        }

        $billNo = $this->generateBillNo();

        $invoiceData = [
            'bill_no' => $billNo,
            'bill_reff' => 'TOPUP-' . $transaction->id,
            'bill_total' => $transaction->amount,
            'bill_description' => 'Top Up Saldo',
            'cust_name' => preg_replace('/[^a-zA-Z0-9\s]/', '', Auth::user()->name ?? 'Customer'),
            'cust_email' => Auth::user()->email ?? '',
            'cust_phone' => Auth::user()->phone ?? '',
            'due_date' => now()->addMinutes((int) config('faspay.invoice_expiration', 30))->format('Y-m-d H:i:s'),
            'bill_expired_date' => now()->addMinutes((int) config('faspay.invoice_expiration', 30))->format('Y-m-d H:i:s'),
            'return_url' => route('faspay.return'),
            'notif_url' => route('faspay.notification'),
        ];

        $response = $faspayService->createInvoice($invoiceData);

        if (!($response['success'] ?? false) || empty($response['payment_url'])) {
            $reason = $response['message'] ?? 'Gagal membuat link pembayaran Faspay.';
            return back()->with('error', $reason);
        }

        $transaction->update([
            'singapay_ref' => $billNo,
            'bill_no' => $billNo,
            'payment_ref' => $billNo,
            'trx_id' => $response['trx_id'] ?? null,
            'status' => TopupTransaction::STATUS_PROCESSING,
        ]);

        return redirect($response['payment_url']);
    }

    private function getGatewayOptions(): array
    {
        $gateways = config('payment_gateways.gateways', []);
        $orderedGateways = [];

        foreach (config('payment_gateways.order', []) as $gatewayKey) {
            if (array_key_exists($gatewayKey, $gateways)) {
                $orderedGateways[$gatewayKey] = $gateways[$gatewayKey];
                unset($gateways[$gatewayKey]);
            }
        }

        return $orderedGateways + $gateways;
    }

    private function resolveDefaultGateway(array $gatewayOptions): string
    {
        return (string) config('payment_gateways.default', 'singapay');
    }

    private function generateBillNo(): string
    {
        $prefix = config('payment_gateways.invoice_prefix', 'TRX');
        return $prefix . '-TU-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6));
    }
}
