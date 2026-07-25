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
            <div class="px-6 py-5 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900">{{ $survey->title }}</h2>
            </div>

            <div class="px-6 py-5 space-y-5">
                {{-- Description --}}
                @if($survey->description)
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Deskripsi</h3>
                        <div class="text-sm text-gray-600 leading-relaxed prose prose-sm max-w-none">
                            {!! nl2br(e($survey->description)) !!}
                        </div>
                    </div>
                @endif

                {{-- Eligibility Criteria --}}
                @if($survey->eligibility_criteria && count(array_filter($survey->eligibility_criteria)))
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Kriteria Responden</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @php $criteria = $survey->eligibility_criteria; @endphp

                            @if(!empty($criteria['jenis_kelamin']))
                                <div class="flex items-start gap-2.5 px-3 py-2.5 rounded-xl bg-gray-50 border border-gray-100">
                                    <i data-lucide="users" class="w-4 h-4 text-gray-400 flex-shrink-0 mt-0.5"></i>
                                    <div>
                                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Jenis Kelamin</p>
                                        <p class="text-xs font-medium text-gray-700 mt-0.5">
                                            {{ is_array($criteria['jenis_kelamin']) ? implode(', ', $criteria['jenis_kelamin']) : $criteria['jenis_kelamin'] }}
                                        </p>
                                    </div>
                                </div>
                            @endif

                            @if(!empty($criteria['age_min']) || !empty($criteria['age_max']))
                                <div class="flex items-start gap-2.5 px-3 py-2.5 rounded-xl bg-gray-50 border border-gray-100">
                                    <i data-lucide="calendar" class="w-4 h-4 text-gray-400 flex-shrink-0 mt-0.5"></i>
                                    <div>
                                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Usia</p>
                                        <p class="text-xs font-medium text-gray-700 mt-0.5">
                                            @if(!empty($criteria['age_min']) && !empty($criteria['age_max']))
                                                {{ $criteria['age_min'] }} - {{ $criteria['age_max'] }} tahun
                                            @elseif(!empty($criteria['age_min']))
                                                Minimal {{ $criteria['age_min'] }} tahun
                                            @else
                                                Maksimal {{ $criteria['age_max'] }} tahun
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            @endif

                            @if(!empty($criteria['provinsi']))
                                <div class="flex items-start gap-2.5 px-3 py-2.5 rounded-xl bg-gray-50 border border-gray-100">
                                    <i data-lucide="map-pin" class="w-4 h-4 text-gray-400 flex-shrink-0 mt-0.5"></i>
                                    <div>
                                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Provinsi</p>
                                        <p class="text-xs font-medium text-gray-700 mt-0.5">
                                            {{ is_array($criteria['provinsi']) ? implode(', ', $criteria['provinsi']) : $criteria['provinsi'] }}
                                        </p>
                                    </div>
                                </div>
                            @endif

                            @if(!empty($criteria['kota']))
                                <div class="flex items-start gap-2.5 px-3 py-2.5 rounded-xl bg-gray-50 border border-gray-100">
                                    <i data-lucide="building" class="w-4 h-4 text-gray-400 flex-shrink-0 mt-0.5"></i>
                                    <div>
                                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Kota</p>
                                        <p class="text-xs font-medium text-gray-700 mt-0.5">
                                            {{ is_array($criteria['kota']) ? implode(', ', $criteria['kota']) : $criteria['kota'] }}
                                        </p>
                                    </div>
                                </div>
                            @endif

                            @if(!empty($criteria['pendidikan']))
                                <div class="flex items-start gap-2.5 px-3 py-2.5 rounded-xl bg-gray-50 border border-gray-100">
                                    <i data-lucide="graduation-cap" class="w-4 h-4 text-gray-400 flex-shrink-0 mt-0.5"></i>
                                    <div>
                                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Pendidikan</p>
                                        <p class="text-xs font-medium text-gray-700 mt-0.5">
                                            {{ is_array($criteria['pendidikan']) ? implode(', ', $criteria['pendidikan']) : $criteria['pendidikan'] }}
                                        </p>
                                    </div>
                                </div>
                            @endif

                            @if(!empty($criteria['pekerjaan']))
                                <div class="flex items-start gap-2.5 px-3 py-2.5 rounded-xl bg-gray-50 border border-gray-100">
                                    <i data-lucide="briefcase" class="w-4 h-4 text-gray-400 flex-shrink-0 mt-0.5"></i>
                                    <div>
                                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Pekerjaan</p>
                                        <p class="text-xs font-medium text-gray-700 mt-0.5">
                                            {{ is_array($criteria['pekerjaan']) ? implode(', ', $criteria['pekerjaan']) : $criteria['pekerjaan'] }}
                                        </p>
                                    </div>
                                </div>
                            @endif
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
                    <div class="w-9 h-9 rounded-xl bg-sky-50 border border-sky-100 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="clock" class="w-4.5 h-4.5 text-sky-500"></i>
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
                @if($isEligible)
                    <form method="POST" action="{{ route('responden.surveys.start', $survey) }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-sky-500 hover:bg-sky-600 text-white font-bold text-sm shadow-md shadow-sky-500/20 hover:shadow-lg hover:shadow-sky-500/30 hover:scale-[1.02] transition-all">
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
                    <div class="text-center">
                        <div class="w-12 h-12 mx-auto rounded-2xl bg-red-50 border border-red-100 flex items-center justify-center mb-3">
                            <i data-lucide="shield-x" class="w-6 h-6 text-red-400"></i>
                        </div>
                        <p class="text-sm font-semibold text-gray-700 mb-1">Tidak Memenuhi Kriteria</p>
                        <p class="text-xs text-gray-400 leading-relaxed">
                            Profil demografis Anda belum sesuai dengan kriteria yang dibutuhkan survey ini. Pastikan profil Anda sudah lengkap dan sesuai.
                        </p>
                        <a href="{{ route('responden.profile.edit') }}" class="inline-flex items-center gap-1.5 mt-4 px-4 py-2 rounded-xl bg-gray-50 border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-100 transition">
                            <i data-lucide="user-circle" class="w-3.5 h-3.5"></i>
                            Perbarui Profil
                        </a>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

@endsection
