<div class="space-y-6">

    <!-- Toast Notification -->
    @if ($notification)
        <div 
            x-data="{ show: true }" 
            x-show="show" 
            x-init="setTimeout(() => { show = false; $wire.dismissNotification(); }, 4000)"
            class="fixed top-20 right-6 z-50 max-w-sm w-full p-4 rounded-xl shadow-lg border transition-all transform ease-out duration-300 {{ $notification['type'] === 'error' ? 'bg-red-50 border-red-200 text-red-800' : 'bg-emerald-50 border-emerald-200 text-emerald-800' }}"
        >
            <div class="flex items-start justify-between space-x-3">
                <div class="flex items-center space-x-2">
                    @if ($notification['type'] === 'error')
                        <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    @endif
                    <p class="text-xs sm:text-sm font-semibold">{{ $notification['message'] }}</p>
                </div>
                <button wire:click="dismissNotification" class="text-slate-400 hover:text-slate-600 p-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Rekening Donasi Resmi</h1>
            <p class="text-sm text-slate-500 mt-0.5">PBI-14 · Rekening penyelenggara yang ditampilkan di halaman donasi dana</p>
        </div>

        <button 
            wire:click="openCreateForm" 
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium rounded-lg shadow-xs transition duration-150"
        >
            Tambah Rekening
        </button>
    </div>

    <!-- Main Content: Dua Kolom (Tabel Kiri & Panel Form Kanan) -->
    <div class="grid grid-cols-1 {{ $showForm ? 'lg:grid-cols-12' : 'lg:grid-cols-1' }} gap-6 items-start">
        
        <!-- Kolom Kiri: Filter & Kartu Tabel -->
        <div class="{{ $showForm ? 'lg:col-span-7 xl:col-span-8' : 'w-full' }} space-y-4">
            
            <!-- Filter Kejadian -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Kejadian</label>
                <select 
                    wire:model.live="eventFilter" 
                    class="w-full py-2.5 px-3.5 bg-white text-slate-800 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition shadow-2xs"
                >
                    <option value="">Semua kejadian</option>
                    @foreach ($events as $event)
                        <option value="{{ $event->id }}">{{ $event->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Kartu Tabel Rekening -->
            <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs">
                <div class="border border-slate-200/80 rounded-lg overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-slate-50/70 text-slate-500 text-[11px] font-semibold uppercase tracking-wider border-b border-slate-200/80">
                            <tr>
                                <th class="py-3 px-4">BANK & NOMOR</th>
                                <th class="py-3 px-4">ATAS NAMA</th>
                                <th class="py-3 px-4 text-center">STATUS</th>
                                <th class="py-3 px-4 text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($accounts as $account)
                                <tr wire:key="account-{{ $account->id }}" class="transition {{ $accountId == $account->id ? 'bg-blue-50/70' : 'hover:bg-slate-50/60' }}">
                                    <!-- Bank & Nomor -->
                                    <td class="py-3 px-4">
                                        <span class="font-semibold text-slate-900 text-sm block leading-tight">{{ $account->bank_name }}</span>
                                        <span class="font-mono text-xs text-slate-700 block mt-0.5 whitespace-nowrap">{{ $account->account_number }}</span>
                                        @if ($account->disasterEvent)
                                            <span class="text-[11px] text-slate-400 block mt-0.5">
                                                {{ $account->disasterEvent->name }}
                                                @if ($account->fund_donations_count > 0)
                                                    • {{ $account->fund_donations_count }} donasi
                                                @endif
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Atas Nama -->
                                    <td class="py-3 px-4 text-slate-600 text-sm">
                                        {{ $account->account_holder }}
                                    </td>

                                    <!-- Status (hanya tampilan) -->
                                    <td class="py-3 px-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium border {{ $account->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200/60' : 'bg-slate-100 text-slate-600 border-slate-200/60' }}">
                                            {{ $account->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="py-3 px-4">
                                        <div class="flex flex-wrap items-center justify-center gap-2">
                                            <button 
                                                wire:click="openEditForm({{ $account->id }})" 
                                                title="Ubah data rekening"
                                                class="px-3 py-1 text-xs font-medium text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-md shadow-2xs transition"
                                            >
                                                Ubah
                                            </button>

                                            <button 
                                                wire:click="requestToggle({{ $account->id }})" 
                                                title="{{ $account->is_active ? 'Nonaktifkan rekening' : 'Aktifkan rekening' }}"
                                                class="px-3 py-1 text-xs font-medium text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-md shadow-2xs transition"
                                            >
                                                {{ $account->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>

                                            @if ((int) $account->fund_donations_count === 0)
                                                <button 
                                                    wire:click="confirmDelete({{ $account->id }})" 
                                                    title="Hapus rekening"
                                                    class="px-3 py-1 text-xs font-medium text-white bg-red-600 hover:bg-red-700 rounded-md shadow-2xs transition"
                                                >
                                                    Hapus
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-12 text-center text-slate-400">
                                        <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                        <p class="text-sm font-semibold text-slate-700">Tidak ada rekening donasi ditemukan</p>
                                        <p class="text-xs text-slate-400 mt-1">Coba sesuaikan filter kejadian atau tambahkan rekening baru.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($accounts->hasPages())
                    <div class="mt-4">
                        {{ $accounts->links() }}
                    </div>
                @endif
            </div>

        </div>

        <!-- Kolom Kanan: Panel Form (Ubah / Tambah) -->
        @if ($showForm)
            @php
                // Nama singkat bank untuk judul form (cth: "BRI")
                $displayBankName = 'Rekening';
                if ($bank_name) {
                    if (preg_match('/\((.*?)\)/', $bank_name, $match)) {
                        $displayBankName = $match[1];
                    } else {
                        $displayBankName = str_replace(['Bank ', 'BANK '], '', $bank_name);
                    }
                }
            @endphp
            <div 
                wire:key="form-{{ $accountId ?? 'new' }}"
                x-data 
                x-init="$el.scrollIntoView({ behavior: 'smooth', block: 'nearest' })"
                class="lg:col-span-5 xl:col-span-4 lg:sticky lg:top-24 self-start"
            >
                <div class="bg-white rounded-xl border border-slate-200/80 p-6 shadow-2xs">
                    
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-base font-bold text-slate-900">
                            {{ $accountId ? 'Ubah rekening: ' . $displayBankName : 'Tambah rekening' }}
                        </h3>
                        <button 
                            wire:click="closeForm" 
                            class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-50 transition"
                            title="Tutup form"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="save" class="space-y-4">
                        <!-- Kejadian -->
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1.5">Kejadian</label>
                            <select 
                                wire:model="disaster_event_id" 
                                class="w-full py-2 px-3 text-sm bg-white text-slate-800 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                            >
                                <option value="">Pilih Kejadian</option>
                                @foreach ($events as $event)
                                    <option value="{{ $event->id }}">{{ $event->name }}</option>
                                @endforeach
                            </select>
                            @error('disaster_event_id') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Nama Bank -->
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1.5">Nama bank</label>
                            <select 
                                wire:model="bank_name" 
                                class="w-full py-2 px-3 text-sm bg-white text-slate-800 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                            >
                                <option value="">Pilih Bank</option>
                                @foreach ($banks as $bank)
                                    <option value="{{ $bank }}">{{ $bank }}</option>
                                @endforeach
                            </select>
                            @error('bank_name') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Nomor Rekening -->
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1.5">Nomor rekening</label>
                            <input 
                                type="text" 
                                wire:model="account_number" 
                                class="w-full py-2 px-3 text-sm bg-slate-50/40 focus:bg-white text-slate-800 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition" 
                                placeholder="0000-01-000000-30-0" 
                            />
                            @error('account_number') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Nama Pemilik Rekening -->
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1.5">Nama pemilik rekening</label>
                            <input 
                                type="text" 
                                wire:model="account_holder" 
                                class="w-full py-2 px-3 text-sm bg-slate-50/40 focus:bg-white text-slate-800 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition" 
                                placeholder="Contoh: Pusdalops Kab. Bandung" 
                            />
                            @error('account_holder') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Keterangan (Opsional) -->
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1.5">Keterangan (Opsional)</label>
                            <input 
                                type="text" 
                                wire:model="notes" 
                                class="w-full py-2 px-3 text-sm bg-slate-50/40 focus:bg-white text-slate-800 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition" 
                                placeholder="Keterangan tambahan" 
                            />
                            @error('notes') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1.5">Status</label>
                            <select 
                                wire:model="is_active" 
                                class="w-full py-2 px-3 text-sm bg-white text-slate-800 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                            >
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                            @error('is_active') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Catatan Proteksi Rekening -->
                        <p class="text-xs text-slate-400 mt-2 mb-4 leading-relaxed">
                            Rekening yang sudah menerima donasi hanya dapat dinonaktifkan, tidak dihapus.
                        </p>

                        <!-- Action Buttons -->
                        <div class="grid grid-cols-2 gap-3 pt-2">
                            <button 
                                type="button" 
                                wire:click="closeForm" 
                                class="w-full py-2.5 px-4 bg-white hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-lg border border-slate-200 transition text-center"
                            >
                                Batal
                            </button>
                            <button 
                                type="submit" 
                                class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium rounded-lg shadow-xs transition text-center"
                            >
                                {{ $accountId ? 'Simpan Perubahan' : 'Simpan Rekening' }}
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        @endif

    </div>

    <!-- Modal Konfirmasi Hapus -->
    @if ($showDeleteModal && $deleteTarget)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" wire:click="cancelDelete" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 p-6">
                    <div class="flex items-start space-x-4">
                        <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Hapus rekening {{ $deleteTarget->bank_name }}?</h3>
                            <p class="text-xs font-mono text-slate-500 mt-1">{{ $deleteTarget->account_number }}</p>
                            <p class="text-xs text-slate-500 mt-2">
                                Rekening yang belum pernah menerima donasi dana dapat dihapus. Rekening yang sudah menerima donasi hanya dapat dinonaktifkan.
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end space-x-3">
                        <button type="button" wire:click="cancelDelete" class="px-4 py-2 bg-white text-slate-700 hover:bg-slate-100 text-xs sm:text-sm font-semibold rounded-xl border border-slate-200 transition">
                            Batal
                        </button>
                        <button type="button" wire:click="deleteAccount" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition">
                            Hapus Rekening
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Konfirmasi Nonaktifkan -->
    @if ($showStatusModal && $statusAccount)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" wire:click="cancelStatus" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 p-6">
                    <div class="flex items-start space-x-4">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Nonaktifkan rekening {{ $statusAccount->bank_name }}?</h3>
                            <p class="text-xs font-mono text-slate-500 mt-1">{{ $statusAccount->account_number }}</p>
                            <p class="text-xs text-slate-500 mt-2">
                                Rekening akan disembunyikan dari halaman donasi dana. Donasi yang sudah tercatat tetap aman, dan rekening dapat diaktifkan kembali kapan saja.
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end space-x-3">
                        <button type="button" wire:click="cancelStatus" class="px-4 py-2 bg-white text-slate-700 hover:bg-slate-100 text-xs sm:text-sm font-semibold rounded-xl border border-slate-200 transition">
                            Batal
                        </button>
                        <button type="button" wire:click="confirmDeactivate" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition">
                            Nonaktifkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>