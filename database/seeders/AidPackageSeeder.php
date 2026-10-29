<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AidPackageSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $packages = [
            [
                'id' => 1,
                'name' => 'Paket Sembako Keluarga (3 Hari)',
                'target' => 'keluarga',
                'description' => 'Paket pangan pokok darurat untuk 1 kepala keluarga selama 3 hari pertama pasca bencana.',
                'is_active' => true,
                'created_by' => 2, // Koordinator Budi
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'Paket Nutrisi & Sanitasi Balita',
                'target' => 'balita',
                'description' => 'Paket khusus perlengkapan higienitas dan nutrisi bagi bayi di bawah usia 5 tahun.',
                'is_active' => true,
                'created_by' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'name' => 'Paket Hangat & Kesehatan Lansia',
                'target' => 'lansia',
                'description' => 'Paket selimut wol, sarung, biskuit protein, dan suplemen hangat untuk warga usia lanjut.',
                'is_active' => true,
                'created_by' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('aid_packages')->upsert($packages, ['id'], ['name', 'target', 'description', 'is_active', 'created_by', 'updated_at']);

        $packageItems = [
            // Paket 1 (Keluarga): Beras 5kg, Mie Instan 1 dus, Minyak Goreng 1 pouch, Air Mineral 1 dus
            ['aid_package_id' => 1, 'item_id' => 1, 'quantity' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['aid_package_id' => 1, 'item_id' => 2, 'quantity' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['aid_package_id' => 1, 'item_id' => 5, 'quantity' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['aid_package_id' => 1, 'item_id' => 3, 'quantity' => 1, 'created_at' => $now, 'updated_at' => $now],

            // Paket 2 (Balita): Popok 1 bal, Susu Formula 2 kotak, Minyak Telon 1 botol, Biskuit 1 kaleng
            ['aid_package_id' => 2, 'item_id' => 15, 'quantity' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['aid_package_id' => 2, 'item_id' => 16, 'quantity' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['aid_package_id' => 2, 'item_id' => 17, 'quantity' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['aid_package_id' => 2, 'item_id' => 4, 'quantity' => 1, 'created_at' => $now, 'updated_at' => $now],

            // Paket 3 (Lansia): Selimut 1 lembar, Sarung 1 lembar, Biskuit 2 kaleng, Air Mineral 1 dus
            ['aid_package_id' => 3, 'item_id' => 6, 'quantity' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['aid_package_id' => 3, 'item_id' => 8, 'quantity' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['aid_package_id' => 3, 'item_id' => 4, 'quantity' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['aid_package_id' => 3, 'item_id' => 3, 'quantity' => 1, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('aid_package_items')->upsert($packageItems, ['aid_package_id', 'item_id'], ['quantity', 'updated_at']);
    }
}
