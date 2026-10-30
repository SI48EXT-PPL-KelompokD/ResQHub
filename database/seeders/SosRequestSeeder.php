<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SosRequestSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $sos = [
            [
                'id' => 1,
                'requester_id' => 10, // Ahmad Fauzi
                'disaster_event_id' => 1, // Banjir Citarum
                'help_type' => 'evakuasi',
                'people_count' => 4,
                'has_vulnerable' => true,
                'lat' => -6.9961000,
                'lng' => 107.6238000,
                'phone_number' => '081234567810',
                'note' => 'Terjebak di lantai 2 rumah bersama 1 nenek lumpuh dan 1 balita. Air sudah mencapai 1,8 meter.',
                'status' => 'ditangani',
                'cancel_reason' => null,
                'created_at' => $now->copy()->subHours(8),
                'updated_at' => $now->copy()->subHours(6),
            ],
            [
                'id' => 2,
                'requester_id' => 11, // Nurul Aini
                'disaster_event_id' => 1,
                'help_type' => 'medis',
                'people_count' => 1,
                'has_vulnerable' => true,
                'lat' => -6.9920000,
                'lng' => 107.6210000,
                'phone_number' => '081234567811',
                'note' => 'Kaki terkena seng atap yang roboh saat banjir, pendarahan terus dan butuh perban/antiseptik.',
                'status' => 'baru',
                'cancel_reason' => null,
                'created_at' => $now->copy()->subHours(1),
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'requester_id' => 10,
                'disaster_event_id' => 1,
                'help_type' => 'pangan',
                'people_count' => 6,
                'has_vulnerable' => false,
                'lat' => -6.9975000,
                'lng' => 107.6260000,
                'phone_number' => '081234567810',
                'note' => 'Warga bertahan di mushola RT 03, kehabisan air minum dan makanan sejak pagi.',
                'status' => 'selesai',
                'cancel_reason' => null,
                'created_at' => $now->copy()->subDays(2),
                'updated_at' => $now->copy()->subDays(1),
            ],
        ];

        DB::table('sos_requests')->upsert($sos, ['id'], [
            'requester_id', 'disaster_event_id', 'help_type', 'people_count', 'has_vulnerable',
            'lat', 'lng', 'phone_number', 'note', 'status', 'cancel_reason', 'updated_at'
        ]);
    }
}
