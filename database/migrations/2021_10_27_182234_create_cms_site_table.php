<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCmsSiteTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cms_site', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('index')->nullable()->default(0);
            $table->string('name', 256)->nullable();
            $table->integer('root');
            $table->json('properties')->nullable();
            $table->tinyInteger('enabled')->default(0);
            $table->integer('position')->nullable(); // TODO
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('cms_site');
    }
}
