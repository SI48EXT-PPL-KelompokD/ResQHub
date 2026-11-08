<?php

use App\Services\EarlyWarningService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// PBI-02: ubah peringatan aktif yang melewati masa berlaku menjadi kedaluwarsa
Artisan::command('warnings:expire', function (EarlyWarningService $service) {
    $this->info($service->expireOverdue().' peringatan ditandai kedaluwarsa.');
})->purpose('Tandai peringatan dini yang melewati masa berlaku sebagai kedaluwarsa');

Schedule::command('warnings:expire')->everyFiveMinutes();
