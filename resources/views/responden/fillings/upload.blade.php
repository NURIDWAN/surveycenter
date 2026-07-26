@extends('layouts.responden')

@section('page-title', 'Upload Bukti Pengisian')
@section('page-description', 'Unggah screenshot bukti pengisian survey')

@section('content')

<div class="max-w-2xl mx-auto">

    {{-- Survey Info Card --}}
    <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-orange-50 border border-orange-100 flex items-center justify-center flex-shrink-0">
                <i data-lucide="clipboard-list" class="w-5 h-5 text-orange-500"></i>
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="text-sm font-bold text-gray-900">{{ $filling->survey->title }}</h3>
                <p class="text-xs text-gray-400 mt-1">Unggah bukti screenshot setelah Anda menyelesaikan survey</p>

                @if($filling->survey->form_link)
                    <a href="{{ $filling->survey->form_link }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 mt-3 px-3 py-1.5 rounded-lg bg-emerald-50 border border-emerald-100 text-xs font-semibold text-emerald-600 hover:bg-emerald-100 hover:text-emerald-700 transition">
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        Buka Google Form
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Upload Form --}}
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-sm font-bold text-gray-900">Upload Bukti Screenshot</h2>
            <p class="text-xs text-gray-400 mt-0.5">File harus berformat JPG atau PNG, maksimal 2MB</p>
        </div>

        <form action="{{ route('responden.fillings.upload.store', $filling) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
            @csrf

            {{-- File Input --}}
            <div>
                <label for="proof_file" class="block text-xs font-semibold text-gray-700 mb-2">
                    Screenshot Bukti Pengisian <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="file"
                           name="proof_file"
                           id="proof_file"
                           accept="image/jpeg,image/jpg,image/png"
                           class="block w-full text-sm text-gray-500
                                  file:mr-4 file:py-2.5 file:px-4
                                  file:rounded-xl file:border file:border-gray-200
                                  file:text-xs file:font-semibold
                                  file:bg-gray-50 file:text-gray-700
                                  hover:file:bg-gray-100 hover:file:border-gray-300
                                  file:transition file:cursor-pointer
                                  focus:outline-none
                                  @error('proof_file') border-red-300 @enderror">
                </div>
                @error('proof_file')
                    <p class="mt-1.5 text-xs text-red-600 font-medium flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3 h-3"></i>
                        {{ $message }}
                    </p>
                @enderror
                <p class="mt-1.5 text-[11px] text-gray-400">Format: JPG, JPEG, PNG. Maksimal: 2MB</p>
            </div>

            {{-- Catatan Textarea --}}
            <div>
                <label for="catatan" class="block text-xs font-semibold text-gray-700 mb-2">
                    Catatan <span class="text-gray-400 font-normal">(opsional)</span>
                </label>
                <textarea name="catatan"
                          id="catatan"
                          rows="4"
                          maxlength="1000"
                          placeholder="Tambahkan catatan jika diperlukan..."
                          class="block w-full rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-3 text-sm text-gray-700 placeholder-gray-400 focus:border-orange-300 focus:bg-white focus:ring-2 focus:ring-orange-500/10 transition @error('catatan') border-red-300 bg-red-50/50 @enderror">{{ old('catatan') }}</textarea>
                @error('catatan')
                    <p class="mt-1.5 text-xs text-red-600 font-medium flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3 h-3"></i>
                        {{ $message }}
                    </p>
                @enderror
                <p class="mt-1.5 text-[11px] text-gray-400">Maksimal 1000 karakter</p>
            </div>

            {{-- Submit Button --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-sm font-bold shadow-md shadow-orange-500/10 hover:shadow-lg hover:scale-[1.02] transition-all">
                    <i data-lucide="upload" class="w-4 h-4"></i>
                    Upload Bukti
                </button>
                <a href="{{ route('responden.fillings.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-sm font-medium text-gray-600 hover:text-gray-800 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>

</div>

@endsection
