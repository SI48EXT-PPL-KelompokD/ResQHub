<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shelter_id')->constrained('shelters')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->foreignId('aid_package_id')->nullable()->constrained('aid_packages')->nullOnDelete();
            $table->foreignId('refugee_id')->nullable()->constrained('refugees')->nullOnDelete();
            $table->string('recipient_note', 255)->nullable();
            $table->integer('quantity');
            $table->string('photo_path', 255)->nullable();
            $table->foreignId('distributed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('distributed_at');
            $table->string('cancel_reason', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributions');
    }
};
