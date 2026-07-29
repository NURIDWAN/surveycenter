@extends('layouts.responden')

@section('page-title', 'Pengisian Saya')
@section('page-description', 'Riwayat pengisian survey Anda')

@section('content')

<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h2 class="text-sm font-bold text-gray-900">Riwayat Pengisian</h2>
            <p class="text-xs text-gray-400 mt-0.5">Semua survey yang telah Anda kerjakan</p>
        </div>
        <a href="{{ route('responden.surveys.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-50 border border-orange-100 text-xs font-semibold text-orange-600 hover:bg-orange-100 hover:text-orange-700 transition">
            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
            Cari Survey
        </a>
    </div>

    @if($fillings->isEmpty())
        <div class="px-6 py-12 text-center">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center mb-4">
                <i data-lucide="file-check" class="w-7 h-7 text-gray-300"></i>
            </div>
            <p class="text-sm font-medium text-gray-500">Belum ada pengisian</p>
            <p class="text-xs text-gray-400 mt-1">Mulai kerjakan survey untuk melihat riwayat pengisian Anda</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-400">Survey</th>
                        <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-400">Tanggal</th>
                        <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-400">Status</th>
                        <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-400 hidden md:table-cell">Keterangan</th>
                        <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($fillings as $filling)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            {{-- Survey Name --}}
                            <td class="px-6 py-4">
                                <p class="text-sm font-semibold text-gray-900 leading-tight">{{ $filling->survey->title ?? '—' }}</p>
                            </td>

                            {{-- Date --}}
                            <td class="px-6 py-4">
                                <span class="text-xs text-gray-500 font-medium">{{ $filling->created_at->format('d M Y, H:i') }}</span>
                            </td>

                            {{-- Status Badge --}}
                            <td class="px-6 py-4">
                                @switch($filling->status)
                                    @case(\App\Models\SurveyFilling::STATUS_SEDANG_DIKERJAKAN)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 border border-blue-100 text-xs font-bold text-blue-700">
                                            <i data-lucide="edit" class="w-3 h-3"></i>
                                            Sedang Dikerjakan
                                        </span>
                                        @break
                                    @case(\App\Models\SurveyFilling::STATUS_MENUNGGU_VERIFIKASI)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-50 border border-amber-100 text-xs font-bold text-amber-700">
                                            <i data-lucide="clock" class="w-3 h-3"></i>
                                            Menunggu Verifikasi
                                        </span>
                                        @break
                                    @case(\App\Models\SurveyFilling::STATUS_DISETUJUI)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-100 text-xs font-bold text-emerald-700">
                                            <i data-lucide="check-circle" class="w-3 h-3"></i>
                                            Disetujui
                                        </span>
                                        @break
                                    @case(\App\Models\SurveyFilling::STATUS_DITOLAK)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-red-50 border border-red-100 text-xs font-bold text-red-700">
                                            <i data-lucide="x-circle" class="w-3 h-3"></i>
                                            Ditolak
                                        </span>
                                        @break
                                @endswitch
                            </td>

                            {{-- Keterangan (rejection reason / reward) --}}
                            <td class="px-6 py-4 hidden md:table-cell">
                                @if($filling->status === \App\Models\SurveyFilling::STATUS_DITOLAK)
                                    <div class="max-w-xs">
                                        <p class="text-xs font-medium text-red-600">{{ $filling->rejectionReason->label ?? '—' }}</p>
                                        @if($filling->rejection_notes)
                                            <p class="text-[11px] text-gray-400 mt-0.5 truncate">{{ $filling->rejection_notes }}</p>
                                        @endif
                                    </div>
                                @elseif($filling->status === \App\Models\SurveyFilling::STATUS_DISETUJUI)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-100 text-xs font-bold text-emerald-700">
                                        +{{ \App\Helpers\RupiahHelper::formatRupiah($filling->survey->reward_amount ?? 0) }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-6 py-4">
                                @if($filling->status === \App\Models\SurveyFilling::STATUS_SEDANG_DIKERJAKAN)
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <a href="{{ route('responden.fillings.upload', $filling) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-50 border border-orange-100 text-xs font-semibold text-orange-600 hover:bg-orange-100 hover:text-orange-700 transition">
                                            <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                            Upload Bukti
                                        </a>
                                        @if($filling->survey->form_link ?? null)
                                            <a href="{{ $filling->survey->form_link }}" target="_blank" rel="noopener noreferrer"
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 border border-emerald-100 text-xs font-semibold text-emerald-600 hover:bg-emerald-100 hover:text-emerald-700 transition">
                                                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                                Buka Form
                                            </a>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- Mobile card view for smaller screens showing keterangan --}}
<div class="mt-4 space-y-3 md:hidden">
    @foreach($fillings as $filling)
        @if($filling->status === \App\Models\SurveyFilling::STATUS_DITOLAK || $filling->status === \App\Models\SurveyFilling::STATUS_DISETUJUI)
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-xs font-semibold text-gray-900">{{ $filling->survey->title ?? '—' }}</p>
                @if($filling->status === \App\Models\SurveyFilling::STATUS_DITOLAK)
                    <div class="mt-2 px-3 py-2 rounded-lg bg-red-50 border border-red-100">
                        <p class="text-xs font-medium text-red-600">Alasan: {{ $filling->rejectionReason->label ?? '—' }}</p>
                        @if($filling->rejection_notes)
                            <p class="text-[11px] text-red-500 mt-0.5">{{ $filling->rejection_notes }}</p>
                        @endif
                    </div>
                @elseif($filling->status === \App\Models\SurveyFilling::STATUS_DISETUJUI)
                    <div class="mt-2 px-3 py-2 rounded-lg bg-emerald-50 border border-emerald-100">
                        <p class="text-xs font-bold text-emerald-700">Reward: +{{ \App\Helpers\RupiahHelper::formatRupiah($filling->survey->reward_amount ?? 0) }}</p>
                    </div>
                @endif
            </div>
        @endif
    @endforeach
</div>

@endsection
