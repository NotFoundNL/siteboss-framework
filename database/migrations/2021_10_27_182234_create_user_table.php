<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('user', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable()->unique('email');
            $table->string('mobile', 32)->nullable();
            $table->integer('session_id')->nullable()->default('0');
            $table->string('secret')->default('');
            $table->string('password')->nullable()->default('');
            $table->json('properties')->nullable();
            $table->tinyInteger('enabled')->default(0);
            $table->set('status', ['DRAFT', 'PUBLISHED', 'DELETED'])->default('DRAFT');
            $table->string('key')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('user');
    }
}
