<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\SendMessage;
use App\Message;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Image;

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

		$msg = $request->getContent();

		$data = json_decode($msg, true);

		$data['attachment'] = $this->saveImgOnServer($data['attachment']);

		$msg = json_encode($data);

		if (Message::create( $data )) {
			broadcast(new SendMessage( $msg ))->toOthers();
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

	private function saveImgOnServer( String $urlData ) {
		$link = '';
		try {
			if(strlen($urlData) > 128) {
				list($mime, $data)   = explode(';', $urlData);
				list(, $data)       = explode(',', $data);

				$ext = explode('/',$mime)[1];

				$fileName = mt_rand() . time() . '.' . $ext;
				$path = storage_path().'/app/public/uploads/images/' . $fileName;

				Image::make($urlData)->save($path);

				$link = '/chat/images/' . $fileName; // production
				//$link = '/storage/uploads/images/' . $fileName; // dev
			}
		}
		catch (\Exception $e) {
			$msg = $e;
		}
		return $link;
	}
}
