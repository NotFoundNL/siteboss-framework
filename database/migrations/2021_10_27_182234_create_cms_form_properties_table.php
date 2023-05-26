<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCmsFormPropertiesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cms_form_properties', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('custom')->default(0);
            $table->string('name', 30);
            $table->string('type', 30)->unique();
            $table->json('options')->nullable();
            $table->foreignId('combinationId')->nullable(); //TODO: use combination_id
            $table->set('status', ['DRAFT', 'PUBLISHED', 'DELETED'])->default('PUBLISHED');
            $table->tinyInteger('has_value')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('cms_form_properties');
    }
}
