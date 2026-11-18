@extends('layouts.donor')

@section('title', 'Janji Donasi Barang')

@section('panel')
    @php
        $openNeeds = \Illuminate\Support\Facades\DB::table('aid_needs')
            ->join('items', 'items.id', '=', 'aid_needs.item_id')
            ->join('shelters', 'shelters.id', '=', 'aid_needs.shelter_id')
            ->whereNull('aid_needs.deleted_at')
            ->where('aid_needs.status', 'terbuka')
            ->select('aid_needs.id', 'items.name as item_name', 'shelters.name as shelter_name', 'aid_needs.qty_needed', 'aid_needs.qty_pledged', 'items.unit')
            ->get();
    @endphp

    <div class="flex items-center justify-between mb-5">
        <h3 class="text-base font-bold text-slate-900">Janji Donasi Barang</h3>
        <a href="{{ route('donor.goods-donations.index') }}"
           class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-50 transition"
           title="Tutup panel">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </a>
    </div>

    <form method="POST" action="{{ route('donor.goods-donations.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="aid_need_id" value="{{ $need->id }}">

        @if ($openNeeds->count() > 1)
            <div>
                <label for="need_switcher" class="block text-xs font-medium text-slate-700 mb-1.5">Kebutuhan posko</label>
                <select id="need_switcher"
                        onchange="window.location.href='{{ route('donor.goods-donations.create') }}?aid_need=' + this.value"
                        class="w-full py-2 px-3 text-sm bg-white text-slate-800 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 transition">
                    @foreach ($openNeeds as $opt)
                        <option value="{{ $opt->id }}" @selected($opt->id === $need->id)>
                            {{ $opt->item_name }} · {{ $opt->shelter_name }} (sisa {{ max(0, $opt->qty_needed - $opt->qty_pledged) }} {{ $opt->unit }})
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        @include('donor.goods-donations._form', [
            'submit' => 'Konfirmasi Janji'
        ])

        <p class="text-xs text-slate-400 leading-relaxed">
            Setelah dikonfirmasi Anda mendapat kode donasi (contoh DN-2610-0042) untuk ditunjukkan saat menyerahkan barang.
        </p>
    </form>
@endsection
