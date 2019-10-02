<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Broadcast::channel('App.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });

//Broadcast::routes(['middleware' => ['web', 'auth', 'api']]);

Broadcast::channel('chat_room.{room_id}', function ( $user, $room_id ) {

	/*if ( $user->rooms->contains( $room_id ) ) { 

		return [ 'id' => $user->id , 'name' => $user->name ];

	}*/

	if ( $user->id === Auth::user()->id ) {
		return [ 'id' => $user->id , 'name' => $user->name, 'social_id' => $user->social_id ];
	}
});
