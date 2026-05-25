@extends('layouts.user')

@section('title', 'Wallet')
@section('page-title', 'Wallet')
@section('page-description', 'Saldo dan mutasi wallet akun Anda')

@section('content')
<div class="space-y-6">
    <div class="bg-gradient-to-r from-emerald-50 to-teal-50 rounded-xl border border-emerald-200/80 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <i data-lucide="wallet" class="w-6 h-6 text-emerald-600"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">Saldo Wallet</p>
                    <p class="text-3xl font-extrabold text-emerald-600">
                        Rp {{ number_format($wallet->balance, 0, ',', '.') }}
                    </p>
                </div>
            </div>
            <a href="{{ route('user.topups.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Top Up
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-gray-900">Mutasi Wallet</h3>
                <p class="text-xs text-gray-500 mt-0.5">Riwayat saldo masuk dan keluar</p>
            </div>
            <a href="{{ route('user.topups.index') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-100 text-gray-700 text-xs font-semibold hover:bg-gray-200 transition">
                Riwayat Top Up
            </a>
        </div>

        @if($transactions->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="receipt-text" class="w-8 h-8 text-gray-400"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Belum ada mutasi wallet</h3>
                <p class="text-gray-500 mb-6">Mutasi akan muncul setelah top up berhasil atau saldo digunakan.</p>
                <a href="{{ route('user.topups.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                    Top Up Sekarang
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-gray-50/50 border-b border-gray-200/80 text-gray-500">
                        <tr>
                            <th class="px-6 py-4 font-medium">Tanggal</th>
                            <th class="px-6 py-4 font-medium">Keterangan</th>
                            <th class="px-6 py-4 font-medium">Tipe</th>
                            <th class="px-6 py-4 font-medium text-right">Nominal</th>
                            <th class="px-6 py-4 font-medium text-right">Saldo Akhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($transactions as $trx)
                            @php($isCredit = $trx->type === \App\Models\WalletTransaction::TYPE_CREDIT)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-6 py-4 text-gray-500">
                                    {{ $trx->created_at->format('d M Y, H:i') }}
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-900">{{ $trx->description ?? '-' }}</p>
                                    @if($trx->reference_type && $trx->reference_id)
                                        <p class="text-xs text-gray-500 mt-0.5">{{ strtoupper($trx->reference_type) }} #{{ $trx->reference_id }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $isCredit ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $isCredit ? 'Masuk' : 'Keluar' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-semibold {{ $isCredit ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $isCredit ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-right font-medium text-gray-900">
                                    Rp {{ number_format($trx->balance_after, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($transactions->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $transactions->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>
@endpush
@endsection
