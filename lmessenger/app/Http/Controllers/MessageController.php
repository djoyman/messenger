<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\SendMessage;
use App\Message;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

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
	
	public function send( Request $request ) {

		$data = $request->getContent();

		$result = Message::create( json_decode($data, true) );

		if (count($result) > 0) {
			broadcast(new SendMessage( json_encode($result) ))->toOthers();
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
