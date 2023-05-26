<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCmsTemplateitemTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cms_templateitem', function (Blueprint $table) {
            $table->increments('id');
            $table->string('rights', 128)->nullable();
            $table->integer('template')->nullable();
            $table->string('type', 32)->nullable();
            $table->string('name', 128)->nullable();
            $table->string('internal', 32)->nullable();
            $table->string('description')->nullable();
            $table->json('properties')->nullable();
            $table->integer('global')->default('0');
            $table->integer('order')->nullable();
            $table->integer('enabled')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('cms_templateitem');
    }
}
