<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Events\ConnectionEstablished;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Daftarkan fungsi matematika trigonometri untuk koneksi SQLite (Rumus Haversine posko evakuasi)
        Event::listen(ConnectionEstablished::class, function (ConnectionEstablished $event) {
            if ($event->connection->getDriverName() === 'sqlite') {
                $pdo = $event->connection->getPdo();
                if (method_exists($pdo, 'sqliteCreateFunction')) {
                    $pdo->sqliteCreateFunction('acos', function ($val) {
                        return acos(max(-1.0, min(1.0, (float) $val)));
                    }, 1);
                    $pdo->sqliteCreateFunction('cos', 'cos', 1);
                    $pdo->sqliteCreateFunction('sin', 'sin', 1);
                    $pdo->sqliteCreateFunction('radians', 'deg2rad', 1);
                }
            }
        });
    }
}
