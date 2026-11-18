<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_donations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->foreignId('donor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('aid_need_id')->constrained('aid_needs')->cascadeOnDelete();
            $table->integer('quantity');
            $table->enum('delivery_method', ['antar', 'jemput']);
            $table->date('est_arrival');
            $table->enum('status', ['dijanjikan', 'diterima', 'disalurkan', 'dibatalkan'])->default('dijanjikan');
            $table->string('cancel_reason', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_donations');
    }
};
