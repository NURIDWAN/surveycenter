@extends('layouts.app')

@section('content')
{{-- Hero Header Section --}}
<div class="relative overflow-hidden border-b border-orange-100 bg-gradient-to-b from-orange-50 via-white to-white py-12 lg:py-16">
    <div class="relative mx-auto max-w-7xl px-5 lg:px-8">
        <div class="max-w-3xl">
            <span class="mb-4 inline-flex items-center gap-2 rounded-full border border-orange-200 bg-white px-3.5 py-1.5 text-sm font-semibold text-orange-600 shadow-sm">
                <i class="fa-solid fa-clipboard-list text-orange-500"></i> Public Directory
            </span>
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl lg:text-5xl">
                Kumpulan <span class="text-orange-600">Kuisioner</span>
            </h1>
            <p class="mt-4 text-base leading-8 text-slate-600 sm:text-lg">
                Jelajahi berbagai riset dan survei aktif. Berikan tanggapan Anda dan bantu pengambil keputusan mendapatkan data yang lebih baik.
            </p>
        </div>
    </div>
</div>

{{-- Main Content Section --}}
<div class="min-h-screen bg-[#fafafa] py-10 lg:py-14">
    <div class="mx-auto max-w-7xl px-5 lg:px-8">

        {{-- Filter & Search Header Card --}}
        <div class="mb-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:p-6">
            <form action="{{ route('kumpulan-quisioner') }}" method="GET" class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                {{-- Status Filter Tabs (Semua, Tersedia, Tidak Tersedia) --}}
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Status: Semua --}}
                    <a href="{{ route('kumpulan-quisioner', array_merge(request()->query(), ['status' => 'all', 'page' => 1])) }}"
                       class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold transition-all duration-200 {{ $statusFilter === 'all' ? 'bg-orange-500 text-white shadow-md shadow-orange-500/20' : 'bg-slate-50 text-slate-600 ring-1 ring-slate-200 hover:bg-orange-50 hover:text-orange-700 hover:ring-orange-200' }}">
                        <i class="fa-solid fa-layer-group text-xs"></i>
                        <span>Semua Kuisioner</span>
                        <span class="rounded-full px-2 py-0.5 text-xs font-extrabold {{ $statusFilter === 'all' ? 'bg-white/20 text-white' : 'bg-white text-slate-700 ring-1 ring-slate-200' }}">
                            {{ $totalCount }}
                        </span>
                    </a>

                    {{-- Status: Tersedia --}}
                    <a href="{{ route('kumpulan-quisioner', array_merge(request()->query(), ['status' => 'available', 'page' => 1])) }}"
                       class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold transition-all duration-200 {{ $statusFilter === 'available' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'bg-slate-50 text-slate-600 ring-1 ring-slate-200 hover:bg-emerald-50 hover:text-emerald-700 hover:ring-emerald-200' }}">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span>Tersedia</span>
                        <span class="rounded-full px-2 py-0.5 text-xs font-extrabold {{ $statusFilter === 'available' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }}">
                            {{ $availableCount }}
                        </span>
                    </a>

                    {{-- Status: Tidak Tersedia --}}
                    <a href="{{ route('kumpulan-quisioner', array_merge(request()->query(), ['status' => 'unavailable', 'page' => 1])) }}"
                       class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold transition-all duration-200 {{ $statusFilter === 'unavailable' ? 'bg-rose-600 text-white shadow-md shadow-rose-600/20' : 'bg-slate-50 text-slate-600 ring-1 ring-slate-200 hover:bg-rose-50 hover:text-rose-700 hover:ring-rose-200' }}">
                        <span class="inline-block h-2 w-2 rounded-full bg-rose-500"></span>
                        <span>Tidak Tersedia</span>
                        <span class="rounded-full px-2 py-0.5 text-xs font-extrabold {{ $statusFilter === 'unavailable' ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-800' }}">
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
                               class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-10 text-sm text-slate-800 placeholder-slate-400 transition focus:border-orange-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>

                        @if($search)
                            <a href="{{ route('kumpulan-quisioner', ['status' => $statusFilter]) }}"
                               class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-slate-400 hover:text-slate-600">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        @endif
                    </div>
                </div>

            </form>
        </div>

        {{-- Active Filter Bar Notice if search/filter applied --}}
        @if($search || $statusFilter !== 'all')
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-orange-100 bg-orange-50 px-4 py-3 text-sm text-orange-900">
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
                <a href="{{ route('kumpulan-quisioner') }}" class="font-bold text-orange-600 hover:text-orange-800 underline transition">
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
                    @endphp

                    <div class="group relative flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-orange-200 hover:shadow-lg hover:shadow-orange-500/10">

                        <div>
                            {{-- Card Header: Status Badge --}}
                            <div class="mb-4 flex items-center">
                                {{-- Availability Status Badge --}}
                                @if($isAvailable)
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                        <span class="relative flex h-2 w-2">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                        </span>
                                        Tersedia
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-bold text-slate-600">
                                        <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                                        Tidak Tersedia
                                    </span>
                                @endif
                            </div>

                            {{-- Survey Title --}}
                            <h3 class="mb-2 text-xl font-bold leading-snug text-slate-950 transition-colors line-clamp-2 group-hover:text-orange-600">
                                {{ $survey->title }}
                            </h3>

                            {{-- Survey Description Snippet --}}
                            @if(!empty($survey->description))
                                <p class="mb-5 text-sm leading-6 text-slate-600 line-clamp-2">
                                    {{ $survey->description }}
                                </p>
                            @else
                                <p class="mb-5 text-sm italic leading-6 text-slate-400">
                                    Tidak ada deskripsi tambahan.
                                </p>
                            @endif

                            {{-- Divider --}}
                            <div class="my-5 border-t border-slate-100"></div>

                            {{-- Details List (Deadline & Duration & Questions) --}}
                            <div class="mb-6 space-y-3 text-sm text-slate-600">
                                {{-- Durasi Pengerjaan --}}
                                <div class="flex items-center gap-2.5">
                                    <div class="grid h-8 w-8 place-items-center rounded-lg bg-orange-50 text-orange-600 transition-colors group-hover:bg-orange-100">
                                        <i class="fa-regular fa-clock text-sm"></i>
                                    </div>
                                    <div>
                                        <span class="block text-xs font-bold uppercase tracking-wide text-slate-400">Durasi Pengerjaan</span>
                                        <span class="font-semibold text-slate-800">{{ $durationText }}</span>
                                    </div>
                                </div>

                                {{-- Deadline --}}
                                <div class="flex items-center gap-2.5">
                                    <div class="grid h-8 w-8 place-items-center rounded-lg bg-orange-50 text-orange-600 transition-colors group-hover:bg-orange-100">
                                        <i class="fa-regular fa-calendar-check text-sm"></i>
                                    </div>
                                    <div>
                                        <span class="block text-xs font-bold uppercase tracking-wide text-slate-400">Deadline</span>
                                        <span class="font-semibold {{ ($survey->deadline && $survey->deadline->isPast()) ? 'text-rose-600 font-bold' : 'text-slate-800' }}">
                                            {{ $deadlineText }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Jumlah Pertanyaan --}}
                                @if($survey->question_count > 0)
                                    <div class="flex items-center gap-2.5">
                                        <div class="grid h-8 w-8 place-items-center rounded-lg bg-orange-50 text-orange-600 transition-colors group-hover:bg-orange-100">
                                            <i class="fa-solid fa-list-check text-sm"></i>
                                        </div>
                                        <div>
                                            <span class="block text-xs font-bold uppercase tracking-wide text-slate-400">Jumlah Pertanyaan</span>
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
                                           class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-orange-500 py-3 text-sm font-bold text-white shadow-lg shadow-orange-500/20 transition-all hover:bg-orange-600 hover:shadow-orange-500/30">
                                            <span>Isi Kuisioner Sekarang</span>
                                            <i class="fa-solid fa-arrow-right text-xs transition-transform group-hover:translate-x-1"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('responden.surveys.show', $survey) }}"
                                           class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 py-3 text-sm font-bold text-white shadow-md transition-all hover:bg-slate-800">
                                            <span>Lihat Detail Kuisioner</span>
                                            <i class="fa-solid fa-arrow-right text-xs"></i>
                                        </a>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}"
                                       class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-orange-500 py-3 text-sm font-bold text-white shadow-lg shadow-orange-500/20 transition-all hover:bg-orange-600 hover:shadow-orange-500/30">
                                        <span>Isi Kuisioner (Login Responden)</span>
                                        <i class="fa-solid fa-right-to-bracket text-xs"></i>
                                    </a>
                                @endauth
                            @else
                                <button disabled
                                        class="inline-flex w-full cursor-not-allowed items-center justify-center gap-2 rounded-xl bg-slate-100 py-3 text-sm font-bold text-slate-400">
                                    <i class="fa-solid fa-lock text-xs"></i>
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
            <div class="mx-auto my-12 max-w-xl rounded-3xl border border-slate-200 bg-white p-12 text-center shadow-sm">
                <div class="mx-auto mb-5 grid h-20 w-20 place-items-center rounded-2xl bg-orange-50 text-orange-500">
                    <i class="fa-solid fa-folder-open text-3xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-extrabold text-slate-950">Kuisioner Tidak Ditemukan</h3>
                <p class="mb-6 text-sm leading-6 text-slate-500">
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
                <a href="{{ route('kumpulan-quisioner') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-slate-800">
                    <i class="fa-solid fa-arrows-rotate text-xs"></i>
                    <span>Tampilkan Semua Kuisioner</span>
                </a>
            </div>
        @endif

    </div>
</div>
@endsection
