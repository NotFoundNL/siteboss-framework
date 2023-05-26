<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLangTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('lang', function (Blueprint $table) {
            $table->id();
            $table->string('url', 32)->unique()->index('url');
            $table->string('language', 50);
            $table->string('flag', 6)->nullable();
            $table->tinyInteger('default')->default('0');
            $table->tinyInteger('enabled')->default('1');
            $table->integer('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('lang');
    }
}
