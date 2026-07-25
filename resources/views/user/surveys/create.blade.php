@extends('layouts.user')

@section('title', 'Buat Survey Baru')
@section('page-title', 'Buat Survey Baru')
@section('page-description', 'Buat survey baru untuk mengumpulkan data dari responden')

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- Back Button --}}
    <div class="mb-6">
        <a href="{{ route('user.surveys.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-orange-600 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Daftar Survey
        </a>
    </div>

    {{-- Form Card --}}
    <div class="bg-white rounded-xl border border-gray-200/80 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Informasi Survey</h2>
            <p class="text-sm text-gray-500 mt-1">Lengkapi form berikut untuk membuat survey baru</p>
        </div>

        <div class="px-6 pt-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-2">
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                    <p class="text-[11px] text-gray-500">Volume Pricing (per soal/orang)</p>
                    <div class="mt-1 space-y-0.5 text-xs">
                        @php $vTiers = \App\Helpers\VolumePricing::getTiers(); $prev = 1; @endphp
                        @foreach($vTiers as $tier)
                        <p class="{{ $loop->last ? 'text-emerald-600 font-bold' : ($loop->first ? 'text-gray-700' : 'text-orange-600') }}">
                            <span class="font-semibold">{{ $tier['max'] === null ? '≥ ' . number_format($prev, 0, ',', '.') : number_format($prev, 0, ',', '.') . '–' . number_format($tier['max'], 0, ',', '.') }}:</span>
                            Rp {{ number_format($tier['price'], 0, ',', '.') }}
                        </p>
                        @php if($tier['max'] !== null) $prev = $tier['max'] + 1; @endphp
                        @endforeach
                    </div>
                </div>
                <div class="rounded-lg border border-red-200 bg-red-50 p-3">
                    <p class="text-[11px] text-red-600">Minimum Order</p>
                    <p class="text-sm font-bold text-red-700">Rp {{ number_format(\App\Helpers\VolumePricing::getMinOrder(), 0, ',', '.') }} / survey</p>
                    <p class="text-[11px] text-red-600">Wajib saat checkout</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('user.surveys.store') }}" class="p-6 space-y-6">
            @csrf

            {{-- Title --}}
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    Judul Survey <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition @error('title') border-red-500 @enderror"
                    placeholder="Contoh: Survei Kepuasan Pelanggan 2024">
                @error('title')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Deskripsi
                </label>
                <textarea name="description" id="description" rows="3"
                    class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition resize-none @error('description') border-red-500 @enderror"
                    placeholder="Deskripsi singkat tentang survey ini (opsional)">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Question & Respondent Count --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                {{-- Question Count --}}
                <div>
                    <label for="question_count" class="block text-sm font-medium text-gray-700 mb-2">
                        Jumlah Pertanyaan <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="question_count" id="question_count" value="{{ old('question_count', 10) }}" 
                        min="1" max="100" required
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition @error('question_count') border-red-500 @enderror"
                        placeholder="Contoh: 10">
                    <p class="mt-1.5 text-xs text-gray-500">Minimal 1, maksimal 100 pertanyaan</p>
                    @error('question_count')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Respondent Count --}}
                <div>
                    <label for="respondent_count" class="block text-sm font-medium text-gray-700 mb-2">
                        Jumlah Responden <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="respondent_count" id="respondent_count" value="{{ old('respondent_count', 100) }}" 
                        min="1" max="10000" required
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition @error('respondent_count') border-red-500 @enderror"
                        placeholder="Contoh: 100">
                    <p class="mt-1.5 text-xs text-gray-500">Minimal 1, maksimal 10,000 responden</p>
                    @error('respondent_count')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Survey Link --}}
            <div>
                <label for="form_link" class="block text-sm font-medium text-gray-700 mb-2">
                    Link Survey <span class="text-red-500">*</span>
                </label>
                <input type="url" name="form_link" id="form_link" value="{{ old('form_link') }}" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition @error('form_link') border-red-500 @enderror"
                    placeholder="https://docs.google.com/forms/...">
                <p class="mt-1.5 text-xs text-gray-500">
                    Link wajib diisi. Sistem memvalidasi URL form dan memastikan judul form sama dengan judul survey.
                    Platform didukung: Google Forms, Microsoft Forms, Typeform, Jotform, Tally, Formstack.
                </p>

                <div class="mt-3 flex items-center gap-2">
                    <button type="button" id="analyzeFormButton"
                        class="inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100 transition">
                        Analisa AI Form
                    </button>
                    <span id="analyzeFormLoading" class="hidden text-xs text-blue-600">Menganalisa...</span>
                </div>

                <div id="aiAnalyzerCard" class="hidden mt-3 rounded-xl border border-gray-200 bg-gray-50 p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">AI Form Analyzer</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-[11px] text-gray-500">Judul Form</p>
                        <p id="aiDetectedTitle" class="text-sm font-semibold text-gray-900">-</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="rounded-lg border border-gray-200 bg-white p-3">
                            <p class="text-[11px] text-gray-500">Persentase Kemiripan Judul</p>
                            <p id="aiTitlePercent" class="text-base font-bold text-gray-900">-</p>
                            <p id="aiTitleStatus" class="text-xs mt-1"></p>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-white p-3">
                            <p class="text-[11px] text-gray-500">Jumlah Soal (Input vs Form)</p>
                            <p id="aiQuestionCount" class="text-base font-bold text-gray-900">-</p>
                            <p id="aiQuestionStatus" class="text-xs mt-1"></p>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-3">
                        <p class="text-[11px] text-gray-500">Review Pertanyaan</p>
                        <p id="aiQuestionReview" class="text-sm font-medium text-gray-800 mt-1">-</p>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-3">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-[11px] text-gray-500">Pertanyaan Terdeteksi</p>
                            <span id="aiDetectedQuestionsMeta" class="text-[11px] text-gray-400">0 item</span>
                        </div>
                        <input type="text" id="aiDetectedQuestionsSearch"
                            class="hidden mt-2 w-full rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs focus:border-blue-400 focus:ring-blue-400"
                            placeholder="Cari pertanyaan...">
                        <ul id="aiDetectedQuestions" class="mt-1 list-disc list-inside text-xs text-gray-700 space-y-1">
                            <li>-</li>
                        </ul>
                        <button type="button" id="aiDetectedQuestionsToggle"
                            class="hidden mt-2 text-[11px] font-semibold text-blue-600 hover:text-blue-700">
                            Lihat semua
                        </button>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-3">
                        <p class="text-[11px] text-gray-500 mb-2">Tipe Soal Terdeteksi</p>
                        <div id="aiQuestionTypeSummary" class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-1 text-[11px] text-gray-600">Belum ada data</span>
                        </div>
                    </div>

                    <details class="rounded-lg border border-gray-200 bg-white p-3">
                        <summary class="cursor-pointer text-[11px] font-semibold text-gray-600">Debug Analyzer</summary>
                        <div class="mt-2 space-y-1 text-[11px] text-gray-600">
                            <p>Entry ID terdeteksi: <span id="aiDebugEntryCount" class="font-semibold text-gray-800">0</span></p>
                            <p>Load data tersedia: <span id="aiDebugLoadData" class="font-semibold text-gray-800">Tidak</span></p>
                            <p>Judul pertanyaan terpetakan: <span id="aiDebugQuestionTitleCount" class="font-semibold text-gray-800">0</span></p>
                            <p class="break-all">Entry IDs: <span id="aiDebugEntryIds" class="font-mono text-[10px] text-gray-700">-</span></p>
                        </div>
                    </details>

                    <p id="aiAnalyzerError" class="hidden text-xs text-red-600"></p>
                </div>
                @error('form_link')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Reward & Scheduling (Survey Center Indonesia) --}}
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
                        <input type="number" name="reward_amount" id="reward_amount" value="{{ old('reward_amount', 0) }}"
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
                        <input type="datetime-local" name="deadline" id="deadline" value="{{ old('deadline') }}"
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
                        <input type="number" name="estimated_time_minutes" id="estimated_time_minutes" value="{{ old('estimated_time_minutes') }}"
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
                                    {{ is_array(old('ec_jenis_kelamin')) && in_array('Laki-laki', old('ec_jenis_kelamin')) ? 'checked' : '' }}
                                    class="w-4 h-4 rounded border-gray-300 text-orange-500 focus:ring-orange-400">
                                Laki-laki
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="ec_jenis_kelamin[]" value="Perempuan"
                                    {{ is_array(old('ec_jenis_kelamin')) && in_array('Perempuan', old('ec_jenis_kelamin')) ? 'checked' : '' }}
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
                            <input type="number" name="ec_age_min" id="ec_age_min" value="{{ old('ec_age_min') }}"
                                min="0" max="100"
                                class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition @error('ec_age_min') border-red-500 @enderror"
                                placeholder="Contoh: 18">
                            @error('ec_age_min')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="ec_age_max" class="block text-sm font-medium text-gray-700 mb-2">Usia Maksimum</label>
                            <input type="number" name="ec_age_max" id="ec_age_max" value="{{ old('ec_age_max') }}"
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
                        <input type="text" name="ec_provinsi" id="ec_provinsi" value="{{ old('ec_provinsi') }}"
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
                        <input type="text" name="ec_kota" id="ec_kota" value="{{ old('ec_kota') }}"
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
                        <input type="text" name="ec_pendidikan" id="ec_pendidikan" value="{{ old('ec_pendidikan') }}"
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
                        <input type="text" name="ec_pekerjaan" id="ec_pekerjaan" value="{{ old('ec_pekerjaan') }}"
                            class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition @error('ec_pekerjaan') border-red-500 @enderror"
                            placeholder="Contoh: Mahasiswa, Karyawan Swasta, PNS">
                        <p class="mt-1.5 text-xs text-gray-500">Pisahkan dengan koma jika lebih dari satu</p>
                        @error('ec_pekerjaan')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Cost Estimation --}}
            <div class="bg-gradient-to-br from-orange-50 to-amber-50 rounded-xl p-5 border border-orange-100">
                <h3 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i data-lucide="calculator" class="w-4 h-4 text-orange-600"></i>
                    Estimasi Biaya
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Harga per soal per orang</span>
                        <span class="text-gray-900 font-semibold" id="unit-price">Rp {{ number_format(\App\Helpers\VolumePricing::getTiers()[0]['price'] ?? 500, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Kalkulasi</span>
                        <span class="text-gray-900" id="base-cost">0 soal × 0 responden</span>
                    </div>
                    <div class="border-t border-orange-200 pt-3 flex justify-between">
                        <span class="font-semibold text-gray-900">Total Biaya</span>
                        <span class="font-bold text-orange-600 text-lg" id="total-cost">Rp 0</span>
                    </div>
                    <p id="special-price-msg" class="hidden text-xs text-emerald-600 font-medium text-right"></p>
                    <p id="min-warning" class="hidden text-xs text-red-500 font-medium text-right">⚠ Minimal order Rp 50.000 per survey</p>
                </div>
                <p class="text-xs text-gray-500 mt-4">
                    * Harga otomatis turun sesuai jumlah responden (volume pricing)
                </p>
            </div>

            {{-- Checkbox S&K --}}
            <div class="flex items-start gap-2.5">
                <input type="checkbox" id="agreeTerms"
                    class="w-4 h-4 mt-0.5 rounded border-gray-300 text-orange-500 focus:ring-orange-400 cursor-pointer flex-shrink-0"
                    onclick="handleCheckboxClick(event)">
                <label for="agreeTerms" class="text-xs text-gray-600 leading-snug select-none"
                       onclick="handleLabelClick(event)" style="cursor:pointer">
                    Saya telah membaca dan menyetujui
                    <span class="text-orange-500 font-semibold underline">Syarat &amp; Ketentuan</span>
                    yang berlaku.
                </label>
            </div>

            {{-- Submit Button --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('user.surveys.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                    Batal
                </a>
                <button type="submit" id="submitSurveyBtn" disabled
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-orange-600 rounded-lg hover:bg-orange-700 transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Buat Survey
                </button>
            </div>
        </form>
    </div>

    {{-- Info Card --}}
    <div class="mt-6 bg-blue-50 rounded-xl p-5 border border-blue-100">
        <div class="flex gap-3">
            <div class="flex-shrink-0">
                <i data-lucide="info" class="w-5 h-5 text-blue-600"></i>
            </div>
            <div>
                <h4 class="text-sm font-medium text-blue-900">Langkah Selanjutnya</h4>
                <p class="text-sm text-blue-700 mt-1">
                    Setelah membuat survey, Anda akan diarahkan ke halaman pembayaran. Survey akan mulai diproses setelah pembayaran dikonfirmasi.
                </p>
            </div>
        </div>
    </div>

    {{-- Price Level Section --}}
    <div class="mt-6 bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Price Level</h2>
            <p class="text-sm text-gray-500 mt-1">
                Level harga berkisar antara 1× hingga 1.5×, tergantung tingkat kesulitan dalam mencapai
                target responden berdasarkan profil yang ditetapkan.
            </p>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="flex items-center justify-center">
                    <img src="{{ asset('assets/Harga-Survey-Center-1024x606.png') }}"
                         alt="Price Level Table"
                         class="rounded-xl border border-gray-100 shadow-sm max-w-full w-full object-contain">
                </div>
                <div class="flex items-center justify-center">
                    <img src="{{ asset('assets/incase-768x247.jpg') }}"
                         alt="Price Level Formula"
                         class="rounded-xl border border-gray-100 shadow-sm max-w-full w-full object-contain">
                </div>
            </div>
            <p class="text-xs text-red-500 text-center mt-4 font-medium">
                * Harga dasar dapat meningkat tergantung tingkat kesulitan survey
            </p>
        </div>
    </div>

{{-- ═══════════ MODAL SYARAT & KETENTUAN ═══════════ --}}
<div id="termsModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
  <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[85vh] flex flex-col overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 flex-shrink-0">
      <h3 class="text-lg font-bold text-gray-900">Syarat &amp; Ketentuan</h3>
      <button id="modalCloseX" onclick="closeTermsModal()"
          class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100"
          style="opacity:.3;cursor:not-allowed" disabled>
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="flex items-center gap-2 px-6 py-2 bg-amber-50 border-b border-amber-100 text-amber-700 text-xs font-medium">
      <svg class="w-3.5 h-3.5 animate-bounce flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      Scroll sampai bawah untuk mengaktifkan tombol
    </div>
    <div id="termsBody" class="flex-1 overflow-y-auto px-6 py-5 terms-prose" onscroll="checkTermsScroll()">
      @if($terms)
        {!! $terms !!}
      @else
        <p class="text-gray-400 text-sm italic text-center py-8">Syarat &amp; ketentuan belum diatur oleh admin.</p>
      @endif
      <div class="h-6"></div>
    </div>
    <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between flex-shrink-0 bg-gray-50">
      <p id="scrollMsg" class="text-xs text-gray-400 italic">Scroll sampai bawah untuk melanjutkan</p>
      <div class="flex gap-3">
        <button id="modalCloseBtn" onclick="closeTermsModal()"
            class="px-4 py-2 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-200 transition"
            style="opacity:.3;cursor:not-allowed" disabled>Tutup</button>
        <button id="acceptBtn" onclick="acceptTerms()"
            class="px-5 py-2 text-sm font-bold text-white bg-orange-500 rounded-lg transition hover:bg-orange-600"
            style="opacity:.3;cursor:not-allowed" disabled>Saya Setuju</button>
      </div>
    </div>
  </div>
</div>

<style>
.terms-prose h1{font-size:1.3rem;font-weight:700;margin-bottom:.5rem;color:#111827}
.terms-prose h2{font-size:1.1rem;font-weight:700;margin-bottom:.5rem;color:#1f2937}
.terms-prose p{margin-bottom:.75rem;color:#374151;font-size:.875rem;line-height:1.65}
.terms-prose ul{list-style:disc;padding-left:1.5rem;margin-bottom:.75rem}
.terms-prose ol{list-style:decimal;padding-left:1.5rem;margin-bottom:.75rem}
.terms-prose li{margin-bottom:.25rem;color:#374151;font-size:.875rem}
.terms-prose strong{font-weight:700}
.terms-prose blockquote{border-left:4px solid #fbbf24;padding-left:1rem;color:#6b7280;margin:.75rem 0}
</style>

<script>
function updateSubmitState(){
    const cb=document.getElementById('agreeTerms');
    const btn=document.getElementById('submitSurveyBtn');
    if(btn) btn.disabled=!(cb&&cb.checked);
}
function handleCheckboxClick(e){
    const cb=document.getElementById('agreeTerms');
    if(!cb.checked){e.preventDefault();openTermsModal();}
    else{updateSubmitState();}
}
function handleLabelClick(e){
    e.preventDefault();
    const cb=document.getElementById('agreeTerms');
    if(!cb.checked){openTermsModal();}
    else{cb.checked=false;updateSubmitState();}
}
function openTermsModal(){
    const m=document.getElementById('termsModal');
    m.classList.remove('hidden');m.classList.add('flex');
    const body=document.getElementById('termsBody');
    body.scrollTop=0;setScrolledState(false);
    setTimeout(checkTermsScroll,150);
}
function closeTermsModal(){
    const m=document.getElementById('termsModal');
    m.classList.add('hidden');m.classList.remove('flex');
}
function acceptTerms(){
    document.getElementById('agreeTerms').checked=true;
    updateSubmitState();closeTermsModal();
}
function checkTermsScroll(){
    const b=document.getElementById('termsBody');
    if(!b)return;
    if(b.scrollTop+b.clientHeight>=b.scrollHeight-40)setScrolledState(true);
}
function setScrolledState(done){
    const accept=document.getElementById('acceptBtn');
    const closeBtn=document.getElementById('modalCloseBtn');
    const closeX=document.getElementById('modalCloseX');
    const msg=document.getElementById('scrollMsg');
    if(done){
        [accept,closeBtn,closeX].forEach(el=>{if(el){el.disabled=false;el.style.opacity='1';el.style.cursor='pointer';}});
        if(msg)msg.textContent='Anda sudah membaca syarat & ketentuan';
    }else{
        [accept,closeBtn,closeX].forEach(el=>{if(el){el.disabled=true;el.style.opacity='.3';el.style.cursor='not-allowed';}});
        if(msg)msg.textContent='Scroll sampai bawah untuk melanjutkan';
    }
}
</script>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') lucide.createIcons();

        const questionInput   = document.getElementById('question_count');
        const respondentInput = document.getElementById('respondent_count');
        const unitPriceEl     = document.getElementById('unit-price');
        const baseCostEl      = document.getElementById('base-cost');
        const totalCostEl     = document.getElementById('total-cost');
        const specialPriceMsg = document.getElementById('special-price-msg');
        const minWarning      = document.getElementById('min-warning');
        const MIN_ORDER       = {{ \App\Helpers\VolumePricing::getMinOrder() }};
        const PRICING_TIERS   = {!! \App\Helpers\VolumePricing::tiersForJs() !!};

        function formatCurrency(value) {
            return 'Rp ' + value.toLocaleString('id-ID');
        }

        function getVolumePricePerUnit(respondents) {
            for (const tier of PRICING_TIERS) {
                if (tier.max === null || respondents <= tier.max) return tier.price;
            }
            return PRICING_TIERS[0]?.price ?? 500;
        }

        function calculateCost() {
            const questions   = parseInt(questionInput.value) || 0;
            const respondents = parseInt(respondentInput.value) || 0;
            const unitPrice   = getVolumePricePerUnit(respondents);
            const total       = questions * respondents * unitPrice;

            unitPriceEl.textContent = formatCurrency(unitPrice);
            baseCostEl.textContent  = questions + ' soal \u00d7 ' + respondents + ' responden';
            totalCostEl.textContent = formatCurrency(total);

            if (respondents >= 100) {
                specialPriceMsg.textContent = 'Anda mendapatkan harga spesial ' + formatCurrency(unitPrice) + '/soal karena order > ' + (respondents >= 1000 ? '1.000' : respondents >= 500 ? '500' : '100') + ' responden';
                specialPriceMsg.classList.remove('hidden');
            } else {
                specialPriceMsg.classList.add('hidden');
            }

            if (questions > 0 && respondents > 0) {
                minWarning.classList.toggle('hidden', total >= MIN_ORDER);
            } else {
                minWarning.classList.add('hidden');
            }
        }

        questionInput.addEventListener('input', calculateCost);
        respondentInput.addEventListener('input', calculateCost);

        // Initial calculation
        calculateCost();

        const analyzeButton = document.getElementById('analyzeFormButton');
        const loading = document.getElementById('analyzeFormLoading');
        const card = document.getElementById('aiAnalyzerCard');
        const err = document.getElementById('aiAnalyzerError');
        const detectedTitle = document.getElementById('aiDetectedTitle');
        const titlePercent = document.getElementById('aiTitlePercent');
        const titleStatus = document.getElementById('aiTitleStatus');
        const questionCount = document.getElementById('aiQuestionCount');
        const questionStatus = document.getElementById('aiQuestionStatus');
        const questionReview = document.getElementById('aiQuestionReview');
        const detectedQuestions = document.getElementById('aiDetectedQuestions');
        const detectedQuestionsMeta = document.getElementById('aiDetectedQuestionsMeta');
        const detectedQuestionsToggle = document.getElementById('aiDetectedQuestionsToggle');
        const detectedQuestionsSearch = document.getElementById('aiDetectedQuestionsSearch');
        const questionTypeSummary = document.getElementById('aiQuestionTypeSummary');
        const debugEntryCount = document.getElementById('aiDebugEntryCount');
        const debugLoadData = document.getElementById('aiDebugLoadData');
        const debugQuestionTitleCount = document.getElementById('aiDebugQuestionTitleCount');
        const debugEntryIds = document.getElementById('aiDebugEntryIds');
        const form = analyzeButton ? analyzeButton.closest('form') : null;
        const titleInput = form ? form.querySelector('input[name="title"]') : null;
        const questionInputAnalyze = form ? form.querySelector('input[name="question_count"]') : null;
        const linkInput = form ? form.querySelector('input[name="form_link"]') : null;
        const tokenInput = form ? form.querySelector('input[name="_token"]') : null;
        const detectedPreviewLimit = 8;
        let detectedQuestionsData = [];
        let detectedQuestionsExpanded = false;
        let detectedQuestionsKeyword = '';
        let detectedQuestionItems = [];
        let autoAnalyzeTimer = null;
        let inFlightController = null;

        function resetAnalyzerView() {
            card.classList.remove('hidden');
            err.classList.add('hidden');
            err.textContent = '';
        }

        function formatQuestionType(type) {
            const map = {
                short_text: 'Short Text',
                paragraph: 'Paragraph',
                multiple_choice: 'Multiple Choice',
                dropdown: 'Dropdown',
                checkbox: 'Checkbox',
                linear_scale: 'Linear Scale',
                multiple_choice_grid: 'MC Grid',
                checkbox_grid: 'Checkbox Grid',
                date: 'Date',
                time: 'Time',
                date_time: 'Date Time',
                unknown: 'Unknown',
            };

            return map[type] || type.replace(/_/g, ' ');
        }

        function renderQuestionTypeSummary() {
            questionTypeSummary.innerHTML = '';

            if (!Array.isArray(detectedQuestionItems) || detectedQuestionItems.length === 0) {
                questionTypeSummary.innerHTML = '<span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-1 text-[11px] text-gray-600">Belum ada data</span>';
                return;
            }

            const typeMap = {};
            detectedQuestionItems.forEach((item) => {
                const key = item.type || 'unknown';
                typeMap[key] = (typeMap[key] || 0) + 1;
            });

            Object.keys(typeMap).sort().forEach((key) => {
                const badge = document.createElement('span');
                badge.className = 'inline-flex items-center rounded-full bg-blue-50 px-2 py-1 text-[11px] font-medium text-blue-700 border border-blue-100';
                badge.textContent = `${formatQuestionType(key)}: ${typeMap[key]}`;
                questionTypeSummary.appendChild(badge);
            });
        }

        function renderDetectedQuestions() {
            detectedQuestions.innerHTML = '';

            const filteredQuestions = detectedQuestionsData.filter((item) =>
                item.toLowerCase().includes(detectedQuestionsKeyword.toLowerCase())
            );

            if (!Array.isArray(detectedQuestionsData) || detectedQuestionsData.length === 0) {
                const li = document.createElement('li');
                li.textContent = 'Belum terdeteksi';
                detectedQuestions.appendChild(li);
                detectedQuestionsMeta.textContent = '0 item';
                detectedQuestionsToggle.classList.add('hidden');
                detectedQuestionsSearch.classList.add('hidden');
                return;
            }

            detectedQuestionsSearch.classList.remove('hidden');

            if (filteredQuestions.length === 0) {
                const li = document.createElement('li');
                li.textContent = 'Tidak ada pertanyaan yang cocok';
                detectedQuestions.appendChild(li);
                detectedQuestionsMeta.textContent = `0 dari ${detectedQuestionsData.length} item`;
                detectedQuestionsToggle.classList.add('hidden');
                return;
            }

            const total = filteredQuestions.length;
            const visibleItems = detectedQuestionsExpanded
                ? filteredQuestions
                : filteredQuestions.slice(0, detectedPreviewLimit);

            const escapedKeyword = detectedQuestionsKeyword
                .replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            const keywordRegex = escapedKeyword ? new RegExp(`(${escapedKeyword})`, 'ig') : null;

            visibleItems.forEach((item) => {
                const li = document.createElement('li');
                if (keywordRegex) {
                    li.innerHTML = item.replace(keywordRegex, '<mark class="bg-yellow-200 px-0.5 rounded">$1</mark>');
                } else {
                    li.textContent = item;
                }
                detectedQuestions.appendChild(li);
            });

            detectedQuestionsMeta.textContent = detectedQuestionsKeyword
                ? `${total} dari ${detectedQuestionsData.length} item`
                : `${total} item`;

            if (total > detectedPreviewLimit) {
                detectedQuestionsToggle.classList.remove('hidden');
                detectedQuestionsToggle.textContent = detectedQuestionsExpanded
                    ? 'Sembunyikan'
                    : `Lihat semua (${total})`;
            } else {
                detectedQuestionsToggle.classList.add('hidden');
            }
        }

        detectedQuestionsToggle.addEventListener('click', function () {
            detectedQuestionsExpanded = !detectedQuestionsExpanded;
            renderDetectedQuestions();
        });

        detectedQuestionsSearch.addEventListener('input', function (event) {
            detectedQuestionsKeyword = event.target.value || '';
            detectedQuestionsExpanded = false;
            renderDetectedQuestions();
        });

        function canAnalyze() {
            if (!titleInput || !questionInputAnalyze || !linkInput) {
                return false;
            }

            const title = titleInput.value.trim();
            const link = linkInput.value.trim();
            const question = Number(questionInputAnalyze.value || 0);

            return title !== '' && link !== '' && question > 0;
        }

        async function runAnalysis(showValidationMessage = false) {
            if (!form || !tokenInput || !titleInput || !questionInputAnalyze || !linkInput) {
                return;
            }

            const token = tokenInput.value;
            const title = titleInput.value.trim();
            const question = questionInputAnalyze.value;
            const link = linkInput.value.trim();

            if (!title || !question || !link) {
                if (showValidationMessage) {
                    resetAnalyzerView();
                    err.classList.remove('hidden');
                    err.textContent = 'Isi judul, jumlah pertanyaan, dan link form terlebih dahulu.';
                }
                return;
            }

            if (inFlightController) {
                inFlightController.abort();
            }

            inFlightController = new AbortController();
            loading.classList.remove('hidden');
            analyzeButton.disabled = true;

            try {
                const response = await fetch("{{ route('form-analyzer.preview') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    body: JSON.stringify({
                        title: title,
                        question_count: Number(question),
                        form_link: link,
                    }),
                    signal: inFlightController.signal,
                });

                const data = await response.json();
                resetAnalyzerView();

                if (!response.ok || !data.ok) {
                    err.classList.remove('hidden');
                    err.textContent = data.message || 'Gagal menganalisa link form.';
                    return;
                }

                detectedTitle.textContent = data.title.detected || '-';
                titlePercent.textContent = `${data.title.similarity_percent}%`;
                titleStatus.textContent = data.title.is_match ? 'Cocok' : 'Tidak cocok';
                titleStatus.className = `text-xs mt-1 ${data.title.is_match ? 'text-emerald-600' : 'text-red-600'}`;

                questionCount.textContent = `${data.question_count.input} vs ${data.question_count.detected ?? '-'}`;
                questionStatus.textContent = data.question_count.is_match ? 'Cocok' : 'Tidak cocok';
                questionStatus.className = `text-xs mt-1 ${data.question_count.is_match ? 'text-emerald-600' : 'text-red-600'}`;

                questionReview.textContent = data.question_review || '-';
                questionReview.className = `text-sm font-medium mt-1 ${data.question_count.is_match ? 'text-emerald-700' : 'text-amber-700'}`;

                detectedQuestionsData = Array.isArray(data.detected_questions) ? data.detected_questions : [];
                detectedQuestionItems = Array.isArray(data.detected_question_items) ? data.detected_question_items : [];
                detectedQuestionsExpanded = false;
                detectedQuestionsKeyword = '';
                detectedQuestionsSearch.value = '';
                renderDetectedQuestions();
                renderQuestionTypeSummary();

                const debug = data.debug || {};
                debugEntryCount.textContent = String(debug.entry_ids_count ?? 0);
                debugLoadData.textContent = debug.has_load_data ? 'Ya' : 'Tidak';
                debugQuestionTitleCount.textContent = String(debug.question_titles_count ?? 0);
                debugEntryIds.textContent = Array.isArray(debug.entry_ids) && debug.entry_ids.length > 0
                    ? debug.entry_ids.join(', ')
                    : '-';

            } catch (error) {
                if (error.name !== 'AbortError') {
                    resetAnalyzerView();
                    err.classList.remove('hidden');
                    err.textContent = 'Terjadi kesalahan saat menghubungi analyzer.';
                }
            } finally {
                loading.classList.add('hidden');
                analyzeButton.disabled = false;
                inFlightController = null;
            }
        }

        function scheduleAutoAnalyze() {
            if (autoAnalyzeTimer) {
                clearTimeout(autoAnalyzeTimer);
            }

            autoAnalyzeTimer = setTimeout(() => {
                if (canAnalyze()) {
                    runAnalysis(false);
                }
            }, 800);
        }

        if (analyzeButton) {
            analyzeButton.addEventListener('click', function () {
                runAnalysis(true);
            });

            titleInput?.addEventListener('input', scheduleAutoAnalyze);
            questionInputAnalyze?.addEventListener('input', scheduleAutoAnalyze);
            linkInput?.addEventListener('input', scheduleAutoAnalyze);

            if (canAnalyze()) {
                scheduleAutoAnalyze();
            }
        }
    });
</script>
@endpush
