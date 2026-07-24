<?php

namespace MasterSoft\Http\Controllers\ValetParking;

use Illuminate\Http\Request;
use MasterSoft\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use MasterSoft\Cliente;
use MasterSoft\placas;
use MasterSoft\pedidos;
use MasterSoft\tarifas;
use MasterSoft\pedidos_detalle;
use MasterSoft\usuario_pedidos;
use MasterSoft\Empresa;
use MasterSoft\cpe_cabecera;
use MasterSoft\cpe_detalle;
use MasterSoft\EmpresaNegocios;
use MasterSoft\cuentascobrar;
use MasterSoft\usuario_facturacion;
use MasterSoft\tipocambio;
use MasterSoft\MontoLetras;
use \Milon\Barcode\DNS1D;
use \Milon\Barcode\DNS2D;  
use CodeItNow\BarcodeBundle\Utils\BarcodeGenerator;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use DB;
use Carbon;

class PuntoVentaController extends Controller
{
    

    public function __construct()
    {
        
        $this->middleware('auth');
    }


    public function ingresovehiculo(){

      	$tiposvehiculos = DB::tABLE('tipos_vehiculos')
      	->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();

      	$tarifas = DB::tABLE('tarifas')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();

        $documentos = DB::tABLE('tipo_documento_identidad')->get();
        
      	return view('ValetParking.ingresovehiculo',compact('tiposvehiculos','tarifas','documentos'));

    }

    public function registraringreso(Request $request){

   
        $rucemp = trim(Auth::user()->IdEmpresa);
     
        $tdicod = $request->get('tdicod');
        $placa = strtoupper($request->get('placa'));
        $tipovehiculo = $request->get('tipovehiculo');
        $tarifa = $request->get('tarifa');
        $dni = $request->get('dni');
        $nombre = $request->get('nombre');
        $descripcion = $request->get('descripcion');
        $nombreconductor = $request->get('nombreconductor');
        $telefonoconductor = $request->get('telefonoconductor');
        $placa2 = $request->get('placa2');
        $dniconductor = $request->get('dniconductor');
        $fecha = $request->get('fecha');

        if(empty($placa)){

          return response()->json(['mensaje' => 'Ingresar la placa del vehiculo','codigo'=>'504']);

        }

        $buscarplaca = DB::tABLE('pedidos')
        ->where('placa',$placa)
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('ped_est','Aperturado')
        ->count();

        if($buscarplaca > 0){

          return response()->json(['mensaje' => 'La Placa se encuentra registrada en otro estacionamiento','codigo'=>'504']);

        }

        $cliente = Cliente::FirstOrCreate(['clinum'=>$dni,'rucemp'=>$rucemp],['clinom'=>$nombre,'rucemp'=>$rucemp,'tdicod'=>$tdicod]);
   
        $busplaca = DB::tABLE('placas')->where('plac_prim',$placa)->first();

        if(empty($busplaca)){

            $nueva_placa = new placas;
            $nueva_placa->plac_prim = $placa;
            $nueva_placa->plac_secu = $placa2;
            $nueva_placa->clicod = $cliente->clicod;
            $nueva_placa->save();
        }else{

               $nueva_placa = placas::findOrFail($busplaca->plac_id);
              $nueva_placa->plac_prim = $placa;
              $nueva_placa->plac_secu = $placa2;
              $nueva_placa->clicod = $cliente->clicod;
              $nueva_placa->update();

        }
     

        $buscartarifa = DB::tABLE('tarifas')->where('id_tarifa',$tarifa)->first();
        
        $preuni = $buscartarifa->precio;
        $valuni = $buscartarifa->precio/1.1055;
        $subtotal = $buscartarifa->precio/1.1055;
        $total = $buscartarifa->precio;
        $igv = $total - $subtotal;
        $cantidad = '1';
        $unidad = 'ZZ';
        
        $barcode = new BarcodeGenerator();
         $barcode->setText($placa);
         $barcode->setType(BarcodeGenerator::Gs1128);
         $barcode->setNoLengthLimit(true);
         $barcode->setAllowsUnknownIdentifier(true);
         $code = $barcode->generate();

     

        $pedido = new pedidos;
      //  $pedido->mes_id=$mesas;
        $pedido->placa = $placa;
        $pedido->tdicod =$tdicod;
        $pedido->placa2 = $placa2;
        $pedido->fecha = $fecha;
        $pedido->tipovehiculo=$tipovehiculo;
        $pedido->tarifa = $tarifa;
        $pedido->descripcion = $descripcion;
        $pedido->dni=$dni;
        $pedido->nombreconductor = $nombreconductor;
        $pedido->telefonoconductor = $telefonoconductor;
        $pedido->dniconductor = $dniconductor;
        $pedido->tolerancia = $buscartarifa->tolerancia;
        //$pedido->codigobarra = $code;
        $pedido->nombre=$nombre;
        $pedido->subtotal=$subtotal;
        $pedido->igv=$igv;
        $pedido->total=$total;
        $pedido->id_empresa_negocio=Auth::user()->id_empresa_negocio;
        $pedido->ped_est='Aperturado';
        $pedido->IdUsuario = Auth::user()->IdUsuario;
        $pedido->save();

        $data = base64_decode($code);
        $filepath = "barcode_".$pedido->ped_id.'.png'; 
        file_put_contents($filepath, $data);

        $id_ped = $pedido->ped_id;    

        $ped_det = new pedidos_detalle;
        $ped_det->ped_id=$pedido->ped_id;
        $ped_det->cantidad=$cantidad;
        $ped_det->unidad=$unidad;
        $ped_det->provunitem=$valuni;
        $ped_det->propunitem=$preuni;
        $ped_det->igvitem=$igv;
        $ped_det->subtotalitem=$subtotal;
        $ped_det->IdEmpresa=$rucemp;
        $ped_det->totalitem=$total;
        $ped_det->detalle = $buscartarifa->descripcion;
        $ped_det->save();

        $usuario_pedidos = new usuario_pedidos;
        $usuario_pedidos->ped_id = $id_ped;
        $usuario_pedidos->id_turno = Auth::user()->id_turno;
        $usuario_pedidos->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $usuario_pedidos->IdEmpresa = Auth::user()->IdEmpresa;
        $usuario_pedidos->referencia = "Registro";
        $usuario_pedidos->save();

        self::imprimirticket($id_ped);

        if($request->ajax()) {
            return response()->json(['mensaje' => 'Registrado correctamente','id_ped'=>$id_ped]);
        }

    }


    public function buscarplaca(Request $request, $placa){

 	
        $pedido = DB::tABLE('pedidos')
        ->where('placa',$placa)
        ->where('ped_est','Aperturado')
        ->first();
        

        $cont = DB::tABLE('pedidos')
        ->where('placa',$placa)
        ->where('ped_est','Aperturado')
        ->count();
        
        if($cont == '0'){

           if($request->ajax()) {
            return response()->json(['mensaje' => 'NO SE ENCONTRÓ LA PLACA','codigo'=>'ERROR']);
        	}
        }else{
         
           if($request->ajax()) {
            return response()->json(['mensaje' => 'PLACA ENCONTRADA','codigo'=>$placa]);
        	}

        }
        
    

    }

    public function cobrarplaca($placa){

      $validar = DB::tABLE('pedidos')
      ->where('placa',$placa)
      ->where('ped_est','Aperturado')->get();


      if(count($validar)==0){
      	return Redirect::to('/ingresovehiculo');
      }

      $fechahoractual = now()->format('Y-m-d H:i:s');

      DB::tABLE('pedidos')->where('placa',$placa)
      ->where('ped_est','Aperturado')
      ->update(['fecha_salida'=>$fechahoractual]);

       $pedido = DB::tABLE('pedidos as p')
      ->where('p.placa',$placa)
      ->where('ped_est','Aperturado')
      ->where('p.id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->first();


     
        $creditos = DB::tABLE('credito_dias')->get();

      $mediospagos = DB::tABLE('medios_pagos')->get();

      $rucemp = trim(Auth::user()->IdEmpresa);

      $categorias = DB::tABLE('categorias')
      ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->orderby('cat_nom','asc')->get();

      $tiposvehiculos = DB::tABLE('tipos_vehiculos')
      ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();

      $tarifas = DB::tABLE('tarifas')
      ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->get();

      $comprobante = DB::tABLE('tipo_documento')->get();

      $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();

      $igv = DB::tABLE('tipo_igv')
      ->where('tigest','=','Activo')
      ->orderBy('tigcod','asc')
      ->get();

  

       $bancos = DB::tABLE('bancos')
        ->join('cuentasbancarias','cuentasbancarias.ban_id','bancos.ban_id')
        ->join('tipo_cuentas','tipo_cuentas.tip_cuen_id','cuentasbancarias.tip_cuen_id')
        ->join('moneda','moneda.moncod','cuentasbancarias.moncod')
        ->where('cuentasbancarias.IdEmpresa',Auth::user()->IdEmpresa)
        ->get();

      

      $unidades = DB::tABLE('unidad_medida')->get();

  
      $pedidos = DB::tABLE('pedidos as p')
      ->join('pedidos_detalle as pd','p.ped_id','pd.ped_id')
      ->where('p.ped_id',$pedido->ped_id)
      ->where('ped_est','Aperturado')
      ->where('p.id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->first();

      $buscartarifa = tarifas::findOrFail($pedido->tarifa);

      $unitiempo = DB::tABLE('unidad_tiempo')->where('id_uni_tie',$buscartarifa->id_uni_tie)->first();

      $carbon1 = new \Carbon\Carbon($pedido->fecha_hora);

      $carbon2 = new \Carbon\Carbon($pedido->fecha_salida);

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

        $buscarpedido = pedidos::findOrFail($pedido->ped_ir);
        $buscarpedido->total = $dias * $buscartarifa->precio;
        $buscarpedido->subtotal = $buscarpedido->total/1.1055;
        $buscarpedido->igv = $buscarpedido->total - $buscarpedido->subtotal;
        $buscarpedido->fecha = $fechahoractual;
        $buscarpedido->update();

        $buscarpedidodetalle = pedidos_detalle::findOrFail($pedidos->ped_det_id);
        $buscarpedidodetalle->ped_id=$pedido->ped_ir;
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

        $buscarpedido = pedidos::findOrFail($pedido->ped_id);
        $buscarpedido->total = $horas * $buscartarifa->precio;
        $buscarpedido->subtotal = $buscarpedido->total/1.1055;
        $buscarpedido->igv = $buscarpedido->total - $buscarpedido->subtotal;
        $buscarpedido->update();

        $buscarpedidodetalle = pedidos_detalle::findOrFail($pedidos->ped_det_id);
        $buscarpedidodetalle->ped_id=$pedido->ped_id;
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
      ->where('p.ped_id',$pedido->ped_id)
      ->where('ped_est','Aperturado')
      ->where('p.id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->first();

 
      $users = DB::tABLE('users')->get();


      
      return view('valetparking.cobrarplaca',compact('users','categorias','pedidos','unidades','totales','tipodocumento','comprobante','tiposvehiculos','tarifas','pedido','detallepedido','unitiempo','bancos','mediospagos','creditos'));

    }


    public function registrarcobroplaca(Request $request){


        $rucemp = trim(Auth::user()->IdEmpresa);
        $mediopago = $request->get('mediopago');

        $tdicod = $request->get('tdicod');
        $cliruc = $request->get('clinum');
        $pedido = $request->get('ped_id');

        $pedido_est= pedidos::findOrFail($pedido);

        $tarifa = $request->get('tarifa');

        $buscartarifa = DB::tABLE('tarifas')
        ->where('id_tarifa',$tarifa)
        ->first();

        $cuenta = $request->get('cuen_ban_id');
        $monto = $request->get('monto');
        $medio = $request->get('medio');
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
        $estadopago = $request->get('estadopago');

        $cuentatarjeta = DB::tABLE('medios_pagos')->where('id_med_pag',$mediopago)->first();

        $buscre = DB::tABLE('credito_dias')->where('cre_dia_id',$estadopago)->first();


        $comision=1;
        if($buscre->cre_dia_tip =='CONTADO'){

            $totalcredito =0;
            $totalcontado=$request->get('total');
            $estadopago = $buscre->cre_dia_tip;
            if(empty($cuentatarjeta)){
              $cuen_ban_id = $cuenta;
            }else{
              $cuen_ban_id = $cuentatarjeta->cuen_ban_id;
              $comision = $cuentatarjeta->comision;
            }

            $mediopago = $request->get('mediopago');
            
            
        }else{

            $efectivo = 0;
            $cuen_ban_id = $cuenta;
            $estadopago = $buscre->cre_dia_tip;
            $totalcredito = $request->get('total');
            $totalcontado = 0;
             $mediopago = "0";

        }
       
      
        $topcod = '0101';


        if($tdocod == '01'){
          $senudoc = DB::tABLE('empresa_negocios')->select('FseEmpresa','FnuEmpresa')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
          $numcomp =  $senudoc->FnuEmpresa+1;
          $sercomp =  $senudoc->FseEmpresa;
        }elseif ($tdocod =='03') {
          $senudoc = DB::tABLE('empresa_negocios')->select('BseEmpresa','BnuEmpresa')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
          $numcomp =  $senudoc->BnuEmpresa+1;
          $sercomp =  $senudoc->BseEmpresa;
        }elseif ($tdocod =='13') {
          $senudoc = DB::tABLE('empresa_negocios')->select('SerNota','NumNota')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
          $numcomp =  $senudoc->NumNota+1;
          $sercomp =  $senudoc->SerNota;
        }elseif ($tdocod =='50') {
          $senudoc = DB::tABLE('empresa_negocios')->select('SerVale','NumVale')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
          $numcomp =  $senudoc->NumVale+1;
          $sercomp =  $senudoc->SerVale;
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
        $cliente = Cliente::UpdateOrCreate(['clinum'=>$cliruc],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$clicor,'rucemp'=>$rucemp,'tdicod'=>$tdicod]);


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
        $cabecera->direccion = $clidir;
        $cabecera->moncod = $mondoc;
        $cabecera->tipcambio = $camdoc;
        $cabecera->ccatvg =  $total/1.1055;
        $cabecera->ccaigv = $total-($total/1.1055);
        $cabecera->ccaitv = $total;
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
          $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
          $cabecera->serdoc= $sercomp;
          $cabecera->numdoc = $numdoc;
          
        }elseif($tdocod=='03'){
          if( $empresanegocio->BnuEmpresa == $numcomp){
              $modnumcomp = $numcomp+1;
          }else{
              $modnumcomp = $numcomp;
          }

          $empresanegocio->BseEmpresa = $sercomp;
          $empresanegocio->BnuEmpresa = $modnumcomp;
  
          $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
          $cabecera->serdoc= $sercomp;
          $cabecera->numdoc = $numdoc;
          
        }elseif($tdocod=='13'){
          if( $empresanegocio->NumNota == $numcomp){
              $modnumcomp = $numcomp+1;
          }else{
              $modnumcomp = $numcomp;
          }

          $empresanegocio->SerNota = $sercomp;
          $empresanegocio->NumNota = $modnumcomp;
    

          $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
          $cabecera->serdoc= $sercomp;
          $cabecera->numdoc = $numdoc;
      
        }elseif($tdocod=='50'){
          if( $empresanegocio->NumVale == $numcomp){
              $modnumcomp = $numcomp+1;
          }else{
              $modnumcomp = $numcomp;
          }

          $empresanegocio->SerVale = $sercomp;
          $empresanegocio->NumVale = $modnumcomp;
        

          $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
          $cabecera->serdoc= $sercomp;
          $cabecera->numdoc = $numdoc;
          
        }


           $empresanegocio->update();

          $empresa->update();
          $cabecera->save();

          $codfact = $cabecera->IdCpe_cabecera; 

           foreach ($medio as $index => $mp) {
             
             if($monto[$index] > '0.00'){

                DB::tABLE('venta_medio_pago')
                ->insert(['id_turno'=>Auth::user()->id_turno,'IdCpe_cabecera'=>$codfact,'id_med_pag'=>$mp,'monto'=>$monto[$index]]);

                $datamp = DB::tABLE('medios_pagos')->where('id_med_pag',$mp)->first();

                if($buscre->cre_dia_tip =='CONTADO' &&  !empty($datamp->cuen_ban_id)){

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
                  $movimiento->importe = $cabecera->totalcontado;
                }else{
                  $movimiento->importe = $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)*1.18)); 
                }
                        
                $movimiento->estado = '1';
                $movimiento->mov_fecha = $cabecera->ccafem;
                $movimiento->clicod = $cabecera->clicod;
                $movimiento->registro = 'Registrado';
                 
                if($contar==0){
                  $totalsaldo =  $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)*1.18));
                }else{
                  $totalsaldo = $buscar->last()->saldo + $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)*1.18));
                }
                          

                $movimiento->saldo = $totalsaldo;
                $movimiento->IdEmpresa = Auth::user()->IdEmpresa;
                $movimiento->id_empresa_negocio = Auth::user()->id_empresa_negocio;
                $movimiento->save();


              }
             }
            

          }

          $usuario_facturacion = new usuario_facturacion;
          $usuario_facturacion->IdCpe_cabecera = $codfact;
          $usuario_facturacion->id_turno = Auth::user()->id_turno;
          $usuario_facturacion->ped_id = $pedido;
          $usuario_facturacion->id_empresa_negocio = Auth::user()->id_empresa_negocio;
          $usuario_facturacion->IdEmpresa = Auth::user()->IdEmpresa;
          $usuario_facturacion->referencia = "Registro";
          $usuario_facturacion->save();


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

            $detalle->procod = '';
            $detalle->cdepsu = "";
            $detalle->cdedes =  $buscartarifa->descripcion;
            $detalle->cdevun =  $pedidosdetalle->provunitem;
            $detalle->cdepuni = $pedidosdetalle->propunitem;
            $detalle->tigcod = '10';
            $detalle->cdevve = $total;
            $detalle->cdepve = $total/1.1055;
            $detalle->cdeigv = $total-($total/1.1055);
            $detalle->fecha_venta = $fecemi;
            $detalle->save();
    

            if($buscre->cre_dia_tip =='CREDITO'){

              $cuentacobrar = new cuentascobrar;
              $cuentacobrar->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
              $cuentacobrar->clicod = $cabecera->clicod;
              $cuentacobrar->fec_ven = date('Y-m-d',strtotime($fecemi."+ ".$buscre->cre_dia_fac));
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

            }


      if($buscre->cre_dia_tip =='CONTADO' &&  !empty($cuen_ban_id)){

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
                  $totalsaldo =  $cabecera->ccaitv-($cabecera->ccaitv*(($comision/100)*1.18));
                }else{
                  $totalsaldo = $buscar->last()->saldo + $cabecera->ccaitv-($cabecera->ccaitv*(($comision/100)*1.18));
                }
                

              $movimiento->saldo = $totalsaldo;
              $movimiento->IdEmpresa = Auth::user()->IdEmpresa;
              $movimiento->id_empresa_negocio = Auth::user()->id_empresa_negocio;
              $movimiento->save();


      }
    
      if($tdocod =='01' || $tdocod =='03'){
         
        $cabecera->generar_xml_boleta_factura($codfact);

        self::imprimir($codfact,$tdocod);

      }

      
   
         
       if($request->ajax()) {
            return response()->json(['mensaje' => 'Registrado correctamente']);
        }

    }


      public function imprimir($cpe,$tipdoc){
    
    $rucemp = Auth::user()->IdEmpresa;

    $empresa = Empresa::findOrFail($rucemp);

    $empresanegocios = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);

    $nomdoc = DB::tABLE('tipo_documento')->where('tdocod',$tipdoc)->first();
  
 
     if($tipdoc == '01' || $tipdoc == '03' || $tipdoc == '13'){
      $cabecera = DB::tABLE('cpe_cabecera as cab')
      ->leftjoin('cliente as cli','cab.ccandi','=','cli.clinum')
      ->leftjoin('tipo_documento as tdo','cab.tdocod','=','tdo.tdocod')
      ->leftjoin('tipo_documento_identidad as tdi','cab.tdicod','=','tdi.tdicod')
      ->leftjoin('moneda as mon','cab.moncod','=','mon.moncod')
      ->leftjoin('tipo_operacion as top','cab.topcod','=','top.topcod')
      
      ->where('IdCpe_cabecera','=',$cpe)
      ->first();
    
        

    $totalletras= MontoLetras::convertir(number_format($cabecera->ccaitv,'2','.',''),$cabecera->monnom,'Centimos');
      
     $detalle=DB::tABLE('cpe_detalle as det')
     ->leftjoin('unidad_medida as umed','det.umecod','=','umed.umecod')
     ->where('IdCpe_cabecera','=',$cpe)->get();

     $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

    }

      $impresoras = DB::tABLE('configuracion_impresoras')->where('Id',Auth::user()->terminal)->first();

      $medios = DB::tABLE('venta_medio_pago')
      ->join('medios_pagos','medios_pagos.id_med_pag','venta_medio_pago.id_med_pag')
      ->where('IdCpe_cabecera',$cpe)
      ->get();



    try { 

      $connector = new WindowsPrintConnector("smb://".$impresoras->ruta);

     
        $printer = new Printer($connector);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
       
       if($cabecera->tdocod !='13'){
          if(file_exists($empresanegocios->logosuc)){
              $logo = EscposImage::load(public_path().'/'.$empresanegocios->logosuc,false);
              $printer->bitImage($logo);
        }
        $printer->setFont(Printer::FONT_A);

        $printer->text("CALLE ABTAO N° 16 - BELEN "."\n");
    
        $printer->text("Teléfono: 948-132687  -  947-545913"."\n"."\n");
		   }
		   
        $printer->text("Vendedor: ".$cabecera->vendedor."\n"."\n");
		    if($cabecera->tdocod !='13'){
        $printer->text("BELEN - MAYNAS - LORETO"."\n"."\n");
     //   $printer->text("OUT & PRIDE"."\n");
        $printer->text('RUC:'.$empresa->IdEmpresa."\n");
     
       }
       

        $printer->text($nomdoc->tdodes."\n");
        $printer->text($cabecera->serdoc."-".$cabecera->numdoc."\n"."\n");
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("Fecha:       ".$cabecera->fecha_hora."\n");
        $printer->text("RUC/DNI:     ".$cabecera->clinum."\n");
        $printer->text("Cliente:     ".$cabecera->ccanom."\n");
        $printer->text("Dirección:   ".$cabecera->direccion."\n"."\n");
    

        if(!empty($mesa)){
          $printer->text("Mesa:       ".$mesa->mes_nom."\n"."\n");
        }
       
      
       
        
        $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->text("CODIGO    DESCRIPCION "."\n");
        $printer->text("CANTIDAD    UNIDAD    PRECIO    TOTAL"."\n");
        $printer->text("________________________________________________"."\n");
        foreach ($detalle as $det) {
         
        if($det->cdevve > 0){
           $printer->text($det->procod."  ".$det->cdedes."\n");
       $printer->text($det->cdecan."       ".$det->umecod."       ".$det->cdepuni."       ".$det->cdevve."\n\n");
     }else{

        $printer->text($det->cdedes."\n");
      
     }
          
       
           
          
          
        }
       $printer->text("\n");
         $printer->text("_______________________________________________"."\n");

      foreach ($medios as $m) {
          $printer->text($m->nom_med_pag." ".$cabecera->simbolo."                        ".$m->monto."\n");
      }

      if($cabecera->tdocod !='13'){  
       $printer->text("SUBTOTAL: ".$cabecera->simbolo."                       ".$cabecera->ccatvg."\n");
       //  $printer->text("OP. GRAVADA: ".$cabecera->simbolo."                    ".$cabecera->ccatvg."\n");
       $printer->text("OP. EXONERADA: ".$cabecera->simbolo."                  ".$cabecera->ccatexo."\n");
      // $printer->text("OP. INAFECTA: ".$cabecera->simbolo."         "."0.00"."\n");
       $printer->text("IGV 18%: ".$cabecera->simbolo."                        ".$cabecera->ccaigv."\n");
       $printer->text("ICBPER: ".$cabecera->simbolo."                         ".$cabecera->icbper."\n");
      }
       $printer->text("TOTAL: ".$cabecera->simbolo."                          ".$cabecera->ccaitv."\n"."\n");
       $printer->text("PAGA CON: ".$cabecera->simbolo."                       ".$cabecera->paga."\n");
       $printer->text("VUELTO: ".$cabecera->simbolo."                         ".$cabecera->vuelto."\n"."\n");
       
       $printer->text($totalletras." ".$cabecera->monnom."\n"."\n");
 $printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->text("REPRESENTACIÓN IMPRESA DE LA ".$cabecera->tdodes."\n"."\n");
       if($cabecera->tdocod=='01' || $cabecera->tdocod=='03'){
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $logo1 = EscposImage::load(public_path().'/qr/QR-'.$rucemp.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$cabecera->numdoc.'.png',false);
           $printer->bitImage($logo1);
        }
        if($cabecera->tdocod !='13'){  
           $printer->text("\n"."BIENES TRANSFERIDOS EN LA AMAZONIA PARA SER CONSUMIDOS EN LA MISMA. SERVICIOS PRESTADOS EN LA AMAZONIA"."\n");
      $printer->setJustification(Printer::JUSTIFY_CENTER);
      $printer->text("\n".$empresanegocios->pie."\n");

}

        $printer->feed();
         
     
        $printer->cut();
         
     
        $printer->pulse();
         
        /*
          Para imprimir realmente, tenemos que "cerrar"
          la conexión con la impresora. Recuerda incluir esto al final de todos los archivos
        */
        $printer->close();
      }catch (\Exception $e) {
           //dd($e);

      }
  }
  

     public function imprimirticket($pedido){

       	try{

       		 $IdEmpresa = Auth::user()->IdEmpresa;

          $empresa = Empresa::findOrFail($IdEmpresa);

          $empresanegocios = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

          $cab_pedido = DB::tABLE('pedidos')
           ->where('ped_id',$pedido)
           ->first();

           $detalle = DB::tABLE('pedidos_detalle')->where('ped_id',$pedido)
               ->leftjoin('productos','pedidos_detalle.IdProducto','productos.IdProducto')
               ->leftjoin('categorias','categorias.cat_id','productos.cat_id')
               
               ->get();

          $cajero = DB::tABLE('users')->where('IdUsuario',$cab_pedido->IdUsuario)->first();
          $mesa= DB::tABLE('mesas')->where('mes_id',$cab_pedido->mes_id)->first();

          $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

                  $connector = new WindowsPrintConnector("smb://miraflores/caja1");
                  $printer = new Printer($connector);
                

                    $printer->setJustification(Printer::JUSTIFY_CENTER);
                    $printer->text("\n"."\n");
                    $img = EscposImage::load(public_path()."/barcode_".$cab_pedido->ped_id.".png");
                    $printer->bitImage($img,Printer::IMG_DOUBLE_WIDTH | Printer::IMG_DOUBLE_HEIGHT);
                  // $printer -> setBarcodeHeight(48);
                  // $printer->setBarcodeTextPosition(Printer::BARCODE_TEXT_BELOW);
                  // $printer -> barcode($cab_pedido->placa,Printer::BARCODE_Gs1128);
                    $printer->setFont(Printer::FONT_A);
                    $printer->text("\n"."\n".$sucursal->nombre_comercial."\n");
                   
                    $printer->text("TELEFONO: ".$sucursal->telefono."\n");
                    $printer->text($sucursal->direccion."\n");
                    $printer->text($sucursal->distrito." ".$sucursal->provincia." ".$sucursal->departamento);
                    $printer->text("Fecha: ".Carbon::parse($cab_pedido->fecha_hora)->format('d-m-Y')."\n");
                    $printer->text("Ingreso: ".Carbon::parse($cab_pedido->fecha_hora)->format('H:i')."\n");
                     $printer->text("Placa: ".$cab_pedido->placa."\n");
                    $printer->setJustification(Printer::JUSTIFY_LEFT);
                    
                    
                    $printer->setJustification(Printer::JUSTIFY_LEFT);
                    $printer->text("CONCEPTO    "."\n");
                    $printer->text("________________________________________"."\n");
                    foreach ($detalle as $det) {
                     
                   
                       $printer->text($det->detalle."\n");

                    }
                     $printer->setJustification(Printer::JUSTIFY_CENTER);
                     $printer->text("\n"."\n"."ATENCION 24 HORAS "."\n");
                     $printer->text("\n"."Cajero: ".$cajero->name." ".$cajero->apeusu."\n");

                   $printer->text("\n");
                    $printer->feed();
                    $printer->cut();
                    //$printer->pulse();
                    $printer->close();


       	}catch(\Exception $e){

       	}
         


    }

    public function eliminaringreso(Request $request){

    	$placa = $request->get('id');

    	$eliminar = pedidos::findOrFail($placa);
    	$eliminar->ped_est ='Eliminado';
    	$eliminar->update();

    	return Redirect::to('/ingresovehiculo');
    }


}
