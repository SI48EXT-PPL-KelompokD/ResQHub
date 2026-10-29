<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $logs = [
            [
                'id' => 1,
                'user_id' => 1, // Admin Pusdalops
                'subject_type' => 'App\\Models\\DisasterReport',
                'subject_id' => 1,
                'action' => 'verified_report',
                'changes' => json_encode([
                    'status_before' => 'menunggu',
                    'status_after' => 'diterima',
                    'disaster_event_id' => 1,
                ]),
                'created_at' => $now->copy()->subDays(3)->setHour(6),
                'updated_at' => $now->copy()->subDays(3)->setHour(6),
            ],
            [
                'id' => 2,
                'user_id' => 1,
                'subject_type' => 'App\\Models\\EarlyWarning',
                'subject_id' => 1,
                'action' => 'escalated_warning_level',
                'changes' => json_encode([
                    'old_level' => 'siaga',
                    'new_level' => 'awas',
                    'note' => 'Kenaikan TMA Sungai Citarum melebihi batas aman.',
                ]),
                'created_at' => $now->copy()->subDays(1),
                'updated_at' => $now->copy()->subDays(1),
            ],
            [
                'id' => 3,
                'user_id' => 1,
                'subject_type' => 'App\\Models\\FundDonation',
                'subject_id' => 1,
                'action' => 'verified_fund_donation',
                'changes' => json_encode([
                    'code' => 'FDN-2610-0001',
                    'amount' => 5000000,
                    'status' => 'terverifikasi',
                ]),
                'created_at' => $now->copy()->subDays(2)->addHours(1),
                'updated_at' => $now->copy()->subDays(2)->addHours(1),
            ],
            [
                'id' => 2,
                'user_id' => 2, // Koordinator Budi
                'subject_type' => 'App\\Models\\ShelterFacility',
                'subject_id' => 8,
                'action' => 'updated_facility_condition',
                'changes' => json_encode([
                    'type' => 'mck',
                    'old_condition' => 'baik',
                    'new_condition' => 'rusak_ringan',
                    'note' => 'Kran air macet',
                ]),
                'created_at' => $now->copy()->subHours(12),
                'updated_at' => $now->copy()->subHours(12),
            ],
        ];

        DB::table('activity_logs')->upsert($logs, ['id'], [
            'user_id', 'subject_type', 'subject_id', 'action', 'changes', 'updated_at'
        ]);
    }
}
