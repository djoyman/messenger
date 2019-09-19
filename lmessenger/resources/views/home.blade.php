@extends('layouts.app')

@section('content')
@guest
// Hello
@else
@if (Auth::user()->is_admin === 1)
	<admin-dashboard :token="{{ json_encode(Auth::user()->api_token) }}"></admin-dashboard>
@else
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

					You are logged in!
					
					<api-token :token="{{ json_encode(Auth::user()->api_token) }}"></api-token>
					<a href="{{ route('logout') }}"
						onclick="event.preventDefault();
										document.getElementById('logout-form').submit();">
						{{ __('Logout') }}
					</a>

					<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
						@csrf
					</form>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endguest
@endsection
