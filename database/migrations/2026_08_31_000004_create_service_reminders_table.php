<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Reminder servis otomatis via WhatsApp
        Schema::create('service_reminders', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('vehicle_id');
            $table->uuid('customer_id');
            $table->enum('trigger_type', ['interval_days', 'mileage'])->default('interval_days');
            $table->integer('interval_days')->nullable();   // Contoh: 90 hari
            $table->integer('mileage_interval')->nullable(); // Contoh: 5000 km
            $table->date('last_service_date')->nullable();
            $table->integer('last_service_mileage')->nullable();
            $table->date('next_reminder_date')->nullable();
            $table->integer('next_reminder_mileage')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });

        // Log pengiriman WhatsApp
        Schema::create('whatsapp_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('customer_id')->nullable();
            $table->string('phone_number');
            $table->enum('type', ['reminder', 'invoice', 'status_update', 'custom']);
            $table->text('message');
            $table->enum('status', ['queued', 'sent', 'delivered', 'failed'])->default('queued');
            $table->string('reference_id')->nullable(); // WO ID, Invoice ID, dll
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_logs');
        Schema::dropIfExists('service_reminders');
    }
};
