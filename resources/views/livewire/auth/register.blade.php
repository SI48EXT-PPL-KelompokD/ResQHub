<div class="min-h-screen flex flex-col lg:flex-row">
    
    <!-- Left Banner (Branding) -->
    <div class="relative lg:w-5/12 bg-blue-600 flex flex-col justify-between p-8 sm:p-12 lg:p-14 text-white overflow-hidden">
        <!-- Background Rescue Pattern -->
        <div class="absolute inset-0 opacity-10 pointer-events-none">
            <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">
                <defs>
                    <pattern id="grid-pattern-reg" width="40" height="40" patternUnits="userSpaceOnUse">
                        <path d="M 40 0 L 0 0 0 40" fill="none" stroke="currentColor" stroke-width="1"></path>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#grid-pattern-reg)"></rect>
            </svg>
        </div>

        <!-- Top Left: Logo & Emergency Alert Tag -->
        <div class="relative z-10 flex items-center justify-between">
            <a href="{{ route('login') }}" class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30 shadow-inner">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <span class="text-2xl font-bold tracking-tight text-white">ResQHub</span>
            </a>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-500/50 text-blue-100 border border-blue-400/30">
                Pendaftaran Terintegrasi
            </span>
        </div>

        <!-- Center Content -->
        <div class="relative z-10 my-auto py-8">
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight leading-tight text-white mb-4">
                Bergabung dalam Jaringan Tanggap Darurat
            </h1>
            <p class="text-sm sm:text-base text-blue-100/90 leading-relaxed font-normal mb-8">
                Pilih peran Anda dalam ekosistem penanggulangan bencana. Setiap peran memiliki akses terpadu sesuai wewenang dan kapasitas operasional.
            </p>

            <!-- Role Permissions Guidance -->
            <div class="space-y-3 pt-6 border-t border-blue-500/40 text-xs sm:text-sm text-blue-100">
                <div class="flex items-start space-x-3">
                    <div class="w-5 h-5 rounded-full bg-blue-500/80 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <strong class="text-white">Relawan & Tim Medis:</strong>
                        <p class="text-blue-200 text-xs mt-0.5">Menerima panggilan darurat SOS, tugas penugasan lapangan, dan pelaporan langsung dari posko.</p>
                    </div>
                </div>

                <div class="flex items-start space-x-3">
                    <div class="w-5 h-5 rounded-full bg-blue-500/80 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <strong class="text-white">Donatur:</strong>
                        <p class="text-blue-200 text-xs mt-0.5">Menyalurkan donasi logistik & dana dengan transparansi laporan alokasi hingga ke tangan pengungsi.</p>
                    </div>
                </div>

                <div class="flex items-start space-x-3">
                    <div class="w-5 h-5 rounded-full bg-blue-500/80 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <strong class="text-white">Warga & Korban:</strong>
                        <p class="text-blue-200 text-xs mt-0.5">Mengirimkan sinyal darurat SOS satu sentuhan, mencari posko terdekat, dan memantau status bencana.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Left: Institutional Identity -->
        <div class="relative z-10 text-xs text-blue-200/80 flex items-center justify-between pt-6 border-t border-blue-500/40">
            <span>Kelompok SI-48-EXT B-A • Telkom University</span>
            <span class="font-mono">PPL 2026</span>
        </div>
    </div>

    <!-- Right: Registration Form Card -->
    <div class="lg:w-7/12 p-6 sm:p-10 lg:p-12 overflow-y-auto">
        <div class="max-w-2xl mx-auto">

            <!-- Header -->
            <div class="mb-8">
                <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">
                    Registrasi Akun Baru
                </h2>
                <p class="mt-1.5 text-sm text-slate-500">
                    Lengkapi formulir di bawah ini untuk memulai akses ke dashboard ResQHub.
                </p>
            </div>

            <!-- Error Notification Banner -->
            @if ($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200/80 text-rose-800 text-xs sm:text-sm">
                    <div class="flex items-start space-x-3">
                        <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div class="flex-1">
                            <p class="font-semibold mb-1">Mohon periksa isian formulir Anda:</p>
                            <ul class="list-disc pl-4 space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form wire:submit.prevent="register" class="space-y-8">
                
                <!-- Section 1: Role Selection Cards -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">
                        1. Pilih Peran / Hak Akses Akun
                    </label>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        
                        <!-- Relawan Card -->
                        <button 
                            type="button" 
                            wire:click="selectRole('relawan')"
                            class="p-3.5 rounded-2xl border text-left transition relative {{ $role === 'relawan' ? 'border-blue-600 bg-blue-50/50 ring-2 ring-blue-500/20' : 'border-slate-200 hover:border-slate-300 bg-white' }}"
                        >
                            <div class="w-8 h-8 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center mb-2.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                            </div>
                            <span class="block text-xs font-bold text-slate-900 leading-tight">Relawan</span>
                            <span class="block text-[11px] text-slate-500 mt-0.5">SAR, logistik, dapur</span>
                            @if ($role === 'relawan')
                                <div class="absolute top-2.5 right-2.5 w-4 h-4 rounded-full bg-blue-600 text-white flex items-center justify-center">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            @endif
                        </button>

                        <!-- Nakes Card -->
                        <button 
                            type="button" 
                            wire:click="selectRole('nakes')"
                            class="p-3.5 rounded-2xl border text-left transition relative {{ $role === 'nakes' ? 'border-blue-600 bg-blue-50/50 ring-2 ring-blue-500/20' : 'border-slate-200 hover:border-slate-300 bg-white' }}"
                        >
                            <div class="w-8 h-8 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center mb-2.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <span class="block text-xs font-bold text-slate-900 leading-tight">Tenaga Medis</span>
                            <span class="block text-[11px] text-slate-500 mt-0.5">Dokter, perawat, P3K</span>
                            @if ($role === 'nakes')
                                <div class="absolute top-2.5 right-2.5 w-4 h-4 rounded-full bg-blue-600 text-white flex items-center justify-center">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            @endif
                        </button>

                        <!-- Donatur Card -->
                        <button 
                            type="button" 
                            wire:click="selectRole('donatur')"
                            class="p-3.5 rounded-2xl border text-left transition relative {{ $role === 'donatur' ? 'border-blue-600 bg-blue-50/50 ring-2 ring-blue-500/20' : 'border-slate-200 hover:border-slate-300 bg-white' }}"
                        >
                            <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center mb-2.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                            </div>
                            <span class="block text-xs font-bold text-slate-900 leading-tight">Donatur</span>
                            <span class="block text-[11px] text-slate-500 mt-0.5">Bantuan dana / logistik</span>
                            @if ($role === 'donatur')
                                <div class="absolute top-2.5 right-2.5 w-4 h-4 rounded-full bg-blue-600 text-white flex items-center justify-center">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            @endif
                        </button>

                        <!-- Warga Card -->
                        <button 
                            type="button" 
                            wire:click="selectRole('warga')"
                            class="p-3.5 rounded-2xl border text-left transition relative {{ $role === 'warga' ? 'border-blue-600 bg-blue-50/50 ring-2 ring-blue-500/20' : 'border-slate-200 hover:border-slate-300 bg-white' }}"
                        >
                            <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center mb-2.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <span class="block text-xs font-bold text-slate-900 leading-tight">Warga Terdampak</span>
                            <span class="block text-[11px] text-slate-500 mt-0.5">SOS, lapor, cek posko</span>
                            @if ($role === 'warga')
                                <div class="absolute top-2.5 right-2.5 w-4 h-4 rounded-full bg-blue-600 text-white flex items-center justify-center">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            @endif
                        </button>

                        <!-- Koordinator Card -->
                        <button 
                            type="button" 
                            wire:click="selectRole('koordinator')"
                            class="p-3.5 rounded-2xl border text-left transition relative {{ $role === 'koordinator' ? 'border-blue-600 bg-blue-50/50 ring-2 ring-blue-500/20' : 'border-slate-200 hover:border-slate-300 bg-white' }}"
                        >
                            <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center mb-2.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <span class="block text-xs font-bold text-slate-900 leading-tight">Koordinator Posko</span>
                            <span class="block text-[11px] text-slate-500 mt-0.5">Kelola posko & logistik</span>
                            @if ($role === 'koordinator')
                                <div class="absolute top-2.5 right-2.5 w-4 h-4 rounded-full bg-blue-600 text-white flex items-center justify-center">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            @endif
                        </button>

                        <!-- Admin Card -->
                        <button 
                            type="button" 
                            wire:click="selectRole('admin')"
                            class="p-3.5 rounded-2xl border text-left transition relative {{ $role === 'admin' ? 'border-blue-600 bg-blue-50/50 ring-2 ring-blue-500/20' : 'border-slate-200 hover:border-slate-300 bg-white' }}"
                        >
                            <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center mb-2.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <span class="block text-xs font-bold text-slate-900 leading-tight">Admin Pusdalops</span>
                            <span class="block text-[11px] text-slate-500 mt-0.5">Pengawasan penuh</span>
                            @if ($role === 'admin')
                                <div class="absolute top-2.5 right-2.5 w-4 h-4 rounded-full bg-blue-600 text-white flex items-center justify-center">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            @endif
                        </button>

                    </div>
                </div>

                <!-- Section 2: General User Credentials -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">
                        2. Informasi Akun Dasar
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        
                        <!-- Nama Lengkap -->
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Lengkap</label>
                            <input 
                                type="text" 
                                wire:model="name" 
                                required 
                                placeholder="Contoh: Rian Hidayat" 
                                class="w-full py-2.5 px-3.5 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition bg-slate-50/50 hover:bg-white"
                            />
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Alamat Email</label>
                            <input 
                                type="email" 
                                wire:model="email" 
                                required 
                                placeholder="nama@contoh.com" 
                                class="w-full py-2.5 px-3.5 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition bg-slate-50/50 hover:bg-white"
                            />
                        </div>

                        <!-- Nomor WhatsApp / HP -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nomor WhatsApp / HP</label>
                            <input 
                                type="text" 
                                wire:model="phone" 
                                required 
                                placeholder="081234567890" 
                                class="w-full py-2.5 px-3.5 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition bg-slate-50/50 hover:bg-white"
                            />
                        </div>

                        <!-- Kabupaten / Kota -->
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Kabupaten / Kota Domisili</label>
                            <select 
                                wire:model="regency" 
                                class="w-full py-2.5 px-3.5 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition bg-slate-50/50 hover:bg-white"
                            >
                                <option value="Kabupaten Bandung">Kabupaten Bandung</option>
                                <option value="Kota Bandung">Kota Bandung</option>
                                <option value="Kabupaten Bandung Barat">Kabupaten Bandung Barat</option>
                                <option value="Kota Cimahi">Kota Cimahi</option>
                                <option value="Kabupaten Sumedang">Kabupaten Sumedang</option>
                                <option value="Kabupaten Garut">Kabupaten Garut</option>
                                <option value="Kabupaten Cianjur">Kabupaten Cianjur</option>
                            </select>
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Kata Sandi (Min. 8 Karakter)</label>
                            <input 
                                type="password" 
                                wire:model="password" 
                                required 
                                placeholder="••••••••" 
                                class="w-full py-2.5 px-3.5 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition bg-slate-50/50 hover:bg-white"
                            />
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Konfirmasi Kata Sandi</label>
                            <input 
                                type="password" 
                                wire:model="password_confirmation" 
                                required 
                                placeholder="••••••••" 
                                class="w-full py-2.5 px-3.5 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition bg-slate-50/50 hover:bg-white"
                            />
                        </div>

                    </div>
                </div>

                <!-- Section 3: Dynamic Role-Specific Fields -->
                <div class="p-5 rounded-2xl bg-blue-50/40 border border-blue-100">
                    
                    <!-- If Relawan -->
                    @if ($role === 'relawan')
                        <div>
                            <div class="flex items-center space-x-2 mb-3">
                                <span class="w-6 h-6 rounded-lg bg-purple-600 text-white flex items-center justify-center text-xs font-bold">R</span>
                                <h3 class="text-sm font-bold text-slate-900">Kualifikasi & Kesiagaan Relawan</h3>
                            </div>

                            <p class="text-xs text-slate-500 mb-4">
                                Data ini digunakan untuk mencocokkan tugas evakuasi dan logistik darurat sesuai kompetensi dan jangkauan Anda.
                            </p>

                            <!-- Checkbox Keahlian Relawan -->
                            <div class="mb-4">
                                <label class="block text-xs font-semibold text-slate-700 mb-2">Pilih Keahlian Relawan (Bisa pilih lebih dari satu):</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach ($availableSkills as $skill)
                                        <label class="flex items-center space-x-2.5 p-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer text-xs">
                                            <input 
                                                type="checkbox" 
                                                wire:model="skills" 
                                                value="{{ $skill->id }}" 
                                                class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500 cursor-pointer"
                                            />
                                            <span class="font-medium text-slate-800">{{ $skill->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Radius Siaga -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Radius Siaga Operasional</label>
                                    <select 
                                        wire:model="radius_km" 
                                        class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition"
                                    >
                                        <option value="5">Hingga 5 KM dari Posko</option>
                                        <option value="10">Hingga 10 KM (Standar)</option>
                                        <option value="20">Hingga 20 KM</option>
                                        <option value="50">Hingga 50 KM (Siap Lintas Kecamatan)</option>
                                    </select>
                                </div>

                                <!-- Status Kesiapan -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Status Kesiapan Awal</label>
                                    <select 
                                        wire:model="availability" 
                                        class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition"
                                    >
                                        <option value="siap">Siap Diterjunkan (Aktif)</option>
                                        <option value="tidak_siap">Belum Siap / Cadangan</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                    <!-- If Nakes -->
                    @elseif ($role === 'nakes')
                        <div>
                            <div class="flex items-center space-x-2 mb-3">
                                <span class="w-6 h-6 rounded-lg bg-teal-600 text-white flex items-center justify-center text-xs font-bold">M</span>
                                <h3 class="text-sm font-bold text-slate-900">Verifikasi Tenaga Kesehatan</h3>
                            </div>

                            <p class="text-xs text-slate-500 mb-4">
                                Tenaga medis bertugas menangani pemeriksaan kesehatan pengungsi, rujukan rumah sakit, dan kebutuhan obat.
                            </p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Nomor STR -->
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nomor STR (Surat Tanda Registrasi)</label>
                                    <input 
                                        type="text" 
                                        wire:model="str_number" 
                                        required 
                                        placeholder="Contoh: STR-12093848123" 
                                        class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition"
                                    />
                                </div>

                                <!-- Spesialisasi -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Profesi / Keahlian Medis</label>
                                    <select 
                                        wire:model="medical_specialty" 
                                        class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition"
                                    >
                                        <option value="Dokter Umum">Dokter Umum</option>
                                        <option value="Dokter Spesialis">Dokter Spesialis</option>
                                        <option value="Perawat Lapangan">Perawat Lapangan</option>
                                        <option value="Bidan">Bidan</option>
                                        <option value="Tenaga Farmasi / Apoteker">Tenaga Farmasi / Apoteker</option>
                                        <option value="Paramedis EMT">Paramedis EMT / Ambulans</option>
                                    </select>
                                </div>

                                <!-- Asal Faskes -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Asal Faskes / Rumah Sakit / Puskesmas</label>
                                    <input 
                                        type="text" 
                                        wire:model="medical_facility" 
                                        placeholder="Contoh: RSUD Al-Ihsan / Puskesmas Baleendah" 
                                        class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition"
                                    />
                                </div>
                            </div>
                        </div>

                    <!-- If Donatur -->
                    @elseif ($role === 'donatur')
                        <div>
                            <div class="flex items-center space-x-2 mb-3">
                                <span class="w-6 h-6 rounded-lg bg-amber-600 text-white flex items-center justify-center text-xs font-bold">D</span>
                                <h3 class="text-sm font-bold text-slate-900">Profil Donatur</h3>
                            </div>

                            <p class="text-xs text-slate-500 mb-4">
                                Membantu transparansi penyaluran donasi uang maupun barang logistik serta pengiriman kuitansi elektronik resmi.
                            </p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Kategori Donatur</label>
                                    <select 
                                        wire:model="donor_type" 
                                        class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition"
                                    >
                                        <option value="individu">Individu / Perorangan</option>
                                        <option value="komunitas">Komunitas / Yayasan</option>
                                        <option value="perusahaan">Perusahaan / CSR</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Lembaga / Organisasi (Opsional)</label>
                                    <input 
                                        type="text" 
                                        wire:model="organization_name" 
                                        placeholder="Contoh: Yayasan Peduli Sesama" 
                                        class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition"
                                    />
                                </div>
                            </div>
                        </div>

                    <!-- If Koordinator -->
                    @elseif ($role === 'koordinator')
                        <div>
                            <div class="flex items-center space-x-2 mb-3">
                                <span class="w-6 h-6 rounded-lg bg-blue-600 text-white flex items-center justify-center text-xs font-bold">K</span>
                                <h3 class="text-sm font-bold text-slate-900">Penugasan Koordinator Posko</h3>
                            </div>

                            <p class="text-xs text-slate-500 mb-4">
                                Koordinator memiliki hak akses mengelola kapasitas posko, mendata pengungsi, mencatat kebutuhan bantuan, dan mendistribusikan logistik.
                            </p>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Instansi Induk / Satgas</label>
                                <input 
                                    type="text" 
                                    wire:model="coordinator_org" 
                                    placeholder="Contoh: BPBD Kabupaten Bandung / Tagana Posko Cisarua" 
                                    class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition"
                                />
                            </div>
                        </div>

                    <!-- If Warga -->
                    @elseif ($role === 'warga')
                        <div>
                            <div class="flex items-center space-x-2 mb-3">
                                <span class="w-6 h-6 rounded-lg bg-slate-700 text-white flex items-center justify-center text-xs font-bold">W</span>
                                <h3 class="text-sm font-bold text-slate-900">Alamat & Informasi Kedaruratan Warga</h3>
                            </div>

                            <p class="text-xs text-slate-500 mb-4">
                                Informasi ini memudahkan petugas SAR dan posko evakuasi menemukan titik lokasi Anda saat terjadi keadaan darurat.
                            </p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Alamat Tempat Tinggal (Jalan, RT/RW, Desa/Kelurahan)</label>
                                    <input 
                                        type="text" 
                                        wire:model="address" 
                                        placeholder="Contoh: Jl. Raya Baleendah No. 12 RT 03/RW 07" 
                                        class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Jumlah Anggota Keluarga (Opsional)</label>
                                    <input 
                                        type="number" 
                                        min="1" 
                                        wire:model="family_members_count" 
                                        placeholder="Contoh: 4" 
                                        class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition"
                                    />
                                </div>
                            </div>
                        </div>

                    <!-- If Admin -->
                    @elseif ($role === 'admin')
                        <div>
                            <div class="flex items-center space-x-2 mb-3">
                                <span class="w-6 h-6 rounded-lg bg-rose-600 text-white flex items-center justify-center text-xs font-bold">A</span>
                                <h3 class="text-sm font-bold text-slate-900">Hak Akses Penuh Pusdalops BPBD</h3>
                            </div>

                            <p class="text-xs text-slate-500">
                                Akun Admin Pusdalops memiliki hak akses menyeluruh untuk memvalidasi laporan bencana, mengelola hak akses seluruh pengguna, posko, dan penyaluran logistik nasional/daerah.
                            </p>
                        </div>
                    @endif

                </div>

                <!-- Submit Button -->
                <div>
                    <button 
                        type="submit" 
                        wire:loading.attr="disabled"
                        class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-medium text-sm rounded-xl shadow-xs hover:shadow transition duration-150 flex items-center justify-center space-x-2 disabled:opacity-50"
                    >
                        <span wire:loading.remove>Daftar Akun Sekarang</span>
                        <span wire:loading class="flex items-center space-x-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Memproses pendaftaran...</span>
                        </span>
                        <svg wire:loading.remove class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>

                <!-- Login Redirect Link -->
                <div class="pt-4 border-t border-slate-100 text-center">
                    <p class="text-sm text-slate-600">
                        Sudah memiliki akun terdaftar? 
                        <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-700 hover:underline">
                            Masuk ke akun Anda di sini
                        </a>
                    </p>
                </div>

            </form>

        </div>
    </div>

</div>
