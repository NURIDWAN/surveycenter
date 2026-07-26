@extends('layouts.responden')

@section('page-title', 'Tarik Saldo')
@section('page-description', 'Ajukan penarikan saldo Anda')

@section('content')

<div class="max-w-2xl mx-auto">

    {{-- Saldo Info Card --}}
    <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-orange-50 border border-orange-100 flex items-center justify-center flex-shrink-0">
                <i data-lucide="wallet" class="w-5 h-5 text-orange-500"></i>
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="text-sm font-bold text-gray-900 mb-3">Saldo Anda</h3>

                <div class="space-y-2 mb-3">
                    <div class="flex items-center justify-between px-3 py-2.5 rounded-lg bg-emerald-50 border border-emerald-100">
                        <span class="text-xs text-emerald-700 font-medium">Saldo Reward (dapat ditarik)</span>
                        <span class="text-lg font-extrabold text-emerald-700">{{ \App\Helpers\RupiahHelper::formatRupiah($rewardBalance) }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-amber-50 border border-amber-100">
                    <i data-lucide="info" class="w-4 h-4 text-amber-500 flex-shrink-0"></i>
                    <p class="text-xs text-amber-700 font-medium">Minimum penarikan: <span class="font-bold">{{ \App\Helpers\RupiahHelper::formatRupiah($minThreshold) }}</span></p>
                </div>
            </div>
        </div>
    </div>

    {{-- Withdrawal Form --}}
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-sm font-bold text-gray-900">Form Penarikan Saldo</h2>
            <p class="text-xs text-gray-400 mt-0.5">Isi data rekening tujuan penarikan</p>
        </div>

        <form action="{{ route('responden.withdrawals.store') }}" method="POST" class="p-6 space-y-5">
            @csrf

            {{-- Amount --}}
            <div>
                <label for="amount" class="block text-xs font-semibold text-gray-700 mb-2">
                    Jumlah Penarikan (Rp) <span class="text-red-500">*</span>
                </label>
                <input type="number"
                       name="amount"
                       id="amount"
                       min="{{ $minThreshold }}"
                       max="{{ $rewardBalance }}"
                       value="{{ old('amount') }}"
                       placeholder="Masukkan jumlah penarikan"
                       class="block w-full rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-sm text-gray-700 placeholder-gray-400 focus:border-orange-300 focus:bg-white focus:ring-2 focus:ring-orange-500/10 transition @error('amount') border-red-300 bg-red-50/50 @enderror">
                @error('amount')
                    <p class="mt-1.5 text-xs text-red-600 font-medium flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3 h-3"></i>
                        {{ $message }}
                    </p>
                @enderror
                <p class="mt-1.5 text-[11px] text-gray-400">Minimum {{ \App\Helpers\RupiahHelper::formatRupiah($minThreshold) }}, maksimum saldo reward Anda saat ini</p>
            </div>

            {{-- Provider Name --}}
            <div>
                <label for="provider_name" class="block text-xs font-semibold text-gray-700 mb-2">
                    Nama Provider / Bank <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="provider_name"
                       id="provider_name"
                       value="{{ old('provider_name') }}"
                       placeholder="Contoh: BCA, BNI, GoPay, OVO, DANA"
                       maxlength="100"
                       class="block w-full rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-sm text-gray-700 placeholder-gray-400 focus:border-orange-300 focus:bg-white focus:ring-2 focus:ring-orange-500/10 transition @error('provider_name') border-red-300 bg-red-50/50 @enderror">
                @error('provider_name')
                    <p class="mt-1.5 text-xs text-red-600 font-medium flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3 h-3"></i>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Account Number --}}
            <div>
                <label for="account_number" class="block text-xs font-semibold text-gray-700 mb-2">
                    Nomor Rekening / Akun <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="account_number"
                       id="account_number"
                       value="{{ old('account_number') }}"
                       placeholder="Masukkan nomor rekening atau nomor akun"
                       maxlength="50"
                       class="block w-full rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-sm text-gray-700 placeholder-gray-400 focus:border-orange-300 focus:bg-white focus:ring-2 focus:ring-orange-500/10 transition @error('account_number') border-red-300 bg-red-50/50 @enderror">
                @error('account_number')
                    <p class="mt-1.5 text-xs text-red-600 font-medium flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3 h-3"></i>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Account Holder Name --}}
            <div>
                <label for="account_holder_name" class="block text-xs font-semibold text-gray-700 mb-2">
                    Nama Pemilik Rekening <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="account_holder_name"
                       id="account_holder_name"
                       value="{{ old('account_holder_name') }}"
                       placeholder="Masukkan nama sesuai rekening"
                       maxlength="255"
                       class="block w-full rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-sm text-gray-700 placeholder-gray-400 focus:border-orange-300 focus:bg-white focus:ring-2 focus:ring-orange-500/10 transition @error('account_holder_name') border-red-300 bg-red-50/50 @enderror">
                @error('account_holder_name')
                    <p class="mt-1.5 text-xs text-red-600 font-medium flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3 h-3"></i>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Submit Button --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-sm font-bold shadow-md shadow-orange-500/10 hover:shadow-lg hover:scale-[1.02] transition-all">
                    <i data-lucide="banknote" class="w-4 h-4"></i>
                    Ajukan Penarikan
                </button>
                <a href="{{ route('responden.withdrawals.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-sm font-medium text-gray-600 hover:text-gray-800 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>

</div>

@endsection
