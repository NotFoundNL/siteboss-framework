<?php

namespace NotFound\Framework\Services\Auth;

use App\Exceptions\OpenID\OpenIDException;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;

class RemoteTokenDecoder extends AbstractTokenDecoder
{
    private \stdClass $decodedToken;

    protected function decodeToken(): void
    {
        $this->decodedToken = json_decode(JWT::urlsafeB64Decode($this->tokenParts[1]));
    }

    protected function verifyToken(): void
    {
        // Validate the ID token claims:
        if ($this->decodedToken->iss != config('openid.issuer')) {
            throw OpenIDException::invalidIssuer($this->decodedToken->iss, config('openid.issuer'));
        }

        // Validate the AppId claims:
        if ($this->decodedToken->appid != config('openid.client_id')) {
            throw OpenIDException::invalidVerification($this->decodedToken->appid, config('openid.client_id'));
        }
    }

    public function getDecodedToken(): \stdClass
    {
        return $this->getRemoteAuthenticatedToken();
    }

    private function getRemoteAuthenticatedToken(): \stdClass
    {
        $userEndpoint = $this->openIdConfiguration['userinfo_endpoint'];
        if (! $userEndpoint) {
            throw OpenIDException::noUserInfoEndpoint();
        }

        $response = Http::withToken($this->token)->get($userEndpoint);
        if (! $response->ok()) {
            throw new \Exception('Token is invalid.');
        }

        return (object) $response->json();
    }
}
