<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('menu', function (Blueprint $table) {
            $table->id();
            $table->string('rights', 128)->nullable()->default('');
            $table->string('cms_rights', 128)->default('');
            $table->integer('parent');
            $table->string('url', 33);
            $table->tinyInteger('type');
            $table->integer('template')->default('0');
            $table->integer('language')->default('2');
            $table->tinyInteger('enabled');
            $table->string('link')->nullable();
            $table->integer('attr');
            $table->timestamp('updated')->useCurrent();
            $table->tinyInteger('menu')->default(0);
            $table->json('properties')->nullable();
            $table->integer('order');
            $table->set('status', ['DRAFT', 'PUBLISHED', 'DELETED'])->default('PUBLISHED');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('menu');
    }
}
