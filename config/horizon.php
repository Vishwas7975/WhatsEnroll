<?php

use Illuminate\Support\Str;

return [

    'name' => env('HORIZON_NAME', 'WhatsEnroll'),

    'domain' => env('HORIZON_DOMAIN', null),

    'path' => env('HORIZON_PATH', 'horizon'),

    'use' => 'default',

    'prefix' => env(
        'HORIZON_PREFIX',
        Str::slug(env('APP_NAME', 'laravel'), '_') . '_horizon:'
    ),

    'middleware' => ['web', 'auth'],

    'waits' => [
        'redis:default'     => 3,
        'redis:bot'         => 1,
        'redis:credentials' => 5,
    ],

    'trim' => [
        'recent'        => 60,
        'pending'       => 60,
        'completed'     => 180,
        'recent_failed' => 10080,
        'failed'        => 10080,
        'monitored'     => 10080,
    ],

    'silenced' => [],

    'metrics' => [
        'trim_snapshots' => [
            'job'   => 24,
            'queue' => 24,
        ],
    ],

    'fast_termination' => false,

    'memory_limit' => 128,

    'environments' => [

        'production' => [

            'bot-supervisor' => [
                'connection'          => 'redis',
                'queue'               => ['bot'],
                'balance'             => 'auto',
                'autoScalingStrategy' => 'time',
                'minProcesses'        => 2,
                'maxProcesses'        => 8,
                'balanceMaxShift'     => 1,
                'balanceCooldown'     => 3,
                'tries'               => 2,
                'timeout'             => 60,
                'memory'              => 128,
            ],

            'credentials-supervisor' => [
                'connection'    => 'redis',
                'queue'         => ['credentials'],
                'balance'       => 'simple',
                'minProcesses'  => 1,
                'maxProcesses'  => 4,
                'tries'         => 3,
                'timeout'       => 120,
                'memory'        => 128,
            ],

            'default-supervisor' => [
                'connection'    => 'redis',
                'queue'         => ['default'],
                'balance'       => 'simple',
                'minProcesses'  => 1,
                'maxProcesses'  => 2,
                'tries'         => 3,
                'timeout'       => 60,
                'memory'        => 128,
            ],
        ],

        'local' => [

            'bot-supervisor' => [
                'connection'    => 'redis',
                'queue'         => ['bot'],
                'balance'       => 'simple',
                'minProcesses'  => 1,
                'maxProcesses'  => 2,
                'tries'         => 2,
                'timeout'       => 60,
                'memory'        => 128,
            ],

            'credentials-supervisor' => [
                'connection'    => 'redis',
                'queue'         => ['credentials'],
                'balance'       => 'simple',
                'minProcesses'  => 1,
                'maxProcesses'  => 2,
                'tries'         => 3,
                'timeout'       => 120,
                'memory'        => 128,
            ],

            'default-supervisor' => [
                'connection'    => 'redis',
                'queue'         => ['default'],
                'balance'       => 'simple',
                'minProcesses'  => 1,
                'maxProcesses'  => 1,
                'tries'         => 3,
                'timeout'       => 60,
                'memory'        => 128,
            ],
        ],
    ],
];