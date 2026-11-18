<?php

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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->string('payment_gateway', 50)->default('midtrans');
            $table->string('payment_method', 50)->default('qris');
            $table->string('external_ref_id', 150)->nullable();
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->enum('status', ['pending', 'settlement', 'failed'])->default('pending');
            $table->json('value')->nullable(); // qr_string, qr_code_url, midtrans raw payload
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
