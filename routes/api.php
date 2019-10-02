<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

Route::middleware(['auth:api', 'throttle:60,10'])->group(function () {

	Route::post('messages', 'MessageController@send');

	Route::get('messages/history/{roomId}', 'MessageController@getMessageHistory');

	Route::get('admin/users/{userId}', 'HomeController@getUser');
	Route::put('admin/users/ban/{roomId}/{userId}', 'HomeController@banUser');
	Route::get('admin/channels', 'HomeController@getChannels');

});