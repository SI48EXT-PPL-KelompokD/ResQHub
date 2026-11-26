<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
            [
                'id' => 4,
                'disaster_event_id' => 1,
                'shelter_id' => 1,
                'sos_request_id' => null,
                'skill_id' => 7, // Transportasi & Distribusi
                'title' => 'Distribusi Logistik Posko Al-Ikhlas',
                'description' => 'Pengantaran paket bantuan sembako dan selimut ke posko pengungsian Al-Ikhlas.',
                'lat' => -6.9915000,
                'lng' => 107.6300000,
                'quota' => 2,
                'start_at' => $now->copy()->subDays(2),
                'end_at' => $now->copy()->subDays(2)->addHours(4),
                'status' => 'selesai',
                'cancel_reason' => null,
                'created_by' => 2,
                'created_at' => $now->copy()->subDays(3),
                'updated_at' => $now->copy()->subDays(2)->addHours(4),
            ],
            [
                'id' => 5,
                'disaster_event_id' => 1,
                'shelter_id' => 1,
                'sos_request_id' => null,
                'skill_id' => 2, // Dapur Umum
                'title' => 'Dapur Umum GOR Baleendah',
                'description' => 'Penyiapan dan pembagian makanan siap saji untuk pengungsi di posko GOR Baleendah.',
                'lat' => -6.9935000,
                'lng' => 107.6250000,
                'quota' => 4,
                'start_at' => $now->copy()->subHours(2),
                'end_at' => $now->copy()->addHours(4),
                'status' => 'berjalan',
                'cancel_reason' => null,
                'created_by' => 2,
                'created_at' => $now->copy()->subHours(3),
                'updated_at' => $now->copy()->subHours(2),
            ],
            [
                'id' => 6,
                'disaster_event_id' => 1,
                'shelter_id' => 1,
                'sos_request_id' => null,
                'skill_id' => 3, // Logistik & Pergudangan
                'title' => 'Pencatatan & Sortir Stok Gudang Posko Cipamokolan',
                'description' => 'Verifikasi fisik stok sembako, pakaian layak pakai, dan obat-obatan yang masuk hari ini, lalu perbarui kartu stok gudang.',
                'lat' => -6.9935000,
                'lng' => 107.6250000,
                'quota' => 3,
                'start_at' => $now->copy()->addHours(2),
                'end_at' => $now->copy()->addHours(6),
                'status' => 'terbuka',
                'cancel_reason' => null,
                'created_by' => 2,
                'created_at' => $now->copy()->subHour(),
                'updated_at' => $now->copy()->subHour(),
            ],
            [
                'id' => 7,
                'disaster_event_id' => 1,
                'shelter_id' => 1,
                'sos_request_id' => null,
                'skill_id' => 4, // Medis & P3K
                'title' => 'Pemeriksaan Kesehatan Lanjutan Pengungsi Lansia',
                'description' => 'Pemeriksaan tekanan darah, gula darah, dan pemberian obat rutin untuk pengungsi lansia di posko induk.',
                'lat' => -6.9932000,
                'lng' => 107.6251000,
                'quota' => 2,
                'start_at' => $now->copy()->addHours(4),
                'end_at' => $now->copy()->addHours(8),
                'status' => 'terbuka',
                'cancel_reason' => null,
                'created_by' => 2,
                'created_at' => $now->copy()->subMinutes(45),
                'updated_at' => $now->copy()->subMinutes(45),
            ],
            [
                'id' => 8,
                'disaster_event_id' => 1,
                'shelter_id' => 1,
                'sos_request_id' => null,
                'skill_id' => 2, // Dapur Umum & Pangan
                'title' => 'Dapur Umum Lapangan Cieunteung',
                'description' => 'Penyiapan 500 porsi makanan siap saji untuk pengungsi di titik evakuasi Lapangan Cieunteung.',
                'lat' => -6.9850000,
                'lng' => 107.6180000,
                'quota' => 4,
                'start_at' => $now->copy()->addDay(),
                'end_at' => $now->copy()->addDay()->addHours(5),
                'status' => 'terbuka',
                'cancel_reason' => null,
                'created_by' => 2,
                'created_at' => $now->copy()->subMinutes(30),
                'updated_at' => $now->copy()->subMinutes(30),
            ],
        ];

        DB::table('volunteer_tasks')->upsert($tasks, ['id'], [
            'disaster_event_id', 'shelter_id', 'sos_request_id', 'skill_id', 'title',
            'description', 'lat', 'lng', 'quota', 'start_at', 'end_at', 'status', 'cancel_reason',
            'created_by', 'updated_at',
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
            // Tugas 4 (Distribusi): Novendini Faradita (Volunteer Profile 5)
            [
                'id' => 4,
                'volunteer_task_id' => 4,
                'volunteer_profile_id' => 5,
                'status' => 'selesai',
                'reject_reason' => null,
                'created_at' => $now->copy()->subDays(2),
                'updated_at' => $now->copy()->subDays(2)->addHours(4),
            ],
            // Tugas 5 (Dapur Umum): Novendini Faradita (Volunteer Profile 5)
            [
                'id' => 5,
                'volunteer_task_id' => 5,
                'volunteer_profile_id' => 5,
                'status' => 'diterima',
                'reject_reason' => null,
                'created_at' => $now->copy()->subHours(2),
                'updated_at' => $now->copy()->subHours(2)->addMinutes(10),
            ],
            // Tugas 6 (Stok gudang, terbuka): Agus Kurniawan - penawaran menunggu konfirmasi
            [
                'id' => 6,
                'volunteer_task_id' => 6,
                'volunteer_profile_id' => 4,
                'status' => 'ditawarkan',
                'reject_reason' => null,
                'created_at' => $now->copy()->subHour(),
                'updated_at' => $now->copy()->subHour(),
            ],
            // Tugas 7 (Kesehatan lansia, terbuka): Novendini Faradita - penawaran menunggu konfirmasi
            [
                'id' => 7,
                'volunteer_task_id' => 7,
                'volunteer_profile_id' => 5,
                'status' => 'ditawarkan',
                'reject_reason' => null,
                'created_at' => $now->copy()->subMinutes(40),
                'updated_at' => $now->copy()->subMinutes(40),
            ],
            // Tugas 8 (Dapur umum, terbuka): Rian Hidayat - penawaran menunggu konfirmasi
            [
                'id' => 8,
                'volunteer_task_id' => 8,
                'volunteer_profile_id' => 3,
                'status' => 'ditawarkan',
                'reject_reason' => null,
                'created_at' => $now->copy()->subMinutes(25),
                'updated_at' => $now->copy()->subMinutes(25),
            ],
        ];

        // Penugasan demo untuk Novendini hanya berlaku bila profilnya ada (dibuat via aplikasi).
        $profileIds = DB::table('volunteer_profiles')->pluck('id')->all();
        $assignments = array_values(array_filter(
            $assignments,
            fn (array $assignment) => in_array($assignment['volunteer_profile_id'], $profileIds, true)
        ));

        DB::table('task_assignments')->upsert($assignments, ['id'], [
            'volunteer_task_id', 'volunteer_profile_id', 'status', 'reject_reason', 'updated_at',
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
            'task_assignment_id', 'type', 'lat', 'lng', 'note', 'photo_path', 'confirmed_at', 'updated_at',
        ]);
    }
}
