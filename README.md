# Laradrop

[![Software License][ico-license]](LICENSE.md)


This is a file manager using Dropzone.js for Laravel 5.  It provides basic functionality for managing, uploading,
and deleting files.

## Installation

NOTE: If you haven't set up a database yet for your app, please do that first as per Laravel docs -  http://laravel.com/docs/5.0/database.

Via composer
```
composer require jasekz/laradrop
```

Then in your `config/app.php` add 
```php
    'Jasekz\Laradrop\LaradropServiceProvider'
```    
to the `providers` array.

Then run 

    artisan vendor:publish
    
followed by

    artisan migrate

Laradrop uses Laravel's Filesystem mechanism (https://laravel.com/docs/5.2/filesystem) and by default will store your 
files in the `storage/app` directory.  If you would like to modify this behavior, along with other default settings, you can set your `.env` file variables:
```php
# s3, local, or Rackspace.  See 'Other Driver Prerequisites' at https://laravel.com/docs/5.2/filesystem.  Defaults to 'local'
LARADROP_DISK=local 

# If your files need to be web accessible, set this param.  S3, for example, would be 'https://s3.amazonaws.com/my-bucket'.  Defaults to the web root (public).
LARADROP_DISK_PUBLIC_URL=/img 

# If a thumbnail can not be generated due to incompatible file or any other reason, what image do you want to use? Defaults to 'vendor/jasekz/laradrop/img/genericThumbs/no-thumb.png'
LARADROP_DEFAULT_THUMB=/img/no-thumb.png

# Max file upload size in MB.  Defaults to 10.
LARADROP_MAX_UPLOAD_SIZE=20

# Max file size (in MB) for which thumbnail generation will be attempted.  If your server has an issue processing thumbs, you can lower this value.  Defaults to 10.
LARADROP_MAX_THUMBNAIL_SIZE=10

# Defaults to 150px.
LARADROP_THUMB_WIDTH=150

# Defaults to 150px.
LARADROP_THUMB_HEIGHT=150

# Run crud operations through middlware.  Defaults to none.
LARADROP_MIDDLEWARE=web
```
## Usage
This package requires Dropzone.js, jQuery, and jQuery UI.  Include these somewhere in your template:
``` php
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script src="/vendor/jasekz/laradrop/js/enyo.dropzone.js"></script>
<script src="/vendor/jasekz/laradrop/js/laradrop.js"></script>
```

By default, Laradrop is designed for Bootstrap, but it's not a requirement.  Include Bootstrap if you'd like to use it:
``` php
<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" type="text/css">
```


Add the html code where you'd like to implement the file manager.  Note, that by default, there is no middleware assigned to the Laradrop controller, however, it you assign middleware which contains csrf protection, you must include the `laradrop-csrf-token="{{ csrf_token() }}"` attribute.
``` html
<div class="laradrop" laradrop-csrf-token="{{ csrf_token() }}"> </div>
```

Finally, bind the button using jQuery:
```javascript
<script>
jQuery(document).ready(function(){
    // with defaults:
    jQuery('.laradrop').laradrop();
    
    // with custom params
    jQuery('.laradrop').laradrop({
        breadCrumbRootText: 'My Root', // optional 
        actionConfirmationText: 'Sure about that?', // optional
        onInsertCallback: function (src){ // optional
            // if you need to bind the select button, implement here
             alert('File '+src+' selected.  Please implement onInsertCallback().');
        },
        onErrorCallback: function(msg){ // optional
            // if you need an error status indicator, implement here
            alert('An error occured: '+msg);
        },
         onSuccessCallback: function(serverRes){ // optional
            // if you need a success status indicator, implement here
        }
        }); 
});
</script>
```

## Events
Laradrop currently fires two events:

1. **Jasekz\Laradrop\Events\FileWasUploaded** - this is fired after a file has been uploaded and saved.
2. **Jasekz\Laradrop\Events\FileWasDeleted** - this is fired after a file is deleted.

## Handlers (upload, delete, list, etc)
If you'd like to implement your own hanldlers (or extend the existing ones with your own controllers), you can do so.  All you need to do, is to defined the routes to the appropriate handlers in the button attributes.  This also allows you to easily have multiple handlers for different use cases, if so desired.
``` html
<div class="laradrop"
    laradrop-file-source="{{ route('yourRoute.index') }}" 
    laradrop-upload-handler="{{ route('yourRoute.store') }}"
    laradrop-file-delete-handler="{{ route('yourRoute.destroy', 0) }}"
    laradrop-file-move-handler="{{ route('yourRoute.move') }}"
    laradrop-file-create-handler="{{ route('yourRoute.create') }}"
    laradrop-containers="{{ route('yourRoute.containers') }}"
    laradrop-csrf-token="{{ csrf_token() }}">
</div>z
```


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.



[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
