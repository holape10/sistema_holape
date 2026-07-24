<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\See;
use Greenter\Model\Voided\Voided;
use MasterSoft\resumenes;
use Greenter\Model\Voided\VoidedDetail;
use Greenter\Model\Summary\Summary;
use Greenter\Model\Summary\SummaryDetail;
use Greenter\Model\Summary\SummaryPerception;
use Greenter\Model\Sale\Document;
use DOMDocument;
use MasterSoft\pedidos;
use MasterSoft\pedidos_detalle;
use MasterSoft\TipoIGV;
use MasterSoft\Empresa;
use MasterSoft\Cliente;
use MasterSoft\caja;
use MasterSoft\cpe_cabecera;
use MasterSoft\cuentaspagar;
use MasterSoft\cuentaspagardetalle;
use MasterSoft\compras_cabecera;
use MasterSoft\compras_detalle;
use MasterSoft\documento_relacionado;
use MasterSoft\movimientoscaja;
use MasterSoft\unidad_medida;
use MasterSoft\cpe_detalle;
use MasterSoft\usuario_pedidos;
use MasterSoft\usuario_facturacion;
use MasterSoft\cpe_baja;
use MasterSoft\mesas;
use MasterSoft\proveedor;
use MasterSoft\movimientos;
use MasterSoft\movimientosbancarios;
use MasterSoft\movimientosinsumos;
use MasterSoft\EmpresaNegocios;
use MasterSoft\insumos;
use MasterSoft\Comprobante;
use MasterSoft\cpe_nota_detalle;
use MasterSoft\cpe_nota;
use MasterSoft\User;
use MasterSoft\tipo_documento_identidad;
use MasterSoft\productos;
use MasterSoft\tarifas;
use MasterSoft\tipocambio;
use MasterSoft\MontoLetras;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Http\Requests\FacturaCreateFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use QR_Code\Exceptions\InvalidVCardAddressEntryException;
use QR_Code\Exceptions\InvalidVCardPhoneEntryException;
use QR_Code\Types\QR_CalendarEvent;
use QR_Code\Types\QR_EmailMessage;
use QR_Code\Types\QR_meCard;
use QR_Code\Types\QR_Phone;
use QR_Code\Types\QR_Sms;
use QR_Code\Types\QR_Text;
use QR_Code\Types\QR_Url;
use QR_Code\Types\QR_VCard;
use QR_Code\Types\QR_WiFi;
use QR_Code\Types\vCard\Person;
use QR_Code\Types\vCard\Phone;
use DB;
use Hash;
use PDF;

class CuentasPagarController extends Controller
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


    	$fecin = $request->get('fecin');
    	$fecfin = $request->get('fecfin');
      $placa = $request->get('placa');
    	$tipdoc = $request->get('tipdoc');
    	$numdoc = $request->get('numdoc');
    	$clicod = $request->get('clicod');
    	$estado = $request->get('estado');
      $tipo = $request->get('tipfec');


        $ser = substr($numdoc,strpos($numdoc,'-')-4,4);
        $num = substr($numdoc,strpos($numdoc,'-')+1,8);

      if(!empty($fecin)){

    		
    		$cuentas = DB::tABLE('cuentas_pagar')
	        ->leftjoin('compras_cabecera','compras_cabecera.com_cab_id','cuentas_pagar.com_cab_id')
	        ->leftjoin('proveedor','proveedor.prov_id','compras_cabecera.prov_id')
	        ->leftjoin('cuentasbancarias','cuentasbancarias.cuen_ban_id','cuentas_pagar.cuen_ban_id')
	        ->leftjoin('tipo_cuentas','tipo_cuentas.tip_cuen_id','cuentasbancarias.tip_cuen_id')
	        ->leftjoin('moneda','moneda.moncod','cuentasbancarias.moncod')
	        ->leftjoin('bancos','bancos.ban_id','cuentasbancarias.ban_id')
	        ->where('cuentas_pagar.id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->where(function($query1) use ($fecin, $fecfin,$tipo,$numdoc)
          {

             if(empty($numdoc)){
              if($tipo=='0'){
                $query1->where('fec_ven','>=',$fecin)
                      ->where('fec_ven','<=',$fecfin);
              }elseif($tipo=='1'){
                $query1->where('com_fec','>=',$fecin)
                      ->where('com_fec','<=',$fecfin);
              }
             }
            
            
          })
          ->where(function($query2) use ($estado,$numdoc)
          {
            
              if(empty($numdoc)){
                if($estado !='Todos') {
                  $query2->where('cuentas_pagar.estado_cob',$estado);
                }
              }
            
            
          })
          ->where(function($query3) use($clicod,$numdoc) {
               if(empty($numdoc)){
                  if($clicod !='Todos') {
                    $query3->where('compras_cabecera.prov_id',$clicod);
                  }
               }
              
               
          })
          ->where(function($query4) use($tipdoc,$numdoc,$clicod,$estado,$ser,$num) {
            if(!empty($numdoc)){

              $query4->where('com_doc_ser',$ser)
                    ->where('com_doc_num',$num);

            }elseif($tipdoc !='Todos'){

              $query4->where('tdocod',$tipdoc);
            }

           })
	        ->orderby('cuentas_pagar.cue_pag_id','desc')
	        ->get();




    	}else{
    		$cuentas = DB::tABLE('cuentas_pagar')
	        ->leftjoin('compras_cabecera','compras_cabecera.com_cab_id','cuentas_pagar.com_cab_id')
	        ->leftjoin('proveedor','proveedor.prov_id','compras_cabecera.prov_id')
	        ->leftjoin('cuentasbancarias','cuentasbancarias.cuen_ban_id','cuentas_pagar.cuen_ban_id')
	        ->leftjoin('tipo_cuentas','tipo_cuentas.tip_cuen_id','cuentasbancarias.tip_cuen_id')
	        ->leftjoin('moneda','moneda.moncod','cuentasbancarias.moncod')
	        ->leftjoin('bancos','bancos.ban_id','cuentasbancarias.ban_id')
	        ->where('cuentas_pagar.id_empresa_negocio',Auth::user()->id_empresa_negocio)
	        ->orderby('cuentas_pagar.cue_pag_id','desc')
	        ->get();

    	}
       
       
       	$proveedores = DB::tABLE('proveedor')->get();
       	$documentos = DB::tABLE('tipo_documento')->where('cuentas_pagar','1')->get();

        return view('empresas.cuentaspagar.cuentaspagar',compact('cuentas','proveedores','documentos','tipo'));
    }

    
    public function cuentaspagar(Request $request)
    {

        try{

          $items = $request->get('items');
        	$tiposdocumentos = DB::tABLE('tipo_documento_identidad')->get();
          $proveedores = DB::tABLE('proveedor')->get();
          $documentos = DB::tABLE('tipo_documento')->get();
          $mediospagos = DB::tABLE('medios_pagos')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();
          $cuentasbancarias = DB::tABLE('cuentasbancarias')
          ->leftjoin('tipo_cuentas','tipo_cuentas.tip_cuen_id','cuentasbancarias.tip_cuen_id')
          ->leftjoin('moneda','moneda.moncod','cuentasbancarias.moncod')
          ->leftjoin('bancos','bancos.ban_id','cuentasbancarias.ban_id')
          ->get();

          $conceptos = DB::tABLE('conceptosbancarios')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();

        	$totalcuenta =0;
        	$cuentas =[];

        	foreach($items as $item){

           		$cuenta = DB::tABLE('cuentas_pagar')->where('cuentas_pagar.cue_pag_id',$item)->first();
           		$totalcuenta = $totalcuenta + $cuenta->saldo;

           		$buscar = DB::tABLE('cuentas_pagar')
          		->leftjoin('compras_cabecera','compras_cabecera.com_cab_id','cuentas_pagar.com_cab_id')
          		->leftjoin('proveedor','proveedor.prov_id','compras_cabecera.prov_id')
          		->leftjoin('tipo_documento','tipo_documento.tdocod','compras_cabecera.tdocod')
          		->where('cuentas_pagar.cue_pag_id',$item)
          		->where('cuentas_pagar.id_empresa_negocio',Auth::user()->id_empresa_negocio)
          		->first();

           		$detalle = DB::tABLE('compras_detalle')->where('com_cab_id',$cuenta->com_cab_id)->get();
         
           		$detalles[] = $detalle;
           		$cuentas[] = $buscar;

       		 }

        
        return view('empresas.cuentaspagar.ingresarcuentaspagar',compact('cuentas','proveedores','documentos','cuentasbancarias','conceptos','totalcuenta','tiposdocumentos','mediospagos'));

        }catch(\Exception $e){

          dd($e);

          return Redirect::to('/cuentaspagar');
        }

        
    }


    public function ingresar($cuenta)
    {
        $cuentas = DB::tABLE('cuentas_pagar')
        ->leftjoin('cpe_cabecera','cpe_cabecera.IdCpe_cabecera','cuentas_pagar.IdCpe_cabecera')
        ->leftjoin('cliente','cliente.clicod','cuentas_pagar.clicod')
        ->leftjoin('tipo_documento','tipo_documento.tdocod','cpe_cabecera.tdocod')
        ->where('cuentas_pagar.cue_pag_id',$cuenta)
        ->where('cuentas_pagar.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();
       
        $cuentasbancarias = DB::tABLE('cuentasbancarias')
        ->leftjoin('tipo_cuentas','tipo_cuentas.tip_cuen_id','cuentasbancarias.tip_cuen_id')
        ->leftjoin('moneda','moneda.moncod','cuentasbancarias.moncod')
        ->leftjoin('bancos','bancos.ban_id','cuentasbancarias.ban_id')
        ->get();

        $conceptos = DB::tABLE('conceptosbancarios')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();

       	$clientes = DB::tABLE('cliente')->get();
       	$documentos = DB::tABLE('tipo_documento')->get();

        $total = DB::tABLE('cuentas_pagar')->where('estado_cob','pendiente')->where('clicod',$cuentas->clicod)->sum('saldo');

        $productos = DB::tABLE('productos')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();

        return view('empresas.cuentascobrar.ingresarcuentacobrar',compact('cuentas','clientes','documentos','cuentasbancarias','conceptos','productos'));
    }

     public function ingresarcuenta()
    {
       
       
        $cuentasbancarias = DB::tABLE('cuentasbancarias')
        ->leftjoin('tipo_cuentas','tipo_cuentas.tip_cuen_id','cuentasbancarias.tip_cuen_id')
        ->leftjoin('moneda','moneda.moncod','cuentasbancarias.moncod')
        ->leftjoin('bancos','bancos.ban_id','cuentasbancarias.ban_id')
        ->get();

           $productos = DB::tABLE('productos')
           ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
           ->orderby('pronom','asc')
           ->get();


        $conceptos = DB::tABLE('conceptosbancarios')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();

        $clientes = DB::tABLE('cliente')->get();
        $documentos = DB::tABLE('tipo_documento')->get();

        return view('empresas.cuentascobrar.ingresarcuenta',compact('clientes','documentos','cuentasbancarias','conceptos','total','productos'));
    }


      public function cuentascobrar(Request $request)
    {

        try{

          $items = $request->get('items');

        $tiposdocumentos = DB::tABLE('tipo_documento_identidad')->get();


        $totalcuenta =0;
        $cuentas =[];

        foreach($items as $item){

           $cuenta = DB::tABLE('cuentas_pagar')->where('cuentas_pagar.cue_pag_id',$item)->first();

           $totalcuenta = $totalcuenta + $cuenta->saldo;

           $buscar = DB::tABLE('cuentas_pagar')
          ->leftjoin('cpe_cabecera','cpe_cabecera.IdCpe_cabecera','cuentas_pagar.IdCpe_cabecera')
          ->leftjoin('cliente','cliente.clicod','cuentas_pagar.clicod')
          ->leftjoin('tipo_documento','tipo_documento.tdocod','cpe_cabecera.tdocod')
          ->where('cuentas_pagar.cue_pag_id',$item)
          ->where('cuentas_pagar.id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->first();

           $detalle = DB::tABLE('cpe_detalle')->where('IdCpe_cabecera',$cuenta->IdCpe_cabecera)->get();
         
           $detalles[] = $detalle;
           $cuentas[] = $buscar;

        }


       
        $cuentasbancarias = DB::tABLE('cuentasbancarias')
        ->leftjoin('tipo_cuentas','tipo_cuentas.tip_cuen_id','cuentasbancarias.tip_cuen_id')
        ->leftjoin('moneda','moneda.moncod','cuentasbancarias.moncod')
        ->leftjoin('bancos','bancos.ban_id','cuentasbancarias.ban_id')
        ->get();

        $conceptos = DB::tABLE('conceptosbancarios')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();

        $clientes = DB::tABLE('cliente')->get();
        $documentos = DB::tABLE('tipo_documento')->get();

        //    $total = DB::tABLE('cuentas_pagar')->where('estado_cob','pendiente')->where('clicod',$cuentas->clicod)->sum('saldo');

        
        return view('empresas.cuentascobrar.ingresarcuentascobrar',compact('cuentas','clientes','documentos','cuentasbancarias','conceptos','totalcuenta','tiposdocumentos'));

        }catch(\Exception $e){

          return Redirect::to('/cuentaspagar');
        }

        
    }

    public function registrar(Request $request)
    {
       		
       $tdocod = $request->get('tdocod');
       $tdicod = $request->get('tdicod');
       $cliruc = $request->get('clinum');
       $clinom = $request->get('clinom');
       $clidir = $request->get('clidir');

       $importetotal = $request->get('importetotal');

       $items = $request->get('items');
       $importe =$request->get('importe');
       $abonoimporte = $request->get('abonoimporte');


          if($abonoimporte=='0'){
         return response()->json(['estado'=>'ERROR','mensaje' => 'EL ABONO DEBE SER MAYOR A 0']);
      }



      if($abonoimporte > '0'){

          foreach ($items as $index =>$item){

              if($abonoimporte > '0'){

                $cuenta = cuentaspagar::findOrFail($item);


                if($abonoimporte > $cuenta->saldo){
                  return response()->json(['estado'=>'ERROR','mensaje' => 'EL MONTO NO PUEDE SER MAYOR AL SALDO PENDIENTE']);
                }


                if($abonoimporte <= $cuenta->saldo){

                  $abono = $abonoimporte;
                  $abonoimporte= '0';

                }elseif($abonoimporte > $cuenta->saldo){

                  $abono = $cuenta->saldo;
                  $abonoimporte = $abonoimporte - $cuenta->saldo;

                }

                $detalle = new cuentaspagardetalle;
                $detalle->cue_pag_id = $item;
                $detalle->fec_dep = $request->get('fec_dep');
                $detalle->abono = $abono;
                $detalle->comentario = $request->get('detalle');
                $detalle->fec_reg = $request->get('fec_reg');
                $detalle->save();

               
                $cabecera = compras_cabecera::findOrFail($cuenta->com_cab_id);
                $cabecera->saldofactura = $cabecera->total - $abono; 
                $cabecera->update();

                $cuenta->abono =  $cuenta->abono+$abono;
                $cuenta->saldo =  $cuenta->saldo - $abono;
                
                if($cuenta->saldo ==0){
                  $cuenta->fec_pago = $request->get('fec_dep');
                  $cuenta->estado_cob = 'cancelado';
                }
                          
                
                $cuenta->fecha_deposito = $request->get('fec_dep');
                $cuenta->update();

                
              /*  $buscar = movimientoscaja::get();

                $contar = movimientoscaja::count();

                $movimiento = new movimientoscaja;
                $movimiento->tip_caj_id = '009';        
                $movimiento->mov_com = $request->get('detalle');
                $movimiento->doc_id =  $cabecera->tdocod;
                $movimiento->mov_num_doc = $cabecera->serdoc.'-'.$cabecera->numdoc;

                $movimiento->importe = $abono;
                $movimiento->IdUsuario = Auth::user()->IdUsuario;
                                //$movimiento->id_turno = Auth::user()->id_turno;
                $movimiento->mov_fecha =  $request->get('fec_dep');
                $movimiento->registro = 'Registrado';

                if($contar==0){
                  $totalsaldo =  $abono;
                }else{
                  $totalsaldo = $buscar->last()->saldo + $abono;
                }
                            
                $movimiento->saldo = $totalsaldo;
                $movimiento->IdEmpresa = Auth::user()->IdEmpresa;
                $movimiento->id_empresa_negocio = Auth::user()->id_empresa_negocio;
                $movimiento->save();*/


                 }
          }
        }

    

  if($abonoimporte > '0'){

      foreach ($medios as $j => $mp) {

        if($monto[$j]>'0'){
          DB::tABLE('cuentas_cobrar_medios')
          ->insert([
            'numero_recibo'=>$numero_recibo,
            'med_pag_id'=>$mp,'monto'=>$monto[$j],
            'cue_cob_det_id'=>$detalle->cue_cob_det_id

          ]);
        }
       
      }
    }


    return Redirect::to('/cuentaspagar');
     if($request->ajax()) {
          return response()->json(['estado'=>'CORRECTO','mensaje' => 'Registrado']);
        }


    }


    public function registrarcuenta(Request $request){

       $rucemp = Auth::user()->IdEmpresa;
       $fecreg = $request->get('fecreg');
       $tdicod = $request->get('tdicod');
       $cliruc = $request->get('clinum');
       $clinom = $request->get('clinom');
       $clidir = $request->get('clidir');
       $fecven = $request->get('fecven');
       $deuda = $request->get('deuda');
       $idproducto = $request->get('concepto');

       $tdocod='50';
       $efectivo = 0;
       $cuen_ban_id = "";
       $estadopago = 'credito';
       $totalcredito = $deuda;
       $totalcontado = 0;
       $mediopago = "0";

        $cliente = Cliente::updateOrCreate(['clinum'=>$cliruc,'rucemp'=>$rucemp],['clinom'=>$clinom,'rucemp'=>$rucemp,'tdicod'=>$tdicod,'clidir'=>$clidir]);

        $senudoc = DB::tABLE('empresa_negocios')->select('SerVale','NumVale')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
          $numcomp =  $senudoc->NumVale+1;
          $sercomp =  $senudoc->SerVale;

      //Crear un objeto de la cabecera del comprobante para registrar los datos de la cabecera
        $cabecera = new cpe_cabecera;
        $cabecera->tdocod = '50';
        $cabecera->topcod = '0101';
        $cabecera->ccafve = $fecven;
        $cabecera->ccafem = $fecreg;
        $cabecera->tdicod = $tdicod;
        $cabecera->ccandi = $cliruc;
        $cabecera->ccanom = $clinom;
        $cabecera->moncod = 'PEN';
        $cabecera->tipcambio = '0.00';
        $cabecera->ccatvg =  $deuda/1.1055;
        $cabecera->ccaigv = $deuda - ($deuda/1.1055);
        $cabecera->ccaitv = $deuda;
        $cabecera->mediopago = $mediopago;
        $cabecera->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $cabecera->cuen_ban_id = $cuen_ban_id;
        $cabecera->totalcontado = $totalcontado;
        $cabecera->estadopago = 'credito';
        $cabecera->totalcredito = $totalcredito;
        $cabecera->clicod = $cliente->clicod;
        $cabecera->placa = "";
        //$cabecera->ped_id = $pedido;
        //$cabecera->mes_id =  $mesa;

        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->IdEmpresa =  $rucemp;

       
        $empresa = Empresa::findOrFail($rucemp);
        $empresanegocio = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);
     
        if( $empresanegocio->NumVale == $numcomp){
              $modnumcomp = $numcomp+1;
          }else{
              $modnumcomp = $numcomp;
          }

          $empresanegocio->SerVale = $sercomp;
          $empresanegocio->NumVale = $modnumcomp;
          //$empresa->update();

          $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
          $cabecera->serdoc= $sercomp;
          $cabecera->numdoc = $numdoc;
     

        $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
        $cabecera->serdoc= $sercomp;
        $cabecera->numdoc = $numdoc;
       
        $empresanegocio->update();

        $empresa->update();
        $cabecera->save();
        $codfact = $cabecera->IdCpe_cabecera; 

        $codfact = $cabecera->IdCpe_cabecera;
     

        $producto = Productos::findOrFail($idproducto);

        $detalle = new cpe_detalle;
        $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
        $detalle->umecod = $producto->umecod;
        $detalle->cdecan = '1';
       
        $detalle->procod = $producto->procod;
        $detalle->IdProducto = $producto->IdProducto;
        $detalle->cdepsu = "";
        $detalle->cdedes =  $producto->pronom;
        $detalle->cdevun =  $deuda/1.1055;
        $detalle->cdepuni = $deuda;
        $detalle->tigcod = $producto->tigcod;
        $detalle->cdevve = $deuda;
        $detalle->cdepve = $deuda/1.1055;
        $detalle->cdeigv = $deuda-($deuda/1.1055);
        $detalle->fecha_venta = $fecreg;
        $detalle->save();
    
        $cuentacobrar = new cuentascobrar;
        $cuentacobrar->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
        $cuentacobrar->clicod = $cabecera->clicod;
        $cuentacobrar->fec_ven = $fecven;
        $cuentacobrar->abono = '0.00';
        $cuentacobrar->estado_cob = 'pendiente';
        $cuentacobrar->total = $cabecera->ccaitv;
       //$cuentacobrar->placa = $pedido_est->placa;
        $cuentacobrar->saldo = $cabecera->ccaitv;
        $cuentacobrar->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $cuentacobrar->save();

      if($empresa->formato =='TICKET' && $tdocod!='15'){
          for($i=1;$i<=$empresa->imp_venta;$i++){

              if($request->get('opcion')=='0'){
               self::imprimir($codfact,$tdocod);
              }
          }
        }elseif($empresa->formato=='A4'){
            self::generarpdfgeneral($codfact);
        }



        return response()->json(['mensaje' =>'Registrado']);
    }


    public function detalle($id)
    {
        $cuenta = DB::tABLE('cuentas_pagar')
        ->leftjoin('compras_cabecera','compras_cabecera.com_cab_id','cuentas_pagar.com_cab_id')
        ->leftjoin('tipo_documento','tipo_documento.tdocod','compras_cabecera.tdocod')
        ->leftjoin('proveedor','proveedor.prov_id','compras_cabecera.prov_id')
        ->where('cuentas_pagar.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();
       
         $detalle = DB::tABLE('cuentas_pagar_detalle as ccd')
        ->leftjoin('cuentasbancarias','cuentasbancarias.cuen_ban_id','ccd.cuen_ban_id')
        ->leftjoin('tipo_cuentas','tipo_cuentas.tip_cuen_id','cuentasbancarias.tip_cuen_id')
        ->leftjoin('moneda','moneda.moncod','cuentasbancarias.moncod')
        ->leftjoin('bancos','bancos.ban_id','cuentasbancarias.ban_id')
        ->where('ccd.cue_pag_id',$id)
        ->where('est_cue_pag_det','REGISTRADO')
        ->orderby('ccd.cue_pag_det_id','desc')
        ->get();
       
     
    

        return view('empresas.cuentaspagar.detallecuentapagar',compact('cuenta','detalle'));
    }




      public function generarcomprobante($cpe,$tdocod,$clidir,$clinom,$clinum,$tdicod){

        $fecha = now()->format('Y-m-d');

        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);

        $total = 0;
        $totaltickets =0;


        foreach ($cpe as $c) {

            $registro = cpe_cabecera::findOrFail($c['cpe']);
            $detallereg = cpe_detalle::where('IdCpe_cabecera',$c['cpe'])->first();

            $total = $total + $registro->ccaitv;


           $conceptos[] =  array('concepto' =>$detallereg->cdedes,'precio'=>$c['abono'],'unidad'=>$detallereg->umecod,'cantidad'=>$detallereg->cdecan,'IdProducto'=>$detallereg->IdProducto,'codigo'=>$detallereg->procod,'tigcod'=>$detallereg->tigcod);

          // $conceptos[] =  array('concepto' =>$detallereg->cdedes);
            
            
        }
     
     

     
        if($tdocod == '01'){
          $senudoc = DB::tABLE('empresa_negocios')->select('FseEmpresa','FnuEmpresa')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
          $numcomp =  $senudoc->FnuEmpresa+1;
          $sercomp =  $senudoc->FseEmpresa;

          
        }elseif ($tdocod =='03') {
          $senudoc = DB::tABLE('empresa_negocios')->select('BseEmpresa','BnuEmpresa')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
          $numcomp =  $senudoc->BnuEmpresa+1;
          $sercomp =  $senudoc->BseEmpresa;
       
        }

          //Registrar el cliente enviado a través del formulario si no existe
        $cliente = Cliente::UpdateOrCreate(['clinum'=>$clinum,'rucemp'=>Auth::user()->IdEmpresa],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>'','rucemp'=>Auth::user()->IdEmpresa,'tdicod'=>$tdicod]);

        $cpe_cabecera = new cpe_cabecera;
        $cpe_cabecera->tdocod = $tdocod;
        $cpe_cabecera->topcod = '0101';
        $cpe_cabecera->ccafem = $fecha;
        $cpe_cabecera->tdicod = $tdicod;
        $cpe_cabecera->ccandi = $clinum;
        $cpe_cabecera->ccanom = $clinom;
        $cpe_cabecera->moncod = 'PEN';
        $cpe_cabecera->tipcambio = '0.00';
        $cpe_cabecera->ccatvg =  $total/1.1055;
        $cpe_cabecera->ccaigv = $total-($total/1.1055);
        $cpe_cabecera->ccaitv = $total;
        $cpe_cabecera->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $cpe_cabecera->valor = '1';
        $cpe_cabecera->clicod = $cliente->clicod;
        $cpe_cabecera->placa = '';
       // $cpe_cabecera->ped_id = $registro->ped_id;
       // $cpe_cabecera->mes_id =  $registro->med_id;
        $cpe_cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cpe_cabecera->IdEmpresa =  Auth::user()->IdEmpresa;    
      
        $empresanegocio = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);
     
     
        if($tdocod=='01'){
          if( $empresanegocio->FnuEmpresa == $numcomp){
              $modnumcomp = $numcomp+1;
          }else{
              $modnumcomp = $numcomp;
          }

          $empresanegocio->FseEmpresa = $sercomp;
          $empresanegocio->FnuEmpresa = $modnumcomp;
         // $empresanegocio->update();

          $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
          $cpe_cabecera->serdoc= $sercomp;
          $cpe_cabecera->numdoc = $numdoc;
          //$cpe_cabecera->save();
        }elseif($tdocod=='03'){
          if( $empresanegocio->BnuEmpresa == $numcomp){
              $modnumcomp = $numcomp+1;
          }else{
              $modnumcomp = $numcomp;
          }

          $empresanegocio->BseEmpresa = $sercomp;
          $empresanegocio->BnuEmpresa = $modnumcomp;
          //$empresanegocio->update();

          $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
          $cpe_cabecera->serdoc= $sercomp;
          $cpe_cabecera->numdoc = $numdoc;
          //$cpe_cabecera->save();
        }elseif($tdocod=='51'){
          if( $empresanegocio->NumNotCob == $numcomp){
              $modnumcomp = $numcomp+1;
          }else{
              $modnumcomp = $numcomp;
          }

          $empresanegocio->SerNotCob = $sercomp;
          $empresanegocio->NumNotCob = $modnumcomp;
          //$empresanegocio->update();

          $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
          $cpe_cabecera->serdoc= $sercomp;
          $cpe_cabecera->numdoc = $numdoc;
          //$cpe_cabecera->save();
        }

          $empresanegocio->update();

  
          $cpe_cabecera->save();
    
          foreach ($conceptos as $concep) {
            
    
            $detalle = new cpe_detalle;
            $detalle->IdCpe_cabecera =  $cpe_cabecera->IdCpe_cabecera;
            $detalle->umecod = $concep['unidad'];
            $detalle->cdecan = $concep['cantidad'];
            $detalle->procod = $concep['codigo'];
            $detalle->cdepsu = "";
            $detalle->cdedes =  $concep['concepto'];
            $detalle->cdevun =  $concep['precio']/1.1055;
            $detalle->cdepuni = $concep['precio'];
            $detalle->tigcod = $concep['tigcod'];
            $detalle->cdevve = $concep['precio'];
            $detalle->cdepve = $concep['precio']/1.1055;
            $detalle->cdeigv = $concep['precio']-($concep['precio']/1.1055);
            $detalle->fecha_venta = $fecha;
            $detalle->save();
          }
           
    

         
          if($empresa->formato =='TICKET' && $tdocod!='15'){
          for($i=1;$i<=$empresa->imp_venta;$i++){

              if($request->get('opcion')=='0'){
               self::imprimir($cpe_cabecera->IdCpe_cabecera,$cabecera->tdocod);
              }
          }
        }elseif($empresa->formato=='A4'){
            self::generarpdfgeneral($cpe_cabecera->IdCpe_cabecera);
      }

        self::generarcpe($cpe_cabecera->IdCpe_cabecera);


      }    



    public function generarcpe($codfact){

       $cabecera = DB::tABLE('cpe_cabecera')
        ->leftjoin('cliente','cliente.clicod','cpe_cabecera.clicod')
        ->where('IdCpe_cabecera',$codfact)->first();
        

        $detalles = DB::tABLE('cpe_detalle')->where('IdCpe_cabecera',$codfact)->get();
        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

         $numdoc = str_pad($cabecera->numdoc,8,"0", STR_PAD_LEFT);

        $moneda = DB::tABLE('moneda')->where('moncod',$cabecera->moncod)->first();
        $empresa = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

        $rutxmlfile = public_path().'/xml/';
        $rutcdrfile = public_path().'/cdr/';

        $nomxmlcdr=   Auth::user()->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc;


        $filexml =  Auth::user()->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.xml';
        $filecdrzip =  'R-'.Auth::user()->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.zip';

        $see = self::configuracion();
        
        $clidireccion = new Address();
        $clidireccion->setDireccion($cabecera->clidir);

        // Cliente
        $client = new Client();
        $client->setTipoDoc($cabecera->tdicod)
            ->setNumDoc($cabecera->ccandi)
            ->setRznSocial($cabecera->ccanom)
            ->setAddress($clidireccion);

        // Emisor
        $address = new Address();
        $address->setUbigueo($sucursal->ubigeo)
        ->setDepartamento($sucursal->departamento)
        ->setProvincia($sucursal->provincia)
        ->setDistrito($sucursal->distrito)
        ->setUrbanizacion('-')
        ->setDireccion($sucursal->direccion);

        $company = new Company();
        $company->setRuc($empresa->IdEmpresa)
            ->setRazonSocial($empresa->NomEmpresa)
            ->setNombreComercial('-')
            ->setAddress($address);

        // Venta
        $invoice = (new Invoice())
            ->setUblVersion('2.1')
            ->setTipoOperacion($cabecera->topcod) // Catalog. 51
            ->setTipoDoc($cabecera->tdocod)
            ->setSerie($cabecera->serdoc)
            ->setCorrelativo($cabecera->numdoc)
            ->setFechaEmision(new \DateTime($cabecera->ccafem))
            ->setTipoMoneda($cabecera->moncod)
            ->setClient($client)
            ->setMtoOperGravadas($cabecera->ccatvg)
            ->setMtoIGV($cabecera->ccaigv)
            ->setTotalImpuestos($cabecera->ccaigv)
            ->setValorVenta($cabecera->ccatvg)
            ->setSubTotal($cabecera->ccaitv)
            ->setMtoImpVenta($cabecera->ccaitv)
            ->setCompany($company);

      
        $items = [];
        foreach ($detalles as $detalle) {

        
          $item = (new SaleDetail())
            ->setCodProducto($detalle->procod)
            ->setUnidad($detalle->umecod)
            ->setCantidad($detalle->cdecan)
            ->setDescripcion($detalle->cdedes)
            ->setMtoBaseIgv($detalle->cdepve)
            ->setPorcentajeIgv(18.00) // 18%
            ->setIgv($detalle->cdeigv)
            ->setTipAfeIgv($detalle->tigcod)
            ->setTotalImpuestos($detalle->cdeigv)
            ->setMtoValorVenta($detalle->cdepve)
            ->setMtoValorUnitario($detalle->cdevun)
            ->setMtoPrecioUnitario($detalle->cdepuni);

         $items[] = $item;
        }



        $totalletras= MontoLetras::convertir(number_format($cabecera->ccaitv,'2','.',''),$moneda->monnom,'Centimos');

        $legend = (new Legend())
            ->setCode('1000')
            ->setValue($totalletras);

        
        $invoice->setDetails($items)
                ->setLegends([$legend]);

        $result = $see->send($invoice);

        // Guardar XML
        file_put_contents($rutxmlfile.$nomxmlcdr.'.xml',
        $see->getFactory()->getLastXml());

        if (!$result->isSuccess()) {
            $error = $result->getError();
            $actualizar = cpe_cabecera::findOrFail($cabecera->IdCpe_cabecera);
            $actualizar->ccacodsun = $error->getCode();
            $actualizar->ccadessun = $error->getMessage();
            $actualizar->update();

           
        }

        $resultado = $result->getCdrResponse();
        // Guardar CDR
        file_put_contents($rutcdrfile.'R-'.$nomxmlcdr.'.zip', $result->getCdrZip());

        $actualizar = cpe_cabecera::findOrFail($cabecera->IdCpe_cabecera);
        if(!empty($resultado)){
          $actualizar->ccacodsun = $resultado->getCode();
          $actualizar->ccadessun = $resultado->getDescription();
        }
        

        $xml = file_get_contents($rutxmlfile.$nomxmlcdr.'.xml');
                  
        $params = [
          $actualizar->IdEmpresa,
          $actualizar->tdocod,
          $actualizar->serdoc,
          $actualizar->numdoc,
          number_format($actualizar->ccaigv, 2, '.', ''),
          number_format($actualizar->ccaitv, 2, '.', ''),
          $actualizar->ccafem,
          $actualizar->tdicod,
          $actualizar->ccandi,
        ];

        $content = implode('|', $params).'|';

        $DOM = new DOMDocument('1.0', 'utf-8');
        $DOM->loadXML($xml);
        $hash = $DOM->getElementsByTagName('DigestValue')->item(0)->nodeValue;
        $actualizar->codhash = $hash;
        $actualizar->ccaqr = $content;
        $actualizar->update();

   
        return "success";

    }




  public function generarcomunicacionbaja($codfact){

    $cabecera = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$codfact)->first();
    $baja = DB::tABLE('cpe_baja')->where('IdCpe_cabecera',$codfact)->first();
    $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
    $moneda = DB::tABLE('moneda')->where('moncod',$cabecera->moncod)->first();
    $empresa = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

    $BanuEmpresa = $empresa->BanuEmpresa+1;
    $see = self::configuracion();
    
    // Emisor
    $address = new Address();
    $address->setUbigueo($sucursal->ubigeo)
    ->setDepartamento($sucursal->departamento)
    ->setProvincia($sucursal->provincia)
    ->setDistrito($sucursal->distrito)
    ->setUrbanizacion('-')
    ->setDireccion($sucursal->direccion);

    $company = new Company();
    $company->setRuc($empresa->IdEmpresa)
            ->setRazonSocial($empresa->NomEmpresa)
            ->setNombreComercial('')
            ->setAddress($address);


    $detail = new VoidedDetail();
    $detail->setTipoDoc($cabecera->tdocod)
    ->setSerie($cabecera->serdoc)
    ->setCorrelativo($cabecera->numdoc)
    ->setDesMotivoBaja($cabecera->motivo_baja);

    $voided = new Voided();
    $voided->setCorrelativo($BanuEmpresa)
    ->setFecGeneracion(new \DateTime($cabecera->ccafem))
    ->setFecComunicacion(new \DateTime($baja->cbdfco))
    ->setCompany($company)
    ->setDetails([$detail]);

    
    $result = $see->send($voided);

    file_put_contents($voided->getName().'.xml',
    $see->getFactory()->getLastXml());

    
    if (!$result->isSuccess()) {
        $error = $result->getError();
        $actualizar = cpe_cabecera::findOrFail($codfact);
        $actualizar->ccacodsun = $error->getCode();
        $actualizar->ccadessun = $error->getMessage();
        $actualizar->update();

        return "error";
    }


    $ticket = $result->getTicket();
    echo 'Ticket :<strong>' . $ticket .'</strong>';

   

    $res = $see->getStatus($ticket);
 
    $baja = DB::tABLE('cpe_baja')->where('IdCpe_cabecera',$codfact)->first();
    $actualizarbaja = cpe_baja::findOrFail($baja->IdCpe_baja);
    $actualizarbaja->ccasuntick = $ticket;
    $actualizarbaja->ccacodsun = $res->getCode();
    $actualizarbaja->update();

    if (!$res->isSuccess()) {
  
         $error = $res->getError();
    
        $actualizarbaja = cpe_baja::findOrFail($baja->IdCpe_baja);
        $actualizarbaja->ccacodsun = $error->getCode();
        $actualizarbaja->ccadessun = $error->getMessage();
        $actualizarbaja->update();

        return;
    }

  
    $resultado = $res->getCdrResponse();

    file_put_contents($voided->getName().'.zip', $res->getCdrZip());


    return "success";

  }

   public function generarresumen($codfact){

    $cabecera = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$codfact)->first();
    $baja = DB::tABLE('cpe_baja')->where('IdCpe_cabecera',$codfact)->first();
    $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
    $moneda = DB::tABLE('moneda')->where('moncod',$cabecera->moncod)->first();
    $empresa = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

    $ResnuEmpresa = $sucursal->ResnuEmpresa+1;

    $actualizarnum = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);
    $actualizarnum->ResnuEmpresa = $ResnuEmpresa;
    $actualizarnum->update();

    $see = self::configuracion();
    
    // Emisor
    $address = new Address();
    $address->setUbigueo($sucursal->ubigeo)
    ->setDepartamento($sucursal->departamento)
    ->setProvincia($sucursal->provincia)
    ->setDistrito($sucursal->distrito)
    ->setUrbanizacion('-')
    ->setDireccion($sucursal->direccion);

    $company = new Company();
    $company->setRuc($empresa->IdEmpresa)
            ->setRazonSocial($empresa->NomEmpresa)
            ->setNombreComercial('')
            ->setAddress($address);

    $detail = new SummaryDetail();
    $detail->setTipoDoc($cabecera->tdocod)
            ->setSerieNro($cabecera->serdoc.'-'.$cabecera->numdoc)
            ->setEstado('3')
            ->setClienteTipo($cabecera->tdicod)
            ->setClienteNro($cabecera->ccandi)
            ->setTotal($cabecera->ccaitv)
            ->setMtoOperGravadas($cabecera->ccatvg)
            ->setMtoOperInafectas(0.00)
            ->setMtoOperExoneradas($cabecera->ccatve)
            ->setMtoOperExportacion($cabecera->ccatexp)
            ->setMtoOtrosCargos(0.00)
            ->setMtoIGV($cabecera->ccaigv);

    $sum = new Summary();
    $sum->setFecGeneracion(new \DateTime($cabecera->ccafem))
        ->setFecResumen(new \DateTime($baja->cbdfco))
        ->setCorrelativo($ResnuEmpresa)
        ->setCompany($company)
        ->setDetails([$detail]);

    
    $result = $see->send($sum);

    file_put_contents($sum->getName().'.xml',
    $see->getFactory()->getLastXml());

    
    if (!$result->isSuccess()) {
        $error = $result->getError();
        $actualizar = cpe_cabecera::findOrFail($codfact);
        $actualizar->ccacodsun = $error->getCode();
        $actualizar->ccadessun = $error->getMessage();
        $actualizar->update();

        return "error";
    }


    $ticket = $result->getTicket();
    echo 'Ticket :<strong>' . $ticket .'</strong>';

   

    $res = $see->getStatus($ticket);
 
    $baja = DB::tABLE('cpe_baja')->where('IdCpe_cabecera',$codfact)->first();
    $actualizarbaja = cpe_baja::findOrFail($baja->IdCpe_baja);
    $actualizarbaja->ccasuntick = $ticket;
    $actualizarbaja->ccacodsun = $res->getCode();
    $actualizarbaja->update();

      if($res->getCode()=='0'){
      $cabecera = cpe_cabecera::findOrFail($codfact);
      $cabecera->ccacodsun = '8';
      $cabecera->update();
    }

    if (!$res->isSuccess()) {
  
         $error = $res->getError();
    
        $actualizarbaja = cpe_baja::findOrFail($baja->IdCpe_baja);
        $actualizarbaja->ccacodsun = $error->getCode();
        $actualizarbaja->ccadessun = $error->getMessage();
        $actualizarbaja->update();

        return;
    }

  
    $resultado = $res->getCdrResponse();

    file_put_contents($sum->getName().'.zip', $res->getCdrZip());


    return "success";

  }

  public function generarresumendiario($fechacomprobantes,$tipo){

    $fechageneracion = now()->format('Y-m-d');

    $contar = DB::tABLE('cpe_cabecera')->where('tdocod','03')->where('ccafem',$fechacomprobantes)->count();

    if($contar >0){

        if($tipo=='1'){

      $cabeceras = DB::tABLE('cpe_cabecera')
      ->where('tdocod','03')
      ->where('ccafem','=',$fechacomprobantes)
      ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->get();


    }elseif($tipo=='3'){


      $cabeceras = DB::tABLE('cpe_cabecera')->where('tdocod','03')
      ->where('ccafem','=',$fechacomprobantes)
      ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->where('ccabaj','!=','')
      ->where('tdocod','03')
      ->get();

    }

    $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

    $empresa = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

    $ResnuEmpresa = $sucursal->ResnuEmpresa +1;

    $actualizarnum = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);
    $actualizarnum->ResnuEmpresa = $ResnuEmpresa;
    $actualizarnum->update();

    $resumen = new resumenes;
    $resumen->res_fec = $fechageneracion;
    $resumen->res_fec_gen = $fechacomprobantes;

    $see = self::configuracion();
    
    // Emisor
    $address = new Address();
    $address->setUbigueo($sucursal->ubigeo)
    ->setDepartamento($sucursal->departamento)
    ->setProvincia($sucursal->provincia)
    ->setDistrito($sucursal->distrito)
    ->setUrbanizacion('-')
    ->setDireccion($sucursal->direccion);

    $company = new Company();
    $company->setRuc($empresa->IdEmpresa)
            ->setRazonSocial($empresa->NomEmpresa)
            ->setNombreComercial('')
            ->setAddress($address);

    $comprobantes = [];
    foreach ($cabeceras as $cabecera) {
        
        $detail = new SummaryDetail();
        $detail->setTipoDoc($cabecera->tdocod)
            ->setSerieNro($cabecera->serdoc.'-'.$cabecera->numdoc)
            ->setEstado($tipo)
            ->setClienteTipo($cabecera->tdicod)
            ->setClienteNro($cabecera->ccandi)
            ->setTotal($cabecera->ccaitv)
            ->setMtoOperGravadas($cabecera->ccatvg)
            ->setMtoOperInafectas(0.00)
            ->setMtoOperExoneradas($cabecera->ccatve)
            ->setMtoOperExportacion($cabecera->ccatexp)
            ->setMtoOtrosCargos(0.00)
            ->setMtoIGV($cabecera->ccaigv);

        $comprobantes[] = $detail;
    }
 

    $sum = new Summary();
    $sum->setFecGeneracion(new \DateTime($fechacomprobantes))
        ->setFecResumen(new \DateTime($fechageneracion))
        ->setCorrelativo($ResnuEmpresa)
        ->setCompany($company)
        ->setDetails($comprobantes);

    $result = $see->send($sum);

    file_put_contents($sum->getName().'.xml',
    $see->getFactory()->getLastXml());



    if (!$result->isSuccess()) {
        
        $error = $result->getError();
        $resumen->error_code = $error->getCode();
        $resumen->error = $error->getMessage();
       
  
    }


    $ticket = $result->getTicket();
    echo 'Ticket :<strong>' . $ticket .'</strong>';

    $resumen->res_ticket = $ticket;

   



    $resumen->id_empresa_negocio = Auth::user()->id_empresa_negocio;
    $resumen->res_tip =$tipo;

 

 //consultarticket
    $res = $see->getStatus($ticket);
  


    if (!$res->isSuccess()) {
  
        $error = $res->getError();
        $resumen->error_code_ticket = $error->getCode();
        $resumen->error_ticket=$error->getMessage();

    
    }

   
    $resultado = $res->getCdrResponse();


    if($res->getCode()=='0'){
        
      $resumen->res_est = $resultado->getDescription();
      $resumen->res_cod_est =$resultado->getCode();

    }

    file_put_contents($sum->getName().'.zip', $res->getCdrZip());

    $resumen->save();

    foreach ($cabeceras as $comp) {
      
      $cpe = cpe_cabecera::findOrFail($comp->IdCpe_cabecera);
      if($tipo=='1'){
        $cpe->ticket = $ticket;
        
        if($res->getCode()=='0'){
          $cpe->ccacodsun = $resultado->getCode();
        }else{
          $cpe->ccacodsun = $error->getCode();
        }

      }elseif($tipo=='3'){
        $cpe->ticketbaja = $ticket;

         if($res->getCode()=='0'){
            $cpe->ccacodsun = '8';
          }else{
            $cpe->ccacodsun = $error->getCode();
          }

      }
      
     
      $cpe->update();
    }

   
    return "success";
    }else{

       return Redirect::to('/SisFact');
    }

  

  }


  public function consultarticketbaja($codfact){

    $see = self::configuracion();

    $baja = DB::tABLE('cpe_baja')->where('IdCpe_cabecera',$codfact)->first();


    $res = $see->getStatus($baja->ccasuntick);
  

    $actualizarbaja = cpe_baja::findOrFail($baja->IdCpe_baja);
    $actualizarbaja->ccacodsun = $res->getCode();
    $actualizarbaja->update();

    if($res->getCode()=='0'){
      $cabecera = cpe_cabecera::findOrFail($codfact);
      $cabecera->ccacodsun = '8';
      $cabecera->update();
    }

    if (!$res->isSuccess()) {
  
        $error = $res->getError();
    
        $actualizarbaja = cpe_baja::findOrFail($baja->IdCpe_baja);
        $actualizarbaja->ccacodsun = $error->getCode();
        $actualizarbaja->ccadessun = $error->getMessage();
        $actualizarbaja->update();

        return Redirect::to('/SisFact');
    }

  
    $resultado = $res->getCdrResponse();


    

   
    file_put_contents($resultado->getId().'.zip', $res->getCdrZip());


     return Redirect::to('/SisFact');


  }
     

  public function consultarticket($ticket,$tipo){

    $see = self::configuracion();

   
    $res = $see->getStatus($ticket);
   


    if (!$res->isSuccess()) {
  
        $error = $res->getError();
        $resumenes = resumenes::where('res_ticket',$ticket)
        ->update(['error_ticket'=>$error->getMessage(),'error_code_ticket'=>$error->getCode()]);

    }

  
    $resultado = $res->getCdrResponse();


    if($res->getCode()=='0'){
      
        $resumenes = resumenes::where('res_ticket',$ticket)
      ->update(['res_est'=>$resultado->getDescription(),'res_cod_est'=>$resultado->getCode()]);

    }


    file_put_contents($resultado->getId().'.zip', $res->getCdrZip());

    if($tipo=='1'){
        $buscarcomp = DB::tABLE('cpe_cabecera')->where('ticket',$ticket)->where('tdocod','03')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();
      
      foreach ($buscarcomp as $comp) {
          
          $cpe = cpe_cabecera::findOrFail($comp->IdCpe_cabecera);
          if($res->getCode()=='0'){
            $cpe->ccacodsun = $resultado->getCode();
          }else{
            $cpe->ccacodsun = $error->getCode();
          }
          $cpe->update();
        }

    }elseif($tipo =='3'){
      $buscarcomp = DB::tABLE('cpe_cabecera')->where('ticketbaja',$ticket)->where('tdocod','03')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();

        foreach ($buscarcomp as $comp) {
          
          $cpe = cpe_cabecera::findOrFail($comp->IdCpe_cabecera);
          if($res->getCode()=='0'){
            $cpe->ccacodsun = '8';
          }else{
            $cpe->ccacodsun = $error->getCode();
          }
          $cpe->update();
        }

    }
  



     return Redirect::to('/SisFact');


  }

  public function configuracion(){
    
      $rucemp = trim(Auth::user()->IdEmpresa);
      $empresa = Empresa::findOrFail($rucemp);
        $usuario = $rucemp.$empresa->wsusuario;
      $contrasena = $empresa->claveSunat;

      $rutacertificado = public_path().'/certificados/'.$rucemp.'.pem';

      $see = new See();

      if($empresa->produccion=='FE_PRODUCCION'){
          $see->setService(SunatEndpoints::FE_PRODUCCION);
      }else{
          $see->setService(SunatEndpoints::FE_BETA);
      }
 
      if(file_exists($rutacertificado)){
         $see->setCertificate(file_get_contents($rutacertificado));
       }else{
          $see->setCertificate(file_get_contents(public_path().'/certificados/prueba.pem'));
       }
     
      $see->setCredentials($usuario,$contrasena);

      return $see;

    }

    public function descargar($venta,$tipo)
    {

      $cabecera = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$venta)->first();

      $codfact =  Auth::user()->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.str_pad($cabecera->numdoc,8,"0", STR_PAD_LEFT);

      $rucemp = trim(Auth::user()->IdEmpresa);
      $rutpdfile = public_path().'/pdf/';
      $rutxmlfile = public_path().'/xml/';
      $rutcdrfile = public_path().'/cdr/';
     // $file= $rutpdfile.$codfact.'.pdf';
      //$file= $codfact.'.pdf';
      $file= $rutpdfile.$codfact.'.pdf';
      $xml= $rutxmlfile.$codfact.'.xml';
      $cdr= $rutcdrfile.'R-'.$codfact.'.zip';

    if($tipo =='pdf'){

       if (file_exists($file))
      {
        $headers = array(
              'Content-Type: application/pdf',
            );

        return response()->download($file);
      }

    }elseif($tipo =='xml'){
        if (file_exists($xml))
      {
        $headers = array(
              'Content-Type: application/xml',
            );

        return response()->download($xml);
      }

    }elseif($tipo =='cdr'){

      if (file_exists($cdr))
      {
        $headers = array(
              'Content-Type: application/zip',
            );

        return response()->download($cdr);
      }

    }
     

     

         return Redirect::to('/SisFact');
      
    }
    
     public function enviarcorreo($comprobante,$correo){

        $rucemp = trim(Auth::user()->IdEmpresa);
        $empresa = Empresa::findOrFail($rucemp);
      
        $cabpdf = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$comprobante)->first();
        $corcli = $correo;
      
        $cliente = DB::tABLE('cliente')->where('clinum',$cabpdf->ccandi)->where('rucemp',$rucemp)->first();
      
    
        $ruta_pdf = public_path().'/pdf/';
        $ruta_xml = public_path().'/xml/';

        $numdoc = str_pad($cabpdf->numdoc,8,"0", STR_PAD_LEFT);

        $nompdffile=$rucemp.'-'.$cabpdf->tdocod.'-'.$cabpdf->serdoc.'-'.$numdoc.'.pdf'; 
        $nomxmlfile=$rucemp.'-'.$cabpdf->tdocod.'-'.$cabpdf->serdoc.'-'.$numdoc.'.xml'; 


        $destino4 = "";
        $destino5 = "";

         if(file_exists($ruta_pdf.$nompdffile)){
            $destino4=$ruta_pdf.$nompdffile;
         
          }

          if(file_exists($ruta_xml.$nomxmlfile)){
           
            $destino5=$ruta_xml.$nomxmlfile;
          }

      
          $rutpdfile ='/opt/data/comprobantes/'.$rucemp.'/pdf/';
          

                $objDemo = new \stdClass();
              
                $objDemo->tipo_comprobante = 'Comprobante Electrónica';
                $objDemo->sender = 'huancayoimportaciones@gmail.com';
                $objDemo->receiver = $cliente->clinom;
                $objDemo->invoicepdf = $destino4;
                $objDemo->invoicexml = $destino5;
                $objDemo->empresa = $empresa->NomEmpresa;

                
                if(!empty($corcli)){
                  Mail::to($corcli)->send(new FacturacionEmail($objDemo,$destino4,$destino5));
                }
           
 

          return "realizado";
      }

    
    public function generarpdfgeneral($venta){


      $rucemp =Auth::user()->IdEmpresa;
      $rutapdf = public_path().'/pdf/';

      self::generarqr($venta);

      $empresa = Empresa::findOrFail($rucemp);

      $sucursal = DB::tABLE('empresa_negocios')
      ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
     

      $cabpdf = DB::tABLE('cpe_cabecera')
      ->leftjoin('moneda as mon','cpe_cabecera.moncod','=','mon.moncod')
      ->leftjoin('tipo_documento','tipo_documento.tdocod','cpe_cabecera.tdocod')
      ->where('IdCpe_cabecera',$venta)
      ->first();


      $detpdf = DB::tABLE('cpe_detalle')
      ->leftjoin('productos','productos.IdProducto','cpe_detalle.IdProducto')
      ->where('IdCpe_cabecera',$venta)->get();

      $cliente= DB::tABLE('cliente as cli')
      ->leftjoin('cpe_cabecera as c','c.clicod','=','cli.clicod')
      ->where('IdCpe_cabecera','=',$venta)
      ->where('cli.clicod','=',$cabpdf->clicod)
      ->first();
                  
      $nompdffile=$rucemp.'-'.$cabpdf->tdocod.'-'.$cabpdf->serdoc.'-'.str_pad($cabpdf->numdoc,8,"0", STR_PAD_LEFT).'.pdf'; 

      $moneda = DB::tABLE('moneda')->where('moncod','=',$cabpdf->moncod)->first();


      $totalletras= MontoLetras::convertir(number_format($cabpdf->ccaitv,'2','.',''),$moneda->monnom,'Centimos');

      $numdoc = str_pad($cabpdf->numdoc,8,"0", STR_PAD_LEFT);
        
      $qrfile =  'QR-'.$rucemp.'-'.$cabpdf->tdocod.'-'.$cabpdf->serdoc.'-'.$numdoc.'.png'; 

      $imgqr = "/".$qrfile;

      
      $view = \View::make('empresas.comprobantes.general.comprobante', compact('cabpdf','detpdf','cliente','totalletras','empresa','imgqr','sucursal','qrfile'));

                  
      $pdf = \App::make('dompdf.wrapper');
      $contenido = $view->render();
      $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

    
  
        return 'realizado';
    }



     public function imprimir($cpe,$tipdoc){
    
    $rucemp = Auth::user()->IdEmpresa;

    $empresa = Empresa::findOrFail($rucemp);

    $empresanegocios = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);

    $nomdoc = DB::tABLE('tipo_documento')->where('tdocod',$tipdoc)->first();

 
     if($tipdoc == '01' || $tipdoc == '03' || $tipdoc == '13'){
      $cabecera = DB::tABLE('cpe_cabecera as cab')
      ->join('cliente as cli','cab.ccandi','=','cli.clinum')
      ->join('tipo_documento as tdo','cab.tdocod','=','tdo.tdocod')
      ->join('tipo_documento_identidad as tdi','cab.tdicod','=','tdi.tdicod')
      ->join('moneda as mon','cab.moncod','=','mon.moncod')
      ->join('tipo_operacion as top','cab.topcod','=','top.topcod')
      ->leftjoin('users','users.IdUsuario','cab.mozo')
      ->where('IdCpe_cabecera','=',$cpe)
      ->first();
    
        
      $mesa = DB::tABLE('mesas')->where('mes_id',$cabecera->mes_id)->first();

      $pedido = DB::tABLE('pedidos')
      ->leftjoin('users','users.IdUsuario','pedidos.mozo')
      ->where('ped_id',$cabecera->ped_id)
      ->first();


    $totalletras= MontoLetras::convertir(number_format($cabecera->ccaitv,'2','.',''),$cabecera->monnom,'Centimos');
      
     $detalle=DB::tABLE('cpe_detalle as det')
     ->leftjoin('unidad_medida as umed','det.umecod','=','umed.umecod')
     ->where('IdCpe_cabecera','=',$cpe)->get();

     $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

    }

      $impresoras = DB::tABLE('configuracion_impresoras')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();

        

    try { 

      $connector = new WindowsPrintConnector("smb://".$impresoras->ruta);

     
        $printer = new Printer($connector);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        if(file_exists($empresanegocios->logosuc)){
              $logo = EscposImage::load(public_path().'/'.$empresanegocios->logosuc,false);
              $printer->bitImage($logo);
        }
        $printer->setFont(Printer::FONT_A);
        $printer->text("\n".$empresa->NomEmpresa."\n");
        $printer->text($empresanegocios->cabecera."\n");
     //   $printer->text("OUT & PRIDE"."\n");
        $printer->text('RUC:'.$empresa->IdEmpresa."\n");
        $printer->text($empresanegocios->direccion."\n");
        $printer->text($empresanegocios->telefono."\n");
        $printer->text($nomdoc->tdodes."\n");
        $printer->text($cabecera->serdoc."-".$cabecera->numdoc."\n"."\n");
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("Fecha:       ".$cabecera->fecha_hora."\n");
        $printer->text("RUC/DNI:     ".$cabecera->clinum."\n");
        $printer->text("Cliente:     ".$cabecera->ccanom."\n");
        $printer->text("Dirección:   ".$cabecera->clidir."\n"."\n");
        if(!empty($pedido)){
          $printer->text("Pedido:     ".$pedido->ped_tip."\n"."\n");
          $printer->text("Mozo:       ".$pedido->name.' '.$pedido->apeusu."\n");
        }else{
          $printer->text("Pedido:     ".$cabecera->ped_tip."\n"."\n");
          $printer->text("Mozo:       ".$cabecera->name.' '.$cabecera->apeusu."\n");
        }

        if(!empty($mesa)){
          $printer->text("Mesa:       ".$mesa->mes_nom."\n"."\n");
        }
       
      
       
        
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("CONCEPTO   CANTIDAD       IMPORTE"."\n");
        $printer->text("____________________________________"."\n");
        foreach ($detalle as $det) {
         
           $primeralinea = str_pad(substr($det->cdedes,0,17),17," ",STR_PAD_RIGHT);
           $segundalinea = str_pad(substr($det->cdedes,18,34),17," ",STR_PAD_RIGHT);

           $terceralinea = str_pad(substr($det->pronomobs,0,17),17," ",STR_PAD_RIGHT);
           $cuartalinea = str_pad(substr($det->pronomobs,18,34),17," ",STR_PAD_RIGHT);
           $quintalinea = str_pad(substr($det->pronomobs,35,52),17," ",STR_PAD_RIGHT);

           $printer->text($primeralinea."   ".$det->cdecan."  ".$det->cdevve."\n");

            if(!empty(trim($segundalinea))){
             $printer->text($segundalinea."\n");
           }
           
           if(!empty(trim($terceralinea))){
             $printer->text($terceralinea."\n");
           }

          if(!empty(trim($cuartalinea))){
              $printer->text($cuartalinea."\n");
           }

          if(!empty(trim($quintalinea))){
            $printer->text($quintalinea."\n");
          }


           
          
          
        }
       $printer->text("\n");
         $printer->text("_________________________________"."\n");

      $printer->text("OBSERAVACIONES"."\n");
      $printer->text($cabecera->ccaobs."\n"."\n");
      if($cabecera->visa!= 0.00){
      $printer->text("VISA ".$cabecera->simbolo."                  ".$cabecera->visa."\n");
      
      }

      if($cabecera->mastercard!= 0.00){
      $printer->text("MAST ".$cabecera->simbolo."                  ".$cabecera->mastercard."\n");
      
      }

      if($cabecera->efectivo!= 0.00){
      $printer->text("EFEC ".$cabecera->simbolo."                  ".$cabecera->efectivo."\n");
      
      }

       $printer->text("SUBTOTAL: ".$cabecera->simbolo."             ".$cabecera->ccatvg."\n");
       $printer->text("OP. GRAVADA: ".$cabecera->simbolo."          ".$cabecera->ccatvg."\n");
       $printer->text("OP. EXONERADA: ".$cabecera->simbolo."        ".$cabecera->ccatexo."\n");
      // $printer->text("OP. INAFECTA: ".$cabecera->simbolo."         "."0.00"."\n");
       $printer->text("IGV 18%: ".$cabecera->simbolo."              ".$cabecera->ccaigv."\n");
       $printer->text("ICBPER: ".$cabecera->simbolo."               ".$cabecera->icbper."\n");
       $printer->text("TOTAL: ".$cabecera->simbolo."                ".$cabecera->ccaitv."\n"."\n");
       $printer->text("PAGA CON: ".$cabecera->simbolo."             ".$cabecera->paga."\n");
       $printer->text("VUELTO: ".$cabecera->simbolo."               ".$cabecera->vuelto."\n"."\n");
       
       $printer->text($totalletras." ".$cabecera->monnom."\n"."\n");

       if($cabecera->tdocod=='01' || $cabecera->tdocod=='03'){
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $logo1 = EscposImage::load(public_path().'/QR-'.$rucemp.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.str_pad($cabecera->numdoc,8,"0", STR_PAD_LEFT).'.png',false);
           $printer->bitImage($logo1);
        }
      $printer->setJustification(Printer::JUSTIFY_CENTER);
      $printer->text("\n".$empresanegocios->pie."\n");



        $printer->feed();
         
     
        $printer->cut();
         
     
        $printer->pulse();
         
        /*
          Para imprimir realmente, tenemos que "cerrar"
          la conexión con la impresora. Recuerda incluir esto al final de todos los archivos
        */
        $printer->close();
      }catch (\Exception $e) {
           
      
      }
  }

  public function generarqr($comprobante){

    try{
      $cabecera = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$comprobante)->first();


      $numdoc = str_pad($cabecera->numdoc,8,"0", STR_PAD_LEFT);

      $qrfile =  'QR-'.$cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.png'; 

    
      $rucemp = trim(Auth::user()->IdEmpresa);
      $ruta = public_path().'/';
      $file = $ruta.$qrfile;
      
      if(!empty($cabecera->ccaqr)){
         return \QRCode::text($cabecera->ccaqr)->setMargin(1)->setSize(7)->setOutFile($file)->png();
      }else{
        return '';
      }
     

    }catch(\Exception $e){

    }
      

  }

  public function editar_pago($id){

    $cuentas = cuentaspagardetalle::findOrFail($id);
    $cuenta_cabecera = cuentaspagar::findOrFail($cuentas->cue_pag_id);
    $cabecera = compras_cabecera::findOrFail($cuenta_cabecera->com_cab_id);
    $proveedor = Proveedor::findOrFail($cabecera->prov_id);
    $mediospagos = DB::tABLE('medios_pagos')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();

    $medios_cobros = DB::tABLE('medios_pagos')
    ->leftJoin('cuentas_pagar_medios', function ($join) use ($id) {
        $join->on('cuentas_pagar_medios.med_pag_id', '=', 'medios_pagos.id_med_pag')
            ->where('cue_pag_det_id', '=', $id);
    })
    ->get();

    $vendedores = DB::tABLE('users')
    ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
    ->where('role_id','5')
    ->get();

    return view('empresas.cuentaspagar.editarpago',compact('cuentas','vendedores','cabecera','cuenta_cabecera','mediospagos','medios_cobros','proveedor'));

  }


  public function actualizar_pago(Request $request){

    $id = $request->get('cue_pag_det_id');
    $fec_reg = $request->get('fec_reg');
    $fec_dep = $request->get('fec_dep');
    $abonoimporte = $request->get('abonoimporte');
    $detalle = $request->get('detalle');
    $monto = $request->get('monto');
    $medios = $request->get('medio');

    $bus_cue = cuentaspagardetalle::findOrFail($id);

    $cuenta_cabecera = cuentaspagar::findOrFail($bus_cue->cue_pag_id);

    $venta_cabecera = compras_cabecera::findOrFail($cuenta_cabecera->com_cab_id);

    $cuen_cab = DB::tABLE('cuentas_pagar')
    ->where('cue_pag_id',$bus_cue->cue_pag_id)
    ->first();

    $cuen_det = DB::tABLE('cuentas_pagar_detalle')
    ->where('cue_pag_det_id','!=',$id)
    ->where('cue_pag_id',$bus_cue->cue_pag_id)
    ->get();

    $total_abono = 0;

    foreach($cuen_det as $cd){
      $total_abono = $total_abono + $cd->abono;
    }

    $total_abono = $total_abono + $abonoimporte;

    if($total_abono > $cuen_cab->total){

      if($request->ajax()) {

        return response()->json(['estado'=>'ERROR','mensaje' => 'SUMATORIA DE ABONOS ES MAYOR AL TOTAL DE LA DEUDA']);

      }

    }


       if($abonoimporte > '0'){

                $act_det = cuentaspagardetalle::findOrFail($id);
                $act_det->fec_dep = $fec_dep;
                $act_det->abono = $abonoimporte;
                $act_det->comentario = $detalle;
                $act_det->fec_reg = $fec_reg;
                $act_det->update();
              

                DB::tABLE('cuentas_pagar_medios')->where('cue_pag_det_id',$id)->delete();        

                foreach ($medios as $j => $mp) {
                    if($monto[$j]>'0'){
                      DB::tABLE('cuentas_pagar_medios')
                      ->insert([
                        //'numero_recibo'=>$numero_recibo,
                        'med_pag_id'=>$mp,'monto'=>$monto[$j],
                        'cue_pag_det_id'=>$id
                      ]);
                  } 
                }


            
          }else{

            if($request->ajax()) {

              return response()->json(['estado'=>'ERROR','mensaje' => 'EL ABONO DEBE SER MAYOR A 0']);

            }

          }

        self::calcular_pagos($bus_cue->cue_pag_id);

        if($request->ajax()) {
          return response()->json(['estado'=>'CORRECTO','mensaje' => 'Registrado']);
        }

        
  }

   public function calcular_pagos($id){

    $cuenta_cabecera = cuentaspagar::findOrFail($id);
    $cabecera = compras_cabecera::findOrFail($cuenta_cabecera->com_cab_id);
    $cuenta_detalle = cuentaspagardetalle::where('cue_pag_id',$id)
    ->where('est_cue_pag_det','REGISTRADO')
    ->orderby('fec_dep','asc')
    ->get();

    $total = $cuenta_cabecera->total;
    $saldo = 0;
    $abono = 0;

    foreach($cuenta_detalle as $cd){

      $act_detalle = cuentaspagardetalle::findOrFail($cd->cue_pag_det_id);
      $act_detalle->total_detalle = $total;
      $act_detalle->saldo_detalle = $total - $cd->abono;
      $act_detalle->update();

      $abono = $abono + $cd->abono;
      $total = $total - $cd->abono;

    }

    $saldo = $cuenta_cabecera->total - $abono;

    $act_cabecera = cuentaspagar::findOrFail($id);
    $act_cabecera->abono = $abono;
    $act_cabecera->saldo = $saldo;
    $act_cabecera->update();

    return 'CALCULADO';

 
  }

  public function eliminar_cuenta_pagar(Request $request){

    $eliminar = DB::tABLE('cuentas_pagar_detalle')->where('cue_pag_det_id',$request->get('id'))->update(['est_cue_pag_det'=>'ELIMINADO']);

    $bus_cue = cuentaspagardetalle::findOrFail($request->get('id'));

    self::calcular_pagos($bus_cue->cue_pag_id);

    return Redirect::to('/cuentaspagar');

  }


}
