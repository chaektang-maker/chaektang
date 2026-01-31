<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'แจกตัง') }}</title>

        <!-- SEO Meta Tags -->
        <meta name="description" content="แจกตัง - ระบบตรวจหวยออนไลน์ ตรวจผลสลากกินแบ่งรัฐบาล เลขเด็ด สถิติหวยย้อนหลัง ตารางคะแนนความแม่นยำ ตรวจหวยง่าย รวดเร็ว ปลอดภัย">
        <meta name="keywords" content="ตรวจหวย, ผลหวย, สลากกินแบ่ง, เลขเด็ด, สถิติหวย, ตารางคะแนน, ตรวจผลรางวัล, หวยออนไลน์, แจกตัง">
        <meta name="author" content="แจกตัง">
        <meta name="robots" content="index, follow">
        <meta name="language" content="Thai">
        <meta name="revisit-after" content="1 days">
        
        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ config('app.url') }}">
        <meta property="og:title" content="แจกตัง - ตรวจหวย">
        <meta property="og:description" content="ตรวจหวยออนไลน์ ตรวจผลสลากกินแบ่งรัฐบาล เลขเด็ด สถิติหวยย้อนหลัง ตารางคะแนนความแม่นยำ">
        <meta property="og:image" content="{{ config('app.url') }}/logo-29012026.jpg">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:image:alt" content="แจกตัง - Logo">
        <meta property="og:locale" content="th_TH">
        <meta property="og:site_name" content="{{ config('app.name', 'แจกตัง') }}">
        
        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:url" content="{{ config('app.url') }}">
        <meta name="twitter:title" content="{{ config('app.name', 'แจกตัง') }} - ระบบตรวจหวยออนไลน์">
        <meta name="twitter:description" content="ตรวจหวยออนไลน์ ตรวจผลสลากกินแบ่งรัฐบาล เลขเด็ด สถิติหวยย้อนหลัง">
        <meta name="twitter:image" content="{{ config('app.url') }}/logo-29012026.jpg">
        <meta name="twitter:image:alt" content="แจกตัง - Logo">
        
        <!-- Canonical URL -->
        <link rel="canonical" href="{{ config('app.url') }}{{ request()->getPathInfo() }}">
        
        <!-- Structured Data (JSON-LD) -->
        <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('app.name', 'แจกตัง'),
            'alternateName' => 'แจกตัง',
            'url' => config('app.url'),
            'description' => 'ระบบตรวจหวยออนไลน์ ตรวจผลสลากกินแบ่งรัฐบาล เลขเด็ด สถิติหวยย้อนหลัง ตารางคะแนนความแม่นยำ',
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => config('app.url') . '/search?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
        
        <!-- Favicon -->
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('logo-29012026.jpg') }}">

        <!-- Google Analytics -->
        @include('partials.google-analytics')
        
        <!-- Google AdSense -->
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-5880634868894114"
            crossorigin="anonymous"></script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased" style="font-family: 'Noto Sans Thai', 'Inter', sans-serif;">
        @inertia
    </body>
</html>
