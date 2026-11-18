<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StockMovementSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $movements = [
            // Mutasi masuk dari donasi barang air mineral (38 dus)
            [
                'id' => 1,
                'stock_id' => 3, // Air Mineral Posko 1
                'type' => 'masuk',
                'quantity' => 38,
                'reason' => 'Penerimaan donasi barang GDN-2610-0001 (kondisi baik)',
                'donation_receipt_id' => 1,
                'distribution_id' => null,
                'created_by' => 2,
                'created_at' => $now->copy()->subDays(1)->setHour(11),
                'updated_at' => $now->copy()->subDays(1)->setHour(11),
            ],
            // Mutasi masuk dari donasi barang selimut (50 lembar)
            [
                'id' => 2,
                'stock_id' => 5, // Selimut Posko 1
                'type' => 'masuk',
                'quantity' => 50,
                'reason' => 'Penerimaan donasi barang GDN-2610-0002',
                'donation_receipt_id' => 2,
                'distribution_id' => null,
                'created_by' => 2,
                'created_at' => $now->copy()->subDays(1)->setHour(15),
                'updated_at' => $now->copy()->subDays(1)->setHour(15),
            ],
            // Mutasi keluar penyaluran selimut (2 lembar)
            [
                'id' => 3,
                'stock_id' => 5,
                'type' => 'keluar',
                'quantity' => 2,
                'reason' => 'Penyaluran ke keluarga pengungsi Sulaeman (lansia/bumil)',
                'donation_receipt_id' => null,
                'distribution_id' => 2,
                'created_by' => 7,
                'created_at' => $now->copy()->subHours(6),
                'updated_at' => $now->copy()->subHours(6),
            ],
            // Mutasi keluar air mineral ke dapur umum (10 dus)
            [
                'id' => 4,
                'stock_id' => 3,
                'type' => 'keluar',
                'quantity' => 10,
                'reason' => 'Penyaluran ke Dapur Umum Posko Baleendah',
                'donation_receipt_id' => null,
                'distribution_id' => 3,
                'created_by' => 2,
                'created_at' => $now->copy()->subHours(4),
                'updated_at' => $now->copy()->subHours(4),
            ],
            // Mutasi penyesuaian (stok opname beras rusak lembab terkena tempias air)
            [
                'id' => 5,
                'stock_id' => 1, // Beras Posko 1
                'type' => 'penyesuaian',
                'quantity' => -10,
                'reason' => 'Penyesuaian stok opname: 10 kg beras lembab dan rusak',
                'donation_receipt_id' => null,
                'distribution_id' => null,
                'created_by' => 2,
                'created_at' => $now->copy()->subHours(2),
                'updated_at' => $now->copy()->subHours(2),
            ],
        ];

        DB::table('stock_movements')->upsert($movements, ['id'], [
            'stock_id', 'type', 'quantity', 'reason', 'donation_receipt_id',
            'distribution_id', 'created_by', 'updated_at'
        ]);
    }
}
