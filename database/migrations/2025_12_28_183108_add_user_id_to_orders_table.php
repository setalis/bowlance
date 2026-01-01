<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        // Проверяем, существует ли уже колонка user_id
        $columns = DB::select("PRAGMA table_info(orders)");
        $hasUserId = false;
        foreach ($columns as $column) {
            if ($column->name === 'user_id') {
                $hasUserId = true;
                break;
            }
        }

        if ($hasUserId) {
            // Колонка уже существует, пропускаем миграцию
            return;
        }

        if ($driver === 'sqlite') {
            // Для SQLite нужно пересоздать таблицу с новой колонкой user_id
            DB::statement('CREATE TABLE orders_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                customer_name VARCHAR NOT NULL,
                customer_phone VARCHAR NOT NULL,
                customer_address TEXT,
                status VARCHAR NOT NULL DEFAULT "new" CHECK(status IN ("new", "preparing", "delivering", "completed", "pending_verification")),
                total DECIMAL(10, 2) NOT NULL,
                completed_at DATETIME,
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
            )');

            DB::statement('INSERT INTO orders_new (id, customer_name, customer_phone, customer_address, status, total, completed_at, created_at, updated_at) 
                SELECT id, customer_name, customer_phone, customer_address, status, total, completed_at, created_at, updated_at FROM orders');

            DB::statement('DROP TABLE orders');
            DB::statement('ALTER TABLE orders_new RENAME TO orders');
        } else {
            // Для MySQL/MariaDB используем стандартный подход
            Schema::table('orders', function ($table) {
                $table->foreignId('user_id')->nullable()->after('id');
            });
            
            Schema::table('orders', function ($table) {
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // Для SQLite нужно пересоздать таблицу без колонки user_id
            DB::statement('CREATE TABLE orders_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                customer_name VARCHAR NOT NULL,
                customer_phone VARCHAR NOT NULL,
                customer_address TEXT,
                status VARCHAR NOT NULL DEFAULT "new" CHECK(status IN ("new", "preparing", "delivering", "completed", "pending_verification")),
                total DECIMAL(10, 2) NOT NULL,
                completed_at DATETIME,
                created_at DATETIME,
                updated_at DATETIME
            )');

            DB::statement('INSERT INTO orders_new (id, customer_name, customer_phone, customer_address, status, total, completed_at, created_at, updated_at) 
                SELECT id, customer_name, customer_phone, customer_address, status, total, completed_at, created_at, updated_at FROM orders');

            DB::statement('DROP TABLE orders');
            DB::statement('ALTER TABLE orders_new RENAME TO orders');
        } else {
            Schema::table('orders', function ($table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }
    }
};
