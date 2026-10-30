<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VolunteerTaskSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $tasks = [
            [
                'id' => 1,
                'disaster_event_id' => 1,
                'shelter_id' => 1,
                'sos_request_id' => 1, // Tindak lanjut SOS evakuasi nenek & balita
                'skill_id' => 1, // Evakuasi & SAR
                'title' => 'Evakuasi Perahu Karet Warga Terjebak RW 07 Andir',
                'description' => 'Evakuasi 4 jiwa termasuk 1 lansia lumpuh dan 1 balita dari lantai 2 rumah tergenang 1,8 meter.',
                'lat' => -6.9961000,
                'lng' => 107.6238000,
                'quota' => 2,
                'start_at' => $now->copy()->subHours(7),
                'end_at' => $now->copy()->subHours(3),
                'status' => 'berjalan',
                'cancel_reason' => null,
                'created_by' => 2, // Koordinator Budi
                'created_at' => $now->copy()->subHours(8),
                'updated_at' => $now->copy()->subHours(6),
            ],
            [
                'id' => 2,
                'disaster_event_id' => 1,
                'shelter_id' => 1,
                'sos_request_id' => null,
                'skill_id' => 3, // Logistik & Pergudangan
                'title' => 'Bongkar Muat & Sortir Donasi Logistik Posko Utama',
                'description' => 'Penerimaan truk bantuan sembako dan pakaian dari donatur, pencatatan stok fisik di gudang aula GOR.',
                'lat' => -6.9935000,
                'lng' => 107.6250000,
                'quota' => 3,
                'start_at' => $now->copy()->subHours(10),
                'end_at' => $now->copy()->subHours(5),
                'status' => 'selesai',
                'cancel_reason' => null,
                'created_by' => 2,
                'created_at' => $now->copy()->subHours(12),
                'updated_at' => $now->copy()->subHours(5),
            ],
            [
                'id' => 3,
                'disaster_event_id' => 1,
                'shelter_id' => 1,
                'sos_request_id' => null,
                'skill_id' => 4, // Medis & P3K
                'title' => 'Skrining Triase Pengungsi Kloter Baru Baleendah',
                'description' => 'Pemeriksaan kesehatan tanda vital dan pengelompokan triase (hijau/kuning/merah) untuk pengungsi yang baru tiba.',
                'lat' => -6.9935000,
                'lng' => 107.6250000,
                'quota' => 2,
                'start_at' => $now->copy()->subHours(4),
                'end_at' => $now->copy()->addHours(2),
                'status' => 'berjalan',
                'cancel_reason' => null,
                'created_by' => 2,
                'created_at' => $now->copy()->subHours(5),
                'updated_at' => $now->copy()->subHours(4),
            ],
        ];

        DB::table('volunteer_tasks')->upsert($tasks, ['id'], [
            'disaster_event_id', 'shelter_id', 'sos_request_id', 'skill_id', 'title',
            'description', 'lat', 'lng', 'quota', 'start_at', 'end_at', 'status', 'cancel_reason',
            'created_by', 'updated_at'
        ]);

        $assignments = [
            // Tugas 1 (Evakuasi): Rian Hidayat (Volunteer Profile 3)
            [
                'id' => 1,
                'volunteer_task_id' => 1,
                'volunteer_profile_id' => 3,
                'status' => 'diterima',
                'reject_reason' => null,
                'created_at' => $now->copy()->subHours(7),
                'updated_at' => $now->copy()->subHours(7)->addMinutes(15),
            ],
            // Tugas 2 (Logistik): Agus Kurniawan (Volunteer Profile 4)
            [
                'id' => 2,
                'volunteer_task_id' => 2,
                'volunteer_profile_id' => 4,
                'status' => 'selesai',
                'reject_reason' => null,
                'created_at' => $now->copy()->subHours(10),
                'updated_at' => $now->copy()->subHours(5),
            ],
            // Tugas 3 (Medis): Ns. Dewi (Volunteer Profile 2)
            [
                'id' => 3,
                'volunteer_task_id' => 3,
                'volunteer_profile_id' => 2,
                'status' => 'diterima',
                'reject_reason' => null,
                'created_at' => $now->copy()->subHours(4),
                'updated_at' => $now->copy()->subHours(4)->addMinutes(10),
            ],
        ];

        DB::table('task_assignments')->upsert($assignments, ['id'], [
            'volunteer_task_id', 'volunteer_profile_id', 'status', 'reject_reason', 'updated_at'
        ]);

        $reports = [
            // Laporan Rian checkin di lokasi evakuasi
            [
                'id' => 1,
                'task_assignment_id' => 1,
                'type' => 'checkin',
                'lat' => -6.9961000,
                'lng' => 107.6238000,
                'note' => 'Tiba di titik luncur perahu karet RW 07 Andir bersama tim SAR gabungan.',
                'photo_path' => 'tasks/checkin_rian_andir.jpg',
                'confirmed_at' => $now->copy()->subHours(6),
                'created_at' => $now->copy()->subHours(6),
                'updated_at' => $now->copy()->subHours(6),
            ],
            // Laporan Rian progres evakuasi
            [
                'id' => 2,
                'task_assignment_id' => 1,
                'type' => 'progres',
                'lat' => -6.9961500,
                'lng' => 107.6239000,
                'note' => 'Nenek dan balita berhasil dievakuasi ke perahu karet dengan selamat, sedang diarahkan menuju posko.',
                'photo_path' => 'tasks/progres_rian_perahu.jpg',
                'confirmed_at' => $now->copy()->subHours(4),
                'created_at' => $now->copy()->subHours(4),
                'updated_at' => $now->copy()->subHours(4),
            ],
            // Laporan Agus selesai tugas logistik
            [
                'id' => 3,
                'task_assignment_id' => 2,
                'type' => 'selesai',
                'lat' => -6.9935000,
                'lng' => 107.6250000,
                'note' => 'Seluruh paket bantuan dari truk berhasil diturunkan, dihitung, dan ditata di rak gudang.',
                'photo_path' => 'tasks/selesai_agus_gudang.jpg',
                'confirmed_at' => $now->copy()->subHours(5),
                'created_at' => $now->copy()->subHours(5),
                'updated_at' => $now->copy()->subHours(5),
            ],
        ];

        DB::table('task_reports')->upsert($reports, ['id'], [
            'task_assignment_id', 'type', 'lat', 'lng', 'note', 'photo_path', 'confirmed_at', 'updated_at'
        ]);
    }
}
