<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HealthCheckSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $checks = [
            [
                'id' => 1,
                'refugee_id' => 1, // Keluarga Ahmad Fauzi (Nenek)
                'nakes_id' => 4, // dr. Hendra
                'complaint' => 'Sesak napas berat, demam tinggi sejak kemarin, saturasi oksigen turun hingga 88%.',
                'temperature' => 38.9,
                'blood_pressure' => '150/95',
                'pulse' => 112,
                'triage' => 'merah',
                'action' => 'Diberikan oksigen nasal kanul 4 lpm, paracetamol infus, segera siapkan rujukan ambulans.',
                'checked_at' => $now->copy()->subHours(6),
                'delete_reason' => null,
                'deleted_at' => null,
                'created_at' => $now->copy()->subHours(6),
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'refugee_id' => 2, // Keluarga Sulaeman
                'nakes_id' => 5, // Ns. Dewi
                'complaint' => 'Ibu hamil usia kandungan 32 minggu mengeluh pusing dan kaki bengkak.',
                'temperature' => 36.8,
                'blood_pressure' => '130/85',
                'pulse' => 84,
                'triage' => 'kuning',
                'action' => 'Edukasi istirahat posisi miring kiri, cek DJJ normal, observasi tekanan darah per 4 jam.',
                'checked_at' => $now->copy()->subHours(12),
                'delete_reason' => null,
                'deleted_at' => null,
                'created_at' => $now->copy()->subHours(12),
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'refugee_id' => 1, // Balita Ahmad Fauzi
                'nakes_id' => 5, // Ns. Dewi
                'complaint' => 'Gatal-gatal di sela jari tangan dan kaki karena air banjir.',
                'temperature' => 36.5,
                'blood_pressure' => '100/65',
                'pulse' => 96,
                'triage' => 'hijau',
                'action' => 'Pembersihan luka dengan antiseptik, pemberian salep hidrokortison dan antihistamin sirup.',
                'checked_at' => $now->copy()->subHours(18),
                'delete_reason' => null,
                'deleted_at' => null,
                'created_at' => $now->copy()->subHours(18),
                'updated_at' => $now,
            ],
        ];

        DB::table('health_checks')->upsert($checks, ['id'], [
            'refugee_id', 'nakes_id', 'complaint', 'temperature', 'blood_pressure',
            'pulse', 'triage', 'action', 'checked_at', 'delete_reason', 'deleted_at', 'updated_at'
        ]);

        $referrals = [
            [
                'id' => 1,
                'health_check_id' => 1, // Dari checkup merah nenek Ahmad Fauzi
                'facility_name' => 'RSUD Al-Ihsan Baleendah',
                'reason' => 'Pneumonia berat suspek komplikasi efusi pleura disertai desaturasi oksigen',
                'patient_condition' => 'Pasien sadar tapi sesak berat, terpasang nasal kanul 4 lpm',
                'transport_needed' => true,
                'status' => 'dalam_perjalanan',
                'outcome' => null,
                'cancel_reason' => null,
                'created_by' => 4, // dr. Hendra
                'created_at' => $now->copy()->subHours(5),
                'updated_at' => $now->copy()->subHours(4),
            ],
        ];

        DB::table('medical_referrals')->upsert($referrals, ['id'], [
            'health_check_id', 'facility_name', 'reason', 'patient_condition', 'transport_needed',
            'status', 'outcome', 'cancel_reason', 'created_by', 'updated_at'
        ]);
    }
}
