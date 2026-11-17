@extends('layouts.admin', ['title' => View::yieldContent('title', 'Donasi Barang Saya')])

@section('content')
    @php
        $donations ??= \App\Models\GoodsDonation::query()
            ->withDisplay()
            ->ownedBy(auth()->user())
            ->orderByDesc('goods_donations.id')
            ->paginate(10);

        $activeDonationId = isset($donation) ? $donation->id : null;
        $hasPanel = View::hasSection('panel');

        $badge = [
            'dijanjikan' => 'bg-blue-50 text-blue-700 border-blue-200/60',
            'diterima' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60',
            'disalurkan' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60',
            'dibatalkan' => 'bg-slate-100 text-slate-600 border-slate-200/60',
        ];
    @endphp

    <div class="space-y-6">
        @if (session('success'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800 shadow-2xs">
                {{ session('success') }}
            </div>
        @endif

        @foreach (['status', 'aid_need_id', 'cancel_reason'] as $key)
            @error($key)
                <div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-800 shadow-2xs">
                    {{ $message }}
                </div>
            @enderror
        @endforeach

        <!-- Page Header (Gaya PBI-14) -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Donasi Barang Saya</h1>
                <p class="text-sm text-slate-500 mt-0.5">PBI-13 · Kelola janji donasi barang untuk kebutuhan posko evakuasi</p>
            </div>

            <a href="{{ route('donor.goods-donations.create') }}"
               class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-medium rounded-lg shadow-xs transition duration-150">
                Janji Donasi Baru
            </a>
        </div>

        <!-- Main Content: Dua Kolom (Daftar Kiri & Panel Form/Detail Kanan, seperti PBI-14) -->
        <div class="grid grid-cols-1 {{ $hasPanel ? 'lg:grid-cols-12' : 'lg:grid-cols-1' }} gap-6 items-start">

            <!-- Kolom Kiri: Daftar Donasi Barang Saya -->
            <div class="{{ $hasPanel ? 'lg:col-span-7 xl:col-span-8' : 'w-full' }}">
                <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs space-y-3">
                    @forelse ($donations as $item)
                        <div class="relative rounded-lg border p-4 transition {{ $activeDonationId === $item->id ? 'bg-blue-50/70 border-blue-200' : 'bg-white border-slate-200/80 hover:bg-slate-50/60' }}">
                            <div class="flex items-start justify-between gap-3">
                                <a href="{{ route('donor.goods-donations.show', $item) }}" class="text-sm font-semibold text-slate-900 after:absolute after:inset-0">
                                    {{ $item->code }} · {{ $item->item_name }} {{ $item->quantity }} {{ $item->item_unit }}
                                </a>
                                <span class="shrink-0 inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium border {{ $badge[$item->status->value] }}">
                                    {{ $item->status->label() }}
                                </span>
                            </div>

                            <p class="mt-1 text-xs text-slate-500">
                                @switch($item->status->value)
                                    @case('dijanjikan')
                                        {{ $item->shelter_name }} · perkiraan tiba {{ $item->est_arrival->locale('id')->translatedFormat('j M') }} · {{ Str::lower($item->delivery_method->label()) }}
                                        @break
                                    @case('diterima')
                                        Diterima di {{ $item->shelter_name }}
                                        @break
                                    @case('disalurkan')
                                        Disalurkan dari {{ $item->shelter_name }}
                                        @break
                                    @default
                                        {{ Str::startsWith($item->cancel_reason, 'Dibatalkan otomatis') ? $item->cancel_reason : 'Dibatalkan: '.$item->cancel_reason }}
                                @endswitch
                            </p>

                            <div class="relative z-10 mt-3 flex flex-wrap items-center gap-2">
                                <a href="{{ route('donor.goods-donations.show', $item) }}"
                                   class="px-3 py-1.5 text-xs font-medium text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-md shadow-2xs transition">
                                    Detail
                                </a>

                                @if ($item->isPledged())
                                    <a href="{{ route('donor.goods-donations.edit', $item) }}"
                                       class="px-3 py-1.5 text-xs font-medium text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-md shadow-2xs transition">
                                        Ubah
                                    </a>
                                    <button type="button"
                                            data-cancel
                                            data-action="{{ route('donor.goods-donations.destroy', $item) }}"
                                            data-code="{{ $item->code }}"
                                            data-qty="{{ $item->quantity }} {{ $item->item_unit }}"
                                            class="px-3 py-1.5 text-xs font-medium text-white bg-red-600 hover:bg-red-700 rounded-md shadow-2xs transition">
                                        Batalkan
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-slate-400">
                            <p class="text-sm font-semibold text-slate-700">Belum ada janji donasi.</p>
                            <p class="text-xs text-slate-400 mt-1">Klik tombol "Janji Donasi Baru" di kanan atas untuk membuat janji donasi barang.</p>
                        </div>
                    @endforelse

                    @if ($donations->hasPages())
                        <div class="pt-2">
                            {{ $donations->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Kolom Kanan: Panel Form (Tambah / Ubah) atau Detail -->
            @if ($hasPanel)
                <div class="lg:col-span-5 xl:col-span-4 lg:sticky lg:top-24 self-start">
                    <div class="bg-white rounded-xl border border-slate-200/80 p-6 shadow-2xs">
                        @yield('panel')
                    </div>
                </div>
            @endif

        </div>
    </div>

    @include('donor.goods-donations._cancel-modal')
@endsection
