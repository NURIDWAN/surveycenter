<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $seoTitle ?? 'SurveyCenter' }}</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/logosc.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/logosc.png') }}">
    <link rel="shortcut icon" href="{{ asset('assets/logosc.png') }}" type="image/png">

    {{-- SEO Meta --}}
    <meta name="description" content="{{ $seoDesc ?? 'SurveyCenter - Jasa Survey Pasar & Sebar Kuesioner Terpercaya' }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:locale" content="id_ID">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="SurveyCenter">
    <meta property="og:title" content="{{ $seoTitle ?? 'SurveyCenter' }}">
    <meta property="og:description" content="{{ $seoDesc ?? 'SurveyCenter - Jasa Survey Pasar & Sebar Kuesioner Terpercaya' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('assets/logosc.png') }}">
    <meta property="og:image:width" content="500">
    <meta property="og:image:height" content="500">
    <meta property="og:image:alt" content="SurveyCenter">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $seoTitle ?? 'SurveyCenter' }}">
    <meta name="twitter:description" content="{{ $seoDesc ?? 'SurveyCenter - Jasa Survey Pasar & Sebar Kuesioner Terpercaya' }}">
    <meta name="twitter:image" content="{{ asset('assets/logosc.png') }}">

    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{-- Inter Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>

    {{-- Stacked Styles --}}
    @stack('styles')
</head>

<body class="bg-white">
    @yield('content')
</body>

</html>
