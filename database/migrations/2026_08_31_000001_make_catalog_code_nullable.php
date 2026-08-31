<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ubah kolom 'sku' di products dan 'code' di service_items menjadi nullable.
     * Menggunakan raw ALTER TABLE agar tidak mengganggu index unique yang sudah ada.
     */
    public function up(): void
    {
        // products.sku: NOT NULL -> NULL (index unique tetap ada)
        DB::statement('ALTER TABLE `products` MODIFY COLUMN `sku` VARCHAR(255) NULL');
        // service_items.code: NOT NULL -> NULL (index unique tetap ada)
        DB::statement('ALTER TABLE `service_items` MODIFY COLUMN `code` VARCHAR(255) NULL');
    }
    public function down(): void
    {
        DB::statement('ALTER TABLE `products` MODIFY COLUMN `sku` VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE `service_items` MODIFY COLUMN `code` VARCHAR(255) NOT NULL');
    }
};