@php
  $footerSettings = \App\Models\Setting::whereIn('key', [
    'footer_alamat', 'footer_whatsapp', 'footer_email', 'sosmed_facebook',
    'sosmed_twitter', 'sosmed_linkedin', 'sosmed_instagram', 'sosmed_tiktok'
  ])->pluck('value', 'key');
  $footerWa = preg_replace('/[^0-9]/', '', $footerSettings['footer_whatsapp'] ?? '6285198887963');
@endphp
<footer class="site-footer bg-[#111a2a] px-5 pb-5 pt-12 text-slate-300">

  <div class="mx-auto max-w-6xl">
    <div class="grid gap-10 border-b border-white/10 pb-10 sm:grid-cols-2 lg:grid-cols-[1.25fr_.8fr_.8fr_1.25fr]">
      <div>
        <a href="{{ route('landing') }}" class="flex items-center gap-2.5">
          <img src="{{ asset('assets/logosc.png') }}" alt="Survey Center Indonesia" class="h-11 w-11 object-contain">
          <div class="leading-none"><strong class="block text-xs font-extrabold text-orange-400">Survey Center<br>Indonesia</strong><span class="mt-1 block text-[6px] tracking-wide text-slate-500">PT. MARKET RESEARCH & BRANDING</span></div>
        </a>
        <p class="mt-5 max-w-xs text-[10px] leading-5 text-slate-400">Menyediakan layanan riset pasar terpercaya untuk membantu bisnis membuat keputusan yang lebih baik.</p>
        <div class="mt-5 flex gap-2">
          @foreach([['sosmed_instagram','fa-instagram'],['sosmed_linkedin','fa-linkedin-in'],['sosmed_twitter','fa-x-twitter'],['sosmed_tiktok','fa-tiktok']] as $social)
            @if(!empty($footerSettings[$social[0]]))<a href="{{ $footerSettings[$social[0]] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $social[0] }}" class="grid h-7 w-7 place-items-center rounded-full border border-white/10 text-[10px] transition hover:border-orange-400 hover:text-orange-400"><i class="fa-brands {{ $social[1] }}"></i></a>@endif
          @endforeach
          @if(!empty($footerSettings['sosmed_facebook']))<a href="{{ $footerSettings['sosmed_facebook'] }}" target="_blank" rel="noopener noreferrer" aria-label="Facebook" class="grid h-7 w-7 place-items-center rounded-full border border-white/10 text-[10px] hover:text-orange-400"><i class="fa-brands fa-facebook-f"></i></a>@endif
        </div>
      </div>
      <div><h3 class="text-xs font-extrabold text-white">Layanan</h3><ul class="mt-5 space-y-3 text-[9px] text-slate-400">@foreach($jenis->take(5) as $item)<li><a href="{{ route('layanan.show', $item->slug) }}" class="hover:text-orange-400">{{ $item->title }}</a></li>@endforeach @if($jenis->isEmpty())<li>Survey Kepuasan Pelanggan</li><li>Survey Brand Awareness</li><li>Survey Potensi Pasar</li><li>Survey Segmentasi</li><li>Survey Pengembangan Produk</li>@endif</ul></div>
      <div><h3 class="text-xs font-extrabold text-white">Perusahaan</h3><ul class="mt-5 space-y-3 text-[9px] text-slate-400"><li><a href="{{ route('about') }}" class="hover:text-orange-400">About Us</a></li><li><a href="{{ route('contact') }}" class="hover:text-orange-400">Our Team</a></li><li><a href="{{ route('blog.index') }}" class="hover:text-orange-400">Blog</a></li><li><a href="{{ route('pricing') }}" class="hover:text-orange-400">Karir</a></li><li><a href="{{ route('contact') }}" class="hover:text-orange-400">Contact Us</a></li></ul></div>
      <div><h3 class="text-xs font-extrabold text-white">Hubungi Kami</h3><div class="mt-5 space-y-3 text-[9px] leading-5 text-slate-400"><p class="flex gap-3"><i class="fa-solid fa-location-dot mt-1 text-orange-400"></i><span class="whitespace-pre-line">{{ $footerSettings['footer_alamat'] ?? "Scientia Residences Tower C, Lantai II\nJl. Scientia Square Utara, Curug Sangereng, Tangerang 15810" }}</span></p><p class="flex gap-3"><i class="fa-solid fa-envelope mt-1 text-orange-400"></i><a href="mailto:{{ $footerSettings['footer_email'] ?? 'info@surveycenter.co.id' }}" class="hover:text-orange-400">Email: {{ $footerSettings['footer_email'] ?? 'info@surveycenter.co.id' }}</a></p><p class="flex gap-3"><i class="fa-brands fa-whatsapp mt-1 text-orange-400"></i><a href="https://wa.me/{{ $footerWa }}" target="_blank" rel="noopener noreferrer" class="hover:text-orange-400">Telp: {{ $footerSettings['footer_whatsapp'] ?? '+62 851-9888-7963' }}</a></p></div></div>
    </div>
    <p class="pt-5 text-center text-[8px] text-slate-500">© {{ date('Y') }} Survey Center Indonesia. All rights reserved.</p>
  </div>
</footer>
