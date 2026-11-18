<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $password = Hash::make('password');

        $users = [
            [
                'id' => 1,
                'name' => 'Admin Pusdalops BPBD',
                'email' => 'admin@resqhub.id',
                'phone' => '081234567801',
                'password' => $password,
                'role' => 'admin',
                'regency' => 'Kabupaten Bandung',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'Budi Santoso (Koordinator Posko GOR)',
                'email' => 'koordinator.budi@resqhub.id',
                'phone' => '081234567802',
                'password' => $password,
                'role' => 'koordinator',
                'regency' => 'Kabupaten Bandung',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'name' => 'Siti Rahma (Koordinator Posko Balai Desa)',
                'email' => 'koordinator.siti@resqhub.id',
                'phone' => '081234567803',
                'password' => $password,
                'role' => 'koordinator',
                'regency' => 'Kabupaten Bandung',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'name' => 'dr. Hendra Pratama',
                'email' => 'nakes.hendra@resqhub.id',
                'phone' => '081234567804',
                'password' => $password,
                'role' => 'nakes',
                'regency' => 'Kabupaten Bandung',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'name' => 'Ns. Dewi Lestari, S.Kep',
                'email' => 'nakes.dewi@resqhub.id',
                'phone' => '081234567805',
                'password' => $password,
                'role' => 'nakes',
                'regency' => 'Kabupaten Bandung',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 6,
                'name' => 'Rian Hidayat (Relawan SAR)',
                'email' => 'relawan.rian@resqhub.id',
                'phone' => '081234567806',
                'password' => $password,
                'role' => 'relawan',
                'regency' => 'Kabupaten Bandung',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 7,
                'name' => 'Agus Kurniawan (Relawan Logistik)',
                'email' => 'relawan.agus@resqhub.id',
                'phone' => '081234567807',
                'password' => $password,
                'role' => 'relawan',
                'regency' => 'Kota Bandung',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 8,
                'name' => 'Anisa Fitri (Donatur Utama)',
                'email' => 'donatur.anisa@resqhub.id',
                'phone' => '081234567808',
                'password' => $password,
                'role' => 'donatur',
                'regency' => 'Kota Bandung',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 9,
                'name' => 'PT Harapan Bangsa Peduli',
                'email' => 'donatur.csr@resqhub.id',
                'phone' => '081234567809',
                'password' => $password,
                'role' => 'donatur',
                'regency' => 'Kota Bandung',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 10,
                'name' => 'Ahmad Fauzi (Warga Terdampak)',
                'email' => 'warga.ahmad@resqhub.id',
                'phone' => '081234567810',
                'password' => $password,
                'role' => 'warga',
                'regency' => 'Kabupaten Bandung',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 11,
                'name' => 'Nurul Aini (Warga Pelapor)',
                'email' => 'warga.nurul@resqhub.id',
                'phone' => '081234567811',
                'password' => $password,
                'role' => 'warga',
                'regency' => 'Kabupaten Bandung',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('users')->upsert($users, ['id'], ['name', 'email', 'phone', 'password', 'role', 'regency', 'is_active', 'updated_at']);
    }
}
