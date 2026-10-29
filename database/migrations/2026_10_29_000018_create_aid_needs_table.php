<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aid_needs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shelter_id')->constrained('shelters')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->integer('qty_needed');
            $table->integer('qty_pledged')->default(0);
            $table->integer('qty_fulfilled')->default(0);
            $table->enum('priority', ['mendesak', 'tinggi', 'normal'])->default('normal');
            $table->date('deadline')->nullable();
            $table->enum('status', ['terbuka', 'terpenuhi', 'ditutup'])->default('terbuka');
            $table->string('close_reason', 255)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aid_needs');
    }
};
