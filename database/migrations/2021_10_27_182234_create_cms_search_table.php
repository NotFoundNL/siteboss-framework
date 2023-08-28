<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCmsSearchTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cms_search', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('language');
            $table->string('url', 128)->unique();
            $table->string('type', 64)->nullable();
            $table->integer('updated')->nullable();
            $table->integer('site_id')->nullable();
            $table->set('search_status', ['PENDING', 'UPDATED', 'SKIPPED', 'INSERTED', 'NOT_INDEXABLE', 'NOT_FOUND', 'FAILED'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('cms_search');
    }
}
