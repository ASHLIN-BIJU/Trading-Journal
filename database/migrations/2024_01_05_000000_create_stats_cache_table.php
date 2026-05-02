<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stats_cache', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_pnl', 15, 2)->default(0);
            $table->decimal('win_rate', 5, 2)->default(0);
            $table->decimal('loss_rate', 5, 2)->default(0);
            $table->decimal('profit_factor', 10, 4)->default(0);
            $table->decimal('expectancy', 10, 4)->default(0);
            $table->decimal('avg_win', 15, 2)->default(0);
            $table->decimal('avg_loss', 15, 2)->default(0);
            $table->decimal('avg_rr', 10, 4)->default(0);
            $table->decimal('max_drawdown', 10, 4)->default(0);
            $table->decimal('max_drawdown_amount', 15, 2)->default(0);
            $table->integer('total_trades')->default(0);
            $table->integer('winning_trades')->default(0);
            $table->integer('losing_trades')->default(0);
            $table->integer('breakeven_trades')->default(0);
            $table->integer('max_win_streak')->default(0);
            $table->integer('max_loss_streak')->default(0);
            $table->timestamp('computed_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stats_cache');
    }
};
