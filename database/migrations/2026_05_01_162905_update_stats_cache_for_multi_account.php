<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stats_cache', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);
            $table->dropColumn('user_id');
            $table->foreignId('trading_account_id')->unique()->constrained('trading_accounts')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stats_cache', function (Blueprint $table) {
            $table->dropForeign(['trading_account_id']);
            $table->dropUnique(['trading_account_id']);
            $table->dropColumn('trading_account_id');
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
        });
    }
};
