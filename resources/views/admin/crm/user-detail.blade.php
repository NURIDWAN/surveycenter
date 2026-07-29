@extends('layouts.crm')

@section('title', 'Detail User')
@section('page-title', 'Detail User')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">{{ $user->name }}</h2>
            <p class="text-sm text-gray-500 mt-1">{{ $user->email }} @if($user->phone) • {{ $user->phone }} @endif</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('crm.manage-users.toggle-responden', $user) }}">
                @csrf
                @method('PATCH')
                @if($user->is_responden)
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold rounded-lg hover:bg-emerald-100 transition" title="Klik untuk nonaktifkan akses responden">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Responden: Aktif
                    </button>
                @else
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 bg-gray-100 text-gray-600 border border-gray-200 text-xs font-semibold rounded-lg hover:bg-gray-200 transition" title="Klik untuk aktifkan akses responden">
                        <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                        Responden: Nonaktif
                    </button>
                @endif
            </form>
            @if(!$user->is_admin)
                <form method="POST" action="{{ route('admin.users.impersonate', $user) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        Login User
                    </button>
                </form>
            @endif
            <a href="{{ route('crm.manage-users') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Kembali
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Total Survey</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_surveys'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Total Transaksi</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_transactions'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Transaksi Berhasil</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['total_paid_transactions'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Total Dibayar</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">Rp {{ number_format($stats['total_paid_amount'], 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" action="{{ route('crm.manage-users.show', $user) }}" class="flex flex-col sm:flex-row gap-2 sm:items-end">
            <div>
                <label class="block text-xs text-gray-600 mb-1">Filter Status Transaksi</label>
                <select name="trx_status" class="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none">
                    <option value="all" {{ $trxStatus === 'all' ? 'selected' : '' }}>Semua</option>
                    <option value="pending" {{ $trxStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ $trxStatus === 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="paid" {{ $trxStatus === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="failed" {{ $trxStatus === 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 bg-orange-600 text-white text-sm font-medium rounded-lg hover:bg-orange-700 transition">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    Terapkan
                </button>
                <a href="{{ route('crm.manage-users.show', $user) }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Survey</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jumlah</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($transactions as $transaction)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3.5 text-gray-500 text-xs">{{ $transaction->created_at->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3.5 text-gray-900 font-medium">{{ $transaction->survey->title ?? 'Survey' }}</td>
                            <td class="px-4 py-3.5 font-semibold text-gray-900">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $transaction->statusBadgeClass() }}">
                                    {{ $transaction->statusLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <a href="{{ route('admin.transactions.show', $transaction) }}" target="_blank"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-orange-50 text-orange-700 text-xs font-medium rounded-lg hover:bg-orange-100 transition">
                                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                    Lihat
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-12">
                                <i data-lucide="inbox" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
                                <p class="text-sm text-gray-500">Belum ada transaksi untuk user ini</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($transactions->hasPages())
        <div>{{ $transactions->links() }}</div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>
@endpush
