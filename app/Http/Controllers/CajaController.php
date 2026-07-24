<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use MasterSoft\movimientosbancarios;
use MasterSoft\movimientoscaja;
use MasterSoft\gastos_cabecera;
use MasterSoft\gastos_detalle;
use MasterSoft\usuario_gastos;
use MasterSoft\movimientos;

use DB;

class CajaController extends Controller
{

	 public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function movimientoscaja(Request $request){

    	$fecin = $request->get('fecin');
    	$fecfin = $request->get('fecfin');

    	if(!empty($fecin)){
	    	$movimientos = DB::tABLE('movimientoscaja')
		    ->where('mov_fecha','>=',$fecin)
		    ->where('mov_fecha','<=',$fecfin)
		    ->leftjoin('cuentasbancarias','cuentasbancarias.cuen_ban_id','movimientoscaja.cuen_ban_id')
		    ->leftjoin('bancos','bancos.ban_id','cuentasbancarias.ban_id')
		    ->leftjoin('tiposcaja','tiposcaja.tip_caj_id','movimientoscaja.tip_caj_id')
		    ->orderBy('mov_caj_id','desc')
		   	->get();
	   }else{
		   	$movimientos = DB::tABLE('movimientoscaja')
		    ->leftjoin('cuentasbancarias','cuentasbancarias.cuen_ban_id','movimientoscaja.cuen_ban_id')
		    ->leftjoin('bancos','bancos.ban_id','cuentasbancarias.ban_id')
		    ->leftjoin('tiposcaja','tiposcaja.tip_caj_id','movimientoscaja.tip_caj_id')
		      ->orderBy('mov_caj_id','desc')
		   	->get();
	   }

    	return view('empresas.movimientos.movimientoscaja',compact('movimientos'));

    }

    public function movimientosbancarios(Request $request){

    	$fecin = $request->get('fecin');
    	$fecfin = $request->get('fecfin');
    	$est_id = $request->get('est_id');
    	$cuen_ban_id= $request->get('cuen_ban_id');
    	$clicod = $request->get('clicod');

    	if(!empty($fecin)){
    		$movimientos = DB::tABLE('movimientosbancarios')
    		->where(function($query) use($est_id,$clicod) {
    			if($est_id !='Todos' && $clicod!='Todos'){
    				$query->where('estado',$est_id)
                      	  ->orwhere('movimientosbancarios.clicod',$clicod);
    			}elseif($est_id !='Todos') {
    				$query->where('estado',$est_id);
    			}elseif($clicod !='Todos') {
    				$query->where('movimientosbancarios.clicod',$clicod);
    			}
               
            })
	    	->where('cuentasbancarias.cuen_ban_id',$cuen_ban_id)
	    	->where('mov_fecha','>=',$fecin)
	    	->where('mov_fecha','<=',$fecfin)
	    	->leftjoin('cliente','cliente.clicod','movimientosbancarios.clicod')
	    	->leftjoin('tiposdocumentos','tiposdocumentos.doc_id','movimientosbancarios.doc_id')
	    	->leftjoin('cuentasbancarias','cuentasbancarias.cuen_ban_id','movimientosbancarios.cuen_ban_id')
	    	->leftjoin('bancos','bancos.ban_id','cuentasbancarias.ban_id')
	    	->leftjoin('conceptosbancarios','conceptosbancarios.concepto_id','movimientosbancarios.concepto_id')
	    	->orderBy('mov_ban_id','desc')
	    	->get();


    	}
    
    	$cuenta = DB::tABLE('bancos')
    	->leftjoin('cuentasbancarias','cuentasbancarias.ban_id','bancos.ban_id')
    	->leftjoin('tipo_cuentas','tipo_cuentas.tip_cuen_id','cuentasbancarias.tip_cuen_id')
    	->leftjoin('moneda','moneda.moncod','cuentasbancarias.moncod')
    	->where('cuentasbancarias.IdEmpresa',Auth::user()->IdEmpresa)
    	->where('cuen_ban_id',$cuen_ban_id)
    	->first();

    	$clientes = DB::tABLE('cliente')->where('rucemp',Auth::user()->IdEmpresa)->get();
    	$conceptos = DB::tABLE('conceptosbancarios')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();

    	$bancos = DB::tABLE('bancos')
    	->leftjoin('cuentasbancarias','cuentasbancarias.ban_id','bancos.ban_id')
    	->leftjoin('tipo_cuentas','tipo_cuentas.tip_cuen_id','cuentasbancarias.tip_cuen_id')
    	->leftjoin('moneda','moneda.moncod','cuentasbancarias.moncod')
    	->where('cuentasbancarias.IdEmpresa',Auth::user()->IdEmpresa)
    	->get();

    	return view('empresas.movimientos.movimientosbancarios',compact('conceptos','bancos','movimientos','clientes','cuenta'));
    }

    public function ingresarmovimientosbancarios(){

    	$conceptos = DB::tABLE('conceptosbancarios')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();
    	$bancos = DB::tABLE('bancos')
    	->leftjoin('cuentasbancarias','cuentasbancarias.ban_id','bancos.ban_id')
    	->leftjoin('tipo_cuentas','tipo_cuentas.tip_cuen_id','cuentasbancarias.tip_cuen_id')
    	->leftjoin('moneda','moneda.moncod','cuentasbancarias.moncod')
    	->where('cuentasbancarias.IdEmpresa',Auth::user()->IdEmpresa)
    	->get();

    	$documentos = DB::tABLE('tiposdocumentos')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();
    	$clientes = DB::tABLE('cliente')->where('rucemp',Auth::user()->IdEmpresa)->get();

    	return view('empresas.movimientos.ingresarmovimientobancario',compact('conceptos','bancos','documentos','clientes'));
    }

    public function registrarmovimientosbancarios(Request $request){

    	$cuen_ban_id = $request->get('cuen_ban_id');

		$buscar = movimientosbancarios::where('cuen_ban_id',$cuen_ban_id)->get();

		$contar = movimientosbancarios::where('cuen_ban_id',$cuen_ban_id)->count();

    	$movimiento = new movimientosbancarios;
    	$movimiento->mov_tip = $request->get('mov_tip');
		$movimiento->concepto_id = $request->get('concepto_id');
		$movimiento->mov_com = $request->get('mov_com');
		$movimiento->doc_id =  $request->get('doc_id');
		 $movimiento->IdUsuario = Auth::user()->IdUsuario;
        //$movimiento->id_turno = Auth::user()->id_turno;
		$movimiento->mov_num_doc = $request->get('mov_num_doc');
		$movimiento->cuen_ban_id = $request->get('cuen_ban_id');
		$movimiento->mov_num_oper = $request->get('mov_num_oper');
		$movimiento->importe = $request->get('importe');
		$movimiento->estado = $request->get('estado');
		$movimiento->mov_fecha = $request->get('mov_fecha');
		$movimiento->clicod = $request->get('clicod');
		$movimiento->registro = 'Registrado';
		if($request->get('mov_tip')=='debe'){

			if($contar==0){
				$totalsaldo =  $request->get('importe');
			}else{
				$totalsaldo = $buscar->last()->saldo + $request->get('importe');
			}
			

		}elseif($request->get('mov_tip')=='haber'){

			if($contar==0){

				$totalsaldo =  $request->get('importe');
			}else{
				$totalsaldo = $buscar->last()->saldo - $request->get('importe');
			}

			

		}

		$movimiento->saldo = $totalsaldo;
		$movimiento->IdEmpresa = Auth::user()->IdEmpresa;
		$movimiento->id_empresa_negocio = Auth::user()->id_empresa_negocio;
		$movimiento->save();

		return Redirect::to('/movimientosbancarios');
    }


    public function editarmovimientosbancarios($movimiento){

    	$conceptos = DB::tABLE('conceptosbancarios')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();
    	$bancos = DB::tABLE('bancos')
    	->leftjoin('cuentasbancarias','cuentasbancarias.ban_id','bancos.ban_id')
    	->leftjoin('tipo_cuentas','tipo_cuentas.tip_cuen_id','cuentasbancarias.tip_cuen_id')
    	->leftjoin('moneda','moneda.moncod','cuentasbancarias.moncod')
    	->where('cuentasbancarias.IdEmpresa',Auth::user()->IdEmpresa)
    	->get();
    	$documentos = DB::tABLE('tiposdocumentos')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();
    	$clientes = DB::tABLE('cliente')->where('rucemp',Auth::user()->IdEmpresa)->get();

    	$movimiento = DB::tABLE('movimientosbancarios')
    	->where('mov_ban_id',$movimiento)
    	->leftjoin('cliente','cliente.clicod','movimientosbancarios.clicod')
    	->leftjoin('tiposdocumentos','tiposdocumentos.doc_id','movimientosbancarios.doc_id')
    	->leftjoin('cuentasbancarias','cuentasbancarias.cuen_ban_id','movimientosbancarios.cuen_ban_id')
    	->leftjoin('bancos','bancos.ban_id','cuentasbancarias.ban_id')
    	->leftjoin('conceptosbancarios','conceptosbancarios.concepto_id','movimientosbancarios.concepto_id')
    	->first();

    	return view('empresas.movimientos.actualizarmovimientosbancarios',compact('conceptos','bancos','documentos','movimiento','clientes'));


    }


    public function actualizarmovimientosbancarios(Request $request){

    	$cuen_ban_id = $request->get('cuen_ban_id');

    	$movimiento = movimientosbancarios::FindOrFail($request->get('mov_ban_id'));
    	$movimiento->mov_tip = $request->get('mov_tip');
		$movimiento->concepto_id = $request->get('concepto_id');
		$movimiento->mov_com = $request->get('mov_com');
		$movimiento->doc_id =  $request->get('doc_id');
		$movimiento->mov_num_doc = $request->get('mov_num_doc');
		$movimiento->cuen_ban_id = $request->get('cuen_ban_id');
		$movimiento->mov_num_oper = $request->get('mov_num_oper');
		$movimiento->IdUsuario = Auth::user()->IdUsuario;
        //$movimiento->id_turno = Auth::user()->id_turno;
		$movimiento->importe = $request->get('importe');
		$movimiento->estado = $request->get('estado');
		$movimiento->mov_fecha = $request->get('mov_fecha');
		$movimiento->clicod = $request->get('clicod');
		$movimiento->IdEmpresa = Auth::user()->IdEmpresa;
		$movimiento->id_empresa_negocio = Auth::user()->id_empresa_negocio;
		

		$buscar = movimientosbancarios::where('mov_ban_id','<',$request->get('mov_ban_id'))
		->where('registro','Registrado')
		->where('cuen_ban_id',$cuen_ban_id)
		->orderBy('mov_ban_id','desc')
		->first();

		

		$contar = movimientosbancarios::where('mov_ban_id','<',$request->get('mov_ban_id'))
		->where('registro','Registrado')
		->where('cuen_ban_id',$cuen_ban_id)
		->orderBy('mov_ban_id','desc')
		->count();

		if($request->get('mov_tip')=='debe'){

			if($contar==0){
				$totalsaldo =  $request->get('importe');
			}else{
				$totalsaldo = $buscar->saldo + $request->get('importe');
			}
			


		}elseif($request->get('mov_tip')=='haber'){

			if($contar==0){

				$totalsaldo =  (-1)*$request->get('importe');
			}else{
				$totalsaldo = $buscar->saldo - $request->get('importe');
			}
		}
		$movimiento->saldo = $totalsaldo;
		$movimiento->update();

		$buscarmovimientos = movimientosbancarios::where('mov_ban_id','>',$request->get('mov_ban_id'))
		->where('registro','Registrado')
		->where('cuen_ban_id',$cuen_ban_id)
		->orderBy('mov_ban_id','asc')
		->get();


		$saldo = 0;
		$i=0;
		$saldoanterior=0;
		foreach ($buscarmovimientos as $busmov) {
				
				if($i==0){

					$saldoanterior = $movimiento->saldo;
				}
				

				if($busmov->mov_tip =='debe'){

				    $saldo = $saldoanterior + $busmov->importe;

				}elseif($busmov->mov_tip =='haber'){

					$saldo = $saldoanterior - $busmov->importe;

				}

				$saldoanterior = $saldo;

			
			$buscarregistro = movimientosbancarios::FindOrFail($busmov->mov_ban_id);
			$buscarregistro->saldo = $saldo;
			$buscarregistro->update();

			$saldo=0;

			$i++;
		}


		return Redirect::to('/movimientosbancarios');

    }

    public function eliminarmovimientosbancarios(Request $request,$id=0){

    	if($id==0){
    		$mov_ban_id = $request->get('mov_ban_id');
    	}else{
    		$mov_ban_id = $id;
    	}
    	

    	$movimientos = movimientosbancarios::FindOrFail($mov_ban_id);
    	$movimientos->registro ='Eliminado';
    	$movimientos->update();

    	$buscar = movimientosbancarios::where('mov_ban_id','<',$request->get('mov_ban_id'))->orderBy('mov_ban_id','desc')->where('registro','Registrado')->first();

    	$buscarcontar = movimientosbancarios::where('mov_ban_id','<',$request->get('mov_ban_id'))->orderBy('mov_ban_id','desc')->where('registro','Registrado')->count();

    	$buscarmovimientos = movimientosbancarios::where('mov_ban_id','>',$request->get('mov_ban_id'))->orderBy('mov_ban_id','asc')->where('registro','Registrado')->get();

		$saldo = 0;
		$i=0;
		$saldoanterior=0;
		foreach ($buscarmovimientos as $busmov) {
				
				if($buscarcontar ==0){

					if($i==0){
						$saldoanterior = '0.00';
					}

				}else{
					if($i==0){

					 $saldoanterior = $buscar->saldo;

					}
				}
				
				

				if($busmov->mov_tip =='debe'){

				    $saldo = $saldoanterior + $busmov->importe;

				}elseif($busmov->mov_tip =='haber'){

					$saldo = $saldoanterior - $busmov->importe;

				}

				$saldoanterior = $saldo;

			
			$buscarregistro = movimientosbancarios::FindOrFail($busmov->mov_ban_id);
			$buscarregistro->saldo = $saldo;
			$buscarregistro->update();

			$saldo=0;

			$i++;
		}



    	return  Redirect::to('/movimientosbancarios');

    }


    //-------------------------------------movimientos caja -----------------------------------------//

     public function ingresarmovimientoscaja(){

    	$conceptos = DB::tABLE('tiposcaja')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();
    	$bancos = DB::tABLE('bancos')
    	->leftjoin('cuentasbancarias','cuentasbancarias.ban_id','bancos.ban_id')
    	->leftjoin('tipo_cuentas','tipo_cuentas.tip_cuen_id','cuentasbancarias.tip_cuen_id')
    	->leftjoin('moneda','moneda.moncod','cuentasbancarias.moncod')
    	->where('cuentasbancarias.IdEmpresa',Auth::user()->IdEmpresa)
    	->get();


    	$conceptosbancarios = DB::tABLE('conceptosbancarios')->get();
    	$documentos = DB::tABLE('tiposdocumentos')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();
    	$clientes = DB::tABLE('cliente')->where('rucemp',Auth::user()->IdEmpresa)->get();

    	return view('empresas.movimientos.ingresarmovimientocaja',compact('conceptosbancarios','bancos','documentos','clientes','conceptos'));
    }

    public function registrarmovimientoscaja(Request $request){

    	$buscar = movimientoscaja::get();

		$contar = movimientoscaja::count();

    	$movimiento = new movimientoscaja;
    	
    	$tipomovimiento = DB::tABLE('tiposcaja')->where('tip_caj_id',$request->get('tip_caj_id'))->first();

		$movimiento->tip_caj_id = $request->get('tip_caj_id');
		$movimiento->mov_com = $request->get('mov_com');
		$movimiento->cuen_ban_id = $request->get('cuen_ban_id');
		$movimiento->concepto_id = $request->get('concepto_id');
		$movimiento->mov_num_oper = $request->get('mov_num_oper');
		$movimiento->importe = $request->get('importe');
		$movimiento->IdUsuario = Auth::user()->IdUsuario;
        //$movimiento->id_turno = Auth::user()->id_turno;
		$movimiento->mov_fecha = $request->get('mov_fecha');
		$movimiento->registro = 'Registrado';

		if($tipomovimiento->tipo =='ENTRADA'){

			if($contar==0){
				$totalsaldo =  $request->get('importe');
			}else{
				$totalsaldo = $buscar->last()->saldo + $request->get('importe');
			}
			

		}elseif($tipomovimiento->tipo =='SALIDA'){

			if($contar==0){

				$totalsaldo =  $request->get('importe');
			}else{
				$totalsaldo = $buscar->last()->saldo - $request->get('importe');
			}

			

		}

		$movimiento->saldo = $totalsaldo;
		$movimiento->IdEmpresa = Auth::user()->IdEmpresa;
		$movimiento->id_empresa_negocio = Auth::user()->id_empresa_negocio;
		$movimiento->save();

		if($tipomovimiento->gasto =='SI'){

		$gastos = new gastos_cabecera;
        $gastos->gast_doc_ser = 'GAST';
        $gastos->gast_doc_num =  $request->get('mov_num_oper');
        $gastos->gast_fec = $request->get('mov_fecha');
        $gastos->gast_fec_ven = $request->get('mov_fecha');
        $gastos->mon_id = 'PEN';
        $gastos->tot_igv = $request->get('importe')-$request->get('importe')/1.1055;
        $gastos->tot_grav = $request->get('importe')/1.1055;
        $gastos->tot_grat = '0.00';
        $gastos->tot_exon = '0.00';
        $gastos->tot_inaf = '0.00';
        $gastos->tot_desc_por = '0.00';
        $gastos->tot_desc = '0.00';
        $gastos->tot_otr_car = '0.00';
        $gastos->tot_exp = '0.00';
        $gastos->tot_otr_tri = '0.00';
        $gastos->total_gast = $request->get('importe');
        $gastos->gast_obs = $request->get('mov_com');
        $gastos->IdEmpresa = Auth::user()->IdEmpresa;
         $gastos->IdUsuario = Auth::user()->IdUsuario;
        $gastos->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $gastos->tipo_movimiento = 'GASTO';
        $gastos->save();

         $usuario_gastos = new usuario_gastos;
         $usuario_gastos->gast_cab_id = $gastos->gast_cab_id;
         $usuario_gastos->id_empresa_negocio = Auth::user()->id_empresa_negocio;
         $usuario_gastos->IdEmpresa = Auth::user()->IdEmpresa;
         $usuario_gastos->referencia = 'GASTO';
         $usuario_gastos->save();

        $codgast= $gastos->gast_cab_id;

   
        $gastos_det = new gastos_detalle;
        $gastos_det->val_uni = $request->get('importe')/1.1055;
        $gastos_det->pre_uni = $request->get('importe');
        $gastos_det->tip_igv = '10';
        $gastos_det->igv = $request->get('importe')-$request->get('importe')/1.1055;
        $gastos_det->det_gasto = $request->get('mov_com');
        $gastos_det->subtotal= $request->get('importe')/1.1055;
        $gastos_det->total= $request->get('importe');
        $gastos_det->cantidad= '1';
        $gastos_det->ume_cod= 'NIU';
        $gastos_det->gast_cab_id= $gastos->gast_cab_id;
        $gastos_det->IdEmpresa= Auth::user()->IdEmpresa;

        $gastos_det->save();

        $movimiento = new movimientos;
        $movimiento->mov_fec = $request->get('mov_fecha');
        $movimiento->mov_tip = 'E';
        $movimiento->mov_mot = 'gasto';
        $movimiento->cantidad = '1';
        $movimiento->unidad = 'NIU';
        $movimiento->IdEmpresa = Auth::user()->IdEmpresa;
       // $movimiento->IdProducto = $IdProducto->IdProducto;
        $movimiento->save();


	}

		if(!empty($request->get('cuen_ban_id'))){
			$buscarmovban = movimientosbancarios::get();

			$contarmovban = movimientosbancarios::count();

	    	$movimientoban = new movimientosbancarios;
	    	$movimientoban->mov_tip =  'debe';
			$movimientoban->concepto_id = $request->get('concepto_id');
			$movimientoban->mov_com = $request->get('mov_com');
			$movimientoban->doc_id =  $request->get('doc_id');
			$movimientoban->mov_num_doc = $request->get('mov_num_doc');
			$movimientoban->cuen_ban_id = $request->get('cuen_ban_id');
			$movimientoban->mov_num_oper = $request->get('mov_num_oper');
			$movimientoban->importe = $request->get('importe');
			$movimientoban->estado = '1';
			$movimientoban->mov_fecha = $request->get('mov_fecha');
			//$movimientoban->clicod = $request->get('clicod');
			$movimientoban->registro = 'Registrado';


			if($contarmovban==0){
				$totalsaldoban =  $request->get('importe');
			}else{
				$totalsaldoban = $buscarmovban->last()->saldo + $request->get('importe');
			}
			
			$movimientoban->saldo = $totalsaldoban;
			$movimientoban->IdEmpresa = Auth::user()->IdEmpresa;
			$movimientoban->id_empresa_negocio = Auth::user()->id_empresa_negocio;
			$movimientoban->save();

			$buscarmovcaj = movimientoscaja::FindOrFail($movimiento->mov_caj_id);
			$buscarmovcaj->mov_ban_id = $movimientoban->mov_ban_id;
			$buscarmovcaj->update();

		}

	


		return Redirect::to('/movimientoscaja');
    }


    public function editarmovimientoscaja($movimiento){

    	$conceptos = DB::tABLE('tiposcaja')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();
    	$bancos = DB::tABLE('bancos')
    	->leftjoin('cuentasbancarias','cuentasbancarias.ban_id','bancos.ban_id')
    	->leftjoin('tipo_cuentas','tipo_cuentas.tip_cuen_id','cuentasbancarias.tip_cuen_id')
    	->leftjoin('moneda','moneda.moncod','cuentasbancarias.moncod')
    	->where('cuentasbancarias.IdEmpresa',Auth::user()->IdEmpresa)
    	->get();
    	$documentos = DB::tABLE('tiposdocumentos')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();
    	$clientes = DB::tABLE('cliente')->where('rucemp',Auth::user()->IdEmpresa)->get();

    	$movimiento = DB::tABLE('movimientoscaja')
    	->where('mov_caj_id',$movimiento)
    	->leftjoin('cuentasbancarias','cuentasbancarias.cuen_ban_id','movimientoscaja.cuen_ban_id')
    	->leftjoin('bancos','bancos.ban_id','cuentasbancarias.ban_id')
    	->leftjoin('tiposcaja','tiposcaja.tip_caj_id','movimientoscaja.tip_caj_id')
    	->first();

    	$conceptosbancarios = DB::tABLE('conceptosbancarios')->get();

    	return view('empresas.movimientos.actualizarmovimientoscaja',compact('conceptos','bancos','documentos','movimiento','clientes','conceptosbancarios'));


    }

    public function actualizarmovimientoscaja(Request $request){

    	$movimiento = movimientoscaja::FindOrFail($request->get('mov_caj_id'));
		$movimiento->tip_caj_id = $request->get('tip_caj_id');
		$movimiento->mov_com = $request->get('mov_com');
		$movimiento->cuen_ban_id = $request->get('cuen_ban_id');
		$movimiento->mov_num_oper = $request->get('mov_num_oper');
		$movimiento->importe = $request->get('importe');
		 $movimiento->IdUsuario = Auth::user()->IdUsuario;
           //   $movimiento->id_turno = Auth::user()->id_turno;
		$movimiento->mov_fecha = $request->get('mov_fecha');

		$tipomovimiento = DB::tABLE('tiposcaja')->where('tip_caj_id',$request->get('tip_caj_id'))->first();

		$buscar = movimientoscaja::where('mov_caj_id','<',$request->get('mov_caj_id'))->orderBy('mov_caj_id','desc')->where('registro','Registrado')->first();

		

		$contar = movimientoscaja::where('mov_caj_id','<',$request->get('mov_caj_id'))->orderBy('mov_caj_id','desc')->where('registro','Registrado')->count();

		if($tipomovimiento->tipo =='ENTRADA'){

			if($contar==0){
				$totalsaldo =  $request->get('importe');
			}else{
				$totalsaldo = $buscar->saldo + $request->get('importe');
			}
			


		}elseif($tipomovimiento->tipo =='SALIDA'){

			if($contar==0){

				$totalsaldo =  (-1)*$request->get('importe');
			}else{
				$totalsaldo = $buscar->saldo - $request->get('importe');
			}
		}
		$movimiento->saldo = $totalsaldo;

		$movimiento->IdEmpresa = Auth::user()->IdEmpresa;
		$movimiento->id_empresa_negocio = Auth::user()->id_empresa_negocio;
		$movimiento->update();

		$buscarmovimientos = movimientoscaja::where('mov_caj_id','>',$request->get('mov_caj_id'))->orderBy('mov_caj_id','asc')
		->where('registro','Registrado')
		->leftjoin('tiposcaja','tiposcaja.tip_caj_id','movimientoscaja.tip_caj_id')
		->get();


		$saldo = 0;
		$i=0;
		$saldoanterior=0;
		foreach ($buscarmovimientos as $busmov) {
				
				if($i==0){

					$saldoanterior = $movimiento->saldo;
				}
				

				if($busmov->tipo =='ENTRADA'){

				    $saldo = $saldoanterior + $busmov->importe;

				}elseif($busmov->tipo =='SALIDA'){

					$saldo = $saldoanterior - $busmov->importe;

				}

				$saldoanterior = $saldo;

			
			$buscarregistro = movimientoscaja::FindOrFail($busmov->mov_caj_id);
			$buscarregistro->saldo = $saldo;
			$buscarregistro->update();

			$saldo=0;

			$i++;
		}

		return Redirect::to('/movimientoscaja');

    }

    public function eliminarmovimientoscaja(Request $request){

    	$movimientos = movimientoscaja::FindOrFail($request->get('mov_caj_id'));
    	$movimientos->registro ='Eliminado';
    	$movimientos->update();

    	$buscar = movimientoscaja::where('mov_caj_id','<',$request->get('mov_caj_id'))->orderBy('mov_caj_id','desc')->where('registro','Registrado')->first();

    	$buscarcontar = movimientoscaja::where('mov_caj_id','<',$request->get('mov_caj_id'))->orderBy('mov_caj_id','desc')->where('registro','Registrado')->count();

    	$buscarmovimientos = movimientoscaja::where('mov_caj_id','>',$request->get('mov_caj_id'))->orderBy('mov_caj_id','asc')->where('registro','Registrado')->leftjoin('tiposcaja','tiposcaja.tip_caj_id','movimientoscaja.tip_caj_id')->get();

		$saldo = 0;
		$i=0;
		$saldoanterior=0;
		foreach ($buscarmovimientos as $busmov) {
				
				if($buscarcontar ==0){

					if($i==0){
						$saldoanterior = '0.00';
					}

				}else{
					if($i==0){

					 $saldoanterior = $buscar->saldo;

					}
				}
				
				

				if($busmov->tipo =='ENTRADA'){

				    $saldo = $saldoanterior + $busmov->importe;

				}elseif($busmov->tipo =='SALIDA'){

					$saldo = $saldoanterior - $busmov->importe;

				}

				$saldoanterior = $saldo;

			
			$buscarregistro = movimientoscaja::FindOrFail($busmov->mov_caj_id);
			$buscarregistro->saldo = $saldo;
			$buscarregistro->update();

			$saldo=0;

			$i++;
		}

		self::eliminarmovimientosbancario($movimientos->mov_ban_id);


    	return  Redirect::to('/movimientoscaja');

    }

       public function eliminarmovimientosbancario($id){

    
    		$mov_ban_id = $id;
    	

    	$movimientos = movimientosbancarios::FindOrFail($mov_ban_id);
    	$movimientos->registro ='Eliminado';
    	$movimientos->update();

    	$buscar = movimientosbancarios::where('mov_ban_id','<',$mov_ban_id)->orderBy('mov_ban_id','desc')->where('registro','Registrado')->first();

    	$buscarcontar = movimientosbancarios::where('mov_ban_id','<',$mov_ban_id)->orderBy('mov_ban_id','desc')->where('registro','Registrado')->count();

    	$buscarmovimientos = movimientosbancarios::where('mov_ban_id','>',$mov_ban_id)->orderBy('mov_ban_id','asc')->where('registro','Registrado')->get();

		$saldo = 0;
		$i=0;
		$saldoanterior=0;
		foreach ($buscarmovimientos as $busmov) {
				
				if($buscarcontar ==0){

					if($i==0){
						$saldoanterior = '0.00';
					}

				}else{
					if($i==0){

					 $saldoanterior = $buscar->saldo;

					}
				}
				
				

				if($busmov->mov_tip =='debe'){

				    $saldo = $saldoanterior + $busmov->importe;

				}elseif($busmov->mov_tip =='haber'){

					$saldo = $saldoanterior - $busmov->importe;

				}

				$saldoanterior = $saldo;

			
			$buscarregistro = movimientosbancarios::FindOrFail($busmov->mov_ban_id);
			$buscarregistro->saldo = $saldo;
			$buscarregistro->update();

			$saldo=0;

			$i++;
		}



    	return  Redirect::to('/movimientosbancarios');

    }


}
