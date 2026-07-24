<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\XMLSecLibs\Sunat\SignedXML;
use Greenter\Xml\Builder\InvoiceBuilder;
use Greenter\Xml\Builder\VoidedBuilder;
use Greenter\Xml\Builder\NoteBuilder;
use Greenter\Xml\Builder\SummaryBuilder;
use Greenter\Model\Sale\Legend;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\Model\Sale\Note;
use Greenter\Model\Voided\Voided;
use Greenter\Model\Voided\VoidedDetail;
use Greenter\Model\Summary\Summary;
use Greenter\Model\Summary\SummaryDetail;
use Greenter\Model\Summary\SummaryPerception;
use Greenter\Model\Sale\Document;
use Greenter\Ws\Services\SoapClient;
use Greenter\Ws\Services\SummarySender;
use Greenter\Ws\Services\BillSender;
use Greenter\Ws\Services\ExtService;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Ws\Services\ConsultCdrService;
use Greenter\Model\Despatch\Despatch;
use Greenter\Model\Despatch\DespatchDetail;
use Greenter\Model\Despatch\Direction;
use Greenter\Model\Despatch\Shipment;
use Greenter\Model\Despatch\Transportist;
use Greenter\Xml\Builder\DespatchBuilder;
use Greenter\Model\Sale\Charge;
use Greenter\See;
use DOMDocument;
use MasterSoft\resumenes;
use MasterSoft\Mail\FacturacionEmail;
use Illuminate\Support\Facades\Mail;
use MasterSoft\Http\Requests;
use MasterSoft\cuentascobrar;
use MasterSoft\guias_remision;
use MasterSoft\guias_remision_detalle;
use MasterSoft\Marcas;
use MasterSoft\movimientoscaja;
use MasterSoft\Empresa;
use MasterSoft\categorias;
use MasterSoft\movimientosbancarios;
use MasterSoft\cuentascobrardetalle;
use MasterSoft\pedidos;
use MasterSoft\pedidos_detalle;
use MasterSoft\TipoIGV;
use MasterSoft\usuario_pedidos;
use MasterSoft\usuario_facturacion;
use MasterSoft\Cliente;
use MasterSoft\Proveedor;
use MasterSoft\caja;
use MasterSoft\cpe_cabecera;
use MasterSoft\documento_relacionado;
use MasterSoft\unidad_medida;
use MasterSoft\cpe_detalle;
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
use Config;
use ZipArchive;
use Greenter\Xml\Parser\InvoiceParser;
use Greenter\Xml\Parser\PerceptionParser;

class UtilitariosController extends Controller
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


    public function limpiardata(){

      DB::tABLE('empresa')->delete();
      DB::tABLE('almacenes')->delete();
      DB::tABLE('empresa_negocios')->delete();
      DB::tABLE('cpe_cabecera')->delete();
      DB::tABLE('compras_cabecera')->delete();
      DB::tABLE('cuentas_cobrar')->delete();
      DB::tABLE('cuentas_cobrar_detalle')->delete();
       DB::tABLE('cuentas_cobrar_medios')->delete();
      DB::tABLE('medios_pagos')->delete();
      DB::tABLE('credito_dias')->delete();
      DB::tABLE('areas')->delete();
      DB::tABLE('cuentas_pagar')->delete();
      DB::tABLE('cuentas_pagar_detalle')->delete();
      DB::tABLE('gastos_cabecera')->delete();
      DB::tABLE('gastos_detalle')->delete();
      DB::tABLE('productos')->delete();
      DB::tABLE('saldos_arqueo')->delete();
      DB::tABLE('users')->delete();
      DB::tABLE('usuario_eliminar')->delete();
      DB::tABLE('usuario_facturacion')->delete();
      DB::tABLE('usuario_gastos')->delete();
      DB::tABLE('usuario_modificar')->delete();
      DB::tABLE('usuario_pedidos')->delete();
      DB::tABLE('usuario_sucursal')->delete();
      DB::tABLE('venta_medio_pago')->delete();
      DB::tABLE('ventas_cuotas')->delete();
      DB::tABLE('turno_medio_pago')->delete();
      DB::tABLE('resumenes')->delete();
      DB::tABLE('categorias')->delete();
      DB::tABLE('subcategorias')->delete();
     // DB::tABLE('pisos')->delete();
    //  DB::tABLE('mesas')->delete();
       DB::tABLE('movimientos_cabecera')->delete();
        DB::tABLE('movimientos_detalle')->delete();
         DB::tABLE('movimientos_productos')->delete();
          DB::tABLE('inventario_cabecera')->delete();
           DB::tABLE('inventario_detalle')->delete();

      DB::tABLE('modelos')->delete();
      DB::tABLE('marcas')->delete();
      DB::tABLE('laboratorio')->delete();
       DB::tABLE('guias_remision')->delete();
        DB::tABLE('guias_remision_detalle')->delete();
       DB::tABLE('configuracion_impresoras')->delete();
      
        // DB::tABLE('bancos')->delete();
          DB::tABLE('cuentasbancarias')->delete();
        DB::tABLE('movimientosbancarios')->delete();
        DB::tABLE('movimientoscaja')->delete();
      //   DB::tABLE('aplicativos')->delete();
          DB::tABLE('proveedor')->delete();
         DB::tABLE('cliente')->delete();
        

         DB::tABLE('tipo_cuentas')->delete();
          DB::tABLE('turno_medio_pago')->delete();


      return 'listo';
    }


    public function index()
    {
        
       return view('administrador.utilitarios.utilitarios');
    }

    

    public function buscarcomprobantes(Request $request){

        

        $razsoc = $request->get('cliente');
        $respse = $request->get('tiper');
        $tipdoc = $request->get('tipdoc');
        $estado = $request->get('estado');
        $estado_sunat = $request->get('estado_sunat');


        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');

        if(empty($fecin)){

          $fecin = now()->modify('first day of this month')->format('Y-m-d');
          $fecfin = now()->modify('last day of this month')->format('Y-m-d');
          $tipdoc = '0';
          $estado ='2';
          $estado_sunat='2';
        }

        $codigos_sunat = DB::tABLE('estados_sunat')->get();

        $serdoc=$request->get('serdoc');
        $comp=$request->get('comp');
        $numdoc = $request->get('numdoc');
        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
        $doccomprobante = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->orderBy('tdocod','asc')->pluck('tdodes', 'tdocod');

        $documentos = DB::tABLE('tipo_documento')->where('formulario','3')->get();
          
            
            $IdEmpresa = Auth::user()->IdEmpresa;
            $ser = substr($comp,strpos($comp,'-')-4,4);
            $num = substr($comp,strpos($comp,'-')+1,8);

         

            $comprobantes = DB::tABLE('cpe_cabecera as cpe_c')->select('des_doc','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.ccasunrescod','enviado','ccadessun','clicorcli','clicorcli2','clicorcli3','clicorcli4','cliente.clicor','cliente.clicor2','cliente.clicor3','clicor4')
               ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
               ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
               ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
               ->join('cliente','cliente.clicod','cpe_c.clicod')
               ->where('cpe_c.ccafem','>=',$fecin)
               ->where('cpe_c.ccafem','<=',$fecfin)
               ->whereNull('ccabaj')
                 ->where(function ($query) {
                        $query->where('cpe_c.tdocod','01')
                            ->orWhere('cpe_c.tdocod','03');
                        })    
               ->where('cpe_c.id_empresa_negocio',Auth::user()->id_empresa_negocio)
               ->where(function ($query) use ($tipdoc) {
                  if($tipdoc !=0){
                    $query->where('cpe_c.tdocod',$tipdoc);
                  }

                })
                ->where(function ($query1) use ($estado) {
                    if($estado!=2){
                         $query1->where('cpe_c.enviado',$estado);
                    }
                })
                ->where(function ($query2) use ($estado_sunat) {
                    if($estado_sunat==0){
                         $query2->where('cpe_c.ccacodsun',$estado_sunat);
                    }elseif($estado_sunat==1){
                        $query2->where('cpe_c.ccacodsun','!=','0')
                        ->orwhere('cpe_c.ccacodsun','>','0')
                        ->orwhereNull('cpe_c.ccacodsun')
                        ;
                    }
                })
               ->orderby('IdCpe_cabecera','desc')
               ->paginate(10000);
              

        
          
            return view('administrador.utilitarios.buscardocumentos',compact('codigos_sunat','comprobantes','empresa','doccomprobante','fecfin','fecin','documentos','tipdoc'));


    }




    public function generarxmlmasivo(Request $request){

        $items = $request->get('items');


        foreach($items as $item){

            $bus_com = cpe_cabecera::findOrFail($item);

            $gen_xml = new cpe_cabecera;

            if($bus_com->tdocod=='01' || $bus_com->tdocod=='03'){
                $nom_arch= $gen_xml->generar_xml_boleta_factura($item);
            }elseif($bus_com->tdocod=='07' ||  $bus_com->tdocod=='08'){
                $nom_arch= $gen_xml->generar_xml_nota_credito_debito($item);
            }

            $xml_fir = $gen_xml->firmar_xml($nom_arch);


           
        }

        return response()->json(['mensaje' => 'XML GENERADO']); 
          
    }


    public function generarpdfmasivo(Request $request){

        $items = $request->get('items');

        foreach($items as $item){

   
            $gen_pdf = new cpe_cabecera;
            $gen_pdf->generar_nuevo_qr($item);
            $gen_pdf->generarpdfgeneral($item);
           
            
        }

       return response()->json(['mensaje' => 'PDF GENERADO']); 
    }

  
    public function CambiarEstadoSunat(Request $request){
        

        $items = $request->get('items');
        $codigos_sunat = $request->get('codigos_sunat');

        foreach ($items as $key =>$item) {
    
    //dd($codigos_sunat[$key]);            
            $cabecera = cpe_cabecera::findOrFail($item);
            $cabecera->ccacodsun = $codigos_sunat[$key];
            $cabecera->update();

        }  


        return response()->json(['mensaje' => 'ESTADO CAMBIADO']);


    }


    public function  importar_presentaciones(Request $request)
{

      
        \Excel::load(Input::file('archivo'), function($reader) {
            $excel = $reader->get()->toArray();

            // iteracción
           //  $reader->each(function($row)  use($producto,$rucemp) {

            foreach ($excel as $key => $value) {

              
                $productos = DB::tABLE('productos')->where('procod',$value['codigo_referencia'])->where('tipo','1')
                ->first();


               // dd($buscarproducto);

                if(!empty($productos)){

                  try{

                     $objpresentacion = new productos;
                    //$objpresentacion->IdEmpresa= trim(Auth::user()->IdEmpresa);
                   if(empty($value['codigo'])){
                      $objpresentacion->procod = $value['codigo_referencia'];
                   }else{
                     $objpresentacion->procod = $value['codigo'];
                   }
                    
                  if(empty($value['descripcion'])){
                      $objpresentacion->pronom = $productos->pronom;
                   }else{
                     $objpresentacion->pronom = $value['descripcion'];
                   }

                    

                    

                    $objpresentacion->marca =  $productos->marca;
                    $objpresentacion->modelo = $productos->modelo;
                    $objpresentacion->color =  $productos->color;
                    
                    $objpresentacion->umecod = $value['unidad_medida'];
                    $objpresentacion->moncod = $value['moneda'];
                    $objpresentacion->propun = $value['precio_publico'];
                    $objpresentacion->propun1 = $value['precio_mayor'];
                    $objpresentacion->propun2 = $value['precio_especial'];
                    $objpresentacion->icbper =  '0';
                    $objpresentacion->tipo = $value['tipo_producto'];
                    $objpresentacion->costo = $value['costo'];
                    $objpresentacion->costo_total = $value['costo_total'];
                    $objpresentacion->costofijo = $value['costo_total'];
                    $objpresentacion->factor = $value['factor'];
                    $objpresentacion->tigcod = $value['tipo_igv'];

                    $objpresentacion->id_empresa_negocio = Auth::user()->id_empresa_negocio;
                    $objpresentacion->IdEmpresa = Auth::user()->IdEmpresa;
                    $objpresentacion->promocion = $productos->promocion;
                    $objpresentacion->cat_id = $productos->cat_id;
                    $objpresentacion->proest = "Activo";
                    $objpresentacion->pro_rel = $productos->IdProducto;
                     $objpresentacion->color = '#3f4aee';
                    
                    $objpresentacion->save();



                  }catch(Exception $e){
                    
                    dd($e);
                  }
                  
                }else{
                    //echo $value['codigo_referencia'].'<br>';
                }
             

            


               

            }
        
        });

           
        return Redirect::to('/utilitarios');      
    }


 

 public function  importar_productos(Request $request)
{

      
        \Excel::load(Input::file('archivo'), function($reader) {
            $excel = $reader->get()->toArray();

            // iteracción
           //  $reader->each(function($row)  use($producto,$rucemp) {

            foreach ($excel as $key => $value) {

                try{

                    $buscar_producto = DB::tABLE('productos')->where('procod',trim($value['codigo']))->get();

                if(count($buscar_producto)=='0'){

                    if(!empty($value['marca'])){
                        
                        $busmarca = DB::tABLE('marcas')->where('mar_nom',$value['marca'])->first();
                        
                        if(empty($busmarca)){
                            
                            $nueva_marca = new marcas;
                            $nueva_marca->mar_nom = $value['marca'];
                            $nueva_marca->save();
                            
                            $marca = $nueva_marca->mar_id;
                        }else{
                            $marca = $busmarca->mar_id;
                        }
                        
                    }else{
                        $marca = "";
                    }

                      if(!empty($value['categoria'])){
                                
                                $buscategoria = DB::tABLE('categorias')->where('cat_nom',$value['categoria'])->first();
                              
                                if(empty($buscategoria)){
                                    
                                    $nueva_categoria = new categorias;
                                    $nueva_categoria->cat_nom = $value['categoria'];
                                    $nueva_categoria->save();
                                    
                                    $categoria = $nueva_categoria->cat_id;
                                }else{
                                    $categoria = $buscategoria->cat_id;
                                }
                                
                            }else{
                                $categoria = "";
                            }


                    
                    $objpresentacion = new productos;
                    $objpresentacion->procod = $value['codigo'];
                    $objpresentacion->codigo_barra = $value['codigo_barra'];
                    $objpresentacion->pronom = $value['descripcion'];
                    $objpresentacion->unidad_medida_des = $value['unidad_medida_des'];
                    $objpresentacion->marca =  "";
                    $objpresentacion->modelo = "";
                    $objpresentacion->color =  "";
                    $objpresentacion->umecod = $value['unidad_medida'];
                    $objpresentacion->moncod = $value['moneda'];
                    $objpresentacion->propun = $value['precio_publico'];
                    $objpresentacion->propun1 = $value['precio_mayor'];
                    $objpresentacion->propun2 = $value['precio_especial'];
                    $objpresentacion->icbper =  $value['icbper'];
                    $objpresentacion->promocion = '0';
                    $objpresentacion->tipo = $value['tipo_producto'];
                    $objpresentacion->stock_migrar = $value['stock_migrar'];
                    $objpresentacion->costo = $value['costo'];
                    $objpresentacion->costo_total = $value['costo_total'];
                    $objpresentacion->costofijo = $value['costo_total'];
                    $objpresentacion->flete = $value['flete'];
                    $objpresentacion->peso = $value['peso'];
                    $objpresentacion->factor = $value['factor'];
                    $objpresentacion->tigcod = $value['tipo_igv'];
                    $objpresentacion->id_empresa_negocio = Auth::user()->id_empresa_negocio;
                    $objpresentacion->IdEmpresa = Auth::user()->IdEmpresa;
                    $objpresentacion->cat_id = $categoria;
                    $objpresentacion->proest = "Activo";
                    $objpresentacion->save();


                }
                   


                  }catch(Exception $e){
                        
                        dd($e);
                  }
           
                    

            }
        
        });

           
       return Redirect::to('/actualizar');   

    }


    public function actualizar(){

      $empresas = DB::tABLE('empresa_negocios')->get();

      $productos = DB::tABLE('productos')->get();

      foreach ($empresas as $emp) {
        
        foreach ($productos as $pro) {
            

            $buspro = DB::tABLE('producto_empresa')
           // ->where('id_empresa_negocio',$emp->id_empresa_negocio)
            ->where('IdProducto',$pro->IdProducto)
            ->first();

       
            if(empty($buspro)){

               DB::tABLE('producto_empresa')
               ->insert(['IdProducto'=>$pro->IdProducto,
                'id_empresa_negocio'=>$emp->id_empresa_negocio,
                'precio'=>$pro->propun,
                'precio3'=>$pro->propun2,
                'precio2'=>$pro->propun1
              ]); 
            }

           
        }
      }

     return Redirect::to('/actualizarproductostock');

    }
    
public function actualizar_producto_stock(){

      $empresas = DB::tABLE('empresa_negocios')->get();

    

      $productos = DB::tABLE('productos')->where('tipo','1')->get();

      foreach ($empresas as $emp) {
        $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$emp->id_empresa_negocio)->get();


        if(count($almacenes)>'0'){
          foreach ($almacenes as $alm){

              foreach ($productos as $pro) {
                    
                     $buspro = DB::tABLE('producto_stock')
                   // ->where('id_empresa_negocio',$emp->id_empresa_negocio)
                    ->where('IdProducto',$pro->IdProducto)
                    ->first();

                     if(empty($buspro)){

                                DB::tABLE('producto_stock')->insert([
                               'IdProducto'=>$pro->IdProducto,
                               'id_empresa_negocio'=>$emp->id_empresa_negocio,
                               'id_almacen'=>$alm->id_almacen,
                               'stock'=>$pro->stock_migrar,
                        'stock_inicial'=>$pro->stock_migrar
                               
                               ]); 
                     }
               
               
              }
            }
           

        }
       


      }

       return Redirect::to('/utilitarios');

    }

  public function actualizarproductostock(){

      $empresas = DB::tABLE('empresa_negocios')->where('id_empresa_negocio','3')->get();

      $productos = DB::tABLE('productos')->get();

      foreach ($empresas as $emp) {
        

        foreach ($productos as $pro) {
            

            $buspro = DB::tABLE('producto_empresa')
            ->where('id_empresa_negocio',$emp->id_empresa_negocio)
            ->where('IdProducto',$pro->IdProducto)
            ->first();

       

            if(empty($bus_pro)){

               DB::tABLE('producto_empresa')->insert(['IdProducto'=>$pro->IdProducto,'id_empresa_negocio'=>$emp->id_empresa_negocio]); 
            }

           
        }
      }

    }


public function  importar_clientes(Request $request)
{

      
        \Excel::load(Input::file('archivo'), function($reader) {
            $excel = $reader->get()->toArray();

            // iteracción
           //  $reader->each(function($row)  use($producto,$rucemp) {

            foreach ($excel as $key => $value) {

              
                  try{

                    if(empty($value['tipo_documento'])){

                        $cont_carac = strlen($value['numero_documento']);

                        $obt_dig = substr($value['numero_documento'], 0, 1);

                        if($cont_carac =='11' && ($obt_dig=='10' || $obt_dig=='20' || $obt_dig=='15' || $obt_dig=='17')){
                            $tdicod ='6';

                        }else{

                            $tdicod='1';

                        }

                    }else{
                        $tdicod = $value['tipo_documento'];
                    }
                    
                 

                    Cliente::UpdateOrCreate(
                        ['clinum'=>trim($value['numero_documento'])],
                        [
                            'clinom'=>$value['razon_social'],
                            'clidir'=>$value['direccion'],
                            'clicor'=>$value['correo1'],
                            'clicor2'=>$value['correo2'],
                            'clicor3'=>$value['correo3'],
                            'clicor4'=>$value['correo4'],
                            'rucemp'=>Auth::user()->IdEmpresa,
                            'tdicod'=>$tdicod,
                            'telefono'=>$value['telefono'],
                           
                        ]);



                  }catch(Exception $e){
                        
                       dd($e);
                  }
           
                    

            }
        
        });

           
       return Redirect::to('/utilitarios');      
    }


    public function  importar_proveedores(Request $request)
{

      
        \Excel::load(Input::file('archivo'), function($reader) {
            $excel = $reader->get()->toArray();

            // iteracción
           //  $reader->each(function($row)  use($producto,$rucemp) {

            foreach ($excel as $key => $value) {

              
                  try{

                    if(empty($value['tipo_documento'])){

                        $cont_carac = strlen($value['numero_documento']);

                        $obt_dig = substr($value['numero_documento'], 0, 1);

                        if($cont_carac =='11' && ($obt_dig=='10' || $obt_dig=='20' || $obt_dig=='15' || $obt_dig=='17')){
                            $tdicod ='6';

                        }else{

                            $tdicod='1';

                        }

                    }else{
                        $tdicod = $value['tipo_documento'];
                    }
                    
                 

                   proveedor::UpdateOrCreate(
                        ['prov_ruc'=>$value['numero_documento']],
                        [
                            'prov_raz'=>$value['razon_social'],
                            'prov_dir'=>$value['direccion'],
                            'prov_cor'=>$value['correo'],
                            'IdEmpresa'=>Auth::user()->IdEmpresa,
                            'tdicod'=>$tdicod,
                            'prov_num_con'=>$value['telefono'],
                           
                        ]);



                  }catch(Exception $e){
                        dd($e);
                  }
           
                    

            }
        
        });

           
       return Redirect::to('/utilitarios');      
    }



    public function descargar_formatos($tipo)
    {

        
        $ruta = public_path();

        if($tipo=='1'){

           $file = $ruta.'/formatos_importar/formato_productos.xlsx';

        }elseif($tipo=='2'){

              $file = $ruta.'/formatos_importar/formato_presentaciones.xlsx';

        }elseif($tipo=='3'){

              $file = $ruta.'/formatos_importar/formato_clientes.xlsx';

        }elseif($tipo=='4'){
              $file = $ruta.'/formatos_importar/formato_proveedores.xlsx';
        }


   

      if(file_exists($file))
      {

        
        $headers = array(
              'Content-Type: application/pdf',
            );

        return response()->download($file);

      }
    
     
         return Redirect::to('/utilitarios');
      
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
        //
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
    public function destroy($id)
    {
        //
    }

    public function actualizar_cabecera(){

        $cabecera = DB::tABLE('usuario_facturacion')->get();

        foreach ($cabecera as $cab){
           
           cpe_cabecera::where('IdCpe_cabecera',$cab->IdCpe_cabecera)->update(['id_turno'=>$cab->id_turno]);

        }

        return 'actualizado';
    }

   public function generar_codigo_movimiento(){

        $cabeceras = DB::tABLE('cpe_cabecera')->get();

        foreach ($cabeceras as $cabecera) {
           $bus_cpe = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$cabecera->IdCpe_cabecera)->First();

          $bus_suc = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$bus_cpe->id_empresa_negocio)->first();

          $gen_cod = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$cabecera->IdCpe_cabecera)
          ->update(['cod_mov'=>'MOV'.$bus_suc->cod_suc.$cabecera->IdCpe_cabecera]);
        }
       

        return 'codigos generados';
    }

    public function importar_ventas_excel(Request $request){


        return view('administrador.utilitarios.importar_ventas_excel');
    }

    public function registrar_ventas_excel(Request $request){

        $bus_almacen = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
        
          $ids = [];

         \Excel::load(Input::file('archivo'), function($reader) use($sucursal, $bus_almacen,$ids){
            $excel = $reader->get()->toArray();

            foreach ($excel as $key => $value) {


             $numero_recibo = $value['numero_recibo_operacion'];
             $fecha = $value['fecha'];
             $numero_ruc_dni = $value['numero_ruc_o_dni'];
             $razon_social = $value['razon_social'];
             $direccion = $value['direccion'];
             $bus_cabecera = DB::tABLE('cpe_cabecera')->where('numero_operacion',$numero_recibo)->get();

           

             if(count($bus_cabecera)=='0'){


                    if($value['tipo_documento_identidad']=='DNI'){
                        $tdocod = '03';
                        $tdicod = '1';
                    }elseif($value['tipo_documento_identidad']=='RUC'){
                        $tdocod = '01';
                        $tdicod = '6';
                    }

                      $empresanegocio = EmpresaNegocios::findOrFail($sucursal->id_empresa_negocio);

                      if($tdocod == '01'){
                          $senudoc = DB::tABLE('empresa_negocios')->select('FseEmpresa','FnuEmpresa')->where('id_empresa_negocio',$sucursal->id_empresa_negocio)->first();
                          $numcomp =  $senudoc->FnuEmpresa+1;
                          $sercomp =  $senudoc->FseEmpresa;
                      }elseif ($tdocod =='03') {
                          $senudoc = DB::tABLE('empresa_negocios')->select('BseEmpresa','BnuEmpresa')->where('id_empresa_negocio',$sucursal->id_empresa_negocio)->first();
                          $numcomp =  $senudoc->BnuEmpresa+1;
                          $sercomp =  $senudoc->BseEmpresa;
                      }


                    if($tdocod == '01'){
                            $empresanegocio->FseEmpresa = $sercomp;
                            $empresanegocio->FnuEmpresa = $numcomp;;
                        
                      }elseif ($tdocod =='03') {
                            $empresanegocio->BnuEmpresa = $numcomp;
                            $empresanegocio->BseEmpresa = $sercomp;
                      }

                  

                     

                      $empresanegocio->update();

                    $cliente = Cliente::UpdateOrCreate(['clinum'=>$numero_ruc_dni],['clinom'=>$razon_social,'clidir'=>$direccion,'tdicod'=>$tdicod,'rucemp'=>Auth::user()->IdEmpresa]); 


                    $cabecera = new cpe_cabecera;

                    if($value['tipo_documento_identidad']=='DNI'){
                        $cabecera->tdocod = $tdocod;
                        $cabecera->tdicod = $tdicod;
                    }elseif($value['tipo_documento_identidad']=='RUC'){
                        $cabecera->tdocod = $tdocod;
                        $cabecera->tdicod = $tdicod;
                    }
                   
                    
                    $cabecera->topcod = '0101';
                    $cabecera->ccafem = $value['fecha'];
                    $cabecera->id_almacen = $bus_almacen->id_almacen;
                    $cabecera->ccafve =$value['fecha'];
                    $cabecera->numero_operacion = $numero_recibo;
  
                    
                    $cabecera->ccandi = $numero_ruc_dni;
                    $cabecera->ccanom = $razon_social;
                    $cabecera->direccion = $direccion;
                    $cabecera->clicod = $cliente->clicod;
                  
                      if($value['moneda']=='SOLES'){
                        $cabecera->moncod =='PEN';
                      }elseif ($value['moneda']=='DOLARES') {
                        $cabecera->moncod =='USD';
                      }

                      $cabecera->id_empresa_negocio = $sucursal->id_empresa_negocio;
                      $cabecera->IdUsuario = Auth::user()->IdUsuario;
                      $cabecera->IdEmpresa = Auth::user()->IdEmpresa;
                      $cabecera->serdoc= $sercomp;
                      $cabecera->numdoc = $numcomp;
                      $cabecera->save();

                      $ids[]=array(
                         'id'=>$cabecera->IdCpe_cabecera,
                         'numero_recibo'=>$cabecera->numero_operacion
                      );

             }

           
             $cantidad = $value['cantidad'];
             $codigo_servicio_producto = $value['codigo_servicio_producto'];
             $descripcion_servicio_producto = $value['descripcion_servicio_producto'];
             $precio_unitario = $value['precio_unitario'];
             $total = $value['total'];


            $detalle = new cpe_detalle;
            //$detalle->IdCpe_cabecera =  $bus_id->IdCpe_cabecera;
            $detalle->cdecan = $cantidad;
            $detalle->numero_recibo = $numero_recibo;
            $detalle->cdepuni = $precio_unitario;
            $detalle->cdevun = $precio_unitario;
            $detalle->cdevve  = $total;
            $detalle->cdepve  = $total;
            $detalle->cdeigv = '0.00';
            $detalle->costo = '0.00';
            $detalle->tigcod = '30';
            $detalle->umecod = 'ZZ';
            $detalle->procod = $codigo_servicio_producto;
            $detalle->cdedes = $descripcion_servicio_producto;
            $detalle->icbper = '0';
            $detalle->id_almacen_pro = $bus_almacen->id_almacen;
            $detalle->save();
           
    
 
            }
            
           // dd($ids);
        foreach($ids as $i){

            cpe_detalle::where('numero_recibo',$i['numero_recibo'])->update(['IdCpe_cabecera'=>$i['id']]);

            $bus_detalle = DB::tABLE('cpe_detalle')->where('IdCpe_cabecera',$i['id'])->get();

            foreach($bus_detalle as $bd){

                $act_cpe = cpe_cabecera::findOrFail($i['id']);
                $act_cpe->ccaitv = $act_cpe->ccaitv+$bd->cdevve;
                $act_cpe->ccaigv ='0';
                $act_cpe->ccatinaf = $act_cpe->ccatinaf + $bd->cdevve;
                $act_cpe->update();
  

            }
        }


        });


        return view('administrador.utilitarios.importar_ventas_excel');
    }

    public function backup_bd(Request $request){

     \exec(' C:\laragon\bin\php\php-7.2.34-Win32-VC15-x64\php.exe" "artisan" db:dump > "NUL" 2>&1');
        return 'listo';
    }

    public function migrar_xml()
    {

       $arrFiles = array();
        $objDir = dir(public_path()."/migrar_xml");
 
        while (false !== ($entry = $objDir->read())) {
           $arrFiles[] = $entry;

           if($entry=='.'){

           }elseif($entry=='..'){

           }else{
                $parser = new InvoiceParser();
                $xml = file_get_contents(public_path().'/migrar_xml/'.$entry);
                $factura = $parser->parse($xml);

           //   dd($factura);

                if($factura->getTipoDoc()=='01' || $factura->getTipoDoc()=='03'  ){

                $cabecera = new cpe_cabecera;

                $cabecera->tdocod = $factura->getTipoDoc();
                $cabecera->topcod = '0101';
                $cabecera->ccafem = $factura->getFechaEmision();
                $cabecera->cod_tip_ope ='01';
                $cabecera->ccafve =  $factura->getFechaEmision();
                $cabecera->totalcontado = '0';
                
                if($factura->getTipoDoc()=='01'){
                     $cabecera->tdicod = '6';
                }
                if($factura->getTipoDoc()=='03'){
                     $cabecera->tdicod = '1';    
                }
               
                $cabecera->ccandi = $factura->getClient()->getNumDoc();
                $cabecera->ccanom = $factura->getClient()->getRznSocial();
                $cabecera->moncod = "PEN";
                $cabecera->direccion = $factura->getClient()->getAddress()->getDireccion();
                $cabecera->ccatexo =  $factura->getMtoImpVenta();
                $cabecera->ccaigv = '0.00';
                $cabecera->ccatinaf =  '0.00';
                $cabecera->ccaitv = $factura->getMtoImpVenta();
                $cabecera->id_empresa_negocio = Auth::user()->id_empresa_negocio;
                $cabecera->clicod = '';
                $cabecera->ccaobs = '';
                $cabecera->paga = '0';
                $cabecera->tipcambio = '0';
                $cabecera->vuelto = '0';
                $cabecera->estadopago = 'CONTADO';
                $cabecera->IdUsuario = Auth::user()->IdUsuario;
                $cabecera->IdEmpresa = Auth::user()->IdEmpresa;
                $cabecera->serdoc= $factura->getSerie();
                $cabecera->numdoc = $factura->getCorrelativo();
                $cabecera->save();

                foreach($factura->getDetails() as $det){
                    $detalle = new cpe_detalle;
                    $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
                    $detalle->umecod = $det->getUnidad();
                    $detalle->cdecan = $det->getCantidad();
                    $detalle->cdedes = $det->getDescripcion();
                    $detalle->tigcod = '20';
                    $detalle->valor_unitario = $det->getMtoPrecioUnitario();
                    $detalle->cdepuni = $det->getMtoPrecioUnitario();
                    $detalle->cdevun = $det->getMtoPrecioUnitario();
                    $detalle->cdevve = $det->getMtoValorVenta();
                    $detalle->cdepve =  $det->getMtoValorVenta();
                    $detalle->cdeigv = '0.00';
                    $detalle->fecha_venta = $factura->getFechaEmision();
                    $detalle->save();
                }

            }


           }
         

            

        }
    
      
    }


    public function actualizar_proveedores(Request $request){

        $compras = DB::tABLE('compras_cabecera')->get();

        foreach($compras as $comp){

            $bus_prov = DB::tABLE('proveedor')->where('prov_id',$comp->prov_id)->first();

            DB::tABLE('movimientos_productos')->where('com_cab_id',$comp->com_cab_id)->update(['cliente'=>$bus_prov->prov_raz]);

        }

        return '-';
    }

}
