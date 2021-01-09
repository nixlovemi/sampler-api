<?php

use Illuminate\Http\Request;

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

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/

Route::get('me', 'AuthController@me');
Route::get('unauthenticated', 'AuthController@unauthenticated')->name('unauthenticated');
Route::post('login', 'AuthController@login');
// Route::post('register', 'AuthController@register');
Route::post('logout', 'AuthController@logout');

Route::prefix('books')->group(function () {
    Route::get('/', 'BooksController@getAll')/*->where('page', '[0-9]+')*/; //@TODO implement pagination
    Route::get('/{id}', 'BooksController@show')->where('id', '[0-9]+');
    Route::post('/', 'BooksController@store');
});