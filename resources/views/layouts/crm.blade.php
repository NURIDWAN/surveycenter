<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard CRM') — SurveyCenter</title>

    {{-- TailwindCSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                    fontSize: {
                        '2xs': ['0.65rem', { lineHeight: '1rem' }],
                    }
                }
            }
        }
    </script>

    {{-- AlpineJS --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" referrerpolicy="no-referrer" />

    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .sidebar-scroll::-webkit-scrollbar { width: 0; }
        .sidebar-scroll { scrollbar-width: none; }

        .content-scroll::-webkit-scrollbar { width: 5px; }
        .content-scroll::-webkit-scrollbar-track { background: transparent; }
        .content-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

        .nav-item {
            transition: all 0.15s ease;
            position: relative;
        }

        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 60%;
            background: #fb923c;
            border-radius: 0 3px 3px 0;
        }

        .nav-tooltip {
            visibility: hidden;
            opacity: 0;
            transform: translateX(-4px);
            transition: all 0.15s ease;
        }
        .nav-item:hover .nav-tooltip {
            visibility: visible;
            opacity: 1;
            transform: translateX(0);
        }

        .sidebar-gradient {
            background: linear-gradient(180deg, #0f172a 0%, #1a1f35 50%, #0f172a 100%);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .page-enter { animation: fadeInUp 0.3s ease-out; }
    </style>

    @stack('styles')
</head>

<body class="bg-[#f8f9fc] text-gray-800" x-data="{ sidebarOpen: true, mobileSidebar: false, userMenu: false }">

    <div class="flex h-screen overflow-hidden">

        {{-- Mobile Overlay --}}
        <div x-show="mobileSidebar"
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="mobileSidebar = false"
             class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-40 lg:hidden"
             style="display: none;"></div>

        {{-- ═══════════════════════════════════════ --}}
        {{-- SIDEBAR --}}
        {{-- ═══════════════════════════════════════ --}}
        <aside :class="[
                sidebarOpen ? 'w-[260px]' : 'w-[70px]',
                mobileSidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
            ]"
            class="fixed lg:relative inset-y-0 left-0 z-50 flex flex-col sidebar-gradient text-white transition-all duration-300 ease-in-out shadow-xl shadow-black/10">

            {{-- Floating Toggle Button --}}
            <button @click="sidebarOpen = !sidebarOpen"
                class="absolute -right-3 top-6 w-6 h-6 bg-white rounded-full hidden lg:flex items-center justify-center text-gray-700 hover:text-orange-600 shadow-md border border-gray-200 z-[60] hover:scale-110 transition-all">
                <i x-show="sidebarOpen" data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                <i x-show="!sidebarOpen" data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </button>

            {{-- Logo --}}
            <div class="flex items-center h-[64px] px-4 flex-shrink-0" :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-white/10 backdrop-blur flex items-center justify-center flex-shrink-0 shadow-lg p-1">
                        <img src="{{ asset('assets/logosc.png') }}" alt="SurveyCenter Logo" class="w-full h-full object-contain">
                    </div>
                    <div x-show="sidebarOpen" x-transition:enter="transition-opacity duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="min-w-0">
                        <span class="font-bold text-[13px] tracking-wide text-white block">SurveyCenter</span>
                        <span class="text-2xs text-gray-500 font-medium">CRM Panel</span>
                    </div>
                </div>
                <button @click="mobileSidebar = false" class="ml-auto w-7 h-7 rounded-lg hover:bg-white/10 transition lg:hidden flex items-center justify-center">
                    <i data-lucide="x" class="w-4 h-4 text-gray-400"></i>
                </button>
            </div>

            <div class="mx-4 border-t border-white/[0.06]"></div>

            {{-- Navigation --}}
            <nav class="flex-1 px-3 py-5 space-y-0.5 overflow-y-auto sidebar-scroll">

                <p x-show="sidebarOpen" class="px-3 mb-3 text-2xs font-semibold uppercase tracking-[0.15em] text-gray-500">CRM</p>
                <div x-show="!sidebarOpen" class="mb-2 mx-3 border-t border-white/[0.06]"></div>

                @php
                    $crmLinks = [
                        ['route' => 'crm.dashboard', 'is' => 'crm.dashboard', 'icon' => 'layout-dashboard', 'label' => 'Dashboard CRM'],
                        ['route' => 'admin.surveys.manage', 'is' => 'admin.surveys.*', 'icon' => 'clipboard-list', 'label' => 'Kelola Survey', 'extra' => 'admin.responses.*,admin.transactions.progress.*'],
                        ['route' => 'admin.respondent-withdrawals.index', 'is' => 'admin.respondent-withdrawals.*', 'icon' => 'banknote', 'label' => 'Penarikan Responden'],
                        ['route' => 'admin.survey-fillings.index', 'is' => 'admin.survey-fillings.*', 'icon' => 'clipboard-check', 'label' => 'Verifikasi Pengisian'],
                        ['route' => 'crm.manage-users', 'is' => 'crm.manage-users', 'icon' => 'users', 'label' => 'Manage User', 'extra' => 'followups.*,pilih-client,crm.customer-already'],
                    ];
                @endphp

                @foreach($crmLinks as $link)
                    @php
                        $isActive = request()->routeIs($link['is']);
                        if (isset($link['extra'])) {
                            foreach(explode(',', $link['extra']) as $extra) {
                                $isActive = $isActive || request()->routeIs(trim($extra));
                            }
                        }
                    @endphp
                    <div class="relative">
                        <a href="{{ route($link['route']) }}"
                           class="nav-item {{ $isActive ? 'active' : '' }} flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium
                           {{ $isActive ? 'bg-white/[0.08] text-white' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-200' }}">
                            <i data-lucide="{{ $link['icon'] }}" class="w-[18px] h-[18px] flex-shrink-0 {{ $isActive ? 'text-orange-400' : '' }}"></i>
                            <span x-show="sidebarOpen" class="whitespace-nowrap">{{ $link['label'] }}</span>
                        </a>
                        <div x-show="!sidebarOpen" class="nav-tooltip absolute left-full top-1/2 -translate-y-1/2 ml-3 z-[60]">
                            <div class="bg-gray-900 text-white text-xs font-medium px-2.5 py-1.5 rounded-lg shadow-xl whitespace-nowrap border border-white/10">
                                {{ $link['label'] }}
                            </div>
                        </div>
                    </div>
                @endforeach

            </nav>

            {{-- Bottom --}}
            <div class="px-3 pb-4 flex-shrink-0 space-y-1">
                <div class="border-t border-white/[0.06] mb-3"></div>

                <div class="relative">
                    <a href="{{ route('pilih-dashboard') }}"
                        class="nav-item flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium text-gray-400 hover:bg-white/[0.05] hover:text-gray-200">
                        <i data-lucide="repeat" class="w-[18px] h-[18px] flex-shrink-0"></i>
                        <span x-show="sidebarOpen" class="whitespace-nowrap">Switch Dashboard</span>
                    </a>
                    <div x-show="!sidebarOpen" class="nav-tooltip absolute left-full top-1/2 -translate-y-1/2 ml-3 z-[60]">
                        <div class="bg-gray-900 text-white text-xs font-medium px-2.5 py-1.5 rounded-lg shadow-xl whitespace-nowrap border border-white/10">Switch Dashboard</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <div class="relative">
                        <button type="submit"
                            class="nav-item flex items-center gap-3 w-full px-3 py-2 rounded-lg text-[13px] font-medium text-gray-500 hover:bg-red-500/10 hover:text-red-400">
                            <i data-lucide="log-out" class="w-[18px] h-[18px] flex-shrink-0"></i>
                            <span x-show="sidebarOpen" class="whitespace-nowrap">Keluar</span>
                        </button>
                        <div x-show="!sidebarOpen" class="nav-tooltip absolute left-full top-1/2 -translate-y-1/2 ml-3 z-[60]">
                            <div class="bg-gray-900 text-white text-xs font-medium px-2.5 py-1.5 rounded-lg shadow-xl whitespace-nowrap border border-white/10">Keluar</div>
                        </div>
                    </div>
                </form>
            </div>
        </aside>

        {{-- ═══════════════════════════════════════ --}}
        {{-- MAIN CONTENT --}}
        {{-- ═══════════════════════════════════════ --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            {{-- Top Bar --}}
            <header class="h-[64px] bg-white/80 backdrop-blur-lg border-b border-gray-200/80 flex items-center px-4 sm:px-6 lg:px-8 flex-shrink-0 z-30">
                <button @click="mobileSidebar = true" class="p-2 -ml-2 rounded-lg hover:bg-gray-100 transition lg:hidden mr-1">
                    <i data-lucide="align-left" class="w-5 h-5 text-gray-500"></i>
                </button>

                <div class="min-w-0">
                    <h1 class="text-[15px] font-semibold text-gray-900 truncate">@yield('page-title', 'Dashboard CRM')</h1>
                </div>

                <div class="flex-1"></div>

                <div class="flex items-center gap-2">
                    <div class="hidden md:flex items-center gap-1.5 text-xs text-gray-400 bg-gray-50 px-3 py-1.5 rounded-lg">
                        <i data-lucide="calendar-days" class="w-3.5 h-3.5"></i>
                        <span>{{ now()->translatedFormat('l, d M Y') }}</span>
                    </div>

                    <div class="relative" @click.away="userMenu = false">
                        <button @click="userMenu = !userMenu"
                            class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-gray-100 transition">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-orange-500 to-amber-600 flex items-center justify-center shadow-sm">
                                <span class="text-white font-bold text-xs">A</span>
                            </div>
                            <div class="hidden sm:block text-left">
                                <p class="text-xs font-semibold text-gray-700 leading-none">Admin</p>
                                <p class="text-2xs text-gray-400 leading-none mt-0.5">CRM Manager</p>
                            </div>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-gray-400 hidden sm:block"></i>
                        </button>

                        <div x-show="userMenu"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl shadow-gray-200/50 border border-gray-200 py-1.5 z-50"
                             style="display: none;">
                            <a href="{{ route('pilih-dashboard') }}" class="flex items-center gap-2.5 px-3.5 py-2 text-[13px] text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                                <i data-lucide="repeat" class="w-4 h-4 text-gray-400"></i>
                                Switch Dashboard
                            </a>
                            <div class="my-1 border-t border-gray-100"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center gap-2.5 px-3.5 py-2 text-[13px] text-red-600 hover:bg-red-50 transition w-full text-left">
                                    <i data-lucide="log-out" class="w-4 h-4"></i>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Page Content --}}
            <main class="flex-1 overflow-y-auto content-scroll">
                <div class="p-4 sm:p-6 lg:p-8 page-enter">
                    {{-- Flash Messages --}}
                    @if(session('success'))
                        <div class="mb-6 flex items-center gap-3 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200/60 text-emerald-700 text-[13px]"
                             x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                            <div class="w-5 h-5 rounded-full bg-emerald-500 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="check" class="w-3 h-3 text-white"></i>
                            </div>
                            <span class="font-medium">{{ session('success') }}</span>
                            <button @click="show = false" class="ml-auto p-0.5 rounded hover:bg-emerald-100 transition">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-6 flex items-center gap-3 px-4 py-3 rounded-xl bg-red-50 border border-red-200/60 text-red-700 text-[13px]"
                             x-data="{ show: true }" x-show="show"
                             x-transition:leave="transition ease-in duration-200">
                            <div class="w-5 h-5 rounded-full bg-red-500 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="x" class="w-3 h-3 text-white"></i>
                            </div>
                            <span class="font-medium">{{ session('error') }}</span>
                            <button @click="show = false" class="ml-auto p-0.5 rounded hover:bg-red-100 transition">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>

    @stack('notifications')
    @stack('scripts')
</body>

</html>
