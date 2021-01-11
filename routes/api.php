<?php
use Symfony\Component\HttpFoundation\Response;

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

// @TODO Sampler: is this the best way?
Route::fallback(function ()
{
    $response = lpApiResponse(true, 'Route not found.');
    return response()->json($response, Response::HTTP_NOT_FOUND);
});

Route::get('me', 'AuthController@me');
Route::get('unauthenticated', 'AuthController@unauthenticated')->name('unauthenticated');
Route::post('login', 'AuthController@login');
Route::post('logout', 'AuthController@logout');

Route::prefix('books')->group(function ()
{
    Route::get('/', 'BooksController@getAll'); //@TODO Sampler: implement pagination
    Route::get('/{id}', 'BooksController@show')->where('id', '[0-9]+');
    Route::post('/', 'BooksController@store');
    Route::put('/{id}', 'BooksController@update')->where('id', '[0-9]+');
    Route::delete('/{id}', 'BooksController@destroy')->where('id', '[0-9]+');
    Route::patch('/{id}/{activate}', 'BooksController@activate')
            ->where('id', '[0-9]+')
            ->where('activate', '[0-1]+');
    
    Route::post('/checkin/{id}', 'BooksController@checkIn')->where('id', '[0-9]+');
    Route::post('/checkout/{id}', 'BooksController@checkOut')->where('id', '[0-9]+');
});

Route::prefix('users')->group(function ()
{
    Route::get('/', 'UsersController@getAll'); //@TODO Sampler: implement pagination
    Route::get('/{id}', 'UsersController@show')->where('id', '[0-9]+');
    Route::post('/', 'UsersController@store');
    Route::put('/{id}', 'UsersController@update')->where('id', '[0-9]+');
    Route::delete('/{id}', 'UsersController@destroy')->where('id', '[0-9]+');
    Route::patch('/{id}/{activate}', 'UsersController@activate')
            ->where('id', '[0-9]+')
            ->where('activate', '[0-1]+');
});