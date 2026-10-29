<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disaster_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('disaster_event_id')->nullable()->constrained('disaster_events')->nullOnDelete();
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
            $table->string('title', 255);
            $table->text('description');
            $table->integer('estimated_affected')->nullable();
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->string('address', 255);
            $table->dateTime('occurred_at');
            $table->enum('status', ['menunggu', 'diterima', 'ditolak'])->default('menunggu');
            $table->text('reject_reason')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disaster_reports');
    }
};
