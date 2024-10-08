<?php

return [
    'settings_file_path' => env('APIRONE_SETTINGS_FILE_PATH', storage_path('app/apirone/settings.json')),
    'table_prefix' => env('APIRONE_TABLE_PREFIX', ''),
    'callback_url' => env('APIRONE_CALLBACK_URL', 'http://localhost:8000/api/callback'),
    'lifetime' => env('APIRONE_LIFETIME', 4000),
];
