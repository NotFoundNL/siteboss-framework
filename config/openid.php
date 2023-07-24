<?php

//this file is published by the siteboss-framework package

return [
    /*
    |--------------------------------------------------------------------------
    | Authenticate method
    |--------------------------------------------------------------------------
    |
    | There are multiple ways to authenticate a token. Currently there are two
    | methods supported.
    |
    | Local: We use the configuration_url to manually create the public keys
    | necessary to decode the token and verify the token ourselves. Most OpenID
    | providers support this method. This also has the least overhead.
    |
    | Remote: We make a http request to an authentication URL. This url
    | verifies the token.  This will create an extra request for every request
    | made to SiteBoss. The URL is defined in the OIDC configuration url
    |
    | Supported methods: "remote", "local"
    |
    */
    'authentication_method' => env('OIDC_AUTHENTICATION_METHOD', 'remote'),

    /*
    |--------------------------------------------------------------------------
    | OpenID Configuration URI
    |--------------------------------------------------------------------------
    |
    | If method is "remote":
    |
    | The OpenID Connect Server configuration endpoint. SiteBoss will use this
    | endpoint to gather all the information necessary to verify the token.
    | Usually the URL ends with '.well-known/openid-configuration'
    |
    */
    'configuration_url' => env('OIDC_CONFIGURATION_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | OpenID Client ID
    |--------------------------------------------------------------------------
    |
    | The client ID is provided by the OpenID Connect server.
    | This is used to specify which client in the realm SiteBoss is connecting
    | to.
    |
    */
    'client_id' => env('OIDC_CLIENT_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | OpenID issuer claim
    |--------------------------------------------------------------------------
    |
    | Identifier for the creator of the token. Is always a plain URI. This is
    | usually the URI of the OpenID server you're connecting to. This is used
    | to verify the token came from the server you wanted to connect to.
    |
    */
    'issuer' => env('OIDC_ISSUER', ''),

    /*
    |--------------------------------------------------------------------------
    | OpenID ID Token mail claim

    |--------------------------------------------------------------------------
    |
    | The token should contain a email. This option defines the key in the token
    | Where the email lives. We use this if 'create_user_with_email'
    | is enabled to update the database with the email. The OpenID provider
    | is free to choose how the claim is called.
    |
    */
    'mail_claim' => env('OIDC_MAIL_CLAIM', 'email'),

    /*
    |--------------------------------------------------------------------------
    | Use existing email
    |--------------------------------------------------------------------------
    |
    | Check the database if there is a user with the same email address as from the token.
    | If so, update that user to match the sub from the token.
    | This is so that users can be created before hand and to give them appropriate permissions.
    |
    */
    'use_existing_email' => env('OIDC_USE_EXISTING_EMAIL', false),

    /*
    |--------------------------------------------------------------------------
    | Create user
    |--------------------------------------------------------------------------
    |
    | Create a new user if the token is validated against the public key and
    | there is no existing sub (or email if 'use_existing_email' is enabled)
    | By default the user is created with only a sub.
    |
    */
    'create_user' => env('OIDC_CREATE_USER', true),
];
