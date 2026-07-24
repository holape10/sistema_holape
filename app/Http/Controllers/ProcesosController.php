<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\procesos;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Http\Requests\FacturaCreateFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;

class ProcesosController extends Controller
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
      if($request){
        $buscar = $request->get('buscar');

        if(empty($buscar)){
            $procesos = DB::tABLE('procesos')->paginate(10);
        }else{  
             $procesos = DB::tABLE('procesos')->where('proc_nom',$buscar)->paginate(10);
        }
        return view('empresas.procesos.index',compact('procesos','buscar'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('empresas.procesos.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $procesos = new procesos;
        $procesos->proc_cod = $request->get('proc_cod');
        $procesos->proc_nom = $request->get('proc_nom');
        $procesos->save();

        return Redirect::to('/procesos');
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
      $procesos=procesos::findOrFail($id);
      return view('empresas.procesos.edit',compact('procesos'));
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
      $procesos=procesos::findOrFail($id);
      $procesos->proc_cod = $request->get('proc_cod');
      $procesos->proc_nom = $request->get('proc_nom');
      $procesos->update();
      return Redirect::to('/procesos');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $procesos= procesos::findOrFail($id);
      $procesos->delete();

      return Redirect::to('/procesos');
    }

    public function operaciones(Request $request){

    	$negocios = DB::tABLE('empresa_negocios')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();
    	$sucursal = $request->get('sucursal');
    	$documento = $request->get('documento');
    	$cliente = $request->get('cliente');
        $servicio = $request->get('servicio');

        $servicios = DB::tABLE('procesos')->get();

        $operadores = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        ->where('role_id','7')
        ->get();

        $maquinas = DB::tABLE('maquinas')->get();

        $contar = count($maquinas);


        $ser = substr($documento,strpos($documento,'-')-4,4);
        $num = substr($documento,strpos($documento,'-')+1,8);


    	$fec_ini = $request->get('fec_ini');
    	$fec_fin = $request->get('fec_fin');


        if(empty($fecin)){

          $fec_ini = now()->modify('first day of this month')->format('Y-m-d');
          $fec_fin = now()->modify('last day of this month')->format('Y-m-d');

        }


    	
    		$comprobantes = DB::tABLE('procesos_comprobante')
            ->join('procesos','procesos.proc_id','procesos_comprobante.proc_id')
            ->join('cpe_cabecera','procesos_comprobante.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
    		->join('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
            ->leftjoin('users','users.IdUsuario','procesos_comprobante.operador')
	    	->where('procesos','1')
	    	->where('ccafem','>=',$fec_ini)
	    	->where('ccafem','<=',$fec_fin)
	    	/*->where(function ($query1) use ($estado){
		            if(!empty($estado)){
		                $query1->where('est_ope','0')
		                    ->orWhere('est_ope','1');
		            }
	        })*/
	        ->where(function ($query1) {
		           
		        $query1->where('cpe_cabecera.tdocod','03')
		               ->orWhere('cpe_cabecera.tdocod','01');
		            
	        })
	        ->whereNull('ccabaj')
	    	->where(function ($query1) use ($cliente){
		            if(!empty($cliente)){
		                $query1->where('cpe_cabecera.ccanom','like','%'.$cliente.'%')
		                    ->orWhere('cpe_cabecera.ccandi','=',$cliente);
		            }
	        })
            ->where(function ($query1) use ($servicio){
                    if(!empty($servicio)){
                        $query1->where('procesos.proc_id',$servicio);
                    }
            })
	        ->where(function ($query2) use ($documento,$ser,$num){
	            if(!empty($documento)){
	                $query2->where('serdoc',$ser)
	                    ->Where('numdoc',$num);
	            }
	        })
	    	->get();
    	
    	

    	return view('empresas.procesos.operaciones',compact('operadores','maquinas','contar','servicios','comprobantes','fec_ini','fec_fin','negocios','sucursal','cliente','documento'));

    }

    public function iniciarprocesos($id){

    	$procesos = DB::tABLE('procesos')->get();

    	DB::tABLE('cpe_cabecera')
    	->where('IdCpe_cabecera',$id)
    	->update([
    		'est_ope'=>'1',
    		'fec_ini_proc'=>now()->format('Y-m-d h:i:s'),

    	]);

    	foreach($procesos as $proc){

    		DB::tABLE('procesos_comprobante')
    		->insert([
    			'proc_id'=>$proc->proc_id,
    			'IdCpe_cabecera'=>$id
    		]);
    	}



    	return Redirect::to('/operaciones');
    }

    public function mostrarprocesos($id){

    	$cabecera = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$id)->first();

    	$procesos = DB::tABLE('procesos_comprobante')
    	->join('procesos','procesos.proc_id','procesos_comprobante.proc_id')
    	->join('cpe_cabecera','procesos_comprobante.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
    	->leftjoin('users','users.IdUsuario','procesos_comprobante.operador')
    	->where('cpe_cabecera.IdCpe_cabecera',$id)
        ->orderby('procesos.proc_id','asc')
    	->get();

    	$operadores = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        ->where('role_id','7')
        ->get();

        $maquinas = DB::tABLE('maquinas')->get();

        $contar = count($maquinas);

    	return view('empresas.procesos.mostrarprocesos',compact('procesos','cabecera','operadores','maquinas','contar'));

    }

    public function iniciarproceso(Request $request){

    	$id = $request->get('proc_comp_id');
        $maquinas = $request->get('maquina');
    	$operador = $request->get('operador');

    	DB::tABLE('procesos_comprobante')->where('proc_comp_id',$id)
    	->update([
    		'proc_fec_ini'=>now()->format('Y-m-d h:i:s'),
    		'proc_comp_est'=>'1',
    		'operador'=>$operador
    	]);

        if(!empty($maquinas)){
            foreach ($maquinas as $maquina) {
              DB::tABLE('procesos_maquinas')->insert(['proc_comp_id'=>$id,'maq_id'=>$maquina]);
            }
        }
       
   		
   		$buspro = DB::tABLE('procesos_comprobante')->where('proc_comp_id',$id)->first();

   		return Redirect::to('/operaciones');
   }

    public function finalizarproceso(Request $request){

    	$id=$request->get('proc_comp_id');

    	$hora_fin = now()->format('Y-m-d h:i:s');

    	DB::tABLE('procesos_comprobante')->where('proc_comp_id',$id)
    	->update(['proc_fec_fin'=>$hora_fin,
    		'proc_comp_est'=>'2'
    	]);

    	$buspro = DB::tABLE('procesos_comprobante')->where('proc_comp_id',$id)->first();
   		
    	$pendientes = DB::tABLE('procesos_comprobante')
    	->where('IdCpe_cabecera',$buspro->IdCpe_cabecera)
    	->where('proc_comp_est','!=','2')->get();

    	if(count($pendientes)=='0'){
    		DB::tABLE('cpe_cabecera')
	    	->where('IdCpe_cabecera',$buspro->IdCpe_cabecera)
	    	->update([
	    		'est_ope'=>'2',
	    		'fec_fin_proc'=>$hora_fin,

	    	]);
    	}

    	return Redirect::to('/operaciones');

    }

      public function observacionproceso(Request $request){

    	$id=$request->get('proc_comp_id');

    	DB::tABLE('procesos_comprobante')->where('proc_comp_id',$id)
    	->update(['proc_obs'=>$request->get('observacion')
    	]);

    	$buspro = DB::tABLE('procesos_comprobante')->where('proc_comp_id',$id)->first();
   		

    	return Redirect::to('/operaciones');

    }

    public function detalleorden($venta){

    $cabecera = DB::tABLE('cpe_cabecera')
    ->join('tipo_documento','tipo_documento.tdocod','cpe_cabecera.tdocod')
    ->where('IdCpe_cabecera',$venta)->first();

    $detalle = DB::tABLE('cpe_cabecera')
    ->join('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
    ->where('cpe_cabecera.IdCpe_cabecera',$venta)
    ->get();

    return view('empresas.procesos.detalleventa',compact('cabecera','detalle'));

 }

}
