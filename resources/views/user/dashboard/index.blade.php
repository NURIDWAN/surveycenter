@extends('layouts.user')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-description', 'Selamat datang di dashboard SurveyCenter')

@section('content')
<div class="space-y-6">

    {{-- ============================================================ --}}
    {{-- ROW 1: Banner Slider + Quick Actions --}}
    {{-- ============================================================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Banner Slider (Col Span 2) --}}
        <div class="lg:col-span-2">
            @if($banners->isNotEmpty())
            <section x-data="{
                current: 0,
                total: {{ $banners->count() }},
                autoplay: null,
                init() {
                    this.startAutoplay();
                },
                startAutoplay() {
                    this.autoplay = setInterval(() => { this.next(); }, 4500);
                },
                stopAutoplay() {
                    clearInterval(this.autoplay);
                },
                next() {
                    this.current = (this.current + 1) % this.total;
                },
                goTo(index) {
                    this.current = index;
                    this.stopAutoplay();
                    this.startAutoplay();
                }
            }" class="relative w-full overflow-hidden rounded-2xl shadow-sm border border-gray-200/80 bg-white">
                <div class="relative overflow-hidden rounded-2xl">
                    <div class="flex transition-transform duration-700 ease-in-out"
                         :style="'transform: translateX(-' + (current * 100) + '%)'">
                        @foreach($banners as $banner)
                        <div class="relative w-full flex-shrink-0">
                            <img
                                src="{{ asset('storage/' . $banner->image) }}"
                                alt="Dashboard banner"
                                loading="lazy"
                                class="w-full h-56 sm:h-72 lg:h-80 object-cover">
                        </div>
                        @endforeach
                    </div>
                    {{-- Navigation Dots --}}
                    @if($banners->count() > 1)
                    <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-2 z-10">
                        @foreach($banners as $index => $banner)
                        <button @click="goTo({{ $index }})"
                                :class="current === {{ $index }} ? 'bg-orange-500 w-6' : 'bg-white/60 w-2'"
                                class="h-2 rounded-full transition-all duration-300 hover:bg-orange-400"></button>
                        @endforeach
                    </div>
                    @endif
                </div>
            </section>
            @else
            {{-- Fallback Gradient Banner with Owl Mascot --}}
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#fff0e5] via-[#ffecd9] to-[#ffe5cc] border border-orange-100 p-6 sm:p-8 flex items-center justify-between h-56 sm:h-72 lg:h-80 shadow-sm shadow-orange-500/5">
                <div class="absolute top-0 right-0 w-64 h-64 bg-orange-100/20 rounded-full -translate-y-1/2 translate-x-1/4 blur-2xl"></div>
                <div class="relative z-10 max-w-[65%] flex flex-col justify-center h-full">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-orange-500 text-white text-[10px] font-bold uppercase tracking-wider mb-2.5 w-fit shadow-sm shadow-orange-500/10">
                        Promo Spesial
                    </div>
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-gray-950 leading-tight">
                        Nikmati Promo <span class="text-orange-600">Corporate</span>
                    </h1>
                    <p class="mt-1 text-gray-700 text-xs sm:text-sm font-medium">Dapatkan diskon hingga <span class="text-orange-600 font-extrabold text-sm sm:text-base">30%</span> untuk paket corporate!</p>
                    <div class="mt-4 flex items-center gap-2">
                        <a href="{{ route('user.surveys.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-orange-600 text-white rounded-xl font-bold text-xs hover:bg-orange-700 transition shadow-md shadow-orange-600/10">
                            Buat Survey
                        </a>
                        <span class="px-2.5 py-1 text-[10px] font-bold text-orange-600 bg-white rounded-full border border-orange-100">Up To 30%</span>
                    </div>
                </div>
                {{-- Owl Mascot --}}
                <div class="relative w-[30%] sm:w-[25%] lg:w-[22%] flex justify-end items-end h-full">
                    <img src="{{ asset('assets/owl-mascot.png') }}" alt="SurveyCenter Mascot" class="max-h-[90%] object-contain transform hover:scale-105 transition duration-300">
                </div>
            </div>
            @endif
        </div>

        {{-- Right: Quick Actions Card --}}
        <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="text-[14px] font-bold text-gray-900 mb-4 tracking-tight flex items-center gap-2">
                    <i data-lucide="zap" class="w-4.5 h-4.5 text-orange-500"></i>
                    Aksi Cepat
                </h3>
                <div class="space-y-3">
                    {{-- Action 1: Buat Survey Baru --}}
                    <a href="{{ route('user.surveys.create') }}" class="flex items-center justify-between p-3 rounded-xl bg-orange-50/30 border border-orange-100/50 hover:bg-orange-50 transition group">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-orange-100/60 flex items-center justify-center text-orange-600 flex-shrink-0">
                                <i data-lucide="plus-circle" class="w-4.5 h-4.5"></i>
                            </div>
                            <div class="text-left">
                                <p class="text-xs font-bold text-gray-900">Buat Survey Baru</p>
                                <p class="text-[10px] text-gray-500 mt-0.5">Buat survey dari awal</p>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-gray-400 group-hover:text-orange-500 group-hover:translate-x-0.5 transition-all"></i>
                    </a>

                    {{-- Action 2: Lihat Survey Saya --}}
                    <a href="{{ route('user.surveys.index') }}" class="flex items-center justify-between p-3 rounded-xl bg-emerald-50/30 border border-emerald-100/50 hover:bg-emerald-50 transition group">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-emerald-100/60 flex items-center justify-center text-emerald-700 flex-shrink-0">
                                <i data-lucide="clipboard-list" class="w-4.5 h-4.5"></i>
                            </div>
                            <div class="text-left">
                                <p class="text-xs font-bold text-gray-900">Lihat Survey Saya</p>
                                <p class="text-[10px] text-gray-500 mt-0.5">Kelola survey yang Anda buat</p>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-gray-400 group-hover:text-emerald-600 group-hover:translate-x-0.5 transition-all"></i>
                    </a>

                    {{-- Action 3: Laporan & Analytics --}}
                    <a href="{{ route('user.analytics') }}" class="flex items-center justify-between p-3 rounded-xl bg-blue-50/30 border border-blue-100/50 hover:bg-blue-50 transition group">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-blue-100/60 flex items-center justify-center text-blue-600 flex-shrink-0">
                                <i data-lucide="bar-chart-2" class="w-4.5 h-4.5"></i>
                            </div>
                            <div class="text-left">
                                <p class="text-xs font-bold text-gray-900">Laporan & Analytics</p>
                                <p class="text-[10px] text-gray-500 mt-0.5">Lihat laporan dan analitik</p>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-gray-400 group-hover:text-blue-500 group-hover:translate-x-0.5 transition-all"></i>
                    </a>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-2xs text-gray-400 font-medium">
                <span>Diperbarui secara real-time</span>
                <i data-lucide="refresh-cw" class="w-3 h-3 text-gray-300"></i>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- ROW 2: Statistics Cards with Sparklines --}}
    {{-- ============================================================ --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
        {{-- Card 1: Survey Dibuat --}}
        <div class="relative overflow-hidden bg-white rounded-2xl border border-gray-200/80 p-5 hover:shadow-md transition duration-200">
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 rounded-xl bg-orange-50 flex items-center justify-center border border-orange-100">
                    <i data-lucide="clipboard-list" class="w-4.5 h-4.5 text-orange-600"></i>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Survey</span>
            </div>
            <p class="text-2xl font-black text-gray-900 leading-tight">{{ $totalSurveys }}</p>
            <p class="text-2xs font-semibold text-gray-400 uppercase tracking-wider mt-0.5">Survey Dibuat</p>
            <div class="mt-2 text-orange-500">
                @php
                    $sparkData = $sparkSurveys;
                    $max = max($sparkData) ?: 1;
                    $width = 60;
                    $height = 20;
                    $points = collect($sparkData)->map(function ($val, $i) use ($max, $width, $height) {
                        $x = ($i / 6) * $width;
                        $y = $height - (($val / $max) * $height);
                        return "$x,$y";
                    })->implode(' ');
                @endphp
                <svg width="{{ $width }}" height="{{ $height }}" class="inline-block">
                    <polyline points="{{ $points }}" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
        </div>

        {{-- Card 2: Pertanyaan --}}
        <div class="relative overflow-hidden bg-white rounded-2xl border border-gray-200/80 p-5 hover:shadow-md transition duration-200">
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center border border-blue-100">
                    <i data-lucide="help-circle" class="w-4.5 h-4.5 text-blue-600"></i>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Pertanyaan</span>
            </div>
            <p class="text-2xl font-black text-gray-900 leading-tight">{{ $totalQuestions }}</p>
            <p class="text-2xs font-semibold text-gray-400 uppercase tracking-wider mt-0.5">Pertanyaan</p>
            <div class="mt-2 text-blue-500">
                @php
                    $sparkData = $sparkQuestions;
                    $max = max($sparkData) ?: 1;
                    $width = 60;
                    $height = 20;
                    $points = collect($sparkData)->map(function ($val, $i) use ($max, $width, $height) {
                        $x = ($i / 6) * $width;
                        $y = $height - (($val / $max) * $height);
                        return "$x,$y";
                    })->implode(' ');
                @endphp
                <svg width="{{ $width }}" height="{{ $height }}" class="inline-block">
                    <polyline points="{{ $points }}" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
        </div>

        {{-- Card 3: Target Responden --}}
        <div class="relative overflow-hidden bg-white rounded-2xl border border-gray-200/80 p-5 hover:shadow-md transition duration-200">
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center border border-emerald-100">
                    <i data-lucide="users" class="w-4.5 h-4.5 text-emerald-600"></i>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Target</span>
            </div>
            <p class="text-2xl font-black text-gray-900 leading-tight">{{ number_format($totalTargetResponden, 0, ',', '.') }}</p>
            <p class="text-2xs font-semibold text-gray-400 uppercase tracking-wider mt-0.5">Target Responden</p>
            <div class="mt-2 text-emerald-500">
                @php
                    $sparkData = $sparkTargetResponden;
                    $max = max($sparkData) ?: 1;
                    $width = 60;
                    $height = 20;
                    $points = collect($sparkData)->map(function ($val, $i) use ($max, $width, $height) {
                        $x = ($i / 6) * $width;
                        $y = $height - (($val / $max) * $height);
                        return "$x,$y";
                    })->implode(' ');
                @endphp
                <svg width="{{ $width }}" height="{{ $height }}" class="inline-block">
                    <polyline points="{{ $points }}" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
        </div>

        {{-- Card 4: Responden Diperoleh --}}
        <div class="relative overflow-hidden bg-white rounded-2xl border border-gray-200/80 p-5 hover:shadow-md transition duration-200">
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 rounded-xl bg-cyan-50 flex items-center justify-center border border-cyan-100">
                    <i data-lucide="user-check" class="w-4.5 h-4.5 text-cyan-600"></i>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Respon</span>
            </div>
            <p class="text-2xl font-black text-gray-900 leading-tight">{{ number_format($totalRespondenDiperoleh, 0, ',', '.') }}</p>
            <p class="text-2xs font-semibold text-gray-400 uppercase tracking-wider mt-0.5">Responden Diperoleh</p>
            <div class="mt-2 text-cyan-500">
                @php
                    $sparkData = $sparkRespondenDiperoleh;
                    $max = max($sparkData) ?: 1;
                    $width = 60;
                    $height = 20;
                    $points = collect($sparkData)->map(function ($val, $i) use ($max, $width, $height) {
                        $x = ($i / 6) * $width;
                        $y = $height - (($val / $max) * $height);
                        return "$x,$y";
                    })->implode(' ');
                @endphp
                <svg width="{{ $width }}" height="{{ $height }}" class="inline-block">
                    <polyline points="{{ $points }}" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
        </div>

        {{-- Card 5: Total Transaksi --}}
        <div class="relative overflow-hidden bg-white rounded-2xl border border-gray-200/80 p-5 hover:shadow-md transition duration-200 col-span-2 md:col-span-1">
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 rounded-xl bg-purple-50 flex items-center justify-center border border-purple-100">
                    <i data-lucide="wallet" class="w-4.5 h-4.5 text-purple-600"></i>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Transaksi</span>
            </div>
            <p class="text-xl sm:text-2xl font-black text-gray-900 leading-tight">Rp {{ number_format($totalSpent, 0, ',', '.') }}</p>
            <p class="text-2xs font-semibold text-gray-400 uppercase tracking-wider mt-0.5">Total Transaksi</p>
            <div class="mt-2 text-purple-500">
                @php
                    $sparkData = $sparkTransactions;
                    $max = max($sparkData) ?: 1;
                    $width = 60;
                    $height = 20;
                    $points = collect($sparkData)->map(function ($val, $i) use ($max, $width, $height) {
                        $x = ($i / 6) * $width;
                        $y = $height - (($val / $max) * $height);
                        return "$x,$y";
                    })->implode(' ');
                @endphp
                <svg width="{{ $width }}" height="{{ $height }}" class="inline-block">
                    <polyline points="{{ $points }}" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- ROW 3: Three-Column Bottom Section --}}
    {{-- ============================================================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Column 1: Performance Survey --}}
        <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-[14px] font-bold text-gray-900 tracking-tight flex items-center gap-2">
                        <i data-lucide="activity" class="w-4.5 h-4.5 text-orange-500"></i>
                        Performa Survey
                    </h3>
                    <span class="text-[10px] font-semibold text-gray-400 bg-gray-50 px-2 py-1 rounded-lg border border-gray-100">7 Hari Terakhir</span>
                </div>

                {{-- Metrics Grid --}}
                <div class="grid grid-cols-2 gap-3 mb-5">
                    <div class="bg-gray-50/50 border border-gray-100 rounded-xl p-3">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Response Rate</p>
                        <p class="text-base font-extrabold text-gray-900 mt-0.5">{{ $responseRate }}%</p>
                    </div>
                    <div class="bg-gray-50/50 border border-gray-100 rounded-xl p-3">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Completion Rate</p>
                        <p class="text-base font-extrabold text-gray-900 mt-0.5">{{ $completionRate }}%</p>
                    </div>
                    <div class="bg-gray-50/50 border border-gray-100 rounded-xl p-3">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Survey Aktif</p>
                        <p class="text-base font-extrabold text-orange-600 mt-0.5">{{ $activeSurveys }}</p>
                    </div>
                    <div class="bg-gray-50/50 border border-gray-100 rounded-xl p-3">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Avg. Waktu</p>
                        <p class="text-base font-extrabold text-gray-900 mt-0.5">{{ round($avgCompletionDays) }} menit</p>
                    </div>
                </div>

                {{-- Chart.js Line Chart --}}
                <div class="relative w-full h-36 bg-gray-50/30 rounded-xl border border-gray-100 p-2">
                    <canvas id="performanceChart" class="w-full h-full"></canvas>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center gap-2 text-2xs text-gray-500 font-medium">
                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-500"></i>
                <span>Data performa diperbarui secara real-time</span>
            </div>
        </div>

        {{-- Column 2: Survey Terbaru --}}
        <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-[14px] font-bold text-gray-900 tracking-tight flex items-center gap-2">
                        <i data-lucide="file-text" class="w-4.5 h-4.5 text-orange-500"></i>
                        Survey Terbaru
                    </h3>
                    <a href="{{ route('user.surveys.index') }}" class="text-2xs font-bold text-orange-600 hover:text-orange-700">Lihat Semua</a>
                </div>

                {{-- Survey List --}}
                <div class="space-y-3">
                    @forelse($recentSurveys->take(5) as $survey)
                        <a href="{{ route('user.surveys.show', $survey) }}" class="block p-3 rounded-xl border border-gray-100 bg-gray-50/30 hover:bg-gray-50 transition">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs font-bold text-gray-900 truncate block max-w-[70%]">{{ $survey->title }}</span>
                                        @if($survey->status === 'active')
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">Aktif</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-gray-100 text-gray-500 border border-gray-200">Draft</span>
                                        @endif
                                    </div>
                                    <p class="text-[10px] text-gray-400">{{ $survey->admin_responses_sum_respond_count ?? 0 }} responden &bull; {{ $survey->created_at->translatedFormat('d M Y') }}</p>
                                </div>
                                <div class="w-7 h-7 rounded-lg bg-white border border-gray-100 flex items-center justify-center text-gray-400 flex-shrink-0">
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="py-8 text-center bg-gray-50/50 rounded-xl border border-gray-100">
                            <i data-lucide="clipboard-list" class="w-8 h-8 mx-auto mb-2 text-gray-300"></i>
                            <p class="text-xs text-gray-400 mb-2">Belum ada survey dibuat</p>
                            <a href="{{ route('user.surveys.create') }}" class="inline-flex items-center gap-1 text-2xs font-bold text-orange-600 hover:text-orange-700">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                Buat Survey Pertama
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="mt-5">
                <a href="{{ route('user.surveys.index') }}" class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl border border-orange-200 bg-orange-50/50 text-orange-700 hover:bg-orange-50 text-xs font-bold transition">
                    Kelola Semua Survey
                </a>
            </div>
        </div>

        {{-- Column 3: Reward & Poin --}}
        <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="text-[14px] font-bold text-gray-900 tracking-tight flex items-center gap-2 mb-4">
                    <i data-lucide="gift" class="w-4.5 h-4.5 text-orange-500"></i>
                    Reward & Poin
                </h3>

                {{-- Points Display Widget --}}
                <div class="relative overflow-hidden rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-white p-4 mb-4 shadow-md shadow-orange-500/10">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/4 blur-lg"></div>
                    <div class="flex items-center justify-between relative z-10">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-orange-100">Poin Anda</p>
                            <p class="text-2xl font-black mt-0.5">{{ number_format($user->point_balance ?? 0, 0, ',', '.') }} <span class="text-xs font-semibold text-orange-100">poin</span></p>
                            <p class="text-[9px] text-orange-100 mt-1">Tukarkan poin dengan berbagai hadiah menarik!</p>
                        </div>
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center backdrop-blur shadow-inner">
                            <i data-lucide="gift" class="w-6 h-6 text-white"></i>
                        </div>
                    </div>
                </div>

                {{-- How to earn points --}}
                <div class="bg-gray-50/50 border border-gray-100 rounded-xl p-3.5 space-y-2.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Cara mendapatkan poin:</p>

                    <div class="flex items-start gap-2.5">
                        <div class="w-5 h-5 rounded-full bg-orange-50 flex items-center justify-center flex-shrink-0 text-orange-600 mt-0.5">
                            <i data-lucide="star" class="w-3 h-3 fill-orange-500 text-orange-500"></i>
                        </div>
                        <div class="text-left">
                            <p class="text-2xs font-bold text-gray-800">1 Respon = 1 Poin</p>
                            <p class="text-[9px] text-gray-400">Poin otomatis masuk dari responden survey</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-2.5">
                        <div class="w-5 h-5 rounded-full bg-emerald-50 flex items-center justify-center flex-shrink-0 text-emerald-600 mt-0.5">
                            <i data-lucide="users" class="w-3 h-3 text-emerald-600"></i>
                        </div>
                        <div class="text-left">
                            <p class="text-2xs font-bold text-gray-800">Undang teman = 10 Poin</p>
                            <p class="text-[9px] text-gray-400">Dapatkan poin dari program afiliasi Anda</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-2.5">
                        <div class="w-5 h-5 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0 text-blue-600 mt-0.5">
                            <i data-lucide="calendar" class="w-3 h-3 text-blue-600"></i>
                        </div>
                        <div class="text-left">
                            <p class="text-2xs font-bold text-gray-800">Survey harian = 5 Poin</p>
                            <p class="text-[9px] text-gray-400">Dapatkan bonus poin dari survey harian</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ route('user.rewards.index') }}" class="flex items-center justify-center gap-1.5 w-full py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold transition shadow-md shadow-orange-500/10">
                    Lihat Reward
                </a>
            </div>
        </div>

    </div>

    {{-- ============================================================ --}}
    {{-- ROW 4: Footer Security Bar --}}
    {{-- ============================================================ --}}
    <div class="flex flex-col sm:flex-row items-center justify-between px-6 py-4 bg-white rounded-2xl border border-gray-200/80 shadow-sm gap-3">
        <div class="flex items-center gap-2 text-sm text-gray-600">
            <i data-lucide="shield-check" class="w-4.5 h-4.5 text-emerald-500"></i>
            <div>
                <span class="font-semibold text-gray-800">Keamanan Terjamin</span>
                <p class="text-[10px] text-gray-400">Data Anda dilindungi dengan enkripsi standar industri</p>
            </div>
        </div>
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <span>Butuh bantuan?</span>
            <a href="{{ \Illuminate\Support\Facades\Route::has('contact') ? route('contact') : '#' }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-orange-50 text-orange-600 font-semibold text-xs hover:bg-orange-100 transition border border-orange-100">
                Hubungi Support
            </a>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('performanceChart');
    if (ctx) {
        new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [
                    {
                        label: 'Responden',
                        data: @json($chartRespondents),
                        borderColor: '#f97316',
                        backgroundColor: 'rgba(249, 115, 22, 0.08)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointBackgroundColor: '#f97316',
                    },
                    {
                        label: 'Target',
                        data: @json($chartTargets),
                        borderColor: '#94a3b8',
                        borderDash: [5, 5],
                        fill: false,
                        tension: 0.4,
                        borderWidth: 1.5,
                        pointRadius: 0,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 8,
                            boxHeight: 8,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: { size: 10 }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { size: 9 }, color: '#94a3b8' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 9 }, color: '#94a3b8' }
                    }
                }
            }
        });
    }
});
</script>
@endpush
