<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

class Message extends Model
{
	/**
	 * Push message to Redis database
	 * 
	 * @param Array $data
	 * @return
	 */
	
	public static function create( Array $data ) {

		$messageLimit = 20;

		$setChannel = 'messages:room:' . $data['room_id'];

		$setLength = Redis::zcard($setChannel) + 1;

		$setValue = ':message:' . $setLength;

		$setScore = microtime(true);

		// Remove first message
		if (Redis::zcard($setChannel) >= $messageLimit) {

			$min = Redis::zrange($setChannel, 0, 0);
			if (Redis::zrem($setChannel, $min[0]) === 1) {
				if (Redis::zadd($setChannel, $setScore, $min[0]) === 1) {
					$hashKey = 'conversation:room:' . $data['room_id'] . $min[0];

					$values = array(
						'date' => $data['date'], 
						'name' => $data['name'],
						'content' => $data['content'],
						'from' => $data['from'],
						'room_id' => $data['room_id'],
						'attachment' => $data['attachment']
					);
	
					$result = Redis::hmset($hashKey, $values);
		
					if ($result != 'OK') {
						die('Database error: unable to add to Redis hash');
					}
				
				} else {
					die('Database error: unable to append to Redis sorted set');
				}

			} else {
				die('Database error: unable to delete Redis set value');
			}

		} else {
			if (Redis::zadd($setChannel, $setScore, $setValue) === 1) {
			
				$hashKey = 'conversation:room:' . $data['room_id'] . $setValue;
				
				$values = array(
					'date' => $data['date'], 
					'name' => $data['name'],
					'content' => $data['content'],
					'from' => $data['from'],
					'room_id' => $data['room_id'],
					'attachment' => $data['attachment']
				);

				$result = Redis::hmset($hashKey, $values);
	
				if ($result != 'OK') {
					die('Database error: unable to add to Redis hash');
				}
	
			} else {
				die('Database error: unable to add to Redis sorted set');
			}
		}

		return true;

	}

	/**
	 * Edit existing message
	 * 
	 * @param String $msg
	 * @return Boolean
	 */

	public function edit(String $msg) {



	}
	
	/**
	 * Get message list from Redis database
	 * 
	 * @param Int $roomId
	 * @return Array
	 */

	public static function loadAll( Int $roomId ) {
		$setChannel = 'messages:room:' . $roomId;
		$valuesFromSet = Redis::zrange($setChannel, 0, -1); // ['message:1', 'message:2', ...]
		$messages = [];

		foreach($valuesFromSet as $msg) {
			$hashKey = 'conversation:room:' . $roomId . $msg;
			$msgFields = Redis::hvals($hashKey); // ['from', 'name', ...]
			$messages[] = array(
				'date' => $msgFields[0], 
				'name' => $msgFields[1],
				'content' => $msgFields[2],
				'from' => $msgFields[3],
				'room_id' => $msgFields[4],
				'attachment' => $msgFields[5]
			);
		}

		return $messages;

	}
}
