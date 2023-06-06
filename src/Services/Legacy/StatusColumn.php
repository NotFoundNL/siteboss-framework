<?php

namespace NotFound\Framework\Services\Legacy;

use Illuminate\Support\Facades\Schema;
use NotFound\Framework\Models\BaseModel;
use NotFound\Framework\Models\LegacyModel;

class StatusColumn
{
    public static function wherePublished($query, string $tableName)
    {
        if (Schema::hasColumn($tableName, 'status')) {
            return $query->where('status', 'published');
        } else {
            return $query->where('deleted_at', null);
        }
    }

    public static function whereDeleted($query, string $tableName)
    {
        if (Schema::hasColumn($tableName, 'status')) {
            return $query->where('status', 'deleted');
        } else {
            return $query->whereNot('deleted_at', null);
        }
    }

    public static function deleteModel(BaseModel|LegacyModel $model): bool
    {
        if (Schema::hasColumn($model->getTable(), 'deleted_at')) {
            $model->deleted_at = now();
        }

        if (Schema::hasColumn($model->getTable(), 'status')) {
            $model->status = 'deleted';
        }

        return $model->save();
    }

    public static function deleteQuery($query, string $tableName): bool
    {
        if (Schema::hasColumn($tableName, 'status')) {
            $result = $query->update(['status' => 'deleted']);
        }

        if (Schema::hasColumn($tableName, 'deleted_at')) {
            $result2 = $query->update(['deleted_at' => now()]);
        }

        return ($result ?? true) && ($result2 ?? true);
    }
}
