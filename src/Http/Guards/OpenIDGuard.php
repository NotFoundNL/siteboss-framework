<?php

namespace NotFound\Framework\Http\Guards;

use NotFound\Framework\Providers\Auth\OpenIDUserProvider;
use NotFound\Framework\Services\Auth\Token;
use NotFound\Framework\Services\Auth\TokenDecoder;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use NotFound\Framework\Models\CmsGroup;

class OpenIDGuard implements Guard
{
    use GuardHelpers;

    private array $config;

    private Request $request;

    private $decodedToken;

    public function __construct(OpenIDUserProvider $provider, Request $request)
    {
        $this->config = config('openid');
        $this->provider = $provider;
        $this->request = $request;
        $this->user = null;
    }

    /**
     * Validate a user's token.
     */
    public function validate(array $credentials = []): bool
    {
        $token = $this->request->bearerToken();
        if (! $token) {
            return false;
        }

        try {
            $tokenDecoder = new TokenDecoder();
            $this->decodedToken = $tokenDecoder->decode($token, $this->config['authentication_method']);
        } catch (\Exception $e) {
            Log::warning('[OpenID Guard] Token decode error: '.$e->getMessage());

            return false;
        }

        $user = $this->provider->retrieveByToken($this->decodedToken?->sub, $this->decodedToken);

        if ($user) {
            $this->setUser($user);

            return true;
        }

        return false;
    }

    /**
     * Called in Illuminate\Auth\Middleware\Authenticate
     * if this returns true then the auth manager uses the guard
     * if it returns false on the guard, it'll throw an unauthenticatedDownload request.
     *
     * So this is the entry point when the guard is being checked. Hence calling validate.
     *
     * @return bool
     */
    public function check()
    {
        return $this->validate();
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        return $this->user;
    }

    /**
     * Check if authenticated user has a a specific role into resource
     *
     * @param  string  $resource
     * @param  string  $role
     * @return bool
     */
    public function hasRole($role, $resource = 'siteboss')
    {
        // TODO: MOVE THIS
        $groupC = new CmsGroup();
        $roles = $groupC->getRolesByUser($this->user());

        $onlyLocalRoles = $roles->where('remote', 0);

        return $onlyLocalRoles->contains($role);
    }
}
