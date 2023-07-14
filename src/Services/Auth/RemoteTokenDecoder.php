<?php

namespace NotFound\Framework\Services\Auth;

use Illuminate\Support\Facades\Http;
use NotFound\Framework\Exceptions\OpenID\OpenIDException;
use NotFound\Framework\Services\Auth\LocalTokenDecoder;

class RemoteTokenDecoder extends AbstractTokenDecoder
{
    private \stdClass $decodedToken;

    protected function decodeToken(): void
    {
        $this->decodedToken = $this->getDecodedToken();
    }

    protected function verifyToken(): void
    {
        $idToken = $_COOKIE['auth__id_token_oidc'];
        $decoder = new LocalTokenDecoder($idToken);
        $decodedIdToken = $decoder->getDecodedToken();

        // Check if tokens exist
        if(!$decodedIdToken || !$this->decodedToken) {
            throw OpenIDException::invalidVerification($this->decodedToken->email, config('openid.client_id')); //TODO change exception
        }

        // Validate idToken has same sub and email as token
        if ($this->decodedToken->sub != $decodedIdToken->sub && $this->decodedToken->email != $decodedIdToken->email) {
            throw OpenIDException::invalidVerification($this->decodedToken->email, config('openid.client_id')); //TODO change exception
        }

        // Validate the AppId claims:
        if ($decodedIdToken->aud != config('openid.client_id')) {
            throw OpenIDException::invalidVerification($this->decodedToken->aud, config('openid.client_id'));
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
