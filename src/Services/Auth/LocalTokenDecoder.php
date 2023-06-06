<?php

namespace NotFound\Framework\Services\Auth;

use NotFound\Framework\Exceptions\OpenID\OpenIDException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class LocalTokenDecoder extends AbstractTokenDecoder
{
    private \stdClass $decodedToken;

    protected function decodeToken(): void
    {
        $keys = $this->parseJwtVerificationKeys();

        $this->decodedToken = JWT::decode($this->token, $keys);
        if (! $this->decodedToken) {
            throw new \Exception('Signature invalid.');
        }
    }

    protected function verifyToken(): void
    {
        // Validate the ID token claims:
        if ($this->decodedToken->iss != config('openid.issuer')) {
            throw OpenIDException::invalidIssuer($this->decodedToken->iss, config('openid.issuer'));
        }

        if (isset($this->decodedToken->ver) && $this->decodedToken->ver != '2.0') {
            throw OpenIDException::invalidVerification('Token version invalid.');
        }
    }

    public function getDecodedToken(): \stdClass
    {
        return $this->decodedToken;
    }

    /**
     * Get JWT verification keys OpenID JWKS URI.
     *
     * @return array
     */
    private function parseJwtVerificationKeys()
    {
        $secondsToRemember = 3600;

        $keysUri = $this->openIdConfiguration['jwks_uri'];
        $keysHash = crc32($keysUri);
        $response = Cache::remember('openid_keys_uri_'.$keysHash, $secondsToRemember, function () use ($keysUri) {
            return Http::get($keysUri)->json();
        });

        $keys = [];
        foreach ($response['keys'] as $keyInfo) {
            if (! isset($keyInfo['x5c']) || ! is_array($keyInfo['x5c'])) {
                continue;
            }

            foreach ($keyInfo['x5c'] as $encodedKey) {
                $cert =
                        '-----BEGIN CERTIFICATE-----'.PHP_EOL
                        .chunk_split($encodedKey, 64, PHP_EOL)
                        .'-----END CERTIFICATE-----'.PHP_EOL;

                $certObject = openssl_x509_read($cert);
                if ($certObject === false) {
                    throw new \RuntimeException('An attempt to read '.$encodedKey.' as a certificate failed.');
                }

                $pkeyObject = openssl_pkey_get_public($certObject);
                if ($pkeyObject === false) {
                    throw new \RuntimeException('An attempt to read a public key from a '.$encodedKey.' certificate failed.');
                }

                $pkeyArray = openssl_pkey_get_details($pkeyObject);
                if ($pkeyArray === false) {
                    throw new \RuntimeException('An attempt to get a public key as an array from a '.$encodedKey.' certificate failed.');
                }

                $publicKey = $pkeyArray['key'];

                $keys[$keyInfo['kid']] = new Key($publicKey, 'RS256');
            }
        }

        return $keys;
    }
}
