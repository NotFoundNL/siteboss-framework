<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Duplicated records point to the files of the record they were copied
     * from. Every record keeps its own row, so the file can be removed as soon
     * as the last record that uses it is purged.
     */
    public function up(): void
    {
        Schema::table('cms_uploads', function (Blueprint $table) {
            $table->unsignedBigInteger('source_upload_id')->nullable()->after('container_type')->index();
        });
    }

    public function down(): void
    {
        Schema::table('cms_uploads', function (Blueprint $table) {
            $table->dropColumn('source_upload_id');
        });
    }
};
