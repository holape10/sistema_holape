<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\Proveedor;
use MasterSoft\User;
use MasterSoft\tipo_documento_identidad;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use DB;

class ProveedorController extends Controller
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
      $ruc = trim(Auth::user()->IdEmpresa);
      if($request){
          $prov = trim($request->get('txtProv'));
          if(empty($prov)){
              $proveedores= DB::tABLE('proveedor')->where('IdEmpresa','=',$ruc)
              ->orderby('prov_raz','asc')
              ->paginate(10);
          } else{
          $proveedores= DB::tABLE('proveedor')
          ->where('IdEmpresa','=',$ruc)
          ->where(function ($query) use ($prov) {
                $query->where('prov_raz','like', '%'.$prov.'%')
                      ->orwhere('prov_ruc','=',$prov);
          })
          ->orderby('prov_raz','asc')
          ->paginate(10);
          }

          return view('empresas.proveedor.index',compact('proveedores','prov'));
       }
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */

   public function create()
   {
       $documentos = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderby('tdicod')->get();
       return view('empresas.proveedor.create',['documentos'=>$documentos]);
   }

   /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */

    public function store(Request $request)
    {
        $proveedor = new Proveedor;
        $proveedor->IdEmpresa = trim(Auth::user()->IdEmpresa);
        $proveedor->prov_ruc = $request->get('txtProvRuc');
        $proveedor->prov_raz = $request->get('txtProvRaz');
        $proveedor->tdicod = $request->get('cmbTdi');
        $proveedor->prov_con = $request->get('txtProvCon');
        $proveedor->prov_num_con = $request->get('txtProvNumCon');
        $proveedor->prov_cor = $request->get('txtProvCor');
        $proveedor->prov_dir = $request->get('txtProvDir');

        $proveedor->save();
        return Redirect::to('/proveedor');
    }

    public function edit($id)
    {
        $proveedor=Proveedor::findOrFail($id);
        $documentos=DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->get();
        return view('empresas.proveedor.edit',['proveedor'=>$proveedor,'documentos'=>$documentos]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $ids
     * @return \Illuminate\Http\Response
     */

     public function update(Request $request, $id)
     {
         $proveedor= Proveedor::findOrFail($id);
         $proveedor->IdEmpresa = trim(Auth::user()->IdEmpresa);
         $proveedor->tdicod = $request->get('cmbTdi');
         $proveedor->prov_ruc = $request->get('txtnum');
         $proveedor->prov_raz = $request->get('txtProvRaz');
         $proveedor->prov_con = $request->get('txtProvCon');
         $proveedor->prov_num_con = $request->get('txtProvNumCon');
         $proveedor->prov_cor = $request->get('txtProvCor');
         $proveedor->prov_dir = $request->get('txtProvDir');
         $proveedor->update();
         return Redirect::to('/proveedor');
     }

     public function destroy($id)
     {
         $proveedor= Proveedor::findOrFail($id);
         $proveedor->delete();

         return Redirect::to('/proveedor');
     }

     public function editarContrasena ($id)
     {
         $usuario = User::findOrFail($id);
         return view('empresas.proveedor.contrasena',['usuario'=>$usuario]);
     }

     public function cambiarContrasena(Request $request)
     {
         $idUsuario = $request->get('idUsuario');
         $usuario = User::findOrFail($idUsuario);
         $usuario->password = bcrypt($request->get('password'));
         $usuario->update();

         return Redirect::to('/MasterSoft');
     }

}
