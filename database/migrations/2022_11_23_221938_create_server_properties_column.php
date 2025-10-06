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
        Schema::table('cms_tableitem', function (Blueprint $table) {
            $table->json('server_properties')->after('properties')->nullable();
        });
        Schema::table('cms_templateitem', function (Blueprint $table) {
            $table->json('server_properties')->after('properties')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cms_tableitem', function (Blueprint $table) {
            $table->dropColumn('server_properties');
        });
        Schema::table('cms_templateitem', function (Blueprint $table) {
            $table->dropColumn('server_properties');
        });
    }
};
