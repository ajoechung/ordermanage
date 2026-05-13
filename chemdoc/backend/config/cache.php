<?php
return [
    'default' => 'file',
    'stores' => [
        'file' => [
            'type' => 'File',
            'path' => '../runtime/cache/',
            'prefix' => '',
            'expire' => 0,
        ],
    ],
];
