<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Stok per produk per cabang
        Schema::create('branch_stocks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('branch_id');
            $table->uuid('product_id');
            $table->integer('quantity')->default(0);
            $table->integer('min_stock')->default(5);  // Low stock threshold
            $table->string('rack_location')->nullable(); // Lokasi rak
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->unique(['branch_id', 'product_id']); // Satu record per produk per cabang
        });

        // Riwayat mutasi stok
        Schema::create('stock_movements', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('branch_id');
            $table->uuid('product_id');
            $table->enum('type', ['in', 'out', 'transfer_in', 'transfer_out', 'adjustment']);
            $table->integer('quantity'); // Positif = masuk, negatif = keluar
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->string('reference')->nullable(); // WO number, invoice, dll
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });

        // Transfer antar cabang
        Schema::create('inter_branch_transfers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('transfer_number')->unique();
            $table->uuid('from_branch_id');
            $table->uuid('to_branch_id');
            $table->enum('status', ['pending', 'in_transit', 'received', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->uuid('requested_by')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();

            $table->foreign('from_branch_id')->references('id')->on('branches');
            $table->foreign('to_branch_id')->references('id')->on('branches');
        });

        // Item dalam transfer
        Schema::create('inter_branch_transfer_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('transfer_id');
            $table->uuid('product_id');
            $table->integer('quantity_requested');
            $table->integer('quantity_received')->nullable();
            $table->timestamps();

            $table->foreign('transfer_id')->references('id')->on('inter_branch_transfers')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inter_branch_transfer_items');
        Schema::dropIfExists('inter_branch_transfers');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('branch_stocks');
    }
};
