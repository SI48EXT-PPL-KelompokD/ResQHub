<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('volunteer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('domicile_lat', 10, 7)->nullable();
            $table->decimal('domicile_lng', 10, 7)->nullable();
            $table->integer('radius_km')->default(10);
            $table->enum('availability', ['siap', 'tidak_siap'])->default('siap');
            $table->string('str_number', 30)->nullable();
            $table->string('certificate_path', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('volunteer_profiles');
    }
};
