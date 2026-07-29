@extends('layouts.auth')
@section('seo_slug', 'login')

@push('styles')
<style>
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-[#f2f6f9] text-[#071D49] relative">
    
    {{-- Header --}}
    <header class="absolute top-0 left-0 right-0 w-full flex justify-between items-center px-8 lg:px-[8%] py-8 z-50">
        {{-- Logo --}}
        <a href="{{ url('/') }}" class="flex items-center gap-2.5 text-[22px] font-bold tracking-tight text-[#071D49]">
            <div class="flex items-center gap-[3px]">
                <div class="w-[3px] h-[15px] rounded-full bg-[#f97316]"></div>
                <div class="w-[4px] h-[22px] rounded-full bg-[#071D49]"></div>
                <div class="w-[3px] h-[15px] rounded-full bg-[#f97316]"></div>
            </div>
            <span>SurveyCenter</span>
        </a>
        
        {{-- Top Right --}}
        <div class="flex items-center gap-4 text-[13.5px] font-medium text-slate-500">
            <span class="hidden sm:inline text-[#64748b]">Belum ada akun?</span>
            <a href="{{ route('register') }}" class="px-5 py-2.5 rounded border border-slate-200 bg-white text-[#ea580c] font-bold shadow-sm hover:bg-slate-50 transition-colors">Sign Up</a>
        </div>
    </header>

    {{-- Main Container --}}
    <div class="flex w-full min-h-screen relative z-10">
        
        {{-- Left half - Blog Articles --}}
        <div class="hidden lg:flex w-[45%] h-full pt-[130px] px-[8%] flex-col">
            <h2 class="text-[20px] font-black mb-8 text-[#071D49] tracking-tight">What's new</h2>
            <div class="flex flex-col gap-6 overflow-y-auto pb-20 scrollbar-hide pr-4">
                @forelse($articles as $article)
                <a href="{{ route('blog.show', $article->slug) }}" class="flex gap-4 items-start group">
                    <img src="{{ $article->image ? asset('storage/' . $article->image) : 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=150&q=80' }}" 
                         class="w-[90px] h-[60px] rounded-md object-cover flex-shrink-0 transition-transform duration-300 group-hover:scale-105 border border-slate-200"
                         alt="{{ $article->title }}">
                    <div class="flex flex-col w-full px-1">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-[#0ea5e9] text-[11px] font-bold">{{ $article->category ?? 'SurveyCenter Update' }}</span>
                            <span class="text-slate-400 text-[10px] font-semibold tracking-wide uppercase">{{ $article->created_at->format('d M y') }}</span>
                        </div>
                        <h3 class="text-[13px] font-bold text-[#071D49] leading-snug group-hover:text-[#0ea5e9] transition-colors">{{ Str::limit($article->title, 70) }}</h3>
                    </div>
                </a>
                @empty
                <p class="text-slate-400 text-sm">Belum ada artikel.</p>
                @endforelse
            </div>
        </div>

        {{-- Right half - Login Form --}}
    {{-- Curved Background --}}
    <div class="absolute right-0 top-0 bottom-0 w-[58%] bg-white z-10 pointer-events-none hidden lg:block" 
         style="border-top-left-radius: 25% 50%; border-bottom-left-radius: 25% 50%; box-shadow: -15px 0 45px rgba(0,0,0,0.01);"></div>

    <div class="w-full lg:w-[55%] lg:ml-auto min-h-screen lg:min-h-[calc(100vh-64px)] flex flex-col justify-center items-center relative z-20 px-8 py-10 bg-white lg:bg-transparent">
        <div class="w-full max-w-[390px] my-auto">
            <h1 class="text-[34px] font-extrabold text-[#071D49] tracking-tight mb-[18px]">Log In</h1>
            
            <p class="text-[13px] text-slate-500 leading-relaxed max-w-[340px] mb-8 mt-1">
                Masukkan akun Anda untuk mengakses dashboard SurveyCenter.
            </p>

            {{-- Status Message --}}
            @if (session('status'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded text-[12.5px] mb-6 shadow-sm font-medium">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Error Messages --}}
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded text-[12.5px] mb-6 shadow-sm">
                    <ul class="list-disc ml-4 text-left font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <a href="{{ route('auth.google') }}" class="w-full py-3.5 bg-white border border-[#e2e8f0] hover:bg-slate-50 text-[#071D49] rounded-md font-bold text-[14.5px] transition-colors shadow-sm mb-5 mt-2 flex items-center justify-center gap-3">
                <svg viewBox="0 0 24 24" class="w-5 h-5">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Masuk dengan Google
            </a>

            <div class="flex items-center my-6">
                <div class="flex-grow border-t border-[#e2e8f0]"></div>
                <span class="px-4 text-[12px] text-slate-400 font-semibold uppercase">atau dengan email</span>
                <div class="flex-grow border-t border-[#e2e8f0]"></div>
            </div>

            <form action="{{ route('login.submit') }}" method="POST">
                @csrf
                <input type="hidden" name="redirect" value="{{ old('redirect', $redirect ?? request('redirect')) }}">
                <div class="mb-5">
                    <label class="block text-[10.5px] font-extrabold text-[#071D49] mb-2.5 tracking-widest uppercase" for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" 
                           class="w-full px-4 py-3 bg-[#f1f5f9] border-none rounded-md text-[14px] text-[#071D49] font-medium focus:ring-2 focus:ring-[#ea580c] focus:bg-white transition-all outline-none" 
                           placeholder="nama@email.com" required>
                </div>
                
                <div class="mb-6">
                    <label class="block text-[10.5px] font-extrabold text-[#071D49] mb-2.5 tracking-widest uppercase" for="password">Password</label>
                    <input type="password" id="password" name="password" 
                           class="w-full px-4 py-3 bg-[#f1f5f9] border-none rounded-md text-[14px] text-[#071D49] font-medium focus:ring-2 focus:ring-[#ea580c] focus:bg-white transition-all outline-none" 
                           placeholder="••••••••••••••" required>
                </div>

                <button type="submit" class="w-full py-3.5 bg-[#ea580c] hover:bg-[#c2410c] text-white rounded-md font-bold text-[14.5px] transition-colors shadow-sm mb-6 mt-2">
                    Log me in
                </button>
                
                <div class="text-[10.5px] text-slate-500 leading-relaxed mb-10">
                    Dengan melanjutkan, Anda memahami dan menyetujui penggunaan Kami atas informasi yang Anda sampaikan sesuai dengan ketentuan Pemberitahuan Privasi.
                </div>

                <div class="text-center mt-2">
                    <a href="{{ route('password.request') }}" class="inline-block text-[13px] font-semibold text-slate-500 hover:text-[#ea580c] transition-colors">Lupa kata sandi?</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
