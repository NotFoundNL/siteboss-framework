<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cms_content_blocks', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('source_table_id');
            $table->foreignId('source_record_id');
            $table->foreignId('target_table_id');
            $table->foreignId('target_record_id');
            $table->integer('order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cms_content_blocks');
    }
};
