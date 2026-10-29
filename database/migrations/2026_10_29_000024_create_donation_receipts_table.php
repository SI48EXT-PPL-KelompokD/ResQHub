<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donation_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_donation_id')->constrained('goods_donations')->cascadeOnDelete();
            $table->foreignId('shelter_id')->constrained('shelters')->cascadeOnDelete();
            $table->integer('qty_good');
            $table->integer('qty_damaged')->default(0);
            $table->foreignId('received_by')->constrained('users')->cascadeOnDelete();
            $table->dateTime('received_at');
            $table->string('cancel_reason', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donation_receipts');
    }
};
