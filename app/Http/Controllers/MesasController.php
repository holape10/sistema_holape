<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use MasterSoft\mesas;
use DB;

class MesasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $rucemp = trim(Auth::user()->IdEmpresa);
    if($request){
      $buscar = $request->get('buscar');

      if(empty($buscar)){
          $mesas = DB::tABLE('mesas')
          ->leftjoin('pisos','pisos.pis_id','mesas.pis_id')
          ->leftjoin('users','users.IdUsuario','mesas.IdUsuario')
          
          ->where('mesas.id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->paginate(100);
      }else{
           $mesas = DB::tABLE('mesas')
           ->leftjoin('pisos','pisos.pis_id','mesas.pis_id')
           ->leftjoin('users','users.IdUsuario','mesas.IdUsuario')
           ->where('mes_nom',$buscar)
           
           ->where('mesas.id_empresa_negocio',Auth::user()->id_empresa_negocio)
           ->paginate(10);
      }
      return view('empresas.mesas.index',compact('mesas','buscar'));
      }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $pisos = DB::tABLE('pisos')
         ->where('suc_id',Auth::user()->id_empresa_negocio)
        ->where('emp_id',Auth::user()->IdEmpresa)
        ->get();

             $usuarios = DB::tABLE('users')
        ->leftjoin('role_user','role_user.user_IdUsuario','users.IdUsuario')
        ->where('role_id','5')->get();


        return view('empresas.mesas.create',compact('pisos','usuarios'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rucemp = trim(Auth::user()->IdEmpresa);
        $mesa=new mesas;
        $mesa->mes_nom = $request->get('txtMesNom') ;
        $mesa->pis_id = $request->get('pis_id');
        $mesa->IdUsuario = $request->get('usuario');
        $mesa->IdEmpresa= $rucemp;
        $mesa->id_empresa_negocio= Auth::user()->id_empresa_negocio;
        $mesa->save();
        
      return Redirect::to('/mesa');
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

    public function buscar_mesas(Request $request,$id){
      $mesas = DB::tABLE('mesas')->where('pis_id',$id)->where('ind_union','0')->get();     
      $vista = view('empresas.mesas.listar_mesas',compact('mesas'))->render();
      if($request->ajax()){
        return response()->json(['vista'=>$vista]);
      }
    }

     public function buscar_mesas_mobil(Request $request,$id){
      $mesas = DB::tABLE('mesas')->where('pis_id',$id)->where('ind_union','0')->get();     
      $vista = view('empresas.mesas.listar_mesas_mobil',compact('mesas'))->render();
      if($request->ajax()){
        return response()->json(['vista'=>$vista]);
      }
    }


    public function buscar_mesas_desocupadas(Request $request){

      $mesas = DB::tABLE('mesas')
      ->where('mes_est','Libre')
      ->get();
     
      $vista = view('empresas.mesas.listar_mesas_desocupadas',compact('mesas'))->render();

      if($request->ajax()){
        return response()->json(['vista'=>$vista]);
      }


    }

    public function buscar_mesas_desunir(Request $request,$mes_id){

      $mesas = DB::tABLE('mesas_union')->select('mesas_union.mes_id','mes_nom')
      ->join('mesas','mesas.mes_id','mesas_union.mes_id')
      ->where('mes_id_act',$mes_id)
      ->where('mes_uni_est','APERTURADO')
      ->get();
     
      $vista = view('empresas.mesas.listar_mesas_desunir',compact('mesas'))->render();

      if($request->ajax()){
        return response()->json(['vista'=>$vista]);
      }


    }

      public function buscar_mesas_desocupadas_unir(Request $request,$id=0){

      $mesas = DB::tABLE('mesas')
      ->where('mes_est','Libre')
      ->get();

      $mesas_unidas = '';

      if($id!=0){
        $mesas_unidas = DB::tABLE('mesas_union')->select('mesas_union.mes_id','mes_nom')
        ->join('mesas','mesas.mes_id','mesas_union.mes_id')
        ->where('mes_id_act',$id)
        ->where('mes_uni_est','APERTURADO')
        ->get(); 
      }
 
     
      $vista = view('empresas.mesas.listar_mesas_desocupadas_unir',compact('mesas','mesas_unidas','id'))->render();

      if($request->ajax()){
        return response()->json(['vista'=>$vista]);
      }


    }


    public function buscar_mesas_caja(Request $request,$id){

      $mesas = DB::tABLE('mesas')->where('pis_id',$id)->where('ind_union','0')->get();
     
      $vista = view('empresas.mesas.listar_mesas_caja',compact('mesas'))->render();

      if($request->ajax()){
        return response()->json(['vista'=>$vista]);
      }


    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        
        $mesa = DB::tABLE('mesas')->leftjoin('users','users.IdUsuario','mesas.IdUsuario')->where('mes_id',$id)->first();

          $pisos = DB::tABLE('pisos')
          ->where('emp_id',Auth::user()->IdEmpresa)
          ->where('suc_id',Auth::user()->id_empresa_negocio)
          ->get();


          $usuarios = DB::tABLE('users')
          ->leftjoin('role_user','role_user.user_IdUsuario','users.IdUsuario')
          ->where('role_id','5')->get();

          return view('empresas.mesas.edit',compact('mesa','pisos','usuarios'));
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
          $mesa= mesas::findOrFail($id);
          $rucemp = trim(Auth::user()->IdEmpresa);
          $mesa->mes_nom = $request->get('txt_pronom');
          $mesa->IdUsuario = $request->get('usuario');
          $mesa->id_empresa_negocio= Auth::user()->id_empresa_negocio;
          $mesa->pis_id = $request->get('pis_id');
          $mesa->update();
          return Redirect::to('/mesa');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     public function destroy($id)
     {
         $mesa= mesas::findOrFail($id);
         $mesa->delete();

         return Redirect::to('/mesa');
     }

    public function unir_mesas(Request $request){

      $mes_id_act_unir = $request->get('mes_id_act_unir');
      $ped_id_act_unir = $request->get('ped_id_act_unir');
      $mesas_unir = $request->get('mesas_unir');

      $buscar = DB::tABLE('mesas_union')->where('mes_id_act',$mes_id_act_unir)->where('mes_uni_est','APERTURADO')->get();

      if(!empty($buscar)){
        foreach($buscar as $bus){
            DB::tABLE('mesas')->where('mes_id',$bus->mes_id)->update(['mes_est'=>'Libre','ind_union'=>'0']);
        }

        DB::tABLE('mesas_union')->where('mes_id_act',$mes_id_act_unir)->where('mes_uni_est','APERTURADO')->delete();
      }
    


      foreach($mesas_unir as $mu){
        DB::tABLE('mesas_union')->insert(['mes_id'=>$mu,'mes_id_act'=>$mes_id_act_unir,'ped_id'=>$ped_id_act_unir,'mes_uni_est'=>'APERTURADO']);

        if($mu != $mes_id_act_unir){
            DB::tABLE('mesas')->where('mes_id',$mu)->update(['mes_est'=>'Ocupado','ind_union'=>'1']);
        }
       

      }

      DB::tABLE('mesas')->where('mes_id',$mes_id_act_unir)->update(['mes_est'=>'Ocupado']);


      return Redirect::to('/consola');



     }

      public function desunir_mesas(Request $request){

      $mes_id_act_unir = $request->get('mes_id_act_desunir');
      $ped_id_act_unir = $request->get('ped_id_act_desunir');
      $mesas_unir = $request->get('mesas_unir');

      $buscar = DB::tABLE('mesas_union')->where('mes_id_act',$mes_id_act_unir)->where('mes_uni_est','APERTURADO')->get();

      foreach($buscar as $bus){
          DB::tABLE('mesas')->where('mes_id',$bus->mes_id)->update(['mes_est'=>'Libre']);
      }

      DB::tABLE('mesas_union')->where('mes_id_act',$mes_id_act_unir)->where('mes_uni_est','APERTURADO')->delete();

      foreach($mesas_unir as $mu){

        DB::tABLE('mesas_union')->insert(['mes_id'=>$mu,'mes_id_act'=>$mes_id_act_unir,'ped_id'=>$ped_id_act_unir,'mes_uni_est'=>'APERTURADO']);

        if($mu != $mes_id_act_unir){
            DB::tABLE('mesas')->where('mes_id',$mu)->update(['mes_est'=>'Ocupado','ind_union'=>'1']);
        }
       

      }

      DB::tABLE('mesas')->where('mes_id',$mes_id_act_unir)->update(['mes_est'=>'Ocupado']);


      return Redirect::to('/consola');



     }

}
