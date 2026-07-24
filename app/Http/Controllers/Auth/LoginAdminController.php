<?php

namespace MasterSoft\Http\Controllers\Auth;

use MasterSoft\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use DB;


class LoginAdminController extends Controller
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
    protected $redirectTo = 'home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
      public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }


    public function showFormLogin(){
        
           return view('auth/login'); 
        
    }

    public function login(Request $request)
    {   
        $rucemp = $request->get('IdEmpresa');
        $email = $request->get('email');
        $rol = $request->get('rol');

        
        $this->validateAdmin($request);
       
        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            $this->username() => 'required|email',
            'password' => 'required|string',
            'IdEmpresa' => 'required|numeric|digits:11',
        ], [
            $this->username().'.required' =>'El correo electrónico es obligatorio',
             $this->username().'.email' =>'Ingresar un usuario válido', 
            'IdEmpresa.required' => 'RUC es un campo obligatorio',
            'IdEmpresa.numeric'=>'RUC es un campo numérico',
            'IdEmpresa.digits'=>'RUC es de 11 digitos',
            'password.required'=>'Ingresar su contraseña',
       ]);
    }

    protected function validateAdmin(Request $request)
    {
        $this->validate($request, [
            $this->username() => 'required|string',
            'password' => 'required|string'
        ], [
            $this->username().'.required' =>'El correo electrónico es obligatorio',
             $this->username().'.email' =>'Ingresar un usuario válido', 
       ]
        );
    }

      protected function credentials(Request $request)
    {

        return $request->only($this->username(),'password');
     
    }
	
	protected function Logout(){
    auth()->logout();

    return redirect('/admin');
  }

      protected function authenticated(Request $request, $user)
    {
        
    }



}
