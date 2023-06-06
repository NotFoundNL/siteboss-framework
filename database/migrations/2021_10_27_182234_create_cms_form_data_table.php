<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use NotFound\Framework\Models\Forms\Form;

class CreateCmsFormDataTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cms_form_data', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Form::class, 'form_id');
            $table->json('data');
            $table->ipaddress('ip_address');
            $table->timestamp('timestamp')->useCurrent();
            $table->foreignId('user_id')->nullable();
            $table->set('status', ['DRAFT', 'PUBLISHED', 'DELETED'])->default('PUBLISHED');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('cms_form_data');
    }
}
