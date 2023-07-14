<?php

namespace NotFound\Framework\Services\Auth;

use NotFound\Framework\Exceptions\OpenID\OpenIDException;

class TokenDecoder
{
    public function decode(string $token, string $authenticationMethod): object
    {
        if ($authenticationMethod == 'remote') {
            $decoder = new RemoteTokenDecoder($token);
        } elseif ($authenticationMethod == 'local') {
            $decoder = new LocalTokenDecoder($token);
        } else {
            throw OpenIDException::invalidAuthenticationMethod($authenticationMethod);
        }
        
        return $decoder->getDecodedToken();
    }
}
