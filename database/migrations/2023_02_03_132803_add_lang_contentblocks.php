<?php

use NotFound\Framework\Models\Lang;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        if (Schema::hasColumn('cms_content_blocks', 'lang_id')) {
            return;
        }
        Schema::table('cms_content_blocks', function (Blueprint $table) {
            $table->foreignIdFor(Lang::class, 'lang_id')->after('target_record_id');
        });
        // TODO: This is a temporary solution. We need to add a default language from the database.
        DB::table('cms_content_blocks')->update(['lang_id' => 1]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cms_content_blocks', function (Blueprint $table) {
            $table->dropColumn('lang_id');
        });
    }
};
