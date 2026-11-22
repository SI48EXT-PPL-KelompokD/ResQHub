<div class="space-y-6">
    <!-- Header Page & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">
                <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded font-mono">Modul F</span>
                <span>•</span>
                <span class="text-blue-600">PBI-17</span>
                <span>•</span>
                <span>Logistik & Penyaluran</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Penerimaan Donasi Barang</h1>
            <p class="text-sm text-slate-500 mt-1">
                Catat kedatangan fisik donasi barang, perbarui stok gudang & progres kebutuhan posko, serta kirim notifikasi otomatis ke donatur.
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <button 
                wire:click="openCreateModal"
                class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Catat Penerimaan Donasi
            </button>
        </div>
    </div>

    <!-- Alert / Notification -->
    @if ($notification)
        <div class="rounded-xl p-4 transition-all duration-200 {{ $notification['type'] === 'success' ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : 'bg-rose-50 border border-rose-200 text-rose-800' }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    @if ($notification['type'] === 'success')
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @endif
                    <span class="text-sm font-medium">{{ $notification['message'] }}</span>
                </div>
                <button wire:click="dismissNotification" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Penerimaan -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Penerimaan</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($stats['total_receipts']) }}</p>
                <p class="text-xs text-slate-400 mt-0.5">Catatan fisik aktif</p>
            </div>
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </div>
        </div>

        <!-- Total Barang Baik -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Barang Kondisi Baik</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($stats['total_good']) }}</p>
                <p class="text-xs text-emerald-600/80 mt-0.5">Menambah stok gudang</p>
            </div>
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>

        <!-- Total Barang Rusak -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Barang Rusak / Afkir</p>
                <p class="text-2xl font-bold text-rose-600 mt-1">{{ number_format($stats['total_damaged']) }}</p>
                <p class="text-xs text-rose-500 mt-0.5">Tidak dimasukkan ke stok</p>
            </div>
            <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>

        <!-- Donasi Menunggu Tiba -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Menunggu Tiba</p>
                <p class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($stats['total_pending']) }}</p>
                <p class="text-xs text-amber-600/80 mt-0.5">Status dijanjikan</p>
            </div>
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Filter & Search Controls -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Search Input -->
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Cari Data</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search"
                        placeholder="Cari kode donasi, donatur, barang, posko..." 
                        class="w-full pl-9 pr-4 py-2 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-800 placeholder-slate-400"
                    />
                </div>
            </div>

            <!-- Filter Posko -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Posko</label>
                <select 
                    wire:model.live="shelterFilter"
                    class="w-full py-2 px-3 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-800"
                >
                    <option value="">Semua Posko</option>
                    @foreach ($userShelters as $shelter)
                        <option value="{{ $shelter->id }}">{{ $shelter->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Barang -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Barang Katalog</label>
                <select 
                    wire:model.live="itemFilter"
                    class="w-full py-2 px-3 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-800"
                >
                    <option value="">Semua Barang</option>
                    @foreach ($items as $item)
                        <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->unit }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 border-t border-slate-100">
            <!-- Filter Status -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Status Penerimaan</label>
                <select 
                    wire:model.live="statusFilter"
                    class="w-full py-2 px-3 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-800"
                >
                    <option value="all">Semua Status</option>
                    <option value="diterima">Diterima (Aktif)</option>
                    <option value="dibatalkan">Dibatalkan</option>
                    <option value="disalurkan">Disalurkan</option>
                </select>
            </div>

            <!-- Tanggal Mulai -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Tanggal Mulai</label>
                <input 
                    type="date" 
                    wire:model.live="startDate"
                    class="w-full py-2 px-3 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-800"
                />
            </div>

            <!-- Tanggal Akhir -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Tanggal Akhir</label>
                <input 
                    type="date" 
                    wire:model.live="endDate"
                    class="w-full py-2 px-3 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-800"
                />
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50/75 border-b border-slate-200 text-xs uppercase font-semibold text-slate-500 tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Donasi & Donatur</th>
                        <th class="py-3.5 px-4">Barang & Posko</th>
                        <th class="py-3.5 px-4 text-center">Jumlah Diterima</th>
                        <th class="py-3.5 px-4">Petugas & Waktu</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($receipts as $receipt)
                        @php
                            $donation = $receipt->goodsDonation;
                            $item = $donation?->aidNeed?->item;
                            $shelter = $receipt->shelter;
                            $isCancelled = $receipt->isCancelled();
                            $isDisalurkan = $donation?->isDisalurkan();
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition duration-150 {{ $isCancelled ? 'bg-slate-50/40 text-slate-400' : '' }}">
                            <!-- Donasi & Donatur -->
                            <td class="py-4 px-4 align-top">
                                <div class="flex flex-col">
                                    <div class="flex items-center space-x-2">
                                        <span class="font-mono font-bold text-slate-900 {{ $isCancelled ? 'line-through text-slate-400' : '' }}">
                                            {{ $donation?->code ?? 'N/A' }}
                                        </span>
                                        @if ($donation?->delivery_method === 'antar')
                                            <span class="text-[10px] bg-sky-50 text-sky-700 border border-sky-200 px-1.5 py-0.5 rounded font-medium">Diantar</span>
                                        @else
                                            <span class="text-[10px] bg-purple-50 text-purple-700 border border-purple-200 px-1.5 py-0.5 rounded font-medium">Dijemput</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-600 mt-1 font-medium">
                                        {{ $donation?->donor?->name ?? 'Donatur Umum' }}
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        Janji: {{ $donation?->quantity ?? 0 }} {{ $item?->unit ?? 'unit' }}
                                    </div>
                                </div>
                            </td>

                            <!-- Barang & Posko -->
                            <td class="py-4 px-4 align-top">
                                <div class="flex items-start space-x-3">
                                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 font-bold flex items-center justify-center text-xs shrink-0 mt-0.5">
                                        {{ $item ? strtoupper(substr($item->name, 0, 2)) : 'BR' }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-slate-900 {{ $isCancelled ? 'line-through text-slate-400' : '' }}">
                                            {{ $item?->name ?? 'Barang Bantuan' }}
                                        </div>
                                        <div class="text-xs text-slate-500">
                                            Posko: <span class="font-medium text-slate-700">{{ $shelter?->name ?? '-' }}</span>
                                        </div>
                                        @if ($item?->category)
                                            <span class="text-[10px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded mt-0.5 inline-block">
                                                {{ $item->category }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Jumlah Diterima -->
                            <td class="py-4 px-4 align-top text-center">
                                <div class="inline-flex flex-col items-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $isCancelled ? 'bg-slate-100 text-slate-500' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                        Baik: {{ $receipt->qty_good }} {{ $item?->unit ?? 'unit' }}
                                    </span>
                                    @if ($receipt->qty_damaged > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-rose-50 text-rose-700 border border-rose-200 mt-1">
                                            Rusak: {{ $receipt->qty_damaged }} {{ $item?->unit ?? 'unit' }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Petugas & Waktu -->
                            <td class="py-4 px-4 align-top">
                                <div class="text-xs text-slate-800 font-medium">
                                    {{ $receipt->received_at ? $receipt->received_at->translatedFormat('d M Y, H:i') : '-' }}
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    Penerima: <span class="text-slate-700">{{ $receipt->receiver?->name ?? 'Petugas' }}</span>
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="py-4 px-4 align-top text-center">
                                @if ($isCancelled)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200" title="{{ $receipt->cancel_reason }}">
                                        Dibatalkan
                                    </span>
                                    <p class="text-[10px] text-rose-500 mt-1 max-w-[120px] truncate mx-auto" title="{{ $receipt->cancel_reason }}">
                                        {{ $receipt->cancel_reason }}
                                    </p>
                                @elseif ($isDisalurkan)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        Disalurkan
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Diterima
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-4 px-4 align-top text-right whitespace-nowrap">
                                <div class="flex items-center justify-end space-x-1.5">
                                    <!-- Detail -->
                                    <button 
                                        wire:click="openDetailModal({{ $receipt->id }})" 
                                        class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                        title="Lihat Detail & Mutasi"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>

                                    @if (!$isCancelled && !$isDisalurkan)
                                        <!-- Ubah / Koreksi -->
                                        <button 
                                            wire:click="openEditModal({{ $receipt->id }})" 
                                            class="p-1.5 text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition"
                                            title="Koreksi Jumlah / Kondisi"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        <!-- Batalkan (Hapus) -->
                                        <button 
                                            wire:click="openCancelModal({{ $receipt->id }})" 
                                            class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition"
                                            title="Batalkan Penerimaan"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center">
                                <div class="max-w-sm mx-auto text-center">
                                    <div class="w-12 h-12 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-semibold text-slate-800">Belum ada catatan penerimaan</h3>
                                    <p class="text-xs text-slate-500 mt-1">
                                        Belum ada donasi barang yang dicatat penerimaannya sesuai kriteria filter yang dipilih.
                                    </p>
                                    <button 
                                        wire:click="openCreateModal"
                                        class="mt-4 inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg"
                                    >
                                        Catat Penerimaan Sekarang
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($receipts->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $receipts->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL 1: Catat Penerimaan Donasi (Tambah) -->
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity" wire:click="closeCreateModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-slate-200">
                    <form wire:submit.prevent="saveReceipt">
                        <div class="bg-white px-6 pt-6 pb-4">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-bold text-slate-900">Catat Penerimaan Donasi Barang</h3>
                                        <p class="text-xs text-slate-500">Pilih donasi masuk yang telah tiba secara fisik di posko.</p>
                                    </div>
                                </div>
                                <button type="button" wire:click="closeCreateModal" class="text-slate-400 hover:text-slate-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div class="mt-4 space-y-4">
                                <!-- Pilih Donasi Masuk (Cari / Dropdown) -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                        Pilih Donasi Barang Masuk <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="mb-2">
                                        <input 
                                            type="text" 
                                            wire:model.live.debounce.300ms="donationSearch"
                                            placeholder="Ketik kode (misal GDN-...) atau nama donatur..."
                                            class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-slate-800 placeholder-slate-400"
                                        />
                                    </div>

                                    <div class="max-h-40 overflow-y-auto border border-slate-200 rounded-xl divide-y divide-slate-100">
                                        @forelse ($availableDonations as $d)
                                            <div 
                                                wire:click="selectDonation({{ $d->id }})" 
                                                class="p-2.5 hover:bg-blue-50/60 cursor-pointer transition flex items-center justify-between {{ $selectedDonationId === $d->id ? 'bg-blue-50 border-l-4 border-blue-600' : '' }}"
                                            >
                                                <div>
                                                    <div class="flex items-center space-x-2">
                                                        <span class="font-mono text-xs font-bold text-slate-900">{{ $d->code }}</span>
                                                        <span class="text-[10px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded">
                                                            {{ $d->delivery_method === 'antar' ? 'Diantar' : 'Dijemput' }}
                                                        </span>
                                                    </div>
                                                    <div class="text-xs text-slate-600 mt-0.5">
                                                        <span class="font-medium text-slate-800">{{ $d->donor?->name ?? 'Donatur' }}</span>
                                                        <span class="text-slate-400">•</span>
                                                        <span>{{ $d->aidNeed?->item?->name ?? 'Barang' }} ({{ $d->quantity }} {{ $d->aidNeed?->item?->unit ?? 'unit' }})</span>
                                                    </div>
                                                    <div class="text-[11px] text-slate-400">
                                                        Tujuan: {{ $d->aidNeed?->shelter?->name ?? '-' }}
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    @if ($selectedDonationId === $d->id)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-600 text-white">
                                                            Terpilih
                                                        </span>
                                                    @else
                                                        <span class="text-xs font-medium text-blue-600">Pilih</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @empty
                                            <div class="p-3 text-center text-xs text-slate-400">
                                                Tidak ada donasi dengan status 'Dijanjikan' yang sesuai.
                                            </div>
                                        @endforelse
                                    </div>
                                    @error('selectedDonationId') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Box Ringkasan Donasi Terpilih -->
                                @if ($selectedDonation)
                                    <div class="p-3 bg-blue-50/60 border border-blue-200/80 rounded-xl space-y-1 text-xs">
                                        <div class="flex justify-between">
                                            <span class="text-slate-500">Kode & Donatur:</span>
                                            <span class="font-semibold text-slate-800">{{ $selectedDonation->code }} — {{ $selectedDonation->donor?->name }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-slate-500">Kebutuhan Item:</span>
                                            <span class="font-semibold text-slate-800">{{ $selectedDonation->aidNeed?->item?->name }} (Janji: {{ $selectedDonation->quantity }} {{ $selectedDonation->aidNeed?->item?->unit }})</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-slate-500">Estimasi Tiba:</span>
                                            <span class="font-medium text-slate-700">{{ $selectedDonation->est_arrival ? $selectedDonation->est_arrival->format('d M Y') : '-' }}</span>
                                        </div>
                                    </div>
                                @endif

                                <!-- Posko Penerima -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                        Posko Penerima <span class="text-rose-500">*</span>
                                    </label>
                                    <select 
                                        wire:model="receiptShelterId"
                                        class="w-full py-2 px-3 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-800"
                                    >
                                        <option value="">Pilih Posko</option>
                                        @foreach ($userShelters as $shelter)
                                            <option value="{{ $shelter->id }}">{{ $shelter->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('receiptShelterId') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Kuantitas Baik & Rusak -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                            Jumlah Kondisi Baik <span class="text-rose-500">*</span>
                                        </label>
                                        <input 
                                            type="number" 
                                            min="0"
                                            wire:model="qtyGood"
                                            class="w-full py-2 px-3 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-800"
                                            placeholder="Contoh: 40"
                                        />
                                        <p class="text-[11px] text-emerald-600 mt-0.5">Jumlah ini yang masuk ke stok gudang.</p>
                                        @error('qtyGood') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                            Jumlah Rusak / Cacat <span class="text-rose-500">*</span>
                                        </label>
                                        <input 
                                            type="number" 
                                            min="0"
                                            wire:model="qtyDamaged"
                                            class="w-full py-2 px-3 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-800"
                                            placeholder="0 jika tidak ada"
                                        />
                                        <p class="text-[11px] text-rose-500 mt-0.5">Dicatat sebagai afkir (tidak masuk stok).</p>
                                        @error('qtyDamaged') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <!-- Waktu Penerimaan -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                        Waktu Penerimaan Fisik <span class="text-rose-500">*</span>
                                    </label>
                                    <input 
                                        type="datetime-local" 
                                        wire:model="receivedAt"
                                        class="w-full py-2 px-3 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-slate-800"
                                    />
                                    @error('receivedAt') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-50 px-6 py-3.5 flex items-center justify-end space-x-3 border-t border-slate-100">
                            <button 
                                type="button" 
                                wire:click="closeCreateModal"
                                class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-xl transition"
                            >
                                Batal
                            </button>
                            <button 
                                type="submit" 
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-xs transition"
                            >
                                Simpan Penerimaan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL 2: Detail Penerimaan & Mutasi Terkait (Lihat) -->
    @if ($showDetailModal && $detailReceipt)
        @php
            $dDonation = $detailReceipt->goodsDonation;
            $dItem = $dDonation?->aidNeed?->item;
            $dAidNeed = $dDonation?->aidNeed;
        @endphp
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity" wire:click="closeDetailModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200">
                    <div class="bg-white px-6 pt-6 pb-4">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-slate-900">Rincian Penerimaan Donasi</h3>
                                    <p class="text-xs text-slate-500 font-mono">{{ $dDonation?->code }}</p>
                                </div>
                            </div>
                            <button type="button" wire:click="closeDetailModal" class="text-slate-400 hover:text-slate-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="mt-4 space-y-4 text-xs">
                            <!-- Info Donatur & Posko -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3.5 bg-slate-50 rounded-xl">
                                <div>
                                    <p class="font-semibold text-slate-500 uppercase tracking-wider text-[10px]">Data Donatur</p>
                                    <p class="font-bold text-slate-900 text-sm mt-0.5">{{ $dDonation?->donor?->name ?? 'Umum' }}</p>
                                    <p class="text-slate-500">{{ $dDonation?->donor?->email ?? '-' }}</p>
                                    <p class="text-slate-500">{{ $dDonation?->donor?->phone ?? '-' }}</p>
                                    <div class="mt-2">
                                        <span class="text-[10px] bg-sky-100 text-sky-800 px-2 py-0.5 rounded font-medium">
                                            Metode: {{ $dDonation?->delivery_method === 'antar' ? 'Diantar Donatur' : 'Dijemput Relawan' }}
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <p class="font-semibold text-slate-500 uppercase tracking-wider text-[10px]">Posko & Bencana</p>
                                    <p class="font-bold text-slate-900 text-sm mt-0.5">{{ $detailReceipt->shelter?->name ?? '-' }}</p>
                                    <p class="text-slate-500">{{ $detailReceipt->shelter?->address ?? '-' }}</p>
                                    <p class="text-blue-600 font-medium mt-1">{{ $detailReceipt->shelter?->disasterEvent?->name ?? '-' }}</p>
                                </div>
                            </div>

                            <!-- Rincian Fisik Barang & Status -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3.5 border border-slate-200 rounded-xl">
                                <div>
                                    <p class="text-slate-400 text-[11px]">Barang Bantuan</p>
                                    <p class="font-bold text-slate-900 text-sm mt-0.5">{{ $dItem?->name ?? '-' }}</p>
                                    <p class="text-slate-500">{{ $dItem?->category ?? '-' }} ({{ $dItem?->unit ?? 'unit' }})</p>
                                </div>
                                <div>
                                    <p class="text-slate-400 text-[11px]">Kondisi Fisik</p>
                                    <div class="mt-0.5 space-y-0.5">
                                        <p class="text-emerald-700 font-semibold">Baik: {{ $detailReceipt->qty_good }} {{ $dItem?->unit }}</p>
                                        <p class="text-rose-600 font-semibold">Rusak: {{ $detailReceipt->qty_damaged }} {{ $dItem?->unit }}</p>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-slate-400 text-[11px]">Petugas Penerima</p>
                                    <p class="font-medium text-slate-800 mt-0.5">{{ $detailReceipt->receiver?->name ?? '-' }}</p>
                                    <p class="text-slate-400 text-[10px]">
                                        {{ $detailReceipt->received_at ? $detailReceipt->received_at->translatedFormat('d M Y, H:i') : '-' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Kebutuhan Posko Terkait -->
                            @if ($dAidNeed)
                                <div class="p-3 bg-blue-50/50 border border-blue-100 rounded-xl">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="font-semibold text-slate-700">Progres Kebutuhan Posko</span>
                                        <span class="text-xs font-bold text-blue-700">
                                            {{ $dAidNeed->qty_fulfilled }} / {{ $dAidNeed->qty_needed }} {{ $dItem?->unit }}
                                            ({{ $dAidNeed->qty_needed > 0 ? round(($dAidNeed->qty_fulfilled / $dAidNeed->qty_needed) * 100) : 0 }}%)
                                        </span>
                                    </div>
                                    <div class="w-full bg-slate-200 rounded-full h-2">
                                        <div 
                                            class="bg-blue-600 h-2 rounded-full transition-all" 
                                            style="width: {{ min(100, $dAidNeed->qty_needed > 0 ? ($dAidNeed->qty_fulfilled / $dAidNeed->qty_needed) * 100 : 0) }}%"
                                        ></div>
                                    </div>
                                    <div class="flex justify-between items-center text-[11px] text-slate-500 mt-1">
                                        <span>Prioritas: <strong class="capitalize">{{ $dAidNeed->priority }}</strong></span>
                                        <span>Status Kebutuhan: <strong class="uppercase text-blue-600">{{ $dAidNeed->status }}</strong></span>
                                    </div>
                                </div>
                            @endif

                            <!-- Status Pembatalan jika ada -->
                            @if ($detailReceipt->isCancelled())
                                <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl">
                                    <p class="font-bold text-rose-800 text-xs flex items-center">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Penerimaan Telah Dibatalkan
                                    </p>
                                    <p class="text-rose-700 mt-1">
                                        Alasan: <span class="font-medium">{{ $detailReceipt->cancel_reason }}</span>
                                    </p>
                                    @if ($detailReceipt->deleted_at)
                                        <p class="text-rose-500 text-[10px] mt-0.5">
                                            Dibatalkan pada: {{ $detailReceipt->deleted_at->translatedFormat('d M Y, H:i') }}
                                        </p>
                                    @endif
                                </div>
                            @endif

                            <!-- Riwayat Mutasi Stok Terkait -->
                            <div>
                                <h4 class="font-bold text-slate-800 uppercase tracking-wider text-[11px] mb-2">Mutasi Stok Gudang Terkait</h4>
                                <div class="border border-slate-200 rounded-xl overflow-hidden">
                                    <table class="w-full text-left text-xs">
                                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500">
                                            <tr>
                                                <th class="py-2 px-3">Tipe</th>
                                                <th class="py-2 px-3 text-center">Jumlah</th>
                                                <th class="py-2 px-3">Alasan / Keterangan</th>
                                                <th class="py-2 px-3 text-right">Waktu</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @forelse ($detailReceipt->stockMovements as $mv)
                                                <tr>
                                                    <td class="py-2 px-3">
                                                        @if ($mv->type === 'masuk')
                                                            <span class="text-emerald-700 font-semibold bg-emerald-50 px-1.5 py-0.5 rounded">Masuk</span>
                                                        @elseif ($mv->type === 'penyesuaian')
                                                            <span class="text-amber-700 font-semibold bg-amber-50 px-1.5 py-0.5 rounded">Penyesuaian</span>
                                                        @else
                                                            <span class="text-slate-700 font-semibold bg-slate-100 px-1.5 py-0.5 rounded">Keluar</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-2 px-3 text-center font-mono font-bold {{ $mv->quantity > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                                        {{ $mv->quantity > 0 ? '+' : '' }}{{ $mv->quantity }}
                                                    </td>
                                                    <td class="py-2 px-3 text-slate-600">{{ $mv->reason }}</td>
                                                    <td class="py-2 px-3 text-right text-slate-400 font-mono">{{ $mv->created_at->format('d/m/Y H:i') }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="py-3 text-center text-slate-400">
                                                        Belum ada log mutasi tercatat.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 px-6 py-3 border-t border-slate-100 text-right">
                        <button 
                            type="button" 
                            wire:click="closeDetailModal"
                            class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-xl transition"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL 3: Koreksi Penerimaan (Ubah) -->
    @if ($showEditModal && $editReceipt)
        @php
            $eItem = $editReceipt->goodsDonation?->aidNeed?->item;
        @endphp
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity" wire:click="closeEditModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200">
                    <form wire:submit.prevent="updateReceipt">
                        <div class="bg-white px-6 pt-6 pb-4">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-bold text-slate-900">Koreksi Penerimaan Donasi</h3>
                                        <p class="text-xs text-slate-500 font-mono">{{ $editReceipt->goodsDonation?->code }}</p>
                                    </div>
                                </div>
                                <button type="button" wire:click="closeEditModal" class="text-slate-400 hover:text-slate-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div class="mt-4 space-y-3.5">
                                <div class="p-3 bg-amber-50/60 border border-amber-200/80 rounded-xl text-xs text-amber-800">
                                    <p class="font-medium">Koreksi hanya dapat dilakukan selama barang belum disalurkan.</p>
                                    <p class="text-[11px] text-amber-700/80 mt-0.5">Stok gudang dan progres kebutuhan akan disesuaikan otomatis dari selisih jumlah baik.</p>
                                </div>

                                <div class="text-xs text-slate-600 space-y-1">
                                    <p>Barang: <strong class="text-slate-900">{{ $eItem?->name }}</strong> ({{ $eItem?->unit }})</p>
                                    <p>Posko: <strong class="text-slate-900">{{ $editReceipt->shelter?->name }}</strong></p>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                            Jumlah Baik <span class="text-rose-500">*</span>
                                        </label>
                                        <input 
                                            type="number" 
                                            min="0"
                                            wire:model="editQtyGood"
                                            class="w-full py-2 px-3 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-slate-800"
                                        />
                                        @error('editQtyGood') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                            Jumlah Rusak <span class="text-rose-500">*</span>
                                        </label>
                                        <input 
                                            type="number" 
                                            min="0"
                                            wire:model="editQtyDamaged"
                                            class="w-full py-2 px-3 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-slate-800"
                                        />
                                        @error('editQtyDamaged') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                        Alasan Koreksi
                                    </label>
                                    <input 
                                        type="text" 
                                        wire:model="editReason"
                                        placeholder="Contoh: Koreksi selisih hitung fisik saat bongkar muat"
                                        class="w-full py-2 px-3 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-slate-800 placeholder-slate-400"
                                    />
                                    @error('editReason') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-50 px-6 py-3.5 flex items-center justify-end space-x-3 border-t border-slate-100">
                            <button 
                                type="button" 
                                wire:click="closeEditModal"
                                class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-xl transition"
                            >
                                Batal
                            </button>
                            <button 
                                type="submit" 
                                class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-xl shadow-xs transition"
                            >
                                Simpan Koreksi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL 4: Pembatalan Penerimaan (Hapus) -->
    @if ($showCancelModal && $cancelReceipt)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity" wire:click="closeCancelModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200">
                    <form wire:submit.prevent="confirmCancel">
                        <div class="bg-white px-6 pt-6 pb-4">
                            <div class="flex items-center space-x-3 pb-3 border-b border-slate-100">
                                <div class="w-10 h-10 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-slate-900">Batalkan Penerimaan Donasi</h3>
                                    <p class="text-xs text-slate-500 font-mono">{{ $cancelReceipt->goodsDonation?->code }}</p>
                                </div>
                            </div>

                            <div class="mt-4 space-y-3">
                                <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 space-y-1">
                                    <p class="font-bold">Konfirmasi Pembatalan Catatan</p>
                                    <p>Penerimaan donasi sejumlah <strong>{{ $cancelReceipt->qty_good }} unit</strong> akan ditarik kembali dari stok gudang, dan status donasi akan dikembalikan menjadi <strong>"Dijanjikan"</strong>.</p>
                                    <p class="text-[11px] text-rose-700/80">Pembatalan hanya diizinkan selama barang belum disalurkan ke pengungsi.</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                        Alasan Pembatalan <span class="text-rose-500">*</span>
                                    </label>
                                    <textarea 
                                        wire:model="cancelReason"
                                        rows="3"
                                        placeholder="Tuliskan alasan pembatalan penerimaan (contoh: Salah catat posko tujuan / donasi belum benar-benar tiba)..."
                                        class="w-full py-2 px-3 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-500 focus:border-rose-500 text-slate-800 placeholder-slate-400"
                                    ></textarea>
                                    @error('cancelReason') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-50 px-6 py-3.5 flex items-center justify-end space-x-3 border-t border-slate-100">
                            <button 
                                type="button" 
                                wire:click="closeCancelModal"
                                class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-xl transition"
                            >
                                Batal
                            </button>
                            <button 
                                type="submit" 
                                class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-xs transition"
                            >
                                Ya, Batalkan Penerimaan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
