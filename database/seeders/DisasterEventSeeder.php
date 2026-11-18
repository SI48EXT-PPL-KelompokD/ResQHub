<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DisasterEventSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $events = [
            [
                'id' => 1,
                'name' => 'Banjir Luapan Sungai Citarum Baleendah 2026',
                'disaster_type' => 'banjir',
                'severity' => 'berat',
                'status' => 'aktif',
                'regency' => 'Kabupaten Bandung',
                'lat' => -6.9942000,
                'lng' => 107.6255000,
                'started_at' => $now->copy()->subDays(3),
                'ended_at' => null,
                'created_at' => $now->copy()->subDays(3),
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'Tanah Longsor Cisarua Bandung Barat',
                'disaster_type' => 'tanah_longsor',
                'severity' => 'sedang',
                'status' => 'aktif',
                'regency' => 'Kabupaten Bandung Barat',
                'lat' => -6.8210000,
                'lng' => 107.5480000,
                'started_at' => $now->copy()->subDays(5),
                'ended_at' => null,
                'created_at' => $now->copy()->subDays(5),
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'name' => 'Gempa Bumi Darat Sesar Lembang M 4.8 (Simulasi)',
                'disaster_type' => 'gempa_bumi',
                'severity' => 'sedang',
                'status' => 'pemulihan',
                'regency' => 'Kabupaten Bandung Barat',
                'lat' => -6.8150000,
                'lng' => 107.6180000,
                'started_at' => $now->copy()->subDays(14),
                'ended_at' => $now->copy()->subDays(2),
                'created_at' => $now->copy()->subDays(14),
                'updated_at' => $now,
            ],
        ];

        DB::table('disaster_events')->upsert($events, ['id'], ['name', 'disaster_type', 'severity', 'status', 'regency', 'lat', 'lng', 'started_at', 'ended_at', 'updated_at']);
    }
}
