<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCmsFormCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cms_form_categories', function (Blueprint $table) {
            $table->id();
            $table->string('rights')->nullable();
            $table->string('name', 64)->unique();
            $table->string('slug', 64);
            $table->json('properties')->nullable();
            $table->set('status', ['DRAFT', 'PUBLISHED', 'DELETED'])->default('PUBLISHED');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('cms_form_categories');
    }
}
