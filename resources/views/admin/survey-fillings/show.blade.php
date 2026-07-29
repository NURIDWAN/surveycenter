@extends('layouts.crm')

@section('title', 'Detail Pengisian Survey')
@section('page-title', 'Detail Pengisian Survey')

@section('content')
<div class="space-y-5" x-data="{ showRejectModal: false }">

    {{-- Back --}}
    <a href="{{ route('admin.survey-fillings.index', request()->only('status')) }}"
       class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 transition font-medium">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        Kembali ke Daftar
    </a>

    {{-- Flash --}}
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- LEFT: Info Cards --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Survey Info --}}
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900">Informasi Survey</h3>
                    @switch($filling->status)
                        @case('menunggu_verifikasi')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                                <i data-lucide="clock" class="w-3 h-3"></i> Menunggu Verifikasi
                            </span>
                            @break
                        @case('disetujui')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                <i data-lucide="check-circle" class="w-3 h-3"></i> Disetujui
                            </span>
                            @break
                        @case('ditolak')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                <i data-lucide="x-circle" class="w-3 h-3"></i> Ditolak
                            </span>
                            @break
                        @case('sedang_dikerjakan')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                <i data-lucide="loader" class="w-3 h-3"></i> Sedang Dikerjakan
                            </span>
                            @break
                    @endswitch
                </div>
                <div class="p-5 space-y-3">
                    <div>
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Judul Survey</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $filling->survey->title ?? '-' }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Reward</p>
                            <p class="text-sm font-bold text-emerald-600">Rp {{ number_format($filling->survey->reward_amount ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Link Form</p>
                            @if($filling->survey->form_link ?? null)
                                <a href="{{ $filling->survey->form_link }}" target="_blank"
                                   class="inline-flex items-center gap-1 text-xs font-semibold text-orange-600 hover:text-orange-700">
                                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Buka Form
                                </a>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Waktu Pengajuan</p>
                            <p class="text-xs text-gray-700">{{ $filling->created_at?->format('d M Y, H:i:s') }}</p>
                        </div>
                        @if($filling->status === 'disetujui' && $filling->approved_at)
                            <div>
                                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Disetujui Pada</p>
                                <p class="text-xs text-emerald-700">{{ $filling->approved_at->format('d M Y, H:i:s') }}</p>
                            </div>
                        @elseif($filling->status === 'ditolak' && $filling->rejected_at)
                            <div>
                                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Ditolak Pada</p>
                                <p class="text-xs text-red-600">{{ $filling->rejected_at->format('d M Y, H:i:s') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Rejection Info --}}
            @if($filling->status === 'ditolak')
                <div class="bg-red-50 border border-red-200 rounded-2xl p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-red-600"></i>
                        <h3 class="text-sm font-bold text-red-800">Alasan Penolakan</h3>
                    </div>
                    <p class="text-sm font-semibold text-red-700">{{ $filling->rejectionReason->label ?? '-' }}</p>
                    @if($filling->rejection_notes)
                        <p class="text-xs text-red-600 mt-1">{{ $filling->rejection_notes }}</p>
                    @endif
                </div>
            @endif

            {{-- Catatan Responden --}}
            @if($filling->catatan)
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Catatan dari Responden</p>
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $filling->catatan }}</p>
                </div>
            @endif

            {{-- Bukti Screenshot --}}
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-900">Bukti Screenshot Pengisian</h3>
                </div>
                <div class="p-5">
                    @if($filling->proof_file_path)
                        <a href="{{ asset('storage/' . $filling->proof_file_path) }}" target="_blank">
                            <img src="{{ asset('storage/' . $filling->proof_file_path) }}"
                                 alt="Bukti pengisian survey"
                                 class="max-w-full rounded-xl border border-gray-200 shadow-sm hover:opacity-90 transition cursor-zoom-in">
                        </a>
                        <p class="text-[11px] text-gray-400 mt-2">Klik gambar untuk membuka di tab baru</p>
                    @else
                        <div class="flex flex-col items-center justify-center py-10 text-center">
                            <i data-lucide="image-off" class="w-10 h-10 text-gray-300 mb-2"></i>
                            <p class="text-sm text-gray-400">Bukti screenshot belum diunggah</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- RIGHT: Sidebar --}}
        <div class="space-y-5">

            {{-- Responden Info --}}
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-900">Data Responden</h3>
                </div>
                <div class="p-5">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-orange-400 to-amber-500 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                            {{ strtoupper(substr($filling->user->name ?? 'R', 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $filling->user->name ?? '-' }}</p>
                            <p class="text-xs text-gray-400">{{ $filling->user->email ?? '-' }}</p>
                        </div>
                    </div>
                    @if($filling->user)
                        <div class="space-y-2 text-xs">
                            @if($filling->user->jenis_kelamin)
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Jenis Kelamin</span>
                                    <span class="font-medium text-gray-700">{{ $filling->user->jenis_kelamin }}</span>
                                </div>
                            @endif
                            @if($filling->user->tanggal_lahir)
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Usia</span>
                                    <span class="font-medium text-gray-700">{{ $filling->user->tanggal_lahir->age }} tahun</span>
                                </div>
                            @endif
                            @if($filling->user->provinsi)
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Provinsi</span>
                                    <span class="font-medium text-gray-700">{{ $filling->user->provinsi }}</span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Verifikasi Actions --}}
            @if($filling->status === 'menunggu_verifikasi')
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="text-sm font-bold text-gray-900">Aksi Verifikasi</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Tinjau bukti di atas sebelum memverifikasi</p>
                    </div>
                    <div class="p-5 space-y-3">
                        <form action="{{ route('admin.survey-fillings.approve', $filling) }}" method="POST"
                              onsubmit="return confirm('Setujui pengisian ini dan kirim reward Rp {{ number_format($filling->survey->reward_amount ?? 0, 0, ',', '.') }} ke responden?')">
                            @csrf
                            <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700 transition shadow-sm">
                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                Setujui & Kirim Reward
                            </button>
                        </form>
                        <button @click="showRejectModal = true"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-red-600 text-white text-sm font-semibold rounded-xl hover:bg-red-700 transition shadow-sm">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                            Tolak Pengisian
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div x-show="showRejectModal"
     x-cloak
     x-transition.opacity
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md" @click.stop
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-red-50 flex items-center justify-center">
                    <i data-lucide="x-circle" class="w-5 h-5 text-red-600"></i>
                </div>
                <h3 class="text-sm font-bold text-gray-900">Tolak Pengisian Survey</h3>
            </div>
            <button @click="showRejectModal = false" class="p-1.5 rounded-lg hover:bg-gray-100 transition">
                <i data-lucide="x" class="w-4 h-4 text-gray-500"></i>
            </button>
        </div>
        <form action="{{ route('admin.survey-fillings.reject', $filling) }}" method="POST" class="p-6 space-y-4">
            @csrf
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
            <div class="flex items-center justify-end gap-3 pt-1 border-t border-gray-100">
                <button type="button" @click="showRejectModal = false"
                    class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
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

<style>[x-cloak] { display: none !important; }</style>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>
@endpush
@endsection
