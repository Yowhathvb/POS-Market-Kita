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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('store_website_id')->nullable()->constrained('store_websites')->nullOnDelete();
            $table->string('name', 200);
            $table->string('slug', 200);
            $table->enum('product_type', ['digital', 'physical'])->default('digital');
            $table->decimal('price', 15, 2)->default(0);
            $table->integer('stock')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('value')->nullable(); // file_url, gambar, deskripsi
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
