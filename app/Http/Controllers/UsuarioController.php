<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\Empresa;
use MasterSoft\User;
use MasterSoft\Role;
use MasterSoft\role_user;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use MasterSoft\Http\Requests\UsuarioFormRequest;
use MasterSoft\Http\Requests\UsuarioUpdateFormRequest;


use DB;


class UsuarioController extends Controller
{


    public function __construct()
    {
        $this->middleware('auth');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request){

            $query=trim($request->get('searchText'));
            $usuarios=DB::tABLE('users as u')
            ->join('empresa as e','u.IdEmpresa','=','e.IdEmpresa')
            ->select('u.IdUsuario','e.NomEmpresa','u.name','u.apeusu','u.email','u.estusu')
            ->where('u.name','like','%'.$query.'%')
            ->orderBy('nomEmpresa','asc')  
            ->paginate(2000);
            return view('administrador.usuarios.index',["usuarios"=>$usuarios,"searchText"=>$query]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
         $empresas=DB::tABLE('empresa')->get();
         $negocios = DB::tABLE('empresa_negocios')->where('IdEmpresa',$empresas->first()->IdEmpresa)->get();
         $roles = DB::tABLE('roles')->orderby('description','asc')->get();
         return view('administrador.usuarios.create',compact('empresas','negocios','roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


    public function store(Request $request)
    {
        $usuario = new User;
        $usuario->name = $request->get('name');
        $usuario->apeusu = $request->get('apeUsuario');
        $usuario->estusu = 'Activo';
        $usuario->IdEmpresa = $request->get('IdEmpresa');
        $usuario->email = $request->get('email');
        $usuario->password = bcrypt($request->get('password'));
        $usuario->id_empresa_negocio = $request->get('idnegocio');
        $usuario->IdIngreso = $request->get('IdIngreso');
        $usuario->save();

        $role_user = new role_user;
        $role_user->role_id= $request->get('rol');
        $role_user->user_IdUsuario= $usuario->IdUsuario;
        $role_user->save();
        
       

        return Redirect::to('administrador/usuarios');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
       
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
         //$usuario=User::findOrFail($id);
         $usuario =  DB::tABLE('users')->where('IdUsuario',$id)->leftjoin('role_user','user_IdUsuario','IdUsuario')->first();
         
        $empresas=DB::tABLE('empresa')->get();
        $negocios = DB::tABLE('empresa_negocios')->where('IdEmpresa',$usuario->IdEmpresa)->get();
        $roles = DB::tABLE('roles')->orderby('description','asc')->get();

        return view('administrador.usuarios.edit',compact('usuario','empresas','negocios','roles'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $usuario= User::findOrFail($id);
        $usuario->name = $request->get('name');
        $usuario->apeusu = $request->get('apeUsuario');
        $usuario->IdEmpresa = $request->get('IdEmpresa');
        $usuario->email = $request->get('email');
        $usuario->estusu = $request->get('estUsuario');
        $usuario->id_empresa_negocio = $request->get('idnegocio');

        if(!empty(trim($request->get('password')))){

             $usuario->password = bcrypt($request->get('password'));
        }
       

        $usuario->IdIngreso = $request->get('IdIngreso');
        $usuario->update();

        $buscarrol = DB::tABLE('role_user')->where('user_IdUsuario',$usuario->IdUsuario)->first();

        $role_user = role_user::findOrFail($buscarrol->id);
        $role_user->role_id= $request->get('rol');
        $role_user->user_IdUsuario= $usuario->IdUsuario;
        $role_user->update();

        return Redirect::to('administrador/usuarios');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $usuario= User::findOrFail($id);
        $usuario->delete();

        return Redirect::to('administrador/usuarios');
    }

    public function editarContrasena ($id)
    {
        $usuario = User::findOrFail($id);
        return view('administrador.usuarios.contrasena',['usuario'=>$usuario]);
    }

       public function cambiarContrasena(Request $request)
    {
        $idUsuario = $request->get('idUsuario');
        $usuario = User::findOrFail($idUsuario);
        if(!empty($request->get('password'))){
          $usuario->password = bcrypt($request->get('password'));  
        }
        
        if(!empty($request->get('passwordadmin'))){
            $usuario->password_admin = bcrypt($request->get('passwordadmin'));
        }
      
        $usuario->update();

        return Redirect::to('administrador/usuarios');
    }
}
