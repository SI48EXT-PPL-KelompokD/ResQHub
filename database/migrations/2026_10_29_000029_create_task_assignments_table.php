<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('volunteer_task_id')->constrained('volunteer_tasks')->cascadeOnDelete();
            $table->foreignId('volunteer_profile_id')->constrained('volunteer_profiles')->cascadeOnDelete();
            $table->enum('status', ['ditawarkan', 'diterima', 'ditolak', 'selesai'])->default('ditawarkan');
            $table->text('reject_reason')->nullable();
            $table->timestamps();

            $table->unique(['volunteer_task_id', 'volunteer_profile_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_assignments');
    }
};
