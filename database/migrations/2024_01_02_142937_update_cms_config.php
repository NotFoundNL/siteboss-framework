<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('cms_config', function (Blueprint $table) {
            $table->renameColumn('rights', 'editable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cms_config', function (Blueprint $table) {
            $table->renameColumn('editable', 'rights');
        });
    }
};
