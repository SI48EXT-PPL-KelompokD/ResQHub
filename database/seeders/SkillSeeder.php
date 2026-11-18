<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $skills = [
            ['id' => 1, 'name' => 'Evakuasi & SAR', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Dapur Umum & Pangan', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'Logistik & Pergudangan', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'name' => 'Medis & P3K', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'name' => 'Dukungan Psikososial', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'name' => 'Pendataan Pengungsi', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'name' => 'Transportasi & Distribusi', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('skills')->upsert($skills, ['id'], ['name', 'updated_at']);
    }
}
