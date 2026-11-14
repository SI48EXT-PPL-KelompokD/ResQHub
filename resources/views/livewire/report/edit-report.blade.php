<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Halaman -->
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.reports.show', $report) }}" wire:navigate class="p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-white border border-transparent hover:border-slate-200 transition" title="Kembali ke detail laporan">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900">Ubah Laporan Kejadian</h1>
            <p class="text-sm text-slate-500 mt-1">Laporan hanya dapat diubah selama masih menunggu verifikasi.</p>
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">

        @include('livewire.report.partials.form-fields')

        <!-- Foto Kejadian -->
        <section class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 space-y-4">
            <div>
                <h2 class="text-sm font-bold text-slate-800">Foto Kejadian</h2>
                <p class="text-xs text-slate-500 mt-0.5">Opsional, maksimal {{ $maxPhotos }} foto JPG/PNG, masing-masing 2 MB. Foto yang ditandai dihapus saat perubahan disimpan.</p>
            </div>

            <div class="grid grid-cols-3 gap-3">
                @foreach ($existingPhotos as $existing)
                    @php $marked = in_array($existing->id, $removed_photo_ids, true); @endphp
                    <div wire:key="existing-{{ $existing->id }}" class="relative aspect-square rounded-xl overflow-hidden border {{ $marked ? 'border-rose-300' : 'border-slate-200' }} bg-slate-100">
                        <img src="{{ $existing->url }}" alt="Foto laporan" class="w-full h-full object-cover {{ $marked ? 'opacity-30 grayscale' : '' }}">
                        @if ($marked)
                            <div class="absolute inset-0 flex flex-col items-center justify-center gap-1.5 p-2 text-center">
                                <span class="text-[11px] font-semibold text-rose-700">Akan dihapus</span>
                                <button type="button" wire:click="restoreExistingPhoto({{ $existing->id }})" class="px-2 py-1 rounded-lg bg-white border border-slate-200 text-[11px] font-semibold text-slate-700 hover:bg-slate-50">Urungkan</button>
                            </div>
                        @else
                            <button type="button" wire:click="removeExistingPhoto({{ $existing->id }})" class="absolute top-1.5 right-1.5 w-7 h-7 rounded-full bg-white/95 text-rose-600 shadow-sm flex items-center justify-center hover:bg-rose-50" title="Hapus foto">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        @endif
                    </div>
                @endforeach

                @foreach ($photos as $index => $photo)
                    <div wire:key="photo-{{ $index }}" class="relative aspect-square rounded-xl overflow-hidden border border-blue-200 bg-slate-100">
                        @if (method_exists($photo, 'isPreviewable') && $photo->isPreviewable())
                            <img src="{{ $photo->temporaryUrl() }}" alt="Pratinjau foto baru {{ $index + 1 }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-[11px] text-slate-500 p-2 text-center">{{ $photo->getClientOriginalName() }}</div>
                        @endif
                        <span class="absolute bottom-1.5 left-1.5 px-1.5 py-0.5 rounded-md bg-blue-600 text-white text-[10px] font-semibold">Baru</span>
                        <button type="button" wire:click="removePhoto({{ $index }})" class="absolute top-1.5 right-1.5 w-7 h-7 rounded-full bg-white/95 text-rose-600 shadow-sm flex items-center justify-center hover:bg-rose-50" title="Hapus foto">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endforeach

                @if (count($photos) < $availableSlots)
                    <label class="aspect-square rounded-xl border-2 border-dashed border-slate-200 hover:border-blue-400 hover:bg-blue-50/40 flex flex-col items-center justify-center text-slate-400 hover:text-blue-600 cursor-pointer transition">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span class="text-[11px] font-semibold mt-1">Tambah foto</span>
                        <input type="file" wire:model="newPhotos" multiple accept="image/jpeg,image/png" class="sr-only">
                    </label>
                @endif
            </div>

            <div wire:loading wire:target="newPhotos" class="text-xs text-blue-600">Mengunggah foto...</div>
            @error('photos') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            @error('photos.*') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            @error('newPhotos.*') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </section>

        <!-- Aksi -->
        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
            <a href="{{ route('admin.reports.show', $report) }}" wire:navigate class="inline-flex justify-center px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Batal</a>
            <button type="submit" wire:loading.attr="disabled" wire:target="save,newPhotos" class="inline-flex justify-center items-center space-x-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white text-sm font-semibold shadow-sm shadow-blue-500/20 transition">
                <span wire:loading.remove wire:target="save">Simpan Perubahan</span>
                <span wire:loading wire:target="save">Menyimpan...</span>
            </button>
        </div>
    </form>
</div>
