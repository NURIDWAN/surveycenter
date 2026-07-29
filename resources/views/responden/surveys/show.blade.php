@extends('layouts.responden')

@section('page-title', $survey->title)
@section('page-description', 'Detail survey')

@section('content')

{{-- Back Button --}}
<div class="mb-6">
    <a href="{{ route('responden.surveys.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 transition font-medium">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        Kembali ke Daftar Survey
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Main Content --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Survey Info Card --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900">{{ $survey->title }}</h2>
                @if($survey->status === \App\Models\Survey::STATUS_ACTIVE)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-xs font-bold text-emerald-600">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Aktif
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-gray-100 text-xs font-bold text-gray-500">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                        Nonaktif
                    </span>
                @endif
            </div>

            <div class="px-6 py-5 space-y-5">
                {{-- Description --}}
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Deskripsi Survey</h3>
                    <div class="text-sm text-gray-600 leading-relaxed prose prose-sm max-w-none">
                        @if(!empty($survey->description))
                            {!! nl2br(e($survey->description)) !!}
                        @elseif(!empty($survey->notes_for_respondent))
                            {!! nl2br(e($survey->notes_for_respondent)) !!}
                        @else
                            <p class="text-gray-500 italic">Silakan baca dan isi pertanyaan pada survey ini dengan seksama sampai selesai.</p>
                        @endif
                    </div>
                </div>

                {{-- Notes from Admin --}}
                @if(\Illuminate\Support\Facades\Schema::hasColumn('surveys', 'notes_for_respondent') && $survey->notes_for_respondent)
                    <div class="rounded-xl bg-amber-50 border border-amber-100 px-4 py-3 flex items-start gap-3">
                        <i data-lucide="info" class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5"></i>
                        <div>
                            <p class="text-xs font-bold text-amber-700 uppercase tracking-wider mb-1">Catatan dari Peneliti</p>
                            <p class="text-sm text-amber-800 leading-relaxed">{{ $survey->notes_for_respondent }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">

        {{-- Survey Details Card --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-bold text-gray-900">Informasi Survey</h3>
            </div>
            <div class="px-5 py-4 space-y-4">
                {{-- Reward --}}
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="banknote" class="w-4.5 h-4.5 text-emerald-500"></i>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Reward</p>
                        <p class="text-sm font-bold text-emerald-700">{{ \App\Helpers\RupiahHelper::formatRupiah($survey->reward_amount) }}</p>
                    </div>
                </div>

                {{-- Estimated Time --}}
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-orange-50 border border-orange-100 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="clock" class="w-4.5 h-4.5 text-orange-500"></i>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Estimasi Waktu</p>
                        <p class="text-sm font-medium text-gray-700">
                            @if($survey->estimated_time_minutes)
                                {{ $survey->estimated_time_minutes }} menit
                            @else
                                <span class="text-gray-400">Tidak ditentukan</span>
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Deadline --}}
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="calendar" class="w-4.5 h-4.5 text-amber-500"></i>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Deadline</p>
                        <p class="text-sm font-medium text-gray-700">
                            @if($survey->deadline)
                                {{ $survey->deadline->format('d M Y, H:i') }}
                                @if($survey->deadline->isPast())
                                    <span class="text-xs text-red-500 font-semibold">(Berakhir)</span>
                                @elseif($survey->deadline->diffInDays(now()) <= 3)
                                    <span class="text-xs text-amber-500 font-semibold">(Segera berakhir)</span>
                                @endif
                            @else
                                <span class="text-gray-400">Tidak ada deadline</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Action Card --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-5">
                @if($survey->status === \App\Models\Survey::STATUS_ACTIVE && $isEligible)
                    <form method="POST" action="{{ route('responden.surveys.start', $survey) }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm shadow-md shadow-orange-500/20 hover:shadow-lg hover:shadow-orange-500/30 hover:scale-[1.02] transition-all">
                            <i data-lucide="external-link" class="w-4.5 h-4.5"></i>
                            Mulai Survey
                        </button>
                    </form>

                    @if($survey->form_link)
                        <a href="{{ $survey->form_link }}" target="_blank" rel="noopener noreferrer" class="mt-3 w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200 text-gray-600 hover:bg-gray-100 hover:text-gray-700 text-xs font-semibold transition">
                            <i data-lucide="link" class="w-3.5 h-3.5"></i>
                            Buka Google Form
                        </a>
                    @endif

                    <p class="text-[11px] text-gray-400 text-center mt-3">
                        Klik "Mulai Survey" untuk memulai pengisian. Anda akan diarahkan ke Google Form.
                    </p>
                @else
                    <div class="text-center py-2">
                        <div class="w-12 h-12 mx-auto rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center mb-3">
                            <i data-lucide="alert-circle" class="w-6 h-6 text-amber-500"></i>
                        </div>
                        <p class="text-sm font-semibold text-gray-700 mb-1">Survey Tidak Aktif</p>
                        <p class="text-xs text-gray-400 leading-relaxed">
                            Survey ini sedang tidak aktif atau tidak menerima pengisian baru saat ini.
                        </p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

@endsection
