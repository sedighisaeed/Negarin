<?php

return [
    'url' => [
        'verify_dns' => env('NEGARIN_SECURITY_URL_VERIFY_DNS', false),

        'trusted_domains' => env('NEGARIN_SECURITY_URL_TRUSTED_DOMAINS', 'negarin.social,negarin.art,mastodon.social'),
    ],

    'forgot-email' => [
        'enabled' => env('NEGARIN_AUTH_ALLOW_EMAIL_FORGOT', true),

        'limits' => [
            'max' => [
                'hourly' => env('NEGARIN_AUTH_FORGOT_EMAIL_MAX_HOURLY', 50),
                'daily' => env('NEGARIN_AUTH_FORGOT_EMAIL_MAX_DAILY', 100),
                'weekly' => env('NEGARIN_AUTH_FORGOT_EMAIL_MAX_WEEKLY', 200),
                'monthly' => env('NEGARIN_AUTH_FORGOT_EMAIL_MAX_MONTHLY', 500),
            ]
        ]
    ]
];













