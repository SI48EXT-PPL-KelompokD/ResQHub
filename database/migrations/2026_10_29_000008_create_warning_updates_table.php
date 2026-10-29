<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warning_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('early_warning_id')->constrained('early_warnings')->cascadeOnDelete();
            $table->enum('old_level', ['waspada', 'siaga', 'awas']);
            $table->enum('new_level', ['waspada', 'siaga', 'awas']);
            $table->text('note')->nullable();
            $table->foreignId('updated_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warning_updates');
    }
};
