<?php 
Route::group(['middleware' => config('laradrop.middleware') ? config('laradrop.middleware') : null], function () {
    
    Route::get('laradrop/containers', [
        'as' => 'laradrop.containers',
        'uses' => '\jjfs85\Laradrop\Http\Controllers\LaradropController@getContainers'
    ]);
    
    Route::post('laradrop/move', [
        'as' => 'laradrop.move',
        'uses' => '\jjfs85\Laradrop\Http\Controllers\LaradropController@move'
    ]);
    
    Route::post('laradrop/create', [
        'as' => 'laradrop.create',
        'uses' => '\jjfs85\Laradrop\Http\Controllers\LaradropController@create'
    ]);
    
    Route::resource('laradrop', '\jjfs85\Laradrop\Http\Controllers\LaradropController');
    
});