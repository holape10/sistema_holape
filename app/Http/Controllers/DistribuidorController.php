<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\distribuidor;
use MasterSoft\User;
use MasterSoft\tipo_documento_identidad;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use DB;

class DistribuidorController extends Controller
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
          $dist = trim($request->get('txtDist'));
          if(empty($dist)){
              $distribuidores= DB::tABLE('distribuidor')->where('IdEmpresa','=',$ruc)
              ->orderby('NomDistribuidor','asc')
              ->paginate(10);
          } else{
          $distribuidores= DB::tABLE('distribuidor')
          ->where('IdEmpresa','=',$ruc)
          ->where(function ($query) use ($dist) {
                $query->where('NomDistribuidor','like', '%'.$dist.'%')
                      ->orwhere('RucDistribuidor','=',$dist);
          })
          ->orderby('NomDistribuidor','asc')
          ->paginate(10);
          }

          return view('empresas.distribuidor.index',compact('distribuidores','dist'));
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
       return view('empresas.distribuidor.create',['documentos'=>$documentos]);
   }

   /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */

    public function store(Request $request)
    {
        $distribuidor = new distribuidor;
        $distribuidor->IdEmpresa = trim(Auth::user()->IdEmpresa);
        $distribuidor->RucDistribuidor = $request->get('txtDistRuc');
        $distribuidor->NomDistribuidor = $request->get('txtDistRaz');
        $distribuidor->tdicod = $request->get('cmbTdi');
        $distribuidor->ContDistribuidor = $request->get('txtDistCon');
        $distribuidor->ContNumDistribuidor = $request->get('txtDistNumCon');
        $distribuidor->CorDistribuidor = $request->get('txtDistCor');
        $distribuidor->DirDistribuidor = $request->get('txtDistDir');

        $distribuidor->save();
        return Redirect::to('/distribuidor');
    }

    public function edit($id)
    {
        $distribuidor=distribuidor::findOrFail($id);
        $documentos=DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->get();
        return view('empresas.distribuidor.edit',['distribuidor'=>$distribuidor,'documentos'=>$documentos]);
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
         $distribuidor= distribuidor::findOrFail($id);
         $distribuidor->IdEmpresa = trim(Auth::user()->IdEmpresa);
         $distribuidor->tdicod = $request->get('cmbTdi');
         $distribuidor->RucDistribuidor = $request->get('txtnum');
         $distribuidor->NomDistribuidor = $request->get('txtDistRaz');
         $distribuidor->ContNumDistribuidor = $request->get('txtDistCon');
         $distribuidor->ContNumDistribuidor = $request->get('txtDistNumCon');
         $distribuidor->CorDistribuidor = $request->get('txtDistCor');
         $distribuidor->DirDistribuidor = $request->get('txtDistDir');
         $distribuidor->EstDistribuidor = $request->get('txtDistEst');
         $distribuidor->update();
         return Redirect::to('/distribuidor');
     }

     public function destroy($id)
     {
         $distribuidor= distribuidor::findOrFail($id);
         $distribuidor->delete();

         return Redirect::to('/distribuidor');
     }

     public function editarContrasena ($id)
     {
         $usuario = User::findOrFail($id);
         return view('empresas.distribuidor.contrasena',['usuario'=>$usuario]);
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
