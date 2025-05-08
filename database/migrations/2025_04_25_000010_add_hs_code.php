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
        // Add payment_term to vendors table
        Schema::table('items', function (Blueprint $table) {
            $table->integer('hscode')->default(null)->after('sale_price_currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove payment_term from vendors table
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('hscode');
        });
    }
};