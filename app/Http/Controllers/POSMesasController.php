<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\Empresa;
use MasterSoft\pedidos;
use MasterSoft\usuario_pedidos;
use MasterSoft\usuario_facturacion;
use MasterSoft\mesas;
use MasterSoft\pedidos_detalle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use DB;

class POSMesasController extends Controller
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
    public function index()
    {


      $rucemp = trim(Auth::user()->IdEmpresa);
      $mesas ='';

      $primer_piso = DB::tABLE('pisos')
      ->where('suc_id',Auth::user()->id_empresa_negocio)
      ->where('emp_id',$rucemp)->first();

      if(!empty($primer_piso)){

        if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('caja') ){
          
          $mesas = DB::tABLE('mesas')->select('mesas.mes_id','mes_nom','mes_est', DB::raw("(SELECT ped_id FROM pedidos WHERE mesas.mes_id = pedidos.mes_id AND id_empresa_negocio='".Auth::user()->id_empresa_negocio."' and ped_est='Aperturado') as ped_id") )
          ->join('pisos','pisos.pis_id','mesas.pis_id')
          ->where('IdEmpresa',$rucemp)
          ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->where('mesas.pis_id',$primer_piso->pis_id)
          ->orderby('mesas.mes_id','asc')
          ->get(); 

        }else{

           $mesas = DB::tABLE('mesas')->select('mesas.mes_id','mes_nom','mes_est', DB::raw("(SELECT ped_id FROM pedidos WHERE mesas.mes_id = pedidos.mes_id AND id_empresa_negocio='".Auth::user()->id_empresa_negocio."' and ped_est='Aperturado') as ped_id") )
          ->join('pisos','pisos.pis_id','mesas.pis_id')
          ->where('IdEmpresa',$rucemp)
          ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->where('mesas.pis_id',$primer_piso->pis_id)
          ->where('IdUsuario',Auth::user()->IdUsuario)
          ->orderby('mesas.mes_id','asc')
          ->get(); 


        }
        

    
      }
      

       $users = DB::tABLE('users')->get();

      $pisos = DB::tABLE('pisos')
      ->where('emp_id',$rucemp)
      ->where('suc_id',Auth::user()->id_empresa_negocio)
      ->get();
 
      return view('empresas.mesas',compact('mesas','primer_piso','pisos','users'));
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
        $rucemp = trim(Auth::user()->IdEmpresa);
        $empresa = Empresa::findOrFail($rucemp);
        $mesas = $request->get('txtMesaId');
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
        $pedido->mes_id=$mesas;
        $pedido->fecha = $fecha;
        $pedido->total=$total;
        $pedido->subtotal=$subtotal;
        $pedido->est_ped_id ='1';
        $pedido->igv=$igv;
        $pedido->ped_tip = 'SALON';
        $pedido->IdEmpresa=$rucemp;
        $pedido->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $pedido->ped_est='Aperturado';
        $pedido->IdUsuario = Auth::user()->IdUsuario;
           if(!empty($request->get('mozo'))){
            $pedido->mozo = $request->get('mozo');
          }else{
            $pedido->mozo = Auth::user()->IdUsuario;
          }
        $pedido->save();

        $id_ped = $pedido->ped_id;
        $mesa = mesas::findOrFail($mesas);
        $mesa->mes_est='Ocupado';
        $mesa->update();

        if(!empty($unidad)){
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
        }
        

        $usuario_pedidos = new usuario_pedidos;
        $usuario_pedidos->ped_id = $id_ped;
        $usuario_pedidos->id_turno = Auth::user()->id_turno;
        $usuario_pedidos->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $usuario_pedidos->IdEmpresa = Auth::user()->IdEmpresa;
        $usuario_pedidos->referencia = "Registro";
        $usuario_pedidos->save();

        for($i=1;$i<=$empresa->imp_pedido;$i++){
            self::imprimirpedido($id_ped);
        }

        if($request->ajax()) {
            return response()->json(['mensaje' => 'Registrado correctamente','id_ped'=>$id_ped]);
        }
      /*  $mesas = DB::tABLE('mesas')->where('IdEmpresa',$rucemp)->get();
        return view('empresas.mesas',compact('id_ped','mesas'));*/
       // return Redirect::to('/mesas');
    }

     public function adicionarpedidollevar(Request $request)
    {
        $rucemp = trim(Auth::user()->IdEmpresa);
         $empresa = Empresa::findOrFail($rucemp);
        $mesas = $request->get('txtMesaId');
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
        $ped_id = $request->get('txtPedId');
        $detalle = $request->get('detalle');
        $iddetalle = $request->get('iddetalle');

       
        $pedido = pedidos::findOrFail($ped_id);
      
        $pedido->fecha = $fecha;
        $pedido->total=$total;
        $pedido->subtotal=$subtotal;
        $pedido->igv=$igv;
        $pedido->fecha_hora_modificacion = now()->format('Y-m-d H:i:s');
        $pedido->IdEmpresa=$rucemp;
        $pedido->ped_est='Aperturado';
        $pedido->tipo = $request->get('tipo_pedido');
        
        $pedido->IdUsuarioMod = Auth::user()->IdUsuario;
        $pedido->id_empresa_negocio = Auth::user()->id_empresa_negocio;

        if(!empty($request->get('mozo'))){
          $pedido->mozo = $request->get('mozo');
        }else{
          $pedido->mozo = Auth::user()->IdUsuario;
        }

        $pedido->etiqueta = 'ITEM ADICIONAL';
        $pedido->update();

        $id_ped = $pedido->ped_id;

   
      
      if(!empty($unidad)){

        foreach ($unidad as $index => $unid) {
          // code...
          $subitem =$valuni[$index]*$cantidad[$index];
          $igvitem =$totalitem[$index]-$subitem;

          $buscardetalle = pedidos_detalle::WHERE('ped_det_id',$iddetalle[$index])
		  ->where('estadoitem','!=','Eliminado')
		  ->count();
		  



          if($buscardetalle < 1 ){

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
       

        }

      }

        

        $usuario_pedidos = new usuario_pedidos;
        $usuario_pedidos->ped_id = $id_ped;
        $usuario_pedidos->id_turno = Auth::user()->id_turno;
        $usuario_pedidos->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $usuario_pedidos->IdEmpresa = Auth::user()->IdEmpresa;
        $usuario_pedidos->referencia = "Actualizo";
        $usuario_pedidos->save();

        for($i=1;$i<=$empresa->imp_pedido;$i++){
          self::imprimirpedidollevar($id_ped);
        }

        

          if($request->ajax()) {
            return response()->json(['mensaje' => 'Registrado correctamente','pedido'=>$id_ped]);
          }

         // $mesas = DB::tABLE('mesas')->where('IdEmpresa',$rucemp)->get();
       // return Redirect::to('/mesas');
        // return view('empresas.mesas',compact('id_ped','mesas'));
    }

 public function adicionarpedido(Request $request)
    {
        $rucemp = trim(Auth::user()->IdEmpresa);
        $empresa = Empresa::findOrFail($rucemp);
        $mesas = $request->get('txtMesaId');
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
        $ped_id = $request->get('txtPedId');
        $detalle = $request->get('detalle');
        $iddetalle = $request->get('iddetalle');

       
        

        $pedido = pedidos::findOrFail($ped_id);
        $pedido->mes_id=$mesas;
        $pedido->fecha = $fecha;
        $pedido->total=$total;
        $pedido->subtotal=$subtotal;
        $pedido->igv=$igv;
        $pedido->IdEmpresa=$rucemp;
        
        $pedido->ped_est='Aperturado';
        $pedido->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $pedido->IdUsuarioMod = Auth::user()->IdUsuario;
        $pedido->fecha_hora_modificacion = now()->format('Y-m-d H:i:s');
         if(!empty($request->get('mozo'))){
          $pedido->mozo = $request->get('mozo');
        }else{
          $pedido->mozo = Auth::user()->IdUsuario;
        }
         $pedido->etiqueta = 'ITEM ADICIONAL';
        $pedido->update();

        $id_ped = $pedido->ped_id;

        $mesa = mesas::findOrFail($mesas);
        $mesa->mes_est='Ocupado';
        $mesa->update();
  DB::tABLE('pedidos_detalle')->where('ped_id',$ped_id)->delete();

       
     //   if(!empty($unidad)){

           foreach ($unidad as $index => $unid) {
          // code...
         // $subitem =$valuni[$index]*$cantidad[$index];
         // $igvitem =$totalitem[$index]-$subitem;

          $buscardetalle = pedidos_detalle::WHERE('ped_det_id',$iddetalle[$index])
          ->where('estadoitem','!=','Eliminado')
          ->count();

        

         // if($buscardetalle =='0'){

                $ped_det = new pedidos_detalle;
                $ped_det->ped_id=$pedido->ped_id;
                $ped_det->IdProducto=$producto[$index];
                $ped_det->cantidad=$cantidad[$index];
                $ped_det->unidad=$unidad[$index];
                $ped_det->provunitem=$valuni[$index];
                $ped_det->propunitem=$preuni[$index];
              //  $ped_det->igvitem=$igvitem;
               // $ped_det->subtotalitem=$subitem;
                $ped_det->IdEmpresa=$rucemp;
                $ped_det->totalitem=$totalitem[$index];
                $ped_det->detalle = $detalle[$index];
                $ped_det->impreso = 'imprimir';
                $ped_det->save();

        /*  }else{
                
                if($buscardetalle->cantidad != $cantidad[$index]){
                    $ped_det = pedidos_detalle::findOrFail($iddetalle[$index]);
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
                    $ped_det->update();
                }
               
        
          }*/
       

      //  }


        }
        
    
    
 
          
          if($request->ajax()) {
            return response()->json(['mensaje' => 'Registrado correctamente','id_ped'=>$id_ped]);
          }

         // $mesas = DB::tABLE('mesas')->where('IdEmpresa',$rucemp)->get();
       // return Redirect::to('/mesas');
        // return view('empresas.mesas',compact('id_ped','mesas'));
    }
    
   public function mostrar_mesas($id_ped,$tipo=0){
        $rucemp = trim(Auth::user()->IdEmpresa);
      
      $primer_piso = DB::tABLE('pisos')->where('suc_id',Auth::user()->id_empresa_negocio)->first();

      if(!empty($primer_piso)){

        if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('caja') ){
            $mesas = DB::tABLE('mesas')
          ->join('pisos','pisos.pis_id','mesas.pis_id')
          ->where('IdEmpresa',$rucemp)
          ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->where('mesas.pis_id',$primer_piso->pis_id)
          ->orderby('mesas.mes_id','asc')
          ->get(); 
        }else{

          $mesas = DB::tABLE('mesas')
          ->join('pisos','pisos.pis_id','mesas.pis_id')
          ->where('IdEmpresa',$rucemp)
          ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->where('mesas.pis_id',$primer_piso->pis_id)
          ->where('IdUsuario',Auth::user()->IdUsuario)
          ->orderby('mesas.mes_id','asc')
          ->get(); 


        }
        

    
      }

    

       $users = DB::tABLE('users')->get();

      $pisos = DB::tABLE('pisos')
      ->where('suc_id',Auth::user()->id_empresa_negocio)
      ->get();
 
       // return Redirect::to('/mesas');
        return view('empresas.mesas',compact('id_ped','mesas','primer_piso','users','pisos','tipo'));
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
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$id)
    {
      $mesa_id = $request->get('mesa');
      $motivo = $request->get('obser');

      $mesa = mesas::findOrFail($mesa_id);
      $mesa->mes_est ='Libre';
      $mesa->update();
      
      $deleteped = pedidos::findOrFail($id);
      $deleteped->ped_est = 'Eliminado';
      $deleteped->MotElim = $motivo;
      $deleteped->IdUsuarioDel = Auth::user()->IdUsuario;
      $deleteped->update();

      return Redirect::to('/mesas');
    }


      public function imprimirpedido($pedido){

           $IdEmpresa = Auth::user()->IdEmpresa;

           $empresanegocios = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

           $cab_pedido = DB::tABLE('pedidos')
           ->where('ped_id',$pedido)
		   ->leftjoin('users','users.IdUsuario','pedidos.mozo')
           ->first();

           $mesa= DB::tABLE('mesas')->where('mes_id',$cab_pedido->mes_id)->first();

           
           $impresoras = DB::tABLE('configuracion_impresoras')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();



           foreach ($impresoras as $impresora) {
                

                $detalle = DB::tABLE('pedidos_detalle')->where('ped_id',$pedido)
               ->join('productos','pedidos_detalle.IdProducto','productos.IdProducto')
               ->leftjoin('categorias','categorias.cat_id','productos.cat_id')
               ->where('categorias.id_empresa_negocio',Auth::user()->id_empresa_negocio)
               ->where('pedidos_detalle.impreso','imprimir')
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
                     if(empty($cab_pedido->etiqueta)){
                        $printer->text("Fecha:". $cab_pedido->fecha_hora."\n");
                    }else{
                        $printer->text("Fecha:". $cab_pedido->fecha_hora_modificacion."\n"."\n");
                    }
					
					$printer->text("Mozo:". $cab_pedido->name.' '.$cab_pedido->apeusu."\n"."\n");
                   
                    $printer->setJustification(Printer::JUSTIFY_LEFT);
                    
                    
                    $printer->setJustification(Printer::JUSTIFY_LEFT);
                    $printer->text("CONCEPTO    CANTIDAD       OBS."."\n");
                    $printer->text("_________________________________"."\n");
                    foreach ($detalle as $det) {
                     
                       $primeralinea = str_pad(substr($det->pronom,0,17),17," ",STR_PAD_RIGHT);
                       $segundalinea = str_pad(substr($det->pronom,18,34),17," ",STR_PAD_RIGHT);
                       $printer->text($primeralinea."   ".$det->cantidad."  ".$det->detalle."\n");
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

    public function imprimirpedidollevar($pedido){

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
               ->where('pedidos_detalle.impreso','imprimir')
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

   



}
