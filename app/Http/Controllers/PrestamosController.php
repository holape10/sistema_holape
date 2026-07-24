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
use MasterSoft\tipos_vehiculos;
use MasterSoft\cuentascobrar;
use MasterSoft\guias_remision;
use MasterSoft\guias_remision_detalle;
use MasterSoft\movimientoscaja;
use MasterSoft\Empresa;
use MasterSoft\movimientosbancarios;
use MasterSoft\cuentascobrardetalle;
use MasterSoft\pedidos;
use MasterSoft\pedidos_detalle;
use MasterSoft\TipoIGV;
use MasterSoft\usuario_pedidos;
use MasterSoft\usuario_facturacion;
use MasterSoft\Cliente;
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


class PrestamosController extends Controller
{


    public function __construct()
    {
        $this->middleware('auth')->except(['ingresomozo', 'mesas']);
    }

    
  public function prestamos($codfact){

  	 $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

        $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();

        $combustible = DB::tABLE('combustible')->get();

        $marcas = DB::tABLE('marcas')->get();

        $modelos = DB::tABLE('modelos')->get();

        $tecnicos = DB::tABLE('tecnicos')->get();

        $creditos = DB::tABLE('credito_dias')->get();

        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);

        $documentos = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderby('tdicod')->get();

        $tipos_igv = DB::tABLE('tipo_igv')->get();

        $rucemp = trim(Auth::user()->IdEmpresa);

        $senudoc = DB::tABLE('empresa_negocios')
                    ->select('serieguia','numeroguia')
                    ->where('IdEmpresa','=',$rucemp)
                    ->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)
                    ->first(); 

        $motivos = DB::tABLE('motivo_traslado')
        ->orderBy('motivo','asc')->get();

        $modalidades = DB::tABLE('modalidad_traslado')
        ->orderBy('modalidad','asc')->get();

        $mediospagos = DB::tABLE('medios_pagos')->get();

         // consultar tipos de documentos de identidad
        $docidentidad = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

        //consultar tipo de documento 
        $doccomprobante = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->orderBy('tdocod','asc')->get();

         // consultar tipos de monedas
        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

        $categorias = DB::tABLE('categorias')
      //  ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->orderby('cat_nom','asc')
        ->get();

        $clientes = DB::tABLE('cliente')->get();

        $comprobante = DB::tABLE('tipo_documento')->get();

        $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();

  

        $unidades = DB::tABLE('unidad_medida')
        ->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vendedores = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        //->where('role_id','5')
        ->get();

        $mediospagos = DB::tABLE('medios_pagos')->get();

          $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

        $almacen = DB::tABLE('almacenes')
        ->where('predeterminado','1')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();

        $gastos = DB::tABLE('tipo_gastos')->get();

    
        return view('empresas.prestamos.prestamos',compact('codfact','categorias','comprobante','tipodocumento','unidades','unidades','vendedores','creditos','mediospagos','clientes','documentos','datos','monedas','senudoc','motivos','modalidades','docidentidad','doccomprobante','monedas','almacenes','gastos','combustible','marcas','modelos','tecnicos','tipos_igv','empresa'));

  }


 public function detalleventa($venta){

    $cabecera = DB::tABLE('cpe_cabecera')
    ->join('tipo_documento','tipo_documento.tdocod','cpe_cabecera.tdocod')
    ->where('IdCpe_cabecera',$venta)->first();

    $detalle = DB::tABLE('cpe_cabecera')
    ->join('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
    ->where('cpe_cabecera.IdCpe_cabecera',$venta)
    ->get();

    return view('empresas.comprobantes.detalleventa',compact('cabecera','detalle'));

 }


    public function pedidos(){

      
        $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first(); 

        $creditos = DB::tABLE('credito_dias')->get();

        $documentos = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderby('tdicod')->get();

        $rucemp = trim(Auth::user()->IdEmpresa);

            $senudoc = DB::tABLE('empresa_negocios')
                    ->select('serieguia','numeroguia')
                    ->where('IdEmpresa','=',$rucemp)
                    ->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)
                    ->first(); 

          $motivos = DB::tABLE('motivo_traslado')
        ->orderBy('motivo','asc')->get();

        $modalidades = DB::tABLE('modalidad_traslado')
        ->orderBy('modalidad','asc')->get();

        $mediospagos = DB::tABLE('medios_pagos')->get();

         // consultar tipos de documentos de identidad
        $docidentidad = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

        //consultar tipo de documento 
        $doccomprobante = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->orderBy('tdocod','asc')->get();

         // consultar tipos de monedas
        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

        $categorias = DB::tABLE('categorias')
      //  ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->orderby('cat_nom','asc')
        ->get();

        $clientes = DB::tABLE('cliente')->get();

        $comprobante = DB::tABLE('tipo_documento')->get();

        $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();

        $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();

        $vendedores = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
      //  ->where('role_id','5')
        ->get();

        $mediospagos = DB::tABLE('medios_pagos')->get();

          $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

        $almacen = DB::tABLE('almacenes')
        ->where('predeterminado','1')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();

        $unidades = DB::tABLE('unidad_medida')->get();

        $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();
     
      /*     $productos = DB::tABLE('productos')
        ->select('procod','marca','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto  AND id_almacen='".$almacen->id_almacen."' AND id_empresa_negocio='".Auth::user()->id_empresa_negocio."') as stock"),'precio')
        ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
        ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
        ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('id_almacen',$almacen->id_almacen)
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();*/

        return view('empresas.puntosventas.pedidos',compact('categorias','comprobante','tipodocumento','igv','unidades','tdocod','cpe','vendedores','creditos','mediospagos','clientes','documentos','datos','monedas','senudoc','motivos','modalidades','docidentidad','doccomprobante','monedas','productos','almacenes','unidades'));
    }

    

    public function albergues(){

       $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first(); 

        $creditos = DB::tABLE('credito_dias')->get();

        $documentos = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderby('tdicod')->get();

        $rucemp = trim(Auth::user()->IdEmpresa);

            $senudoc = DB::tABLE('empresa_negocios')
                    ->select('serieguia','numeroguia')
                    ->where('IdEmpresa','=',$rucemp)
                    ->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)
                    ->first(); 

          $programas = DB::tABLE('programas')->get();

          $motivos = DB::tABLE('motivo_traslado')
        ->orderBy('motivo','asc')->get();

        $modalidades = DB::tABLE('modalidad_traslado')
        ->orderBy('modalidad','asc')->get();

        $mediospagos = DB::tABLE('medios_pagos')->get();

         // consultar tipos de documentos de identidad
        $docidentidad = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

        //consultar tipo de documento 
        $doccomprobante = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->orderBy('tdocod','asc')->get();

         // consultar tipos de monedas
        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

        $categorias = DB::tABLE('categorias')
      //  ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->orderby('cat_nom','asc')
        ->get();

        $clientes = DB::tABLE('cliente')->get();

        $comprobante = DB::tABLE('tipo_documento')->get();

        $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();

        $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();

        $unidades = DB::tABLE('unidad_medida')
        ->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $mozos = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        ->where('role_id','5')
        ->get();

        $mediospagos = DB::tABLE('medios_pagos')->get();

          $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

        $almacen = DB::tABLE('almacenes')
        ->where('predeterminado','1')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();


      $productos = DB::tABLE('productos')
        ->select('procod','marca','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND tipo='1' AND id_almacen='".$almacen->id_almacen."' AND id_empresa_negocio='".Auth::user()->id_empresa_negocio."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE productos.IdProducto = pro.pro_rel AND tipo='2') as cont_pre"))
        ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
        ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
      
        ->where('tipo','1')
        ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('id_almacen',$almacen->id_almacen)
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();


        $servicios = DB::tABLE('servicios')->get();

        return view('empresas.puntosventas.albergues',compact('categorias','comprobante','tipodocumento','igv','unidades','unidades','tdocod','cpe','mozos','creditos','mediospagos','clientes','documentos','datos','monedas','senudoc','motivos','modalidades','docidentidad','doccomprobante','monedas','productos','servicios','programas'));

    }


       public function calcularpedidoalbergue(Request $request,$codigo,$cantidad){

        $programas = DB::tABLE('programas')->where('prog_cod',$codigo)->get();

    
        $productos = DB::tABLE('programas_preparados')->select('productos.IdProducto','productos.pronom',DB::raw('sum(rec_cant) as cantidad_suma'),'productos.umecod','umenom')
        ->join('programas','programas.prog_id','programas_preparados.prog_id')
        ->join('recetas','recetas.prod_id','programas_preparados.IdProducto')
        ->join('productos','productos.IdProducto','recetas.prod_insu')
         ->join('unidad_medida','unidad_medida.umecod','productos.umecod')
        ->where('prog_cod',$codigo)
         ->groupby('recetas.prod_insu')
        ->get();

       

        $vista = view('empresas.puntosventas.divcalcularpedidoalbergue',compact('cantidad','productos'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }

       

      }

    public function pedidoalbergue(){


        $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first(); 


       
            $clientes = DB::tABLE('cliente')->get();

        $almacen = DB::tABLE('almacenes')
        ->where('predeterminado','1')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();



        $negocios = DB::tABLE('empresa_negocios')->get();

        $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();



        $documentos = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderby('tdicod')->get();

        $rucemp = trim(Auth::user()->IdEmpresa);

   

        $clientes = DB::tABLE('cliente')->get();

        $comprobante = DB::tABLE('tipo_documento')->get();

        $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();



        $creditos = DB::tABLE('credito_dias')->get();

        $documentos = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderby('tdicod')->get();

        $rucemp = trim(Auth::user()->IdEmpresa);

            $senudoc = DB::tABLE('empresa_negocios')
                    ->select('serieguia','numeroguia')
                    ->where('IdEmpresa','=',$rucemp)
                    ->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)
                    ->first(); 

          $programas = DB::tABLE('programas')->groupby('prog_cod')->get();

          $motivos = DB::tABLE('motivo_traslado')
        ->orderBy('motivo','asc')->get();

        $modalidades = DB::tABLE('modalidad_traslado')
        ->orderBy('modalidad','asc')->get();

        $mediospagos = DB::tABLE('medios_pagos')->get();

         // consultar tipos de documentos de identidad
        $docidentidad = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

        //consultar tipo de documento 
        $doccomprobante = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->orderBy('tdocod','asc')->get();

         // consultar tipos de monedas
        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

        $categorias = DB::tABLE('categorias')
      //  ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->orderby('cat_nom','asc')
        ->get();

        $clientes = DB::tABLE('cliente')->get();

        $comprobante = DB::tABLE('tipo_documento')->get();

        $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();

        $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();

        $unidades = DB::tABLE('unidad_medida')
        ->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $mozos = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        ->where('role_id','5')
        ->get();

        $mediospagos = DB::tABLE('medios_pagos')->get();

          $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

        $almacen = DB::tABLE('almacenes')
        ->where('predeterminado','1')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();


      $productos = DB::tABLE('productos')
        ->select('procod','marca','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND tipo='1' AND id_almacen='".$almacen->id_almacen."' AND id_empresa_negocio='".Auth::user()->id_empresa_negocio."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE productos.IdProducto = pro.pro_rel AND tipo='2') as cont_pre"))
        ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
        ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
      
        ->where('tipo','1')
        ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('id_almacen',$almacen->id_almacen)
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();


        $servicios = DB::tABLE('servicios')->get();

        return view('empresas.puntosventas.pedidoalbergue',compact('categorias','comprobante','tipodocumento','igv','unidades','unidades','tdocod','cpe','mozos','creditos','mediospagos','clientes','documentos','datos','monedas','senudoc','motivos','modalidades','docidentidad','doccomprobante','monedas','productos','servicios','programas','negocios','almacen','almacenes'));

    }


      public function modificarpedidosalbergue($id){


            $clientes = DB::tABLE('cliente')->get();




        $negocios = DB::tABLE('empresa_negocios')->get();




        $documentos = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderby('tdicod')->get();

        $rucemp = trim(Auth::user()->IdEmpresa);

   

        $clientes = DB::tABLE('cliente')->get();

        $comprobante = DB::tABLE('tipo_documento')->get();

        $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();



        $creditos = DB::tABLE('credito_dias')->get();

        $documentos = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderby('tdicod')->get();

        $rucemp = trim(Auth::user()->IdEmpresa);

            $senudoc = DB::tABLE('empresa_negocios')
                    ->select('serieguia','numeroguia')
                    ->where('IdEmpresa','=',$rucemp)
                    ->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)
                    ->first(); 

          $programas = DB::tABLE('programas')->groupby('prog_cod')->get();

          $motivos = DB::tABLE('motivo_traslado')
        ->orderBy('motivo','asc')->get();

        $modalidades = DB::tABLE('modalidad_traslado')
        ->orderBy('modalidad','asc')->get();

        $mediospagos = DB::tABLE('medios_pagos')->get();

         // consultar tipos de documentos de identidad
        $docidentidad = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

        //consultar tipo de documento 
        $doccomprobante = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->orderBy('tdocod','asc')->get();

         // consultar tipos de monedas
        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

        $categorias = DB::tABLE('categorias')
      //  ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->orderby('cat_nom','asc')
        ->get();

        $clientes = DB::tABLE('cliente')->get();

        $comprobante = DB::tABLE('tipo_documento')->get();

        $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();

        $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();

        $unidades = DB::tABLE('unidad_medida')
        ->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $mozos = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        ->where('role_id','5')
        ->get();

        $mediospagos = DB::tABLE('medios_pagos')->get();


        $servicios = DB::tABLE('servicios')->get();

          $listar = DB::tABLE('pedido_servicio_insu')
        ->join('productos','productos.IdProducto','pedido_servicio_insu.prod_ins')
        ->where('ped_ser_id',$id)
        ->get();     

$cabecera = DB::tABLE('pedido_servicio')->where('ped_ser_id',$id)->first();

    $datosuc = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$cabecera->id_empresa_negocio)->first();

     $datoalm = DB::tABLE('almacenes')->where('id_almacen',$cabecera->id_almacen)->first();


        $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$cabecera->id_empresa_negocio)->get();

        return view('empresas.puntosventas.modificarpedidosalbergue',compact('categorias','comprobante','tipodocumento','igv','unidades','unidades','tdocod','cpe','mozos','creditos','mediospagos','clientes','documentos','datos','monedas','senudoc','motivos','modalidades','docidentidad','doccomprobante','monedas','productos','servicios','programas','negocios','almacen','almacenes','datosuc','datoalm','cabecera','listar'));

    }

    public function registrarpedidoalbergue(Request $request){

        $cantidad = $request->get('cant');
        $producto = $request->get('IdProducto');

         $sucursal= $request->get('part_suc');

        
             $almacen = $request->get('almacen');

         

   //     $almacen = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();
     
         DB::tABLE('pedido_servicio')->insert([
              'id_empresa_negocio'=>$sucursal,
              'id_almacen'=>$almacen,
              'prog_cod'=>$request->get('prog_id'),
              'fec_ped'=>$request->get('fecEmi'),
              'ped_ser_can'=>$request->get('cantidad'),

            ]);

          $mov_cab_id = DB::getPdo()->lastInsertId();

        foreach ($producto as $key => $pro) {
            
            DB::tABLE('pedido_servicio_insu')->insert([
              'prod_ins'=>$pro,
              'cant_ins'=>$cantidad[$key],
              'id_empresa_negocio'=>$sucursal,
              'id_almacen'=>$almacen,
              'prog_cod'=>$request->get('prog_id'),
              'fec_ped'=>$request->get('fecEmi'),
              'ped_ser_id'=>$mov_cab_id,

            ]);

        }

         if($request->ajax()){
              return response()->json(['mensaje'=>'REGISTRADO']);
            }

    }

        public function actualizarpedidoalbergue(Request $request){

        $cantidad = $request->get('cant');
        $producto = $request->get('IdProducto');
        $ped_ser_id = $request->get('ped_ser_id');

         $sucursal= $request->get('part_suc');
          $almacen = $request->get('almacen');


     
         DB::tABLE('pedido_servicio')->where('ped_ser_id',$ped_ser_id)->update([
              'id_empresa_negocio'=>$sucursal,
              'id_almacen'=>$almacen,
              'prog_cod'=>$request->get('prog_id'),
              'fec_ped'=>$request->get('fecEmi'),
              'ped_ser_can'=>$request->get('cantidad'),

            ]);

         DB::tABLE('pedido_servicio_insu')->where('ped_ser_id',$ped_ser_id)->delete();


        foreach ($producto as $key => $pro) {
            
            DB::tABLE('pedido_servicio_insu')->insert([
              'prod_ins'=>$pro,
              'cant_ins'=>$cantidad[$key],
              'id_empresa_negocio'=>$sucursal,
              'id_almacen'=>$almacen,
              'prog_cod'=>$request->get('prog_id'),
              'fec_ped'=>$request->get('fecEmi'),
              'ped_ser_id'=>$ped_ser_id,

            ]);

        }

         if($request->ajax()){
              return response()->json(['mensaje'=>'ACTUALIZADO']);
            }

    }


     public function listarpedidosalbergues(Request $request){
      
    //  $IdProducto = $request->get('IdProducto');
    //  $cantidad = $request->get('cantidad');
       $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first(); 

      $fecha = $request->get('fecEmi');
      $servicio = $request->get('servicio');
      $prog_id = $request->get('prog_id');

       $programas = DB::tABLE('programas')->get();


        $servicios = DB::tABLE('servicios')->get();

      //dd($IdProducto);

     
      $productos = DB::tABLE('pedido_servicio_cab')
      ->join('productos','productos.IdProducto','pedido_servicio_cab.IdProducto')
      ->where('pedido_servicio_cab.ser_cod',$servicio)
      ->wherE('pedido_servicio_cab.prog_id',$prog_id)
      ->get();
    

     return view('empresas.puntosventas.listadopedidosalbergue',compact('programas','productos','fecha','servicio','datos','servicios'));
        
      

    }

      public function listarpedidoalbergue(Request $request){
      
    //  $IdProducto = $request->get('IdProducto');
    //  $cantidad = $request->get('cantidad');
       $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first(); 

      $fecha = $request->get('fecEmi');
      $servicio = $request->get('servicio');
      $prog_id = $request->get('prog_id');

       $programas = DB::tABLE('programas')->select('prog_cod')
       ->groupby('prog_cod')
       ->get();


        $servicios = DB::tABLE('servicios')->get();

      //dd($IdProducto);

        $pedidos = DB::tABLE('pedido_servicio')
        ->join('empresa_negocios','empresa_negocios.id_empresa_negocio','pedido_servicio.id_empresa_negocio')
        ->join('almacenes','almacenes.id_almacen','pedido_servicio.id_almacen')
       
        ->get();
     
      $productos = DB::tABLE('pedido_servicio_cab')
      ->join('productos','productos.IdProducto','pedido_servicio_cab.IdProducto')
      ->where('pedido_servicio_cab.ser_cod',$servicio)
      ->wherE('pedido_servicio_cab.prog_id',$prog_id)
      ->get();
    
      $negocios = DB::tABLE('empresa_negocios')->get();

      $almacenes = DB::tABLE('almacenes')->get();

     return view('empresas.puntosventas.listarpedidoalbergue',compact('programas','productos','fecha','servicio','datos','servicios','pedidos','negocios','almacenes'));
        
      

    }

    public function modificarpedidoalbergue(Request $request){

      $ped_ser_id = $request->get('ped_ser_id');
      $producto = $request->get('IdProducto');
      $movimiento = $request->get('cmb_movimiento');
      $cantidad = $request->get('cantidad');

      $buscar = DB::tABLE('pedido_servicio_cab')->where('ped_ser_id',$ped_ser_id)->first();

      $almacen = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();

     

      if($movimiento =='Ingreso'){
       
        DB::tABLE('pedido_servicio_cab')->where('ped_ser_id',$ped_ser_id)->where('IdProducto',$producto)
        ->update(['total'=>$buscar->total+$cantidad]);

            $receta = DB::tABLE('recetas')->where('prod_id',$producto)->get();

            foreach ($receta as $i => $rec) {
              
              $buscarstock = DB::tABLE('producto_stock')
              ->where('IdProducto',$rec->prod_insu)
              ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
              ->where('id_almacen',$almacen->id_almacen)
              ->first();

              $act_stock = DB::tABLE('producto_stock')
              ->where('pro_sto_id',$buscarstock->pro_sto_id)
              ->update(['stock'=>$buscarstock->stock+($rec->rec_cant*$cantidad)]);

            }

      }elseif($movimiento =='Salida'){
        DB::tABLE('pedido_servicio_cab')->where('ped_ser_id',$ped_ser_id)->where('IdProducto',$producto)
      ->update(['total'=>$buscar->total-$cantidad]);

         $receta = DB::tABLE('recetas')->where('prod_id',$producto)->get();

            foreach ($receta as $i => $rec) {
              
              $buscarstock = DB::tABLE('producto_stock')
              ->where('IdProducto',$rec->prod_insu)
              ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
              ->where('id_almacen',$almacen->id_almacen)
              ->first();

              $act_stock = DB::tABLE('producto_stock')
              ->where('pro_sto_id',$buscarstock->pro_sto_id)
              ->update(['stock'=>$buscarstock->stock-($rec->rec_cant*$cantidad)]);

            }

      }
      
      return Redirect::to("/listarpedidos");


    }

      public function modificarpedidoalber($pedido){

      $buscar = DB::tABLE('pedido_servicio_cab')
      ->join('productos','productos.IdProducto','pedido_servicio_cab.IdProducto')
      ->where('ped_ser_id',$pedido)
      ->first();

     
      
      return view('empresas.puntosventas.modificarpedidoalbergue',compact('buscar'));


    }


     public function buscarpedidosalbergues(Request $request){
      
    //  $IdProducto = $request->get('IdProducto');
    //  $cantidad = $request->get('cantidad');
       $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first(); 

      $fechaini = $request->get('fecEmi');
      $fechafin = $request->get('fecFin');
      $servicio = $request->get('servicio');
     $prog_id = $request->get('prog_id');


        $servicios = DB::tABLE('servicios')->get();



     
      $productos = DB::tABLE('pedido_servicio_cab')
      ->leftjoin('productos','productos.IdProducto','pedido_servicio_cab.IdProducto')
      ->where('pedido_servicio_cab.ser_cod',$servicio)
      ->where('pedido_servicio_cab.prog_id',$prog_id)

      ->where('ped_ser_fec','>=',$fechaini)
      ->where('ped_ser_fec','<=',$fechafin)
      ->get();
    
      $vista = view('empresas.puntosventas.divbuscarservicios',compact('productos'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }


   
      

    }


   public function buscarpedidoalbergue(Request $request){
      
    //  $IdProducto = $request->get('IdProducto');
    //  $cantidad = $request->get('cantidad');
       $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first(); 

     
      $servicio = $request->get('servicio');
     
      $fechaini = $request->get('fecEmi');
      $fechafin = $request->get('fecFin');
      $prog_id = $request->get('prog_id');
      $sucursal = $request->get('part_suc');
      $almacen = $request->get('almacen');


      $servicios = DB::tABLE('servicios')->get();

      $productos = DB::tABLE('pedido_servicio_cab')
      ->leftjoin('productos','productos.IdProducto','pedido_servicio_cab.IdProducto')
      ->where('pedido_servicio_cab.ser_cod',$servicio)
      ->where('pedido_servicio_cab.prog_id',$prog_id)

      ->where('ped_ser_fec','>=',$fechaini)
      ->where('ped_ser_fec','<=',$fechafin)
      ->get();
     
      $pedidos = DB::tABLE('pedido_servicio')
        ->join('empresa_negocios','empresa_negocios.id_empresa_negocio','pedido_servicio.id_empresa_negocio')
        ->join('almacenes','almacenes.id_almacen','pedido_servicio.id_almacen')
        ->where('pedido_servicio.id_empresa_negocio',$sucursal)
        ->where('pedido_servicio.id_almacen',$almacen)
        ->where('pedido_servicio.prog_cod',$prog_id)
         ->where('fec_ped','>=',$fechaini)
        ->where('fec_ped','<=',$fechafin)
        ->get();

      $vista = view('empresas.puntosventas.divbuscarservicio',compact('pedidos'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }


   
      

    }


     public function detallepedidoalbergue(Request $request,$id){
      
    //  $IdProducto = $request->get('IdProducto');
    //  $cantidad = $request->get('cantidad');
       $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first(); 

     
      $servicio = $request->get('servicio');
     
      $fechaini = $request->get('fecEmi');
      $fechafin = $request->get('fecFin');
      $prog_id = $request->get('prog_id');
      $sucursal = $request->get('part_suc');
      $almacen = $request->get('almacen');


      $servicios = DB::tABLE('servicios')->get();


     
    
     $listar = DB::tABLE('pedido_servicio_insu')
        ->join('productos','productos.IdProducto','pedido_servicio_insu.prod_ins')
        ->where('ped_ser_id',$id)
        ->get();     

$cabecera = DB::tABLE('pedido_servicio')->where('ped_ser_id',$id)->first();

    $datosuc = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$cabecera->id_empresa_negocio)->first();

     $datoalm = DB::tABLE('almacenes')->where('id_almacen',$cabecera->id_almacen)->first();

    

     return view('empresas.puntosventas.detallepedidoalbergue',compact('cabecera','listar','datosuc','datoalm'));
      

    }


public function destroy($id){


    DB::tABLE('pedido_servicio')->where('ped_ser_id',$id)->delete();

    return Redirect::to('/listarpedidoalbergue');

}


      public function restaurantpunto(Request $request)
    {

        $rucemp = trim(Auth::user()->IdEmpresa);
        $empresa = Empresa::findOrFail($rucemp);

        $clicod = $request->get('clicod');

 
        $unidades = $request->get('unid');
        $proid = $request->get('proid');
        $detpro = $request->get('pronom');
        $vunit = $request->get('provun');
        $puni = $request->get('propun');
        $vtot = $request->get('itemtotal');
        $cantidades = $request->get('cant');
        $pronomobs = $request->geT('pronomobs');
        $id_almacen = $request->get('id_almacen');
        $tdicod = $request->get('tdicod');
        $mondoc = $request->get('moncod');
        $total = $request->get('total');
        $fecemi = $request->get('fecEmi');
        $ade_pro = $request->get('ade_pro');
        $sal_pro = $request->get('sal_pro');
        $fecven = $request->get('fecVen');
        $tipo_venta = $request->get('tipoventa');
        $observaciones = $request->get('observaciones');
        $pagar = $request->get('pagar');
        $vuelto = $request->get('vuelto');
        $monto = $request->get('monto');
        $medio = $request->get('medio');
        $estadopago = $request->get('estadopago');
        $cliruc = $request->get('clinum');
        $fecha = $request->get('fecEmi');
        $clinom = $request->get('clinom');
        $clidir = $request->get('clidir');
        $clicor = $request->get('clicor');
        $clicor2 = $request->get('clicor2');
        $clicor3 = $request->get('clicor3');
        $clicor4 = $request->get('clicor4');
        $tdocod = $request->get('tdocod');
        $mon_cuo = $request->get('mon_cuo');
        $fec_cuo = $request->get('fec_cuo');
        $cot = $request->get('id');
        $descuento = $request->get('desc');
        $topcod = '0101';
        $id_almacen_pro = $request->get('id_almacen_pro');
       
        $cont_carac = strlen($cliruc);
        $obt_dig = substr(trim($cliruc), 0, 2);

        if($tdocod=='01'){

            if($cont_carac =='11' && ($obt_dig=='10' || $obt_dig=='20' || $obt_dig=='15' || $obt_dig=='17') && $tdicod=='6'){

              
            }else{
                  return response()->json(['estado'=>'error','mensaje'=>'TIPO DE DOCUMENTO NO PERMITIDO PARA EMITIR UNA FACTURA']);
            }
        }
            
          
    

        $buscre = DB::tABLE('credito_dias')->where('cre_dia_id',$estadopago)->first();

        $bus_alm= DB::tABLE('almacenes')->where('id_almacen',$id_almacen)->first();

        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$bus_alm->id_empresa_negocio)->first();

        $tip_cam = DB::tABLE('tipocambio')->where('FecTipCambio',now()->format('Y-m-d'))->first();
        
        $empresanegocio = EmpresaNegocios::findOrFail($sucursal->id_empresa_negocio);

        if(!empty($cot)){

          $bus_cot = cpe_cabecera::findOrFail($cot);
          $bus_cot->estado = 'ACEPTADO';
          $bus_cot->facturado = '1';
          $bus_cot->update();
          $id_vendedor = $bus_cot->IdUsuario_ven;

        }elseif(!empty($request->get('vendedor'))){

          $id_vendedor = $request->get('vendedor');

        }else{
			
			$id_vendedor = Auth::user()->IdUsuario;
		}

        $bus_ven = DB::tABLE('users')->where('IdUsuario',$id_vendedor)->first();

        
       if(!empty($request->get('ped_id'))){
           
          $bus_ped = cpe_cabecera::findOrFail($request->get('ped_id'));

          if($bus_ped->tdocod=='15' || $bus_ped->tdocod=='16'){
              $bus_ped->facturado = '1';
             $bus_ped->update();
          }

         
        }
       

        if(!empty($request->get('id'))){
            $cotizacion = cpe_cabecera::findOrFail($request->get('id'));
            $cotizacion->estado ='ACEPTADO';
            $cotizacion->update();
        }



        if($tdocod == '01'){
          $senudoc = DB::tABLE('empresa_negocios')->select('FseEmpresa','FnuEmpresa')->where('id_empresa_negocio',$sucursal->id_empresa_negocio)->first();
          $numcomp =  $senudoc->FnuEmpresa+1;
          $sercomp =  $senudoc->FseEmpresa;
        }elseif ($tdocod =='03') {
          $senudoc = DB::tABLE('empresa_negocios')->select('BseEmpresa','BnuEmpresa')->where('id_empresa_negocio',$sucursal->id_empresa_negocio)->first();
          $numcomp =  $senudoc->BnuEmpresa+1;
          $sercomp =  $senudoc->BseEmpresa;
        }elseif ($tdocod =='13') {
          $senudoc = DB::tABLE('empresa_negocios')->select('SerNota','NumNota')->where('id_empresa_negocio',$sucursal->id_empresa_negocio)->first();
          $numcomp =  $senudoc->NumNota+1;
          $sercomp =  $senudoc->SerNota;
        }elseif ($tdocod =='15') {
          $senudoc = DB::tABLE('empresa_negocios')->select('ProNum','ProSer')->where('id_empresa_negocio',$sucursal->id_empresa_negocio)->first();
          $numcomp =  $senudoc->ProNum+1;
          $sercomp =  $senudoc->ProSer;
         }elseif ($tdocod =='14') {
          $senudoc = DB::tABLE('empresa_negocios')->select('NumVal','SerVal')->where('id_empresa_negocio',$sucursal->id_empresa_negocio)->first();
          $numcomp =  $senudoc->NumVal+1;
          $sercomp =  $senudoc->SerVal;
        }


        $moneda = DB::tABLE('moneda')->where('moncod','=',$mondoc)->first();

      //Registrar el tipo de cambio enviado a través del formulario si no existe.
        if($mondoc !='PEN'){
          $camdoc = $request->get('camdoc');
          $tipcam = tipocambio::FirstOrCreate(['FecTipCambio'=>$fecemi],['CamCompra'=>$camdoc]);
        }else{
          $camdoc=0;
        }

        $oldDate = strtotime($request->get('clifecnac'));

        $mes = date('m',$oldDate);


      //Registrar el cliente enviado a través del formulario si no existe

       if(empty($tdicod)){
            $tdicod = '1';
        }

        if(empty(trim($cliruc))){

            $cliente = Cliente::Create(['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$request->get('clicor'),'clicor2'=>$request->get('clicor2'),'clicor3'=>$request->get('clicor3'),'clicor4'=>$request->get('clicor4'),'tdicod'=>$tdicod,'telefono'=>$request->get('clitel'),'rucemp'=>Auth::user()->IdEmpresa]);

        }elseif($cliruc=='00000000' && ( trim(strtoupper($clinom))=='VENTAALPORTADOR' or trim(strtoupper($clinom)=='VARIOS'))){

            $cliente = Cliente::UpdateOrCreate(['clinum'=>$cliruc],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$request->get('clicor'),'clicor2'=>$request->get('clicor2'),'clicor3'=>$request->get('clicor3'),'clicor4'=>$request->get('clicor4'),'tdicod'=>$tdicod,'telefono'=>$request->get('clitel'),'rucemp'=>Auth::user()->IdEmpresa]); 

        }else{

            $cliente = Cliente::UpdateOrCreate(['clinum'=>$cliruc],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$request->get('clicor'),'clicor2'=>$request->get('clicor2'),'clicor3'=>$request->get('clicor3'),'clicor4'=>$request->get('clicor4'),'tdicod'=>$tdicod,'telefono'=>$request->get('clitel'),'rucemp'=>Auth::user()->IdEmpresa]); 
             
        }
      


        
         $buscarplaca = DB::tABLE('tipos_vehiculos')->where('placa',$request->get('placa'))->first();
    

        //BUSCAR PLACA

        if(empty($buscarplaca)){


          $vehiculos = new tipos_vehiculos;
          $vehiculos->IdEmpresa = trim(Auth::user()->IdEmpresa);
          $vehiculos->mar_id = $request->get('marca');
          $vehiculos->mod_id = $request->get('modelo');
          $vehiculos->comb_id = $request->get('combustible');
          $vehiculos->clicod = $cliente->clicod;
          $vehiculos->kilometros = $request->get('kilometros');
          $vehiculos->observaciones = $request->get('observaciones');
          $vehiculos->placa = $request->get('placa');
          $vehiculos->kilometros = $request->get('kilometros');
          $vehiculos->cilindrada = $request->get('cilindrada'); 
          $vehiculos->fecinspeccion = $request->get('fecinspeccion');
          $vehiculos->bastidor = $request->get('bastidor');
          $vehiculos->fecrevision = $request->get('fecrevision');
          $vehiculos->fecsoat = $request->get('fecsoat');
          $vehiculos->color = $request->get('color');
          $vehiculos->ano = $request->get('ano');
          $vehiculos->id_empresa_negocio = Auth::user()->id_empresa_negocio;
          $vehiculos->save();

        }else{


            $vehiculos =  tipos_vehiculos::findOrFail($buscarplaca->id_tipo_vehiculo);
            $vehiculos->IdEmpresa = trim(Auth::user()->IdEmpresa);
            $vehiculos->mar_id = $request->get('marca');
            $vehiculos->mod_id = $request->get('modelo');
            $vehiculos->comb_id = $request->get('combustible');
            $vehiculos->clicod = $cliente->clicod;
            $vehiculos->kilometros = $request->get('kilometros');
            $vehiculos->observaciones = $request->get('observaciones');
            $vehiculos->placa = $request->get('placa');
            $vehiculos->kilometros = $request->get('kilometros');
            $vehiculos->cilindrada = $request->get('cilindrada'); 
            $vehiculos->fecinspeccion = $request->get('fecinspeccion');
            $vehiculos->bastidor = $request->get('bastidor');
            $vehiculos->fecrevision = $request->get('fecrevision');
            $vehiculos->fecsoat = $request->get('fecsoat');
            $vehiculos->color = $request->get('color');
            $vehiculos->ano = $request->get('ano');
            $vehiculos->id_empresa_negocio = Auth::user()->id_empresa_negocio;
            $vehiculos->update();

        }
     

        $cabecera = new cpe_cabecera;
        $cabecera->tdocod = $tdocod;
        $cabecera->topcod = $topcod;
        $cabecera->ccafem = $fecemi;
        $cabecera->comb_id = $request->get('combustible');
        $cabecera->kilometros = $request->get('kilometros');
        $cabecera->observaciones = $request->get('observaciones');
        $cabecera->placa = $request->get('placa_comp');
        $cabecera->guia_remision = $request->get('guia_remision');
        $cabecera->cilindrada = $request->get('cilindrada');
        $cabecera->tec_id = $request->get('tecnico');
        $cabecera->bastidor = $request->get('bastidor');
        $cabecera->fecinspeccion = $request->get('fecinspeccion');
        $cabecera->fecsoat = $request->get('fecsoat');
        $cabecera->fecrevision = $request->get('fecrevision');
        $cabecera->color = $request->get('color');
        $cabecera->encargado = $request->get('encargado');
        $cabecera->encargadotel = $request->get('encargadotel');
        $cabecera->id_almacen = $id_almacen;

        if($tdocod =='15'){
          $cabecera->estado ='PENDIENTE';
        }

        if($buscre->cre_dia_tip=='CONTADO' || $buscre->cre_dia_tip=='PERSONALIZADO'){
            $cabecera->ccafve = $request->get('fecVen');
        }else{
            $cabecera->ccafve = date('Y-m-d',strtotime($fecemi."+ ".$buscre->cre_dia_fac." days"));
        }

        if($buscre->cre_dia_tip=='CONTADO'){
            
            $cabecera->totalcontado = $total;
            $cabecera->totalcredito = '0';

        }elseif($buscre->cre_dia_tip=='CREDITO' || $buscre->cre_dia_tip=='PERSONALIZADO'){
            $cabecera->totalcredito = $total;
            $cabecera->totalcontado = '0';


        }
      
        $cabecera->ccaobs = $observaciones;
        $cabecera->tdicod = $tdicod;
        $cabecera->ccandi = $cliruc;
        $cabecera->ccanom = $clinom;
        $cabecera->ped_ref = $request->get('ped_id');
        $cabecera->moncod = $mondoc;
        $cabecera->direccion = $clidir;
        $cabecera->clicorcli = $clicor;
        $cabecera->clicorcli2 = $clicor2;
        $cabecera->clicorcli3 = $clicor3;
        $cabecera->clicorcli4 = $clicor4;
        $cabecera->ade_pro = $ade_pro;
        $cabecera->sal_pro = $sal_pro;
        $cabecera->cre_dia_id = $estadopago;
        $cabecera->IdUsuario_ven = $id_vendedor;
        $cabecera->vendedor = $bus_ven->name.' '.$bus_ven->apeusu;
        
        if(!empty($bus_cot)){
          $cabecera->referencia = $bus_cot->serdoc.'-'.$bus_cot->numdoc;
        }

        $cabecera->tipcambio = $camdoc;

        if($sucursal->tip_igv_pred =='10'){
            $cabecera->ccatvg =  $total/1.1055;
            $cabecera->ccaigv =  $total-$total/1.1055;
        }

        if($sucursal->tip_igv_pred =='20'){
            $cabecera->ccatexo =  $total;
            $cabecera->ccaigv = '0.00';
        }
        
        $cabecera->ccatinaf =  '0.00';
        $cabecera->ccaitv = $total;
        $cabecera->id_empresa_negocio = $sucursal->id_empresa_negocio;
        $cabecera->clicod = $cliente->clicod;
        //$cabecera->icbper = $totalicbper;
        $cabecera->ccaobs = $observaciones;
        $cabecera->paga = $pagar;
        
        if(!empty($tip_cam->CamVenta)){
          $cabecera->tipcambio = $tip_cam->CamVenta;
        }
        
        $cabecera->vuelto = $vuelto;

        if($buscre->cre_dia_tip=='CONTADO'){
           $cabecera->estadopago = 'CONTADO';
        }else{
           $cabecera->estadopago = 'CREDITO';
        }
        
        
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->IdEmpresa =  $rucemp;

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
        
   
        }elseif($tdocod=='15'){
          if( $empresanegocio->ProNum == $numcomp){
              $modnumcomp = $numcomp+1;
          }else{
              $modnumcomp = $numcomp;
          }

          $empresanegocio->ProSer = $sercomp;
          $empresanegocio->ProNum = $modnumcomp;

          $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
          $cabecera->serdoc= $sercomp;
          $cabecera->numdoc = $numdoc;
        
       

        }elseif($tdocod=='14'){
          if( $empresanegocio->NumVal == $numcomp){
              $modnumcomp = $numcomp+1;
          }else{
              $modnumcomp = $numcomp;
          }

          $empresanegocio->SerVal = $sercomp;
          $empresanegocio->NumVal = $modnumcomp;
     

          $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
          $cabecera->serdoc= $sercomp;
          $cabecera->numdoc = $numdoc;
        
        }

        $empresanegocio->update();
        $cabecera->save();

        $codfact = $cabecera->IdCpe_cabecera; 

        if($buscre->cre_dia_tip=='CREDITO' || $buscre->cre_dia_tip=='PERSONALIZADO' ){

            if(!empty($mon_cuo)){
                $i=0;
                foreach ($mon_cuo as $key => $mc){

                    $i=$i+1;

                    DB::tABLE('ventas_cuotas')
                    ->insert([
                        'ven_cuo_num'=>$i,
                        'ven_cuo_fec_ven'=>$fec_cuo[$key],
                        'ven_cuo_mon'=>$mc,
                        'IdCpe_cabecera'=>$codfact
                    ]);
                }
            }else{

                DB::tABLE('ventas_cuotas')
                    ->insert([
                        'ven_cuo_num'=>'1',
                        'ven_cuo_fec_ven'=>$cabecera->ccafve,
                        'ven_cuo_mon'=>$cabecera->ccaitv,
                        'IdCpe_cabecera'=>$codfact
                ]);

            }
        }

        $usuario_facturacion = new usuario_facturacion;
        $usuario_facturacion->IdCpe_cabecera = $codfact;
        $usuario_facturacion->id_turno = Auth::user()->id_turno;
        $usuario_facturacion->id_empresa_negocio = $sucursal->id_empresa_negocio;
        $usuario_facturacion->IdEmpresa = $rucemp;
        $usuario_facturacion->referencia = "Registro";
        $usuario_facturacion->save();

        self::generar_codigo_movimiento($codfact);
  
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
                //  $movimiento->importe = $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)*1.18));

                 $movimiento->importe = $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)));

                }
                        
                $movimiento->estado = '1';
                $movimiento->mov_fecha = $cabecera->ccafem;
                $movimiento->clicod = $cabecera->clicod;
                $movimiento->registro = 'Registrado';
                 
                if($contar==0){
                 // $totalsaldo =  $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)*1.18));

                     $totalsaldo =  $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)));

                }else{
                //  $totalsaldo = $buscar->last()->saldo + $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)*1.18));
                   $totalsaldo = $buscar->last()->saldo + $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)));
                }
                          

                $movimiento->saldo = $totalsaldo;
                $movimiento->IdEmpresa = Auth::user()->IdEmpresa;
                $movimiento->id_empresa_negocio = $sucursal->id_empresa_negocio;
                $movimiento->save();

              }
             }
            
          }


         foreach($proid as $index => $id) {

            if($id !='0'){

                $codpro = productos::findOrFail($id);
                $codproducto = $codpro->procod;

                   if(empty($codpro->pro_rel)){

                      $id_prod = $codpro->IdProducto;

                    }else{
              
                      $id_prod = $codpro->pro_rel;

                    }

                if($tdocod !='15'){
                  
                 

                $stockprod = DB::tABLE('producto_stock')
                ->where('IdProducto',$id_prod)
                ->where('id_empresa_negocio',$sucursal->id_empresa_negocio)
                ->where('id_almacen',$id_almacen)
                ->first();

               // dd($stockprod);
                if(empty($stockprod)){

                  $stock = 0-($cantidades[$index]*$codpro->factor);

                  $stockprod_act = DB::tABLE('producto_stock')
                  ->insert([
                    'stock'=>$stock,
                    'IdProducto'=>$id_prod,
                    'id_empresa_negocio'=>$sucursal->id_empresa_negocio,
                    'id_almacen'=>$id_almacen]
                  );

                  $sto_ini = '0';
                  
                }else{

                   $stock = $stockprod->stock-($cantidades[$index]*$codpro->factor);

                  $stockprod_act = DB::tABLE('producto_stock')
                  ->where('pro_sto_id',$stockprod->pro_sto_id)
                  ->update(['stock'=>$stockprod->stock-($cantidades[$index]*$codpro->factor)]);

                  $sto_ini = $stockprod->stock_inicial;


                }

              }else{

                $stock='0';

              }

            $detalle = new cpe_detalle;
            $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
            $detalle->umecod = $codpro->umecod;
            $detalle->cpe_det_factor = $codpro->factor;
            $detalle->comision = $sucursal->comision;
            $detalle->cdecan = $cantidades[$index];
            $detalle->procod = $codproducto;
            $detalle->IdProducto = $codpro->IdProducto;
            $detalle->IdProducto_rel = $id_prod;
            $detalle->cdepsu = "";
            $detalle->cdedes = $detpro[$index];
            $detalle->pronomobs = $pronomobs[$index];
            $detalle->costo = $codpro->costo;
            $detalle->tigcod = $codpro->tigcod;
            $detalle->icbper = $codpro->icbper;
            $detalle->cpe_det_stock = $stock;
            $detalle->desc_mon = $descuento[$index];
            $detalle->id_almacen_pro = $id_almacen;


           
           
           /*calcular porcentaje de descuento*/
            if($sucursal->tipo_desc=='1'){
                $desc_mon = $descuento[$index];
                $desc_por = ($descuento[$index]*100)/$puni[$index];
            }elseif($sucursal->tipo_desc=='2'){
                $desc_por = $descuento[$index];
                $desc_mon = $puni[$index]*($descuento[$index]/100);
            }
           
            if($codpro->tigcod =='10'){

              $preciouni = $puni[$index];
              $valoruni = $puni[$index]/1.1055;
              $valorunitario = $puni[$index]/1.1055;

              $valorsubtotal = $vtot[$index]/1.1055;
              $valortotal = $vtot[$index];
             
            }elseif($codpro->tigcod=='20'){

              $preciouni = $puni[$index];
              $valoruni = $puni[$index];
              $valorunitario = $puni[$index];

              $valorsubtotal = $vtot[$index];
              $valortotal = $vtot[$index];
            }

            $valorigvtotal =  $valortotal-$valorsubtotal;
           
           

           /*FIN CALCULAR DESCUENTO*/
            $detalle->valor_unitario = $valorunitario;
            $detalle->por_des = $desc_por;
            $detalle->desc_mon = $desc_mon;
            $detalle->cdepuni = $preciouni;
            $detalle->cdevun = $valoruni;
            $detalle->cdevve = $valortotal;
            $detalle->cdepve = $valorsubtotal;
            $detalle->cdeigv = $valorigvtotal;
            $detalle->fecha_venta = $fecemi;
            $detalle->flete = $codpro->flete;
          
            if(isset($stockprod)){
                      $detalle->cpe_det_stock_inicial = $stockprod->stock_inicial;
            }
          
            $detalle->save();


            if(isset($stockprod)){

                     DB::tABLE('movimientos_productos')->insert([
                        'IdProducto'=>$id_prod,
                        'precio'=>$preciouni,
                        'cantidad'=>$cantidades[$index]*$codpro->factor,
                        'costo'=>$codpro->costo,
                        'mov_cab_id'=>'',
                        'stock'=>$stock,
                        'IdProducto_rel'=>$id_prod,
                        'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
                        'com_cab_id'=>'',
                        'stock_inicial'=>$sto_ini,
                        'serie'=>$cabecera->serdoc,
                        'numero'=>$cabecera->numdoc,
                        'tdocod'=>$cabecera->tdocod,
                        'tipo'=>'3',
                        'mov_tip'=>'E',
                        'id_empresa_negocio'=>$sucursal->id_empresa_negocio,
                        'id_almacen'=>$id_almacen,
                        'fecha_mov'=>$fecha,
                    ]);

            }

            $movimiento = new movimientos;
            $movimiento->mov_fec = $fecha; 
            $movimiento->mov_tip = 'E';
            $movimiento->mov_mot = 'Venta';
            $movimiento->cantidad = $cantidades[$index];
            $movimiento->unidad = $codpro->umecod;
            $movimiento->comprobante = $sercomp.'-'.$numdoc;
            $movimiento->IdEmpresa = $rucemp;
            $movimiento->id_empresa_negocio = $sucursal->id_empresa_negocio;
            $movimiento->IdProducto = $id;
            $movimiento->observacion = "Venta desde Punto de Venta";
            $movimiento->IdUsuario = Auth::user()->IdUsuario;
            $movimiento->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
            $movimiento->stockmov = $stock;
            $movimiento->save();


            }else{

            
            $detalle = new cpe_detalle;
            $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
            $detalle->umecod = $unidades[$index];
            $detalle->cdecan = $cantidades[$index];
            $detalle->cdepsu = "";
            $detalle->cdedes = $detpro[$index];
            $detalle->desc_mon = $descuento[$index];

            //$detalle->costo = $codpro->costo;
            $detalle->tigcod = $sucursal->tip_igv_pred;
   //        $detalle->icbper = $codpro->icbper;
        
              /*calcular porcentaje de descuento*/
              $desc_mon='0';
              $desc_por ='0';
          
           
            if($sucursal->tip_igv_pred =='10'){

              $preciouni = $puni[$index];
              $valoruni = $puni[$index]/1.1055;
              $valorunitario = $puni[$index]/1.1055;

              $valorsubtotal = $vtot[$index]/1.1055;
              $valortotal = $vtot[$index];
             
            }elseif($sucursal->tip_igv_pred =='20'){

              $preciouni = $puni[$index];
              $valoruni = $puni[$index];
              $valorunitario = $puni[$index];

              $valorsubtotal = $vtot[$index];
              $valortotal = $vtot[$index];
            }

            $valorigvtotal =  $valortotal-$valorsubtotal;

            $detalle->valor_unitario = $valorunitario;
            $detalle->por_des = $desc_por;
            $detalle->desc_mon = $desc_mon;
            $detalle->cdepuni = $preciouni;
            $detalle->cdevun = $valoruni;
            $detalle->cdevve = $valortotal;
            $detalle->cdepve = $valorsubtotal;
            $detalle->cdeigv = $valorigvtotal;
            $detalle->fecha_venta = $fecemi;
            if(!empty($id_almacen)){
                $detalle->id_almacen_pro = $id_almacen;
            }
            $detalle->save();


            }

          
        }

        
        $gen_xml_pdf = new cpe_cabecera;
        if($tdocod =='01' || $tdocod=='03'){
            $nom_arch= $gen_xml_pdf->generar_xml_boleta_factura($codfact);
            
        }

        
        $cabecera->generar_nuevo_qr($codfact);
        $documento = $cabecera->generarpdfgeneral($codfact);
        


        if($empresa->formato =='TICKET'){         
            if(empty($cabecera->referencia)){
               for($i=1;$i<=$empresa->imp_venta;$i++){
                  if($request->get('opcion')=='0'){
                    self::imprimir($codfact,$tdocod);
                  }
                }
            }
        }elseif($empresa->formato=='A4'){
        
           if($request->get('opcion')=='0'){
              exec("c:\WINDOWS\system32\cmd.exe /c START powershell.exe -ExecutionPolicy Bypass Start-Process -FilePath ".public_path()."/pdf/".$documento." -Verb Print");
            }
            
        }

     

        if($buscre->cre_dia_tip !='CONTADO'){
          self::registrarcuentascobrar($codfact);
        }
       
        if(!empty(trim($clicor))){
           $cabecera->enviar_comprobante_correo($codfact,$clicor);
        }

        if(!empty(trim($clicor2))){
           $cabecera->enviar_comprobante_correo($codfact,$clicor2);
        }

        if(!empty(trim($clicor3))){
           $cabecera->enviar_comprobante_correo($codfact,$clicor3);
        }

        if(!empty(trim($clicor4))){
           $cabecera->enviar_comprobante_correo($codfact,$clicor4);
        }


        if($empresa->tipo_envio =='1'){
               if($tdocod =='01' || $tdocod=='03'){
            $cabecera->enviar_sunat($codfact);
        }
        }

        return response()->json(['estado'=>'success','codfact' =>$codfact,'tdocod'=>$tdocod,'mensaje'=>'Comprobante Emitido','numero'=>$modnumcomp]);
        
               
    }

    //FUNCION PARA PEDIDOS EN NEGOCIOS

   public function registrarpedido(Request $request)
    {
        $detpro = $request->get('pronom');
        $proid = $request->get('proid');
        $puni = $request->get('propun');
        $puniref = $request->get('propunref');
     $cantidades = $request->get('cant');
    $fechaactual = now()->format('Y-m-d');

    $busfecha = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

      $almacen = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();

    if($fechaactual != $busfecha->fecha_pedidos){
       DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->update(['fecha_pedidos'=>$fechaactual,'numNP'=>'0']);
    }

  if(Auth::User()->hasRole('caja') ||  Auth::User()->hasRole('vendedor') ){



        foreach($proid as $index => $id) {
            
            //$con_pro = DB::tABLE('producto_empresa')->where('IdProducto',$id)->first();
            $cal_stock = DB::tABLE('producto_stock')
      ->where('IdProducto',$id)
      ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->where('id_almacen',$almacen->id_almacen)
      ->first();

            if($puni[$index] < $puniref[$index]){
                
                return response()->json(['estado'=>'error','mensaje'=>'NO ESTA PERMITIDO EL DESCUENTO - PRODUCTO: '.$detpro[$index].' PRECIO: '.$puniref[$index]]);
        
        
            }

      if($cal_stock->stock < $cantidades[$index]){
        
        return response()->json(['estado'=>'error','mensaje'=>'NO HAY STOCK PARA LA CANTIDAD INDICADA EN EL SIGUIENTE PRODUCTO  '.$detpro[$index]]);
        
    
      }

        }

    


     }
             
        $tip_cam = DB::tABLE('tipocambio')->where('FecTipCambio',now()->format('Y-m-d'))->first();

      

       //Datos de cabecera
        $tdicod = $request->get('tdicod');
        $tdocod = $request->get('tdocod');
           $tdocod1 = $request->get('tdocod_1');

        $mondoc = $request->get('moncod');
        $total = $request->get('total');
        $fecemi = $request->get('fecEmi');
        $ade_pro = $request->get('ade_pro');
        $sal_pro = $request->get('sal_pro');
        $fecven = $request->get('fecVen');
        $tipo_venta = $request->get('tipoventa');
        $observaciones = $request->get('observaciones');
        $pagar = $request->get('pagar');
        $vuelto = $request->get('vuelto');
      //medios de pago
        $monto = $request->get('monto');
        $medio = $request->get('medio');


     
      //Datos del Cliente
        $cliruc = $request->get('clinum');
        $fecha = $request->get('fecEmi');
        $clinom = $request->get('clinom');
        $clidir = $request->get('clidir');
        $clicor = $request->get('clicor');

     
        $rucemp = trim(Auth::user()->IdEmpresa);
        $empresa = Empresa::findOrFail($rucemp);
        //DETALLE 
        $unidades = $request->get('unid');
       
        
        $vunit = $request->get('provun');
        $puni = $request->get('propun');
        $vtot = $request->get('itemtotal');
       
        $pronomobs = $request->geT('pronomobs');

        $total =0;
        $totalexo = 0;
        $totalafec = 0;
        $totalinaf =0;
        $totaligv =0;
        $totalsubt = 0;
        $totalicbper =0;

        foreach($proid as $index => $p ) {

            $codpro = productos::findOrFail($p);

            $total = $total + ($cantidades[$index]*$puni[$index]);

            if($codpro->icbper=='1'){
              $totalicbper = $totalicbper + ($cantidades[$index]*$empresa->icbper);
            }


            if($codpro->tigcod =='10'){

             
              $totalafec = ($total / 1.18);
              $totaligv = $total-$totalafec;


            }elseif($codpro->tigcod =='20'){

              $totalexo = $total;
              $totaligv =  $total-$totalexo;

            }elseif($codpro->tigcod =='30'){

              $totalinaf = $total;
              $totaligv =  $total-$totalinaf;

            }

        }


      
     

        $topcod = '0101';



          $senudoc = DB::tABLE('empresa_negocios')->select('serieNP','numNP')
          ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->first();

          $numcomp =  $senudoc->numNP+1;
          $sercomp =  $senudoc->serieNP;
        


        $moneda = DB::tABLE('moneda')->where('moncod','=',$mondoc)->first();

      //Registrar el tipo de cambio enviado a través del formulario si no existe.
        if($mondoc !='PEN'){
          $camdoc = $request->get('camdoc');
          $tipcam = tipocambio::FirstOrCreate(['FecTipCambio'=>$fecemi],['CamCompra'=>$camdoc]);
        }else{
          $camdoc=0;
        }

       $oldDate = strtotime($request->get('clifecnac'));

        $mes = date('m',$oldDate);

        $dat_ven = DB::tABLE('users')->where('IdUsuario',$request->get('vendedor'))->first();
      //Registrar el cliente enviado a través del formulario si no existe
        $cliente = Cliente::UpdateOrCreate(['clinum'=>$cliruc],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$clicor,'rucemp'=>$rucemp,'tdicod'=>$tdicod,'telefono'=>$request->get('clitel'),'clifecnac'=>$request->get('clifecnac'),'mes_nac'=>$mes]);

      //Crear un objeto de la cabecera del comprobante para registrar los datos de la cabecera
        $cabecera = new cpe_cabecera;
        $cabecera->tdocod = $tdocod;
         $cabecera->tdocod_fac = $tdocod1;
        $cabecera->topcod = $topcod;
        $cabecera->ccafem = $fecemi;       
        $cabecera->ccafve = $request->get('fecVen');
        $cabecera->tdicod = $tdicod;
        $cabecera->ccandi = $cliruc;
        $cabecera->ccanom = $clinom;
        $cabecera->moncod = $mondoc;
        $cabecera->direccion = $clidir;
        $cabecera->IdUsuario_ven = $request->get('vendedor');
        $cabecera->vendedor = $dat_ven->name.' '.$dat_ven->apeusu;
        $cabecera->clicorcli = $clicor;
        $cabecera->ade_pro = $ade_pro;
        $cabecera->sal_pro = $sal_pro;
        $cabecera->tipcambio = $camdoc;
        $cabecera->ccatvg =  $totalafec;
        $cabecera->ccatexo =  $totalexo;
        $cabecera->ccatinaf =  $totalinaf;
        $cabecera->ccaigv = $totaligv;
        $cabecera->ccaitv = $total;
        $cabecera->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $cabecera->clicod = $cliente->clicod;
  
       
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->IdEmpresa =  $rucemp;

        $empresa = Empresa::findOrFail($rucemp);
        $empresanegocio = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);
     
   
          if( $empresanegocio->numNP == $numcomp){
              $modnumcomp = $numcomp+1;
          }else{
              $modnumcomp = $numcomp;
          }

          $empresanegocio->serieNP = $sercomp;
          $empresanegocio->numNP = $modnumcomp;
         // $empresanegocio->update();

          $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
          $cabecera->serdoc= $sercomp;
          $cabecera->numdoc = $numdoc;
          //$cabecera->save();
        

           $empresanegocio->update();

          $empresa->update();
          $cabecera->save();

          $codfact = $cabecera->IdCpe_cabecera; 
          $usuario_facturacion = new usuario_facturacion;
          $usuario_facturacion->IdCpe_cabecera = $codfact;
          $usuario_facturacion->id_turno = Auth::user()->id_turno;
         
         // $usuario_facturacion->ped_id = $pedido;
          $usuario_facturacion->id_empresa_negocio = Auth::user()->id_empresa_negocio;
          $usuario_facturacion->IdEmpresa = Auth::user()->IdEmpresa;
          $usuario_facturacion->referencia = "Registro";
          $usuario_facturacion->save();
  
      


        //Generar el detalle del comprobante

         foreach($proid as $index => $id) {

            $codpro = productos::findOrFail($id);
            $codproducto = $codpro->procod;

            $detalle = new cpe_detalle;
            $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
            $detalle->umecod = $codpro->umecod;
            $detalle->cdecan = $cantidades[$index];
            $detalle->procod = $codproducto;
            $detalle->IdProducto = $codpro->IdProducto;
            $detalle->cdepsu = "";
            $detalle->cdedes = $detpro[$index];
            $detalle->pronomobs = $pronomobs[$index];
            $detalle->costo = $codpro->costo;
            $detalle->tigcod = $codpro->tigcod;
            $detalle->icbper = $codpro->icbper;

            if($codpro->tigcod =='10'){
              $preciouni = $puni[$index];
              $valoruni = $puni[$index]/1.1055;
            }elseif($codpro->tigcod=='20'){
              $preciouni = $puni[$index];
              $valoruni = $puni[$index];
            }
              $valorigv = $preciouni - $valoruni;

              $valorsubtotal = $valoruni*$cantidades[$index];
              $valortotal = $preciouni*$cantidades[$index];
              $valorigvtotal =  $valorigv*$cantidades[$index];
           

            $detalle->cdepuni = $preciouni;
            $detalle->cdevun = $valoruni;
            $detalle->cdevve = $valortotal;
            $detalle->cdepve = $valorsubtotal;
            $detalle->cdeigv = $valorigvtotal;
            $detalle->fecha_venta = $fecemi;
            $detalle->save();


          
        }

    

      /*  if($empresa->formato =='TICKET'){         
           
               for($i=1;$i<=$empresa->imp_venta;$i++){
                  if($request->get('opcion')=='0'){
                    self::imprimir($codfact,$tdocod);
                  }
                }
           
        }*/



       //  self::registrarguia($request);


        return response()->json(['pedido'=>$modnumcomp,'estado'=>'success','codfact' =>$codfact,'tdocod'=>$tdocod,'mensaje'=>'Comprobante Emitido']);
        
               
    }

    public function actualizarpedido(Request $request)
    {

		  $proid = $request->get('proid');
		   $puni = $request->get('propun');
		    $puniref = $request->get('propunref');
            $descuento = $request->get('desc');
		
		/*foreach($proid as $index => $id) {
			
			//$con_pro = DB::tABLE('producto_empresa')->where('IdProducto',$id)->first();
			
			if($puni[$index] < $puniref[$index]){
				
				return response()->json(['estado'=>'error','mensaje'=>'NO ESTA PERMITIDO EL DESCUENTO']);
        
		
			}
		}*/
			
			
        $tip_cam = DB::tABLE('tipocambio')->where('FecTipCambio',now()->format('Y-m-d'))->first();

        $almacen = DB::tABLE('almacenes')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('predeterminado','1')->first();

       //Datos de cabecera
        $tdicod = $request->get('tdicod');
        $tdocod = $request->get('tdocod');
		 $tdocod1 = $request->get('tdocod_1');
        $mondoc = $request->get('moncod');
        $total = $request->get('total');
        $fecemi = $request->get('fecEmi');
        $ade_pro = $request->get('ade_pro');
        $sal_pro = $request->get('sal_pro');
        $fecven = $request->get('fecVen');
        $tipo_venta = $request->get('tipoventa');
        $observaciones = $request->get('observaciones');
        $pagar = $request->get('pagar');
        $vuelto = $request->get('vuelto');
      //medios de pago
        $monto = $request->get('monto');
        $medio = $request->get('medio');


     
      //Datos del Cliente
        $cliruc = $request->get('clinum');
        $fecha = $request->get('fecEmi');
        $clinom = $request->get('clinom');
        $clidir = $request->get('clidir');
        $clicor = $request->get('clicor');

     
        $rucemp = trim(Auth::user()->IdEmpresa);
        $empresa = Empresa::findOrFail($rucemp);
        //DETALLE 
        $unidades = $request->get('unid');
      
        $detpro = $request->get('pronom');
        $vunit = $request->get('provun');
       
        $vtot = $request->get('itemtotal');
        $cantidades = $request->get('cant');
        $pronomobs = $request->geT('pronomobs');

        $total =0;
        $totalexo = 0;
        $totalafec = 0;
        $totalinaf =0;
        $totaligv =0;
        $totalsubt = 0;
        $totalicbper =0;

        foreach($proid as $index => $p ) {

            $codpro = productos::findOrFail($p);

            $total = $total + ($cantidades[$index]*($puni[$index]-$descuento[$index]));

            if($codpro->icbper=='1'){
              $totalicbper = $totalicbper + ($cantidades[$index]*$empresa->icbper);
            }


            if($codpro->tigcod =='10'){

             
              $totalafec = ($total / 1.18);
              $totaligv = $total-$totalafec;


            }elseif($codpro->tigcod =='20'){

              $totalexo = $total;
              $totaligv =  $total-$totalexo;

            }elseif($codpro->tigcod =='30'){

              $totalinaf = $total;
              $totaligv =  $total-$totalinaf;

            }

        }


      
     

        $topcod = '0101';



          $senudoc = DB::tABLE('empresa_negocios')->select('serieNP','numNP')
          ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->first();

          $numcomp =  $senudoc->numNP+1;
          $sercomp =  $senudoc->serieNP;
        


        $moneda = DB::tABLE('moneda')->where('moncod','=',$mondoc)->first();

      //Registrar el tipo de cambio enviado a través del formulario si no existe.
        if($mondoc !='PEN'){
          $camdoc = $request->get('camdoc');
          $tipcam = tipocambio::FirstOrCreate(['FecTipCambio'=>$fecemi],['CamCompra'=>$camdoc]);
        }else{
          $camdoc=0;
        }

       $oldDate = strtotime($request->get('clifecnac'));

        $mes = date('m',$oldDate);

        $dat_ven = DB::tABLE('users')->where('IdUsuario',$request->get('vendedor'))->first();
      //Registrar el cliente enviado a través del formulario si no existe
        $cliente = Cliente::UpdateOrCreate(['clinum'=>$cliruc],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$clicor,'rucemp'=>$rucemp,'tdicod'=>$tdicod,'telefono'=>$request->get('clitel'),'clifecnac'=>$request->get('clifecnac'),'mes_nac'=>$mes]);

      //Crear un objeto de la cabecera del comprobante para registrar los datos de la cabecera
        $cabecera = cpe_cabecera::findOrFail($request->get('ped_id'));
        $cabecera->tdocod = $tdocod;
			 $cabecera->tdocod_fac = $tdocod1;
        $cabecera->topcod = $topcod;
        $cabecera->ccafem = $fecemi;       
        $cabecera->ccafve = $request->get('fecVen');
        $cabecera->tdicod = $tdicod;
        $cabecera->ccandi = $cliruc;
        $cabecera->ccanom = $clinom;
        $cabecera->moncod = $mondoc;
        $cabecera->direccion = $clidir;
        $cabecera->IdUsuario_ven = $request->get('vendedor');
        $cabecera->vendedor = $dat_ven->name.' '.$dat_ven->apeusu;
        $cabecera->clicorcli = $clicor;
        $cabecera->ade_pro = $ade_pro;
        $cabecera->sal_pro = $sal_pro;
        $cabecera->tipcambio = $camdoc;
        $cabecera->ccatvg =  $totalafec;
        $cabecera->ccatexo =  $totalexo;
        $cabecera->ccatinaf =  $totalinaf;
        $cabecera->ccaigv = $totaligv;
        $cabecera->ccaitv = $total;
        $cabecera->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $cabecera->clicod = $cliente->clicod;
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->IdEmpresa =  $rucemp;
        $cabecera->update();

      


        //Generar el detalle del comprobante

        $iddetalle = $request->get('detalle');

        $registros = DB::tABLE('cpe_detalle')->where('IdCpe_cabecera',$request->get('ped_id'))->get();

        foreach ($registros as $reg) {
            if(!in_array($reg->IdCpe_detalle,$iddetalle)){
              DB::tABLE('cpe_detalle')->where('IdCpe_detalle',$reg->IdCpe_detalle)->delete();
            }
        }


         foreach($proid as $index => $id) {

            $codpro = productos::findOrFail($id);
            $codproducto = $codpro->procod;

            if(!empty($iddetalle[$index])){
                $detalle = cpe_detalle::findOrFail($iddetalle[$index]);
            $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;

            $detalle->umecod = $codpro->umecod;
            $detalle->cdecan = $cantidades[$index];
            $detalle->procod = $codproducto;
            $detalle->IdProducto = $codpro->IdProducto;
            $detalle->cdepsu = "";
            $detalle->cdedes = $detpro[$index];
            $detalle->pronomobs = $pronomobs[$index];
            $detalle->costo = $codpro->costo;
            $detalle->tigcod = $codpro->tigcod;
            $detalle->icbper = $codpro->icbper;
            $detalle->desc_mon  = $descuento[$index];
            if($codpro->tigcod =='10'){
              $preciouni = $puni[$index];
              $valoruni = $puni[$index]/1.1055;
            }elseif($codpro->tigcod=='20'){
              $preciouni = $puni[$index];
              $valoruni = $puni[$index];
            }
              $valorigv = $preciouni - $valoruni;

              $valorsubtotal = $valoruni*$cantidades[$index];
              $valortotal = $preciouni*$cantidades[$index];
              $valorigvtotal =  $valorigv*$cantidades[$index];
          

            $detalle->cdepuni = $preciouni;
            $detalle->cdevun = $valoruni;
            $detalle->cdevve = $valortotal-$descuento[$index];;
            $detalle->cdepve = $valorsubtotal-$descuento[$index];;
            $detalle->cdeigv = $valorigvtotal;
            $detalle->fecha_venta = $fecemi;
            $detalle->update();

          }else{
              $detalle = new cpe_detalle;
            $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;

            $detalle->umecod = $codpro->umecod;
            $detalle->cdecan = $cantidades[$index];
            $detalle->procod = $codproducto;
            $detalle->IdProducto = $codpro->IdProducto;
            $detalle->cdepsu = "";
            $detalle->cdedes = $detpro[$index];
            $detalle->pronomobs = $pronomobs[$index];
            $detalle->costo = $codpro->costo;
            $detalle->tigcod = $codpro->tigcod;
            $detalle->icbper = $codpro->icbper;

            if($codpro->tigcod =='10'){
              $preciouni = $puni[$index];
              $valoruni = $puni[$index]/1.1055;
            }elseif($codpro->tigcod=='20'){
              $preciouni = $puni[$index];
              $valoruni = $puni[$index];
            }
              $valorigv = $preciouni - $valoruni;

              $valorsubtotal = $valoruni*$cantidades[$index];
              $valortotal = $preciouni*$cantidades[$index];
              $valorigvtotal =  $valorigv*$cantidades[$index];
          

            $detalle->cdepuni = $preciouni;
            $detalle->cdevun = $valoruni;
            $detalle->cdevve = $valortotal;
            $detalle->cdepve = $valorsubtotal;
            $detalle->cdeigv = $valorigvtotal;
            $detalle->fecha_venta = $fecemi;
            $detalle->save();

          }
          

          
        }

    

      /*  if($empresa->formato =='TICKET'){         
           
               for($i=1;$i<=$empresa->imp_venta;$i++){
                  if($request->get('opcion')=='0'){
                    self::imprimir($codfact,$tdocod);
                  }
                }
           
        }*/



       //  self::registrarguia($request);


        return response()->json(['pedido'=>$cabecera->numdoc,'estado'=>'success','codfact' =>$cabecera->IdCpe_cabecera,'tdocod'=>$cabecera->tdocod,'mensaje'=>'Comprobante Emitido']);
        
               
    }

 public function buscarpedido(Request $request,$pedido,$tipo){

            $fechaactual = now()->format('Y-m-d');

            if($tipo =='16'){
                 $cabecera = DB::tABLE('cpe_cabecera')
                ->where('numdoc',$pedido)
                ->where('tdocod',$tipo)
                ->where('ccafem',$fechaactual)
                ->first();
            }else{
                 $cabecera = DB::tABLE('cpe_cabecera')
                ->where('numdoc',$pedido)
                ->where('tdocod',$tipo)
                ->first();
            }


            

           
        if(empty($cabecera)){

            return  Redirect::to('/pos')->withErrors(['mensaje' => 'NO SE ENCONTRÓ EL DOCUMENTO']);

        }
      
        if($cabecera->facturado =='1'){

          
         return  Redirect::to('/pos')->withErrors(['mensaje' => 'EL PEDIDO SE ENCUENTRA FACTURADO']);

        }

        if($cabecera->tdocod =='01' || $cabecera->tdocod=='03'){
            if($cabecera->ccacodsun ==='0'){

                
                 return  Redirect::to('/pos')->withErrors(['mensaje' => 'EL COMPROBANTE YA FUE ENVIADO Y ACEPADO POR SUNAT, NO SE PUEDE MODIFICAR']);

           

              }elseif($cabecera->ccacodsun >= 2000 && $cabecera->ccacodsun <= 3999){

                  return  Redirect::to('/pos')->withErrors(['mensaje' => 'EL COMPROBANTE YA FUE ENVIADO Y SE ENCUENTRA ANULADO O RECHAZADO, NO SE PUEDE MODIFICAR']);

                 
              }
        }
    


    

        $mediospagos = DB::tABLE('medios_pagos')->get();

        $clientes = DB::tABLE('cliente')->get();

        $detalle = DB::tABLE('cpe_detalle')
        ->where('IdCpe_cabecera',$cabecera->IdCpe_cabecera)
        ->get();

        $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$cabecera->id_empresa_negocio)->first();

        $almacen = DB::tABLE('almacenes')->where('id_empresa_negocio',$cabecera->id_empresa_negocio)->where('predeterminado','1')->first();

        $vendedores = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        //->where('role_id','5')
        ->get();

        $creditos = DB::tABLE('credito_dias')->get();

        $documentos = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderby('tdicod')->get();

         $docidentidad= DB::tABLE('tipo_documento_identidad')->get();


          $comprobante = DB::tABLE('tipo_documento')->get();

            $senudoc = DB::tABLE('empresa_negocios')
                    ->select('serieguia','numeroguia')
                    ->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)
                    ->first(); 

          $motivos = DB::tABLE('motivo_traslado')
        ->orderBy('motivo','asc')->get();

        $modalidades = DB::tABLE('modalidad_traslado')
        ->orderBy('modalidad','asc')->get();

        $mediospagos = DB::tABLE('medios_pagos')->get();

        $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();

        $unidades = DB::tABLE('unidad_medida')
        ->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

         $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);

          $gastos = DB::tABLE('tipo_gastos')->get();

$tipodocumento = DB::tABLE('tipo_documento_identidad')->get();

        $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$cabecera->id_empresa_negocio)->get();

           $marcas = DB::tABLE('marcas')->get();

        $modelos = DB::tABLE('modelos')->get();

        $tecnicos = DB::tABLE('tecnicos')->get();
         $combustible = DB::tABLE('combustible')->get();

         $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

      
              return view('empresas.puntosventas.divpedido',compact('senudoc','motivos','modalidades','mediospagos','creditos','documentos','almacen','cabecera','detalle','datos','almacenes','vendedores','clientes','mediospagos','unidades','empresa','gastos','docidentidad','tipodocumento','marcas','modelos','tecnicos','combustible','monedas','comprobante'));




    }


    public function modificarpedidos(Request $request,$pedido){

          $fechaactual = now()->format('Y-m-d');

           $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);

        $cabecera = DB::tABLE('cpe_cabecera')
        ->where('numdoc',$pedido)
         ->where('ccafem',$fechaactual)
        ->where('tdocod','16')
        ->first();

       // $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$cabecera->id_empresa_negocio)->first();
        if($cabecera->facturado =='1'){


          
          return  Redirect::to('/pedidos')->withErrors(['mensaje' => 'EL PEDIDO YA SE ENCUENTRA FACTURADO']);

        }

        $mediospagos = DB::tABLE('medios_pagos')->get();

        $clientes = DB::tABLE('cliente')->get();

        $detalle = DB::tABLE('cpe_detalle')
        ->where('IdCpe_cabecera',$cabecera->IdCpe_cabecera)
        ->get();

        $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$cabecera->id_empresa_negocio)->first();

        $almacen = DB::tABLE('almacenes')->where('id_empresa_negocio',$cabecera->id_empresa_negocio)->where('predeterminado','1')->first();

        $vendedores = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
       // ->where('role_id','5')
        ->get();

        $creditos = DB::tABLE('credito_dias')->get();

        $documentos = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderby('tdicod')->get();

         $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();

        $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();

        $unidades = DB::tABLE('unidad_medida')
        ->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();




        $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$cabecera->id_empresa_negocio)->get();

     
        return view('empresas.puntosventas.divmodificarpedido',compact('tipodocumento','creditos','documentos','almacen','cabecera','detalle','datos','almacenes','vendedores','clientes','mediospagos','unidades','empresa'));

     
    }


    //FUNCION PARA ALBERGUES

    public function registrarpedidos(Request $request){
      
      $IdProducto = $request->get('IdProducto');
      $cantidad = $request->get('cantidad');
      $fecha = $request->get('fecEmi');
      $servicio = $request->get('servicio');
      $prog_id = $request->get('prog_id');

      $almacen = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();

      $validar = DB::tABLE('pedido_servicio_cab')
      ->where('ped_ser_fec',$fecha)
      ->where('ser_cod',$servicio)
      ->where('prog_id',$prog_id)
      ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->get();




      if(count($validar)=='0'){
     
          foreach ($IdProducto as $index => $id){

           $buscar = DB::tABLE('pedido_servicio_cab')
           ->where('ser_cod',$servicio)
           ->where('ped_ser_fec',$fecha)
           ->where('prog_id',$prog_id)
           ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
           ->where('IdProducto',$id)
           ->get();



           if(count($buscar)=='0'){


              DB::tABLE('pedido_servicio_cab')->insert([
                'ped_ser_fec'=>$fecha,
                'ser_cod'=>$servicio,
                'total'=>$cantidad[$index],
                'IdProducto'=>$id,
                'id_empresa_negocio'=>Auth::user()->id_empresa_negocio,
                'prog_id'=>$prog_id
              ]);

            }else{

              DB::tABLE('pedido_servicio_cab')
              ->where('ped_ser_id',$buscar->ped_ser_id)
              ->update([
                'total'=>$buscar->total+$cantidad[$index]
              ]);

            }

            $receta = DB::tABLE('recetas')->where('prod_id',$IdProducto)->get();

            foreach ($receta as $i => $rec) {
              
              $buscarstock = DB::tABLE('producto_stock')
              ->where('IdProducto',$rec->prod_insu)
              ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
              ->where('id_almacen',$almacen->id_almacen)
              ->first();

              $act_stock = DB::tABLE('producto_stock')
              ->where('pro_sto_id',$buscarstock->pro_sto_id)
              ->update(['stock'=>$buscarstock->stock-($rec->rec_cant*$cantidad[$index])]);

            }
          }
       

            return response()->json(['estado'=>'success']);

      }else{


        return response()->json(['estado'=>'error','mensaje'=>'EXISTE UN REGISTRO PARA ESE SERVICIO EN LA FECHA INDICADA - SELECCIONAR OTRA FECHA O INGRESAR EN OPCION EDITAR']);
      }
     
    

 
        
      

    }


    public function puntoventa(Request $request){

        $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first(); 

        $creditos = DB::tABLE('credito_dias')->get();

        $documentos = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderby('tdicod')->get();

        $rucemp = trim(Auth::user()->IdEmpresa);

        $categorias = DB::tABLE('categorias')
      //  ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->orderby('cat_nom','asc')
        ->get();

        $clientes = DB::tABLE('cliente')->get();

        $comprobante = DB::tABLE('tipo_documento')->get();

        $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();

        $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();

        $unidades = DB::tABLE('unidad_medida')
        ->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $mozos = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        ->where('role_id','5')
        ->get();

         $senudoc = DB::tABLE('empresa_negocios')
                    ->select('serieguia','numeroguia')
                    ->where('IdEmpresa','=',$rucemp)
                    ->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)
                    ->first(); 

          $motivos = DB::tABLE('motivo_traslado')
        ->orderBy('motivo','asc')->get();

        $modalidades = DB::tABLE('modalidad_traslado')
        ->orderBy('modalidad','asc')->get();

        $mediospagos = DB::tABLE('medios_pagos')->get();

         // consultar tipos de documentos de identidad
        $docidentidad = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

        //consultar tipo de documento 
        $doccomprobante = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->orderBy('tdocod','asc')->get();

         // consultar tipos de monedas
        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();


        return view('empresas.puntosventas.puntoventa',compact('categorias','comprobante','tipodocumento','igv','unidades','unidades','mozos','creditos','mediospagos','clientes','documentos','datos','senudoc','motivos','modalidades','docidentidad','doccomprobante','monedas'));

    }

    public function registrarguia(Request $request){


      //SI ES TRANSPORTE PUBLICO  DATOS DE TRANSPORTISTA

      //SI ES TRASPORTE PRIVADO  DATOS DE CONDUCTOR

      //Empresa que emite el comprobante
      $rucemp = trim(Auth::user()->IdEmpresa);

      $sercomp= $request->get('serdocguia');
      $numcomp= $request->get('numdocguia');
      $fecemi = $request->get('fecEmiguia');
      $fechatraslado = $request->get('fechatraslado');
      $motivo = $request->get('motivo');
      $modalidad = $request->get('modalidad');
      $bultos = $request->get('bultos');
      
      $tdicod = $request->get('tdicod');
      $cliruc = $request->get('clinum');
      $clinom = $request->get('clinom');
      $clidir = $request->get('clidir');
      $tdicodtransportista = $request->get('transportistatdicod');
      $ructransportista = $request->get('transportistanum');
      $nombretransportista = $request->get('transportistanom');

      $estadostock = $request->get('estadostock');

      $tdicodconductor = $request->get('conductortdicod');
      $rucconductor = $request->get('conductornum');
      $nombreconductor = $request->get('conductornom');
      $placa = $request->get('placa');

      $bus_alm = DB::tABLE('almacenes')->where('id_almacen',$request->get('id_almacen'))->first();

        if(empty($request->get('direccionllegada'))){
            $ubigeollegada = $request->get('ubigeollegada');
            $direccionllegada = $clidir;
            $desubigeollegada = $request->get('desubigeollegada');
            $correo = $request->get('correo');
        }else{
            $ubigeollegada = $request->get('ubigeollegada');
            $direccionllegada = $request->get('direccionllegada');
            $desubigeollegada = $request->get('desubigeollegada');
            $correo = $request->get('correo');
        }


       
            $ubigeopartida = $bus_alm->ubigeo;
           $direccionpartida =$bus_alm->direccion;
           $desubigeopartida = $request->get('desubigeopartida');

      


   
    
        

        $unidades = $request->get('unid');
        $codpro = $request->get('proid');
        $detpro = $request->get('pronom');
        $cantidades = $request->get('cant');
      

        $busmotivo = DB::tABLE('motivo_traslado')->where('IdMotivo',$motivo)->first();
        
        $numdoc = str_pad($request->get('numdoc'),8,"0", STR_PAD_LEFT);
        

      //Registrar el cliente enviado a través del formulario si no existe
        $cliente = Cliente::FirstOrCreate(['clinum'=>$cliruc],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$correo,'rucemp'=>$rucemp,'tdicod'=>$tdicod]);
        
       
        
        
      //Crear un objeto de la cabecera del comprobante para registrar los datos de la cabecera
        $cabecera = new guias_remision;
        $cabecera->IdEmpresa =  Auth::user()->IdEmpresa;
        $cabecera->tdocod = '09';
        $cabecera->fechaemision = $fecemi;
        $cabecera->tdicod = $tdicod;
        $cabecera->ruccliente = $cliruc;
        $cabecera->ubigeollegada = $ubigeollegada;
        $cabecera->direccionllegada = $direccionllegada;
        $cabecera->IdMotivo = $motivo;
        $cabecera->pesobruto = '0.00';
        $cabecera->umecod = 'KMG';
        $cabecera->fechatraslado = $fechatraslado;

        $cabecera->ructransportista = $ructransportista;
        $cabecera->nombretransportista = $nombretransportista;
        $cabecera->tdicodtransportista = $tdicodtransportista;
         $cabecera->desubigeollegada = $desubigeollegada;

        $cabecera->rucconductor = $rucconductor;
        $cabecera->nomconductor = $nombreconductor;
        $cabecera->tdicodconductor = $tdicodconductor;

        $cabecera->ubigeopartida = $ubigeopartida;
        $cabecera->direccionpartida = $direccionpartida;
        $cabecera->desubigeopartida = $desubigeopartida;
      
        $cabecera->bultos = $bultos;
        $cabecera->correo = $correo;
        $cabecera->placa = $placa;
        $cabecera->id_empresa_negocio =  Auth::user()->id_empresa_negocio;
      //  $cabecera->datajson = $request->get('tdocod');
        $cabecera->IdModalidad = $modalidad;
  
        $cabecera->nomcliente= $clinom;

    
        $empresa = Empresa::findOrFail($rucemp);
        $empresanegocio = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);
     
        $formato = $empresa->formato;
 
         $empresaguia = Empresa::findOrFail($rucemp);

          $empresanegocio = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);
     
          $formato = $empresaguia->formato;
     
          if( $empresanegocio->numeroguia == $numcomp){
              $modnumcomp = $numcomp+1;
          }else{
              $modnumcomp = $numcomp;
          }

          $empresanegocio->serieguia = $sercomp;
          $empresanegocio->numeroguia = $modnumcomp;
          // $empresanegocio->update();

          $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
          $cabecera->serieguia= $sercomp;
          $cabecera->numeroguia = $numdoc;
          //$cabecera->save();
          
          $empresanegocio->update();
          $cabecera->save();
          $codfact = $cabecera->IdCpe_guia; 

            $i=0;

        foreach( $unidades as $index => $ume ) {
            
            $i=$i+1;
            $codproducto = $codpro[$index];
            
            /*$IdProducto = DB::tABLE('productos')
            ->WHERE('procod',$codproducto)
            ->where('IdEmpresa',$rucemp)
            ->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)
            ->first();*/

            $codproducto = $codpro[$index];

            $detalle = new guias_remision_detalle;
            //$detalle->IdProducto = $IdProducto->IdProducto;
            //$detalle->procod = $codproducto;
            $detalle->pronom = $detpro[$index];
            $detalle->cantidad = $cantidades[$index];
            $detalle->peso ="0.00";
            $detalle->umecod = $ume;
            $detalle->IdCpe_guia =  $cabecera->IdCpe_guia; 
          

            //Guardar en una variable los items en el archivo .det
            $codumecin = unidad_medida::findOrFail($ume);
         
          
            $detalle->save();


        }

        $cabfile =  $cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.json';
        $filepdf=  $cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.pdf';
        $filecdr =  'R-'.$cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.xml';
        $filexml =  $cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.xml';
        $filecdrzip =  'R-'.$cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.zip';
        $filexmlzip =  $cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.zip';

        $raiz = '/opt/data/comprobantes/'.$rucemp.'/json/';
        $rutacdr = '/opt/data/comprobantes/'.$rucemp.'/cdr/';
        $rutaxml = '/opt/data/comprobantes/'.$rucemp.'/xml/';
        $rutapdf = '/opt/data/comprobantes/'.$rucemp.'/pdf/';

        self::generar_xml_guia($cabecera->IdCpe_guia);
        self::generarpdfguia($cabecera->IdCpe_guia);
     
       return response()->json(['estado'=>'registrado','mensaje'=>'REGISTRADO']);
     
  }

     public function generar_xml_guia($codfact){

        $cabecera = DB::tABLE('guias_remision')
        //->leftjoin('cliente','cliente.clicod','guias_remision.clicod')
        ->where('IdCpe_guia',$codfact)->first();
        
        $detalles = DB::tABLE('guias_remision_detalle')
        ->leftjoin('productos','productos.IdProducto','guias_remision_detalle.IdProducto')
        ->where('IdCpe_guia',$codfact)
        ->get();


        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

         
          $numdoc = str_pad($cabecera->numeroguia,8,"0", STR_PAD_LEFT);

        $empresa = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

        $rutxmlfile = public_path().'/xml/';
        $rutcdrfile = public_path().'/cdr/';

        $nomxmlcdr=   Auth::user()->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serieguia.'-'.$numdoc;

        $motivo = DB::tABLE('motivo_traslado')->where('IdMotivo',$cabecera->IdMotivo)->First();
        
        $modalidad = DB::tABLE('modalidad_traslado')->where('IdModalidad',$cabecera->IdModalidad)->First();
          

        $filexml =  Auth::user()->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serieguia.'-'.$numdoc.'.xml';
        $filecdrzip =  'R-'.Auth::user()->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serieguia.'-'.$numdoc.'.zip';

    //  $see = self::configuracion();
        
        $rel = new Document();
        $rel->setTipoDoc($cabecera->tdocod) // Tipo: Numero de Orden de Entrega
        ->setNroDoc($cabecera->serieguia.'-'.$cabecera->numeroguia);

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
        
        $transp = new Transportist();
        $transp->setTipoDoc($cabecera->tdicodtransportista)
            ->setNumDoc($cabecera->ructransportista)
            ->setRznSocial($cabecera->nombretransportista)
            ->setPlaca($cabecera->placa)
            ->setChoferTipoDoc($cabecera->tdicodconductor)
            ->setChoferDoc($cabecera->rucconductor);

        $envio = new Shipment();
        $envio->setCodTraslado($cabecera->IdMotivo) // Cat.20
            ->setDesTraslado($motivo->motivo)
            ->setModTraslado($cabecera->IdModalidad) // Cat.18
            ->setFecTraslado(new \DateTime($cabecera->fechatraslado))
            ->setCodPuerto('')
            ->setIndTransbordo(false)
            ->setPesoTotal(0)
            ->setUndPesoTotal('KGM')
        //    ->setNumBultos(2) // Solo válido para importaciones
            ->setNumContenedor('')
            ->setLlegada(new Direction($cabecera->ubigeollegada, $cabecera->direccionllegada))
            ->setPartida(new Direction($cabecera->ubigeopartida, $cabecera->direccionpartida))
            ->setTransportista($transp);

        $despatch = new Despatch();
        $despatch->setTipoDoc($cabecera->tdocod)
            ->setSerie($cabecera->serieguia)
            ->setCorrelativo($cabecera->numeroguia)
            ->setFechaEmision(new \DateTime($cabecera->fechaemision))
            ->setCompany($company)
            ->setDestinatario((new Client())
                ->setTipoDoc($cabecera->tdicod)
                ->setNumDoc($cabecera->ruccliente)
                ->setRznSocial($cabecera->nomcliente))
           /* ->setTercero((new Client())
                ->setTipoDoc()
                ->setNumDoc()
                ->setRznSocial())*/
            ->setObservacion('')
            ->setRelDoc($rel)
            ->setEnvio($envio);
      
        $items = [];
        foreach ($detalles as $detalle) {

        
          $item = (new DespatchDetail())
            ->setCodigo($detalle->procod)
            ->setUnidad($detalle->umecod)
            ->setCantidad($detalle->cantidad)
            ->setDescripcion($detalle->pronom.' | '.$detalle->marca.' | '.$detalle->modelo)
           // ->setCodProdSunat()
            ;

        

          $items[] = $item;
        }


 
        $despatch->setDetails($items);
     

          $builder = new  DespatchBuilder();

          $xml = $builder->build($despatch);

          $nom_xml = $despatch->getName();

          file_put_contents(public_path().'/xml/'.$nom_xml.'.xml',$xml);

          $firmar_xml = new cpe_cabecera;
          $firmar_xml->firmar_xml($nom_xml);


          $obt_xml = file_get_contents(public_path().'/xml/'.$nom_xml.'.xml');

          $actualizar = guias_remision::findOrFail($codfact);
                
        $params = [
          $actualizar->IdEmpresa,
          $actualizar->tdocod,
          $actualizar->serieguia,
          $actualizar->numeroguia,
          $actualizar->ccafem,
          $actualizar->tdicod,
          $actualizar->ruccliente,
        ];

        $content = implode('|', $params).'|';

        $DOM = new DOMDocument('1.0', 'utf-8');
        $DOM->loadXML($obt_xml);
        $hash = $DOM->getElementsByTagName('DigestValue')->item(0)->nodeValue;
        $actualizar->codhash = $hash;
        $actualizar->ccaqr = $content;
        $actualizar->update();


       
        return "success";

      }    

    


       public function generarpdfguia($venta){


      $rucemp =Auth::user()->IdEmpresa;
      $rutapdf = public_path().'/pdf/';;

      $empresa = Empresa::findOrFail($rucemp);

      $sucursal = DB::tABLE('empresa_negocios')
      ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
     

      $cabpdf = DB::tABLE('guias_remision')->select('bultos','placa','licencia','nomconductor','motivo','modalidad','fechatraslado','nomcliente','nomcliente','direccionllegada','direccionpartida','ul.ubi_des as ubillegada','up.ubi_des as ubipartida','placa','pesobruto','rucconductor','ruccliente','guias_remision.tdocod','numeroguia','serieguia','codhash','tdodes','tdides')
      //->leftjoin('moneda as mon','guias_remision.moncod','=','mon.moncod')
      ->leftjoin('tipo_documento','tipo_documento.tdocod','guias_remision.tdocod')
       ->leftjoin('tipo_documento_identidad','tipo_documento_identidad.tdicod','guias_remision.tdicod')
      ->leftjoin('modalidad_traslado','modalidad_traslado.IdModalidad','guias_remision.IdModalidad')
      ->leftjoin('cat_ubigeo as ul','ul.ubi_cod','guias_remision.ubigeopartida')
      ->leftjoin('cat_ubigeo as up','up.ubi_cod','guias_remision.ubigeollegada')
      ->leftjoin('motivo_traslado','motivo_traslado.IdMotivo','guias_remision.IdMotivo')
      ->where('IdCpe_guia',$venta)
      ->first();

     /* $vehiculo = DB::tABLE('tipos_vehiculos')
      ->leftjoin('marcas','marcas.mar_id','tipos_vehiculos.mar_id')
      ->leftjoin('modelos','modelos.mod_id','tipos_vehiculos.mod_id')
      ->where('placa',$cabpdf->placa)->first();*/

      $detpdf = DB::tABLE('guias_remision_detalle')
     // ->leftjoin('productos','productos.IdProducto','guias_remision_detalle.IdProducto')
      ->leftjoin('unidad_medida','unidad_medida.umecod','guias_remision_detalle.umecod')
      ->where('IdCpe_guia',$venta)->get();

      $cliente= DB::tABLE('cliente as cli')
      ->leftjoin('guias_remision as c','c.ruccliente','=','cli.clinum')
      ->where('IdCpe_guia','=',$venta)
      ->where('cli.clinum','=',$cabpdf->ruccliente)
      ->first();
                  
      $nompdffile=$rucemp.'-'.$cabpdf->tdocod.'-'.$cabpdf->serieguia.'-'.str_pad($cabpdf->numeroguia,8,"0", STR_PAD_LEFT).'.pdf'; 


    //  $numdoc = str_pad($cabpdf->numeroguia,8,"0", STR_PAD_LEFT);
      $numdoc = $cabpdf->numeroguia;

      $qrfile =  'QR-'.$rucemp.'-'.$cabpdf->tdocod.'-'.$cabpdf->serieguia.'-'.$numdoc.'.png'; 

      $imgqr = "/qr/".$qrfile;

        
        
      $view = \View::make('formatos_comprobantes.A4_guia', compact('cabpdf','detpdf','cliente','empresa','imgqr','sucursal','qrfile'));

                  
      $pdf = \App::make('dompdf.wrapper');
      $contenido = $view->render();
      $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

    
  
        return 'realizado';
    }


    public function ingresomozo(Request $request){


      $usuarios = DB::tABLE('users')->get();


      foreach ($usuarios as $usuario ){

       

          if (Hash::check($request->get('password'),$usuario->password)){


              return Redirect::to('/mesas/'.$usuario->IdUsuario.'/'.$usuario->id_empresa_negocio);

          }else{

            

          }

      }
   

    }

    public function mesas($usuario,$sucursal){


    

      $primer_piso = DB::tABLE('pisos')
      ->where('suc_id',$sucursal)
      ->first();

      if(!empty($primer_piso)){

        $mesas = DB::tABLE('mesas')
        ->join('pisos','pisos.pis_id','mesas.pis_id')
        ->where('mesas.pis_id',$primer_piso->pis_id)
        ->where('IdUsuario',$usuario)
        ->orderby('mesas.mes_nom','asc')
        ->get();

    
      }
      

      $users = DB::tABLE('users')->get();

      $pisos = DB::tABLE('pisos')
      ->where('suc_id',$sucursal)
      ->get();
 
      return view('empresas.mesas',compact('mesas','primer_piso','pisos','users'));

    }

    public function configuracion(){
    
      $rucemp = trim(Auth::user()->IdEmpresa);
      $empresa = Empresa::findOrFail($rucemp);
      $usuario = $rucemp.$empresa->wsusuario;
      $contrasena = $empresa->claveSunat;

      $rutacertificado = public_path().'/certificados/'.$rucemp.'.pem';

      $see = new See();

      if($empresa->produccion=='1'){
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
               ->leftjoin('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
               ->leftjoin('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
               ->leftjoin('moneda as mon','cpe_c.moncod','=','mon.moncod')
               ->leftjoin('cliente','cliente.clicod','cpe_c.clicod')
               ->where('cpe_c.ccafem','>=',$fecin)
               ->where('cpe_c.ccafem','<=',$fecfin)
               ->whereNull('ccabaj')
                 ->where(function ($query) {
                        $query->where('cpe_c.tdocod','01')
                            ->orWhere('cpe_c.tdocod','03')
                            ->orWhere('cpe_c.tdocod','07')
                           ->orWhere('cpe_c.tdocod','08');
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
                        $query2->where('cpe_c.ccacodsun','<>','0')
                        ->orwhereNull('cpe_c.ccacodsun');

                    }
                })

               ->orderby('IdCpe_cabecera','desc')
               ->paginate(1000);

          
            return view('empresas.comprobantes.indexfacturacion',compact('comprobantes','empresa','doccomprobante','fecfin','fecin','documentos','tipdoc'));


    }


    public function listarresumenes(Request $request){

        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
        $fechaini = now()->modify('first day of this month')->format('Y-m-d');
        $fechafin = now()->modify('last day of this month')->format('Y-m-d');
      
        $comprobantes = DB::tABLE('resumenes')
        ->orderby('res_id','desc')
        ->where('res_fec_com','>=',$fechaini)
        ->where('res_fec_com','<=',$fechafin)
        ->paginate(100);

        return view('empresas.comprobantes.indexresumen',compact('comprobantes','empresa'));


    }


    public function buscarresumenes(Request $request){

        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
        $fechaini = $request->get('fecin');
        $fechafin = $request->get('fecfin');
      
        $comprobantes = DB::tABLE('resumenes')
        ->orderby('res_id','desc')
        ->where('res_fec_com','>=',$fechaini)
        ->where('res_fec_com','<=',$fechafin)
        ->paginate(100);

        return view('empresas.comprobantes.indexresumen',compact('comprobantes','empresa'));


    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $rucemp = trim(Auth::user()->IdEmpresa);
        $categorias = DB::tABLE('categorias')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->orderby('cat_nom','asc')
        ->where('tipo','=',null)
        ->get();

        $comprobante = DB::tABLE('tipo_documento')->get();
        $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();

        return view('empresas.puntosventas.restaurant',compact('categorias','comprobante','tipodocumento'));
    }

    /**
     * Show the form for creating a new resofgenurce.
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

            //NOMBRE COMERCIAL
            //$printer->text($empresanegocios->nombre_comercial."\n");

             //NOMBRE EMPRESA
            //$printer->text($empresanegocios->NomEmpresa."\n");

            $printer->setFont(Printer::FONT_A);

            //DIRECCION DE LA EMPRESA
            $printer->text($empresanegocios->direccion."\n");

            //UBIGEO DEL CLIENTE DEPARTAMENTO-PROVINCIA-DISTRITO
            $printer->text($empresanegocios->departamento."-".$empresanegocios->provincia."-".$empresanegocios->distrito."\n"."\n");

            //TELEFONO DEL CLIENTE
            if(!empty($empresanegocios->telefono)){
             $printer->text($empresanegocios->telefono."\n"."\n");   
            }
            
            //RUC DE LA EMPRESA
            $printer->text('RUC:'.$empresa->IdEmpresa."\n");
		}
		
        //NOMBRE DEL TIPO DE COMPROBANTE
        $printer->text($nomdoc->tdodes."\n");

        //SERIE Y NUMERO DE COMPROBANTE (serdoc=serie  y numdoc=numero)
        $printer->text($cabecera->serdoc."-".$cabecera->numdoc."\n"."\n");

        //NOMBRE DEL VENDEDOR
        $printer->text("Vendedor: ".$cabecera->vendedor."\n"."\n");
		
        //DATOS DEL CLIENTE
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("Fecha:       ".$cabecera->fecha_hora."\n");
        $printer->text("RUC/DNI:     ".$cabecera->clinum."\n");
        $printer->text("Cliente:     ".$cabecera->ccanom."\n");
        $printer->text("Dirección:   ".$cabecera->direccion."\n"."\n");
    
        //DETALLE DE LOS PRODUCTOS QUE SE VENDEN
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("CODIGO    DESCRIPCION "."\n");
        $printer->text("CANTIDAD    UNIDAD    PRECIO    TOTAL"."\n");
        $printer->text("________________________________________________"."\n");
        foreach ($detalle as $det){
            if($det->cdevve > 0){
                $printer->text($det->procod."  ".$det->cdedes."\n");
                $printer->text($det->cdecan."       ".$det->umecod."       ".$det->cdepuni."       ".$det->cdevve."\n\n");
            }else{
                $printer->text($det->cdedes."\n");
            } 
        }

        $printer->text("\n");
        $printer->text("_______________________________________________"."\n");

        //MEDIOS DE PAGO
        foreach ($medios as $m) {
            $printer->text($m->nom_med_pag." ".$cabecera->simbolo."                        ".$m->monto."\n");
        }

        if($cabecera->tdocod !='13'){  
            $printer->text("SUBTOTAL: ".$cabecera->simbolo."                       ".$cabecera->ccatvg."\n");
            //$printer->text("OP. GRAVADA: ".$cabecera->simbolo."                    ".$cabecera->ccatvg."\n");
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
           

      }
  }
  



    




     public function registrarsalida(Request $request)
    {

 
     
        $almacen = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();

       //Datos de cabecera
        $tdicod = $request->get('tdicod');
        $tdocod = $request->get('tdocod');
        $mondoc = $request->get('moncod');
        $total = $request->get('total');
        $fecemi = $request->get('fecEmi');
        $ade_pro = $request->get('ade_pro');
        $sal_pro = $request->get('sal_pro');
        $fecven = $request->get('fecVen');
        $tipo_venta = $request->get('tipoventa');
        $observaciones = $request->get('observaciones');
        $pagar = $request->get('pagar');
        $vuelto = $request->get('vuelto');
      //medios de pago
        $monto = $request->get('monto');
        $medio = $request->get('medio');


      //estado de pago
        $estadopago = $request->get('estadopago');

        $buscre = DB::tABLE('credito_dias')->where('cre_dia_id',$estadopago)->first();

      //Datos del Cliente
        $cliruc = $request->get('clinum');
        $fecha = $request->get('fecEmi');
        $clinom = $request->get('clinom');
        $clidir = $request->get('clidir');
        $clicor = $request->get('clicor');

        $rucemp = trim(Auth::user()->IdEmpresa);

        $empresa = Empresa::findOrFail($rucemp);
        //DETALLE 
        $unidades = $request->get('unid');
        $proid = $request->get('proid');
        $detpro = $request->get('pronom');
        $vunit = $request->get('provun');
        $puni = $request->get('propun');
        $vtot = $request->get('itemtotal');
        $cantidades = $request->get('cant');
        $pronomobs = $request->geT('pronomobs');

        $total =0;
        $totalexo = 0;
        $totalafec = 0;
        $totalinaf =0;
        $totaligv =0;
        $totalsubt = 0;
        $totalicbper =0;

    
     

        $topcod = '0101';


       
          $senudoc = DB::tABLE('empresa_negocios')->select('serNS','numNS')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
          $numcomp =  $senudoc->numNS+1;
          $sercomp =  $senudoc->serNS;
        


        $moneda = DB::tABLE('moneda')->where('moncod','=',$mondoc)->first();

    

       $oldDate = strtotime($request->get('clifecnac'));

        $mes = date('m',$oldDate);

      //Crear un objeto de la cabecera del comprobante para registrar los datos de la cabecera
        $cabecera = new cpe_cabecera;
        $cabecera->tdocod = '81';
        $cabecera->topcod = $topcod;
        $cabecera->ccafem = $fecemi;


     
        $cabecera->id_empresa_negocio = Auth::user()->id_empresa_negocio;
    
        

        $cabecera->usu_rec = $request->get('clicod');
        $cabecera->are_emp_id = $request->get('area');
      
        $cabecera->ccaobs = $observaciones;
        $cabecera->paga = $pagar;
   
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->IdEmpresa =  $rucemp;

        $empresa = Empresa::findOrFail($rucemp);
        $empresanegocio = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);
     
        $formato = $empresa->formato;
        if($tdocod=='81'){
          if( $empresanegocio->serNS == $numcomp){
              $modnumcomp = $numcomp+1;
          }else{
              $modnumcomp = $numcomp;
          }

          $empresanegocio->serNS = $sercomp;
          $empresanegocio->numNS = $modnumcomp;
         // $empresanegocio->update();

          $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
          $cabecera->serdoc= $sercomp;
          $cabecera->numdoc = $numdoc;
          //$cabecera->save();
        }
           $empresanegocio->update();

          $empresa->update();
          $cabecera->save();

          $codfact = $cabecera->IdCpe_cabecera; 
       
     
      


        //Generar el detalle del comprobante

         foreach($proid as $index => $id) {

            $codpro = productos::findOrFail($id);
            $codproducto = $codpro->procod;

            $detalle = new cpe_detalle;
            $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
            $detalle->umecod = $codpro->umecod;
            $detalle->cdecan = $cantidades[$index];
            $detalle->procod = $codproducto;
            $detalle->IdProducto = $codpro->IdProducto;
            $detalle->cdepsu = "";
            $detalle->cdedes = $detpro[$index];
            $detalle->pronomobs = $pronomobs[$index];
            $detalle->costo = $codpro->costo;
            $detalle->tigcod = $codpro->tigcod;
            $detalle->icbper = $codpro->icbper;

            if($codpro->tigcod =='10'){
              $preciouni = $puni[$index];
              $valoruni = $puni[$index]/1.1055;
            }elseif($codpro->tigcod=='20'){
              $preciouni = $puni[$index];
              $valoruni = $puni[$index];
            }
              $valorigv = $preciouni - $valoruni;

              $valorsubtotal = $valoruni*$cantidades[$index];
              $valortotal = $preciouni*$cantidades[$index];
              $valorigvtotal =  $valorigv*$cantidades[$index];
           

            $detalle->cdepuni = $preciouni;
            $detalle->cdevun = $valoruni;
            $detalle->cdevve = $valortotal;
            $detalle->cdepve = $valorsubtotal;
            $detalle->cdeigv = $valorigvtotal;
            $detalle->fecha_venta = $fecemi;
            $detalle->save();



              
                if(empty($codpro->pro_rel)){

                  $id_prod = $codpro->IdProducto;

                }else{
          
                  $id_prod = $codpro->pro_rel;

                }

                $stockprod = DB::tABLE('producto_stock')
                ->where('IdProducto',$id_prod)
                ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
                ->where('id_almacen',$almacen->id_almacen)
                ->first();

                if(empty($stockprod)){

                  $stock = 0-($cantidades[$index]*$codpro->factor);

                  $stockprod_act = DB::tABLE('producto_stock')
                  ->insert([
                    'stock'=>$stock,
                    'IdProducto'=>$id_prod,
                    'id_empresa_negocio'=>Auth::user()->id_empresa_negocio,
                    'id_almacen'=>$almacen->id_almacen]
                  );

                  
                }else{

                  $stockprod_act = DB::tABLE('producto_stock')
                  ->where('pro_sto_id',$stockprod->pro_sto_id)
                  ->update(['stock'=>$stockprod->stock-($cantidades[$index]*$codpro->factor)]);

                  $stock = $stockprod->stock-($cantidades[$index]*$codpro->factor);


                }

            
               

                $movimiento = new movimientos;
                $movimiento->mov_fec = $fecha; 
                $movimiento->mov_tip = 'E';
                $movimiento->mov_mot = 'Venta';
                $movimiento->cantidad = $cantidades[$index];
                $movimiento->unidad = $codpro->umecod;
                $movimiento->comprobante = $sercomp.'-'.$numdoc;
                $movimiento->IdEmpresa = $rucemp;
                $movimiento->id_empresa_negocio = Auth::user()->id_empresa_negocio;
                $movimiento->IdProducto = $id;
                $movimiento->observacion = "Venta desde Punto de Venta";
                $movimiento->IdUsuario = Auth::user()->IdUsuario;
                $movimiento->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
                $movimiento->stockmov = $stock;
                $movimiento->save();


     
          
        }

     

     /*   if($empresa->formato =='TICKET'){         
            if(empty($cabecera->referencia)){
               for($i=1;$i<=$empresa->imp_venta;$i++){
                  if($request->get('opcion')=='0'){
                    self::imprimir($codfact,$tdocod);
                  }
                }
            }else{
              $documento = self::generarpdfgeneral($codfact);
            }

        }elseif($empresa->formato=='A4'){
        
           $documento = self::generarpdfgeneral($codfact);
         
        }


       if(!empty($documento)){

        if($request->get('opcion')=='0'){
          exec("c:\WINDOWS\system32\cmd.exe /c START powershell.exe -ExecutionPolicy Bypass Start-Process -FilePath E:\laragon\www\sys_mypesoft\public\pdf/".$documento." -Verb Print");
        }

        }*/
       
     


        return response()->json(['estado'=>'success']);
        
               
    }






    
  
    public function enviarcorreos(){

      $cabecera = DB::tABLE('cpe_cabecera')->whereNotNull('clicorcli')->get();

      foreach ($cabecera as $cab) {
           self::enviar_comprobante($cab->IdCpe_cabecera,$cab->clicorcli);
      }
     

    }

    public function registrarventa(Request $request)
    {

        $cot = $request->get('id');

        $tip_cam = DB::tABLE('tipocambio')->where('FecTipCambio',now()->format('Y-m-d'))->first();
        
        if(!empty($cot)){
          $bus_cot = cpe_cabecera::findOrFail($cot);
          $bus_cot->estado = 'ACEPTADO';
          $bus_cot->update();
        }
       //Datos de cabecera
        $tdicod = $request->get('tdicod');
        $tdocod = $request->get('tdocod');
        $mondoc = $request->get('moncod');
        $total = $request->get('total');
        $fecemi = $request->get('fecEmi');
        $fecven = $request->get('fecVen');
        $tipo_venta = $request->get('tipoventa');
        $observaciones = $request->get('observaciones');
        $pagar = $request->get('pagar');
        $vuelto = $request->get('vuelto');
      //medios de pago
        $monto = $request->get('monto');
        $medio = $request->get('medio');


      //estado de pago
        $estadopago = $request->get('estadopago');

        $buscre = DB::tABLE('credito_dias')->where('cre_dia_id',$estadopago)->first();

      //Datos del Cliente
        $cliruc = $request->get('clinum');
        $fecha = $request->get('fecEmi');
        $clinom = $request->get('clinom');
        $clidir = $request->get('clidir');
        $clicor = $request->get('clicor');
        $clicor2 = $request->get('clicor2');
        $clicor3 = $request->get('clicor3');
        $clicor4 = $request->get('clicor4');


      
        if(!empty($request->get('id'))){

            $cotizacion = cpe_cabecera::findOrFail($request->get('id'));
            $cotizacion->estado ='ACEPTADO';
            $cotizacion->update();
        }

        $rucemp = trim(Auth::user()->IdEmpresa);

        $empresa = Empresa::findOrFail($rucemp);
        //DETALLE 
        $unidades = $request->get('unid');
        $proid = $request->get('proid');
        $detpro = $request->get('pronom');
        $vunit = $request->get('provun');
        $puni = $request->get('propun');
        $vtot = $request->get('itemtotal');
        $cantidades = $request->get('cant');
        $pronomobs = $request->geT('pronomobs');

        $total =0;
        $totalexo = 0;
        $totalafec = 0;
        $totalinaf =0;
        $totaligv =0;
        $totalsubt = 0;
        $totalicbper =0;


         foreach($unidades as $index => $uni ) {

            $tigcod ='20';
        
            $total = $total + ($cantidades[$index]*$puni[$index]);


            if($tigcod =='10'){

             
              $totalafec = ($total / 1.18);
              $totaligv = $total-$totalafec;


            }elseif($tigcod =='20'){

              $totalexo = $total;
              $totaligv =  $total-$totalexo;

            }elseif($tigcod =='30'){

              $totalinaf = $total;
              $totaligv =  $total-$totalinaf;

            }

        }

        $tot_pago = 0;

        foreach ($monto as $mon){

            $tot_pago = $tot_pago + $mon; 

        }


        /*if(( $buscre->cre_dia_tip =='CONTADO' && $tdocod !='15')){
          if(number_format($total,'2','.',',') < number_format($tot_pago,'2','.',',')){

             return response()->json(['estado'=>'error','mensaje'=>'LA SUMA DE MEDIOS DE PAGO NO COINCIDE CON EL TOTAL']);
   

          }elseif(number_format($total,'2','.',',') > number_format($tot_pago,'2','.',',')){

               return response()->json(['estado'=>'error','mensaje'=>'LA SUMA DE MEDIOS DE PAGO NO COINCIDE CON EL TOTAL']);
   

          }
        }*/
      
     

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
        }elseif ($tdocod =='15') {
          $senudoc = DB::tABLE('empresa_negocios')->select('ProNum','ProSer')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
          $numcomp =  $senudoc->ProNum+1;
          $sercomp =  $senudoc->ProSer;
        }

        $moneda = DB::tABLE('moneda')->where('moncod','=',$mondoc)->first();

      //Registrar el tipo de cambio enviado a través del formulario si no existe.
        if($mondoc !='PEN'){
          $camdoc = $request->get('camdoc');
          $tipcam = tipocambio::FirstOrCreate(['FecTipCambio'=>$fecemi],['CamCompra'=>$camdoc]);
        }else{
          $camdoc=0;
        }

       $oldDate = strtotime($request->get('clifecnac'));

        $mes = date('m',$oldDate);


      //Registrar el cliente enviado a través del formulario si no existe
         $cliente = Cliente::UpdateOrCreate(['clinum'=>$cliruc],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$clicor,'clicor2'=>$clicor2,'clicor3'=>$clicor3,'clicor4'=>$clicor4,'rucemp'=>$rucemp,'tdicod'=>$tdicod,'telefono'=>$request->get('clitel'),'clifecnac'=>$request->get('clifecnac'),'mes_nac'=>$mes]);

      //Crear un objeto de la cabecera del comprobante para registrar los datos de la cabecera
        $cabecera = new cpe_cabecera;
        $cabecera->tdocod = $tdocod;
        $cabecera->topcod = $topcod;
        $cabecera->ccafem = $fecemi;
        $cabecera->clicorcli = $clicor;

        if($tdocod =='15'){
          $cabecera->estado ='PENDIENTE';
        }

        if($buscre->cre_dia_tip=='CONTADO' || $buscre->cre_dia_tip=='PERSONALIZADO'){
            $cabecera->ccafve = $request->get('fecVen');
        }else{
            $cabecera->ccafve = date('Y-m-d',strtotime($fecemi."+ ".$buscre->cre_dia_fac." days"));
        }

        if($buscre->cre_dia_tip=='CONTADO'){
            $cabecera->totalcontado = $total;
            $cabecera->totalcredito = '0';
        }else{
            $cabecera->totalcredito = $total;
            $cabecera->totalcontado = '0';
        }
      
        $cabecera->ccaobs = $observaciones;
        //$cabecera->ccacde = $request->get();
        $cabecera->tdicod = $tdicod;
        $cabecera->ccandi = $cliruc;
        $cabecera->ccanom = $clinom;
        $cabecera->moncod = $mondoc;
        $cabecera->direccion = $clidir;
          $cabecera->clicorcli2 = $clicor2;
        $cabecera->clicorcli3 = $clicor3;
        $cabecera->clicorcli4 = $clicor4;

        if(!empty($bus_cot)){
          $cabecera->referencia = $bus_cot->serdoc.'-'.$bus_cot->numdoc;
        }

        $cabecera->tipcambio = $camdoc;
        $cabecera->ccatvg =  $totalafec;
        $cabecera->ccatexo =  $totalexo;
        $cabecera->ccatinaf =  $totalinaf;
        $cabecera->ccaigv = $totaligv;
        $cabecera->ccaitv = $total;
          
        if(!empty($tip_cam->CamVenta)){
          $cabecera->tipcambio = $tip_cam->CamVenta;
        }
        
        $cabecera->id_empresa_negocio = Auth::user()->id_empresa_negocio;
    
        

        $cabecera->clicod = $cliente->clicod;
        $cabecera->icbper = $totalicbper;
        $cabecera->ccaobs = $observaciones;
        $cabecera->paga = $pagar;
        $cabecera->vuelto = $vuelto;

        if($buscre->cre_dia_tip=='CONTADO'){
          $cabecera->estadopago = 'CONTADO';
        }else{
          $cabecera->estadopago = 'CREDITO';
        }
        
        
       
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
          if( $empresanegocio->NumNota == $numcomp){
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
          $cabecera->ccacodsun ='0';
          //$cabecera->save();
        }elseif($tdocod=='15'){
          if( $empresanegocio->ProNum == $numcomp){
              $modnumcomp = $numcomp+1;
          }else{
              $modnumcomp = $numcomp;
          }

          $empresanegocio->ProSer = $sercomp;
          $empresanegocio->ProNum = $modnumcomp;
          //$empresa->update();

          $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
          $cabecera->serdoc= $sercomp;
          $cabecera->numdoc = $numdoc;
          $cabecera->ccacodsun ='0';
          //$cabecera->save();
        }

           $empresanegocio->update();

          $empresa->update();
          $cabecera->save();

          $codfact = $cabecera->IdCpe_cabecera; 
          $usuario_facturacion = new usuario_facturacion;
          $usuario_facturacion->IdCpe_cabecera = $codfact;
          $usuario_facturacion->id_turno = Auth::user()->id_turno;
         
         // $usuario_facturacion->ped_id = $pedido;
          $usuario_facturacion->id_empresa_negocio = Auth::user()->id_empresa_negocio;
          $usuario_facturacion->IdEmpresa = Auth::user()->IdEmpresa;
          $usuario_facturacion->referencia = "Registro";
          $usuario_facturacion->save();

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


      


        //Generar el detalle del comprobante

         foreach($unidades as $index => $uni) {
          
            $tigcod = '20';
            
            $detalle = new cpe_detalle;
            $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
            $detalle->umecod = $uni;
            $detalle->cdecan = $cantidades[$index];
            $detalle->cdepsu = "";
            $detalle->cdedes = $detpro[$index];
            //$detalle->costo = $codpro->costo;
            $detalle->tigcod = '20';
   //         $detalle->icbper = $codpro->icbper;

            if($tigcod =='10'){
              $preciouni = $puni[$index];
              $valoruni = $puni[$index]/1.1055;
            }elseif($tigcod=='20'){
              $preciouni = $puni[$index];
              $valoruni = $puni[$index];
            }
              $valorigv = $preciouni - $valoruni;

              $valorsubtotal = $valoruni*$cantidades[$index];
              $valortotal = $preciouni*$cantidades[$index];
              $valorigvtotal =  $valorigv*$cantidades[$index];
           

            $detalle->cdepuni = $preciouni;
            $detalle->cdevun = $valoruni;
            $detalle->cdevve = $valortotal;
            $detalle->cdepve = $valorsubtotal;
            $detalle->cdeigv = $valorigvtotal;
            $detalle->fecha_venta = $fecemi;
            $detalle->save();


          
        }

        // Monto en letras
        $totalletras= MontoLetras::convertir(number_format($cabecera->ccaitv,'2','.',''),$moneda->monnom,'Centimos');

        //Consultar los datos de la empresa emisora
        $datemp = DB::table("empresa")->where("IdEmpresa","=",$rucemp)->first();


        if($tdocod =='01' || $tdocod=='03'){
          $invoice = self::generar_xml_boleta_factura($codfact);
        }
   
        
        if($tdocod =='15'){

           $documento = self::generarpdfgeneral($codfact);

        }


        if($empresa->formato =='TICKET'){

         
            if(empty($cabecera->referencia)){

               for($i=1;$i<=$empresa->imp_venta;$i++){

                  if($request->get('opcion')=='0'){
                    self::imprimir($codfact,$tdocod);
                  }
                }
            }else{


             $documento = self::generarpdfgeneral($codfact);
            }

        }elseif($empresa->formato=='A4'){
         
					$documento = self::generarpdfgeneral($codfact);
			
        }

       

        if($buscre->cre_dia_tip !='CONTADO'){
          self::registrarcuentascobrar($codfact);
        }
       
        if(!empty(trim($clicor))){
           self::enviar_comprobante($codfact,$clicor);
        }

  
        if(!empty($documento)){
          if($request->get('opcion')=='0'){
          exec("c:\WINDOWS\system32\cmd.exe /c START powershell.exe -ExecutionPolicy Bypass Start-Process -FilePath ".public_path()."/pdf/".$documento." -Verb Print");
          }
        }

       // self::registrarguia($request);

        return response()->json(['estado'=>'success','codfact' =>$codfact,'tdocod'=>$tdocod,'mensaje'=>'Comprobante Emitido']);
        
    }

    
    public function pagoservicio(Request $request)
    {


       $documentos = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderby('tdicod')->get();

        $rucemp = trim(Auth::user()->IdEmpresa);

        $empresa = Empresa::findOrFail($rucemp);

        //Datos de cabecera
        $tdicod = $request->get('tdicod');
        $tdocod = $request->get('tdocod'); 
        $mondoc = $request->get('moncod');
        $total = $request->get('total');
        $fecemi = $request->get('fecEmi');
        $fecven = $request->get('fecVen');
        $tipo_venta = $request->get('tipoventa');
        
        $pagar = $request->get('pagar');
        $vuelto = $request->get('vuelto');
      //medios de pago
        $monto = $request->get('monto');
        $medio = $request->get('medio');

      //estado de pago
        $estadopago = $request->get('estadopago');

      //DETALLE 
        $unidades = $request->get('unid');
        $proid = $request->get('proid');
        $detpro = $request->get('pronom');
        $vunit = $request->get('provun');
        $puni = $request->get('propun');
        $vtot = $request->get('itemtotal');
        $cantidades = $request->get('cant');
        $pronomobs = $request->geT('pronomobs');

      //Datos del Cliente
        $cliruc = $request->get('clinum');
        $fecha = $request->get('fecEmi');
        $clinom = $request->get('clinom');
        $clidir = $request->get('clidir');
        $telefono = $request->get('telefono');
        $clicor = $request->get('clicor');
        $topcod = '0101';

      //DATOS REPARACION
        $imei = $request->get('imei');
        $marca = $request->get('marca');
        $modelo = $request->get('modelo');
        $equipo = $request->get('cmbtipo');
        $golpes = $request->get('golpes');
        $enciende = $request->get('enciende');
        $bateria = $request->get('bateria');
        $bandeja = $request->get('bandeja');
        $patron = $request->get('patron');
        $fallas = $request->get('fallas');
        $tecnico = $request->get('tecnico');
        $observaciones = $request->get('observaciones');
        
        $buscre = DB::tABLE('credito_dias')->where('cre_dia_id',$estadopago)->first();

        $oldDate = strtotime($request->get('clifecnac'));

        $mes = date('m',$oldDate);


      //Registrar el cliente enviado a través del formulario si no existe
        $cliente = Cliente::UpdateOrCreate(['clinum'=>$cliruc],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$clicor,'rucemp'=>$rucemp,'tdicod'=>$tdicod,'telefono'=>$request->get('clitel'),'clifecnac'=>$request->get('clifecnac'),'mes_nac'=>$mes]);

        if(!empty($request->get('id'))){

            $cotizacion = cpe_cabecera::findOrFail($request->get('id'));
            $cotizacion->estado ='ACEPTADO';
            $cotizacion->update();
        }

       
        $total =0;
        $totalexo = 0;
        $totalafec = 0;
        $totalinaf =0;
        $totaligv =0;
        $totalsubt = 0;
        $totalicbper =0;

        $totalabonos = 0;
        foreach ($medio as $index => $mp) {
            
             if($monto[$index] > '0.00'){
                
                $totalabonos = $totalabonos + $monto[$index];
             }

        }

         foreach($unidades as $index => $ume ) {

            $codpro = productos::findOrFail($proid[$index]);

            $total = $total + ($cantidades[$index]*$puni[$index]);

            if($codpro->icbper==1){
              $totalicbper = $totalicbper + ($cantidades[$index]*$empresa->icbper);
            }


            if($codpro->tigcod =='10'){

             
              $totalafec = ($total / 1.18);
              $totaligv = $total-$totalafec;


            }elseif($codpro->tigcod =='20'){

              $totalexo = $total;
              $totaligv =  $total-$totalexo;

            }elseif($codpro->tigcod =='30'){

              $totalinaf = $total;
              $totaligv =  $total-$totalinaf;

            }

        }


          $senudoc = DB::tABLE('empresa_negocios')->select('NumOS','SerOS')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
          $numcomp =  $senudoc->NumOS+1;
          $sercomp =  $senudoc->SerOS;
   
        $moneda = DB::tABLE('moneda')->where('moncod','=',$mondoc)->first();

      //Registrar el tipo de cambio enviado a través del formulario si no existe.
        if($mondoc !='PEN'){
          $camdoc = $request->get('camdoc');
          $tipcam = tipocambio::FirstOrCreate(['FecTipCambio'=>$fecemi],['CamCompra'=>$camdoc]);
        }else{
          $camdoc=0;
        }

      
      //Crear un objeto de la cabecera del comprobante para registrar los datos de la cabecera
        $cabecera = new cpe_cabecera;
        $cabecera->tdocod = $tdocod;
        $cabecera->topcod = $topcod;
        $cabecera->ccafem = $fecemi;
        $cabecera->imei = $imei;
        $cabecera->marca = $marca;
        $cabecera->modelo = $modelo;
        $cabecera->tip_equ_id= $equipo;
        $cabecera->direccion = $clidir;
        $cabecera->golpes = $golpes;
        $cabecera->bateria = $bateria;
        $cabecera->enciende = $enciende;
        $cabecera->bandeja = $bandeja;
        $cabecera->patron = $patron;
        $cabecera->fallas = $fallas;
        $cabecera->ccaobs = $observaciones;
        $cabecera->est_equ_id ='1';
        $cabecera->tecnico = $tecnico;

        if($buscre->cre_dia_tip=='CONTADO' || $buscre->cre_dia_tip=='PERSONALIZADO'){
            $cabecera->ccafve = $request->get('fecVen');
        }else{
            $cabecera->ccafve = date('Y-m-d',strtotime($fecemi."+ ".$buscre->cre_dia_fac." days"));
        }

        if($buscre->cre_dia_tip=='CONTADO'){
            $cabecera->totalcontado = $totalabonos;
            $cabecera->totalcredito = $total-$totalabonos;
        }else{
            $cabecera->totalcredito = $total-$totalabonos;
            $cabecera->totalcontado = $totalabonos;
        }
      
        $cabecera->ccaobs = $observaciones;
        $cabecera->cre_dia_id = $buscre->cre_dia_id;
        $cabecera->tdicod = $tdicod;
        $cabecera->ccandi = $cliruc;
        $cabecera->ccanom = $clinom;
        $cabecera->moncod = $mondoc;
        $cabecera->direccion = $clidir;
        $cabecera->telefono = $telefono;
        $cabecera->tipcambio = $camdoc;
        $cabecera->ccatvg =  $totalafec;
        $cabecera->ccatexo =  $totalexo;
        $cabecera->ccatinaf =  $totalinaf;
        $cabecera->ccaigv = $totaligv;
        $cabecera->ccaitv = $total;
        $cabecera->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $cabecera->clicod = $cliente->clicod;
        $cabecera->icbper = $totalicbper;
        $cabecera->ccaobs = $observaciones;
        $cabecera->paga = $pagar;
        $cabecera->vuelto = $vuelto;
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->IdEmpresa =  $rucemp;

        if($buscre->cre_dia_tip=='CONTADO'){
          $cabecera->estadopago = 'CONTADO';
        }else{
          $cabecera->estadopago = 'CREDITO';
        }


        $empresa = Empresa::findOrFail($rucemp);
        $empresanegocio = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);
     
  
        if( $empresanegocio->NumOS== $numcomp){
            $modnumcomp = $numcomp+1;
        }else{
            $modnumcomp = $numcomp;
        }

        $empresanegocio->SerOS = $sercomp;
        $empresanegocio->NumOS = $modnumcomp;
    
        $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
        $cabecera->serdoc= $sercomp;
        $cabecera->numdoc = $numdoc;
        $cabecera->ccacodsun ='0';
    
        $empresanegocio->update();

        $empresa->update();
        $cabecera->save();

        $codfact = $cabecera->IdCpe_cabecera; 
        $usuario_facturacion = new usuario_facturacion;
        $usuario_facturacion->IdCpe_cabecera = $codfact;
        $usuario_facturacion->id_turno = Auth::user()->id_turno;
        $usuario_facturacion->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $usuario_facturacion->IdEmpresa = Auth::user()->IdEmpresa;
        $usuario_facturacion->referencia = "Registro";
        $usuario_facturacion->save();

          foreach ($medio as $index => $mp) {
             
             if($monto[$index] > '0.00'){
                DB::tABLE('venta_medio_pago')->insert(['IdCpe_cabecera'=>$codfact,'id_med_pag'=>$mp,'monto'=>$monto[$index]]);

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
            
                if($comision =='1'){
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


      
        //Generar el detalle del comprobante

         foreach($proid as $index => $id) {

            $codpro = productos::findOrFail($id);
            $codproducto = $codpro->procod;

            $detalle = new cpe_detalle;
            $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
            $detalle->umecod = $codpro->umecod;
            $detalle->cdecan = $cantidades[$index];
            $detalle->procod = $codproducto;
            $detalle->IdProducto = $codpro->IdProducto;
            $detalle->cdepsu = "";
            $detalle->cdedes = $detpro[$index];
            $detalle->pronomobs = $pronomobs[$index];
            $detalle->costo = $codpro->costo;
            $detalle->tigcod = $codpro->tigcod;
            $detalle->icbper = $codpro->icbper;

            if($codpro->tigcod =='10'){
              $preciouni = $puni[$index];
              $valoruni = $puni[$index]/1.1055;
            }elseif($codpro->tigcod=='20'){
              $preciouni = $puni[$index];
              $valoruni = $puni[$index];
            }
              $valorigv = $preciouni - $valoruni;

              $valorsubtotal = $valoruni*$cantidades[$index];
              $valortotal = $preciouni*$cantidades[$index];
              $valorigvtotal =  $valorigv*$cantidades[$index];
           

            $detalle->cdepuni = $preciouni;
            $detalle->cdevun = $valoruni;
            $detalle->cdevve = $valortotal;
            $detalle->cdepve = $valorsubtotal;
            $detalle->cdeigv = $valorigvtotal;
            $detalle->fecha_venta = $fecemi;
            $detalle->save();


             if($tdocod !='15'){


              if($codpro->promocion =='0'){

                $stock_prod =productos::findOrFail($codpro->IdProducto);
                $stock_prod->stock = $stock_prod->stock-$cantidades[$index];;
                $stock_prod->update();

                $movimiento = new movimientos;
                $movimiento->mov_fec = $fecha; 
                $movimiento->mov_tip = 'E';
                $movimiento->mov_mot = 'Venta';
                $movimiento->cantidad = $cantidades[$index];
                $movimiento->unidad = $stock_prod->umecod;
                $movimiento->comprobante = $sercomp.'-'.$numdoc;
                $movimiento->IdEmpresa = $rucemp;
                $movimiento->id_empresa_negocio = Auth::user()->id_empresa_negocio;
                $movimiento->IdProducto = $codpro->IdProducto;
                $movimiento->observacion = "Venta desde Punto de Venta";
                $movimiento->IdUsuario = Auth::user()->IdUsuario;
                $movimiento->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
                $movimiento->stockmov = $stock_prod->stock;
                $movimiento->save();


              }elseif($codpro->promocion =='1'){

                  $combos = DB::tABLE('combos')
                  ->where('prod_id',$codpro->IdProducto)->get();

                  foreach ($combos as $combo) {
                    
                    $buscarproducto = DB::tABLE('productos')
                    ->where('IdProducto',$combo->prod_combo)
                    ->first();

                    if($buscarproducto->promocion =='0'){

                        $stock_prod =productos::findOrFail($buscarproducto->IdProducto);
                        $stock_prod->stock = $stock_prod->stock-($cantidades[$index]*$combo->comb_cant);
                        $stock_prod->update();

                        $movimiento = new movimientos;
                        $movimiento->mov_fec = $fecha; 
                        $movimiento->mov_tip = 'E';
                        $movimiento->mov_mot = 'Venta';
                        $movimiento->cantidad = $cantidades[$index]*$combo->comb_cant;
                        $movimiento->unidad = $stock_prod->umecod;
                        $movimiento->comprobante = $sercomp.'-'.$numdoc;
                        $movimiento->IdEmpresa = $rucemp;
                        $movimiento->id_empresa_negocio = Auth::user()->id_empresa_negocio;
                        $movimiento->IdProducto = $buscarproducto->IdProducto;
                        $movimiento->observacion = "Venta desde Punto de Venta";
                        $movimiento->IdUsuario = Auth::user()->IdUsuario;
                        $movimiento->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
                        $movimiento->stockmov = $stock_prod->stock;
                        $movimiento->save();

                    }elseif($buscarproducto->promocion =='2'){

                        $recetas = DB::tABLE('recetas')
                        ->where('prod_id',$combo->prod_combo)
                        ->get();

                        foreach ($recetas as $receta) {
                           
                            $stock_prod =productos::findOrFail($receta->prod_insu);
                            $stock_prod->stock = $stock_prod->stock-($cantidades[$index]*$receta->rec_cant);
                            $stock_prod->update();

                            $movimiento = new movimientos;
                            $movimiento->mov_fec = $fecha; 
                            $movimiento->mov_tip = 'E';
                            $movimiento->mov_mot = 'Venta';
                            $movimiento->cantidad = $cantidades[$index]*$receta->rec_cant;
                            $movimiento->unidad = $stock_prod->umecod;
                            $movimiento->comprobante = $sercomp.'-'.$numdoc;
                            $movimiento->IdEmpresa = $rucemp;
                            $movimiento->id_empresa_negocio = Auth::user()->id_empresa_negocio;
                            $movimiento->IdProducto = $receta->prod_insu;
                            $movimiento->observacion = "Venta desde Punto de Venta";
                            $movimiento->IdUsuario = Auth::user()->IdUsuario;
                            $movimiento->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
                            $movimiento->stockmov = $stock_prod->stock;
                            $movimiento->save();
                        }
                    }

                  }


              }elseif($codpro->promocion =='2'){

                 $recetas = DB::tABLE('recetas')
                  ->where('prod_id',$codpro->IdProducto)
                  ->get();

                  foreach ($recetas as $receta) {        
                      $stock_prod =productos::findOrFail($receta->prod_insu);
                      $stock_prod->stock = $stock_prod->stock-($cantidades[$index]*$receta->rec_cant);
                      $stock_prod->update();


                      $movimiento = new movimientos;
                      $movimiento->mov_fec = $fecha; 
                      $movimiento->mov_tip = 'E';
                      $movimiento->mov_mot = 'Venta';
                      $movimiento->cantidad = $cantidades[$index]*$receta->rec_cant;
                      $movimiento->unidad = $stock_prod->umecod;
                      $movimiento->comprobante = $sercomp.'-'.$numdoc;
                      $movimiento->IdEmpresa = $rucemp;
                      $movimiento->id_empresa_negocio = Auth::user()->id_empresa_negocio;
                      $movimiento->IdProducto = $receta->prod_insu;
                      $movimiento->observacion = "Venta desde Punto de Venta";
                      $movimiento->IdUsuario = Auth::user()->IdUsuario;
                      $movimiento->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
                      $movimiento->stockmov = $stock_prod->stock;
                      $movimiento->save();
                      
                  }
                 
              }
          }
          
        }


        self::generarqr($codfact);

        if($tdocod =='50'){
           self::generarpdfgeneral($codfact);
        }

        if($empresa->formato =='TICKET'){
          for($i=1;$i<=$empresa->imp_venta;$i++){

              if($request->get('opcion')=='0'){
                self::imprimir($codfact,$tdocod);
              }
          }
        }elseif($empresa->formato=='A4'){
            self::generarpdfgeneral($codfact);
        }


        if($buscre->cre_dia_tip !='CONTADO'){
          self::registrarcuentascobrar($codfact);
        }
       
        if(!empty(trim($clicor))){
           self::enviar_comprobante($codfact,$clicor);
        }

        return response()->json(['estado'=>'success','codfact' =>$codfact,'tdocod'=>$tdocod,'mensaje'=>'Comprobante Emitido']);
 
        
    }


     public function Registrarpagoorden(Request $request)
    {


      

        $rucemp = trim(Auth::user()->IdEmpresa);

        $empresa = Empresa::findOrFail($rucemp);

        //Datos de cabecera
        $tdicod = $request->get('tdicod');
        $tdocod = $request->get('tdocod');
        $mondoc = $request->get('moncod');
        $total = $request->get('total');
        $fecemi = $request->get('fecEmi');
        $fecven = $request->get('fecVen');
        $tipo_venta = $request->get('tipoventa');
        
        $pagar = $request->get('pagar');
        $vuelto = $request->get('vuelto');
      //medios de pago
        $monto = $request->get('monto');
        $medio = $request->get('medio');

      //estado de pago
        $estadopago = $request->get('estadopago');

      //DETALLE 
        $unidades = $request->get('unid');
        $proid = $request->get('proid');
        $detpro = $request->get('pronom');
        $vunit = $request->get('provun');
        $puni = $request->get('propun');
        $vtot = $request->get('itemtotal');
        $cantidades = $request->get('cant');
        $pronomobs = $request->geT('pronomobs');

      //Datos del Cliente
        $cliruc = $request->get('clinum');
        $fecha = $request->get('fecEmi');
        $clinom = $request->get('clinom');
        $clidir = $request->get('clidir');
        $telefono = $request->get('telefono');
        $clicor = $request->get('clicor');
        $topcod = '0101';

      //DATOS REPARACION
        $imei = $request->get('imei');
        $marca = $request->get('marca');
        $modelo = $request->get('modelo');
        $equipo = $request->get('cmbtipo');
        $golpes = $request->get('golpes');
        $enciende = $request->get('enciende');
        $bateria = $request->get('bateria');
        $bandeja = $request->get('bandeja');
        $patron = $request->get('patron');
        $fallas = $request->get('fallas');
        $tecnico = $request->get('tecnico');
        $observaciones = $request->get('observaciones');
        
        $buscre = DB::tABLE('credito_dias')->where('cre_dia_id',$estadopago)->first();

        $oldDate = strtotime($request->get('clifecnac'));

        $mes = date('m',$oldDate);


      //Registrar el cliente enviado a través del formulario si no existe
        $cliente = Cliente::UpdateOrCreate(['clinum'=>$cliruc],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$clicor,'rucemp'=>$rucemp,'tdicod'=>$tdicod,'telefono'=>$request->get('clitel'),'clifecnac'=>$request->get('clifecnac'),'mes_nac'=>$mes]);

        if(!empty($request->get('id'))){

            $cotizacion = cpe_cabecera::findOrFail($request->get('id'));
            $cotizacion->estado ='ACEPTADO';
            $cotizacion->update();
        }

       
        $total =0;
        $totalexo = 0;
        $totalafec = 0;
        $totalinaf =0;
        $totaligv =0;
        $totalsubt = 0;
        $totalicbper =0;

        $totalabonos = 0;
        foreach ($medio as $index => $mp) {
            
             if($monto[$index] > '0.00'){
                
                $totalabonos = $totalabonos + $monto[$index];
             }

        }

        $buscarorden = cpe_cabecera::findOrFail($request->get('id'));
        $buscarorden->est_equ_id = '3';
        $buscarorden->totalcontado = $totalabonos;
        $buscarorden->totalcredito = '0.00';
        $buscarorden->update();

         foreach($unidades as $index => $ume ) {

            $codpro = productos::findOrFail($proid[$index]);

            $total = $total + ($cantidades[$index]*$puni[$index]);

            if($codpro->icbper=='1'){
              $totalicbper = $totalicbper + ($cantidades[$index]*$empresa->icbper);
            }


            if($codpro->tigcod =='10'){

             
              $totalafec = ($total / 1.18);
              $totaligv = $total-$totalafec;


            }elseif($codpro->tigcod =='20'){

              $totalexo = $total;
              $totaligv =  $total-$totalexo;

            }elseif($codpro->tigcod =='30'){

              $totalinaf = $total;
              $totaligv =  $total-$totalinaf;

            }

        }


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
        }elseif ($tdocod =='15') {
          $senudoc = DB::tABLE('empresa_negocios')->select('ProNum','ProSer')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
          $numcomp =  $senudoc->ProNum+1;
          $sercomp =  $senudoc->ProSer;
        }

   
        $moneda = DB::tABLE('moneda')->where('moncod','=',$mondoc)->first();

      //Registrar el tipo de cambio enviado a través del formulario si no existe.
        if($mondoc !='PEN'){
          $camdoc = $request->get('camdoc');
          $tipcam = tipocambio::FirstOrCreate(['FecTipCambio'=>$fecemi],['CamCompra'=>$camdoc]);
        }else{
          $camdoc=0;
        }

      
      //Crear un objeto de la cabecera del comprobante para registrar los datos de la cabecera
        $cabecera = new cpe_cabecera;
        $cabecera->tdocod = $tdocod;
        $cabecera->topcod = $topcod;
        $cabecera->ccafem = $fecemi;
        $cabecera->imei = $imei;
        $cabecera->marca = $marca;
        $cabecera->modelo = $modelo;
        $cabecera->tip_equ_id= $equipo;
        $cabecera->golpes = $golpes;
        $cabecera->bateria = $bateria;
        $cabecera->enciende = $enciende;
        $cabecera->bandeja = $bandeja;
        $cabecera->patron = $patron;
        $cabecera->fallas = $fallas;
        $cabecera->ccaobs = $observaciones;
        $cabecera->est_equ_id ='1';
        $cabecera->tecnico = $tecnico;

        if($buscre->cre_dia_tip=='CONTADO' || $buscre->cre_dia_tip=='PERSONALIZADO'){
            $cabecera->ccafve = $request->get('fecVen');
        }else{
            $cabecera->ccafve = date('Y-m-d',strtotime($fecemi."+ ".$buscre->cre_dia_fac." days"));
        }

        if($buscre->cre_dia_tip=='CONTADO'){
            $cabecera->totalcontado = $totalabonos;
            $cabecera->totalcredito = $total-$totalabonos;
        }else{
            $cabecera->totalcredito = $total-$totalabonos;
            $cabecera->totalcontado = $totalabonos;
        }
      
        $cabecera->ccaobs = $observaciones;
        $cabecera->cre_dia_id = $buscre->cre_dia_id;
        $cabecera->tdicod = $tdicod;
        $cabecera->ccandi = $cliruc;
        $cabecera->ccanom = $clinom;
        $cabecera->moncod = $mondoc;
        $cabecera->direccion = $clidir;
        $cabecera->telefono = $telefono;
        $cabecera->tipcambio = $camdoc;
        $cabecera->ccatvg =  $totalafec;
        $cabecera->ccatexo =  $totalexo;
        $cabecera->ccatinaf =  $totalinaf;
        $cabecera->ccaigv = $totaligv;
        $cabecera->ccaitv = $total;
        $cabecera->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $cabecera->clicod = $cliente->clicod;
        $cabecera->icbper = $totalicbper;
        $cabecera->ccaobs = $observaciones;
        $cabecera->paga = $pagar;
        $cabecera->vuelto = $vuelto;
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->IdEmpresa =  $rucemp;

        if($buscre->cre_dia_tip=='CONTADO'){
          $cabecera->estadopago = 'CONTADO';
        }else{
          $cabecera->estadopago = 'CREDITO';
        }

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
          if( $empresanegocio->NumNota == $numcomp){
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
          $cabecera->ccacodsun ='0';
          //$cabecera->save();
        }elseif($tdocod=='15'){
          if( $empresanegocio->ProNum == $numcomp){
              $modnumcomp = $numcomp+1;
          }else{
              $modnumcomp = $numcomp;
          }

          $empresanegocio->ProSer = $sercomp;
          $empresanegocio->ProNum = $modnumcomp;
          //$empresa->update();

          $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
          $cabecera->serdoc= $sercomp;
          $cabecera->numdoc = $numdoc;
          $cabecera->ccacodsun ='0';
          //$cabecera->save();
        }

           $empresanegocio->update();

          $empresa->update();
          $cabecera->save();

          $codfact = $cabecera->IdCpe_cabecera; 
          $usuario_facturacion = new usuario_facturacion;
          $usuario_facturacion->IdCpe_cabecera = $codfact;
          $usuario_facturacion->id_turno = Auth::user()->id_turno;
         
         // $usuario_facturacion->ped_id = $pedido;
          $usuario_facturacion->id_empresa_negocio = Auth::user()->id_empresa_negocio;
          $usuario_facturacion->IdEmpresa = Auth::user()->IdEmpresa;
          $usuario_facturacion->referencia = "Registro";
          $usuario_facturacion->save();

          foreach ($medio as $index => $mp) {
             
             if($monto[$index] > '0.00'){
                DB::tABLE('venta_medio_pago')->insert(['IdCpe_cabecera'=>$codfact,'id_med_pag'=>$mp,'monto'=>$monto[$index]]);

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
            
                if($comision =='1'){
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


      
        //Generar el detalle del comprobante

         foreach($proid as $index => $id) {

            $codpro = productos::findOrFail($id);
            $codproducto = $codpro->procod;

            $detalle = new cpe_detalle;
            $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
            $detalle->umecod = $codpro->umecod;
            $detalle->cdecan = $cantidades[$index];
            $detalle->procod = $codproducto;
            $detalle->IdProducto = $codpro->IdProducto;
            $detalle->cdepsu = "";
            $detalle->cdedes = $detpro[$index];
            $detalle->pronomobs = $pronomobs[$index];
            $detalle->costo = $codpro->costo;
            $detalle->tigcod = $codpro->tigcod;
            $detalle->icbper = $codpro->icbper;

            if($codpro->tigcod =='10'){
              $preciouni = $puni[$index];
              $valoruni = $puni[$index]/1.1055;
            }elseif($codpro->tigcod=='20'){
              $preciouni = $puni[$index];
              $valoruni = $puni[$index];
            }
              $valorigv = $preciouni - $valoruni;

              $valorsubtotal = $valoruni*$cantidades[$index];
              $valortotal = $preciouni*$cantidades[$index];
              $valorigvtotal =  $valorigv*$cantidades[$index];
           

            $detalle->cdepuni = $preciouni;
            $detalle->cdevun = $valoruni;
            $detalle->cdevve = $valortotal;
            $detalle->cdepve = $valorsubtotal;
            $detalle->cdeigv = $valorigvtotal;
            $detalle->fecha_venta = $fecemi;
            $detalle->save();


          
        }


        self::generarqr($codfact);

        
           self::generarpdfgeneral($codfact);
     

        if($empresa->formato =='TICKET'){
          for($i=1;$i<=$empresa->imp_venta;$i++){

              if($request->get('opcion')=='0'){
                self::imprimir($codfact,$tdocod);
              }
          }
        }elseif($empresa->formato=='A4'){
            self::generarpdfgeneral($codfact);
        }


        if($buscre->cre_dia_tip !='CONTADO'){
          self::registrarcuentascobrar($codfact);
        }
       
        if(!empty(trim($clicor))){
           self::enviar_comprobante($codfact,$clicor);
        }

        return response()->json(['estado'=>'success','codfact' =>$codfact,'tdocod'=>$tdocod,'mensaje'=>'Comprobante Emitido']);
 
        
    }


    public function registrarcuentascobrar($venta){

        $venta = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$venta)->first();

        $cuentacobrar = new cuentascobrar;
        $cuentacobrar->IdCpe_cabecera = $venta->IdCpe_cabecera;
        $cuentacobrar->clicod = $venta->clicod;
        $cuentacobrar->fec_ven = $venta->ccafve;
        $cuentacobrar->abono = $venta->totalcontado;
        $cuentacobrar->estado_cob = 'pendiente';
        $cuentacobrar->total = $venta->totalcredito;
        $cuentacobrar->saldo = $venta->totalcredito;
        $cuentacobrar->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $cuentacobrar->save();

        return $cuentacobrar;
    }
    

    public function actualizarcotizacion(Request $request)
    {

      
       $rucemp = trim(Auth::user()->IdEmpresa);

        //DETALLE 
        $unidades = $request->get('unid');
        $proid = $request->get('proid');
        $detpro = $request->get('pronom');
        $vunit = $request->get('provun');
        $puni = $request->get('propun');
        $vtot = $request->get('itemtotal');
        $cantidades = $request->get('cant');
        $total =0;
        $totalexo = 0;
        $totalafec = 0;
        $totalinaf =0;
        $totaligv =0;
        $totalsubt = 0;
        $totalicbper =0;

         foreach($unidades as $index => $ume ) {

            $codpro = productos::findOrFail($proid[$index]);

            $total = $total + ($cantidades[$index]*$puni[$index]);

            if($codpro->icbper==1){
              $totalicbper = $totalicbper + ($cantidades[$index]*$empresa->icbper);
            }


            if($codpro->tigcod =='10'){

             
              $totalafec = ($total / 1.18);
              $totaligv = $total-$totalafec;


            }elseif($codpro->tigcod =='20'){

              $totalexo = $total;
              $totaligv =  $total-$totalexo;

            }elseif($codpro->tigcod =='30'){

              $totalinaf = $total;
              $totaligv =  $total-$totalinaf;

            }

        }


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
          $senudoc = DB::tABLE('empresa_negocios')->select('FseEmpresa','FnuEmpresa')->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)->first();
          $numcomp =  $senudoc->FnuEmpresa+1;
          $sercomp =  $senudoc->FseEmpresa;
        }elseif ($tdocod =='03') {
          $senudoc = DB::tABLE('empresa_negocios')->select('BseEmpresa','BnuEmpresa')->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)->first();
          $numcomp =  $senudoc->BnuEmpresa+1;
          $sercomp =  $senudoc->BseEmpresa;
        }elseif ($tdocod =='13') {
          $senudoc = DB::tABLE('empresa_negocios')->select('SerNota','NumNota')->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)->first();
          $numcomp =  $senudoc->NumNota+1;
          $sercomp =  $senudoc->SerNota;
        }elseif ($tdocod =='15') {
          $senudoc = DB::tABLE('empresa_negocios')->select('ProNum','ProSer')->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)->first();
          $numcomp =  $senudoc->ProNum+1;
          $sercomp =  $senudoc->ProSer;
        }


       

        $moneda = DB::tABLE('moneda')->where('moncod','=',$mondoc)->first();


      //Registrar el tipo de cambio enviado a través del formulario si no existe.
        if($mondoc !='PEN'){
          $camdoc = $request->get('camdoc');
          $tipcam = tipocambio::FirstOrCreate(['FecTipCambio'=>$fecemi],['CamCompra'=>$camdoc]);
        }else{
          $camdoc=0;
        }

       $oldDate = strtotime($request->get('clifecnac'));

        $mes = date('m',$oldDate);


      //Registrar el cliente enviado a través del formulario si no existe
        $cliente = Cliente::UpdateOrCreate(['clinum'=>$cliruc],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$clicor,'rucemp'=>$rucemp,'tdicod'=>$tdicod,'telefono'=>$request->get('clitel'),'clifecnac'=>$request->get('clifecnac'),'mes_nac'=>$mes]);


      //Crear un objeto de la cabecera del comprobante para registrar los datos de la cabecera
        $cabecera =  cpe_cabecera::findOrFail($request->get('id'));
        $cabecera->tdocod = $request->get('tdocod');
        $cabecera->topcod = $topcod;
        $cabecera->ccafem = $request->get('fecEmi');
        $cabecera->ccafve = $request->get('fecVen');
        $cabecera->tdicod = $tdicod;
        $cabecera->ccandi = $cliruc;
        $cabecera->direccion = $clidir;
        $cabecera->ccanom = $clinom;
        $cabecera->moncod = $mondoc;
        $cabecera->estado = 'PENDIENTE';
   
        $cabecera->tipcambio = $camdoc;
        $cabecera->ccatvg =  $totalafec;
        $cabecera->ccatexo =  $totalexo;
        $cabecera->ccatinaf =  $totalinaf;
        $cabecera->ccaigv = $totaligv;
        $cabecera->ccaitv = $total;
        $cabecera->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $cabecera->visa =  $visa;
        
        
        $cabecera->clicod = $cliente->clicod;
        $cabecera->icbper = $totalicbper;
        $cabecera->paga = $request->get('pagar');
        $cabecera->vuelto = $request->get('vuelto');
        
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->IdEmpresa =  $rucemp;
        $cabecera->update();
          
        $empresa = Empresa::findOrFail($rucemp);
        $codfact = $cabecera->IdCpe_cabecera; 
          
     /*   $usuario_facturacion = new usuario_facturacion;
        $usuario_facturacion->IdCpe_cabecera = $codfact;
        $usuario_facturacion->id_turno = Auth::user()->id_turno;
        $usuario_facturacion->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $usuario_facturacion->IdEmpresa = Auth::user()->IdEmpresa;
        $usuario_facturacion->referencia = "Registro";
        $usuario_facturacion->save();*/

        


        $unidades = $request->get('unid');
        $proid = $request->get('proid');
        $detpro = $request->get('pronom');
        $vunit = $request->get('provun');
        $puni = $request->get('propun');
        $vtot = $request->get('itemtotal');
        $cantidades = $request->get('cant');
        $IdCpe_detalle = $request->get('IdCpe_detalle');

        $registros = DB::tABLE('cpe_detalle')->where('IdCpe_cabecera',$request->get('id'))->get();

        foreach ($registros as $reg) {
            if(!in_array($reg->IdCpe_detalle,$IdCpe_detalle)){
              DB::tABLE('cpe_detalle')->where('IdCpe_detalle',$reg->IdCpe_detalle)->delete();
            }
        }

        //Generar el detalle del comprobante

         foreach($unidades as $index => $ume ) {

            $codpro = productos::findOrFail($proid[$index]);
            $codproducto = $codpro->procod;

            if(!empty($IdCpe_detalle[$index])){

                $detalle = cpe_detalle::findOrFail($IdCpe_detalle[$index]);
                $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
                $detalle->umecod = $codpro->umecod;
                $detalle->cdecan = $cantidades[$index];
                $detalle->procod = $codproducto;
                $detalle->IdProducto = $codpro->IdProducto;
                $detalle->cdepsu = "";
                $detalle->cdedes = $detpro[$index];
                $detalle->costo = $codpro->costo;
                $detalle->tigcod = $codpro->tigcod;
                $detalle->icbper = $codpro->icbper;

                if($codpro->tigcod =='10'){
                  $preciouni = $puni[$index];
                  $valoruni = $puni[$index]/1.1055;
                }elseif($codpro->tigcod=='20'){
                  $preciouni = $puni[$index];
                  $valoruni = $puni[$index];
                }
                  $valorigv = $preciouni - $valoruni;

                  $valorsubtotal = $valoruni*$cantidades[$index];
                  $valortotal = $preciouni*$cantidades[$index];
                  $valorigvtotal =  $valorigv*$cantidades[$index];
               

                $detalle->cdepuni = $preciouni;
                $detalle->cdevun = $valoruni;
                $detalle->cdevve = $valortotal;
                $detalle->cdepve = $valorsubtotal;
                $detalle->cdeigv = $valorigvtotal;
                $detalle->fecha_venta = $fecemi;
                $detalle->update();
            }else{
               $detalle = new cpe_detalle;
              $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
              $detalle->umecod = $codpro->umecod;
              $detalle->cdecan = $cantidades[$index];
              $detalle->procod = $codproducto;
              $detalle->IdProducto = $codpro->IdProducto;
              $detalle->cdepsu = "";
              $detalle->cdedes = $detpro[$index];
              $detalle->costo = $codpro->costo;
              $detalle->tigcod = $codpro->tigcod;
              $detalle->icbper = $codpro->icbper;

              if($codpro->tigcod =='10'){
                $preciouni = $puni[$index];
                $valoruni = $puni[$index]/1.1055;
              }elseif($codpro->tigcod=='20'){
                $preciouni = $puni[$index];
                $valoruni = $puni[$index];
              }
                $valorigv = $preciouni - $valoruni;

                $valorsubtotal = $valoruni*$cantidades[$index];
                $valortotal = $preciouni*$cantidades[$index];
                $valorigvtotal =  $valorigv*$cantidades[$index];
             

              $detalle->cdepuni = $preciouni;
              $detalle->cdevun = $valoruni;
              $detalle->cdevve = $valortotal;
              $detalle->cdepve = $valorsubtotal;
              $detalle->cdeigv = $valorigvtotal;
              $detalle->fecha_venta = $fecemi;
              $detalle->save();
            }
           

          
        }

        self::generarpdfgeneral($codfact);

        return response()->json(['estado'=>'success','codfact' =>$codfact,'tdocod'=>$tdocod,'mensaje'=>'Comprobante Emitido']);
 
        
    }


    public function actualizarorden(Request $request){

        $id = $request->get('id');

        
        $rucemp = trim(Auth::user()->IdEmpresa);

        $empresa = Empresa::findOrFail($rucemp);

        //Datos de cabecera
        $tdicod = $request->get('tdicod');
        $tdocod = $request->get('tdocod');
        $mondoc = $request->get('moncod');
        $total = $request->get('total');
        $fecemi = $request->get('fecEmi');
        $fecven = $request->get('fecVen');
        $tipo_venta = $request->get('tipoventa');
        
        $pagar = $request->get('pagar');
        $vuelto = $request->get('vuelto');
      //medios de pago
        $monto = $request->get('monto');
        $medio = $request->get('medio');

      //estado de pago
        $estadopago = $request->get('estadopago');

      //DETALLE 
        $unidades = $request->get('unid');
        $proid = $request->get('proid');
        $detpro = $request->get('pronom');
        $vunit = $request->get('provun');
        $puni = $request->get('propun');
        $vtot = $request->get('itemtotal');
        $cantidades = $request->get('cant');
        $pronomobs = $request->geT('pronomobs');
        $IdCpe_detalle = $request->get('IdCpe_detalle');


      //Datos del Cliente
        $cliruc = $request->get('clinum');
        $fecha = $request->get('fecEmi');
        $clinom = $request->get('clinom');
        $clidir = $request->get('clidir');
        $telefono = $request->get('telefono');
        $clicor = $request->get('clicor');
        $topcod = '0101';

      //DATOS REPARACION
        $imei = $request->get('imei');
        $marca = $request->get('marca');
        $modelo = $request->get('modelo');
        $equipo = $request->get('cmbtipo');
        $golpes = $request->get('golpes');
        $enciende = $request->get('enciende');
        $bateria = $request->get('bateria');
        $bandeja = $request->get('bandeja');
        $patron = $request->get('patron');
        $fallas = $request->get('fallas');
        $observaciones = $request->get('observaciones');
         $tecnico = $request->get('tecnico');
        
        $buscre = DB::tABLE('credito_dias')->where('cre_dia_id',$estadopago)->first();

       $oldDate = strtotime($request->get('clifecnac'));

        $mes = date('m',$oldDate);


      //Registrar el cliente enviado a través del formulario si no existe
        $cliente = Cliente::UpdateOrCreate(['clinum'=>$cliruc],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$clicor,'rucemp'=>$rucemp,'tdicod'=>$tdicod,'telefono'=>$request->get('clitel'),'clifecnac'=>$request->get('clifecnac'),'mes_nac'=>$mes]);

   
        $total =0;
        $totalexo = 0;
        $totalafec = 0;
        $totalinaf =0;
        $totaligv =0;
        $totalsubt = 0;
        $totalicbper =0;

        $totalabonos = 0;
        foreach ($medio as $index => $mp) {
            
             if($monto[$index] > '0.00'){
                
                $totalabonos = $totalabonos + $monto[$index];
             }

        }
    

         foreach($unidades as $index => $ume ) {

            $codpro = productos::findOrFail($proid[$index]);

            $total = $total + ($cantidades[$index]*$puni[$index]);

            if($codpro->icbper==1){
              $totalicbper = $totalicbper + ($cantidades[$index]*$empresa->icbper);
            }


            if($codpro->tigcod =='10'){

             
              $totalafec = ($total / 1.18);
              $totaligv = $total-$totalafec;


            }elseif($codpro->tigcod =='20'){

              $totalexo = $total;
              $totaligv =  $total-$totalexo;

            }elseif($codpro->tigcod =='30'){

              $totalinaf = $total;
              $totaligv =  $total-$totalinaf;

            }

        }


       

        $moneda = DB::tABLE('moneda')->where('moncod','=',$mondoc)->first();

      //Registrar el tipo de cambio enviado a través del formulario si no existe.
        if($mondoc !='PEN'){
          $camdoc = $request->get('camdoc');
          $tipcam = tipocambio::FirstOrCreate(['FecTipCambio'=>$fecemi],['CamCompra'=>$camdoc]);
        }else{
          $camdoc=0;
        }
    

      //Crear un objeto de la cabecera del comprobante para registrar los datos de la cabecera
        $cabecera = cpe_cabecera::findOrFail($id);
        $cabecera->tdocod = $tdocod;
        $cabecera->topcod = $topcod;
        $cabecera->direccion = $clidir;
        $cabecera->ccafem = $fecemi;
        $cabecera->imei = $imei;
        $cabecera->marca = $marca;
        $cabecera->modelo = $modelo;
        $cabecera->tip_equ_id= $equipo;
        $cabecera->golpes = $golpes;
        $cabecera->bateria = $bateria;
        $cabecera->enciende = $enciende;
        $cabecera->bandeja = $bandeja;
        $cabecera->patron = $patron;
        $cabecera->fallas = $fallas;
        $cabecera->ccaobs = $observaciones;
        $cabecera->est_equ_id ='1';
        $cabecera->tecnico = $tecnico;

        if($buscre->cre_dia_tip=='CONTADO' || $buscre->cre_dia_tip=='PERSONALIZADO'){
            $cabecera->ccafve = $request->get('fecVen');
        }else{
            $cabecera->ccafve = date('Y-m-d',strtotime($fecemi."+ ".$buscre->cre_dia_fac." days"));
        }

        if($buscre->cre_dia_tip=='CONTADO'){
            $cabecera->totalcontado = $totalabonos;
            $cabecera->totalcredito = $total-$totalabonos;
        }else{
            $cabecera->totalcredito = $total-$totalabonos;
            $cabecera->totalcontado = $totalabonos;
        }
      
        $cabecera->ccaobs = $observaciones;
        $cabecera->cre_dia_id = $buscre->cre_dia_id;
        $cabecera->tdicod = $tdicod;
        $cabecera->ccandi = $cliruc;
        $cabecera->ccanom = $clinom;
        $cabecera->moncod = $mondoc;
        $cabecera->direccion = $clidir;
        $cabecera->telefono = $telefono;
        $cabecera->tipcambio = $camdoc;
        $cabecera->ccatvg =  $totalafec;
        $cabecera->ccatexo =  $totalexo;
        $cabecera->ccatinaf =  $totalinaf;
        $cabecera->ccaigv = $totaligv;
        $cabecera->ccaitv = $total;
        $cabecera->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $cabecera->clicod = $cliente->clicod;
        $cabecera->icbper = $totalicbper;
        $cabecera->ccaobs = $observaciones;
        $cabecera->paga = $pagar;
        $cabecera->vuelto = $vuelto;
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->IdEmpresa =  $rucemp;

        if($buscre->cre_dia_tip=='CONTADO'){
          $cabecera->estadopago = 'CONTADO';
        }else{
          $cabecera->estadopago = 'CREDITO';
        }
        $cabecera->update();

        $codfact = $cabecera->IdCpe_cabecera;

        foreach ($medio as $index => $mp) {
             
             if($monto[$index] > '0.00'){

                $busmedpag = DB::tABLE('venta_medio_pago')->where('IdCpe_cabecera',$codfact)->where('id_med_pag',$mp)->first();

                if(empty($busmedpag)){
                  DB::tABLE('venta_medio_pago')->insert(['IdCpe_cabecera'=>$codfact,'id_med_pag'=>$mp,'monto'=>$monto[$index]]);
                }else{
                   DB::tABLE('venta_medio_pago')->where('id_med_pag',$busmedpag->id_med_pag)->update(['monto'=>$monto[$index]]);

                }
                

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
            
                if($comision =='1'){
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


        DB::tABLE('movimientos')->where('IdCpe_cabecera',$cabecera->IdCpe_cabecera)->delete();

        $registros = DB::tABLE('cpe_detalle')->where('IdCpe_cabecera',$request->get('id'))->get();

        foreach ($registros as $reg) {

            $stock_prod =productos::findOrFail($reg->IdProducto);
            $stock_prod->stock = $stock_prod->stock+$reg->cdecan;
            $stock_prod->update();

            if(!in_array($reg->IdCpe_detalle,$IdCpe_detalle)){
              DB::tABLE('cpe_detalle')->where('IdCpe_detalle',$reg->IdCpe_detalle)->delete();
            }
        }

        //Generar el detalle del comprobante

         foreach($proid as $index => $id) {

           
            $codpro = productos::findOrFail($id);
            $codproducto = $codpro->procod;

            if(!empty($IdCpe_detalle[$index])){

                $detalle = cpe_detalle::findOrFail($IdCpe_detalle[$index]);
                $detalle->IdCpe_cabecera =  $codfact;
                $detalle->umecod = $codpro->umecod;
                $detalle->cdecan = $cantidades[$index];
                $detalle->procod = $codproducto;
                $detalle->IdProducto = $codpro->IdProducto;
                $detalle->cdepsu = "";
                $detalle->cdedes = $detpro[$index];
                $detalle->costo = $codpro->costo;
                $detalle->tigcod = $codpro->tigcod;
                $detalle->icbper = $codpro->icbper;

                if($codpro->tigcod =='10'){
                  $preciouni = $puni[$index];
                  $valoruni = $puni[$index]/1.1055;
                }elseif($codpro->tigcod=='20'){
                  $preciouni = $puni[$index];
                  $valoruni = $puni[$index];
                }
                  $valorigv = $preciouni - $valoruni;

                  $valorsubtotal = $valoruni*$cantidades[$index];
                  $valortotal = $preciouni*$cantidades[$index];
                  $valorigvtotal =  $valorigv*$cantidades[$index];
               

                $detalle->cdepuni = $preciouni;
                $detalle->cdevun = $valoruni;
                $detalle->cdevve = $valortotal;
                $detalle->cdepve = $valorsubtotal;
                $detalle->cdeigv = $valorigvtotal;
                $detalle->fecha_venta = $fecemi;
                $detalle->update();
            }else{
               $detalle = new cpe_detalle;
              $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
              $detalle->umecod = $codpro->umecod;
              $detalle->cdecan = $cantidades[$index];
              $detalle->procod = $codproducto;
              $detalle->IdProducto = $codpro->IdProducto;
              $detalle->cdepsu = "";
              $detalle->cdedes = $detpro[$index];
              $detalle->costo = $codpro->costo;
              $detalle->tigcod = $codpro->tigcod;
              $detalle->icbper = $codpro->icbper;

              if($codpro->tigcod =='10'){
                $preciouni = $puni[$index];
                $valoruni = $puni[$index]/1.1055;
              }elseif($codpro->tigcod=='20'){
                $preciouni = $puni[$index];
                $valoruni = $puni[$index];
              }
                $valorigv = $preciouni - $valoruni;

                $valorsubtotal = $valoruni*$cantidades[$index];
                $valortotal = $preciouni*$cantidades[$index];
                $valorigvtotal =  $valorigv*$cantidades[$index];
             

              $detalle->cdepuni = $preciouni;
              $detalle->cdevun = $valoruni;
              $detalle->cdevve = $valortotal;
              $detalle->cdepve = $valorsubtotal;
              $detalle->cdeigv = $valorigvtotal;
              $detalle->fecha_venta = $fecemi;
              $detalle->save();
            }


            //Guardar en una variable los items en el archivo .det
            $codumecin = unidad_medida::findOrFail($ume);

            $IdProducto = DB::tABLE('productos')->WHERE('procod',$codproducto)->where('IdEmpresa',trim(Auth::user()->IdEmpresa))->first();

             if($tdocod !='15'){
              if($codpro->promocion =='0'){

                $stock_prod =productos::findOrFail($codpro->IdProducto);
                $stock_prod->stock = $stock_prod->stock-$cantidades[$index];;
                $stock_prod->update();

                $movimiento = new movimientos;
                $movimiento->mov_fec = $fecha; 
                $movimiento->mov_tip = 'E';
                $movimiento->mov_mot = 'Venta';
                $movimiento->cantidad = $cantidades[$index];
                $movimiento->unidad = $stock_prod->umecod;
                $movimiento->comprobante = $cabecera->sercomp.'-'.$cabecera->numdoc;
                $movimiento->IdEmpresa = $rucemp;
                $movimiento->id_empresa_negocio = Auth::user()->id_empresa_negocio;
                $movimiento->IdProducto = $codpro->IdProducto;
                $movimiento->observacion = "Venta desde Punto de Venta";
                $movimiento->IdUsuario = Auth::user()->IdUsuario;
                $movimiento->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
                $movimiento->stockmov = $stock_prod->stock;
                $movimiento->save();


              }elseif($codpro->promocion =='1'){

                  $combos = DB::tABLE('combos')
                  ->where('prod_id',$codpro->IdProducto)->get();

                  foreach ($combos as $combo) {
                    
                    $buscarproducto = DB::tABLE('productos')
                    ->where('IdProducto',$combo->prod_combo)
                    ->first();

                    if($buscarproducto->promocion =='0'){

                        $stock_prod =productos::findOrFail($buscarproducto->IdProducto);
                        $stock_prod->stock = $stock_prod->stock-($cantidades[$index]*$combo->comb_cant);
                        $stock_prod->update();

                        $movimiento = new movimientos;
                        $movimiento->mov_fec = $fecha; 
                        $movimiento->mov_tip = 'E';
                        $movimiento->mov_mot = 'Venta';
                        $movimiento->cantidad = $cantidades[$index]*$combo->comb_cant;
                        $movimiento->unidad = $stock_prod->umecod;
                        $movimiento->comprobante = $cabecera->sercomp.'-'.$cabecera->numdoc;
                        $movimiento->IdEmpresa = $rucemp;
                        $movimiento->id_empresa_negocio = Auth::user()->id_empresa_negocio;
                        $movimiento->IdProducto = $buscarproducto->IdProducto;
                        $movimiento->observacion = "Venta desde Punto de Venta";
                        $movimiento->IdUsuario = Auth::user()->IdUsuario;
                        $movimiento->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
                        $movimiento->stockmov = $stock_prod->stock;
                        $movimiento->save();




                    }elseif($buscarproducto->promocion =='2'){

                        $recetas = DB::tABLE('recetas')
                        ->where('prod_id',$combo->prod_combo)
                        ->get();

                        foreach ($recetas as $receta) {
                           
                            $stock_prod =productos::findOrFail($receta->prod_insu);
                            $stock_prod->stock = $stock_prod->stock-($cantidades[$index]*$receta->rec_cant);
                            $stock_prod->update();

                            $movimiento = new movimientos;
                            $movimiento->mov_fec = $fecha; 
                            $movimiento->mov_tip = 'E';
                            $movimiento->mov_mot = 'Venta';
                            $movimiento->cantidad = $cantidades[$index]*$receta->rec_cant;
                            $movimiento->unidad = $stock_prod->umecod;
                            $movimiento->comprobante = $cabecera->sercomp.'-'.$cabecera->numdoc;
                            $movimiento->IdEmpresa = $rucemp;
                            $movimiento->id_empresa_negocio = Auth::user()->id_empresa_negocio;
                            $movimiento->IdProducto = $receta->prod_insu;
                            $movimiento->observacion = "Venta desde Punto de Venta";
                            $movimiento->IdUsuario = Auth::user()->IdUsuario;
                            $movimiento->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
                            $movimiento->stockmov = $stock_prod->stock;
                            $movimiento->save();
                        }
                    }

                  }


              }elseif($codpro->promocion =='2'){

                 $recetas = DB::tABLE('recetas')
                  ->where('prod_id',$codpro->IdProducto)
                  ->get();

                  foreach ($recetas as $receta) {        
                      $stock_prod =productos::findOrFail($receta->prod_insu);
                      $stock_prod->stock = $stock_prod->stock-($cantidades[$index]*$receta->rec_cant);
                      $stock_prod->update();


                      $movimiento = new movimientos;
                      $movimiento->mov_fec = $fecha; 
                      $movimiento->mov_tip = 'E';
                      $movimiento->mov_mot = 'Venta';
                      $movimiento->cantidad = $cantidades[$index]*$receta->rec_cant;
                      $movimiento->unidad = $stock_prod->umecod;
                      $movimiento->comprobante = $cabecera->sercomp.'-'.$cabecera->numdoc;
                      $movimiento->IdEmpresa = $rucemp;
                      $movimiento->id_empresa_negocio = Auth::user()->id_empresa_negocio;
                      $movimiento->IdProducto = $receta->prod_insu;
                      $movimiento->observacion = "Venta desde Punto de Venta";
                      $movimiento->IdUsuario = Auth::user()->IdUsuario;
                      $movimiento->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
                      $movimiento->stockmov = $stock_prod->stock;
                      $movimiento->save();
                      
                  }
                 
              }
          }
          
        }

    
     
        self::generarqr($codfact);

        
        if($tdocod =='80'){
           self::generarpdfgeneral($codfact);
        }

        if($empresa->formato =='TICKET'){
          for($i=1;$i<=$empresa->imp_venta;$i++){

              if($request->get('opcion')=='0'){
                self::imprimir($codfact,$tdocod);
              }
          }
        }elseif($empresa->formato=='A4'){
            self::generarpdfgeneral($codfact);
        }


        if($buscre->cre_dia_tip !='CONTADO'){
          self::registrarcuentascobrar($codfact);
        }
       
        if(!empty(trim($clicor))){
           self::enviar_comprobante($codfact,$clicor);
        }

        return response()->json(['estado'=>'success','codfact' =>$codfact,'tdocod'=>$tdocod,'mensaje'=>'Comprobante Emitido']);
 
        
    }








    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
  /*  public function destroy(Request $request, $id)
    {
       
      $motivo = $request->get('obser');
  
      $deleteped = pedidos::findOrFail($id);
      $deleteped->ped_est = 'Eliminado';
      $deleteped->MotElim = $motivo;
      $deleteped->IdUsuarioDel = Auth::user()->IdUsuario;
      $deleteped->update();

        return Redirect::to('/listallevar');
    }

    */


    public function tomarpedido($mesa)
    {
       $rucemp = trim(Auth::user()->IdEmpresa);
      $categorias = DB::tABLE('categorias')
        ->select('cat_id','cat_nom','color')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->orderby('cat_nom','asc')
        ->where('tipo','=',null)
        ->get();
       $mozos = DB::tABLE('users')->join('role_user','role_user.user_IdUsuario','users.IdUsuario')->where('role_id','5')->get();

      //$mesas = DB::tABLE('mesas')->where('mes_id',$mesa)->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
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
      return view('empresas.puntosventas.restaurant',compact('categorias','pedidos','mesas','unidades','totales','mozos'));
    }

    public function mostrarpiso($piso)
    {

  
      $rucemp = trim(Auth::user()->IdEmpresa);

      $primer_piso = DB::tABLE('pisos')->where('pis_id',$piso)->where('emp_id',$rucemp)->first();

       if(!empty($primer_piso)){

        if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('caja') ){
            $mesas = DB::tABLE('mesas')
          ->join('pisos','pisos.pis_id','mesas.pis_id')
          ->where('IdEmpresa',$rucemp)
          ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->where('mesas.pis_id',$primer_piso->pis_id)
          ->orderby('mesas.mes_nom','asc')
          ->get(); 
        }else{

          $mesas = DB::tABLE('mesas')
          ->join('pisos','pisos.pis_id','mesas.pis_id')
          ->where('IdEmpresa',$rucemp)
          ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->where('mesas.pis_id',$primer_piso->pis_id)
          ->where('IdUsuario',Auth::user()->IdUsuario)
          ->orderby('mesas.mes_nom','asc')
          ->get(); 


        }
        

    
      }
       $users = DB::tABLE('users')->get();

      $pisos = DB::tABLE('pisos')->where('emp_id',$rucemp)->get();
 
      return view('empresas.mesas',compact('mesas','primer_piso','pisos','users'));

    }


    public function modificarpedido($mesa)
    {
       $rucemp = trim(Auth::user()->IdEmpresa);
      
      $categorias = DB::tABLE('categorias')
        ->select('cat_id','cat_nom','color')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->orderby('cat_nom','asc')
        ->where('tipo','=',null)
        ->get();

      $mozos = DB::tABLE('users')->join('role_user','role_user.user_IdUsuario','users.IdUsuario')->where('role_id','5')->get();


      //$mesas = DB::tABLE('mesas')->where('mes_id',$mesa)->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
      $mesas = DB::tABLE('mesas')->where('mes_id',$mesa)->first();
      $unidades = DB::tABLE('unidad_medida')->get();

      $users = DB::tABLE('users')->get();

      if($mesas->mes_est == 'Libre'){
        return Redirect::to('/mesas')->with('success','La Mesa seleccionada está libre, ha sido facturado el pedido o eliminado');
      }

       $pedido = DB::tABLE('pedidos as p')
      ->where('p.mes_id',$mesa)
      ->where('ped_est','Aperturado')
      ->where('p.IdEmpresa',$rucemp)
      ->first();

      $ped_dat = DB::tABLE('pedidos')->select('mozo')->where('mozo',$pedido->mozo)->first();

      $pedidos = DB::tABLE('pedidos as p')
      ->join('pedidos_detalle as pd','p.ped_id','pd.ped_id')
      ->join('productos as prod','prod.IdProducto','pd.IdProducto')
      ->where('p.ped_id',$pedido->ped_id)
      ->where('ped_est','Aperturado')
      ->where('p.IdEmpresa',$rucemp)
      ->where('pd.estadoitem', '!=', 'Eliminado')
      ->get();

      $totales= DB::tABLE('pedidos as p')
      ->join('mesas as m','p.mes_id','m.mes_id')
      ->where('p.ped_id',$pedido->ped_id)
      ->where('ped_est','Aperturado')
      ->where('p.IdEmpresa',$rucemp)
      ->first();

      return view('empresas.puntosventas.modificarpedido',compact('empresa','ped_dat','users','categorias','pedidos','mesas','unidades','totales','pedido','mozos'));
    }



    public function editarpedidollevar($idpedido)
    {
       $rucemp = trim(Auth::user()->IdEmpresa);
       $categorias = DB::tABLE('categorias')
        ->select('cat_id','cat_nom','color')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->orderby('cat_nom','asc')
        ->where('tipo','=',null)
        ->get();

     
      $unidades = DB::tABLE('unidad_medida')->get();

      $users = DB::tABLE('users')->get();


       $pedido = DB::tABLE('pedidos as p')
      ->where('p.tipo','Llevar')
      ->where('ped_est','Aperturado')
      ->where('p.IdEmpresa',$rucemp)
      ->where('p.ped_id',$idpedido)
      ->first();

      $ped_dat = DB::tABLE('pedidos')->select('mozo')->where('mozo',$pedido->mozo)->first();

      $pedidos = DB::tABLE('pedidos as p')
      ->join('pedidos_detalle as pd','p.ped_id','pd.ped_id')
      ->join('productos as prod','prod.IdProducto','pd.IdProducto')
      ->where('p.ped_id',$idpedido)
      ->where('ped_est','Aperturado')
      ->where('p.IdEmpresa',$rucemp)
      ->where('pd.estadoitem', '!=', 'Eliminado')
      ->get();

      $totales= DB::tABLE('pedidos as p')
      ->where('p.ped_id',$idpedido)
      ->where('ped_est','Aperturado')
      ->where('p.IdEmpresa',$rucemp)
      ->first();
      
       $mozos = DB::tABLE('users')->join('role_user','role_user.user_IdUsuario','users.IdUsuario')->where('role_id','5')->get();

      return view('empresas.puntosventas.modificarpedidollevar',compact('users','categorias','pedidos','mesas','unidades','totales','pedido','mozos','ped_dat'));
    }

     public function mostrarpedidollevar($idpedido)
    {
       $rucemp = trim(Auth::user()->IdEmpresa);
      $categorias = DB::tABLE('categorias')
        ->select('cat_id','cat_nom','color')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->orderby('cat_nom','asc')
        ->where('tipo','=',null)
        ->get();

     
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


    public function facturacionmesa()
    {

       $rucemp = trim(Auth::user()->IdEmpresa);
      //$mesas = DB::tABLE('mesas')->where('mes_id',$mesa)->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
       $primer_piso = DB::tABLE('pisos')->where('emp_id',$rucemp)->first();

      $mesas = DB::tABLE('mesas')
      ->join('pisos','pisos.pis_id','mesas.pis_id')
      ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->where('mesas.pis_id',$primer_piso->pis_id)
      ->orderby('mesas.mes_id','asc')
      ->get();


      $pisos = DB::tABLE('pisos')->where('emp_id',$rucemp)->get();


      return view('empresas.puntosventas.facturacionmesas',compact('mesas','primer_piso','pisos'));
 
    }

    public function cobrarmesa($mesa)
    {

      $rucemp = trim(Auth::user()->IdEmpresa);
      $categorias = DB::tABLE('categorias')
        ->select('cat_id','cat_nom','color')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->orderby('cat_nom','asc')
        ->where('tipo','=',null)
        ->get();

          $mozos = DB::tABLE('users')->join('role_user','role_user.user_IdUsuario','users.IdUsuario')->where('role_id','5')->get();

       $comprobante = DB::tABLE('tipo_documento')->get();
        $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();
         $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();


      //$mesas = DB::tABLE('mesas')->where('mes_id',$mesa)->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
       $primer_piso = DB::tABLE('pisos')->where('emp_id',$rucemp)->first();

      $mesas = DB::tABLE('mesas')
      ->join('pisos','pisos.pis_id','mesas.pis_id')
      ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->where('mesas.pis_id',$primer_piso->pis_id)
      ->orderby('mesas.mes_id','asc')
      ->get();


      $pisos = DB::tABLE('pisos')->where('emp_id',$rucemp)->get();

      $unidades = DB::tABLE('unidad_medida')->get();

      $pedido = DB::tABLE('pedidos as p')
      ->where('p.mes_id',$mesa)
      ->where('ped_est','Aperturado')
      ->where('p.IdEmpresa',$rucemp)
      ->first();

      $pedidos = DB::tABLE('pedidos as p')
      ->join('pedidos_detalle as pd','p.ped_id','pd.ped_id')
      ->join('productos as prod','prod.IdProducto','pd.IdProducto')
      ->where('p.ped_id',$pedido->ped_id)
      ->where('ped_est','Aperturado')
      ->where('p.IdEmpresa',$rucemp)
      ->where('pd.estadoitem','!=','Eliminado')
      ->get();

      $id_ped = $pedido->ped_id;

      $totales= DB::tABLE('pedidos as p')
      ->join('mesas as m','p.mes_id','m.mes_id')
      ->where('p.ped_id',$pedido->ped_id)
      ->where('ped_est','Aperturado')
      ->where('p.IdEmpresa',$rucemp)
      ->first();

      $mesas = DB::tABLE('mesas')->where('mes_id',$mesa)->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
      //$mesa_est= mesas::findOrFail($mesa);
      //$mesa_est->mes_est = 'Facturando';
      //$mesa_est->update();
      $users = DB::tABLE('users')->get();

      return view('empresas.puntosventas.cobrarmesa',compact('users','categorias','pedidos','mesas','unidades','totales','tipodocumento','comprobante','id_ped','mozos','pedido'));
 
    }
 
    public function facturacionmesas()
    {

      $rucemp = trim(Auth::user()->IdEmpresa);
      $mesas = DB::tABLE('mesas')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();

      return view('empresas.mesas',compact('mesas'));

       $rucemp = trim(Auth::user()->IdEmpresa);
       $mesas = DB::tABLE('mesas')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();
       $comprobante = DB::tABLE('tipo_documento')->get();
       $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();
      $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

      return view('empresas.puntosventas.facturacionmesas',compact('mesas','comprobante','tipodocumento','igv','unidades'));
    }
  
  public function listar_pedido_llevar($id_ped="",$tipo=0){

      $rucemp = trim(Auth::user()->IdEmpresa);

    if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('caja')){
       $pedidos = DB::tABLE('pedidos')->where('tipo','Llevar')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->orderby('ped_id','desc')->paginate(800);
    }else{
        $pedidos = DB::tABLE('pedidos')->where('tipo','Llevar')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('mozo',Auth::user()->mozo)->orderby('ped_id','desc')->paginate(800);
    }
   
    return view('empresas.puntosventas.listallevar',compact('pedidos','id_ped','tipo'));
  }

  public function pedido_llevar(){

      $rucemp = trim(Auth::user()->IdEmpresa);
      $categorias = DB::tABLE('categorias')
        ->select('cat_id','cat_nom','color')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->orderby('cat_nom','asc')
        ->where('tipo','=',null)
        ->get();

      $mozos = DB::tABLE('users')->join('role_user','role_user.user_IdUsuario','users.IdUsuario')->where('role_id','5')->get();

      $unidades = DB::tABLE('unidad_medida')->get();
    
      return view('empresas.puntosventas.pedidollevar',compact('categorias','unidades','mozos'));
  }

  public function registrar_pedido_llevar(Request $request){

        $rucemp = trim(Auth::user()->IdEmpresa);
        $empresa = Empresa::findOrFail($rucemp);
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
        $pedido->cliente = $cliente;
        $pedido->total=$total;
        $pedido->direccion = $request->get('direccion');
        $pedido->telefono = $request->get('telefono');
        $pedido->ped_tip = $request->get('tipo_pedido');
        $pedido->subtotal=$subtotal;
        $pedido->igv=$igv;
        $pedido->est_ped_id ='1';
        $pedido->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $pedido->IdEmpresa=$rucemp;
        $pedido->ped_est='Aperturado';
        $pedido->tipo = $request->get('tipo_pedido');
        if(!empty($request->get('mozo'))){
          $pedido->mozo = $request->get('mozo');
        }else{
          $pedido->mozo = Auth::user()->IdUsuario;
        }
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

        $pedidos = DB::tABLE('pedidos')->where('tipo','Llevar')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->orderby('ped_id','desc')->paginate(100);


        for($i=1;$i<=$empresa->imp_pedido;$i++){
          self::imprimirpedidollevar($id_ped);
        }
       
       // return view('empresas.puntosventas.listallevar',compact('pedidos','id_ped'));
       
  
            return response()->json(['mensaje' => 'Registrado correctamente','pedido'=>$id_ped]);
       

  }

  public function cobrar_llevar($pedido_id){
      $rucemp = trim(Auth::user()->IdEmpresa);
      $categorias = DB::tABLE('categorias')
        ->select('cat_id','cat_nom','color')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->orderby('cat_nom','asc')
        ->where('tipo','=',null)
        ->get();

      $comprobante = DB::tABLE('tipo_documento')->get();
        $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();
         $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

      //$mesas = DB::tABLE('mesas')->where('mes_id',$mesa)->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
      
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


 
 public function eliminarpedido(Request $request,$pedido,$idmesa="0"){

     $user = User::findOrFail(Auth::user()->IdUsuario);

     $usuariobuscar = DB::tABLE('users')->where('IdUsuario',Auth::user()->IdUsuario)->count();

     $usuariopass = DB::tABLE('users')->where('IdUsuario',Auth::user()->IdUsuario)->first();

     //if (Hash::check($codigo,$usuariopass->password_admin) && $usuariobuscar > 0) {

            
            $deleteped = pedidos::findOrFail($pedido);
            $deleteped->IdUsuarioDel = Auth::user()->IdUsuario;
            $deleteped->MotElim = "";
            $deleteped->ped_est = 'Eliminado';
            $deleteped->update();

              DB::tABLE('usuario_eliminar')->insert(
              array('ped_id' => $pedido, 'id_usu_elim' =>Auth::user()->IdUsuario,'id_usu_aut'=>Auth::user()->IdUsuario,'motivo'=>"",'id_empresa_negocio'=>Auth::user()->id_empresa_negocio));

            $pedidodetalle = DB::tABLE('pedidos_detalle')
            ->where('ped_id',$pedido)
            ->update(['estadoitem' => "Eliminado",'MotElim'=>"",'IdUsuarioDel'=>Auth::user()->IdUsuario]);             

            $user = User::findOrFail(Auth::user()->IdUsuario);

         


            if($idmesa!='0'){

              $mesa = mesas::findOrFail($idmesa);
              $mesa->mes_est ='Libre';
              $mesa->update();

              $mesa= DB::tABLE('mesas')->where('mes_id',$idmesa)->first();
            }
            

            $sucursal = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);

            $cab_pedido = DB::tABLE('pedidos')
            ->where('ped_id',$pedido)
            ->first();

           

            
            $impresoras = DB::tABLE('configuracion_impresoras')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();

        if($sucursal->impresion =='Local'){

            foreach ($impresoras as $impresora) {

                $detalle = DB::tABLE('pedidos_detalle')->where('ped_id',$pedido)
               ->join('productos','pedidos_detalle.IdProducto','productos.IdProducto')
               ->leftjoin('categorias','categorias.cat_id','productos.cat_id')
               ->where('categorias.id_empresa_negocio',Auth::user()->id_empresa_negocio)
               ->where('categorias.impresora',$impresora->Id)
               ->get();

                $detallecount = DB::tABLE('pedidos_detalle')->where('ped_id',$pedido)
               ->join('productos','pedidos_detalle.IdProducto','productos.IdProducto')
               ->leftjoin('categorias','categorias.cat_id','productos.cat_id')
               ->where('categorias.id_empresa_negocio',Auth::user()->id_empresa_negocio)
               ->where('categorias.impresora',$impresora->Id)
               ->count();

              if($detallecount >0){

                  $connector = new WindowsPrintConnector("smb://".$impresora->ruta);
                  $printer = new Printer($connector);
                  $printer->setJustification(Printer::JUSTIFY_CENTER);
                  $printer->setFont(Printer::FONT_A);
                  $printer->text("PEDIDO ANULADO"."\n");
                  if(isset($mesa)){
                     $printer->text("Pedido ".$cab_pedido->ped_id.": ". $mesa->mes_nom."\n");
                  }else{
                     $printer->text("Pedido para Llevar ".$cab_pedido->ped_id."\n");
                  }
                 
                 //   $printer->text("OUT & PRIDE"."\n");
                    $printer->text("Cliente:". $cab_pedido->cliente."\n");
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

                    }
                    
                   $printer->text("\n");
                   

                    $printer->feed();
                    
                    $printer->cut();
                    
                     
                    $printer->pulse();
                   
                    $printer->close();
                  }

              }

        }

        if($request->ajax()) {
          return response()->json(['mensaje' => 'Pedido Eliminado']);
        }
   // }

  }


  public function EliminarItemPedido(Request $request,$itempedido,$pedido,$mesa,$producto){


      $user = User::findOrFail(Auth::user()->IdUsuario);

      $usuariopass = DB::tABLE('users')->where('IdUsuario',Auth::user()->IdUsuario)->first();

      $usuariobuscar = DB::tABLE('users')->where('IdUsuario',Auth::user()->IdUsuario)->count();


     //if (Hash::check($codigo,$usuariopass->password_admin) && $usuariobuscar > 0) {


        $deleteped = pedidos_detalle::findOrFail($itempedido);
        $deleteped->estadoitem = 'Eliminado';
         $deleteped->IdUsuarioDel = Auth::user()->IdUsuario;
        $deleteped->MotElim = "";
        $deleteped->update();

       

        DB::tABLE('usuario_eliminar')->insert(
        array('ped_id' => $pedido, 'id_usu_elim' =>Auth::user()->IdUsuario,'id_usu_aut'=>Auth::user()->IdUsuario,'motivo'=>"",'id_empresa_negocio'=>Auth::user()->id_empresa_negocio,'detalle'=>$deleteped->detalle,'IdProducto'=>$deleteped->detalle));

        $sucursal = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);

        $cab_pedido = DB::tABLE('pedidos')
           ->where('ped_id',$pedido)
           ->first();

           $pedidosdetalle = DB::tABLE('pedidos_detalle')
            ->where('ped_id',$pedido)
            ->where('estadoitem','!=','Eliminado')
            ->get();

            $total=0;
            $subtotal=0;
            $igv=0;

            foreach ($pedidosdetalle as $detped) {
              $total = $total + $detped->totalitem;
              $subtotal = $subtotal + $detped->subtotalitem;
              $igv = $igv + $detped->igvitem;
            }

              $cabecera = pedidos::findOrFail($pedido);
            $cabecera->total = $total;
            $cabecera->subtotal = $subtotal;
            $cabecera->igv = $igv;
            $cabecera->update();
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
   // }


    
    
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

          public function imprimirpedido($pedido){

           $IdEmpresa = Auth::user()->IdEmpresa;

           $empresanegocios = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

           $cab_pedido = DB::tABLE('pedidos')
           ->where('ped_id',$pedido)
           ->first();

           $mesa= DB::tABLE('mesas')->where('mes_id',$cab_pedido->mes_id)->first();

           
           $impresoras = DB::tABLE('configuracion_impresoras')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();



           if($empresanegocios->impresion =='Web'){

               $detalle = DB::tABLE('pedidos_detalle')->where('ped_id',$pedido)
               ->join('productos','pedidos_detalle.IdProducto','productos.IdProducto')
               ->leftjoin('categorias','categorias.cat_id','productos.cat_id')
               ->where('pedidos_detalle.impreso','imprimir')
               ->get();

                $buscardetalle = pedidos_detalle::findOrFail($det->ped_det_id);
                       $buscardetalle->impreso ='impreso';
                       $buscardetalle->update();

               return view('empresas.comprobantes.'.$IdEmpresa.'.pedido',compact('cab_pedido','detalle','mesa'));

           }else{

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

           

           }

    }

    public function pedidoscocina(){
        
        $pedidos = DB::tABLE('pedidos')
      ->join('cat_estado_pedido as cep','cep.est_ped_id','pedidos.est_ped_id')
      ->leftjoin('mesas','mesas.mes_id','pedidos.mes_id')
      ->where('pedidos.id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->where('pedidos.est_ped_id','1')
      ->orderby('ped_id','asc')
      ->get();

      $detalles = DB::tABLE('pedidos')->select('pronom','pedidos.ped_id','detalle','cantidad')
        ->join('pedidos_detalle as tbpd','tbpd.ped_id','pedidos.ped_id')
        ->join('productos as tbp','tbp.IdProducto','tbpd.IdProducto')
      ->join('cat_estado_pedido as cep','cep.est_ped_id','pedidos.est_ped_id')
      ->where('pedidos.id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->where('pedidos.est_ped_id','1')
      ->orderby('ped_id','desc')
      ->get();

    
      return view('empresas.puntosventas.pedidoscocina',compact('pedidos','detalles'));
    
    }


    public function entregarpedido($id,Request $request){


      $pedidos = pedidos::findOrFail($id);
      $pedidos->est_ped_id = '5';
      $pedidos->fecha_hora_entrega = now()->format('Y-m-d H:i:s');
      $pedidos->update();

      if(Auth::User()->hasRole('repartidor') ){
          return Redirect::to('/delivery');

      }else{
         return Redirect::to('/listos');

      }
     
    }

  public function preparadopedido($id,Request $request){

      $pedidos = pedidos::findOrFail($id);
      $pedidos->est_ped_id = '2';
      $pedidos->fecha_hora_preparado = now()->format('Y-m-d H:i:s');
      $pedidos->update();

      return Redirect::to('/cocina');

    }

    public function programarenvio(Request $request){
       $repartidor = $request->get('repartidor');
       $items = $request->get('item');

       foreach ($items as $item) {
          $pedidos = pedidos::findOrFail($item);
          $pedidos->repartidor = $repartidor;
          $pedidos->update();
       }

       return Redirect::to('/delivery');

    }

    public function reprogramarenvio($id,Request $request){


      $pedidos = pedidos::findOrFail($id);
      $pedidos->est_ped_id = '8';
      $pedidos->update();

      return Redirect::to('/envios');


    }

    public function pedidosgeneral()
    {
        
      $pedidos = DB::tABLE('pedidos')
      ->join('cat_estado_pedido as cep','cep.est_ped_id','pedidos.est_ped_id')
      ->leftjoin('mesas','mesas.mes_id','pedidos.mes_id')
      ->where('pedidos.IdEmpresa',Auth::user()->IdEmpresa)
      ->where('pedidos.est_ped_id','2')
      ->orderby('ped_id','desc')
      ->get();

      $detalles = DB::tABLE('pedidos')->select('pronom','pedidos.ped_id','detalle')
        ->join('pedidos_detalle as tbpd','tbpd.ped_id','pedidos.ped_id')
        ->join('productos as tbp','tbp.IdProducto','tbpd.IdProducto')
      ->join('cat_estado_pedido as cep','cep.est_ped_id','pedidos.est_ped_id')
      ->where('pedidos.IdEmpresa',Auth::user()->IdEmpresa)
      ->where('pedidos.est_ped_id','2')
      ->orderby('ped_id','desc')
      ->get();

      return view('empresas.puntosventas.pedidosgeneral',compact('pedidos','detalles'));
    
    }

     public function delivery()
    {
        
        if(Auth::User()->hasRole('admin') ||   Auth::User()->hasRole('caja')){
            $pedidos = DB::tABLE('pedidos')
            ->leftjoin('cat_estado_pedido as cep','cep.est_ped_id','pedidos.est_ped_id')
            ->leftjoin('users','users.IdUsuario','pedidos.repartidor')
            ->leftjoin('mesas','mesas.mes_id','pedidos.mes_id')
            ->where('pedidos.IdEmpresa',Auth::user()->IdEmpresa)
            ->where('pedidos.est_ped_id','2')
            ->where('pedidos.ped_tip','DELIVERY')
            ->orderby('ped_id','desc')
            ->get();
        }elseif(Auth::User()->hasRole('repartidor')){
            $pedidos = DB::tABLE('pedidos')
            ->leftjoin('cat_estado_pedido as cep','cep.est_ped_id','pedidos.est_ped_id')
            ->leftjoin('users','users.IdUsuario','pedidos.repartidor')
            ->leftjoin('mesas','mesas.mes_id','pedidos.mes_id')
            ->where('pedidos.IdEmpresa',Auth::user()->IdEmpresa)
            ->where('pedidos.est_ped_id','2')
            ->where('pedidos.ped_tip','DELIVERY')
            ->where('repartidor',Auth::user()->IdUsuario)
            ->orderby('ped_id','desc')
            ->get();

        }
      

      $detalles = DB::tABLE('pedidos')->select('pronom','pedidos.ped_id','detalle')
        ->leftjoin('pedidos_detalle as tbpd','tbpd.ped_id','pedidos.ped_id')
        ->leftjoin('productos as tbp','tbp.IdProducto','tbpd.IdProducto')
      ->leftjoin('cat_estado_pedido as cep','cep.est_ped_id','pedidos.est_ped_id')
      ->where('pedidos.IdEmpresa',Auth::user()->IdEmpresa)
      ->where('pedidos.est_ped_id','2')
      ->where('pedidos.ped_tip','Delivery')
      ->orderby('ped_id','desc')
      ->get();


      $repartidores = DB::tABLE('users')->join('role_user','users.IdUsuario','role_user.user_IdUsuario')->where('role_id','8')->get();
      

      return view('empresas.puntosventas.pedidosdelivery',compact('pedidos','detalles','repartidores'));
    
    }

        
    public function generarqr($comprobante){


    $cabecera = cpe_cabecera::findOrFail($comprobante);
    $params = [
     $cabecera->IdEmpresa,
     $cabecera->tdocod,
     $cabecera->serdoc,
     $cabecera->numdoc,
      number_format($cabecera->ccaigv, 2, '.', ''),
      number_format($cabecera->ccaitv, 2, '.', ''),
     $cabecera->ccafem,
     $cabecera->tdicod,
     $cabecera->ccandi,
    ];

    $content = implode('|', $params).'|';

   $cabecera->ccaqr = $content;
   $cabecera->update();

     // $numdoc = str_pad($cabecera->numdoc,8,"0", STR_PAD_LEFT);
      $numdoc = $cabecera->numdoc;
      $qrfile =  'QR-'.$cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.png'; 

    
      $rucemp = trim(Auth::user()->IdEmpresa);
      $ruta = public_path().'/qr/';
      $file = $ruta.$qrfile;
      
      return \QRCode::text($cabecera->ccaqr)->setMargin(1)->setSize(7)->setOutFile($file)->png();

    }

   

  public function consultarticket($res_id){

      self::consultar_ticket($res_id);

     return Redirect::to('/listarresumenes');


  }

  public function enviarsunat(Request $request){

    $items = $request->get('items');

    foreach ($items as $item){
      
      $bus = cpe_cabecera::findOrFail($item);

      if($bus->tdocod=='01' || $bus->tdocod=='03'){

         $bus->generar_xml_boleta_factura($item);

      }elseif($bus->tdocod=='07' || $bus->tdocod=='08'){

          $bus->generar_xml_nota($item);

      }

     


      try{

        $bus->enviar_sunat($item);

    }catch(\Exception $e){


    }
      
     // self::generarcomprobante($item);

    }

     return response()->json(['mensaje' => 'Enviado']);
  }


    public function generarnotapdf($nota){

      $cabpdf = DB::tABLE('cpe_nota')
      ->select('cpe_cabecera.id_empresa_negocio','cpe_nota.serdoc','cpe_nota.numdoc','cpe_nota.ccanom','cpe_nota.ccandi','cpe_nota.IdEmpresa','cpe_nota.tdocod','cpe_cabecera.tdicod','tdodes','cpe_nota.ccafem','cpe_nota.moncod','cpe_nota.ccatvg','cpe_nota.ccaitv','cpe_nota.ccatve','cpe_nota.ccatvi','cpe_nota.ccaigv','cpe_cabecera.serdoc as sermod','cpe_cabecera.numdoc as nummod','cpe_nota.ccaobs','direccion','tipo_documento_identidad.tdides','cpe_nota.ccatexo')
      ->join('cpe_cabecera','cpe_cabecera.IdCpe_cabecera','cpe_nota.IdCpe_cabecera')
      ->join('tipo_documento','tipo_documento.tdocod','cpe_nota.tdocod')
      ->join('tipo_documento_identidad','tipo_documento_identidad.tdicod','cpe_cabecera.tdicod')
      ->where('IdCpe_nota',$nota)
      ->first();



      //$numdoc = str_pad($cabpdf->numdoc,8,"0", STR_PAD_LEFT);

      $numdoc = $cabpdf->numdoc;

      $nompdffile=$cabpdf->IdEmpresa.'-'.$cabpdf->tdocod.'-'.$cabpdf->serdoc.'-'.$numdoc.'.pdf'; 


      $rutpdfile = public_path().'/pdf/';

      $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);

      $logo = public_path()."/imagenes/logos/".$empresa->LogEmpresa;

      $qrfile =  'QR-'.$empresa->IdEmpresa.'-'.$cabpdf->tdocod.'-'.$cabpdf->serdoc.'-'.$numdoc.'.png'; 

      $imgqr = "/qr/".$qrfile;

      $sucursal = DB::tABLE('empresa_negocios')
      ->leftjoin('formatos_comprobantes','formatos_comprobantes.cod_for_com','empresa_negocios.cod_for_com')
      ->where('id_empresa_negocio',$cabpdf->id_empresa_negocio)->first();


      $detpdf = DB::tABLE('cpe_nota_detalle')
      ->where('IdCpe_nota',$nota)
      ->get();

      $view = \View::make('formatos_comprobantes.'.$sucursal->descripcion, compact('cabpdf','detpdf','empresa','sucursal','logo','imgqr'));

      $pdf = \App::make('dompdf.wrapper');
      $contenido = $view->render();
      $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutpdfile.$nompdffile);

      return 'realizado';

    }



  public function generarpdfgeneral($venta){


      $rucemp =Auth::user()->IdEmpresa;
      $rutapdf = public_path().'/pdf/';

      $empresa = Empresa::findOrFail($rucemp);

      $sucursal = DB::tABLE('empresa_negocios')
      ->leftjoin('formatos_comprobantes','formatos_comprobantes.cod_for_com','empresa_negocios.cod_for_com')
      ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->first();
     

      $cabpdf = DB::tABLE('cpe_cabecera')
      ->leftjoin('moneda as mon','cpe_cabecera.moncod','=','mon.moncod')
      ->leftjoin('tipo_documento','tipo_documento.tdocod','cpe_cabecera.tdocod')
      ->leftjoin('tipo_documento_identidad','tipo_documento_identidad.tdicod','cpe_cabecera.tdicod')
      ->where('IdCpe_cabecera',$venta)
      ->first();


      $detpdf = DB::tABLE('cpe_detalle')->select('cdevve','ubicacion','cpe_detalle.procod','cpe_detalle.cdecan','cpe_detalle.umecod','cdedes','cdevun','cdeigv','cdepve','cdevve','cdepuni','cpe_detalle.IdProducto')
      ->leftjoin('productos','productos.IdProducto','cpe_detalle.IdProducto')
      ->where('IdCpe_cabecera',$venta)->get();

   //   dd($detpdf);

      $cliente= DB::tABLE('cliente as cli')
      ->leftjoin('cpe_cabecera as c','c.clicod','=','cli.clicod')
      ->where('IdCpe_cabecera','=',$venta)
      ->where('cli.clicod','=',$cabpdf->clicod)
      ->first();
               

       // $numdoc = str_pad($cabpdf->numdoc,8,"0", STR_PAD_LEFT);
       $numdoc = $cabpdf->numdoc;

      $nompdffile=$rucemp.'-'.$cabpdf->tdocod.'-'.$cabpdf->serdoc.'-'.$numdoc.'.pdf'; 

      $moneda = DB::tABLE('moneda')->where('moncod','=',$cabpdf->moncod)->first();


      $totalletras= MontoLetras::convertir(number_format($cabpdf->ccaitv,'2','.',''),$moneda->monnom,'Centimos');

    
   
      $qrfile =  'QR-'.$rucemp.'-'.$cabpdf->tdocod.'-'.$cabpdf->serdoc.'-'.$numdoc.'.png'; 

      $imgqr = "/qr/".$qrfile;

      if(file_exists($rutapdf.$nompdffile)){

        unlink($rutapdf.$nompdffile);

      }
      
    //  return view('formatos_comprobantes.comprobante', compact('cabpdf','detpdf','cliente','totalletras','empresa','imgqr','sucursal','qrfile'));

      if($cabpdf->tdocod =='15'){

        $view = \View::make('formatos_comprobantes.A4_comprobante_1',compact('cabpdf','detpdf','cliente','totalletras','empresa','imgqr','sucursal','qrfile'));

      }elseif($cabpdf->tdocod=='80' || $cabpdf->tdocod=='70') {

         $vehiculo = DB::tABLE('tipos_vehiculos')
     ->leftjoin('marcas','marcas.mar_id','tipos_vehiculos.mar_id')
     ->where('placa',$cabpdf->placa)->first();
        

          $view = \View::make('empresas.comprobantes.general.ordenes', compact('cabpdf','detpdf','cliente','totalletras','empresa','imgqr','sucursal','qrfile','vehiculo'));


    }elseif($cabpdf->tdocod=='03' || $cabpdf->tdocod=='01' || $cabpdf->tdocod=='07' || $cabpdf->tdocod=='08' ){

         $view = \View::make('formatos_comprobantes.'.$sucursal->descripcion, compact('cabpdf','detpdf','cliente','totalletras','empresa','imgqr','sucursal','qrfile'));
 
    }
      
      
      
                  
      $pdf = \App::make('dompdf.wrapper');
      $contenido = $view->render();
      $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

    
   

        return $nompdffile;
    }

    public function generarcodigo($venta){


      $rucemp =Auth::user()->IdEmpresa;
      $rutapdf = public_path().'/pdf/';

      $empresa = Empresa::findOrFail($rucemp);

      $sucursal = DB::tABLE('empresa_negocios')
      ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
     

      $cabpdf = DB::tABLE('cpe_cabecera')
      ->leftjoin('moneda as mon','cpe_cabecera.moncod','=','mon.moncod')
      ->leftjoin('tipo_documento','tipo_documento.tdocod','cpe_cabecera.tdocod')
      ->where('IdCpe_cabecera',$venta)
      ->first();

      $cliente= DB::tABLE('cliente as cli')
      ->leftjoin('cpe_cabecera as c','c.clicod','=','cli.clicod')
      ->where('IdCpe_cabecera','=',$venta)
      ->where('cli.clicod','=',$cabpdf->clicod)
      ->first();
      

      $barcode = new BarcodeGenerator();
      $barcode->setText($placa);
      $barcode->setType(BarcodeGenerator::Gs1128);
      $barcode->setNoLengthLimit(true);
      $barcode->setAllowsUnknownIdentifier(true);
      $code = $barcode->generate();

      $data = base64_decode($code);
      $filepath = "barcode_".$cabpdf->IdCpe_cabecera.'.png'; 
      file_put_contents($filepath, $data);

       // $numdoc = str_pad($cabpdf->numdoc,8,"0", STR_PAD_LEFT);
       $numdoc = $cabpdf->numdoc;
      
      $nompdffile=$rucemp.'-'.$cabpdf->tdocod.'-'.$cabpdf->serdoc.'-'.$numdoc.'.pdf'; 

      $moneda = DB::tABLE('moneda')->where('moncod','=',$cabpdf->moncod)->first();


      $totalletras= MontoLetras::convertir(number_format($cabpdf->ccaitv,'2','.',''),$moneda->monnom,'Centimos');

      //$numdoc = str_pad($cabpdf->numdoc,8,"0", STR_PAD_LEFT);

      $numdoc = $cabpdf->numdoc;
        
      $qrfile =  'QR-'.$rucemp.'-'.$cabpdf->tdocod.'-'.$cabpdf->serdoc.'-'.$numdoc.'.png'; 

    
      
      $view = \View::make('formatos_comprobantes.comprobante', compact('cabpdf','detpdf','cliente','totalletras','empresa','imgqr','sucursal','qrfile'));

                  
      $pdf = \App::make('dompdf.wrapper');
      $contenido = $view->render();
      $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

    
  
        return 'realizado';
    }


    public function descargar($venta,$tipo)
    {

      $cabecera = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$venta)->first();

      $codfact = $cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.str_pad($cabecera->numdoc,8,"0", STR_PAD_LEFT);

      $codfact = $cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$cabecera->numdoc;


      $rucemp = trim(Auth::user()->IdEmpresa);

      $rutpdfile = public_path().'/pdf/';
      $rutxmlfile = public_path().'/xml/';
      $rutcdrfile = public_path().'/cdr/';
     
      $file= $rutpdfile.$codfact.'.pdf';
      $xml= $rutxmlfile.$codfact.'.xml';
      $cdr= $rutcdrfile.'R-'.$codfact.'.zip';


    if($tipo =='pdf'){


        $bus_com = cpe_cabecera::findOrFail($venta);
       

      if(file_exists($file))
      {

        unlink($file);
         $bus_com->generar_nuevo_qr($venta);
        $bus_com->generarpdfgeneral($venta);

        $headers = array(
              'Content-Type: application/pdf',
            );

        return response()->download($file);

      }else{

         $bus_com->generar_nuevo_qr($venta);
        $bus_com->generarpdfgeneral($venta);
        $bus_com->generarpdfgeneral($venta);

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

      $bus_com = cpe_cabecera::findOrFail($venta);
      $bus_com->consultar_cdr($venta);

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


    public function modificarcotizacion($id){

        
        $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first(); 

        $creditos = DB::tABLE('credito_dias')->get();

        $documentos = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderby('tdicod')->get();

        $rucemp = trim(Auth::user()->IdEmpresa);

        $categorias = DB::tABLE('categorias')
      //  ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->orderby('cat_nom','asc')
        ->get();

        $clientes = DB::tABLE('cliente')->get();



        $mediospagos = DB::tABLE('medios_pagos')->get();

        $comprobante = DB::tABLE('tipo_documento')->get();

        $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();

          $unidades = DB::tABLE('unidad_medida')
        ->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

       
        $mozos = DB::tABLE('users')->join('role_user','role_user.user_IdUsuario','users.IdUsuario')->where('role_id','5')->get();

         $cabecera = DB::tABLE('cpe_cabecera')
         ->leftjoin('cliente','cliente.clicod','cpe_cabecera.clicod')
         ->where('IdCpe_cabecera',$id)
         ->first();

         $detalle = DB::tABLE('cpe_detalle')
         ->leftjoin('productos','productos.IdProducto','cpe_detalle.IdProducto')
         ->where('IdCpe_cabecera',$id)
         ->get();


        return view('empresas.puntosventas.modificarcotizacion',compact('categorias','comprobante','tipodocumento','igv','unidades','unidades','tdocod','cpe','mozos','creditos','mediospagos','clientes','documentos','datos','cabecera','detalle'));
    

    }


    public function modificarorden($id){

    
     $documentos = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderby('tdicod')->get();

          $clientes = DB::tABLE('cliente')->get();

        $rucemp = trim(Auth::user()->IdEmpresa);
        $categorias = DB::tABLE('categorias')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();
        $comprobante = DB::tABLE('tipo_documento')->get();
        $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();
         $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $mozos = DB::tABLE('users')->join('role_user','role_user.user_IdUsuario','users.IdUsuario')->where('role_id','5')->get();

         $cabecera = DB::tABLE('cpe_cabecera')
         ->leftjoin('cliente','cliente.clicod','cpe_cabecera.clicod')
         ->where('IdCpe_cabecera',$id)
         ->first();

         $detalle = DB::tABLE('cpe_detalle')
         ->leftjoin('productos','productos.IdProducto','cpe_detalle.IdProducto')
         ->where('IdCpe_cabecera',$id)
         ->get();

        $mozos = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        ->where('role_id','5')
        ->get();

        $tecnicos = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        ->where('role_id','5')
        ->get();

        $equipos = DB::tABLE('tipo_equipo')->get();

        $pagos = DB::tABLE('venta_medio_pago')->where('IdCpe_cabecera',$id)->get();


        $creditos = DB::tABLE('credito_dias')->get();

        $mediospagos = DB::tABLE('medios_pagos')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();

        return view('empresas.puntosventas.modificarorden',compact('categorias','comprobante','tipodocumento','igv','unidades','unidades','tdocod','cpe','mozos','cabecera','detalle','mozos','tecnicos','equipos','creditos','mediospagos','pagos','documentos','clientes'));
    

    }

     public function cobrarorden($id){

        
         $documentos = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderby('tdicod')->get();

          $clientes = DB::tABLE('cliente')->get();

        $rucemp = trim(Auth::user()->IdEmpresa);
        $categorias = DB::tABLE('categorias')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();
        $comprobante = DB::tABLE('tipo_documento')->get();
        $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();
         $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $mozos = DB::tABLE('users')->join('role_user','role_user.user_IdUsuario','users.IdUsuario')->where('role_id','5')->get();

         $cabecera = DB::tABLE('cpe_cabecera')
         ->leftjoin('cliente','cliente.clicod','cpe_cabecera.clicod')
         ->where('IdCpe_cabecera',$id)
         ->first();

         $detalle = DB::tABLE('cpe_detalle')
         ->leftjoin('productos','productos.IdProducto','cpe_detalle.IdProducto')
         ->where('IdCpe_cabecera',$id)
         ->get();

        $mozos = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        ->where('role_id','5')
        ->get();

        $tecnicos = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        ->where('role_id','5')
        ->get();

        $equipos = DB::tABLE('tipo_equipo')->get();

        $pagos = DB::tABLE('venta_medio_pago')->where('IdCpe_cabecera',$id)->get();


        $creditos = DB::tABLE('credito_dias')->get();

        $mediospagos = DB::tABLE('medios_pagos')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();

        return view('empresas.puntosventas.cobrarorden',compact('categorias','comprobante','tipodocumento','igv','unidades','unidades','tdocod','cpe','mozos','cabecera','detalle','mozos','tecnicos','equipos','creditos','mediospagos','pagos','clientes','documentos'));
    

    }


    public function cobrarcotizacion($id){

   
       $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first(); 

        $creditos = DB::tABLE('credito_dias')->get();

        $documentos = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderby('tdicod')->get();

        $rucemp = trim(Auth::user()->IdEmpresa);

        $categorias = DB::tABLE('categorias')
      //  ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->orderby('cat_nom','asc')
        ->get();

        $clientes = DB::tABLE('cliente')->get();



        $mediospagos = DB::tABLE('medios_pagos')->get();

        $comprobante = DB::tABLE('tipo_documento')->get();

        $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();

          $unidades = DB::tABLE('unidad_medida')
        ->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

       
        $mozos = DB::tABLE('users')->join('role_user','role_user.user_IdUsuario','users.IdUsuario')->where('role_id','5')->get();

         $cabecera = DB::tABLE('cpe_cabecera')
         ->leftjoin('cliente','cliente.clicod','cpe_cabecera.clicod')
         ->where('IdCpe_cabecera',$id)
         ->first();

         $detalle = DB::tABLE('cpe_detalle')
         ->leftjoin('productos','productos.IdProducto','cpe_detalle.IdProducto')
         ->where('IdCpe_cabecera',$id)
         ->get();


        return view('empresas.puntosventas.cobrarcotizacion',compact('categorias','comprobante','tipodocumento','igv','unidades','unidades','tdocod','cpe','mozos','creditos','mediospagos','clientes','documentos','datos','cabecera','detalle'));
    

    }


     public function editarventa($id){

             $cabecera = DB::tABLE('cpe_cabecera')
            ->where('IdCpe_cabecera',$id)
            ->first();

              $rucemp = Auth::user()->IdEmpresa;

            $cuotas = DB::tABLE('ventas_cuotas')
            ->where('IdCpe_cabecera',$id)
            ->orderby('ven_cuo_id','asc')
            ->get();

            $gastos = DB::tABLE('tipo_gastos')->get();

        
            $senudoc = DB::tABLE('empresa_negocios')
                    ->select('serieguia','numeroguia')
                    ->where('IdEmpresa','=',$rucemp)
                    ->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)
                    ->first(); 
     
        if($cabecera->tdocod =='01' || $cabecera->tdocod=='03'){

            if($cabecera->ccacodsun ==='0'){

          
                  return Redirect::to('/SisFact')->with('danger','EL COMPROBANTE YA FUE ENVIADO Y ACEPADO POR SUNAT, NO SE PUEDE MODIFICAR'); 

               

            
              }elseif($cabecera->ccacodsun >= 2000 && $cabecera->ccacodsun <= 3999){

                 return Redirect::to('/SisFact')->with('danger','EL COMPROBANTE YA FUE ENVIADO Y SE ENCUENTRA ANULADO O RECHAZADO, NO SE PUEDE MODIFICAR'); 

                

              }
        }
    
        $mediospagos = DB::tABLE('medios_pagos')->get();

        $motivos = DB::tABLE('motivo_traslado')
        ->orderBy('motivo','asc')->get();

        $modalidades = DB::tABLE('modalidad_traslado')
        ->orderBy('modalidad','asc')->get();

          $combustible = DB::tABLE('combustible')->get();


        $clientes = DB::tABLE('cliente')->get();

        $detalle = DB::tABLE('cpe_detalle')
        ->where('IdCpe_cabecera',$cabecera->IdCpe_cabecera)
        ->get();

        $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$cabecera->id_empresa_negocio)->first();

        $almacen = DB::tABLE('almacenes')->where('id_empresa_negocio',$cabecera->id_empresa_negocio)->where('predeterminado','1')->first();

        $vendedores = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
       // ->where('role_id','5')
        ->get();

        $creditos = DB::tABLE('credito_dias')->get();

        $documentos = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderby('tdicod')->get();

          $comprobante = DB::tABLE('tipo_documento')->get();

         $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();

        $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();

        $docidentidad = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

        $unidades = DB::tABLE('unidad_medida')
        ->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

          $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);

        $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$cabecera->id_empresa_negocio)->get();


        $marcas = DB::tABLE('marcas')->get();

        $modelos = DB::tABLE('modelos')->get();

        $tecnicos = DB::tABLE('tecnicos')->get();
         $combustible = DB::tABLE('combustible')->get();

         $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();


      

        return view('empresas.puntosventas.editarventa',compact('gastos','tipodocumento','creditos','documentos','almacen','cabecera','detalle','datos','almacenes','vendedores','clientes','mediospagos','unidades','datos','empresa','senudoc','rucemp','motivos','modalidades','docidentidad','marcas','modelos','tecnicos','combustible','comprobante','monedas'));

      
      


    }

      

  public function actualizarventa(Request $request)
    {

        $tdicod = $request->get('tdicod');
        $tdocod = $request->get('tdocod');
        $mondoc = $request->get('moncod');
        $total = $request->get('total');
        $id_almacen = $request->get('id_almacen');
        $fecemi = $request->get('fecEmi');
        $ade_pro = $request->get('ade_pro');
        $sal_pro = $request->get('sal_pro');
        $fecven = $request->get('fecVen');
        $tipo_venta = $request->get('tipoventa');
        $observaciones = $request->get('observaciones');
        $pagar = $request->get('pagar');
        $vuelto = $request->get('vuelto');
        $monto = $request->get('monto');
        $medio = $request->get('medio');
        $descuento = $request->get('desc');
        $estadopago = $request->get('estadopago');
        $cliruc = $request->get('clinum');
        $fecha = $request->get('fecEmi');
        $clinom = $request->get('clinom');
        $clidir = $request->get('clidir');
        $clicor = $request->get('clicor');
        $clicor2 = $request->get('clicor2');
        $clicor3 = $request->get('clicor3');
        $clicor4 = $request->get('clicor4');
        $mon_cuo = $request->get('mon_cuo');
        $fec_cuo = $request->get('fec_cuo');
       
        $topcod = '0101';

        $cont_carac = strlen($cliruc);
        $obt_dig = substr(trim($cliruc), 0, 2);


        if($tdocod=='01'){

            if($cont_carac =='11' && ($obt_dig=='10' || $obt_dig=='20' || $obt_dig=='15' || $obt_dig=='17') && $tdicod=='6'){

              
            }else{
                  return response()->json(['estado'=>'error','mensaje'=>'TIPO DE DOCUMENTO NO PERMITIDO PARA EMITIR UNA FACTURA']);
            }
        }
            

        if(!empty($request->get('vendedor'))){

          $id_vendedor = $request->get('vendedor');

        }else{
      
            $id_vendedor = Auth::user()->IdUsuario;
        
        }
        $bus_ven = DB::tABLE('users')->where('IdUsuario',$id_vendedor)->first();

        $bus_alm = DB::tABLE('almacenes')->where('id_almacen',$request->get('id_almacen'))->first();

        $almacen = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();

        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$almacen->id_empresa_negocio)->first();

        $rucemp = trim(Auth::user()->IdEmpresa);

        $empresa = Empresa::findOrFail($rucemp);
        //DETALLE 
        $unidades = $request->get('unid');
        $proid = $request->get('proid');
        $detpro = $request->get('pronom');
        $vunit = $request->get('provun');
        $puni = $request->get('propun');
        $vtot = $request->get('itemtotal');
        $cantidades = $request->get('cant');
      

       


      //Datos del cliente
        //Datos de cabecera
       
        $buscre = DB::tABLE('credito_dias')->where('cre_dia_id',$estadopago)->first();

      //buscar almacen

        $bus_alm = DB::tABLE('almacenes')->where('id_almacen',$id_almacen)->first();

      //Datos del Cliente
      
       

        $moneda = DB::tABLE('moneda')->where('moncod','=',$mondoc)->first();


      //Registrar el tipo de cambio enviado a través del formulario si no existe.
        if($mondoc !='PEN'){
          $camdoc = $request->get('camdoc');
          $tipcam = tipocambio::FirstOrCreate(['FecTipCambio'=>$fecemi],['CamCompra'=>$camdoc]);
        }else{
          $camdoc=0;
        }

        
        $oldDate = strtotime($request->get('clifecnac'));

        $mes = date('m',$oldDate);


      //Registrar el cliente enviado a través del formulario si no existe
      $cliente = Cliente::UpdateOrCreate(['clinum'=>$cliruc],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$clicor,'clicor2'=>$clicor2,'clicor3'=>$clicor3,'clicor4'=>$clicor4,'rucemp'=>$rucemp,'tdicod'=>$tdicod,'telefono'=>$request->get('clitel'),'clifecnac'=>$request->get('clifecnac'),'mes_nac'=>$mes]);


        $cabecera = cpe_cabecera::findOrFail($request->get('ped_id'));
      //  $cabecera->tdocod = $tdocod;
        $cabecera->topcod = $topcod;
        $cabecera->ccafem = $fecemi;

        if($tdocod =='15'){
          $cabecera->estado ='PENDIENTE';
        }

        if($buscre->cre_dia_tip=='CONTADO' || $buscre->cre_dia_tip=='PERSONALIZADO'){
            $cabecera->ccafve = $request->get('fecVen');
        }else{
            $cabecera->ccafve = date('Y-m-d',strtotime($fecemi."+ ".$buscre->cre_dia_fac." days"));
        }

        if($buscre->cre_dia_tip=='CONTADO'){
            $cabecera->totalcontado = $total;
            $cabecera->totalcredito = '0';
        }else{
            $cabecera->totalcredito = $total;
            $cabecera->totalcontado = '0';
        }
      
        $cabecera->ccaobs = $observaciones;
        //$cabecera->ccacde = $request->get();
        $cabecera->tdicod = $tdicod;
        $cabecera->ccandi = $cliruc;
        $cabecera->ccanom = $clinom;
        $cabecera->ped_ref = $request->get('ped_id');
        $cabecera->moncod = $mondoc;
        $cabecera->direccion = $clidir;
        $cabecera->cre_dia_id = $estadopago;
        $cabecera->clicorcli = $clicor;
        $cabecera->clicorcli2 = $clicor2;
        $cabecera->guia_remision = $request->get('guia_remision');
        $cabecera->placa  = $request->get('placa_comp');
        $cabecera->clicorcli3 = $clicor3;
        $cabecera->clicorcli4 = $clicor4;
        $cabecera->ade_pro = $ade_pro;
        $cabecera->sal_pro = $sal_pro;

        if(!empty($bus_cot)){
          $cabecera->referencia = $bus_cot->serdoc.'-'.$bus_cot->numdoc;
        }

        $cabecera->tipcambio = $camdoc;
       if($sucursal->tip_igv_pred =='10'){
            $cabecera->ccatvg =  $total/1.1055;
            $cabecera->ccaigv = '0.00';
        }

        if($sucursal->tip_igv_pred =='20'){
            $cabecera->ccatexo =  $total;
            $cabecera->ccaigv = '0.00';
        }
        
        $cabecera->ccatinaf =  '0.00';
        $cabecera->ccaitv = $total;
        $cabecera->IdUsuario_ven = $id_vendedor;
        $cabecera->vendedor = $bus_ven->name.' '.$bus_ven->apeusu;
        $cabecera->id_empresa_negocio = Auth::user()->id_empresa_negocio;
    
        

        $cabecera->clicod = $cliente->clicod;
     //   $cabecera->icbper = $totalicbper;
        $cabecera->ccaobs = $observaciones;
        $cabecera->paga = $pagar;
        
        if(!empty($tip_cam->CamVenta)){
          $cabecera->tipcambio = $tip_cam->CamVenta;
        }
        
        $cabecera->vuelto = $vuelto;

        if($buscre->cre_dia_tip=='CONTADO'){
          $cabecera->estadopago = 'CONTADO';
        }else{
          $cabecera->estadopago = 'CREDITO';
        }
        
        
             $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->IdEmpresa =  $rucemp;

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



          
        
        $codfact = $cabecera->IdCpe_cabecera; 
          
        DB::tABLE('ventas_cuotas')->where('IdCpe_cabecera',$codfact)->delete();

        if($buscre->cre_dia_tip=='CREDITO' || $buscre->cre_dia_tip=='PERSONALIZADO' ){

            if(!empty($mon_cuo)){
                $i=0;
                foreach ($mon_cuo as $key => $mc){

                    $i=$i+1;

                    DB::tABLE('ventas_cuotas')
                    ->insert([
                        'ven_cuo_num'=>$i,
                        'ven_cuo_fec_ven'=>$fec_cuo[$key],
                        'ven_cuo_mon'=>$mc,
                        'IdCpe_cabecera'=>$codfact
                    ]);
                }
            }else{

                DB::tABLE('ventas_cuotas')
                    ->insert([
                        'ven_cuo_num'=>'1',
                        'ven_cuo_fec_ven'=>$cabecera->ccafve,
                        'ven_cuo_mon'=>$cabecera->ccaitv,
                        'IdCpe_cabecera'=>$codfact
                ]);

            }
        }

        $unidades = $request->get('unid');
        $proid = $request->get('proid');
        $detpro = $request->get('pronom');
        $vunit = $request->get('provun');
        $puni = $request->get('propun');
        $vtot = $request->get('itemtotal');
        $cantidades = $request->get('cant');
        $IdCpe_detalle = $request->get('IdCpe_detalle');

   
        $registros = DB::tABLE('cpe_detalle')->where('IdCpe_cabecera',$request->get('ped_id'))->get();

        DB::tABLE('venta_medio_pago')->where('IdCpe_cabecera',$request->get('ped_id'))->delete();

        foreach ($registros as $reg) {
      
        if(empty($reg->IdProducto) || $reg->IdProducto=='0'){

        }else{
             $buspro = DB::tABLE('productos')->where('IdProducto',$reg->IdProducto)->first();
          
          if(!empty($buspro->pro_rel)){
            $id = $buspro->pro_rel;
          }else{
            $id = $reg->IdProducto;
          }
    
      
            $stock_prod = DB::tABLE('producto_stock')
            ->where('IdProducto',$id)
            ->where('id_almacen',$bus_alm->id_almacen)
            ->where('id_empresa_negocio',$bus_alm->id_empresa_negocio)
            ->first();
  
            DB::tABLE('producto_stock')
            ->where('IdProducto',$id)
            ->where('id_almacen',$bus_alm->id_almacen)
            ->where('id_empresa_negocio',$bus_alm->id_empresa_negocio)
            ->update(['stock'=>$stock_prod->stock+($reg->cdecan*$reg->cpe_det_factor)]);

        }
         

          
        }


        DB::tABLE('cpe_detalle')->where('IdCpe_cabecera',$request->get('ped_id'))->delete();

        DB::tABLE('movimientos_productos')->where('IdCpe_cabecera',$request->get('ped_id'))->delete();

        //Generar el detalle del comprobante

        foreach($proid as $index => $id) {

            if($id !='0'){

                $codpro = productos::findOrFail($id);
                $codproducto = $codpro->procod;

                   if(empty($codpro->pro_rel)){

                      $id_prod = $codpro->IdProducto;

                    }else{
              
                      $id_prod = $codpro->pro_rel;

                    }

                if($tdocod !='15'){
                  
                 

                $stockprod = DB::tABLE('producto_stock')
                ->where('IdProducto',$id_prod)
                ->where('id_empresa_negocio',$sucursal->id_empresa_negocio)
                ->where('id_almacen',$id_almacen)
                ->first();

               // dd($stockprod);
                if(empty($stockprod)){

                  $stock = 0-($cantidades[$index]*$codpro->factor);

                  $stockprod_act = DB::tABLE('producto_stock')
                  ->insert([
                    'stock'=>$stock,
                    'IdProducto'=>$id_prod,
                    'id_empresa_negocio'=>$sucursal->id_empresa_negocio,
                    'id_almacen'=>$id_almacen]
                  );

                  $sto_ini = '0';
                  
                }else{

                   $stock = $stockprod->stock-($cantidades[$index]*$codpro->factor);

                  $stockprod_act = DB::tABLE('producto_stock')
                  ->where('pro_sto_id',$stockprod->pro_sto_id)
                  ->update(['stock'=>$stockprod->stock-($cantidades[$index]*$codpro->factor)]);

                  $sto_ini = $stockprod->stock_inicial;


                }

              }else{

                $stock='0';

              }

            $detalle = new cpe_detalle;
            $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
            $detalle->umecod = $codpro->umecod;
            $detalle->cpe_det_factor = $codpro->factor;
            $detalle->comision = $sucursal->comision;
            $detalle->cdecan = $cantidades[$index];
            $detalle->procod = $codproducto;
            $detalle->IdProducto = $codpro->IdProducto;
            $detalle->IdProducto_rel = $id_prod;
            $detalle->cdepsu = "";
            $detalle->cdedes = $detpro[$index];
         //   $detalle->pronomobs = $pronomobs[$index];
            $detalle->costo = $codpro->costo;
            $detalle->tigcod = $codpro->tigcod;
            $detalle->icbper = $codpro->icbper;
            $detalle->cpe_det_stock = $stock;
            $detalle->desc_mon = $descuento[$index];


           
           
           /*calcular porcentaje de descuento*/
            if($sucursal->tipo_desc=='1'){
                $desc_mon = $descuento[$index];
                $desc_por = ($descuento[$index]*100)/$puni[$index];
            }elseif($sucursal->tipo_desc=='2'){
                $desc_por = $descuento[$index];
                $desc_mon = $puni[$index]*($descuento[$index]/100);
            }
           
            if($codpro->tigcod =='10'){

              $preciouni = $puni[$index];
              $valoruni = $puni[$index]/1.1055;
              $valorunitario = $puni[$index]/1.1055;

              $valorsubtotal = $vtot[$index]/1.1055;
              $valortotal = $vtot[$index];
             
            }elseif($codpro->tigcod=='20'){

              $preciouni = $puni[$index];
              $valoruni = $puni[$index];
              $valorunitario = $puni[$index];

              $valorsubtotal = $vtot[$index];
              $valortotal = $vtot[$index];
            }

            $valorigvtotal =  $valortotal-$valorsubtotal;
           
           

           /*FIN CALCULAR DESCUENTO*/
            $detalle->valor_unitario = $valorunitario;
            $detalle->por_des = $desc_por;
            $detalle->desc_mon = $desc_mon;
            $detalle->cdepuni = $preciouni;
            $detalle->cdevun = $valoruni;
            $detalle->cdevve = $valortotal;
            $detalle->cdepve = $valorsubtotal;
            $detalle->cdeigv = $valorigvtotal;
            $detalle->fecha_venta = $fecemi;
            $detalle->flete = $codpro->flete;

            if(isset($stockprod)){
                      $detalle->cpe_det_stock_inicial = $stockprod->stock_inicial;
            }
          
            $detalle->save();


            if(isset($stockprod)){

                     DB::tABLE('movimientos_productos')->insert([
                        'IdProducto'=>$id_prod,
                        'precio'=>$preciouni,
                        'cantidad'=>$cantidades[$index]*$codpro->factor,
                        'costo'=>$codpro->costo,
                        'mov_cab_id'=>'',
                        'stock'=>$stock,
                        'IdProducto_rel'=>$id_prod,
                        'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
                        'com_cab_id'=>'',
                        'stock_inicial'=>$sto_ini,
                        'serie'=>$cabecera->serdoc,
                        'numero'=>$cabecera->numdoc,
                        'tdocod'=>$cabecera->tdocod,
                        'tipo'=>'3',
                        'mov_tip'=>'E',
                        'id_empresa_negocio'=>$sucursal->id_empresa_negocio,
                        'id_almacen'=>$id_almacen,
                        'fecha_mov'=>$fecha,
                    ]);

              /*  $movimiento = new movimientos;
                $movimiento->mov_fec = $fecha; 
                $movimiento->mov_tip = 'E';
                $movimiento->mov_mot = 'Venta';
                $movimiento->cantidad = $cantidades[$index];
                $movimiento->unidad = $codpro->umecod;
                $movimiento->comprobante = $sercomp.'-'.$numdoc;
                $movimiento->IdEmpresa = $rucemp;
                $movimiento->id_empresa_negocio = $sucursal->id_empresa_negocio;
                $movimiento->IdProducto = $id;
                $movimiento->tipo='3';
                $movimiento->observacion = "Venta desde Punto de Venta";
                $movimiento->IdUsuario = Auth::user()->IdUsuario;
                $movimiento->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
                $movimiento->stockmov = $stock;
                $movimiento->save();
*/

            }

          


            }else{

            
            $detalle = new cpe_detalle;
            $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
            $detalle->umecod = $unidades[$index];
            $detalle->cdecan = $cantidades[$index];
            $detalle->cdepsu = "";
            $detalle->cdedes = $detpro[$index];
            $detalle->desc_mon = $descuento[$index];

            //$detalle->costo = $codpro->costo;
            $detalle->tigcod = $sucursal->tip_igv_pred;
   //        $detalle->icbper = $codpro->icbper;
        
              /*calcular porcentaje de descuento*/
              $desc_mon='0';
              $desc_por='0';
         
            if($sucursal->tip_igv_pred =='10'){

              $preciouni = $puni[$index];
              $valoruni = $puni[$index]/1.1055;
              $valorunitario = $puni[$index]/1.1055;

              $valorsubtotal = $vtot[$index]/1.1055;
              $valortotal = $vtot[$index];
             
            }elseif($sucursal->tip_igv_pred =='20'){

              $preciouni = $puni[$index];
              $valoruni = $puni[$index];
              $valorunitario = $puni[$index];

              $valorsubtotal = $vtot[$index];
              $valortotal = $vtot[$index];
            }

            $valorigvtotal =  $valortotal-$valorsubtotal;

            $detalle->valor_unitario = $valorunitario;
            $detalle->por_des = $desc_por;
            $detalle->desc_mon = $desc_mon;
            $detalle->cdepuni = $preciouni;
            $detalle->cdevun = $valoruni;
            $detalle->cdevve = $valortotal;
            $detalle->cdepve = $valorsubtotal;
            $detalle->cdeigv = $valorigvtotal;
            $detalle->fecha_venta = $fecemi;
            $detalle->save();


            }

          
        }


          
       

        $gen_xml_pdf = new cpe_cabecera;
        if($tdocod =='01' || $tdocod=='03'){
            $nom_arch= $gen_xml_pdf->generar_xml_boleta_factura($codfact);
            
        }

        
        $cabecera->generar_nuevo_qr($codfact);
        $documento = $cabecera->generarpdfgeneral($codfact);
        


        if($empresa->formato =='TICKET'){         
            if(empty($cabecera->referencia)){
               for($i=1;$i<=$empresa->imp_venta;$i++){
                  if($request->get('opcion')=='0'){
                    self::imprimir($codfact,$tdocod);
                  }
                }
            }
        }elseif($empresa->formato=='A4'){
        
           if($request->get('opcion')=='0'){
             /* exec("c:\WINDOWS\system32\cmd.exe /c START powershell.exe -ExecutionPolicy Bypass Start-Process -FilePath E:\laragon\www\sys_mypesoft\public\pdf/".$documento." -Verb Print");*/
            }
            
        }


        DB::tABLE('cuentas_cobrar')->where('IdCpe_cabecera',$codfact)->delete();

        if($buscre->cre_dia_tip !='CONTADO'){
          self::registrarcuentascobrar($codfact);
        }


        return response()->json(['estado'=>'success','codfact' =>$codfact,'tdocod'=>$tdocod,'mensaje'=>'Comprobante Emitido']);
 
        
    }


	  public function imprimircuenta($pedido){

           $IdEmpresa = Auth::user()->IdEmpresa;

           $empresanegocios = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

           $cab_pedido = DB::tABLE('pedidos')
           ->where('ped_id',$pedido)
		   ->leftjoin('users','users.IdUsuario','pedidos.mozo')
           ->first();

           $mesa= DB::tABLE('mesas')->where('mes_id',$cab_pedido->mes_id)->first();

           
           $impresoras = DB::tABLE('configuracion_impresoras')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();




                $detalle = DB::tABLE('pedidos_detalle')->where('ped_id',$pedido)
               ->join('productos','pedidos_detalle.IdProducto','productos.IdProducto')
               ->leftjoin('categorias','categorias.cat_id','productos.cat_id')
               ->where('categorias.id_empresa_negocio',Auth::user()->id_empresa_negocio)
               ->where('estadoitem','!=','Eliminado')
      
               ->get();

            
         

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


                    }
                   $printer->text("\n");
                    $printer->feed();
                    $printer->cut();
                    $printer->pulse();
                    $printer->close();


                 $buscarpedido = DB::tABLE('pedidos')
                ->where('ped_id',$pedido)
                ->update(['etiqueta' => ""]); 

        

           


    }

     public function enviar_comprobante($codfact,$correo){

        $rucemp = trim(Auth::user()->IdEmpresa);
        $empresa = Empresa::findOrFail($rucemp);
        
        $correo_envio = env('MAIL_USERNAME',$empresa->correo_envio);
        $contrasena_envio = env('MAIL_PASSWORD', $empresa->contrasena_envio);

        $cabpdf = cpe_cabecera::findOrFail($codfact)
        ->where('IdCpe_cabecera',$codfact)
        ->first();


        $cabpdf->generarpdfgeneral($codfact);

        $corcli = $correo;
      
        $cliente = DB::tABLE('cliente')->where('clinum',$cabpdf->ccandi)->where('rucemp',$rucemp)->first();
      
    
        $ruta_pdf = public_path().'/pdf/';
        $ruta_xml = public_path().'/xml/';

        $numdoc = $cabpdf->numdoc;

        Config::set('mail.username', $empresa->correo_envio);
        Config::set('mail.password', $empresa->contrasena_envio);
    
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
          

                $objDemo = new \stdClass();
              
                $objDemo->tipo_comprobante = 'Comprobante Electrónica';
                $objDemo->sender = $empresa->correo_envio;
                $objDemo->receiver = $cliente->clinom;
                $objDemo->invoicepdf = $destino4;
                $objDemo->invoicexml = $destino5;
                $objDemo->empresa = $empresa->NomEmpresa;

                
                if(!empty($corcli)){

                  try{
                       Mail::to($corcli)->send(new FacturacionEmail($objDemo,$destino4,$destino5,$empresa->correo_envio));
                  }catch(\Exception $e){
                    
                  }
              
                }
           
 

          return Redirect::to('/SisFact');
      }

      public function actualizarestado(Request $request){

        $id = $request->get('id');
        $estado = $request->get('est_equ_id');

        if($estado =='3'){

           return Redirect::to('/cobrarorden/'.$id);

        }else{

          $cabecera = cpe_cabecera::findOrFail($id);
          $cabecera->est_equ_id = $estado;
          $cabecera->update();

          
          return Redirect::to('/ordenes');

        }
       

      }
   public function enviar_ose($archivo){

        $urlService = 'https://ose-demo.nubefact.com/ol-ti-itcpe/billService';
        $soap = new SoapClient();
        $soap->setService($urlService);
        $soap->setCredentials('20541158902necosis','adm11700');
        $sender = new BillSender();
        $sender->setClient($soap);

        $xml = file_get_contents(public_path().'/xml/'.$archivo.'.xml');
        $result = $sender->send($archivo, $xml);

       
        if (!$result->isSuccess()) {
            // Error en la conexion con el servicio de SUNAT
            var_dump($result->getError());

            echo 'sdasd';
            return;
        }

        $cdr = $result->getCdrResponse();

      
        file_put_contents(public_path().'/cdr/'.'R-'.$archivo.'.zip', $result->getCdrZip());

        // Verificar CDR (Factura aceptada o rechazada)
        $code = (int)$cdr->getCode();

        
        if ($code === 0) {
            echo 'ESTADO: ACEPTADA'.PHP_EOL;
            if (count($cdr->getNotes()) > 0) {
                echo 'INCLUYE OBSERVACIONES:'.PHP_EOL;
                // Mostrar observaciones
                foreach ($cdr->getNotes() as $obs) {
                    echo 'OBS: '.$obs.PHP_EOL;
                }
            }

        } else if ($code >= 2000 && $code <= 3999) {
            echo 'ESTADO: RECHAZADA'.PHP_EOL;

        } else {
            /* Esto no debería darse, pero si ocurre, es un CDR inválido que debería tratarse como un error-excepción. */
            /*code: 0100 a 1999 */
            echo 'Excepción';
        }

        echo $cdr->getDescription().PHP_EOL;

      }


     public function registraranulacion(Request $request,$comp,$motivo){
      
      $rucemp = Auth::user()->IdEmpresa;

      $empresa = Empresa::findOrFail($rucemp);
      
      $fec_gen = now()->format('Y-m-d');

      $cabecera = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$comp)->first();

      $almacen = DB::tABLE('almacenes')->where('id_empresa_negocio',$cabecera->id_empresa_negocio)->where('predeterminado','1')->first();


      $detalle = DB::tABLE('cpe_detalle')->where('IdCpe_cabecera',$comp)->get();



      if($cabecera->tdocod=='01' || $cabecera->tdocod=='03' || $cabecera->tdocod=='13'){

        foreach ($detalle as $key => $det) {
            
          if(!empty($det->IdProducto)){

            $producto = Productos::findOrFail($det->IdProducto);
            
            if(empty($producto->pro_rel)){

              $id_prod = $producto->IdProducto;

            }else{
          
              $id_prod = $producto->pro_rel;

            }

            $stockprod = DB::tABLE('producto_stock')
            ->where('IdProducto',$id_prod)
            ->where('id_empresa_negocio',$cabecera->id_empresa_negocio)
            ->where('id_almacen',$almacen->id_almacen)
            ->first();

            if(empty($stockprod)){
              
              $stock = 0+($det->cdecan*$producto->factor);
              $stockprod_act = DB::tABLE('producto_stock')
                  ->insert([
                    'stock'=>$stock,'IdProducto'=>$id_prod,
                    'id_empresa_negocio'=>$cabecera->id_empresa_negocio,
                    'id_almacen'=>$almacen->id_almacen]
                  );

            }else{

                $stockprod_act = DB::tABLE('producto_stock')
                ->where('pro_sto_id',$stockprod->pro_sto_id)
                ->update(['stock'=>$stockprod->stock+($det->cdecan*$producto->factor)]);

                $stock = $stockprod->stock+($det->cdecan*$producto->factor);
            }

            $movimiento = new movimientos;
            $movimiento->mov_fec = $fec_gen; 
            $movimiento->mov_tip = 'E';
            $movimiento->mov_mot = 'ANULACION';
            $movimiento->cantidad = $det->cdecan;
            $movimiento->unidad = $det->umecod;
            $movimiento->comprobante = $cabecera->serdoc.'-'.$cabecera->numdoc;
            $movimiento->IdEmpresa = $rucemp;
            $movimiento->id_empresa_negocio = $cabecera->id_empresa_negocio;
            $movimiento->IdProducto = $producto->IdProducto;
            $movimiento->observacion = "Venta desde Punto de Venta";
            $movimiento->IdUsuario = Auth::user()->IdUsuario;
            $movimiento->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
            $movimiento->stockmov = $stock;
            $movimiento->save();


          }
          
          
        } 
      }
     

      if($cabecera->tdocod=='01'){
        
          $resumen = new resumenes;
          $resumen->res_fec_com = $cabecera->ccafem;
          $resumen->res_fec_gen = $fec_gen;
          $resumen->id_empresa_negocio = $cabecera->id_empresa_negocio;
          $resumen->res_tip ='3';
          $resumen->tip_res_com = 'CBA';
          $resumen->save();


          $cabecera = cpe_cabecera::findOrFail($comp);
          $cabecera->motivo_baja = $motivo;
          $cabecera->ccabaj = $fec_gen;
          $cabecera->ccasunrescod='7';
          $cabecera->ccatvg ='0.00';
          $cabecera->ccaigv ='0.00';
          $cabecera->ccaitv ='0.00';
          if(!empty($resumen->res_id)){
          $cabecera->res_id = $resumen->res_id;
          }
          $cabecera->est_sunat = 'ANULADO';
          $cabecera->update();
      

         self::generar_xml_comunicacion($comp);
      
      }elseif($cabecera->tdocod=='03'){
          
          $resumen = new resumenes;
          $resumen->res_fec_com = $cabecera->ccafem;
          $resumen->res_fec_gen = $fec_gen;
          $resumen->id_empresa_negocio = $cabecera->id_empresa_negocio;
          $resumen->res_tip ='3';
          $resumen->tip_res_com = 'GRD';
          $resumen->save();


          $cabecera = cpe_cabecera::findOrFail($comp);
          $cabecera->motivo_baja = $motivo;
          $cabecera->ccabaj = $fec_gen;
          $cabecera->ccasunrescod='7';
          $cabecera->ccatvg ='0.00';
          $cabecera->ccaigv ='0.00';
          $cabecera->ccaitv ='0.00';
          if(!empty($resumen->res_id)){
          $cabecera->res_id = $resumen->res_id;
          }
          $cabecera->est_sunat = 'ANULADO';
          $cabecera->update();
          

         self::generar_xml_resumen($comp);

      }else{
          $cabecera = cpe_cabecera::findOrFail($comp);
          $cabecera->motivo_baja = $motivo;
          $cabecera->ccabaj = $fec_gen;
          $cabecera->ccasunrescod='7';
          $cabecera->ccatvg ='0.00';
          $cabecera->ccaigv ='0.00';
          $cabecera->ccaitv ='0.00';
          if(!empty($resumen->res_id)){
          $cabecera->res_id = $resumen->res_id;
          }
          $cabecera->est_sunat = 'ANULADO';
          $cabecera->update();
      }

      DB::tABLE('movimientos_productos')->where('IdCpe_cabecera',$comp)->delete();

      DB::tABLE('cuentas_cobrar')->where('IdCpe_cabecera',$comp)->delete();

      if($cabecera->tdocod =='80'){

          if($request->ajax()) {
          return response()->json(['mensaje' => 'orden']);
        }
   
      }elseif($cabecera->tdocod =='15'){
          if($request->ajax()) {
          return response()->json(['mensaje' => 'cotizacion']);
        }
  
      }else{

          if($request->ajax()) {
          return response()->json(['mensaje' => 'cpe']);
        }

      }
     
    }


/*-------------------------------------------------------------INICIO REGISTRAR NOTA DE CREDITO----------------------------------------*/

public function registrarnota(Request $request){

        $rucemp = trim(Auth::user()->IdEmpresa);
        $empresa = Empresa::findOrFail($rucemp);

        $serdoc = $request->get('serdoc');
        $numdoc = str_pad($request->get('numdoc'),8,"0", STR_PAD_LEFT);
        $sercomp = $request->get('serdoc');
        $numcomp = $request->get('numdoc');
        $tdicod= $request->get('tdicod');
        $tipdoc = $request->get('tdo_cod');
        $tipnot = $request->get('tipnot');
        $desnota = $request->get('desnota');
        $motivo = $request->get('obser');
        $clinom = $request->get('clinom');
        $clinum = $request->get('clinum');
        $clidir = $request->get('clidir');
        $clicor = $request->get('clicor');
        $tipcambio = $request->get('camdoc');
        $fecemi = $request->get('fecEmi');
        $mondoc = $request->get('tipmon');
        $monnom = $request->get('mondoc');
        $otrosc = $request->get('otrosc');
        $grav = $request->get('grav');
        $grat = $request->get('grat');
        $inaf = $request->get('inaf');
        $exon = $request->get('exon');
        $igv = $request->get('igv');
        $isc = $request->get('isc');
        $otros = $request->get('otros');
        $total = $request->get('total');
        $tdocod = $request->get('txt_tdocod');
        $serdocmod = $request->get('serdocmod');
        $descglb = $request->get('totdesc');
        $numdocmod = str_pad($request->get('numdocmod'),8,"0", STR_PAD_LEFT);
        $moneda = DB::tABLE('moneda')->where('moncod','=',$mondoc)->first();


        //DATOS DOCUMENTO RELACIONADO

        $tdomod = $request->get('tdomod');
       // $tipnc = $request->get('tipnc');
       
  
        $docmod = DB::tABLE('cpe_cabecera')
        ->select('IdCpe_cabecera','id_empresa_negocio','topcod','id_almacen')
        ->where('serdoc','=',$serdocmod)
        ->where('numdoc','=',$numdocmod)
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();
 
        $dat_cli = DB::tABLE('cliente')->where('clinum',$clinum)->first();


        $IdCpe_cabecera=$docmod->IdCpe_cabecera;
        //-----FIN DATOS DOCUMENTOS RELACIONADOS       
        
        //Registrar la cabecera de la factura

        $cabecera = new cpe_cabecera;
        $cabecera->tdocod = $tdocod;
        //$cabecera->tdocod = $tdocod;
        $cabecera->topcod = $docmod->topcod;
        $cabecera->ccafem = $fecemi;
        $cabecera->ccaobs = $motivo;
        $cabecera->tdicod = $tdicod;
        $cabecera->serdoc = $serdoc;
        $cabecera->numdoc = $numdoc;
        $cabecera->ccandi = $clinum;
        $cabecera->ccanom = $clinom;
        $cabecera->clicod = $dat_cli->clicod;
        $cabecera->tipcambio = $tipcambio;
        $cabecera->id_empresa_negocio = $docmod->id_empresa_negocio;
        $cabecera->tipnot = $tipnot;
        $cabecera->moncod = $request->get('tipmon');
       // $cabecera->IdEmpresa = $rucemp;
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->ccacar = $request->get('otrosc');
        $cabecera->ccatvg = $request->get('grav');
        $cabecera->ccatvgr = $request->get('grat');
        $cabecera->ccatvi = $request->get('inaf');
        $cabecera->ccatexo = $request->get('exon');
        $cabecera->ccaigv = $request->get('igv');
        $cabecera->ccaisc = $request->get('isc');
        $cabecera->ccaotr = $request->get('otros');
        $cabecera->ccaitv = $request->get('total');
        $cabecera->serdoc= $request->get('serdoc');
        $cabecera->ccades= $descglb;
        $cabecera->IdEmpresa = $rucemp;
        $cabecera->IdCpe_cabecera_ref = $IdCpe_cabecera;
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->id_almacen = $docmod->id_almacen;
        
        $nota = $serdoc.'-'.$numdoc;
        $cabfactura = cpe_cabecera::findOrFail($IdCpe_cabecera);
        $cabfactura->ccanot = $nota;
       
        $cabfactura->update();
        


        if ($tdocod =='07') {
            if($tipdoc =='01'){
                $empresanegocio = EmpresaNegocios::findOrFail($docmod->id_empresa_negocio);

                if( $empresanegocio->FcnuEmpresa == $numcomp){
                    $modnumdoc= $numcomp+1;
                }else{
                    $modnumdoc = $numcomp;
                }

                $empresanegocio->FcseEmpresa = $serdoc;
                $empresanegocio->FcnuEmpresa = $modnumdoc;
                //$empresanegocio->update();

                $numdoc = str_pad($modnumdoc,8,"0", STR_PAD_LEFT);
                $cabecera->serdoc= $serdoc;
                $cabecera->numdoc = $numdoc;
                

            }elseif($tipdoc=='03'){
                $empresanegocio = EmpresaNegocios::findOrFail($docmod->id_empresa_negocio);
                
                if( $empresanegocio->BcnuEmpresa == $numcomp){
                    $modnumdoc= $numcomp+1;
                }else{
                    $modnumdoc = $numcomp;
                }

                $empresanegocio->BcseEmpresa = $serdoc;
                $empresanegocio->BcnuEmpresa = $modnumdoc;
                //$empresanegocio->update();

                $numdoc = str_pad($modnumdoc,8,"0", STR_PAD_LEFT);
                $cabecera->serdoc= $serdoc;
                $cabecera->numdoc = $numdoc;
                

            }
        }elseif ($tdocod =='08') {
            if($tipdoc =='01'){
                $empresanegocio = EmpresaNegocios::findOrFail($docmod->id_empresa_negocio);
                if( $empresanegocio->FdnuEmpresa == $numcomp){
                    $modnumdoc= $numcomp+1;
                }else{
                    $modnumdoc = $numcomp;
                }

                $empresanegocio->FdseEmpresa = $serdoc;
                $empresanegocio->FdnuEmpresa = $modnumdoc;
                //$empresanegocio->update();

                $numdoc = str_pad($modnumdoc,8,"0", STR_PAD_LEFT);
                $cabecera->serdoc= $serdoc;
                $cabecera->numdoc = $numdoc;
              
            }elseif($tipdoc=='03'){
                $empresanegocio = EmpresaNegocios::findOrFail($docmod->id_empresa_negocio);
              
                if( $empresanegocio->BdnuEmpresa == $numcomp){
                    $modnumdoc= $numcomp+1;
                }else{
                    $modnumdoc = $numcomp;
                }

                $empresanegocio->BdseEmpresa = $serdoc;
                $empresanegocio->BdnuEmpresa = $modnumdoc;
                //$empresanegocio->update();

                $numdoc = str_pad($modnumdoc,8,"0", STR_PAD_LEFT);
                $cabecera->serdoc= $serdoc;
                $cabecera->numdoc = $numdoc;
               
            }
        }
        
          $empresanegocio->update();
          $cabecera->save();
          $codfact = $cabecera->IdCpe_cabecera; 
         
        
                 
       //Registrar el detalle de la factura
        $cantidades = $request->get('cant');
        $unidades = $request->get('unid');
        $codpro = $request->get('codpro');
        $detpro = $request->get('detpro');
        $vunit = $request->get('vunit');
        $puni = $request->get('preuni');
        $vigv = $request->get('vigv');
        $tigv = $request->get('tigv');
        $vsub = $request->get('vsub');
        $vtot = $request->get('vtot');


           foreach( $unidades as $index => $ume ) {
            $detalle = new cpe_detalle;
            $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera; 
            $dpro = $detpro[$index];
            $detalle->cdedes = $dpro;
            
              $codproducto = $codpro[$index];
              $detalle->umecod = $ume;
              $detalle->cdecan = $cantidades[$index];
              $detalle->procod = $codproducto;
              $detalle->cdepsu = $codproducto;
              $detalle->cdevun = $puni[$index];
              $detalle->cdepuni = $puni[$index];
              $detalle->cdeigv = $vigv[$index];
              $detalle->tigcod = $tigv[$index];
              $detalle->cdepve = $vtot[$index];
              $detalle->cdevve = $vtot[$index];
              $detalle->save();

                if($tipnot=='01'){

                    $producto = Productos::findOrFail($codpro[$index]);
            
                    if(empty($producto->pro_rel)){

                      $id_prod = $producto->IdProducto;

                    }else{
                  
                      $id_prod = $producto->pro_rel;

                    }

                    $almacen = DB::tABLE('almacenes')->where('id_empresa_negocio',$cabecera->id_empresa_negocio)->first();

                    $stockprod = DB::tABLE('producto_stock')
                    ->where('IdProducto',$id_prod)
                    ->where('id_empresa_negocio',$cabecera->id_empresa_negocio)
                    ->where('id_almacen',$almacen->id_almacen)
                    ->first();

                    if(empty($stockprod)){
                      
                      $stock = 0+($detalle->cdecan*$producto->factor);
                      $stockprod_act = DB::tABLE('producto_stock')
                          ->insert([
                            'stock'=>$stock,'IdProducto'=>$id_prod,
                            'id_empresa_negocio'=>$cabecera->id_empresa_negocio,
                            'id_almacen'=>$almacen->id_almacen]
                          );

                    }else{

                        $stockprod_act = DB::tABLE('producto_stock')
                        ->where('pro_sto_id',$stockprod->pro_sto_id)
                        ->update(['stock'=>$stockprod->stock+($detalle->cdecan*$producto->factor)]);

                        $stock = $stockprod->stock+($detalle->cdecan*$producto->factor);
                    }

                    $movimiento = new movimientos;
                    $movimiento->mov_fec = $fecemi; 
                    $movimiento->mov_tip = 'E';
                    $movimiento->mov_mot = 'ANULACION';
                    $movimiento->cantidad = $detalle->cdecan;
                    $movimiento->unidad = $detalle->umecod;
                    $movimiento->comprobante = $cabecera->serdoc.'-'.$cabecera->numdoc;
                    $movimiento->IdEmpresa = Auth::user()->IdEmpresa;
                    $movimiento->id_empresa_negocio = $cabecera->id_empresa_negocio;
                    $movimiento->IdProducto = $producto->IdProducto;
                    $movimiento->observacion = "Venta desde Punto de Venta";
                    $movimiento->IdUsuario = Auth::user()->IdUsuario;
                    $movimiento->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
                    $movimiento->stockmov = $stock;
                    $movimiento->save();

                }



       
          }
    

      


        $nom_arch = $cabecera->generar_xml_nota($codfact);
        $cabecera->generar_nuevo_qr($codfact);
        $cabecera->generarpdfgeneral($codfact);
       
        if($empresa->tipo_envio =='1'){
            $cabecera->enviar_sunat($codfact);
        }

        return Redirect::to('/SisFact')->with('success');

   
    }


/*---------------------------------------------------------------FIN REGISTRO NOTA DE CREDITO------------------------------------------*/


 public function generar_xml_resumen_diario(Request $request){

    $fechacomprobantes = $request->get('fecresumen');
    $tipo = $request->get('tipo');
    $fechageneracion = now()->format('Y-m-d');


    $contar = DB::tABLE('cpe_cabecera')
    ->where('tdocod','03')
    ->where('ccafem',$fechacomprobantes)
    ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
    ->count();

 
    if($contar >0){

    if($tipo=='1'){

      $cabeceras = DB::tABLE('cpe_cabecera')
      ->where('tdocod','03')
      ->where('ccafem','=',$fechacomprobantes)
      ->where('enviado','0')
      ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->get();

      if(empty($cabeceras)){
             return response()->json(['mensaje' => 'No hay comprobantes']);
      }

    }elseif($tipo=='3'){


      $cabeceras = DB::tABLE('cpe_cabecera')->where('tdocod','03')
      ->where('ccafem','=',$fechacomprobantes)
      ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->where('ccabaj','!=','')
      ->where('tdocod','03')
      ->get();

         if(empty($cabeceras)){

           return response()->json(['mensaje' => 'No hay comprobantes']);
         }

    }

    $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

    $empresa = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

    $ResnuEmpresa = $empresa->ResnuEmpresa +1;

    $actualizarnum = Empresa::findOrFail(Auth::user()->IdEmpresa);
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
            ->setMtoOperExoneradas($cabecera->ccatexo)
            ->setMtoOperExportacion(0.00)
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


    $builder = new SummaryBuilder();
    $xml = $builder->build($sum);

    $nom_xml = $sum->getName();

    file_put_contents(public_path().'/xml/'.$nom_xml.'.xml',$xml);

    $firmar_xml = new cpe_cabecera;
    $firmar_xml->firmar_xml($nom_xml);

    $resumen = new resumenes;
    $resumen->res_fec_com = $fechacomprobantes;
    $resumen->res_fec_gen = $fechageneracion;
    $resumen->id_empresa_negocio = Auth::user()->id_empresa_negocio;
    $resumen->res_tip =$tipo;
    $resumen->nom_arch = $nom_xml;
    $resumen->tip_res_com ='GRD';
    $resumen->save();

    foreach ($cabeceras as $comp) {
      
      $cpe = cpe_cabecera::findOrFail($comp->IdCpe_cabecera);
      $cpe->res_id = $resumen->res_id;
      $cpe->enviado ='1';
      $cpe->update();
    }

        

   // if($empresa->tipo_envio =='1'){
        
     

          self::enviar_sunat_resumen_comunicacion($resumen->res_id);

     
   // }
  


   
      
      return response()->json(['mensaje' => 'Enviado']);

    }else{

         return response()->json(['mensaje' => 'No hay comprobantes']);
    }

  }

  
  public function generar_xml_resumen($codfact){

    $cabecera = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$codfact)->first();

    $baja = DB::tABLE('resumenes')->where('res_id',$cabecera->res_id)->first();

    $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$cabecera->id_empresa_negocio)->first();

    $moneda = DB::tABLE('moneda')->where('moncod',$cabecera->moncod)->first();

    $empresa = DB::tABLE('empresa')->where('IdEmpresa',$sucursal->IdEmpresa)->first();

    $ResnuEmpresa = $empresa->ResnuEmpresa+1;

    $actualizarnum = Empresa::findOrFail(Auth::user()->IdEmpresa);
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
            ->setMtoOperExportacion(0.00)
            ->setMtoOtrosCargos(0.00)
            ->setMtoIGV($cabecera->ccaigv);

    $sum = new Summary();
    $sum->setFecGeneracion(new \DateTime($baja->res_fec_gen))
        ->setFecResumen(new \DateTime($baja->res_fec_com))
        ->setCorrelativo($ResnuEmpresa)
        ->setCompany($company)
        ->setDetails([$detail]);



    $builder = new SummaryBuilder();
    $xml = $builder->build($sum);

    $nom_xml = $sum->getName();

    file_put_contents(public_path().'/xml/'.$nom_xml.'.xml',$xml);


    $firmar_xml = new cpe_cabecera;
    $firmar_xml->firmar_xml($nom_xml);

    $act_cab = resumenes::findOrFail($cabecera->res_id);
    $act_cab->nom_arch = $nom_xml;
    $act_cab->update();


    if($empresa->tipo_envio =='1'){
      self::enviar_sunat_resumen_comunicacion($cabecera->res_id);
    }
  

    return $nom_xml;


  }

 


  public function generar_xml_comunicacion($codfact){

    $cabecera = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$codfact)->first();

    $baja = DB::tABLE('resumenes')
    ->where('res_id',$cabecera->res_id)
    ->first();


    $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$cabecera->id_empresa_negocio)->first();
    $moneda = DB::tABLE('moneda')->where('moncod',$cabecera->moncod)->first();
    $empresa = DB::tABLE('empresa')->where('IdEmpresa',$sucursal->IdEmpresa)->first();


    $see = self::configuracion();
    
    $BanuEmpresa = $empresa->BanuEmpresa+1;

    $actualizarnum = Empresa::findOrFail(Auth::user()->IdEmpresa);
    $actualizarnum->BanuEmpresa = $BanuEmpresa;
    $actualizarnum->update();

  

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
    ->setFecGeneracion(new \DateTime($baja->res_fec_gen))
    ->setFecComunicacion(new \DateTime($baja->res_fec_com))
    ->setCompany($company)
    ->setDetails([$detail]);

    $builder = new VoidedBuilder();
    $xml = $builder->build($voided);

    $nom_xml = $voided->getName();

    file_put_contents(public_path().'/xml/'.$nom_xml.'.xml',$xml);

    $firmar_xml = new cpe_cabecera;
    $firmar_xml->firmar_xml($nom_xml);

    $act_cab = resumenes::findOrFail($cabecera->res_id);
    $act_cab->nom_arch = $nom_xml;
    $act_cab->update();

    if($empresa->tipo_envio =='1'){


          self::enviar_sunat_resumen_comunicacion($cabecera->res_id);
     
    }
  

    return $nom_xml;

  }


/*---------------------------------------------------------------FIN GENERAR XML ----------------------------------------------*/

 

   public function enviar_sunat_resumen_comunicacion($res_id){

      $resumen = resumenes::findOrFail($res_id);

      $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$resumen->id_empresa_negocio)->first();

      $empresa = DB::tABLE('empresa')->where('IdEmpresa',$sucursal->IdEmpresa)->first();

      $usuario = $empresa->IdEmpresa.$empresa->wsusuario;
      $contrasena = $empresa->claveSunat;


      if($empresa->tip_env_fac_id=='01'){

        if($empresa->produccion =='1'){

          $urlService = 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService';

        }elseif($empresa->produccion =='0'){

          $urlService = 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService';

        }


      }elseif($empresa->tip_env_fac_id =='02'){

        if($empresa->produccion =='1'){

          $urlService = 'https://ose.nubefact.com/ol-ti-itcpe/billService?wsdl';

        }elseif($empresa->produccion =='0'){

          $urlService = 'https://ose-demo.nubefact.com/ol-ti-itcpe/billService';

        }

    }

      $soap = new SoapClient();
      $soap->setService($urlService);
      $soap->setCredentials($usuario, $contrasena);
      $sender = new SummarySender();
      $sender->setClient($soap);

      $xml = file_get_contents(public_path().'/xml/'.$resumen->nom_arch.'.xml');
      $result = $sender->send($resumen->nom_arch, $xml);

      if (!$result->isSuccess()) {
           $resumen_act = resumenes::findOrFail($res_id);
           $resumen_act->res_est = $result->getError()->getMessage();
           $resumen_act->res_cod_est = $result->getError()->getCode();
           $resumen_act->update();
          // Error en la conexion con el servicio de SUNAT
          //var_dump($result->getError());
          return;
      }

      // Guardar el ticket en el sistema, servira para consultar el estado del documento. 
      $ticket = $result->getTicket();


      $resumen->res_ticket = $ticket;
      $resumen->update();

      self::consultar_ticket($res_id);

      return 'listo';

  }



  public function consultar_ticket($res_id){

      $resumen = DB::tABLE('resumenes')->where('res_id',$res_id)->first();
      
      $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$resumen->id_empresa_negocio)->first();

      $empresa = DB::tABLE('empresa')->where('IdEmpresa',$sucursal->IdEmpresa)->first();

      $usuario = $empresa->IdEmpresa.$empresa->wsusuario;
      $contrasena = $empresa->claveSunat;

      // URL del servicio.
      
      if($empresa->tip_env_fac_id=='01'){

        if($empresa->produccion =='1'){

          $urlService = 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService';

        }elseif($empresa->produccion =='0'){

          $urlService = 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService';

        }


      }elseif($empresa->tip_env_fac_id =='02'){

        if($empresa->produccion =='1'){

          $urlService = 'https://ose.nubefact.com/ol-ti-itcpe/billService?wsdl';

        }elseif($empresa->produccion =='0'){

          $urlService = 'https://ose-demo.nubefact.com/ol-ti-itcpe/billService';

        }

    }

      $soap = new SoapClient();
      $soap->setService($urlService);
      $soap->setCredentials($usuario, $contrasena);
      $statusService = new ExtService();
      $statusService->setClient($soap);

      $status = $statusService->getStatus($resumen->res_ticket);

      if (!$status->isSuccess()) {

           $resumen_act = resumenes::findOrFail($res_id);
           $resumen_act->res_est = $result->getError()->getMessage();
           $resumen_act->res_cod_est = $result->getError()->getCode();
           $resumen_act->update();
          // Error en la conexion con el servicio de SUNAT
         //var_dump($status);
          return;
      }

      $cdr = $status->getCdrResponse();
      file_put_contents(public_path().'/cdr/'.$resumen->nom_arch.'.zip', $status->getCdrZip());
      //var_dump($cdr);


      // Verificar CDR (Resumen aceptado o rechazado)
      $code = (int)$cdr->getCode();

      $actualizar = resumenes::findOrFail($res_id);
      if($code === 0){
        $dato = 'ACEPTADO';
        if(count($cdr->getNotes()) > 0) {
          
          $dato = 'ACEPTADO CON OBSERVACIONES'; 

          foreach ($cdr->getNotes() as $obs){
              
          }
        }
      }elseif($code >= 2000 && $code <= 3999){
        $dato = "RECHAZADO";
      }else{
      
        $dato = 'Excepción';
      }
      $actualizar->est_sunat = $dato;
      $actualizar->res_cod_est = $code;
      $actualizar->res_est = $cdr->getDescription();
      $actualizar->update();



      return 'listo';

  }

 



	
	public function voucher($pedido){


        $rucemp = Auth::user()->IdEmpresa;

        $empresa = Empresa::findOrFail($rucemp);

        $empresanegocios = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);

        $cabecera = DB::tABLE('cpe_cabecera as cab')
        ->join('cliente as cli','cab.ccandi','=','cli.clinum')
        ->join('tipo_documento as tdo','cab.tdocod','=','tdo.tdocod')
        ->join('tipo_documento_identidad as tdi','cab.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cab.moncod','=','mon.moncod')
        ->join('tipo_operacion as top','cab.topcod','=','top.topcod')
        ->where('IdCpe_cabecera','=',$pedido)
        ->first();
      

        $totalletras= MontoLetras::convertir(number_format($cabecera->ccaitv,'2','.',''),$cabecera->monnom,'Centimos');
        
        $detalle = cpe_detalle::where('IdCpe_cabecera',$pedido)
        ->leftjoin('unidad_medida as umed','cpe_detalle.umecod','=','umed.umecod')
        ->get();

        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();


        $numdoc = $cabecera->numdoc;
        $qrfile =  'QR-'.$cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.png'; 
        $imgqr = "/qr/".$qrfile;


        return view('formatos_comprobantes.ticket_factura',compact('cabecera','totalletras','detalle','mesa','empresa','sucursal','imgqr'));


  


    }
	

    public function registrar_movimiento($movimiento){

        dd($movimiento);
    }

    public function generar_codigo_movimiento($IdCpe_cabecera){

        $bus_cpe = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$IdCpe_cabecera)->First();

        $bus_suc = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$bus_cpe->id_empresa_negocio)->first();

        $gen_cod = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$IdCpe_cabecera)->update(['cod_mov'=>'MOV'.$bus_suc->cod_suc.$IdCpe_cabecera]);

        return $gen_cod;
    }
}


