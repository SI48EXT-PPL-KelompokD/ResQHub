@php
    $donation ??= null;
    $field = 'block w-full rounded-lg border bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500';
@endphp

<div class="rounded-lg bg-blue-50 p-4 text-sm text-blue-800">
    <p class="font-medium">{{ $info['item_name'] }} · {{ $info['shelter_name'] }}</p>
    <p class="mt-1">
        Kebutuhan {{ $need->qty_needed }} {{ $info['item_unit'] }} · terpenuhi {{ $need->qty_fulfilled }} · dijanjikan {{ $need->qty_pledged }} · sisa {{ $remaining }} {{ $info['item_unit'] }}.
        Prioritas {{ ucfirst($need->priority) }}@if ($need->deadline), batas {{ $need->deadline->locale('id')->translatedFormat('j M Y') }}@endif.
    </p>
</div>

<div>
    <label for="quantity" class="mb-2 block text-sm font-medium text-gray-900">Jumlah yang dijanjikan</label>
    <div class="flex">
        <input type="number" id="quantity" name="quantity" min="1" max="{{ $remaining }}" step="1"
               value="{{ old('quantity', $donation?->quantity) }}"
               class="block w-full min-w-0 flex-1 rounded-l-lg border bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 @error('quantity') border-red-500 @else border-gray-300 @enderror">
        <span class="inline-flex items-center rounded-r-lg border border-l-0 border-gray-300 bg-gray-100 px-3 text-sm text-gray-600">{{ $info['item_unit'] }}</span>
    </div>
    <p class="mt-1 text-xs text-gray-500">Maksimal {{ $remaining }} {{ $info['item_unit'] }} (sisa kebutuhan).</p>
    @error('quantity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="delivery_method" class="mb-2 block text-sm font-medium text-gray-900">Metode pengantaran</label>
        @php $method = old('delivery_method', $donation?->delivery_method?->value); @endphp
        <select id="delivery_method" name="delivery_method"
                class="{{ $field }} @error('delivery_method') border-red-500 @else border-gray-300 @enderror">
            <option value="antar" @selected($method === 'antar')>Antar sendiri ke posko</option>
            <option value="jemput" @selected($method === 'jemput')>Minta dijemput</option>
        </select>
        @error('delivery_method') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="est_arrival" class="mb-2 block text-sm font-medium text-gray-900">Perkiraan tiba</label>
        <input type="date" id="est_arrival" name="est_arrival"
               value="{{ old('est_arrival', $donation?->est_arrival?->format('Y-m-d')) }}"
               class="{{ $field }} @error('est_arrival') border-red-500 @else border-gray-300 @enderror">
        @error('est_arrival') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>

<button type="submit"
        class="min-h-11 w-full rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300">
    {{ $submit }}
</button>

