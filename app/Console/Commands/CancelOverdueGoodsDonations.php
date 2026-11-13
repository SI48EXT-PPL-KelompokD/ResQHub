<?php

namespace App\Console\Commands;

use App\Enums\GoodsDonationStatus;
use App\Models\GoodsDonation;
use App\Services\GoodsDonationService;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

class CancelOverdueGoodsDonations extends Command
{
    protected $signature = 'goods-donations:cancel-overdue';

    protected $description = 'Batalkan otomatis janji donasi barang yang belum dikonfirmasi 3 hari setelah perkiraan tiba';

    public function handle(GoodsDonationService $service): int
    {
        $cutoff = now()->subDays(3)->toDateString();
        $cancelled = 0;

        GoodsDonation::query()
            ->where('status', GoodsDonationStatus::Dijanjikan->value)
            ->whereDate('est_arrival', '<=', $cutoff)
            ->chunkById(100, function ($donations) use ($service, &$cancelled) {
                foreach ($donations as $donation) {
                    try {
                        $service->cancel(
                            $donation,
                            null,
                            'Dibatalkan otomatis: tidak dikonfirmasi 3 hari setelah perkiraan tiba.',
                            auto: true,
                        );
                        $cancelled++;
                    } catch (ValidationException) {
                        // Status berubah saat command berjalan, lewati.
                    }
                }
            });

        $this->info("{$cancelled} janji donasi dibatalkan otomatis.");

        return self::SUCCESS;
    }
}
