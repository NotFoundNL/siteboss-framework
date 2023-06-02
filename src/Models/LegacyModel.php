<?php

namespace NotFound\Framework\Models;

use NotFound\Framework\Models\Scopes\PublishedScope;
use NotFound\Framework\Services\Assets\AssetValues;
use NotFound\Framework\Services\Assets\TableService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * NotFound\Framework\Models\LegacyModel
 *
 * @method static \Illuminate\Database\Eloquent\Builder|LegacyModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LegacyModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LegacyModel query()
 *
 * @mixin \Eloquent
 */
class LegacyModel extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = [''];

    /**
     * Add Global Scope to ensure only items are published.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new PublishedScope());
    }

    public function v()
    {
        $cacheKey = 'table_'.$this->table.'_'.$this->id.'_'.Lang::current()->url;
        $secondsToRemember = 7 * 24 * 60 * 60;

        return Cache::remember($cacheKey, $secondsToRemember, function () {
            $tableService = new TableService(Table::whereTable($this->table)->first(), Lang::current(), $this->id);

            $tableValues = $tableService->getValues();

            return new AssetValues($tableValues);
        });
    }
}
