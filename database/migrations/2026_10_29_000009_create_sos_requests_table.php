<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sos_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('disaster_event_id')->nullable()->constrained('disaster_events')->nullOnDelete();
            $table->enum('help_type', ['evakuasi', 'medis', 'pangan']);
            $table->integer('people_count')->default(1);
            $table->boolean('has_vulnerable')->default(false);
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->string('phone_number', 20)->nullable();
            $table->text('note')->nullable();
            $table->enum('status', ['baru', 'ditangani', 'selesai', 'dibatalkan'])->default('baru');
            $table->string('cancel_reason', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sos_requests');
    }
};
