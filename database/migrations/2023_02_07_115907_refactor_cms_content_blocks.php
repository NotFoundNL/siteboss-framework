<?php

use NotFound\Framework\Models\CmsContentBlocks;
use NotFound\Framework\Models\Table;
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
        Schema::table('cms_content_blocks', function (Blueprint $table) {
            $table->enum('asset_type', ['table', 'page'])->after('id');
            $table->foreignId('source_asset_item_id')->after('asset_type');
        });

        $blocks = CmsContentBlocks::withTrashed()->get();
        foreach ($blocks as $block) {
            $table = Table::whereId($block->source_table_id)->with('items')->first();
            $contentBlock = $table->items->firstWhere('type', 'ContentBlocks');

            $block->source_asset_item_id = $contentBlock->id;
            $block->save();
        }

        Schema::table('cms_content_blocks', function (Blueprint $table) {
            $table->dropColumn('source_table_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
