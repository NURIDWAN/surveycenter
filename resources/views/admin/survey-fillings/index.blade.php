@extends('layouts.crm')

@section('title', 'Verifikasi Pengisian Survey')
@section('page-title', 'Verifikasi Pengisian Survey')

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Verifikasi Pengisian Survey</h2>
            <p class="text-sm text-gray-500 mt-0.5">Tinjau dan verifikasi bukti pengisian survey dari responden</p>
        </div>
        @php
            $pendingCount = \App\Models\SurveyFilling::where('status', 'menunggu_verifikasi')->count();
        @endphp
        @if($pendingCount > 0)
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-100 text-amber-700 text-xs font-bold rounded-full self-start sm:self-auto">
                <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                {{ $pendingCount }} Menunggu Verifikasi
            </span>
        @endif
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 flex-shrink-0"></i>
            <p class="text-sm text-emerald-700 font-medium">{{ session('success') }}</p>
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 flex-shrink-0"></i>
            <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Filter Tabs --}}
    <div class="bg-white rounded-xl border border-gray-200 p-1 flex gap-1 w-fit">
        @php
            $tabs = [
                '' => 'Semua',
                'menunggu_verifikasi' => 'Menunggu',
                'disetujui' => 'Disetujui',
                'ditolak' => 'Ditolak',
                'sedang_dikerjakan' => 'Dikerjakan',
            ];
        @endphp
        @foreach($tabs as $value => $label)
            <a href="{{ route('admin.survey-fillings.index', $value ? ['status' => $value] : []) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition
                   {{ request('status', '') === $value
                       ? 'bg-orange-600 text-white shadow-sm'
                       : 'text-gray-600 hover:bg-gray-100' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Survey</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Responden</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Reward</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Waktu Kirim</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($fillings as $filling)
                        <tr class="hover:bg-gray-50 transition {{ $filling->status === 'menunggu_verifikasi' ? 'bg-amber-50/20' : '' }}">
                            <td class="px-4 py-3.5">
                                <p class="font-medium text-gray-900 truncate max-w-[200px]" title="{{ $filling->survey->title ?? '-' }}">
                                    {{ $filling->survey->title ?? '-' }}
                                </p>
                                <p class="text-[11px] text-gray-400 mt-0.5">
                                    Reward: Rp {{ number_format($filling->survey->reward_amount ?? 0, 0, ',', '.') }}
                                </p>
                            </td>
                            <td class="px-4 py-3.5">
                                <p class="font-medium text-gray-800">{{ $filling->user->name ?? '-' }}</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">{{ $filling->user->email ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3.5">
                                @if($filling->status === 'disetujui')
                                    <span class="text-sm font-bold text-emerald-600">
                                        Rp {{ number_format($filling->survey->reward_amount ?? 0, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-xs text-gray-500">
                                {{ $filling->created_at?->format('d M Y') }}<br>
                                <span class="text-gray-400">{{ $filling->created_at?->format('H:i') }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @switch($filling->status)
                                    @case('menunggu_verifikasi')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-700">
                                            <i data-lucide="clock" class="w-3 h-3"></i> Menunggu
                                        </span>
                                        @break
                                    @case('disetujui')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700">
                                            <i data-lucide="check-circle" class="w-3 h-3"></i> Disetujui
                                        </span>
                                        @break
                                    @case('ditolak')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-red-100 text-red-700">
                                            <i data-lucide="x-circle" class="w-3 h-3"></i> Ditolak
                                        </span>
                                        @break
                                    @case('sedang_dikerjakan')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-100 text-blue-700">
                                            <i data-lucide="loader" class="w-3 h-3"></i> Dikerjakan
                                        </span>
                                        @break
                                @endswitch
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($filling->status === 'menunggu_verifikasi')
                                        <form method="POST" action="{{ route('admin.survey-fillings.approve', $filling) }}"
                                            onsubmit="return confirm('Setujui pengisian ini dan kirim reward ke responden?')">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center gap-1 px-2 py-1.5 bg-emerald-600 text-white text-xs font-semibold rounded-lg hover:bg-emerald-700 transition">
                                                <i data-lucide="check" class="w-3 h-3"></i> Setujui
                                            </button>
                                        </form>
                                        <button type="button" onclick="openRejectModal({{ $filling->id }})"
                                            class="inline-flex items-center gap-1 px-2 py-1.5 bg-red-600 text-white text-xs font-semibold rounded-lg hover:bg-red-700 transition">
                                            <i data-lucide="x" class="w-3 h-3"></i> Tolak
                                        </button>
                                    @endif
                                    <a href="{{ route('admin.survey-fillings.show', $filling) }}"
                                        class="inline-flex items-center gap-1 px-2 py-1.5 border border-gray-200 text-gray-600 text-xs font-semibold rounded-lg hover:bg-gray-50 transition">
                                        <i data-lucide="eye" class="w-3 h-3"></i> Detail
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center">
                                <i data-lucide="clipboard-check" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                                <p class="text-sm text-gray-400 font-medium">Belum ada data pengisian survey.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($fillings->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $fillings->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Reject Modal --}}
<div id="rejectModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-red-50 flex items-center justify-center">
                    <i data-lucide="x-circle" class="w-5 h-5 text-red-600"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-900">Tolak Pengisian Survey</h3>
                    <p class="text-xs text-gray-400">Pilih alasan penolakan</p>
                </div>
            </div>
            <button onclick="closeRejectModal()" class="p-1.5 rounded-lg hover:bg-gray-100 transition">
                <i data-lucide="x" class="w-4 h-4 text-gray-500"></i>
            </button>
        </div>
        <form id="rejectForm" method="POST" action="">
            @csrf
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">
                        Alasan Penolakan <span class="text-red-500">*</span>
                    </label>
                    <select name="rejection_reason_id" required
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-red-400 outline-none">
                        <option value="">— Pilih Alasan —</option>
                        @foreach($rejectionReasons as $reason)
                            <option value="{{ $reason->id }}">{{ $reason->label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">
                        Catatan Tambahan <span class="text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <textarea name="notes" rows="3"
                        class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-red-400 outline-none resize-none"
                        placeholder="Catatan untuk responden..."></textarea>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                <button type="button" onclick="closeRejectModal()"
                    class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-white transition">
                    Batal
                </button>
                <button type="submit"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition">
                    <i data-lucide="x" class="w-4 h-4"></i> Tolak Pengisian
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });

    function openRejectModal(id) {
        const modal = document.getElementById('rejectModal');
        const form = document.getElementById('rejectForm');
        form.action = '/admin/survey-fillings/' + id + '/reject';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeRejectModal() {
        const modal = document.getElementById('rejectModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endpush
@endsection
