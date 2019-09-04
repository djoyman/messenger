<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Socialite;
use App\User;
use Auth;
use Cookie;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
		$this->middleware('guest')->except(['logout', 'access']);
	}

	public function access($id) {

		Cookie::queue(Cookie::make('room_access', $id, 60));

		if (Auth::check()) {
			$room = '/room/' . Cookie::get('room_access');
			return redirect()->to($room);
		}

		return redirect()->to('/login');
	}
	
	/**
     * Redirect the user to the authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider( $service )
    {
        return Socialite::driver($service)->redirect();
    }

    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleGoogleProviderCallback()
    {
        try {
            $user = Socialite::driver('google')->user();
        } catch (Exception $e) {
			return redirect('/login');
		}
		
		// check if they're an existing user
		$existingUser = User::where('email', $user->email)->first();
		
		if($existingUser){
            // log them in
            Auth::login($existingUser, true);
        } else {
            // create a new user
            $newUser                  = new User;
            $newUser->name            = $user->name;
            $newUser->email           = $user->email;
            $newUser->google_id       = $user->id;
            $newUser->avatar          = $user->avatar;
            $newUser->avatar_original = $user->avatar_original;
            $newUser->save();
            Auth::login($newUser, true);
		}
		
		$room = '/room/' . Cookie::get('room_access');

		return redirect()->to($room);
	}
	
	/**
     * Obtain the user information from VK.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleVkProviderCallback()
    {
        try {
            $user = Socialite::driver('vkontakte')->user();
        } catch (Exception $e) {
			return redirect('/login');
		}
		
		// check if they're an existing user
		$existingUser = User::where('email', $user->email)->first();
		
		if($existingUser){
            // log them in
            Auth::login($existingUser, true);
        } else {
            // create a new user
            $newUser                  = new User;
            $newUser->name            = $user->name;
            $newUser->email           = $user->email;
            $newUser->google_id       = $user->id;
            $newUser->avatar          = $user->avatar;
            $newUser->avatar_original = $user->avatar_original;
            $newUser->save();
            Auth::login($newUser, true);
		}
		
        return redirect()->to('/home');
    }
}
