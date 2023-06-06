<?php

namespace NotFound\Framework\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User;
use Illuminate\Notifications\Notifiable;

/**
 * NotFound\Framework\Models\CmsUser
 *
 * @property int $id
 * @property int|null $session_id
 * @property string|null $mobile
 * @property int|null $last_attempt
 * @property int|null $failed_attempts
 * @property int|null $last_change
 * @property int|null $last_login
 * @property object|null $properties
 * @property string|null $name
 * @property string|null $email
 * @property string $secret
 * @property string|null $password
 * @property int|null $enabled
 * @property int|null $order
 * @property string $sub
 * @property object|null $preferences
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \NotFound\Framework\Models\CmsGroup> $groups
 * @property-read int|null $groups_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CmsUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsUser onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsUser whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsUser whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsUser whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsUser whereFailedAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsUser whereLastAttempt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsUser whereLastChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsUser whereLastLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsUser whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsUser whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsUser whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsUser wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsUser wherePreferences($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsUser whereProperties($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsUser whereSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsUser whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsUser whereSub($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsUser withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsUser withoutTrashed()
 *
 * @mixin \Eloquent
 */
class CmsUser extends User
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $hidden = [
        'secret',
        'password',
    ];

    public $timestamps = false;

    protected $table = 'cms_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'login',
        'sub',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'properties' => 'object',
        'preferences' => 'object',
    ];

    public function groups()
    {
        //TODO: proper name is: cc_cms_group_user || cc_cms_user_group
        return $this->belongsToMany(CmsGroup::class, 'cms_usergroup', 'user_id', 'group_id');
    }

    /**
     * Use an expression to check if the user has the appropriate
     * rights.
     *
     * Example: "!admin || user" or "form-data && admin" or "admin"
     * check the database which groups exist
     *
     * @param  string  $expression The expression to check
     * @param  bool  $default if the rights are not set, returns this
     * @return bool user is authorized for the expression
     */
    public function checkRights($expression, $default = true): bool
    {
        // Allow a simple expression to disable a field
        if ($expression == 'false') {
            return true;
        }

        if (! isset($expression) || empty(trim($expression))) {
            // Rights have not been set => return true;
            // This is to make things backwards compatible
            return $default;
        }

        //Check for use of illegal characters. Just allow () || && ! and alpha
        if (preg_match('/[^a-z &!|)(-]/', $expression)) {
            abort(500, 'Syntax error encountered in checkRights! Use of illegal characters.');
        }

        $rights = preg_replace_callback('/[a-z-]+/', [$this, 'expressionCallBack'], trim($expression));

        $result = null;
        eval('$result='.$rights.';');

        return $result;
    }

    /**
     * Called from CheckRights()
     *
     * @param  mixed  $matches
     * @return string that's either 'false' or 'true'
     */
    private function expressionCallBack($matches): string
    {
        $group = $matches[0];
        if ($group === 'true' || $group === 'false') {
            abort(500, __("Syntax error encountered in checkRights! Use of illegal String ('true' || 'false')."));
        }

        $groupC = new CmsGroup();
        if (! $groupC->getCachedGroups()->pluck('internal')->contains($group)) {
            abort(500, sprintf("Syntax error encountered in checkRights! Use of non-existing group '%s'.", $group));
        }

        //Check for an exact match.
        if ($this->hasRole($group)) {
            return 'true';
        }

        return 'false';
    }

    /**
     * explicityHasRole
     *
     * Checks if the user has explicitly (not inherited) been given the role.
     *
     * @param  string  $role The role to check
     * @return array of roles
     */
    public function explicityHasRole($role)
    {
        $groupC = new CmsGroup();
        $roles = $groupC->getRolesByUser($this, useRecursion: false);

        return $roles->contains($role);
    }

    public function hasRole($rolesToCheck)
    {
        if (trim($rolesToCheck) == '') {
            return false;
        }

        $roles = explode(',', $rolesToCheck);
        foreach ($roles as $role) {
            // OpenID roles from SSO
            // if (auth()->hasRole(trim($role))) {
            //     return true;
            // // Local SiteBoss roles
            // } else
            if ($this->hasLocalRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks the database against the roles assigned to the user
     *
     * @param  string  $role role to check
     */
    public function hasLocalRole(string $role): bool
    {
        $groupC = new CmsGroup();
        $roles = $groupC->getRolesByUser($this);

        return $roles->contains($role);
    }
}
