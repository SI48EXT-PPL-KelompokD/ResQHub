<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900">Laporan Kejadian Saya</h1>
            <p class="text-sm text-slate-500 mt-1">Pantau status laporan bencana yang Anda kirim ke Pusdalops BPBD.</p>
        </div>
        <a
            href="{{ route('admin.reports.create') }}"
            wire:navigate
            class="inline-flex items-center justify-center space-x-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm shadow-blue-500/20 transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
            <span>Buat Laporan</span>
        </a>
    </div>

    @if ($statusMessage)
        <div class="flex items-start space-x-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
            <svg class="w-5 h-5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ $statusMessage }}</span>
        </div>
    @endif

    <!-- Daftar Laporan -->
    @if ($reports->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200/80 p-10 text-center">
            <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h2 class="mt-4 text-sm font-semibold text-slate-800">Belum ada laporan</h2>
            <p class="mt-1 text-sm text-slate-500">Laporkan kejadian bencana di sekitar Anda agar segera diverifikasi petugas.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($reports as $report)
                @php
                    $badge = match ($report->status) {
                        \App\Models\DisasterReport::STATUS_ACCEPTED => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        \App\Models\DisasterReport::STATUS_REJECTED => 'bg-rose-50 text-rose-700 border-rose-200',
                        default => 'bg-amber-50 text-amber-700 border-amber-200',
                    };
                    $thumbnail = $report->photos->first();
                @endphp
                <article wire:key="report-{{ $report->id }}" class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 flex gap-4">
                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl overflow-hidden bg-slate-100 shrink-0 flex items-center justify-center text-slate-300">
                        @if ($thumbnail)
                            <img src="{{ $thumbnail->url }}" alt="Foto {{ $report->title }}" class="w-full h-full object-cover" loading="lazy">
                        @else
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full border {{ $badge }}">{{ $report->status_label }}</span>
                            <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">{{ $report->disaster_type_label }}</span>
                        </div>
                        <h2 class="mt-2 text-sm sm:text-base font-semibold text-slate-900 truncate">{{ $report->title }}</h2>
                        <p class="mt-0.5 text-xs text-slate-500 truncate">{{ $report->address }}</p>
                        <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-[11px] text-slate-500">
                            <span>Kejadian: {{ $report->occurred_at?->translatedFormat('d M Y H:i') }}</span>
                            <span>Dikirim: {{ $report->created_at?->diffForHumans() }}</span>
                            <span>{{ $report->photos->count() }} foto</span>
                        </div>

                        @if ($report->status === \App\Models\DisasterReport::STATUS_REJECTED && $report->reject_reason)
                            <p class="mt-3 text-xs text-rose-700 bg-rose-50 border border-rose-100 rounded-lg px-3 py-2">
                                <span class="font-semibold">Alasan penolakan:</span> {{ $report->reject_reason }}
                            </p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div>
            {{ $reports->links() }}
        </div>
    @endif
</div>
