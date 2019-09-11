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

Route::middleware(['auth:api', 'throttle:60,10'])->group(function () {

	Route::post('messages', 'MessageController@send');

	Route::get('messages/history/{roomId}', 'MessageController@getMessageHistory');

});