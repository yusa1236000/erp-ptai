<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTempUpdateTable extends Migration
{
    public function up()
    {
        Schema::create('userstemp_update', function (Blueprint $table) {
            $table->id(); // kolom 'id'
            $table->string('item_id'); // kolom 'name'
            $table->string('name'); // kolom 'name'
            $table->string('hscode'); // kolom 'email'
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('userstemp_update');
    }
}
