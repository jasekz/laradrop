*This uses SoftDelete*
# Laradrop

[![Software License][ico-license]](LICENSE.md)


This is a file manager using Dropzone.js for Laravel 5.  It provides basic functionality for managing, uploading,
and deleting files.

## Demo
A demo of the system can be found at http://laradrop.elegrit.com.

## Quick Start
Here's an 4 step process to get a fully functional demo, similar to http://laradrop.elegrit.com.


1)  Follow the **Installation**  instructions below.
    
        Getting errors?  Make sure you have a database set up (http://laravel.com/docs/5.0/database).
        
2) In a view (welcome.blade.php, for example), add:  
```html
<html>
    <head>
        <title>Laradrop Demo</title>
        <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="/vendor/jasekz/laradrop/css/styles.css" rel="stylesheet" type="text/css">
        <link href="https://fonts.googleapis.com/css?family=Lato:300" rel="stylesheet" type="text/css">
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
        <script src="//code.jquery.com/ui/1.11.4/jquery-ui.min.js" ></script>
        <script src="/vendor/jasekz/laradrop/js/enyo.dropzone.js"></script>
        <script src="/vendor/jasekz/laradrop/js/laradrop.js"></script>
    </head>
    <body>
        <div class="laradrop" laradrop-csrf-token="{{ csrf_token() }}"> </div>
    </body>
    <script>
    jQuery(document).ready(function(){
        jQuery('.laradrop').laradrop();
    });
    </script>
</html>
```

3) In your **.env** file, add:

```
LARADROP_DISK_PUBLIC_URL=/uploads
LARADROP_DISK=laradrop
```
4) In your **config/filesystems.php**, add to your `disks` array:
```
'laradrop' => [
            'driver' => 'local',
            'root' => public_path('uploads'), // will put files in 'public/upload' directory
        ],
```
That's it.  If you have any issues or question, please feel free to open an issue.

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

## Configuration (.env)

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

# Run laradrop routes through middlware.  Defaults to none.
LARADROP_MIDDLEWARE=web
```
## Usage
This package requires Dropzone.js, jQuery, and jQuery UI.  Include these somewhere in your template:
``` php
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.min.js" ></script>
<script src="/vendor/jasekz/laradrop/js/enyo.dropzone.js"></script>
<script src="/vendor/jasekz/laradrop/js/laradrop.js"></script>
```

By default, Laradrop is designed for Bootstrap, but it's not a requirement.  Include Bootstrap and the Laradrop styles if you'd like to use it:
``` php
<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" type="text/css">
<link href="/vendor/jasekz/laradrop/css/styles.css" rel="stylesheet" type="text/css">
```


Add the html code where you'd like to implement the file manager.  Note, that by default, there is no middleware assigned to the Laradrop controller, however, it you assign middleware which contains csrf protection, you must include the `laradrop-csrf-token="{{ csrf_token() }}"` attribute.
``` html
<div class="laradrop" laradrop-csrf-token="{{ csrf_token() }}"> </div>
```

Finally, bind it using jQuery:
```javascript
<script>
jQuery(document).ready(function(){
    // Simplest:
    jQuery('.laradrop').laradrop();
    
    // With custom params:
    jQuery('.laradrop-custom').laradrop({
        breadCrumbRootText: 'My Root', // optional 
        actionConfirmationText: 'Sure about that?', // optional
        onInsertCallback: function (obj){ // optional 
            // if you need to bind the select button, implement here
             alert('Thumb src: '+obj.src+'. File ID: '+obj.id+'.  Please implement onInsertCallback().');
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
    laradrop-csrf-token="{{ csrf_token() }}"
    laradrop-allow=".pdf">
</div>
```
## File type validations
The default implementation of accept checks the file's mime type or extension against this list. This is a comma separated list of mime types or file extensions.

Eg.: image/*,application/pdf,.psd

If the Dropzone is clickable this option will also be used as accept parameter on the hidden file input as well.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.



[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
