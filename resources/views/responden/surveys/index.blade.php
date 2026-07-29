@extends('layouts.responden')

@section('page-title', 'Survey Tersedia')
@section('page-description', 'Daftar survey yang tersedia untuk Anda')

@section('content')

<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h2 class="text-sm font-bold text-gray-900">Survey Tersedia</h2>
            <p class="text-xs text-gray-400 mt-0.5">Daftar survey yang sedang aktif</p>
        </div>
    </div>

    @if($surveys->isEmpty())
        <div class="px-6 py-16 text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center mb-4">
                <i data-lucide="inbox" class="w-8 h-8 text-gray-300"></i>
            </div>
            <p class="text-sm font-medium text-gray-500">Belum ada survey tersedia</p>
            <p class="text-xs text-gray-400 mt-1">Saat ini belum ada survey aktif yang tersedia.</p>
        </div>
    @else
        {{-- Mobile Cards --}}
        <div class="block sm:hidden divide-y divide-gray-50">
            @foreach($surveys as $survey)
                <a href="{{ route('responden.surveys.show', $survey) }}" class="block px-5 py-4 hover:bg-gray-50/50 transition-colors">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-gray-900 leading-tight truncate">{{ $survey->title }}</p>
                            <div class="flex items-center gap-3 mt-2">
                                @if($survey->estimated_time_minutes)
                                    <span class="text-[11px] text-gray-400 font-medium flex items-center gap-1">
                                        <i data-lucide="clock" class="w-3 h-3"></i>
                                        {{ $survey->estimated_time_minutes }} menit
                                    </span>
                                @endif
                            </div>
                            @if($survey->deadline)
                                <p class="text-[11px] text-gray-400 mt-1.5 flex items-center gap-1">
                                    <i data-lucide="calendar" class="w-3 h-3"></i>
                                    Deadline: {{ $survey->deadline->format('d M Y') }}
                                </p>
                            @endif
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-gray-300 flex-shrink-0 mt-1"></i>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Desktop Table --}}
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-400">Survey</th>
                        <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-400">Estimasi Waktu</th>
                        <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-400">Deadline</th>
                        <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wider text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($surveys as $survey)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="text-sm font-semibold text-gray-900 leading-tight">{{ $survey->title }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs text-gray-500 font-medium">
                                    @if($survey->estimated_time_minutes)
                                        {{ $survey->estimated_time_minutes }} menit
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs text-gray-500 font-medium">
                                    @if($survey->deadline)
                                        {{ $survey->deadline->format('d M Y') }}
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('responden.surveys.show', $survey) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-50 border border-orange-100 text-xs font-semibold text-orange-600 hover:bg-orange-100 hover:text-orange-700 transition">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($surveys->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $surveys->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
