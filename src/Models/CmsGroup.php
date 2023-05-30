<?php

namespace NotFound\Framework\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * NotFound\Framework\Models\CmsGroup
 *
 * @property int $id
 * @property string|null $properties
 * @property int|null $parent
 * @property string|null $internal
 * @property string|null $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CmsGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsGroup onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsGroup whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsGroup whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsGroup whereInternal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsGroup whereParent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsGroup whereProperties($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsGroup withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsGroup withoutTrashed()
 *
 * @mixin \Eloquent
 */
class CmsGroup extends BaseModel
{
    use SoftDeletes;

    public $timestamps = false;

    protected $table = 'cms_group';

    public function getCachedGroups(): Collection
    {
        $secondsToRemember = 1 * 24 * 60 * 60;

        return Cache::remember('allRoles', $secondsToRemember, function () {
            return $this->get();
        });
    }

    public function getCachedRolesByActiveUser(): Collection
    {
        if (! session()->has('currentRoles')) {
            session()->put('currentRoles', $this->getRolesByUser(Auth::user()));
        }

        return session('currentRoles');
    }

    public function getRolesByUser(CmsUser $user, bool $useRecursion = true): Collection
    {
        $groupCollection = $this
            ->join('cms_usergroup', 'cms_usergroup.group_id', '=', 'cms_group.id')
            ->where('cms_usergroup.user_id', $user->id)
            ->select('cms_group.*')
            ->get();
        if (! $useRecursion) {
            // We don't want to use recursion, so we return the groups we found.
            return $groupCollection->pluck('internal');
        }

        $rolesWithChildren = new Collection();
        foreach ($groupCollection as $item) {
            $this->recursiveSetRights($rolesWithChildren, $item);
        }

        return $rolesWithChildren;
    }

    private function recursiveSetRights(Collection &$groupCollection, $recursiveItem): void
    {
        $groupCollection->add($recursiveItem->internal);
        $groups = $this->where('parent', $recursiveItem->id)->get();

        foreach ($groups as $item) {
            $this->recursiveSetRights($groupCollection, $item);
        }
    }
}
