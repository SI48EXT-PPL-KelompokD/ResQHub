<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DistributionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $distributions = [
            [
                'id' => 1,
                'shelter_id' => 1, // Posko Baleendah
                'item_id' => null,
                'aid_package_id' => 1, // Paket Sembako Keluarga
                'refugee_id' => 1, // Ahmad Fauzi
                'recipient_note' => 'Keluarga Ahmad Fauzi (4 jiwa)',
                'quantity' => 1,
                'photo_path' => 'distributions/dist_ahmad_sembako.jpg',
                'distributed_by' => 7, // Relawan Agus
                'distributed_at' => $now->copy()->subHours(8),
                'cancel_reason' => null,
                'created_at' => $now->copy()->subHours(8),
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'shelter_id' => 1,
                'item_id' => 6, // Selimut Wol
                'aid_package_id' => null,
                'refugee_id' => 2, // Sulaeman
                'recipient_note' => 'Keluarga Sulaeman (lansia & bumil)',
                'quantity' => 2,
                'photo_path' => 'distributions/dist_sulaeman_selimut.jpg',
                'distributed_by' => 7,
                'distributed_at' => $now->copy()->subHours(6),
                'cancel_reason' => null,
                'created_at' => $now->copy()->subHours(6),
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'shelter_id' => 1,
                'item_id' => 3, // Air Mineral
                'aid_package_id' => null,
                'refugee_id' => null,
                'recipient_note' => 'Distribusi ke Dapur Umum Posko Baleendah',
                'quantity' => 10,
                'photo_path' => null,
                'distributed_by' => 2, // Koordinator Budi
                'distributed_at' => $now->copy()->subHours(4),
                'cancel_reason' => null,
                'created_at' => $now->copy()->subHours(4),
                'updated_at' => $now,
            ],
        ];

        DB::table('distributions')->upsert($distributions, ['id'], [
            'shelter_id', 'item_id', 'aid_package_id', 'refugee_id', 'recipient_note',
            'quantity', 'photo_path', 'distributed_by', 'distributed_at', 'cancel_reason', 'updated_at'
        ]);
    }
}
