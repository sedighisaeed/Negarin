<?php

return [
    'rate-limits' => [
        'v1Dot1' => [
            'accounts' => [
                'usernameToId' => [
                    'enabled' => env('NEGARIN_API_RL_V1DOT1_ACCT_U2ID_ENABLED', true),
                    'limit' => env('NEGARIN_API_RL_V1DOT1_ACCT_U2ID_LIMIT', 30),
                    'decay' => env('NEGARIN_API_RL_V1DOT1_ACCT_U2ID_DECAY', 120),
                    'ip_enabled' => env('NEGARIN_API_RL_V1DOT1_ACCT_U2ID_BY_IP_ENABLED', false),
                    'ip_limit' => env('NEGARIN_API_RL_V1DOT1_ACCT_U2ID_BY_IP_LIMIT', 30),
                    'ip_decay' => env('NEGARIN_API_RL_V1DOT1_ACCT_U2ID_BY_IP_DECAY', 120),
                ]
            ]
        ]
    ]
];













