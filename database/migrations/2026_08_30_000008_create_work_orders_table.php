<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('branch_id')->nullable();
            $table->uuid('customer_id');
            $table->uuid('vehicle_id');
            $table->uuid('assigned_mechanic_id')->nullable();
            $table->string('status')->default('Pending');
            $table->unsignedInteger('odometer')->nullable();
            $table->text('complaint')->nullable();
            $table->json('price_snapshot')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status']);
            $table->index(['customer_id', 'vehicle_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
