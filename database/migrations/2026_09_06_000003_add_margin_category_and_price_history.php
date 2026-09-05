<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PATCH MIGRATION — Sesi 13
 * - products: kategori + margin % per item (untuk fitur margin/harga pokok
 *   khusus role owner).
 * - category_margins: setelan margin % default per kategori.
 * - product_price_histories: riwayat perubahan harga jual/beli + keterangan.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table): void {
                if (! Schema::hasColumn('products', 'category')) {
                    $table->string('category', 100)->nullable()->index();
                }

                if (! Schema::hasColumn('products', 'margin_percent')) {
                    $table->decimal('margin_percent', 5, 2)->nullable()->comment('Margin % khusus item ini, override margin per kategori');
                }
            });
        }

        if (! Schema::hasTable('category_margins')) {
            Schema::create('category_margins', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('category', 100)->unique();
                $table->decimal('margin_percent', 5, 2)->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('product_price_histories')) {
            Schema::create('product_price_histories', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('product_id')->index();
                $table->decimal('old_cost_price', 18, 2)->nullable();
                $table->decimal('new_cost_price', 18, 2)->nullable();
                $table->decimal('old_sell_price', 18, 2)->nullable();
                $table->decimal('new_sell_price', 18, 2)->nullable();
                $table->text('note')->nullable();
                $table->uuid('changed_by')->nullable();
                $table->timestamps();

                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                $table->foreign('changed_by')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_price_histories');
        Schema::dropIfExists('category_margins');

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table): void {
                if (Schema::hasColumn('products', 'margin_percent')) {
                    $table->dropColumn('margin_percent');
                }
                if (Schema::hasColumn('products', 'category')) {
                    $table->dropColumn('category');
                }
            });
        }
    }
};
