<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fund_donations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->foreignId('donor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('disaster_event_id')->constrained('disaster_events')->cascadeOnDelete();
            $table->foreignId('donation_account_id')->constrained('donation_accounts')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->boolean('is_anonymous')->default(false);
            $table->string('proof_path', 255)->nullable();
            $table->enum('status', [
                'menunggu_verifikasi',
                'terverifikasi',
                'ditolak',
                'dibatalkan',
            ])->default('menunggu_verifikasi');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fund_donations');
    }
};
