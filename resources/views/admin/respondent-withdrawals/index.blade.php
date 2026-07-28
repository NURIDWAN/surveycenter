@extends('layouts.crm')

@section('title', 'Penarikan Responden')
@section('page-title', 'Penarikan Responden')

@section('content')
<div class="space-y-5">

    {{-- Header & Stats --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Permintaan Penarikan Saldo Responden</h2>
            <p class="text-sm text-gray-500 mt-0.5">Kelola permintaan penarikan saldo reward dari responden</p>
        </div>
        <div class="flex items-center gap-3">
            @if($pendingCount > 0)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-100 text-amber-700 text-xs font-bold rounded-full">
                    <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                    {{ $pendingCount }} Pending
                </span>
            @endif
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-full border border-emerald-100">
                <i data-lucide="banknote" class="w-3.5 h-3.5"></i>
                Total Disetujui: Rp {{ number_format($totalApproved, 0, ',', '.') }}
            </span>
        </div>
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

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('admin.respondent-withdrawals.index') }}"
          class="bg-white rounded-xl border border-gray-200 p-4 flex flex-col sm:flex-row gap-3">
        <div class="flex-1 relative">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Cari nama / email responden..."
                class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none">
        </div>
        <select name="status" class="px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none">
            <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>Semua Status</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
        </select>
        <button type="submit"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-orange-600 text-white rounded-lg text-sm font-medium hover:bg-orange-700 transition">
            <i data-lucide="filter" class="w-4 h-4"></i>
            Filter
        </button>
        <a href="{{ route('admin.respondent-withdrawals.index') }}"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
            Reset
        </a>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Responden</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Jumlah</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Tujuan Transfer</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Tanggal Ajuan</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($withdrawals as $wd)
                    <tr class="hover:bg-gray-50 transition {{ $wd->status === 'pending' ? 'bg-amber-50/20' : '' }}">
                        <td class="px-4 py-3.5">
                            <p class="font-medium text-gray-900">{{ $wd->user->name ?? '-' }}</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">{{ $wd->user->email ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="text-base font-bold text-gray-900">Rp {{ number_format($wd->amount, 0, ',', '.') }}</span>
                        </td>
                        <td class="px-4 py-3.5">
                            <p class="font-medium text-gray-800">{{ $wd->provider_name }}</p>
                            <p class="text-[11px] text-gray-500 mt-0.5">{{ $wd->account_number }}</p>
                            <p class="text-[11px] text-gray-400">a.n. {{ $wd->account_holder_name }}</p>
                        </td>
                        <td class="px-4 py-3.5 text-gray-500 text-xs">
                            {{ $wd->created_at->format('d M Y') }}<br>
                            <span class="text-gray-400">{{ $wd->created_at->format('H:i') }}</span>
                        </td>
                        <td class="px-4 py-3.5">
                            @if($wd->status === 'approved')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700">
                                    <i data-lucide="check-circle" class="w-3 h-3"></i> Disetujui
                                </span>
                                @if($wd->processed_at)
                                    <p class="text-[10px] text-gray-400 mt-1">{{ $wd->processed_at->format('d M Y H:i') }}</p>
                                @endif
                            @elseif($wd->status === 'rejected')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-red-100 text-red-700">
                                    <i data-lucide="x-circle" class="w-3 h-3"></i> Ditolak
                                </span>
                                @if($wd->notes)
                                    <p class="text-[10px] text-red-400 mt-1">{{ $wd->notes }}</p>
                                @endif
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-700">
                                    <i data-lucide="clock" class="w-3 h-3"></i> Pending
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-right">
                            @if($wd->status === 'pending')
                                <div class="flex items-center justify-end gap-2">
                                    <form method="POST"
                                        action="{{ route('admin.respondent-withdrawals.approve', $wd) }}"
                                        onsubmit="return confirm('Setujui penarikan Rp {{ number_format($wd->amount, 0, ',', '.') }} untuk {{ $wd->user->name ?? '-' }}?')">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-lg hover:bg-emerald-700 transition">
                                            <i data-lucide="check" class="w-3 h-3"></i> Setujui
                                        </button>
                                    </form>
                                    <button type="button"
                                        onclick="openRejectModal({{ $wd->id }})"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-red-600 text-white text-xs font-medium rounded-lg hover:bg-red-700 transition">
                                        <i data-lucide="x" class="w-3 h-3"></i> Tolak
                                    </button>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-16 text-center">
                            <i data-lucide="inbox" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                            <p class="text-sm text-gray-400 font-medium">Belum ada permintaan penarikan.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($withdrawals->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $withdrawals->withQueryString()->links() }}
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
                    <h3 class="text-sm font-bold text-gray-900">Tolak Permintaan Penarikan</h3>
                    <p class="text-xs text-gray-400">Berikan alasan penolakan (opsional)</p>
                </div>
            </div>
            <button onclick="closeRejectModal()" class="p-1.5 rounded-lg hover:bg-gray-100 transition">
                <i data-lucide="x" class="w-4 h-4 text-gray-500"></i>
            </button>
        </div>
        <form id="rejectForm" method="POST" action="">
            @csrf
            <div class="p-6">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Alasan Penolakan</label>
                <textarea name="notes" rows="3"
                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent resize-none"
                    placeholder="Contoh: Nomor rekening tidak valid..."></textarea>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                <button type="button" onclick="closeRejectModal()"
                    class="px-4 py-2 text-sm font-medium text-gray-600 border border-gray-300 rounded-lg hover:bg-white transition">
                    Batal
                </button>
                <button type="submit"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition">
                    <i data-lucide="x" class="w-4 h-4"></i> Tolak Permintaan
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
        form.action = '/admin/respondent-withdrawals/' + id + '/reject';
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
