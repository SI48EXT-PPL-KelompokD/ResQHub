<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disaster_events', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->enum('disaster_type', [
                'banjir',
                'tanah_longsor',
                'gempa_bumi',
                'angin_puting_beliung',
                'cuaca_ekstrem',
                'kebakaran_hutan',
                'tsunami',
                'gunung_meletus',
                'kekeringan',
                'lainnya',
            ]);
            $table->enum('severity', ['ringan', 'sedang', 'berat']);
            $table->enum('status', ['aktif', 'pemulihan', 'selesai'])->default('aktif');
            $table->string('regency', 100);
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disaster_events');
    }
};
