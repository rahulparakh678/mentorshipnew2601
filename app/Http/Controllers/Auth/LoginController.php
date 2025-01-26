<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;


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
    //protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function redirectTo()
    {

        if(!auth()->user()->verified){

            // return ('hello');
            return view('auth.login1')->with('error', 'Email verification pending');
        }

        // if (!auth()->user()->verified) {
        //     return redirect()->back()->with('error', 'Email verification pending');
        // }
        


        if (auth()->user()->is_mentor) {
        return '/mentor/dashboard';


        }

        if (auth()->user()->is_admin) {
            return '/admin';
        }

        if (auth()->user()->is_mentee) {
            return '/mentee/dashboard';

        }

        return '/';


    }
    
    public function logout()
    {
        Auth::logout();

        // Optionally, you can redirect the user to a specific page after logout
        return redirect()->route('login');
    }
}
