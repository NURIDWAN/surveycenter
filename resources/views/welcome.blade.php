@extends('layouts.app')

@section('title', 'Survey Center Indonesia')
@section('seo_slug', 'home')

@push('styles')
<style>
    html { scroll-behavior: smooth; }
    .landing-shell { color: #172033; }
    .hero-dots { background-image: radial-gradient(#ff8b42 1.4px, transparent 1.4px); background-size: 10px 10px; }
    .dashboard-shadow { box-shadow: 0 25px 60px rgba(24, 39, 75, .12), 0 6px 20px rgba(24, 39, 75, .06); }
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

    .logo-marquee-left { display: flex; width: max-content; animation: marqueeLeft 30s linear infinite; }
    .logo-marquee-left:hover { animation-play-state: paused; }
    .logo-marquee-right { display: flex; width: max-content; animation: marqueeRight 30s linear infinite; }
    .logo-marquee-right:hover { animation-play-state: paused; }

    @keyframes marqueeLeft { from { transform: translateX(0); } to { transform: translateX(-50%); } }
    @keyframes marqueeRight { from { transform: translateX(-50%); } to { transform: translateX(0); } }

    @media (prefers-reduced-motion: reduce) {
      .logo-marquee-left, .logo-marquee-right { animation: none; }
      .scroll-smooth { scroll-behavior: auto; }
    }
</style>
@endpush

@section('content')

<div class="landing-shell overflow-hidden bg-white">
  <section class="relative px-5 pb-10 pt-10 sm:pt-14 lg:px-8">
    <div class="hero-dots absolute -left-16 top-24 h-44 w-44 rounded-full opacity-30"></div>
    <div class="absolute -left-20 top-16 h-44 w-44 rounded-full bg-gradient-to-br from-orange-300/45 to-orange-50/10 blur-sm"></div>
    <div class="hero-dots absolute -right-10 top-28 h-40 w-32 opacity-35"></div>
    <div class="mx-auto max-w-6xl text-center">
      <span class="mb-4 inline-flex items-center gap-2 rounded-full bg-orange-50 px-4 py-2 text-xs font-extrabold uppercase tracking-[.18em] text-orange-500">
        <i class="fa-solid fa-chart-line"></i> Solusi riset bisnis terpercaya
      </span>
      <h1 class="mx-auto max-w-4xl text-3xl font-black leading-[1.12] tracking-tight text-[#111827] sm:text-5xl lg:text-[56px]">
        Data Akurat, <span class="text-orange-500">Keputusan Bisnis</span> Lebih Tepat
      </h1>
      <p class="mx-auto mt-5 max-w-2xl text-sm leading-6 text-slate-500 sm:text-[15px]">
        Kumpulkan data, pahami kebutuhan, dan temukan <em class="font-semibold text-orange-500">insight</em><br class="hidden sm:block">
        yang relevan untuk mendukung setiap keputusan bisnis Anda.
      </p>
      <div class="mt-7 flex flex-col items-center justify-center gap-3 sm:flex-row">
        <a href="{{ route('register') }}" class="inline-flex min-w-44 items-center justify-center gap-2 rounded-lg bg-orange-500 px-6 py-3.5 text-xs font-extrabold text-white shadow-xl shadow-orange-200 transition hover:-translate-y-0.5 hover:bg-orange-600">
          Buat Survey Gratis <i class="fa-solid fa-arrow-right text-xs"></i>
        </a>
        <a href="#demo-dashboard" class="inline-flex min-w-44 items-center justify-center rounded-lg border border-orange-300 bg-white px-6 py-3.5 text-xs font-extrabold text-orange-500 transition hover:bg-orange-50">Lihat Demo Dashboard</a>
      </div>

      {{-- Dashboard mockup --}}
      <div id="demo-dashboard" class="dashboard-shadow relative mx-auto mt-12 w-full max-w-[940px] overflow-hidden rounded-[20px]">
        <img src="{{ asset('assets/dashboard.webp') }}" alt="Demo Dashboard Survey Center" class="w-full h-auto rounded-[20px]" loading="lazy">
      </div>{{-- /dashboard --}}
    </div>
  </section>


  {{-- Has Been Trusted --}}
  <section class="bg-white px-5 py-14 sm:py-16 overflow-hidden">
    <div class="mx-auto max-w-6xl text-center">
      <h2 class="text-2xl font-black text-slate-900 sm:text-3xl">Has Been <span class="text-orange-500">Trusted</span></h2>

      @if($partnerLogos->isNotEmpty())
        @php
          $half = ceil($partnerLogos->count() / 2);
          $row1 = $partnerLogos->take($half);
          $row2 = $partnerLogos->skip($half);
        @endphp

        {{-- Row 1: scroll left --}}
        <div class="mt-10 overflow-hidden [mask-image:linear-gradient(to_right,transparent,black_8%,black_92%,transparent)]">
          <div class="logo-marquee-left flex items-center gap-6 sm:gap-12 pr-6 sm:pr-12">
            @foreach($row1->concat($row1) as $logo)
              <img src="{{ asset('storage/'.$logo->logo_path) }}" alt="{{ $logo->name }}" loading="lazy" class="h-10 w-24 sm:h-14 sm:w-40 shrink-0 object-contain grayscale opacity-60 transition hover:grayscale-0 hover:opacity-100">
            @endforeach
          </div>
        </div>

        {{-- Row 2: scroll right --}}
        <div class="mt-4 sm:mt-6 overflow-hidden [mask-image:linear-gradient(to_right,transparent,black_8%,black_92%,transparent)]">
          <div class="logo-marquee-right flex items-center gap-6 sm:gap-12 pr-6 sm:pr-12">
            @foreach($row2->concat($row2) as $logo)
              <img src="{{ asset('storage/'.$logo->logo_path) }}" alt="{{ $logo->name }}" loading="lazy" class="h-10 w-24 sm:h-14 sm:w-40 shrink-0 object-contain grayscale opacity-60 transition hover:grayscale-0 hover:opacity-100">
            @endforeach
          </div>
        </div>
      @else
        @php
          $fallbackRow1 = ['PRASARANA', 'PGN', 'KBN', 'SEVENDREAM', 'Infokost', 'Telkom Property', 'TAMINA'];
          $fallbackRow2 = ['Prodia', 'SKIN+', 'Indosat', 'Perumnas', 'MASPION GROUP', 'Jasindo', 'BRI'];
        @endphp

        {{-- Fallback Row 1: scroll left --}}
        <div class="mt-10 overflow-hidden [mask-image:linear-gradient(to_right,transparent,black_8%,black_92%,transparent)]">
          <div class="logo-marquee-left flex items-center gap-8 sm:gap-14 pr-8 sm:pr-14">
            @foreach(array_merge($fallbackRow1, $fallbackRow1) as $brand)
              <span class="shrink-0 text-xs sm:text-sm font-black tracking-wide text-slate-400 whitespace-nowrap">{{ $brand }}</span>
            @endforeach
          </div>
        </div>

        {{-- Fallback Row 2: scroll right --}}
        <div class="mt-4 sm:mt-6 overflow-hidden [mask-image:linear-gradient(to_right,transparent,black_8%,black_92%,transparent)]">
          <div class="logo-marquee-right flex items-center gap-8 sm:gap-14 pr-8 sm:pr-14">
            @foreach(array_merge($fallbackRow2, $fallbackRow2) as $brand)
              <span class="shrink-0 text-xs sm:text-sm font-black tracking-wide text-slate-400 whitespace-nowrap">{{ $brand }}</span>
            @endforeach
          </div>
        </div>
      @endif
    </div>
  </section>

  {{-- Services --}}
  @php
    $serviceCards = [
      ['fa-face-smile', 'Survey Kepuasan Pelanggan', 'Ukur tingkat kepuasan pelanggan terhadap produk dan layanan Anda.'],
      ['fa-star', 'Survey Brand Awareness', 'Mengukur tingkat kesadaran merek dan memperkuat posisi di pasar.'],
      ['fa-map', 'Survey Potensi Pasar', 'Menggali potensi pasar dan peluang agar tepat sasaran sesuai segmen.'],
      ['fa-bullseye', 'Survey Segmentasi & Positioning', 'Memahami perilaku pasar dan posisi produk di benak konsumen.'],
      ['fa-user-group', 'Survey Pengembangan Produk / Jasa', 'Memastikan kualitas pengembangan produk baru berdasarkan data.'],
      ['fa-gears', 'Survey Pengukuran Indeks Kepuasan', 'Mengukur kepuasan masyarakat terhadap layanan publik atau bisnis.'],
      ['fa-binoculars', 'Survey Penelitian Pasar', 'Riset mendalam untuk memahami kondisi pasar dan peluang bisnis.'],
      ['fa-location-crosshairs', 'Survey Online & Offline', 'Pengumpulan data fleksibel untuk kebutuhan dan karakter responden.'],
    ];
  @endphp
  <section class="bg-white px-5 py-14 sm:py-16">
    <div class="mx-auto max-w-6xl">
      <div class="text-center">
        <span class="text-xs font-extrabold uppercase tracking-[.2em] text-orange-500">Layanan Kami</span>
        <h2 class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl">Layanan Survey Sesuai <span class="text-orange-500">Kebutuhan Anda</span></h2>
        <p class="mx-auto mt-3 max-w-xl text-xs leading-5 text-slate-500">Pilih layanan survey profesional yang dirancang untuk memberikan data akurat dan insight relevan bagi bisnis Anda.</p>
      </div>
      <div class="mt-9 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        @foreach($serviceCards as $service)
          @php $serviceModel = $jenis->first(fn($item) => str_contains(strtolower($item->title), strtolower(str_replace('Survey ', '', $service[1])))); @endphp
          <a href="{{ $serviceModel ? route('layanan.show', $serviceModel->slug) : route('contact') }}" class="group flex min-h-28 gap-3 rounded-xl border border-slate-100 bg-white p-4 shadow-[0_6px_20px_rgba(15,23,42,.04)] transition hover:-translate-y-1 hover:border-orange-200 hover:shadow-lg">
            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-orange-50 text-sm text-orange-500 transition group-hover:bg-orange-500 group-hover:text-white"><i class="fa-solid {{ $service[0] }}"></i></span>
            <span><strong class="block text-sm leading-4 text-slate-800">{{ $service[1] }}</strong><small class="mt-1.5 block text-xs leading-4 text-slate-400">{{ $service[2] }}</small></span>
          </a>
        @endforeach
      </div>
      <div class="mt-7 text-center"><a href="{{ route('contact') }}" class="inline-flex items-center gap-2 rounded-lg border border-orange-300 px-5 py-2.5 text-xs font-extrabold text-orange-500 hover:bg-orange-50">Lihat Semua Layanan <i class="fa-solid fa-arrow-right text-xs"></i></a></div>
    </div>
  </section>

  {{-- Project Delivered / Testimoni --}}
  <section class="bg-[#fbfcfe] px-5 py-14 sm:py-16">
    <div class="mx-auto max-w-6xl">
      <div class="text-center">
        <span class="inline-flex items-center gap-2 rounded-full bg-green-50 px-4 py-2 text-xs font-extrabold uppercase tracking-[.18em] text-green-600">
          <i class="fa-solid fa-circle-check"></i> Project Delivered
        </span>
        <p class="mt-3 text-sm text-slate-500">Beberapa cuplikan project yang pernah kami buat</p>
      </div>

      @if(isset($testimoniImages) && $testimoniImages->isNotEmpty())
        <div class="relative mt-8">
          {{-- Carousel container --}}
          <div id="testimoni-carousel" class="flex gap-4 overflow-x-auto scroll-smooth snap-x snap-mandatory pb-4 scrollbar-hide items-stretch">
            @foreach($testimoniImages as $testimoni)
              <div class="min-w-[200px] max-w-[220px] flex-shrink-0 snap-start h-[300px]">
                <img src="{{ asset('storage/'.$testimoni->image_path) }}" alt="Testimoni {{ $loop->iteration }}" loading="lazy" class="h-full w-full rounded-xl object-cover shadow-md border border-slate-100">
              </div>
            @endforeach
          </div>

          {{-- Navigation arrows --}}
          <button onclick="document.getElementById('testimoni-carousel').scrollBy({left:-300,behavior:'smooth'})" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-3 grid h-10 w-10 place-items-center rounded-full bg-orange-500 text-white shadow-lg hover:bg-orange-600 transition z-10">
            <i class="fa-solid fa-chevron-left text-sm"></i>
          </button>
          <button onclick="document.getElementById('testimoni-carousel').scrollBy({left:300,behavior:'smooth'})" class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-3 grid h-10 w-10 place-items-center rounded-full bg-orange-500 text-white shadow-lg hover:bg-orange-600 transition z-10">
            <i class="fa-solid fa-chevron-right text-sm"></i>
          </button>
        </div>
      @else
        <p class="mt-8 text-center text-sm text-slate-400">Belum ada testimoni yang ditampilkan.</p>
      @endif
    </div>
  </section>

  {{-- Articles --}}
  <section class="bg-[#fbfcfe] px-5 py-14 sm:py-16">
    <div class="mx-auto max-w-6xl">
      <div class="flex items-end justify-between gap-4">
        <div><span class="text-xs font-extrabold uppercase tracking-[.2em] text-orange-500">Insight</span><h2 class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl">Insight & Artikel Terbaru</h2></div>
        <a href="{{ route('blog.index') }}" class="hidden items-center gap-2 text-xs font-extrabold text-orange-500 sm:flex">Lihat Semua Artikel <i class="fa-solid fa-arrow-right"></i></a>
      </div>
      <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @forelse($articles->take(4) as $article)
          <article class="group overflow-hidden rounded-xl border border-slate-100 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
            <a href="{{ $article->slug ? route('blog.show', $article->slug) : route('blog.index') }}" class="relative block h-40 overflow-hidden bg-slate-100">
              <img src="{{ $article->image ? asset('storage/'.$article->image) : asset('assets/incase-768x247.jpg') }}" alt="{{ $article->title }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
              <span class="absolute bottom-3 left-3 rounded bg-orange-500 px-2 py-1 text-xs font-bold text-white">{{ $article->category ?? 'Market' }}</span>
            </a>
            <div class="p-4"><time class="text-xs text-slate-400">{{ $article->created_at->format('d M Y') }}</time><h3 class="mt-2 line-clamp-2 min-h-9 text-sm font-extrabold leading-[18px] text-slate-800">{{ $article->title }}</h3><a href="{{ $article->slug ? route('blog.show', $article->slug) : route('blog.index') }}" class="mt-4 inline-flex items-center gap-1.5 text-xs font-bold text-orange-500">Baca selengkapnya <i class="fa-solid fa-arrow-right text-sm"></i></a></div>
          </article>
        @empty
          @foreach(['Beyond Survey: Bagaimana Riset Market Membantu Mengubah Data Menjadi Keputusan Bisnis','Sebelum Meluncurkan Produk Baru, Wajib 7 Pertanyaan Penting Ini Terlebih Dahulu','Mengapa Banyak Keputusan Bisnis Gagal Tanpa Didukung Data?','2 Pilar Riset Market Research: Panduan Lengkap untuk Bisnis'] as $index => $title)
            <article class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-sm"><img src="{{ asset($index % 2 ? 'assets/incase-768x247.jpg' : 'assets/Harga-Survey-Center-1024x606.png') }}" alt="{{ $title }}" class="h-40 w-full object-cover"><div class="p-4"><span class="text-xs text-slate-400">22 Mei 2026</span><h3 class="mt-2 min-h-10 text-sm font-extrabold leading-[18px] text-slate-800">{{ $title }}</h3><a href="{{ route('blog.index') }}" class="mt-4 inline-block text-xs font-bold text-orange-500">Baca selengkapnya →</a></div></article>
          @endforeach
        @endforelse
      </div>
      <a href="{{ route('blog.index') }}" class="mt-7 flex items-center justify-center gap-2 text-xs font-extrabold text-orange-500 sm:hidden">Lihat Semua Artikel <i class="fa-solid fa-arrow-right"></i></a>
    </div>
  </section>

  {{-- CTA --}}
  <section class="relative overflow-hidden bg-gradient-to-r from-[#ff7a00] to-[#ff4d00] px-5 py-9 text-white">
    <div class="hero-dots absolute -right-8 top-0 h-full w-48 opacity-15"></div>
    <div class="mx-auto grid max-w-6xl items-center gap-6 sm:grid-cols-[140px_1fr_auto]">
      <img src="{{ asset('assets/owl-mascot.png') }}" alt="Maskot Survey Center" loading="lazy" class="mx-auto -mb-9 -mt-4 h-36 object-contain drop-shadow-xl sm:mx-0">
      <div class="text-center sm:text-left"><h2 class="text-xl font-black sm:text-2xl">Siap Mengambil Keputusan Bisnis yang Lebih Tepat?</h2><p class="mt-2 max-w-xl text-xs leading-5 text-white/80">Mulai kumpulkan data berkualitas bersama Survey Center Indonesia dan temukan insight yang benar-benar relevan bagi bisnis Anda.</p></div>
      <div class="flex flex-wrap justify-center gap-3 sm:justify-end"><a href="{{ route('register') }}" class="rounded-lg bg-amber-300 px-5 py-3 text-xs font-extrabold text-slate-900 shadow-lg hover:bg-amber-200">Buat Survey Gratis</a><a href="{{ route('contact') }}" class="rounded-lg border border-white/70 px-5 py-3 text-xs font-extrabold text-white hover:bg-white hover:text-orange-600">Hubungi Kami</a></div>
    </div>
  </section>
</div>
@endsection
