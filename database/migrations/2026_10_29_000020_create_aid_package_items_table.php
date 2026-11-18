<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aid_package_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aid_package_id')->constrained('aid_packages')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->timestamps();

            $table->unique(['aid_package_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aid_package_items');
    }
};
