<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCmsUploadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cms_upload', function (Blueprint $table) {
            $table->id();
            $table->integer('containerId')->nullable();
            $table->string('containerType', 256)->nullable();
            $table->string('filename')->nullable();
            $table->string('mimetype', 128)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cms_upload');
    }
}
