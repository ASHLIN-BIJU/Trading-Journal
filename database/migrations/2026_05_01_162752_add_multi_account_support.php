<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('active_account_id')->nullable()->constrained('trading_accounts')->nullOnDelete();
            $table->dropColumn('account_balance');
        });

        Schema::table('trades', function (Blueprint $table) {
            $table->foreignId('trading_account_id')->nullable()->constrained('trading_accounts')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->dropForeign(['trading_account_id']);
            $table->dropColumn('trading_account_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['active_account_id']);
            $table->dropColumn('active_account_id');
            $table->decimal('account_balance', 15, 2)->default(10000.00);
        });
    }
};
