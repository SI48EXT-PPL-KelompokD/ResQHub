<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Pencarian Posko Evakuasi Terdekat' }} - ResQHub</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Leaflet Maps CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <!-- Styles & Scripts via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        .leaflet-container { font-family: inherit; }
        .pulsing-user-marker {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #2563eb;
            border: 3px solid #ffffff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.4);
            animation: pulse-ring 2s infinite;
        }
        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.6); }
            70% { box-shadow: 0 0 0 14px rgba(37, 99, 235, 0); }
            100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
        }
    </style>

    @livewireStyles
</head>
<body class="min-h-full flex flex-col antialiased text-slate-800 bg-slate-50 selection:bg-blue-600 selection:text-white">

    <!-- Top Emergency Navbar -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/90 shadow-2xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 gap-4">
                
                <!-- Logo & Brand -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('public.shelters') }}" class="flex items-center space-x-2.5 group">
                        <div class="w-9 h-9 rounded-xl bg-blue-600 group-hover:bg-blue-700 text-white flex items-center justify-center shadow-md shadow-blue-500/20 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-lg font-bold tracking-tight text-slate-900 group-hover:text-blue-600 transition">ResQHub</span>
                            <span class="hidden sm:inline-block ml-1.5 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-red-100 text-red-700 rounded-full border border-red-200">
                                Posko Siaga
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Center Nav Links -->
                <nav class="hidden md:flex items-center space-x-1">
                    <a 
                        href="{{ route('public.shelters') }}" 
                        class="px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-xl bg-blue-50 text-blue-700 transition"
                    >
                        Cari Posko Terdekat
                    </a>
                </nav>

                <!-- Right Actions -->
                <div class="flex items-center space-x-2.5">
                    <!-- Call Emergency Hotline 112 -->
                    <a 
                        href="tel:112" 
                        class="hidden sm:inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-red-700 bg-red-50 hover:bg-red-100 border border-red-200/80 transition"
                        title="Panggilan Darurat BPBD / Call Center 112"
                    >
                        <svg class="w-4 h-4 text-red-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <span>Darurat: 112</span>
                    </a>

                    @auth
                        <a 
                            href="{{ route('admin.dashboard') }}" 
                            class="inline-flex items-center space-x-1.5 px-3.5 py-2 text-xs sm:text-sm font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-xl shadow-xs transition"
                        >
                            <span>Dashboard Petugas</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @else
                        <a 
                            href="{{ route('login') }}" 
                            class="inline-flex items-center space-x-1 px-3.5 py-2 text-xs sm:text-sm font-semibold text-slate-700 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition"
                        >
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            <span>Masuk Petugas</span>
                        </a>
                    @endauth
                </div>

            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1">
        {{ $slot }}
    </main>

    <!-- Public Footer -->
    <footer class="bg-white border-t border-slate-200/90 py-8 text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center space-x-2">
                    <div class="w-6 h-6 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-xs">R</div>
                    <span class="font-bold text-slate-800">ResQHub</span>
                    <span>&bull;</span>
                    <span>Sistem Informasi Tanggap Bencana Terpadu</span>
                </div>
                <div class="flex items-center space-x-4 text-slate-600">
                    <span class="text-slate-400">PBI-04 Modul B: Posko Evakuasi</span>
                    <span>&bull;</span>
                    <span>&copy; {{ date('Y') }} ResQHub BPBD</span>
                </div>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
