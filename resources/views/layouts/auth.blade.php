<!DOCTYPE html>
<html lang="id" class="h-full bg-paper">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Akun' }} - ResQHub</title>
    <meta name="robots" content="noindex">

    @include('layouts.partials.brand-head')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="flex min-h-full flex-col bg-paper font-sans text-ink antialiased">
    <header class="border-b border-line bg-white">
        <div class="mx-auto flex h-16 max-w-5xl items-center justify-between gap-4 px-4 sm:px-6">
            <a href="{{ route('home') }}" aria-label="ResQHub, kembali ke beranda">
                <img src="{{ asset('images/brand/logo-horizontal.webp') }}" alt="ResQHub" width="128" height="32" class="h-8 w-auto">
            </a>
            <a href="{{ route('public.shelters') }}" class="rq-link text-sm">Cari posko tanpa akun</a>
        </div>
    </header>

    <main class="flex flex-1 justify-center px-4 py-10 sm:py-14">
        <div class="w-full {{ $width ?? 'max-w-md' }}">
            {{ $slot }}
        </div>
    </main>

    <footer class="px-4 pb-6 text-center text-[13px] text-slate-600">
        Keadaan mengancam nyawa? <a href="tel:112" class="font-bold text-red-800 underline underline-offset-2">Telepon 112</a>
    </footer>

    @livewireScripts
</body>
</html>
