<?php

declare(strict_types=1);

namespace NotFound\Framework\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use NotFound\Framework\Services\Assets\AssetValues;
use NotFound\Framework\Services\Assets\TableService;

/**
 * NotFound\Framework\Models\BaseModel
 *
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel query()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel withoutTrashed()
 *
 * @mixin \Eloquent
 */
class BaseModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = [''];

    /**
     * Fetch the cached values for this model
     * based on the CMS settings.
     *
     * As this is causes heavy use of resources we'll cache
     * the result for a week.
     */
    public function v(): AssetValues
    {
        $updated = '';
        $secondsToRemember = 3600;
        if ($this->updated_at !== null) {
            $secondsToRemember = 7 * 24 * 60 * 60;
            $updated = (string) $this->updated_at->getTimeStamp();
        }
        $cacheKey = 'table_'.$this->table.'_'.$this->id.'_'.Lang::current()->url.$updated;

        return Cache::remember($cacheKey, $secondsToRemember, function () {
            $tableService = new TableService(Table::whereTable($this->table)->first(), Lang::current(), $this->id);

            $tableValues = $tableService->getValues();

            return new AssetValues($tableValues);
        });
    }
}
