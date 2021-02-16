<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

// Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

//aktualizacja ścieżek dla obsługi autoryzacji 
Route::get('/','App\Http\Controllers\PostController@index')->name('home');
Route::get('/home', ['as'=>'home', 'uses'=>'App\Http\Controllers\PostController@index']);

Route::get('/logout', 'UserController@logout');

Route::prefix('auth')->group(function () {
    Auth::routes();
});

Route::middleware(['auth'])->group(function () {
    Route::get('new-post', 'App\Http\Controllers\PostController@create');
    Route::post('new-post', 'App\Http\Controllers\PostController@store');
    Route::get('edit/{slug}', 'App\Http\Controllers\PostController@edit');
    Route::post('update', 'App\Http\Controllers\PostController@update');
    Route::get('delete/{id}', 'App\Http\Controllers\PostController@destroy');
    Route::get('my-all-post', 'App\Http\Controllers\UserController@user_posts_all');
    Route::get('my-drafts', 'App\Http\Controllers\UserController@user_posts_draft');
    Route::post('comment/add', 'App\Http\Controllers\CommentController@store');
    Route::post('comment/delete/{id}', 'App\Http\Controllers\CommentController@destroy');
});

Route::get('user/{id}', 'App\Http\Controllers\UserController@profile')->where('id', '[0-9]+');
Route::get('user/{id}/posts', 'App\Http\Controllers\UserController@user_posts')->where('id', '[0-9]+');

Route::get('/{slug}', ['as'=>'post', 'uses'=>'App\Http\Controllers\PostController@show'])->where('slug', '[A-Za-z0-9-_]+');