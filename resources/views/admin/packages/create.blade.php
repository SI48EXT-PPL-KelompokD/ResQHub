@component('layouts.admin')
    <div class="mx-auto max-w-5xl space-y-6">
        <div>
            <p class="text-sm font-semibold text-blue-700">Modul D · Kebutuhan bantuan</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Tambah Paket Bantuan</h1>
            <p class="mt-1 text-sm text-slate-600">Satu paket dapat berisi beberapa barang dengan jumlah berbeda.</p>
        </div>

        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800"><p class="font-semibold">Periksa kembali isian berikut:</p><ul class="mt-2 list-disc space-y-1 pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <form method="POST" action="{{ route('admin.packages.store') }}" x-data="packageForm(@js(old('items', [['item_id' => '', 'quantity' => 1]])))" class="space-y-6">
            @csrf
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="font-semibold text-slate-900">Informasi paket</h2>
                <div class="mt-5 grid gap-5 md:grid-cols-2">
                    <div class="space-y-1.5"><label for="name" class="block text-sm font-semibold text-slate-700">Nama paket</label><input id="name" name="name" value="{{ old('name') }}" required autofocus class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Contoh: Paket Sembako Keluarga 3 Hari">@error('name')<p class="text-xs text-rose-700">{{ $message }}</p>@enderror</div>
                    <div class="space-y-1.5"><label for="target" class="block text-sm font-semibold text-slate-700">Target penerima</label><select id="target" name="target" required class="block w-full cursor-pointer rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"><option value="">Pilih target</option>@foreach ($targets as $value => $label)<option value="{{ $value }}" @selected(old('target') === $value)>{{ $label }}</option>@endforeach</select>@error('target')<p class="text-xs text-rose-700">{{ $message }}</p>@enderror</div>
                    <div class="space-y-1.5 md:col-span-2"><label for="description" class="block text-sm font-semibold text-slate-700">Deskripsi <span class="font-normal text-slate-500">(opsional)</span></label><textarea id="description" name="description" rows="3" class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Jelaskan penggunaan atau periode paket.">{{ old('description') }}</textarea>@error('description')<p class="text-xs text-rose-700">{{ $message }}</p>@enderror</div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3"><div><h2 class="font-semibold text-slate-900">Isi paket</h2><p class="mt-1 text-sm text-slate-500">Minimal satu barang. Barang tidak boleh dipilih dua kali.</p></div><button type="button" @click="addItem()" class="cursor-pointer rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-800 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-200">Tambah barang</button></div>
                <div class="mt-5 space-y-3">
                    <template x-for="(row, index) in items" :key="index">
                        <div class="grid gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-[1fr_160px_auto] sm:items-end">
                            <div class="space-y-1.5"><label :for="'item-'+index" class="block text-sm font-semibold text-slate-700">Barang</label><select :id="'item-'+index" :name="'items['+index+'][item_id]'" x-model="row.item_id" required class="block w-full cursor-pointer rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"><option value="">Pilih barang</option>@foreach ($items as $item)<option value="{{ $item->id }}">{{ $item->name }} ({{ $item->unit }})</option>@endforeach</select></div>
                            <div class="space-y-1.5"><label :for="'quantity-'+index" class="block text-sm font-semibold text-slate-700">Jumlah</label><input :id="'quantity-'+index" :name="'items['+index+'][quantity]'" x-model="row.quantity" type="number" min="1" required class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"></div>
                            <button type="button" @click="removeItem(index)" :disabled="items.length === 1" class="cursor-pointer rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50">Hapus</button>
                        </div>
                    </template>
                </div>
                @error('items')<p class="mt-3 text-xs text-rose-700">{{ $message }}</p>@enderror
            </section>

            <div class="flex flex-wrap justify-end gap-3"><a href="{{ route('admin.packages.index') }}" class="cursor-pointer rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-200">Batal</a><button type="submit" class="cursor-pointer rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-200">Simpan paket</button></div>
        </form>
    </div>
    @push('scripts')
        <script>
            function packageForm(initialItems) {
                return { items: initialItems.length ? initialItems : [{ item_id: '', quantity: 1 }], addItem() { this.items.push({ item_id: '', quantity: 1 }); }, removeItem(index) { if (this.items.length > 1) this.items.splice(index, 1); } };
            }
        </script>
    @endpush
@endcomponent
