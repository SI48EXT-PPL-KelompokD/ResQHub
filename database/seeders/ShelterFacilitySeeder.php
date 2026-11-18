<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ShelterFacilitySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $facilities = [
            // Posko 1 (GOR Baleendah)
            ['shelter_id' => 1, 'type' => 'mck', 'units' => 8, 'capacity' => 150, 'condition' => 'baik', 'note' => '4 bilik pria, 4 bilik wanita', 'photo_path' => null, 'created_at' => $now, 'updated_at' => $now],
            ['shelter_id' => 1, 'type' => 'dapur_umum', 'units' => 1, 'capacity' => 500, 'condition' => 'baik', 'note' => 'Dikelola Tagana & relawan mandiri', 'photo_path' => null, 'created_at' => $now, 'updated_at' => $now],
            ['shelter_id' => 1, 'type' => 'pos_kesehatan', 'units' => 1, 'capacity' => 50, 'condition' => 'baik', 'note' => 'Tersedia tempat tidur observasi & obat esensial', 'photo_path' => null, 'created_at' => $now, 'updated_at' => $now],
            ['shelter_id' => 1, 'type' => 'listrik', 'units' => 2, 'capacity' => 300, 'condition' => 'baik', 'note' => 'Genset cadangan 10 KVA aktif', 'photo_path' => null, 'created_at' => $now, 'updated_at' => $now],
            ['shelter_id' => 1, 'type' => 'air_bersih', 'units' => 4, 'capacity' => 2000, 'condition' => 'baik', 'note' => 'Toren 1000L x 4 pasokan PDAM Tirta Raharja', 'photo_path' => null, 'created_at' => $now, 'updated_at' => $now],
            ['shelter_id' => 1, 'type' => 'laktasi', 'units' => 1, 'capacity' => 10, 'condition' => 'baik', 'note' => 'Tenda tertutup khusus ibu menyusui', 'photo_path' => null, 'created_at' => $now, 'updated_at' => $now],
            ['shelter_id' => 1, 'type' => 'ramah_anak', 'units' => 1, 'capacity' => 30, 'condition' => 'baik', 'note' => 'Area bermain dan trauma healing', 'photo_path' => null, 'created_at' => $now, 'updated_at' => $now],

            // Posko 2 (Balai Desa Dayeuhkolot)
            ['shelter_id' => 2, 'type' => 'mck', 'units' => 4, 'capacity' => 80, 'condition' => 'rusak_ringan', 'note' => '1 kran air macet, sedang dalam perbaikan', 'photo_path' => null, 'created_at' => $now, 'updated_at' => $now],
            ['shelter_id' => 2, 'type' => 'dapur_umum', 'units' => 1, 'capacity' => 250, 'condition' => 'baik', 'note' => 'Menggunakan dapur aula balai desa', 'photo_path' => null, 'created_at' => $now, 'updated_at' => $now],
            ['shelter_id' => 2, 'type' => 'pos_kesehatan', 'units' => 1, 'capacity' => 20, 'condition' => 'baik', 'note' => 'Didukung tenaga medis Puskesmas Dayeuhkolot', 'photo_path' => null, 'created_at' => $now, 'updated_at' => $now],
            ['shelter_id' => 2, 'type' => 'air_bersih', 'units' => 2, 'capacity' => 1000, 'condition' => 'baik', 'note' => 'Suplai tangki BPBD', 'photo_path' => null, 'created_at' => $now, 'updated_at' => $now],

            // Posko 3 (Kecamatan Cisarua)
            ['shelter_id' => 3, 'type' => 'mck', 'units' => 3, 'capacity' => 60, 'condition' => 'baik', 'note' => 'Toilet kantor kecamatan', 'photo_path' => null, 'created_at' => $now, 'updated_at' => $now],
            ['shelter_id' => 3, 'type' => 'dapur_umum', 'units' => 1, 'capacity' => 150, 'condition' => 'baik', 'note' => 'Dapur logistik kecamatan', 'photo_path' => null, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('shelter_facilities')->upsert($facilities, ['shelter_id', 'type'], [
            'units', 'capacity', 'condition', 'note', 'photo_path', 'updated_at'
        ]);
    }
}
