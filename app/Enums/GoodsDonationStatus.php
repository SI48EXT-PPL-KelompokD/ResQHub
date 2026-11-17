<?php

namespace App\Enums;

enum GoodsDonationStatus: string
{
    case Dijanjikan = 'dijanjikan';
    case Diterima = 'diterima';
    case Disalurkan = 'disalurkan';
    case Dibatalkan = 'dibatalkan';

    public function label(): string
    {
        return match ($this) {
            self::Dijanjikan => 'Dijanjikan',
            self::Diterima => 'Diterima',
            self::Disalurkan => 'Disalurkan',
            self::Dibatalkan => 'Dibatalkan',
        };
    }
}
