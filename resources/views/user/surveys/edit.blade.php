@extends('layouts.user')

@section('title', 'Edit Survey')
@section('page-title', 'Edit Survey')
@section('page-description', 'Perbarui informasi survey Anda')

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- Back Button --}}
    <div class="mb-6">
        <a href="{{ route('user.surveys.show', $survey) }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-orange-600 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Detail Survey
        </a>
    </div>

    {{-- Form Card --}}
    <div class="bg-white rounded-xl border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Edit Survey</h2>
            <p class="text-sm text-gray-500 mt-1">Perbarui informasi survey berikut</p>
        </div>

        <form method="POST" action="{{ route('user.surveys.update', $survey) }}" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            {{-- Title --}}
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    Judul Survey <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" id="title" value="{{ old('title', $survey->title) }}" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition @error('title') border-red-500 @enderror"
                    placeholder="Contoh: Survei Kepuasan Pelanggan 2024">
                @error('title')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Reward & Scheduling --}}
            <div class="border-t border-gray-100 pt-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i data-lucide="gift" class="w-4 h-4 text-orange-600"></i>
                    Reward & Penjadwalan Responden
                </h3>
                <p class="text-xs text-gray-500 mb-4">Opsional — isi jika Anda ingin survey ini tersedia untuk responden di Survey Center Indonesia.</p>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    {{-- Reward Amount --}}
                    <div>
                        <label for="reward_amount" class="block text-sm font-medium text-gray-700 mb-2">
                            Reward (Rp)
                        </label>
                        <input type="number" name="reward_amount" id="reward_amount" value="{{ old('reward_amount', $survey->reward_amount ?? 0) }}"
                            min="0" step="1000"
                            class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition @error('reward_amount') border-red-500 @enderror"
                            placeholder="Contoh: 5000">
                        <p class="mt-1.5 text-xs text-gray-500">Jumlah reward per responden (Rupiah)</p>
                        @error('reward_amount')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Deadline --}}
                    <div>
                        <label for="deadline" class="block text-sm font-medium text-gray-700 mb-2">
                            Deadline
                        </label>
                        <input type="datetime-local" name="deadline" id="deadline"
                            value="{{ old('deadline', $survey->deadline ? $survey->deadline->format('Y-m-d\TH:i') : '') }}"
                            class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition @error('deadline') border-red-500 @enderror">
                        <p class="mt-1.5 text-xs text-gray-500">Batas waktu pengisian survey</p>
                        @error('deadline')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Estimated Time --}}
                    <div>
                        <label for="estimated_time_minutes" class="block text-sm font-medium text-gray-700 mb-2">
                            Estimasi Waktu (menit)
                        </label>
                        <input type="number" name="estimated_time_minutes" id="estimated_time_minutes"
                            value="{{ old('estimated_time_minutes', $survey->estimated_time_minutes) }}"
                            min="1"
                            class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition @error('estimated_time_minutes') border-red-500 @enderror"
                            placeholder="Contoh: 10">
                        <p class="mt-1.5 text-xs text-gray-500">Estimasi waktu pengisian (menit)</p>
                        @error('estimated_time_minutes')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Eligibility Criteria --}}
            @php
                $ec = $survey->eligibility_criteria ?? [];
            @endphp
            <div class="border-t border-gray-100 pt-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i data-lucide="filter" class="w-4 h-4 text-orange-600"></i>
                    Kriteria Kelayakan Responden
                </h3>
                <p class="text-xs text-gray-500 mb-4">Opsional — kosongkan jika survey terbuka untuk semua responden.</p>

                <div class="space-y-5">
                    {{-- Jenis Kelamin --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Kelamin</label>
                        <div class="flex items-center gap-4">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="ec_jenis_kelamin[]" value="Laki-laki"
                                    {{ (is_array(old('ec_jenis_kelamin', $ec['jenis_kelamin'] ?? [])) && in_array('Laki-laki', old('ec_jenis_kelamin', $ec['jenis_kelamin'] ?? []))) ? 'checked' : '' }}
                                    class="w-4 h-4 rounded border-gray-300 text-orange-500 focus:ring-orange-400">
                                Laki-laki
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="ec_jenis_kelamin[]" value="Perempuan"
                                    {{ (is_array(old('ec_jenis_kelamin', $ec['jenis_kelamin'] ?? [])) && in_array('Perempuan', old('ec_jenis_kelamin', $ec['jenis_kelamin'] ?? []))) ? 'checked' : '' }}
                                    class="w-4 h-4 rounded border-gray-300 text-orange-500 focus:ring-orange-400">
                                Perempuan
                            </label>
                        </div>
                        <p class="mt-1.5 text-xs text-gray-500">Kosongkan jika tidak ada batasan jenis kelamin</p>
                    </div>

                    {{-- Age Range --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="ec_age_min" class="block text-sm font-medium text-gray-700 mb-2">Usia Minimum</label>
                            <input type="number" name="ec_age_min" id="ec_age_min"
                                value="{{ old('ec_age_min', $ec['age_min'] ?? '') }}"
                                min="0" max="100"
                                class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition @error('ec_age_min') border-red-500 @enderror"
                                placeholder="Contoh: 18">
                            @error('ec_age_min')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="ec_age_max" class="block text-sm font-medium text-gray-700 mb-2">Usia Maksimum</label>
                            <input type="number" name="ec_age_max" id="ec_age_max"
                                value="{{ old('ec_age_max', $ec['age_max'] ?? '') }}"
                                min="0" max="100"
                                class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition @error('ec_age_max') border-red-500 @enderror"
                                placeholder="Contoh: 35">
                            @error('ec_age_max')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Provinsi --}}
                    <div>
                        <label for="ec_provinsi" class="block text-sm font-medium text-gray-700 mb-2">Provinsi</label>
                        <input type="text" name="ec_provinsi" id="ec_provinsi"
                            value="{{ old('ec_provinsi', is_array($ec['provinsi'] ?? null) ? implode(', ', $ec['provinsi']) : '') }}"
                            class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition @error('ec_provinsi') border-red-500 @enderror"
                            placeholder="Contoh: Jawa Barat, DKI Jakarta, Jawa Timur">
                        <p class="mt-1.5 text-xs text-gray-500">Pisahkan dengan koma jika lebih dari satu</p>
                        @error('ec_provinsi')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kota --}}
                    <div>
                        <label for="ec_kota" class="block text-sm font-medium text-gray-700 mb-2">Kota/Kabupaten</label>
                        <input type="text" name="ec_kota" id="ec_kota"
                            value="{{ old('ec_kota', is_array($ec['kota'] ?? null) ? implode(', ', $ec['kota']) : '') }}"
                            class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition @error('ec_kota') border-red-500 @enderror"
                            placeholder="Contoh: Bandung, Jakarta Selatan">
                        <p class="mt-1.5 text-xs text-gray-500">Pisahkan dengan koma jika lebih dari satu</p>
                        @error('ec_kota')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Pendidikan --}}
                    <div>
                        <label for="ec_pendidikan" class="block text-sm font-medium text-gray-700 mb-2">Pendidikan</label>
                        <input type="text" name="ec_pendidikan" id="ec_pendidikan"
                            value="{{ old('ec_pendidikan', is_array($ec['pendidikan'] ?? null) ? implode(', ', $ec['pendidikan']) : '') }}"
                            class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition @error('ec_pendidikan') border-red-500 @enderror"
                            placeholder="Contoh: S1, S2, SMA">
                        <p class="mt-1.5 text-xs text-gray-500">Pisahkan dengan koma jika lebih dari satu</p>
                        @error('ec_pendidikan')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Pekerjaan --}}
                    <div>
                        <label for="ec_pekerjaan" class="block text-sm font-medium text-gray-700 mb-2">Pekerjaan</label>
                        <input type="text" name="ec_pekerjaan" id="ec_pekerjaan"
                            value="{{ old('ec_pekerjaan', is_array($ec['pekerjaan'] ?? null) ? implode(', ', $ec['pekerjaan']) : '') }}"
                            class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition @error('ec_pekerjaan') border-red-500 @enderror"
                            placeholder="Contoh: Mahasiswa, Karyawan Swasta, PNS">
                        <p class="mt-1.5 text-xs text-gray-500">Pisahkan dengan koma jika lebih dari satu</p>
                        @error('ec_pekerjaan')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Submit Button --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('user.surveys.show', $survey) }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                    Batal
                </a>
                <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-orange-600 rounded-lg hover:bg-orange-700 transition shadow-sm">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
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
