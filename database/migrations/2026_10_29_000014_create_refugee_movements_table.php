<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refugee_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('refugee_id')->constrained('refugees')->cascadeOnDelete();
            $table->foreignId('from_shelter_id')->nullable()->constrained('shelters')->nullOnDelete();
            $table->foreignId('to_shelter_id')->nullable()->constrained('shelters')->nullOnDelete();
            $table->enum('type', ['masuk', 'pindah', 'pulang']);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refugee_movements');
    }
};
