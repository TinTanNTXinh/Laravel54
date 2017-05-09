<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::group(['middleware' => []], function () {

    Route::group(['prefix' => 'docs'], function () {
        Route::get('/', function () {
            return view('docs.Manual');
        });
    });


    Route::group(['prefix' => 'artisan'], function () {
        Route::get('reset', 'ArtisanController@getCommandReset');
    });

    Route::group(['prefix' => 'file'], function () {
        Route::get('import-export', 'FileController@getImportExport');
        Route::get('download/{type}', 'FileController@getDownload');
        Route::post('import', 'FileController@postImport');
    });

    Route::group(['prefix' => 'test'], function () {
        Route::get('test', 'TestController@getTest');
    });

     Route::any('/{slug}', function () {
         return File::get(public_path() . '/home/index.html');
     })->where('slug', '([A-z\d-\/_.]+)?');

//    Route::get('/{any}', function ($any) {
//        return File::get(public_path() . '/home/index.html');
//    })->where('any', '.*');
});

