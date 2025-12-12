<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->string('tracking_number');
            $table->string('carrier');
            $table->foreignId('order_shipment_status_id')->constrained('order_shipment_statuses')->noActionOnDelete();
            $table->dateTime('shipped_at');
            $table->dateTime('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_shipments');
    }
};
