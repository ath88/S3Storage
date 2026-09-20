<?php

Route::group(['middleware' => 'web', 'prefix' => \Helper::getSubdirectory(), 'namespace' => 'Modules\S3Storage\Http\Controllers'], function()
{
    Route::get('/', 'S3StorageController@index');
});
