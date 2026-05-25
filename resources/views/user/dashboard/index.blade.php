@extends('layouts.user')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-description', 'Selamat datang di dashboard SurveyCenter')

@section('content')
<div class="space-y-6">

    {{-- Welcome Slider / Discount Banner --}}
    @if($banners->isNotEmpty())
    <section class="relative w-full overflow-hidden rounded-2xl shadow-md">
        <div class="relative overflow-hidden rounded-2xl">
            <div id="dashboardSlider" class="flex transition-transform duration-700 ease-in-out">
                @foreach($banners as $banner)
                <div class="relative w-full flex-shrink-0">
                    <img
                        src="{{ asset('storage/' . $banner->image) }}"
                        alt="Dashboard banner"
                        loading="lazy"
                        class="w-full h-40 sm:h-52 lg:h-64 object-cover">
                </div>
                @endforeach
            </div>
            {{-- Dots --}}
            @if($banners->count() > 1)
            <div id="dashboardBannerDots" class="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-2 z-10"></div>
            @endif
        </div>
    </section>
    @else
    {{-- Fallback: Welcome Banner Default --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-orange-500 via-orange-600 to-amber-600 text-white p-6 sm:p-8">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/4 blur-2xl"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-amber-400/20 rounded-full translate-y-1/2 -translate-x-1/4 blur-xl"></div>
        <div class="relative z-10">
            <p class="text-orange-200 text-xs font-medium uppercase tracking-widest mb-2">{{ now()->translatedFormat('l, d F Y') }}</p>
            <h1 class="text-2xl sm:text-3xl font-bold leading-tight">Selamat Datang, {{ $user->name }}!</h1>
            <p class="mt-2 text-orange-100 text-sm max-w-lg">Kelola survey Anda dengan mudah. Buat survey baru dengan harga mulai Rp 350 per soal per responden.</p>
            <a href="{{ route('user.surveys.create') }}" class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 bg-white text-orange-600 rounded-lg font-semibold text-sm hover:bg-orange-50 transition shadow-lg shadow-orange-700/20">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Buat Survey Baru
            </a>
        </div>
    </div>
    @endif

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
        {{-- Total Survey --}}
        <div class="bg-white rounded-xl border border-gray-200/80 p-5 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
                    <i data-lucide="clipboard-list" class="w-5 h-5 text-orange-600"></i>
                </div>
                <span class="text-xs font-medium text-gray-400">Total</span>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $totalSurveys }}</p>
            <p class="text-xs text-gray-500 mt-1">Survey dibuat</p>
        </div>

        {{-- Total Pertanyaan --}}
        <div class="bg-white rounded-xl border border-gray-200/80 p-5 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                    <i data-lucide="help-circle" class="w-5 h-5 text-blue-600"></i>
                </div>
                <span class="text-xs font-medium text-gray-400">Total</span>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $totalQuestions }}</p>
            <p class="text-xs text-gray-500 mt-1">Pertanyaan</p>
        </div>

        {{-- Target Responden --}}
        <div class="bg-white rounded-xl border border-gray-200/80 p-5 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                    <i data-lucide="users" class="w-5 h-5 text-emerald-600"></i>
                </div>
                <span class="text-xs font-medium text-gray-400">Total</span>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $totalTargetResponden }}</p>
            <p class="text-xs text-gray-500 mt-1">Target responden</p>
        </div>

        {{-- Responden Diperoleh --}}
        <div class="bg-white rounded-xl border border-gray-200/80 p-5 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-cyan-100 flex items-center justify-center">
                    <i data-lucide="user-check" class="w-5 h-5 text-cyan-700"></i>
                </div>
                <span class="text-xs font-medium text-gray-400">Admin</span>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $totalRespondenDiperoleh }}</p>
            <p class="text-xs text-gray-500 mt-1">Responden diperoleh</p>
            <p class="text-[11px] text-gray-400 mt-1.5">
                @if($adminTerakhirInput && $adminTerakhirInput->inputByAdmin)
                    Input terakhir: {{ $adminTerakhirInput->inputByAdmin->name }} ({{ $adminTerakhirInput->updated_at->format('d M Y H:i') }})
                @else
                    Input terakhir: Belum ada
                @endif
            </p>
        </div>

        {{-- Total Pengeluaran --}}
        <div class="bg-white rounded-xl border border-gray-200/80 p-5 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                    <i data-lucide="wallet" class="w-5 h-5 text-purple-600"></i>
                </div>
                <span class="text-xs font-medium text-gray-400">Dibayar</span>
            </div>
            <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($totalSpent, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">Total transaksi</p>
        </div>
    </div>

    {{-- Saldo & Reward Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Saldo Deposit Card --}}
        <div class="bg-gradient-to-r from-emerald-50 to-teal-50 rounded-xl border border-emerald-200/80 p-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
                        <i data-lucide="wallet" class="w-6 h-6 text-emerald-600"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Saldo Deposit Anda</p>
                        <p class="text-2xl font-extrabold text-emerald-600">Rp {{ number_format($user->deposit_balance, 0, ',', '.') }}</p>
                        <p class="text-[11px] text-gray-500 mt-0.5">Gunakan saldo untuk pembayaran survey yang lebih cepat!</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('user.wallet.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white border border-emerald-200 text-emerald-700 text-xs font-semibold hover:bg-emerald-50 transition shadow-sm">
                        Wallet
                    </a>
                    <a href="{{ route('user.topups.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition shadow-sm">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Top Up
                    </a>
                </div>
            </div>
        </div>

        {{-- Poin Reward Card --}}
        <div class="bg-gradient-to-r from-amber-50 to-orange-50 rounded-xl border border-amber-200/80 p-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center">
                        <i data-lucide="coins" class="w-6 h-6 text-amber-600"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Reward & Poin Anda</p>
                        <p class="text-2xl font-extrabold text-amber-600">{{ number_format($user->point_balance, 0, ',', '.') }} <span class="text-sm font-medium text-gray-500">poin</span></p>
                        <p class="text-[11px] text-gray-500 mt-0.5">Rp 1.000 transaksi = 1 poin. Tukarkan dengan tunai!</p>
                    </div>
                </div>
                <a href="{{ route('user.rewards.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-500 text-white text-xs font-semibold hover:bg-amber-600 transition shadow-sm">
                    <i data-lucide="gift" class="w-4 h-4"></i>
                    Lihat Reward
                </a>
            </div>
        </div>
    </div>

    {{-- Info Pricing untuk Create Survey --}}
    <div class="bg-white rounded-xl border border-gray-200/80 p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
            <div>
                <h2 class="text-sm font-semibold text-gray-900">Informasi Harga Sebelum Buat Survey</h2>
                <p class="text-xs text-gray-500 mt-1">Ringkasan dari halaman pricing agar bisa langsung dipakai saat create survey.</p>
            </div>
            <a href="{{ route('pricing') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-orange-200 bg-orange-50 text-orange-700 text-xs font-semibold hover:bg-orange-100 transition">
                <i data-lucide="calculator" class="w-4 h-4"></i>
                Buka Kalkulator Pricing
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                <p class="text-[11px] text-gray-500">Volume Pricing (per soal/orang)</p>
                <div class="mt-1 space-y-0.5 text-xs">
                    @php $vTiers = \App\Helpers\VolumePricing::getTiers(); $prev = 1; @endphp
                    @foreach($vTiers as $tier)
                    <p class="{{ $loop->last ? 'text-emerald-600 font-bold' : ($loop->first ? 'text-gray-700' : 'text-orange-600') }}">
                        <span class="font-semibold">{{ $tier['max'] === null ? '≥ ' . number_format($prev, 0, ',', '.') : number_format($prev, 0, ',', '.') . '–' . number_format($tier['max'], 0, ',', '.') }}:</span>
                        Rp {{ number_format($tier['price'], 0, ',', '.') }}
                    </p>
                    @php if($tier['max'] !== null) $prev = $tier['max'] + 1; @endphp
                    @endforeach
                </div>
            </div>
            <div class="rounded-lg border border-red-200 bg-red-50 p-3">
                <p class="text-[11px] text-red-600">Minimum Order</p>
                <p class="text-sm font-bold text-red-700">Rp {{ number_format(\App\Helpers\VolumePricing::getMinOrder(), 0, ',', '.') }} / survey</p>
                <p class="text-[11px] text-red-600">AI analyzer aktif saat judul + link terisi</p>
            </div>
        </div>
    </div>

    {{-- Content Grid --}}
    <div class="grid lg:grid-cols-2 gap-6">

        {{-- Survey Terbaru --}}
        <div class="bg-white rounded-xl border border-gray-200/80 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">Survey Terbaru</h2>
                <a href="{{ route('user.surveys.index') }}" class="text-xs font-medium text-orange-600 hover:text-orange-700">Lihat Semua</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentSurveys as $survey)
                    <a href="{{ route('user.surveys.show', $survey) }}" class="flex items-center gap-4 px-5 py-4 hover:bg-gray-50 transition">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-orange-100 to-amber-100 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="file-text" class="w-5 h-5 text-orange-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $survey->title }}</p>
                            <p class="text-xs text-gray-500">{{ $survey->question_count }} pertanyaan &bull; Target {{ $survey->respondent_count }} &bull; Diperoleh {{ $survey->admin_responses_sum_respond_count ?? 0 }}</p>
                        </div>
                        @php
                            $latestTransaction = $survey->transactions->first();
                        @endphp
                        <div class="text-right flex-shrink-0">
                            @php
                                $progress = $latestTransaction?->safeProgress() ?? 0;
                                $tahap1Selesai = $latestTransaction?->isStage1Completed() ?? false;
                                $tahap2Selesai = $latestTransaction?->isStage2Completed() ?? false;
                            @endphp
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                @if($progress >= 100) bg-emerald-100 text-emerald-700
                                @elseif($progress > 0) bg-blue-100 text-blue-700
                                @else bg-gray-100 text-gray-600
                                @endif">
                                {{ $progress }}%
                            </span>
                            <div class="mt-1.5 h-1.5 w-28 bg-gray-200 rounded-full overflow-hidden ml-auto">
                                <div
                                    @class([
                                        'h-full rounded-full',
                                        'bg-emerald-500' => $progress >= 100,
                                        'bg-blue-500' => $progress > 0 && $progress < 100,
                                        'bg-gray-300' => $progress <= 0,
                                    ])
                                    data-progress-width="{{ $progress }}"
                                ></div>
                            </div>
                            @if($latestTransaction)
                                <p class="text-[11px] mt-1 {{ $tahap1Selesai ? 'text-emerald-600' : 'text-amber-600' }}">Tahap 1: {{ $tahap1Selesai ? 'Selesai' : 'Menunggu Pembayaran' }}</p>
                                <p class="text-[11px] mt-0.5 {{ $tahap2Selesai ? 'text-emerald-600' : 'text-gray-500' }}">Tahap 2: {{ $tahap2Selesai ? 'Selesai' : 'Proses' }}</p>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="px-5 py-12 text-center">
                        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                            <i data-lucide="clipboard-list" class="w-6 h-6 text-gray-400"></i>
                        </div>
                        <p class="text-sm text-gray-500 mb-3">Belum ada survey</p>
                        <a href="{{ route('user.surveys.create') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-orange-600 hover:text-orange-700">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            Buat Survey Pertama
                        </a>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Transaksi Terakhir --}}
        <div class="bg-white rounded-xl border border-gray-200/80 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">Transaksi Terakhir</h2>
                <a href="{{ route('user.transactions.index') }}" class="text-xs font-medium text-orange-600 hover:text-orange-700">Lihat Semua</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentTransactions as $transaction)
                    @php
                        $trxProgress = $transaction->safeProgress();
                        $trxTahap1 = $transaction->isStage1Completed();
                        $trxTahap2 = $transaction->isStage2Completed();
                    @endphp
                    <div class="flex items-center gap-4 px-5 py-4">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 {{ $transaction->statusIconBackgroundClass() }}">
                            <i data-lucide="receipt" class="w-5 h-5 {{ $transaction->statusIconColorClass() }}"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $transaction->survey->title ?? 'Survey' }}</p>
                            <p class="text-xs text-gray-500">{{ $transaction->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-sm font-semibold text-gray-900">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $transaction->statusBadgeClass() }}">
                                {{ $transaction->statusLabel() }}
                            </span>
                            <div class="mt-1.5 h-1.5 w-28 bg-gray-200 rounded-full overflow-hidden ml-auto">
                                <div
                                    @class([
                                        'h-full rounded-full',
                                        'bg-emerald-500' => $trxProgress >= 100,
                                        'bg-blue-500' => $trxProgress > 0 && $trxProgress < 100,
                                        'bg-gray-300' => $trxProgress <= 0,
                                    ])
                                    data-progress-width="{{ $trxProgress }}"
                                ></div>
                            </div>
                            <p class="text-[11px] mt-1 {{ $trxTahap1 ? 'text-emerald-600' : 'text-amber-600' }}">Tahap 1: {{ $trxTahap1 ? 'Selesai' : 'Menunggu Pembayaran' }}</p>
                            <p class="text-[11px] mt-0.5 {{ $trxTahap2 ? 'text-emerald-600' : 'text-gray-500' }}">Tahap 2: {{ $trxTahap2 ? 'Selesai' : 'Proses' }}</p>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-12 text-center">
                        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                            <i data-lucide="receipt" class="w-6 h-6 text-gray-400"></i>
                        </div>
                        <p class="text-sm text-gray-500">Belum ada transaksi</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Menunggu Pembayaran Alert --}}
    @if($pendingPayments > 0)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
                <i data-lucide="alert-circle" class="w-5 h-5 text-amber-600"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium text-amber-800">Anda memiliki pembayaran yang menunggu</p>
                <p class="text-xs text-amber-600">Total: Rp {{ number_format($pendingPayments, 0, ',', '.') }}</p>
            </div>
            <a href="{{ route('user.transactions.index') }}" class="px-4 py-2 bg-amber-600 text-white rounded-lg text-sm font-medium hover:bg-amber-700 transition">
                Bayar Sekarang
            </a>
        </div>
    @endif

    {{-- Pembayaran Gagal Alert --}}
    @if($failedTransactions > 0)
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                <i data-lucide="x-circle" class="w-5 h-5 text-red-600"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium text-red-800">Ada pembayaran yang belum transaksi berhasil</p>
                <p class="text-xs text-red-600">Silakan coba bayar lagi. Jika masih gagal, hubungi admin.</p>
            </div>
            <a href="{{ route('user.transactions.index', ['status' => 'failed']) }}" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition">
                Coba Bayar Lagi
            </a>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') lucide.createIcons();

        document.querySelectorAll('[data-progress-width]').forEach(function(el) {
            const value = parseInt(el.dataset.progressWidth || '0', 10);
            const width = Math.max(0, Math.min(100, value));
            el.style.width = width + '%';
        });

        // Dashboard Banner Slider
        (function(){
            const slider = document.getElementById('dashboardSlider');
            if (!slider) return;
            const slides = Array.from(slider.children);
            const total = slides.length;
            if (total <= 1) return;
            const dotsEl = document.getElementById('dashboardBannerDots');

            slider.style.width = `${total * 100}%`;
            slides.forEach(s => { s.style.width = `${100 / total}%`; s.style.flex = '0 0 auto'; });

            if (dotsEl) {
                slides.forEach((_, i) => {
                    const btn = document.createElement('button');
                    btn.className = 'w-2.5 h-2.5 rounded-full bg-white/50 transition';
                    btn.addEventListener('click', () => { show(i); reset(); });
                    dotsEl.appendChild(btn);
                });
            }

            const dots = dotsEl ? Array.from(dotsEl.children) : [];
            let idx = 0, timer = null;

            function show(i) {
                idx = (i + total) % total;
                slider.style.transform = `translateX(-${idx * 100 / total}%)`;
                if (dots.length > 0) {
                    dots.forEach((d, j) => {
                        d.className = 'w-2.5 h-2.5 rounded-full transition ' + (j === idx ? 'bg-white' : 'bg-white/40');
                    });
                }
            }

            function next() { show(idx + 1); }
            function reset() { clearInterval(timer); timer = setInterval(next, 4500); }

            slider.parentElement.addEventListener('mouseenter', () => clearInterval(timer));
            slider.parentElement.addEventListener('mouseleave', reset);

            show(0);
            reset();
        })();
    });
</script>
@endpush
