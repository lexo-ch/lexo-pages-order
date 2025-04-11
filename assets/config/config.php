<?php

if (!defined('ABSPATH')) {
    exit; // Don't access directly
};

use const LEXO\PO\{
    PATH,
    URL
};

return [
    'priority'  => 90,
    'dist_path' => PATH . 'dist',
    'dist_uri'  => URL . 'dist',
    'assets'    => [
        'front' => [
            'styles'    => [],
            'scripts'   => []
        ],
        'admin' => [
            'styles'    => ['css/admin-po.css'],
            'scripts'   => ['js/admin-po.js']
        ],
        'editor' => [
            'styles'    => []
        ],
    ]
];
