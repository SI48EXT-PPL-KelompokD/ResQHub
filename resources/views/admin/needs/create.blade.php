@component('layouts.admin')
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="space-y-1">
            <p class="text-sm font-medium text-blue-600">Modul D · Kebutuhan</p>
            <h1 class="text-2xl font-bold text-slate-900">Tambah Kebutuhan Bantuan</h1>
            <p class="text-sm text-slate-600">Pilih barang dari katalog dan jelaskan kebutuhan posko yang masih terbuka.</p>
        </div>

        <form method="POST" action="{{ route('admin.needs.store') }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="space-y-1.5 sm:col-span-2">
                    <label for="shelter_id" class="block text-sm font-semibold text-slate-700">Posko</label>
                    <select id="shelter_id" name="shelter_id" required class="block w-full cursor-pointer rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="">Pilih posko</option>
                        @foreach ($shelters as $shelter)
                            <option value="{{ $shelter->id }}" @selected(old('shelter_id') == $shelter->id)>{{ $shelter->name }} · {{ ucfirst($shelter->status) }}</option>
                        @endforeach
                    </select>
                    @if ($shelters->isEmpty())
                        <p class="text-xs text-amber-700">Belum ada posko aktif yang menjadi tanggung jawab Anda.</p>
                    @endif
                    @error('shelter_id')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-1.5 sm:col-span-2">
                    <label for="item_id" class="block text-sm font-semibold text-slate-700">Barang</label>
                    <select id="item_id" name="item_id" required class="block w-full cursor-pointer rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="">Pilih barang dari katalog</option>
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}" @selected(old('item_id') == $item->id)>{{ $item->name }} ({{ $item->unit }})</option>
                        @endforeach
                    </select>
                    @error('item_id')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-1.5">
                    <label for="qty_needed" class="block text-sm font-semibold text-slate-700">Jumlah dibutuhkan</label>
                    <input id="qty_needed" type="number" min="1" name="qty_needed" value="{{ old('qty_needed') }}" required class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    @error('qty_needed')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-1.5">
                    <label for="priority" class="block text-sm font-semibold text-slate-700">Prioritas</label>
                    <select id="priority" name="priority" required class="block w-full cursor-pointer rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        @foreach ($priorities as $value => $label)
                            <option value="{{ $value }}" @selected(old('priority', 'normal') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('priority')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-1.5">
                    <label for="deadline" class="block text-sm font-semibold text-slate-700">Batas waktu</label>
                    <input id="deadline" type="date" name="deadline" value="{{ old('deadline') }}" min="{{ now()->toDateString() }}" class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <p class="text-xs text-slate-500">Wajib diisi jika prioritas Mendesak.</p>
                    @error('deadline')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-6 flex flex-wrap justify-end gap-3">
                <a href="{{ route('admin.needs.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</a>
                <button type="submit" class="cursor-pointer rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Simpan kebutuhan</button>
            </div>
        </form>
    </div>
@endcomponent
