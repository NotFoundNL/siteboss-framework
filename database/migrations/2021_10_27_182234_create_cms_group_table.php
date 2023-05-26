<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCmsGroupTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cms_group', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('parent')->nullable();
            $table->string('internal', 32)->unique();
            $table->string('name', 128);
            $table->json('properties')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('cms_group');
    }
}
