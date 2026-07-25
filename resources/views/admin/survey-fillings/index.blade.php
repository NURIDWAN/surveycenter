@extends('layouts.crm')

@section('title', 'Verifikasi Survey Filling')
@section('page-title', 'Verifikasi Survey Filling')

@section('content')
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Verifikasi Pengisian Survey</h2>
                <p class="text-sm text-gray-500 mt-1">Kelola dan verifikasi pengisian survey dari responden</p>
            </div>
        </div>

        {{-- Status Filter --}}
        <div class="flex items-center gap-3">
            <label for="status-filter" class="text-sm font-medium text-gray-700">Filter Status:</label>
            <select id="status-filter" onchange="window.location.href=this.value"
                    class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                <option value="{{ route('admin.survey-fillings.index') }}"
                    {{ !request('status') ? 'selected' : '' }}>
                    Semua
                </option>
                <option value="{{ route('admin.survey-fillings.index', ['status' => 'menunggu_verifikasi']) }}"
                    {{ request('status') === 'menunggu_verifikasi' ? 'selected' : '' }}>
                    Menunggu Verifikasi
                </option>
                <option value="{{ route('admin.survey-fillings.index', ['status' => 'disetujui']) }}"
                    {{ request('status') === 'disetujui' ? 'selected' : '' }}>
                    Disetujui
                </option>
                <option value="{{ route('admin.survey-fillings.index', ['status' => 'ditolak']) }}"
                    {{ request('status') === 'ditolak' ? 'selected' : '' }}>
                    Ditolak
                </option>
            </select>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Survey</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Responden</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Waktu Kirim</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($fillings as $filling)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3.5 font-medium text-gray-900">{{ $filling->survey->title ?? '-' }}</td>
                                <td class="px-4 py-3.5 text-gray-700">{{ $filling->user->name ?? '-' }}</td>
                                <td class="px-4 py-3.5 text-gray-500 text-xs">
                                    {{ $filling->created_at ? $filling->created_at->format('d M Y H:i') : '-' }}
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @switch($filling->status)
                                        @case('menunggu_verifikasi')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Menunggu Verifikasi
                                            </span>
                                            @break
                                        @case('disetujui')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Disetujui
                                            </span>
                                            @break
                                        @case('ditolak')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Ditolak
                                            </span>
                                            @break
                                        @case('sedang_dikerjakan')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Sedang Dikerjakan
                                            </span>
                                            @break
                                        @default
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                                {{ $filling->status }}
                                            </span>
                                    @endswitch
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.survey-fillings.show', $filling) }}"
                                           class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 hover:text-gray-700 transition"
                                           title="Lihat Detail">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-12">
                                    <i data-lucide="clipboard-check" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
                                    <p class="text-sm text-gray-500">Belum ada data pengisian survey</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-2">
            {{ $fillings->appends(request()->query())->links() }}
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
