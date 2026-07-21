@extends('layouts.user')

@section('title', 'Detail Transaksi')
@section('page-title', 'Detail Transaksi')
@section('page-description', 'Informasi lengkap transaksi survey')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Back Button --}}
    <div>
        <a href="{{ route('user.transactions.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-orange-600 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Riwayat Transaksi
        </a>
    </div>

    {{-- Transaction Header --}}
    <div class="bg-white rounded-xl border border-gray-200/80 overflow-hidden">
        <div class="bg-gradient-to-r from-orange-50 to-amber-50 px-6 py-5 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-lg bg-white border border-gray-200 flex items-center justify-center">
                        <i data-lucide="receipt" class="w-7 h-7 text-orange-600"></i>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Nomor Transaksi</p>
                        <p class="text-xl font-bold text-gray-900">#{{ $transaction->id }}</p>
                    </div>
                </div>
                <div class="text-right">
                    @php
                        $progress = $transaction->safeProgress();
                        $createPaymentCompleted = $transaction->isStage1Completed();
                        $hasilCompleted = $transaction->isStage2Completed();
                    @endphp
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold {{ $transaction->statusBadgeClass() }}">
                        {{ $transaction->statusLabel() }}
                    </span>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-6">
            {{-- Amount Section --}}
            <div class="border-b border-gray-100 pb-6">
                <p class="text-xs font-medium text-gray-500 uppercase mb-2">Jumlah Pembayaran</p>
                <p class="text-4xl font-bold text-gray-900">
                    Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                </p>
            </div>

            {{-- Survey Info --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase mb-3">Survey</p>
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-lg bg-orange-100 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="file-text" class="w-6 h-6 text-orange-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">
                                {{ $transaction->survey?->title ?? 'Survey Tidak Ditemukan' }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $transaction->survey?->question_count ?? 0 }} Pertanyaan
                            </p>
                        </div>
                    </div>
                </div>

                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase mb-3">Tanggal Transaksi</p>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $transaction->created_at->format('d F Y') }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $transaction->created_at->format('H:i:s') }}</p>
                    </div>
                </div>
            </div>

            {{-- Main Stages --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="rounded-lg border px-4 py-3 {{ $createPaymentCompleted ? 'bg-emerald-50 border-emerald-200' : 'bg-amber-50 border-amber-200' }}">
                    <p class="text-xs font-semibold uppercase {{ $createPaymentCompleted ? 'text-emerald-700' : 'text-amber-700' }}">Tahap 1</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">Create Pembayaran</p>
                    <p class="text-xs mt-1 {{ $createPaymentCompleted ? 'text-emerald-700' : 'text-amber-700' }}">
                        {{ $createPaymentCompleted ? 'Selesai' : 'Menunggu Pembayaran' }}
                    </p>
                </div>

                <div class="rounded-lg border px-4 py-3 {{ $hasilCompleted ? 'bg-emerald-50 border-emerald-200' : 'bg-gray-50 border-gray-200' }}">
                    <p class="text-xs font-semibold uppercase {{ $hasilCompleted ? 'text-emerald-700' : 'text-gray-600' }}">Tahap 2</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">Hasil</p>
                    <p class="text-xs mt-1 {{ $hasilCompleted ? 'text-emerald-700' : 'text-gray-600' }}">
                        {{ $hasilCompleted ? 'Selesai' : 'Dalam proses pengerjaan' }}
                    </p>
                </div>
            </div>

            {{-- Progress Section --}}
            <div class="border-t border-gray-100 pt-6">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-medium text-gray-500 uppercase">Progress Pengerjaan</p>
                    <p class="text-sm font-bold text-gray-900">{{ $progress }}%</p>
                </div>
                <div class="w-full h-3 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all
                        @if($progress >= 100) bg-emerald-500
                        @elseif($progress > 0) bg-blue-500
                        @else bg-gray-300
                        @endif"
                        data-progress-width="{{ $progress }}"></div>
                </div>
                <p class="text-xs text-gray-500 mt-3">
                    @if($progress >= 100)
                        <i data-lucide="check-circle" class="w-4 h-4 inline mr-1 text-emerald-600"></i>
                        Pengerjaan survey telah selesai
                    @elseif($progress > 0)
                        <i data-lucide="loader" class="w-4 h-4 inline mr-1 text-blue-600"></i>
                        Survey sedang dikerjakan
                    @else
                        <i data-lucide="clock" class="w-4 h-4 inline mr-1 text-amber-600"></i>
                        Survey menunggu untuk dikerjakan
                    @endif
                </p>
            </div>

            {{-- Additional Info --}}
            <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                @php
                    $rawMethod = (string) ($transaction->payment_method ?? '');
                    $normalizedMethod = strtolower($rawMethod);

                    $methodLabelMap = [
                        'qris' => 'QRIS',
                        'virtual_account' => 'Virtual Account',
                        'e_wallet' => 'E-Wallet',
                        'gopay' => 'GoPay',
                        'transfer' => 'Transfer Bank',
                        'bank_transfer' => 'Transfer Bank',
                    ];

                    if (str_starts_with($normalizedMethod, 'va_')) {
                        $paymentMethodLabel = 'Virtual Account (' . strtoupper(substr($normalizedMethod, 3)) . ')';
                    } else {
                        $paymentMethodLabel = $methodLabelMap[$normalizedMethod] ?? ($rawMethod !== '' ? strtoupper($rawMethod) : 'Belum ditentukan');
                    }

                    $gatewayReference = $transaction->payment_ref ?: $transaction->singapay_ref;
                    $reference = strtolower((string) ($transaction->singapay_ref ?? ''));

                    if ($reference === '') {
                        $paymentGatewayLabel = 'Belum ditentukan';
                    } else {
                        $paymentGatewayLabel = 'SingaPay';
                    }
                @endphp

                <h4 class="text-sm font-semibold text-gray-900">Informasi Transaksi</h4>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Payment Gateway</p>
                        <p class="font-medium text-gray-900">{{ $paymentGatewayLabel }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Metode Pembayaran</p>
                        <p class="font-medium text-gray-900">{{ $paymentMethodLabel }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Referensi Pembayaran</p>
                        <p class="font-medium text-gray-900">{{ $gatewayReference ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Nomor Bill</p>
                        <p class="font-medium text-gray-900">
                            {{ $transaction->bill_no ?? '-' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">ID Transaksi</p>
                        <p class="font-medium text-gray-900">
                            {{ $transaction->trx_id ?? '-' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="flex gap-3">
        <a href="{{ route('user.surveys.show', $transaction->survey) }}" class="flex-1 px-4 py-3 bg-orange-600 text-white rounded-lg font-medium text-sm hover:bg-orange-700 transition text-center">
            <i data-lucide="eye" class="w-4 h-4 inline mr-2"></i>
            Lihat Survey
        </a>
        <a href="{{ route('transactions.download', $transaction->id) }}" class="flex-1 px-4 py-3 bg-white border border-gray-300 text-gray-700 rounded-lg font-medium text-sm hover:bg-gray-50 transition text-center flex items-center justify-center">
            <i data-lucide="download" class="w-4 h-4 inline mr-2"></i>
            Unduh Invoice
        </a>
        @if(in_array($transaction->status, [\App\Models\Transaction::STATUS_PENDING, \App\Models\Transaction::STATUS_FAILED], true))
            <a href="{{ route('user.payments.show', $transaction) }}" class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-lg font-medium text-sm hover:bg-blue-700 transition">
                <i data-lucide="credit-card" class="w-4 h-4 inline mr-2"></i>
                {{ $transaction->status === \App\Models\Transaction::STATUS_FAILED ? 'Coba Bayar Lagi' : 'Bayar Sekarang' }}
            </a>
        @endif
    </div>

    {{-- Status Info Card --}}
    @php($statusInfo = $transaction->statusInfoCard())
    <div class="{{ $statusInfo['containerClass'] }} rounded-xl p-5">
        <div class="flex gap-3">
            <div class="flex-shrink-0">
                <i data-lucide="{{ $statusInfo['icon'] }}" class="w-5 h-5 {{ $statusInfo['iconClass'] }}"></i>
            </div>
            <div>
                <h4 class="text-sm font-medium {{ $statusInfo['titleClass'] }}">{{ $statusInfo['title'] }}</h4>
                <p class="text-sm mt-1 {{ $statusInfo['descriptionClass'] }}">{{ $statusInfo['description'] }}</p>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-progress-width]').forEach(function(el) {
            const value = parseInt(el.dataset.progressWidth || '0', 10);
            const safeValue = Math.min(Math.max(value, 0), 100);
            el.style.width = safeValue + '%';
        });

        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>
@endpush
