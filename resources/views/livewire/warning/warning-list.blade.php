<div class="max-w-6xl mx-auto space-y-6">

    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900">Peringatan Dini</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola peringatan dini bencana beserta wilayah sasaran dan instruksi evakuasi untuk warga.</p>
        </div>
        <div class="flex items-center gap-3 self-start sm:self-auto">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full border border-emerald-200 bg-emerald-50 text-emerald-700 text-xs font-semibold">
                {{ $activeCount }} peringatan aktif
            </span>
            <a href="{{ route('admin.warnings.create') }}" wire:navigate class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm shadow-blue-500/20 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Buat Peringatan
            </a>
        </div>
    </div>

    <!-- Filter -->
    <section class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label for="filter-status" class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Status</label>
                <select id="filter-status" wire:model.live="status" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter-level" class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Tingkat</label>
                <select id="filter-level" wire:model.live="level" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <option value="">Semua tingkat</option>
                    @foreach ($levels as $option)
                        <option value="{{ $option }}">{{ \App\Models\EarlyWarning::levelLabel($option) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if ($hasFilters)
            <div class="mt-3 flex items-center justify-between gap-3 text-xs text-slate-500">
                <span>{{ $warnings->total() }} peringatan sesuai filter.</span>
                <button type="button" wire:click="resetFilters" class="font-semibold text-blue-700 hover:text-blue-800">Hapus filter</button>
            </div>
        @endif
    </section>

    <!-- Daftar Peringatan -->
    @if ($warnings->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200/80 p-10 text-center">
            <h2 class="text-sm font-semibold text-slate-800">{{ $hasFilters ? 'Tidak ada peringatan yang sesuai filter' : 'Belum ada peringatan dini' }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ $hasFilters ? 'Ubah atau hapus filter untuk melihat peringatan lain.' : 'Buat peringatan pertama sebagai draf atau langsung terbitkan.' }}</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($warnings as $warning)
                <article wire:key="warning-{{ $warning->id }}" class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 flex flex-col sm:flex-row gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            @include('livewire.warning.partials.badges', ['warning' => $warning])
                        </div>
                        <h2 class="mt-2 text-sm sm:text-base font-semibold text-slate-900 truncate">
                            <a href="{{ route('admin.warnings.show', $warning) }}" wire:navigate class="hover:text-blue-700">{{ $warning->title }}</a>
                        </h2>
                        <p class="mt-0.5 text-xs text-slate-500 truncate">
                            Sasaran:
                            @if (! empty($warning->target_regencies))
                                {{ implode(', ', $warning->target_regencies) }}
                            @endif
                            @if ($warning->radius_km)
                                {{ ! empty($warning->target_regencies) ? '+' : '' }} radius {{ $warning->radius_km }} km dari titik peta
                            @endif
                        </p>
                        <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-[11px] text-slate-500">
                            <span>Berlaku: {{ $warning->starts_at?->translatedFormat('d M Y H:i') }} s.d. {{ $warning->ends_at?->translatedFormat('d M Y H:i') }}</span>
                            <span>Oleh: {{ $warning->issuer?->name ?? '-' }}</span>
                            <span>{{ $warning->updates_count }} pembaruan</span>
                            @if ($warning->disasterEvent)
                                <span>Kejadian: {{ $warning->disasterEvent->name }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="shrink-0 self-start sm:self-center flex gap-2">
                        @can('update', $warning)
                            <a href="{{ route('admin.warnings.edit', $warning) }}" wire:navigate class="inline-flex items-center justify-center px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">Ubah</a>
                        @endcan
                        <a href="{{ route('admin.warnings.show', $warning) }}" wire:navigate class="inline-flex items-center justify-center px-3 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm shadow-blue-500/20 transition">Lihat Detail</a>
                    </div>
                </article>
            @endforeach
        </div>

        <div>
            {{ $warnings->links() }}
        </div>
    @endif
</div>
