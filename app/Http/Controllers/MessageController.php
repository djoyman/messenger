<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\SendMessage;
use App\Message;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase;
use Kreait\Firebase\Messaging\CloudMessage;

class MessageController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth:api', [
			'except' => [
				'index',
			]
		]);
	}

	private function notify(string $roomId, string $title, string $body) {
		$serviceAccountPath = 'fileName.json';
		$messaging = (new Firebase\Factory())->withServiceAccount($serviceAccountPath)->createMessaging();            
		$message = CloudMessage::withTarget('topic', $roomId)
			->withNotification(\Kreait\Firebase\Messaging\Notification::create($title, $body));
		$messaging->send($message);
	}
	
	public function send( Request $request ) {

		if (Auth::user()->banned === 1) {
			return redirect()->to('/banned');
		}
		
		$data = $request->getContent();

		$result = Message::create( json_decode($data, true) );

		if ( count($result) > 0) {
			broadcast(new SendMessage( json_encode($result) ))->toOthers();
			$this->notify($result['room_id'], $result['name'], $result['content']);	
		}
	}

	public function getMessageHistory( Int $roomId ) {
		$messages = Message::loadAll($roomId);

		$currentPage = Paginator::resolveCurrentPage();
		$perPage = 50;

		$messages = array_reverse($messages);

		$currentMessages = array_slice($messages, $perPage * ($currentPage - 1), $perPage);

		return new Paginator($currentMessages, count($messages), $perPage, $currentPage);
	}
}
