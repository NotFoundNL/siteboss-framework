<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStringsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('strings', function (Blueprint $table) {
            $table->id();
            $table->string('table', 32)->default('');
            $table->string('name', 32)->default('');
            $table->unsignedTinyInteger('lang_id');
            $table->unsignedInteger('string_id');
            $table->text('value')->nullable();

            $table->unique(['string_id', 'lang_id', 'table', 'name'], 'string_id');
            $table->index(['table', 'string_id', 'name', 'lang_id'], 'table');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('strings');
    }
}
