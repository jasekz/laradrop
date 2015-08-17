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
```
composer update
```

Then in your `config/app.php` add 
```php
    'Jasekz\Laradrop\LaradropServiceProvider'
```    
in the `providers` array and
```php
    'Laradrop' => 'Jasekz\Laradrop\LaradropFacade'
```
to the `aliases` array.

Finally, run 

    artisan vendor:publish
    
followed by

    artisan migrate

Now in your .env file, define your file upload paths and urls:
```php
LARADROP_STORAGE_ENGINE=local
LARADROP_INITIAL_UPLOADS_DIR=/absolute/path/to/storage
LARADROP_STORAGE_ENGINES.LOCAL.UPLOADS_DIR=/absolute/path/to/public/uploads
LARADROP_STORAGE_ENGINES.LOCAL.PUBLIC_LOCATION=/uploads
```
## Usage
This package requires Dropzone.js, jQuery, Bootstrap, as well as some application level js.  Include these somewhere in your view:
``` php
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet" type="text/css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<script src="/vendor/jasekz/laradrop/js/enyo.dropzone.js"></script>
<script src="/vendor/jasekz/laradrop/js/laradrop.js"></script>
```

NOTE:  I plan on removing the Bootstrap requirement, however, right now it is needed for the modal dialog and styling.

Add a button where you want to implement the file manager:
``` html
<div class="laradrop"
  laradrop-upload-handler="{{ route('laradrop.store') }}"
  laradrop-file-delete-handler="{{ route('laradrop.destroy') }}" 
  laradrop-file-source="{{ route('laradrop.index') }}"
  laradrop-csrf-token="{{ csrf_token() }}" >
  <button class='btn btn-primary laradrop-select-file' >Add File</button>
</div>
```

Finally, bind the button using jQuery:
```javascript
<script>
jQuery(document).ready(function(){
	jQuery('.laradrop').laradrop({
		onInsertCallback: function (src){
		  // this is called when the 'select' button is clicked on a thumbnail
			alert('File '+src+' selected.  Please implement onInsertCallback().');
		}
	});
});
</script>
```

## Events
Laradrop currently fires two events:
1. Jasekz\Laradrop\Events\FileWasUploaded - this is fired as soon as the file is uploaded to the initial uploads directory, as defined by ```LARADROP_INITIAL_UPLOADS_DIR```.  At this point, the file is not yet saved in the database, thumbnails are not created and it is not moved to the final location, as defined by ```LARADROP_STORAGE_ENGINES.LOCAL.UPLOADS_DIR```.
2. Jasekz\Laradrop\Events\FileWasDeleted - this is fired as soon as the file is deleted from the database.  At this point, the file and thumbnails still reside in the uploads dir, as defined by ```LARADROP_STORAGE_ENGINES.LOCAL.UPLOADS_DIR```.


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.



[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
