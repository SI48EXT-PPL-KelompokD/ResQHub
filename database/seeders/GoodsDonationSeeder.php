<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GoodsDonationSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $donations = [
            [
                'id' => 1,
                'code' => 'GDN-2610-0001',
                'donor_id' => 8, // Anisa Fitri
                'aid_need_id' => 1, // Air Mineral (Posko 1)
                'quantity' => 40,
                'delivery_method' => 'antar',
                'est_arrival' => $now->copy()->subDays(1)->toDateString(),
                'status' => 'diterima',
                'cancel_reason' => null,
                'created_at' => $now->copy()->subDays(2),
                'updated_at' => $now->copy()->subDays(1),
            ],
            [
                'id' => 2,
                'code' => 'GDN-2610-0002',
                'donor_id' => 9, // PT Harapan Bangsa
                'aid_need_id' => 2, // Selimut Wol (Posko 1)
                'quantity' => 50,
                'delivery_method' => 'antar',
                'est_arrival' => $now->copy()->subDays(1)->toDateString(),
                'status' => 'disalurkan',
                'cancel_reason' => null,
                'created_at' => $now->copy()->subDays(2),
                'updated_at' => $now->copy()->subDays(1),
            ],
            [
                'id' => 3,
                'code' => 'GDN-2610-0003',
                'donor_id' => 8,
                'aid_need_id' => 3, // Popok Bayi (Posko 1)
                'quantity' => 10,
                'delivery_method' => 'jemput',
                'est_arrival' => $now->copy()->addDays(1)->toDateString(),
                'status' => 'dijanjikan',
                'cancel_reason' => null,
                'created_at' => $now->copy()->subHours(5),
                'updated_at' => $now,
            ],
        ];

        DB::table('goods_donations')->upsert($donations, ['id'], [
            'code', 'donor_id', 'aid_need_id', 'quantity', 'delivery_method', 'est_arrival',
            'status', 'cancel_reason', 'updated_at'
        ]);
    }
}
