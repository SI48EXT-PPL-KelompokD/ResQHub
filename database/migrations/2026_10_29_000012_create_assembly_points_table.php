<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assembly_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disaster_event_id')->constrained('disaster_events')->cascadeOnDelete();
            $table->foreignId('shelter_id')->nullable()->constrained('shelters')->nullOnDelete();
            $table->string('name', 150);
            $table->enum('place_type', ['lapangan', 'gedung', 'dataran_tinggi', 'lainnya']);
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->integer('capacity')->default(0);
            $table->enum('status', ['aktif', 'tidak_aman'])->default('aktif');
            $table->string('access_note', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assembly_points');
    }
};
