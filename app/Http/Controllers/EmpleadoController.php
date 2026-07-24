<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\Cliente;
use MasterSoft\User;
use MasterSoft\empleado;
use MasterSoft\Role;
use MasterSoft\role_user;
use MasterSoft\tipo_documento_identidad;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use MasterSoft\Http\Requests\ClienteFormRequest;
use MasterSoft\Http\Requests\ClienteUpdateFormRequest;
use Illuminate\Support\Facades\Auth;
use DB;


class EmpleadoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
       
            
            $bus_emp = trim($request->get('bus_emp'));
           
            $empleados = DB::tABLE('empleado')
            ->select('empleado.emp_id','emp_nom','est_des','emp_ape_mat','emp_ape_pat','emp_dir','emp_tel','emp_cel','emp_num_doc','email','codigo_movil','est_color','roles.description as rol_nombre', 'asistencia')
            ->leftjoin('estados','estados.est_cod','empleado.est_cod')
            ->leftjoin('users','users.emp_id','empleado.emp_id')
            ->leftjoin('roles','roles.id','empleado.rol_id')
            ->where(function ($query) use ($bus_emp) {
                if(!empty($bus_emp)){
                    $query->Where('emp_nom','like','%'.$bus_emp.'%')
                    ->orwhere('emp_num_doc','=',$bus_emp);
                }
            })
            ->orderby('emp_nom','asc')
            ->get();

      
            return view('empresas.empleado.index',["empleados"=>$empleados,"bus_emp"=>$bus_emp]);
      
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {   
     

        $estados = DB::tABLE('estados')->orderby('est_des','asc')->get();
        $sexo = DB::tABLE('sexo')->get();
        $roles = DB::tABLE('roles')->orderby('description','asc')->get();
        $negocios = DB::tABLE('empresa_negocios')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();

        $documentos = DB::tABLE('tipo_documento_identidad')->where('empleado','1')->where('tdiest','=','Activo')->orderby('tdicod')->get();

        return view('empresas.empleado.create',compact('documentos','estados','sexo','roles','negocios'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
{   
 
    $bus_emp = DB::TABLE('empleado')->where('emp_num_doc',$request->get('emp_num_doc'))->first();

    if(!empty($bus_emp)){
        if($request->ajax()){
            return response()->json(['estado'=>'error','mensaje'=>'El Empleado Existe']);
        }
    }

    if(empty($request->get('emp_num_doc'))){
        if($request->ajax()){
          return response()->json(['estado'=>'error','mensaje'=>'Ingresar Número de Documento']);
        }
    }

    if(empty($request->get('emp_nom'))){
        if($request->ajax()){
          return response()->json(['estado'=>'error','mensaje'=>'Ingresar Nombre']);
        }
    }

    if(empty($request->get('emp_ape_pat'))){
        if($request->ajax()){
          return response()->json(['estado'=>'error','mensaje'=>'Ingresar Apellido Paterno']);
        }
    }

    // Validar que si es rol Mozo, debe tener código móvil
        $rol_seleccionado = DB::table('roles')->where('id', $request->get('rol_id'))->first();
        if(!empty($rol_seleccionado) && stripos($rol_seleccionado->description, 'mozo') !== false) {
            if(empty(trim($request->get('codigo_movil')))){
                if($request->ajax()){
                    return response()->json(['estado'=>'error','mensaje'=>'El Código Móvil es obligatorio para el rol Mozo']);
                }
            }
        }

    // Validar código móvil (si se ingresó)
    if(!empty(trim($request->get('codigo_movil')))){
        
        $bus_codigo = DB::TABLE('users')->where('codigo_movil',$request->get('codigo_movil'))->first();

        if(!empty($bus_codigo)){
            if($request->ajax()){
                return response()->json(['estado'=>'error','mensaje'=>'El Código Móvil ya existe - Elegir Otro']);
            }
        }
    }

    // Validar email y contraseña (si se ingresó email)
    if(!empty(trim($request->get('email')))){

        $bus_usu = DB::TABLE('users')->where('email',$request->get('email'))->first();

        if(!empty($bus_usu)){
            if($request->ajax()){
                return response()->json(['estado'=>'error','mensaje'=>'El Usuario de Acceso Existe - Elegir Otro']);
            }
        }

        if(empty($request->get('password'))){
            if($request->ajax()){
                return response()->json(['estado'=>'error','mensaje'=>'Ingresar Contraseña']);
            }
        }

        if(empty($request->get('password_confirmation'))){
            if($request->ajax()){
                return response()->json(['estado'=>'error','mensaje'=>'Confirmar Contraseña']);
            }
        }

         if(trim($request->get('password_confirmation')) != trim($request->get('password'))){
            if($request->ajax()){
                return response()->json(['estado'=>'error','mensaje'=>'Las Contraseñas No Coinciden']);
            }
        }
    }

    $empleado= new empleado;
    $empleado->emp_nom=$request->get('emp_nom');
    $empleado->emp_ape_mat=$request->get('emp_ape_mat');
    $empleado->emp_ape_pat=$request->get('emp_ape_pat');
    $empleado->emp_dir=$request->get('emp_dir');
    $empleado->emp_fec_nac=$request->get('emp_fec_nac');
    $empleado->emp_cor=$request->get('emp_cor');
    $empleado->emp_tel=$request->get('emp_tel');
    $empleado->emp_cel=$request->get('emp_cel');
    $empleado->sex_cod=$request->get('sex_cod');
    $empleado->est_cod=$request->get('est_cod');
    $empleado->emp_num_doc=$request->get('emp_num_doc');
    $empleado->tdicod=$request->get('tdicod');
    $empleado->rol_id=$request->get('rol_id');
    $empleado->asistencia = $request->get('asistencia');
    $empleado->id_empresa_negocio=$request->get('id_empresa_negocio');
    $empleado->save();

    if(!empty(trim($request->get('email')))){

        $usuario = new User;
        $usuario->name = $request->get('emp_nom');
        $usuario->apeusu = $request->get('emp_ape_pat').' '.$request->get('emp_ape_mat');
        $usuario->estusu = $request->get('est_cod');
        $usuario->IdEmpresa = Auth::user()->IdEmpresa;
        $usuario->email = $request->get('email');
        $usuario->password = bcrypt($request->get('password'));
        $usuario->codigo_movil = !empty($request->get('codigo_movil')) ? $request->get('codigo_movil') : null;
        $usuario->id_empresa_negocio = $request->get('id_empresa_negocio');
        $usuario->emp_id = $empleado->emp_id;
        $usuario->save();

        $role_user = new role_user;
        $role_user->role_id= $request->get('rol_id');
        $role_user->user_IdUsuario= $usuario->IdUsuario;
        $role_user->save();
    }
 
    if($request->ajax()){
        return response()->json(['estado'=>'success','mensaje'=>'REGISTRADO CORRECTAMENTE']);
    }
 
}


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function consultarDocumento(Request $request)
    {
        $documento = trim($request->get('documento'));
        // Coloca aquí tu token de apiperu.dev
        $token = 'c7c656604942b0a6df5fa225835e99eb7376cf841d38781b91f651f72e03cc09'; 

        if (strlen($documento) === 8) {
            $params = json_encode(['dni' => $documento]);
            $url = "https://apiperu.dev/api/dni";
        } elseif (strlen($documento) === 11) {
            $params = json_encode(['ruc' => $documento]);
            $url = "https://apiperu.dev/api/ruc";
        } else {
            return response()->json(['error' => 'El documento debe tener 8 dígitos (DNI) o 11 dígitos (RUC).']);
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ],
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return response()->json(['error' => 'Error de conexión: ' . $err]);
        }

        return response()->json(json_decode($response, true));
    }

    public function edit($id)
    {
        $empleado = empleado::leftjoin('users','users.emp_id','empleado.emp_id')
        ->select('empleado.emp_id','emp_nom','emp_ape_mat','emp_ape_pat','emp_dir','emp_tel','emp_cel','emp_num_doc','est_cod','sex_cod','emp_cor','email','codigo_movil','rol_id','asistencia')
        ->findOrFail($id);
        $documentos = DB::tABLE('tipo_documento_identidad')->where('empleado','1')->where('tdiest','=','Activo')->orderby('tdicod')->get();

        $roles = DB::tABLE('roles')->orderby('description','asc')->get();

        $negocios = DB::tABLE('empresa_negocios')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();

        $estados = DB::tABLE('estados')->orderby('est_des','asc')->get();
        $sexo = DB::tABLE('sexo')->get();

       
        return view('empresas.empleado.edit',compact('empleado','documentos','estados','sexo','roles','negocios'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    public function update(Request $request)
{   

    $id = $request->get('id');

    $bus_emp = DB::TABLE('empleado')->where('emp_id','!=',$id)->where('emp_num_doc',$request->get('emp_num_doc'))->get();

    if(count($bus_emp) > '0'){
        if($request->ajax()){
            return response()->json(['estado'=>'error','mensaje'=>'El Empleado Existe']);
        }
    }

    if(empty($request->get('emp_num_doc'))){
        if($request->ajax()){
          return response()->json(['estado'=>'error','mensaje'=>'Ingresar Número de Documento']);
        }
    }

    if(empty($request->get('emp_nom'))){
        if($request->ajax()){
          return response()->json(['estado'=>'error','mensaje'=>'Ingresar Nombre']);
        }
    }

    if(empty($request->get('emp_ape_pat'))){
        if($request->ajax()){
          return response()->json(['estado'=>'error','mensaje'=>'Ingresar Apellido Paterno']);
        }
    }

    // Validar que si es rol Mozo, debe tener código móvil
    $rol_seleccionado = DB::table('roles')->where('id', $request->get('rol_id'))->first();
    if(!empty($rol_seleccionado) && stripos($rol_seleccionado->description, 'mozo') !== false) {
        if(empty(trim($request->get('codigo_movil')))){
            if($request->ajax()){
                return response()->json(['estado'=>'error','mensaje'=>'El Código Móvil es obligatorio para el rol Mozo']);
            }
        }
    }

    // Validar código móvil (si se ingresó)
    if(!empty(trim($request->get('codigo_movil')))){
        
        $bus_codigo = DB::TABLE('users')->where('emp_id','!=',$id)->where('codigo_movil',$request->get('codigo_movil'))->get();

        if(count($bus_codigo) > '0'){
            if($request->ajax()){
                return response()->json(['estado'=>'error','mensaje'=>'El Código Móvil ya existe - Elegir Otro']);
            }
        }
    }

    // Validar email (si se ingresó)
    if(!empty(trim($request->get('email')))){

        $bus_usu = DB::TABLE('users')->where('emp_id','!=',$id)->where('email',$request->get('email'))->get();

        if(count($bus_usu) > '0'){
            if($request->ajax()){
                return response()->json(['estado'=>'error','mensaje'=>'El Usuario de Acceso Existe - Elegir Otro']);
            }
        }

        if(!empty($request->get('password'))){
            
            if(empty($request->get('password_confirmation'))){
                if($request->ajax()){
                    return response()->json(['estado'=>'error','mensaje'=>'Confirmar Contraseña']);
                }
            }

            if(trim($request->get('password_confirmation')) != trim($request->get('password'))){
                if($request->ajax()){
                    return response()->json(['estado'=>'error','mensaje'=>'Las Contraseñas No Coinciden']);
                }
            }

        }
    }

    $empleado= empleado::findOrFail($id);
    $empleado->emp_nom=$request->get('emp_nom');
    $empleado->emp_ape_mat=$request->get('emp_ape_mat');
    $empleado->emp_ape_pat=$request->get('emp_ape_pat');
    $empleado->emp_dir=$request->get('emp_dir');
    $empleado->emp_fec_nac=$request->get('emp_fec_nac');
    $empleado->emp_cor=$request->get('emp_cor');
    $empleado->emp_tel=$request->get('emp_tel');
    $empleado->emp_cel=$request->get('emp_cel');
    $empleado->sex_cod=$request->get('sex_cod');
    $empleado->est_cod=$request->get('est_cod');
    $empleado->emp_num_doc=$request->get('emp_num_doc');
    $empleado->tdicod=$request->get('tdicod');
    $empleado->rol_id=$request->get('rol_id');
    $empleado->asistencia = $request->get('asistencia');
    $empleado->id_empresa_negocio=$request->get('id_empresa_negocio');
    $empleado->update();

    if(!empty(trim($request->get('email')))){

        $bus_dat_emp = DB::TABLE('users')->where('emp_id',$id)->first();

        if(!empty($bus_dat_emp)){

            $usuario = User::findOrFail($bus_dat_emp->IdUsuario);
            $usuario->name = $request->get('emp_nom');
            $usuario->apeusu = $request->get('emp_ape_pat').' '.$request->get('emp_ape_mat');
            $usuario->estusu = $request->get('est_cod');
            $usuario->IdEmpresa = Auth::user()->IdEmpresa;
            $usuario->email = $request->get('email');
            
            // Solo actualizar password si se ingresó uno nuevo
            if(!empty($request->get('password'))){
                $usuario->password = bcrypt($request->get('password'));
            }
            
            $usuario->codigo_movil = !empty($request->get('codigo_movil')) ? $request->get('codigo_movil') : null;
            $usuario->id_empresa_negocio = $request->get('id_empresa_negocio');
            $usuario->emp_id = $empleado->emp_id;
            $usuario->update(); 

            $role_user = role_user::where('user_IdUsuario',$usuario->IdUsuario)->update(['role_id'=>$request->get('rol_id')]);

        }else{

            $usuario = new User;
            $usuario->name = $request->get('emp_nom');
            $usuario->apeusu = $request->get('emp_ape_pat').' '.$request->get('emp_ape_mat');
            $usuario->estusu = $request->get('est_cod');
            $usuario->IdEmpresa = Auth::user()->IdEmpresa;
            $usuario->email = $request->get('email');
            $usuario->password = bcrypt($request->get('password'));
            $usuario->codigo_movil = !empty($request->get('codigo_movil')) ? $request->get('codigo_movil') : null;
            $usuario->id_empresa_negocio = $request->get('id_empresa_negocio');
            $usuario->emp_id = $empleado->emp_id;
            $usuario->save();

            $role_user = new role_user;
            $role_user->role_id= $request->get('rol_id');
            $role_user->user_IdUsuario= $usuario->IdUsuario;
            $role_user->save();

        }
    }

    if($request->ajax()){
        return response()->json(['estado'=>'success','mensaje'=>'ACTUALIZADO CORRECTAMENTE']);
    }
 
}


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $empleado = empleado::findOrFail($id);
        $empleado->delete();

        return Redirect::to('/empleado');
    }
  

}
