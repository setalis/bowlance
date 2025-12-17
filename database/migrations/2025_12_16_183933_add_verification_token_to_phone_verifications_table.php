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
        Schema::table('phone_verifications', function (Blueprint $table) {
            $table->string('verification_token')->unique()->nullable()->after('order_id');
            $table->string('telegram_chat_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('phone_verifications', function (Blueprint $table) {
            $table->dropColumn('verification_token');
            $table->string('telegram_chat_id')->nullable(false)->change();
        });
    }
};
