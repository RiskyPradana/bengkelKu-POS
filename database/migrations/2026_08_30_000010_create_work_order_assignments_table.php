<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_assignments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('work_order_id');
            $table->uuid('mechanic_id');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();

            $table->index(['work_order_id', 'mechanic_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_assignments');
    }
};
