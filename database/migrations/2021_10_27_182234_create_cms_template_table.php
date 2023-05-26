<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCmsTemplateTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cms_template', function (Blueprint $table) {
            $table->id();
            $table->string('rights', 128)->default('');
            $table->string('name');
            $table->string('desc')->nullable();
            $table->string('filename', 32)->nullable();
            $table->tinyInteger('list')->default(0);
            $table->string('allow_children')->nullable();
            $table->integer('attr')->nullable();
            $table->string('params', 128)->nullable();
            $table->json('properties')->nullable();
            $table->integer('order')->nullable();
            $table->tinyInteger('enabled')->default(1);
            $table->set('status', ['DRAFT', 'PUBLISHED', 'DELETED'])->default('PUBLISHED');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('cms_template');
    }
}
