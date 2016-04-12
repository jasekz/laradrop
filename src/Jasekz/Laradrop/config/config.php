<?php

return [    
    // max file upload size in MB
    'max_upload_size' => env('LARADROP_MAX_UPLOAD_SIZE', 10),
    
    // dimensions for thumbnail generator
    'thumb_dimensions' => ['width' => env('LARADROP_THUMB_WIDTH', 150), 'height' => env('LARADROP_THUMB_HEIGHT', 150)],
    
    // storage location - use config/filesystems.php 'disks'
    'disk' => env('LARADROP_DISK', 'local'),
    
    // if this needs to be publicly accessible, this is the 'root storage directory'
    'disk_public_url' => env('LARADROP_DISK_PUBLIC_URL', '/'),
];