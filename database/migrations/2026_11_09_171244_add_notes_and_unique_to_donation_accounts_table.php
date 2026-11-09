<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donation_accounts', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('account_holder');
            $table->unique(
                ['disaster_event_id', 'account_number'],
                'donation_accounts_event_number_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('donation_accounts', function (Blueprint $table) {
            $table->dropUnique('donation_accounts_event_number_unique');
            $table->dropColumn('notes');
        });
    }
};