@extends('layouts.crm')

@section('title', 'Detail Pengisian Survey')
@section('page-title', 'Detail Pengisian Survey')

@push('styles')
<style>
    [x-cloak] {
        display: none !important;
    }
</style>
@endpush

@section('content')
    <div class="space-y-6" x-data="{ showRejectModal: false }">

        {{-- Back Link --}}
        <div>
            <a href="{{ route('admin.survey-fillings.index') }}"
               class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Kembali ke Daftar
            </a>
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

        {{-- Detail Card --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Informasi Pengisian Survey</h2>
            </div>

            <div class="p-6 space-y-4">
                {{-- Survey Name --}}
                <div class="flex justify-between items-center bg-orange-50 p-3 rounded">
                    <span class="font-semibold text-sm text-gray-700">Survey:</span>
                    <span class="text-sm text-orange-700 font-medium">{{ $filling->survey->title ?? '-' }}</span>
                </div>

                {{-- Respondent Name --}}
                <div class="flex justify-between items-center bg-gray-50 p-3 rounded">
                    <span class="font-semibold text-sm text-gray-700">Nama Responden:</span>
                    <span class="text-sm text-gray-900">{{ $filling->user->name ?? '-' }}</span>
                </div>

                {{-- Respondent Email --}}
                <div class="flex justify-between items-center bg-orange-50 p-3 rounded">
                    <span class="font-semibold text-sm text-gray-700">Email Responden:</span>
                    <span class="text-sm text-gray-900">{{ $filling->user->email ?? '-' }}</span>
                </div>

                {{-- Waktu Kirim --}}
                <div class="flex justify-between items-center bg-gray-50 p-3 rounded">
                    <span class="font-semibold text-sm text-gray-700">Waktu Kirim:</span>
                    <span class="text-sm text-gray-900">{{ $filling->created_at ? $filling->created_at->format('d M Y H:i:s') : '-' }}</span>
                </div>

                {{-- Google Form Link --}}
                <div class="flex justify-between items-center bg-orange-50 p-3 rounded">
                    <span class="font-semibold text-sm text-gray-700">Google Form:</span>
                    <span class="text-sm">
                        @if($filling->survey->google_form_link ?? null)
                            <a href="{{ $filling->survey->google_form_link }}" target="_blank"
                               class="inline-flex items-center gap-1 text-orange-600 hover:text-orange-700 font-medium">
                                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                Buka Link
                            </a>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </span>
                </div>

                {{-- Status --}}
                <div class="flex justify-between items-center bg-gray-50 p-3 rounded">
                    <span class="font-semibold text-sm text-gray-700">Status:</span>
                    <span>
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
                    </span>
                </div>

                {{-- Catatan Responden --}}
                @if($filling->catatan)
                    <div class="flex justify-between items-start bg-orange-50 p-3 rounded">
                        <span class="font-semibold text-sm text-gray-700">Catatan Responden:</span>
                        <span class="text-sm text-gray-900 text-right max-w-xs">{{ $filling->catatan }}</span>
                    </div>
                @endif

                {{-- Rejection Info (if rejected) --}}
                @if($filling->status === 'ditolak')
                    <div class="flex justify-between items-center bg-red-50 p-3 rounded">
                        <span class="font-semibold text-sm text-gray-700">Alasan Penolakan:</span>
                        <span class="text-sm text-red-700">{{ $filling->rejectionReason->label ?? '-' }}</span>
                    </div>
                    @if($filling->rejection_notes)
                        <div class="flex justify-between items-start bg-red-50 p-3 rounded">
                            <span class="font-semibold text-sm text-gray-700">Catatan Penolakan:</span>
                            <span class="text-sm text-red-700 text-right max-w-xs">{{ $filling->rejection_notes }}</span>
                        </div>
                    @endif
                @endif

                {{-- Approved At --}}
                @if($filling->status === 'disetujui' && $filling->approved_at)
                    <div class="flex justify-between items-center bg-green-50 p-3 rounded">
                        <span class="font-semibold text-sm text-gray-700">Disetujui Pada:</span>
                        <span class="text-sm text-green-700">{{ $filling->approved_at->format('d M Y H:i:s') }}</span>
                    </div>
                @endif

                {{-- Rejected At --}}
                @if($filling->status === 'ditolak' && $filling->rejected_at)
                    <div class="flex justify-between items-center bg-red-50 p-3 rounded">
                        <span class="font-semibold text-sm text-gray-700">Ditolak Pada:</span>
                        <span class="text-sm text-red-700">{{ $filling->rejected_at->format('d M Y H:i:s') }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Proof Screenshot --}}
        @if($filling->proof_file_path)
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">Bukti Screenshot</h2>
                </div>
                <div class="p-6">
                    <img src="{{ asset('storage/' . $filling->proof_file_path) }}"
                         alt="Bukti pengisian survey"
                         class="max-w-full h-auto rounded-lg border border-gray-200 shadow-sm">
                </div>
            </div>
        @endif

        {{-- Action Buttons (only for menunggu_verifikasi) --}}
        @if($filling->status === 'menunggu_verifikasi')
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">Aksi Verifikasi</h2>
                </div>
                <div class="p-6 flex items-center gap-3">
                    {{-- Approve Button --}}
                    <form action="{{ route('admin.survey-fillings.approve', $filling) }}" method="POST"
                          onsubmit="return confirm('Apakah Anda yakin ingin menyetujui pengisian survey ini?')">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition shadow-sm">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            Setujui
                        </button>
                    </form>

                    {{-- Reject Button (opens modal) --}}
                    <button @click="showRejectModal = true"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition shadow-sm">
                        <i data-lucide="x-circle" class="w-4 h-4"></i>
                        Tolak
                    </button>
                </div>
            </div>
        @endif

        {{-- Reject Modal --}}
        <div x-show="showRejectModal"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-black/50" @click="showRejectModal = false"></div>

            {{-- Modal Content --}}
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6 z-10"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">

                <h3 class="text-lg font-semibold text-gray-900 mb-4">Tolak Pengisian Survey</h3>

                <form action="{{ route('admin.survey-fillings.reject', $filling) }}" method="POST">
                    @csrf

                    {{-- Rejection Reason Dropdown --}}
                    <div class="mb-4">
                        <label for="rejection_reason_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Alasan Penolakan <span class="text-red-500">*</span>
                        </label>
                        <select name="rejection_reason_id" id="rejection_reason_id" required
                                class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500">
                            <option value="">-- Pilih Alasan --</option>
                            @foreach($rejectionReasons as $reason)
                                <option value="{{ $reason->id }}">{{ $reason->label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Notes Textarea --}}
                    <div class="mb-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                            Catatan Tambahan (opsional)
                        </label>
                        <textarea name="notes" id="notes" rows="3"
                                  class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                  placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                    </div>

                    {{-- Modal Buttons --}}
                    <div class="flex items-center justify-end gap-3">
                        <button type="button" @click="showRejectModal = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                            Tolak Pengisian
                        </button>
                    </div>
                </form>
            </div>
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
