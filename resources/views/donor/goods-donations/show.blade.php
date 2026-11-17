@extends('layouts.donor')

@section('title', 'Detail Janji Donasi')

@section('panel')
    @php
        $badge = [
            'dijanjikan' => 'bg-blue-50 text-blue-700 border-blue-200/60',
            'diterima' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60',
            'disalurkan' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60',
            'dibatalkan' => 'bg-slate-100 text-slate-600 border-slate-200/60',
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

    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-2.5">
            <h3 class="text-base font-bold font-mono text-slate-900">{{ $donation->code }}</h3>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium border {{ $badge[$status] }}">
                {{ $donation->status->label() }}
            </span>
        </div>
        <a href="{{ route('donor.goods-donations.index') }}"
           class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-50 transition"
           title="Tutup panel">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </a>
    </div>

    <div class="space-y-5">
        <dl class="space-y-2.5 text-xs sm:text-sm border-b border-slate-100 pb-4">
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Barang</dt>
                <dd class="text-right font-semibold text-slate-900">{{ $info['item_name'] }} {{ $donation->quantity }} {{ $info['item_unit'] }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Posko</dt>
                <dd class="text-right font-medium text-slate-800">{{ $info['shelter_name'] }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Metode pengantaran</dt>
                <dd class="text-right font-medium text-slate-800">{{ $donation->delivery_method->label() }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">Perkiraan tiba</dt>
                <dd class="text-right font-medium text-slate-800">{{ $donation->est_arrival->locale('id')->translatedFormat('j M Y') }}</dd>
            </div>
        </dl>

        <div>
            <h4 class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Linimasa status</h4>
            <ol class="space-y-3.5 border-l-2 border-slate-200 pl-4 ml-1">
                @foreach ($steps as $step)
                    @php
                        $dot = $step['red'] ? 'bg-red-600' : ($step['done'] ? 'bg-blue-600' : 'bg-slate-300');
                        $title = $step['red'] ? 'text-red-700' : ($step['done'] ? 'text-slate-900' : 'text-slate-400');
                    @endphp
                    <li class="relative">
                        <span class="absolute -left-[21px] top-1.5 h-2.5 w-2.5 rounded-full {{ $dot }}"></span>
                        <p class="text-sm font-medium {{ $title }}">{{ $step['label'] }}</p>
                        @if ($step['date'])
                            <p class="text-xs text-slate-500">{{ $step['date']->locale('id')->translatedFormat('j M Y, H:i') }}</p>
                        @endif
                        @if ($step['note'])
                            <p class="mt-0.5 text-xs text-red-600">{{ $step['note'] }}</p>
                        @endif
                    </li>
                @endforeach
            </ol>
        </div>

        @if ($donation->isPledged())
            <div class="grid grid-cols-2 gap-3 pt-2">
                <a href="{{ route('donor.goods-donations.edit', $donation) }}"
                   class="w-full py-2.5 px-4 bg-white hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-lg border border-slate-200 transition text-center">
                    Ubah
                </a>
                <button type="button"
                        data-cancel
                        data-action="{{ route('donor.goods-donations.destroy', $donation) }}"
                        data-code="{{ $donation->code }}"
                        data-qty="{{ $donation->quantity }} {{ $info['item_unit'] }}"
                        class="w-full py-2.5 px-4 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg shadow-xs transition text-center">
                    Batalkan
                </button>
            </div>
        @endif
    </div>
@endsection
