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

//USER
Route::group([
    'middleware' => ['jwt.verify'],
    'prefix' => 'auth'
], function ($router) {
    Route::post('logout', 'AuthController@logout')->name('logout');
    Route::post('refresh', 'AuthController@refresh')->name('refresh');
    Route::post('user', 'AuthController@me')->name('user-detail');
});
Route::group([
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', 'AuthController@login')->name('login');
});

//PACKAGE
Route::group([
    'middleware' => ['jwt.verify'],
    'prefix' => 'package'
], function ($router) {
    Route::get('/', 'PackageController@index')->name('package');
    Route::get('/{id}', 'PackageController@show')->name('package-detail');
    Route::post('/', 'PackageController@store')->name('package-post');
});

//ORGANIZATION
Route::group([
    'middleware' => ['jwt.verify'],
    'prefix' => 'organization'
], function ($router) {
    Route::get('/', 'OrganizationsController@index')->name('organizations');
});
