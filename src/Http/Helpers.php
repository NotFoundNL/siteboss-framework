<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

if (! function_exists('siteboss_path')) {
    /**
     * Helper function to return the root of the siteboss folder
     *
     * @param $path adds string to the path
     * @return string root path of the 'site' folder
     */
    function siteboss_path(?string $path = null): string
    {
        return base_path().'/../site/'.$path;
    }
}

if (! function_exists('set_database_prefix')) {
    /**
     * Converts [][] to the DB prefix set in the config
     * Also accepts tablename without [][]
     *
     * @param $tableName for example: '[][]user' or 'user'
     * @return string 'cc_user'
     */
    function set_database_prefix(string $tableName)
    {
        $prefix = config('database.prefix');
        $tableName = str_replace('[][]', $prefix, $tableName);
        if (strpos($tableName, $prefix) !== 0) {
            $tableName = $prefix.$tableName;
        }

        return $tableName;
    }
}

if (! function_exists('remove_database_prefix')) {
    /**
     * Converts [][] to the DB prefix set in the config
     * Also accepts tablename without [][]
     *
     * @param $tableName for example: '[][]user' or 'user'
     * @return string 'cc_user'
     */
    function remove_database_prefix(string $tableName)
    {
        $prefix = config('database.prefix');
        $tableName = str_replace('[][]', $prefix, $tableName);
        if (strpos($tableName, $prefix) === 0) {
            $tableName = substr($tableName, strlen($prefix));
        }

        return $tableName;
    }
}

if (! function_exists('format_size')) {
    function format_size($size, $precision = 2)
    {
        $base = log($size, 1024);
        $suffixes = ['', 'K', 'M', 'G', 'T'];

        return round(pow(1024, $base - floor($base)), $precision).$suffixes[floor($base)];
    }
}

if (! function_exists('db_table_items_change_order')) {
    function db_table_items_change_order(string $tableName, int $recordId, int $replacedRecordId, string $whereSql = '')
    {
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
     * @param  mixed  $root The root folder for the path, we'll not go higher than this.
     * @param  mixed  $dir The path from the root folder
     * @return bool true if the directory was created or already existed.
     */
    function make_directories($root, $dir): bool
    {
        $dir = Str::lower($dir);
        if (substr($dir, 0, 1) !== '/') {
            $dir = '/'.$dir;
        }

        if (! is_dir($root)) {
            // Root folder must exist!
            Log::error('[makeDirIfNotExist] Root directory does not exist: '.$root);

            return false;
        }
        // No sneaky going up paths
        if (str_contains($dir, '..')) {
            Log::error('[makeDirIfNotExist] Directory contains ..: '.$dir);

            return false;
        }
        if (is_dir($root.$dir)) {
            // All set
            return true;
        } else {
            // Directory does not exist, so lets check the parent directory
            $parentDir = dirname($dir);
            if (! is_dir($root.$parentDir)) {
                // Parent directory does not exist, so lets create it
                make_directories($root, $parentDir);
            }
        }
        if (! mkdir($root.$dir)) {
            Log::error('[makeDirIfNotExist] Permission denied: '.$dir);

            return false;
        }

        return true;
    }
}