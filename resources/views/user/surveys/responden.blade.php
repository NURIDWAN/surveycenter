@extends('layouts.user')

@section('title', 'Kelola Survey Responden')
@section('page-title', 'Kelola Survey Responden')
@section('page-description', 'Aktifkan atau nonaktifkan survey untuk responden')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Survey untuk Responden</h2>
            <p class="text-sm text-gray-500">Kelola ketersediaan survey di panel responden</p>
        </div>
        <a href="{{ route('user.surveys.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-orange-600 text-white rounded-lg font-medium text-sm hover:bg-orange-700 transition shadow-sm">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Buat Survey Baru
        </a>
    </div>

    {{-- Info Card --}}
    <div class="bg-blue-50 rounded-xl p-4 border border-blue-100">
        <div class="flex gap-3">
            <i data-lucide="info" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5"></i>
            <p class="text-sm text-blue-700">
                Hanya survey yang sudah <strong>dibayar</strong> yang muncul di sini. Aktifkan survey agar tampil di dashboard responden.
            </p>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200/80 overflow-hidden">
        @if($surveys->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Survey</th>
                            <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Target</th>
                            <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Disetujui</th>
                            <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Menunggu</th>
                            <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Reward</th>
                            <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($surveys as $survey)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-orange-100 to-amber-100 flex items-center justify-center flex-shrink-0">
                                            <i data-lucide="users" class="w-5 h-5 text-orange-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $survey->title }}</p>
                                            <p class="text-xs text-gray-500">{{ $survey->created_at->format('d M Y') }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="text-sm font-medium text-gray-900">{{ $survey->respondent_count }}</span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="text-sm font-bold text-emerald-600">{{ $survey->approved_count }}</span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="text-sm font-medium text-amber-600">{{ $survey->pending_count }}</span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="text-sm font-medium text-gray-900">Rp {{ number_format($survey->reward_amount ?? 0, 0, ',', '.') }}</span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    @if($survey->status === 'active')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-1.5"></span>
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <form method="POST" action="{{ route('user.surveys.toggle-status', $survey) }}">
                                        @csrf
                                        @method('PATCH')
                                        @if($survey->status === 'active')
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-amber-700 bg-amber-50 border border-amber-200 hover:bg-amber-100 transition">
                                                <i data-lucide="pause-circle" class="w-3.5 h-3.5"></i>
                                                Nonaktifkan
                                            </button>
                                        @else
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 transition">
                                                <i data-lucide="play-circle" class="w-3.5 h-3.5"></i>
                                                Aktifkan
                                            </button>
                                        @endif
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($surveys->hasPages())
                <div class="px-5 py-4 border-t border-gray-100">
                    {{ $surveys->links() }}
                </div>
            @endif
        @else
            <div class="px-5 py-16 text-center">
                <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="users" class="w-8 h-8 text-gray-400"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-1">Belum ada survey untuk responden</h3>
                <p class="text-sm text-gray-500 mb-4">Buat survey dan bayar terlebih dahulu agar bisa dikelola di sini</p>
                <a href="{{ route('user.surveys.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-orange-600 text-white rounded-lg font-medium text-sm hover:bg-orange-700 transition">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Buat Survey
                </a>
            </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>
@endpush
