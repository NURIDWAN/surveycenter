@extends('layouts.admin')

@section('title', 'Detail Transaksi #' . $transaction->id)
@section('page-title', 'Detail Transaksi')

@section('content')
<div class="max-w-4xl mx-auto space-y-5">

    {{-- Back --}}
    <a href="{{ route('admin.transactions.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 transition font-medium">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        Kembali ke Daftar Transaksi
    </a>

    {{-- Flash --}}
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 flex-shrink-0"></i>
            <p class="text-sm text-emerald-700 font-medium">{{ session('success') }}</p>
        </div>
    @endif
    @if(session('error') || $errors->any())
        <div class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 flex-shrink-0"></i>
            <div>
                @if(session('error'))
                    <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
                @endif
                @foreach($errors->all() as $error)
                    <p class="text-sm text-red-700 font-medium">{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- LEFT: Transaction Details --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Transaction Info --}}
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Transaksi #{{ $transaction->id }}</h3>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $transaction->created_at?->format('d M Y, H:i:s') }}</p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $transaction->statusBadgeClass() }}">
                        {{ $transaction->statusLabel() }}
                    </span>
                </div>
                <div class="p-5 grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Jumlah</p>
                        <p class="text-xl font-bold text-gray-900">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Metode Bayar</p>
                        <p class="text-sm font-medium text-gray-700">{{ $transaction->payment_method ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-0.5">SingaPay Ref</p>
                        <p class="text-xs font-mono text-gray-600 break-all">{{ $transaction->singapay_ref ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Progress</p>
                        <div class="flex items-center gap-2">
                            <div class="h-2 flex-1 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $transaction->safeProgress() }}%"></div>
                            </div>
                            <span class="text-xs font-semibold text-gray-600">{{ $transaction->safeProgress() }}%</span>
                        </div>
                    </div>
                    @if($transaction->bill_no)
                    <div>
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Bill No</p>
                        <p class="text-xs font-mono text-gray-600">{{ $transaction->bill_no }}</p>
                    </div>
                    @endif
                    @if($transaction->trx_id)
                    <div>
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-0.5">TRX ID</p>
                        <p class="text-xs font-mono text-gray-600">{{ $transaction->trx_id }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Survey Info --}}
            @if($transaction->survey)
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-900">Informasi Survey</h3>
                </div>
                <div class="p-5 space-y-3">
                    <div>
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Judul</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $transaction->survey->title }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Pertanyaan</p>
                            <p class="text-sm text-gray-700">{{ $transaction->survey->question_count ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Target Responden</p>
                            <p class="text-sm text-gray-700">{{ $transaction->survey->respondent_count ?? '—' }}</p>
                        </div>
                    </div>
                    @if($transaction->survey->form_link)
                    <div>
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Link Form</p>
                        <a href="{{ $transaction->survey->form_link }}" target="_blank"
                           class="inline-flex items-center gap-1 text-xs font-semibold text-orange-600 hover:text-orange-700">
                            <i data-lucide="external-link" class="w-3 h-3"></i>
                            Buka Form
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @endif

        </div>

        {{-- RIGHT: Actions --}}
        <div class="space-y-5">

            {{-- User Info --}}
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-900">Pemesan</h3>
                </div>
                <div class="p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-orange-400 to-amber-500 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                            {{ strtoupper(substr($transaction->user->name ?? 'U', 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $transaction->user->name ?? '—' }}</p>
                            <p class="text-xs text-gray-400">{{ $transaction->user->email ?? '—' }}</p>
                        </div>
                    </div>
                    @if($transaction->user->phone ?? null)
                        <p class="text-xs text-gray-500">{{ $transaction->user->phone }}</p>
                    @endif
                </div>
            </div>

            {{-- ─── Manual Status Update ─── --}}
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-900">Ubah Status Pembayaran</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Update manual status transaksi</p>
                </div>
                <form action="{{ route('admin.transactions.update', $transaction) }}" method="POST" class="p-5 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Status</label>
                        <select name="status" required
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-400 outline-none">
                            @foreach([
                                'pending' => 'Menunggu Pembayaran',
                                'processing' => 'Verifikasi',
                                'paid' => 'Dibayar',
                                'failed' => 'Gagal',
                            ] as $value => $label)
                                <option value="{{ $value }}" {{ $transaction->status === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <input type="hidden" name="amount" value="{{ $transaction->amount }}">
                    <input type="hidden" name="survey_id" value="{{ $transaction->survey_id }}">
                    <input type="hidden" name="user_id" value="{{ $transaction->user_id }}">
                    <input type="hidden" name="payment_method" value="{{ $transaction->payment_method }}">
                    <input type="hidden" name="singapay_ref" value="{{ $transaction->singapay_ref }}">

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">
                            Catatan
                            <span class="font-normal text-gray-400">(opsional)</span>
                        </label>
                        <textarea name="admin_note" rows="2"
                            class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-400 outline-none resize-none"
                            placeholder="Alasan perubahan status..."></textarea>
                    </div>

                    <button type="submit"
                        onclick="return confirm('Yakin ingin mengubah status transaksi ini?')"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-orange-600 text-white text-sm font-semibold rounded-xl hover:bg-orange-700 transition shadow-sm">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Simpan Perubahan
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>
@endpush
@endsection
