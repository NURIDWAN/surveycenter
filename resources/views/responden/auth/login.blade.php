<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>Login Responden — SurveyCenter</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/logosc.png') }}">

    {{-- Tailwind CSS & JS via Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
    </style>
</head>

<body class="bg-[#f2f6f9] min-h-screen">
    <div class="min-h-screen flex flex-col items-center justify-center py-10 px-4 sm:px-6">

        {{-- Logo --}}
        <a href="{{ url('/') }}" class="flex items-center gap-2.5 mb-8">
            <div class="flex items-center gap-[3px]">
                <div class="w-[3px] h-[15px] rounded-full bg-[#f97316]"></div>
                <div class="w-[4px] h-[22px] rounded-full bg-[#071D49]"></div>
                <div class="w-[3px] h-[15px] rounded-full bg-[#f97316]"></div>
            </div>
            <span class="text-[22px] font-bold tracking-tight text-[#071D49]">SurveyCenter</span>
        </a>

        {{-- Card --}}
        <div class="w-full max-w-sm bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.06)] p-8 sm:p-10">

            {{-- Heading --}}
            <h1 class="text-[28px] font-extrabold text-[#071D49] tracking-tight mb-2 text-center">Masuk Responden</h1>
            <p class="text-[14px] text-slate-500 text-center mb-8 leading-relaxed">
                Masuk ke akun responden Anda untuk mulai mengisi survei.
            </p>

            {{-- Status Message --}}
            @if (session('status'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-[13px] mb-6 shadow-sm font-medium">
                    {{ session('status') }}
                </div>
            @endif

            {{-- General Error Messages --}}
            @if ($errors->any() && !$errors->has('email') && !$errors->has('password'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-[13px] mb-6 shadow-sm">
                    <ul class="list-disc ml-4 font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Credential error (without revealing which field is wrong) --}}
            @if ($errors->has('email') && !old('email_field_error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-[13px] mb-6 shadow-sm font-medium">
                    {{ $errors->first('email') }}
                </div>
            @endif

            <form action="{{ route('responden.login.submit') }}" method="POST">
                @csrf

                {{-- Email --}}
                <div class="mb-4">
                    <label class="block text-[11px] font-extrabold text-[#071D49] tracking-wider uppercase mb-2" for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           class="w-full px-4 py-3 bg-[#f8fafc] border border-slate-200 rounded-lg text-[14px] text-[#071D49] font-medium focus:ring-2 focus:ring-[#ea580c] focus:border-[#ea580c] focus:bg-white transition-all outline-none"
                           placeholder="nama@email.com" required>
                    @error('email')
                        @if(old('email_field_error'))
                            <p class="mt-1.5 text-[12px] text-red-600 font-medium">{{ $message }}</p>
                        @endif
                    @enderror
                </div>

                {{-- Password --}}
                <div class="mb-6">
                    <label class="block text-[11px] font-extrabold text-[#071D49] tracking-wider uppercase mb-2" for="password">Password</label>
                    <input type="password" id="password" name="password"
                           class="w-full px-4 py-3 bg-[#f8fafc] border border-slate-200 rounded-lg text-[14px] text-[#071D49] font-medium focus:ring-2 focus:ring-[#ea580c] focus:border-[#ea580c] focus:bg-white transition-all outline-none"
                           placeholder="Masukkan password Anda" required>
                    @error('password')
                        <p class="mt-1.5 text-[12px] text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Submit --}}
                <button type="submit" class="w-full py-3.5 bg-[#ea580c] hover:bg-[#c2410c] text-white rounded-lg font-bold text-[15px] transition-colors shadow-[0_4px_14px_0_rgba(234,88,12,0.3)] mb-6">
                    Masuk
                </button>

                {{-- Link to register --}}
                <div class="text-center">
                    <span class="text-[14px] text-slate-600 font-medium">
                        Belum punya akun? <a href="{{ route('responden.register') }}" class="text-[#ea580c] font-bold hover:underline">Daftar Sekarang</a>
                    </span>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
