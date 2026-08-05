<?php

namespace MasterSoft\Http\Controllers\Auth;

use MasterSoft\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Muestra el formulario de login (Detecta móvil y carga datos)
     */
    public function showFormLogin(Request $request)
    {
        $userAgent = $request->header('User-Agent');
        
        // Detectar móviles y tablets
        if (preg_match('/(android|iphone|ipad|mobile|tablet)/i', $userAgent)) {
            return redirect('/logmovil');
        }
        
        // Consultas a BD
        $empresas = DB::table('empresa')->get();
        $sucursales = DB::table('empresa_negocios')->get();
        $terminales = DB::table('configuracion_impresoras')->get();
        $usuarios = DB::table('users')->where('estusu', '1')->get();
        
        return view('auth.login', compact('empresas', 'sucursales', 'terminales', 'usuarios')); 
    }
        
    /**
     * Mostrar formulario de login móvil
     */
    public function showFormLoginMovil()
    {      
        $empresas = DB::table('empresa')->get();
        $sucursales = DB::table('empresa_negocios')->get();
        $terminales = DB::table('configuracion_impresoras')->get();
        
        $usuarios = DB::table('users')
            ->join('role_user', 'users.IdUsuario', '=', 'role_user.user_IdUsuario')
            ->where('role_user.role_id', 8)
            ->where('users.estusu', '1') 
            ->select('users.*')
            ->get();
        
        return view('auth.logmovil', compact('empresas', 'sucursales', 'terminales', 'usuarios')); 
    }

    /**
     * Procesa login móvil con código
     */
    public function loginMovil(Request $request)
    {   
        $request->validate([
            'usuario_id' => 'required',
            'codigo_movil' => 'required|numeric',
            'sucursal' => 'required',
        ], [
            'usuario_id.required' => 'Seleccionar un usuario',
            'codigo_movil.required' => 'Ingresar su código',
            'codigo_movil.numeric' => 'El código debe ser numérico',
            'sucursal.required' => 'Seleccionar sucursal',
        ]);

        $usuario = DB::table('users')
            ->where('IdUsuario', $request->usuario_id)
            ->where('estusu', '1')
            ->first();

        if (!$usuario) {
            return back()->withErrors(['usuario_id' => 'Usuario no encontrado o inactivo'])->withInput();
        }

        if ($usuario->codigo_movil != $request->codigo_movil) {
            return back()->withErrors(['codigo_movil' => 'Código incorrecto'])->withInput();
        }

        $sucursal = DB::table('empresa_negocios')->where('id_empresa_negocio', $request->sucursal)->first();

        DB::table('users')->where('IdUsuario', $usuario->IdUsuario)->update([
            'IdEmpresa' => $sucursal->IdEmpresa ?? null,
            'id_empresa_negocio' => $request->sucursal,
            'terminal' => $request->terminal ?? null
        ]);

        Auth::loginUsingId($usuario->IdUsuario);
        $request->session()->regenerate();

        return redirect()->intended($this->redirectTo);
    }

    /**
     * Procesar login móvil viejo (mantenido por compatibilidad)
     */
    public function loginMovilold(Request $request)
    {   
        $request->validate([
            'codigo_movil' => 'required|numeric',
            'password' => 'required|string',
            'sucursal' => 'required',
        ], [
            'codigo_movil.required' => 'Ingresar su código',
            'codigo_movil.numeric' => 'El código debe ser numérico',
            'password.required' => 'Ingresar su contraseña',
            'sucursal.required' => 'Seleccionar sucursal',
        ]);

        $usuario = DB::table('users')
            ->where('codigo_movil', $request->codigo_movil)
            ->where('estusu', '1')
            ->first();

        if (!$usuario) {
            return back()->withErrors(['codigo_movil' => 'Código no encontrado o usuario inactivo'])->withInput();
        }

        $sucursal = DB::table('empresa_negocios')->where('id_empresa_negocio', $request->sucursal)->first();

        DB::table('users')->where('IdUsuario', $usuario->IdUsuario)->update([
            'IdEmpresa' => $sucursal->IdEmpresa ?? null,
            'id_empresa_negocio' => $request->sucursal,
            'terminal' => $request->terminal ?? null
        ]);

        if (Auth::attempt(['email' => $usuario->email, 'password' => $request->password, 'estusu' => '1'])) {
            $request->session()->regenerate();
            return redirect()->intended($this->redirectTo);
        }

        return back()->withErrors(['password' => 'Contraseña incorrecta'])->withInput();
    }

    /**
     * Procesa el login principal de escritorio
     */
    public function login(Request $request)
    {   
        // 1. Control de intentos fallidos (Reemplazo del trait AuthenticatesUsers)
        $this->throttleLogin($request);

        // 2. Validación básica
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
            'sucursal' => 'required', 
        ]);

        // 3. Verificar usuario y estado activo
        $usuario = DB::table('users')
            ->where('email', $request->email)
            ->where('estusu', '1') 
            ->first();

        if (!$usuario) {
            $this->incrementLoginAttempts($request);
            return back()->withErrors(['email' => 'El usuario no existe, está inactivo o las credenciales son incorrectas'])->withInput();
        }

        // 4. Validar permiso de sucursal
        if ((int)$usuario->id_empresa_negocio !== (int)$request->sucursal) {
            return back()->withErrors(['sucursal' => 'No tienes permiso para acceder a esta sucursal.'])->withInput();
        }

        $sucursal = DB::table('empresa_negocios')->where('id_empresa_negocio', $request->sucursal)->first();
        $idEmpresaFinal = $request->has('empresa') ? $request->empresa : ($sucursal->IdEmpresa ?? null);

        // 5. Actualizar datos de sesión en la BD
        DB::table('users')->where('email', $usuario->email)->update([
            'IdEmpresa' => $idEmpresaFinal,
            'id_empresa_negocio' => $request->sucursal,
            'terminal' => $request->terminal ?? null
        ]);

        // 6. Intentar autenticación
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
            'estusu' => '1'
        ];

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            $this->clearLoginAttempts($request); // Limpiar intentos al tener éxito
            return redirect()->intended($this->redirectTo);
        }

        // 7. Fallo de autenticación (Contraseña incorrecta)
        $this->incrementLoginAttempts($request);
        return back()->withErrors(['email' => 'Contraseña incorrecta.'])->withInput();
    }

    /**
     * Cierra la sesión del usuario
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    // ==========================================
    // MÉTODOS AUXILIARES (Reemplazan al Trait eliminado)
    // ==========================================

    protected function throttleLogin(Request $request)
    {
        if (RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            $seconds = RateLimiter::availableIn($this->throttleKey($request));
            throw ValidationException::withMessages([
                'email' => 'Demasiados intentos de acceso. Por favor inténtelo de nuevo en ' . $seconds . ' segundos.',
            ]);
        }
    }

    protected function incrementLoginAttempts(Request $request)
    {
        RateLimiter::hit($this->throttleKey($request), 60); // Bloqueo de 60 segundos
    }

    protected function clearLoginAttempts(Request $request)
    {
        RateLimiter::clear($this->throttleKey($request));
    }

    protected function throttleKey(Request $request)
    {
        return Str::transliterate(Str::lower($request->input('email')) . '|' . $request->ip());
    }
}