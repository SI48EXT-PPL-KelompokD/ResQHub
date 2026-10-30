<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('volunteer_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disaster_event_id')->constrained('disaster_events')->cascadeOnDelete();
            $table->foreignId('shelter_id')->nullable()->constrained('shelters')->nullOnDelete();
            $table->foreignId('sos_request_id')->nullable()->constrained('sos_requests')->nullOnDelete();
            $table->foreignId('skill_id')->constrained('skills')->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('description');
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->integer('quota')->default(1);
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->enum('status', ['terbuka', 'berjalan', 'selesai', 'dibatalkan'])->default('terbuka');
            $table->string('cancel_reason', 255)->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('volunteer_tasks');
    }
};
