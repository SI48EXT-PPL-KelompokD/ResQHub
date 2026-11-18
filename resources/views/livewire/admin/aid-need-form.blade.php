<div class="max-w-3xl mx-auto space-y-6">

    <!-- Breadcrumb & Back -->
    <div class="flex items-center space-x-2 text-xs text-slate-500">
        <a href="{{ route('admin.stocks.index') }}" wire:navigate class="hover:text-blue-600 transition">Stok Logistik Posko</a>
        <span>/</span>
        <span class="text-slate-800 font-semibold">Form Pengajuan Kebutuhan (PBI-11)</span>
    </div>

    <!-- Page Header -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div class="flex items-center space-x-3">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                </svg>
            </div>
            <div>
                <span class="text-[11px] font-bold text-blue-600 uppercase tracking-wider font-mono">Modul D • PBI-11</span>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pengajuan Kebutuhan Bantuan Posko</h1>
                <p class="text-xs text-slate-500">Ajukan kebutuhan bantuan baru sebelum stok di gudang posko habis agar donatur/BPBD dapat menyalurkan.</p>
            </div>
        </div>
        <a 
            href="{{ route('admin.stocks.index') }}" 
            wire:navigate
            class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition"
        >
            Kembali ke Stok
        </a>
    </div>

    <!-- Alert Ringkasan Kondisi Stok Saat Ini -->
    @if ($selectedItem && $selectedShelter)
        <div class="p-4 rounded-2xl border {{ $currentStock && $currentStock->isDepleted() ? 'bg-amber-50 border-amber-200 text-amber-900' : 'bg-blue-50 border-blue-200 text-blue-900' }} shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider block">Status Barang Terpilih:</span>
                    <h3 class="text-base font-extrabold mt-0.5">{{ $selectedItem->name }} ({{ $selectedItem->category_label }})</h3>
                    <p class="text-xs text-slate-600 mt-0.5">Posko: <strong>{{ $selectedShelter->name }}</strong></p>
                </div>
                <div class="text-right">
                    @if ($currentStock)
                        <span class="text-xs font-semibold text-slate-500 block">Sisa Stok Fisik:</span>
                        <span class="text-lg font-black {{ $currentStock->quantity <= 0 ? 'text-rose-600' : ($currentStock->isDepleted() ? 'text-amber-600' : 'text-emerald-600') }}">
                            {{ $currentStock->quantity }} {{ $selectedItem->unit }}
                        </span>
                        <span class="text-[11px] text-slate-400 block">Ambang: {{ $currentStock->min_threshold }} {{ $selectedItem->unit }}</span>
                    @else
                        <span class="text-xs font-bold text-slate-500">Belum ada stok fisik di posko ini</span>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Form Container -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs">
        <form wire:submit.prevent="submit" class="space-y-5">
            
            <!-- Posko -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Posko Pemohon <span class="text-rose-500">*</span></label>
                <select 
                    wire:model.live="shelter_id"
                    class="w-full px-4 py-2.5 bg-slate-50 text-xs sm:text-sm text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition"
                >
                    <option value="">-- Pilih Posko --</option>
                    @foreach ($shelters as $sh)
                        <option value="{{ $sh->id }}">{{ $sh->name }} (Kapasitas: {{ $sh->capacity }})</option>
                    @endforeach
                </select>
                @error('shelter_id') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Barang Bantuan (Terisi Otomatis dari Tabel Stok) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Barang Bantuan (Katalog) <span class="text-rose-500">*</span></label>
                <select 
                    wire:model.live="item_id"
                    class="w-full px-4 py-2.5 bg-slate-50 text-xs sm:text-sm text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition font-medium"
                >
                    <option value="">-- Pilih Barang Bantuan --</option>
                    @foreach ($items as $item)
                        <option value="{{ $item->id }}">
                            {{ $item->name }} ({{ $item->category_label }} • Satuan: {{ $item->unit }})
                        </option>
                    @endforeach
                </select>
                @error('item_id') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Jumlah Kebutuhan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jumlah Yang Dibutuhkan <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <input 
                            type="number" 
                            min="1"
                            wire:model="qty_needed"
                            placeholder="Contoh: 150"
                            class="w-full px-4 py-2.5 bg-slate-50 text-xs sm:text-sm text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition font-bold text-base"
                        />
                        @if ($selectedItem)
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-xs text-slate-400 font-semibold">
                                {{ $selectedItem->unit }}
                            </div>
                        @endif
                    </div>
                    @error('qty_needed') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Prioritas -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tingkat Prioritas <span class="text-rose-500">*</span></label>
                    <select 
                        wire:model="priority"
                        class="w-full px-4 py-2.5 bg-slate-50 text-xs sm:text-sm text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition font-medium"
                    >
                        <option value="mendesak">🚨 Mendesak (Stok Habis / Darurat)</option>
                        <option value="tinggi">⚡ Tinggi (Stok Menipis &lt; Ambang)</option>
                        <option value="normal">📦 Normal (Kebutuhan Cadangan)</option>
                    </select>
                    @error('priority') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Tenggat Waktu (Deadline) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tenggat Waktu Kebutuhan (Opsional)</label>
                <input 
                    type="date" 
                    wire:model="deadline"
                    class="w-full px-4 py-2.5 bg-slate-50 text-xs sm:text-sm text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition font-mono"
                />
                @error('deadline') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Catatan Tambahan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Keterangan / Alasan Kebutuhan</label>
                <textarea 
                    wire:model="notes"
                    rows="2"
                    placeholder="Contoh: Pasokan air mineral di posko sudah di bawah ambang batas kritis seiring bertambahnya 50 pengungsi baru..."
                    class="w-full px-4 py-2.5 bg-slate-50 text-xs sm:text-sm text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition"
                ></textarea>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                <a 
                    href="{{ route('admin.stocks.index') }}" 
                    wire:navigate
                    class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs sm:text-sm font-semibold hover:bg-slate-50 transition"
                >
                    Batal
                </a>
                <button 
                    type="submit"
                    class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold shadow-xs shadow-blue-500/20 transition active:scale-95 cursor-pointer"
                >
                    Kirim Kebutuhan Bantuan
                </button>
            </div>

        </form>
    </div>

</div>

