@extends('layouts.responden')

@section('page-title', 'Dashboard')
@section('page-description', 'Ringkasan aktivitas dan survey tersedia')

@section('content')

{{-- Profile Completion Banner (optional/dismissable) --}}
@if(!$profileComplete)
    <div class="mb-6 flex items-center gap-4 px-5 py-4 rounded-2xl bg-orange-50 shadow-sm" x-data="{ show: true }" x-show="show" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center flex-shrink-0">
            <i data-lucide="user-circle" class="w-5 h-5 text-orange-600"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-900">Lengkapi Profil Anda <span class="text-xs font-normal text-gray-400">(opsional)</span></p>
            <p class="text-xs text-gray-500 mt-0.5">Lengkapi data demografis agar sistem dapat mencocokkan Anda dengan survey yang relevan.</p>
        </div>
        <a href="{{ route('responden.profile.edit') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold shadow-sm hover:shadow-md transition-all flex-shrink-0">
            <i data-lucide="edit" class="w-3.5 h-3.5"></i>
            Lengkapi
        </a>
        <button @click="show = false" class="p-1 rounded-lg hover:bg-orange-100 transition flex-shrink-0" title="Tutup">
            <i data-lucide="x" class="w-4 h-4 text-orange-400"></i>
        </button>
    </div>
@endif

{{-- Statistics Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

    {{-- Saldo Card --}}
    <div class="relative overflow-hidden bg-white rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow">
        <div class="absolute top-0 right-0 w-20 h-20 bg-orange-50 rounded-full -translate-y-1/2 translate-x-1/4 blur-lg"></div>
        <div class="relative">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center">
                    <i data-lucide="wallet" class="w-5 h-5 text-orange-500"></i>
                </div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Saldo</p>
            </div>
            <p class="text-xl font-extrabold text-gray-900">{{ \App\Helpers\RupiahHelper::formatRupiah($saldo) }}</p>
        </div>
    </div>

    {{-- Survey Tersedia Card --}}
    <div class="relative overflow-hidden bg-white rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow">
        <div class="absolute top-0 right-0 w-20 h-20 bg-emerald-50 rounded-full -translate-y-1/2 translate-x-1/4 blur-lg"></div>
        <div class="relative">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                    <i data-lucide="clipboard-list" class="w-5 h-5 text-emerald-500"></i>
                </div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Survey Tersedia</p>
            </div>
            <p class="text-xl font-extrabold text-gray-900">{{ $surveyTersediaCount }}</p>
        </div>
    </div>

    {{-- Menunggu Verifikasi Card --}}
    <div class="relative overflow-hidden bg-white rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow">
        <div class="absolute top-0 right-0 w-20 h-20 bg-amber-50 rounded-full -translate-y-1/2 translate-x-1/4 blur-lg"></div>
        <div class="relative">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
                    <i data-lucide="clock" class="w-5 h-5 text-amber-500"></i>
                </div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Menunggu Verifikasi</p>
            </div>
            <p class="text-xl font-extrabold text-gray-900">{{ $menungguCount }}</p>
        </div>
    </div>

    {{-- Disetujui Card --}}
    <div class="relative overflow-hidden bg-white rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow">
        <div class="absolute top-0 right-0 w-20 h-20 bg-violet-50 rounded-full -translate-y-1/2 translate-x-1/4 blur-lg"></div>
        <div class="relative">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-5 h-5 text-violet-500"></i>
                </div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Disetujui</p>
            </div>
            <p class="text-xl font-extrabold text-gray-900">{{ $disetujuiCount }}</p>
        </div>
    </div>

</div>

{{-- Available Surveys Section --}}
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 flex items-center justify-between">
        <div>
            <h2 class="text-sm font-bold text-gray-900">Survey Tersedia</h2>
            <p class="text-xs text-gray-400 mt-0.5">Survey yang cocok dengan profil Anda</p>
        </div>
        <a href="{{ route('responden.surveys.index') }}" class="text-xs font-semibold text-orange-600 hover:text-orange-700 transition flex items-center gap-1">
            Lihat Semua
            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
        </a>
    </div>

    @if($availableSurveys->isEmpty())
        <div class="px-6 py-12 text-center">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-gray-50 flex items-center justify-center mb-4">
                <i data-lucide="inbox" class="w-7 h-7 text-gray-300"></i>
            </div>
            <p class="text-sm font-medium text-gray-500">Belum ada survey tersedia</p>
            <p class="text-xs text-gray-400 mt-1">Lengkapi profil demografis Anda untuk mendapatkan lebih banyak survey</p>
            @if(!$profileComplete)
                <a href="{{ route('responden.profile.edit') }}" class="inline-flex items-center gap-1.5 mt-4 px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold shadow-sm transition">
                    <i data-lucide="user-circle" class="w-3.5 h-3.5"></i>
                    Lengkapi Profil
                </a>
            @endif
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-400">Survey</th>
                        <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-400">Reward</th>
                        <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-400 hidden sm:table-cell">Estimasi Waktu</th>
                        <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-400 hidden md:table-cell">Deadline</th>
                        <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($availableSurveys as $survey)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="text-sm font-semibold text-gray-900 leading-tight">{{ $survey->title }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 text-xs font-bold text-emerald-700">
                                    {{ \App\Helpers\RupiahHelper::formatRupiah($survey->reward_amount) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 hidden sm:table-cell">
                                <span class="text-xs text-gray-500 font-medium">
                                    @if($survey->estimated_time_minutes)
                                        {{ $survey->estimated_time_minutes }} menit
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4 hidden md:table-cell">
                                <span class="text-xs text-gray-500 font-medium">
                                    @if($survey->deadline)
                                        {{ $survey->deadline->format('d M Y') }}
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('responden.surveys.show', $survey) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-50 text-xs font-semibold text-orange-600 hover:bg-orange-100 hover:text-orange-700 transition">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection
