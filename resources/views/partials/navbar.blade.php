<nav x-data="{ open: false, services: false }" class="site-navbar sticky top-0 z-50 border-b border-slate-100 bg-white/95 backdrop-blur">
  <div class="mx-auto flex h-[72px] max-w-7xl items-center justify-between px-5 lg:px-8">
    <a href="{{ route('landing') }}" class="flex items-center gap-2.5" aria-label="Survey Center Indonesia">
      <img src="{{ asset('assets/logosc.png') }}" alt="Survey Center Indonesia" class="h-11 w-11 object-contain">
      <div class="leading-none">
        <strong class="block text-[13px] font-extrabold text-orange-500">Survey Center<br>Indonesia</strong>
        <span class="mt-1 block text-[7px] tracking-wide text-slate-400">PT. MARKET RESEARCH & BRANDING</span>
      </div>
    </a>

    <div class="hidden items-center gap-7 text-[13px] font-semibold text-slate-700 lg:flex">
      <a href="{{ route('landing') }}" class="border-b-2 border-orange-500 py-2 text-orange-500">Home</a>
      <a href="{{ route('about') }}" class="transition hover:text-orange-500">About</a>
      <div class="relative" @mouseenter="services = true" @mouseleave="services = false">
        <button @click="services = !services" class="flex items-center gap-1 py-4 transition hover:text-orange-500">
          Layanan <i class="fa-solid fa-chevron-down text-[8px]"></i>
        </button>
        <div x-cloak x-show="services" x-transition class="absolute left-1/2 top-[48px] w-[560px] -translate-x-1/2 rounded-2xl border border-slate-100 bg-white p-5 shadow-2xl">
          <div class="grid grid-cols-2 gap-7">
            <div>
              <p class="mb-2 text-[10px] font-extrabold uppercase tracking-wider text-orange-500">Jenis Survei</p>
              @foreach($jenis as $item)
                <a href="{{ route('layanan.show', $item->slug) }}" class="block rounded-lg px-2 py-1.5 text-xs font-medium text-slate-600 hover:bg-orange-50 hover:text-orange-600">{{ $item->title }}</a>
              @endforeach
            </div>
            <div>
              <p class="mb-2 text-[10px] font-extrabold uppercase tracking-wider text-orange-500">Layanan Tambahan</p>
              @foreach($tambahan as $item)
                <a href="{{ route('layanan.show', $item->slug) }}" class="block rounded-lg px-2 py-1.5 text-xs font-medium text-slate-600 hover:bg-orange-50 hover:text-orange-600">{{ $item->title }}</a>
              @endforeach
            </div>
          </div>
        </div>
      </div>
      <a href="{{ route('pricing') }}" class="transition hover:text-orange-500">Harga</a>
      <a href="{{ route('blog.index') }}" class="transition hover:text-orange-500">Blog</a>
      <a href="{{ route('contact') }}" class="transition hover:text-orange-500">Contact Us</a>
    </div>

    <div class="hidden items-center gap-2.5 lg:flex">
      <a href="{{ route('responden.login') }}" class="rounded-full border border-sky-300 px-5 py-2 text-xs font-bold text-sky-600 hover:bg-sky-50">Isi Survey & Dapatkan Saldo</a>
      @auth
        <a href="{{ route('user.dashboard') }}" class="rounded-full border border-orange-300 px-5 py-2 text-xs font-bold text-orange-600 hover:bg-orange-50">Dashboard</a>
      @else
        <a href="{{ route('login') }}" class="rounded-full border border-orange-300 px-5 py-2 text-xs font-bold text-orange-600 hover:bg-orange-50">Login</a>
        <a href="{{ route('register') }}" class="rounded-full bg-orange-500 px-5 py-2.5 text-xs font-bold text-white shadow-lg shadow-orange-200 transition hover:bg-orange-600">Daftar Gratis</a>
      @endauth
    </div>

    <button @click="open = !open" class="grid h-10 w-10 place-items-center rounded-xl text-slate-700 lg:hidden" aria-label="Buka menu">
      <i class="fa-solid" :class="open ? 'fa-xmark' : 'fa-bars'"></i>
    </button>
  </div>

  <div x-cloak x-show="open" x-transition class="border-t border-slate-100 bg-white px-5 py-5 shadow-xl lg:hidden">
    <div class="space-y-3 text-sm font-semibold text-slate-700">
      <a href="{{ route('landing') }}" class="block text-orange-500">Home</a>
      <a href="{{ route('about') }}" class="block">About</a>
      <button @click="services = !services" class="flex w-full items-center justify-between">Layanan <i class="fa-solid fa-chevron-down text-[9px]"></i></button>
      <div x-show="services" class="max-h-60 space-y-2 overflow-y-auto border-l-2 border-orange-100 pl-4 text-xs font-medium text-slate-500">
        @foreach($jenis->concat($tambahan) as $item)
          <a href="{{ route('layanan.show', $item->slug) }}" class="block">{{ $item->title }}</a>
        @endforeach
      </div>
      <a href="{{ route('pricing') }}" class="block">Harga</a>
      <a href="{{ route('blog.index') }}" class="block">Blog</a>
      <a href="{{ route('contact') }}" class="block">Contact Us</a>
    </div>
    <div class="mt-5 flex flex-col gap-3 border-t border-slate-100 pt-4">
      <a href="{{ route('responden.login') }}" class="w-full rounded-full border border-sky-300 px-5 py-3 text-center text-xs font-bold text-sky-600 hover:bg-sky-50">Isi Survey & Dapatkan Saldo</a>
      <div class="flex gap-3">
        @auth
          <a href="{{ route('user.dashboard') }}" class="w-full rounded-full bg-orange-500 px-5 py-3 text-center text-xs font-bold text-white">Dashboard</a>
        @else
          <a href="{{ route('login') }}" class="flex-1 rounded-full border border-orange-300 px-5 py-3 text-center text-xs font-bold text-orange-600">Login</a>
          <a href="{{ route('register') }}" class="flex-1 rounded-full bg-orange-500 px-5 py-3 text-center text-xs font-bold text-white">Daftar Gratis</a>
        @endauth
      </div>
    </div>
  </div>
</nav>