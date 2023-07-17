<?php

namespace NotFound\Framework\Providers\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Log;
use Nette\NotImplementedException;
use NotFound\Framework\Models\CmsUser;
use stdClass;

class OpenIDUserProvider implements UserProvider
{
    /**
     * The Mongo User Model
     */
    private CmsUser $model;

    /**
     * Create a new mongo user provider.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     * @return void
     */
    public function __construct(CmsUser $userModel)
    {
        $this->model = $userModel;
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     */
    public function retrieveById($identifier): ?Authenticatable
    {
        return $this->model->where('enabled', true)->where('sub', $identifier)->first();
    }

    /**
     * Retrieve a user by their e-mail address.
     */
    public function retrieveByEmail(string $email): ?CmsUser
    {
        return $this->model->where('email', $email)->first();
    }

    private function getEmailFromToken(object $token): ?string
    {
        $mailClaim = config('openid.mail_claim');
        if (! isset($token->$mailClaim)) {
            Log::error('Mail claim not set');

            return null;
        }

        return $token->$mailClaim;
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  stdClass  $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        $model = $this->retrieveById($identifier);
        // Sub exists in the database
        if ($model) {
            return $model;
        }

        // Update user sub if email exists
        if (config('openid.use_existing_email')) {
            $emailInToken = $this->getEmailFromToken($token);
            $model = $this->retrieveByEmail($emailInToken);

            if ($model) {
                if ($model->enabled !== 1) {
                    return;
                }
                $model->sub = $identifier;
                $model->save();

                return $model;
            }
        }

        if (config('openid.create_user')) {
            // Create user
            $user = new $this->model();

            if (config('openid.create_user_with_email')) {
                $user->email = $this->getEmailFromToken($token);
            }

            $user->sub = $token->sub;
            $user->enabled = 1;
            $user->save();

            return $user;
        }

        return null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  string  $token
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        throw new NotImplementedException();
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        throw new NotImplementedException();
    }

    /**
     * Validate a user against the given credentials.
     *
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        throw new NotImplementedException();
    }
}
