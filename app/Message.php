<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;
use Image;

use function GuzzleHttp\json_decode;

class Message extends Model
{
	/**
	 * Push message to Redis database
	 * 
	 * @param Array $data
	 * @return Array
	 */
	
	public static function create( Array $data ) {
		
		$messageLimit = 500000;

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

					
					if ($data['attachment:source'] !== '')
						$data['attachment:source'] = Message::saveImgOnServer($data['attachment:source']);

					$values = array(
						'date' => $data['date'], 
						'name' => $data['name'],
						'content' => $data['content'],
						'from' => $data['from'],
						'history_order' => $min[0],
						'room_id' => $data['room_id'],
						'attachment:source' => $data['attachment:source'],
						'attachment:width' => $data['attachment:width'],
						'attachment:height'=> $data['attachment:height']
					);

					Redis::hmset($hashKey, $values);

					return $values;
				
				} else {
					//die('Database error: unable to append to Redis sorted set');
					return [];
				}

			} else {
				//die('Database error: unable to delete Redis set value');
				return [];
			}

		} else {
			if (Redis::zadd($setChannel, $setScore, $setValue) === 1) {
			
				$hashKey = 'conversation:room:' . $data['room_id'] . $setValue;

				if ($data['attachment:source'] !== '')
					$data['attachment:source'] = Message::saveImgOnServer($data['attachment:source']);
				
				$values = array(
					'date' => $data['date'], 
					'name' => $data['name'],
					'content' => $data['content'],
					'from' => $data['from'],
					'history_order' => $setValue,
					'room_id' => $data['room_id'],
					'attachment:source' => $data['attachment:source'],
					'attachment:width' => $data['attachment:width'],
					'attachment:height'=> $data['attachment:height']
				);

				Redis::hmset($hashKey, $values);

				return $values;
	
			} else {
				//die('Database error: unable to add to Redis sorted set');
				return [];
			}
		}
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
			$hashData = Redis::hgetall($hashKey);
			
			$messages[] = $hashData;
		}

		return $messages;

	}

	/**
	 * Delete all messages by user
	 * 
	 * @param Int $userId
	 * @param Int $roomId
	 * @return Int
	 */

	public static function deleteAllByUserId( Int $userId, Int $roomId ) {
		$setChannel = 'messages:room:' . $roomId;
		$valuesFromSet = Redis::zrange($setChannel, 0, -1);
		$deletedMessages = 0;

		foreach($valuesFromSet as $msg) {
			$hashKey = 'conversation:room:' . $roomId . $msg;
			$fromUser = (int) Redis::hget($hashKey, 'from');
			
			if ($fromUser === $userId) {
				Redis::del($hashKey);
				Redis::zrem($setChannel, $msg);
				$deletedMessages++;
			}
		}

		return $deletedMessages;
	}

	private static function saveImgOnServer( String $urlData ) {
		$link = '';
		try {
			if(strlen($urlData) > 128) {
				list($mime, $data)   = explode(';', $urlData);
				list(, $data)       = explode(',', $data);

				$ext = explode('/',$mime)[1];

				$fileName = mt_rand() . time() . '.' . $ext;
				$path = storage_path().'/app/public/uploads/images/' . $fileName;

				Image::make($urlData)->save($path);

				$link = '/images/' . $fileName; // production
				//$link = '/storage/uploads/images/' . $fileName; // dev
			}
		}
		catch (\Exception $e) {
			$msg = $e;
		}
		return $link;
	} 
}
