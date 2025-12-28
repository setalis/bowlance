<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('login_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('phone');
            $table->string('code', 255); // Храним хеш кода
            $table->string('login_token')->unique()->nullable(); // Токен для входа через Telegram
            $table->string('telegram_chat_id')->nullable();
            $table->timestamp('expires_at');
            $table->integer('attempts')->default(0);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'phone']);
            $table->index('login_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_verifications');
    }
};
