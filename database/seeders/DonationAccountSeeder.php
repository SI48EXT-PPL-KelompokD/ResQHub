<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DonationAccountSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $accounts = [
            [
                'id' => 1,
                'disaster_event_id' => 1, // Banjir Citarum
                'bank_name' => 'Bank Rakyat Indonesia (BRI)',
                'account_number' => '0123-01-001234-53-8',
                'account_holder' => 'BPBD Kab Bandung Peduli Bencana',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'disaster_event_id' => 1,
                'bank_name' => 'Bank Mandiri',
                'account_number' => '130-00-9876543-2',
                'account_holder' => 'BPBD Kab Bandung Tanggap Darurat',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'disaster_event_id' => 2, // Longsor Cisarua
                'bank_name' => 'Bank Central Asia (BCA)',
                'account_number' => '777-1234567',
                'account_holder' => 'Forum Relawan Siaga Bencana KBB',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('donation_accounts')->upsert($accounts, ['id'], [
            'disaster_event_id', 'bank_name', 'account_number', 'account_holder', 'is_active', 'updated_at'
        ]);
    }
}
