<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $items = [
            // Pangan
            ['id' => 1, 'name' => 'Beras Medium Premium', 'category' => 'pangan', 'unit' => 'kg', 'min_threshold' => 100, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Mie Instan Kaldu Ayam', 'category' => 'pangan', 'unit' => 'dus', 'min_threshold' => 20, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'Air Mineral Botol 600ml', 'category' => 'pangan', 'unit' => 'dus', 'min_threshold' => 30, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'name' => 'Biskuit Protein Siap Santap', 'category' => 'pangan', 'unit' => 'kaleng', 'min_threshold' => 25, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'name' => 'Minyak Goreng Sawit 1L', 'category' => 'pangan', 'unit' => 'pouch', 'min_threshold' => 30, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],

            // Sandang
            ['id' => 6, 'name' => 'Selimut Wol Tebal Dewasa', 'category' => 'sandang', 'unit' => 'lembar', 'min_threshold' => 40, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'name' => 'Pakaian Dalam Higienis Dewasa', 'category' => 'sandang', 'unit' => 'lusin', 'min_threshold' => 15, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 8, 'name' => 'Sarung Tenun Dewasa', 'category' => 'sandang', 'unit' => 'lembar', 'min_threshold' => 30, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],

            // Kebersihan
            ['id' => 9, 'name' => 'Sabun Mandi Antiseptik Batang', 'category' => 'kebersihan', 'unit' => 'pcs', 'min_threshold' => 50, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 10, 'name' => 'Pasta Gigi & Sikat Gigi Pack', 'category' => 'kebersihan', 'unit' => 'pack', 'min_threshold' => 40, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 11, 'name' => 'Pembalut Wanita Higienis', 'category' => 'kebersihan', 'unit' => 'pack', 'min_threshold' => 35, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],

            // Obat-obatan
            ['id' => 12, 'name' => 'Paracetamol Tablet 500mg', 'category' => 'obat_obatan', 'unit' => 'strip', 'min_threshold' => 50, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 13, 'name' => 'Kasa Steril & Plester Gulung', 'category' => 'obat_obatan', 'unit' => 'box', 'min_threshold' => 20, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 14, 'name' => 'Oralit Bubuk Sachet', 'category' => 'obat_obatan', 'unit' => 'box', 'min_threshold' => 25, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],

            // Perlengkapan Bayi
            ['id' => 15, 'name' => 'Popok Bayi Ukuran M', 'category' => 'perlengkapan_bayi', 'unit' => 'bal', 'min_threshold' => 20, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 16, 'name' => 'Susu Formula Bayi 6-12 Bulan 400g', 'category' => 'perlengkapan_bayi', 'unit' => 'kotak', 'min_threshold' => 30, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 17, 'name' => 'Minyak Telon Bayi 60ml', 'category' => 'perlengkapan_bayi', 'unit' => 'botol', 'min_threshold' => 25, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],

            // Peralatan
            ['id' => 18, 'name' => 'Terpal Plastik Biru 4x6 Meter', 'category' => 'peralatan', 'unit' => 'lembar', 'min_threshold' => 15, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 19, 'name' => 'Senter LED Rechargeable Waterproof', 'category' => 'peralatan', 'unit' => 'unit', 'min_threshold' => 10, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 20, 'name' => 'Matras Alas Tidur Lipat', 'category' => 'peralatan', 'unit' => 'lembar', 'min_threshold' => 50, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('items')->upsert($items, ['id'], ['name', 'category', 'unit', 'min_threshold', 'is_active', 'updated_at']);
    }
}
