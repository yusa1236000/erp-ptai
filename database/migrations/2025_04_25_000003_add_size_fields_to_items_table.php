<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSizeFieldsToItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            // New fields
            $table->boolean('is_purchasable')->nullable()->after('document_path');
            $table->boolean('is_sellable')->nullable()->after('is_purchasable');
            $table->decimal('cost_price', 15, 2)->nullable()->after('is_sellable');
            $table->decimal('sale_price', 15, 2)->nullable()->after('cost_price');
            
            // Add size related fields
            $table->decimal('length', 15, 4)->nullable()->after('sale_price');
            $table->decimal('width', 15, 4)->nullable()->after('length');
            $table->decimal('thickness', 15, 4)->nullable()->after('width');
            $table->decimal('weight', 15, 4)->nullable()->after('thickness');

            // Document upload field
            $table->string('document_path')->nullable()->after('weight');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn([
                'is_purchasable',
                'is_sellable',
                'cost_price',
                'sale_price',
                'length',
                'width',
                'thickness',
                'weight',
                'document_path',
            ]);
        });
    }
}
