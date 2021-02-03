<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => ['cors']], function () {
    Route::post('/login', 'Auth\ApiAuthController@login')->name('login.api');
    Route::post('/registrar','Auth\ApiAuthController@registrar')->name('registrar.api');
    
});

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', 'Auth\ApiAuthController@logout')->name('logout.api');
    Route::post('/modificar','Auth\ApiAuthController@modificar')->name('modificar.api');
    Route::post('/verInformacion','Auth\ApiAuthController@verInformacion')->name('verInformacion.api');
    Route::post('/photo/search','PhotoController@search');
    Route::resource('/photo','PhotoController');
});