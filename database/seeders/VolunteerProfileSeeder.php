<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VolunteerProfileSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $profiles = [
            [
                'id' => 1,
                'user_id' => 4, // dr. Hendra (Nakes)
                'domicile_lat' => -6.9932000,
                'domicile_lng' => 107.6251000,
                'radius_km' => 20,
                'availability' => 'siap',
                'str_number' => 'STR-3204-199408-0012',
                'certificate_path' => 'certificates/str_hendra.pdf',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'user_id' => 5, // Ns. Dewi (Nakes)
                'domicile_lat' => -6.9850000,
                'domicile_lng' => 107.6180000,
                'radius_km' => 15,
                'availability' => 'siap',
                'str_number' => 'STR-3204-199803-0045',
                'certificate_path' => 'certificates/str_dewi.pdf',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'user_id' => 6, // Rian Hidayat (Relawan SAR)
                'domicile_lat' => -6.9915000,
                'domicile_lng' => 107.6300000,
                'radius_km' => 25,
                'availability' => 'siap',
                'str_number' => null,
                'certificate_path' => 'certificates/sar_cert_rian.pdf',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'user_id' => 7, // Agus Kurniawan (Relawan Logistik)
                'domicile_lat' => -6.9380000,
                'domicile_lng' => 107.6100000,
                'radius_km' => 15,
                'availability' => 'siap',
                'str_number' => null,
                'certificate_path' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('volunteer_profiles')->upsert($profiles, ['id'], ['user_id', 'domicile_lat', 'domicile_lng', 'radius_km', 'availability', 'str_number', 'certificate_path', 'updated_at']);

        $volunteerSkills = [
            // dr. Hendra -> Medis & P3K, Dukungan Psikososial
            ['volunteer_profile_id' => 1, 'skill_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['volunteer_profile_id' => 1, 'skill_id' => 5, 'created_at' => $now, 'updated_at' => $now],
            // Ns. Dewi -> Medis & P3K, Pendataan Pengungsi
            ['volunteer_profile_id' => 2, 'skill_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['volunteer_profile_id' => 2, 'skill_id' => 6, 'created_at' => $now, 'updated_at' => $now],
            // Rian -> Evakuasi & SAR, Transportasi
            ['volunteer_profile_id' => 3, 'skill_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['volunteer_profile_id' => 3, 'skill_id' => 7, 'created_at' => $now, 'updated_at' => $now],
            // Agus -> Logistik, Dapur Umum
            ['volunteer_profile_id' => 4, 'skill_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['volunteer_profile_id' => 4, 'skill_id' => 2, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('volunteer_skill')->upsert($volunteerSkills, ['volunteer_profile_id', 'skill_id'], ['updated_at']);
    }
}
