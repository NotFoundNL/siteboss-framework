<?php

namespace NotFound\Framework\Services\Auth;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use NotFound\Framework\Exceptions\OpenID\OpenIDException;

abstract class AbstractTokenDecoder
{
    protected string $token;

    protected array $openIdConfiguration;

    public function __construct($token)
    {
        $this->token = $token;

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
