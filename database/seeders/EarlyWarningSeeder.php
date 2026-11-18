<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EarlyWarningSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $warnings = [
            [
                'id' => 1,
                'disaster_event_id' => 1,
                'issued_by' => 1, // Admin
                'title' => 'PERINGATAN AWAS: Kenaikan TMA Sungai Citarum Hulu',
                'hazard_type' => 'banjir',
                'level' => 'awas',
                'target_regencies' => json_encode(['Kabupaten Bandung', 'Kota Bandung']),
                'center_lat' => -6.9942000,
                'center_lng' => 107.6255000,
                'radius_km' => 15,
                'instruction' => 'Warga bantaran Sungai Citarum (Baleendah, Dayeuhkolot, Bojongsoang) diimbau segera evakuasi mandiri ke titik kumpul atau posko evakuasi terdekat.',
                'starts_at' => $now->copy()->subDays(2),
                'ends_at' => $now->copy()->addDays(2),
                'status' => 'aktif',
                'revoke_reason' => null,
                'created_at' => $now->copy()->subDays(2),
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'disaster_event_id' => 2,
                'issued_by' => 1,
                'title' => 'PERINGATAN SIAGA: Potensi Longsor Susulan Lereng Cisarua',
                'hazard_type' => 'tanah_longsor',
                'level' => 'siaga',
                'target_regencies' => json_encode(['Kabupaten Bandung Barat']),
                'center_lat' => -6.8210000,
                'center_lng' => 107.5480000,
                'radius_km' => 5,
                'instruction' => 'Hindari beraktivitas di dekat tebing terjal saat hujan turun lebih dari 1 jam. Pantau rekahan tanah.',
                'starts_at' => $now->copy()->subDays(4),
                'ends_at' => $now->copy()->addDays(1),
                'status' => 'aktif',
                'revoke_reason' => null,
                'created_at' => $now->copy()->subDays(4),
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'disaster_event_id' => 1,
                'issued_by' => 1,
                'title' => 'PERINGATAN WASPADA: Hujan Lebat Disertai Angin Kencang',
                'hazard_type' => 'cuaca_ekstrem',
                'level' => 'waspada',
                'target_regencies' => json_encode(['Kabupaten Bandung']),
                'center_lat' => -6.9900000,
                'center_lng' => 107.6000000,
                'radius_km' => 25,
                'instruction' => 'Waspadai pohon tumbang dan genangan air di jalan raya.',
                'starts_at' => $now->copy()->subDays(10),
                'ends_at' => $now->copy()->subDays(8),
                'status' => 'kedaluwarsa',
                'revoke_reason' => null,
                'created_at' => $now->copy()->subDays(10),
                'updated_at' => $now->copy()->subDays(8),
            ],
        ];

        DB::table('early_warnings')->upsert($warnings, ['id'], [
            'disaster_event_id', 'issued_by', 'title', 'hazard_type', 'level', 'target_regencies',
            'center_lat', 'center_lng', 'radius_km', 'instruction', 'starts_at', 'ends_at', 'status',
            'revoke_reason', 'updated_at'
        ]);

        $updates = [
            [
                'id' => 1,
                'early_warning_id' => 1,
                'old_level' => 'siaga',
                'new_level' => 'awas',
                'note' => 'Tinggi Muka Air (TMA) di Pos Pantau Kamojang melebihi 650 cm. Status dinaikkan menjadi Awas.',
                'updated_by' => 1,
                'created_at' => $now->copy()->subDays(1),
                'updated_at' => $now->copy()->subDays(1),
            ],
        ];

        DB::table('warning_updates')->upsert($updates, ['id'], [
            'early_warning_id', 'old_level', 'new_level', 'note', 'updated_by', 'updated_at'
        ]);
    }
}
