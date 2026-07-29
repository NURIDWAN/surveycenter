@extends('layouts.crm')

@section('title', 'Kelola Survey')
@section('page-title', 'Kelola Survey')

@section('content')
    <div class="space-y-6" x-data="surveyManager()" @keydown.escape.window="closeModal()">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Daftar Survey</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Menampilkan survey berstatus paid. Detail data dipindahkan ke modal agar tabel lebih rapi.
                </p>
            </div>
        </div>

        @if($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3">
                <p class="text-sm font-medium text-red-700">Gagal menyimpan data responden:</p>
                <ul class="mt-2 list-disc pl-5 text-sm text-red-600 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="GET" action="{{ route('admin.surveys.manage') }}"
              class="bg-white rounded-xl border border-gray-200 p-4 grid grid-cols-1 lg:grid-cols-[1fr_auto_auto_auto] gap-3 items-end">
            <div>
                <label for="search" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Cari Survey / User</label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Contoh: Survei Kepuasan atau email user"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-orange-500 focus:ring-orange-500"
                >
            </div>

            <div>
                <label for="filter" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Filter</label>
                <select
                    id="filter"
                    name="filter"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-orange-500 focus:ring-orange-500"
                >
                    <option value="needs_results" {{ $filter === 'needs_results' ? 'selected' : '' }}>Perlu hasil admin</option>
                    <option value="completed" {{ $filter === 'completed' ? 'selected' : '' }}>Sudah lengkap</option>
                    <option value="all_paid" {{ $filter === 'all_paid' ? 'selected' : '' }}>Semua paid</option>
                </select>
            </div>

            <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-orange-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-orange-700 transition">
                <i data-lucide="filter" class="w-4 h-4"></i>
                Terapkan
            </button>

            <a href="{{ route('admin.surveys.manage') }}"
               class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                Reset
            </a>
        </form>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div>
                <table class="w-full text-sm table-fixed">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="w-[10%] px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ID DB</th>
                            <th class="w-[26%] px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Survey</th>
                            <th class="w-[20%] px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pemesan</th>
                            <th class="w-[14%] px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="w-[14%] px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Progress</th>
                            <th class="w-[16%] px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">URL Form</th>
                            <th class="w-[16%] px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($surveys as $survey)
                            @php
                                $latestPaidTransaction = $survey->transactions->first();
                                $latestAdminResponseLink = optional($survey->adminResponses->first())->google_form_link;
                                $latestUserResponseLink = optional($survey->responses->first())->google_form_link;
                                $effectiveUserFormLink = $survey->form_link ?: $latestUserResponseLink;
                                $targetRespondent = (int) ($survey->respondent_count ?? 0);
                                $obtainedRespondent = (int) ($survey->admin_responses_sum_respond_count ?? 0);
                                $remainingRespondent = max($targetRespondent - $obtainedRespondent, 0);
                                $progress = $latestPaidTransaction ? $latestPaidTransaction->safeProgress() : 0;
                                $isCompleted = $targetRespondent > 0
                                    ? $obtainedRespondent >= $targetRespondent
                                    : $progress >= 100;
                            @endphp

                            <tr class="hover:bg-gray-50 transition align-top">
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700">
                                        #{{ $survey->id }}
                                    </span>
                                </td>

                                <td class="px-4 py-3.5">
                                    <p class="font-medium text-gray-900 truncate" title="{{ $survey->title }}">{{ $survey->title }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $survey->question_count ?? 0 }} pertanyaan</p>
                                </td>

                                <td class="px-4 py-3.5">
                                    <p class="font-medium text-gray-800 truncate" title="{{ $survey->user->name ?? '-' }}">{{ $survey->user->name ?? '-' }}</p>
                                    <p class="text-xs text-gray-500 mt-1 truncate" title="{{ $survey->user->email ?? '-' }}">{{ $survey->user->email ?? '-' }}</p>
                                </td>

                                <td class="px-4 py-3.5">
                                    @if($isCompleted)
                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 border border-emerald-200">
                                            Lengkap
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700 border border-amber-200">
                                            Perlu hasil admin
                                        </span>
                                    @endif

                                    {{-- Responden visibility badge --}}
                                    <div class="mt-1.5">
                                        @if($survey->status === 'active')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 border border-blue-200">
                                                <i data-lucide="users" class="w-3 h-3"></i>
                                                Tampil Responden
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-500 border border-gray-200">
                                                <i data-lucide="eye-off" class="w-3 h-3"></i>
                                                Tersembunyi
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-4 py-3.5">
                                    <div class="space-y-1.5">
                                        <div class="flex items-center justify-between text-[11px]">
                                            <span class="font-semibold {{ $progress >= 100 ? 'text-emerald-700' : ($progress > 0 ? 'text-blue-700' : 'text-gray-500') }}">
                                                {{ $progress }}%
                                            </span>
                                            <span class="text-gray-400">Tahap 2</span>
                                        </div>
                                        <div class="h-2.5 w-full rounded-full bg-gray-200 overflow-hidden">
                                            <div
                                                class="h-2.5 rounded-full {{ $progress >= 100 ? 'bg-emerald-500' : ($progress > 0 ? 'bg-blue-500' : 'bg-gray-300') }}"
                                                data-progress-width="{{ $progress }}"
                                            ></div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-3.5 align-top">
                                    <div class="flex flex-col gap-2">
                                        @if(!empty($effectiveUserFormLink))
                                            <a href="{{ $effectiveUserFormLink }}" target="_blank" rel="noopener noreferrer"
                                               class="inline-flex w-fit items-center gap-1 rounded-lg border border-orange-200 bg-orange-50 px-2.5 py-1.5 text-xs font-medium text-orange-700 hover:bg-orange-100 transition">
                                                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                                URL User
                                            </a>
                                        @endif

                                        @if(!empty($latestAdminResponseLink))
                                            <a href="{{ $latestAdminResponseLink }}" target="_blank" rel="noopener noreferrer"
                                               class="inline-flex w-fit items-center gap-1 rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100 transition">
                                                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                                URL Admin
                                            </a>
                                        @endif

                                        @if(empty($effectiveUserFormLink) && empty($latestAdminResponseLink))
                                            <span class="text-xs text-gray-400">Belum ada link</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-4 py-3.5">
                                    <div class="flex flex-wrap gap-2">
                                        <button
                                            type="button"
                                            @click="openDetailModal({
                                                id: {{ $survey->id }},
                                                title: @js($survey->title),
                                                created_at: @js(optional($survey->created_at)->format('d M Y H:i')),
                                                question_count: {{ (int) ($survey->question_count ?? 0) }},
                                                form_link: @js($effectiveUserFormLink),
                                                latest_admin_form_link: @js($latestAdminResponseLink),
                                                user_name: @js(optional($survey->user)->name),
                                                user_email: @js(optional($survey->user)->email),
                                                target_respondent: {{ $targetRespondent }},
                                                obtained_respondent: {{ $obtainedRespondent }},
                                                remaining_respondent: {{ $remainingRespondent }},
                                                progress: {{ $progress }},
                                                status_label: @js($isCompleted ? 'Lengkap' : 'Perlu hasil admin'),
                                                admin_responses: @js(
                                                    $survey->adminResponses->map(function ($response) {
                                                        return [
                                                            'id' => $response->id,
                                                            'respond_count' => $response->respond_count,
                                                            'google_form_link' => $response->google_form_link,
                                                            'updated_at' => optional($response->updated_at)->format('d M Y H:i'),
                                                            'admin_name' => optional($response->inputByAdmin)->name,
                                                        ];
                                                    })->values()
                                                )
                                            })"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 px-2.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition"
                                        >
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                            Detail
                                        </button>

                                        <button
                                            type="button"
                                            @click="openCreateModal({
                                                surveyId: {{ $survey->id }},
                                                surveyTitle: @js($survey->title),
                                                storeUrl: @js(route('admin.surveys.respondents.store', $survey))
                                            })"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-orange-600 px-2.5 py-2 text-xs font-semibold text-white hover:bg-orange-700 transition"
                                        >
                                            <i data-lucide="user-plus" class="w-4 h-4"></i>
                                            Tambah
                                        </button>

                                        @if($latestPaidTransaction)
                                            <a
                                                href="{{ route('admin.transactions.progress.edit', $latestPaidTransaction) }}"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-blue-300 px-2.5 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-50 transition"
                                            >
                                                <i data-lucide="gauge" class="w-4 h-4"></i>
                                                Progress
                                            </a>
                                        @endif

                                        @if($survey->adminResponses->isNotEmpty())
                                            <button
                                                type="button"
                                                @click="openEditModal({
                                                    surveyId: {{ $survey->id }},
                                                    surveyTitle: @js($survey->title),
                                                    responses: @js(
                                                        $survey->adminResponses->map(function ($response) use ($survey) {
                                                            return [
                                                                'id' => $response->id,
                                                                'respond_count' => $response->respond_count,
                                                                'google_form_link' => $response->google_form_link,
                                                                'updated_at' => optional($response->updated_at)->format('d M Y H:i'),
                                                                'admin_name' => optional($response->inputByAdmin)->name,
                                                                'update_url' => route('admin.surveys.respondents.update', [$survey, $response]),
                                                            ];
                                                        })->values()
                                                    )
                                                })"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 px-2.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition"
                                            >
                                                <i data-lucide="pencil" class="w-4 h-4"></i>
                                                Edit
                                            </button>
                                        @endif

                                        {{-- Toggle Responden Active/Draft --}}
                                        @if($survey->status === 'active')
                                            <button type="button"
                                                @click="openDeactivateModal({
                                                    surveyTitle: @js($survey->title),
                                                    toggleUrl: @js(route('admin.surveys.toggle-status', $survey))
                                                })"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-amber-300 bg-amber-50 px-2.5 py-2 text-xs font-semibold text-amber-700 hover:bg-amber-100 transition"
                                            >
                                                <i data-lucide="eye-off" class="w-4 h-4"></i>
                                                Nonaktifkan
                                            </button>
                                        @else
                                            <button type="button"
                                                @click="openActivateModal({
                                                    surveyId: {{ $survey->id }},
                                                    surveyTitle: @js($survey->title),
                                                    currentReward: {{ (int) ($survey->reward_amount ?? 0) }},
                                                    currentEstimatedTime: @js($survey->estimated_time_minutes ?? ''),
                                                    currentDeadline: @js($survey->deadline ? $survey->deadline->format('Y-m-d\TH:i') : ''),
                                                    currentDescription: @js($survey->description ?? ''),
                                                    currentNotes: @js($survey->notes_for_respondent ?? ''),
                                                    toggleUrl: @js(route('admin.surveys.toggle-status', $survey))
                                                })"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-300 bg-emerald-50 px-2.5 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 transition"
                                            >
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                                Aktifkan
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center">
                                    <i data-lucide="inbox" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i>
                                    <p class="text-sm text-gray-500">Tidak ada survey yang cocok dengan filter saat ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {{ $surveys->links() }}
        </div>

        <div
            x-show="modalOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-[1px] z-40"
            style="display: none;"
            @click="closeModal()"
        ></div>

        <div
            x-show="modalOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-3"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-3"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="display: none;"
        >
            <div class="w-full max-w-xl rounded-2xl bg-white border border-gray-200 shadow-2xl" @click.stop>
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900" x-text="modalMode === 'create' ? 'Tambah Responden' : (modalMode === 'edit' ? 'Edit Responden' : 'Detail Survey')"></h3>
                        <p class="text-xs text-gray-500 mt-0.5" x-text="modalSurveyTitle"></p>
                    </div>
                    <button type="button" @click="closeModal()" class="p-2 rounded-lg hover:bg-gray-100 transition">
                        <i data-lucide="x" class="w-4 h-4 text-gray-500"></i>
                    </button>
                </div>

                <div class="px-5 py-4 space-y-4" x-show="modalMode === 'detail'">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                            <p class="text-[11px] text-gray-500">ID Survey</p>
                            <p class="text-sm font-semibold text-gray-900" x-text="detailData.id ? '#' + detailData.id : '-' "></p>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                            <p class="text-[11px] text-gray-500">Pertanyaan</p>
                            <p class="text-sm font-semibold text-gray-900" x-text="detailData.question_count ?? 0"></p>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                            <p class="text-[11px] text-gray-500">Progress</p>
                            <p class="text-sm font-semibold text-gray-900" x-text="(detailData.progress ?? 0) + '%' "></p>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                            <p class="text-[11px] text-gray-500">Status</p>
                            <p class="text-sm font-semibold text-gray-900" x-text="detailData.status_label || '-' "></p>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 p-3">
                        <p class="text-xs text-gray-500">Pemesan</p>
                        <p class="text-sm font-semibold text-gray-900" x-text="detailData.user_name || '-' "></p>
                        <p class="text-xs text-gray-500 mt-1" x-text="detailData.user_email || '-' "></p>
                        <p class="text-xs text-gray-500 mt-2">Dibuat: <span class="font-medium text-gray-700" x-text="detailData.created_at || '-' "></span></p>
                    </div>

                    <div class="rounded-lg border border-gray-200 p-3">
                        <p class="text-xs font-semibold text-gray-700 mb-2">URL Form</p>
                        <div class="space-y-2">
                            <div>
                                <p class="text-[11px] text-gray-500">URL dari user</p>
                                <template x-if="detailData.form_link">
                                    <a :href="detailData.form_link" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-xs font-semibold text-orange-600 hover:text-orange-700 break-all">
                                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                        <span x-text="detailData.form_link"></span>
                                    </a>
                                </template>
                                <template x-if="!detailData.form_link">
                                    <p class="text-xs text-gray-400">Belum ada</p>
                                </template>
                            </div>

                            <div>
                                <p class="text-[11px] text-gray-500">URL terbaru input admin</p>
                                <template x-if="detailData.latest_admin_form_link">
                                    <a :href="detailData.latest_admin_form_link" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-700 break-all">
                                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                        <span x-text="detailData.latest_admin_form_link"></span>
                                    </a>
                                </template>
                                <template x-if="!detailData.latest_admin_form_link">
                                    <p class="text-xs text-gray-400">Belum ada</p>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <div class="rounded-lg border border-gray-200 bg-orange-50 px-3 py-2">
                            <p class="text-[11px] text-orange-700">Target</p>
                            <p class="text-sm font-semibold text-orange-900" x-text="detailData.target_respondent ?? 0"></p>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-blue-50 px-3 py-2">
                            <p class="text-[11px] text-blue-700">Masuk Admin</p>
                            <p class="text-sm font-semibold text-blue-900" x-text="detailData.obtained_respondent ?? 0"></p>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                            <p class="text-[11px] text-gray-700">Sisa</p>
                            <p class="text-sm font-semibold text-gray-900" x-text="detailData.remaining_respondent ?? 0"></p>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 p-3">
                        <p class="text-xs font-semibold text-gray-700 mb-2">Riwayat Input Admin</p>
                        <template x-if="!Array.isArray(detailData.admin_responses) || detailData.admin_responses.length === 0">
                            <p class="text-sm text-gray-500">Belum ada data responden dari admin.</p>
                        </template>
                        <div class="space-y-2 max-h-56 overflow-y-auto pr-1" x-show="Array.isArray(detailData.admin_responses) && detailData.admin_responses.length > 0">
                            <template x-for="item in (detailData.admin_responses || [])" :key="item.id">
                                <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-xs font-semibold text-gray-900">Response #<span x-text="item.id"></span> - <span x-text="item.respond_count"></span> responden</p>
                                            <p class="text-[11px] text-gray-500 mt-0.5"><span x-text="item.updated_at || '-'"></span> <span x-show="item.admin_name">- <span x-text="item.admin_name"></span></span></p>
                                        </div>
                                        <a x-show="item.google_form_link" :href="item.google_form_link" target="_blank" class="text-[11px] font-semibold text-orange-600 hover:text-orange-700">Link</a>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="button" @click="closeModal()"
                                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            Tutup
                        </button>
                    </div>
                </div>

                <div class="px-5 py-4" x-show="modalMode === 'create'">
                    <form :action="createFormAction" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah responden</label>
                            <input
                                type="number"
                                name="respond_count"
                                min="1"
                                required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-orange-500 focus:ring-orange-500"
                                placeholder="Contoh: 25"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Link hasil <span class="text-red-500">*</span></label>
                            <input
                                type="url"
                                name="google_form_link"
                                required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-orange-500 focus:ring-orange-500"
                                placeholder="https://..."
                            >
                            <p class="mt-1 text-xs text-gray-500">
                                Wajib diisi. Link akan divalidasi dan judul form harus sama dengan judul survey.
                                Platform didukung: Google Forms, Microsoft Forms, Typeform, Jotform, Tally, Formstack.
                            </p>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-1">
                            <button type="button" @click="closeModal()"
                                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                                Batal
                            </button>
                            <button type="submit"
                                    class="rounded-lg bg-orange-600 px-4 py-2 text-sm font-medium text-white hover:bg-orange-700 transition">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>

                <div class="px-5 py-4 space-y-4" x-show="modalMode === 'edit'">
                    <template x-if="editResponses.length === 0">
                        <p class="text-sm text-gray-500">Belum ada responden admin yang bisa diedit.</p>
                    </template>

                    <template x-for="item in editResponses" :key="item.id">
                        <form :action="item.update_url" method="POST" class="rounded-xl border border-gray-200 p-3 space-y-3">
                            @csrf
                            @method('PUT')

                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Response #<span x-text="item.id"></span></p>
                                    <p class="text-[11px] text-gray-500">
                                        <span x-text="item.updated_at || '-'"></span>
                                        <span x-show="item.admin_name">&middot; oleh <span x-text="item.admin_name"></span></span>
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah responden</label>
                                    <input
                                        type="number"
                                        name="respond_count"
                                        min="1"
                                        :value="item.respond_count"
                                        required
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-orange-500 focus:ring-orange-500"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Link hasil <span class="text-red-500">*</span></label>
                                    <input
                                        type="url"
                                        name="google_form_link"
                                        :value="item.google_form_link || ''"
                                        required
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-orange-500 focus:ring-orange-500"
                                        placeholder="https://..."
                                    >
                                    <p class="mt-1 text-[11px] text-gray-500">
                                        Wajib diisi, dan judul form harus cocok dengan judul survey.
                                    </p>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit"
                                        class="rounded-lg bg-orange-600 px-3 py-2 text-xs font-semibold text-white hover:bg-orange-700 transition">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </template>

                    <div class="flex justify-end">
                        <button type="button" @click="closeModal()"
                                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════ ACTIVATE MODAL ══════════ --}}
        <div
            x-show="activateModalOpen"
            x-transition.opacity
            class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
            style="display: none;"
        >
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md" @click.stop>
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center">
                            <i data-lucide="eye" class="w-5 h-5 text-emerald-600"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Aktifkan Survey untuk Responden</h3>
                            <p class="text-xs text-gray-500 mt-0.5 truncate max-w-[240px]" x-text="activateSurveyTitle"></p>
                        </div>
                    </div>
                    <button @click="closeActivateModal()" class="p-1.5 rounded-lg hover:bg-gray-100 transition">
                        <i data-lucide="x" class="w-4 h-4 text-gray-500"></i>
                    </button>
                </div>

                <form :action="activateFormUrl" method="POST" class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">
                    @csrf
                    @method('PATCH')

                    <div class="rounded-xl bg-emerald-50 border border-emerald-100 px-4 py-3">
                        <p class="text-xs text-emerald-700">Survey akan tampil di dashboard responden yang memenuhi kriteria eligibilitas.</p>
                    </div>

                    {{-- Reward --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Reward per Responden (Rp)
                            <span class="text-gray-400 font-normal ml-1">— opsional</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-medium text-gray-500">Rp</span>
                            <input
                                type="number"
                                name="reward_amount"
                                :value="activateRewardAmount"
                                @input="activateRewardAmount = $event.target.value"
                                min="0"
                                step="1000"
                                class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none"
                                placeholder="Contoh: 5000"
                            >
                        </div>
                        <p class="mt-1 text-xs text-gray-400">Jumlah reward yang diterima responden setelah survey disetujui.</p>
                    </div>

                    {{-- Estimasi Waktu & Deadline in 2 columns --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Estimasi Waktu
                                <span class="text-gray-400 font-normal">(menit)</span>
                            </label>
                            <input
                                type="number"
                                name="estimated_time_minutes"
                                :value="activateEstimatedTime"
                                @input="activateEstimatedTime = $event.target.value"
                                min="1"
                                class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none"
                                placeholder="Contoh: 10"
                            >
                            <p class="mt-1 text-xs text-gray-400">Dikosongkan = tidak ditentukan</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Deadline
                            </label>
                            <input
                                type="datetime-local"
                                name="deadline"
                                :value="activateDeadline"
                                @input="activateDeadline = $event.target.value"
                                class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none"
                            >
                            <p class="mt-1 text-xs text-gray-400">Otomatis nonaktif saat deadline tercapai</p>
                        </div>
                    </div>

                    {{-- Deskripsi Survey --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Deskripsi Survey
                            <span class="text-gray-400 font-normal ml-1">— opsional</span>
                        </label>
                        <textarea
                            name="description"
                            :value="activateDescription"
                            @input="activateDescription = $event.target.value"
                            rows="3"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none resize-none"
                            placeholder="Deskripsi atau penjelasan mengenai survey ini..."
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-400">Deskripsi utama survey yang akan dilihat oleh responden.</p>
                    </div>

                    {{-- Catatan untuk Responden --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Catatan untuk Responden
                            <span class="text-gray-400 font-normal ml-1">— opsional</span>
                        </label>
                        <textarea
                            name="notes_for_respondent"
                            :value="activateNotes"
                            @input="activateNotes = $event.target.value"
                            rows="3"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none resize-none"
                            placeholder="Contoh: Pastikan Anda mengisi semua pertanyaan dengan jujur. Screenshot bukti pengisian harus menampilkan halaman konfirmasi..."
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-400">Ditampilkan di halaman detail survey untuk responden.</p>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                        <button type="button" @click="closeActivateModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition">
                            <i data-lucide="play-circle" class="w-4 h-4"></i>
                            Aktifkan Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ══════════ DEACTIVATE MODAL ══════════ --}}
        <div
            x-show="deactivateModalOpen"
            x-transition.opacity
            class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
            style="display: none;"
        >
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm" @click.stop>
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center">
                            <i data-lucide="eye-off" class="w-5 h-5 text-amber-600"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Nonaktifkan Survey</h3>
                            <p class="text-xs text-gray-500 mt-0.5 truncate max-w-[200px]" x-text="deactivateSurveyTitle"></p>
                        </div>
                    </div>
                    <button @click="closeDeactivateModal()" class="p-1.5 rounded-lg hover:bg-gray-100 transition">
                        <i data-lucide="x" class="w-4 h-4 text-gray-500"></i>
                    </button>
                </div>

                <form :action="deactivateFormUrl" method="POST" class="px-6 py-5 space-y-4">
                    @csrf
                    @method('PATCH')

                    <div class="rounded-xl bg-amber-50 border border-amber-100 px-4 py-3">
                        <p class="text-xs text-amber-700">Survey akan disembunyikan dari dashboard responden. Responden yang sudah mengisi tidak terpengaruh.</p>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-gray-100">
                        <button type="button" @click="closeDeactivateModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-amber-600 rounded-lg hover:bg-amber-700 transition">
                            <i data-lucide="eye-off" class="w-4 h-4"></i>
                            Ya, Nonaktifkan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
<script>
    function surveyManager() {
        return {
            modalOpen: false,
            modalMode: 'create',
            modalSurveyTitle: '',
            createFormAction: '',
            detailData: {},
            editResponses: [],

            // Activate modal state
            activateModalOpen: false,
            activateSurveyTitle: '',
            activateFormUrl: '',
            activateRewardAmount: 0,
            activateEstimatedTime: '',
            activateDeadline: '',
            activateDescription: '',
            activateNotes: '',

            openActivateModal(payload) {
                this.activateSurveyTitle = payload.surveyTitle || '';
                this.activateFormUrl = payload.toggleUrl || '';
                this.activateRewardAmount = payload.currentReward || 0;
                this.activateEstimatedTime = payload.currentEstimatedTime || '';
                this.activateDeadline = payload.currentDeadline || '';
                this.activateDescription = payload.currentDescription || '';
                this.activateNotes = payload.currentNotes || '';
                this.activateModalOpen = true;
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });
            },

            closeActivateModal() {
                this.activateModalOpen = false;
            },

            // Deactivate modal state
            deactivateModalOpen: false,
            deactivateSurveyTitle: '',
            deactivateFormUrl: '',

            openDeactivateModal(payload) {
                this.deactivateSurveyTitle = payload.surveyTitle || '';
                this.deactivateFormUrl = payload.toggleUrl || '';
                this.deactivateModalOpen = true;
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });
            },

            closeDeactivateModal() {
                this.deactivateModalOpen = false;
            },

            openDetailModal(payload) {
                this.modalMode = 'detail';
                this.modalSurveyTitle = payload.title || '';
                this.createFormAction = '';
                this.detailData = payload || {};
                this.editResponses = [];
                this.modalOpen = true;

                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                });
            },

            openCreateModal(payload) {
                this.modalMode = 'create';
                this.modalSurveyTitle = payload.surveyTitle || '';
                this.createFormAction = payload.storeUrl || '';
                this.detailData = {};
                this.editResponses = [];
                this.modalOpen = true;

                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                });
            },

            openEditModal(payload) {
                this.modalMode = 'edit';
                this.modalSurveyTitle = payload.surveyTitle || '';
                this.createFormAction = '';
                this.detailData = {};
                this.editResponses = Array.isArray(payload.responses) ? payload.responses : [];
                this.modalOpen = true;

                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                });
            },

            closeModal() {
                this.modalOpen = false;
            },
        };
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        document.querySelectorAll('[data-progress-width]').forEach(function (el) {
            const value = parseInt(el.dataset.progressWidth || '0', 10);
            const width = Math.max(0, Math.min(100, value));
            el.style.width = width + '%';
        });
    });
</script>
@endpush
