<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCmsMenuTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cms_menu', function (Blueprint $table) {
            $table->id();
            $table->string('rights', 128)->default('');
            $table->string('icon', 32)->nullable();
            $table->string('title', 128)->nullable();
            $table->integer('level')->default(0);
            $table->string('target', 128)->nullable();
            $table->string('group', 32)->default('');
            $table->json('properties')->nullable();
            $table->integer('order')->nullable(); // TODO: FIX
            $table->tinyInteger('enabled')->default(1);
            $table->set('status', ['DRAFT', 'PUBLISHED', 'DELETED'])->default('DRAFT');
            $table->string('to')->default('');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('cms_menu');
    }
}
