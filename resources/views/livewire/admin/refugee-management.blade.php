<div>
    @if ($notification)
        <div role="alert">
            <p>{{ $notification['message'] }}</p>
            @if (!empty($notification['warnings']))
                <ul>
                    @foreach ($notification['warnings'] as $warning)
                        <li>{{ $warning }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    @if (!empty($formWarnings))
        <div>
            @foreach ($formWarnings as $w)
                <p>{{ $w['message'] }}</p>
            @endforeach
        </div>
    @endif

    <div>
        <h1>Manajemen Data Pengungsi</h1>
        @foreach ($refugees as $refugee)
            <div wire:key="refugee-{{ $refugee->id }}">
                <span>{{ $refugee->head_name }}</span>
                <span>{{ $refugee->masked_nik }}</span>
                <span>{{ $refugee->status_label }}</span>
                <span>{{ $refugee->shelter?->name }}</span>
            </div>
        @endforeach

        {{ $refugees->links() }}
    </div>

    @if ($showDetailModal && $selectedRefugee)
        <div>
            <h2>Detail Keluarga: {{ $selectedRefugee->head_name }}</h2>
            <p>NIK: {{ $selectedRefugee->masked_nik }}</p>
            <ul>
                @foreach ($selectedRefugee->movements as $movement)
                    <li>
                        {{ $movement->type_label }}
                        @if ($movement->fromShelter)
                            dari {{ $movement->fromShelter->name }}
                        @endif
                        @if ($movement->toShelter)
                            ke {{ $movement->toShelter->name }}
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>

