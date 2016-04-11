<?php

return [    
    // temporary storage dir; this is where the file will be dropped on upload
    'LARADROP_INITIAL_UPLOADS_DIR' => env('LARADROP_INITIAL_UPLOADS_DIR', public_path()),
    
    'LARADROP_STORAGE_ENGINE' => 'local', // LOCAL, S3
    'LARADROP_STORAGE_ENGINES' => [
        'LOCAL' => [
            'UPLOADS_DIR' => env('LARADROP_STORAGE_ENGINES.LOCAL.UPLOADS_DIR', public_path()),
            'PUBLIC_LOCATION' => env('LARADROP_STORAGE_ENGINES.LOCAL.PUBLIC_LOCATION', '/img'),
        ],
//         'S3' => [ // future feature
//             'BUCKET' => env('LARADROP_STORAGE_ENGINES.S3.BUCKET', ''),
//         ]
    ],
];