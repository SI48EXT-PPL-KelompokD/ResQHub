<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database in strict dependency order.
     */
    public function run(): void
    {
        $this->call([
            // 1. User & Otentikasi / Peran
            UserSeeder::class,

            // 2. Keahlian & Profil Relawan
            SkillSeeder::class,
            VolunteerProfileSeeder::class,

            // 3. Modul A: Bencana, Laporan, Peringatan Dini, & SOS
            DisasterEventSeeder::class,
            DisasterReportSeeder::class,
            EarlyWarningSeeder::class,
            SosRequestSeeder::class,

            // 4. Modul B: Posko Evakuasi, Fasilitas, & Titik Kumpul
            ShelterSeeder::class,
            ShelterFacilitySeeder::class,
            AssemblyPointSeeder::class,

            // 5. Modul C: Pengungsi & Layanan Medis
            RefugeeSeeder::class,
            HealthCheckSeeder::class,

            // 6. Modul D: Katalog Barang, Kebutuhan Posko, & Paket Bantuan
            ItemSeeder::class,
            AidNeedSeeder::class,
            AidPackageSeeder::class,

            // 7. Modul E: Rekening Resmi, Donasi Dana, & Janji Donasi Barang
            DonationAccountSeeder::class,
            FundDonationSeeder::class,
            GoodsDonationSeeder::class,

            // 8. Modul F: Penerimaan Donasi, Stok Logistik, Penyaluran, & Mutasi
            DonationReceiptSeeder::class,
            StockSeeder::class,
            DistributionSeeder::class,
            StockMovementSeeder::class,

            // 9. Modul G: Tugas Relawan, Penugasan, & Laporan Tugas
            VolunteerTaskSeeder::class,

            // 10. Audit Trail Polimorfik
            ActivityLogSeeder::class,
        ]);
    }
}
