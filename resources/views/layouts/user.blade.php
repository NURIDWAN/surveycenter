<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — SurveyCenter</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/logosc.png') }}">

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
                    },
                    colors: {
                        primary: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            200: '#fed7aa',
                            300: '#fdba74',
                            400: '#fb923c',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c',
                            800: '#9a3412',
                            900: '#7c2d12',
                        }
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
            background: #ea580c;
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
            background: linear-gradient(180deg, #c2410c 0%, #ea580c 50%, #f97316 100%);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .page-enter { animation: fadeInUp 0.3s ease-out; }
    </style>

    @stack('styles')
</head>

<body class="bg-[#f8f9fc] text-gray-800" x-data="{ sidebarOpen: true, mobileSidebar: false, userMenu: false, notifMenu: false }">

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
            class="fixed lg:relative inset-y-0 left-0 z-50 flex flex-col bg-white border-r border-gray-200/80 text-gray-700 transition-all duration-300 ease-in-out shadow-sm">

            {{-- Floating Toggle Button --}}
            <button @click="sidebarOpen = !sidebarOpen"
                class="absolute -right-3 top-6 w-6 h-6 bg-white rounded-full hidden lg:flex items-center justify-center text-gray-400 hover:text-orange-600 shadow-md border border-gray-200 z-[60] hover:scale-110 transition-all">
                <i x-show="sidebarOpen" data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                <i x-show="!sidebarOpen" data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </button>

            {{-- Logo --}}
            <div class="flex items-center h-[64px] px-5 flex-shrink-0" :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                <a href="{{ url('/') }}" class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-orange-50 flex items-center justify-center flex-shrink-0 shadow-sm p-1.5 border border-orange-100">
                        <img src="{{ asset('assets/logosc.png') }}" alt="SurveyCenter Logo" class="w-full h-full object-contain">
                    </div>
                    <div x-show="sidebarOpen" x-transition:enter="transition-opacity duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="min-w-0">
                        <span class="font-bold text-[14px] tracking-wide text-gray-900 block">SurveyCenter</span>
                        <span class="text-[10px] text-gray-400 font-medium">User Dashboard</span>
                    </div>
                </a>
                <button @click="mobileSidebar = false" class="ml-auto w-7 h-7 rounded-lg hover:bg-gray-100 transition lg:hidden flex items-center justify-center">
                    <i data-lucide="x" class="w-4 h-4 text-gray-400"></i>
                </button>
            </div>

            <div class="mx-5 border-t border-gray-100"></div>

            {{-- Navigation --}}
            <nav class="flex-1 px-3 py-5 space-y-5 overflow-y-auto sidebar-scroll">

                @php
                    $menuGroups = [
                        'SURVEY' => [
                            ['route' => 'user.dashboard', 'is' => 'user.dashboard', 'icon' => 'layout-dashboard', 'label' => 'Dashboard'],
                            ['route' => 'user.surveys.index', 'is' => 'user.surveys.*', 'icon' => 'clipboard-list', 'label' => 'Survey Saya'],
                            ['route' => 'user.surveys.create', 'is' => 'user.surveys.create', 'icon' => 'plus-circle', 'label' => 'Buat Survey'],
                            
                        ],
                        'TRANSAKSI' => [
                            ['route' => 'user.transactions.index', 'is' => 'user.transactions.*', 'icon' => 'receipt', 'label' => 'Transaksi'],
                            ['route' => 'user.wallet.index', 'is' => 'user.wallet.*', 'icon' => 'wallet', 'label' => 'Wallet'],
                        ],
                        'ANALYTICS' => [
                            ['route' => 'user.analytics', 'is' => 'user.analytics', 'icon' => 'bar-chart-2', 'label' => 'Analytics'],
                            ['route' => 'user.reports.index', 'is' => 'user.reports.*', 'icon' => 'file-bar-chart', 'label' => 'Laporan'],
                        ],
                        'REWARD' => [
                            ['route' => 'user.rewards.index', 'is' => 'user.rewards.*', 'icon' => 'gift', 'label' => 'Reward & Poin'],
                            ['route' => 'user.affiliate.index', 'is' => 'user.affiliate.*', 'icon' => 'share-2', 'label' => 'Affiliate'],
                        ],
                        'AKUN' => [
                            ['route' => 'user.profile.show', 'is' => 'user.profile.*', 'icon' => 'user-circle', 'label' => 'Profil Saya'],
                    
                        ],
                    ];
                @endphp

                @foreach($menuGroups as $groupName => $links)
                    <div>
                        <p x-show="sidebarOpen" class="px-3 mb-2 text-[10px] font-bold uppercase tracking-wider text-gray-400/90">{{ $groupName }}</p>
                        <div x-show="!sidebarOpen" class="mb-2 mx-2 border-t border-gray-100"></div>
                        
                        <div class="space-y-0.5">
                            @foreach($links as $link)
                                @php
                                    $isActive = request()->routeIs($link['is']);
                                    $href = \Illuminate\Support\Facades\Route::has($link['route']) ? route($link['route']) : '#';
                                @endphp
                                <div class="relative">
                                    <a href="{{ $href }}"
                                       class="nav-item {{ $isActive ? 'active' : '' }} flex items-center gap-3 px-3 py-2 rounded-xl text-[13px] font-medium transition-all duration-200
                                       {{ $isActive 
                                            ? 'bg-orange-50 text-orange-600 font-semibold shadow-sm shadow-orange-500/5' 
                                            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                        <i data-lucide="{{ $link['icon'] }}" class="w-[18px] h-[18px] flex-shrink-0 {{ $isActive ? 'text-orange-500' : 'text-gray-400 group-hover:text-gray-900' }}"></i>
                                        <span x-show="sidebarOpen" class="whitespace-nowrap flex-1">
                                            {{ $link['label'] }}
                                        </span>
                                    </a>
                                    <div x-show="!sidebarOpen" class="nav-tooltip absolute left-full top-1/2 -translate-y-1/2 ml-3 z-[60]">
                                        <div class="bg-gray-900 text-white text-xs font-medium px-2.5 py-1.5 rounded-lg shadow-xl whitespace-nowrap border border-white/10">
                                            {{ $link['label'] }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

            </nav>

            {{-- Saldo Widget (Moved down just above footer) --}}
            @auth
            <div class="mx-3 mt-auto mb-2 p-4 rounded-xl bg-gray-50/80 border border-gray-100 relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-16 h-16 bg-orange-50 rounded-full -translate-y-1/2 translate-x-1/4 blur-md"></div>
                
                <div x-show="sidebarOpen" x-transition:enter="transition-opacity duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Saldo Deposit</p>
                    <p class="text-base font-extrabold text-gray-900 mb-3">Rp {{ number_format(auth()->user()->deposit_balance ?? 0, 0, ',', '.') }}</p>
                    <a href="{{ route('user.topups.create') }}" class="flex items-center justify-center gap-1.5 w-full py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white hover:scale-[1.02] transition-all text-xs font-bold shadow-md shadow-orange-500/10">
                        <i data-lucide="wallet" class="w-3.5 h-3.5"></i>
                        Top Up Wallet
                    </a>
                </div>

                <div x-show="!sidebarOpen" class="flex flex-col items-center justify-center relative cursor-pointer" @click="sidebarOpen = true" x-transition:enter="transition-opacity duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <i data-lucide="wallet" class="w-5 h-5 text-gray-500 group-hover:text-orange-500 transition"></i>
                </div>
            </div>
            @endauth

            {{-- Bottom --}}
            <div class="px-3 pb-4 flex-shrink-0 space-y-1">
                <div class="border-t border-gray-100 mb-3"></div>

                <div class="relative">
                    <a href="{{ url('/') }}"
                        class="nav-item flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium text-gray-500 hover:bg-gray-50 hover:text-gray-900 transition-all">
                        <i data-lucide="home" class="w-[18px] h-[18px] flex-shrink-0 text-gray-400"></i>
                        <span x-show="sidebarOpen" class="whitespace-nowrap">Kembali ke Website</span>
                    </a>
                    <div x-show="!sidebarOpen" class="nav-tooltip absolute left-full top-1/2 -translate-y-1/2 ml-3 z-[60]">
                        <div class="bg-gray-900 text-white text-xs font-medium px-2.5 py-1.5 rounded-lg shadow-xl whitespace-nowrap border border-white/10">Kembali ke Website</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <div class="relative">
                        <button type="submit"
                            class="nav-item flex items-center gap-3 w-full px-3 py-2 rounded-lg text-[13px] font-medium text-red-500 hover:bg-red-50 hover:text-red-600 transition-all">
                            <i data-lucide="log-out" class="w-[18px] h-[18px] flex-shrink-0"></i>
                            <span x-show="sidebarOpen" class="whitespace-nowrap">Keluar</span>
                        </button>
                        <div x-show="!sidebarOpen" class="nav-tooltip absolute left-full top-1/2 -translate-y-1/2 ml-3 z-[60]">
                            <div class="bg-gray-900 text-white text-xs font-medium px-2.5 py-1.5 rounded-lg shadow-xl whitespace-nowrap border border-white/10 text-red-500">Keluar</div>
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
                    <h1 class="text-[15px] font-semibold text-gray-900 truncate">@yield('page-title', 'Dashboard')</h1>
                    <p class="text-xs text-gray-400 truncate hidden sm:block">@yield('page-description', '')</p>
                </div>

                <div class="flex-1"></div>

                <div class="flex items-center gap-2">
                    {{-- Notifications --}}
                    <div class="relative" @click.away="notifMenu = false">
                        <button @click="notifMenu = !notifMenu"
                            class="relative p-2 rounded-lg hover:bg-gray-100 transition">
                            <i data-lucide="bell" class="w-5 h-5 text-gray-500"></i>
                            @if(auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                            @endif
                        </button>

                        <div x-show="notifMenu"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl shadow-gray-200/50 border border-gray-200 py-2 z-50"
                             style="display: none;">
                            <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center">
                                <h3 class="text-sm font-semibold text-gray-900">Notifikasi</h3>
                                @if(auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                                    <form method="POST" action="{{ route('user.notifications.readAll') }}">
                                        @csrf
                                        <button type="submit" class="text-[11px] text-orange-600 hover:text-orange-700 font-medium pb-0.5">Tandai sudah dibaca</button>
                                    </form>
                                @endif
                            </div>
                            <div class="max-h-80 overflow-y-auto">
                                @if(auth()->check() && auth()->user()->notifications->count() > 0)
                                    @foreach(auth()->user()->notifications->take(10) as $notification)
                                        <a href="{{ route('user.notifications.read', $notification->id) }}" class="block px-4 py-3 border-b border-gray-50 hover:bg-gray-50 transition {{ $notification->read_at ? 'opacity-60' : 'bg-orange-50/20' }}">
                                            <div class="flex gap-3">
                                                <div class="flex-shrink-0 mt-0.5">
                                                    <div class="w-8 h-8 rounded-full {{ $notification->read_at ? 'bg-gray-100 text-gray-500' : 'bg-orange-100 text-orange-600' }} flex items-center justify-center">
                                                        <i data-lucide="bell" class="w-4 h-4"></i>
                                                    </div>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium {{ $notification->read_at ? 'text-gray-700' : 'text-gray-900' }}">
                                                        {{ $notification->data['message'] ?? 'Notification' }}
                                                    </p>
                                                    <p class="text-[10px] text-gray-500 mt-1 uppercase tracking-wider">{{ $notification->created_at->diffForHumans() }}</p>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                @else
                                    <div class="px-4 py-8 text-center text-sm text-gray-400">
                                        <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-gray-300"></i>
                                        Belum ada notifikasi
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- User Menu --}}
                    <div class="relative" @click.away="userMenu = false">
                        <button @click="userMenu = !userMenu"
                            class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-gray-100 transition">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-orange-500 to-amber-600 flex items-center justify-center shadow-sm">
                                <span class="text-white font-bold text-xs">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</span>
                            </div>
                            <div class="hidden sm:block text-left">
                                <p class="text-xs font-semibold text-gray-700 leading-none">{{ auth()->user()->name ?? 'User' }}</p>
                                <p class="text-2xs text-gray-400 leading-none mt-0.5">Member</p>
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
                            <a href="{{ route('user.profile.show') }}" class="flex items-center gap-2.5 px-3.5 py-2 text-[13px] text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                                <i data-lucide="user-circle" class="w-4 h-4 text-gray-400"></i>
                                Profil Saya
                            </a>
                            <a href="{{ url('/') }}" class="flex items-center gap-2.5 px-3.5 py-2 text-[13px] text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                                <i data-lucide="home" class="w-4 h-4 text-gray-400"></i>
                                Ke Website
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

                    @if(session()->has('impersonator_admin_id'))
                        <div class="mb-6 flex flex-col sm:flex-row sm:items-center gap-3 px-4 py-3 rounded-xl bg-blue-50 border border-blue-200/60 text-blue-800 text-[13px]">
                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-blue-500 flex items-center justify-center flex-shrink-0 mt-0.5 sm:mt-0">
                                    <i data-lucide="shield-alert" class="w-3 h-3 text-white"></i>
                                </div>
                                <div>
                                    <p class="font-semibold">Mode Admin: Login sebagai user aktif</p>
                                    <p class="text-blue-700 text-xs mt-0.5">Anda masuk sebagai {{ auth()->user()->name }} dari akun admin {{ session('impersonator_admin_name', 'Admin') }}.</p>
                                </div>
                            </div>
                            <div class="sm:ml-auto">
                                <form method="POST" action="{{ route('admin.impersonation.stop') }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 text-white text-xs font-medium hover:bg-blue-700 transition">
                                        <i data-lucide="undo-2" class="w-3.5 h-3.5"></i>
                                        Kembali ke Admin
                                    </button>
                                </form>
                            </div>
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



    @stack('scripts')
</body>

</html>
