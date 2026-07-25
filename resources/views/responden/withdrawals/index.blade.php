@extends('layouts.responden')

@section('page-title', 'Riwayat Penarikan')
@section('page-description', 'Riwayat penarikan saldo Anda')

@section('content')

<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h2 class="text-sm font-bold text-gray-900">Riwayat Penarikan</h2>
            <p class="text-xs text-gray-400 mt-0.5">Semua permintaan penarikan saldo Anda</p>
        </div>
        <a href="{{ route('responden.withdrawals.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-sky-50 border border-sky-100 text-xs font-semibold text-sky-600 hover:bg-sky-100 hover:text-sky-700 transition">
            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
            Tarik Saldo
        </a>
    </div>

    @if($withdrawals->isEmpty())
        <div class="px-6 py-12 text-center">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center mb-4">
                <i data-lucide="banknote" class="w-7 h-7 text-gray-300"></i>
            </div>
            <p class="text-sm font-medium text-gray-500">Belum ada penarikan</p>
            <p class="text-xs text-gray-400 mt-1">Ajukan penarikan saldo untuk melihat riwayat di sini</p>
        </div>
    @else
        {{-- Desktop Table --}}
        <div class="overflow-x-auto hidden md:block">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-400">Jumlah</th>
                        <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-400">Provider</th>
                        <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-400">No. Rekening</th>
                        <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-400">Status</th>
                        <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-400">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($withdrawals as $withdrawal)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            {{-- Amount --}}
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold text-gray-900">{{ \App\Helpers\RupiahHelper::formatRupiah($withdrawal->amount) }}</span>
                            </td>

                            {{-- Provider --}}
                            <td class="px-6 py-4">
                                <span class="text-sm font-medium text-gray-700">{{ $withdrawal->provider_name }}</span>
                            </td>

                            {{-- Account Number --}}
                            <td class="px-6 py-4">
                                <span class="text-xs text-gray-500 font-medium">{{ $withdrawal->account_number }}</span>
                            </td>

                            {{-- Status Badge --}}
                            <td class="px-6 py-4">
                                @switch($withdrawal->status)
                                    @case('pending')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-50 border border-amber-100 text-xs font-bold text-amber-700">
                                            <i data-lucide="clock" class="w-3 h-3"></i>
                                            Pending
                                        </span>
                                        @break
                                    @case('approved')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-100 text-xs font-bold text-emerald-700">
                                            <i data-lucide="check-circle" class="w-3 h-3"></i>
                                            Disetujui
                                        </span>
                                        @break
                                    @case('rejected')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-red-50 border border-red-100 text-xs font-bold text-red-700">
                                            <i data-lucide="x-circle" class="w-3 h-3"></i>
                                            Ditolak
                                        </span>
                                        @break
                                @endswitch
                            </td>

                            {{-- Date --}}
                            <td class="px-6 py-4">
                                <span class="text-xs text-gray-500 font-medium">{{ $withdrawal->created_at->format('d M Y, H:i') }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile Card View --}}
        <div class="md:hidden divide-y divide-gray-50">
            @foreach($withdrawals as $withdrawal)
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-bold text-gray-900">{{ \App\Helpers\RupiahHelper::formatRupiah($withdrawal->amount) }}</span>
                        @switch($withdrawal->status)
                            @case('pending')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-50 border border-amber-100 text-xs font-bold text-amber-700">
                                    <i data-lucide="clock" class="w-3 h-3"></i>
                                    Pending
                                </span>
                                @break
                            @case('approved')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-100 text-xs font-bold text-emerald-700">
                                    <i data-lucide="check-circle" class="w-3 h-3"></i>
                                    Disetujui
                                </span>
                                @break
                            @case('rejected')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-red-50 border border-red-100 text-xs font-bold text-red-700">
                                    <i data-lucide="x-circle" class="w-3 h-3"></i>
                                    Ditolak
                                </span>
                                @break
                        @endswitch
                    </div>
                    <div class="flex items-center gap-3 text-xs text-gray-500">
                        <span class="font-medium">{{ $withdrawal->provider_name }}</span>
                        <span class="text-gray-300">•</span>
                        <span>{{ $withdrawal->account_number }}</span>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1">{{ $withdrawal->created_at->format('d M Y, H:i') }}</p>
                </div>
            @endforeach
        </div>
    @endif
</div>

@endsection
