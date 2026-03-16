<?php

return [
    'storage_disk' => 'public',

    'allowed_file_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'docx', 'xlsx', 'zip'],

    'max_file_size' => 20480, // in KB (20MB)

    'image_processing' => [
        'enabled' => true,
        'max_width' => 1920,
        'quality' => 80,
    ],

    'route_prefix' => 'file-manager',
    'middleware' => ['web'],
];
