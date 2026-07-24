<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use MasterSoft\cuentasbancarias;
use DB;

class CuentasBancariasController extends Controller
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
          $cuentasbancarias = DB::tABLE('cuentasbancarias')
          ->join('moneda','moneda.moncod','cuentasbancarias.moncod')
          ->join('bancos','bancos.ban_id','cuentasbancarias.ban_id')
          ->join('tipo_cuentas','tipo_cuentas.tip_cuen_id','cuentasbancarias.tip_cuen_id')
          ->where('cuentasbancarias.IdEmpresa',$rucemp)
          ->paginate(10);
      }else{
           $cuentasbancarias = DB::tABLE('cuentasbancarias')
          ->join('moneda','moneda.moncod','cuentasbancarias.moncod')
          ->join('bancos','bancos.ban_id','cuentasbancarias.ban_id')
          ->join('tipo_cuentas','tipo_cuentas.tip_cuen_id','cuentasbancarias.tip_cuen_id')
          ->where('cuentasbancarias.IdEmpresa',$rucemp)
           ->paginate(10);
      }
      return view('empresas.cuentasbancarias.index',compact('cuentasbancarias','buscar'));
      }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    	$monedas = DB::tABLE('moneda')->get();
    	$bancos = DB::tABLE('bancos')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();
    	$tipocuentas = DB::tABLE('tipo_cuentas')->get();
        return view('empresas.cuentasbancarias.create',compact('monedas','bancos','tipocuentas'));
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
        $cuentabancaria = new cuentasbancarias;
        $cuentabancaria->id_empresa_negocio = Auth::user()->id_empresa_negocio;
		$cuentabancaria->IdEmpresa = $rucemp;
		$cuentabancaria->cuen_ban_com = $request->get('cuen_ban_com');
		$cuentabancaria->ban_id = $request->get('ban_id');
		$cuentabancaria->tip_cuen_id = $request->get('tip_cuen_id');
		$cuentabancaria->cuen_ban_num = $request->get('cuen_ban_num');
		$cuentabancaria->moncod = $request->get('moncod');
        $cuentabancaria->save();

      return Redirect::to('/cuentasbancarias');
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
    public function edit($id)
    {
          $cuentabancaria=cuentasbancarias::findOrFail($id);
          $monedas = DB::tABLE('moneda')->get();
          $bancos = DB::tABLE('bancos')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();
          $tipocuentas = DB::tABLE('tipo_cuentas')->get();

          return view('empresas.cuentasbancarias.edit',compact('cuentabancaria','monedas','bancos','tipocuentas'));
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
        $rucemp = trim(Auth::user()->IdEmpresa);
        $cuentabancaria= cuentasbancarias::findOrFail($id);
        $cuentabancaria->id_empresa_negocio = Auth::user()->id_empresa_negocio;
    		$cuentabancaria->IdEmpresa = $rucemp;
    		$cuentabancaria->ban_id = $request->get('ban_id');
    		$cuentabancaria->cuen_ban_com = $request->get('cuen_ban_com');
    		$cuentabancaria->tip_cuen_id = $request->get('tip_cuen_id');
    		$cuentabancaria->cuen_ban_num = $request->get('cuen_ban_num');
    		$cuentabancaria->moncod = $request->get('moncod');
        $cuentabancaria->update();

        return Redirect::to('/cuentasbancarias');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     public function destroy($id)
     {
         $cuentabancaria= cuentasbancarias::findOrFail($id);
         $cuentabancaria->delete();

         return Redirect::to('/cuentasbancarias');
     }
}
