@extends('layouts.app')

@section('content')
	@guest
		// Nothing
	@else
		<chat-app :token="{{ json_encode(Auth::user()->api_token) }}" :room="{{ $room }}" :user="{{ Auth::user() }}"></chat-app>
	@endguest
@endsection