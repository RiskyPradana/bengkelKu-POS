<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sesi 14: data master dinamis (Merek, Satuan, Rak) + beberapa model harga
 * per item (Level Harga 1-4, mengikuti iPos 5) + voucher kasir + kolom
 * tambahan supplier untuk mendukung import dari iPos 5.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('units', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('abbreviation')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('racks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('branch_id')->nullable();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
        });

        Schema::create('product_price_levels', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('product_id');
            $table->unsignedTinyInteger('level_no');
            $table->string('level_name')->nullable();
            $table->decimal('price', 18, 2)->default(0);
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->unique(['product_id', 'level_no']);
        });

        Schema::create('vouchers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('type')->default('fixed'); // percent | fixed
            $table->decimal('value', 18, 2)->default(0);
            $table->decimal('min_purchase', 18, 2)->default(0);
            $table->decimal('max_discount', 18, 2)->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('used_count')->default(0);
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->uuid('brand_id')->nullable()->after('category');
            $table->uuid('unit_id')->nullable()->after('brand_id');
            $table->string('price_mode')->default('single')->after('unit_id'); // single | level

            $table->foreign('brand_id')->references('id')->on('brands')->nullOnDelete();
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
        });

        Schema::table('branch_stocks', function (Blueprint $table): void {
            $table->uuid('rack_id')->nullable()->after('rack_location');
            $table->foreign('rack_id')->references('id')->on('racks')->nullOnDelete();
        });

        Schema::table('suppliers', function (Blueprint $table): void {
            $table->string('external_code')->nullable()->after('name');
            $table->string('city')->nullable()->after('address');
            $table->string('province')->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table): void {
            $table->dropColumn(['external_code', 'city', 'province']);
        });

        Schema::table('branch_stocks', function (Blueprint $table): void {
            $table->dropForeign(['rack_id']);
            $table->dropColumn('rack_id');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropForeign(['brand_id']);
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['brand_id', 'unit_id', 'price_mode']);
        });

        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('product_price_levels');
        Schema::dropIfExists('racks');
        Schema::dropIfExists('units');
        Schema::dropIfExists('brands');
    }
};
