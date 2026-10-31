<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RefugeeSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $refugees = [
            [
                'id' => 1,
                'shelter_id' => 1, // Posko Baleendah
                'head_name' => 'Ahmad Fauzi',
                'nik' => '3204111205800001',
                'members' => 4,
                'infants' => 1,
                'elderly' => 1,
                'pregnant' => 0,
                'disabled' => 1,
                'status' => 'di_posko',
                'checked_in_at' => $now->copy()->subDays(2)->setHour(10),
                'checked_out_at' => null,
                'delete_reason' => null,
                'deleted_at' => null,
                'created_at' => $now->copy()->subDays(2),
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'shelter_id' => 1,
                'head_name' => 'Sulaeman Darmawan',
                'nik' => '3204112507750003',
                'members' => 5,
                'infants' => 0,
                'elderly' => 2,
                'pregnant' => 1,
                'disabled' => 0,
                'status' => 'di_posko',
                'checked_in_at' => $now->copy()->subDays(3)->setHour(14),
                'checked_out_at' => null,
                'delete_reason' => null,
                'deleted_at' => null,
                'created_at' => $now->copy()->subDays(3),
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'shelter_id' => 1, // Pindah dari Balai Desa Dayeuhkolot (2) ke Posko Utama GOR Baleendah (1)
                'head_name' => 'Ujang Suherman',
                'nik' => '3204121010880005',
                'members' => 3,
                'infants' => 1,
                'elderly' => 0,
                'pregnant' => 0,
                'disabled' => 0,
                'status' => 'pindah',
                'checked_in_at' => $now->copy()->subDays(3)->setHour(8),
                'checked_out_at' => $now->copy()->subDays(1)->setHour(16),
                'delete_reason' => null,
                'deleted_at' => null,
                'created_at' => $now->copy()->subDays(3),
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'shelter_id' => 1,
                'head_name' => 'Endang Haryanto',
                'nik' => '3204110304920002',
                'members' => 2,
                'infants' => 0,
                'elderly' => 0,
                'pregnant' => 0,
                'disabled' => 0,
                'status' => 'pulang',
                'checked_in_at' => $now->copy()->subDays(2)->setHour(9),
                'checked_out_at' => $now->copy()->subHours(5),
                'delete_reason' => null,
                'deleted_at' => null,
                'created_at' => $now->copy()->subDays(2),
                'updated_at' => $now,
            ],
        ];

        DB::table('refugees')->upsert($refugees, ['id'], [
            'shelter_id', 'head_name', 'nik', 'members', 'infants', 'elderly', 'pregnant', 'disabled',
            'status', 'checked_in_at', 'checked_out_at', 'delete_reason', 'deleted_at', 'updated_at'
        ]);

        $movements = [
            [
                'id' => 1,
                'refugee_id' => 1,
                'from_shelter_id' => null,
                'to_shelter_id' => 1,
                'type' => 'masuk',
                'created_by' => 2, // Koordinator Budi
                'created_at' => $now->copy()->subDays(2)->setHour(10),
                'updated_at' => $now->copy()->subDays(2)->setHour(10),
            ],
            [
                'id' => 2,
                'refugee_id' => 2,
                'from_shelter_id' => null,
                'to_shelter_id' => 1,
                'type' => 'masuk',
                'created_by' => 2,
                'created_at' => $now->copy()->subDays(3)->setHour(14),
                'updated_at' => $now->copy()->subDays(3)->setHour(14),
            ],
            [
                'id' => 3,
                'refugee_id' => 3,
                'from_shelter_id' => 2,
                'to_shelter_id' => 1,
                'type' => 'pindah',
                'created_by' => 3, // Koordinator Siti
                'created_at' => $now->copy()->subDays(1)->setHour(16),
                'updated_at' => $now->copy()->subDays(1)->setHour(16),
            ],
            [
                'id' => 4,
                'refugee_id' => 4,
                'from_shelter_id' => 1,
                'to_shelter_id' => null,
                'type' => 'pulang',
                'created_by' => 2,
                'created_at' => $now->copy()->subHours(5),
                'updated_at' => $now->copy()->subHours(5),
            ],
        ];

        DB::table('refugee_movements')->upsert($movements, ['id'], [
            'refugee_id', 'from_shelter_id', 'to_shelter_id', 'type', 'created_by', 'updated_at'
        ]);
    }
}
