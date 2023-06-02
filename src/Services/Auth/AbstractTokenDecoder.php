<?php

namespace NotFound\Framework\Services\Auth;

use App\Exceptions\OpenID\OpenIDException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

abstract class AbstractTokenDecoder
{
    protected string $token;

    protected array $openIdConfiguration;

    public function __construct($token)
    {
        $this->token = $token;
        $this->tokenParts = explode('.', $token);

        // Ensure there's exactly 3 parts in the token
        if (count($this->tokenParts) !== 3) {
            throw new \Exception('Invalid token format.');

            return false;
        }

        $this->getOpenIdConfiguration();

        $this->decodeToken();
        $this->verifyToken();
    }

    private function getOpenIdConfiguration()
    {
        $secondsToRemember = 3600;

        $configUri = config('openid.configuration_url');
        $configUriHash = crc32($configUri);
        $this->openIdConfiguration = Cache::remember('openid_configuration_url_'.$configUriHash, $secondsToRemember, function () use ($configUri) {
            $response = Http::get($configUri);
            if (! $response->ok()) {
                throw OpenIDException::invalidConfigurationURL();
            }

            return $response->json();
        });
    }

    abstract protected function decodeToken(): void;

    abstract protected function verifyToken(): void;

    abstract public function getDecodedToken(): \stdClass;
}
