<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_return_id')->constrained()->restrictOnDelete();
            $table->decimal('amount');
            $table->foreignId('order_refund_status_id')->constrained('order_refund_statuses')->noActionOnDelete();
            $table->dateTime('processed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_refunds');
    }
};
