@extends('layouts.crm')

@section('title', 'Update Progress Survey')

@section('content')
<div class="max-w-3xl mx-auto my-12 p-8 bg-white rounded-2xl shadow-xl font-sans">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">
            Update Progress Survey #{{ $transaction->id }}
        </h1>
        <span class="px-3 py-1 bg-gray-100 rounded-full text-sm font-medium text-gray-700">
            Status: {{ ucfirst($transaction->status) }}
        </span>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 mb-4 rounded shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-700 p-3 mb-4 rounded shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-6 bg-gray-50 rounded-xl shadow-inner border border-gray-200 overflow-hidden">
        <div class="p-5">
            <h3 class="text-lg font-bold mb-3 border-b border-gray-200 pb-2 text-gray-800 flex items-center gap-2">
                <i data-lucide="file-text" class="w-5 h-5 text-gray-500"></i> Detail Survey
            </h3>
            <p class="text-gray-800">
                <span class="font-semibold">Judul Survey:</span> {{ $transaction->survey?->title ?? '-' }}
            </p>
            <div class="mt-3">
                <span class="font-semibold text-gray-800">Deskripsi / Kebutuhan:</span>
                <p class="text-gray-600 mt-1 italic text-sm p-3 bg-white rounded border border-gray-100">
                    {{ $transaction->survey?->description ?? 'Tidak ada deskripsi yang diberikan.' }}
                </p>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mt-4">
                <div class="p-3 bg-white rounded-lg shadow-sm border border-gray-100 flex flex-col justify-center items-center">
                    <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider mb-1">Pertanyaan</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $transaction->survey?->question_count ?? 0 }}</p>
                </div>
                <div class="p-3 bg-white rounded-lg shadow-sm border border-gray-100 flex flex-col justify-center items-center">
                    <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider mb-1">Responden (Target)</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $transaction->survey?->respondent_count ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="p-5 bg-gray-100 border-t border-gray-200">
            <h3 class="text-lg font-bold mb-3 border-b border-gray-300 pb-2 text-gray-800 flex items-center gap-2">
                <i data-lucide="user" class="w-5 h-5 text-gray-500"></i> Informasi Pemesan
            </h3>
            <div class="flex flex-col sm:flex-row justify-between gap-4">
                <div>
                    <p class="text-gray-700">
                        <span class="font-semibold text-gray-900">Nama User:</span> {{ optional($transaction->user)->name ?? 'Guest User' }}
                    </p>
                    <p class="text-gray-700 mt-1">
                        <span class="font-semibold text-gray-900">Email:</span> {{ optional($transaction->user)->email ?? '-' }}
                    </p>
                    <p class="text-gray-700 mt-1">
                        <span class="font-semibold text-gray-900">Nomor HP:</span> {{ optional($transaction->user)->phone ?? '-' }}
                    </p>
                </div>
                <div class="sm:text-right">
                    <p class="text-gray-700 mb-1">Total Biaya Pembayaran</p>
                    <span class="px-4 py-2 bg-green-100 text-green-800 rounded-lg font-extrabold text-xl shadow-sm inline-block">
                        Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.transactions.progress.update', $transaction) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label for="progress" class="block font-semibold mb-2">Progress (%)</label>
            <input type="number" name="progress" id="progress" min="0" max="100"
                   value="{{ old('progress', $transaction->progress) }}"
                   class="border border-gray-300 p-2 rounded w-28 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
        </div>

        <div class="w-full bg-gray-200 rounded-full h-6 overflow-hidden mt-6">
            <div id="progressBar"
                 class="bg-green-500 h-6 rounded-full text-white text-center font-semibold transition-all duration-500 ease-out flex items-center justify-center"
                 style="width: {{ old('progress', $transaction->progress) }}%;">
                {{ old('progress', $transaction->progress) }}%
            </div>
        </div>

        <div class="pt-6 mt-6 border-t border-gray-200">
            <label for="notification_message" class="block font-semibold mb-2 text-gray-800">
                Pesan Notifikasi untuk User <span class="text-sm font-normal text-gray-500">(Opsional)</span>
            </label>
            <p class="text-sm text-gray-600 mb-3">Pesan ini akan dikirimkan ke menu notifikasi user. Sangat berguna untuk memberikan link Google Drive hasil survey atau update penting.</p>
            <textarea name="notification_message" id="notification_message" rows="3"
                      class="w-full border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                      placeholder="Contoh: Survey telah selesai, berikut link hasilnya: https://drive.google.com/..."></textarea>
        </div>

        <div class="flex gap-3 mt-6">
            <button type="submit"
                    class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition font-semibold shadow">
                Update Progress
            </button>

            <a href="{{ route('admin.transactions.progress.index') }}"
               class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition font-semibold shadow">
               Kembali
            </a>
        </div>
    </form>
</div>

<script>
    const progressInput = document.getElementById('progress');
    const progressBar = document.getElementById('progressBar');

    progressInput.addEventListener('input', function() {
        let value = Math.min(Math.max(this.value, 0), 100);
        progressBar.style.width = value + '%';
        progressBar.textContent = value + '%';
    });
</script>
@endsection
