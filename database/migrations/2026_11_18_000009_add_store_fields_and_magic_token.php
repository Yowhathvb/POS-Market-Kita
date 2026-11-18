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
        Schema::table('users', function (Blueprint $table) {
            $table->string('id_toko', 50)->nullable()->unique()->after('id_users');
            $table->string('nama_toko', 150)->nullable()->after('id_toko');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('magic_token', 64)->nullable()->unique()->after('invoice_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['id_toko', 'nama_toko']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['magic_token']);
        });
    }
};
