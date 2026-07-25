<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') — SurveyCenter</title>

    {{-- TailwindCSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                    colors: {
                        sidebar: {
                            DEFAULT: '#0f172a',
                            hover: '#1e293b',
                            active: '#334155',
                        },
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
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

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- jQuery & Select2 JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Sidebar scroll */
        .sidebar-scroll::-webkit-scrollbar { width: 0; }
        .sidebar-scroll { scrollbar-width: none; }

        /* Main content scroll */
        .content-scroll::-webkit-scrollbar { width: 5px; }
        .content-scroll::-webkit-scrollbar-track { background: transparent; }
        .content-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .content-scroll::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

        /* Sidebar link transitions */
        .nav-item {
            transition: all 0.15s ease;
            position: relative;
        }

        /* Active indicator bar */
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

        /* Tooltip for collapsed sidebar */
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

        /* Subtle gradient for sidebar */
        .sidebar-gradient {
            background: linear-gradient(180deg, #0f172a 0%, #1a1f35 50%, #0f172a 100%);
        }

        /* User dropdown */
        .user-dropdown {
            transform-origin: top right;
        }

        /* Page transition */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .page-enter {
            animation: fadeInUp 0.3s ease-out;
        }

        /* Select2 override for admin theme */
        .select2-container--default .select2-selection--single {
            border-radius: 0.5rem;
            border-color: #e5e7eb;
            height: 42px;
            padding-top: 5px;
        }
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
                        <span class="text-2xs text-gray-500 font-medium">Admin Panel</span>
                    </div>
                </div>
                <button @click="mobileSidebar = false" class="ml-auto w-7 h-7 rounded-lg hover:bg-white/10 transition lg:hidden flex items-center justify-center">
                    <i data-lucide="x" class="w-4 h-4 text-gray-400"></i>
                </button>
            </div>

            {{-- Separator --}}
            <div class="mx-4 border-t border-white/[0.06]"></div>

            {{-- Navigation --}}
            <nav class="flex-1 px-3 py-5 space-y-0.5 overflow-y-auto sidebar-scroll">

                {{-- Section: Menu Utama --}}
                <p x-show="sidebarOpen" class="px-3 mb-3 text-2xs font-semibold uppercase tracking-[0.15em] text-gray-500">Menu Utama</p>
                <div x-show="!sidebarOpen" class="mb-2 mx-3 border-t border-white/[0.06]"></div>

                @php
                    $adminLinks = [
                        ['route' => 'admin.dashboard', 'is' => 'admin.dashboard', 'icon' => 'layout-dashboard', 'label' => 'Dashboard', 'check' => 'routeIs'],
                        ['route' => 'settings.edit', 'is' => 'settings.*', 'icon' => 'settings', 'label' => 'Pengaturan', 'check' => 'routeIs'],
                    ];
                    $contentLinks = [
                        ['route' => 'tabs.index', 'is' => 'tabs.*', 'icon' => 'layers', 'label' => 'Kelola Tabs', 'check' => 'routeIs'],
                        ['route' => 'admin.discount-banners.index', 'is' => 'admin.discount-banners.*', 'icon' => 'image', 'label' => 'Kelola Banners', 'check' => 'routeIs'],
                        ['route' => 'admin.dashboard-banners.index', 'is' => 'admin.dashboard-banners.*', 'icon' => 'monitor', 'label' => 'Slider Dashboard', 'check' => 'routeIs'],
                        ['route' => 'partner-logos.index', 'is' => 'partner-logos.*', 'icon' => 'award', 'label' => 'Kelola Logos', 'check' => 'routeIs'],
                        ['route' => 'customer-stories.index', 'is' => 'customer-stories.*', 'icon' => 'message-square-quote', 'label' => 'Customer Stories', 'check' => 'routeIs'],
                        ['route' => 'admin.testimoni.index', 'is' => 'admin.testimoni.*', 'icon' => 'message-circle', 'label' => 'Testimoni', 'check' => 'routeIs'],
                        ['route' => 'admin.articles.index', 'is' => 'admin.articles.*', 'icon' => 'file-text', 'label' => 'Kelola Articles', 'check' => 'routeIs'],
                    ];
                    $businessLinks = [
                        ['route' => 'admin.layanan.index', 'is' => 'admin.layanan.*', 'icon' => 'briefcase', 'label' => 'Kelola Layanan', 'check' => 'routeIs'],
                        ['route' => 'admin.transactions.index', 'is' => 'admin.transactions.*', 'icon' => 'credit-card', 'label' => 'Transaksi', 'check' => 'routeIs'],
                        ['route' => 'admin.survey-fillings.index', 'is' => 'admin.survey-fillings.*', 'icon' => 'file-check-2', 'label' => 'Verifikasi Survey Filling', 'check' => 'routeIs'],
                        ['route' => 'admin.reward-items.index', 'is' => 'admin.reward-items.*', 'icon' => 'gift', 'label' => 'Kelola Reward', 'check' => 'routeIs'],
                        ['route' => 'admin.reward-redemptions.index', 'is' => 'admin.reward-redemptions.*', 'icon' => 'repeat', 'label' => 'Penukaran Poin', 'check' => 'routeIs'],
                        ['route' => 'admin.affiliate-withdrawals.index', 'is' => 'admin.affiliate-withdrawals.*', 'icon' => 'wallet', 'label' => 'Withdrawal Affiliate', 'check' => 'routeIs'],
                        ['route' => 'admin.seo.index', 'is' => 'admin.seo.*', 'icon' => 'search', 'label' => 'Kelola SEO', 'check' => 'routeIs'],
                        ['route' => 'admin.terms.edit', 'is' => 'admin.terms.*', 'icon' => 'file-check', 'label' => 'Syarat & Ketentuan', 'check' => 'routeIs'],
                    ];
                @endphp

                @foreach($adminLinks as $link)
                    @php
                        $isActive = $link['check'] === 'is' ? request()->is($link['is']) : request()->routeIs($link['is']);
                        $href = $link['check'] === 'is' ? '/'.$link['route'] : route($link['route']);
                    @endphp
                    <div class="relative">
                        <a href="{{ $href }}"
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

                {{-- Section: Konten --}}
                <p x-show="sidebarOpen" class="px-3 mt-7 mb-3 text-2xs font-semibold uppercase tracking-[0.15em] text-gray-500">Konten</p>
                <div x-show="!sidebarOpen" class="my-3 mx-3 border-t border-white/[0.06]"></div>

                @foreach($contentLinks as $link)
                    @php $isActive = request()->routeIs($link['is']); @endphp
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

                {{-- Section: Bisnis --}}
                <p x-show="sidebarOpen" class="px-3 mt-7 mb-3 text-2xs font-semibold uppercase tracking-[0.15em] text-gray-500">Bisnis</p>
                <div x-show="!sidebarOpen" class="my-3 mx-3 border-t border-white/[0.06]"></div>

                @foreach($businessLinks as $link)
                    @php $isActive = request()->routeIs($link['is']); @endphp
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

            {{-- Bottom Section --}}
            <div class="px-3 pb-4 flex-shrink-0 space-y-1">
                <div class="border-t border-white/[0.06] mb-3"></div>

                {{-- Switch Dashboard --}}
                <div class="relative">
                    <a href="{{ route('pilih-dashboard') }}"
                        class="nav-item flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium text-gray-400 hover:bg-white/[0.05] hover:text-gray-200">
                        <i data-lucide="repeat" class="w-[18px] h-[18px] flex-shrink-0"></i>
                        <span x-show="sidebarOpen" class="whitespace-nowrap">Switch Dashboard</span>
                    </a>
                    <div x-show="!sidebarOpen" class="nav-tooltip absolute left-full top-1/2 -translate-y-1/2 ml-3 z-[60]">
                        <div class="bg-gray-900 text-white text-xs font-medium px-2.5 py-1.5 rounded-lg shadow-xl whitespace-nowrap border border-white/10">
                            Switch Dashboard
                        </div>
                    </div>
                </div>

                {{-- Logout --}}
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <div class="relative">
                        <button type="submit"
                            class="nav-item flex items-center gap-3 w-full px-3 py-2 rounded-lg text-[13px] font-medium text-gray-500 hover:bg-red-500/10 hover:text-red-400">
                            <i data-lucide="log-out" class="w-[18px] h-[18px] flex-shrink-0"></i>
                            <span x-show="sidebarOpen" class="whitespace-nowrap">Logout</span>
                        </button>
                        <div x-show="!sidebarOpen" class="nav-tooltip absolute left-full top-1/2 -translate-y-1/2 ml-3 z-[60]">
                            <div class="bg-gray-900 text-white text-xs font-medium px-2.5 py-1.5 rounded-lg shadow-xl whitespace-nowrap border border-white/10">
                                Logout
                            </div>
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
                {{-- Mobile menu --}}
                <button @click="mobileSidebar = true" class="p-2 -ml-2 rounded-lg hover:bg-gray-100 transition lg:hidden mr-1">
                    <i data-lucide="align-left" class="w-5 h-5 text-gray-500"></i>
                </button>

                {{-- Breadcrumb / Page Title --}}
                <div class="min-w-0">
                    <h1 class="text-[15px] font-semibold text-gray-900 truncate">@hasSection('page-title')@yield('page-title')@elseif(trim($__env->yieldContent('title')))@yield('title')@else Dashboard @endif</h1>
                    @hasSection('breadcrumb')
                        <div class="flex items-center gap-1.5 text-xs text-gray-400 mt-0.5">
                            <span>Admin</span>
                            <i data-lucide="chevron-right" class="w-3 h-3"></i>
                            @yield('breadcrumb')
                        </div>
                    @endif
                </div>

                {{-- Spacer --}}
                <div class="flex-1"></div>

                {{-- Right Side Actions --}}
                <div class="flex items-center gap-2">
                    {{-- Date --}}
                    <div class="hidden md:flex items-center gap-1.5 text-xs text-gray-400 bg-gray-50 px-3 py-1.5 rounded-lg">
                        <i data-lucide="calendar-days" class="w-3.5 h-3.5"></i>
                        <span>{{ now()->translatedFormat('l, d M Y') }}</span>
                    </div>

                    {{-- User Avatar + Dropdown --}}
                    <div class="relative" @click.away="userMenu = false">
                        <button @click="userMenu = !userMenu"
                            class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-gray-100 transition">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-orange-500 to-purple-600 flex items-center justify-center shadow-sm">
                                <span class="text-white font-bold text-xs">A</span>
                            </div>
                            <div x-show="sidebarOpen || window.innerWidth < 1024" class="hidden sm:block text-left">
                                <p class="text-xs font-semibold text-gray-700 leading-none">Admin</p>
                                <p class="text-2xs text-gray-400 leading-none mt-0.5">Administrator</p>
                            </div>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-gray-400 hidden sm:block"></i>
                        </button>

                        {{-- Dropdown --}}
                        <div x-show="userMenu"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="user-dropdown absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl shadow-gray-200/50 border border-gray-200 py-1.5 z-50"
                             style="display: none;">
                            <a href="{{ route('pilih-dashboard') }}" class="flex items-center gap-2.5 px-3.5 py-2 text-[13px] text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                                <i data-lucide="repeat" class="w-4 h-4 text-gray-400"></i>
                                Switch Dashboard
                            </a>
                            <div class="my-1 border-t border-gray-100"></div>
                            <form action="{{ route('admin.logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="flex items-center gap-2.5 px-3.5 py-2 text-[13px] text-red-600 hover:bg-red-50 transition w-full text-left">
                                    <i data-lucide="log-out" class="w-4 h-4"></i>
                                    Logout
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
                             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
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

    {{-- Initialize Lucide Icons --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>

    @stack('scripts')
</body>

</html>
