<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shelter_facilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shelter_id')->constrained('shelters')->cascadeOnDelete();
            $table->enum('type', [
                'mck',
                'dapur_umum',
                'pos_kesehatan',
                'listrik',
                'air_bersih',
                'laktasi',
                'ramah_anak',
            ]);
            $table->integer('units')->default(1);
            $table->integer('capacity')->default(0);
            $table->enum('condition', ['baik', 'rusak_ringan', 'tidak_berfungsi'])->default('baik');
            $table->string('note', 255)->nullable();
            $table->string('photo_path', 255)->nullable();
            $table->timestamps();

            $table->unique(['shelter_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shelter_facilities');
    }
};
