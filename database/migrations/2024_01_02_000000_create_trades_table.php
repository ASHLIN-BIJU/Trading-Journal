<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('asset', 20);          // e.g. XAUUSD, EURUSD
            $table->enum('type', ['buy', 'sell']);
            $table->decimal('entry_price', 15, 5);
            $table->decimal('exit_price', 15, 5)->nullable();
            $table->decimal('stop_loss', 15, 5)->nullable();
            $table->decimal('take_profit', 15, 5)->nullable();
            $table->decimal('lot_size', 10, 4);
            $table->decimal('profit_loss', 15, 2)->nullable();      // $ P&L
            $table->decimal('profit_loss_pct', 10, 4)->nullable();  // % P&L
            $table->decimal('risk_amount', 15, 2)->nullable();      // $ risk
            $table->decimal('reward_amount', 15, 2)->nullable();    // $ potential reward
            $table->decimal('risk_reward', 10, 4)->nullable();      // R:R ratio
            $table->decimal('pips', 10, 2)->nullable();             // pips gained/lost
            $table->enum('result', ['win', 'loss', 'breakeven', 'open'])->default('open');
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->text('notes')->nullable();
            $table->timestamp('trade_date');
            $table->timestamp('closed_at')->nullable();
            $table->string('session', 20)->nullable();              // London/NY/Tokyo
            $table->string('timeframe', 10)->nullable();            // 1M, 5M, 1H, etc.
            $table->timestamps();

            $table->index(['user_id', 'trade_date']);
            $table->index(['user_id', 'result']);
            $table->index(['user_id', 'asset']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
