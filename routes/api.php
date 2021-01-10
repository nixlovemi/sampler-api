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

/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/

Route::get('me', 'AuthController@me');
Route::get('unauthenticated', 'AuthController@unauthenticated')->name('unauthenticated');
Route::post('login', 'AuthController@login');
Route::post('logout', 'AuthController@logout');

Route::prefix('books')->group(function () {
    Route::get('/', 'BooksController@getAll')/*->where('page', '[0-9]+')*/; //@TODO Sampler: implement pagination
    Route::get('/{id}', 'BooksController@show')->where('id', '[0-9]+');
    Route::post('/', 'BooksController@store');
    Route::put('/{id}', 'BooksController@update')->where('id', '[0-9]+');
    Route::delete('/{id}', 'BooksController@destroy')->where('id', '[0-9]+');
    Route::patch('/{id}/{activate}', 'BooksController@activate')
            ->where('id', '[0-9]+')
            ->where('activate', '[0-1]+');
});

Route::prefix('users')->group(function () {
    Route::post('/', 'UsersController@store');
});