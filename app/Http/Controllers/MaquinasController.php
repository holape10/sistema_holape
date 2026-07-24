<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\maquinas;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Http\Requests\FacturaCreateFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;

class MaquinasController extends Controller
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
            $maquinas = DB::tABLE('maquinas')->paginate(10);
        }else{  
             $maquinas = DB::tABLE('maquinas')->where('maq_nom',$buscar)->paginate(10);
        }
        return view('empresas.maquinas.index',compact('maquinas','buscar'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('empresas.maquinas.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $maquinas = new maquinas;
        $maquinas->maq_cod = $request->get('maq_cod');
        $maquinas->maq_nom = $request->get('maq_nom');
        $maquinas->save();

        return Redirect::to('/maquinas');
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
      $maquinas=maquinas::findOrFail($id);
      return view('empresas.maquinas.edit',compact('maquinas'));
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
      $maquinas=maquinas::findOrFail($id);
      $maquinas->maq_cod = $request->get('maq_cod');
      $maquinas->maq_nom = $request->get('maq_nom');
      $maquinas->update();
      return Redirect::to('/maquinas');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $maquinas= maquinas::findOrFail($id);
      $maquinas->delete();

      return Redirect::to('/maquinas');
    }

    public function operaciones(Request $request){

    	$negocios = DB::tABLE('empresa_negocios')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();
    	$sucursal = $request->get('sucursal');
    	$documento = $request->get('documento');
    	$cliente = $request->get('cliente');


        $ser = substr($documento,strpos($documento,'-')-4,4);
        $num = substr($documento,strpos($documento,'-')+1,8);


    	$fec_ini = $request->get('fec_ini');
    	$fec_fin = $request->get('fec_fin');

    	if(empty($fec_ini)){
    		$comprobantes = DB::tABLE('cpe_cabecera')
    		 ->join('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
    		 ->where(function ($query1) {
		           
		        $query1->where('cpe_cabecera.tdocod','03')
		               ->orWhere('cpe_cabecera.tdocod','01');
		            
	        })
    		 ->whereNull('ccabaj')
	    	->where('maquinas','1')
	    	/*->where(function ($query1){
		            
		                $query1->where('est_ope','0')
		                    ->orWhere('est_ope','1');
		            
	        })*/
	    	->get();
    	}else{
    		$comprobantes = DB::tABLE('cpe_cabecera')
    		 ->join('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
	    	->where('maquinas','1')
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
	        ->where(function ($query2) use ($documento,$ser,$num){
	            if(!empty($documento)){
	                $query2->where('serdoc',$ser)
	                    ->Where('numdoc',$num);
	            }
	        })
	    	->get();
    	}
    	

    	return view('empresas.maquinas.operaciones',compact('comprobantes','fec_ini','fec_fin','negocios','sucursal','cliente','documento'));

    }

    public function iniciarmaquinas($id){

    	$maquinas = DB::tABLE('maquinas')->get();

    	DB::tABLE('cpe_cabecera')
    	->where('IdCpe_cabecera',$id)
    	->update([
    		'est_ope'=>'1',
    		'fec_ini_proc'=>now()->format('Y-m-d h:i:s'),

    	]);

    	foreach($maquinas as $proc){

    		DB::tABLE('maquinas_comprobante')
    		->insert([
    			'proc_id'=>$proc->proc_id,
    			'IdCpe_cabecera'=>$id
    		]);
    	}



    	return Redirect::to('/operaciones');
    }

    public function mostrarmaquinas($id){

    	$cabecera = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$id)->first();

    	$maquinas = DB::tABLE('maquinas_comprobante')
    	->join('maquinas','maquinas.proc_id','maquinas_comprobante.proc_id')
    	->join('cpe_cabecera','maquinas_comprobante.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
    	->leftjoin('users','users.IdUsuario','maquinas_comprobante.operador')
    	->where('cpe_cabecera.IdCpe_cabecera',$id)
        ->orderby('maquinas.proc_id','asc')
    	->get();

    	$operadores = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        ->where('role_id','7')
        ->get();

    	return view('empresas.maquinas.mostrarmaquinas',compact('maquinas','cabecera','operadores'));

    }

    public function iniciarproceso(Request $request){

    	$id = $request->get('proc_comp_id');
    	$operador = $request->get('operador');

    	DB::tABLE('maquinas_comprobante')->where('proc_comp_id',$id)
    	->update([
    		'proc_fec_ini'=>now()->format('Y-m-d h:i:s'),
    		'proc_comp_est'=>'1',
    		'operador'=>$operador
    	]);
   		
   		$buspro = DB::tABLE('maquinas_comprobante')->where('proc_comp_id',$id)->first();

   		return Redirect::to('/mostrarmaquinas/'.$buspro->IdCpe_cabecera);
   }

    public function finalizarproceso(Request $request){

    	$id=$request->get('proc_comp_id');

    	$hora_fin = now()->format('Y-m-d h:i:s');

    	DB::tABLE('maquinas_comprobante')->where('proc_comp_id',$id)
    	->update(['proc_fec_fin'=>$hora_fin,
    		'proc_comp_est'=>'2'
    	]);

    	$buspro = DB::tABLE('maquinas_comprobante')->where('proc_comp_id',$id)->first();
   		
    	$pendientes = DB::tABLE('maquinas_comprobante')
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

    	return Redirect::to('/mostrarmaquinas/'.$buspro->IdCpe_cabecera);

    }

      public function observacionproceso(Request $request){

    	$id=$request->get('proc_comp_id');

    	DB::tABLE('maquinas_comprobante')->where('proc_comp_id',$id)
    	->update(['proc_obs'=>$request->get('observacion')
    	]);

    	$buspro = DB::tABLE('maquinas_comprobante')->where('proc_comp_id',$id)->first();
   		

    	return Redirect::to('/mostrarmaquinas/'.$buspro->IdCpe_cabecera);

    }

    public function detalleorden($venta){

    $cabecera = DB::tABLE('cpe_cabecera')
    ->join('tipo_documento','tipo_documento.tdocod','cpe_cabecera.tdocod')
    ->where('IdCpe_cabecera',$venta)->first();

    $detalle = DB::tABLE('cpe_cabecera')
    ->join('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
    ->where('cpe_cabecera.IdCpe_cabecera',$venta)
    ->get();

    return view('empresas.maquinas.detalleventa',compact('cabecera','detalle'));

 }

}
