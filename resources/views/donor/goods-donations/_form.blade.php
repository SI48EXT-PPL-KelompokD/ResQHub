@php
    $donation ??= null;
    $field = 'w-full py-2 px-3 text-sm bg-white text-slate-800 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition';
@endphp

<div class="rounded-lg bg-blue-50 border border-blue-100 p-3.5 text-xs sm:text-sm text-blue-800">
    <p class="font-semibold">{{ $info['item_name'] }} · {{ $info['shelter_name'] }}</p>
    <p class="mt-1 leading-relaxed">
        Kebutuhan {{ $need->qty_needed }} {{ $info['item_unit'] }} · terpenuhi {{ $need->qty_fulfilled }} · dijanjikan {{ $need->qty_pledged }} · sisa {{ $remaining }} {{ $info['item_unit'] }}.
        Prioritas {{ ucfirst($need->priority) }}@if ($need->deadline), batas {{ $need->deadline->locale('id')->translatedFormat('j M Y') }}@endif.
    </p>
</div>

<div>
    <label for="quantity" class="block text-xs font-medium text-slate-700 mb-1.5">Jumlah yang dijanjikan</label>
    <div class="flex">
        <input type="number" id="quantity" name="quantity" min="1" max="{{ $remaining }}" step="1"
               value="{{ old('quantity', $donation?->quantity) }}"
               class="block w-full min-w-0 flex-1 rounded-l-lg border bg-slate-50/40 focus:bg-white py-2 px-3 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600 @error('quantity') border-red-500 @else border-slate-200 @enderror">
        <span class="inline-flex items-center rounded-r-lg border border-l-0 border-slate-200 bg-slate-100 px-3 text-xs font-medium text-slate-600">{{ $info['item_unit'] }}</span>
    </div>
    <p class="mt-1 text-xs text-slate-400">Maksimal {{ $remaining }} {{ $info['item_unit'] }} (sisa kebutuhan).</p>
    @error('quantity') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
</div>

<div>
    <label for="delivery_method" class="block text-xs font-medium text-slate-700 mb-1.5">Metode pengantaran</label>
    @php $method = old('delivery_method', $donation?->delivery_method?->value); @endphp
    <select id="delivery_method" name="delivery_method"
            class="{{ $field }} @error('delivery_method') border-red-500 @else border-slate-200 @enderror">
        <option value="antar" @selected($method === 'antar')>Antar sendiri ke posko</option>
        <option value="jemput" @selected($method === 'jemput')>Minta dijemput</option>
    </select>
    @error('delivery_method') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
</div>

<div>
    <label for="est_arrival" class="block text-xs font-medium text-slate-700 mb-1.5">Perkiraan tiba</label>
    <input type="date" id="est_arrival" name="est_arrival"
           value="{{ old('est_arrival', $donation?->est_arrival?->format('Y-m-d')) }}"
           class="{{ $field }} @error('est_arrival') border-red-500 @else border-slate-200 @enderror">
    @error('est_arrival') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
</div>

<div class="grid grid-cols-2 gap-3 pt-2">
    <a href="{{ route('donor.goods-donations.index') }}"
       class="w-full py-2.5 px-4 bg-white hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-lg border border-slate-200 transition text-center">
        Batal
    </a>
    <button type="submit"
            class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium rounded-lg shadow-xs transition text-center">
        {{ $submit }}
    </button>
</div>
