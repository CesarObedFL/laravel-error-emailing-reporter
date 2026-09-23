<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Enable Error Reporter
    |--------------------------------------------------------------------------
    |
    | Only report errors when the application runs in production.
    |
    */
    'enabled' => env('ERROR_REPORTER_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Allowed Environments
    |--------------------------------------------------------------------------
    |
    | Errors will only be reported when the current environment matches
    | any value in this list.
    |
    */
    'environments' => ['production'],

    /*
    |--------------------------------------------------------------------------
    | Recipients
    |--------------------------------------------------------------------------
    |
    | One or more email addresses that will receive the error reports.
    |
    */
    'recipients' => array_filter(
        explode(',', (string) env('ERROR_REPORTER_RECIPIENTS', ''))
    ),

    /*
    |--------------------------------------------------------------------------
    | Sender
    |--------------------------------------------------------------------------
    |
    | Sender email address. If null, it will use the Laravel mail "from"
    | configuration by default.
    |
    */
    'from' => env('ERROR_REPORTER_FROM', null),

    /*
    |--------------------------------------------------------------------------
    | Mailer
    |--------------------------------------------------------------------------
    |
    | Mailer connection to use. Must match a key inside config/mail.php.
    |
    */
    'mailer' => env('ERROR_REPORTER_MAILER', null),

    /*
    |--------------------------------------------------------------------------
    | Subject Prefix
    |--------------------------------------------------------------------------
    */
    'subject_prefix' => env('ERROR_REPORTER_SUBJECT_PREFIX', '[ERROR]'),

    /*
    |--------------------------------------------------------------------------
    | Throttle (in seconds)
    |--------------------------------------------------------------------------
    |
    | Prevents sending duplicate reports of the same error within the given
    | time window (in seconds). Set to 0 to disable.
    |
    */
    'throttle_seconds' => env('ERROR_REPORTER_THROTTLE', 60),

    /*
    |--------------------------------------------------------------------------
    | Ignored Exceptions
    |--------------------------------------------------------------------------
    |
    | Exception classes listed here will never be reported.
    |
    */
    'ignored_exceptions' => [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Validation\ValidationException::class,
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Include Context
    |--------------------------------------------------------------------------
    */
    'include_request'  => true,
    'include_session'  => false,
    'include_headers'  => true,
];