<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_table', function (Blueprint $table) {
            $table->boolean('allow_archive')->default(false)->after('allow_sort');
            $table->boolean('allow_duplicate')->default(false)->after('allow_sort');
        });
    }

    public function down(): void
    {
        Schema::table('cms_table', function (Blueprint $table) {
            $table->dropColumn('allow_duplicate');
            $table->dropColumn('allow_archive');
        });
    }
};
