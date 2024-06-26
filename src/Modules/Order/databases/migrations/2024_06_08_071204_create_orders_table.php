<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('merchant_name');
            $table->string('external_order_id');
            $table->string('delivery_service_name');
            $table->json('delivery_address');
            $table->string('sender_phone');
            $table->string('recipient_phone');
            $table->string('scheduled_delivery_time')->nullable();
            $table->string('status');
            $table->string('total_amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
