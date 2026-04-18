<?php

return [
    'disk' => 'public',
    'base_directory' => 'media',
    'quality' => 80,
    'format' => 'webp',
    'sizes' => [
        '16x9' => [
            'medium' => ['width' => 800, 'height' => 450],
            'big' => ['width' => 1600, 'height' => 900],
        ],
        '4x3' => [
            'medium' => ['width' => 800, 'height' => 600],
            'big' => ['width' => 1600, 'height' => 1200],
        ],
    ],
];
