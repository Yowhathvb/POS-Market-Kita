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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('invoice_number', 100)->unique();
            $table->foreignId('store_website_id')->nullable()->constrained('store_websites')->nullOnDelete();
            $table->foreignId('seller_user_id')->constrained('users')->onDelete('cascade');
            $table->string('buyer_email', 150);
            $table->string('buyer_google_sub', 100)->nullable();
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->enum('status', ['unpaid', 'paid', 'expired'])->default('unpaid');
            $table->json('value')->nullable(); // customer_name, customer_phone, catatan pembeli, kupon, ip
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
