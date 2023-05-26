<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Redirect site config
    private array $tables = [
        'cms_content_blocks',
        'cms_editorsettings',
        'cms_group',
        'cms_menu',
        'cms_table',
        'cms_user',
        'cms_tableitem',
        'cms_template',
        'cms_templateitem',
        'cms_form',
        'cms_form_categories',
        'cms_form_data',
        'cms_form_fields',
        'cms_form_filetypes',
        'cms_form_properties',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }

            if (Schema::hasColumn($tableName, 'status')) {
                DB::table($tableName)->where('status', 'deleted')->update(['deleted_at' => now()]);
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('status');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->tables as $tableName) {
            DB::table($tableName)->where('deleted_at', '!=', null)->update(['status' => 'deleted']);

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropSoftDeletes();
                $table->set('status', ['DRAFT', 'PUBLISHED', 'DELETED'])->default('PUBLISHED');
            });
        }
    }
};
