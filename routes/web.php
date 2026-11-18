<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\RefugeeController;
use App\Livewire\Auth\Register;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\HealthCheckManagement;
use App\Livewire\Admin\RefugeeManagement;
use App\Livewire\Admin\UserManagement;
use App\Livewire\Admin\ShelterManagement;
use App\Livewire\Public\ShelterSearch;
use App\Livewire\Report\CreateReport;
use App\Livewire\Report\EditReport;
use App\Livewire\Report\MyReports;
use App\Livewire\Report\ShowReport;
use App\Http\Controllers\Donor\GoodsDonationController;
use App\Livewire\Admin\DonationAccountManagement;

// Rute Publik (Warga & Pengunjung) - PBI-04 Pencarian Posko Terdekat (Haversine)
Route::get('/posko', ShelterSearch::class)->name('public.shelters');

// Root redirect
Route::get('/', function () {
    return redirect()->route('login');
});

// Autentikasi Tamu (Guest)
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', Register::class)->name('register');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Group Terotentikasi
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/users', UserManagement::class)->middleware('role:admin')->name('users');
    Route::get('/shelters', ShelterManagement::class)->middleware('role:admin,koordinator')->name('shelters');

    // Modul C: Pengungsi (PBI-07)
    Route::get('/refugees', RefugeeManagement::class)->name('refugees');
    Route::get('/refugees/list', [RefugeeController::class, 'index'])->name('refugees.index');
    Route::get('/refugees/{refugee}', [RefugeeController::class, 'show'])->name('refugees.show');
    Route::post('/refugees', [RefugeeController::class, 'store'])->name('refugees.store');
    Route::put('/refugees/{refugee}', [RefugeeController::class, 'update'])->name('refugees.update');
    Route::post('/refugees/{refugee}/checkout', [RefugeeController::class, 'checkout'])->name('refugees.checkout');
    Route::post('/refugees/{refugee}/transfer', [RefugeeController::class, 'transfer'])->name('refugees.transfer');
    Route::delete('/refugees/{refugee}', [RefugeeController::class, 'destroy'])->name('refugees.destroy');

    // Modul C: Pemeriksaan & Triase Pengungsi (PBI-08)
    Route::get('/health-checks', HealthCheckManagement::class)->name('health-checks');
    Route::get('/health-checks/list', [HealthCheckController::class, 'index'])->name('health-checks.index');
    Route::get('/health-checks/summary', [HealthCheckController::class, 'summary'])->name('health-checks.summary');
    Route::get('/health-checks/refugee/{refugee}', [HealthCheckController::class, 'history'])
        ->whereNumber('refugee')
        ->name('health-checks.history');
    Route::get('/health-checks/{healthCheck}', [HealthCheckController::class, 'show'])->name('health-checks.show');
    Route::post('/health-checks', [HealthCheckController::class, 'store'])->name('health-checks.store');
    Route::put('/health-checks/{healthCheck}', [HealthCheckController::class, 'update'])->name('health-checks.update');
    Route::delete('/health-checks/{healthCheck}', [HealthCheckController::class, 'destroy'])->name('health-checks.destroy');

    // Modul A - Laporan Kejadian (PBI-01) untuk warga
    Route::middleware('role:warga')->group(function () {
        Route::get('/laporan', MyReports::class)->name('reports.index');
        Route::get('/laporan/buat', CreateReport::class)->name('reports.create');
        Route::get('/laporan/{report}', ShowReport::class)->name('reports.show');
        Route::get('/laporan/{report}/ubah', EditReport::class)->name('reports.edit');
    });

    //Donation Accounts
    Route::get('/donation-accounts', DonationAccountManagement::class)
        ->middleware('role:admin')
        ->name('donation-accounts');
});

// Donatur: janji donasi barang (PBI-13)
Route::middleware(['auth', 'role:donatur'])->prefix('donor')->name('donor.')->group(function () {
    Route::resource('goods-donations', GoodsDonationController::class)
        ->parameters(['goods-donations' => 'goodsDonation']);
});


