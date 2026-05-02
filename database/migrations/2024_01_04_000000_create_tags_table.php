<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 50);
            $table->string('color', 10)->default('#00D4A8');
            $table->enum('category', ['strategy', 'setup', 'mistake', 'emotion', 'other'])->default('other');
            $table->timestamps();

            $table->unique(['user_id', 'name']);
        });

        Schema::create('trade_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trade_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->unique(['trade_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trade_tag');
        Schema::dropIfExists('tags');
    }
};
