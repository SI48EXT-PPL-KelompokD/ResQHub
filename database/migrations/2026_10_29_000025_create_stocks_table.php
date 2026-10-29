<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shelter_id')->constrained('shelters')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->integer('min_threshold')->default(10);
            $table->timestamps();

            $table->unique(['shelter_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
