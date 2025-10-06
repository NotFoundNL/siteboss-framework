<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use NotFound\Framework\Models\Forms\Form;

class CreateCmsFormFieldsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cms_form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Form::class, 'form_id');
            $table->foreignId('parent_id')->nullable();
            $table->string('type', 20);
            $table->integer('trigger_field_id')->nullable();
            $table->text('trigger_value')->nullable();
            $table->json('properties')->nullable();
            $table->integer('order'); // TODO: FIX
            $table->set('status', ['DRAFT', 'PUBLISHED', 'DELETED'])->default('PUBLISHED');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('cms_form_fields');
    }
}
