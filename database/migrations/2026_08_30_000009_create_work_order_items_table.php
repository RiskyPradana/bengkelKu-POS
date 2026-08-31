<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('work_order_id');
            $table->string('item_type');
            $table->uuid('product_id')->nullable();
            $table->uuid('service_item_id')->nullable();
            $table->string('name');
            $table->unsignedInteger('qty')->default(1);
            $table->decimal('unit_price', 18, 2)->default(0);
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->json('snapshot')->nullable();
            $table->timestamps();

            $table->index(['work_order_id', 'item_type']);
            $table->index(['product_id', 'service_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_items');
    }
};
