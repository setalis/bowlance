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

        // Проверяем, существует ли уже колонка delivery_type
        $columns = DB::select('PRAGMA table_info(orders)');
        $hasDeliveryType = false;
        foreach ($columns as $column) {
            if ($column->name === 'delivery_type') {
                $hasDeliveryType = true;
                break;
            }
        }

        if ($hasDeliveryType) {
            // Колонка уже существует, пропускаем миграцию
            return;
        }

        if ($driver === 'sqlite') {
            // Для SQLite используем ALTER TABLE (поддерживается с версии 3.25.0)
            DB::statement("ALTER TABLE orders ADD COLUMN delivery_type VARCHAR NOT NULL DEFAULT 'pickup' CHECK(delivery_type IN ('pickup', 'delivery'))");
        } else {
            // Для MySQL/MariaDB используем стандартный подход
            Schema::table('orders', function ($table) {
                $table->enum('delivery_type', ['pickup', 'delivery'])->default('pickup')->after('customer_phone');
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
            // Для SQLite нужно пересоздать таблицу без колонки delivery_type
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

            DB::statement('INSERT INTO orders_new (id, user_id, customer_name, customer_phone, customer_address, status, total, completed_at, created_at, updated_at) 
                SELECT id, user_id, customer_name, customer_phone, customer_address, status, total, completed_at, created_at, updated_at FROM orders');

            DB::statement('DROP TABLE orders');
            DB::statement('ALTER TABLE orders_new RENAME TO orders');
        } else {
            Schema::table('orders', function ($table) {
                $table->dropColumn('delivery_type');
            });
        }
    }
};
