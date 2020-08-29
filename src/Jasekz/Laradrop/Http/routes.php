<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => config('laradrop.middleware') ? config('laradrop.middleware') : null], function () {

    Route::get('laradrop/containers', [
        'as' => 'laradrop.containers',
        'uses' => '\Jasekz\Laradrop\Http\Controllers\LaradropController@getContainers',
    ]);

    Route::post('laradrop/move', [
        'as' => 'laradrop.move',
        'uses' => '\Jasekz\Laradrop\Http\Controllers\LaradropController@move',
    ]);

    Route::post('laradrop/create', [
        'as' => 'laradrop.create',
        'uses' => '\Jasekz\Laradrop\Http\Controllers\LaradropController@create',
    ]);

    Route::resource('laradrop', '\Jasekz\Laradrop\Http\Controllers\LaradropController')->except(['create']);

});
