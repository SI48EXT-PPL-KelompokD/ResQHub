<?php

namespace App\Support;

use App\Models\User;

/**
 * Menyusun menu sidebar berdasarkan peran pengguna.
 * Menu bertanda `soon` tetap tampil tetapi nonaktif sampai fiturnya tersedia.
 */
class AppNavigation
{
    /**
     * @return list<array{title: string|null, items: list<array<string, mixed>>}>
     */
    public static function forUser(User $user): array
    {
        $dashboard = ['title' => null, 'items' => [self::link('Dashboard', 'admin.dashboard', 'home', ['admin.dashboard'])]];

        $sections = match ($user->role) {
            'admin' => self::admin(),
            'koordinator' => self::koordinator(),
            'nakes' => self::nakes(),
            'relawan' => self::relawan(),
            'donatur' => self::donatur(),
            default => self::warga(),
        };

        return [$dashboard, ...$sections];
    }

    private static function admin(): array
    {
        $pendingUsers = User::query()->whereIn('role', ['koordinator', 'nakes'])->where('is_active', false)->count();

        return [
            self::section('Tanggap Darurat', [
                self::soon('Verifikasi Laporan', 'check'),
                self::soon('SOS Masuk', 'alert'),
                self::soon('Peringatan Dini', 'bell'),
            ]),
            self::section('Posko dan Pengungsi', [
                self::link('Posko', 'admin.shelters', 'building', ['admin.shelters*']),
                self::link('Pengungsi', 'admin.refugees', 'users', ['admin.refugees*']),
                self::link('Pemeriksaan', 'admin.health-checks', 'heart', ['admin.health-checks*']),
                self::link('Tugas Relawan', 'admin.tasks', 'clipboard', ['admin.tasks']),
            ]),
            self::section('Logistik dan Donasi', [
                self::link('Katalog Barang', 'admin.items.index', 'box', ['admin.items.*']),
                self::link('Stok Logistik', 'admin.stocks.index', 'chart', ['admin.stocks.*', 'admin.aid-needs.*']),
                self::link('Rekening Donasi', 'admin.donation-accounts', 'card', ['admin.donation-accounts']),
                self::soon('Donasi Dana', 'wallet'),
            ]),
            self::section('Sistem', [
                self::link('Manajemen Pengguna', 'admin.users', 'user-cog', ['admin.users'], $pendingUsers),
            ]),
        ];
    }

    private static function koordinator(): array
    {
        return [
            self::section('Posko', [
                self::link('Posko Saya', 'admin.shelters', 'building', ['admin.shelters*']),
                self::soon('Titik Kumpul', 'pin'),
                self::link('Pengungsi', 'admin.refugees', 'users', ['admin.refugees*']),
                self::link('Pemeriksaan', 'admin.health-checks', 'heart', ['admin.health-checks*']),
                self::soon('Rujukan Medis', 'cross'),
            ]),
            self::section('Logistik', [
                self::link('Kebutuhan', 'admin.needs.index', 'box', ['admin.needs.*']),
                self::link('Stok Logistik', 'admin.stocks.index', 'chart', ['admin.stocks.*', 'admin.aid-needs.*']),
                self::soon('Paket Bantuan', 'gift'),
                self::soon('Penerimaan Donasi', 'inbox'),
                self::soon('Penyaluran', 'truck'),
            ]),
            self::section('Tanggap Darurat', [
                self::soon('SOS Masuk', 'alert'),
                self::link('Tugas Relawan', 'admin.tasks', 'clipboard', ['admin.tasks']),
            ]),
        ];
    }

    private static function nakes(): array
    {
        return [
            self::section('Layanan Kesehatan', [
                self::link('Pengungsi', 'admin.refugees', 'users', ['admin.refugees*']),
                self::link('Pemeriksaan', 'admin.health-checks', 'heart', ['admin.health-checks*']),
                self::soon('Rujukan Medis', 'cross'),
            ]),
            self::section('Penugasan', [
                self::soon('Tugas Saya', 'clipboard'),
                self::soon('Laporan Tugas', 'document'),
            ]),
        ];
    }

    private static function relawan(): array
    {
        return [
            self::section('Penugasan', [
                self::link('Tugas Saya', 'admin.tasks', 'clipboard', ['admin.tasks']),
                self::soon('Laporan Tugas', 'document'),
            ]),
            self::section('Informasi', [
                self::link('Peta Posko', 'public.shelters', 'map', ['public.shelters']),
            ]),
        ];
    }

    private static function warga(): array
    {
        return [
            self::section('Darurat', [
                self::link('Laporan Saya', 'admin.reports.index', 'document', ['admin.reports.*']),
                self::soon('Kirim SOS', 'alert'),
                self::soon('Peringatan Dini', 'bell'),
            ]),
            self::section('Informasi', [
                self::link('Peta Posko', 'public.shelters', 'map', ['public.shelters']),
                self::link('Kebutuhan Bantuan', 'needs.public', 'box', ['needs.public']),
            ]),
        ];
    }

    private static function donatur(): array
    {
        return [
            self::section('Donasi', [
                self::link('Donasi Barang Saya', 'donor.goods-donations.index', 'gift', ['donor.goods-donations.*']),
                self::soon('Donasi Dana', 'wallet'),
            ]),
            self::section('Informasi', [
                self::link('Kebutuhan Bantuan', 'needs.public', 'box', ['needs.public']),
                self::link('Peta Posko', 'public.shelters', 'map', ['public.shelters']),
            ]),
        ];
    }

    private static function section(string $title, array $items): array
    {
        return ['title' => $title, 'items' => $items];
    }

    private static function link(string $label, string $route, string $icon, array $active, int $badge = 0): array
    {
        return compact('label', 'route', 'icon', 'active', 'badge') + ['soon' => false];
    }

    private static function soon(string $label, string $icon): array
    {
        return ['label' => $label, 'route' => null, 'icon' => $icon, 'active' => [], 'badge' => 0, 'soon' => true];
    }
}
