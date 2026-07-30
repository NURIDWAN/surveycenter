@extends('layouts.app')

@section('content')
{{-- Hero Header Section --}}
<div class="relative overflow-hidden bg-slate-900 py-12 lg:py-16 text-white">
    <!-- Ambient Background Glows -->
    <div class="absolute -top-24 -left-20 w-96 h-96 rounded-full bg-orange-500/20 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -right-20 w-96 h-96 rounded-full bg-orange-600/15 blur-3xl pointer-events-none"></div>

    <div class="relative mx-auto max-w-7xl px-5 lg:px-8">
        <div class="max-w-3xl">
            <span class="inline-flex items-center gap-2 rounded-full bg-orange-500/10 px-3.5 py-1.5 text-xs font-semibold text-orange-400 border border-orange-500/20 backdrop-blur-md mb-4">
                <i class="fa-solid fa-clipboard-list text-orange-400"></i> Public Directory
            </span>
            <h1 class="text-3xl font-extrabold tracking-tight sm:text-4xl lg:text-5xl text-white">
                Kumpulan <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 via-amber-300 to-orange-500">Kuisioner</span>
            </h1>
            <p class="mt-4 text-base sm:text-lg text-slate-300 leading-relaxed">
                Jelajahi berbagai riset & survei aktif. Berikan tanggapan Anda, bantu pengambil keputusan, dan dapatkan imbalan menarik untuk setiap kuisioner yang Anda selesaikan.
            </p>
        </div>
    </div>
</div>

{{-- Main Content Section --}}
<div class="bg-slate-50 min-h-screen py-10 lg:py-14">
    <div class="mx-auto max-w-7xl px-5 lg:px-8">

        {{-- Filter & Search Header Card --}}
        <div class="mb-8 rounded-2xl border border-slate-200/80 bg-white p-5 lg:p-6 shadow-sm">
            <form action="{{ route('surveys.public') }}" method="GET" class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                {{-- Status Filter Tabs (Semua, Tersedia, Tidak Tersedia) --}}
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Status: Semua --}}
                    <a href="{{ route('surveys.public', array_merge(request()->query(), ['status' => 'all', 'page' => 1])) }}"
                       class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition-all duration-200 {{ $statusFilter === 'all' ? 'bg-orange-500 text-white shadow-md shadow-orange-500/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-900' }}">
                        <i class="fa-solid fa-layer-group text-[11px]"></i>
                        <span>Semua Kuisioner</span>
                        <span class="rounded-full bg-white/20 px-2 py-0.5 text-[10px] font-extrabold {{ $statusFilter === 'all' ? 'text-white' : 'bg-slate-200 text-slate-700' }}">
                            {{ $totalCount }}
                        </span>
                    </a>

                    {{-- Status: Tersedia --}}
                    <a href="{{ route('surveys.public', array_merge(request()->query(), ['status' => 'available', 'page' => 1])) }}"
                       class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition-all duration-200 {{ $statusFilter === 'available' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'bg-slate-100 text-slate-600 hover:bg-emerald-50 hover:text-emerald-700' }}">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span>Tersedia</span>
                        <span class="rounded-full px-2 py-0.5 text-[10px] font-extrabold {{ $statusFilter === 'available' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }}">
                            {{ $availableCount }}
                        </span>
                    </a>

                    {{-- Status: Tidak Tersedia --}}
                    <a href="{{ route('surveys.public', array_merge(request()->query(), ['status' => 'unavailable', 'page' => 1])) }}"
                       class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition-all duration-200 {{ $statusFilter === 'unavailable' ? 'bg-rose-600 text-white shadow-md shadow-rose-600/20' : 'bg-slate-100 text-slate-600 hover:bg-rose-50 hover:text-rose-700' }}">
                        <span class="inline-block h-2 w-2 rounded-full bg-rose-500"></span>
                        <span>Tidak Tersedia</span>
                        <span class="rounded-full px-2 py-0.5 text-[10px] font-extrabold {{ $statusFilter === 'unavailable' ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-800' }}">
                            {{ $unavailableCount }}
                        </span>
                    </a>
                </div>

                {{-- Search Box --}}
                <div class="relative w-full lg:w-80">
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
                    <div class="relative">
                        <input type="text"
                               name="search"
                               value="{{ $search }}"
                               placeholder="Cari judul kuisioner..."
                               class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-2.5 pl-10 pr-10 text-xs text-slate-800 placeholder-slate-400 transition focus:border-orange-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>

                        @if($search)
                            <a href="{{ route('surveys.public', ['status' => $statusFilter]) }}"
                               class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        @endif
                    </div>
                </div>

            </form>
        </div>

        {{-- Active Filter Bar Notice if search/filter applied --}}
        @if($search || $statusFilter !== 'all')
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-xl bg-orange-50/60 border border-orange-100 px-4 py-3 text-xs text-orange-900">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-filter text-orange-500"></i>
                    <span>
                        Filter aktif:
                        @if($statusFilter === 'available')
                            <strong>Status: Tersedia</strong>
                        @elseif($statusFilter === 'unavailable')
                            <strong>Status: Tidak Tersedia</strong>
                        @else
                            <strong>Status: Semua</strong>
                        @endif
                        @if($search)
                            | Kata kunci: <strong>"{{ $search }}"</strong>
                        @endif
                    </span>
                </div>
                <a href="{{ route('surveys.public') }}" class="font-bold text-orange-600 hover:text-orange-800 underline transition">
                    Reset Filter
                </a>
            </div>
        @endif

        {{-- Card Grid Section --}}
        @if($surveys->count() > 0)
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach($surveys as $survey)
                    @php
                        $isAvailable = $survey->is_available;
                        $deadlineText = $survey->deadline ? $survey->deadline->translatedFormat('d M Y, H:i') : 'Tidak Ada Deadline';
                        $durationText = $survey->estimated_time_minutes ? $survey->estimated_time_minutes . ' Menit' : '5 - 10 Menit';
                        $rewardText = $survey->reward_amount > 0 ? 'Rp ' . number_format($survey->reward_amount, 0, ',', '.') : 'Gratis / Poin';
                    @endphp

                    <div class="group relative flex flex-col justify-between rounded-2xl border border-slate-200/90 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-orange-200 hover:shadow-xl hover:shadow-orange-500/5">

                        <div>
                            {{-- Card Header: Status Badge & Reward --}}
                            <div class="mb-4 flex items-center justify-between gap-2">
                                {{-- Availability Status Badge --}}
                                @if($isAvailable)
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[11px] font-bold text-emerald-700">
                                        <span class="relative flex h-2 w-2">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                        </span>
                                        Tersedia
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-100 px-3 py-1 text-[11px] font-bold text-slate-600">
                                        <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                                        Tidak Tersedia
                                    </span>
                                @endif

                                {{-- Insentif / Reward Badge --}}
                                <span class="inline-flex items-center gap-1 rounded-lg bg-orange-50 px-2.5 py-1 text-xs font-extrabold text-orange-600 border border-orange-100">
                                    <i class="fa-solid fa-coins text-amber-500 text-[11px]"></i>
                                    {{ $rewardText }}
                                </span>
                            </div>

                            {{-- Survey Title --}}
                            <h3 class="mb-2 text-lg font-bold text-slate-900 group-hover:text-orange-600 transition-colors line-clamp-2 leading-snug">
                                {{ $survey->title }}
                            </h3>

                            {{-- Survey Description Snippet --}}
                            @if(!empty($survey->description))
                                <p class="mb-4 text-xs text-slate-500 line-clamp-2 leading-relaxed">
                                    {{ $survey->description }}
                                </p>
                            @else
                                <p class="mb-4 text-xs italic text-slate-400">
                                    Tidak ada deskripsi tambahan.
                                </p>
                            @endif

                            {{-- Divider --}}
                            <div class="my-4 border-t border-slate-100"></div>

                            {{-- Details List (Deadline & Duration & Questions) --}}
                            <div class="space-y-2.5 text-xs text-slate-600 mb-6">
                                {{-- Durasi Pengerjaan --}}
                                <div class="flex items-center gap-2.5">
                                    <div class="grid h-7 w-7 place-items-center rounded-lg bg-slate-100 text-slate-600 group-hover:bg-orange-100 group-hover:text-orange-600 transition-colors">
                                        <i class="fa-regular fa-clock text-xs"></i>
                                    </div>
                                    <div>
                                        <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Durasi Pengerjaan</span>
                                        <span class="font-semibold text-slate-800">{{ $durationText }}</span>
                                    </div>
                                </div>

                                {{-- Deadline --}}
                                <div class="flex items-center gap-2.5">
                                    <div class="grid h-7 w-7 place-items-center rounded-lg bg-slate-100 text-slate-600 group-hover:bg-orange-100 group-hover:text-orange-600 transition-colors">
                                        <i class="fa-regular fa-calendar-check text-xs"></i>
                                    </div>
                                    <div>
                                        <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Deadline</span>
                                        <span class="font-semibold {{ ($survey->deadline && $survey->deadline->isPast()) ? 'text-rose-600 font-bold' : 'text-slate-800' }}">
                                            {{ $deadlineText }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Jumlah Pertanyaan --}}
                                @if($survey->question_count > 0)
                                    <div class="flex items-center gap-2.5">
                                        <div class="grid h-7 w-7 place-items-center rounded-lg bg-slate-100 text-slate-600 group-hover:bg-orange-100 group-hover:text-orange-600 transition-colors">
                                            <i class="fa-solid fa-list-check text-xs"></i>
                                        </div>
                                        <div>
                                            <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Jumlah Pertanyaan</span>
                                            <span class="font-semibold text-slate-800">{{ $survey->question_count }} Soal</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Card Footer / Action Button --}}
                        <div class="pt-2">
                            @if($isAvailable)
                                @auth
                                    @if(auth()->user()->isResponden())
                                        <a href="{{ route('responden.surveys.show', $survey) }}"
                                           class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-orange-500 py-3 text-xs font-bold text-white shadow-lg shadow-orange-500/20 transition-all hover:bg-orange-600 hover:shadow-orange-500/30">
                                            <span>Isi Kuisioner Sekarang</span>
                                            <i class="fa-solid fa-arrow-right text-[10px] transition-transform group-hover:translate-x-1"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('responden.surveys.show', $survey) }}"
                                           class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 py-3 text-xs font-bold text-white shadow-md transition-all hover:bg-slate-800">
                                            <span>Lihat Detail Kuisioner</span>
                                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                        </a>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}"
                                       class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-orange-500 py-3 text-xs font-bold text-white shadow-lg shadow-orange-500/20 transition-all hover:bg-orange-600 hover:shadow-orange-500/30">
                                        <span>Isi Kuisioner (Login Responden)</span>
                                        <i class="fa-solid fa-right-to-bracket text-[10px]"></i>
                                    </a>
                                @endauth
                            @else
                                <button disabled
                                        class="inline-flex w-full cursor-not-allowed items-center justify-center gap-2 rounded-xl bg-slate-100 py-3 text-xs font-bold text-slate-400">
                                    <i class="fa-solid fa-lock text-[10px]"></i>
                                    <span>Kuisioner Tidak Tersedia</span>
                                </button>
                            @endif
                        </div>

                    </div>
                @endforeach
            </div>

            {{-- Pagination Links --}}
            <div class="mt-10">
                {{ $surveys->links() }}
            </div>
        @else
            {{-- Empty State --}}
            <div class="my-12 rounded-3xl border border-slate-200/80 bg-white p-12 text-center shadow-sm max-w-xl mx-auto">
                <div class="mx-auto grid h-20 w-20 place-items-center rounded-2xl bg-orange-50 text-orange-500 mb-5">
                    <i class="fa-solid fa-folder-open text-3xl"></i>
                </div>
                <h3 class="text-lg font-extrabold text-slate-900 mb-2">Kuisioner Tidak Ditemukan</h3>
                <p class="text-xs text-slate-500 leading-relaxed mb-6">
                    @if($search)
                        Tidak ada kuisioner yang sesuai dengan kata kunci <strong>"{{ $search }}"</strong>.
                    @elseif($statusFilter === 'available')
                        Saat ini belum ada kuisioner yang berstatus <strong>Tersedia</strong>.
                    @elseif($statusFilter === 'unavailable')
                        Belum ada kuisioner dengan status <strong>Tidak Tersedia</strong>.
                    @else
                        Belum ada kuisioner yang terdaftar saat ini.
                    @endif
                </p>
                <a href="{{ route('surveys.public') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-xs font-bold text-white shadow-md hover:bg-slate-800 transition">
                    <i class="fa-solid fa-arrows-rotate text-[11px]"></i>
                    <span>Tampilkan Semua Kuisioner</span>
                </a>
            </div>
        @endif

    </div>
</div>
@endsection
