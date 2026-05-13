<?php
return [
    'default' => 'file',
    'stores' => [
        'file' => [
            'type' => 'File',
            'path' => __DIR__ . '/../runtime/cache',
            'prefix' => '',
            'expire' => 0,
        ],
    ],
];
