<?php

return [
    /*
    |--------------------------------------------------------------------------
    | System Protected Roles
    |--------------------------------------------------------------------------
    |
    | These roles cannot be assigned via the dashboard, modified, or deleted.
    |
    */
    'protected_roles' => [
        'Super Administrator',
    ],

    /*
    |--------------------------------------------------------------------------
    | System Protected Accounts
    |--------------------------------------------------------------------------
    |
    | These accounts can only be edited by themselves when logged in.
    | Other users cannot modify these accounts.
    |
    */
    'protected_accounts' => [
        'admin@admin.com',
    ],
];
