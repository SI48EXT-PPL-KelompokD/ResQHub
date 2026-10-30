<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DisasterReportSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $reports = [
            [
                'id' => 1,
                'reporter_id' => 10, // Ahmad Fauzi
                'disaster_event_id' => 1, // Banjir Citarum
                'disaster_type' => 'banjir',
                'title' => 'Banjir merendam permukiman RW 07 Andir setinggi 1,5 meter',
                'description' => 'Air sungai Citarum meluap sejak dini hari pukul 02.00 WIB. Saat ini arus deras dan jalan utama terputus, ada beberapa lansia terjebak.',
                'estimated_affected' => 120,
                'lat' => -6.9950000,
                'lng' => 107.6240000,
                'address' => 'Jl. Andir No. 45, Kel. Andir, Kec. Baleendah, Kab. Bandung',
                'occurred_at' => $now->copy()->subDays(3)->setHour(2)->setMinute(30),
                'status' => 'diterima',
                'reject_reason' => null,
                'verified_by' => 1, // Admin
                'verified_at' => $now->copy()->subDays(3)->setHour(6)->setMinute(0),
                'created_at' => $now->copy()->subDays(3)->setHour(3)->setMinute(0),
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'reporter_id' => 11, // Nurul Aini
                'disaster_event_id' => 1,
                'disaster_type' => 'banjir',
                'title' => 'Tanggul pembatas anak sungai jebol di Dayeuhkolot',
                'description' => 'Tanggul jebol mengakibatkan air masuk ke perumahan warga secara mendadak. Butuh perahu karet segera.',
                'estimated_affected' => 85,
                'lat' => -6.9890000,
                'lng' => 107.6200000,
                'address' => 'Kp. Bojong Asih RW 04, Dayeuhkolot, Kab. Bandung',
                'occurred_at' => $now->copy()->subDays(2)->setHour(8)->setMinute(0),
                'status' => 'diterima',
                'reject_reason' => null,
                'verified_by' => 1,
                'verified_at' => $now->copy()->subDays(2)->setHour(9)->setMinute(15),
                'created_at' => $now->copy()->subDays(2)->setHour(8)->setMinute(30),
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'reporter_id' => 10,
                'disaster_event_id' => null,
                'disaster_type' => 'cuaca_ekstrem',
                'title' => 'Pohon tumbang menutup jalan raya Banjaran',
                'description' => 'Hujan badai menyebabkan pohon beringin tua tumbang menimpa kabel listrik dan menutup total badan jalan.',
                'estimated_affected' => 10,
                'lat' => -7.0300000,
                'lng' => 107.5900000,
                'address' => 'Jl. Raya Banjaran KM 12, Kab. Bandung',
                'occurred_at' => $now->copy()->subHours(4),
                'status' => 'menunggu',
                'reject_reason' => null,
                'verified_by' => null,
                'verified_at' => null,
                'created_at' => $now->copy()->subHours(3),
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'reporter_id' => 11,
                'disaster_event_id' => null,
                'disaster_type' => 'kebakaran_hutan',
                'title' => 'Asap tebal di lahan kosong perbukitan Soreang',
                'description' => 'Terlihat kepulan asap, tetapi setelah dicek warga setempat itu pembakaran sampah kebun yang sudah padam.',
                'estimated_affected' => 0,
                'lat' => -7.0250000,
                'lng' => 107.5200000,
                'address' => 'Kawasan Bukit Soreang, Kab. Bandung',
                'occurred_at' => $now->copy()->subDays(1),
                'status' => 'ditolak',
                'reject_reason' => 'Bukan kejadian bencana kebakaran lahan, pembakaran sampah terkendali dan sudah dipadamkan oleh warga setempat.',
                'verified_by' => 1,
                'verified_at' => $now->copy()->subDays(1)->addHours(2),
                'created_at' => $now->copy()->subDays(1),
                'updated_at' => $now,
            ],
        ];

        DB::table('disaster_reports')->upsert($reports, ['id'], [
            'reporter_id', 'disaster_event_id', 'disaster_type', 'title', 'description', 'estimated_affected',
            'lat', 'lng', 'address', 'occurred_at', 'status', 'reject_reason', 'verified_by', 'verified_at', 'updated_at'
        ]);

        $photos = [
            ['id' => 1, 'disaster_report_id' => 1, 'path' => 'reports/banjir_andir_1.jpg', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'disaster_report_id' => 1, 'path' => 'reports/banjir_andir_2.jpg', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'disaster_report_id' => 2, 'path' => 'reports/tanggul_jebol.jpg', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'disaster_report_id' => 3, 'path' => 'reports/pohon_tumbang.jpg', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('report_photos')->upsert($photos, ['id'], ['disaster_report_id', 'path', 'updated_at']);
    }
}
