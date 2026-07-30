@extends('layouts.auth')
@section('seo_slug', 'register')

@push('styles')
<style>
    body { 
        font-family: 'Inter', sans-serif; 
        background-color: #fff6f0; 
        position: relative; 
        overflow-x: hidden; 
    }
    /* Abstract background shapes */
    body::before {
        content: "";
        position: absolute;
        top: -20%;
        left: -10%;
        width: 60%;
        height: 60%;
        background: radial-gradient(circle, rgba(253,186,116,0.25) 0%, rgba(255,255,255,0) 70%);
        z-index: 0;
    }
    body::after {
        content: "";
        position: absolute;
        top: 40%;
        right: -20%;
        width: 80%;
        height: 80%;
        background: radial-gradient(circle, rgba(253,186,116,0.3) 0%, rgba(255,255,255,0) 70%);
        z-index: 0;
    }
    .form-input-container { position: relative; }
    .form-input-icon-left {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #f97316;
        font-size: 16px;
    }
    .form-input-icon-right {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #f97316;
        font-size: 16px;
        cursor: pointer;
    }
    .form-input-with-icon {
        padding-left: 48px;
        padding-right: 48px;
    }
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    .dot-pattern {
        position: absolute;
        top: 24px;
        right: 24px;
        width: 60px;
        height: 60px;
        background-image: radial-gradient(#f97316 2px, transparent 2px);
        background-size: 14px 14px;
        opacity: 0.3;
    }
    .bg-shape-left {
        position: absolute;
        left: -150px;
        top: 20%;
        width: 300px;
        height: 600px;
        background: #ffedd5;
        border-radius: 50%;
        filter: blur(40px);
        opacity: 0.8;
        z-index: 1;
    }
    .bg-shape-right {
        position: absolute;
        right: -150px;
        bottom: 10%;
        width: 400px;
        height: 400px;
        background: #ffedd5;
        border-radius: 50%;
        filter: blur(50px);
        opacity: 0.8;
        z-index: 1;
    }
</style>
@endpush

@section('content')
<div class="bg-shape-left"></div>
<div class="bg-shape-right"></div>

<div class="min-h-screen flex flex-col items-center justify-center py-10 px-4 sm:px-6 relative z-10">
    <div class="w-full max-w-[560px] bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.06)] border border-orange-50 p-8 sm:p-12 relative z-20 overflow-hidden">
        
        <!-- Decorative dot pattern top right -->
        <div class="dot-pattern"></div>

        <!-- Logo -->
        <div class="flex items-center justify-center gap-2.5 mb-8 relative z-10">
            <div class="w-10 h-10 bg-white border border-orange-200 rounded-[10px] flex items-center justify-center shadow-sm">
                <i class="fa-solid fa-check text-orange-500 text-[20px]"></i>
            </div>
            <div class="flex flex-col justify-center">
                <span class="text-[24px] font-extrabold text-slate-800 leading-none">SurveyCenter</span>
                <span class="text-[10px] font-bold text-slate-600 tracking-[0.2em] mt-[3px]">INDONESIA</span>
            </div>
        </div>
        
        <!-- Heading -->
        <h1 class="text-[26px] sm:text-[30px] font-extrabold text-[#1e293b] leading-[1.3] text-center mb-3">
            Buat Akun & <br class="hidden sm:block">
            Dapatkan <span class="text-[#ea580c]">Responden Berkualitas</span>
        </h1>
        
        <!-- Subheading -->
        <p class="text-slate-500 text-[14px] sm:text-[15px] font-medium text-center mb-6 px-2 leading-relaxed">
            Kelola survei, temukan responden yang tepat, dan dapatkan data yang valid.
        </p>

        {{-- Role Selector --}}
        <div x-data="{ role: 'survey' }">
        <div class="flex rounded-lg bg-[#f1f5f9] p-1 mb-8">
            <button type="button" @click="role = 'survey'" :class="role === 'survey' ? 'bg-white shadow-sm text-[#ea580c] font-bold' : 'text-slate-500 font-medium'" class="flex-1 py-2.5 px-3 rounded-md text-[13px] transition-all text-center">
                Buat Survey
            </button>
            <button type="button" @click="role = 'responden'" :class="role === 'responden' ? 'bg-white shadow-sm text-[#ea580c] font-bold' : 'text-slate-500 font-medium'" class="flex-1 py-2.5 px-3 rounded-md text-[13px] transition-all text-center">
                Suarakan Pendapatmu
            </button>
        </div>

        <!-- Features -->
        <div class="grid grid-cols-3 gap-3 sm:gap-4 mb-8" x-show="role === 'survey'" x-transition>
            <div class="flex flex-col items-center text-center gap-2.5">
                <div class="w-[52px] h-[52px] rounded-full bg-orange-50 flex items-center justify-center">
                    <i class="fa-solid fa-users text-orange-500 text-[22px]"></i>
                </div>
                <span class="text-[11.5px] sm:text-[12px] font-semibold text-slate-700 leading-[1.3]">Akses ribuan<br>responden aktif</span>
            </div>
            <div class="flex flex-col items-center text-center gap-2.5">
                <div class="w-[52px] h-[52px] rounded-full border border-orange-100 flex items-center justify-center">
                    <i class="fa-solid fa-bullseye text-orange-500 text-[22px]"></i>
                </div>
                <span class="text-[11.5px] sm:text-[12px] font-semibold text-slate-700 leading-[1.3]">Targeting sesuai<br>kebutuhan Anda</span>
            </div>
            <div class="flex flex-col items-center text-center gap-2.5">
                <div class="w-[52px] h-[52px] rounded-full border border-orange-100 flex items-center justify-center">
                    <i class="fa-solid fa-chart-column text-orange-500 text-[22px]"></i>
                </div>
                <span class="text-[11.5px] sm:text-[12px] font-semibold text-slate-700 leading-[1.3]">Data valid &<br>terpercaya</span>
            </div>
        </div>

        <!-- Google Auth -->
        <a :href="'{{ route('auth.google') }}' + '?role=' + role" class="w-full py-3.5 bg-white border border-slate-200 hover:bg-slate-50 hover:border-slate-300 text-slate-800 rounded-lg font-bold text-[15px] transition-all shadow-sm flex items-center justify-center gap-3 mb-6">
            <svg viewBox="0 0 24 24" class="w-5 h-5">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            Daftar dengan Google
        </a>

        <!-- Divider -->
        <div class="flex items-center my-6">
            <div class="flex-grow border-t border-slate-200"></div>
            <span class="px-4 text-[12px] text-[#ea580c] font-bold uppercase tracking-wider">ATAU DENGAN EMAIL</span>
            <div class="flex-grow border-t border-slate-200"></div>
        </div>

        <!-- Error Messages -->
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm mb-6 shadow-sm">
                <ul class="list-disc ml-4 text-left font-medium">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form :action="role === 'responden' ? '{{ route('responden.register.submit') }}' : '{{ route('register.post') }}'" method="POST">
            @csrf
            
            <div class="mb-4">
                <label class="block text-[11px] font-extrabold text-slate-800 tracking-wider uppercase mb-2" for="name">NAMA LENGKAP</label>
                <div class="form-input-container">
                    <i class="fa-regular fa-user form-input-icon-left"></i>
                    <input type="text" id="name" :name="role === 'responden' ? 'nama' : 'name'" value="{{ old('name', old('nama')) }}" 
                           class="w-full form-input-with-icon py-[14px] bg-white border border-slate-200 rounded-lg text-[14px] text-slate-800 font-medium focus:ring-1 focus:ring-orange-500 focus:border-orange-500 transition-all outline-none shadow-sm" 
                           placeholder="Masukkan nama lengkap Anda" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-[11px] font-extrabold text-slate-800 tracking-wider uppercase mb-2" for="email">EMAIL</label>
                <div class="form-input-container">
                    <i class="fa-regular fa-envelope form-input-icon-left"></i>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" 
                           class="w-full form-input-with-icon py-[14px] bg-white border border-slate-200 rounded-lg text-[14px] text-slate-800 font-medium focus:ring-1 focus:ring-orange-500 focus:border-orange-500 transition-all outline-none shadow-sm" 
                           placeholder="nama@email.com" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-[11px] font-extrabold text-slate-800 tracking-wider uppercase mb-2" for="phone" x-text="role === 'responden' ? 'NOMOR WHATSAPP' : 'NOMOR TELEPON'">NOMOR TELEPON</label>
                <div class="form-input-container">
                    <i class="fa-solid fa-phone-flip form-input-icon-left"></i>
                    <input type="tel" id="phone" :name="role === 'responden' ? 'whatsapp_number' : 'phone'" value="{{ old('phone', old('whatsapp_number')) }}" 
                           class="w-full form-input-with-icon py-[14px] bg-white border border-slate-200 rounded-lg text-[14px] text-slate-800 font-medium focus:ring-1 focus:ring-orange-500 focus:border-orange-500 transition-all outline-none shadow-sm" 
                           placeholder="08xxxxxxxxxx" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-[11px] font-extrabold text-slate-800 tracking-wider uppercase mb-2" for="password">PASSWORD</label>
                <div class="form-input-container">
                    <i class="fa-solid fa-lock form-input-icon-left"></i>
                    <input type="password" id="password" name="password" 
                           class="w-full form-input-with-icon py-[14px] bg-white border border-slate-200 rounded-lg text-[14px] text-slate-800 font-medium focus:ring-1 focus:ring-orange-500 focus:border-orange-500 transition-all outline-none shadow-sm" 
                           placeholder="Minimal 8 karakter" required>
                    <i class="fa-regular fa-eye-slash form-input-icon-right toggle-password" data-target="password"></i>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-[11px] font-extrabold text-slate-800 tracking-wider uppercase mb-2" for="password_confirmation">KONFIRMASI PASSWORD</label>
                <div class="form-input-container">
                    <i class="fa-solid fa-lock form-input-icon-left"></i>
                    <input type="password" id="password_confirmation" name="password_confirmation" 
                           class="w-full form-input-with-icon py-[14px] bg-white border border-slate-200 rounded-lg text-[14px] text-slate-800 font-medium focus:ring-1 focus:ring-orange-500 focus:border-orange-500 transition-all outline-none shadow-sm" 
                           placeholder="Masukkan ulang password Anda" required>
                    <i class="fa-regular fa-eye-slash form-input-icon-right toggle-password" data-target="password_confirmation"></i>
                </div>
            </div>

            <!-- Terms and Privacy Checkbox -->
            <div class="flex items-start gap-3 mb-6">
                <input type="checkbox" id="terms" name="terms" class="mt-[2px] flex-shrink-0 w-4 h-4 border-slate-300 rounded text-[#ea580c] focus:ring-[#ea580c] cursor-pointer" required>
                <label for="terms" class="text-[13px] text-slate-600 leading-[1.5] font-medium cursor-pointer">
                    Saya menyetujui <a href="#" class="text-[#ea580c] hover:text-[#c2410c] font-semibold transition-colors">Kebijakan Privasi</a> dan <a href="#" class="text-[#ea580c] hover:text-[#c2410c] font-semibold transition-colors">Ketentuan Layanan</a>
                </label>
            </div>

            <button type="submit" class="w-full py-[14px] bg-[#ea580c] hover:bg-[#c2410c] text-white rounded-lg font-bold text-[16px] transition-colors shadow-[0_4px_14px_0_rgba(234,88,12,0.39)] mb-6">
                Buat Akun & Mulai
            </button>
            
            <div class="flex items-center justify-center gap-2 mb-8 text-[12.5px] text-slate-500 font-medium">
                <svg class="w-[18px] h-[18px] text-[#ea580c]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                Data Anda aman dan hanya digunakan untuk keperluan layanan.
            </div>

            <div class="text-center">
                <span class="text-[14px] text-slate-600 font-medium">
                    Sudah punya akun? <a href="{{ route('login') }}" class="text-[#ea580c] font-bold hover:underline">Masuk</a>
                </span>
            </div>
        </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('.toggle-password').forEach(function(icon) {
        icon.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            
            if (input.type === 'password') {
                input.type = 'text';
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
            } else {
                input.type = 'password';
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            }
        });
    });
</script>
@endpush
@endsection
