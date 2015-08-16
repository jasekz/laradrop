# Laradrop

[![Software License][ico-license]](LICENSE.md)


This is a file manager using Dropzone.js for Laravel 5.  It provides basic functionality for managing, uploading,
and deleting files.

## Installation

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
LARADROP_INITIAL_UPLOADS_DIR=/myapp/storage
LARADROP_STORAGE_ENGINES.LOCAL.UPLOADS_DIR=/myapp/public/images
LARADROP_STORAGE_ENGINES.LOCAL.PUBLIC_LOCATION=/images
```
## Usage
This package requires Dropzone.js as well as some application level js.  Include these somewhere in your view:
``` php
<script src="/vendor/jasekz/laradrop/js/enyo.dropzone.js"></script>
<script src="/vendor/jasekz/laradrop/js/laradrop.js"></script>
```

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

jQuery('.laradrop').laradrop({
	onInsertCallback: function (src){
	  // this is called when the 'select' button is clicked on a thumbnail
		alert('File '+src+' selected.  Please implement onInsertCallback().');
	}
});
```
## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.



[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
