<?php

namespace App\Enums;

enum DeliveryMethod: string
{
    case Antar = 'antar';
    case Jemput = 'jemput';

    public function label(): string
    {
        return match ($this) {
            self::Antar => 'Antar sendiri',
            self::Jemput => 'Minta dijemput',
        };
    }
}
