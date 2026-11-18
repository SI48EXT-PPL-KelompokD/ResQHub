<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ShelterSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $shelters = [
            [
                'id' => 1,
                'disaster_event_id' => 1, // Banjir Citarum
                'coordinator_id' => 2, // Budi Santoso
                'name' => 'Posko Utama GOR Baleendah',
                'address' => 'Jl. Adipati Agung No. 1, Baleendah, Kab. Bandung',
                'lat' => -6.9935000,
                'lng' => 107.6250000,
                'capacity' => 300,
                'occupancy' => 185,
                'contact' => '081234567802 (Pak Budi)',
                'status' => 'aktif',
                'close_reason' => null,
                'created_at' => $now->copy()->subDays(3),
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'disaster_event_id' => 1,
                'coordinator_id' => 3, // Siti Rahma
                'name' => 'Posko Evakuasi Balai Desa Dayeuhkolot',
                'address' => 'Jl. Raya Dayeuhkolot No. 120, Dayeuhkolot, Kab. Bandung',
                'lat' => -6.9880000,
                'lng' => 107.6190000,
                'capacity' => 150,
                'occupancy' => 142,
                'contact' => '081234567803 (Ibu Siti)',
                'status' => 'aktif',
                'close_reason' => null,
                'created_at' => $now->copy()->subDays(3),
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'disaster_event_id' => 2, // Longsor Cisarua
                'coordinator_id' => 2,
                'name' => 'Posko Darurat Kantor Kecamatan Cisarua',
                'address' => 'Jl. Kolonel Masturi No. 88, Cisarua, Kab. Bandung Barat',
                'lat' => -6.8205000,
                'lng' => 107.5490000,
                'capacity' => 100,
                'occupancy' => 45,
                'contact' => '081234567802 (Posko Cisarua)',
                'status' => 'aktif',
                'close_reason' => null,
                'created_at' => $now->copy()->subDays(5),
                'updated_at' => $now,
            ],
        ];

        DB::table('shelters')->upsert($shelters, ['id'], [
            'disaster_event_id', 'coordinator_id', 'name', 'address', 'lat', 'lng',
            'capacity', 'occupancy', 'contact', 'status', 'close_reason', 'updated_at'
        ]);
    }
}
