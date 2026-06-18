<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Security extends BaseConfig
{
    /**
     * CSRF Protection Method
     */
    public string $csrfProtection = 'cookie';

    /**
     * CSRF Token Randomization
     */
    public bool $tokenRandomize = true;

    /**
     * CSRF Token Name
     */
    public string $tokenName = 'csrf_token_name';

    /**
     * CSRF Header Name
     */
    public string $headerName = 'X-CSRF-TOKEN';

    /**
     * CSRF Cookie Name
     */
    public string $cookieName = 'csrf_cookie_name';

    /**
     * CSRF Expires
     */
    public int $expires = 7200;

    /**
     * CSRF Regenerate
     */
    public bool $regenerate = true;

    /**
     * CSRF Redirect
     */
    public bool $redirect = true;

    /**
     * CSRF SameSite
     */
    public string $samesite = 'Lax';
}
