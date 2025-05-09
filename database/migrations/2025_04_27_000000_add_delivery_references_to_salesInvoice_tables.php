<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryReferencesToSalesInvoiceTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Tambahkan kolom do_id ke tabel SalesInvoice
        Schema::table('SalesInvoice', function (Blueprint $table) {
            $table->unsignedBigInteger('do_id')->nullable()->after('so_id');
            $table->foreign('do_id')->references('delivery_id')->on('Delivery')->onDelete('set null');
        });
        
        // Tambahkan kolom do_line_id ke tabel SalesInvoiceLine
        Schema::table('SalesInvoiceLine', function (Blueprint $table) {
            $table->unsignedBigInteger('do_line_id')->nullable()->after('so_line_id');
            $table->foreign('do_line_id')->references('line_id')->on('DeliveryLine')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Hapus kolom do_id dari tabel SalesInvoice
        Schema::table('SalesInvoice', function (Blueprint $table) {
            $table->dropForeign(['do_id']);
            $table->dropColumn('do_id');
        });
        
        // Hapus kolom do_line_id dari tabel SalesInvoiceLine
        Schema::table('SalesInvoiceLine', function (Blueprint $table) {
            $table->dropForeign(['do_line_id']);
            $table->dropColumn('do_line_id');
        });
    }
}