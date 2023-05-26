<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCmsRedirectTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cms_redirect', function (Blueprint $table) {
            $table->id();
            $table->string('url')->default('')->index('url');
            $table->string('redirect')->default('');
            $table->integer('site_id')->nullable()->default('0');
            $table->set('status', ['DRAFT', 'PUBLISHED', 'DELETED'])->default('PUBLISHED');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('cms_redirect');
    }
}
