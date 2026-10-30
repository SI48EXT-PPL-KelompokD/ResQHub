<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} - ResQHub</title>

    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full antialiased text-slate-800 bg-slate-50/60" x-data="{ sidebarOpen: false }">

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
            <!-- Brand Header (Fixed Top) -->
            <div class="h-16 flex items-center justify-between px-6 border-b border-slate-100 shrink-0">
                <a href="{{ route('admin.dashboard') }}" wire:navigate class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-sm shadow-blue-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-lg font-bold tracking-tight text-blue-600 leading-none block">ResQHub</span>
                        <span class="text-[11px] font-medium text-slate-500 tracking-tight block mt-0.5">
                            {{ Auth::user()->role_label ?? 'Pusdalops BPBD' }}
                        </span>
                    </div>
                </a>

                <!-- Close button on mobile -->
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Navigation Links (Scrollable Center) -->
            <nav class="p-3 space-y-1 overflow-y-auto flex-1 min-h-0">
                    
                    <!-- Dashboard Utama -->
                    <a 
                        href="{{ route('admin.dashboard') }}" 
                        wire:navigate
                        class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition duration-150 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-700 font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                    >
                        <svg class="w-5 h-5 {{ request()->routeIs('admin.dashboard') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <!-- Section Divider: Modul Operasional -->
                    <div class="pt-4 pb-1 px-3">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Modul Operasional</span>
                    </div>

                    <!-- Modul B: Data Posko (Maheswari) -->
                    @if (Auth::user()->canAccessModule('modul_b'))
                        <a 
                            href="#" 
                            class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition duration-150 group"
                            title="Modul B - Posko Evakuasi"
                        >
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span class="flex-1">Data Posko</span>
                            <span class="text-[10px] bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded font-mono">Modul B</span>
                        </a>
                    @endif

                    <!-- Modul C: Pengungsi & Kesehatan (Shalman) -->
                    @if (Auth::user()->canAccessModule('modul_c'))
                        <a 
                            href="{{ route('admin.refugees') }}" 
                            wire:navigate
                            class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition duration-150 group {{ request()->routeIs('admin.refugees*') ? 'bg-blue-50 text-blue-700 font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                            title="Modul C - Pengungsi & Kesehatan"
                        >
                            <svg class="w-5 h-5 {{ request()->routeIs('admin.refugees*') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span class="flex-1">Pengungsi</span>
                            <span class="text-[10px] {{ request()->routeIs('admin.refugees*') ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-500' }} px-1.5 py-0.5 rounded font-mono">Modul C</span>
                        </a>
                    @endif

                    <!-- Modul D: Kebutuhan Bantuan (Fadli) -->
                    @if (Auth::user()->canAccessModule('modul_d'))
                        <a 
                            href="#" 
                            class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition duration-150 group"
                            title="Modul D - Kebutuhan Bantuan"
                        >
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <span class="flex-1">Kebutuhan</span>
                            <span class="text-[10px] bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded font-mono">Modul D</span>
                        </a>
                    @endif

                    <!-- Modul E & F: Penerimaan Donasi (Ariq & Arifin) -->
                    @if (Auth::user()->canAccessModule('modul_e'))
                        <a 
                            href="#" 
                            class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition duration-150 group"
                            title="Modul E & F - Penerimaan Donasi"
                        >
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                            <span class="flex-1">Penerimaan Donasi</span>
                            <span class="text-[10px] bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded font-mono">Modul E/F</span>
                        </a>
                    @endif

                    <!-- Modul F: Stok & Penyaluran (Arifin) -->
                    @if (Auth::user()->canAccessModule('modul_f'))
                        <a 
                            href="#" 
                            class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition duration-150 group"
                            title="Modul F - Stok & Penyaluran"
                        >
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <span class="flex-1">Stok & Penyaluran</span>
                            <span class="text-[10px] bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded font-mono">Modul F</span>
                        </a>
                    @endif

                    <!-- Modul G: Tugas Relawan (Novendini) -->
                    @if (Auth::user()->canAccessModule('modul_g'))
                        <a 
                            href="#" 
                            class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition duration-150 group"
                            title="Modul G - Tugas Relawan"
                        >
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                            <span class="flex-1">{{ Auth::user()->isRelawan() ? 'Tugas Saya' : 'Tugas Relawan' }}</span>
                            <span class="text-[10px] bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded font-mono">Modul G</span>
                        </a>
                    @endif

                    <!-- Modul A: SOS Masuk & Laporan (Ivan) -->
                    @if (Auth::user()->canAccessModule('modul_a'))
                        <a 
                            href="#" 
                            class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition duration-150 group"
                            title="Modul A - SOS & Laporan"
                        >
                            <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span class="flex-1">{{ Auth::user()->isWarga() ? 'Kirim SOS & Lapor' : 'SOS Masuk' }}</span>
                            <span class="text-xs bg-rose-100 text-rose-700 px-2 py-0.5 rounded-full font-bold">2 Baru</span>
                        </a>
                    @endif

                    <!-- Section Divider: Konfigurasi Sistem (Hanya Admin) -->
                    @if (Auth::user()->isAdmin())
                        <div class="pt-4 pb-1 px-3">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Pengaturan Sistem</span>
                        </div>

                        <!-- User Management (Livewire wire:navigate) -->
                        <a 
                            href="{{ route('admin.users') }}" 
                            wire:navigate
                            class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition duration-150 {{ request()->routeIs('admin.users') ? 'bg-blue-50 text-blue-700 font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <svg class="w-5 h-5 {{ request()->routeIs('admin.users') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span>Manajemen Pengguna</span>
                        </a>
                    @endif

                </nav>
            <!-- Sidebar Footer (User info & Quick logout) -->
            <div class="p-3 border-t border-slate-100 bg-slate-50/80 shrink-0">
                <div class="flex items-center justify-between p-2">
                    <div class="flex items-center space-x-3 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                            {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-slate-800 truncate">{{ Auth::user()->name ?? 'Pengguna' }}</p>
                            <p class="text-[10px] text-slate-500 truncate">{{ Auth::user()->email ?? '-' }}</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Keluar dari akun" class="text-slate-400 hover:text-rose-600 p-1.5 rounded-lg hover:bg-white transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            <!-- Top App Bar Header -->
            <header class="h-16 bg-white border-b border-slate-200/80 flex items-center justify-between px-4 sm:px-8 z-30 sticky top-0">
                
                <!-- Left: Hamburger & Search Bar -->
                <div class="flex items-center space-x-4 flex-1 max-w-xl">
                    <button @click="sidebarOpen = true" class="lg:hidden text-slate-500 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Global Search Input -->
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            placeholder="Cari laporan, posko, pengungsi, bantuan..." 
                            class="w-full pl-10 pr-4 py-2 bg-slate-50 hover:bg-white focus:bg-white text-xs sm:text-sm text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                        />
                    </div>
                </div>

                <!-- Right: Active Status, Notifications, & User Profile Dropdown -->
                <div class="flex items-center space-x-3 sm:space-x-4">
                    
                    <!-- Status Kejadian Aktif Indicator -->
                    <div class="hidden md:flex items-center space-x-2 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-200/80 text-emerald-700 text-xs font-medium">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Sistem Aktif & Terhubung</span>
                    </div>

                    <!-- Notification Bell with Ping -->
                    <button class="relative p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-rose-500 ring-2 ring-white"></span>
                    </button>

                    <!-- User Name & Avatar -->
                    <div class="flex items-center space-x-3 pl-2 sm:pl-3 border-l border-slate-200">
                        <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                            {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="hidden sm:block text-left">
                            <span class="text-xs font-semibold text-slate-800 block leading-tight">{{ Auth::user()->name ?? 'Pengguna' }}</span>
                            <span class="text-[10px] text-slate-500 block leading-tight">{{ Auth::user()->role_label ?? 'Admin' }}</span>
                        </div>
                    </div>

                </div>

            </header>

            <!-- Main Dynamic Page Slot -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>

        </div>

    </div>

    @livewireScripts
</body>
</html>
