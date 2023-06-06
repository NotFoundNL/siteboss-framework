<?php

namespace NotFound\Framework\Exceptions\OpenID;

use Exception;

class OpenIDException extends Exception
{
    public static function invalidConfigurationURL(): OpenIDException
    {
        return new self(
            sprintf('[OpenID] Invalid response from configuration url')
        );
    }

    public static function noUserInfoEndpoint(): OpenIDException
    {
        return new self(
            sprintf('[OpenID] Couldn\'t find "userinfo_endpoint" in configuration_url.')
        );
    }

    public static function invalidVerification($message): OpenIDException
    {
        return new self(
            sprintf('[OpenID] '.$message)
        );
    }

    public static function invalidIssuer($tokenIssuer, $localIssuer): OpenIDException
    {
        return new self(
            sprintf(
                '[OpenID] Token Issuer (%s) doesn\'t correspond with local Issuer(%s).',
                $tokenIssuer,
                $localIssuer
            )
        );
    }

    public static function invalidAuthenticationMethod($method): OpenIDException
    {
        return new self("Unsupported authentication_method [{$method}]");
    }
}
