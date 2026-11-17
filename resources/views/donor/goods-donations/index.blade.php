@extends('layouts.donor')

@section('title', 'Donasi Barang Saya')

@section('content')
    @php
        $badge = [
            'dijanjikan' => 'bg-blue-100 text-blue-800',
            'diterima' => 'bg-green-100 text-green-800',
            'disalurkan' => 'bg-green-100 text-green-800',
            'dibatalkan' => 'bg-gray-100 text-gray-700',
        ];
    @endphp

    <div class="space-y-3">
        @forelse ($donations as $donation)
            <div class="relative rounded-lg border border-gray-200 bg-white p-4 hover:bg-gray-50">
                <div class="flex items-start justify-between gap-3">
                    <a href="{{ route('donor.goods-donations.show', $donation) }}" class="font-medium after:absolute after:inset-0">
                        {{ $donation->code }} · {{ $donation->item_name }} {{ $donation->quantity }} {{ $donation->item_unit }}
                    </a>
                    <span class="shrink-0 rounded-md px-2 py-0.5 text-xs font-medium {{ $badge[$donation->status->value] }}">
                        {{ $donation->status->label() }}
                    </span>
                </div>

                <p class="mt-1 text-sm text-gray-500">
                    @switch($donation->status->value)
                        @case('dijanjikan')
                            {{ $donation->shelter_name }} · perkiraan tiba {{ $donation->est_arrival->locale('id')->translatedFormat('j M') }} · {{ Str::lower($donation->delivery_method->label()) }}
                            @break
                        @case('diterima')
                            Diterima di {{ $donation->shelter_name }}
                            @break
                        @case('disalurkan')
                            Disalurkan dari {{ $donation->shelter_name }}
                            @break
                        @default
                            {{ Str::startsWith($donation->cancel_reason, 'Dibatalkan otomatis') ? $donation->cancel_reason : 'Dibatalkan: '.$donation->cancel_reason }}
                    @endswitch
                </p>

                @if ($donation->isPledged())
                    <div class="relative z-10 mt-3 flex gap-2">
                        <a href="{{ route('donor.goods-donations.edit', $donation) }}"
                           class="inline-flex min-h-11 items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-blue-300">
                            Ubah
                        </a>
                        <button type="button"
                                data-cancel
                                data-action="{{ route('donor.goods-donations.destroy', $donation) }}"
                                data-code="{{ $donation->code }}"
                                data-qty="{{ $donation->quantity }} {{ $donation->item_unit }}"
                                class="min-h-11 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-300">
                            Batalkan
                        </button>
                    </div>
                @endif
            </div>
        @empty
            <p class="py-8 text-center text-sm text-gray-500">Belum ada janji donasi.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $donations->links() }}</div>

    @include('donor.goods-donations._cancel-modal')
@endsection
