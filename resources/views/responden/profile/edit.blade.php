@extends('layouts.responden')

@section('page-title', 'Profil Demografis')
@section('page-description', 'Lengkapi data diri Anda agar sistem dapat mencocokkan survei yang relevan')

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- Header Card --}}
    <div class="mb-6 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-orange-50 border border-orange-100 flex items-center justify-center">
            <i data-lucide="user-circle" class="w-5 h-5 text-orange-500"></i>
        </div>
        <div>
            <h2 class="text-lg font-bold text-gray-900">Profil Demografis</h2>
            <p class="text-sm text-gray-500">Lengkapi data untuk mendapatkan survei yang sesuai dengan profil Anda.</p>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <form method="POST" action="{{ route('responden.profile.update') }}" class="p-6 sm:p-8 space-y-6">
            @csrf
            @method('PUT')

            {{-- Tanggal Lahir --}}
            <div>
                <label for="tanggal_lahir" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Tanggal Lahir
                </label>
                <input
                    type="date"
                    id="tanggal_lahir"
                    name="tanggal_lahir"
                    value="{{ old('tanggal_lahir', $user->tanggal_lahir?->format('Y-m-d')) }}"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-400 transition @error('tanggal_lahir') border-red-300 bg-red-50/30 @enderror"
                >
                @error('tanggal_lahir')
                    <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Jenis Kelamin --}}
            <div>
                <label for="jenis_kelamin" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Jenis Kelamin
                </label>
                <select
                    id="jenis_kelamin"
                    name="jenis_kelamin"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-400 transition @error('jenis_kelamin') border-red-300 bg-red-50/30 @enderror"
                >
                    <option value="">— Pilih Jenis Kelamin —</option>
                    <option value="Laki-laki" {{ old('jenis_kelamin', $user->jenis_kelamin) === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="Perempuan" {{ old('jenis_kelamin', $user->jenis_kelamin) === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                </select>
                @error('jenis_kelamin')
                    <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Provinsi --}}
            <div>
                <label for="provinsi" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Provinsi
                </label>
                <input
                    type="text"
                    id="provinsi"
                    name="provinsi"
                    value="{{ old('provinsi', $user->provinsi) }}"
                    placeholder="Contoh: Jawa Barat"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-400 transition @error('provinsi') border-red-300 bg-red-50/30 @enderror"
                >
                @error('provinsi')
                    <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Kota --}}
            <div>
                <label for="kota" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Kota / Kabupaten
                </label>
                <input
                    type="text"
                    id="kota"
                    name="kota"
                    value="{{ old('kota', $user->kota) }}"
                    placeholder="Contoh: Bandung"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-400 transition @error('kota') border-red-300 bg-red-50/30 @enderror"
                >
                @error('kota')
                    <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Pendidikan --}}
            <div>
                <label for="pendidikan" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Pendidikan Terakhir
                </label>
                <input
                    type="text"
                    id="pendidikan"
                    name="pendidikan"
                    value="{{ old('pendidikan', $user->pendidikan) }}"
                    placeholder="Contoh: S1"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-400 transition @error('pendidikan') border-red-300 bg-red-50/30 @enderror"
                >
                @error('pendidikan')
                    <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Pekerjaan --}}
            <div>
                <label for="pekerjaan" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Pekerjaan
                </label>
                <input
                    type="text"
                    id="pekerjaan"
                    name="pekerjaan"
                    value="{{ old('pekerjaan', $user->pekerjaan) }}"
                    placeholder="Contoh: Karyawan Swasta"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-400 transition @error('pekerjaan') border-red-300 bg-red-50/30 @enderror"
                >
                @error('pekerjaan')
                    <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Info Note --}}
            <div class="flex items-start gap-2.5 p-3.5 rounded-xl bg-amber-50/70 border border-amber-200/60">
                <i data-lucide="info" class="w-4 h-4 text-amber-500 mt-0.5 flex-shrink-0"></i>
                <p class="text-xs text-amber-700 leading-relaxed">
                    Semua kolom bersifat opsional. Anda dapat mengisi sebagian data dan melengkapinya nanti. Data profil digunakan untuk mencocokkan survei yang sesuai.
                </p>
            </div>

            {{-- Submit Button --}}
            <div class="pt-2">
                <button
                    type="submit"
                    class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold shadow-md shadow-orange-500/10 hover:shadow-lg hover:shadow-orange-500/20 transition-all hover:scale-[1.01] flex items-center justify-center gap-2"
                >
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Simpan Profil
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
