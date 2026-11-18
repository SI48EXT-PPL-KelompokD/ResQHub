<!DOCTYPE html>
<html lang="id" class="h-full bg-paper">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ \Illuminate\Support\Str::of($title ?? 'Dashboard')->replaceLast(' - ResQHub', '') }} - ResQHub</title>

    @include('layouts.partials.brand-head')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <!-- Leaflet.js CSS (OpenStreetMap) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    @stack('styles')
    
    <style>
        [x-cloak] { display: none !important; }
        /* Leaflet tile reset for Tailwind */
        .leaflet-container img {
            max-width: none !important;
        }
        .leaflet-pane {
            z-index: 10 !important;
        }
        .leaflet-top, .leaflet-bottom {
            z-index: 20 !important;
        }
    </style>
</head>
<body class="h-full bg-paper font-sans text-slate-800 antialiased" x-data="{ sidebarOpen: false }">

    <div class="min-h-screen flex flex-col lg:flex-row">
        
        <!-- Mobile Sidebar Backdrop -->
        <div 
            x-show="sidebarOpen" 
            x-cloak
            @click="sidebarOpen = false" 
            class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden transition-opacity"
        ></div>

        <!-- Sidebar Navigation (Sticky Viewport Height) -->
        <aside 
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200/80 flex flex-col transition-transform duration-200 ease-in-out lg:sticky lg:top-0 lg:h-screen shrink-0"
        >
            <!-- Brand -->
            <div class="flex h-16 shrink-0 items-center justify-between border-b border-slate-200 px-5">
                <a href="{{ route('admin.dashboard') }}" wire:navigate aria-label="ResQHub, buka dashboard">
                    <img src="{{ asset('images/brand/logo-horizontal.webp') }}" alt="ResQHub" width="128" height="32" class="h-8 w-auto">
                </a>
                <button type="button" @click="sidebarOpen = false" class="inline-flex h-10 w-10 cursor-pointer items-center justify-center rounded-md text-slate-500 hover:bg-slate-100 lg:hidden">
                    <span class="sr-only">Tutup menu</span>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-width="2" d="M6 6l12 12M18 6L6 18" /></svg>
                </button>
            </div>

            <!-- Menu sesuai peran -->
            <nav class="min-h-0 flex-1 overflow-y-auto px-3 py-4" aria-label="Menu aplikasi">
                @foreach (\App\Support\AppNavigation::forUser(Auth::user()) as $section)
                    <div class="{{ $loop->first ? '' : 'mt-5' }}">
                        @if ($section['title'])
                            <h2 class="mb-1 px-3 text-xs font-semibold text-slate-500">{{ $section['title'] }}</h2>
                        @endif
                        <ul class="space-y-0.5">
                            @foreach ($section['items'] as $item)
                                <li>
                                    @if ($item['soon'])
                                        <span aria-disabled="true" title="Fitur ini sedang disiapkan" class="flex h-9 cursor-not-allowed items-center gap-2.5 rounded-md px-3 text-sm text-slate-400">
                                            @include('layouts.partials.nav-icon', ['name' => $item['icon']])
                                            <span class="min-w-0 flex-1 truncate">{{ $item['label'] }}</span>
                                            <span class="shrink-0 text-[11px] font-medium">Segera</span>
                                        </span>
                                    @else
                                        @php $isActive = request()->routeIs(...$item['active']); @endphp
                                        <a href="{{ route($item['route']) }}"
                                           @if (str_starts_with($item['route'], 'admin.')) wire:navigate @endif
                                           @if ($isActive) aria-current="page" @endif
                                           class="flex h-9 items-center gap-2.5 rounded-md px-3 text-sm transition-colors {{ $isActive ? 'bg-brand-50 font-semibold text-brand-700' : 'font-medium text-slate-700 hover:bg-slate-100 hover:text-ink' }}">
                                            @include('layouts.partials.nav-icon', ['name' => $item['icon']])
                                            <span class="min-w-0 flex-1 truncate">{{ $item['label'] }}</span>
                                            @if ($item['badge'] > 0)
                                                <span class="shrink-0 rounded-full bg-red-700 px-1.5 text-[11px] leading-[18px] font-bold text-white tabular-nums">{{ $item['badge'] }}</span>
                                            @endif
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </nav>

            <!-- Akun -->
            <div class="shrink-0 border-t border-slate-200 p-3">
                <a href="{{ route('home') }}" class="mb-2 flex h-9 items-center gap-2.5 rounded-md px-3 text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-ink">
                    @include('layouts.partials.nav-icon', ['name' => 'map'])
                    <span>Lihat Portal Publik</span>
                </a>
                <div class="flex items-center gap-2.5 px-2">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-ink text-xs font-bold text-white">{{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-[13px] font-semibold text-ink">{{ Auth::user()->name ?? 'Pengguna' }}</p>
                        <p class="truncate text-xs text-slate-500">{{ Auth::user()->role_label }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Keluar dari akun" class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-md text-slate-500 hover:bg-red-50 hover:text-red-700">
                            <span class="sr-only">Keluar</span>
                            <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" /></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            <!-- Bilah atas -->
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between gap-3 border-b border-slate-200 bg-white px-4 sm:px-6 lg:px-8">
                <button type="button" @click="sidebarOpen = true" class="-ml-2 inline-flex h-10 w-10 cursor-pointer items-center justify-center rounded-md text-slate-600 hover:bg-slate-100 lg:hidden">
                    <span class="sr-only">Buka menu</span>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16" /></svg>
                </button>

                <div class="ml-auto flex items-center gap-2 sm:gap-3">
                    @livewire('layout.notification-bell')

                    @if (Auth::user()->canManageVolunteerProfile())
                        <button type="button" x-on:click="$dispatch('open-profile')" class="flex h-10 cursor-pointer items-center gap-2.5 rounded-md border-l border-slate-200 pr-2 pl-3 text-left hover:bg-slate-50" title="Buka profil keahlian">
                            <span class="hidden sm:block">
                                <span class="block text-[13px] leading-tight font-semibold text-ink">{{ Auth::user()->name }}</span>
                                <span class="block text-xs leading-tight text-slate-500">Profil keahlian</span>
                            </span>
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-ink text-xs font-bold text-white">{{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}</span>
                        </button>
                    @else
                        <div class="flex items-center gap-2.5 border-l border-slate-200 pl-3">
                            <span class="hidden sm:block">
                                <span class="block text-[13px] leading-tight font-semibold text-ink">{{ Auth::user()->name }}</span>
                                <span class="block text-xs leading-tight text-slate-500">{{ Auth::user()->role_label }}</span>
                            </span>
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-ink text-xs font-bold text-white">{{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}</span>
                        </div>
                    @endif
                </div>
            </header>

            <!-- Main Dynamic Page Slot -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                {{ $slot ?? '' }}
                @yield('content')
            </main>

        </div>

    </div>

    <!-- Leaflet.js (OpenStreetMap) -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    @if (Auth::user()?->canManageVolunteerProfile())
        @livewire('volunteer.profile-manager')
    @endif

    @livewireScripts
    @stack('scripts')
</body>
</html>
