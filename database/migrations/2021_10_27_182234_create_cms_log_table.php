<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCmsLogTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cms_log', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date');
            $table->integer('user');
            $table->integer('actionid')->nullable();
            $table->string('action', 128)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('cms_log');
    }
}
