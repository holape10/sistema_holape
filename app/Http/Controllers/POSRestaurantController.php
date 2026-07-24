<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\pedidos;
use MasterSoft\pedidos_detalle;
use MasterSoft\TipoIGV;
use MasterSoft\Empresa;
use MasterSoft\Cliente;
use MasterSoft\caja;
use MasterSoft\cpe_cabecera;
use MasterSoft\cuentascobrar;
use MasterSoft\movimientoscaja;
use MasterSoft\movimientosbancarios;
use MasterSoft\cuentascobrardetalle;
use MasterSoft\documento_relacionado;
use MasterSoft\unidad_medida;
use MasterSoft\cpe_detalle;
use MasterSoft\usuario_pedidos;
use MasterSoft\usuario_facturacion;
use MasterSoft\cpe_baja;
use MasterSoft\mesas;
use MasterSoft\movimientos;
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
use DB;
use Hash;

class POSRestaurantController extends Controller
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




    public function index()
    {
        $rucemp = trim(Auth::user()->IdEmpresa);
        $categorias = DB::tABLE('categorias')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->orderby('cat_nom','asc')->get();
        $comprobante = DB::tABLE('tipo_documento')->get();
        $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();

        return view('empresas.puntosventas.restaurant',compact('categorias','comprobante','tipodocumento'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
     public function store(Request $request)
    {

        if(Auth::user()->turno =='Cerrado'){

          return Redirect::to('/estacionamientos')->with('danger','SE REQUIERE APERTURAR TURNO');

        }


        $rucemp = trim(Auth::user()->IdEmpresa);
        $mediopago = $request->get('mediopago');

        $tdicod = $request->get('tdicod');
        $cliruc = $request->get('clinum');
        $pedido = $request->get('txtPedId');
        $pedido_est= pedidos::findOrFail($pedido);

        $tarifa = $request->get('tarifa');
        $buscartarifa = DB::tABLE('tarifas')->where('id_tarifa',$tarifa)->first();
        $cuenta = $request->get('cuen_ban_id');

        $clinom = $request->get('clinom');
        $clidir = $request->get('clidir');
        $clicor = $request->get('clicor');
        $grav = $request->get('subtotal');
        $igv = $request->get('igv');
        $total = $request->get('total');
        $mondoc = 'PEN';
        $tdocod = $request->get('tdocod');
        $fecemi = $request->get('fecEmi');

        $fecha = $request->get('fecEmi');
        $fecven = $request->get('fecVen');
        $operacion = $request->get('operacion');

        $cuentatarjeta = DB::tABLE('medios_pagos')->where('id_med_pag',$mediopago)->first();
        $comision=1;
        if($request->get('estadopago') =='contado'){

            $totalcredito =0;
            $totalcontado=$request->get('total');
            $estadopago = $request->get('estadopago');
            //$visa=0;
            //$mastercard=0;
            //$transferencia=0;
            if(empty($cuentatarjeta)){
              $cuen_ban_id = $cuenta;
            }else{
              $cuen_ban_id = $cuentatarjeta->cuen_ban_id;
              $comision = $cuentatarjeta->comision;
            }
            
            
        }else{

            $efectivo = 0;
            //$visa = 0;
            //$mastercard= 0;
            $cuen_ban_id = $cuenta;
            $estadopago = $request->get('estadopago');
            //$transferencia= 0;
            $totalcredito = $request->get('total');
            $totalcontado = 0;

        }
       
        
        $mesa= $request->get('txtMesaId');
        $topcod = '0101';


        if($tdocod == '01'){
          $senudoc = DB::tABLE('empresa_negocios')->select('FseEmpresa','FnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $numcomp =  $senudoc->FnuEmpresa+1;
          $sercomp =  $senudoc->FseEmpresa;
        }elseif ($tdocod =='03') {
          $senudoc = DB::tABLE('empresa_negocios')->select('BseEmpresa','BnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $numcomp =  $senudoc->BnuEmpresa+1;
          $sercomp =  $senudoc->BseEmpresa;
        }elseif ($tdocod =='13') {
          $senudoc = DB::tABLE('empresa_negocios')->select('SerNota','NumNota')->where('IdEmpresa','=',$rucemp)->first();
          $numcomp =  $senudoc->NumNota+1;
          $sercomp =  $senudoc->SerNota;
        }

 

        $moneda = DB::tABLE('moneda')->where('moncod','=',$mondoc)->first();


      //Registrar el tipo de cambio enviado a través del formulario si no existe.
        if($mondoc !='PEN'){
          $camdoc = $request->get('camdoc');
          $tipcam = tipocambio::FirstOrCreate(['FecTipCambio'=>$fecemi],['CamCompra'=>$camdoc]);
        }else{
          $camdoc=0;
        }

      //Registrar el cliente enviado a través del formulario si no existe
        $cliente = Cliente::FirstOrCreate(['clinum'=>$cliruc,'rucemp'=>$rucemp],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$clicor,'rucemp'=>$rucemp,'tdicod'=>$tdicod]);


      //Crear un objeto de la cabecera del comprobante para registrar los datos de la cabecera
        $cabecera = new cpe_cabecera;
        $cabecera->tdocod = $request->get('tdocod');
        $cabecera->topcod = $topcod;


        if($request->get('estadopago') =='credito15'){
          $cabecera->ccafve = date('Y-m-d',strtotime($fecemi."+ 15 days"));
        }elseif($request->get('estadopago') =='credito30'){
          $cabecera->ccafve = date('Y-m-d',strtotime($fecemi."+ 30 days"));
        }else{
          $cabecera->ccafve = $request->get('fecVen');
        }

        $cabecera->ccafem = $request->get('fecEmi');
        
       
        $cabecera->tdicod = $tdicod;
        $cabecera->ccandi = $cliruc;
        $cabecera->ccanom = $clinom;
        $cabecera->moncod = $mondoc;
        $cabecera->tipcambio = $camdoc;
        $cabecera->ccatvg =  $grav;
        $cabecera->ccaigv = $igv;
        $cabecera->ccaitv = $total;
        $cabecera->mediopago = $mediopago;
        $cabecera->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        //$cabecera->efectivo = $efectivo;
        //$cabecera->visa = $visa;
        //$cabecera->mastercard = $mastercard;
        //$cabecera->transferencia = $transferencia;
        $cabecera->operacion = $operacion;
        $cabecera->cuen_ban_id = $cuen_ban_id;
        $cabecera->totalcontado = $totalcontado;
        $cabecera->estadopago = $estadopago;
        $cabecera->totalcredito = $totalcredito;
        $cabecera->clicod = $cliente->clicod;
        $cabecera->placa = $pedido_est->placa;
        $cabecera->ped_id = $pedido;
        $cabecera->mes_id =  $mesa;

        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->IdEmpresa =  $rucemp;

       
        $empresa = Empresa::findOrFail($rucemp);
        $empresanegocio = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);
     
        $formato = $empresa->formato;
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
          $cabecera->serdoc= $sercomp;
          $cabecera->numdoc = $numdoc;
          //$cabecera->save();
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
          $cabecera->serdoc= $sercomp;
          $cabecera->numdoc = $numdoc;
          //$cabecera->save();
        }elseif($tdocod=='13'){
          if( $empresanegocio->notanumero == $numcomp){
              $modnumcomp = $numcomp+1;
          }else{
              $modnumcomp = $numcomp;
          }

          $empresanegocio->SerNota = $sercomp;
          $empresanegocio->NumNota = $modnumcomp;
          //$empresa->update();

          $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
          $cabecera->serdoc= $sercomp;
          $cabecera->numdoc = $numdoc;
          //$cabecera->save();
        }

           $empresanegocio->update();

          $empresa->update();
          $cabecera->save();
          $codfact = $cabecera->IdCpe_cabecera; 

          $usuario_facturacion = new usuario_facturacion;
          $usuario_facturacion->IdCpe_cabecera = $codfact;
          $usuario_facturacion->id_turno = Auth::user()->id_turno;
          $usuario_facturacion->ped_id = $pedido;
          $usuario_facturacion->id_empresa_negocio = Auth::user()->id_empresa_negocio;
          $usuario_facturacion->IdEmpresa = Auth::user()->IdEmpresa;
          $usuario_facturacion->referencia = "Registro";
          $usuario_facturacion->save();


          $mesa_est= mesas::findOrFail($mesa);
          $mesa_est->mes_est = 'Libre';
          $mesa_est->update();

        
          $pedido_est->ped_est = 'Cerrado';
          $pedido_est->IdUsuarioCob = Auth::user()->IdUsuario;
          $pedido_est->update();
          $codfact = $cabecera->IdCpe_cabecera;

            $pedidosdetalle = DB::tABLE('pedidos_detalle')->where('ped_id',$pedido)->first();
        
            $unidades = 'ZZ';

            $detalle = new cpe_detalle;
            $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
            $detalle->umecod = $unidades;
            $detalle->cdecan = $pedidosdetalle->cantidad;
            $codproducto = "SE01";
            $detalle->procod = $codproducto;
            $detalle->cdepsu = "";
            $detalle->cdedes =  $buscartarifa->descripcion;
            $detalle->cdevun =  $pedidosdetalle->provunitem;
            $detalle->cdepuni = $pedidosdetalle->propunitem;
            $detalle->tigcod = '10';
            $detalle->cdevve = $total;
            $detalle->cdepve = $grav;
            $detalle->cdeigv = $total-$grav;
            $detalle->fecha_venta = $fecemi;
            $detalle->save();
    

            if($request->get('estadopago') =='credito15'){

              $cuentacobrar = new cuentascobrar;
              $cuentacobrar->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
              $cuentacobrar->clicod = $cabecera->clicod;
              $cuentacobrar->fec_ven = date('Y-m-d',strtotime($fecemi."+ 15 days"));
              $cuentacobrar->abono = '0.00';
              $cuentacobrar->estado_cob = 'pendiente';
              $cuentacobrar->total = $cabecera->ccaitv;
              $cuentacobrar->placa = $pedido_est->placa;
              $cuentacobrar->saldo = $cabecera->ccaitv;
              //$cuentacobrar->cuen_ban_id =
              //$cuentacobrar->num_oper =
              //$cuentacobrar->fecha_deposito =
              $cuentacobrar->id_empresa_negocio = Auth::user()->id_empresa_negocio;
              $cuentacobrar->save();


              //$cuentacobrardet = new cuentascobrardetalle;
              //$cuentacobrardet->cue_cob_id =
              //$cuentacobrardet->fec_dep =
              //$cuentacobrardet->abono =
              //$cuentacobrardet->save();

            }elseif($request->get('estadopago') =='credito30'){

              $cuentacobrar = new cuentascobrar;
              $cuentacobrar->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
              $cuentacobrar->clicod = $cabecera->clicod;
              $cuentacobrar->fec_ven = date('Y-m-d',strtotime($fecemi."+ 30 days"));
              $cuentacobrar->abono = '0.00';
              $cuentacobrar->estado_cob = 'pendiente';
              $cuentacobrar->placa = $pedido_est->placa;
                $cuentacobrar->total = $cabecera->ccaitv;
              $cuentacobrar->saldo = $cabecera->ccaitv;
              //$cuentacobrar->cuen_ban_id =
              //$cuentacobrar->num_oper =
              //$cuentacobrar->fecha_deposito =
              $cuentacobrar->id_empresa_negocio = Auth::user()->id_empresa_negocio;
              $cuentacobrar->save();


              //$cuentacobrardet = new cuentascobrardetalle;
              //$cuentacobrardet->cue_cob_id =
              //$cuentacobrardet->fec_dep =
              //$cuentacobrardet->abono =
              //$cuentacobrardet->save();

            }

        
        // Monto en letras
        $totalletras= MontoLetras::convertir(number_format($cabecera->ccaitv,'2','.',''),$moneda->monnom,'Centimos');

        //$cabfile es el nombre con el cual se guarda el archivo que contiene los datos del comproabnte
        $cabfile =  $cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.json';
        $filepdf=  $cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.pdf';
        $filecdr =  'R-'.$cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.xml';
        $filexml =  $cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.xml';
        $filecdrzip =  'R-'.$cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.zip';
        $filexmlzip =  $cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.zip';
     

        $qrfile =  'QR-'.$cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.png'; 
        
        //Consultar los datos de la empresa emisora
        $datemp = DB::table("empresa")->where("IdEmpresa","=",$rucemp)->first();

        if($tdocod=='01'){
           $nomcomp = "factura";
        }elseif($tdocod=='03'){
           $nomcomp = "boleta";
        }elseif($tdocod =='13') {
          $nomcomp = "nota venta";
        }

     
        //$raiz = '/opt/data/comprobantes/'.$rucemp.'/json/';
        //$rutacdr = '/opt/data/comprobantes/'.$rucemp.'/cdr/';
        //$rutaxml = '/opt/data/comprobantes/'.$rucemp.'/xml/';
        //$rutapdf = '/opt/data/comprobantes/'.$rucemp.'/pdf/';
        
        $raiz =  public_path().'/json/';
        $rutacdr = public_path().'/cdr/';
        $rutaxml = public_path().'/xml/';
        $rutapdf = public_path().'/pdf/';

        //$data_json = json_encode($data,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

       // $archivo = fopen($raiz.$cabfile, "a");
       // fputs($archivo,$data_json);
       // fclose($archivo);
        

      if($request->get('estadopago') =='contado' &&  !empty($cuen_ban_id)){

              $buscar = movimientosbancarios::where('cuen_ban_id',$cuen_ban_id)->get();

              $contar = movimientosbancarios::where('cuen_ban_id',$cuen_ban_id)->count();

              $movimiento = new movimientosbancarios;
              $movimiento->mov_tip = 'debe';
              $movimiento->concepto_id = $cuentatarjeta->concepto_id;
              $movimiento->doc_id =  $cabecera->tdocod;
              $movimiento->mov_num_doc = $cabecera->serdoc.'-'.$cabecera->numdoc;
              $movimiento->cuen_ban_id = $cuen_ban_id;
              $movimiento->IdUsuario = Auth::user()->IdUsuario;
              $movimiento->id_turno = Auth::user()->id_turno;
              // $movimiento->mov_num_oper = $request->get('mov_num_oper');
              if($comision ==1){
                $movimiento->importe = $cabecera->ccaitv;
              }else{
                $movimiento->importe = $cabecera->ccaitv-($cabecera->ccaitv*(($comision/100)*1.18)); 
              }
              
              $movimiento->estado = '1';
              $movimiento->mov_fecha = $cabecera->ccafem;
              $movimiento->clicod = $cabecera->clicod;
              $movimiento->registro = 'Registrado';
       
                if($contar==0){
                  $totalsaldo =  $total;
                }else{
                  $totalsaldo = $buscar->last()->saldo + $total;
                }
                

              $movimiento->saldo = $totalsaldo;
              $movimiento->IdEmpresa = Auth::user()->IdEmpresa;
              $movimiento->id_empresa_negocio = Auth::user()->id_empresa_negocio;
              $movimiento->save();


      }
    

        if($tdocod !='13'){
         //$leer_respuesta = self::webserviceonline($data_json);


         //$retornarpdf = self::webservicepdf($tdocod,$sercomp,$numcomp,$codfact);
      
          $comp = cpe_cabecera::findOrFail($codfact);

          if(!empty($leer_respuesta['codigo_hash'])){
            
          $comp->codhash = $leer_respuesta['codigo_hash'];
          $comp->error = substr($leer_respuesta['errors'],2,250);
          $comp->ccacodsun = $leer_respuesta['estado_documento'];
          $comp->ccadessun = substr($leer_respuesta['sunat_description'],2,250);
          $comp->ccasunrescod = substr($leer_respuesta['sunat_responsecode'],2,250);
          $comp->ccaenlace = $leer_respuesta['url'];
          $comp->ccasunnot = $leer_respuesta['sunat_note'];
          $comp->ccaqr = $leer_respuesta['cadena_para_codigo_qr'];
        }
          /*if(!empty($comp->ccaqr)){
            self::generar_qr($qrfile,$comp->ccaqr);
          }*/
          
          $comp->update();

      if(!empty($leer_respuesta['xml_enviado'])){
          $dataxml = base64_decode($leer_respuesta['xml_enviado']);
          file_put_contents($rutaxml.$filexmlzip,$dataxml);

      }

      if(!empty($leer_respuesta['cdr_sunat'])){
          $datacdr = base64_decode($leer_respuesta['cdr_sunat']);
          file_put_contents($rutacdr.$filecdrzip,$datacdr);
      }          
          return Redirect::to('/estacionamientos/'.$tdocod.'/'.$codfact);
          
        

      }else{
      
         return Redirect::to('/estacionamientos/'.$tdocod.'/'.$codfact);
      }


    }

    public function restaurantpunto(Request $request)
    {

      
       $rucemp = trim(Auth::user()->IdEmpresa);

      //Datos del cliente
        $tdicod = $request->get('tdicod');
        $tipo_venta = $request->get('tipoventa');
        $cliruc = $request->get('clinum');
        $fecha = $request->get('fecEmi');
        $clinom = $request->get('clinom');
        $clidir = $request->get('clidir');
        $clicor = $request->get('clicor');
        $grav = $request->get('subtotal');
        $igv = $request->get('igv');
        $total = $request->get('total');
        $mondoc = 'PEN';
        $tdocod = $request->get('tdocod');
        $fecemi = $request->get('fecEmi');
        $fecven = $request->get('fecVen');
        $tippago = $request->get('txtTipPag');
        $efectivo = $request->get('efectivo1');
        $visa = $request->get('visa');
        $mastercard = $request->get('mastercard');
       
        $topcod = '0101';


        if($tdocod == '01'){
          $senudoc = DB::tABLE('empresa_negocios')->select('FseEmpresa','FnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $numcomp =  $senudoc->FnuEmpresa+1;
          $sercomp =  $senudoc->FseEmpresa;
        }elseif ($tdocod =='03') {
          $senudoc = DB::tABLE('empresa_negocios')->select('BseEmpresa','BnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $numcomp =  $senudoc->BnuEmpresa+1;
          $sercomp =  $senudoc->BseEmpresa;
        }elseif ($tdocod =='13') {
          $senudoc = DB::tABLE('empresa_negocios')->select('SerNota','NumNota')->where('IdEmpresa','=',$rucemp)->first();
          $numcomp =  $senudoc->NumNota+1;
          $sercomp =  $senudoc->SerNota;
        }


       

        $moneda = DB::tABLE('moneda')->where('moncod','=',$mondoc)->first();


      //Registrar el tipo de cambio enviado a través del formulario si no existe.
        if($mondoc !='PEN'){
          $camdoc = $request->get('camdoc');
          $tipcam = tipocambio::FirstOrCreate(['FecTipCambio'=>$fecemi],['CamCompra'=>$camdoc]);
        }else{
          $camdoc=0;
        }

      //Registrar el cliente enviado a través del formulario si no existe
        $cliente = Cliente::FirstOrCreate(['clinum'=>$cliruc,'rucemp'=>$rucemp],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$clicor,'rucemp'=>$rucemp,'tdicod'=>$tdicod]);


      //Crear un objeto de la cabecera del comprobante para registrar los datos de la cabecera
        $cabecera = new cpe_cabecera;
        $cabecera->tdocod = $request->get('tdocod');
        $cabecera->topcod = $topcod;
        $cabecera->ccafem = $request->get('fecEmi');
        $cabecera->ccafve = $request->get('fecVen');
       // $cabecera->ccaobs = $request->get('obser');
        //$cabecera->ccacde = $request->get();
        $cabecera->tdicod = $tdicod;
        $cabecera->ccandi = $cliruc;
        $cabecera->ccanom = $clinom;
        $cabecera->moncod = $mondoc;
        $cabecera->tipo_pago = $tippago;
        $cabecera->tipcambio = $camdoc;
        $cabecera->ccatvg =  $grav;
        $cabecera->ccaigv = $igv;
        $cabecera->ccaitv = $total;
        $cabecera->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $cabecera->visa =  $visa;
        $cabecera->tipo_venta =  $tipo_venta;
        $cabecera->efectivo = $efectivo;
        $cabecera->mastercard = $mastercard;
        $cabecera->clicod = $cliente->clicod;
      

        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->IdEmpresa =  $rucemp;

        $empresa = Empresa::findOrFail($rucemp);
        $empresanegocio = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);
     
        $formato = $empresa->formato;
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
          $cabecera->serdoc= $sercomp;
          $cabecera->numdoc = $numdoc;
          //$cabecera->save();
        }elseif($tdocod=='03'){
          if( $empresanegocio->FnuEmpresa == $numcomp){
              $modnumcomp = $numcomp+1;
          }else{
              $modnumcomp = $numcomp;
          }

          $empresanegocio->BseEmpresa = $sercomp;
          $empresanegocio->BnuEmpresa = $modnumcomp;
          //$empresanegocio->update();

          $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
          $cabecera->serdoc= $sercomp;
          $cabecera->numdoc = $numdoc;
          //$cabecera->save();
        }elseif($tdocod=='13'){
          if( $empresanegocio->notanumero == $numcomp){
              $modnumcomp = $numcomp+1;
          }else{
              $modnumcomp = $numcomp;
          }

          $empresanegocio->SerNota = $sercomp;
          $empresanegocio->NumNota = $modnumcomp;
          //$empresa->update();

          $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
          $cabecera->serdoc= $sercomp;
          $cabecera->numdoc = $numdoc;
          //$cabecera->save();
        }

           $empresanegocio->update();

          $empresa->update();
          $cabecera->save();
          $codfact = $cabecera->IdCpe_cabecera; 


        $unidades = $request->get('unid');
        $proid = $request->get('proid');
        $detpro = $request->get('pronom');
        $vunit = $request->get('provun');
        $puni = $request->get('propun');
        $vtot = $request->get('itemtotal');
        $cantidades = $request->get('cant');

        //REGISTRAR VENTAS COMO INGRESOS
        $ingresos = new gastos_cabecera;
        $ingresos->gast_doc_ser = $sercomp;
        $ingresos->gast_doc_num = $numdoc;
        $ingresos->gast_fec = $request->get('fecEmi');
        $ingresos->gast_fec_ven = $request->get('fecVen');
        $ingresos->mon_id = $mondoc;
        $ingresos->prov_id = $cliente->clicod;
        $ingresos->tip_cam = $camdoc;
        $ingresos->tot_igv = $igv;
        $ingresos->tot_grav = $grav;
        $ingresos->tot_grat = "0.00";
        $ingresos->tot_exon = "0.00";
        $ingresos->tot_inaf = "0.00";
        $ingresos->tot_desc_por = "0.00";
        $ingresos->tot_desc = "0.00";
        $ingresos->tot_otr_car ="0.00";
        $ingresos->tot_exp = "0.00";
        $ingresos->tot_otr_tri ="0.00";
        $ingresos->total_gast = $total;
        $ingresos->gast_obs = "INGRESO POR VENTA - ".$sercomp.'-'.$numdoc;
        $ingresos->tdocod = $request->get('tdocod');
        $ingresos->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $ingresos->tipo_movimiento = "INGRESO";
        $ingresos->IdCpe_cabecera = $codfact;
        $ingresos->save();


        //Generar el detalle del comprobante
        foreach($unidades as $index => $ume ) {


            $detalle = new cpe_detalle;
            $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
            $detalle->umecod = $ume;
            $detalle->cdecan = $cantidades[$index];

            $codpro = productos::findOrFail($proid[$index]);
            $codproducto = $codpro->procod;
            $detalle->procod = $codproducto;
            $detalle->cdepsu = "";
            $detalle->cdedes = $detpro[$index];
            $detalle->cdevun = $vunit[$index];
            $detalle->cdepuni = $puni[$index];

            $detalle->tigcod = '10';
            $vsub = $vunit[$index] * $cantidades[$index];
            $detalle->cdevve = $vtot[$index];
            $detalle->cdepve = $vsub;
            $vigv = $vtot[$index] - $vsub;
            $detalle->cdeigv = $vigv;
            $detalle->fecha_venta = $fecemi;
            $detalle->save();


            //Guardar en una variable los items en el archivo .det
            $codumecin = unidad_medida::findOrFail($ume);

            $detallejson[] = array("COD_ITEM"       => $detalle->procod,
                        "COD_UNID_ITEM"             => $codumecin->umecin,
                        "CANT_UNID_ITEM"            => $detalle->cdecan,
                        "VAL_UNIT_ITEM"             => $detalle->cdevun,      
                        "PRC_VTA_UNIT_ITEM"         => $detalle->cdepuni,
                        "VAL_VTA_ITEM"              => $detalle->cdepve,
                        "MNT_BRUTO"                 => $detalle->cdepve,
                        "MNT_PV_ITEM"               => $detalle->cdevve,
                        "COD_TIP_PRC_VTA"           => "01",
                        "COD_TIP_AFECT_IGV_ITEM"    => $detalle->tigcod,
                        "COD_TRIB_IGV_ITEM"         => "1000",
                        "POR_IGV_ITEM"              => "18",
                        "MNT_IGV_ITEM"              => number_format($detalle->cdeigv,'2','.',''),     
                        "TXT_DESC_ITEM"             => $detalle->cdedes,                 
                        "DET_VAL_ADIC01"            => "",
                        "DET_VAL_ADIC02"            => "",
                        "DET_VAL_ADIC03"            => "",
                        "DET_VAL_ADIC04"            => "",
                        "DET_VAL_ADIC05"            => "",
                        "DET_VAL_ADIC06"            => "",
                        "DET_VAL_ADIC07"            => "",
                        "DET_VAL_ADIC08"            => "",
                        "DET_VAL_ADIC09"            => "",
                        "DET_VAL_ADIC10"            => "");

          

              $IdProducto = DB::tABLE('productos')->WHERE('procod',$codproducto)->where('IdEmpresa',trim(Auth::user()->IdEmpresa))->first();

                 $buscarstock = DB::tABLE('producto_almacen')->where('IdProducto',$IdProducto->IdProducto)->where('id_almacen',Auth::user()->id_almacen)->first();

        
                $calcstock = $buscarstock->stock-$cantidades[$index];
                DB::tABLE('producto_almacen')
                ->where('IdProducto',$IdProducto->IdProducto)
                ->where('id_almacen',Auth::user()->id_almacen)
                ->update(['stock' =>$calcstock]);


              $ingresos_det = new gastos_detalle;
              $ingresos_det->pro_id = $IdProducto->IdProducto;
              $ingresos_det->val_uni = $vunit[$index];
              $ingresos_det->pre_uni = $puni[$index];
              $ingresos_det->tip_igv = $detalle->tigcod;
              $ingresos_det->igv = $vigv;
              $ingresos_det->det_gasto = $detpro[$index];
              $ingresos_det->subtotal=$vsub;
              $ingresos_det->total= $vtot[$index];
              $ingresos_det->cantidad= $cantidades[$index];
              $ingresos_det->ume_cod= $ume;
              $ingresos_det->gast_cab_id= $ingresos->gast_cab_id;
              $ingresos_det->pre_ven_min= "0.00";
              $ingresos_det->pre_ven_may= "0.00";
              $ingresos_det->IdEmpresa= Auth::user()->IdEmpresa;
              $ingresos_det->save();


                $movimiento = new movimientos;
                $movimiento->mov_fec = $fecha; 
                $movimiento->mov_tip = 'E';
                $movimiento->mov_mot = 'Venta';
                $movimiento->cantidad = $cantidades[$index];
                $movimiento->unidad = $ume;
                $movimiento->comprobante = $sercomp.'-'.$numdoc;
                $movimiento->IdEmpresa = $rucemp;
                $movimiento->IdProducto = $IdProducto->IdProducto;
                $movimiento->observacion = "Venta desde Punto de Venta";
                $movimiento->IdUsuario = Auth::user()->IdUsuario;
                $movimiento->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
                $movimiento->save();

             
                $stock_prod =productos::findOrFail($IdProducto->IdProducto);
                $stock_prod->stock = $stock_prod->stock-$cantidades[$index];;
                $stock_prod->update();

            

        }

        // Monto en letras
        $totalletras= MontoLetras::convertir(number_format($cabecera->ccaitv,'2','.',''),$moneda->monnom,'Centimos');

        //$cabfile es el nombre con el cual se guarda el archivo que contiene los datos del comproabnte
        $cabfile =  $cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.json';
        $filepdf=  $cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.pdf';
        $filecdr =  'R-'.$cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.xml';
        $filexml =  $cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.xml';
        $filecdrzip =  'R-'.$cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.zip';
        $filexmlzip =  $cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.zip';
     

        $qrfile =  'QR-'.$cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.png'; 

        //Consultar los datos de la empresa emisora
        $datemp = DB::table("empresa")->where("IdEmpresa","=",$rucemp)->first();

        if($tdocod=='01'){
           $nomcomp = "factura";
        }elseif($tdocod=='03'){
           $nomcomp = "boleta";
        }elseif($tdocod =='13') {
          $nomcomp = "nota venta";
        }

        //inicio json
          $data = array(
                  "TOKEN"                             =>$empresa->wscontrasena,
                  "COD_TIP_NIF_EMIS"                  => "6",
                  "NUM_NIF_EMIS"                      => $rucemp,
                  "NOM_RZN_SOC_EMIS"                  => $empresa->NomEmpresa,
                  "NOM_COMER_EMIS"                    => "",
                  "COD_UBI_EMIS"                      => "",
                  "TXT_DMCL_FISC_EMIS"                => $empresa->DirEmpresa,
                  "COD_TIP_NIF_RECP"                  => $tdicod,
                  "NUM_NIF_RECP"                      => $cliruc,
                  "NOM_RZN_SOC_RECP"                  => $clinom,
                  "TXT_DMCL_FISC_RECEP"               => $clidir,
                  "FEC_EMIS"                          => $fecemi,
                  "FEC_VENCIMIENTO"                   => $fecven,
                  "COD_TIP_CPE"                       => $tdocod,
                  "NUM_SERIE_CPE"                     => $sercomp,
                  "NUM_CORRE_CPE"                     => $numcomp,
                  "COD_MND"                           => $mondoc,
                  "MailEnvio"                         => $clicor,
                  "COD_PRCD_CARGA"                    => "001",
                  "MNT_TOT_GRAVADO"                   => $grav,
                  "MNT_TOT_INAFECTO"                  => "",
                  "MNT_TOT_EXONERADO"                 => "",
                  "MNT_TOT_GRATUITO"                  => "",
                  "MNT_TOT_TRIB_IGV"                  => $igv,
                  "MNT_TOT"                           => $total, 
                  "COD_PTO_VENTA"                     => "",
                  "ENVIAR_A_SUNAT"                    => "true",
                  "RETORNA_XML_ENVIO"                 => "true",
                  "RETORNA_XML_CDR"                   => "true",
                  "RETORNA_PDF"                       => "true",
                  "COD_FORM_IMPR"                     => "004",
                  "TXT_VERS_UBL"                      =>"2.1",
                  "TXT_VERS_ESTRUCT_UBL"              =>"2.0",
                  "COD_ANEXO_EMIS"                    => $empresanegocio->codigofiscal,
                  "COD_TIP_OPE_SUNAT"                 => $topcod,
                  "items"                             =>$detallejson,
                 
              );


        $raiz =  public_path().'/json/';
        $rutacdr = public_path().'/cdr/';
        $rutaxml = public_path().'/xml/';
        $rutapdf = public_path().'/pdf/';

        $data_json = json_encode($data,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $archivo = fopen($raiz.$cabfile, "a");
        fputs($archivo,$data_json);
        fclose($archivo);
        
        
        if($tdocod !='13'){
          $leer_respuesta = self::webserviceonline($data_json);
      
          $comp = cpe_cabecera::findOrFail($codfact);

          if(!empty($leer_respuesta['codigo_hash'])){
            
              $comp->codhash = $leer_respuesta['codigo_hash'];
              $comp->error = substr($leer_respuesta['errors'],2,250);
              $comp->ccacodsun = $leer_respuesta['estado_documento'];
              $comp->ccadessun = substr($leer_respuesta['sunat_description'],2,250);
              $comp->ccasunrescod = substr($leer_respuesta['sunat_responsecode'],2,250);
              $comp->ccaenlace = $leer_respuesta['url'];
              $comp->ccasunnot = $leer_respuesta['sunat_note'];
              $comp->ccaqr = $leer_respuesta['cadena_para_codigo_qr'];
          }
       
          
          $comp->update();

          if(!empty($leer_respuesta['xml_enviado'])){
              $dataxml = base64_decode($leer_respuesta['xml_enviado']);
              file_put_contents($rutaxml.$filexmlzip,$dataxml);

          }

          if(!empty($leer_respuesta['cdr_sunat'])){
              $datacdr = base64_decode($leer_respuesta['cdr_sunat']);
              file_put_contents($rutacdr.$filecdrzip,$datacdr);
          }          
          return Redirect::to('/pos/'.$tdocod.'/'.$codfact);
          
        

      }else{
      
         return Redirect::to('/pos/'.$tdocod.'/'.$codfact);
      }

     // return $leer_respuesta;

        
    }

    public function webserviceonline($data_json){

      $rucemp =Auth::user()->IdEmpresa;
      $empresa = Empresa::findOrFail($rucemp);
      // RUTA para enviar documentos
      $ruta = "https://econosystems.proveedorpse.com/api/invoiceService.svc/SendInvoice";

      //TOKEN para enviar documentos
      $token = $empresa->wscontrasena;
     
      //Invocamos el servicio de NUBEFACT
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $ruta);
      curl_setopt(
        $ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Token token="'.$token.'"',
        'Content-Type: application/json',
        )
      );
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $respuesta  = curl_exec($ch);
      curl_close($ch);

      $leer_respuesta = json_decode($respuesta, true);

      return $leer_respuesta;
     
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
       
      $motivo = $request->get('obser');
  
      $deleteped = pedidos::findOrFail($id);
      $deleteped->ped_est = 'Eliminado';
      $deleteped->MotElim = $motivo;
      $deleteped->IdUsuarioDel = Auth::user()->IdUsuario;
      $deleteped->update();

        return Redirect::to('/listallevar');
    }


    public function tomarpedido($mesa)
    {

      if(Auth::user()->turno =='Cerrado'){

          return Redirect::to('/estacionamientos')->with('danger','SE REQUIERE APERTURAR TURNO');

      }

      $rucemp = trim(Auth::user()->IdEmpresa);

      $tiposvehiculos = DB::tABLE('tipos_vehiculos')->where('IdEmpresa',$rucemp)->get();

      $tarifas = DB::tABLE('tarifas')->where('IdEmpresa',$rucemp)->get();

      $mesas = DB::tABLE('mesas')->where('mes_id',$mesa)->first();

      $unidades = DB::tABLE('unidad_medida')->get();

      $pedidos = DB::tABLE('pedidos as p')
      ->join('pedidos_detalle as pd','p.ped_id','pd.ped_id')
      ->join('mesas as m','p.mes_id','m.mes_id')
      ->join('productos as prod','prod.IdProducto','pd.IdProducto')
      ->where('p.mes_id',$mesa)
      ->where('mes_est','Ocupado')
      ->where('ped_est','Aperturado')
      ->where('p.IdEmpresa',$rucemp)
      ->get();

      $totales= DB::tABLE('pedidos as p')
      ->join('mesas as m','p.mes_id','m.mes_id')
      ->where('p.mes_id',$mesa)
      ->where('mes_est','Ocupado')
      ->where('ped_est','Aperturado')
      ->where('p.IdEmpresa',$rucemp)
      ->first();

      return view('empresas.puntosventas.restaurant',compact('pedidos','mesas','unidades','totales','tiposvehiculos','tarifas'));
    }

    public function editarpedido(Request $request, $mesa)
    {

      if(Auth::user()->turno =='Cerrado'){

          return Redirect::to('/estacionamientos')->with('danger','SE REQUIERE APERTURAR TURNO');

      }

       $rucemp = trim(Auth::user()->IdEmpresa);
      $categorias = DB::tABLE('categorias')->where('IdEmpresa',$rucemp)->orderby('cat_nom','asc')->get();
      $tiposvehiculos = DB::tABLE('tipos_vehiculos')->where('IdEmpresa',$rucemp)->get();
       $tarifas = DB::tABLE('tarifas')->where('IdEmpresa',$rucemp)->get();


      //$mesas = DB::tABLE('mesas')->where('mes_id',$mesa)->where('IdEmpresa',$rucemp)->first();
      $mesas = DB::tABLE('mesas')
      ->where('mes_id',$mesa)
      ->first();

      $unidades = DB::tABLE('unidad_medida')->get();

      $users = DB::tABLE('users')->get();

      if($mesas->mes_est == 'Libre'){
        return Redirect::to('/estacionamientos')->with('success','La Mesa seleccionada está libre, ha sido facturado el pedido o eliminado');
      }

       $pedido = DB::tABLE('pedidos as p')
      ->where('p.mes_id',$mesa)
      ->where('ped_est','Aperturado')
      ->where('p.id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->first();

      $pedidos = DB::tABLE('pedidos as p')
      ->join('pedidos_detalle as pd','p.ped_id','pd.ped_id')
      ->join('productos as prod','prod.IdProducto','pd.IdProducto')
      ->where('p.mes_id',$mesa)
      ->where('ped_est','Aperturado')
      ->where('p.id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->where('pd.estadoitem', '!=', 'Eliminado')
      ->get();



      $totales= DB::tABLE('pedidos as p')
      ->join('mesas as m','p.mes_id','m.mes_id')
      ->where('p.mes_id',$mesa)
      ->where('ped_est','Aperturado')
      ->where('p.id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->first();
      
      return view('empresas.puntosventas.modificarpedido',compact('users','categorias','pedidos','mesas','unidades','totales','pedido','tiposvehiculos','tarifas'));
    }

    public function editarpedidollevar($idpedido)
    {
      
      $rucemp = trim(Auth::user()->IdEmpresa);
      $categorias = DB::tABLE('categorias')->where('IdEmpresa',$rucemp)->orderby('cat_nom','asc')->get();

      $unidades = DB::tABLE('unidad_medida')->get();

      $users = DB::tABLE('users')->get();

      $pedido = DB::tABLE('pedidos as p')
      ->where('p.tipo','Llevar')
      ->where('ped_est','Aperturado')
      ->where('p.IdEmpresa',$rucemp)
      ->where('p.ped_id',$idpedido)
      ->first();

      $pedidos = DB::tABLE('pedidos as p')
      ->join('pedidos_detalle as pd','p.ped_id','pd.ped_id')
      ->join('productos as prod','prod.IdProducto','pd.IdProducto')
      ->where('p.ped_id',$idpedido)
      ->where('ped_est','Aperturado')
      ->where('p.IdEmpresa',$rucemp)
      ->where('pd.estadoitem', '!=', 'Eliminado')
      ->get();

      $totales= DB::tABLE('pedidos as p')
      ->join('mesas as m','p.mes_id','m.mes_id')
      ->where('p.ped_id',$idpedido)
      ->where('ped_est','Aperturado')
      ->where('p.IdEmpresa',$rucemp)
      ->first();
      
      return view('empresas.puntosventas.modificarpedidollevar',compact('users','categorias','pedidos','mesas','unidades','totales','pedido'));
    }

     public function mostrarpedidollevar($idpedido)
    {
       $rucemp = trim(Auth::user()->IdEmpresa);
      $categorias = DB::tABLE('categorias')->where('IdEmpresa',$rucemp)->orderby('cat_nom','asc')->get();

     
      $unidades = DB::tABLE('unidad_medida')->get();

      $users = DB::tABLE('users')->get();


       $pedido = DB::tABLE('pedidos as p')
      ->where('p.IdEmpresa',$rucemp)
      ->where('p.ped_id',$idpedido)
      ->first();

      $pedidos = DB::tABLE('pedidos as p')
      ->join('pedidos_detalle as pd','p.ped_id','pd.ped_id')
      ->join('productos as prod','prod.IdProducto','pd.IdProducto')
      ->where('p.ped_id',$idpedido)
      ->where('p.IdEmpresa',$rucemp)
      ->where('pd.estadoitem', '==', 'Eliminado')
      ->get();

      $totales= DB::tABLE('pedidos as p')
      ->join('mesas as m','p.mes_id','m.mes_id')
      ->where('p.ped_id',$idpedido)
      ->where('ped_est','Eliminado')
      ->where('p.IdEmpresa',$rucemp)
      ->first();
      
      return view('empresas.puntosventas.modificarpedidollevar',compact('users','categorias','pedidos','mesas','unidades','totales','pedido'));
    }

    public function BuscarPlaca(Request $request){
        $placa = $request->get('placa');

        $pedido = DB::tABLE('pedidos')->where('placa',$placa)->where('ped_est','Aperturado')->first();
        
       

        $cont = DB::tABLE('pedidos')->where('placa',$placa)->where('ped_est','Aperturado')->count();
        if($cont == '0'){
          return Redirect::to('/estacionamientos');
        }else{
         
           return Redirect::to('/cobrarmesa/'.$pedido->mes_id);

        }
        
    }


    public function facturacionmesa()
    {

        if(Auth::user()->turno =='Cerrado'){

          return Redirect::to('/estacionamientos')->with('danger','SE REQUIERE APERTURAR TURNO');

      }

       $rucemp = trim(Auth::user()->IdEmpresa);
      $mesas = DB::tABLE('mesas')->where('IdEmpresa',$rucemp)->get();

      return view('empresas.puntosventas.facturacionmesas',compact('mesas'));
 
    }

    public function cobrarmesa($mesa)
    {

        if(Auth::user()->turno =='Cerrado'){

          return Redirect::to('/estacionamientos')->with('danger','SE REQUIERE APERTURAR TURNO');

      }


      $mediospagos = DB::tABLE('medios_pagos')->get();
      $rucemp = trim(Auth::user()->IdEmpresa);
      $categorias = DB::tABLE('categorias')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->orderby('cat_nom','asc')->get();
      $tiposvehiculos = DB::tABLE('tipos_vehiculos')->where('IdEmpresa',$rucemp)->get();
      $tarifas = DB::tABLE('tarifas')->where('IdEmpresa',$rucemp)->get();
      $comprobante = DB::tABLE('tipo_documento')->get();


      $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();
      $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
      ->orderBy('tigcod','asc')->get();
      $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
      ->orderBy('umecod','asc')->get();

       $bancos = DB::tABLE('bancos')
        ->join('cuentasbancarias','cuentasbancarias.ban_id','bancos.ban_id')
        ->join('tipo_cuentas','tipo_cuentas.tip_cuen_id','cuentasbancarias.tip_cuen_id')
        ->join('moneda','moneda.moncod','cuentasbancarias.moncod')
        ->where('cuentasbancarias.IdEmpresa',Auth::user()->IdEmpresa)
        ->get();

      $fechahoractual = now()->format('Y-m-d H:i:s');
      
        
      //$mesas = DB::tABLE('mesas')->where('mes_id',$mesa)->where('IdEmpresa',$rucemp)->first();
      $mesas = DB::tABLE('mesas')->where('mes_id',$mesa)->first();
      $unidades = DB::tABLE('unidad_medida')->get();

      $pedido = DB::tABLE('pedidos as p')
      ->where('p.mes_id',$mesa)
      ->where('ped_est','Aperturado')
      ->where('p.id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->first();


      $id_ped = $pedido->ped_id;
      
      $pedidos = DB::tABLE('pedidos as p')
      ->join('pedidos_detalle as pd','p.ped_id','pd.ped_id')
      ->where('p.ped_id',$pedido->ped_id)
      ->where('ped_est','Aperturado')
      ->where('p.id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->first();

      $buscartarifa = tarifas::findOrFail($pedido->tarifa);

      $unitiempo = DB::tABLE('unidad_tiempo')->where('id_uni_tie',$buscartarifa->id_uni_tie)->first();


      $carbon1 = new \Carbon\Carbon($pedido->fecha_hora);

      $carbon2 = new \Carbon\Carbon($fechahoractual);

      if($unitiempo->id_uni_tie =='DIA'){

        $horasDiff=$carbon1->diffInHours($carbon2);

        
        $hours = $horasDiff % 24;
       
        $horas = $horasDiff - $hours;

        $dias = $horas/24;

       
        $toleranciamin = $buscartarifa->tolerancia*60;
        $hoursmin = $hours*60;

       
        if($hoursmin > $toleranciamin){
            $dias = $dias+1;

        }

        if($dias < 1){
            $dias=1;
        }

        $buscarpedido = pedidos::findOrFail($id_ped);
        $buscarpedido->total = $dias * $buscartarifa->precio;
        $buscarpedido->subtotal = $buscarpedido->total/1.1055;
        $buscarpedido->igv = $buscarpedido->total - $buscarpedido->subtotal;
        $buscarpedido->fecha = $fechahoractual;
        $buscarpedido->update();

        $buscarpedidodetalle = pedidos_detalle::findOrFail($pedidos->ped_det_id);
        $buscarpedidodetalle->ped_id=$id_ped;
        $buscarpedidodetalle->cantidad=$dias;
        $buscarpedidodetalle->unidad='ZZ';
        $buscarpedidodetalle->provunitem=$buscartarifa->precio/1.1055; 
        $buscarpedidodetalle->propunitem=$buscartarifa->precio;
        $buscarpedidodetalle->subtotalitem=$dias*$buscarpedidodetalle->provunitem;
        $buscarpedidodetalle->totalitem=$dias*$buscarpedidodetalle->propunitem;
        $buscarpedidodetalle->igvitem=$buscarpedidodetalle->totalitem - $buscarpedidodetalle->subtotalitem;
        $buscarpedidodetalle->IdEmpresa=$rucemp;
        $buscarpedidodetalle->detalle = $buscartarifa->descripcion;
        $buscarpedidodetalle->update();

      }elseif($unitiempo->id_uni_tie =='HR'){

        $minutesDiff=$carbon1->diffInMinutes($carbon2);

        $minutes = $minutesDiff % 60;
       
        $minutos = $minutesDiff - $minutes;

        $horas = $minutos/60;

      
        if($minutes > $buscartarifa->tolerancia){
            $horas = $horas+1;
        }


        if($horas < 1){
            $horas=1;
        }

        $buscarpedido = pedidos::findOrFail($id_ped);
        $buscarpedido->total = $horas * $buscartarifa->precio;
        $buscarpedido->subtotal = $buscarpedido->total/1.1055;
        $buscarpedido->igv = $buscarpedido->total - $buscarpedido->subtotal;
        $buscarpedido->update();

        $buscarpedidodetalle = pedidos_detalle::findOrFail($pedidos->ped_det_id);
        $buscarpedidodetalle->ped_id=$id_ped;
        $buscarpedidodetalle->cantidad=$horas;
        $buscarpedidodetalle->unidad='ZZ';
        $buscarpedidodetalle->provunitem=$buscartarifa->precio/1.1055; 
        $buscarpedidodetalle->propunitem=$buscartarifa->precio;
        $buscarpedidodetalle->subtotalitem=$horas*$buscarpedidodetalle->provunitem;
        $buscarpedidodetalle->totalitem=$horas*$buscarpedidodetalle->propunitem;
        $buscarpedidodetalle->igvitem=$buscarpedidodetalle->totalitem - $buscarpedidodetalle->subtotalitem;
        $buscarpedidodetalle->IdEmpresa=$rucemp;
        $buscarpedidodetalle->detalle = $buscartarifa->descripcion;
        $buscarpedidodetalle->update();
      }

    
      

     

       $detallepedido =  DB::tABLE('pedidos as p')
      ->join('pedidos_detalle as pd','p.ped_id','pd.ped_id')
      ->where('p.ped_id',$pedido->ped_id)
      ->where('ped_est','Aperturado')
      ->where('p.id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->first();

     $totales= DB::tABLE('pedidos as p')
      ->join('mesas as m','p.mes_id','m.mes_id')
      ->where('p.ped_id',$pedido->ped_id)
      ->where('ped_est','Aperturado')
      ->where('p.id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->first();

      $mesas = DB::tABLE('mesas')->where('mes_id',$mesa)->where('IdEmpresa',$rucemp)->first();
  
      $users = DB::tABLE('users')->get();


      
      return view('empresas.puntosventas.cobrarmesa',compact('users','categorias','pedidos','mesas','unidades','totales','tipodocumento','comprobante','id_ped','tiposvehiculos','tarifas','pedido','detallepedido','unitiempo','bancos','mediospagos'));
 
    }
 
    public function facturacionmesas()
    {

      $rucemp = trim(Auth::user()->IdEmpresa);
      $mesas = DB::tABLE('mesas')->where('IdEmpresa',$rucemp)->get();

      return view('empresas.mesas',compact('mesas'));
       $rucemp = trim(Auth::user()->IdEmpresa);
      $mesas = DB::tABLE('mesas')->where('IdEmpresa',$rucemp)->get();
       $comprobante = DB::tABLE('tipo_documento')->get();
        $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();
         $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

      return view('empresas.puntosventas.facturacionmesas',compact('mesas','comprobante','tipodocumento','igv','unidades'));
    }
  
  public function listar_pedido_llevar($id_ped=""){

      $rucemp = trim(Auth::user()->IdEmpresa);
    $pedidos = DB::tABLE('pedidos')->where('tipo','Llevar')->where('IdEmpresa',$rucemp)->orderby('ped_id','desc')->paginate(800);
    return view('empresas.puntosventas.listallevar',compact('pedidos','id_ped'));
  }

  public function pedido_llevar(){

      $rucemp = trim(Auth::user()->IdEmpresa);
      $categorias = DB::tABLE('categorias')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->orderby('cat_nom','asc')->get();
      $aplicativos = DB::tABLE('aplicativos')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->orderby('apli_nom','asc')->get();

      $unidades = DB::tABLE('unidad_medida')->get();
    
      return view('empresas.puntosventas.pedidollevar',compact('categorias','unidades','aplicativos'));
  }

  public function registrar_pedido_llevar(Request $request){

        $rucemp = trim(Auth::user()->IdEmpresa);

        $cliente = $request->get('cliente');
        $direccion = $request->get('direccion');
        $fecha = $request->get('fecha');
        
        $producto = $request->get('proid');
        $cantidad  = $request->get('cant');
        $unidad = $request->get('unid');
        $valuni = $request->get('provun');
        $preuni = $request->get('propun');
        $subtotal = $request->get('subtotal');
        $igv = $request->get('igv');
        $total = $request->get('total');
        $totalitem = $request->get('itemtotal');
        $detalle = $request->get('detalle');

        $pedido = new pedidos;
        $pedido->fecha = $fecha;
        $pedido->direccion = $direccion;
        $pedido->cliente = $cliente;
        $pedido->apli_id= $request->get('aplicativo');
        $pedido->total=$total;
        $pedido->subtotal=$subtotal;
        $pedido->igv=$igv;
        $pedido->IdEmpresa=$rucemp;
        $pedido->ped_est='Aperturado';

        $pedido->tipo = 'Llevar';
        $pedido->IdUsuario = Auth::user()->IdUsuario;
        $pedido->save();

        $id_ped = $pedido->ped_id;
       

        foreach ($unidad as $index => $unid) {
          // code...
          $subitem =$valuni[$index]*$cantidad[$index];
          $igvitem =$totalitem[$index]-$subitem;

          $ped_det = new pedidos_detalle;
          $ped_det->ped_id=$pedido->ped_id;
          $ped_det->IdProducto=$producto[$index];
          $ped_det->cantidad=$cantidad[$index];
          $ped_det->unidad=$unidad[$index];
          $ped_det->provunitem=$valuni[$index];
          $ped_det->propunitem=$preuni[$index];
          $ped_det->igvitem=$igvitem;
          $ped_det->subtotalitem=$subitem;
          $ped_det->IdEmpresa=$rucemp;
          $ped_det->totalitem=$totalitem[$index];
          $ped_det->detalle = $detalle[$index];
          $ped_det->impreso = 'imprimir';
          $ped_det->save();

        }

        $pedidos = DB::tABLE('pedidos')->where('tipo','Llevar')->where('IdEmpresa',$rucemp)->orderby('ped_id','desc')->paginate(100);

       // return view('empresas.puntosventas.listallevar',compact('pedidos','id_ped'));
        return Redirect::to('/listallevar/'.$id_ped);

  }

  public function cobrar_llevar($pedido_id){
      $rucemp = trim(Auth::user()->IdEmpresa);
      $categorias = DB::tABLE('categorias')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->orderby('cat_nom','asc')->get();

      $comprobante = DB::tABLE('tipo_documento')->get();
        $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();
         $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

      //$mesas = DB::tABLE('mesas')->where('mes_id',$mesa)->where('IdEmpresa',$rucemp)->first();
      
      $unidades = DB::tABLE('unidad_medida')->get();

      $pedido = DB::tABLE('pedidos as p')
      ->where('p.ped_id',$pedido_id)
      ->where('ped_est','Aperturado')
      ->where('p.IdEmpresa',$rucemp)
      ->first();

      $pedidos = DB::tABLE('pedidos as p')
      ->join('pedidos_detalle as pd','p.ped_id','pd.ped_id')
      ->join('productos as prod','prod.IdProducto','pd.IdProducto')
      ->where('p.ped_id',$pedido->ped_id)
      ->where('ped_est','Aperturado')
      ->where('p.IdEmpresa',$rucemp)
      ->get();

      $totales= DB::tABLE('pedidos as p')
      ->where('p.ped_id',$pedido->ped_id)
      ->where('ped_est','Aperturado')
      ->where('p.IdEmpresa',$rucemp)
      ->first();


      return view('empresas.puntosventas.cobrarllevar',compact('pedido','categorias','pedidos','unidades','totales','tipodocumento','comprobante','pedido_id'));
 
  }

  public function reg_cobro_llevar(Request $request)
    {


        $rucemp = trim(Auth::user()->IdEmpresa);

      //Datos del cliente
        $tdicod = $request->get('tdicod');
        $direccion = $request->get('delivery');
        $tipo_venta = $request->get('tipoventa');
        $cliruc = $request->get('clinum');
        $fecha = $request->get('fecEmi');
        $clinom = $request->get('clinom');
        $pedido = $request->get('pedido');
        $clidir = $request->get('clidir');
        $clicor = $request->get('clicor');
        $grav = $request->get('subtotal');
        $igv = $request->get('igv');
        $total = $request->get('total');
        $mondoc = 'PEN';
        $tdocod = $request->get('tdocod');
        $fecemi = $request->get('fecEmi');
        $fecven = $request->get('fecVen');
        $tippago = $request->get('txtTipPag');
        $efectivo = $request->get('efectivo1');
        $visa = $request->get('visa');
        $mastercard = $request->get('mastercard');
       
        $topcod = '0101';

    if($tdocod == '01'){
          $senudoc = DB::tABLE('empresa_negocios')->select('FseEmpresa','FnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $numcomp =  $senudoc->FnuEmpresa+1;
          $sercomp =  $senudoc->FseEmpresa;
        }elseif ($tdocod =='03') {
          $senudoc = DB::tABLE('empresa_negocios')->select('BseEmpresa','BnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $numcomp =  $senudoc->BnuEmpresa+1;
          $sercomp =  $senudoc->BseEmpresa;
        }elseif ($tdocod =='13') {
          $senudoc = DB::tABLE('empresa_negocios')->select('SerNota','NumNota')->where('IdEmpresa','=',$rucemp)->first();
          $numcomp =  $senudoc->NumNota+1;
          $sercomp =  $senudoc->SerNota;
        }

 

        $moneda = DB::tABLE('moneda')->where('moncod','=',$mondoc)->first();


      //Registrar el tipo de cambio enviado a través del formulario si no existe.
        if($mondoc !='PEN'){
          $camdoc = $request->get('camdoc');
          $tipcam = tipocambio::FirstOrCreate(['FecTipCambio'=>$fecemi],['CamCompra'=>$camdoc]);
        }else{
          $camdoc=0;
        }

      //Registrar el cliente enviado a través del formulario si no existe
        $cliente = Cliente::FirstOrCreate(['clinum'=>$cliruc,'rucemp'=>$rucemp],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$clicor,'rucemp'=>$rucemp,'tdicod'=>$tdicod]);

        $buscarpedido = pedidos::findOrFail($pedido);

      //Crear un objeto de la cabecera del comprobante para registrar los datos de la cabecera
        $cabecera = new cpe_cabecera;
        $cabecera->tdocod = $request->get('tdocod');
        $cabecera->topcod = $topcod;
        $cabecera->ccafem = $request->get('fecEmi');
        $cabecera->ccafve = $request->get('fecVen');
       // $cabecera->ccaobs = $request->get('obser');
        //$cabecera->ccacde = $request->get();
        $cabecera->tdicod = $tdicod;
          $cabecera->direccion = $direccion;
        $cabecera->ccandi = $cliruc;
        $cabecera->ccanom = $clinom;
        $cabecera->moncod = $mondoc;
        $cabecera->tipo_pago = $tippago;
        $cabecera->tipcambio = $camdoc;
        $cabecera->ccatvg =  $grav;
        $cabecera->ccaigv = $igv;
        $cabecera->ccaitv = $total;
        $cabecera->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $cabecera->visa =  $visa;
        $cabecera->tipo_venta =  $tipo_venta;
        $cabecera->efectivo = $efectivo;
        $cabecera->mastercard = $mastercard;
        $cabecera->clicod = $cliente->clicod;
        if($buscarpedido->apli_id =='4' || $buscarpedido->apli_id =='5'){
           $cabecera->estadocobrar = 'Cobrado';

        }else{
            $cabecera->estadocobrar = 'Por Cobrar';
        }
        $cabecera->apli_id = $buscarpedido->apli_id;
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->IdEmpresa =  $rucemp;

    

        $empresa = Empresa::findOrFail($rucemp);
        $empresanegocio = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);
         $formato = $empresa->formato;
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
          $cabecera->serdoc= $sercomp;
          $cabecera->numdoc = $numdoc;
          //$cabecera->save();
        }elseif($tdocod=='03'){
          if( $empresanegocio->FnuEmpresa == $numcomp){
              $modnumcomp = $numcomp+1;
          }else{
              $modnumcomp = $numcomp;
          }

          $empresanegocio->BseEmpresa = $sercomp;
          $empresanegocio->BnuEmpresa = $modnumcomp;
          //$empresanegocio->update();

          $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
          $cabecera->serdoc= $sercomp;
          $cabecera->numdoc = $numdoc;
          //$cabecera->save();
        }elseif($tdocod=='13'){
          if( $empresanegocio->notanumero == $numcomp){
              $modnumcomp = $numcomp+1;
          }else{
              $modnumcomp = $numcomp;
          }

          $empresanegocio->SerNota = $sercomp;
          $empresanegocio->NumNota = $modnumcomp;
          //$empresa->update();

          $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
          $cabecera->serdoc= $sercomp;
          $cabecera->numdoc = $numdoc;
          //$cabecera->save();
        }

           $empresanegocio->update();

          $empresa->update();
          $cabecera->save();
          $codfact = $cabecera->IdCpe_cabecera; 
          
          $pedido_est= pedidos::findOrFail($pedido);
          $pedido_est->ped_est = 'Entregado';
          $pedido_est->IdUsuarioCob = Auth::user()->IdUsuario;
          $pedido_est->update();

          $codfact = $cabecera->IdCpe_cabecera;


        $unidades = $request->get('unid');
        $proid = $request->get('proid');
        $detpro = $request->get('pronom');
        $vunit = $request->get('provun');
        $puni = $request->get('propun');
        $vtot = $request->get('itemtotal');
        $cantidades = $request->get('cant');


        //Generar el detalle del comprobante
        foreach($unidades as $index => $ume ) {


            $detalle = new cpe_detalle;
           // $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
            $detalle->umecod = $ume;
            $detalle->cdecan = $cantidades[$index];

            $codpro = productos::findOrFail($proid[$index]);
            $codproducto = $codpro->procod;
            $detalle->procod = $codproducto;
            $detalle->cdepsu = "";
            $detalle->cdedes = $detpro[$index];
            $detalle->cdevun = $vunit[$index];
            $detalle->cdepuni = $puni[$index];

            $detalle->tigcod = '10';
            $vsub = $vunit[$index]*$cantidades[$index];
            $detalle->cdevve = $vtot[$index];
            $detalle->cdepve = $vsub;
            $vigv = $vtot[$index]-$vsub;
            $detalle->cdeigv = $vigv;


            //Guardar en una variable los items en el archivo .det
            $codumecin = unidad_medida::findOrFail($ume);

            $detallejson[] = array("COD_ITEM"       => $detalle->procod,
                        "COD_UNID_ITEM"             => $codumecin->umecin,
                        "CANT_UNID_ITEM"            => $detalle->cdecan,
                        "VAL_UNIT_ITEM"             => $detalle->cdevun,      
                        "PRC_VTA_UNIT_ITEM"         => $detalle->cdepuni,
                        "VAL_VTA_ITEM"              => $detalle->cdepve,
                        "MNT_BRUTO"                 => $detalle->cdepve,
                        "MNT_PV_ITEM"               => $detalle->cdevve,
                        "COD_TIP_PRC_VTA"           => "01",
                        "COD_TIP_AFECT_IGV_ITEM"    => $detalle->tigcod,
                        "COD_TRIB_IGV_ITEM"         => "1000",
                        "POR_IGV_ITEM"              => "18",
                        "MNT_IGV_ITEM"              => number_format($detalle->cdeigv,'2','.',''),     
                        "TXT_DESC_ITEM"             => $detalle->cdedes,                 
                        "DET_VAL_ADIC01"            => "",
                        "DET_VAL_ADIC02"            => "",
                        "DET_VAL_ADIC03"            => "",
                        "DET_VAL_ADIC04"            => "",
                        "DET_VAL_ADIC05"            => "",
                        "DET_VAL_ADIC06"            => "",
                        "DET_VAL_ADIC07"            => "",
                        "DET_VAL_ADIC08"            => "",
                        "DET_VAL_ADIC09"            => "",
                        "DET_VAL_ADIC10"            => "");

            $detalle = new cpe_detalle;
            $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
            $detalle->umecod = $ume;
            $detalle->cdecan = $cantidades[$index];

            $codpro = productos::findOrFail($proid[$index]);
            $codproducto = $codpro->procod;
            $detalle->procod = $codproducto;
            $detalle->cdepsu = "";
            $detalle->cdedes = $detpro[$index];
            $detalle->cdevun = $vunit[$index];
            $detalle->cdepuni = $puni[$index];

            $detalle->tigcod = '10';
            $vsub = $vunit[$index] * $cantidades[$index];
            $detalle->cdevve = $vtot[$index];
            $detalle->cdepve = $vsub;
            $vigv = $vtot[$index] - $vsub;
            $detalle->cdeigv = $vigv;
            $detalle->fecha_venta = $fecemi;
            $detalle->save();

              $IdProducto = DB::tABLE('productos')->WHERE('procod',$codproducto)->where('id_empresa_negocio',trim(Auth::user()->id_empresa_negocio))->first();

            
            if($IdProducto->promocion =='0'){

                $movimiento = new movimientos;
                $movimiento->mov_fec = $fecha; 
                $movimiento->mov_tip = 'E';
                $movimiento->mov_mot = 'Venta';
                $movimiento->cantidad = $cantidades[$index];
                $movimiento->unidad = $ume;
                $movimiento->comprobante = $sercomp.'-'.$numdoc;
                $movimiento->IdEmpresa = $rucemp;
                $movimiento->IdProducto = $IdProducto->IdProducto;
                $movimiento->observacion = "Venta desde Punto de Venta";
                $movimiento->IdUsuario = Auth::user()->IdUsuario;
                $movimiento->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
                $movimiento->save();

             
                $stock_prod =productos::findOrFail($IdProducto->IdProducto);
                $stock_prod->stock = $stock_prod->stock-$cantidades[$index];;
                $stock_prod->update();

            }elseif($IdProducto->promocion =='2'){

                  if(!empty($IdProducto->producto1)){
                    $movimiento = new movimientosinsumos;
                    $movimiento->mov_fec = $fecha; 
                    $movimiento->mov_tip = 'E';
                    $movimiento->mov_mot = 'Venta';
                    $movimiento->cantidad = $cantidades[$index];
                    $movimiento->medida= $IdProducto->cantidad1;
                    $movimiento->totalmedida = $IdProducto->cantidad1*$cantidades[$index];
                    $movimiento->unidad = $ume;
                    $movimiento->comprobante = $sercomp.'-'.$numdoc;
                    $movimiento->IdEmpresa = $rucemp;
                    $movimiento->IdInsumo = $IdProducto->producto1;
                    $movimiento->observacion = "Venta desde Punto de Venta";
                    $movimiento->IdUsuario = Auth::user()->IdUsuario;
                    $movimiento->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
                    $movimiento->save();

                    $stock_prod =insumos::findOrFail($IdProducto->producto1);

                    $stock_fraccion =  $stock_prod->totalmedida-($cantidades[$index]*$IdProducto->cantidad1);

                    $fraccion = $stock_fraccion % $stock_prod->medida;

                    $entero = ($stock_fraccion - $fraccion) / $stock_prod->medida;

                    $sumaunidades = ($entero*$stock_prod->medida)+$fraccion;

                    
                    $stock_prod->stock = $entero;
                    $stock_prod->fraccion = $fraccion;
                    $stock_prod->totalmedida = $sumaunidades;
                    $stock_prod->update();

                  }

                  if(!empty($IdProducto->producto2)){
                    $movimiento = new movimientosinsumos;
                    $movimiento->mov_fec = $fecha; 
                    $movimiento->mov_tip = 'E';
                    $movimiento->mov_mot = 'Venta';
                    $movimiento->cantidad = $cantidades[$index];
                    $movimiento->medida= $IdProducto->cantidad1;
                    $movimiento->totalmedida = $IdProducto->cantidad2*$cantidades[$index];
                    $movimiento->unidad = $ume;
                    $movimiento->comprobante = $sercomp.'-'.$numdoc;
                    $movimiento->IdEmpresa = $rucemp;
                    $movimiento->IdInsumo = $IdProducto->producto2;
                    $movimiento->observacion = "Venta desde Punto de Venta";
                    $movimiento->IdUsuario = Auth::user()->IdUsuario;
                    $movimiento->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
                    $movimiento->save();




                    $stock_prod =insumos::findOrFail($IdProducto->producto2);

                    $stock_fraccion =  $stock_prod->totalmedida-($cantidades[$index]*$IdProducto->cantidad2);

                    $fraccion = $stock_fraccion % $stock_prod->medida;

                    $entero = ($stock_fraccion - $fraccion) / $stock_prod->medida;

                    $sumaunidades = ($entero*$stock_prod->medida)+$fraccion;

                    
                    $stock_prod->stock = $entero;
                    $stock_prod->fraccion = $fraccion;
                    $stock_prod->totalmedida = $sumaunidades;
                    $stock_prod->update();
                  }

                  if(!empty($IdProducto->producto3)){
                    $movimiento = new movimientosinsumos;
                    $movimiento->mov_fec = $fecha; 
                    $movimiento->mov_tip = 'E';
                    $movimiento->mov_mot = 'Venta';
                    $movimiento->cantidad = $cantidades[$index];
                    $movimiento->medida= $IdProducto->cantidad3;
                    $movimiento->totalmedida = $IdProducto->cantidad3*$cantidades[$index];
                    $movimiento->unidad = $ume;
                    $movimiento->comprobante = $sercomp.'-'.$numdoc;
                    $movimiento->IdEmpresa = $rucemp;
                    $movimiento->IdInsumo = $IdProducto->producto3;
                    $movimiento->observacion = "Venta desde Punto de Venta";
                    $movimiento->IdUsuario = Auth::user()->IdUsuario;
                    $movimiento->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
                    $movimiento->save();




                    $stock_prod =insumos::findOrFail($IdProducto->producto3);

                    $stock_fraccion =  $stock_prod->totalmedida-($cantidades[$index]*$IdProducto->cantidad3);

                    $fraccion = $stock_fraccion % $stock_prod->medida;

                    $entero = ($stock_fraccion - $fraccion) / $stock_prod->medida;

                    $sumaunidades = ($entero*$stock_prod->medida)+$fraccion;

                    
                    $stock_prod->stock = $entero;
                    $stock_prod->fraccion = $fraccion;
                    $stock_prod->totalmedida = $sumaunidades;
                    $stock_prod->update();
                  }

                  if(!empty($IdProducto->producto4)){
                      $movimiento = new movimientosinsumos;
                      $movimiento->mov_fec = $fecha; 
                      $movimiento->mov_tip = 'E';
                      $movimiento->mov_mot = 'Venta';
                      $movimiento->cantidad = $cantidades[$index];
                      $movimiento->medida= $IdProducto->cantidad4;
                      $movimiento->totalmedida = $IdProducto->cantidad4*$cantidades[$index];
                      $movimiento->unidad = $ume;
                      $movimiento->comprobante = $sercomp.'-'.$numdoc;
                      $movimiento->IdEmpresa = $rucemp;
                      $movimiento->IdInsumo = $IdProducto->producto4;
                      $movimiento->observacion = "Venta desde Punto de Venta";
                      $movimiento->IdUsuario = Auth::user()->IdUsuario;
                      $movimiento->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
                      $movimiento->save();

                      $stock_prod =insumos::findOrFail($IdProducto->producto4);

                      $stock_fraccion =  $stock_prod->totalmedida-($cantidades[$index]*$IdProducto->cantidad4);

                      $fraccion = $stock_fraccion % $stock_prod->medida;

                      $entero = ($stock_fraccion - $fraccion) / $stock_prod->medida;

                      $sumaunidades = ($entero*$stock_prod->medida)+$fraccion;

                      
                      $stock_prod->stock = $entero;
                      $stock_prod->fraccion = $fraccion;
                      $stock_prod->totalmedida = $sumaunidades;
                      $stock_prod->update();
                  }


            }else{

                  if(!empty($IdProducto->producto1)){
                    $movimiento = new movimientos;
                    $movimiento->mov_fec = $fecha; 
                    $movimiento->mov_tip = 'E';
                    $movimiento->mov_mot = 'Venta';
                    $movimiento->cantidad = $cantidades[$index]*$IdProducto->cantidad1;
                    $movimiento->unidad = $ume;
                    $movimiento->comprobante = $sercomp.'-'.$numdoc;
                    $movimiento->IdEmpresa = $rucemp;
                    $movimiento->IdProducto = $IdProducto->producto1;
                    $movimiento->observacion = "Venta desde Punto de Venta - Promocion";
                    $movimiento->IdUsuario = Auth::user()->IdUsuario;
                    $movimiento->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
                    $movimiento->save();

                    $stock_prod =productos::findOrFail($IdProducto->producto1);
                    $stock_prod->stock = $stock_prod->stock-($cantidades[$index]*$IdProducto->cantidad1);
                    $stock_prod->update();
                  }

                  if(!empty($IdProducto->producto2)){
                    $movimiento = new movimientos;
                    $movimiento->mov_fec = $fecha; 
                    $movimiento->mov_tip = 'E';
                    $movimiento->mov_mot = 'Venta';
                    $movimiento->cantidad = $cantidades[$index]*$IdProducto->cantidad2;
                    $movimiento->unidad = $ume;
                    $movimiento->comprobante = $sercomp.'-'.$numdoc;
                    $movimiento->IdEmpresa = $rucemp;
                    $movimiento->IdProducto = $IdProducto->producto2;
                    $movimiento->observacion = "Venta desde Punto de Venta - Promocion";
                    $movimiento->IdUsuario = Auth::user()->IdUsuario;
                    $movimiento->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
                    $movimiento->save();

                 
                    $stock_prod =productos::findOrFail($IdProducto->producto2);
                    $stock_prod->stock = $stock_prod->stock-($cantidades[$index]*$IdProducto->cantidad2);
                    $stock_prod->update();
                  }

                  if(!empty($IdProducto->producto3)){
                    $movimiento = new movimientos;
                    $movimiento->mov_fec = $fecha; 
                    $movimiento->mov_tip = 'E';
                    $movimiento->mov_mot = 'Venta';
                    $movimiento->cantidad = $cantidades[$index]*$IdProducto->cantidad3;
                    $movimiento->unidad = $ume;
                    $movimiento->comprobante = $sercomp.'-'.$numdoc;
                    $movimiento->IdEmpresa = $rucemp;
                    $movimiento->IdProducto = $IdProducto->producto3;
                    $movimiento->observacion = "Venta desde Punto de Venta - Promocion";
                    $movimiento->IdUsuario = Auth::user()->IdUsuario;
                    $movimiento->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
                    $movimiento->save();

                 
                    $stock_prod =productos::findOrFail($IdProducto->producto3);
                    $stock_prod->stock = $stock_prod->stock-($cantidades[$index]*$IdProducto->cantidad3);
                    $stock_prod->update();
                  }

                  if(!empty($IdProducto->producto4)){
                    $movimiento = new movimientos;
                    $movimiento->mov_fec = $fecha; 
                    $movimiento->mov_tip = 'E';
                    $movimiento->mov_mot = 'Venta';
                    $movimiento->cantidad = $cantidades[$index]*$IdProducto->cantidad4;
                    $movimiento->unidad = $ume;
                    $movimiento->comprobante = $sercomp.'-'.$numdoc;
                    $movimiento->IdEmpresa = $rucemp;
                    $movimiento->IdProducto = $IdProducto->producto4;
                    $movimiento->observacion = "Venta desde Punto de Venta - Promocion";
                    $movimiento->IdUsuario = Auth::user()->IdUsuario;
                    $movimiento->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
                    $movimiento->save();

                 
                    $stock_prod =productos::findOrFail($IdProducto->producto4);
                    $stock_prod->stock = $stock_prod->stock-($cantidades[$index]*$IdProducto->cantidad4);
                    $stock_prod->update();
                  }
            }

        }
 $totalletras= MontoLetras::convertir(number_format($cabecera->ccaitv,'2','.',''),$moneda->monnom,'Centimos');

        //$cabfile es el nombre con el cual se guarda el archivo que contiene los datos del comproabnte
        $cabfile =  $cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.json';
        $filepdf=  $cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.pdf';
        $filecdr =  'R-'.$cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.xml';
        $filexml =  $cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.xml';
        $filecdrzip =  'R-'.$cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.zip';
        $filexmlzip =  $cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.zip';
     

        $qrfile =  'QR-'.$cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.png'; 
        
        //Consultar los datos de la empresa emisora
        $datemp = DB::table("empresa")->where("IdEmpresa","=",$rucemp)->first();

        if($tdocod=='01'){
           $nomcomp = "factura";
        }elseif($tdocod=='03'){
           $nomcomp = "boleta";
        }elseif($tdocod =='13') {
          $nomcomp = "nota venta";
        }

        //inicio json
         $data = array(
                  "TOKEN"                             =>$empresa->wscontrasena,
                  "COD_TIP_NIF_EMIS"                  => "6",
                  "NUM_NIF_EMIS"                      => $rucemp,
                  "NOM_RZN_SOC_EMIS"                  => $empresa->NomEmpresa,
                  "NOM_COMER_EMIS"                    => "",
                  "COD_UBI_EMIS"                      => "",
                  "TXT_DMCL_FISC_EMIS"                => $empresa->DirEmpresa,
                  "COD_TIP_NIF_RECP"                  => $tdicod,
                  "NUM_NIF_RECP"                      => $cliruc,
                  "NOM_RZN_SOC_RECP"                  => $clinom,
                  "TXT_DMCL_FISC_RECEP"               => $clidir,
                  "FEC_EMIS"                          => $fecemi,
                  "FEC_VENCIMIENTO"                   => $fecven,
                  "COD_TIP_CPE"                       => $tdocod,
                  "NUM_SERIE_CPE"                     => $sercomp,
                  "NUM_CORRE_CPE"                     => $numcomp,
                  "COD_MND"                           => $mondoc,
                  "MailEnvio"                         => $clicor,
                  "COD_PRCD_CARGA"                    => "001",
                  "MNT_TOT_GRAVADO"                   => $grav,
                  "MNT_TOT_INAFECTO"                  => "",
                  "MNT_TOT_EXONERADO"                 => "",
                  "MNT_TOT_GRATUITO"                  => "",
                  "MNT_TOT_TRIB_IGV"                  => $igv,
                  "MNT_TOT"                           => $total, 
                  "COD_PTO_VENTA"                     => "",
                  "ENVIAR_A_SUNAT"                    => "true",
                  "RETORNA_XML_ENVIO"                 => "true",
                  "RETORNA_XML_CDR"                   => "true",
                  "RETORNA_PDF"                       => "true",
                  "COD_FORM_IMPR"                     => "004",
                  "TXT_VERS_UBL"                      =>"2.1",
                  "TXT_VERS_ESTRUCT_UBL"              =>"2.0",
                  "COD_ANEXO_EMIS"                    => $empresanegocio->codigofiscal,
                  "COD_TIP_OPE_SUNAT"                 => $topcod,
                  "items"                             =>$detallejson,
                 
              );
      //fin json
      //fin json


        //Generar el archivo JSON del comprobante que se enviará al OSE
        $data_json = json_encode($data,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $raiz =  public_path().'/json/';
        $rutacdr = public_path().'/cdr/';
        $rutaxml = public_path().'/xml/';
        $rutapdf = public_path().'/pdf/';

        $data_json = json_encode($data,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $archivo = fopen($raiz.$cabfile, "a");
        fputs($archivo,$data_json);
        fclose($archivo);
        
         if($tdocod !='13'){
         $leer_respuesta = self::webserviceonline($data_json);


         //$retornarpdf = self::webservicepdf($tdocod,$sercomp,$numcomp,$codfact);
      
          $comp = cpe_cabecera::findOrFail($codfact);

          if(!empty($leer_respuesta['codigo_hash'])){
            
          $comp->codhash = $leer_respuesta['codigo_hash'];
          $comp->error = substr($leer_respuesta['errors'],2,250);
          $comp->ccacodsun = $leer_respuesta['estado_documento'];
          $comp->ccadessun = substr($leer_respuesta['sunat_description'],2,250);
          $comp->ccasunrescod = substr($leer_respuesta['sunat_responsecode'],2,250);
          $comp->ccaenlace = $leer_respuesta['url'];
          $comp->ccasunnot = $leer_respuesta['sunat_note'];
          $comp->ccaqr = $leer_respuesta['cadena_para_codigo_qr'];
        }
          /*if(!empty($comp->ccaqr)){
            self::generar_qr($qrfile,$comp->ccaqr);
          }*/
          
          $comp->update();

      if(!empty($leer_respuesta['xml_enviado'])){
          $dataxml = base64_decode($leer_respuesta['xml_enviado']);
          file_put_contents($rutaxml.$filexmlzip,$dataxml);

      }

      if(!empty($leer_respuesta['cdr_sunat'])){
          $datacdr = base64_decode($leer_respuesta['cdr_sunat']);
          file_put_contents($rutacdr.$filecdrzip,$datacdr);
      }          
          return Redirect::to('/pos/'.$tdocod.'/'.$codfact);
          
        

      }else{
      
         return Redirect::to('/pos/'.$tdocod.'/'.$codfact);
      }

      
    }

    public function listar_pedidos(){

       $rucemp = trim(Auth::user()->IdEmpresa);
      $pedidos = DB::tABLE('pedidos as ped')
      ->leftjoin('mesas as mes','ped.mes_id','mes.mes_id')
      ->where('ped.IdEmpresa',$rucemp)->orderby('ped_id','desc')->get();

      return view('empresas.puntosventas.listarpedidos',compact('pedidos'));
  }


 

 public function eliminarpedido(Request $request){


    $idmesa=$request->get('mesa');
    $observacion=$request->get('obser');
    $codigo=$request->get('codigo');
    $usuario = $request->get('usuario');

 
    $user = User::findOrFail($usuario);

     if($idmesa=='0'){

     }else{

      $pedido = DB::tABLE('pedidos as p')
      ->where('p.mes_id',$idmesa)
      ->where('p.ped_est','Aperturado')
      ->where('p.id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->first();


     }
     


     $usuariobuscar = DB::tABLE('users')->where('IdUsuario',$usuario)->count();



     if (Hash::check($codigo,$user->password_admin) && $usuariobuscar > 0) {


        // INICIO CALCULAR TIEMPO
            $fechahoractual = now()->format('Y-m-d H:i:s');
      
            $buspedido = DB::tABLE('pedidos as p')
            ->where('p.ped_id',$pedido->ped_id)
            ->where('p.id_empresa_negocio',Auth::user()->id_empresa_negocio)
            ->first();
            
            $buspedidos = DB::tABLE('pedidos as p')
            ->join('pedidos_detalle as pd','p.ped_id','pd.ped_id')
            ->where('p.ped_id',$pedido->ped_id)
            ->where('p.id_empresa_negocio',Auth::user()->id_empresa_negocio)
            ->first();

            $bustarifa = tarifas::findOrFail($buspedido->tarifa);

            $unitiempo = DB::tABLE('unidad_tiempo')->where('id_uni_tie',$bustarifa->id_uni_tie)->first();

            $carbon1 = new \Carbon\Carbon($buspedido->fecha_hora);

            $carbon2 = new \Carbon\Carbon($fechahoractual);

            if($unitiempo->id_uni_tie =='DIA'){

              $horasDiff=$carbon1->diffInHours($carbon2);

              
              $hours = $horasDiff % 24;
             
              $horas = $horasDiff - $hours;

              $dias = $horas/24;

             
              $toleranciamin = $bustarifa->tolerancia*60;
              $hoursmin = $hours*60;

             
              if($hoursmin > $toleranciamin){
                  $dias = $dias+1;

              }

              if($dias < 1){
                  $dias=1;
              }

              $buscarpedido = pedidos::findOrFail($pedido->ped_id);
              $buscarpedido->total = $dias * $bustarifa->precio;
              $buscarpedido->subtotal = $buscarpedido->total/1.1055;
              $buscarpedido->igv = $buscarpedido->total - $buscarpedido->subtotal;
              $buscarpedido->fecha = $fechahoractual;
              $buscarpedido->update();

              $buscarpedidodetalle = pedidos_detalle::findOrFail($buspedidos->ped_det_id);
              $buscarpedidodetalle->ped_id=$pedido->ped_id;
              $buscarpedidodetalle->cantidad=$dias;
              $buscarpedidodetalle->unidad='ZZ';
              $buscarpedidodetalle->provunitem=$bustarifa->precio/1.1055; 
              $buscarpedidodetalle->propunitem=$bustarifa->precio;
              $buscarpedidodetalle->subtotalitem=$dias*$buscarpedidodetalle->provunitem;
              $buscarpedidodetalle->totalitem=$dias*$buscarpedidodetalle->propunitem;
              $buscarpedidodetalle->igvitem=$buscarpedidodetalle->totalitem - $buscarpedidodetalle->subtotalitem;
              $buscarpedidodetalle->detalle = $bustarifa->descripcion;
              $buscarpedidodetalle->update();

              $tiempo = $dias;

            }elseif($unitiempo->id_uni_tie =='HR'){

              $minutesDiff=$carbon1->diffInMinutes($carbon2);

              $minutes = $minutesDiff % 60;
             
              $minutos = $minutesDiff - $minutes;

              $horas = $minutos/60;

            
              if($minutes > $bustarifa->tolerancia){
                  $horas = $horas+1;
              }


              if($horas < 1){
                  $horas=1;
              }

              $tiempo = $horas;

              $buscarpedido = pedidos::findOrFail($pedido->ped_id);
              $buscarpedido->total = $horas * $bustarifa->precio;
              $buscarpedido->subtotal = $buscarpedido->total/1.1055;
              $buscarpedido->igv = $buscarpedido->total - $buscarpedido->subtotal;
              $buscarpedido->update();

              $buscarpedidodetalle = pedidos_detalle::findOrFail($pedidos->ped_det_id);
              $buscarpedidodetalle->ped_id=$pedido->ped_id;
              $buscarpedidodetalle->cantidad=$horas;
              $buscarpedidodetalle->unidad='ZZ';
              $buscarpedidodetalle->provunitem=$bustarifa->precio/1.1055; 
              $buscarpedidodetalle->propunitem=$bustarifa->precio;
              $buscarpedidodetalle->subtotalitem=$horas*$buscarpedidodetalle->provunitem;
              $buscarpedidodetalle->totalitem=$horas*$buscarpedidodetalle->propunitem;
              $buscarpedidodetalle->igvitem=$buscarpedidodetalle->totalitem - $buscarpedidodetalle->subtotalitem;
              $buscarpedidodetalle->detalle = $bustarifa->descripcion;
              $buscarpedidodetalle->update();
            }


        //FIN CALCULAR TIEMPO

            $deleteped = pedidos::findOrFail($pedido->ped_id);
            $deleteped->IdUsuarioDel = $usuario;
            $deleteped->MotElim = $observacion;
            $deleteped->ped_est = 'Eliminado';
            $deleteped->update();

            DB::tABLE('usuario_eliminar')->insert(
              array('ped_id' => $pedido->ped_id, 'id_usu_elim' =>Auth::user()->IdUsuario,'id_usu_aut'=>$usuario,'motivo'=>$observacion,'id_empresa_negocio'=>Auth::user()->id_empresa_negocio)
            );

            $pedidodetalle = DB::tABLE('pedidos_detalle')
            ->where('ped_id',$pedido->ped_id)
            ->update(['estadoitem' => "Eliminado",'MotElim'=>$observacion,'IdUsuarioDel'=>$usuario]);             

            $user = User::findOrFail(Auth::user()->IdUsuario);

            if($idmesa!='0'){
              $mesa = mesas::findOrFail($idmesa);
              $mesa->mes_est ='Libre';
              $mesa->update();

              $mesa= DB::tABLE('mesas')->where('mes_id',$idmesa)->first();
            }
            
            $sucursal = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);

            $cab_pedido = DB::tABLE('pedidos')
            ->where('ped_id',$pedido->ped_id)
            ->first();

        
        if($request->ajax()) {
          return response()->json(['mensaje' => 'INGRESO ELIMINADO']);
        }
        
    }else{
        
        
          return response()->json(['mensaje' => 'USUARIO NO TIENE AUTORIZACION']);
        
    }

  }

  public function PedidoEditar(Request $request){


    $idmesa=$request->get('mesamod');
    $observacion=$request->get('obsermod');
    $codigo=$request->get('codigomod');
    $usuario = $request->get('usuariomod');

 
    $user = User::findOrFail($usuario);

     if($idmesa=='0'){

     }else{

      $pedido = DB::tABLE('pedidos as p')
      ->where('p.mes_id',$idmesa)
      ->where('p.ped_est','Aperturado')
      ->where('p.id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->first();


     }
     


     $usuariobuscar = DB::tABLE('users')->where('IdUsuario',$usuario)->count();



    if (Hash::check($codigo,$user->password_admin) && $usuariobuscar > 0) {


    
        if($request->ajax()) {
          return response()->json(['pedido'=>$idmesa]);
        }
        
    }else{
        
        
          return response()->json(['mensaje' => 'USUARIO NO TIENE AUTORIZACION']);
        
    }

  }



  public function EliminarItemPedido(Request $request,$itempedido,$pedido,$mesa,$producto,$codigo="",$observacion="",$usuario){


      $user = User::findOrFail($usuario);
    
      $usuariobuscar = DB::tABLE('users')->where('IdUsuario',$usuario)->count();

     if (Hash::check($codigo,$user->password_admin) && $usuariobuscar > 0) {


        $deleteped = pedidos_detalle::findOrFail($itempedido);
        $deleteped->estadoitem = 'Eliminado';
         $deleteped->IdUsuarioDel = $usuario;
        $deleteped->MotElim = $observacion;
        $deleteped->update();

        $sucursal = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);

        $cab_pedido = DB::tABLE('pedidos')
           ->where('ped_id',$pedido)
           ->first();

        $mesa= DB::tABLE('mesas')->where('mes_id',$mesa)->first();

           
        $impresoras = DB::tABLE('configuracion_impresoras')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();

        if($sucursal->impresion =='Local'){

            foreach ($impresoras as $impresora) {
                

                $detalle = DB::tABLE('pedidos_detalle')->where('ped_id',$pedido)
               ->join('productos','pedidos_detalle.IdProducto','productos.IdProducto')
               ->leftjoin('categorias','categorias.cat_id','productos.cat_id')
               ->where('categorias.id_empresa_negocio',Auth::user()->id_empresa_negocio)
               ->where('categorias.impresora',$impresora->Id)
               ->where('pedidos_detalle.ped_det_id',$itempedido)
               ->first();

                  $detallecount = DB::tABLE('pedidos_detalle')->where('ped_id',$pedido)
               ->join('productos','pedidos_detalle.IdProducto','productos.IdProducto')
               ->leftjoin('categorias','categorias.cat_id','productos.cat_id')
               ->where('categorias.id_empresa_negocio',Auth::user()->id_empresa_negocio)
               ->where('categorias.impresora',$impresora->Id)
               ->where('pedidos_detalle.ped_det_id',$itempedido)
               ->count();

              if($detallecount >0){

                  $connector = new WindowsPrintConnector("smb://".$impresora->ruta);
                  $printer = new Printer($connector);
                

                    $printer->setJustification(Printer::JUSTIFY_CENTER);
                    $printer->setFont(Printer::FONT_A);
                    $printer->text("ITEM ANULADO "."\n");
                    if(isset($mesa)){
                        $printer->text("Pedido ".$cab_pedido->ped_id.": ". $mesa->mes_nom."\n");
                      }else{
                          $printer->text("Pedido para Llevar ".$cab_pedido->ped_id."\n");
                      }

                    $printer->text("Cliente:". $cab_pedido->cliente."\n");
                    $printer->text("Fecha:". $cab_pedido->fecha_hora."\n");
                   
                    $printer->setJustification(Printer::JUSTIFY_LEFT);
                    
                    
                    $printer->setJustification(Printer::JUSTIFY_LEFT);
                    $printer->text("CONCEPTO                CANTIDAD       OBS."."\n");
                    $printer->text("________________________________________________"."\n");
                    
                       $primeralinea = str_pad(substr($detalle->pronom,0,17),17," ",STR_PAD_RIGHT);
                       $segundalinea = str_pad(substr($detalle->pronom,18,34),17," ",STR_PAD_RIGHT);
                       $printer->text($primeralinea."          ".$detalle->cantidad."        ".$detalle->detalle."\n");
                       $printer->text($segundalinea."\n");
                    
                   $printer->text("\n");
                   

                    $printer->feed();
                    
                    $printer->cut();
                    
                     
                    $printer->pulse();
                   
                    $printer->close();
                  }

              }

        }

        if($request->ajax()) {
          return response()->json(['mensaje' => 'Item Eliminado']);
        }
    }


    
    
  }


  public function aperturarcaja(){
    $caja = new caja;
    $caja->usuario =Auth::user()->IdUsuario;
    $caja->empresa = Auth::user()->IdEmpresa;
    $caja->save();
    
  
    return Redirect::to('/SisFact');
    
  }

  public function cerrarcaja($id){
    $caja = caja::findOrFail($id);
    $caja->usuario =Auth::user()->IdUsuario;
    $caja->empresa = Auth::user()->IdEmpresa;
    $caja->estado = 'Cerrado';
    $caja->update();

     return Redirect::to('/SisFact');
    
  }


  
}
