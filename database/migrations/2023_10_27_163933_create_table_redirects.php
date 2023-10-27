<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cms_redirects', function (Blueprint $table) {
            $table->id();
            $table->string('url')->unique();
            $table->string('redirect');
            $table->boolean('recursive')->default(false);
            $table->boolean('rewrite')->default(false);
            $table->boolean('enabled')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_redirects');
    }
};
