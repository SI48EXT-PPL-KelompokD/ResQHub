<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_donations', function (Blueprint $table) {
            $table->softDeletes();
            $table->index(['donor_id', 'status'], 'goods_donations_donor_status_index');
            $table->index(['aid_need_id', 'status'], 'goods_donations_need_status_index');
            $table->index(['status', 'est_arrival'], 'goods_donations_status_arrival_index');
        });
    }

    public function down(): void
    {
        Schema::table('goods_donations', function (Blueprint $table) {
            $table->dropIndex('goods_donations_donor_status_index');
            $table->dropIndex('goods_donations_need_status_index');
            $table->dropIndex('goods_donations_status_arrival_index');
            $table->dropSoftDeletes();
        });
    }
};