<?php

namespace NotFound\Framework\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use NotFound\Framework\Auth\Notifications\VerifyEmail;
use NotFound\Framework\Helpers\BooleanExpressionEvaluator;

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
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, CmsGroup> $groups
 * @property-read int|null $groups_count
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
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
class CmsUser extends User implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $hidden = [
        'secret',
        'password',
    ];

    public $timestamps = false;

    protected $table = 'cms_user';

    protected $fillable = [
        'name',
        'login',
        'sub',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'object',
            'preferences' => 'object',
            'email_verified_at' => 'datetime',
        ];
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(CmsGroup::class, 'cms_usergroup', 'user_id', 'group_id');
    }

    protected function emailVerified(): Attribute
    {
        return Attribute::make(
            get: fn () => (bool) $this->email_verified_at,
        );
    }

    /**
     * Use an expression to check if the user has the appropriate rights.
     *
     * Example: "!admin || user" or "form-data && admin" or "admin"
     */
    public function checkRights(string $expression, bool $default = true): bool
    {
        if ($expression == 'false') {
            return true;
        }

        if ($expression === null || empty(trim($expression))) {
            return $default;
        }

        if (preg_match('/[^a-z &!|)(-]/', $expression)) {
            abort(500, 'Syntax error encountered in checkRights! Use of illegal characters.');
        }

        $resolved = preg_replace_callback('/[a-z-]+/', [$this, 'expressionCallBack'], trim($expression));

        return BooleanExpressionEvaluator::evaluate($resolved);
    }

    private function expressionCallBack(array $matches): string
    {
        $group = $matches[0];
        if ($group === 'true' || $group === 'false') {
            abort(500, __("Syntax error encountered in checkRights! Use of illegal String ('true' || 'false')."));
        }

        if (! CmsGroup::getCachedGroups()->pluck('internal')->contains($group)) {
            abort(500, sprintf("Syntax error encountered in checkRights! Use of non-existing group '%s'.", $group));
        }

        return $this->hasRole($group) ? 'true' : 'false';
    }

    /**
     * Checks if the user has explicitly (not inherited) been given the role.
     */
    public function explicitlyHasRole(string $role): bool
    {
        $roles = CmsGroup::getRolesByUser($this, useRecursion: false);

        return $roles->contains($role);
    }

    public function hasRole(string $rolesToCheck): bool
    {
        if (trim($rolesToCheck) == '') {
            return false;
        }

        $roles = explode(',', $rolesToCheck);
        foreach ($roles as $role) {
            if ($this->hasLocalRole($role)) {
                return true;
            }
        }

        return false;
    }

    public function hasLocalRole(string $role): bool
    {
        $roles = CmsGroup::getRolesByUser($this);

        return $roles->contains($role);
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmail);
    }
}
