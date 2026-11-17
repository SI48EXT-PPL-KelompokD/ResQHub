@extends('layouts.donor')

@section('title', 'Detail Janji Donasi')
@section('back', route('donor.goods-donations.index'))

@section('content')
    @php
        $badge = [
            'dijanjikan' => 'bg-blue-100 text-blue-800',
            'diterima' => 'bg-green-100 text-green-800',
            'disalurkan' => 'bg-green-100 text-green-800',
            'dibatalkan' => 'bg-gray-100 text-gray-700',
        ];
        $status = $donation->status->value;
        $cancelLog = $logs->first(fn ($l) => in_array($l->action, ['cancelled_goods_donation', 'auto_cancelled_goods_donation']));
        $order = ['dijanjikan' => 0, 'diterima' => 1, 'disalurkan' => 2];
        $current = $order[$status] ?? 0;

        $steps = $status === 'dibatalkan'
            ? [
                ['label' => 'Dijanjikan', 'done' => true, 'red' => false, 'date' => $donation->created_at, 'note' => null],
                ['label' => 'Dibatalkan', 'done' => true, 'red' => true, 'date' => $cancelLog?->created_at, 'note' => $donation->cancel_reason],
            ]
            : [
                ['label' => 'Dijanjikan', 'done' => $current >= 0, 'red' => false, 'date' => $donation->created_at, 'note' => null],
                ['label' => 'Diterima', 'done' => $current >= 1, 'red' => false, 'date' => null, 'note' => null],
                ['label' => 'Disalurkan', 'done' => $current >= 2, 'red' => false, 'date' => null, 'note' => null],
            ];
    @endphp

    <div class="space-y-4">
        <div class="rounded-lg border border-gray-200 bg-white p-4">
            <div class="flex items-start justify-between gap-3">
                <p class="font-mono text-lg font-bold text-gray-900">{{ $donation->code }}</p>
                <span class="shrink-0 rounded-md px-2 py-0.5 text-xs font-medium {{ $badge[$status] }}">
                    {{ $donation->status->label() }}
                </span>
            </div>

            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Barang</dt>
                    <dd class="text-right font-medium text-gray-900">{{ $info['item_name'] }} {{ $donation->quantity }} {{ $info['item_unit'] }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Posko</dt>
                    <dd class="text-right font-medium text-gray-900">{{ $info['shelter_name'] }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Metode pengantaran</dt>
                    <dd class="text-right font-medium text-gray-900">{{ $donation->delivery_method->label() }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Perkiraan tiba</dt>
                    <dd class="text-right font-medium text-gray-900">{{ $donation->est_arrival->locale('id')->translatedFormat('j M Y') }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-4">
            <h2 class="mb-3 text-sm font-semibold text-gray-900">Linimasa status</h2>
            <ol class="space-y-4 border-l-2 border-gray-200 pl-4">
                @foreach ($steps as $step)
                    @php
                        $dot = $step['red'] ? 'bg-red-600' : ($step['done'] ? 'bg-blue-600' : 'bg-gray-300');
                        $title = $step['red'] ? 'text-red-700' : ($step['done'] ? 'text-gray-900' : 'text-gray-400');
                    @endphp
                    <li class="relative">
                        <span class="absolute -left-[21px] top-1.5 h-2.5 w-2.5 rounded-full {{ $dot }}"></span>
                        <p class="text-sm font-medium {{ $title }}">{{ $step['label'] }}</p>
                        @if ($step['date'])
                            <p class="text-xs text-gray-500">{{ $step['date']->locale('id')->translatedFormat('j M Y, H:i') }}</p>
                        @endif
                        @if ($step['note'])
                            <p class="mt-0.5 text-xs text-red-600">{{ $step['note'] }}</p>
                        @endif
                    </li>
                @endforeach
            </ol>
        </div>

        @if ($donation->isPledged())
            <div class="flex gap-2">
                <a href="{{ route('donor.goods-donations.edit', $donation) }}"
                   class="inline-flex min-h-11 flex-1 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-blue-300">
                    Ubah
                </a>
                <button type="button"
                        data-cancel
                        data-action="{{ route('donor.goods-donations.destroy', $donation) }}"
                        data-code="{{ $donation->code }}"
                        data-qty="{{ $donation->quantity }} {{ $info['item_unit'] }}"
                        class="min-h-11 flex-1 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-300">
                    Batalkan
                </button>
            </div>
        @endif
    </div>

    @include('donor.goods-donations._cancel-modal')
@endsection

