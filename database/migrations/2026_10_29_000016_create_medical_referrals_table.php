<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('health_check_id')->constrained('health_checks')->cascadeOnDelete();
            $table->string('facility_name', 150);
            $table->text('reason');
            $table->text('patient_condition');
            $table->boolean('transport_needed')->default(false);
            $table->enum('status', [
                'diajukan',
                'dalam_perjalanan',
                'diterima_faskes',
                'selesai',
                'dibatalkan',
            ])->default('diajukan');
            $table->string('outcome', 255)->nullable();
            $table->string('cancel_reason', 255)->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_referrals');
    }
};
