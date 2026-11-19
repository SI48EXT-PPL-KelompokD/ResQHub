@php
    $statusClass = match ($warning->status) {
        \App\Models\EarlyWarning::STATUS_ACTIVE => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        \App\Models\EarlyWarning::STATUS_REVOKED => 'bg-rose-50 text-rose-700 border-rose-200',
        \App\Models\EarlyWarning::STATUS_EXPIRED => 'bg-slate-100 text-slate-600 border-slate-200',
        default => 'bg-blue-50 text-blue-700 border-blue-200',
    };
    $levelClass = match ($warning->level) {
        'awas' => 'bg-red-600 text-white border-red-600',
        'siaga' => 'bg-orange-500 text-white border-orange-500',
        default => 'bg-yellow-300 text-yellow-900 border-yellow-300',
    };
@endphp
<span class="text-[11px] font-semibold px-2 py-0.5 rounded-full border {{ $statusClass }}">{{ $warning->status_label }}</span>
<span class="text-[11px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full border {{ $levelClass }}">{{ $warning->level_label }}</span>
<span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">{{ $warning->hazard_type_label }}</span>
