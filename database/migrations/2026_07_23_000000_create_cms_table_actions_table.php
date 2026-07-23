<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_table_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained('cms_table')->cascadeOnDelete();
            $table->enum('condition', ['create', 'edit', 'archive', 'delete']);
            $table->unsignedInteger('days');
            $table->enum('action', ['archive', 'delete', 'purge']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_table_actions');
    }
};
