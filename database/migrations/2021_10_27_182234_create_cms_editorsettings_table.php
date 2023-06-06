<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCmsEditorsettingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cms_editorsettings', function (Blueprint $table) {
            $table->id();
            $table->string('name', 32)->nullable();
            $table->json('settings')->nullable();
            $table->tinyInteger('enabled')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('cms_editorsettings');
    }
}
