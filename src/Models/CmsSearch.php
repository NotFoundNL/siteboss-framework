<?php

namespace NotFound\Framework\Models;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * NotFound\Framework\Models\CmsSearch
 *
 * @property int $id
 * @property string $url
 * @property mixed|null $search_status
 * @property string $language
 * @property string|null $type
 * @property int|null $updated
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CmsSearch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsSearch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsSearch onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsSearch query()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsSearch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsSearch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsSearch whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsSearch whereSearchStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsSearch whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsSearch whereUpdated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsSearch whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsSearch whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsSearch withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsSearch withoutTrashed()
 *
 * @mixin \Eloquent
 */
class CmsSearch extends BaseModel
{
    protected $table = 'cms_search';

    private array $status = ['PENDING', 'ADDED', 'SKIPPED', 'UPDATED', 'NOT_INDEXABLE', 'NOT_FOUND'];

    private static array $skipStatus = []; //['NOT_INDEXABLE', 'NOT_FOUND'];

    protected $fillable = ['url'];

    public static function setAllPending()
    {
        try {
            CmsSearch::query()->whereNotIn('search_status', self::$skipStatus)
                ->update(['search_status' => 'PENDING', 'updated_at' => DB::raw('updated_at')]); // ignore timestamps
        } catch (QueryException $ex) {
            dd($ex->getMessage());
        }
    }
}
