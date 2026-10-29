<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AidNeedSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $needs = [
            [
                'id' => 1,
                'shelter_id' => 1, // Posko Baleendah
                'item_id' => 3, // Air Mineral
                'qty_needed' => 100,
                'qty_pledged' => 40,
                'qty_fulfilled' => 30,
                'priority' => 'mendesak',
                'deadline' => $now->copy()->addDays(2)->toDateString(),
                'status' => 'terbuka',
                'close_reason' => null,
                'deleted_at' => null,
                'created_at' => $now->copy()->subDays(2),
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'shelter_id' => 1,
                'item_id' => 6, // Selimut Wol
                'qty_needed' => 80,
                'qty_pledged' => 50,
                'qty_fulfilled' => 50,
                'priority' => 'tinggi',
                'deadline' => $now->copy()->addDays(3)->toDateString(),
                'status' => 'terbuka',
                'close_reason' => null,
                'deleted_at' => null,
                'created_at' => $now->copy()->subDays(2),
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'shelter_id' => 1,
                'item_id' => 15, // Popok Bayi
                'qty_needed' => 30,
                'qty_pledged' => 10,
                'qty_fulfilled' => 0,
                'priority' => 'mendesak',
                'deadline' => $now->copy()->addDays(1)->toDateString(),
                'status' => 'terbuka',
                'close_reason' => null,
                'deleted_at' => null,
                'created_at' => $now->copy()->subDays(1),
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'shelter_id' => 2, // Posko Dayeuhkolot
                'item_id' => 1, // Beras
                'qty_needed' => 200,
                'qty_pledged' => 200,
                'qty_fulfilled' => 200,
                'priority' => 'tinggi',
                'deadline' => $now->copy()->subDays(1)->toDateString(),
                'status' => 'terpenuhi',
                'close_reason' => null,
                'deleted_at' => null,
                'created_at' => $now->copy()->subDays(3),
                'updated_at' => $now->copy()->subDays(1),
            ],
        ];

        DB::table('aid_needs')->upsert($needs, ['id'], [
            'shelter_id', 'item_id', 'qty_needed', 'qty_pledged', 'qty_fulfilled',
            'priority', 'deadline', 'status', 'close_reason', 'deleted_at', 'updated_at'
        ]);
    }
}
