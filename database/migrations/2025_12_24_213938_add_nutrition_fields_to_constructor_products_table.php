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
        Schema::table('constructor_products', function (Blueprint $table) {
            $table->text('description')->nullable()->after('price');
            $table->string('weight_volume')->nullable()->after('description');
            $table->integer('calories')->nullable()->after('weight_volume');
            $table->decimal('proteins', 5, 2)->nullable()->after('calories');
            $table->decimal('fats', 5, 2)->nullable()->after('proteins');
            $table->decimal('carbohydrates', 5, 2)->nullable()->after('fats');
            $table->decimal('fiber', 5, 2)->nullable()->after('carbohydrates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('constructor_products', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'weight_volume',
                'calories',
                'proteins',
                'fats',
                'carbohydrates',
                'fiber',
            ]);
        });
    }
};
