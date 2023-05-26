<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCmsSchedulerTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cms_scheduler', function (Blueprint $table) {
            $table->id();
            $table->string('internal', 32)->nullable();
            $table->string('name')->nullable();
            $table->integer('frequency')->nullable();
            $table->integer('trigger')->default('0');
            $table->tinyInteger('state')->nullable();
            $table->json('properties');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('cms_scheduler');
    }
}
