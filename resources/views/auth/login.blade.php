@extends('layouts.app')

@section('content')
	<chat-login :route="{{ json_encode(route('login')) }}"></chat-login>
@endsection
