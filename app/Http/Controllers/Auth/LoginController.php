<?php

namespace MasterSoft\Http\Controllers\Auth;

use MasterSoft\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use DB;


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
    | */

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
        $userAgent = request()->header('User-Agent');
        
        // Detectar móviles y tablets
        $isMobile = preg_match('/(android|iphone|ipad|mobile|tablet)/i', $userAgent);
        
        if ($isMobile) {
            return redirect('/logmovil');
        }
        
        // Consultas a BD
        $empresas = DB::table('empresa')->get(); // <-- AGREGADO: Obtener empresas
        $sucursales = DB::table('empresa_negocios')->get();
        $terminales = DB::table('configuracion_impresoras')->get();
        $usuarios = DB::table('users')->where('estusu', '1')->get();
        
        return view('auth/login', compact('empresas', 'sucursales','terminales', 'usuarios')); 
    }
        
    // Mostrar formulario de login móvil
    public function showFormLoginMovil()
    {      
        $empresas = DB::table('empresa')->get(); // <-- AGREGADO
        $sucursales = DB::table('empresa_negocios')->get();
        $terminales = DB::table('configuracion_impresoras')->get();
        
        $usuarios = DB::table('users')
            ->join('role_user', 'users.IdUsuario', '=', 'role_user.user_IdUsuario')
            ->where('role_user.role_id', 8)
            ->where('users.estusu', '1') 
            ->select('users.*')
            ->get();
        
        return view('auth/logmovil', compact('empresas', 'sucursales','terminales', 'usuarios')); 
    }

    public function loginMovil(Request $request)
    {   
        // Validar usuario seleccionado y código
        $this->validate($request, [
            'usuario_id' => 'required',
            'codigo_movil' => 'required|numeric',
            'sucursal' => 'required',
        ], [
            'usuario_id.required' => 'Seleccionar un usuario',
            'codigo_movil.required' => 'Ingresar su código',
            'codigo_movil.numeric' => 'El código debe ser numérico',
            'sucursal.required' => 'Seleccionar sucursal',
        ]);

        // AJUSTE: Buscar usuario por ID pero asegurando que esté activo (estusu = 1)
        $usuario = DB::table('users')
            ->where('IdUsuario', $request->get('usuario_id'))
            ->where('estusu', '1') // <-- Bloqueo si está inactivo
            ->first();

        if(is_null($usuario)){
            return back()->withErrors(['usuario_id' => 'Usuario no encontrado o inactivo'])->withInput();
        }

        // Verificar código móvil
        if($usuario->codigo_movil != $request->get('codigo_movil')){
            return back()->withErrors(['codigo_movil' => 'Código incorrecto'])->withInput();
        }

        // Actualizar sucursal y terminal del usuario
        $sucursal = DB::table('empresa_negocios')->where('id_empresa_negocio', $request->get('sucursal'))->first();

        DB::table('users')->where('IdUsuario', $usuario->IdUsuario)
        ->update([
            'IdEmpresa' => $sucursal->IdEmpresa,
            'id_empresa_negocio' => $request->get('sucursal'),
            'terminal' => $request->get('terminal')
        ]);

        // Autenticar directamente sin verificar contraseña
        auth()->loginUsingId($usuario->IdUsuario);

        return redirect()->intended($this->redirectTo);
    }

    // Procesar login móvil viejo (mantenido por compatibilidad si lo usas)
    public function loginMovilold(Request $request)
    {   
        $this->validate($request, [
            'codigo_movil' => 'required|numeric',
            'password' => 'required|string',
            'sucursal' => 'required',
        ], [
            'codigo_movil.required' => 'Ingresar su código',
            'codigo_movil.numeric' => 'El código debe ser numérico',
            'password.required' => 'Ingresar su contraseña',
            'sucursal.required' => 'Seleccionar sucursal',
        ]);

        // AJUSTE: Buscar por código móvil pero asegurar que esté activo
        $usuario = DB::table('users')
            ->where('codigo_movil', $request->get('codigo_movil'))
            ->where('estusu', '1') // <-- Bloqueo si está inactivo
            ->first();

        if(is_null($usuario)){
            return back()->withErrors(['codigo_movil' => 'Código no encontrado o usuario inactivo'])->withInput();
        }

        $sucursal = DB::table('empresa_negocios')->where('id_empresa_negocio', $request->get('sucursal'))->first();

        DB::table('users')->where('IdUsuario', $usuario->IdUsuario)
        ->update([
            'IdEmpresa' => $sucursal->IdEmpresa,
            'id_empresa_negocio' => $request->get('sucursal'),
            'terminal' => $request->get('terminal')
        ]);

        if (auth()->attempt([
            'email' => $usuario->email,
            'password' => $request->get('password')
        ])) {
            return redirect()->intended($this->redirectTo);
        }

        return back()->withErrors(['password' => 'Contraseña incorrecta'])->withInput();
    }

    public function login(Request $request)
    {   
        $usuario = DB::table('users')
            ->where('email', $request->get('email'))
            ->where('estusu', '1') 
            ->first();

        if(is_null($usuario)){
            auth()->logout();
            return redirect('/')->withErrors(['email' => 'El usuario no existe, está inactivo o las credenciales son incorrectas']);
        }

        // login usuario de acuerdo a sucursal - si fuese sistema full contabilidad borrar 
        if ((int)$usuario->id_empresa_negocio !== (int)$request->get('sucursal')) {
            return back()->withErrors(['sucursal' => 'No tienes permiso para acceder a esta sucursal.'])->withInput();
        }

        $sucursal = DB::table('empresa_negocios')->where('id_empresa_negocio', $request->get('sucursal'))->first();

        // Al guardar, priorizamos el IdEmpresa seleccionado si se envía, de lo contrario usamos el vinculado a la sucursal.
        $idEmpresaFinal = $request->has('empresa') ? $request->get('empresa') : $sucursal->IdEmpresa;

        DB::table('users')->where('email', $usuario->email)
        ->update([
            'IdEmpresa' => $idEmpresaFinal,
            'id_empresa_negocio' => $request->get('sucursal'),
            'terminal' => $request->get('terminal')
        ]);

        $this->validateLogin($request);

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        $credentials = $this->credentials($request);
        $credentials['estusu'] = '1';

        if (auth()->attempt($credentials, $request->filled('remember'))) {
            return $this->sendLoginResponse($request);
        }

        $this->incrementLoginAttempts($request);
        return $this->sendFailedLoginResponse($request);
    }

    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            'password' => 'required|string',
        ], [
            'password.required' => 'Ingresar su contraseña',
        ]);
    }

    protected function credentials(Request $request)
    {
        return $request->only($this->username(), 'password', 'IdEmpresa');
    }
    
    protected function Logout(){
        auth()->logout();
        return redirect('/');
    }

    protected function authenticated(Request $request, $user)
    {
        
    }
}