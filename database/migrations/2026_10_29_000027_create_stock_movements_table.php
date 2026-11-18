<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained('stocks')->cascadeOnDelete();
            $table->enum('type', ['masuk', 'keluar', 'penyesuaian']);
            $table->integer('quantity');
            $table->string('reason', 255)->nullable();
            $table->foreignId('donation_receipt_id')->nullable()->constrained('donation_receipts')->nullOnDelete();
            $table->foreignId('distribution_id')->nullable()->constrained('distributions')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
