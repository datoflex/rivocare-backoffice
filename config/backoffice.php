<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Two-factor authentication
    |--------------------------------------------------------------------------
    |
    | When enabled, every user must set up an authenticator app before they
    | can use the backoffice. Keep this on in every shared or hosted
    | environment; only switch it off for local development.
    |
    */

    'mfa_required' => (bool) env('MFA_REQUIRED', true),

];
