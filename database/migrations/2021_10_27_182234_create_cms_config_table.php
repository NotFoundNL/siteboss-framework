<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCmsConfigTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cms_config', function (Blueprint $table) {
            $table->id();
            $table->string('rights', 64)->nullable();
            $table->string('name', 155);
            $table->text('value')->nullable();
            $table->text('description')->nullable();
            $table->string('code', 155)->default('');
            $table->unsignedInteger('type')->default('1');
            $table->string('visible', 64)->default('');
            $table->integer('site_id')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('cms_config');
    }
}
