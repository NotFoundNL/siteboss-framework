<?php

use App\Models\Table;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCmsTableitemTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cms_tableitem', function (Blueprint $table) {
            $table->id();
            $table->string('rights', 128)->default('');
            $table->foreignIdFor(Table::class, 'table_id')->nullable();
            $table->string('type', 64)->nullable();
            $table->string('internal', 64)->nullable();
            $table->string('name', 128)->nullable();
            $table->text('description')->nullable();
            $table->json('properties')->nullable();
            $table->integer('order')->nullable(); //TODO: FIX
            $table->tinyInteger('enabled')->nullable()->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('cms_tableitem');
    }
}
