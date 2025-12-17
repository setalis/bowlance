<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // Для SQLite нужно пересоздать таблицу с новым CHECK constraint
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

            DB::statement('INSERT INTO orders_new SELECT * FROM orders');

            DB::statement('DROP TABLE orders');
            DB::statement('ALTER TABLE orders_new RENAME TO orders');
        } else {
            // Для MySQL/MariaDB используем MODIFY COLUMN с ENUM
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('new', 'preparing', 'delivering', 'completed', 'pending_verification') DEFAULT 'new'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // Для SQLite нужно пересоздать таблицу со старым CHECK constraint
            DB::statement('CREATE TABLE orders_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                customer_name VARCHAR NOT NULL,
                customer_phone VARCHAR NOT NULL,
                customer_address TEXT,
                status VARCHAR NOT NULL DEFAULT "new" CHECK(status IN ("new", "preparing", "delivering", "completed")),
                total DECIMAL(10, 2) NOT NULL,
                completed_at DATETIME,
                created_at DATETIME,
                updated_at DATETIME
            )');

            DB::statement('INSERT INTO orders_new SELECT * FROM orders WHERE status != "pending_verification"');

            DB::statement('DROP TABLE orders');
            DB::statement('ALTER TABLE orders_new RENAME TO orders');
        } else {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('new', 'preparing', 'delivering', 'completed') DEFAULT 'new'");
        }
    }
};
