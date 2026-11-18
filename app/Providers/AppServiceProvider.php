<?php

namespace App\Providers;

use App\Models\DonationAccount;
use App\Policies\DonationAccountPolicy;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;
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
        // Seluruh pesan validasi dan format tanggal memakai Bahasa Indonesia.
        app()->setLocale('id');
        \Carbon\Carbon::setLocale('id');

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

        Gate::policy(DonationAccount::class, DonationAccountPolicy::class);

        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = route('password.reset', ['token' => $token, 'email' => $notifiable->getEmailForPasswordReset()]);

            return (new MailMessage)
                ->subject('Atur ulang kata sandi ResQHub')
                ->greeting('Halo, '.$notifiable->name)
                ->line('Kami menerima permintaan untuk mengatur ulang kata sandi akun ResQHub Anda.')
                ->action('Atur Ulang Kata Sandi', $url)
                ->line('Tautan ini berlaku selama 60 menit. Abaikan email ini jika Anda tidak memintanya.')
                ->salutation('Salam, Tim ResQHub');
        });
    }
}
