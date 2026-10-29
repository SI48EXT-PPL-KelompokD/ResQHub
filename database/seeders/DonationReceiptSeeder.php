<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DonationReceiptSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $receipts = [
            [
                'id' => 1,
                'goods_donation_id' => 1, // Air mineral (40 dus)
                'shelter_id' => 1, // Posko Baleendah
                'qty_good' => 38,
                'qty_damaged' => 2, // 2 dus kemasan penyok/bocor
                'received_by' => 2, // Koordinator Budi
                'received_at' => $now->copy()->subDays(1)->setHour(11),
                'cancel_reason' => null,
                'created_at' => $now->copy()->subDays(1)->setHour(11),
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'goods_donation_id' => 2, // Selimut (50 lembar)
                'shelter_id' => 1,
                'qty_good' => 50,
                'qty_damaged' => 0,
                'received_by' => 2,
                'received_at' => $now->copy()->subDays(1)->setHour(15),
                'cancel_reason' => null,
                'created_at' => $now->copy()->subDays(1)->setHour(15),
                'updated_at' => $now,
            ],
        ];

        DB::table('donation_receipts')->upsert($receipts, ['id'], [
            'goods_donation_id', 'shelter_id', 'qty_good', 'qty_damaged',
            'received_by', 'received_at', 'cancel_reason', 'updated_at'
        ]);
    }
}
