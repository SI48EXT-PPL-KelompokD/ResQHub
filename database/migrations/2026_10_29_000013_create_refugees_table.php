<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refugees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shelter_id')->constrained('shelters')->cascadeOnDelete();
            $table->string('head_name', 100);
            $table->string('nik', 16)->nullable();
            $table->integer('members')->default(1);
            $table->integer('infants')->default(0);
            $table->integer('elderly')->default(0);
            $table->integer('pregnant')->default(0);
            $table->integer('disabled')->default(0);
            $table->enum('status', ['di_posko', 'pindah', 'pulang'])->default('di_posko');
            $table->dateTime('checked_in_at');
            $table->dateTime('checked_out_at')->nullable();
            $table->string('delete_reason', 255)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refugees');
    }
};
