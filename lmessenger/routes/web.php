<?php

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

Route::get('/', function () {
    return view('/home');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/room/{room}', 'RoomController@index');

Route::get('/chat/access/{id}', 'Auth\LoginController@access');

Route::get('login/{service}', 'Auth\LoginController@redirectToProvider');
Route::get('login/google/callback', 'Auth\LoginController@handleGoogleProviderCallback');
Route::get('login/vk/callback', 'Auth\LoginController@handleVkProviderCallback');
