<?php

return [    
    // max file upload size in MB
    'max_upload_size' => env('LARADROP_MAX_UPLOAD_SIZE', 10),
    
    // max size for thumb generation in MB; if your server crashes while trying to generate thumbs, you can lower the threshold here
    'max_thumbnail_size' => env('LARADROP_MAX_THUMBNAIL_SIZE', 10),

    // dimensions for thumbnail generator
    'thumb_dimensions' => ['width' => env('LARADROP_THUMB_WIDTH', 150), 'height' => env('LARADROP_THUMB_HEIGHT', 150)],

    // when true, the thumbnail sizes will be based solely on the width set above
    'thumb_aspect' => env('LARADROP_THUMB_ASPECT', true),
    
    // default thumbnail (if one can not be generated)
    'default_thumbnail_url' => env('LARADROP_DEFAULT_THUMB', '/vendor/jjfs85/laradrop/img/genericThumbs/no-thumb.png'),
    
    // storage location - use config/filesystems.php 'disks'
    'disk' => env('LARADROP_DISK', 'local'),
    
    // if this needs to be publicly accessible, this is the 'root storage directory'
    'disk_public_url' => env('LARADROP_DISK_PUBLIC_URL', ''),
];