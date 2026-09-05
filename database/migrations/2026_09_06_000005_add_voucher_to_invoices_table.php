<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sesi 14: simpan voucher yang diterapkan pada sebuah invoice, supaya
 * riwayatnya jelas dan pemakaian voucher (used_count) tidak dobel dihitung.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->uuid('voucher_id')->nullable()->after('tax');
            $table->string('voucher_code')->nullable()->after('voucher_id');

            $table->foreign('voucher_id')->references('id')->on('vouchers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropForeign(['voucher_id']);
            $table->dropColumn(['voucher_id', 'voucher_code']);
        });
    }
};
