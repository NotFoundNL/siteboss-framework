<?php

use Illuminate\Support\Facades\DB;

if (! function_exists('db_table_items_change_order')) {
    function db_table_items_change_order(string $tableName, int $recordId, int $replacedRecordId, string $whereSql = ''): void
    {
        trigger_error('Method '.__METHOD__.' is deprecated', E_USER_DEPRECATED);
        $movedRecord = DB::table($tableName)->where('id', $recordId)->first();
        $replacedRecord = DB::table($tableName)->where('id', $replacedRecordId)->first();

        if (! isset($movedRecord->order) || ! isset($replacedRecord->order)) {
            throw new \Exception(__('response.table.order.column_null'));
        }

        $newRecordOrder = $replacedRecord->order;

        $tableNameWithPrefix = config('database.prefix').$tableName;
        $queryString = '';
        if ($movedRecord->order > $replacedRecord->order) {
            // Moved up
            $queryString = "
                UPDATE {$tableNameWithPrefix}
                   SET `order` = `order` + 1
                 WHERE `order` >= {$newRecordOrder}
                   AND `order` < {$movedRecord->order}
                ";
        } else {
            // Moved down
            $queryString = "
                UPDATE {$tableNameWithPrefix}
                   SET `order` = `order` - 1
                 WHERE `order` > {$movedRecord->order}
                   AND `order` <= {$newRecordOrder}
                ";
        }

        $updated = DB::update($queryString.$whereSql);

        if (! $updated) {
            throw new \Exception(__('response.table.order.error'));
        }

        DB::table($tableName)->where('id', $recordId)->update(['order' => $newRecordOrder]);
    }
}

if (! function_exists('make_directories')) {
    /**
     * @param  string  $root  The root folder for the path, we'll not go higher than this.
     * @param  string  $dir  The path from the root folder
     * @return bool true if the directory was created or already existed.
     */
    function make_directories(string $root, string $dir): bool
    {
        trigger_error('Method '.__METHOD__.' is deprecated', E_USER_DEPRECATED);

        return \Sb::makeDirectory($root, $dir);
    }
}
