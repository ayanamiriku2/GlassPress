<?php
/**
 * GlassPress Configuration
 * Generated during installation on 2026-03-28 14:53:00
 * 
 * SECURITY: Do not share this file or expose it publicly.
 */

return [
    'debug' => false,

    'database' => [
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'your_database',
        'username' => 'your_password',
        'password' => 'your_password',
        'prefix' => 'gp_',
        'charset' => 'utf8mb4',
    ],

    'security' => [
        'key' => '49371cdbe3175fd02dff04680719ec7a62e95507b15b52a6198539037c3b38f8',
        'auth_salt' => 'd47d7010029d83fdb87cdcd39f9afc8e8a9c80186cd8509e55c2d794cdedc19f',
    ],

    'uploads' => [
        'max_size' => 10485760,
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'zip', 'doc', 'docx', 'xls', 'xlsx', 'mp4', 'mp3'],
    ],
];