@php
    $badge = match ($report->status) {
        \App\Models\DisasterReport::STATUS_ACCEPTED => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        \App\Models\DisasterReport::STATUS_REJECTED => 'bg-rose-50 text-rose-700 border-rose-200',
        default => 'bg-amber-50 text-amber-700 border-amber-200',
    };
@endphp
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div class="flex items-start gap-3 min-w-0">
            <a href="{{ route('admin.reports.index') }}" wire:navigate class="p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-white border border-transparent hover:border-slate-200 transition" title="Kembali ke daftar laporan">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full border {{ $badge }}">{{ $report->status_label }}</span>
                    <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">{{ $report->disaster_type_label }}</span>
                </div>
                <h1 class="mt-2 text-xl sm:text-2xl font-bold tracking-tight text-slate-900 break-words">{{ $report->title }}</h1>
                <p class="text-sm text-slate-500 mt-1">Detail Laporan Kejadian</p>
            </div>
        </div>

        @can('update', $report)
            <div class="flex gap-2 shrink-0">
                <a href="{{ route('admin.reports.edit', $report) }}" wire:navigate class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Ubah</a>
                <button
                    type="button"
                    wire:click="delete"
                    wire:confirm="Hapus laporan ini? Laporan dan fotonya akan dihapus permanen."
                    wire:loading.attr="disabled"
                    class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-rose-200 bg-white text-sm font-semibold text-rose-600 hover:bg-rose-50 disabled:opacity-60 transition"
                >Hapus</button>
            </div>
        @endcan
    </div>

    @if ($statusMessage)
        <div class="flex items-start space-x-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
            <svg class="w-5 h-5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ $statusMessage }}</span>
        </div>
    @endif

    @if ($report->status === \App\Models\DisasterReport::STATUS_REJECTED && $report->reject_reason)
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-sm text-rose-800">
            <span class="font-semibold">Alasan penolakan:</span> {{ $report->reject_reason }}
        </div>
    @elseif ($report->status === \App\Models\DisasterReport::STATUS_ACCEPTED)
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-sm text-emerald-800">
            Laporan diterima{{ $report->disasterEvent ? ' dan dikaitkan ke kejadian "'.$report->disasterEvent->name.'"' : '' }}. Laporan yang sudah diverifikasi tidak dapat diubah atau dihapus.
        </div>
    @elseif (! $report->isPending())
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-600">Laporan yang sudah diverifikasi tidak dapat diubah atau dihapus.</div>
    @endif

    @include('livewire.report.partials.detail-sections')
</div>
