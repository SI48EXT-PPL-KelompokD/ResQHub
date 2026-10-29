<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->enum('category', [
                'pangan',
                'sandang',
                'kebersihan',
                'obat_obatan',
                'perlengkapan_bayi',
                'peralatan',
            ]);
            $table->string('unit', 20);
            $table->integer('min_threshold')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['name', 'unit']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
