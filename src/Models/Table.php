<?php

namespace NotFound\Framework\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use NotFound\Framework\Services\Legacy\StatusColumn;
use NotFound\Framework\Traits\Exchangeable;

/**
 * NotFound\Framework\Models\Table
 *
 * @property int $id
 * @property string|null $comments
 * @property string $rights
 * @property string|null $url
 * @property string|null $table
 * @property string|null $name
 * @property bool $allow_create
 * @property bool $allow_delete
 * @property bool $allow_sort
 * @property object|null $properties
 * @property bool|null $enabled
 * @property int|null $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \NotFound\Framework\Models\TableItem> $items
 * @property-read int|null $items_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Table newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Table newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Table onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Table query()
 * @method static \Illuminate\Database\Eloquent\Builder|Table whereAllowCreate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Table whereAllowDelete($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Table whereAllowSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Table whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Table whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Table whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Table whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Table whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Table whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Table whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Table whereProperties($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Table whereRights($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Table whereTable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Table whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Table whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Table withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Table withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Table extends AssetModel
{
    use Exchangeable;
    use SoftDeletes;

    protected $table = 'cms_table';

    protected $visible = ['id', 'items', 'name', 'url'];

    protected $fillable = ['name', 'url', 'table', 'properties', 'enabled', 'allow_sort', 'allow_create', 'allow_delete'];

    protected $casts = [
        'properties' => 'object',
        'enabled' => 'boolean',
        'allow_sort' => 'boolean',
        'allow_create' => 'boolean',
        'allow_delete' => 'boolean',
    ];

    public function getIdentifier()
    {
        return $this->attributes['table'];
    }

    public function items()
    {
        return $this->hasMany(TableItem::class);
    }

    /**
     * Removes prefix or [][] from the table column
     *
     * @param  string  $tableName
     */
    public function getSiteTableName(): string
    {
        return $this->attributes['table'] ?? with(new $this->attributes['model'])->getTable();
    }

    /**
     * Returns an site table (aka a table that is specifically create for an site or app) based on record id
     *
     * @param  int  $entityId
     * @return object
     */
    public function getSiteTableRowByRecordId($recordId)
    {
        $tableName = $this->getSiteTableName();

        return DB::table($tableName)->whereId($recordId)->first();
    }

    /**
     * Returns all the localized rows on <table>_tr based on entity id
     *
     * @param  int  $entityId
     */
    public function getLocalizedRow($entityId, Lang $lang)
    {
        $siteTableNameTr = $this->getSiteTableName().'_tr';

        return DB::table($siteTableNameTr)->where('entity_id', $entityId)->where('lang_id', $lang->id)->first();
    }

    public function saveLocalize($translatedArray, $entityId, $langId)
    {
        $tableNameTr = $this->getSiteTableName().'_tr';

        if (DB::table($tableNameTr)->where('entity_id', $entityId)->where('lang_id', $langId)->count() == 0) {
            $translatedArray['entity_id'] = $entityId;
            $translatedArray['lang_id'] = $langId;
            DB::table($tableNameTr)->insert($translatedArray);
        } else {
            DB::table($tableNameTr)->where('entity_id', $entityId)->where('lang_id', $langId)->update($translatedArray);
        }
    }

    public function updateRecord(array $record): int
    {
        if ($record['id'] === null) {
            Log::alert('[TableService] Update record was called without ID');
            abort(500, 'something went wrong');
        }

        $tableName = $this->getSiteTableName();

        if (Schema::hasColumn($tableName, 'updated_at')) {
            $record['updated_at'] = now();
        }

        DB::table($tableName)->where('id', $record['id'])->update($record);

        return $record['id'];
    }

    public function createRecord($record): int
    {
        $tableName = $this->getSiteTableName();

        if (Schema::hasColumn($tableName, 'order') && $this->allow_sort) {
            DB::table($tableName)->increment('order');

            $record['order'] = 1;
        }

        if (Schema::hasColumn($tableName, 'status')) {
            // Remove when support for status column is dropped
            $record['status'] = 'PUBLISHED';
        }

        if (Schema::hasColumn($tableName, 'created_at')) {
            $record['created_at'] = now();
        }

        DB::table($tableName)->insert($record);

        return DB::getPdo()->lastInsertId();
    }

    public function deleteRecord(int $recordId): bool
    {
        $tableName = $this->getSiteTableName();

        if ($this->isLocalized()) {
            $translatedTableName = $tableName.'_tr';

            //TODO: delete language not all
            $succeeded = StatusColumn::deleteQuery(DB::table($translatedTableName)->where('entity_id', $recordId), $translatedTableName);
            if (! $succeeded) {
                return false;
            }
        }

        return StatusColumn::deleteQuery(DB::table($tableName)->where('id', $recordId), $tableName);
    }

    public function isLocalized(): bool
    {
        return $this->properties?->localize ?? false;
    }

    public function isOrdered(): bool
    {
        return $this->attributes['allow_sort'];
    }
}
