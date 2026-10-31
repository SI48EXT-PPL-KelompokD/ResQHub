<div class="space-y-6">

    <!-- Dashboard Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                Dashboard Pusdalops & Posko
            </h1>
            <p class="text-sm text-slate-500 mt-0.5">
                {{ $activeEvent->name ?? 'Posko Bencana Tanggap Darurat' }} • 
                <span class="inline-flex items-center text-emerald-700 font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                    Status: {{ ucfirst($activeEvent->status ?? 'Aktif') }} ({{ $activeEvent->regency ?? 'Kab. Bandung' }})
                </span>
            </p>
        </div>

        <div class="flex items-center space-x-3">
            @if (Auth::user()->isAdmin())
                <a 
                    href="{{ route('admin.users') }}" 
                    wire:navigate
                    class="inline-flex items-center space-x-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition duration-150"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    <span>Kelola Pengguna</span>
                </a>
            @elseif (Auth::user()->isWarga())
                <button class="inline-flex items-center space-x-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition duration-150">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Kirim SOS Darurat</span>
                </button>
            @elseif (Auth::user()->isRelawan())
                <span class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Status: Siap Ditugaskan</span>
                </span>
            @elseif (Auth::user()->isDonatur())
                <button class="inline-flex items-center space-x-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition duration-150">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span>Salurkan Donasi</span>
                </button>
            @elseif (Auth::user()->isNakes())
                <span class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-teal-50 text-teal-700 border border-teal-200 rounded-xl text-xs font-semibold">
                    <span>Tim Medis Siaga Posko</span>
                </span>
            @endif
        </div>
    </div>

    <!-- 4 Key Summary Metric Cards (Sesuai Mockup Figma) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Card 1: Okupansi Posko -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Okupansi Posko</span>
                <span class="p-1.5 rounded-lg bg-blue-50 text-blue-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </span>
            </div>
            <div class="flex items-baseline space-x-2">
                <span class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $totalOccupancy }}</span>
                <span class="text-sm font-medium text-slate-400">/ {{ $totalCapacity }} jiwa</span>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs text-slate-500">
                <span>{{ $occupancyPercent }}% terisi</span>
                <span class="px-2 py-0.5 rounded-full font-semibold {{ $occupancyPercent >= 90 ? 'bg-red-50 text-red-700' : ($occupancyPercent >= 75 ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700') }}">
                    {{ $occupancyPercent >= 90 ? 'Penuh' : ($occupancyPercent >= 75 ? 'Hampir Penuh' : 'Tersedia') }}
                </span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-1.5 mt-2 overflow-hidden">
                <div class="h-1.5 rounded-full {{ $occupancyPercent >= 90 ? 'bg-red-500' : ($occupancyPercent >= 75 ? 'bg-amber-500' : 'bg-blue-600') }}" style="width: {{ min(100, $occupancyPercent) }}%"></div>
            </div>
        </div>

        <!-- Card 2: Kelompok Rentan -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Kelompok Rentan</span>
                <span class="p-1.5 rounded-lg bg-purple-50 text-purple-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </span>
            </div>
            <div class="flex items-baseline space-x-2">
                <span class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $vulnerableCount }}</span>
                <span class="text-sm font-medium text-slate-400">jiwa prioritas</span>
            </div>
            <p class="mt-3 text-xs text-slate-500">
                Balita, lansia, ibu hamil, disabilitas
            </p>
            <div class="w-full bg-slate-100 rounded-full h-1.5 mt-2">
                <div class="bg-purple-600 h-1.5 rounded-full" style="width: 65%"></div>
            </div>
        </div>

        <!-- Card 3: SOS Baru -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">SOS Baru</span>
                <span class="p-1.5 rounded-lg bg-rose-50 text-rose-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </span>
            </div>
            <div class="flex items-baseline space-x-2">
                <span class="text-3xl font-extrabold text-rose-600 tracking-tight">{{ $newSosCount }}</span>
                <span class="text-sm font-medium text-slate-400">permintaan aktif</span>
            </div>
            <p class="mt-3 text-xs text-slate-500">
                Menunggu evakuasi & pertolongan
            </p>
            <div class="w-full bg-slate-100 rounded-full h-1.5 mt-2">
                <div class="bg-rose-500 h-1.5 rounded-full animate-pulse" style="width: 80%"></div>
            </div>
        </div>

        <!-- Card 4: Stok Menipis -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs hover:border-slate-300 transition">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Stok Menipis</span>
                <span class="p-1.5 rounded-lg bg-amber-50 text-amber-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </span>
            </div>
            <div class="flex items-baseline space-x-2">
                <span class="text-3xl font-extrabold text-amber-600 tracking-tight">{{ $lowStockCount }}</span>
                <span class="text-sm font-medium text-slate-400">item darurat</span>
            </div>
            <p class="mt-3 text-xs text-slate-500">
                Estimasi habis dalam 2 hari ke depan
            </p>
            <div class="w-full bg-slate-100 rounded-full h-1.5 mt-2">
                <div class="bg-amber-500 h-1.5 rounded-full" style="width: 45%"></div>
            </div>
        </div>

    </div>

    <!-- Widget Ringkasan Triase Kesehatan Harian (Modul C · PBI-08) -->
    @if (!empty($triageSummary))
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Triase Kesehatan Hari Ini</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    {{ $triageSummary['totals']['total'] }} pemeriksaan tercatat · {{ $triageSummary['unreferred_merah'] ?? 0 }} triase merah belum dirujuk
                </p>
            </div>
            <div class="flex items-center flex-wrap gap-2 text-xs font-semibold">
                <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800">Hijau: {{ $triageSummary['totals']['hijau'] }}</span>
                <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800">Kuning: {{ $triageSummary['totals']['kuning'] }}</span>
                <span class="px-2.5 py-1 rounded-full bg-red-100 text-red-700">Merah: {{ $triageSummary['totals']['merah'] }}</span>
                <a href="{{ route('admin.health-checks') }}" wire:navigate class="ml-1 text-blue-600 hover:text-blue-700 font-semibold">
                    Kelola Pemeriksaan →
                </a>
            </div>
        </div>
    @endif

    <!-- Main Section: Grid 2 Columns (Left: SOS Table, Right: Urgent Needs & Map Preview) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: SOS Masuk Table -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900 tracking-tight">SOS Masuk</h2>
                    <p class="text-xs text-slate-500">Daftar permintaan darurat dari warga terdampak</p>
                </div>
                <a href="#" class="text-xs font-semibold text-blue-600 hover:text-blue-700 hover:underline">
                    Lihat semua
                </a>
            </div>

            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-slate-50/80 text-slate-500 text-[11px] font-semibold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4">Pemohon</th>
                            <th class="py-3 px-4">Jenis</th>
                            <th class="py-3 px-4 text-center">Orang</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentSos as $sos)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3 px-4 font-mono text-xs font-semibold text-slate-700">
                                    {{ \Carbon\Carbon::parse($sos->created_at)->format('H.i') }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-semibold text-slate-800 block">{{ $sos->requester_name }}</span>
                                    <span class="text-[11px] text-slate-400 block truncate max-w-[180px]">{{ $sos->note }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="capitalize px-2 py-0.5 rounded-md font-medium text-xs bg-slate-100 text-slate-700">
                                        {{ $sos->help_type }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center font-semibold text-slate-700">
                                    {{ $sos->people_count }}
                                    @if ($sos->has_vulnerable)
                                        <span class="block text-[10px] text-purple-600 font-medium">rentan</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if ($sos->status === 'baru')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200/80">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span>
                                            Baru
                                        </span>
                                    @elseif ($sos->status === 'ditangani')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/80">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5"></span>
                                            Ditangani
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                            Selesai
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    @if ($sos->status === 'baru')
                                        <button class="px-2.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg shadow-2xs transition">
                                            Buat Tugas
                                        </button>
                                    @else
                                        <button class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium rounded-lg transition">
                                            Detail
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400 text-sm">
                                    Belum ada permintaan darurat SOS saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right 1 Col: Kebutuhan Mendesak & Peta Posko -->
        <div class="space-y-6">

            <!-- Kebutuhan Mendesak Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 tracking-tight">Kebutuhan Mendesak</h2>
                        <p class="text-xs text-slate-500">Bantuan prioritas posko evakuasi</p>
                    </div>
                    <a href="#" class="text-xs font-semibold text-blue-600 hover:text-blue-700 hover:underline">
                        Semua
                    </a>
                </div>

                <div class="space-y-4">
                    @forelse ($urgentNeeds as $need)
                        @php
                            $fulfilledPct = $need->qty_needed > 0 ? min(100, round(($need->qty_fulfilled / $need->qty_needed) * 100)) : 0;
                            $pledgedPct = $need->qty_needed > 0 ? min(100, round((($need->qty_fulfilled + $need->qty_pledged) / $need->qty_needed) * 100)) : 0;
                        @endphp
                        <div class="p-3.5 rounded-xl bg-slate-50/70 border border-slate-100 hover:bg-slate-50 transition">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-xs sm:text-sm font-semibold text-slate-900">
                                        {{ $need->item_name }} • {{ $need->qty_needed }} {{ $need->unit }}
                                    </h3>
                                    <p class="text-[11px] text-slate-500 mt-0.5">
                                        {{ $need->shelter_name }}
                                    </p>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide {{ $need->priority === 'mendesak' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $need->priority }}
                                </span>
                            </div>

                            <!-- Progress Bar -->
                            <div class="mt-3">
                                <div class="flex justify-between text-[11px] text-slate-500 mb-1">
                                    <span>Terpenuhi {{ $fulfilledPct }}%</span>
                                    <span>Dijanjikan {{ $pledgedPct }}%</span>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-2 relative overflow-hidden">
                                    <div class="bg-amber-400 h-2 rounded-full absolute left-0" style="width: {{ $pledgedPct }}%"></div>
                                    <div class="bg-blue-600 h-2 rounded-full absolute left-0" style="width: {{ $fulfilledPct }}%"></div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center py-4 text-xs text-slate-400">Tidak ada kebutuhan mendesak saat ini.</p>
                    @endforelse
                </div>
            </div>

            <!-- Peta Posko & SOS Quick Preview -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-base font-bold text-slate-900 tracking-tight">Peta Posko & SOS</h2>
                    <span class="text-xs text-blue-600 font-semibold cursor-pointer hover:underline">Perbesar Peta</span>
                </div>
                
                <div class="h-36 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center relative overflow-hidden group">
                    <!-- Stylized SVG Map preview -->
                    <div class="absolute inset-0 bg-blue-50/50 flex items-center justify-center">
                        <svg class="w-full h-full text-blue-200/60" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 200 100">
                            <path d="M0 40 Q50 30 100 45 T200 40" stroke="currentColor" stroke-width="8" fill="none" />
                            <path d="M40 0 L40 100 M140 0 L140 100 M0 70 L200 70" stroke="#e2e8f0" stroke-width="2" />
                        </svg>
                    </div>

                    <!-- Geolocation Pin Indicators -->
                    <div class="relative z-10 flex items-center space-x-6">
                        <div class="flex flex-col items-center">
                            <span class="w-3.5 h-3.5 rounded-full bg-blue-600 border-2 border-white shadow-sm ring-4 ring-blue-500/20"></span>
                            <span class="text-[10px] font-bold text-slate-700 bg-white/90 px-1.5 py-0.5 rounded shadow-xs mt-1">Posko GOR</span>
                        </div>
                        <div class="flex flex-col items-center">
                            <span class="w-3.5 h-3.5 rounded-full bg-rose-600 border-2 border-white shadow-sm ring-4 ring-rose-500/20 animate-bounce"></span>
                            <span class="text-[10px] font-bold text-rose-700 bg-white/90 px-1.5 py-0.5 rounded shadow-xs mt-1">SOS RW 07</span>
                        </div>
                        <div class="flex flex-col items-center">
                            <span class="w-3.5 h-3.5 rounded-full bg-emerald-600 border-2 border-white shadow-sm ring-4 ring-emerald-500/20"></span>
                            <span class="text-[10px] font-bold text-slate-700 bg-white/90 px-1.5 py-0.5 rounded shadow-xs mt-1">Posko Dayeuhkolot</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Bottom Section: Stok Menipis Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900 tracking-tight">Stok Menipis</h2>
                <p class="text-xs text-slate-500">Barang logistik yang berada di bawah ambang batas minimum</p>
            </div>
            <a href="#" class="text-xs font-semibold text-blue-600 hover:text-blue-700 hover:underline">
                Stok & penyaluran
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50/80 text-slate-500 text-[11px] font-semibold uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Nama Barang</th>
                        <th class="py-3 px-4">Posko</th>
                        <th class="py-3 px-4 text-center">Sisa Stok</th>
                        <th class="py-3 px-4 text-center">Ambang Batas</th>
                        <th class="py-3 px-4">Estimasi Habis</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($lowStocks as $stock)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-4 font-semibold text-slate-800">
                                {{ $stock->item_name }}
                                <span class="block text-[11px] text-slate-400 capitalize">{{ $stock->category }}</span>
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                {{ $stock->shelter_name }}
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-rose-600">
                                {{ $stock->quantity }} {{ $stock->unit }}
                            </td>
                            <td class="py-3 px-4 text-center text-slate-500">
                                {{ $stock->min_threshold }} {{ $stock->unit }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-amber-50 text-amber-700">
                                    ~1,5 s.d. 2 hari
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <button class="px-2.5 py-1.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-medium rounded-lg shadow-2xs transition">
                                    Ajukan Kebutuhan
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400 text-xs">
                                Seluruh stok logistik masih berada di atas batas aman.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
