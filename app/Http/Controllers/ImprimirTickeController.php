<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Empresa;
use MasterSoft\cpe_cabecera;
use MasterSoft\EmpresaNegocios;
use MasterSoft\gastos_cabecera;
use MasterSoft\pedidos_detalle;
use MasterSoft\MontoLetras;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;

use PDF;
use DB;

class ImprimirTickeController extends Controller
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
       return view('empresas.cierre.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
   
       public function imprimirturno($turno){

          $IdEmpresa = Auth::user()->IdEmpresa;

          $empresa = Empresa::findOrFail($IdEmpresa);


          $empresanegocios = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

    
          $impresoras = DB::tABLE('configuracion_impresoras')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();

       //   if($empresanegocios->impresion =='Web'){

            

               $gastos = DB::tABLE('gastos_cabecera as g')
               ->join('usuario_gastos as ug','ug.gast_cab_id','g.gast_cab_id')
               ->where('ug.id_turno',$turno)
               ->where('referencia','GASTO')
               ->where('g.est_gasto','!=','Eliminado')
               ->sum('total_gast');

               $ingresos = DB::tABLE('gastos_cabecera as g')->join('usuario_gastos as ug','ug.gast_cab_id','g.gast_cab_id')->where('ug.id_turno',$turno)->where('referencia','INGRESO')->where('g.est_gasto','!=','Eliminado')->sum('total_gast');





               //CANTIDAD DE BOLETAS - CANTIDAD DE FACTURAS - CANTIDAD NOTAS
               $cantidadboletas = DB::tABLE('cpe_cabecera as cp')->join('usuario_facturacion as uf','uf.IdCpe_cabecera','cp.IdCpe_cabecera')->where('uf.id_turno',$turno)->where('tdocod','03')->count();

               $cantidadfacturas = DB::tABLE('cpe_cabecera as cp')->join('usuario_facturacion as uf','uf.IdCpe_cabecera','cp.IdCpe_cabecera')->where('uf.id_turno',$turno)->where('tdocod','01')->count();

               $cantidadnotas = DB::tABLE('cpe_cabecera as cp')->join('usuario_facturacion as uf','uf.IdCpe_cabecera','cp.IdCpe_cabecera')->where('uf.id_turno',$turno)->where('tdocod','13')->count();


               // TOTAL MONTO NOTAS - TOTAL MONTOS BOLETAS - TOTAL MONTO FACTURAS
               $montonotas = DB::tABLE('cpe_cabecera as cp')->join('usuario_facturacion as uf','uf.IdCpe_cabecera','cp.IdCpe_cabecera')->where('uf.id_turno',$turno)->where('tdocod','13')->sum('ccaitv');

               $montoboletas = DB::tABLE('cpe_cabecera as cp')->join('usuario_facturacion as uf','uf.IdCpe_cabecera','cp.IdCpe_cabecera')->where('uf.id_turno',$turno)->where('tdocod','03')->sum('ccaitv');

               $montofacturas = DB::tABLE('cpe_cabecera as cp')->join('usuario_facturacion as uf','uf.IdCpe_cabecera','cp.IdCpe_cabecera')->where('uf.id_turno',$turno)->where('tdocod','01')->sum('ccaitv');

               $totalventas = DB::tABLE('cpe_cabecera as cp')->join('usuario_facturacion as uf','uf.IdCpe_cabecera','cp.IdCpe_cabecera')->where('uf.id_turno',$turno)->sum('ccaitv');




               //mayor y menor de boletas, facturas, notas
               $maymennotas = DB::tABLE('cpe_cabecera as cp')->join('usuario_facturacion as uf','uf.IdCpe_cabecera','cp.IdCpe_cabecera')->where('uf.id_turno',$turno)->where('tdocod','13')->get();

               $maymenboletas = DB::tABLE('cpe_cabecera as cp')->join('usuario_facturacion as uf','uf.IdCpe_cabecera','cp.IdCpe_cabecera')->where('uf.id_turno',$turno)->where('tdocod','03')->get();

               $maymenfacturas = DB::tABLE('cpe_cabecera as cp')->join('usuario_facturacion as uf','uf.IdCpe_cabecera','cp.IdCpe_cabecera')->where('uf.id_turno',$turno)->where('tdocod','01')->get();

          


               // TOTAL EFECTIVO - TOTAL VISA - TOTAL MASTERCARD - TOTAL TRANSFERENCIA
               $efectivo = DB::tABLE('cpe_cabecera as cp')->join('usuario_facturacion as uf','uf.IdCpe_cabecera','cp.IdCpe_cabecera')->where('uf.id_turno',$turno)->sum('efectivo');

               $visa = DB::tABLE('cpe_cabecera as cp')->join('usuario_facturacion as uf','uf.IdCpe_cabecera','cp.IdCpe_cabecera')->where('uf.id_turno',$turno)->sum('visa');

               $mastercard = DB::tABLE('cpe_cabecera as cp')->join('usuario_facturacion as uf','uf.IdCpe_cabecera','cp.IdCpe_cabecera')->where('uf.id_turno',$turno)->sum('mastercard');

               $transferencia = DB::tABLE('cpe_cabecera as cp')->join('usuario_facturacion as uf','uf.IdCpe_cabecera','cp.IdCpe_cabecera')->where('uf.id_turno',$turno)->sum('transferencia');

               $cantidadcredito = DB::tABLE('cpe_cabecera as cp')
                   ->join('usuario_facturacion as uf','uf.IdCpe_cabecera','cp.IdCpe_cabecera')
                   ->where('uf.id_turno',$turno)
                   ->where(DB::raw('upper(trim(cp.estadopago))'),'CREDITO')
                   ->count();

               $totalcredito = DB::tABLE('cpe_cabecera as cp')
                   ->join('usuario_facturacion as uf','uf.IdCpe_cabecera','cp.IdCpe_cabecera')
                   ->where('uf.id_turno',$turno)
                   ->where(DB::raw('upper(trim(cp.estadopago))'),'CREDITO')
                   ->sum('totalcredito');

               $mennot = "";
               $maynot = "";
               $menbol = "";
               $maybol = "";
               $menfac = "";
               $mayfac = "";

               foreach ($maymennotas as $not) {
                  $arraynot[] = $not->serdoc.'-'.$not->numdoc;
               }

               foreach ($maymenboletas as $bol) {
                  $arraybol[] = $bol->serdoc.'-'.$bol->numdoc;
               }

               foreach ($maymenfacturas as $fac) {
                  $arrayfac[] = $fac->serdoc.'-'.$fac->numdoc;
               }

               if(!isset($arraynot)){
                $arraynot[]=[];
               }else{
                $mennot = min($arraynot);
                $maynot = max($arraynot);
               }

               if(!isset($arraybol)){
                $arraybol[]=[];
               }else{
                $menbol = min($arraybol);
                $maybol = max($arraybol);
               }

               if(!isset($arrayfac)){
                $arrayfac[]=[];
               }else{
                $menfac = min($arrayfac);
                $mayfac = max($arrayfac);
               }

               

               
               

              //TOTAL ESTACIONAMIENTOS OCUPADOS
                $totalocupados = DB::tABLE('mesas')
                ->where('mes_est','Ocupado')
                ->where('IdEmpresa',Auth::user()->IdEmpresa)
                ->count();

              //TOTAL ESTACIONAMIENTOS LIBRES
                $totallibres = DB::tABLE('mesas')
                ->where('mes_est','Libre')
                ->where('IdEmpresa',Auth::user()->IdEmpresa)
                ->count();


                $datosturno = DB::tABLE('turnos')->where('id_turno',$turno)->first();


                $cajero = DB::tABLE('turnos as t')->join('users as u','u.IdUsuario','t.IdUsuario')->where('t.id_turno',$turno)->first();
                             
                
               return view('formatos_comprobantes.ticket_cajaturno',compact('empresa','sucursal','datosturno','gastos','ingresos','totalocupados','totallibres','cajero','efectivo','visa','mastercard','transferencia','cantidadnotas','cantidadfacturas','cantidadboletas','montonotas','montoboletas','montofacturas','turno','maymennotas','maymenfacturas','maymenboletas','menbol','maybol','mennot','maynot','menfac','mayfac','totalventas','cantidadcredito','totalcredito'));

       /*    }else{

              foreach ($impresoras as $impresora) {
                
                $detalle = DB::tABLE('pedidos_detalle')->where('ped_id',$pedido)
               ->leftjoin('productos','pedidos_detalle.IdProducto','productos.IdProducto')
               ->leftjoin('categorias','categorias.cat_id','productos.cat_id')
               ->where('categorias.id_empresa_negocio',Auth::user()->id_empresa_negocio)
               
               ->where('categorias.impresora',$impresora->Id)
               ->get();

               $detallecount = DB::tABLE('pedidos_detalle')->where('ped_id',$pedido)
               ->join('productos','pedidos_detalle.IdProducto','productos.IdProducto')
               ->leftjoin('categorias','categorias.cat_id','productos.cat_id')
               ->where('categorias.id_empresa_negocio',Auth::user()->id_empresa_negocio)
               ->where('pedidos_detalle.impreso','imprimir')
               ->where('categorias.impresora',$impresora->Id)
               ->count();

              if($detallecount >0){
                  $connector = new WindowsPrintConnector("smb://".$impresora->ruta);
                  $printer = new Printer($connector);
                
                  $printer->setJustification(Printer::JUSTIFY_CENTER);
                  $printer->setFont(Printer::FONT_A);
                  $printer->text($cab_pedido->etiqueta."\n");
                  $printer->text("Pedido ".$cab_pedido->ped_id.": ". $mesa->mes_nom."\n");
                 //   $printer->text("OUT & PRIDE"."\n");
                  $printer->text("Fecha:". $cab_pedido->fecha_hora."\n"); 
                  $printer->setJustification(Printer::JUSTIFY_LEFT);
                      
                  $printer->setJustification(Printer::JUSTIFY_LEFT);
                  $printer->text("CONCEPTO                CANTIDAD       OBS."."\n");
                  $printer->text("________________________________________________"."\n");
                  
                  foreach ($detalle as $det) {   
                       $primeralinea = str_pad(substr($det->pronom,0,17),17," ",STR_PAD_RIGHT);
                       $segundalinea = str_pad(substr($det->pronom,18,34),17," ",STR_PAD_RIGHT);
                       $printer->text($primeralinea."          ".$det->cantidad."        ".$det->detalle."\n");
                       $printer->text($segundalinea."\n");
                       $buscardetalle = pedidos_detalle::findOrFail($det->ped_det_id);
                       $buscardetalle->impreso ='impreso';
                       $buscardetalle->update();
                  }

                  $printer->text("\n");
                  $printer->feed();
                  $printer->cut();
                  $printer->pulse();
                  $printer->close();

              }

              $buscarpedido = DB::tABLE('pedidos')
              ->where('ped_id',$pedido)
              ->update(['etiqueta' => ""]); 
            }
          }*/

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
      ->where('IdCpe_cabecera','=',$cpe)->where('IdEmpresa','=',$rucemp)
      ->first();
    
      $mesa = DB::tABLE('mesas')->where('mes_id',$cabecera->mes_id)->first();

    $totalletras= MontoLetras::convertir(number_format($cabecera->ccaitv,'2','.',''),$cabecera->monnom,'Centimos');
      
     $detalle=DB::tABLE('cpe_detalle as det')->join('unidad_medida as umed','det.umecod','=','umed.umecod')->where('IdCpe_cabecera','=',$cpe)->get();

     $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

    }elseif($tipdoc == '07'){
      $cabecera = DB::tABLE('cpe_nota as cpe_n')
              ->select('cpe_n.ccafem','tdodes','cpe_n.serdoc','clinum','cpe_n.ccaigv','cpe_n.ccatvg','simbolo','clidir','clinom','cpe_n.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_n.ccaitv','cpe_c.IdEmpresa','cpe_n.tdocod','cpe_n.IdCpe_nota','cpe_c.ccanot','cpe_c.ccabaj','cpe_n.codhash','cpe_n.ccasunrescod')
              ->join('cpe_cabecera as cpe_c','cpe_n.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
        ->join('cliente as cli','cpe_n.ccandi','=','cli.clinum')
              ->join('tipo_documento as tip_d','cpe_n.tdocod','=','tip_d.tdocod')
              ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
              ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
      ->where('cpe_n.IdCpe_nota','=',$cpe)->where('cpe_n.IdEmpresa','=',$rucemp)
      ->first();
      
      $totalletras= MontoLetras::convertir(number_format($cabecera->ccaitv,'2','.',''),$cabecera->monnom,'Centimos');
      
      $detalle=DB::tABLE('cpe_nota_detalle as det')->join('unidad_medida as umed','det.umecod','=','umed.umecod')->where('IdCpe_nota','=',$cpe)->get();

      $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

    }elseif($tipdoc == '08'){
      $cabecera = DB::tABLE('cpe_nota as cpe_n')
              ->select('cpe_n.ccafem','tdodes','cpe_n.serdoc','clinum','simbolo','cpe_n.ccaigv','cpe_n.ccatvg','clidir','clinom','cpe_n.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_n.ccaitv','cpe_c.IdEmpresa','cpe_n.tdocod','cpe_n.IdCpe_nota','cpe_c.ccanot','cpe_c.ccabaj','cpe_n.codhash','cpe_n.ccasunrescod')
              ->join('cpe_cabecera as cpe_c','cpe_n.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
        ->join('cliente as cli','cpe_n.ccandi','=','cli.clinum')
              ->join('tipo_documento as tip_d','cpe_n.tdocod','=','tip_d.tdocod')
              ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
              ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
      ->where('cpe_n.IdCpe_nota','=',$cpe)->where('cpe_n.IdEmpresa','=',$rucemp)
      ->first();
      
        $totalletras= MontoLetras::convertir(number_format($cabecera->ccaitv,'2','.',''),$cabecera->monnom,'Centimos');
      $detalle=DB::tABLE('cpe_nota_detalle as det')->join('unidad_medida as umed','det.umecod','=','umed.umecod')->where('IdCpe_nota','=',$cpe)->get();

      $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

    }

      $impresoras = DB::tABLE('configuracion_impresoras')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('descripcion','CAJA')->first();

      $connector = new WindowsPrintConnector("smb://".$impresoras->ruta);

     
      $printer = new Printer($connector);
    

        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setFont(Printer::FONT_A);
        $printer->text($empresa->NomEmpresa."\n");
     //   $printer->text("OUT & PRIDE"."\n");
        $printer->text($empresanegocios->direccion."\n");
        $printer->text($nomdoc->tdodes."\n");
        $printer->text($cabecera->serdoc."-".$cabecera->numdoc."\n"."\n");
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("Fecha:     ".$cabecera->fecha_hora."\n");
        $printer->text("RUC/DNI:       ".$cabecera->clinum."\n");
        $printer->text("Cliente:     ".$cabecera->ccanom."\n");
        $printer->text("Dirección: ".$cabecera->clidir."\n"."\n");
        
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("CONCEPTO                CANTIDAD       IMPORTE"."\n");
        $printer->text("________________________________________________"."\n");
        foreach ($detalle as $det) {
         
           $primeralinea = str_pad(substr($det->cdedes,0,17),17," ",STR_PAD_RIGHT);
           $segundalinea = str_pad(substr($det->cdedes,18,34),17," ",STR_PAD_RIGHT);
           $printer->text($primeralinea."          ".$det->cdecan."        ".$det->cdevve."\n");
           $printer->text($segundalinea."\n");
        }
       $printer->text("\n");
         $printer->text("________________________________________________"."\n");
      if($cabecera->visa!= 0.00){
      $printer->text("VISA ".$cabecera->simbolo."                                ".$cabecera->visa."\n");
      
      }

      if($cabecera->mastercard!= 0.00){
      $printer->text("MAST ".$cabecera->simbolo."                                ".$cabecera->mastercard."\n");
      
      }

      if($cabecera->efectivo!= 0.00){
      $printer->text("EFEC ".$cabecera->simbolo."                                ".$cabecera->efectivo."\n");
      
      }

       $printer->text("TOTAL: ".$cabecera->simbolo."                              ".$cabecera->ccaitv."\n");
      $printer->text("Tipo de Pago: ".$cabecera->tipo_pago."\n"."\n");
     
       $printer->text($totalletras." .".$cabecera->monnom."\n");

        $printer->feed();
         
     
        $printer->cut();
         
     
        $printer->pulse();
         
        /*
          Para imprimir realmente, tenemos que "cerrar"
          la conexión con la impresora. Recuerda incluir esto al final de todos los archivos
        */
        $printer->close();
  }
  
  
  public function imprimirpedido($pedido,$tipo){

    

        if($tipo != 'cobrar'){
  
           $IdEmpresa = Auth::user()->IdEmpresa;

           $empresa = Empresa::findOrFail($IdEmpresa);

           $empresanegocios = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

           $cab_pedido = DB::tABLE('pedidos')
           ->where('ped_id',$pedido)
           ->first();

           $mesa= DB::tABLE('mesas')->where('mes_id',$cab_pedido->mes_id)->first();

           
           $impresoras = DB::tABLE('configuracion_impresoras')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();



     

               $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

               $detalle = DB::tABLE('pedidos_detalle')->where('ped_id',$pedido)
               ->join('productos','pedidos_detalle.IdProducto','productos.IdProducto')
               ->leftjoin('categorias','categorias.cat_id','productos.cat_id')
               ->where('pedidos_detalle.impreso','imprimir')
               ->get();

               foreach ($detalle as $det) {
                
                $buscardetalle = pedidos_detalle::findOrFail($det->ped_det_id);
                $buscardetalle->impreso ='impreso';
                $buscardetalle->update();
               }

               return view('formatos_comprobantes.pedido',compact('cab_pedido','detalle','mesa','empresa','sucursal'));



        }else{

            
         $buscomp = DB::tABLE('cpe_cabecera')->where('ped_id',$pedido)->first();

         $cpe = $buscomp->IdCpe_cabecera;
         $tdocod = $buscomp->tdocod;
         $tipdoc = $buscomp->tdocod;
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
        ->where('IdCpe_cabecera','=',$cpe)->where('IdEmpresa','=',$rucemp)
        ->first();
      
        $mesa = DB::tABLE('mesas')->where('mes_id',$cabecera->mes_id)->first();

      $totalletras= MontoLetras::convertir(number_format($cabecera->ccaitv,'2','.',''),$cabecera->monnom,'Centimos');
        
       $detalle=DB::tABLE('cpe_detalle as det')->join('unidad_medida as umed','det.umecod','=','umed.umecod')->where('IdCpe_cabecera','=',$cpe)->get();

       $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

      }

    $numdoc = str_pad($cabecera->numdoc,8,"0", STR_PAD_LEFT);
    $qrfile =  'QR-'.$cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.png'; 
    $imgqr = "/qr/".$qrfile;


        return view('formatos_comprobantes.ticket_factura',compact('cabecera','totalletras','detalle','mesa','empresa','sucursal','imgqr'));


  }



    }


    public function imprimircuentaweb($pedido){

            $IdEmpresa = Auth::user()->IdEmpresa;
                    
                
           $IdEmpresa = Auth::user()->IdEmpresa;
           $cab_pedido = DB::tABLE('pedidos')
           ->where('ped_id',$pedido)
           ->first();

           $mesa= DB::tABLE('mesas')->where('mes_id',$cab_pedido->mes_id)->first();

           $detalle = DB::tABLE('pedidos_detalle')->where('ped_id',$pedido)->join('productos','pedidos_detalle.IdProducto','productos.IdProducto')->get();

   

           return view('formatos_comprobantes.precuenta',compact('cab_pedido','detalle','mesa'));


    }


        public function imprimircuenta($pedido)
{
    $IdEmpresa = Auth::user()->IdEmpresa;
    
    $cab_pedido = DB::table('pedidos')
        ->leftjoin('users','users.IdUsuario','pedidos.mozo')
        ->where('ped_id',$pedido)
        ->first();
    
    $mesa = DB::table('mesas')->where('mes_id',$cab_pedido->mes_id)->first();
    $piso = DB::table('pisos')->where('pis_id',$cab_pedido->pis_id)->first();
    
    // ✅ MODIFICADO: Solo items pendientes con cantidad_pendiente calculada
    $detalle = DB::table('pedidos_detalle')
        ->join('productos','pedidos_detalle.IdProducto','productos.IdProducto')
        ->select('pedidos_detalle.*', 'productos.*',
            DB::raw('(ped_det_can - IFNULL(item_facturado, 0)) as cantidad_pendiente'))
        ->where('ped_id',$pedido)
        ->where('estadoitem','Ingresado')
        ->whereRaw('(ped_det_can - IFNULL(item_facturado, 0)) > 0') // Solo pendientes
        ->get();
    
    // ✅ MODIFICADO: Calcular total con cantidad_pendiente
    $consumo_total = 0;
    foreach ($detalle as $det) {
        $consumo_total += $det->cantidad_pendiente * $det->ped_det_pre;
    }
    
    try {
        $impresoras = DB::table('configuracion_impresoras')
            ->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)
            ->where('predeterminado','1')
            ->first();
        
        $connector = new WindowsPrintConnector("smb://".$impresoras->ruta);
        $printer = new Printer($connector);
        
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setFont(Printer::FONT_A);
        $printer->text("PRECUENTA"."\n"."\n");
        
        if(isset($mesa)){
            $printer->text($mesa->mes_nom."\n");
        }
        
        if(!empty($cab_pedido->mozo)){
            $printer->text("Mozo: ". $cab_pedido->name.' '.$cab_pedido->apeusu."\n"."\n");
        }
        
        $printer->text("Fecha: ". $cab_pedido->fecha_hora."\n");
        
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("CONCEPTO  CANTI.   PU  IMPORTE"."\n");
        $printer->text("_____________________________"."\n");
        $printer->setFont(Printer::FONT_A);
        $printer->setTextSize(1,1);
        
        // ✅ MODIFICADO: Usar cantidad_pendiente en la impresión
        foreach ($detalle as $det) {
            $primeralinea = ($det->descripcion);
            $subtotal = $det->ped_det_pre * $det->cantidad_pendiente;
            $printer->text($primeralinea."  ".$det->cantidad_pendiente."  ".$det->ped_det_pre."  ".number_format($subtotal,'2','.','')."\n");
        }
        
        $printer->text("_____________________________"."\n");
        $printer->text("CONSUMO TOTAL: ".number_format($consumo_total, 2, '.', '')."\n"."\n");
        $printer->text("YAPE: 960 703 010"."\n");
        $printer->text("\n");
        $printer->text("DNI-RUC: _______________________"."\n");
        $printer->text("RAZON SOCIAL: __________________"."\n");
        $printer->text("DIRECCION: __________________________"."\n");
        $printer->feed();
        $printer->cut();
        $printer->pulse();
        $printer->close();
        
    } catch(\Exception $e) {
        dd($e);
    }
}


        public function imprimirdetalleventas($fecfin,$fecin){

              $IdEmpresa = Auth::user()->IdEmpresa;
                    
                    
                    $empresa = Empresa::findOrFail($IdEmpresa);
    $empresanegocios = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);
           $total = cpe_cabecera::where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
                        ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                        ->where('cpe_cabecera.ccafem','>=',$fecin)
                        ->where('cpe_cabecera.ccafem','<=',$fecfin)
                        ->where('cpe_cabecera.moncod','=','PEN')
                         ->where('cpe_cabecera.tipo_venta','=','0')
                        ->whereNull('cpe_cabecera.ccabaj')->sum('cpe_detalle.cdevve');

            $productos = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan) as cantidad'),'cdedes','cdepuni','procod')
                        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
                        ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                        ->where('cpe_cabecera.ccafem','>=',$fecin)
                        ->where('cpe_cabecera.ccafem','<=',$fecfin)
                        ->where('cpe_cabecera.moncod','=','PEN')
                         ->where('cpe_cabecera.tipo_venta','=','0')
                        ->whereNull('cpe_cabecera.ccabaj')
                        ->groupby('procod','cdepuni','cdedes')->get();

   
           return view('formatos_comprobantes.productos',compact('empresanegocios','empresa','total','productos','fecin','fecfin'));

        }


         public function imprimirpedidollevar($pedido,$tipo){

          if($tipo=='cobrar'){


         $buscomp = DB::tABLE('cpe_cabecera')->where('ped_id',$pedido)->first();

         $cpe = $buscomp->IdCpe_cabecera;
         $tdocod = $buscomp->tdocod;
         $tipdoc = $buscomp->tdocod;
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
      ->where('IdCpe_cabecera','=',$cpe)->where('IdEmpresa','=',$rucemp)
      ->first();
    
      $mesa = DB::tABLE('mesas')->where('mes_id',$cabecera->mes_id)->first();

    $totalletras= MontoLetras::convertir(number_format($cabecera->ccaitv,'2','.',''),$cabecera->monnom,'Centimos');
      
     $detalle=DB::tABLE('cpe_detalle as det')->join('unidad_medida as umed','det.umecod','=','umed.umecod')->where('IdCpe_cabecera','=',$cpe)->get();

     $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

    }

    $numdoc = str_pad($cabecera->numdoc,8,"0", STR_PAD_LEFT);
    $qrfile =  'QR-'.$cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.png'; 
    $imgqr = "/qr/".$qrfile;

 
        return view('formatos_comprobantes.ticket_factura',compact('cabecera','totalletras','detalle','mesa','empresa','sucursal','imgqr'));


    }else{

              $IdEmpresa = Auth::user()->IdEmpresa;
              $empresa = Empresa::findOrFail($IdEmpresa);
             $cab_pedido = DB::tABLE('pedidos')->leftjoin('aplicativos','aplicativos.apli_id','pedidos.apli_id')
           ->where('ped_id',$pedido)
           ->first();
          

           $empresanegocios = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
          
           $impresoras = DB::tABLE('configuracion_impresoras')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();

     

              $detalle = DB::tABLE('pedidos_detalle')->where('ped_id',$pedido)
                   ->join('productos','pedidos_detalle.IdProducto','productos.IdProducto')
                   ->leftjoin('categorias','categorias.cat_id','productos.cat_id')
                   ->where('categorias.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                   ///->where('pedidos_detalle.impreso','imprimir')
                   ->get();

                foreach($detalle as $det){
                    $buscardetalle = pedidos_detalle::findOrFail($det->ped_det_id);
                       $buscardetalle->impreso ='impreso';
                       $buscardetalle->update();
                }
              


               return view('formatos_comprobantes.pedidollevar',compact('cab_pedido','detalle'));


          }
           

         
        }


              public function imprimircomanda($pedido){

           $IdEmpresa = Auth::user()->IdEmpresa;
           $cab_pedido = DB::tABLE('pedidos')
           ->where('ped_id',$pedido)
           ->first();

          
           $impresoras = DB::tABLE('configuracion_impresoras')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();

              foreach ($impresoras as $impresora) {
                

                $detalle = DB::tABLE('pedidos_detalle')->where('ped_id',$pedido)
               ->join('productos','pedidos_detalle.IdProducto','productos.IdProducto')
               ->leftjoin('categorias','categorias.cat_id','productos.cat_id')
               ->where('categorias.id_empresa_negocio',Auth::user()->id_empresa_negocio)
               ->where('pedidos_detalle.estadoitem','!=','Eliminado')
               ->where('categorias.impresora',$impresora->Id)
               ->get();

                  $detallecount = DB::tABLE('pedidos_detalle')->where('ped_id',$pedido)
               ->join('productos','pedidos_detalle.IdProducto','productos.IdProducto')
               ->leftjoin('categorias','categorias.cat_id','productos.cat_id')
               ->where('categorias.id_empresa_negocio',Auth::user()->id_empresa_negocio)
               ->where('pedidos_detalle.estadoitem','!=','Eliminado')
               ->where('categorias.impresora',$impresora->Id)
               ->count();

              if($detallecount >0){
                  $connector = new WindowsPrintConnector("smb://".$impresora->ruta);
                  $printer = new Printer($connector);
              

                  $printer->setJustification(Printer::JUSTIFY_CENTER);
                  $printer->setFont(Printer::FONT_A);
                  $printer->text($cab_pedido->etiqueta."\n");
                  $printer->text("Pedido Para Llevar ". $cab_pedido->ped_id."\n");
                   if(empty($cab_pedido->etiqueta)){
                        $printer->text("Fecha:". $cab_pedido->fecha_hora."\n");
                    }else{
                        $printer->text("Fecha:". $cab_pedido->fecha_hora_modificacion."\n");
                    }
                  
               //   $printer->text("OUT & PRIDE"."\n");
                  $printer->text("Cliente:". $cab_pedido->cliente."\n");
                  $printer->text("Dirección: ".$cab_pedido->direccion."\n");
                 
                  $printer->setJustification(Printer::JUSTIFY_LEFT);
                  
                  
                  $printer->setJustification(Printer::JUSTIFY_LEFT);
                  $printer->text("CONCEPTO                CANTIDAD       OBS."."\n");
                  $printer->text("________________________________________________"."\n");
                 
                      foreach ($detalle as $det) {
                         $primeralinea = str_pad(substr($det->pronom,0,17),17," ",STR_PAD_RIGHT);
                         $segundalinea = str_pad(substr($det->pronom,18,34),17," ",STR_PAD_RIGHT);
                         $printer->text($primeralinea."          ".$det->cantidad."        ".$det->detalle."\n");
                         $printer->text($segundalinea."\n");

                         $buscardetalle = pedidos_detalle::findOrFail($det->ped_det_id);
                         $buscardetalle->impreso ='impreso';
                         $buscardetalle->update();
                      }

                   $printer->text("\n");
                   

                    $printer->feed();
                    
                    $printer->cut();
                    
                     
                    $printer->pulse();
                   
                    $printer->close();

                  }

                   $buscarpedido = DB::tABLE('pedidos')
                ->where('ped_id',$pedido)
                ->update(['etiqueta' => ""]); 
              }

        }
        
          public function imprimirgasto($gasto){

           $IdEmpresa = Auth::user()->IdEmpresa;
           

          $detalle = gastos_cabecera::select('gast_fec','det_gasto','gast_obs','total')
          ->join('gastos_detalle','gastos_detalle.gast_cab_id','gastos_cabecera.gast_cab_id')
          ->where('gastos_cabecera.gast_cab_id',$gasto)
          ->where('gastos_cabecera.est_gasto','!=','Eliminado')->get();


         
           return view('formatos_comprobantes.gasto',compact('detalle'));

        }
    
    
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
            $fecini = $request->get('fecin');
        $fecfin = $request->get('fecfin');
        
        return view('empresas.cierre.index',compact('fecini','fecfin'));
        }

        public function consolidadoproductos(Request $request)
        {
            $fecini = $request->get('fecin');
        $fecfin = $request->get('fecfin');
        
        return view('empresas.consolidadoproductos.index',compact('fecini','fecfin'));
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
  
      public function imprimircierre($fecfin,$fecin)
        { 
                    
                     $IdEmpresa = Auth::user()->IdEmpresa;
                    

                    $empresa = Empresa::findOrFail($IdEmpresa);

                    $empresanegocios = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);
                   
                    $total = cpe_cabecera::where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
                        ->where('cpe_cabecera.ccafem','>=',$fecin)
                        ->where('cpe_cabecera.ccafem','<=',$fecfin)
                        ->where('cpe_cabecera.moncod','=','PEN')
                         ->where('cpe_cabecera.tipo_venta','=','0')
                        ->whereNull('cpe_cabecera.ccabaj')->sum('cpe_cabecera.ccaitv');

                    $totalvisa = cpe_cabecera::where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
                        ->where('cpe_cabecera.ccafem','>=',$fecin)
                        ->where('cpe_cabecera.ccafem','<=',$fecfin)
                        ->where('cpe_cabecera.moncod','=','PEN')
                         ->where('cpe_cabecera.tipo_venta','=','0')
                        ->whereNull('cpe_cabecera.ccabaj')->sum('cpe_cabecera.visa');

                    $totalmastercard = cpe_cabecera::where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
                        ->where('cpe_cabecera.ccafem','>=',$fecin)
                        ->where('cpe_cabecera.ccafem','<=',$fecfin)
                        ->where('cpe_cabecera.moncod','=','PEN')
                         ->where('cpe_cabecera.tipo_venta','=','0')
                        ->whereNull('cpe_cabecera.ccabaj')->sum('cpe_cabecera.mastercard');

                    $totalefectivo = cpe_cabecera::where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
                        ->where('cpe_cabecera.ccafem','>=',$fecin)
                        ->where('cpe_cabecera.ccafem','<=',$fecfin)
                        ->where('cpe_cabecera.moncod','=','PEN')
                         ->where('cpe_cabecera.tipo_venta','=','0')
                        ->whereNull('cpe_cabecera.ccabaj')->sum('cpe_cabecera.efectivo');
              


                    $totalingresos = gastos_cabecera::select('gast_fec as Fecha Gasto','gast_obs as Observaciones','total_gast as Total','tipo_movimiento as Movimiento')
                    ->where('gastos_cabecera.gast_fec','>=',$fecin)
                    ->where('gastos_cabecera.gast_fec','<=',$fecfin)
                     ->where('gastos_cabecera.tipo_movimiento','=','INGRESO')
                     ->where('gastos_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                    ->where('gastos_cabecera.est_gasto','!=','Eliminado')->sum('total_gast');

                     $totalingresosdetalle = gastos_cabecera::select('gast_fec','gast_obs','total','det_gasto')
                     ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
                    ->where('gastos_cabecera.gast_fec','>=',$fecin)
                    ->where('gastos_cabecera.gast_fec','<=',$fecfin)
                     ->where('gastos_cabecera.tipo_movimiento','=','INGRESO')
                     ->where('gastos_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                    ->where('gastos_cabecera.est_gasto','!=','Eliminado')->get();

                    $totalgastos = gastos_cabecera::select('gast_fec as Fecha Gasto','gast_obs as Observaciones','total_gast as Total')
                    ->where('gastos_cabecera.gast_fec','>=',$fecin)
                    ->where('gastos_cabecera.gast_fec','<=',$fecfin)
                    ->where('gastos_cabecera.tipo_movimiento','=','GASTO')
                    ->where('gastos_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                    ->where('gastos_cabecera.est_gasto','!=','Eliminado')->sum('total_gast');

                    $totalgastosdetalle = gastos_cabecera::select('gast_fec','gast_obs','total','det_gasto')
                     ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
                    ->where('gastos_cabecera.gast_fec','>=',$fecin)
                    ->where('gastos_cabecera.gast_fec','<=',$fecfin)
                     ->where('gastos_cabecera.tipo_movimiento','=','GASTO')
                     ->where('gastos_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                    ->where('gastos_cabecera.est_gasto','!=','Eliminado')->get();
                   

  
              return view('formatos_comprobantes.cierre',compact('empresanegocios','total','totalefectivo','totalvisa','totalmastercard','fecin','fecfin','totalgastos','totalingresos','totalingresosdetalle','totalgastosdetalle','empresa'));
              
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
            public function destroy($id)
            {
                //
            }
}
