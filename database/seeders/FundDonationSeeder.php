<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FundDonationSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $donations = [
            [
                'id' => 1,
                'code' => 'FDN-2610-0001',
                'donor_id' => 8, // Anisa Fitri
                'disaster_event_id' => 1,
                'donation_account_id' => 1, // BRI
                'amount' => 5000000.00,
                'is_anonymous' => false,
                'proof_path' => 'proofs/transfer_anisa_bri.jpg',
                'status' => 'terverifikasi',
                'verified_by' => 1, // Admin Pusdalops
                'note' => 'Semoga lekas surut dan para pengungsi diberikan ketabahan.',
                'created_at' => $now->copy()->subDays(2),
                'updated_at' => $now->copy()->subDays(2)->addHours(1),
            ],
            [
                'id' => 2,
                'code' => 'FDN-2610-0002',
                'donor_id' => 9, // PT Harapan Bangsa Peduli
                'disaster_event_id' => 1,
                'donation_account_id' => 2, // Mandiri
                'amount' => 25000000.00,
                'is_anonymous' => false,
                'proof_path' => 'proofs/transfer_csr_mandiri.pdf',
                'status' => 'terverifikasi',
                'verified_by' => 1,
                'note' => 'Program CSR Tanggap Darurat Bencana Banjir Citarum.',
                'created_at' => $now->copy()->subDays(1),
                'updated_at' => $now->copy()->subDays(1)->addHours(3),
            ],
            [
                'id' => 3,
                'code' => 'FDN-2610-0003',
                'donor_id' => 8,
                'disaster_event_id' => 1,
                'donation_account_id' => 1,
                'amount' => 1000000.00,
                'is_anonymous' => true,
                'proof_path' => 'proofs/transfer_anon_bri.jpg',
                'status' => 'menunggu_verifikasi',
                'verified_by' => null,
                'note' => 'Hamba Allah.',
                'created_at' => $now->copy()->subHours(2),
                'updated_at' => $now,
            ],
        ];

        DB::table('fund_donations')->upsert($donations, ['id'], [
            'code', 'donor_id', 'disaster_event_id', 'donation_account_id', 'amount',
            'is_anonymous', 'proof_path', 'status', 'verified_by', 'note', 'updated_at'
        ]);
    }
}
