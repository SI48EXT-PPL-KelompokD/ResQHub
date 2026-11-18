<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AssemblyPointSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $points = [
            [
                'id' => 1,
                'disaster_event_id' => 1,
                'shelter_id' => 1,
                'name' => 'Lapangan Parkir Masjid Al-Ikhlas Baleendah',
                'place_type' => 'lapangan',
                'lat' => -6.9928000,
                'lng' => 107.6245000,
                'capacity' => 200,
                'status' => 'aktif',
                'access_note' => 'Dapat diakses jalan darat via Jl. Kulalet, bebas genangan air.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'disaster_event_id' => 1,
                'shelter_id' => 2,
                'name' => 'Halaman SDN Dayeuhkolot 01',
                'place_type' => 'lapangan',
                'lat' => -6.9875000,
                'lng' => 107.6185000,
                'capacity' => 150,
                'status' => 'aktif',
                'access_note' => 'Akses hanya bisa perahu karet jika jalan raya tergenang > 1 meter.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'disaster_event_id' => 2,
                'shelter_id' => 3,
                'name' => 'Dataran Terbuka Lapangan Desa Karyawangi',
                'place_type' => 'dataran_tinggi',
                'lat' => -6.8190000,
                'lng' => 107.5470000,
                'capacity' => 100,
                'status' => 'aktif',
                'access_note' => 'Jauh dari lereng curam, aman dari reruntuhan tebing.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'disaster_event_id' => 1,
                'shelter_id' => null,
                'name' => 'Jembatan Lama Dayeuhkolot (Tidak Aman)',
                'place_type' => 'gedung',
                'lat' => -6.9855000,
                'lng' => 107.6205000,
                'capacity' => 50,
                'status' => 'tidak_aman',
                'access_note' => 'Arus sungai sangat deras di bawah jembatan, struktur rentan retak.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('assembly_points')->upsert($points, ['id'], [
            'disaster_event_id', 'shelter_id', 'name', 'place_type', 'lat', 'lng',
            'capacity', 'status', 'access_note', 'updated_at'
        ]);
    }
}
