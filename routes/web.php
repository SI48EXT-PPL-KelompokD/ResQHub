<?php

use App\Http\Controllers\Auth\LoginController;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\UserManagement;
use App\Livewire\Auth\Register;
use App\Livewire\Volunteer\TaskManager;
use Illuminate\Support\Facades\Route;

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
    Route::get('/tasks', TaskManager::class)->name('tasks');
});
