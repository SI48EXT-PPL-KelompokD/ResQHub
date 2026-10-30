<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk ke Akun Anda - ResQHub</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full antialiased text-slate-800">

    <div class="min-h-screen flex flex-col lg:flex-row">
        
        <!-- Left Banner (Branding) -->
        <div class="relative lg:w-1/2 bg-blue-600 flex flex-col justify-between p-8 sm:p-12 lg:p-16 text-white overflow-hidden">
            <!-- Subtle rescue grid background pattern -->
            <div class="absolute inset-0 opacity-10 pointer-events-none">
                <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">
                    <defs>
                        <pattern id="grid-pattern" width="40" height="40" patternUnits="userSpaceOnUse">
                            <path d="M 40 0 L 0 0 0 40" fill="none" stroke="currentColor" stroke-width="1"></path>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#grid-pattern)"></rect>
                </svg>
            </div>

            <!-- Top Left: Logo & Emergency Alert Tag -->
            <div class="relative z-10 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30 shadow-inner">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <span class="text-2xl font-bold tracking-tight text-white">ResQHub</span>
                </div>
                <span class="hidden sm:inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-500/50 text-blue-100 border border-blue-400/30">
                    Sistem Tanggap Bencana
                </span>
            </div>

            <!-- Center Content -->
            <div class="relative z-10 my-auto py-12 max-w-lg">
                <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight leading-tight text-white mb-6">
                    ResQHub
                </h1>
                <p class="text-base sm:text-lg text-blue-100/90 leading-relaxed font-normal">
                    Platform kolaborasi masyarakat dalam kesiapsiagaan dan respons bencana: laporan, posko, donasi, logistik, dan relawan dalam satu tempat.
                </p>

                <!-- Key highlights list -->
                <div class="mt-8 space-y-3.5 pt-6 border-t border-blue-500/40">
                    <div class="flex items-center space-x-3 text-sm text-blue-100">
                        <div class="w-5 h-5 rounded-full bg-blue-500/60 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span>Pelaporan darurat SOS & pemetaan posko terdekat secara real-time</span>
                    </div>
                    <div class="flex items-center space-x-3 text-sm text-blue-100">
                        <div class="w-5 h-5 rounded-full bg-blue-500/60 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span>Pencatatan logistik transparan dari donatur hingga pengungsi</span>
                    </div>
                    <div class="flex items-center space-x-3 text-sm text-blue-100">
                        <div class="w-5 h-5 rounded-full bg-blue-500/60 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span>Pemberian tugas relawan terintegrasi berbasis keahlian & lokasi</span>
                    </div>
                </div>
            </div>

            <!-- Bottom Left: Institutional Identity -->
            <div class="relative z-10 text-xs text-blue-200/80 flex items-center justify-between pt-6">
                <span>Kelompok SI-48-EXT B-A • Telkom University</span>
                <span class="font-mono">v1.0-Sprint 1</span>
            </div>
        </div>

        <!-- Right: Login Form Card -->
        <div class="lg:w-1/2 flex items-center justify-center p-6 sm:p-12 bg-slate-50/80">
            <div class="w-full max-w-md">

                <!-- Main Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-8 sm:p-10">
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold tracking-tight text-slate-900">
                            Masuk ke akun Anda
                        </h2>
                        <p class="mt-1.5 text-sm text-slate-500">
                            Gunakan kredensial akun terdaftar untuk mengakses dashboard.
                        </p>
                    </div>

                    <!-- Flash / Validation Errors -->
                    @if ($errors->any())
                        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200/80 text-red-700 text-sm">
                            <div class="flex items-start space-x-3">
                                <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <div class="flex-1">
                                    <ul class="list-disc pl-4 space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Form -->
                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <!-- Login Field (Email or Phone) -->
                        <div>
                            <label for="login" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                                Email atau nomor HP
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                    </svg>
                                </div>
                                <input 
                                    id="login" 
                                    name="login" 
                                    type="text" 
                                    value="{{ old('login') }}" 
                                    required 
                                    autofocus 
                                    placeholder="nama@contoh.com atau 0812..." 
                                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition bg-slate-50/50 hover:bg-white"
                                />
                            </div>
                        </div>

                        <!-- Password Field -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                                    Kata sandi
                                </label>
                                <a href="#" class="text-xs font-medium text-blue-600 hover:text-blue-700 hover:underline">
                                    Lupa kata sandi?
                                </a>
                            </div>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <input 
                                    id="password" 
                                    name="password" 
                                    type="password" 
                                    required 
                                    placeholder="••••••••" 
                                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition bg-slate-50/50 hover:bg-white"
                                />
                            </div>
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center">
                            <input 
                                id="remember" 
                                name="remember" 
                                type="checkbox" 
                                class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500 cursor-pointer"
                            />
                            <label for="remember" class="ml-2 block text-sm text-slate-600 cursor-pointer select-none">
                                Ingat saya
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button 
                            type="submit" 
                            class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-medium text-sm rounded-lg shadow-sm hover:shadow transition duration-150 flex items-center justify-center space-x-2"
                        >
                            <span>Masuk</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </form>

                    <!-- Footer Link -->
                    <div class="mt-8 pt-6 border-t border-slate-100 text-center">
                        <p class="text-sm text-slate-600">
                            Belum punya akun? 
                            <a href="{{ route('register') }}" class="font-medium text-blue-600 hover:text-blue-700 hover:underline">
                                Daftar sebagai Warga, Donatur, atau Relawan
                            </a>
                        </p>
                    </div>
                </div>

                <!-- Quick Test Login Helper (Sangat membantu rekan tim saat testing) -->
                <div class="mt-6 p-4 rounded-xl bg-white/70 border border-slate-200/70 shadow-sm backdrop-blur">
                    <div class="flex items-center justify-between mb-2.5">
                        <span class="text-xs font-semibold text-slate-700 uppercase tracking-wider flex items-center space-x-1.5">
                            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Akun Uji Coba Cepat (Password: password)</span>
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <button type="button" onclick="fillCreds('admin@resqhub.id')" class="px-2.5 py-1.5 text-left rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blue-700 border border-slate-200/60 transition">
                            <span class="font-semibold block text-slate-800">Admin Pusdalops</span>
                            <span class="text-[11px] text-slate-500">admin@resqhub.id</span>
                        </button>
                        <button type="button" onclick="fillCreds('koordinator.budi@resqhub.id')" class="px-2.5 py-1.5 text-left rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blue-700 border border-slate-200/60 transition">
                            <span class="font-semibold block text-slate-800">Koordinator Posko</span>
                            <span class="text-[11px] text-slate-500">koordinator.budi...</span>
                        </button>
                        <button type="button" onclick="fillCreds('nakes.hendra@resqhub.id')" class="px-2.5 py-1.5 text-left rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blue-700 border border-slate-200/60 transition">
                            <span class="font-semibold block text-slate-800">Tenaga Kesehatan</span>
                            <span class="text-[11px] text-slate-500">nakes.hendra...</span>
                        </button>
                        <button type="button" onclick="fillCreds('relawan.rian@resqhub.id')" class="px-2.5 py-1.5 text-left rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blue-700 border border-slate-200/60 transition">
                            <span class="font-semibold block text-slate-800">Relawan SAR</span>
                            <span class="text-[11px] text-slate-500">relawan.rian...</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <script>
        function fillCreds(email) {
            document.getElementById('login').value = email;
            document.getElementById('password').value = 'password';
        }
    </script>
</body>
</html>
