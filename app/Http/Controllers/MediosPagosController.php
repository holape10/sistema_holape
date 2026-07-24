<?php

namespace MasterSoft\Http\Controllers;
use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\mediospagos;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Http\Requests\FacturaCreateFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;

class mediospagosController extends Controller
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
        $rucemp = trim(Auth::user()->IdEmpresa);

        $conceptos = DB::tABLE('conceptosbancarios')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();
       
        $bancos = DB::tABLE('bancos')
        ->leftjoin('cuentasbancarias','cuentasbancarias.ban_id','bancos.ban_id')
        ->leftjoin('tipo_cuentas','tipo_cuentas.tip_cuen_id','cuentasbancarias.tip_cuen_id')
        ->leftjoin('moneda','moneda.moncod','cuentasbancarias.moncod')
        ->where('cuentasbancarias.IdEmpresa',Auth::user()->IdEmpresa)
        ->get();

      if($request){
        $buscar = $request->get('buscar');

        if(empty($buscar)){
            $mediospagos = DB::tABLE('medios_pagos')
            ->leftjoin('cuentasbancarias','cuentasbancarias.cuen_ban_id','medios_pagos.cuen_ban_id')
             ->leftjoin('bancos','cuentasbancarias.ban_id','bancos.ban_id')
            ->leftjoin('tipo_cuentas','tipo_cuentas.tip_cuen_id','cuentasbancarias.tip_cuen_id')
            ->leftjoin('moneda','moneda.moncod','cuentasbancarias.moncod')
            ->leftjoin('conceptosbancarios','conceptosbancarios.concepto_id','medios_pagos.concepto_id')
            ->where('medios_pagos.IdEmpresa',Auth::user()->IdEmpresa)
            ->paginate(10);
        }else{
             $mediospagos = DB::tABLE('medios_pagos')
             ->leftjoin('cuentasbancarias','cuentasbancarias.cuen_ban_id','medios_pagos.cuen_ban_id')
             ->leftjoin('bancos','cuentasbancarias.ban_id','bancos.ban_id')
             ->leftjoin('tipo_cuentas','tipo_cuentas.tip_cuen_id','cuentasbancarias.tip_cuen_id')
             ->leftjoin('moneda','moneda.moncod','cuentasbancarias.moncod')
             ->leftjoin('conceptosbancarios','conceptosbancarios.concepto_id','medios_pagos.concepto_id')
             ->where('nom_med_pag',$buscar)
             ->where('medios_pagos.IdEmpresa',Auth::user()->IdEmpresa)
             ->paginate(10);
        }


        return view('empresas.mediospagos.index',compact('mediospagos','buscar','bancos','conceptos'));
        }
    }



    public function mediopredeterminado($medio){

        mediospagos::where('predeterminado','1')->update(['predeterminado'=>0]);

        $predeterminada = mediospagos::FindOrFail($medio);
        $predeterminada->predeterminado = '1';
        $predeterminada->update();

    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {   

         $conceptos = DB::tABLE('conceptosbancarios')->get();
        
         $bancos = DB::tABLE('bancos')
        ->leftjoin('cuentasbancarias','cuentasbancarias.ban_id','bancos.ban_id')
        ->leftjoin('tipo_cuentas','tipo_cuentas.tip_cuen_id','cuentasbancarias.tip_cuen_id')
        ->leftjoin('moneda','moneda.moncod','cuentasbancarias.moncod')
        ->where('cuentasbancarias.IdEmpresa',Auth::user()->IdEmpresa)
        ->get();

        return view('empresas.mediospagos.create',compact('bancos','conceptos'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $mediospagos = new mediospagos;
        $mediospagos->IdEmpresa = trim(Auth::user()->IdEmpresa);
        $mediospagos->cuen_ban_id = $request->get('cuen_ban_id');
        $mediospagos->concepto_id = $request->get('concepto_id');
        $mediospagos->nom_med_pag = $request->get('txtNomMedPag');
        $mediospagos->comision = $request->get('comision');
        $mediospagos->comision_mont = $request->get('comision_mont');
        $mediospagos->id_empresa_negocio = trim(Auth::user()->id_empresa_negocio);
 
       
        $mediospagos->save();
        return Redirect::to('/mediospagos');
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
    
       $conceptos = DB::tABLE('conceptosbancarios')->get();

      $mediospagos=mediospagos::findOrFail($id);
       $bancos = DB::tABLE('bancos')
        ->leftjoin('cuentasbancarias','cuentasbancarias.ban_id','bancos.ban_id')
        ->leftjoin('tipo_cuentas','tipo_cuentas.tip_cuen_id','cuentasbancarias.tip_cuen_id')
        ->leftjoin('moneda','moneda.moncod','cuentasbancarias.moncod')

        ->where('cuentasbancarias.IdEmpresa',Auth::user()->IdEmpresa)
        ->get();

      return view('empresas.mediospagos.edit',compact('mediospagos','bancos','conceptos'));
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
      $mediospagos=mediospagos::findOrFail($id);
      $mediospagos->IdEmpresa = trim(Auth::user()->IdEmpresa);
      $mediospagos->nom_med_pag = $request->get('txtNomMedPag');
      $mediospagos->cuen_ban_id = $request->get('cuen_ban_id');
      $mediospagos->concepto_id = $request->get('concepto_id');
      $mediospagos->comision = $request->get('comision');
      $mediospagos->comision_mont = $request->get('comision_mont');
      $mediospagos->id_empresa_negocio = trim(Auth::user()->id_empresa_negocio);
      $mediospagos->update();
      return Redirect::to('/mediospagos');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $mediospagos= mediospagos::findOrFail($id);
      $mediospagos->delete();

      return Redirect::to('/mediospagos');
    }
}
