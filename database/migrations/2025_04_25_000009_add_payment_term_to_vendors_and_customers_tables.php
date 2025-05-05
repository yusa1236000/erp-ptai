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
        Schema::table('vendors', function (Blueprint $table) {
            $table->integer('payment_term')->default(30)->after('preferred_currency');
        });

        // Add payment_term to customers table  
        Schema::table('Customer', function (Blueprint $table) {
            $table->integer('payment_term')->default(30)->after('preferred_currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove payment_term from vendors table
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('payment_term');
        });

        // Remove payment_term from customers table
        Schema::table('Customer', function (Blueprint $table) {
            $table->dropColumn('payment_term');
        });
    }
};