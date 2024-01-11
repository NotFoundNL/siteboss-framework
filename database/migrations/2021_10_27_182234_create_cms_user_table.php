<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCmsUserTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cms_user', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('mobile', 32)->nullable();
            $table->integer('last_attempt')->nullable()->default('0');
            $table->integer('failed_attempts')->nullable()->default('0');
            $table->integer('last_change')->nullable()->default('0');
            $table->integer('last_login')->nullable()->default('0');
            $table->json('properties')->nullable();
            $table->integer('enabled')->nullable();
            $table->set('status', ['DRAFT', 'PUBLISHED', 'DELETED'])->default('DRAFT');
            $table->string('sub')->nullable()->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('cms_user');
    }
}
