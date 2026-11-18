<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('early_warnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disaster_event_id')->nullable()->constrained('disaster_events')->nullOnDelete();
            $table->foreignId('issued_by')->constrained('users')->cascadeOnDelete();
            $table->string('title', 255);
            $table->enum('hazard_type', [
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
            $table->enum('level', ['waspada', 'siaga', 'awas']);
            $table->json('target_regencies')->nullable();
            $table->decimal('center_lat', 10, 7)->nullable();
            $table->decimal('center_lng', 10, 7)->nullable();
            $table->integer('radius_km')->nullable();
            $table->text('instruction');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->enum('status', ['draf', 'aktif', 'kedaluwarsa', 'dicabut'])->default('draf');
            $table->string('revoke_reason', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('early_warnings');
    }
};
