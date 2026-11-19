<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\RefugeeController;
use App\Http\Controllers\Admin\AidNeedController;
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
use App\Livewire\Report\VerificationQueue;
use App\Livewire\Report\VerifyReport;
use App\Livewire\Warning\ShowWarning;
use App\Livewire\Warning\WarningForm;
use App\Livewire\Warning\WarningList;
use App\Http\Controllers\Donor\GoodsDonationController;
use App\Livewire\Admin\DonationAccountManagement;
use App\Http\Controllers\Admin\ItemController;
use App\Livewire\Volunteer\TaskManager;
use App\Livewire\Admin\StockManagement;
use App\Livewire\Admin\AidNeedForm;

// Rute Publik (Warga & Pengunjung) - PBI-04 Pencarian Posko Terdekat (Haversine)
Route::get('/posko', ShelterSearch::class)->name('public.shelters');

// Beranda publik
Route::get('/', [HomeController::class, 'index'])->name('home');

// Autentikasi Tamu (Guest)
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', Register::class)->name('register');
    Route::get('/lupa-kata-sandi', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/lupa-kata-sandi', [PasswordResetController::class, 'store'])->middleware('throttle:6,1')->name('password.email');
    Route::get('/atur-ulang-kata-sandi/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/atur-ulang-kata-sandi', [PasswordResetController::class, 'update'])->middleware('throttle:6,1')->name('password.update');
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

    // Modul A - Verifikasi Laporan Kejadian (PBI-01) untuk Admin Pusdalops
    Route::middleware('role:admin')->group(function () {
        Route::get('/verifikasi-laporan', VerificationQueue::class)->name('reports.verification');
        Route::get('/verifikasi-laporan/{report}', VerifyReport::class)->name('reports.verify');
    });

    // Modul A - Peringatan Dini dan Instruksi Evakuasi (PBI-02) untuk Admin Pusdalops
    Route::middleware('role:admin')->group(function () {
        Route::get('/peringatan-dini', WarningList::class)->name('warnings.index');
        Route::get('/peringatan-dini/buat', WarningForm::class)->name('warnings.create');
        Route::get('/peringatan-dini/{warning}', ShowWarning::class)->name('warnings.show');
        Route::get('/peringatan-dini/{warning}/ubah', WarningForm::class)->name('warnings.edit');
    });

    //Donation Accounts
    Route::get('/donation-accounts', DonationAccountManagement::class)
        ->middleware('role:admin')
        ->name('donation-accounts');

    // Modul D: Katalog barang (PBI-10) dan kebutuhan posko (PBI-11)
    Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
    Route::get('/items', [ItemController::class, 'index'])->name('items.index');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::get('/items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
    Route::put('/items/{item}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');
    Route::patch('/items/{item}/activate', [ItemController::class, 'activate'])->name('items.activate');

    Route::middleware(['role:koordinator'])->group(function () {
        Route::resource('needs', AidNeedController::class)
            ->except(['show'])
            ->parameters(['needs' => 'aid_need']);
    });

    // Modul G: Tugas relawan (PBI-20)
    Route::get('/tasks', TaskManager::class)->name('tasks');

    // Modul F: Stok Logistik Posko (PBI-16)
    Route::middleware(['role:admin,koordinator'])->group(function () {
        Route::get('/stocks', StockManagement::class)->name('stocks.index');
        Route::get('/aid-needs/create', AidNeedForm::class)->name('aid-needs.create');
    });
});

// Donatur: janji donasi barang (PBI-13)
Route::middleware(['auth', 'role:donatur'])->prefix('donor')->name('donor.')->group(function () {
    Route::resource('goods-donations', GoodsDonationController::class)
        ->parameters(['goods-donations' => 'goodsDonation']);
});

// Papan kebutuhan publik (PBI-11)
Route::get('/needs', [AidNeedController::class, 'publicIndex'])->name('needs.public');
