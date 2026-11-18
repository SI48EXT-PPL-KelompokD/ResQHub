<!DOCTYPE html>
<html lang="id" class="h-full bg-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Cari Posko Evakuasi Terdekat' }} - ResQHub</title>
    <meta name="description" content="{{ $description ?? 'Laporkan kejadian bencana, temukan posko evakuasi terdekat, dan lihat bantuan yang paling dibutuhkan.' }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="ResQHub">
    <meta property="og:title" content="{{ $title ?? 'Cari Posko Evakuasi Terdekat' }} - ResQHub">
    <meta property="og:description" content="{{ $description ?? 'Laporkan kejadian bencana, temukan posko evakuasi terdekat, dan lihat bantuan yang paling dibutuhkan.' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('images/brand/logo-vertikal.webp') }}">

    @include('layouts.partials.brand-head')

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <style>
        [x-cloak] { display: none !important; }
        .leaflet-container { font-family: inherit; }
        .pulsing-user-marker {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #1756e3;
            border: 3px solid #ffffff;
            box-shadow: 0 0 0 4px rgba(23, 86, 227, 0.4);
            animation: pulse-ring 2s infinite;
        }
        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(23, 86, 227, 0.6); }
            70% { box-shadow: 0 0 0 14px rgba(23, 86, 227, 0); }
            100% { box-shadow: 0 0 0 0 rgba(23, 86, 227, 0); }
        }
        @media (prefers-reduced-motion: reduce) {
            .pulsing-user-marker { animation: none; }
        }
    </style>

    @livewireStyles
</head>
<body class="flex min-h-full flex-col bg-white font-sans text-ink antialiased">
    @php
        $publicLinks = [
            ['label' => 'Beranda', 'route' => 'home'],
            ['label' => 'Peta Posko', 'route' => 'public.shelters'],
            ['label' => 'Kebutuhan Bantuan', 'route' => 'needs.public'],
        ];
    @endphp

    <a href="#konten" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:bg-white focus:p-3">Lewati ke konten</a>

    <div class="bg-red-700 text-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-1.5 text-[13px] sm:px-6 lg:px-8">
            <span>Keadaan mengancam nyawa? Jangan tunggu aplikasi.</span>
            <a href="tel:112" class="shrink-0 font-bold underline underline-offset-2">Telepon 112</a>
        </div>
    </div>

    <header x-data="{ menu: false }" class="sticky top-0 z-[1100] border-b border-line bg-white">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-6 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="shrink-0" aria-label="ResQHub, kembali ke beranda">
                <img src="{{ asset('images/brand/logo-horizontal.webp') }}" alt="ResQHub" width="144" height="36" class="h-8 w-auto sm:h-9">
            </a>

            <nav class="hidden flex-1 items-center gap-7 md:flex" aria-label="Navigasi utama">
                @foreach ($publicLinks as $link)
                    <a href="{{ route($link['route']) }}"
                       @if (request()->routeIs($link['route'])) aria-current="page" @endif
                       class="border-b-2 py-5 text-[15px] font-semibold transition-colors {{ request()->routeIs($link['route']) ? 'border-brand-600 text-ink' : 'border-transparent text-slate-600 hover:text-ink' }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="hidden items-center gap-2 md:flex">
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="rq-btn">Buka Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="rq-btn-outline">Masuk</a>
                    <a href="{{ route('register') }}" class="rq-btn">Daftar</a>
                @endauth
            </div>

            <button type="button" @click="menu = !menu" :aria-expanded="menu" aria-controls="menu-publik" class="-mr-2 inline-flex h-11 w-11 items-center justify-center rounded-md text-ink md:hidden">
                <span class="sr-only">Buka menu</span>
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path x-show="!menu" stroke-linecap="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16" />
                    <path x-show="menu" x-cloak stroke-linecap="round" stroke-width="2" d="M6 6l12 12M18 6L6 18" />
                </svg>
            </button>
        </div>

        <div id="menu-publik" x-show="menu" x-cloak class="border-t border-line bg-white px-4 pb-4 md:hidden">
            <nav class="flex flex-col py-2" aria-label="Navigasi utama seluler">
                @foreach ($publicLinks as $link)
                    <a href="{{ route($link['route']) }}" class="py-3 text-base font-semibold {{ request()->routeIs($link['route']) ? 'text-brand-600' : 'text-ink' }}">{{ $link['label'] }}</a>
                @endforeach
            </nav>
            <div class="grid grid-cols-2 gap-2">
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="rq-btn col-span-2">Buka Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="rq-btn-outline">Masuk</a>
                    <a href="{{ route('register') }}" class="rq-btn">Daftar</a>
                @endauth
            </div>
        </div>
    </header>

    <main id="konten" class="flex-1">
        {{ $slot }}
    </main>

    <footer class="bg-ink text-white">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-[2fr_1fr_1fr] lg:px-8">
            <div class="max-w-sm">
                <img src="{{ asset('images/brand/logo-horizontal-putih.webp') }}" alt="ResQHub" width="144" height="36" loading="lazy" class="h-9 w-auto">
                <p class="mt-4 text-[15px] leading-relaxed text-white/75">
                    Satu tempat untuk melaporkan kejadian, menemukan posko, dan menyalurkan bantuan saat bencana terjadi.
                </p>
            </div>
            <nav aria-label="Tautan layanan">
                <h2 class="text-sm font-bold">Layanan</h2>
                <ul class="mt-3 space-y-2 text-[15px] text-white/75">
                    <li><a href="{{ route('public.shelters') }}" class="hover:text-white">Peta posko</a></li>
                    <li><a href="{{ route('needs.public') }}" class="hover:text-white">Kebutuhan bantuan</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-white">Daftar akun</a></li>
                </ul>
            </nav>
            <div>
                <h2 class="text-sm font-bold">Darurat</h2>
                <a href="tel:112" class="mt-3 block text-3xl font-extrabold tabular-nums">112</a>
                <p class="mt-1 text-sm text-white/75">Layanan darurat nasional, bebas pulsa.</p>
            </div>
        </div>
        <div class="border-t border-white/15">
            <p class="mx-auto max-w-7xl px-4 py-4 text-[13px] text-white/60 sm:px-6 lg:px-8">
                &copy; {{ date('Y') }} ResQHub. Proyek Perangkat Lunak, Kelompok SI-48-EXT, Telkom University.
            </p>
        </div>
    </footer>

    @livewireScripts
    @stack('scripts')
</body>
</html>
