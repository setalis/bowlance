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
        Schema::table('dishes', function (Blueprint $table) {
            $table->string('image')->nullable()->after('description');
            $table->string('weight_volume')->nullable()->after('image');
            $table->integer('calories')->nullable()->after('weight_volume');
            $table->decimal('proteins', 5, 2)->nullable()->after('calories');
            $table->decimal('fats', 5, 2)->nullable()->after('proteins');
            $table->decimal('carbohydrates', 5, 2)->nullable()->after('fats');
            $table->integer('sort_order')->default(0)->after('carbohydrates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            $table->dropColumn([
                'image',
                'weight_volume',
                'calories',
                'proteins',
                'fats',
                'carbohydrates',
                'sort_order',
            ]);
        });
    }
};
