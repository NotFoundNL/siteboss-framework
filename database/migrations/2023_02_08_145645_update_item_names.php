<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        $this->updateItemNames('cms_templateitem');
        $this->updateItemNames('cms_tableitem');
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

    private function updateItemNames($table)
    {
        $this->renameType($table, 'text', 'Text');
        $this->renameType($table, 'checkbox', 'Checkbox');
        $this->renameType($table, 'tableselect', 'TableSelect');
        $this->renameType($table, 'tags', 'Tags');
        $this->renameType($table, 'button', 'Button');
    }

    private function renameType($table, $oldType, $newType)
    {
        DB::table($table)
            ->where('type', $oldType)
            ->update([
                'type' => $newType,
            ]);
    }
};
