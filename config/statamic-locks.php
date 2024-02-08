<?php

return [
    'database' => [
        'connection' => null,
    ],

    'clear_locks_after' => 5, // minutes

    'locks' => [
        'entry' => [
            'handler' => '\Statamic\Facades\Entry',
            'cp_url' => '/collections/*/entries/{id}',
        ],
        'global' => [
            'handler' => '\Statamic\Facades\GlobalSet',
            'cp_url' => '/globals/{id}',
        ],
        'term' => [
            'handler' => '\Statamic\Facades\Term',
            'cp_url' => '/taxonomies/*/terms/{id}',
        ],
    ],

    'poll_interval' => 30, // seconds
];
