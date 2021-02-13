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
Route::get('/', [App\Http\Controller\PostController::class, 'index'])->name('home');
Route::get('/home', ['as'=>'home', 'uses'=>'PostController@index']);

Route::get('/logout', 'UserController@logout');

Route::prefix('auth')->group(function () {
    Auth::routes();
});

Route::middleware(['auth'])->group(function () {
    Route::get('new-post', 'PostController@create');
    Route::post('new-post', 'PostController@store');
    Route::get('edit/{slug}', 'PostController@edit');
    Route::post('update', 'PostController@update');
    Route::get('delete/{id}', 'PostController@destroy');
    Route::get('my-all-post', 'UserController@user_posts_all');
    Route::get('my-drafts', 'UserController@user_posts_draft');
    Route::post('comment/add', 'CommentController@store');
    Route::post('comment/delete/{id}', 'CommentController@destroy');
});

Route::get('user/{id}', 'UserController@profile')->where('id', '[0-9]+');
Route::get('user/{id}/posts', 'UserController@user_posts')->where('id', '[0-9]+');

Route::get('/{slug}', ['as'=>'post', 'uses'=>'PostController@show'])->where('slug', '[A-Za-z0-9-_]+');