<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('refugee_id')->constrained('refugees')->cascadeOnDelete();
            $table->foreignId('nakes_id')->constrained('users')->cascadeOnDelete();
            $table->text('complaint');
            $table->decimal('temperature', 4, 1);
            $table->string('blood_pressure', 10);
            $table->integer('pulse');
            $table->enum('triage', ['hijau', 'kuning', 'merah']);
            $table->text('action')->nullable();
            $table->dateTime('checked_at');
            $table->string('delete_reason', 255)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_checks');
    }
};
