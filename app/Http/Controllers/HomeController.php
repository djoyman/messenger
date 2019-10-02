<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Room;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
	}

	/**
	 * Get all active users from selected channel
	 * 
	 * @param Int $userId
	 * @return Response
	 */
	
	public function getUser( Int $userId ) {

		if (Auth::user()->is_admin !== 1) {
			return response()->json([
				'error' => 'You have no permissions here'
			]);
		}
		
		$data = [];

		$user = User::where('id', $userId)->first();

		$data[] = [
			'id' => $user->id,
			'name' => $user->name,
			'social_id' => $user->social_id,
			'banned' => $user->banned
		];

		if ( count( $data ) > 0 )
			return response()->json($data);
		else
			return response()->json([
				'error' => 'No user found'
			]);
	}

	 /**
	 * Get list of active channels from database
	 * 
	 * @return Array
	 */
	
	public function getChannels() {

		if (Auth::user()->is_admin !== 1) {
			return response()->json([
				'error' => 'You have no permissions here'
			]);
		}
		
		$data = [];

		$rooms = Room::get();
		
		foreach ($rooms as $room) {
			$channel = 'presence-chat_room.' . $room->id . ':members';
			if (Redis::exists( $channel ) == 1) {
				if ( ! empty(json_decode(Redis::get($channel), true)) ) {
					$data[] = [
						'title' => $channel,
						'alias' => $room->title,
					];
				}
			}
		}

		if ( count( $data ) > 0 )
			return response()->json($data);
		else
			return response()->json([
				'error' => 'No channels found'
			]);
	}

	/**
	 * Ban selected user
	 * 
	 * @param Int $userId
	 * @return Response
	 */

	public function banUser( Int $roomId, Int $userId ) {
		if (Auth::user()->is_admin !== 1) {
			return response()->json([
				'error' => 'You have no permissions here'
			]);
		}

		$user = User::where('id', $userId)->first();

		if ($user->banned === 0) {

			$setChannel = 'messages:room:' . $roomId;
			$valuesFromSet = Redis::zrange($setChannel, 0, -1); // ['message:1', 'message:2', ...]

			$messagesDeleted = 0;

			foreach($valuesFromSet as $msg) {			
				$hashKey = 'conversation:room:' . $roomId . $msg;
				$hashData = Redis::hgetall($hashKey);

				// If this $msg from candidate for ban
				if ( (int)$hashData['from'] === $user->id && $hashData['history_order'] === $msg ) {
					Redis::del($hashKey);
					Redis::zrem($setChannel, $msg);
					$messagesDeleted++;
				}
			}

			$user->banned = 1;
			$user->save();
			return response()->json([
				'result' => 'Пользователь с id: ' . $user->id . ' забанен. Удалено: ' . $messagesDeleted . ' сообщений.'
			]);
		} else {
			return response()->json([
				'error' => 'User already banned'
			]);
		}
	}
}
