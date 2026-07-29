@extends('layouts.crm')

@section('title', 'Manage User')
@section('page-title', 'Manage User')

@section('content')
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-500"></i>
                    Manage User
                </h2>
                <p class="text-sm text-gray-500 mt-1">Daftar semua user, atur hak akses responden, dan akses login cepat</p>
            </div>
        </div>

        {{-- Flash Message --}}
        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        {{-- Filter & Search --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <form method="GET" action="{{ route('crm.manage-users') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Cari User</label>
                    <input type="text" name="q" value="{{ $search ?? request('q') }}" placeholder="Nama, email, atau nomor HP"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Role</label>
                    <select name="role" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none">
                        <option value="all" {{ ($role ?? request('role', 'all')) === 'all' ? 'selected' : '' }}>Semua Role</option>
                        <option value="user" {{ ($role ?? request('role')) === 'user' ? 'selected' : '' }}>User</option>
                        <option value="admin" {{ ($role ?? request('role')) === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Status Responden</label>
                    <select name="is_responden" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none">
                        <option value="all" {{ ($respondenFilter ?? request('is_responden', 'all')) === 'all' ? 'selected' : '' }}>Semua</option>
                        <option value="1" {{ ($respondenFilter ?? request('is_responden')) === '1' ? 'selected' : '' }}>Responden (Aktif)</option>
                        <option value="0" {{ ($respondenFilter ?? request('is_responden')) === '0' ? 'selected' : '' }}>Bukan Responden</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Per Halaman</label>
                    <select name="per_page" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none">
                        <option value="10" {{ (int) ($perPage ?? request('per_page', 10)) === 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ (int) ($perPage ?? request('per_page')) === 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ (int) ($perPage ?? request('per_page')) === 50 ? 'selected' : '' }}>50</option>
                    </select>
                </div>

                <div class="md:col-span-5 flex items-center gap-2">
                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-lg hover:bg-orange-700 transition">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        Terapkan
                    </button>
                    <a href="{{ route('crm.manage-users') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Responden</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Transaksi</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Dibayar</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Terakhir</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Link Form</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($users as $user)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3.5 font-medium text-gray-900">{{ $user->name }}</td>
                                <td class="px-4 py-3.5 text-gray-600">{{ $user->email }}</td>
                                <td class="px-4 py-3.5">
                                    @if($user->is_admin)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700">Admin</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">User</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5">
                                    <form action="{{ route('crm.manage-users.toggle-responden', $user) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        @if($user->is_responden)
                                            <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition" title="Klik untuk nonaktifkan akses responden">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Aktif
                                            </button>
                                        @else
                                            <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500 border border-gray-200 hover:bg-gray-200 transition" title="Klik untuk aktifkan akses responden">
                                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                                Nonaktif
                                            </button>
                                        @endif
                                    </form>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                        {{ $user->transactions->count() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 font-semibold text-emerald-600">
                                    Rp {{ number_format($user->transactions->sum('amount'), 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3.5 text-gray-500 text-xs">
                                    @php
                                        $latestTransaction = $user->transactions->sortByDesc('created_at')->first();
                                        $latestSurvey = $latestTransaction?->survey;
                                        $legacyUserResponse = $latestSurvey?->responses?->firstWhere('input_by_admin_id', null);
                                        $latestSurveyLink = $latestSurvey?->form_link ?: $legacyUserResponse?->google_form_link;
                                    @endphp
                                    {{ $latestTransaction ? \Carbon\Carbon::parse($latestTransaction->created_at)->format('d M Y') : '-' }}
                                </td>
                                <td class="px-4 py-3.5">
                                    @if (!empty($latestSurveyLink))
                                        <a href="{{ $latestSurveyLink }}" target="_blank" rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1 rounded-lg border border-orange-200 bg-orange-50 px-2.5 py-1.5 text-xs font-medium text-orange-700 hover:bg-orange-100 transition">
                                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                            Lihat URL
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('crm.manage-users.show', $user) }}" title="Lihat Detail User"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-orange-50 text-orange-700 text-xs font-medium rounded-lg hover:bg-orange-100 transition">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                            Detail
                                        </a>
                                        @if(!$user->is_admin)
                                            <form action="{{ route('admin.users.impersonate', $user) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" title="Login sebagai user"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-700 text-xs font-medium rounded-lg hover:bg-blue-100 transition">
                                                    <i data-lucide="log-in" class="w-3.5 h-3.5"></i>
                                                    Login User
                                                </button>
                                            </form>
                                        @endif
                                        @if(!empty($user->phone))
                                            <a href="https://wa.me/{{ $user->phone }}" target="_blank" title="Chat WhatsApp"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 text-xs font-medium rounded-lg hover:bg-emerald-100 transition">
                                                <i class="fab fa-whatsapp"></i>
                                                Chat
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-12">
                                    <i data-lucide="inbox" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
                                    <p class="text-sm text-gray-500">Belum ada user terdaftar</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if ($users->hasPages())
            <div class="mt-2">
                {{ $users->links() }}
            </div>
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
