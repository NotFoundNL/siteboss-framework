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
        Schema::table('cms_form_categories', function (Blueprint $table) {
            $table->boolean('enable_confirmation')->default(1)->after('rights')->nullable();
            $table->boolean('enable_export')->default(1)->after('rights')->nullable();
            $table->boolean('enable_notification')->default(1)->after('rights')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cms_form_categories', function (Blueprint $table) {
            $table->dropColumn('enable_confirmation');
            $table->dropColumn('enable_export');
            $table->dropColumn('enable_notification');
        });
    }
};
