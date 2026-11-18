@component('layouts.admin')
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="space-y-1">
            <p class="text-sm font-medium text-blue-600">Modul D · Kebutuhan</p>
            <h1 class="text-2xl font-bold text-slate-900">Edit Kebutuhan Bantuan</h1>
            <p class="text-sm text-slate-600">Jumlah tidak boleh lebih kecil dari komitmen yang sudah dijanjikan atau terpenuhi.</p>
        </div>

        <form method="POST" action="{{ route('admin.needs.update', $aidNeed) }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PUT')
            <div class="mb-6 grid gap-4 rounded-xl bg-slate-50 p-4 text-sm sm:grid-cols-2">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Posko</span>
                    <p class="mt-1 font-semibold text-slate-800">{{ $aidNeed->shelter->name }}</p>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Barang</span>
                    <p class="mt-1 font-semibold text-slate-800">{{ $aidNeed->item->name }} ({{ $aidNeed->item->unit }})</p>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Dijanjikan</span>
                    <p class="mt-1 text-slate-700">{{ number_format($aidNeed->qty_pledged) }}</p>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Terpenuhi</span>
                    <p class="mt-1 text-slate-700">{{ number_format($aidNeed->qty_fulfilled) }}</p>
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="space-y-1.5">
                    <label for="qty_needed" class="block text-sm font-semibold text-slate-700">Jumlah dibutuhkan</label>
                    <input id="qty_needed" type="number" min="{{ max(1, $aidNeed->qty_pledged + $aidNeed->qty_fulfilled) }}" name="qty_needed" value="{{ old('qty_needed', $aidNeed->qty_needed) }}" required class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    @error('qty_needed')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-1.5">
                    <label for="priority" class="block text-sm font-semibold text-slate-700">Prioritas</label>
                    <select id="priority" name="priority" required class="block w-full cursor-pointer rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        @foreach ($priorities as $value => $label)
                            <option value="{{ $value }}" @selected(old('priority', $aidNeed->priority) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('priority')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-1.5">
                    <label for="deadline" class="block text-sm font-semibold text-slate-700">Batas waktu</label>
                    <input id="deadline" type="date" name="deadline" value="{{ old('deadline', $aidNeed->deadline?->format('Y-m-d')) }}" min="{{ now()->toDateString() }}" class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <p class="text-xs text-slate-500">Wajib diisi jika prioritas Mendesak.</p>
                    @error('deadline')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-6 flex flex-wrap justify-end gap-3">
                <a href="{{ route('admin.needs.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</a>
                <button type="submit" class="cursor-pointer rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Simpan perubahan</button>
            </div>
        </form>
    </div>
@endcomponent
