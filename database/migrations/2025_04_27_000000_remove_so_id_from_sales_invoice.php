<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveSoIdFromSalesInvoice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('SalesInvoice', function (Blueprint $table) {
            $table->dropForeign(['so_id']);
            $table->dropColumn('so_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('SalesInvoice', function (Blueprint $table) {
            $table->unsignedBigInteger('so_id')->nullable()->after('customer_id');
            $table->foreign('so_id')->references('so_id')->on('SalesOrder')->onDelete('set null');
        });
    }
}