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
        $this->fix('cms_form');
        $this->fix('cms_form_categories');
        $this->fix('cms_form_fields');
        $this->fix('cms_form_properties');
        $this->fix('cms_form_data');

        Schema::table('cms_form', function (Blueprint $table) {
            $table->string('locales')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cms_form', function (Blueprint $table) {
            $table->dropColumn('locales');
        });
    }

    private function fix($tableName)
    {
        if (! Schema::hasColumn($tableName, 'created_at')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->timestamp('created_at')->nullable();
            });
        }
        if (! Schema::hasColumn($tableName, 'updated_at')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable();
            });
        }
        if (! Schema::hasColumn($tableName, 'deleted_at')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }
};
