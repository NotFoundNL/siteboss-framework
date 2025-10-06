<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCmsTableTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cms_table', function (Blueprint $table) {
            $table->id();
            $table->string('rights', 128)->nullable();
            $table->string('name', 128)->nullable();
            $table->string('table', 128)->nullable();
            $table->string('url', 128)->nullable();
            $table->text('comments')->nullable();
            $table->boolean('allow_create')->default(true);
            $table->boolean('allow_delete')->default(true);
            $table->boolean('allow_sort')->default(true);
            $table->json('properties')->nullable();
            $table->integer('order')->nullable(); // TODO
            $table->tinyInteger('enabled')->default(1);
            $table->set('status', ['DRAFT', 'PUBLISHED', 'DELETED'])->default('PUBLISHED');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('cms_table');
    }
}
