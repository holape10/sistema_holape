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
use MasterSoft\Modelos\Almacen;
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
use Mike42\Escpos\PrintConnectors\DummyPrintConnector;
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
use \Milon\Barcode\DNS1D;
use \Milon\Barcode\DNS2D;  
use DB;
use Hash;
use PDF;
use Config;
use Carbon;


class PuntoVentaController extends Controller
{


    public function __construct()
    {
        $this->middleware('auth')->except(['ingresomozo', 'mesas','descargar_comprobante']);
    }


    public function punto_contrato(Request $request,$codfact=0)
    {
        

            $categorias = DB::tABLE('categorias')->get();

            $comprobantes = DB::tABLE('tipo_documento')->where('contrato','1')->get();

            $documentos = DB::tABLE('tipo_documento_identidad')->orderby('orden','asc')->get();

            $estadopagos = DB::tABLE('credito_dias')->get();

            $mediospagos = DB::tABLE('medios_pagos')->get();

            $monedas = DB::tABLE('moneda')->get();

            $motivos = DB::tABLE('motivo_traslado')
            ->orderBy('motivo','asc')->get();

            $modalidades = DB::tABLE('modalidad_traslado')
            ->orderBy('modalidad','asc')->get();


            $mozos = DB::tABLE('users')
            ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
            ->where('role_id','8')
            ->get();

            $senudoc = DB::tABLE('empresa_negocios')
            ->select('serieguia','numeroguia')
            ->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)
            ->first(); 
            
            $ubigeos = DB::tABLE('cat_ubigeo')->get();

            $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)
            ->first(); 

              $gastos = DB::tABLE('tipo_gastos')->get();

            return view('empresas.puntosventas.punto_contrato',compact('ubigeos','senudoc','motivos','modalidades','monedas','mozos','categorias','comprobantes','documentos','estadopagos','mediospagos','sucursal','codfact','datos','gastos'));

   


    }


       public function caja_venta(Request $request,$codfact=0)
    {
        

            $categorias = DB::tABLE('categorias')->get();

            $comprobantes = DB::tABLE('tipo_documento')->where('caja','1')->get();

            $documentos = DB::tABLE('tipo_documento_identidad')->orderby('orden','asc')->get();

            $estadopagos = DB::tABLE('credito_dias')->get();

            $mediospagos = DB::tABLE('medios_pagos')->get();

            $monedas = DB::tABLE('moneda')->get();

            $motivos = DB::tABLE('motivo_traslado')
            ->orderBy('motivo','asc')->get();

            $modalidades = DB::tABLE('modalidad_traslado')
            ->orderBy('modalidad','asc')->get();


            $mozos = DB::tABLE('users')
            ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
            //->where('role_id','8')
            ->get();

            $senudoc = DB::tABLE('empresa_negocios')
            ->select('serieguia','numeroguia')
            ->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)
            ->first(); 
            
            $ubigeos = DB::tABLE('cat_ubigeo')->get();

            $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)
            ->first(); 

              $gastos = DB::tABLE('tipo_gastos')->get();

            return view('empresas.puntosventas.caja_venta',compact('ubigeos','senudoc','motivos','modalidades','monedas','mozos','categorias','comprobantes','documentos','estadopagos','mediospagos','sucursal','codfact','datos','gastos'));

   


    }
 
    public function caja2(Request $request){

        $categorias = DB::tABLE('categorias')->get();

        $comprobantes = DB::tABLE('tipo_documento')->where('caja','1')->get();



        return view('empresas.puntosventas.caja2',compact('categorias','comprobantes'));

    }



    public function caja_tactil_1(Request $request,$codfact=0)
    {
        

            $categorias = DB::tABLE('categorias')->get();

            $comprobantes = DB::tABLE('tipo_documento')->where('caja','1')->get();

            $documentos = DB::tABLE('tipo_documento_identidad')->orderby('orden','asc')->get();

            $estadopagos = DB::tABLE('credito_dias')->get();

            $mediospagos = DB::tABLE('medios_pagos')->get();

            $monedas = DB::tABLE('moneda')->get();

            $motivos = DB::tABLE('motivo_traslado')
            ->orderBy('motivo','asc')->get();

            $modalidades = DB::tABLE('modalidad_traslado')
            ->orderBy('modalidad','asc')->get();


            $mozos = DB::tABLE('users')
            ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
            ->where('role_id','8')
            ->get();

            $senudoc = DB::tABLE('empresa_negocios')
            ->select('serieguia','numeroguia')
            ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
            ->first(); 
            
            $ubigeos = DB::tABLE('cat_ubigeo')->get();

            $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)
            ->first(); 

              $gastos = DB::tABLE('tipo_gastos')->get();


            $cat_pred =  DB::tABLE('categorias')->where('predeterminado','1')->first();

             $empresa = Empresa::findorFail(Auth::user()->IdEmpresa);

             $productos = DB::tABLE('productos')
             ->join('categorias','categorias.cat_id','productos.cat_id')
             ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
            ->where('promocion','!=','4')
            ->orderby('pronom','asc')
            ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
            ->get();

            $negocio = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

            return view('empresas.puntosventas.caja_tactil_1',compact('productos','ubigeos','senudoc','motivos','modalidades','monedas','mozos','categorias','comprobantes','documentos','estadopagos','mediospagos','sucursal','codfact','datos','gastos','cat_pred','empresa','negocio'));

   


    }

     public function caja_tactil_2(Request $request,$codfact=0)
    {
        

            $categorias = DB::tABLE('categorias')->get();

            $comprobantes = DB::tABLE('tipo_documento')->where('caja','1')->get();

            $documentos = DB::tABLE('tipo_documento_identidad')->orderby('orden','asc')->get();

            $estadopagos = DB::tABLE('credito_dias')->get();

            $mediospagos = DB::tABLE('medios_pagos')->get();

            $monedas = DB::tABLE('moneda')->get();

            $motivos = DB::tABLE('motivo_traslado')
            ->orderBy('motivo','asc')->get();

            $modalidades = DB::tABLE('modalidad_traslado')
            ->orderBy('modalidad','asc')->get();


            $mozos = DB::tABLE('users')
            ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
            ->where('role_id','8')
            ->get();

            $senudoc = DB::tABLE('empresa_negocios')
            ->select('serieguia','numeroguia')
            ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
            ->first(); 
            
            $ubigeos = DB::tABLE('cat_ubigeo')->get();

            $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)
            ->first(); 

              $gastos = DB::tABLE('tipo_gastos')->get();


            $cat_pred =  DB::tABLE('categorias')->where('predeterminado','1')->first();

             $empresa = Empresa::findorFail(Auth::user()->IdEmpresa);

             $productos = DB::tABLE('productos')
             ->join('categorias','categorias.cat_id','productos.cat_id')
             ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
            ->where('promocion','!=','4')
            ->orderby('pronom','asc')
            ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
            ->get();

            $negocio = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

            return view('empresas.puntosventas.caja_tactil_2',compact('productos','ubigeos','senudoc','motivos','modalidades','monedas','mozos','categorias','comprobantes','documentos','estadopagos','mediospagos','sucursal','codfact','datos','gastos','cat_pred','empresa','negocio'));

   


    }


         public function caja_tactil_3(Request $request,$codfact=0)
    {
        

            $categorias = DB::tABLE('categorias')->get();

            $comprobantes = DB::tABLE('tipo_documento')->where('caja','1')->get();

            $documentos = DB::tABLE('tipo_documento_identidad')->orderby('orden','asc')->get();

            $estadopagos = DB::tABLE('credito_dias')->get();

            $mediospagos = DB::tABLE('medios_pagos')->get();

            $monedas = DB::tABLE('moneda')->get();

            $motivos = DB::tABLE('motivo_traslado')
            ->orderBy('motivo','asc')->get();

            $modalidades = DB::tABLE('modalidad_traslado')
            ->orderBy('modalidad','asc')->get();


            $mozos = DB::tABLE('users')
            ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
            ->where('role_id','8')
            ->get();

            $senudoc = DB::tABLE('empresa_negocios')
            ->select('serieguia','numeroguia')
            ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
            ->first(); 
            
            $ubigeos = DB::tABLE('cat_ubigeo')->get();

            $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)
            ->first(); 

              $gastos = DB::tABLE('tipo_gastos')->get();


            $cat_pred =  DB::tABLE('categorias')->where('predeterminado','1')->first();

             $empresa = Empresa::findorFail(Auth::user()->IdEmpresa);

             $productos = DB::tABLE('productos')
             ->join('categorias','categorias.cat_id','productos.cat_id')
             ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
            ->where('promocion','!=','4')
            ->orderby('pronom','asc')
            ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
            ->get();

            $negocio = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

            return view('empresas.puntosventas.caja_tactil_3',compact('productos','ubigeos','senudoc','motivos','modalidades','monedas','mozos','categorias','comprobantes','documentos','estadopagos','mediospagos','sucursal','codfact','datos','gastos','cat_pred','empresa','negocio'));

   


    }


      public function caja_tactil_4(Request $request,$codfact=0)
    {
        

            $categorias = DB::tABLE('categorias')->get();

            $comprobantes = DB::tABLE('tipo_documento')->where('caja','1')->get();

            $documentos = DB::tABLE('tipo_documento_identidad')->orderby('orden','asc')->get();

            $estadopagos = DB::tABLE('credito_dias')->get();

            $mediospagos = DB::tABLE('medios_pagos')->get();

            $monedas = DB::tABLE('moneda')->get();

            $motivos = DB::tABLE('motivo_traslado')
            ->orderBy('motivo','asc')->get();

            $modalidades = DB::tABLE('modalidad_traslado')
            ->orderBy('modalidad','asc')->get();


            $mozos = DB::tABLE('users')
            ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
            ->where('role_id','8')
            ->get();

            $senudoc = DB::tABLE('empresa_negocios')
            ->select('serieguia','numeroguia')
            ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
            ->first(); 
            
            $ubigeos = DB::tABLE('cat_ubigeo')->get();

            $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)
            ->first(); 

              $gastos = DB::tABLE('tipo_gastos')->get();


            $cat_pred =  DB::tABLE('categorias')->where('predeterminado','1')->first();

             $empresa = Empresa::findorFail(Auth::user()->IdEmpresa);

             $productos = DB::tABLE('productos')
             ->join('categorias','categorias.cat_id','productos.cat_id')
             ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
            ->where('promocion','!=','4')
            ->orderby('pronom','asc')
            ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
            ->get();

            $negocio = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

            return view('empresas.puntosventas.caja_tactil_4',compact('productos','ubigeos','senudoc','motivos','modalidades','monedas','mozos','categorias','comprobantes','documentos','estadopagos','mediospagos','sucursal','codfact','datos','gastos','cat_pred','empresa','negocio'));

   


    }


       public function caja_tactil_5(Request $request,$codfact=0)
    {
        

            $categorias = DB::tABLE('categorias')->get();

            $comprobantes = DB::tABLE('tipo_documento')->where('caja','1')->get();

            $documentos = DB::tABLE('tipo_documento_identidad')->orderby('orden','asc')->get();

            $estadopagos = DB::tABLE('credito_dias')->get();

            $mediospagos = DB::tABLE('medios_pagos')->get();

            $monedas = DB::tABLE('moneda')->get();

            $motivos = DB::tABLE('motivo_traslado')
            ->orderBy('motivo','asc')->get();

            $modalidades = DB::tABLE('modalidad_traslado')
            ->orderBy('modalidad','asc')->get();


            $mozos = DB::tABLE('users')
            ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
            ->where('role_id','8')
            ->get();

            $senudoc = DB::tABLE('empresa_negocios')
            ->select('serieguia','numeroguia')
            ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
            ->first(); 
            
            $ubigeos = DB::tABLE('cat_ubigeo')->get();

            $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)
            ->first(); 

              $gastos = DB::tABLE('tipo_gastos')->get();


            $cat_pred =  DB::tABLE('categorias')->where('predeterminado','1')->first();

             $empresa = Empresa::findorFail(Auth::user()->IdEmpresa);

             $productos = DB::tABLE('productos')
             ->join('categorias','categorias.cat_id','productos.cat_id')
             ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
            ->where('promocion','!=','4')
            ->orderby('pronom','asc')
            ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
            ->get();

            $negocio = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

            return view('empresas.puntosventas.caja_tactil_5',compact('productos','ubigeos','senudoc','motivos','modalidades','monedas','mozos','categorias','comprobantes','documentos','estadopagos','mediospagos','sucursal','codfact','datos','gastos','cat_pred','empresa','negocio'));

   


    }

       public function caja_tactil_6(Request $request,$codfact=0)
    {
        // Obtener categorías, comprobantes, etc.
        $categorias = DB::tABLE('categorias')->get();
        $comprobantes = DB::tABLE('tipo_documento')->where('caja','1')->get();
        $documentos = DB::tABLE('tipo_documento_identidad')->orderby('orden','asc')->get();
        $estadopagos = DB::tABLE('credito_dias')->get();
        $mediospagos = DB::tABLE('medios_pagos')->get();
        $monedas = DB::tABLE('moneda')->get();
        $motivos = DB::tABLE('motivo_traslado')->orderBy('motivo','asc')->get();
        $modalidades = DB::tABLE('modalidad_traslado')->orderBy('modalidad','asc')->get();
        $mozos = DB::tABLE('users')->join('role_user','role_user.user_IdUsuario','users.IdUsuario')->where('role_id','8')->get();
        $senudoc = DB::tABLE('empresa_negocios')->select('serieguia','numeroguia')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first(); 
        $ubigeos = DB::tABLE('cat_ubigeo')->get();
        $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)->first(); 
        $gastos = DB::tABLE('tipo_gastos')->get();
        $cat_pred =  DB::tABLE('categorias')->where('predeterminado','1')->first();
        $empresa = Empresa::findorFail(Auth::user()->IdEmpresa);
        $negocio = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

        // Obtener la fecha y hora actuales usando Carbon
        $ahora = Carbon::now();
        $dia_actual_carbon = $ahora->dayOfWeek; // 0 (Domingo) through 6 (Sábado)
        $hora_actual_string = $ahora->toTimeString(); // Formato 'HH:MM:SS'

        // Obtener el almacén predeterminado
        $almacen = DB::table('almacenes')
            ->where('predeterminado', '1')
            ->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)
            ->first();
        $almacen_id = $almacen ? $almacen->id_almacen : 0;

        // **Consulta para obtener los productos con precios dinámicos y stock**
        // Esta consulta es similar a la de buscarcartaimg en ProductosController,
        // pero se ejecuta al cargar la página para inicializar los productos que se muestran.
        $productos = DB::table('productos as p')
            ->select(
                'p.IdProducto',
                'p.procod',
                'p.pronom',
                'p.umecod',                
                'p.color',
                'p.imagenproducto',
                'p.factor',
                'p.icbper',
                'p.acom',
                'cat.cat_sig',
                // Lógica para obtener el precio dinámico por día y hora (incluyendo cruce de medianoche)
                DB::raw("
                    COALESCE(
                        (SELECT psd.precio_especial
                         FROM precios_dia_semana as psd
                         WHERE psd.IdProducto = p.IdProducto
                           AND psd.id_empresa_negocio = '".Auth::user()->id_empresa_negocio."'
                           AND psd.estado = 'Activo'
                           -- Validación de rango de fechas GENERAL (si la promoción es temporal)
                           AND (psd.fecha_inicio_vigencia IS NULL OR psd.fecha_inicio_vigencia <= CURDATE())
                           AND (psd.fecha_fin_vigencia IS NULL OR psd.fecha_fin_vigencia >= CURDATE())
                           AND
                           (
                               -- Caso 1: La promoción INICIA y TERMINA en el MISMO DÍA (sin cruzar medianoche en la definición)
                               (
                                   psd.dia_semana = {$dia_actual_carbon}
                                   AND psd.hora_inicio_vigencia <= psd.hora_fin_vigencia
                                   AND TIME('{$hora_actual_string}') >= psd.hora_inicio_vigencia
                                   AND TIME('{$hora_actual_string}') <= psd.hora_fin_vigencia
                               )
                               OR
                               -- Caso 2: La promoción INICIA en un día y TERMINA en el DÍA SIGUIENTE (cruzando medianoche)
                               (
                                   psd.dia_semana = {$dia_actual_carbon}
                                   AND psd.hora_inicio_vigencia > psd.hora_fin_vigencia
                                   AND TIME('{$hora_actual_string}') >= psd.hora_inicio_vigencia
                               )
                               OR
                               -- Caso 3: La promoción es del DÍA ANTERIOR y todavía está vigente en el día actual
                               (
                                   psd.dia_semana = MOD({$dia_actual_carbon} - 1 + 7, 7)
                                   AND psd.hora_inicio_vigencia > psd.hora_fin_vigencia
                                   AND TIME('{$hora_actual_string}') <= psd.hora_fin_vigencia
                               )
                           )
                           ORDER BY psd.id_precio_dia DESC
                           LIMIT 1
                        ),
                        p.propun
                    ) as precio
                "),
                'p.propun1 as precio2',
                'p.propun2 as precio3',
                
                DB::raw("
                    CASE
                        WHEN p.tipo = '2' AND p.pro_rel IS NOT NULL THEN (
                            SELECT stock / p.factor
                            FROM producto_stock
                            WHERE IdProducto = p.pro_rel
                              AND id_almacen = '{$almacen_id}'
                              AND id_empresa_negocio = '".Auth::user()->id_empresa_negocio."'
                        )
                        ELSE (
                            SELECT stock
                            FROM producto_stock
                            WHERE IdProducto = p.IdProducto
                              AND id_almacen = '{$almacen_id}'
                              AND id_empresa_negocio = '".Auth::user()->id_empresa_negocio."'
                        )
                    END as stock_disponible
                ")
            )
            ->join('categorias as cat','cat.cat_id','p.cat_id') // Unir con categorías para cat_sig
            ->join('producto_empresa','producto_empresa.IdProducto','p.IdProducto')
            ->where('p.promocion','!=','4') // Excluir insumos
            ->orderBy('p.pronom','asc')
            ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
            // Si quieres que solo se muestren los productos de la categoría predeterminada al cargar la página
            // y no todos los productos, puedes descomentar la siguiente línea:
            // ->where('productos.cat_id', $cat_pred->cat_id)
            ->get();

        return view('empresas.puntosventas.caja_tactil_6',compact('productos','ubigeos','senudoc','motivos','modalidades','monedas','mozos','categorias','comprobantes','documentos','estadopagos','mediospagos','sucursal','codfact','datos','gastos','cat_pred','empresa','negocio'));
        
    }


       public function caja(Request $request,$codfact=0)
    {
        

            $categorias = DB::tABLE('categorias')->get();

            $comprobantes = DB::tABLE('tipo_documento')->where('caja','1')->get();

            $documentos = DB::tABLE('tipo_documento_identidad')->orderby('orden','asc')->get();

            $estadopagos = DB::tABLE('credito_dias')->get();

            $mediospagos = DB::tABLE('medios_pagos')->get();

            $monedas = DB::tABLE('moneda')->get();

            $motivos = DB::tABLE('motivo_traslado')
            ->orderBy('motivo','asc')->get();

            $modalidades = DB::tABLE('modalidad_traslado')
            ->orderBy('modalidad','asc')->get();


            $mozos = DB::tABLE('users')
            ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
            ->where('role_id','8')
            ->get();

            $senudoc = DB::tABLE('empresa_negocios')
            ->select('serieguia','numeroguia')
            ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
            ->first(); 
            
            $ubigeos = DB::tABLE('cat_ubigeo')->get();

            $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)
            ->first(); 

              $gastos = DB::tABLE('tipo_gastos')->get();


            $cat_pred =  DB::tABLE('categorias')->where('predeterminado','1')->first();

            $empresa = Empresa::findorFail(Auth::user()->IdEmpresa);

            $negocio = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

            return view('empresas.puntosventas.caja',compact('ubigeos','senudoc','motivos','modalidades','monedas','mozos','categorias','comprobantes','documentos','estadopagos','mediospagos','sucursal','codfact','datos','gastos','cat_pred','empresa','negocio'));

    }

    public function venta_rapida($codfact=0){

        $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

        $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();

        $combustible = DB::tABLE('combustible')->get();

        $habitaciones = DB::tABLE('habitaciones')->get();

        $marcas = DB::tABLE('marcas')->get();

        $modelos = DB::tABLE('modelos')->get();

        $tecnicos = DB::tABLE('tecnicos')->get();

        $creditos = DB::tABLE('credito_dias')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();

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

        $mediospagos = DB::tABLE('medios_pagos')->where('medios_pagos.IdEmpresa',Auth::user()->IdEmpresa)->get();

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

        $clientes = DB::tABLE('cliente')->orderby('clinom','asc')->get();

        $comprobante = DB::tABLE('tipo_documento')->get();

        $comprobantes = DB::tABLE('tipo_documento')->get();

        $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();



        $unidades = DB::tABLE('unidad_medida')
        ->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vendedores = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        //->where('role_id','5')
        ->get();

        $mediospagos = DB::tABLE('medios_pagos')->where('medios_pagos.IdEmpresa',Auth::user()->IdEmpresa)->get();

        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

        $almacen = DB::tABLE('almacenes')
        ->where('predeterminado','1')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();



        $gastos = DB::tABLE('tipo_gastos')->get();

        $users = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        ->where('role_id','2')
        ->get();

        $ubigeos = DB::tABLE('cat_ubigeo')->get();

        $procesos = DB::tABLE('procesos')->get();

        return view('empresas.puntosventas.venta_rapida',compact('habitaciones','comprobantes','users','codfact','categorias','comprobante','tipodocumento','unidades','unidades','vendedores','creditos','mediospagos','clientes','documentos','datos','monedas','senudoc','motivos','modalidades','docidentidad','doccomprobante','monedas','almacenes','gastos','combustible','marcas','modelos','tecnicos','tipos_igv','empresa','procesos','ubigeos'));
    }


    public function salidasproductos($tdocod=0,$cpe=0)
    { 



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

        $colaboradores = DB::tABLE('users')->get();
        $areas = DB::tABLE('areas')->get();

        return view('empresas.puntosventas.salidasproductos',compact('categorias','comprobante','tipodocumento','igv','unidades','unidades','tdocod','cpe','mozos','creditos','mediospagos','clientes','documentos','datos','monedas','senudoc','motivos','modalidades','docidentidad','doccomprobante','monedas','productos','colaboradores','areas'));
    }


    public function punto_venta(Request $request){

        $rucemp = Auth::user()->IdEmpresa;

        $categorias = DB::tABLE('categorias')->get();

        $comprobantes = DB::tABLE('tipo_documento')->where('ventas','1')->get();

        $documentos = DB::tABLE('tipo_documento_identidad')->orderby('orden','asc')->get();

        $estadopagos = DB::tABLE('credito_dias')->get();

        $mediospagos = DB::tABLE('medios_pagos')->get();

        $senudoc = DB::tABLE('empresa_negocios')
        ->select('serieguia','numeroguia')
        ->where('IdEmpresa','=',$rucemp)
        ->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)
        ->first(); 

        $mozos = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        //->where('role_id','8')
        ->get();

        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

        $motivos = DB::tABLE('motivo_traslado')
        ->orderBy('motivo','asc')->get();

        $modalidades = DB::tABLE('modalidad_traslado')
        ->orderBy('modalidad','asc')->get();

        $ubigeos = DB::tABLE('cat_ubigeo')->get();

        $gastos = DB::tABLE('tipo_gastos')->get();

        return view('empresas.puntosventas.punto_venta',compact('mozos','categorias','comprobantes','documentos','estadopagos','mediospagos','monedas','senudoc','motivos','modalidades','ubigeos','gastos'));


    }



    public function punto_venta_editar(Request $request,$id){


        $rucemp = Auth::user()->IdEmpresa;

        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);

        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

        $categorias = DB::tABLE('categorias')->get();

        $comprobantes = DB::tABLE('tipo_documento')->where('ventas','1')->get();

        $documentos = DB::tABLE('tipo_documento_identidad')->orderby('orden','asc')->get();

        $estadopagos = DB::tABLE('credito_dias')->get();

        $mediospagos = DB::tABLE('medios_pagos')->get();

        $mozos = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        //->where('role_id','8')
        ->get();

        $cabecera = DB::tABLE('cpe_cabecera')
        ->where('IdCpe_cabecera',$id)
        ->first();

        $detalle = DB::tABLE('cpe_detalle')
        ->where('IdCpe_cabecera',$cabecera->IdCpe_cabecera)
        ->get();

        
        $cuotas = DB::tABLE('ventas_cuotas')
        ->where('IdCpe_cabecera',$id)
        ->orderby('ven_cuo_id','asc')
        ->get();

        $ventas_medios = DB::tABLE('venta_medio_pago')
        ->join('medios_pagos','medios_pagos.id_med_pag','venta_medio_pago.id_med_pag')
        ->where('IdCpe_cabecera',$cabecera->IdCpe_cabecera)
        ->get();


        if($cabecera->tdocod =='01' || $cabecera->tdocod=='03'){

            if($cabecera->ccacodsun ==='0'){


              return Redirect::to('/SisFact')->with('danger','EL COMPROBANTE YA FUE ENVIADO Y ACEPADO POR SUNAT, NO SE PUEDE MODIFICAR'); 
          }elseif($cabecera->ccacodsun >= 2000 && $cabecera->ccacodsun <= 3999){

           return Redirect::to('/SisFact')->with('danger','EL COMPROBANTE YA FUE ENVIADO Y SE ENCUENTRA ANULADO O RECHAZADO, NO SE PUEDE MODIFICAR'); 

       }
   }

   return view('empresas.puntosventas.punto_venta_editar',compact('mozos','categorias','comprobantes','documentos','estadopagos','mediospagos','detalle','cabecera','ventas_medios','empresa'));

}


  public function registrar_venta(Request $request){

      $rucemp = trim(Auth::user()->IdEmpresa);
      $empresa = Empresa::findOrFail($rucemp);

      $tdicod = $request->get('tdicod');
      $clinum = $request->get('clinum');
      $clinom = $request->get('clinom');
      $clidir = $request->get('clidir');
      $clicor = $request->get('clicor');
      $imprimir = $request->get('imprimir');
      $consumo = $request->get('consumo');

      $mondoc = 'PEN';
      $observaciones = $request->get('observaciones');
     

      $ped_id = $request->get('ped_id');
      $total_venta = $request->get('total_venta');
      $vuelto = $request->get('vuelto');

      $tdocod = $request->get('tdocod');
      $estadopago = $request->get('estadopago');
      $fecEmi = $request->get('fecEmi');
      $fecVen = $request->get('fecVen');
      $consumo = $request->get('consumo');

      $id_med_pag = $request->get('id_med_pag');
      $mon_med_pag = $request->get('mon_med_pag');
      $consumo = $request->get('consumo');
      

      $items = $request->get('txt_id_producto');
      $cantidades = $request->get('txt_cantidad');
      $precios = $request->get('precio_item');
      $pronom = $request->get('txt_producto');
      $total_item = $request->get('total_item');

      $buscre = DB::tABLE('credito_dias')->where('cre_dia_id',$estadopago)->first();

      $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

      $bus_alm= DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();

      $cont_carac = strlen($clinum);
      $obt_dig = substr(trim($clinum), 0, 2);

      if($tdocod=='01'){
        if($cont_carac =='11' && ($obt_dig=='10' || $obt_dig=='20' || $obt_dig=='15' || $obt_dig=='17') && $tdicod=='6'){
  
            }else{
                  return response()->json(['estado'=>'error','mensaje'=>'TIPO DE DOCUMENTO NO PERMITIDO PARA EMITIR UNA FACTURA']);
            }
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


      $cliente = Cliente::UpdateOrCreate(['clinum'=>$clinum],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$clicor,'tdicod'=>$tdicod,'telefono'=>$request->get('clitel'),'rucemp'=>Auth::user()->IdEmpresa]); 



        $cabecera = new cpe_cabecera;
        $cabecera->tdocod = $tdocod;
        $cabecera->ccafem = $fecEmi;
        $cabecera->topcod = '0101';
        $cabecera->id_almacen = $bus_alm->id_almacen;

      
        if($buscre->cre_dia_tip=='CONTADO' || $buscre->cre_dia_tip=='PERSONALIZADO'){
            $cabecera->ccafve = $request->get('fecVen');
        }else{
            $cabecera->ccafve = date('Y-m-d',strtotime($fecemi."+ ".$buscre->cre_dia_fac." days"));
        }

        if($buscre->cre_dia_tip=='CONTADO'){
            
            $cabecera->totalcontado = $total_venta;
            $cabecera->totalcredito = '0';

        }elseif($buscre->cre_dia_tip=='CREDITO' || $buscre->cre_dia_tip=='PERSONALIZADO'){
            $cabecera->totalcredito = $total_venta;
            $cabecera->totalcontado = '0';

        }
      
        $cabecera->ccaobs = $observaciones;
        $cabecera->tdicod = $tdicod;
        $cabecera->ccandi = $clinum;
        $cabecera->ccanom = $clinom;
        $cabecera->ped_id = $ped_id;
        $cabecera->consumo = $consumo;
        $cabecera->moncod = $mondoc;
        $cabecera->direccion = $clidir;
        $cabecera->clicorcli = $clicor;
        $cabecera->cre_dia_id = $estadopago;
        $cabecera->id_turno = Auth::user()->id_turno;
  
        if($sucursal->tip_igv_pred =='10'){
            $cabecera->ccatvg =  $total_venta/1.105;
            $cabecera->ccaigv =  $total_venta-$total_venta/1.105;
        }

        if($sucursal->tip_igv_pred =='20'){
            $cabecera->ccatexo =  $total_venta;
            $cabecera->ccaigv = '0.00';
        }
        
        $cabecera->ccatinaf =  '0.00';
        $cabecera->ccaitv = $total_venta;
        $cabecera->id_empresa_negocio = $sucursal->id_empresa_negocio;
        $cabecera->clicod = $cliente->clicod;
        $cabecera->vuelto = $vuelto;

        if($buscre->cre_dia_tip=='CONTADO'){
           $cabecera->estadopago = 'CONTADO';
        }else{
           $cabecera->estadopago = 'CREDITO';
        }
        
  
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->IdEmpresa =  Auth::user()->IdEmpresa;

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

        if(!empty($id_med_pag)){
            foreach($id_med_pag as $index_mp =>$mp){

                DB::tABLE('venta_medio_pago')
                ->insert(['id_turno'=>Auth::user()->id_turno,
                    'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
                    'id_med_pag'=>$mp,
                    'monto'=>$mon_med_pag[$index_mp]]);
            }
         
        }else{

            $bus_med_pag = DB::tABLE('medios_pagos')->where('IdEmpresa',Auth::user()->IdEmpresa)->where('predeterminado','1')->first();

             DB::tABLE('venta_medio_pago')
                ->insert(['id_turno'=>Auth::user()->id_turno,
                    'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
                    'id_med_pag'=>$bus_med_pag->id_med_pag,
                    'monto'=>$total_venta]);
        }
      

        foreach($items as $index => $item){

            $dat_pro = productos::findOrFail($item);

            if(empty($dat_pro->pro_rel)){
                $id_prod = $dat_pro->IdProducto;
            }else{
                $id_prod = $dat_pro->pro_rel;
            }


            if($dat_pro->tigcod =='10'){

              $precio_uni = $precios[$index];
              $valor_uni = ($precios[$index]/1.105);
             

              $valor_subtotal = ($precios[$index]*$cantidades[$index])/1.105;
              $valor_total = $precios[$index]*$cantidades[$index];
             
            }elseif($dat_pro->tigcod=='20'){
            
              $precio_uni = $precios[$index];
              $valor_uni = $precios[$index];
            

              $valor_subtotal = $precios[$index]*$cantidades[$index];
              $valor_total = $precios[$index]*$cantidades[$index];
            }

          

            $valor_igv_total =  $valor_total-$valor_subtotal;
           
        
            $detalle = new cpe_detalle;
            $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
            $detalle->cdecan = $cantidades[$index];
            $detalle->cdepuni = $precio_uni;
            $detalle->cdevun = $valor_uni;
            $detalle->cdevve  = $valor_total;
            $detalle->cdepve  = $valor_subtotal;
            $detalle->cdeigv = $valor_igv_total;
            $detalle->costo = $dat_pro->costo;
            $detalle->tigcod = $dat_pro->tigcod;
            $detalle->umecod = $dat_pro->umecod;
            $detalle->cpe_det_factor = $dat_pro->factor;
            $detalle->procod = $dat_pro->procod;
            $detalle->IdProducto = $item;
            $detalle->IdProducto_rel = $id_prod;
            $detalle->cdedes = $pronom[$index];
          //$detalle->pronomobs = $pronomobs[$index];  
            $detalle->icbper = $dat_pro->icbper;
          //$detalle->desc_mon = $cantidades[$index]*$descuento[$index];
            $detalle->id_almacen_pro = $bus_alm->id_almacen;
            $detalle->save();
           



        }

        self::registrar_movimiento_salida($cabecera->IdCpe_cabecera);


        if($imprimir=='1'){

            if($empresa->formato =='TICKET'){

                for($i=1;$i<=$empresa->imp_venta;$i++){

                     
                    self::imprimir($cabecera->IdCpe_cabecera,$tdocod);
                    
                }

            }

        }



        if($tdocod =='01' || $tdocod=='03'){
            $sunat = new cpe_cabecera;
            $sunat->generar_nuevo_qr($cabecera->IdCpe_cabecera);
            $sunat->generar_xml_boleta_factura($cabecera->IdCpe_cabecera);

            if($empresa->tipo_envio =='1'){
                
                $sunat->enviar_sunat($cabecera->IdCpe_cabecera); 
            }

            
        }

        return response()->json(['estado'=>'success','codfact' =>$cabecera->IdCpe_cabecera,'tdocod'=>$tdocod,'mensaje'=>'Comprobante Emitido']);

    }


public function registrar_venta_directa(Request $request){

  $rucemp = trim(Auth::user()->IdEmpresa);
  $empresa = Empresa::findOrFail($rucemp);

  $med_pag = $request->get('med_pag');
  $mozo = $request->get('mozo');
  $tdicod = $request->get('tdicod');
  $imprimir = $request->get('imprimir');
  $consumo = $request->get('consumo');

  $mon_cuo = $request->get('mon_cuo');
  $fec_cuo = $request->get('fec_cuo');

  $clinum = $request->get('clinum');
  $clinom = $request->get('clinom');
  $clidir = $request->get('clidir');
  $clicor = $request->get('clicor');

  $mondoc = 'PEN';
  $observaciones = $request->get('observaciones');


  $ped_id = $request->get('ped_id');
  $total_venta = $request->get('total_venta');
  $vuelto = $request->get('vuelto');

  $tdocod = $request->get('tdocod');
  $estadopago = $request->get('estadopago');
  $fecEmi = $request->get('fecEmi');
  $fecVen = $request->get('fecVen');
  $consumo = $request->get('consumo');

  $id_med_pag = $request->get('id_med_pag');
  $mon_med_pag = $request->get('mon_med_pag');
  $consumo = $request->get('consumo');


  $items = $request->get('txt_id_producto');
  $cantidades = $request->get('txt_cantidad');
  $precios = $request->get('precios');

  $buscre = DB::tABLE('credito_dias')->where('cre_dia_id',$estadopago)->first();

  $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

  $bus_alm= DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();

  $cont_carac = strlen($clinum);
  $obt_dig = substr(trim($clinum), 0, 2);

  if($tdocod=='01'){
    if($cont_carac =='11' && ($obt_dig=='10' || $obt_dig=='20' || $obt_dig=='15' || $obt_dig=='17') && $tdicod=='6'){

    }else{
      return response()->json(['estado'=>'error','mensaje'=>'TIPO DE DOCUMENTO NO PERMITIDO PARA EMITIR UNA FACTURA']);
  }
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


$cliente = Cliente::UpdateOrCreate(['clinum'=>$clinum],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$clicor,'tdicod'=>$tdicod,'telefono'=>$request->get('clitel'),'rucemp'=>Auth::user()->IdEmpresa]); 



$cabecera = new cpe_cabecera;
$cabecera->tdocod = $tdocod;
$cabecera->ccafem = $fecEmi;
$cabecera->topcod = '0101';
$cabecera->id_almacen = $bus_alm->id_almacen;


if($buscre->cre_dia_tip=='CONTADO' || $buscre->cre_dia_tip=='PERSONALIZADO'){
    $cabecera->ccafve = $request->get('fecVen');
}else{
    $cabecera->ccafve = date('Y-m-d',strtotime($fecemi."+ ".$buscre->cre_dia_fac." days"));
}

if($buscre->cre_dia_tip=='CONTADO'){

    $cabecera->totalcontado = $total_venta;
    $cabecera->totalcredito = '0';

}elseif($buscre->cre_dia_tip=='CREDITO' || $buscre->cre_dia_tip=='PERSONALIZADO'){
    $cabecera->totalcredito = $total_venta;
    $cabecera->totalcontado = '0';

}

$cabecera->ccaobs = $observaciones;
$cabecera->consumo = $consumo;
$cabecera->tdicod = $tdicod;
$cabecera->ccandi = $clinum;
$cabecera->ccanom = $clinom;
$cabecera->ped_id = $ped_id;
$cabecera->moncod = $mondoc;
$cabecera->direccion = $clidir;
$cabecera->clicorcli = $clicor;
$cabecera->cre_dia_id = $estadopago;
$cabecera->id_turno = Auth::user()->id_turno;

if($sucursal->tip_igv_pred =='10'){
    $cabecera->ccatvg =  $total_venta/1.105;
    $cabecera->ccaigv =  $total_venta-$total_venta/1.105;
}

if($sucursal->tip_igv_pred =='20'){
    $cabecera->ccatexo =  $total_venta;
    $cabecera->ccaigv = '0.00';
}

$cabecera->ccatinaf =  '0.00';
$cabecera->ccaitv = $total_venta;
$cabecera->id_empresa_negocio = $sucursal->id_empresa_negocio;
$cabecera->clicod = $cliente->clicod;
$cabecera->vuelto = $vuelto;

if($buscre->cre_dia_tip=='CONTADO'){
 $cabecera->estadopago = 'CONTADO';
}else{
 $cabecera->estadopago = 'CREDITO';
}


$cabecera->IdUsuario = Auth::user()->IdUsuario;
$cabecera->IdUsuario_ven = $mozo;
$cabecera->IdEmpresa =  Auth::user()->IdEmpresa;

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


// =================================================================
    // INICIO: LÓGICA DE FIDELIZACIÓN (PUNTOS HOLA P)
    // =================================================================
    // Solo damos puntos si no es un cliente "Varios" (00000000)
    if($clinum != '00000000' && strlen(trim($clinum)) >= 8) {
    $saldo_antes = $cliente->puntos ?? 0;
    
    // Obtenemos la tasa de conversión (1 sol = X puntos)
    $regla_base = DB::table('fidelizacion_configs')->where('activo', 1)->first();
    $valor_sol = $regla_base ? $regla_base->valor_sol : 1;
    
    $puntos_ganados = floor($total_venta / $valor_sol);
    $puntos_gastados_total = 0;

    // 1. PROCESAR PREMIOS CANJEADOS EN ESTA VENTA
    if($request->has('premios_canjeados')){
        foreach($request->get('premios_canjeados') as $id_regla){
            $regla_canje = DB::table('fidelizacion_configs')->where('id', $id_regla)->first();
            if($regla_canje){
                $puntos_gastados_total += $regla_canje->puntos_minimos;
                
                // Guardamos el movimiento del premio vinculado a ESTA boleta
                DB::table('puntos_historial')->insert([
                    'cliente_id' => $cliente->clicod,
                    'venta_id' => $cabecera->IdCpe_cabecera,
                    'puntos_ganados' => 0,
                    'puntos_canjeados' => $regla_canje->puntos_minimos,
                    'saldo_antes' => 0, // No es necesario aquí, calcularemos el saldo final global
                    'saldo_despues' => 0, 
                    'motivo' => 'PREMIO: ' . strtoupper($regla_canje->premio),
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now()
                ]);
            }
        }
    }

    // 2. REGISTRAR LOS PUNTOS GANADOS
    if($puntos_ganados > 0){
         DB::table('puntos_historial')->insert([
            'cliente_id' => $cliente->clicod,
            'venta_id' => $cabecera->IdCpe_cabecera,
            'puntos_ganados' => $puntos_ganados,
            'puntos_canjeados' => 0,
            'saldo_antes' => 0,
            'saldo_despues' => 0,
            'motivo' => 'Consumo en Venta',
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now()
        ]);
    }

    // 3. ACTUALIZAR SALDO FINAL DEL CLIENTE
    $saldo_final = $saldo_antes + $puntos_ganados - $puntos_gastados_total;
    DB::table('cliente')
        ->where('clicod', $cliente->clicod)
        ->update(['puntos' => $saldo_final]);
}
    // =================================================================
    // FIN: LÓGICA DE FIDELIZACIÓN
    // =================================================================


self::generar_codigo_movimiento($cabecera->IdCpe_cabecera);

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
                'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera
            ]);
        }
    }else{

        DB::tABLE('ventas_cuotas')
        ->insert([
            'ven_cuo_num'=>'1',
            'ven_cuo_fec_ven'=>$cabecera->ccafve,
            'ven_cuo_mon'=>$cabecera->ccaitv,
            'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera
        ]);

    }
}



if(!empty($id_med_pag)){
    foreach($id_med_pag as $index_mp =>$mp){

        DB::tABLE('venta_medio_pago')
        ->insert(['id_turno'=>Auth::user()->id_turno,
            'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
            'id_med_pag'=>$mp,
            'monto'=>$mon_med_pag[$index_mp]]);
    }
    
}else{

 
   DB::tABLE('venta_medio_pago')
   ->insert(['id_turno'=>Auth::user()->id_turno,
    'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
    'id_med_pag'=>$med_pag,
    'monto'=>$total_venta]);
}


foreach($items as $index => $item){

    $dat_pro = productos::findOrFail($item);

    if(empty($dat_pro->pro_rel)){
        $id_prod = $dat_pro->IdProducto;
    }else{
        $id_prod = $dat_pro->pro_rel;
    }


    if($dat_pro->tigcod =='10'){

      $precio_uni = $precios[$index];
      $valor_uni = ($precios[$index]/1.105);


      $valor_subtotal = ($precios[$index]*$cantidades[$index])/1.105;
      $valor_total = $precios[$index]*$cantidades[$index];

  }elseif($dat_pro->tigcod=='20'){

      $precio_uni = $precios[$index];
      $valor_uni = $precios[$index];


      $valor_subtotal = $precios[$index]*$cantidades[$index];
      $valor_total = $precios[$index]*$cantidades[$index];
  }



  $valor_igv_total =  $valor_total-$valor_subtotal;


  $detalle = new cpe_detalle;
  $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
  $detalle->cdecan = $cantidades[$index];
  $detalle->cdepuni = $precio_uni;
  $detalle->cdevun = $valor_uni;
  $detalle->cdevve  = $valor_total;
  $detalle->cdepve  = $valor_subtotal;
  $detalle->cdeigv = $valor_igv_total;
  $detalle->costo = $dat_pro->costo_total;
  $detalle->tigcod = $dat_pro->tigcod;
  $detalle->umecod = $dat_pro->umecod;
  $detalle->cpe_det_factor = $dat_pro->factor;
  $detalle->procod = $dat_pro->procod;
  $detalle->IdProducto = $item;
  $detalle->IdProducto_rel = $id_prod;
  $detalle->cdedes = $dat_pro->pronom;
          //$detalle->pronomobs = $pronomobs[$index];  
  $detalle->icbper = $dat_pro->icbper;
          //$detalle->desc_mon = $cantidades[$index]*$descuento[$index];
  $detalle->id_almacen_pro = $bus_alm->id_almacen;
  $detalle->save();


}


//self::imprimir_comanda_venta_directa($cabecera->IdCpe_cabecera);
//self::imprimir_precuenta_venta_directa($cabecera->IdCpe_cabecera);
self::registrar_movimiento_salida($cabecera->IdCpe_cabecera);


if($imprimir=='1'){

    if($sucursal->formato =='TICKET' && $sucursal->ticket_pantalla=='0'){    
        for($i=1;$i<=$empresa->imp_venta;$i++){
            self::imprimir($cabecera->IdCpe_cabecera,$tdocod);
            
        }
    }
}






if($tdocod =='01' || $tdocod=='03'){
    $sunat = new cpe_cabecera;
    $sunat->generar_nuevo_qr($cabecera->IdCpe_cabecera);
    $sunat->generar_xml_boleta_factura($cabecera->IdCpe_cabecera);

    if($empresa->tipo_envio =='1'){

        $sunat->enviar_sunat($cabecera->IdCpe_cabecera); 
    }


}


$usuario_facturacion = new usuario_facturacion;
$usuario_facturacion->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
$usuario_facturacion->id_turno = Auth::user()->id_turno;
$usuario_facturacion->id_empresa_negocio = $sucursal->id_empresa_negocio;
$usuario_facturacion->IdEmpresa = $rucemp;
$usuario_facturacion->referencia = "Registro";
$usuario_facturacion->save();

return response()->json(['estado'=>'success','codfact' =>$cabecera->IdCpe_cabecera,'tdocod'=>$tdocod,'mensaje'=>'Comprobante Emitido']);

}


public function actualizar_venta(Request $request){

  $rucemp = trim(Auth::user()->IdEmpresa);
  $empresa = Empresa::findOrFail($rucemp);

  $venta = $request->get('IdCpe_cabecera');

  $med_pag = $request->get('med_pag');

  $tdicod = $request->get('tdicod');
  $imprimir = $request->get('imprimir');
  $consumo = $request->get('consumo');

  $clinum = $request->get('clinum');
  $clinom = $request->get('clinom');
  $clidir = $request->get('clidir');
  $clicor = $request->get('clicor');

  $mondoc = 'PEN';
  $observaciones = $request->get('observaciones');


  $ped_id = $request->get('ped_id');
  $total_venta = $request->get('total_venta');
  $vuelto = $request->get('vuelto');

  $tdocod = $request->get('tdocod');
  $estadopago = $request->get('estadopago');
  $fecEmi = $request->get('fecEmi');
  $fecVen = $request->get('fecVen');
  $consumo = $request->get('consumo');

  $id_med_pag = $request->input('id_med_pag', []);
  $mon_med_pag = $request->input('mon_med_pag', []);

  if(!is_array($id_med_pag) && !empty($id_med_pag)){
      $id_med_pag = [$id_med_pag];
  }

  if(!is_array($mon_med_pag) && !empty($mon_med_pag)){
      $mon_med_pag = [$mon_med_pag];
  }


  $items = $request->get('txt_id_producto');
  $cantidades = $request->get('txt_cantidad');
  $precios = $request->get('precios');

  $buscre = DB::tABLE('credito_dias')->where('cre_dia_id',$estadopago)->first();

  $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

  $bus_alm= DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();

  $cont_carac = strlen($clinum);
  $obt_dig = substr(trim($clinum), 0, 2);

  if($tdocod=='01'){
    if($cont_carac =='11' && ($obt_dig=='10' || $obt_dig=='20' || $obt_dig=='15' || $obt_dig=='17') && $tdicod=='6'){

    }else{
      return response()->json(['estado'=>'error','mensaje'=>'TIPO DE DOCUMENTO NO PERMITIDO PARA EMITIR UNA FACTURA']);
  }
}



$cliente = Cliente::UpdateOrCreate(['clinum'=>$clinum],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$clicor,'tdicod'=>$tdicod,'telefono'=>$request->get('clitel'),'rucemp'=>Auth::user()->IdEmpresa]); 

$cabecera = cpe_cabecera::findOrFail($venta);
$cabecera->tdocod = $tdocod;
$cabecera->ccafem = $fecEmi;
$cabecera->topcod = '0101';
$cabecera->id_almacen = $bus_alm->id_almacen;


if($buscre->cre_dia_tip=='CONTADO' || $buscre->cre_dia_tip=='PERSONALIZADO'){
    $cabecera->ccafve = $request->get('fecVen');
}else{
    $cabecera->ccafve = date('Y-m-d',strtotime($fecemi."+ ".$buscre->cre_dia_fac." days"));
}

if($buscre->cre_dia_tip=='CONTADO'){

    $cabecera->totalcontado = $total_venta;
    $cabecera->totalcredito = '0';

}elseif($buscre->cre_dia_tip=='CREDITO' || $buscre->cre_dia_tip=='PERSONALIZADO'){
    $cabecera->totalcredito = $total_venta;
    $cabecera->totalcontado = '0';

}

$cabecera->ccaobs = $observaciones;
$cabecera->consumo = $consumo;
$cabecera->tdicod = $tdicod;
$cabecera->ccandi = $clinum;
$cabecera->ccanom = $clinom;
$cabecera->ped_id = $ped_id;
$cabecera->moncod = $mondoc;
$cabecera->direccion = $clidir;
$cabecera->clicorcli = $clicor;
$cabecera->cre_dia_id = $estadopago;
$cabecera->id_turno = Auth::user()->id_turno;
$cabecera->cod_tip_ope ='01';

if($sucursal->tip_igv_pred =='10'){
    $cabecera->ccatvg =  $total_venta/1.105;
    $cabecera->ccaigv =  $total_venta-$total_venta/1.105;
}

if($sucursal->tip_igv_pred =='20'){
    $cabecera->ccatexo =  $total_venta;
    $cabecera->ccaigv = '0.00';
}

$cabecera->ccatinaf =  '0.00';
$cabecera->ccaitv = $total_venta;
$cabecera->id_empresa_negocio = $sucursal->id_empresa_negocio;
$cabecera->clicod = $cliente->clicod;
$cabecera->vuelto = $vuelto;

if($buscre->cre_dia_tip=='CONTADO'){
 $cabecera->estadopago = 'CONTADO';
}else{
 $cabecera->estadopago = 'CREDITO';
}


$cabecera->IdUsuario = Auth::user()->IdUsuario;
$cabecera->IdEmpresa =  Auth::user()->IdEmpresa;
$cabecera->update();

$registros = DB::tABLE('cpe_detalle')->where('IdCpe_cabecera',$venta)->get();



DB::tABLE('venta_medio_pago')->where('IdCpe_cabecera',$venta)->delete();

if(count($id_med_pag) > 0){
    $mon_med_pag = array_values($mon_med_pag);

    foreach($id_med_pag as $index_mp => $mp){
        $monto = isset($mon_med_pag[$index_mp]) ? $mon_med_pag[$index_mp] : $total_venta;

        DB::tABLE('venta_medio_pago')
        ->insert(['id_turno'=>Auth::user()->id_turno,
            'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
            'id_med_pag'=>$mp,
            'monto'=>$monto]);
    }
}else{
   DB::tABLE('venta_medio_pago')
   ->insert(['id_turno'=>Auth::user()->id_turno,
    'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
    'id_med_pag'=>$med_pag,
    'monto'=>$total_venta]);
}




foreach ($registros as $reg) {


    $buspro = DB::tABLE('productos')->where('IdProducto',$reg->IdProducto)->first();

    if(!empty($buspro->pro_rel)){
        $id = $buspro->pro_rel;
    }else{
        $id = $reg->IdProducto;
    }


    $stock_prod = DB::tABLE('producto_stock')
    ->where('IdProducto',$id)
    ->where('id_almacen',$bus_alm->id_almacen)
    ->first();

    DB::tABLE('producto_stock')
    ->where('IdProducto',$id)
    ->where('id_almacen',$bus_alm->id_almacen)
    ->update(['stock'=>$stock_prod->stock+($reg->cdecan*$reg->cpe_det_factor)]);


}

DB::tABLE('cpe_detalle')->where('IdCpe_cabecera',$venta)->delete();

DB::tABLE('movimientos_productos')->where('IdCpe_cabecera',$venta)->delete();


foreach($items as $index => $item){

    $dat_pro = productos::findOrFail($item);

    if(empty($dat_pro->pro_rel)){
        $id_prod = $dat_pro->IdProducto;
    }else{
        $id_prod = $dat_pro->pro_rel;
    }


    if($dat_pro->tigcod =='10'){

      $precio_uni = $precios[$index];
      $valor_uni = ($precios[$index]/1.105);


      $valor_subtotal = ($precios[$index]*$cantidades[$index])/1.105;
      $valor_total = $precios[$index]*$cantidades[$index];

  }elseif($dat_pro->tigcod=='20'){

      $precio_uni = $precios[$index];
      $valor_uni = $precios[$index];


      $valor_subtotal = $precios[$index]*$cantidades[$index];
      $valor_total = $precios[$index]*$cantidades[$index];
  }



  $valor_igv_total =  $valor_total-$valor_subtotal;


  $detalle = new cpe_detalle;
  $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
  $detalle->cdecan = $cantidades[$index];
  $detalle->cdepuni = $precio_uni;
  $detalle->cdevun = $valor_uni;
  $detalle->cdevve  = $valor_total;
  $detalle->cdepve  = $valor_subtotal;
  $detalle->cdeigv = $valor_igv_total;
  $detalle->costo = $dat_pro->costo_total;
  $detalle->tigcod = $dat_pro->tigcod;
  $detalle->umecod = $dat_pro->umecod;
  $detalle->cpe_det_factor = $dat_pro->factor;
  $detalle->procod = $dat_pro->procod;
  $detalle->IdProducto = $item;
  $detalle->IdProducto_rel = $id_prod;
  $detalle->cdedes = $dat_pro->pronom;
          //$detalle->pronomobs = $pronomobs[$index];  
  $detalle->icbper = $dat_pro->icbper;
          //$detalle->desc_mon = $cantidades[$index]*$descuento[$index];
  $detalle->id_almacen_pro = $bus_alm->id_almacen;
  $detalle->save();

   
}


 self::registrar_movimiento_salida($cabecera->IdCpe_cabecera);

if($imprimir=='1'){

    if($empresa->formato =='TICKET'){


        for($i=1;$i<=$empresa->imp_venta;$i++){


            self::imprimir($cabecera->IdCpe_cabecera,$tdocod);

        }

    }

}




if($tdocod =='01' || $tdocod=='03'){
    $sunat = new cpe_cabecera;
    $sunat->generar_nuevo_qr($cabecera->IdCpe_cabecera);
    $sunat->generar_xml_boleta_factura($cabecera->IdCpe_cabecera);

    if($empresa->tipo_envio =='1'){

        $sunat->enviar_sunat($cabecera->IdCpe_cabecera); 
    }


}

return response()->json(['estado'=>'success','codfact' =>$cabecera->IdCpe_cabecera,'tdocod'=>$tdocod,'mensaje'=>'Comprobante Emitido']);

}


public function registrar_movimiento_salida($id){

    $cabecera = cpe_cabecera::findOrFail($id);
    $detalle = cpe_detalle::where('IdCpe_cabecera',$id)->get();

    // PRE-CARGAR todos los productos del detalle para reducir consultas
    $productos_ids = $detalle->pluck('IdProducto')->toArray();
    $productos_cache = productos::whereIn('IdProducto', $productos_ids)->get()->keyBy('IdProducto');

    foreach($detalle as $det){

        $bus_pro = $productos_cache[$det->IdProducto];

        if(empty($bus_pro->pro_rel)){
            $id_prod = $bus_pro->IdProducto;
        }else{
            $id_prod = $bus_pro->pro_rel;
        }

        // ========== PROMOCION = 0 (PRODUCTOS SIMPLES) ==========
        if($bus_pro->promocion =='0'){

            $cantidad_principal = $det->cdecan * $det->cpe_det_factor;
            $cantidad_equivalente = 0;
            
            if(!empty($bus_pro->factor_cons) && $bus_pro->factor_cons > 0){
                $cantidad_equivalente = $cantidad_principal * $bus_pro->factor_cons;
            }

            DB::tABLE('movimientos_productos')->insert([
            'IdProducto'=>$det->IdProducto,
            'IdProducto_rel'=>$id_prod,
            'precio'=>$det->cdepuni,
            'cantidad'=>$cantidad_principal,
            'cantidad_equivalente'=>$cantidad_equivalente,
            'costo'=>$det->costo,
            'cliente'=>$cabecera->ccanom,
            'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
            'serie'=>$cabecera->serdoc,
            'numero'=>$cabecera->numdoc,
            'tdocod'=>$cabecera->tdocod,
            'tipo'=>'3',
            'mov_tip'=>'E',
            'stock_equivalente'=>$cantidad_equivalente,
            'id_empresa_negocio'=>$cabecera->id_empresa_negocio,
            'id_almacen'=>$cabecera->id_almacen,
            'fecha_mov'=>$cabecera->ccafem,
                ]);

            $stock_prod = DB::tABLE('producto_stock')
            ->where('IdProducto',$id_prod)
            ->where('id_almacen',$cabecera->id_almacen)
            ->first();

            if($stock_prod){
                DB::tABLE('producto_stock')
                    ->where('pro_sto_id',$stock_prod->pro_sto_id)
                    ->update([
                        'stock' => $stock_prod->stock - $cantidad_principal,
                        'stock_equivalencia' => $stock_prod->stock_equivalencia - $cantidad_equivalente
                    ]);
            }

        } 
        // ========== PROMOCION = 2 (PREPARADOS CON RECETA) ==========
        elseif($bus_pro->promocion =='2'){

                $bus_receta = DB::TABLE('recetas')->where('prod_id',$id_prod)->get();

                if(count($bus_receta)>0){
                    
                    // PRE-CARGAR todos los insumos de la receta
                    $insumos_ids = $bus_receta->pluck('prod_insu')->toArray();
                    $insumos_cache = productos::whereIn('IdProducto', $insumos_ids)->get()->keyBy('IdProducto');
                    
                    // Array para acumular actualizaciones de stock
                    $movimientos = [];
                    $actualizaciones_stock = [];
            
                    foreach($bus_receta as $rec){

                        $bus_insumo = $insumos_cache[$rec->prod_insu];
                        
                        $cantidad_equivalente = $det->cdecan * $rec->rec_cant;
                        
                        $cantidad_principal = 0;
                        if(!empty($bus_insumo->factor_cons) && $bus_insumo->factor_cons > 0){
                            $cantidad_principal = $cantidad_equivalente / $bus_insumo->factor_cons;
                        }

                        $movimientos[] = [
                            'IdProducto'=>$rec->prod_insu,
                            'IdProducto_rel'=>$rec->prod_insu,
                            'precio'=>'0',
                            'cantidad'=>$cantidad_principal,
                            'cantidad_equivalente'=>$cantidad_equivalente,
                            'costo'=>$rec->ins_costo,
                            'cliente'=>$cabecera->ccanom,
                            'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
                            'serie'=>$cabecera->serdoc,
                            'numero'=>$cabecera->numdoc,
                            'tdocod'=>$cabecera->tdocod,
                            'tipo'=>'3',
                            'mov_tip'=>'E',
                            'stock_equivalente'=>$cantidad_equivalente,
                            'id_empresa_negocio'=>$cabecera->id_empresa_negocio,
                            'id_almacen'=>$cabecera->id_almacen,
                            'fecha_mov'=>$cabecera->ccafem,
                        ];

                        $actualizaciones_stock[] = [
                            'IdProducto' => $rec->prod_insu,
                            'cantidad_principal' => $cantidad_principal,
                            'cantidad_equivalente' => $cantidad_equivalente
                        ];

                    }

                    // INSERT masivo de movimientos
                    if(!empty($movimientos)){
                        DB::tABLE('movimientos_productos')->insert($movimientos);
                    }

                    // Actualizar stocks
                    foreach($actualizaciones_stock as $act){
                        $stock_prod_ins = DB::tABLE('producto_stock')
                        ->where('IdProducto',$act['IdProducto'])
                        ->where('id_almacen',$cabecera->id_almacen)
                        ->first();

                        if($stock_prod_ins){
                            DB::tABLE('producto_stock')
                                ->where('pro_sto_id',$stock_prod_ins->pro_sto_id)
                                ->update([
                                    'stock' => $stock_prod_ins->stock - $act['cantidad_principal'],
                                    'stock_equivalencia' => $stock_prod_ins->stock_equivalencia - $act['cantidad_equivalente']
                                ]);
                        }
                    }

                }

        } 
        // ========== PROMOCION = 3 (COMBOS) ========== OPTIMIZADO
        elseif($bus_pro->promocion =='3'){

            $bus_combo = DB::tABLE('combos')->where('IdProducto_comb',$id_prod)->get();

            if(count($bus_combo)>0){

                // PRE-CARGAR todos los productos del combo
                $combo_productos_ids = $bus_combo->pluck('IdProducto_rel')->toArray();
                $combo_productos_cache = productos::whereIn('IdProducto', $combo_productos_ids)->get()->keyBy('IdProducto');

                // Arrays para acumular operaciones
                $movimientos = [];
                $actualizaciones_stock = [];

                foreach($bus_combo as $combo_item){

                    $bus_pro_item = $combo_productos_cache[$combo_item->IdProducto_rel];

                    if(empty($bus_pro_item->pro_rel)){
                        $id_prod_item = $bus_pro_item->IdProducto;
                    }else{
                        $id_prod_item = $bus_pro_item->pro_rel;
                    }

                    // ===== CASO 1: PRODUCTO SIMPLE (promocion=0) =====
                    if($bus_pro_item->promocion =='0'){

                        $cantidad_principal = ($det->cdecan * $combo_item->prod_comb_cant) * $bus_pro_item->factor;
                        $cantidad_equivalente = 0;
                        
                        if(!empty($bus_pro_item->factor_cons) && $bus_pro_item->factor_cons > 0){
                            $cantidad_equivalente = $cantidad_principal * $bus_pro_item->factor_cons;
                        }

                        $movimientos[] = [
                        'IdProducto'=>$combo_item->IdProducto_rel,
                        'IdProducto_rel'=>$id_prod_item,
                        'precio'=>$combo_item->prod_comb_prec,
                        'cantidad'=>$cantidad_principal,
                        'cantidad_equivalente'=>$cantidad_equivalente,
                        'costo'=>$combo_item->prod_comb_cost,
                        'cliente'=>$cabecera->ccanom,
                        'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
                        'serie'=>$cabecera->serdoc,
                        'numero'=>$cabecera->numdoc,
                        'tdocod'=>$cabecera->tdocod,
                        'tipo'=>'3',
                        'mov_tip'=>'E',
                        'stock_equivalente'=>$cantidad_equivalente,
                        'id_empresa_negocio'=>$cabecera->id_empresa_negocio,
                        'id_almacen'=>$cabecera->id_almacen,
                        'fecha_mov'=>$cabecera->ccafem,
                        'descripcion'=>'Combo',
                        ];

                        $actualizaciones_stock[] = [
                            'IdProducto' => $id_prod_item,
                            'cantidad_principal' => $cantidad_principal,
                            'cantidad_equivalente' => $cantidad_equivalente
                        ];

                    } 
                    // ===== CASO 2: PRODUCTO PREPARADO (promocion=2) =====
                    elseif($bus_pro_item->promocion =='2'){

                        $bus_receta = DB::TABLE('recetas')->where('prod_id',$id_prod_item)->get();

                        if(count($bus_receta)>0){
                            
                            // PRE-CARGAR insumos de la receta
                            $receta_insumos_ids = $bus_receta->pluck('prod_insu')->toArray();
                            $receta_insumos_cache = productos::whereIn('IdProducto', $receta_insumos_ids)->get()->keyBy('IdProducto');
                    
                            foreach($bus_receta as $rec){

                                $bus_insumo = $receta_insumos_cache[$rec->prod_insu];
                                
                                $cantidad_equivalente = ($det->cdecan * $combo_item->prod_comb_cant) * $rec->rec_cant;
                                
                                $cantidad_principal = 0;
                                if(!empty($bus_insumo->factor_cons) && $bus_insumo->factor_cons > 0){
                                    $cantidad_principal = $cantidad_equivalente / $bus_insumo->factor_cons;
                                }

                                $movimientos[] = [
                                'IdProducto'=>$rec->prod_insu,
                                'IdProducto_rel'=>$rec->prod_insu,
                                'precio'=>'0',
                                'cantidad'=>$cantidad_principal,
                                'cantidad_equivalente'=>$cantidad_equivalente,
                                'costo'=>$rec->ins_costo,
                                'cliente'=>$cabecera->ccanom,
                                'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
                                'serie'=>$cabecera->serdoc,
                                'numero'=>$cabecera->numdoc,
                                'tdocod'=>$cabecera->tdocod,
                                'tipo'=>'3',
                                'mov_tip'=>'E',
                                'stock_equivalente'=>$cantidad_equivalente,
                                'id_empresa_negocio'=>$cabecera->id_empresa_negocio,
                                'id_almacen'=>$cabecera->id_almacen,
                                'fecha_mov'=>$cabecera->ccafem,
                                'descripcion'=>'Combo',
                                ];

                                $actualizaciones_stock[] = [
                                    'IdProducto' => $rec->prod_insu,
                                    'cantidad_principal' => $cantidad_principal,
                                    'cantidad_equivalente' => $cantidad_equivalente
                                ];

                            }

                        }

                    }
                    // ===== CASO 3: INSUMO (promocion=4) =====
                    elseif($bus_pro_item->promocion =='4'){

                        $cantidad_principal = ($det->cdecan * $combo_item->prod_comb_cant) * $bus_pro_item->factor;
                        $cantidad_equivalente = 0;
                        
                        if(!empty($bus_pro_item->factor_cons) && $bus_pro_item->factor_cons > 0){
                            $cantidad_equivalente = $cantidad_principal * $bus_pro_item->factor_cons;
                        }

                        $movimientos[] = [
                        'IdProducto'=>$combo_item->IdProducto_rel,
                        'IdProducto_rel'=>$id_prod_item,
                        'precio'=>$combo_item->prod_comb_prec,
                        'cantidad'=>$cantidad_principal,
                        'cantidad_equivalente'=>$cantidad_equivalente,
                        'costo'=>$combo_item->prod_comb_cost,
                        'cliente'=>$cabecera->ccanom,
                        'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
                        'serie'=>$cabecera->serdoc,
                        'numero'=>$cabecera->numdoc,
                        'tdocod'=>$cabecera->tdocod,
                        'tipo'=>'3',
                        'mov_tip'=>'E',
                        'stock_equivalente'=>$cantidad_equivalente,
                        'id_empresa_negocio'=>$cabecera->id_empresa_negocio,
                        'id_almacen'=>$cabecera->id_almacen,
                        'fecha_mov'=>$cabecera->ccafem,
                        'descripcion'=>'Combo',
                        ];

                        $actualizaciones_stock[] = [
                            'IdProducto' => $id_prod_item,
                            'cantidad_principal' => $cantidad_principal,
                            'cantidad_equivalente' => $cantidad_equivalente
                        ];

                    }

                } // Fin foreach $bus_combo

                // INSERT masivo de movimientos
                if(!empty($movimientos)){
                    DB::tABLE('movimientos_productos')->insert($movimientos);
                }

                // Actualizar stocks
                foreach($actualizaciones_stock as $act){
                    $stock_prod = DB::tABLE('producto_stock')
                    ->where('IdProducto',$act['IdProducto'])
                    ->where('id_almacen',$cabecera->id_almacen)
                    ->first();

                    if($stock_prod){
                        DB::tABLE('producto_stock')
                            ->where('pro_sto_id',$stock_prod->pro_sto_id)
                            ->update([
                                'stock' => $stock_prod->stock - $act['cantidad_principal'],
                                'stock_equivalencia' => $stock_prod->stock_equivalencia - $act['cantidad_equivalente']
                            ]);
                    }
                }

            }

        } // Fin promocion = 3

    } // Fin foreach $detalle

}



public function registrar_movimiento_ingreso($id){

    $cabecera = cpe_cabecera::findOrFail($id);

    $detalle = cpe_detalle::where('IdCpe_cabecera',$id)->get();

    foreach($detalle as $det){

        $bus_pro = productos::findOrFail($det->IdProducto);

        if(empty($bus_pro->pro_rel)){
            $id_prod = $bus_pro->IdProducto;
        }else{
            $id_prod = $bus_pro->pro_rel;
        }

        if($bus_pro->promocion =='0'){

            DB::tABLE('movimientos_productos')->insert([
            'IdProducto'=>$det->IdProducto,
            'IdProducto_rel'=>$id_prod,
            'precio'=>$det->cdepuni,
            'cantidad'=>$det->cdecan*$det->cpe_det_factor,
            'costo'=>$det->costo,
            'cliente'=>$cabecera->ccanom,
            'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
            'serie'=>$cabecera->serdoc,
            'numero'=>$cabecera->numdoc,
            'tdocod'=>$cabecera->tdocod,
            'tipo'=>'2',
            'mov_tip'=>'I',
            'id_empresa_negocio'=>$cabecera->id_empresa_negocio,
            'id_almacen'=>$cabecera->id_almacen,
            'fecha_mov'=>$cabecera->ccafem,
            'descripcion'=>'ANULACION',
            'cod_tip_ope'=>'02',
                ]);

          
            $mov_cal_stock = new Almacen();
            $mov_cal_stock->movimiento_calcular_stock($id_prod,$cabecera->id_almacen);

          

        }elseif($bus_pro->promocion =='2'){

                $bus_receta = DB::TABLE('recetas')->where('prod_id',$id_prod)->get();

                if(count($bus_receta)>0){
            
                    foreach($bus_receta as $rec){

                        DB::tABLE('movimientos_productos')->insert([
                            'IdProducto'=>$rec->prod_insu,
                            'IdProducto_rel'=>$rec->prod_insu,
                            'precio'=>'0',
                            'cantidad'=>$det->cdecan*$rec->rec_cant,
                            'costo'=>$rec->ins_costo,
                            'cliente'=>$cabecera->ccanom,
                            'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
                            'serie'=>$cabecera->serdoc,
                            'numero'=>$cabecera->numdoc,
                            'tdocod'=>$cabecera->tdocod,
                            'tipo'=>'2',
                            'mov_tip'=>'I',
                            'id_empresa_negocio'=>$cabecera->id_empresa_negocio,
                            'id_almacen'=>$cabecera->id_almacen,
                            'fecha_mov'=>$cabecera->ccafem,
                            'descripcion'=>'ANULACION',
                            'cod_tip_ope'=>'02',
                        ]);

                        
                         $mov_cal_stock = new Almacen();
                        $mov_cal_stock->movimiento_calcular_stock($rec->prod_insu,$cabecera->id_almacen);


                    }
    
                }

        }elseif($bus_pro->promocion =='3'){

            $bus_combo = DB::tABLE('combos')->where('IdProducto_comb',$id_prod)->get();

            if(count($bus_combo)>0){

                $bus_pro_combo = productos::findOrFail($det->IdProducto);

                if(empty($bus_pro_combo->pro_rel)){
                    $id_prod = $bus_pro_combo->IdProducto;
                }else{
                    $id_prod = $bus_pro_combo->pro_rel;
                }

                if($bus_pro_combo->promocion =='0'){

                    DB::tABLE('movimientos_productos')->insert([
                    'IdProducto'=>$det->IdProducto,
                    'IdProducto_rel'=>$id_prod,
                    'precio'=>$det->cdepuni,
                    'cantidad'=>$det->cdecan*$det->cpe_det_factor,
                    'costo'=>$det->costo,
                    'cliente'=>$cabecera->ccanom,
                    'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
                    'serie'=>$cabecera->serdoc,
                    'numero'=>$cabecera->numdoc,
                    'tdocod'=>$cabecera->tdocod,
                    'tipo'=>'2',
                    'mov_tip'=>'I',
                    'id_empresa_negocio'=>$cabecera->id_empresa_negocio,
                    'id_almacen'=>$cabecera->id_almacen,
                    'fecha_mov'=>$cabecera->ccafem,
                    'descripcion'=>'ANULACION',
                    'cod_tip_ope'=>'02',
                    ]);

                  
                         $mov_cal_stock = new Almacen();
                        $mov_cal_stock->movimiento_calcular_stock($id_prod,$cabecera->id_almacen);


                }elseif($bus_pro_combo->promocion =='2'){

                    $bus_receta = DB::TABLE('recetas')->where('prod_id',$id_prod)->get();

                    if(count($bus_receta)>0){
            
                        foreach($bus_receta as $rec){

                            DB::tABLE('movimientos_productos')->insert([
                            'IdProducto'=>$rec->prod_insu,
                            'IdProducto_rel'=>$rec->prod_insu,
                            'precio'=>'0',
                            'cantidad'=>$det->cdecan*$rec->rec_cant,
                            'costo'=>$rec->ins_costo,
                            'cliente'=>$cabecera->ccanom,
                            'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
                            'serie'=>$cabecera->serdoc,
                            'numero'=>$cabecera->numdoc,
                            'tdocod'=>$cabecera->tdocod,
                            'tipo'=>'2',
                            'mov_tip'=>'I',
                            'id_empresa_negocio'=>$cabecera->id_empresa_negocio,
                            'id_almacen'=>$cabecera->id_almacen,
                            'fecha_mov'=>$cabecera->ccafem,
                            'descripcion'=>'ANULACION',
                            'cod_tip_ope'=>'02',
                            ]);

                            $mov_cal_stock = new Almacen();
                            $mov_cal_stock->movimiento_calcular_stock($rec->prod_insu,$cabecera->id_almacen);


                            
                        }
                    }
                }
            }
        }
    }
    
    return 'Registrado';


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



public function detallepedido($venta){

    $cabecera = DB::tABLE('cpe_cabecera')
    ->join('tipo_documento','tipo_documento.tdocod','cpe_cabecera.tdocod')
    ->where('IdCpe_cabecera',$venta)->first();

    $detalle = DB::tABLE('cpe_cabecera')
    ->join('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
    ->where('cpe_cabecera.IdCpe_cabecera',$venta)
    ->get();

    return view('empresas.comprobantes.detallepedido',compact('cabecera','detalle'));

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

         // consultar tipos de monedas
    $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

    $categorias = DB::tABLE('categorias')
      //  ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
    ->orderby('cat_nom','asc')
    ->get();

    $clientes = DB::tABLE('cliente')->get();

    $comprobante = DB::tABLE('tipo_documento')->where('pedido','1')->get();

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



        $users = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        ->where('role_id','2')
        ->get();


        return view('empresas.puntosventas.pedidos',compact('categorias','comprobante','tipodocumento','igv','unidades','vendedores','creditos','mediospagos','clientes','documentos','datos','monedas','senudoc','motivos','modalidades','docidentidad','monedas','almacenes','unidades','users'));
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
    $procesos = $request->get('proceso');
    $clicod = $request->get('clicod');
    $tot_icbper = $request->get('tot_icbper');
    $icbper = $request->get('icbper');
    $mon_icbper = $request->get('mon_icbper');
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
    $descuentoGlobal = $request->get('descuento_global', 0);
    
    if ($descuentoGlobal > 0 && is_numeric($descuentoGlobal)) {
        $total = $total - ($total * ($descuentoGlobal / 100));
    }
    
    $fecemi = $request->get('fecEmi');
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
    $val_uni = $request->get('precio');
    $tdocod = $request->get('tdocod');
    $mon_cuo = $request->get('mon_cuo');
    $fec_cuo = $request->get('fec_cuo');
    $cot = $request->get('cot_id');
    $descuento = $request->get('desc');
    $topcod = '0101';
    $id_almacen_pro = $request->get('id_almacen_pro');

    $cont_carac = strlen($cliruc);
    $obt_dig = substr(trim($cliruc), 0, 2);

    if($total<='0'){
        return response()->json(['estado'=>'error','mensaje'=>'NO SE PUEDE REGISTRAR VENTA CON VALOR 0  ']);
    }

    if(Auth::User()->hasRole('vendedor')  ){
        foreach($proid as $index => $id) {
            $cal_stock = DB::TABLE('producto_stock')
              ->where('IdProducto',$id)
              ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
              ->where('id_almacen',$id_almacen)
              ->first();
            if($cal_stock->stock < $cantidades[$index]){
                return response()->json(['estado'=>'error','mensaje'=>'NO HAY STOCK PARA LA CANTIDAD INDICADA EN EL SIGUIENTE PRODUCTO  '.$detpro[$index]]);
            }
        }
    }

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

    if($mondoc !='PEN'){
        $camdoc = $request->get('camdoc');
        $tipcam = tipocambio::FirstOrCreate(['FecTipCambio'=>$fecemi],['CamCompra'=>$camdoc]);
    }else{
        $camdoc=0;
    }

    $oldDate = strtotime($request->get('clifecnac'));
    $mes = date('m',$oldDate);

    if(empty($tdicod)){
        $tdicod = '1';
    }

    if(empty(trim($cliruc))){
        $cliente = Cliente::Create(['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$request->get('clicor'),'tdicod'=>$tdicod,'telefono'=>$request->get('clitel'),'rucemp'=>Auth::user()->IdEmpresa]);
    }elseif($cliruc=='00000000' && ( trim(strtoupper($clinom))=='VENTAALPORTADOR' or trim(strtoupper($clinom)=='VARIOS'))){
        $cliente = Cliente::UpdateOrCreate(['clinum'=>$cliruc],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$request->get('clicor'),'tdicod'=>$tdicod,'telefono'=>$request->get('clitel'),'rucemp'=>Auth::user()->IdEmpresa]); 
    }else{
        $cliente = Cliente::UpdateOrCreate(['clinum'=>$cliruc],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$request->get('clicor'),'tdicod'=>$tdicod,'telefono'=>$request->get('clitel'),'rucemp'=>Auth::user()->IdEmpresa]); 
    }

    $buscarplaca = DB::tABLE('tipos_vehiculos')->where('placa',$request->get('placa'))->first();
    if(empty($buscarplaca)){
        $vehiculos = new tipos_vehiculos;
        $vehiculos->IdEmpresa = trim(Auth::user()->IdEmpresa);
        $vehiculos->clicod = $cliente->clicod;
        $vehiculos->observaciones = $request->get('observaciones');
        $vehiculos->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $vehiculos->save();
    }else{
        $vehiculos =  tipos_vehiculos::findOrFail($buscarplaca->id_tipo_vehiculo);
        $vehiculos->IdEmpresa = trim(Auth::user()->IdEmpresa);
        $vehiculos->clicod = $cliente->clicod;
        $vehiculos->observaciones = $request->get('observaciones');
        $vehiculos->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $vehiculos->update();
    }

    $cabecera = new cpe_cabecera;
    $cabecera->tdocod = $tdocod;
    $cabecera->topcod = $topcod;
    $cabecera->ccafem = $fecemi;
    $cabecera->observaciones = $request->get('observaciones');
    $cabecera->id_almacen = $id_almacen;
    $cabecera->tot_icbper = $tot_icbper;
    $cabecera->cod_tip_ope ='01';
    $cabecera->id_turno = Auth::user()->id_turno;

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
    //$cabecera->ped_ref = $request->get('ped_id');
    $cabecera->moncod = $mondoc;
    $cabecera->direccion = $clidir;
    $cabecera->clicorcli = $clicor;
    $cabecera->cre_dia_id = $estadopago;
    $cabecera->IdUsuario_ven = $id_vendedor;
    $cabecera->vendedor = $bus_ven->name.' '.$bus_ven->apeusu;
   
    if(!empty($bus_cot)){
        $cabecera->referencia = $bus_cot->serdoc.'-'.$bus_cot->numdoc;
    }

    $cabecera->tipcambio = $camdoc;

    if($sucursal->tip_igv_pred =='10'){
        $cabecera->ccatvg =  $total/1.105;
        $cabecera->ccaigv =  $total-$total/1.105;
    }

    if($sucursal->tip_igv_pred =='20'){
        $cabecera->ccatexo =  $total;
        $cabecera->ccaigv = '0.00';
    }
   
    $cabecera->ccatinaf =  '0.00';
    $cabecera->ccaitv = $total;
    $cabecera->id_empresa_negocio = $sucursal->id_empresa_negocio;
    $cabecera->clicod = $cliente->clicod;
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

    // =================================================================
    // INICIO: LÓGICA DE FIDELIZACIÓN (PUNTOS HOLA P) - PUNTO DE VENTA
    // =================================================================
    if($cliruc != '00000000' && strlen(trim($cliruc)) >= 8 && isset($cliente->clicod)) {
        $saldo_antes = $cliente->puntos ?? 0;
        
        $regla_base = DB::table('fidelizacion_configs')->where('activo', 1)->first();
        $valor_sol = $regla_base ? $regla_base->valor_sol : 1;
        
        $puntos_ganados = floor($total / $valor_sol);
        $puntos_gastados_total = 0;

        if($request->has('premios_canjeados')){
            foreach($request->get('premios_canjeados') as $id_regla){
                // ⚠️ CAMBIA 'id' POR EL NOMBRE DE TU COLUMNA REAL (ej. id_config)
                $regla_canje = DB::table('fidelizacion_configs')->where('id', $id_regla)->first(); 
                if($regla_canje){
                    $puntos_gastados_total += $regla_canje->puntos_minimos;
                    
                    DB::table('puntos_historial')->insert([
                        'cliente_id' => $cliente->clicod,
                        'venta_id' => $codfact,
                        'puntos_ganados' => 0,
                        'puntos_canjeados' => $regla_canje->puntos_minimos,
                        'saldo_antes' => 0, 
                        'saldo_despues' => 0, 
                        'motivo' => 'PREMIO: ' . strtoupper($regla_canje->premio),
                        'created_at' => \Carbon\Carbon::now(),
                        'updated_at' => \Carbon\Carbon::now()
                    ]);

                
                }
            }
        }

        if($puntos_ganados > 0){
             DB::table('puntos_historial')->insert([
                'cliente_id' => $cliente->clicod,
                'venta_id' => $codfact,
                'puntos_ganados' => $puntos_ganados,
                'puntos_canjeados' => 0,
                'saldo_antes' => 0,
                'saldo_despues' => 0,
                'motivo' => 'Consumo en Punto de Venta',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]);
        }

        $saldo_final = $saldo_antes + $puntos_ganados - $puntos_gastados_total;
        DB::table('cliente')->where('clicod', $cliente->clicod)->update(['puntos' => $saldo_final]);
    }
    // =================================================================
    // FIN: LÓGICA DE FIDELIZACIÓN
    // =================================================================

    if($buscre->cre_dia_tip=='CREDITO' || $buscre->cre_dia_tip=='PERSONALIZADO' ){
        if(!empty($mon_cuo)){
            $i=0;
            foreach ($mon_cuo as $key => $mc){
                $i=$i+1;
                DB::tABLE('ventas_cuotas')->insert([
                    'ven_cuo_num'=>$i,
                    'ven_cuo_fec_ven'=>$fec_cuo[$key],
                    'ven_cuo_mon'=>$mc,
                    'IdCpe_cabecera'=>$codfact
                ]);
            }
        }else{
            DB::tABLE('ventas_cuotas')->insert([
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
            // Lógica de movimientos bancarios original intacta...
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
            $detalle->comision = $codpro->comision;
            $detalle->cdecan = $cantidades[$index];
            $detalle->procod = $codproducto;
            $detalle->IdProducto = $codpro->IdProducto;
            $detalle->IdProducto_rel = $id_prod;
            
            $detalle->cdedes = $detpro[$index];
            $detalle->pronomobs = $pronomobs[$index];
            $detalle->costo = $codpro->costo;
            $detalle->tigcod = $codpro->tigcod;
            $detalle->cpe_det_stock = $stock;
            $detalle->desc_mon = $descuento[$index];
            $detalle->id_almacen_pro = $id_almacen;
            $detalle->icbper_det = $icbper[$index];
            $detalle->mon_icbper_det = $mon_icbper[$index];
           
            if($codpro->tigcod =='10'){
                $preciouni = $puni[$index]-$descuento[$index];
                $valoruni = ($puni[$index]/1.105)-($descuento[$index]/1.105);
                $valorunitario = $val_uni[$index]/1.105;
                $valorsubtotal = $vtot[$index]/1.105;
                $valortotal = $vtot[$index];
            }elseif($codpro->tigcod=='20'){
                $preciouni = $puni[$index]-$descuento[$index];
                $valoruni = $puni[$index]-$descuento[$index];
                $valorunitario = $val_uni[$index];
                $valorsubtotal = $vtot[$index];
                $valortotal = $vtot[$index];
            }

            if($sucursal->tipo_desc=='1'){
                $desc_mon = $descuento[$index];
                $desc_por = ($descuento[$index]*100)/$val_uni[$index];
            }elseif($sucursal->tipo_desc=='2'){
                $desc_por = $descuento[$index];
                $desc_mon = $val_uni[$index]*($descuento[$index]/100);
            }

            $valorigvtotal =  $valortotal-$valorsubtotal;
            $detalle->valor_unitario = $valorunitario;
            $detalle->por_des = $desc_por;
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
                    'IdProducto'=>$id,
                    'precio'=>$preciouni,
                    'cantidad'=>$cantidades[$index]*$codpro->factor,
                    'costo'=>$codpro->costo,
                    'cliente'=>$cabecera->ccanom,
                    'descripcion'=>'VENTA',
                    'cod_tip_ope'=>'01',
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
            
            $detalle->cdedes = $detpro[$index];
            $detalle->desc_mon = $descuento[$index];
            $detalle->tigcod = $sucursal->tip_igv_pred;
            $desc_mon='0';
            $desc_por ='0';
          
            if($sucursal->tip_igv_pred =='10'){
              $preciouni = $puni[$index];
              $valoruni = $puni[$index]/1.105;
              $valorunitario = $puni[$index]/1.105;
              $valorsubtotal = $vtot[$index]/1.105;
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
            $detalle->desc_mon = $descuento[$index];
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
       if ($request->get('opcion') == '0') {
            
            // 1. Buscamos el nombre de la impresora que tiene configurada esta caja
            $impresoras = DB::table('configuracion_impresoras')->where('Id', Auth::user()->terminal)->first();
            $nombre_impresora = !empty($impresoras->descripcion) ? $impresoras->descripcion : 'CAJA';

            // 2. Armamos la URL pública completa del PDF (ej: https://restobar.holape.app/pdf/2020202-01-F001.pdf)
            // Usamos la función url() de Laravel que detecta tu dominio automáticamente
            $url_pdf = url('pdf/' . $documento);

            // 3. Lo metemos a la cola de impresión de la base de datos
            DB::table('cola_impresion')->insert([
                'contenido'  => $url_pdf,
                'impresora'  => $nombre_impresora,
                'estado'     => '0'                
            ]);
        }
    }

    if($buscre->cre_dia_tip !='CONTADO'){
      self::registrarcuentascobrar($codfact);
    }
   
    if(!empty(trim($clicor))){
       $cabecera->enviar_comprobante_correo($codfact,$clicor);
    }

    if($empresa->tipo_envio =='1'){
        if($tdocod =='01' || $tdocod=='03'){
            $cabecera->enviar_sunat($codfact);
        }
    }

    if(!empty($procesos)){
        DB::tABLE('cpe_cabecera')
        ->where('IdCpe_cabecera',$codfact)
        ->update([
            'est_ope'=>'1',
            'fec_ini_proc'=>now()->format('Y-m-d h:i:s'),
        ]);

        foreach($procesos as $proc){
            DB::tABLE('procesos_comprobante')
            ->insert([
                'proc_id'=>$proc,
                'IdCpe_cabecera'=>$codfact
            ]);
        }
    }

    return response()->json(['estado'=>'success','codfact' =>$codfact,'tdocod'=>$tdocod,'mensaje'=>'Comprobante Emitido','numero'=>$modnumcomp]);
}

    public function vistaPvnuevo($codfact = 0)
{
    // Datos básicos de la empresa
    $datos = DB::table('empresa_negocios')
        ->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)
        ->first();

    // Datos para selects
    $almacenes = DB::table('almacenes')
        ->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)
        ->get();

    $creditos = DB::table('credito_dias')
        ->where('IdEmpresa', Auth::user()->IdEmpresa)
        ->get();

    $mediospagos = DB::table('medios_pagos')
        ->where('IdEmpresa', Auth::user()->IdEmpresa)
        ->get();

    $clientes = DB::table('cliente')
        ->orderBy('clinom', 'asc')
        ->get();

    $tipodocumento = DB::table('tipo_documento_identidad')
        ->where('tdiest', '=', 'Activo')
        ->orderBy('tdicod', 'asc')
        ->get();

    $vendedores = DB::table('users')
        ->join('role_user', 'role_user.user_IdUsuario', 'users.IdUsuario')
        ->get();

    $unidades = DB::table('unidad_medida')
        ->where('umeest', '=', 'Activo')
        ->orderBy('umecod', 'asc')
        ->get();

    $gastos = DB::table('tipo_gastos')->get();
    $ubigeos = DB::table('cat_ubigeo')->get();
    $procesos = DB::table('procesos')->get();

    // Si hay un comprobante recién generado
    $pdfData = null;
    if (!empty($codfact) && $datos->ticket_pantalla == '1' && $datos->formato == 'A4') {
        $pdfData = DB::table('cpe_cabecera')->where('IdCpe_cabecera', $codfact)->first();
    }

    return view('empresas.puntosventas.pvnuevo', compact(
        'datos', 'almacenes', 'creditos', 'mediospagos', 'clientes',
        'tipodocumento', 'vendedores', 'unidades', 'gastos', 'ubigeos',
        'procesos', 'codfact', 'pdfData'
    ));
}

    public function pvnuevo(Request $request)
{
    // Validación básica
    $request->validate([
        'total' => 'required|numeric|min:0',
        'clinom' => 'required|string',
    ]);

    // Obtener datos del request
    $rucemp = trim(Auth::user()->IdEmpresa);
    $empresa = Empresa::findOrFail($rucemp);
    
    $proid = $request->get('proid', []);
    $detpro = $request->get('pronom', []);
    $cantidades = $request->get('cant', []);
    $puni = $request->get('propun', []);
    $vtot = $request->get('itemtotal', []);
    $descuento = $request->get('desc', []);
    $unidades = $request->get('unid', []);
    $icbper = $request->get('icbper', []);
    $mon_icbper = $request->get('mon_icbper', []);
    $pronomobs = $request->get('pronomobs', []);
    $id_almacen_pro = $request->get('id_almacen_pro', []);

    $tdocod = $request->get('tdocod', '03');
    $mondoc = $request->get('moncod', 'PEN');
    $total = $request->get('total', 0);
    $descuentoGlobal = $request->get('descuento_global', 0);
    $observaciones = $request->get('observaciones', '');
    $estadopago = $request->get('estadopago');
    $cliruc = $request->get('clinum', '00000000');
    $clinom = $request->get('clinom', 'VENTA AL PORTADOR');
    $clidir = $request->get('clidir', '--');
    $clicor = $request->get('clicor', '');
    $clitel = $request->get('clitel', '');
    $tdicod = $request->get('tdicod', '1');
    $id_almacen = $request->get('id_almacen', 1);
    $id_vendedor = $request->get('vendedor', Auth::user()->IdUsuario);
    $fecemi = $request->get('fecEmi', now()->format('Y-m-d'));

    // Validar que haya productos
    if (empty($proid) || count($proid) == 0) {
        return response()->json(['estado' => 'error', 'mensaje' => 'Agregue al menos un producto']);
    }

    // Validar stock
    foreach ($proid as $index => $id) {
        if ($id != '0') {
            $stockProd = DB::table('producto_stock')
                ->where('IdProducto', $id)
                ->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)
                ->where('id_almacen', $id_almacen)
                ->first();

            if ($stockProd && $stockProd->stock < $cantidades[$index]) {
                return response()->json([
                    'estado' => 'error',
                    'mensaje' => "Stock insuficiente para: {$detpro[$index]}"
                ]);
            }
        }
    }

    // Obtener sucursal y almacén
    $sucursal = DB::table('empresa_negocios')
        ->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)
        ->first();

    // Obtener tipo de pago
    $buscre = DB::table('credito_dias')->where('cre_dia_id', $estadopago)->first();

    // Obtener vendedor
    $bus_ven = DB::table('users')->where('IdUsuario', $id_vendedor)->first();

    // Registrar cliente
    if (empty(trim($cliruc))) {
        $cliente = Cliente::create([
            'clinom' => $clinom,
            'clidir' => $clidir,
            'clicor' => $clicor,
            'tdicod' => $tdicod,
            'telefono' => $clitel,
            'rucemp' => Auth::user()->IdEmpresa
        ]);
    } else {
        $cliente = Cliente::updateOrCreate(
            ['clinum' => $cliruc],
            [
                'clinom' => $clinom,
                'clidir' => $clidir,
                'clicor' => $clicor,
                'tdicod' => $tdicod,
                'telefono' => $clitel,
                'rucemp' => Auth::user()->IdEmpresa
            ]
        );
    }

    // Generar número de comprobante
    $sercomp = '';
    $numcomp = 1;
    $empresanegocio = EmpresaNegocios::findOrFail($sucursal->id_empresa_negocio);

    if ($tdocod == '01') {
        $sercomp = $empresanegocio->FseEmpresa;
        $numcomp = $empresanegocio->FnuEmpresa + 1;
        $empresanegocio->FnuEmpresa = $numcomp;
    } elseif ($tdocod == '03') {
        $sercomp = $empresanegocio->BseEmpresa;
        $numcomp = $empresanegocio->BnuEmpresa + 1;
        $empresanegocio->BnuEmpresa = $numcomp;
    } elseif ($tdocod == '13') {
        $sercomp = $empresanegocio->SerNota;
        $numcomp = $empresanegocio->NumNota + 1;
        $empresanegocio->NumNota = $numcomp;
    } elseif ($tdocod == '15') {
        $sercomp = $empresanegocio->ProSer;
        $numcomp = $empresanegocio->ProNum + 1;
        $empresanegocio->ProNum = $numcomp;
    }
    $empresanegocio->update();

    $numdoc = str_pad($numcomp, 8, "0", STR_PAD_LEFT);

    // Crear cabecera
    $cabecera = new cpe_cabecera;
    $cabecera->tdocod = $tdocod;
    $cabecera->topcod = '0101';
    $cabecera->ccafem = $fecemi;
    $cabecera->observaciones = $observaciones;
    $cabecera->id_almacen = $id_almacen;
    $cabecera->cod_tip_ope = '01';
    $cabecera->id_turno = Auth::user()->id_turno ?? 1;

    // Fecha de vencimiento
    if ($buscre->cre_dia_tip == 'CONTADO' || $buscre->cre_dia_tip == 'PERSONALIZADO') {
        $cabecera->ccafve = $request->get('fecVen', $fecemi);
    } else {
        $cabecera->ccafve = date('Y-m-d', strtotime($fecemi . "+ " . $buscre->cre_dia_fac . " days"));
    }

    // Totales
    if ($buscre->cre_dia_tip == 'CONTADO') {
        $cabecera->totalcontado = $total;
        $cabecera->totalcredito = '0';
    } else {
        $cabecera->totalcredito = $total;
        $cabecera->totalcontado = '0';
    }

    $cabecera->ccaobs = $observaciones;
    $cabecera->tdicod = $tdicod;
    $cabecera->ccandi = $cliruc;
    $cabecera->ccanom = $clinom;
    $cabecera->moncod = $mondoc;
    $cabecera->direccion = $clidir;
    $cabecera->clicorcli = $clicor;
    $cabecera->cre_dia_id = $estadopago;
    $cabecera->IdUsuario_ven = $id_vendedor;
    $cabecera->vendedor = $bus_ven->name . ' ' . ($bus_ven->apeusu ?? '');
    $cabecera->serdoc = $sercomp;
    $cabecera->numdoc = $numdoc;
    $cabecera->ccaitv = $total;
    $cabecera->id_empresa_negocio = $sucursal->id_empresa_negocio;
    $cabecera->clicod = $cliente->clicod;
    $cabecera->paga = $request->get('pagar', 0);
    $cabecera->vuelto = $request->get('vuelto', 0);
    $cabecera->estadopago = $buscre->cre_dia_tip == 'CONTADO' ? 'CONTADO' : 'CREDITO';
    $cabecera->IdUsuario = Auth::user()->IdUsuario;
    $cabecera->IdEmpresa = $rucemp;
    $cabecera->save();

    $codfact = $cabecera->IdCpe_cabecera;

    // Registrar usuario facturación
    DB::table('usuario_facturacion')->insert([
        'IdCpe_cabecera' => $codfact,
        'id_turno' => Auth::user()->id_turno ?? 1,
        'id_empresa_negocio' => $sucursal->id_empresa_negocio,
        'IdEmpresa' => $rucemp,
        'referencia' => 'Registro POS Moderno',
        'created_at' => now(),
        'updated_at' => now()
    ]);

    // Registrar medios de pago
    $medios = $request->get('medio', []);
    $montos = $request->get('monto', []);

    foreach ($medios as $index => $mp) {
        if (!empty($mp) && isset($montos[$index]) && $montos[$index] > 0) {
            DB::table('venta_medio_pago')->insert([
                'id_turno' => Auth::user()->id_turno ?? 1,
                'IdCpe_cabecera' => $codfact,
                'id_med_pag' => $mp,
                'monto' => $montos[$index],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    // Registrar detalles
    foreach ($proid as $index => $id) {
        $codpro = productos::find($id);
        if (!$codpro) continue;

        $id_prod = $codpro->pro_rel ?? $codpro->IdProducto;
        $cantidad = $cantidades[$index] * ($codpro->factor ?? 1);

        // Actualizar stock
        if ($tdocod != '15') {
            $stockprod = DB::table('producto_stock')
                ->where('IdProducto', $id_prod)
                ->where('id_empresa_negocio', $sucursal->id_empresa_negocio)
                ->where('id_almacen', $id_almacen)
                ->first();

            if ($stockprod) {
                $newStock = $stockprod->stock - $cantidad;
                DB::table('producto_stock')
                    ->where('pro_sto_id', $stockprod->pro_sto_id)
                    ->update(['stock' => $newStock]);
                $stock = $newStock;
            } else {
                DB::table('producto_stock')->insert([
                    'stock' => -$cantidad,
                    'IdProducto' => $id_prod,
                    'id_empresa_negocio' => $sucursal->id_empresa_negocio,
                    'id_almacen' => $id_almacen
                ]);
                $stock = -$cantidad;
            }
        } else {
            $stock = 0;
        }

        // Calcular valores
        $preciouni = $puni[$index] - ($descuento[$index] ?? 0);
        if ($codpro->tigcod == '10') {
            $valoruni = $preciouni / 1.105;
            $valorsubtotal = $vtot[$index] / 1.105;
            $valortotal = $vtot[$index];
        } else {
            $valoruni = $preciouni;
            $valorsubtotal = $vtot[$index];
            $valortotal = $vtot[$index];
        }

        // Crear detalle
        $detalle = new cpe_detalle;
        $detalle->IdCpe_cabecera = $codfact;
        $detalle->umecod = $codpro->umecod;
        $detalle->cpe_det_factor = $codpro->factor ?? 1;
        $detalle->cdecan = $cantidades[$index];
        $detalle->procod = $codpro->procod;
        $detalle->IdProducto = $codpro->IdProducto;
        $detalle->IdProducto_rel = $id_prod;
        $detalle->cdedes = $detpro[$index];
        $detalle->pronomobs = $pronomobs[$index] ?? '';
        $detalle->costo = $codpro->costo ?? 0;
        $detalle->tigcod = $codpro->tigcod;
        $detalle->cpe_det_stock = $stock;
        $detalle->desc_mon = $descuento[$index] ?? 0;
        $detalle->id_almacen_pro = $id_almacen_pro[$index] ?? $id_almacen;
        $detalle->icbper_det = $icbper[$index] ?? 0;
        $detalle->mon_icbper_det = $mon_icbper[$index] ?? 0;
        $detalle->valor_unitario = $valoruni;
        $detalle->por_des = ($descuento[$index] ?? 0) > 0 ? ($descuento[$index] / $preciouni) * 100 : 0;
        $detalle->cdepuni = $preciouni;
        $detalle->cdevun = $valoruni;
        $detalle->cdevve = $valortotal;
        $detalle->cdepve = $valorsubtotal;
        $detalle->cdeigv = $valortotal - $valorsubtotal;
        $detalle->fecha_venta = $fecemi;
        $detalle->save();

        // Registrar movimiento
        DB::table('movimientos')->insert([
            'mov_fec' => $fecemi,
            'mov_tip' => 'E',
            'mov_mot' => 'Venta',
            'cantidad' => $cantidades[$index],
            'unidad' => $codpro->umecod,
            'comprobante' => $sercomp . '-' . $numdoc,
            'IdEmpresa' => $rucemp,
            'id_empresa_negocio' => $sucursal->id_empresa_negocio,
            'IdProducto' => $codpro->IdProducto,
            'observacion' => "Venta desde POS Moderno",
            'IdUsuario' => Auth::user()->IdUsuario,
            'IdCpe_cabecera' => $codfact,
            'stockmov' => $stock,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    // Generar PDF
    $cabecera->generar_nuevo_qr($codfact);
    $documento = $cabecera->generarpdfgeneral($codfact);

    // Enviar a Sunat si es necesario
    if ($empresa->tipo_envio == '1' && ($tdocod == '01' || $tdocod == '03')) {
        $cabecera->enviar_sunat($codfact);
    }

    // Comisión por venta
    $comisionTotal = $request->get('comision', 0);
    if ($comisionTotal > 0) {
        DB::table('cpe_cabecera')
            ->where('IdCpe_cabecera', $codfact)
            ->update(['comision_venta' => $comisionTotal]);
    }

    return response()->json([
        'estado' => 'success',
        'codfact' => $codfact,
        'tdocod' => $tdocod,
        'mensaje' => 'Comprobante emitido correctamente',
        'numero' => $numcomp,
        'pdf_url' => asset('pdf/' . $documento)
    ]);
}

    public function vistaVentaMasiva()
    {
        $rucemp = trim(Auth::user()->IdEmpresa);
        
        // Filtramos solo los clientes de esta empresa que tienen mensual = 1
        $clientes = DB::table('cliente')
            ->where('rucemp', $rucemp)
            ->where('mensual', 1)
            ->get();

        return view('empresas.puntosventas.ventamasiva', compact('clientes'));
    }

    public function procesarVentaMasiva(Request $request)
    {
        // Evitamos Timeout y límite de memoria
        set_time_limit(0); 
        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '-1');

        $clientes_ids = $request->input('clientes_seleccionados', []);

        if (empty($clientes_ids)) {
            return back()->with('error', 'No seleccionaste ningún cliente para emitir.');
        }

        // --- NUEVO: CAPTURAMOS LA FECHA Y LA DESCRIPCIÓN ---
        // Si por alguna razón llegan vacíos, les ponemos valores por defecto
        $fecha_emision = $request->input('fecha_emision', date('Y-m-d'));
        $descripcion_general = $request->input('descripcion_general', 'SISTEMA DE FACTURACION ELECTRONICA');

        $rucemp = trim(Auth::user()->IdEmpresa);
        $id_turno = Auth::user()->id_turno ?? 0;
        $id_usuario = Auth::user()->IdUsuario;
        $id_empresa_negocio = Auth::user()->id_empresa_negocio;

        $clientes = DB::table('cliente')
            ->whereIn('clicod', $clientes_ids)
            ->get();

        // 1. Preparamos el ZIP (resto de tu código igual...)
        $zip = new \ZipArchive();
        $nombreZip = 'Emision_Masiva_' . date('Ymd_His') . '.zip';
        $rutaZip = public_path('pdf/' . $nombreZip);

        if ($zip->open($rutaZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
            return back()->with('error', 'No se pudo crear el archivo ZIP para las descargas.');
        }

        // 2. Preparamos la cabecera del Excel
        $resultadosCsv = "Cliente,DNI_RUC,Tipo_Comprobante,Serie,Numero,Total\n";

        foreach ($clientes as $cli) {
            if ($cli->monto <= 0) continue;

            $tdocod = $cli->comprobante; // 01 o 13
            $sucursal = DB::table('empresa_negocios')->where('id_empresa_negocio', $id_empresa_negocio)->first();

            $numcomp = 0;
            $sercomp = '';

            if ($tdocod == '01') {
                $numcomp = $sucursal->FnuEmpresa + 1;
                $sercomp = $sucursal->FseEmpresa;
                DB::table('empresa_negocios')->where('id_empresa_negocio', $id_empresa_negocio)->update(['FnuEmpresa' => $numcomp]);
            } elseif ($tdocod == '13') {
                $numcomp = $sucursal->NumNota + 1;
                $sercomp = $sucursal->SerNota;
                DB::table('empresa_negocios')->where('id_empresa_negocio', $id_empresa_negocio)->update(['NumNota' => $numcomp]);
            }

            $numdoc_str = str_pad($numcomp, 8, "0", STR_PAD_LEFT);
            
            // --- LÓGICA DE IGV (GRAVADO VS EXONERADO) ---
            $tip_igv = $sucursal->tip_igv_pred; 
            $total = $cli->monto;

            if ($tip_igv == '20') {
                $subtotal = $total;
                $igv = 0;
            } else {
                $subtotal = $total / 1.18; 
                $igv = $total - $subtotal;
            }

            // Guardar Cabecera
            $cabecera = new cpe_cabecera;
            $cabecera->tdocod = $tdocod;
            $cabecera->topcod = '0101';
            
            // --- REEMPLAZAMOS EL now() POR LA FECHA DEL FORMULARIO ---
            $cabecera->ccafem = $fecha_emision;
            $cabecera->ccafve = $fecha_emision; 
            
            $cabecera->tdicod = $cli->tdicod;
            $cabecera->ccandi = $cli->clinum;
            $cabecera->ccanom = $cli->clinom;
            $cabecera->direccion = $cli->clidir;
            $cabecera->moncod = 'PEN';
            
            if ($tip_igv == '20') {
                $cabecera->ccatexo = $total;
                $cabecera->ccaigv = '0.00';
            } else {
                $cabecera->ccatvg = $subtotal;
                $cabecera->ccaigv = $igv;
            }

            $cabecera->ccaitv = $total;
            $cabecera->totalcontado = $total;
            $cabecera->totalcredito = '0';
            $cabecera->estadopago = 'CONTADO';
            $cabecera->serdoc = $sercomp;
            $cabecera->numdoc = $numdoc_str;
            $cabecera->IdUsuario = $id_usuario;
            $cabecera->IdEmpresa = $rucemp;
            $cabecera->id_empresa_negocio = $id_empresa_negocio;
            $cabecera->clicod = $cli->clicod;
            $cabecera->id_turno = $id_turno;
            $cabecera->estado = 'ACEPTADO';
            $cabecera->save();

            $codfact = $cabecera->IdCpe_cabecera;

            // Guardar Detalle
            $detalle = new cpe_detalle; 
            $detalle->IdCpe_cabecera = $codfact;
            $detalle->umecod = 'ZZ';
            $detalle->cdecan = 1;
            
            // --- REEMPLAZAMOS EL TEXTO FIJO POR LA DESCRIPCIÓN DEL FORMULARIO ---
            $detalle->cdedes = $descripcion_general;
            
            $detalle->valor_unitario = $subtotal;
            $detalle->cdepuni = $total;
            $detalle->cdevun = $subtotal;
            $detalle->cdevve = $total;
            $detalle->cdepve = $subtotal;
            $detalle->cdeigv = $igv;
            
            // --- REEMPLAZAMOS EL now() POR LA FECHA DEL FORMULARIO ---
            $detalle->fecha_venta = $fecha_emision; 
            
            $detalle->tigcod = $tip_igv; 
            $detalle->save();

            // Direccionar a XML y PDF (El resto queda exactamente igual)
            if ($tdocod == '01' || $tdocod == '03') {
                $cabecera->generar_xml_boleta_factura($codfact);
            }
            $cabecera->generar_nuevo_qr($codfact);
            $documento_pdf = $cabecera->generarpdfgeneral($codfact); 

            $rutaDocumentoReal = public_path('pdf/' . $documento_pdf);
            if (file_exists($rutaDocumentoReal)) {
                $zip->addFile($rutaDocumentoReal, $documento_pdf);
            }

            $tipoStr = ($tdocod == '01') ? 'FACTURA' : 'NOTA DE VENTA';
            $resultadosCsv .= "\"{$cli->clinom}\",\"{$cli->clinum}\",\"{$tipoStr}\",\"{$sercomp}\",\"{$numdoc_str}\",\"{$total}\"\n";
        }

        $zip->addFromString('Reporte_General_Masivo.csv', $resultadosCsv);
        $zip->close();

        return response()->download($rutaZip)->deleteFileAfterSend(true);
    }



public function cambiar_comprobante(Request $request)
{




    $rucemp = trim(Auth::user()->IdEmpresa);
    $empresa = Empresa::findOrFail($rucemp);

    $procesos = $request->get('proceso');

      
        $bus_doc_cambio = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$request->get('cab_id'))->first();

        DB::tABLE('movimientos_productos')->where('IdCpe_cabecera',$request->get('cab_id'))->delete();

        $clicod = $request->get('clicod');

        $tot_icbper = $request->get('tot_icbper');
        $icbper = $request->get('icbper');
        $mon_icbper = $request->get('mon_icbper');

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
        $val_uni = $request->get('precio');
        $tdocod = $request->get('tdocod');
        
        //$mon_cuo = $request->get('mon_cuo');
        //$fec_cuo = $request->get('fec_cuo');
        //$cot = $request->get('cot_id');
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

    $cliente = Cliente::Create(['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$request->get('clicor'),'tdicod'=>$tdicod,'telefono'=>$request->get('clitel'),'rucemp'=>Auth::user()->IdEmpresa]);

}elseif($cliruc=='00000000' && ( trim(strtoupper($clinom))=='VENTAALPORTADOR' or trim(strtoupper($clinom)=='VARIOS'))){

    $cliente = Cliente::UpdateOrCreate(['clinum'=>$cliruc],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$request->get('clicor'),'tdicod'=>$tdicod,'telefono'=>$request->get('clitel'),'rucemp'=>Auth::user()->IdEmpresa]); 

}else{

    $cliente = Cliente::UpdateOrCreate(['clinum'=>$cliruc],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$request->get('clicor'),'tdicod'=>$tdicod,'telefono'=>$request->get('clitel'),'rucemp'=>Auth::user()->IdEmpresa]); 

}




$buscarplaca = DB::tABLE('tipos_vehiculos')->where('placa',$request->get('placa'))->first();


        //BUSCAR PLACA

if(empty($buscarplaca)){


  $vehiculos = new tipos_vehiculos;
  $vehiculos->IdEmpresa = trim(Auth::user()->IdEmpresa);
  $vehiculos->clicod = $cliente->clicod;
  $vehiculos->observaciones = $request->get('observaciones');
  $vehiculos->id_empresa_negocio = Auth::user()->id_empresa_negocio;
  $vehiculos->save();

}else{


    $vehiculos =  tipos_vehiculos::findOrFail($buscarplaca->id_tipo_vehiculo);
    $vehiculos->IdEmpresa = trim(Auth::user()->IdEmpresa);
    $vehiculos->clicod = $cliente->clicod;
    $vehiculos->observaciones = $request->get('observaciones');
    $vehiculos->id_empresa_negocio = Auth::user()->id_empresa_negocio;
    $vehiculos->update();

}


$cabecera = new cpe_cabecera;
$cabecera->tdocod = $tdocod;
$cabecera->topcod = $topcod;
$cabecera->ccafem = $fecemi;
$cabecera->observaciones = $request->get('observaciones');
$cabecera->placa = $request->get('placa_comp');
$cabecera->guia_remision = $request->get('guia_remision');
$cabecera->IdCpe_guia_ref = $request->get('IdCpe_guia');
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
$cabecera->tot_icbper = $tot_icbper;
$cabecera->cod_tip_ope ='01';
$cabecera->est_cambio = '1';
$cabecera->doc_cambio = $request->get('cab_id');
$cabecera->doc_cambio_des = $bus_doc_cambio->serdoc.'-'.$bus_doc_cambio->numdoc;

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
//$cabecera->ped_ref = $request->get('ped_id');
$cabecera->moncod = $mondoc;
$cabecera->direccion = $clidir;

$cabecera->clicorcli = $clicor;
$cabecera->cre_dia_id = $estadopago;
$cabecera->IdUsuario_ven = $id_vendedor;
$cabecera->vendedor = $bus_ven->name.' '.$bus_ven->apeusu;

if(!empty($bus_cot)){
  $cabecera->referencia = $bus_cot->serdoc.'-'.$bus_cot->numdoc;
}

$cabecera->tipcambio = $camdoc;

if($sucursal->tip_igv_pred =='10'){
    $cabecera->ccatvg =  $total/1.105;
    $cabecera->ccaigv =  $total-$total/1.105;
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
                //  $movimiento->importe = $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)*1.105));

       $movimiento->importe = $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)));

   }

   $movimiento->estado = '1';
   $movimiento->mov_fecha = $cabecera->ccafem;
   $movimiento->clicod = $cabecera->clicod;
   $movimiento->registro = 'Registrado';

   if($contar==0){
                 // $totalsaldo =  $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)*1.105));

       $totalsaldo =  $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)));

   }else{
                //  $totalsaldo = $buscar->last()->saldo + $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)*1.105));
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

    


$detalle = new cpe_detalle;
$detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
$detalle->umecod = $codpro->umecod;
$detalle->cpe_det_factor = $codpro->factor;
$detalle->comision = $sucursal->comision;
$detalle->cdecan = $cantidades[$index];
$detalle->procod = $codproducto;
$detalle->IdProducto = $codpro->IdProducto;
$detalle->IdProducto_rel = $id_prod;

$detalle->cdedes = $detpro[$index];
$detalle->pronomobs = $pronomobs[$index];
$detalle->costo = $codpro->costofijo;
$detalle->tigcod = $codpro->tigcod;
$detalle->desc_mon = $cantidades[$index]*$descuento[$index];
$detalle->id_almacen_pro = $id_almacen;
$detalle->icbper_det = $icbper[$index];
$detalle->mon_icbper_det = $mon_icbper[$index];


/*calcular porcentaje de descuento*/


if($codpro->tigcod =='10'){

  $preciouni = $puni[$index]-$descuento[$index];;
  $valoruni = ($puni[$index]/1.105)-($descuento[$index]/1.105);
  $valorunitario = $val_uni[$index]/1.105;

  $valorsubtotal = $vtot[$index]/1.105;
  $valortotal = $vtot[$index];

}elseif($codpro->tigcod=='20'){

  $preciouni = $puni[$index]-$descuento[$index];
  $valoruni = $puni[$index]-$descuento[$index];
  $valorunitario = $val_uni[$index];

  $valorsubtotal = $vtot[$index];
  $valortotal = $vtot[$index];
}

if($sucursal->tipo_desc=='1'){
    $desc_mon = $descuento[$index];
    $desc_por = ($descuento[$index]*100)/$val_uni[$index];
}elseif($sucursal->tipo_desc=='2'){
    $desc_por = $descuento[$index];
    $desc_mon = $val_uni[$index]*($descuento[$index]/100);
}


        $valorigvtotal =  $valortotal-$valorsubtotal;



        /*FIN CALCULAR DESCUENTO*/
        $detalle->valor_unitario = $valorunitario;
        $detalle->por_des = $desc_por;
                   // $detalle->desc_mon = $desc_mon;
        $detalle->cdepuni = $preciouni;
        $detalle->cdevun = $valoruni;
        $detalle->cdevve = $valortotal;
        $detalle->cdepve = $valorsubtotal;
        $detalle->cdeigv = $valorigvtotal;
        $detalle->fecha_venta = $fecemi;
        $detalle->flete = $codpro->flete;
        $detalle->save();



               DB::tABLE('movimientos_productos')->insert([
                'IdProducto'=>$id,
                'precio'=>$preciouni,
                'cantidad'=>$cantidades[$index]*$codpro->factor,
                'costo'=>$codpro->costofijo,
                'cliente'=>$cabecera->ccanom,
                'descripcion'=>'VENTA',
                'cod_tip_ope'=>'01',
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




}else{


    $detalle = new cpe_detalle;
    $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
    $detalle->umecod = $unidades[$index];
    $detalle->cdecan = $cantidades[$index];
    
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
      $valoruni = $puni[$index]/1.105;
      $valorunitario = $puni[$index]/1.105;

      $valorsubtotal = $vtot[$index]/1.105;
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
  exec("c:\WINDOWS\system32\cmd.exe /c START powershell.exe -ExecutionPolicy Bypass Start-Process -FilePath ".public_path()."/pdf/".$documento." -Verb Print");
}

}



if($buscre->cre_dia_tip !='CONTADO'){
  self::registrarcuentascobrar($codfact);
}

if(!empty(trim($clicor))){
 $cabecera->enviar_comprobante_correo($codfact,$clicor);
}

if($empresa->tipo_envio =='1'){
 if($tdocod =='01' || $tdocod=='03'){
    $cabecera->enviar_sunat($codfact);
}
}


        // REGISTR DE PROCESOS


if(!empty($procesos)){

   DB::tABLE('cpe_cabecera')
   ->where('IdCpe_cabecera',$codfact)
   ->update([
    'est_ope'=>'1',
    'fec_ini_proc'=>now()->format('Y-m-d h:i:s'),

]);


   foreach($procesos as $proc){

    DB::tABLE('procesos_comprobante')
    ->insert([
        'proc_id'=>$proc,
        'IdCpe_cabecera'=>$codfact
    ]);
}




}

        //FIN REGISTRO DE PROCESOS

return response()->json(['estado'=>'success','codfact' =>$codfact,'tdocod'=>$tdocod,'mensaje'=>'Comprobante Emitido','numero'=>$modnumcomp]);


}


    //FUNCION PARA PEDIDOS EN NEGOCIOS

public function registrarpedido(Request $request)
{

    DB::beginTransaction();

    try{


    $detpro = $request->get('pronom');
    $proid = $request->get('proid');
    $puni = $request->get('propun');
    $puniref = $request->get('propunref');
    $cantidades = $request->get('cant');
    $fechaactual = now()->format('Y-m-d');
    $total = $request->get('total');

    $busfecha = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

    $dat_suc = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();


    $almacen = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();

    if($fechaactual != $busfecha->fecha_pedidos){
         DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->update(['fecha_pedidos'=>$fechaactual,'numNP'=>'0']);
    }


 if(Auth::User()->hasRole('caja') ||  Auth::User()->hasRole('vendedor') ){



    foreach($proid as $index => $id) {

        if($id<>'0'){

                   //$con_pro = DB::tABLE('producto_empresa')->where('IdProducto',$id)->first();
            $cal_stock = DB::tABLE('producto_stock')
            ->where('IdProducto',$id)
            ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
            ->where('id_almacen',$almacen->id_almacen)
            ->first();

            if($puni[$index] < $puniref[$index]){

                return response()->json(['estado'=>'error','mensaje'=>'NO ESTA PERMITIDO EL DESCUENTO - PRODUCTO: '.$detpro[$index].' PRECIO: '.$puniref[$index]]);


            }

            if($dat_suc->ven_sin_sto=='0'){

              if($cal_stock->stock < $cantidades[$index]){

                return response()->json(['estado'=>'error','mensaje'=>'NO HAY STOCK PARA LA CANTIDAD INDICADA EN EL SIGUIENTE PRODUCTO  '.$detpro[$index]]);

                
            }

        }



    }


}




}



if($total<='0'){

    return response()->json(['estado'=>'error','mensaje'=>'NO SE PUEDE REGISTRAR VENTA CON VALOR 0  ']);

}

$tip_cam = DB::tABLE('tipocambio')->where('FecTipCambio',now()->format('Y-m-d'))->first();



       //Datos de cabecera
$tdicod = $request->get('tdicod');
$tdocod = '16';
$tdocod1 = $request->get('tdocod');

$mondoc = $request->get('moncod');

$fecemi = $request->get('fecEmi');
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


    if($p<>'0'){

      $codpro = productos::findOrFail($p);
      $total = $total + ($cantidades[$index]*$puni[$index]);

      if($codpro->icbper=='1'){
          $totalicbper = $totalicbper + ($cantidades[$index]*$empresa->icbper);
      }


      if($codpro->tigcod =='10'){


          $totalafec = ($total / 1.105);
          $totaligv = $total-$totalafec;


      }elseif($codpro->tigcod =='20'){

          $totalexo = $total;
          $totaligv =  $total-$totalexo;

      }elseif($codpro->tigcod =='30'){

          $totalinaf = $total;
          $totaligv =  $total-$totalinaf;

      }

  }else{

   $total = $total + ($cantidades[$index]*$puni[$index]);

   $totalafec = ($total / 1.105);
   $totaligv = $total-$totalafec; 
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

    if($id<>'0'){

      $codpro = productos::findOrFail($id);
      $codproducto = $codpro->procod;

      $detalle = new cpe_detalle;
      $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
      $detalle->umecod = $codpro->umecod;
      $detalle->cdecan = $cantidades[$index];

      $detalle->procod = $codproducto;
      $detalle->IdProducto = $codpro->IdProducto;

      
      $detalle->cdedes = $detpro[$index];
      $detalle->pronomobs = $pronomobs[$index];
      $detalle->costo = $codpro->costofijo;
      $detalle->tigcod = $codpro->tigcod;
      $detalle->icbper = $codpro->icbper;

      if($codpro->tigcod =='10'){
          $preciouni = $puni[$index];
          $valoruni = $puni[$index]/1.105;
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
      $detalle->precio_ref = $puniref[$index];
      $detalle->save();

  }else{



    $detalle = new cpe_detalle;
    $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
    $detalle->umecod = $unidades[$index];
    $detalle->cdecan = $cantidades[$index];



    
    $detalle->cdedes = $detpro[$index];
    $detalle->pronomobs = $pronomobs[$index];
    $detalle->costo ='0';
    $detalle->tigcod = '10';
    $detalle->icbper = '0';

           // if($codpro->tigcod =='10'){
    $preciouni = $puni[$index];
    $valoruni = $puni[$index]/1.105;
          //  }elseif($codpro->tigcod=='20'){
            //  $preciouni = $puni[$index];
           //   $valoruni = $puni[$index];
           // }
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
    $detalle->precio_ref = $puniref[$index];
    $detalle->save();

}




}

    
    DB::commit();

    return response()->json(['pedido'=>$modnumcomp,'estado'=>'success','codfact' =>$codfact,'tdocod'=>$tdocod,'mensaje'=>'Comprobante Emitido']);

    
} catch (\Exception $e) {

    DB::rollback();

    //return response()->json(['estado'=>'error','mensaje'=>$e->getMessage()]);
     return response()->json(['estado'=>'error','mensaje'=>'REVISAR QUE TODOS LOS DATOS SEAN CORRECTOS']);

}




}

public function actualizarpedido(Request $request)
{

    $proid = $request->get('proid');
    $puni = $request->get('propun');
    $puniref = $request->get('propunref');
    $descuento = $request->get('desc');
    $detpro = $request->get('pronom');
          //DETALLE 
        $unidades = $request->get('unid');

        $detpro = $request->get('pronom');
        $vunit = $request->get('provun');

        $vtot = $request->get('itemtotal');
        $cantidades = $request->get('cant');
        $pronomobs = $request->geT('pronomobs');
    $dat_suc = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
    $almacen = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();

 
      if(Auth::User()->hasRole('caja') ||  Auth::User()->hasRole('vendedor') ){

            foreach($proid as $index => $id) {

        if($id<>'0'){

                   //$con_pro = DB::tABLE('producto_empresa')->where('IdProducto',$id)->first();
            $cal_stock = DB::tABLE('producto_stock')
            ->where('IdProducto',$id)
            ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
            ->where('id_almacen',$almacen->id_almacen)
            ->first();

            if($puni[$index] < $puniref[$index]){

                return response()->json(['estado'=>'error','mensaje'=>'NO ESTA PERMITIDO EL DESCUENTO - PRODUCTO: '.$detpro[$index].' PRECIO: '.$puniref[$index]]);


            }

            if($dat_suc->ven_sin_sto=='0'){

              if($cal_stock->stock < $cantidades[$index]){

                return response()->json(['estado'=>'error','mensaje'=>'NO HAY STOCK PARA LA CANTIDAD INDICADA EN EL SIGUIENTE PRODUCTO  '.$detpro[$index]]);

                
            }

        }



    }


}
    }









        $tip_cam = DB::tABLE('tipocambio')->where('FecTipCambio',now()->format('Y-m-d'))->first();

        $almacen = DB::tABLE('almacenes')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('predeterminado','1')->first();

       //Datos de cabecera
        $tdicod = $request->get('tdicod');
        $tdocod = '16';
        $tdocod1 = $request->get('tdocod_1');
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



      //Datos del Cliente
        $cliruc = $request->get('clinum');
        $fecha = $request->get('fecEmi');
        $clinom = $request->get('clinom');
        $clidir = $request->get('clidir');
        $clicor = $request->get('clicor');


        $rucemp = trim(Auth::user()->IdEmpresa);
        $empresa = Empresa::findOrFail($rucemp);
  

        $total =0;
        $totalexo = 0;
        $totalafec = 0;
        $totalinaf =0;
        $totaligv =0;
        $totalsubt = 0;
        $totalicbper =0;

        foreach($proid as $index => $p ) {

            if($p<>'0'){

                $codpro = productos::findOrFail($p);

                $total = $total + ($cantidades[$index]*($puni[$index]-$descuento[$index]));

                if($codpro->icbper=='1'){
                  $totalicbper = $totalicbper + ($cantidades[$index]*$empresa->icbper);
              }


              if($codpro->tigcod =='10'){


                  $totalafec = ($total / 1.105);
                  $totaligv = $total-$totalafec;


              }elseif($codpro->tigcod =='20'){

                  $totalexo = $total;
                  $totaligv =  $total-$totalexo;

              }elseif($codpro->tigcod =='30'){

                  $totalinaf = $total;
                  $totaligv =  $total-$totalinaf;

              }

          }else{

              $total = $total + ($cantidades[$index]*($puni[$index]-$descuento[$index]));

              $totalafec = ($total / 1.105);
              $totaligv = $total-$totalafec;


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
      
      if(empty($iddetalle)){
         DB::tABLE('cpe_detalle')->where('IdCpe_cabecera',$request->get('ped_id'))->delete();
      }else{
        foreach ($registros as $reg){
            if(!in_array($reg->IdCpe_detalle,$iddetalle)){
              DB::tABLE('cpe_detalle')->where('IdCpe_detalle',$reg->IdCpe_detalle)->delete();
            }
          }
      }
     


  foreach($proid as $index => $id) {

    if($id<>'0'){

      $codpro = productos::findOrFail($id);
      $codproducto = $codpro->procod;

      if(!empty($iddetalle[$index])){
        $detalle = cpe_detalle::findOrFail($iddetalle[$index]);
        $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;

        $detalle->umecod = $codpro->umecod;
        $detalle->cdecan = $cantidades[$index];
        $detalle->procod = $codproducto;
        $detalle->IdProducto = $codpro->IdProducto;
        
        $detalle->cdedes = $detpro[$index];
        $detalle->pronomobs = $pronomobs[$index];
        $detalle->costo = $codpro->costofijo;
        $detalle->tigcod = $codpro->tigcod;
        $detalle->icbper = $codpro->icbper;
        $detalle->desc_mon  = $descuento[$index];
        if($codpro->tigcod =='10'){
          $preciouni = $puni[$index];
          $valoruni = $puni[$index]/1.105;
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
      
      $detalle->cdedes = $detpro[$index];
      $detalle->pronomobs = $pronomobs[$index];
      $detalle->costo = $codpro->costofijo;
      $detalle->tigcod = $codpro->tigcod;
      $detalle->icbper = $codpro->icbper;

      if($codpro->tigcod =='10'){
          $preciouni = $puni[$index];
          $valoruni = $puni[$index]/1.105;
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

}else{



    if(!empty($iddetalle[$index])){
        $detalle = cpe_detalle::findOrFail($iddetalle[$index]);
        $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;

        $detalle->umecod = $unidades[$index];
        $detalle->cdecan = $cantidades[$index];

        
        $detalle->cdedes = $detpro[$index];
        $detalle->pronomobs = $pronomobs[$index];
        $detalle->costo = '0';
        $detalle->tigcod = '10';
        $detalle->icbper = '0';
        $detalle->desc_mon  = $descuento[$index];
          //  if($codpro->tigcod =='10'){
        $preciouni = $puni[$index];
        $valoruni = $puni[$index]/1.105;
          //  }elseif($codpro->tigcod=='20'){
          //    $preciouni = $puni[$index];
          //    $valoruni = $puni[$index];
          //  }
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

      $detalle->umecod = $unidades[$index];
      $detalle->cdecan = $cantidades[$index];
         //   $detalle->procod = $codproducto;
         //   $detalle->IdProducto = $codpro->IdProducto;
      
      $detalle->cdedes = $detpro[$index];
      $detalle->pronomobs = $pronomobs[$index];
      $detalle->costo = '0';
      $detalle->tigcod = '10';
      $detalle->icbper = '0';

          //  if($codpro->tigcod =='10'){
      $preciouni = $puni[$index];
      $valoruni = $puni[$index]/1.105;
          //  }elseif($codpro->tigcod=='20'){
           //   $preciouni = $puni[$index];
          //    $valoruni = $puni[$index];
          //  }
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

        if($cabecera->facturado =='2'){


           return  Redirect::to('/pos')->withErrors(['mensaje' => 'EL PEDIDO SE ENCUENTRA ANULADO']);

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

 
  $vendedores = DB::tABLE('users')
  ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        //->where('role_id','5')
  ->get();

  $creditos = DB::tABLE('credito_dias')->get();

  $documentos = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderby('tdicod')->get();

  $docidentidad= DB::tABLE('tipo_documento_identidad')->get();


  $comprobante = DB::tABLE('tipo_documento')->where('caja','1')->get();

   $comprobantes = DB::tABLE('tipo_documento')->get();


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

   $sucursal = DB::tABLE('empresa_negocios')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();

    $bus_alm = new Almacen;
    $almacen = $bus_alm->buscar_almacen_predeterminado($sucursal->first()->id_empresa_negocio);

  $marcas = DB::tABLE('marcas')->get();

  $modelos = DB::tABLE('modelos')->get();

  $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

  $users = DB::tABLE('users')
  ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
  ->where('role_id','2')
  ->get();

  $ubigeos = DB::tABLE('cat_ubigeo')->get();


  return view('empresas.puntosventas.cobrar_pedido',compact('comprobantes','users','senudoc','motivos','modalidades','mediospagos','creditos','documentos','cabecera','detalle','datos','vendedores','clientes','mediospagos','unidades','empresa','gastos','docidentidad','tipodocumento','marcas','modelos','monedas','comprobante','ubigeos','sucursal','almacen'));




}

public function buscar_comprobante($comprobante){



    $ser = substr($comprobante,0,4);
    $num = trim(substr($comprobante,4,8));



    $cabecera= DB::tABLE('cpe_cabecera')
    ->where('numdoc',$num)
    ->where('serdoc',$ser)
    ->first();

 




  if(empty($cabecera)){

    return  Redirect::to('/pos')->withErrors(['mensaje' => 'NO SE ENCONTRÓ EL DOCUMENTO']);

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
        $comprobantes = DB::tABLE('tipo_documento')->get();

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

        $users = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        ->where('role_id','2')
        ->get();

        $ubigeos = DB::tABLE('cat_ubigeo')->get();

        return view('empresas.puntosventas.mostrarcomprobante',compact('comprobantes','users','senudoc','motivos','modalidades','mediospagos','creditos','documentos','almacen','cabecera','detalle','datos','almacenes','vendedores','clientes','mediospagos','unidades','empresa','gastos','docidentidad','tipodocumento','marcas','modelos','tecnicos','combustible','monedas','comprobante','ubigeos','bus_comp'));




    }


    public function buscar_pedido($comprobante){



        $ser = substr($comprobante,0,4);
        $num = trim(substr($comprobante,4,8));



        $bus_comp = DB::tABLE('cpe_cabecera')
        ->where('numdoc',$num)
        ->where('serdoc',$ser)
        ->first();

        if(!empty($bus_comp)){
          $cabecera = DB::tABLE('cpe_cabecera')
          ->where('IdCpe_cabecera',$bus_comp->ped_ref)
          ->first();

      }




      if(empty($cabecera)){

        return  Redirect::to('/pedidos')->withErrors(['mensaje' => 'NO SE ENCONTRÓ EL DOCUMENTO']);

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
        $comprobantes = DB::tABLE('tipo_documento')->get();

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

    

        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

        $users = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        ->where('role_id','2')
        ->get();

        $ubigeos = DB::tABLE('cat_ubigeo')->get();

        return view('empresas.puntosventas.mostrarpedido',compact('comprobantes','users','senudoc','motivos','modalidades','mediospagos','creditos','documentos','almacen','cabecera','detalle','datos','almacenes','vendedores','clientes','mediospagos','unidades','empresa','gastos','docidentidad','tipodocumento','marcas','modelos','monedas','comprobante','ubigeos','bus_comp'));




    }






    public function modificarpedidos(Request $request,$pedido){

      $fechaactual = now()->format('Y-m-d');

      $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);


      $cabecera = DB::tABLE('cpe_cabecera')
      ->where('numdoc',$pedido)
      ->where('ccafem',$fechaactual)
      ->where('tdocod','16')
      ->first();

      if(empty($cabecera)){
         return  Redirect::to('/pedidos')->withErrors(['mensaje' => 'EL PEDIDO NO EXISTE']);
      }
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

      $comprobante = DB::tABLE('tipo_documento')->where('pedido','1')->get();
   
      $unidades = DB::tABLE('unidad_medida')
      ->where('umeest','=','Activo')
      ->orderBy('umecod','asc')->get();


      $users = DB::tABLE('users')
      ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
      ->where('role_id','2')
      ->get();

      $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$cabecera->id_empresa_negocio)->get();


      return view('empresas.puntosventas.editar_pedido',compact('users','tipodocumento','creditos','documentos','almacen','cabecera','detalle','datos','almacenes','vendedores','clientes','mediospagos','unidades','empresa','comprobante'));


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
  $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

  $ubigeopartida = $request->get('ubigeopartida');
  $direccionpartida = $request->get('direccionpartida');
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
  $ubigeollegada = $request->get('ubigeollegada');
  $direccionllegada = $request->get('direccionllegada');
  $correo = $request->get('correo');

  $bus_alm = DB::tABLE('almacenes')->where('id_almacen',$request->get('id_almacen'))->first();
  $bus_ubi_par = DB::tABLE('cat_ubigeo')->where('ubi_cod',$ubigeopartida)->first();


  $bus_ubi_lle = DB::tABLE('cat_ubigeo')->where('ubi_cod',$ubigeollegada)->first();
  $desubigeollegada = $bus_ubi_lle->departamento.'-'.$bus_ubi_lle->provincia.'-'.$bus_ubi_lle->distrito;

  if(empty($direccionpartida)){

    $direccionpartida  = $sucursal->direccion;
    $desubigeopartida = $sucursal->departamento.'-'.$sucursal->provincia.'-'.$sucursal->distrito;
    $ubigeopartida = $sucursal->ubigeo;

       /*  $ubigeopartida = $bus_alm->ubigeo;
           $direccionpartida =$bus_alm->direccion;
           $desubigeopartida = $request->get('desubigeopartida');*/

       }else{

        $desubigeopartida = $bus_ubi_par->departamento.'-'.$bus_ubi_par->provincia.'-'.$bus_ubi_par->distrito;
        $ubigeopartida = $ubigeopartida;

    }

    if(empty($request->get('direccionllegada'))){
        $direccionllegada = $clidir;

    }


    $items = $request->get('proid');
    $cantidades = $request->get('cant');
    $precios = $request->get('precio');


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

  foreach($items as $index => $item ) {

    $i=$i+1;

    $bus_pro = productos::findOrFail($item);

    $detalle = new guias_remision_detalle;
    $detalle->IdProducto = $item;
    $detalle->procod = $bus_pro->procod;
    $detalle->pronom = $bus_pro->pronom;
    $detalle->cantidad = $cantidades[$index];
    $detalle->peso ="0.00";
    $detalle->umecod = $bus_pro->umecod;
    $detalle->IdCpe_guia =  $cabecera->IdCpe_guia; 


    $detalle->save();


}


       // self::generar_xml_guia($cabecera->IdCpe_guia);
$documento = self::generarpdfguia($cabecera->IdCpe_guia);

exec("c:\WINDOWS\system32\cmd.exe /c START powershell.exe -ExecutionPolicy Bypass Start-Process -FilePath ".public_path()."/pdf/".$documento." -Verb Print");

return response()->json(['estado'=>'registrado','mensaje'=>'REGISTRADO','id'=>$codfact,'guia'=>$cabecera->serieguia.'-'.$cabecera->numeroguia]);

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
         // dd($nom_xml);
              $firmar_xml = new cpe_cabecera;
         // $firmar_xml->firmar_xml($nom_xml);


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
       // $hash = $DOM->getElementsByTagName('DigestValue')->item(0)->nodeValue;
       // $actualizar->codhash = $hash;
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


              $cabpdf = DB::tABLE('guias_remision')->select('bultos','placa','licencia','nomconductor','motivo','modalidad','fechatraslado','nomcliente','nomcliente','direccionllegada','direccionpartida','ul.ubi_des as ubillegada','up.ubi_des as ubipartida','placa','pesobruto','rucconductor','ruccliente','guias_remision.tdocod','numeroguia','serieguia','tdodes','tdides')
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



      return $nompdffile;
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

      $mesas = '';

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
    $suc_id = $request->get('suc_id');

    $negocios = DB::tABLE('empresa_negocios')->get();

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

        //VENCIMIENTO CERTIFICADO

  $actual = now()->format('Y-m-d');

  $fecha_inicial = Carbon::createFromFormat('Y-m-d', $empresa->fec_ini_cer);
  $fecha_final = Carbon::createFromFormat('Y-m-d', $empresa->fec_fin_cer);
  $fecha_actual = Carbon::createFromFormat('Y-m-d',$actual);
  $dias_vencimiento = $fecha_actual->diffInDays($fecha_final);


  $doccomprobante = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->orderBy('tdocod','asc')->pluck('tdodes', 'tdocod');

  $documentos = DB::tABLE('tipo_documento')->where('formulario','3')->get();


  $IdEmpresa = Auth::user()->IdEmpresa;
  $ser = substr($comp,strpos($comp,'-')-4,4);
  $num = substr($comp,strpos($comp,'-')+1,8);



  $comprobantes = DB::tABLE('cpe_cabecera as cpe_c')->select('des_doc','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.ccasunrescod','enviado','ccadessun','clicorcli','cliente.clicor')
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
  ->where(function ($query) use ($suc_id) {
            if(!empty($suc_id)){
                $query->where('cpe_c.id_empresa_negocio',$suc_id);
            }
        }) 
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
   ->orderby('tdocod','asc')
  ->orderby('IdCpe_cabecera','desc')
  ->paginate(1000);


  return view('empresas.comprobantes.indexfacturacion',compact('comprobantes','empresa','doccomprobante','fecfin','fecin','documentos','tipdoc','dias_vencimiento','negocios','suc_id'));


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


public function store(Request $request)
{


 $rucemp = trim(Auth::user()->IdEmpresa);
 $empresa = Empresa::findOrFail($rucemp);
        //DETALLE 
 $tot_icbper = $request->get('tot_icbper');
 $icbper = $request->get('icbper');
 $mon_icbper = $request->get('mon_icbper');

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


    if($codpro->tigcod =='10'){

      $totalafec = ($total / 1.105);
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
$pedido = $request->get('pedido');
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
$tippago = $request->get('txtTipPag');
$efectivo = $request->get('efectivo1');
$visa = $request->get('visa');
$mastercard = $request->get('mastercard');
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
$cabecera->ccafem = $request->get('fecEmi');
$cabecera->ccafve = $request->get('fecVen');
       // $cabecera->ccaobs = $request->get('obser');
        //$cabecera->ccacde = $request->get();
$cabecera->tdicod = $tdicod;
$cabecera->ccandi = $cliruc;
$cabecera->ccanom = $clinom;
$cabecera->moncod = $mondoc;
$cabecera->tot_icbper = $tot_icbper;
$cabecera->tipo_pago = $tippago;
$cabecera->id_empresa_negocio = Auth::user()->id_empresa_negocio;
$cabecera->tipcambio = $camdoc;
$cabecera->ccatvg =  $totalafec;
$cabecera->ccatexo =  $totalexo;
$cabecera->ccatinaf =  $totalinaf;
$cabecera->ccaigv = $totaligv;
$cabecera->paga = $request->get('pagar');
$cabecera->vuelto = $request->get('vuelto');
$cabecera->ccaitv = $total;
$cabecera->visa =  $visa;
$cabecera->tipo_venta =  $tipo_venta;
if(!empty($request->get('mozo'))){
  $cabecera->mozo = $request->get('mozo');
}
$cabecera->efectivo = $efectivo;
$cabecera->mastercard = $mastercard;
$cabecera->clicod = $cliente->clicod;
$cabecera->ped_id = $pedido;
$cabecera->mes_id =  $mesa;
$cabecera->icbper = $totalicbper;
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
$pedido_est= pedidos::findOrFail($pedido);
$pedido_est->ped_est = 'Cerrado';
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

    $codpro = productos::findOrFail($proid[$index]);
    $codproducto = $codpro->procod;

    $detalle = new cpe_detalle;
    $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
    $detalle->umecod = $codpro->umecod;
    $detalle->cdecan = $cantidades[$index];
    $detalle->procod = $codproducto;
    $detalle->IdProducto = $codpro->IdProducto;
    
    $detalle->cdedes = $detpro[$index];
    $detalle->costo = $codpro->costofijo;
    $detalle->tigcod = $codpro->tigcod;
    $detalle->icbper = $codpro->icbper;

    if($codpro->tigcod =='10'){
      $preciouni = $puni[$index];
      $valoruni = $puni[$index]/1.105;
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


            //Guardar en una variable los items en el archivo .det
  $codumecin = unidad_medida::findOrFail($ume);



  $IdProducto = DB::tABLE('productos')->WHERE('procod',$codproducto)->where('IdEmpresa',trim(Auth::user()->IdEmpresa))->first();

  if($codpro->promocion =='0'){

    $stock_prod =productos::findOrFail($codpro->IdProducto);
               // $stock_prod->stock = $stock_prod->stock-$cantidades[$index];;
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
      $movimiento->unidad = $stock_prod->umecod;;
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



$comp = cpe_cabecera::findOrFail($codfact);

$params = [
  $comp->IdEmpresa,
  $comp->tdocod,
  $comp->serdoc,
  $comp->numdoc,
  number_format($comp->ccaigv, 2, '.', ''),
  number_format($comp->ccaitv, 2, '.', ''),
  $comp->ccafem,
  $comp->tdicod,
  $comp->ccandi,
];

$content = implode('|', $params).'|';


$comp->ccaqr = $content;

$comp->update();

self::generarqr($codfact);

if($tdocod !='13' || $tdocod !='15'){
 if($empresa->tipo_envio =='1'){
     self::generarcomprobante($codfact);
 }
}




if($empresa->formato =='TICKET' && $tdocod!='15'){
  for($i=1;$i<=$empresa->imp_venta;$i++){
     self::imprimir($codfact,$tdocod);
 }
}elseif($empresa->formato=='A4'){
    self::generarpdfgeneral($codfact);
}


return response()->json(['estado'=>'success','codfact' =>$codfact,'tdocod'=>$tdocod,'mensaje'=>'Comprobante Emitido','id_ped'=>$pedido]);

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

 public function imprimir_comanda_venta_directa($cpe_id)
{
    $IdEmpresa = Auth::user()->IdEmpresa;
    $empresanegocios = DB::table('empresa_negocios')->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)->first();
    // ✅ Traemos los datos de la empresa al igual que en imprimir()
    $empresa = DB::table('empresa')->where('IdEmpresa', $IdEmpresa)->first();

    $cabecera_venta = DB::table('cpe_cabecera as cab')
        ->leftjoin('users', 'users.IdUsuario', 'cab.IdUsuario_ven')
        ->where('cab.IdCpe_cabecera', $cpe_id)
        ->first();

    if (empty($cabecera_venta)) { return; }

    $impresoras = DB::table('configuracion_impresoras')->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)->get();

    foreach ($impresoras as $impresora) {
        $detalle_comanda = DB::table('cpe_detalle')
            ->where('IdCpe_cabecera', $cpe_id)
            ->join('productos', 'cpe_detalle.IdProducto', 'productos.IdProducto')
            ->leftjoin('categorias', 'categorias.cat_id', 'productos.cat_id')
            ->where('categorias.id_empresa_negocio', Auth::user()->id_empresa_negocio)
            ->where('categorias.impresora', $impresora->Id)
            ->get();

        if ($detalle_comanda->count() > 0) {
            $printer = null;
            try {
                if ($impresora->tip_conex_imp == 'RED') {
                    $connector = new NetworkPrintConnector($impresora->ruta, 9100);
                } else {
                    $connector = new WindowsPrintConnector("smb://" . $impresora->ruta);
                }

                $printer = new Printer($connector);
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->setFont(Printer::FONT_A);
                $printer->setTextSize(1, 1);

                // ✅ FIX: Validación estricta del logo para que no intente cargar carpetas
                if (!empty($empresanegocios->logosuc) && is_file(public_path() . '/' . $empresanegocios->logosuc)) {
                    $logo = EscposImage::load(public_path() . '/' . $empresanegocios->logosuc, false);
                    $printer->bitImage($logo);
                }
                
                // ✅ FIX: Usamos NomEmpresa igual que en tu función imprimir()
                $nomEmpresa = !empty($empresa->NomEmpresa) ? $empresa->NomEmpresa : 'VENTA';
                $printer->text($nomEmpresa . "\n"); 
                $printer->text("PEDIDO PARA LLEVAR\n"); 
                $printer->text("Comprobante: " . $cabecera_venta->serdoc . "-" . $cabecera_venta->numdoc . "\n");
                $printer->text("Fecha: " . $cabecera_venta->ccafem . "\n");
                
                if (!empty($cabecera_venta->name)) {
                    $printer->text("Vendedor: " . $cabecera_venta->name . " " . $cabecera_venta->apeusu . "\n");
                }
                if (!empty($cabecera_venta->ccanom) && $cabecera_venta->ccanom != 'VENTA AL PORTADOR') {
                    $printer->text("Cliente: " . $cabecera_venta->ccanom . "\n");
                }

                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text("----------------------------------------\n");
                $printer->text("DESCRIPCION            CANT.  OBS.\n");
                $printer->text("----------------------------------------\n");
                $printer->setFont(Printer::FONT_B);
                $printer->setTextSize(2, 2); 

                foreach ($detalle_comanda as $det) {
                    $printer->text($det->cdecan . "  " . $det->cdedes . "\n");
                    if (!empty($det->pronomobs)) {
                        $printer->text("    (Obs: " . $det->pronomobs . ")\n");
                    }
                }

                $printer->text("\n");
                $printer->setFont(Printer::FONT_B);
                $printer->setTextSize(1, 1);
                $printer->text("----------------------------------------\n");
                
                if (!empty($cabecera_venta->ccaobs)) { 
                     $printer->setJustification(Printer::JUSTIFY_CENTER);
                     $printer->text("Observaciones Generales:\n" . $cabecera_venta->ccaobs . "\n");
                     $printer->text("----------------------------------------\n");
                }
                
                $printer->feed();
                $printer->cut();
                $printer->pulse();
                $printer->close();

            // ✅ FIX: Usamos Throwable para atrapar CUALQUIER tipo de error de PHP
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Error imprimir comanda: " . $e->getMessage());
            } finally {
                if (!empty($printer)) {
                    try { @$printer->close(); } catch (\Throwable $e) {}
                }
            }
        }
    }
}

public function imprimir_precuenta_venta_directa($cpe_id)
{
    $IdEmpresa = Auth::user()->IdEmpresa;
    $empresanegocios = DB::table('empresa_negocios')->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)->first();
    // ✅ Traemos la empresa
    $empresa = DB::table('empresa')->where('IdEmpresa', $IdEmpresa)->first();

    $cabecera_venta = DB::table('cpe_cabecera as cab')
        ->leftjoin('users', 'users.IdUsuario', 'cab.IdUsuario_ven')
        ->where('cab.IdCpe_cabecera', $cpe_id)
        ->first();

    if (empty($cabecera_venta)) { return; }

    $detalle = DB::table('cpe_detalle')
        ->where('IdCpe_cabecera', $cpe_id)
        ->join('productos', 'cpe_detalle.IdProducto', 'productos.IdProducto')
        ->get();

    $printer = null;
    try {
        $impresoras = DB::table('configuracion_impresoras')->where('Id', Auth::user()->terminal)->first();
        if (!$impresoras) { return; }

        $connector = new WindowsPrintConnector("smb://" . $impresoras->ruta);
        $printer = new Printer($connector);

        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setFont(Printer::FONT_A);

        // ✅ FIX: Validación estricta del logo
        if (!empty($empresanegocios->logosuc) && is_file(public_path() . '/' . $empresanegocios->logosuc)) {
            $logo = EscposImage::load(public_path() . '/' . $empresanegocios->logosuc, false);
            $printer->bitImage($logo);
        }

        $printer->setTextSize(2, 2);
        // ✅ FIX: Nombre de empresa corregido
        $nomEmpresa = !empty($empresa->NomEmpresa) ? $empresa->NomEmpresa : 'VENTA';
        $printer->text($nomEmpresa . "\n");
        $printer->setTextSize(1, 1);

        $printer->text("PRECUENTA\n");
        $printer->text("Comprobante: " . $cabecera_venta->serdoc . "-" . $cabecera_venta->numdoc . "\n");
        $printer->text("Fecha: " . $cabecera_venta->ccafem . "\n");

        if (!empty($cabecera_venta->name)) {
            $printer->text("Vendedor: " . $cabecera_venta->name . " " . $cabecera_venta->apeusu . "\n");
        }

        if (!empty($cabecera_venta->ccanom) && $cabecera_venta->ccanom != 'VENTA AL PORTADOR') {
            $printer->text("Cliente: " . $cabecera_venta->ccanom . "\n");
        }

        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("CONCEPTO  CANTIDAD   PU  IMPORTE\n");
        $printer->text("_________________________________\n");

        foreach ($detalle as $det) {
            $primeralinea = str_pad(substr($det->cdedes, 0, 17), 17, " ", STR_PAD_RIGHT);
            $segundalinea = str_pad(substr($det->cdedes, 18, 34), 17, " ", STR_PAD_RIGHT);
            $printer->text($primeralinea . "  " . $det->cdecan . "  " . $det->cdepuni . "  " . $det->cdevve . "\n");
            if (!empty($segundalinea)) {
                $printer->text($segundalinea . "\n");
            }
        }

        $printer->text("\n");
        $printer->text("CONSUMO TOTAL: " . $cabecera_venta->ccaitv . "\n");

        $printer->feed();
        $printer->cut();
        $printer->pulse();
        $printer->close();

    // ✅ FIX: Atrapa fatal errors también
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error("Error imprimir precuenta: " . $e->getMessage());
    } finally {
        if (!empty($printer)) {
            try { @$printer->close(); } catch (\Throwable $e) {}
        }
    }
}

    public function imprimir($cpe,$tipdoc){

        $rucemp = Auth::user()->IdEmpresa;

        $empresa = Empresa::findOrFail($rucemp);

        $empresanegocios = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);

        $nomdoc = DB::tABLE('tipo_documento')->where('tdocod',$tipdoc)->first();


        if($tipdoc == '01' || $tipdoc == '03' || $tipdoc == '13' || $tipdoc == '07' || $tipdoc == '99'){
          $cabecera = DB::tABLE('cpe_cabecera as cab')
          ->leftjoin('cliente as cli','cab.ccandi','=','cli.clinum')
          ->leftjoin('tipo_documento as tdo','cab.tdocod','=','tdo.tdocod')
          ->leftjoin('tipo_documento_identidad as tdi','cab.tdicod','=','tdi.tdicod')
          ->leftjoin('moneda as mon','cab.moncod','=','mon.moncod')
          ->leftjoin('tipo_operacion as top','cab.topcod','=','top.topcod')

          ->where('IdCpe_cabecera','=',$cpe)
          ->first();

          $bus_vendedor = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$cpe)->first();

          self::generarqr($cpe);

          $cuotas = DB::tABLE('ventas_cuotas')->where('IdCpe_cabecera',$cpe)->get();


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

      $historial_puntos = DB::table('puntos_historial')->where('venta_id', $cpe)->get();
    $puntos_ganados_ticket = $historial_puntos->sum('puntos_ganados');
    


 try { 

        //$connector = new WindowsPrintConnector("smb://".$impresoras->ruta);
        //$printer = new Printer($connector);

    //IMPRESORA VIRTUAL
    $connector = new DummyPrintConnector();
    $printer = new Printer($connector);

        $printer->setJustification(Printer::JUSTIFY_CENTER);
        
        $printer->getPrintConnector()->write(PRINTER::ESC . "B" . chr(4) . chr(1));
        

        if($cabecera->tdocod !='13'){
             if(file_exists($empresanegocios->logosuc)){
              $logo = EscposImage::load(public_path().'/'.$empresanegocios->logosuc,false);
              $printer->bitImage($logo);
            }

            //NOMBRE COMERCIAL
            $printer->setTextSize(2,2);
           // $printer->text($empresanegocios->nombre_comercial."\n");
            $printer->setTextSize(1,1);

             //NOMBRE EMPRESA
             $printer->text($empresa->NomEmpresa."\n");

            $printer->setFont(Printer::FONT_A);

             //DIRECCION DE LA EMPRESA
            $printer->text($empresanegocios->direccion."\n");

            //UBIGEO DEL CLIENTE DEPARTAMENTO-PROVINCIA-DISTRITO
            $printer->text($empresanegocios->departamento."-".$empresanegocios->provincia."-".$empresanegocios->distrito."\n");

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
        //$printer->text("Vendedor: ".$bus_vendedor->vendedor."\n"."\n");
        
        //DATOS DEL CLIENTE
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("Fecha:       ".$cabecera->fecha_hora."\n");
        $printer->text("RUC/DNI:     ".$cabecera->clinum."\n");
        $printer->text("Cliente:     ".$cabecera->ccanom."\n");
        $printer->text("Dirección:   ".$cabecera->direccion."\n");
        $printer->text("Condición de Pago:   ".$cabecera->estadopago."\n");
        //DETALLE DE LOS PRODUCTOS QUE SE VENDEN
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        //$printer->text("CODIGO    DESCRIPCION "."\n");
        //$printer->text("CANTIDAD    UNIDAD    PRECIO    TOTAL"."\n");
        $printer->text("______________________________________________"."\n");
        $printer->text("Descripción       Cant.    UDM.   P.U   Total"."\n");
        $printer->text("______________________________________________"."\n");
        foreach ($detalle as $det){
            if($det->cdevve > 0){
                $printer->text($det->cdedes."\n");
            $printer->text("                ".$det->cdecan."  ".$det->umecod."   ".$det->cdepuni."    ".$det->cdevve."\n");
            }else{
                $printer->text($det->cdedes."\n");
            } 
        }

        //$printer->text("\n");
        $printer->text("______________________________________________"."\n");

        //MEDIOS DE PAGO
        foreach ($medios as $m) {
            $printer->text(str_pad($m->nom_med_pag." ".$cabecera->simbolo,21," ", STR_PAD_RIGHT)."                 ".number_format(($m->monto),'2','.',',')."\n");
        }

        if($cabecera->tdocod !='13'){  
            $printer->text(str_pad("SUBTOTAL: ".$cabecera->simbolo,21," ", STR_PAD_RIGHT)."                 ".number_format(($cabecera->ccatvg+$cabecera->ccatexo),'2','.',',')."\n");
            //$printer->text("OP. GRAVADA: ".$cabecera->simbolo."                    ".$cabecera->ccatvg."\n");
           // $printer->text("OP. EXONERADA: ".$cabecera->simbolo."                  ".$cabecera->ccatexo."\n");
            // $printer->text("OP. INAFECTA: ".$cabecera->simbolo."         "."0.00"."\n");
          //  $printer->text("IGV 18%: ".$cabecera->simbolo."                        ".$cabecera->ccaigv."\n");
              $printer->text(str_pad("ICBPER: ".$cabecera->simbolo,21," ", STR_PAD_RIGHT)."                  ".number_format($cabecera->icbper,'2','.',',')."\n");
           
        }
         $printer->text(str_pad("TOTAL: ".$cabecera->simbolo,21," ", STR_PAD_RIGHT)."                 ".number_format($cabecera->ccaitv,'2','.',',')."\n"."\n");

if ($cabecera->paga!=0) {
    $printer->text("PAGA CON: ".$cabecera->simbolo."                       ".$cabecera->paga."\n");
}

if ($cabecera->vuelto!=0) {
    $printer->text("VUELTO: ".$cabecera->simbolo."                         ".$cabecera->vuelto."\n"."\n");
}

    /*$cliente_ticket = DB::table('cliente')->where('clinum', $cabecera->clinum)->first();
        if($cliente_ticket && ($puntos_ganados_ticket > 0 || count($premios_ticket) > 0)) {
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("----------------------------------------------\n");
            $printer->text("*** PUNTOS HOLA P ***\n");
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            
            if($puntos_ganados_ticket > 0){
                $printer->text("Ganaste en esta visita: ".$puntos_ganados_ticket." pts.\n");
            }
            
            foreach($premios_ticket as $premio){
                $printer->text("CANJE: ".$premio->premio_nombre." (-".$premio->puntos_costo." pts)\n");
            }
            
            $printer->text("Saldo Total Acumulado: ".($cliente_ticket->puntos ?? 0)." pts.\n");
            $printer->text("----------------------------------------------\n\n");
        }*/
       
            $printer->text($totalletras." ".$cabecera->monnom."\n"."\n");
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setFont(Printer::FONT_B);
            $printer->text("REPRESENTACION IMPRESA DE LA ".$cabecera->tdodes."\n");
             
            if($cabecera->tdocod=='01' || $cabecera->tdocod=='03'){
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $logo1 = EscposImage::load(public_path().'/qr/QR-'.$rucemp.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$cabecera->numdoc.'.png',false);
                $printer->bitImage($logo1);
            }
            
            if($cabecera->tdocod !='13'){  
                $printer->setFont(Printer::FONT_B);
                $printer->text("\n"."BIENES TRANSFERIDOS EN LA AMAZONIA PARA SER CONSUMIDOS EN LA MISMA. SERVICIOS PRESTADOS EN LA AMAZONIA"."\n");
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->text("\n".$empresanegocios->pie."\n");
            }

            if($cabecera->estadopago=='CREDITO'){

                $printer->setJustification(Printer::JUSTIFY_CENTER);
                 $printer->text("CUOTAS"."\n");
                  $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text("#   F.VENCI.   MONEDA  MONTO"."\n");
                $printer->text("________________________________"."\n");
                foreach ($cuotas as $cuota){
                  
                        $printer->text($cuota->ven_cuo_num."   ".$cuota->ven_cuo_fec_ven."   ".$cabecera->moncod."   ".$cuota->ven_cuo_mon."\n\n");
                    
                }  
            }
         

        $printer->feed();
         
     
        $printer->cut();
         
     
        $printer->pulse();
         
        $codigo_raw = $connector->getData();

        $printer->close();

        DB::table('cola_impresion')->insert([
            'contenido' => base64_encode($codigo_raw),
            'impresora' => $impresoras->descripcion, // "CPE"
            'estado'    => '0'            
        ]);

        
      }catch (\Exception $e) {
        dd($e);

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
    
    $detalle->cdedes = $detpro[$index];
    $detalle->pronomobs = $pronomobs[$index];
    $detalle->costo = $codpro->costo;
    $detalle->tigcod = $codpro->tigcod;
    $detalle->icbper = $codpro->icbper;

    if($codpro->tigcod =='10'){
      $preciouni = $puni[$index];
      $valoruni = $puni[$index]/1.105;
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
  $detalle->cpe_det_venc = $codpro->vencimiento;
  $detalle->cpe_det_lote = $codpro->lote;
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



  DB::tABLE('movimientos_productos')->insert([
    'IdProducto'=>$id_prod,
    'precio'=>'0',
    'cantidad'=>$cantidades[$index]*$codpro->factor,
    'costo'=>$codpro->costo,
    'mov_cab_id'=>'',
    'stock'=>$stock,
    'cliente'=>$cabecera->ccanom,
    'IdProducto_rel'=>$id_prod,
    'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
    'com_cab_id'=>'',
    'stock_inicial'=>'0',
    'serie'=>$cabecera->serdoc,
    'numero'=>$cabecera->numdoc,
    'tdocod'=>$cabecera->tdocod,
    'tipo'=>'3',
    'mov_tip'=>'E',
    'cod_tip_ope'=>'99',
    'descripcion'=>'SALIDA_AREAS',
    'id_empresa_negocio'=>Auth::user()->id_empresa_negocio,
    'id_almacen'=>$almacen->id_almacen,
    'fecha_mov'=>$fecha,
    'mov_lote'=>$codpro->lote,
    'mov_vencimiento'=>$codpro->vencimiento
]);





}






return response()->json(['estado'=>'success']);


}



public function actualizar_salida(Request $request){


    $almacen = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();

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
    $IdCpe_cabecera= $request->get('IdCpe_cabecera');
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

    $moneda = DB::tABLE('moneda')->where('moncod','=',$mondoc)->first();

    

    $oldDate = strtotime($request->get('clifecnac'));

    $mes = date('m',$oldDate);

      //Crear un objeto de la cabecera del comprobante para registrar los datos de la cabecera
    $cabecera = cpe_cabecera::findOrFail($IdCpe_cabecera);
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

    $cabecera->update();

    $codfact = $cabecera->IdCpe_cabecera; 

    $registros = DB::tABLE('cpe_detalle')->where('IdCpe_cabecera',$IdCpe_cabecera)->get();


    foreach ($registros as $reg) {

        try{

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
            ->where('id_almacen',$almacen->id_almacen)
            ->where('id_empresa_negocio',$almacen->id_empresa_negocio)
            ->first();



            DB::tABLE('producto_stock')
            ->where('IdProducto',$id)
            ->where('id_almacen',$almacen->id_almacen)
            ->where('id_empresa_negocio',$almacen->id_empresa_negocio)
            ->update(['stock'=>$stock_prod->stock+($reg->cdecan*$reg->cpe_det_factor)]);

        }

    }catch(\Exception $e){

        dd($reg);
    }


}


DB::tABLE('cpe_detalle')->where('IdCpe_cabecera',$IdCpe_cabecera)->delete();

DB::tABLE('movimientos_productos')->where('IdCpe_cabecera',$IdCpe_cabecera)->delete();

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
    
    $detalle->cdedes = $detpro[$index];
    $detalle->pronomobs = $pronomobs[$index];
    $detalle->costo = $codpro->costo;
    $detalle->tigcod = $codpro->tigcod;
    $detalle->icbper = $codpro->icbper;

    if($codpro->tigcod =='10'){
      $preciouni = $puni[$index];
      $valoruni = $puni[$index]/1.105;
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



  DB::tABLE('movimientos_productos')->insert([
    'IdProducto'=>$id_prod,
    'precio'=>'0',
    'cantidad'=>$cantidades[$index]*$codpro->factor,
    'costo'=>$codpro->costo,
    'mov_cab_id'=>'',
    'stock'=>$stock,
    'cliente'=>$cabecera->ccanom,
    'IdProducto_rel'=>$id_prod,
    'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
    'com_cab_id'=>'',
    'stock_inicial'=>'0',
    'serie'=>$cabecera->serdoc,
    'numero'=>$cabecera->numdoc,
    'tdocod'=>$cabecera->tdocod,
    'tipo'=>'3',
    'mov_tip'=>'E',
    'descripcion'=>'SALIDA_AREAS',
    'id_empresa_negocio'=>Auth::user()->id_empresa_negocio,
    'id_almacen'=>$almacen->id_almacen,
    'fecha_mov'=>$fecha,
]);

}


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


      $totalafec = ($total / 1.105);
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
      $cliente = Cliente::UpdateOrCreate(['clinum'=>$cliruc],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$clicor,'rucemp'=>$rucemp,'tdicod'=>$tdicod,'telefono'=>$request->get('clitel'),'clifecnac'=>$request->get('clifecnac'),'mes_nac'=>$mes]);

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
          $movimiento->importe = $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)*1.105)); 
      }

      $movimiento->estado = '1';
      $movimiento->mov_fecha = $cabecera->ccafem;
      $movimiento->clicod = $cabecera->clicod;
      $movimiento->registro = 'Registrado';

      if($contar==0){
          $totalsaldo =  $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)*1.105));
      }else{
          $totalsaldo = $buscar->last()->saldo + $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)*1.105));
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
    
    $detalle->cdedes = $detpro[$index];
            //$detalle->costo = $codpro->costo;
    $detalle->tigcod = '20';
   //         $detalle->icbper = $codpro->icbper;

    if($tigcod =='10'){
      $preciouni = $puni[$index];
      $valoruni = $puni[$index]/1.105;
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


      $totalafec = ($total / 1.105);
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
          $movimiento->importe = $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)*1.105)); 
      }

      $movimiento->estado = '1';
      $movimiento->mov_fecha = $cabecera->ccafem;
      $movimiento->clicod = $cabecera->clicod;
      $movimiento->registro = 'Registrado';

      if($contar==0){
          $totalsaldo =  $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)*1.105));
      }else{
          $totalsaldo = $buscar->last()->saldo + $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)*1.105));
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
    
    $detalle->cdedes = $detpro[$index];
    $detalle->pronomobs = $pronomobs[$index];
    $detalle->costo = $codpro->costofijo;
    $detalle->tigcod = $codpro->tigcod;
    $detalle->icbper = $codpro->icbper;

    if($codpro->tigcod =='10'){
      $preciouni = $puni[$index];
      $valoruni = $puni[$index]/1.105;
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


      $totalafec = ($total / 1.105);
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
          $movimiento->importe = $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)*1.105)); 
      }

      $movimiento->estado = '1';
      $movimiento->mov_fecha = $cabecera->ccafem;
      $movimiento->clicod = $cabecera->clicod;
      $movimiento->registro = 'Registrado';

      if($contar==0){
          $totalsaldo =  $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)*1.105));
      }else{
          $totalsaldo = $buscar->last()->saldo + $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)*1.105));
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
    
    $detalle->cdedes = $detpro[$index];
    $detalle->pronomobs = $pronomobs[$index];
    $detalle->costo = $codpro->costofijo;
    $detalle->tigcod = $codpro->tigcod;
    $detalle->icbper = $codpro->icbper;

    if($codpro->tigcod =='10'){
      $preciouni = $puni[$index];
      $valoruni = $puni[$index]/1.105;
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


      $totalafec = ($total / 1.105);
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
            
            $detalle->cdedes = $detpro[$index];
            $detalle->costo = $codpro->costofijo;
            $detalle->tigcod = $codpro->tigcod;
            $detalle->icbper = $codpro->icbper;

            if($codpro->tigcod =='10'){
              $preciouni = $puni[$index];
              $valoruni = $puni[$index]/1.105;
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
         
         $detalle->cdedes = $detpro[$index];
         $detalle->costo = $codpro->costofijo;
         $detalle->tigcod = $codpro->tigcod;
         $detalle->icbper = $codpro->icbper;

         if($codpro->tigcod =='10'){
            $preciouni = $puni[$index];
            $valoruni = $puni[$index]/1.105;
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


      $totalafec = ($total / 1.105);
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
      $movimiento->importe = $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)*1.105)); 
  }

  $movimiento->estado = '1';
  $movimiento->mov_fecha = $cabecera->ccafem;
  $movimiento->clicod = $cabecera->clicod;
  $movimiento->registro = 'Registrado';

  if($contar==0){
      $totalsaldo =  $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)*1.105));
  }else{
      $totalsaldo = $buscar->last()->saldo + $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)*1.105));
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
        
        $detalle->cdedes = $detpro[$index];
        $detalle->costo = $codpro->costofijo;
        $detalle->tigcod = $codpro->tigcod;
        $detalle->icbper = $codpro->icbper;

        if($codpro->tigcod =='10'){
          $preciouni = $puni[$index];
          $valoruni = $puni[$index]/1.105;
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
     
     $detalle->cdedes = $detpro[$index];
     $detalle->costo = $codpro->costofijo;
     $detalle->tigcod = $codpro->tigcod;
     $detalle->icbper = $codpro->icbper;

     if($codpro->tigcod =='10'){
        $preciouni = $puni[$index];
        $valoruni = $puni[$index]/1.105;
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

 $empresa = Empresa::findOrFail($rucemp);
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
     // ->where('p.IdEmpresa',$rucemp)
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

   return \QRCode::text($cabecera->ccaqr)->setMargin(1)->setSize(4)->setOutFile($file)->png();

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

$mediospagos = DB::tABLE('medios_pagos')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();

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



$vendedores = DB::tABLE('users')
->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
       // ->where('role_id','5')
->get();

$creditos = DB::tABLE('credito_dias')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();

$documentos = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderby('tdicod')->get();

$comprobante = DB::tABLE('tipo_documento')->where('caja','1')->get();

$comprobantes = DB::tABLE('tipo_documento')->get();

$tipodocumento = DB::tABLE('tipo_documento_identidad')->get();

$igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
->orderBy('tigcod','asc')->get();

$docidentidad = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

$unidades = DB::tABLE('unidad_medida')
->where('umeest','=','Activo')
->orderBy('umecod','asc')->get();

$empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);

$sucursal = DB::tABLE('empresa_negocios')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();

    $bus_alm = new Almacen;
    $almacen = $bus_alm->buscar_almacen_predeterminado($sucursal->first()->id_empresa_negocio);


$marcas = DB::tABLE('marcas')->get();

$modelos = DB::tABLE('modelos')->get();

$combustible = DB::tABLE('combustible')->get();

$monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();


$users = DB::tABLE('users')
->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
->where('role_id','2')
->get();

$ubigeos = DB::tABLE('cat_ubigeo')->get();





return view('empresas.puntosventas.cobrarcotizacion',compact('comprobantes','ubigeos','users','gastos','tipodocumento','creditos','documentos','almacen','cabecera','detalle','datos','vendedores','clientes','mediospagos','unidades','datos','empresa','senudoc','rucemp','motivos','modalidades','docidentidad','marcas','modelos','combustible','comprobante','monedas','ubigeos','comprobantes','sucursal'));






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
    
        $mediospagos = DB::tABLE('medios_pagos')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();

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
       //->where('role_id','5')
        ->get();

        $creditos = DB::tABLE('credito_dias')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();

        $documentos = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderby('tdicod')->get();

        $comprobante = DB::tABLE('tipo_documento')->get();

        $comprobantes = DB::tABLE('tipo_documento')->get();

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

        $users = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        ->where('role_id','2')
        ->get();

        $ubigeos = DB::tABLE('cat_ubigeo')->get();

          
        return view('empresas.puntosventas.editarventa',compact('comprobantes','ubigeos','users','gastos','tipodocumento','creditos','documentos','almacen','cabecera','detalle','datos','almacenes','vendedores','clientes','mediospagos','unidades','datos','empresa','senudoc','rucemp','motivos','modalidades','docidentidad','marcas','modelos','tecnicos','combustible','comprobante','monedas','ubigeos','comprobantes'));
       


    }



public function actualizarventa(Request $request)
{


    DB::beginTransaction();

    try {


        $procesos = $request->get('proceso');

        $IdCpe_guia = $request->get('IdCpe_guia');


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
        $mon_cuo = $request->get('mon_cuo');
        $fec_cuo = $request->get('fec_cuo');
        $val_uni = $request->get('precio');

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
  $cliente = Cliente::UpdateOrCreate(['clinum'=>$cliruc],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$clicor,'rucemp'=>$rucemp,'tdicod'=>$tdicod,'telefono'=>$request->get('clitel'),'clifecnac'=>$request->get('clifecnac'),'mes_nac'=>$mes]);


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
//$cabecera->ped_ref = $request->get('ped_id');
$cabecera->moncod = $mondoc;
$cabecera->direccion = $clidir;
$cabecera->cre_dia_id = $estadopago;
$cabecera->guia_remision = $request->get('guia_remision');
$cabecera->cod_tip_ope ='01';

if(!empty($bus_cot)){
  $cabecera->referencia = $bus_cot->serdoc.'-'.$bus_cot->numdoc;
}

$cabecera->tipcambio = $camdoc;
if($sucursal->tip_igv_pred =='10'){
    $cabecera->ccatvg =  $total/1.105;
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

if(!empty($IdCpe_guia)){
    $act_guia = guias_remision::findOrFail($IdCpe_guia);
    $act_guia->tdicod = $cabecera->tdocod;
    $act_guia->ruccliente = $cabecera->ccandi;
    $act_guia->nomcliente = $cabecera->ccanom;
    $act_guia->direccionllegada = $cabecera->direccion;
    $act_guia->update();
}


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
          $movimiento->importe = $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)*1.105)); 
      }

      $movimiento->estado = '1';
      $movimiento->mov_fecha = $cabecera->ccafem;
      $movimiento->clicod = $cabecera->clicod;
      $movimiento->registro = 'Registrado';

      if($contar==0){
          $totalsaldo =  $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)*1.105));
      }else{
          $totalsaldo = $buscar->last()->saldo + $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)*1.105));
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

if(!empty($IdCpe_guia)){
    $bus_guia = DB::tABLE('guias_remision')->where('IdCpe_guia',$IdCpe_guia)->first();
}

if(!empty($bus_guia)){

    DB::tABLE('guias_remision_detalle')->where('IdCpe_guia',$IdCpe_guia)->delete();
    $i=0;

    foreach($proid as $idx => $item) {

        $i=$i+1;

        $bus_pro = productos::findOrFail($item);

        $detalle = new guias_remision_detalle;
        $detalle->IdProducto = $item;
        $detalle->procod = $bus_pro->procod;
        $detalle->pronom = $bus_pro->pronom;
        $detalle->cantidad = $cantidades[$idx];
        $detalle->peso ="0.00";
        $detalle->umecod = $bus_pro->umecod;
        $detalle->IdCpe_guia =  $IdCpe_guia; 
        $detalle->save();


    }


    $documento_guia = self::generarpdfguia($IdCpe_guia);

    exec("c:\WINDOWS\system32\cmd.exe /c START powershell.exe -ExecutionPolicy Bypass Start-Process -FilePath ".public_path()."/pdf/".$documento_guia." -Verb Print");

}


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

$detalle->cdedes = $detpro[$index];
         //   $detalle->pronomobs = $pronomobs[$index];
$detalle->costo = $codpro->costofijo;
$detalle->tigcod = $codpro->tigcod;
$detalle->icbper = $codpro->icbper;
$detalle->cpe_det_stock = $stock;
$detalle->desc_mon = $descuento[$index];




/*calcular porcentaje de descuento*/
if($sucursal->tipo_desc=='1'){
    $desc_mon = $descuento[$index];
    $desc_por = ($descuento[$index]*100)/$val_uni[$index];
}elseif($sucursal->tipo_desc=='2'){
    $desc_por = $descuento[$index];
    $desc_mon = $val_uni[$index]*($descuento[$index]/100);
}

if($codpro->tigcod =='10'){

  $preciouni = $puni[$index];
  $valoruni = $puni[$index]/1.105;
  $valorunitario = $val_uni[$index]/1.105;

  $valorsubtotal = $vtot[$index]/1.105;
  $valortotal = $vtot[$index];

}elseif($codpro->tigcod=='20'){

  $preciouni = $puni[$index];
  $valoruni = $puni[$index];
  $valorunitario = $val_uni[$index];

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
    'IdProducto'=>$id,
    'precio'=>$preciouni,
    'cantidad'=>$cantidades[$index]*$codpro->factor,
    'costo'=>$codpro->costofijo,
    'mov_cab_id'=>'',
    'stock'=>$stock,
    'cliente'=>$cabecera->ccanom,
    'cod_tip_ope'=>'01',
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

    $mov_cal_stock = new Almacen();
    $mov_cal_stock->movimiento_calcular_stock($id_prod,$id_almacen);

            

}




        }else{


            $detalle = new cpe_detalle;
            $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
            $detalle->umecod = $unidades[$index];
            $detalle->cdecan = $cantidades[$index];
            
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
              $valoruni = $puni[$index]/1.105;
              $valorunitario = $puni[$index]/1.105;

              $valorsubtotal = $vtot[$index]/1.105;
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



if($sucursal->formato =='TICKET' && $sucursal->ticket_pantalla=='0'){         
    if(empty($cabecera->referencia)){
     for($i=1;$i<=$empresa->imp_venta;$i++){
      if($request->get('opcion')=='0'){
        self::imprimir($codfact,$tdocod);
    }
}
}
}elseif($sucursal->formato=='A4' && $sucursal->ticket_pantalla=='0'){

 //if($request->get('opcion')=='0'){
     exec("c:\WINDOWS\system32\cmd.exe /c START powershell.exe -ExecutionPolicy Bypass Start-Process -FilePath ".public_path()."/pdf/".$documento." -Verb Print");
// }

}


DB::tABLE('cuentas_cobrar')->where('IdCpe_cabecera',$codfact)->delete();

if($buscre->cre_dia_tip !='CONTADO'){
    $buscuentacobrar = DB::tABLE('cuentas_cobrar')->where('IdCpe_cabecera',$codfact)->get();

    if(count($buscuentacobrar)>'0'){
      self::actualizarcuentascobrar($codfact);
  }else{
    self::registrarcuentascobrar($codfact);
}


}


        // REGISTR DE PROCESOS
$busprocesos = DB::tABLE('procesos_comprobante')->where('IdCpe_cabecera',$codfact)->get();
if(!empty($procesos)){
    foreach ($busprocesos as $busproc) {

      if(!in_array($busproc->proc_id,$procesos)){
        DB::tABLE('procesos_comprobante')
        ->where('proc_id',$busproc->proc_id)
        ->where('IdCpe_cabecera',$codfact)->delete();
    }  


}

foreach($procesos as $proc){

    $buscar = DB::tABLE('procesos_comprobante')->where('proc_id',$proc)->where('IdCpe_cabecera',$codfact)->first();

    if(empty($buscar)){

       DB::tABLE('procesos_comprobante')
       ->insert([
        'proc_id'=>$proc,
        'IdCpe_cabecera'=>$codfact
    ]);

   }

}
}else{
    DB::tABLE('procesos_comprobante')->where('IdCpe_cabecera',$codfact)->delete();
}


        //FIN REGISTRO DE PROCESOS


DB::commit();

return response()->json(['estado'=>'success','codfact' =>$codfact,'tdocod'=>$tdocod,'mensaje'=>'Comprobante Emitido']);



} catch (\Exception $e) {

    DB::rollback();

    return response()->json(['estado'=>'error','mensaje'=>$e->getMessage()]);

}




}



public function actualizarcuentascobrar($idventa){

    $venta = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$idventa)->first();

    $buscuenta = DB::tABLE('cuentas_cobrar')->where('IdCpe_cabecera',$idventa)->first();

    $cuentacobrar = cuentascobrar::findOrFail($buscuenta->cue_cob_id);

    $saldo = $venta->totalcredito-$buscuenta->abono;

    if($saldo>'0'){
      $cuentacobrar->estado_cob = 'pendiente';   
  }else{
    $cuentacobrar->estado_cob = 'cancelado';
}

$cuentacobrar->total = $venta->totalcredito;
$cuentacobrar->saldo = $venta->totalcredito-$buscuenta->abono;
$cuentacobrar->update();

$buscuentadetalle = DB::tABLE('cuentas_cobrar_detalle')
->where('cue_cob_id',$buscuenta->cue_cob_id)
->orderby('cue_cob_det_id','asc')->get();

$totaldetalle = $venta->totalcredito;

foreach ($buscuentadetalle as $val){

    DB::tABLE('cuentas_cobrar_detalle')->where('cue_cob_det_id',$val->cue_cob_det_id)
    ->update([
        'total_detalle'=>$totaldetalle,
        'abono'=>$val->abono,
        'saldo_detalle'=>$totaldetalle-$val->abono
    ]);

    $totaldetalle = $totaldetalle-$val->abono;
}

return $cuentacobrar;
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



      if($cabecera->tdocod=='01' || $cabecera->tdocod=='03' || $cabecera->tdocod=='13' || $cabecera->tdocod=='81' || $cabecera->tdocod=='85' || $cabecera->tdocod=='86'){

        foreach ($detalle as $key => $det){
           
          if(!empty($det->IdProducto)){

            $producto = Productos::findOrFail($det->IdProducto);
            
            if(empty($producto->pro_rel)){

              $id_prod = $producto->IdProducto;

            }else{
          
              $id_prod = $producto->pro_rel;

            }

            DB::tABLE('movimientos_productos')->where('IdCpe_cabecera',$cabecera->IdCpe_cabecera)->delete();
            
            $mov_cal_stock = new Almacen();
            $mov_cal_stock->movimiento_calcular_stock($det->IdProducto_rel,$cabecera->id_almacen);
  

          } 
        } 

      }elseif($cabecera->tdocod=='82'){

        foreach ($detalle as $key => $det) {
            
          if(!empty($det->IdProducto)){

            $producto = Productos::findOrFail($det->IdProducto);
            
            if(empty($producto->pro_rel)){

              $id_prod = $producto->IdProducto;

            }else{
          
              $id_prod = $producto->pro_rel;

            }

         
            DB::tABLE('movimientos_productos')->where('IdCpe_cabecera',$cabecera->IdCpe_cabecera)->delete();
            
            $mov_cal_stock = new Almacen();
            $mov_cal_stock->movimiento_calcular_stock($det->IdProducto_rel,$cabecera->id_almacen);
         
         
         
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
          $cabecera->estado ='ANULADO';
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
  
      }elseif($cabecera->tdocod =='81'){
          if($request->ajax()) {
          return response()->json(['mensaje' => 'salidas']);
        }
  
      }elseif($cabecera->tdocod =='82'){
          if($request->ajax()) {
          return response()->json(['mensaje' => 'ingresos']);
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
        ->select('IdCpe_cabecera','id_empresa_negocio','topcod','id_almacen','serdoc','numdoc','tdocod','ccafem')
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

        //DOCUMENTO REFERENCIA
        $cabecera->serie_ref = $docmod->serdoc;
        $cabecera->num_ref = $docmod->numdoc;
        $cabecera->tdocod_ref  = $docmod->tdocod;
        $cabecera->ccafem_ref = $docmod->ccafem;
     
        $nota = $serdoc.'-'.$numdoc;
        $cabfactura = cpe_cabecera::findOrFail($IdCpe_cabecera);
        $cabfactura->ccanot = $nota;
        
        if($tipnot=='01'){
           $cabfactura->ccabaj = now()->format('Y-m-d');
        }
        
       
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
          
          // =========================================================
          // COPIAR MEDIO DE PAGO DEL COMPROBANTE ORIGINAL
          // =========================================================
          $medio_pago_original = DB::table('venta_medio_pago')
                                  ->where('IdCpe_cabecera', $IdCpe_cabecera)
                                  ->first();

          if (!empty($medio_pago_original)) {
              DB::table('venta_medio_pago')->insert([
                  'IdCpe_cabecera' => $codfact, 
                  'id_med_pag'     => $medio_pago_original->id_med_pag,
                  'monto'          => $total, 
                  'id_turno'       => $medio_pago_original->id_turno
              ]);
          }
          // =========================================================
          
                 
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
              $detalle->cdevun = $puni[$index];
              $detalle->cdepuni = $puni[$index];
              $detalle->cdeigv = $vigv[$index];
              $detalle->tigcod = $tigv[$index];
              $detalle->cdepve = $vtot[$index];
              $detalle->cdevve = $vtot[$index];
              $detalle->save();

                if($tipnot=='01' && !empty($codpro[$index])){

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
    

      

        self::generar_codigo_movimiento($codfact);

        $nom_arch = $cabecera->generar_xml_nota($codfact);
        $cabecera->generar_nuevo_qr($codfact);
        $cabecera->generarpdfgeneral($codfact);
       
        if($empresa->tipo_envio =='1'){
            $cabecera->enviar_sunat($codfact);
        }

        return Redirect::to('/SisFact')->with('success');

   
    }


/*---------------------------------------------------------------FIN REGISTRO NOTA DE CREDITO------------------------------------------*/


 /*public function generar_xml_resumen_diario(Request $request){

        $fechacomprobantes = $request->get('fecresumen');
        $tipo = $request->get('tipo'); // '1' para enviar, '3' para anular
        $fechageneracion = now()->format('Y-m-d');

        $cabeceras = collect(); // Inicializamos una colección vacía

        // Lógica para obtener los comprobantes según el tipo de acción
        if($tipo == '1'){ // Enviar Comprobantes (pendientes de envío)
            $cabeceras = DB::tABLE('cpe_cabecera')
                ->where('tdocod','03') // Boletas de Venta
                ->where('ccafem','=', $fechacomprobantes)
                ->where('enviado','0') // Solo los que no han sido enviados aún
                ->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)
                ->get();

            if($cabeceras->isEmpty()){
                return response()->json(['mensaje' => 'No hay comprobantes tipo Boleta de Venta (03) pendientes de envío para la fecha seleccionada.']);
            }

        } elseif($tipo == '3'){ // Anular Comprobantes (ya anulados en el sistema pero no en SUNAT)
            $cabeceras = DB::tABLE('cpe_cabecera')
                ->where('tdocod','03') // Boletas de Venta
                ->where('ccafem','=', $fechacomprobantes)
                ->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)
                ->where('ccabaj','!=', '') // Que tengan fecha de baja (ya anulados localmente)
                ->get();

            if($cabeceras->isEmpty()){
                return response()->json(['mensaje' => 'No hay comprobantes tipo Boleta de Venta (03) anulados localmente para la fecha seleccionada.']);
            }
        } else {
            // Manejar caso de tipo no válido, aunque tu select solo tiene 1 y 3
            return response()->json(['mensaje' => 'Tipo de acción no válido.']);
        }
        
        // Si llegamos hasta aquí, significa que $cabeceras no está vacía y podemos proceder a generar el XML.

        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
        $empresa = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

        // Incrementa el correlativo del resumen diario
        $ResnuEmpresa = $empresa->ResnuEmpresa + 1;
        $actualizarnum = Empresa::findOrFail(Auth::user()->IdEmpresa);
        $actualizarnum->ResnuEmpresa = $ResnuEmpresa;
        $actualizarnum->update();

        $see = self::configuracion();

        // Emisor
        $address = new \Greenter\Model\Company\Address();
        $address->setUbigueo($sucursal->ubigeo)
            ->setDepartamento($sucursal->departamento)
            ->setProvincia($sucursal->provincia)
            ->setDistrito($sucursal->distrito)
            ->setUrbanizacion('-')
            ->setDireccion($sucursal->direccion);

        $company = new \Greenter\Model\Company\Company();
        $company->setRuc($empresa->IdEmpresa)
            ->setRazonSocial($empresa->NomEmpresa)
            ->setNombreComercial('')
            ->setAddress($address);

        $comprobantes_detalles = []; // Renombrada para mayor claridad
        foreach ($cabeceras as $cabecera) {
            $detail = new SummaryDetail();
            $detail->setTipoDoc($cabecera->tdocod)
                ->setSerieNro($cabecera->serdoc.'-'.$cabecera->numdoc)
                ->setEstado($tipo) // '1' para adición, '3' para baja
                ->setClienteTipo($cabecera->tdicod)
                ->setClienteNro($cabecera->ccandi)
                ->setTotal($cabecera->ccaitv)
                ->setMtoOperGravadas($cabecera->ccatvg)
                ->setMtoOperInafectas(0.00)
                ->setMtoOperExoneradas($cabecera->ccatexo)
                ->setMtoOperExportacion(0.00)
                ->setMtoOtrosCargos(0.00)
                ->setMtoIGV($cabecera->ccaigv);

            $comprobantes_detalles[] = $detail;
        }

        // Asegúrate de que $comprobantes_detalles no esté vacío al pasarlo a setDetails
        if (empty($comprobantes_detalles)) {
             return response()->json(['mensaje' => 'No se encontraron detalles de comprobantes válidos para generar el resumen.']);
        }


        $sum = new \Greenter\Model\Summary\Summary();
        $sum->setFecGeneracion(new \DateTime($fechacomprobantes))
            ->setFecResumen(new \DateTime($fechageneracion))
            ->setCorrelativo($ResnuEmpresa)
            ->setCompany($company)
            ->setDetails($comprobantes_detalles);


        $builder = new \Greenter\Xml\Builder\SummaryBuilder();
        $xml = $builder->build($sum);

        $nom_xml = $sum->getName();

        file_put_contents(public_path().'/xml/'.$nom_xml.'.xml', $xml);

        $firmar_xml = new \MasterSoft\cpe_cabecera(); // Usar el namespace completo o el alias
        $firmar_xml->firmar_xml($nom_xml);

        $resumen = new \MasterSoft\resumenes(); // Usar el namespace completo o el alias
        $resumen->res_fec_com = $fechacomprobantes;
        $resumen->res_fec_gen = $fechageneracion;
        $resumen->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $resumen->res_tip = $tipo;
        $resumen->nom_arch = $nom_xml;
        $resumen->tip_res_com = 'GRD'; // Grupo de Resumen Diario para Boletas (GRD)
        $resumen->save();

        foreach ($cabeceras as $comp) {
            $cpe = \MasterSoft\cpe_cabecera::findOrFail($comp->IdCpe_cabecera);
            $cpe->res_id = $resumen->res_id;
            $cpe->enviado = '1';
            $cpe->ccacodsun = '0'; // Marcar como aceptado temporalmente hasta recibir el CDR
            $cpe->update();
        }

        self::enviar_sunat_resumen_comunicacion($resumen->res_id);

        return response()->json(['mensaje' => 'Resumen generado y enviado a SUNAT con éxito.']);

    }*/

   /* public function generar_xml_resumen_diario(Request $request){

    $fechacomprobantes = $request->get('fecresumen');
    $tipo = $request->get('tipo'); // '1' para enviar, '3' para anular
    $fechageneracion = now()->format('Y-m-d');

    $cabeceras = collect(); // Inicializamos una colección vacía

    // Lógica para obtener los comprobantes según el tipo de acción
    if($tipo == '1'){ // Enviar Comprobantes (pendientes de envío)
        $cabeceras = DB::tABLE('cpe_cabecera')
            ->where('tdocod','03') // Boletas de Venta
            ->where('ccafem','=', $fechacomprobantes)
            ->where('enviado','0') // Solo los que no han sido enviados aún
            ->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)
            ->get();

        if($cabeceras->isEmpty()){
            return response()->json(['mensaje' => 'No hay comprobantes tipo Boleta de Venta (03) pendientes de envío para la fecha seleccionada.']);
        }

    } elseif($tipo == '3'){ // Anular Comprobantes (ya anulados en el sistema pero no en SUNAT)
        $cabeceras = DB::tABLE('cpe_cabecera')
            ->where('tdocod','03') // Boletas de Venta
            ->where('ccafem','=', $fechacomprobantes)
            ->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)
            ->where('ccabaj','!=', '') // Que tengan fecha de baja (ya anulados localmente)
            ->get();

        if($cabeceras->isEmpty()){
            return response()->json(['mensaje' => 'No hay comprobantes tipo Boleta de Venta (03) anulados localmente para la fecha seleccionada.']);
        }
    } else {
        // Manejar caso de tipo no válido, aunque tu select solo tiene 1 y 3
        return response()->json(['mensaje' => 'Tipo de acción no válido.']);
    }
    
    // Si llegamos hasta aquí, significa que $cabeceras no está vacía y podemos proceder a generar el XML.

    $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
    $empresa = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

    // 1. Incrementa el correlativo del resumen diario (DEBE HACERSE AQUÍ PARA EL XML)
    $ResnuEmpresa = $empresa->ResnuEmpresa + 1;
    $actualizarnum = Empresa::findOrFail(Auth::user()->IdEmpresa);
    $actualizarnum->ResnuEmpresa = $ResnuEmpresa;
    $actualizarnum->update();

    $see = self::configuracion();

    // Emisor (código Greenter, queda igual)
    $address = new \Greenter\Model\Company\Address();
    $address->setUbigueo($sucursal->ubigeo)
        ->setDepartamento($sucursal->departamento)
        ->setProvincia($sucursal->provincia)
        ->setDistrito($sucursal->distrito)
        ->setUrbanizacion('-')
        ->setDireccion($sucursal->direccion);

    $company = new \Greenter\Model\Company\Company();
    $company->setRuc($empresa->IdEmpresa)
        ->setRazonSocial($empresa->NomEmpresa)
        ->setNombreComercial('')
        ->setAddress($address);

    $comprobantes_detalles = []; 
    foreach ($cabeceras as $cabecera) {
        $detail = new SummaryDetail();
        $detail->setTipoDoc($cabecera->tdocod)
            ->setSerieNro($cabecera->serdoc.'-'.$cabecera->numdoc)
            ->setEstado($tipo) // '1' para adición, '3' para baja
            ->setClienteTipo($cabecera->tdicod)
            ->setClienteNro($cabecera->ccandi)
            ->setTotal($cabecera->ccaitv)
            ->setMtoOperGravadas($cabecera->ccatvg)
            ->setMtoOperInafectas(0.00)
            ->setMtoOperExoneradas($cabecera->ccatexo)
            ->setMtoOperExportacion(0.00)
            ->setMtoOtrosCargos(0.00)
            ->setMtoIGV($cabecera->ccaigv);

        $comprobantes_detalles[] = $detail;
    }

    if (empty($comprobantes_detalles)) {
         return response()->json(['mensaje' => 'No se encontraron detalles de comprobantes válidos para generar el resumen.']);
    }

    $sum = new \Greenter\Model\Summary\Summary();
    $sum->setFecGeneracion(new \DateTime($fechacomprobantes))
        ->setFecResumen(new \DateTime($fechageneracion))
        ->setCorrelativo($ResnuEmpresa)
        ->setCompany($company)
        ->setDetails($comprobantes_detalles);

    $builder = new \Greenter\Xml\Builder\SummaryBuilder();
    $xml = $builder->build($sum);
    $nom_xml = $sum->getName();
    file_put_contents(public_path().'/xml/'.$nom_xml.'.xml', $xml);

    $firmar_xml = new \MasterSoft\cpe_cabecera(); 
    $firmar_xml->firmar_xml($nom_xml);

    // 2. Guardamos el registro del resumen (si falla el envío, lo eliminaremos)
    $resumen = new \MasterSoft\resumenes(); 
    $resumen->res_fec_com = $fechacomprobantes;
    $resumen->res_fec_gen = $fechageneracion;
    $resumen->id_empresa_negocio = Auth::user()->id_empresa_negocio;
    $resumen->res_tip = $tipo;
    $resumen->nom_arch = $nom_xml;
    $resumen->tip_res_com = 'GRD'; // Grupo de Resumen Diario para Boletas (GRD)
    $resumen->save();

    // --- INICIO DE LÓGICA DE ENVÍO Y ACTUALIZACIÓN CONDICIONAL ---
    try {
        // 3. INTENTAR EL ENVÍO A SUNAT
        // Si hay error de red o SUNAT rechaza, esta función lanzará una excepción y saltará al 'catch'.
        self::enviar_sunat_resumen_comunicacion($resumen->res_id);

        // 4. SI LLEGA HASTA AQUÍ, EL ENVÍO FUE EXITOSO
        // SOLO AHORA actualizamos el estado de las Boletas
        foreach ($cabeceras as $comp) {
            $cpe = \MasterSoft\cpe_cabecera::findOrFail($comp->IdCpe_cabecera);
            $cpe->res_id = $resumen->res_id;
            $cpe->enviado = '1';
            $cpe->ccacodsun = '0'; // Marcar como pendiente de respuesta de SUNAT (TICKET enviado)
            $cpe->update();
        }

        return response()->json(['mensaje' => 'Resumen generado y enviado a SUNAT con éxito.']);

    } catch (\Exception $e) {
        // 5. SI FALLA EL ENVÍO (por excepción)
        
        // Revertir el correlativo de la empresa (se incrementó antes)
        try {
            $actualizarnum = Empresa::findOrFail(Auth::user()->IdEmpresa);
            $actualizarnum->ResnuEmpresa = $actualizarnum->ResnuEmpresa - 1;
            $actualizarnum->update();
        } catch (\Exception $e2) {
            // Manejo de error de reversión (opcional: loguear)
        }

        // Eliminar el registro del resumen que se creó temporalmente
        if (isset($resumen) && $resumen->exists) {
            $resumen->delete();
        }
        
        // Las boletas (cabeceras) nunca se actualizaron, su 'enviado' sigue en '0'.

        return response()->json([
            'error' => 'Error al intentar enviar a SUNAT.', 
            'mensaje' => 'El envío falló por problemas de conexión o rechazo. Los comprobantes quedaron pendientes de envío. Detalle: ' . $e->getMessage()
        ], 500); // Código 500 para indicar error en el proceso.
    }
}*/
        public function generar_xml_resumen_diario(Request $request){

    $fechacomprobantes = $request->get('fecresumen');
    $tipo = $request->get('tipo'); // '1' para enviar (adicionar), '3' para anular (baja)
    $fechageneracion = now()->format('Y-m-d');

    // ----------------------------------------------------
    // INICIO DE LA NUEVA VALIDACIÓN (Paso 1)
    // ----------------------------------------------------

    // Buscar si ya existe un resumen ACEPTADO (con ticket) para esta fecha y tipo
    $resumen_existente = \MasterSoft\resumenes::where('res_fec_com', $fechacomprobantes)
        ->where('res_tip', $tipo)
        ->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)
        ->whereNotNull('res_ticket') // Buscamos si ya tiene un ticket (fue enviado a SUNAT)
        ->first();

    if ($resumen_existente) {
        // Si ya hay un resumen con ticket para esta fecha y tipo, no se permite el reenvío.
        $accion = ($tipo == '1') ? 'ADICIÓN' : 'ANULACIÓN';
        
        return response()->json([
            'mensaje' => "El Resumen de {$accion} para la fecha {$fechacomprobantes} ya fue **PRESENTADO** a SUNAT con el ticket: {$resumen_existente->res_ticket}.",
            'alerta' => true // Agregamos un indicador para que el frontend muestre la alerta
        ]);
    }

    // ----------------------------------------------------
    // FIN DE LA NUEVA VALIDACIÓN
    // ----------------------------------------------------

    $cabeceras = collect(); // Inicializamos una colección vacía

    // Lógica para obtener los comprobantes según el tipo de acción
    if($tipo == '1'){ // Enviar Comprobantes (pendientes de envío)
        $cabeceras = DB::tABLE('cpe_cabecera')
            ->where('tdocod','03') // Boletas de Venta
            ->where('ccafem','=', $fechacomprobantes)
            ->where('enviado','0') // Solo los que no han sido enviados aún
            ->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)
            ->get();

        if($cabeceras->isEmpty()){
            return response()->json(['mensaje' => 'No hay comprobantes tipo Boleta de Venta (03) pendientes de envío para la fecha seleccionada.']);
        }

    } elseif($tipo == '3'){ // Anular Comprobantes (ya anulados en el sistema pero no en SUNAT)
        $cabeceras = DB::tABLE('cpe_cabecera')
            ->where('tdocod','03') // Boletas de Venta
            ->where('ccafem','=', $fechacomprobantes)
            ->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)
            ->where('ccabaj','!=', '') // Que tengan fecha de baja (ya anulados localmente)
            ->get();

        if($cabeceras->isEmpty()){
            return response()->json(['mensaje' => 'No hay comprobantes tipo Boleta de Venta (03) anulados localmente para la fecha seleccionada.']);
        }
    } else {
        // Manejar caso de tipo no válido, aunque tu select solo tiene 1 y 3
        return response()->json(['mensaje' => 'Tipo de acción no válido.']);
    }
    
    // Si llegamos hasta aquí, significa que $cabeceras no está vacía y podemos proceder a generar el XML.

    $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
    $empresa = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

    // 1. Incrementa el correlativo del resumen diario (DEBE HACERSE AQUÍ PARA EL XML)
    $ResnuEmpresa = $empresa->ResnuEmpresa + 1;
    $actualizarnum = Empresa::findOrFail(Auth::user()->IdEmpresa);
    $actualizarnum->ResnuEmpresa = $ResnuEmpresa;
    $actualizarnum->update();

    $see = self::configuracion();

    // Emisor (código Greenter, queda igual)
    $address = new \Greenter\Model\Company\Address();
    $address->setUbigueo($sucursal->ubigeo)
        ->setDepartamento($sucursal->departamento)
        ->setProvincia($sucursal->provincia)
        ->setDistrito($sucursal->distrito)
        ->setUrbanizacion('-')
        ->setDireccion($sucursal->direccion);

    $company = new \Greenter\Model\Company\Company();
    $company->setRuc($empresa->IdEmpresa)
        ->setRazonSocial($empresa->NomEmpresa)
        ->setNombreComercial('')
        ->setAddress($address);

    $comprobantes_detalles = []; 
    foreach ($cabeceras as $cabecera) {
        $detail = new SummaryDetail();
        $detail->setTipoDoc($cabecera->tdocod)
            ->setSerieNro($cabecera->serdoc.'-'.$cabecera->numdoc)
            ->setEstado($tipo) // '1' para adición, '3' para baja
            ->setClienteTipo($cabecera->tdicod)
            ->setClienteNro($cabecera->ccandi)
            ->setTotal($cabecera->ccaitv)
            ->setMtoOperGravadas($cabecera->ccatvg)
            ->setMtoOperInafectas(0.00)
            ->setMtoOperExoneradas($cabecera->ccatexo)
            ->setMtoOperExportacion(0.00)
            ->setMtoOtrosCargos(0.00)
            ->setMtoIGV($cabecera->ccaigv);

        $comprobantes_detalles[] = $detail;
    }

    if (empty($comprobantes_detalles)) {
         return response()->json(['mensaje' => 'No se encontraron detalles de comprobantes válidos para generar el resumen.']);
    }

    $sum = new \Greenter\Model\Summary\Summary();
    $sum->setFecGeneracion(new \DateTime($fechacomprobantes))
        ->setFecResumen(new \DateTime($fechageneracion))
        ->setCorrelativo($ResnuEmpresa)
        ->setCompany($company)
        ->setDetails($comprobantes_detalles);

    $builder = new \Greenter\Xml\Builder\SummaryBuilder();
    $xml = $builder->build($sum);
    $nom_xml = $sum->getName();
    file_put_contents(public_path().'/xml/'.$nom_xml.'.xml', $xml);

    $firmar_xml = new \MasterSoft\cpe_cabecera(); 
    $firmar_xml->firmar_xml($nom_xml);

    // 2. Guardamos el registro del resumen (si falla el envío, lo eliminaremos)
    $resumen = new \MasterSoft\resumenes(); 
    $resumen->res_fec_com = $fechacomprobantes;
    $resumen->res_fec_gen = $fechageneracion;
    $resumen->id_empresa_negocio = Auth::user()->id_empresa_negocio;
    $resumen->res_tip = $tipo;
    $resumen->nom_arch = $nom_xml;
    $resumen->tip_res_com = 'GRD'; // Grupo de Resumen Diario para Boletas (GRD)
    $resumen->save();

    // --- LÓGICA DE ENVÍO Y ACTUALIZACIÓN CONDICIONAL ---
    try {
        // 3. INTENTAR EL ENVÍO A SUNAT
        self::enviar_sunat_resumen_comunicacion($resumen->res_id);

        // 4. SI LLEGA HASTA AQUÍ, EL ENVÍO FUE EXITOSO
        // SOLO AHORA actualizamos el estado de las Boletas
        foreach ($cabeceras as $comp) {
            $cpe = \MasterSoft\cpe_cabecera::findOrFail($comp->IdCpe_cabecera);
            $cpe->res_id = $resumen->res_id;
            $cpe->enviado = '1';
            $cpe->ccacodsun = '0'; // Marcar como pendiente de respuesta de SUNAT (TICKET enviado)
            $cpe->update();
        }

        return response()->json(['mensaje' => 'Resumen generado y enviado a SUNAT con éxito.']);

    } catch (\Exception $e) {
        // 5. SI FALLA EL ENVÍO (por excepción)
        
        // Revertir el correlativo de la empresa (se incrementó antes)
        try {
            $actualizarnum = Empresa::findOrFail(Auth::user()->IdEmpresa);
            $actualizarnum->ResnuEmpresa = $actualizarnum->ResnuEmpresa - 1;
            $actualizarnum->update();
        } catch (\Exception $e2) {
            // Manejo de error de reversión (opcional: loguear)
        }

        // Eliminar el registro del resumen que se creó temporalmente
        if (isset($resumen) && $resumen->exists) {
            $resumen->delete();
        }
        
        return response()->json([
            'error' => 'Error al intentar enviar a SUNAT.', 
            'mensaje' => 'El envío falló por problemas de conexión o rechazo. Los comprobantes quedaron pendientes de envío. Detalle: ' . $e->getMessage()
        ], 500); 
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


    //if($empresa->tipo_envio =='1'){
    self::enviar_sunat_resumen_comunicacion($cabecera->res_id);
   // }


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
    ->setFecGeneracion(new \DateTime($baja->res_fec_com))
    ->setFecComunicacion(new \DateTime($baja->res_fec_gen))
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

    //if($empresa->tipo_envio =='1'){


    self::enviar_sunat_resumen_comunicacion($cabecera->res_id);

   // }


    return $nom_xml;

}


/*---------------------------------------------------------------FIN GENERAR XML ----------------------------------------------*/



/*public function enviar_sunat_resumen_comunicacion($res_id){

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

}*/

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
    
    // Usamos un try-catch aquí para capturar errores críticos de conexión (como no hay internet)
    try {
        $result = $sender->send($resumen->nom_arch, $xml);
    } catch (\SoapFault $e) {
        // Error de conexión, timeout, o servicio no disponible
        throw new \Exception('Fallo de conexión al Web Service de SUNAT: ' . $e->getMessage());
    } catch (\Exception $e) {
        // Otro tipo de excepción (ej. cURL error)
        throw new \Exception('Error al intentar comunicar con SUNAT: ' . $e->getMessage());
    }

    if (!$result->isSuccess()) {
        $resumen_act = resumenes::findOrFail($res_id);
        $resumen_act->res_est = $result->getError()->getMessage();
        $resumen_act->res_cod_est = $result->getError()->getCode();
        $resumen_act->update();
        
        // Error de SUNAT (ej. datos incorrectos), lanzamos excepción para revertir los cambios
        throw new \Exception('SUNAT rechazó el envío: ' . $result->getError()->getMessage());
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
 $resumen_act->res_est = $status->getError()->getMessage();
 $resumen_act->res_cod_est = $status->getError()->getCode();
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
    ->leftjoin('tipo_nota_credito','tipo_nota_credito.nccod','cab.tipnot')
    ->where('IdCpe_cabecera','=',$pedido)
    ->first();

    $data_cajero = DB::tABLE('users')->where('IdUsuario',$cabecera->IdUsuario)->first();

    $doc_ref = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$cabecera->IdCpe_cabecera_ref)->first();

    $totalletras= MontoLetras::convertir(number_format($cabecera->ccaitv,'2','.',''),$cabecera->monnom,'Centimos');

    $detalle = cpe_detalle::where('IdCpe_cabecera',$pedido)
    ->leftjoin('unidad_medida as umed','cpe_detalle.umecod','=','umed.umecod')
    ->get();

    $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();


    $medios = DB::tABLE('venta_medio_pago')
    ->join('medios_pagos','medios_pagos.id_med_pag','venta_medio_pago.id_med_pag')
    ->where('IdCpe_cabecera',$pedido)
    ->get();

    $numdoc = $cabecera->numdoc;
    $qrfile =  'QR-'.$cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.png'; 
    $imgqr = "/qr/".$qrfile;
    $codbar = $cabecera->serdoc.$cabecera->numdoc;

    $tipo_documento_info = DB::table('tipo_documento')->where('tdocod', $cabecera->tdocod)->first();
    $tdodes_display = $tipo_documento_info->tdodes ?? 'DOCUMENTO';

    return view('formatos_comprobantes.ticket_factura',compact('doc_ref','cabecera','totalletras','detalle','mesa','empresa','sucursal','imgqr','codbar','data_cajero','medios','tdodes_display'));





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

public function enviar_servidor($codfact){

    $cabecera = cpe_cabecera::findOrFail($codfact);
    $cabecera->consultar_cdr($codfact);

    $nom_arch = Auth::user()->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$cabecera->numdoc;

    $estado = cpe_cabecera::findOrFail($codfact);


    try{

        //PDF

     $pdffile = public_path().'/pdf/'.$nom_arch.'.pdf';
     $xmlfile = public_path().'/xml/'.$nom_arch.'.xml';
     $cdrfile = public_path().'/cdr/R-'.$nom_arch.'.zip';

     $nom_pdf = $nom_arch.'.pdf';
     $nom_xml = $nom_arch.'.xml';
     $nom_cdr = 'R-'.$nom_arch.'.zip';

     if(!file_exists($pdffile)){
        $cabecera->generarpdfgeneral($cdofact);
    }

    if(!file_exists($xmlfile)){
        if($cabecera->tdocod=='01' || $cabecera->tdocod='03'){
            $cabecera->generar_xml_boleta_factura($codfact);
        }elseif($cabecera->tdocod=='07' || $cabecera->tdocod='08'){
            $cabecera->generar_xml_nota($codfact);
        }
        
    }

    if($estado->ccacodsun=='0'){

            //env('FTP_ROOT','/home/comprobantes/'.Auth::user()->IdEmpresa.'/pdf/');
        // Config::set('ftm.root', '/home/comprobantes/'.Auth::user()->IdEmpresa.'/pdf/');
        \Storage::disk('sftp')->put($nom_pdf,fopen($pdffile,'r+'));
      //XML
       // env('FTP_ROOT','/home/comprobantes/'.Auth::user()->IdEmpresa.'xml');
        \Storage::disk('sftp')->put($nom_xml,fopen($xmlfile,'r+'));

      //CDR
        // env('FTP_ROOT','/home/comprobantes/'.Auth::user()->IdEmpresa.'zip');
        \Storage::disk('sftp')->put($nom_cdr,fopen($cdrfile,'r+'));
        
    }

    

}catch(\Exception $e){

    dd($e);
}

return Redirect::to('/facturacionelectronica');

}




public function autorizarmodificarprecio(Request $request,$codigo="",$usuario){


    $user = User::findOrFail($usuario);
    
    $usuariobuscar = DB::tABLE('users')->where('IdUsuario',$usuario)->count();

    if (Hash::check($codigo,$user->password) && $usuariobuscar > 0) {

        if($request->ajax()) {
          return response()->json(['AUTORIZADO'=>'true','mensaje' => 'AUTORIZADO']);
      }
  }else{

    if($request->ajax()) {
      return response()->json(['AUTORIZADO'=>'false','mensaje' => 'NO TIENE AUTORIZADO']);
  }
}




}




public function registrarcontingencia(Request $request)
{

    $rucemp = trim(Auth::user()->IdEmpresa);
    $empresa = Empresa::findOrFail($rucemp);

    $procesos = $request->get('proceso');

      /*  $clientess = DB::tABLE('cliente')->get();

        foreach($clientess as $c){

             $cont_carac = strlen($c->clinum);
            $obt_dig = substr(trim($c->clinum), 0, 2);

      
            if($cont_carac =='11' && ($obt_dig=='10' || $obt_dig=='20' || $obt_dig=='15' || $obt_dig=='17')){

                DB::tABLE('cliente')->where('clicod',$c->clicod)->update(['tdicod','6']);
              
            }
           

        }*/

        $clicod = $request->get('clicod');


        $unidades = $request->get('unid');
        $serie_doc =  $request->get('txt_serdoc');
        $numero_doc = $request->get('txt_numdoc');

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
        $val_uni = $request->get('precio');
        $tdocod = $request->get('tdocod');
        $mon_cuo = $request->get('mon_cuo');
        $adelanto = $request->get('adelanto');
        $fec_cuo = $request->get('fec_cuo');
        $cot = $request->get('id');
        $descuento = $request->get('desc');
        $topcod = '0101';
        $id_almacen_pro = $request->get('id_almacen_pro');

        $cont_carac = strlen($cliruc);
        $obt_dig = substr(trim($cliruc), 0, 2);

        if(empty(trim($serie_doc))){

            return response()->json(['estado'=>'error','mensaje'=>'INGRESAR LA SERIE DEL DOCUMENTO']);

        }

        if(empty(trim($numero_doc))){
            
            return response()->json(['estado'=>'error','mensaje'=>'INGRESAR NUMERO DE DOCUMENTO']);
        }

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
        $bus_ped->amortizacion = $total-$adelanto;
        $bus_ped->update();
    }


}


if(!empty($request->get('id'))){
    $cotizacion = cpe_cabecera::findOrFail($request->get('id'));
    $cotizacion->estado ='ACEPTADO';
    $cotizacion->update();
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

    $cliente = Cliente::Create(['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$request->get('clicor'),'tdicod'=>$tdicod,'telefono'=>$request->get('clitel'),'rucemp'=>Auth::user()->IdEmpresa]);

}elseif($cliruc=='00000000' && ( trim(strtoupper($clinom))=='VENTAALPORTADOR' or trim(strtoupper($clinom)=='VARIOS'))){

    $cliente = Cliente::UpdateOrCreate(['clinum'=>$cliruc],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$request->get('clicor'),'tdicod'=>$tdicod,'telefono'=>$request->get('clitel'),'rucemp'=>Auth::user()->IdEmpresa]); 

}else{

    $cliente = Cliente::UpdateOrCreate(['clinum'=>$cliruc],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$request->get('clicor'),'tdicod'=>$tdicod,'telefono'=>$request->get('clitel'),'rucemp'=>Auth::user()->IdEmpresa]); 

}




$buscarplaca = DB::tABLE('tipos_vehiculos')->where('placa',$request->get('placa'))->first();


        //BUSCAR PLACA

if(empty($buscarplaca)){


  $vehiculos = new tipos_vehiculos;
  $vehiculos->IdEmpresa = trim(Auth::user()->IdEmpresa);
  $vehiculos->clicod = $cliente->clicod;
  $vehiculos->observaciones = $request->get('observaciones');
  $vehiculos->id_empresa_negocio = Auth::user()->id_empresa_negocio;
  $vehiculos->save();

}else{


    $vehiculos =  tipos_vehiculos::findOrFail($buscarplaca->id_tipo_vehiculo);
    $vehiculos->IdEmpresa = trim(Auth::user()->IdEmpresa);
    $vehiculos->clicod = $cliente->clicod;
    $vehiculos->observaciones = $request->get('observaciones');
    $vehiculos->id_empresa_negocio = Auth::user()->id_empresa_negocio;
    $vehiculos->update();

}


$cabecera = new cpe_cabecera;
$cabecera->tdocod = $tdocod;
$cabecera->topcod = $topcod;
$cabecera->ccafem = $fecemi;
$cabecera->adelanto = $adelanto;
        //$cabecera->saldo = '0';
$cabecera->amortizacion = $total-$adelanto;
$cabecera->observaciones = $request->get('observaciones');
$cabecera->id_almacen = $id_almacen;
$cabecera->cod_tip_ope = '01';

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
//$cabecera->ped_ref = $request->get('ped_id');
$cabecera->moncod = $mondoc;
$cabecera->direccion = $clidir;
$cabecera->clicorcli = $clicor;
$cabecera->ade_pro = $ade_pro;
$cabecera->contingencia = '1';
$cabecera->sal_pro = $sal_pro;
$cabecera->cre_dia_id = $estadopago;
$cabecera->IdUsuario_ven = $id_vendedor;
$cabecera->vendedor = $bus_ven->name.' '.$bus_ven->apeusu;

if(!empty($bus_cot)){
  $cabecera->referencia = $bus_cot->serdoc.'-'.$bus_cot->numdoc;
}

$cabecera->tipcambio = $camdoc;

if($sucursal->tip_igv_pred =='10'){
    $cabecera->ccatvg =  $total/1.105;
    $cabecera->ccaigv =  $total-$total/1.105;
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

$cabecera->serdoc= $serie_doc;
$cabecera->numdoc = $numero_doc;



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
                //  $movimiento->importe = $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)*1.105));

       $movimiento->importe = $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)));

   }

   $movimiento->estado = '1';
   $movimiento->mov_fecha = $cabecera->ccafem;
   $movimiento->clicod = $cabecera->clicod;
   $movimiento->registro = 'Registrado';

   if($contar==0){
                 // $totalsaldo =  $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)*1.105));

       $totalsaldo =  $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)));

   }else{
                //  $totalsaldo = $buscar->last()->saldo + $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)*1.105));
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

$detalle->cdedes = $detpro[$index];
$detalle->pronomobs = $pronomobs[$index];
$detalle->costo = $codpro->costo;
$detalle->tigcod = $codpro->tigcod;
$detalle->icbper = $codpro->icbper;
$detalle->cpe_det_stock = $stock;
$detalle->desc_mon = $cantidades[$index]*$descuento[$index];
$detalle->id_almacen_pro = $id_almacen;





/*calcular porcentaje de descuento*/


if($codpro->tigcod =='10'){

  $preciouni = $puni[$index]-$descuento[$index];;
  $valoruni = ($puni[$index]/1.105)-($descuento[$index]/1.105);
  $valorunitario = $val_uni[$index]/1.105;

  $valorsubtotal = $vtot[$index]/1.105;
  $valortotal = $vtot[$index];

}elseif($codpro->tigcod=='20'){

  $preciouni = $puni[$index]-$descuento[$index];
  $valoruni = $puni[$index]-$descuento[$index];
  $valorunitario = $val_uni[$index];

  $valorsubtotal = $vtot[$index];
  $valortotal = $vtot[$index];
}

if($sucursal->tipo_desc=='1'){
    $desc_mon = $descuento[$index];
    $desc_por = ($descuento[$index]*100)/$val_uni[$index];
}elseif($sucursal->tipo_desc=='2'){
    $desc_por = $descuento[$index];
    $desc_mon = $val_uni[$index]*($descuento[$index]/100);
}


$valorigvtotal =  $valortotal-$valorsubtotal;



/*FIN CALCULAR DESCUENTO*/
$detalle->valor_unitario = $valorunitario;
$detalle->por_des = $desc_por;
           // $detalle->desc_mon = $desc_mon;
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
    'IdProducto'=>$id,
    'precio'=>$preciouni,
    'cantidad'=>$cantidades[$index]*$codpro->factor,
    'costo'=>$codpro->costo,
    'cliente'=>$cabecera->ccanom,
    'descripcion'=>'VENTA',
    'cod_tip_ope'=>'01',
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
  //dd($valoruni);
$movimiento = new movimientos;
$movimiento->mov_fec = $fecha; 
$movimiento->mov_tip = 'E';
$movimiento->mov_mot = 'Venta';
$movimiento->cantidad = $cantidades[$index];
$movimiento->unidad = $codpro->umecod;
            //$movimiento->comprobante = $sercomp.'-'.$numdoc;
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
      $valoruni = $puni[$index]/1.105;
      $valorunitario = $puni[$index]/1.105;

      $valorsubtotal = $vtot[$index]/1.105;
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


if($empresa->tipo_envio =='1'){
 if($tdocod =='01' || $tdocod=='03'){
    $cabecera->enviar_sunat($codfact);
}
}




return response()->json(['estado'=>'success','codfact' =>$codfact,'tdocod'=>$tdocod,'mensaje'=>'Comprobante Emitido','numero'=>$numero_doc]);


}


public function actualizarcontingencia(Request $request)
{

    $procesos = $request->get('proceso');

    $tdicod = $request->get('tdicod');
    $serie_doc = $request->get('txt_serdoc');
    $numero_doc = $request->get('txt_numdoc');
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
    $mon_cuo = $request->get('mon_cuo');
    $fec_cuo = $request->get('fec_cuo');
    $val_uni = $request->get('precio');

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
$cliente = Cliente::UpdateOrCreate(['clinum'=>$cliruc],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$clicor,'rucemp'=>$rucemp,'tdicod'=>$tdicod,'telefono'=>$request->get('clitel'),'clifecnac'=>$request->get('clifecnac'),'mes_nac'=>$mes]);


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
$cabecera->serdoc = $serie_doc;
$cabecera->numdoc = $numero_doc;
$cabecera->ccanom = $clinom;
//$cabecera->ped_ref = $request->get('ped_id');
$cabecera->moncod = $mondoc;
$cabecera->direccion = $clidir;
$cabecera->cre_dia_id = $estadopago;
$cabecera->clicorcli = $clicor;

$cabecera->guia_remision = $request->get('guia_remision');
$cabecera->placa  = $request->get('placa_comp');
$cabecera->cod_tip_ope = '01';

if(!empty($bus_cot)){
  $cabecera->referencia = $bus_cot->serdoc.'-'.$bus_cot->numdoc;
}

$cabecera->tipcambio = $camdoc;
if($sucursal->tip_igv_pred =='10'){
    $cabecera->ccatvg =  $total/1.105;
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
        $movimiento->mov_num_doc = $serie_doc.'-'.$numero_doc;
        $movimiento->cuen_ban_id = $cuen_ban_id;
        $movimiento->IdUsuario = Auth::user()->IdUsuario;
        $movimiento->id_turno = Auth::user()->id_turno;
                        // $movimiento->mov_num_oper = $request->get('mov_num_oper');
        if($comision ==1){
          $movimiento->importe = $cabecera->totalcontado;
      }else{
          $movimiento->importe = $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)*1.105)); 
      }

      $movimiento->estado = '1';
      $movimiento->mov_fecha = $cabecera->ccafem;
      $movimiento->clicod = $cabecera->clicod;
      $movimiento->registro = 'Registrado';

      if($contar==0){
          $totalsaldo =  $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)*1.105));
      }else{
          $totalsaldo = $buscar->last()->saldo + $cabecera->totalcontado-($cabecera->totalcontado*(($comision/100)*1.105));
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
    $desc_por = ($descuento[$index]*100)/$val_uni[$index];
}elseif($sucursal->tipo_desc=='2'){
    $desc_por = $descuento[$index];
    $desc_mon = $val_uni[$index]*($descuento[$index]/100);
}

if($codpro->tigcod =='10'){

  $preciouni = $puni[$index];
  $valoruni = $puni[$index]/1.105;
  $valorunitario = $val_uni[$index]/1.105;

  $valorsubtotal = $vtot[$index]/1.105;
  $valortotal = $vtot[$index];

}elseif($codpro->tigcod=='20'){

  $preciouni = $puni[$index];
  $valoruni = $puni[$index];
  $valorunitario = $val_uni[$index];

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
    'IdProducto'=>$id,
    'precio'=>$preciouni,
    'cantidad'=>$cantidades[$index]*$codpro->factor,
    'costo'=>$codpro->costo,
    'mov_cab_id'=>'',
    'stock'=>$stock,
    'cliente'=>$cabecera->ccanom,
    'IdProducto_rel'=>$id_prod,
    'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
    'com_cab_id'=>'',
    'stock_inicial'=>$sto_ini,
    'serie'=>$serie_doc,
    'numero'=>$numero_doc,
    'tdocod'=>$cabecera->tdocod,
    'descripcion'=>'VENTA',
    'cod_tip_ope'=>'01',
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
              $valoruni = $puni[$index]/1.105;
              $valorunitario = $puni[$index]/1.105;

              $valorsubtotal = $vtot[$index]/1.105;
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
    $buscuentacobrar = DB::tABLE('cuentas_cobrar')->where('IdCpe_cabecera',$codfact)->get();

    if(count($buscuentacobrar)>'0'){
      self::actualizarcuentascobrar($codfact);
  }else{
    self::registrarcuentascobrar($codfact);
}


}





return response()->json(['estado'=>'success','codfact' =>$codfact,'tdocod'=>$tdocod,'mensaje'=>'Comprobante Emitido']);


}

public function descargar_comprobante($nombre){




  $rutpdfile = '/home/comprobantes/';
     // $rutxmlfile = public_path().'/xml/';
     // $rutcdrfile = public_path().'/cdr/';

  $file= $rutpdfile.$nombre;
     // $xml= $rutxmlfile.$codfact.'.xml';
    //  $cdr= $rutcdrfile.'R-'.$codfact.'.zip';


  if(file_exists($file))
  {


    $headers = array(
      'Content-Type: application/pdf',
  );

    return response()->download($file);

}



}


public function enviar_comprobante_servidor($codfact){

    $cabecera = cpe_cabecera::findOrFail($codfact);
    $cabecera->consultar_cdr($codfact);

    $nom_arch = Auth::user()->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$cabecera->numdoc;

    $estado = cpe_cabecera::findOrFail($codfact);

    $pdffile = public_path().'/pdf/'.$nom_arch.'.pdf';
    $xmlfile = public_path().'/xml/'.$nom_arch.'.xml';
    $cdrfile = public_path().'/cdr/R-'.$nom_arch.'.zip';

    $nom_pdf = $nom_arch.'.pdf';


    if(!file_exists($pdffile)){
        $cabecera->generar_nuevo_qr($codfact);
        $cabecera->generarpdfgeneral($codfact);
    }


    try{

        //PDF

      $pdffile = public_path().'/pdf/'.$nom_arch.'.pdf';
     // $xmlfile = public_path().'/xml/'.$nom_arch.'.xml';
    //  $cdrfile = public_path().'/cdr/R-'.$nom_arch.'.zip';

      $nom_pdf = $nom_arch.'.pdf';
     // $nom_xml = $nom_arch.'.xml';
    //  $nom_cdr = 'R-'.$nom_arch.'.zip';
      
      



            //env('FTP_ROOT','/home/comprobantes/'.Auth::user()->IdEmpresa.'/pdf/');
        // Config::set('ftm.root', '/home/comprobantes/'.Auth::user()->IdEmpresa.'/pdf/');
      \Storage::disk('sftp')->put($nom_pdf,fopen($pdffile,'r+'));
      //XML
       // env('FTP_ROOT','/home/comprobantes/'.Auth::user()->IdEmpresa.'xml');
     //   \Storage::disk('sftp')->put($nom_xml,fopen($xmlfile,'r+'));

      //CDR
        // env('FTP_ROOT','/home/comprobantes/'.Auth::user()->IdEmpresa.'zip');
      //  \Storage::disk('sftp')->put($nom_cdr,fopen($cdrfile,'r+'));




  }catch(\Exception $e){

    dd($e);
}

return '1';

}

public function enviar_whastapp(Request $request){

    $numero = $request->get('numero');
    $nombre = $request->get('nombre');
    $id = $request->get('id');

    $cpe = new cpe_cabecera;
    $cpe->generarpdfgeneral($id);

   // self::enviar_comprobante_servidor($id);

    return redirect()->away('https://api.whatsapp.com/send?phone=+51'.$numero.'&text=https://ig.holape.app/pdf/'.$nombre);
}

public function pos_movil($codfact=0)
{   


    $ubigeos = DB::table('cat_ubigeo')->get();


    $datos = DB::TABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

    $almacenes = DB::TABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();

    $combustible = DB::TABLE('combustible')->get();

    $marcas = DB::TABLE('marcas')->get();

    $modelos = DB::TABLE('modelos')->get();

    $tecnicos = DB::TABLE('tecnicos')->get();

    $creditos = DB::TABLE('credito_dias')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();

    $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);

    $documentos = DB::table('tipo_documento_identidad')->where('tdiest','=','Activo')->orderby('tdicod')->get();

    $tipos_igv = DB::TABLE('tipo_igv')->get();

    $rucemp = trim(Auth::user()->IdEmpresa);

    $senudoc = DB::table('empresa_negocios')
    ->select('serieguia','numeroguia')
    ->where('IdEmpresa','=',$rucemp)
    ->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)
    ->first(); 

    $motivos = DB::table('motivo_traslado')
    ->orderBy('motivo','asc')->get();

    $modalidades = DB::table('modalidad_traslado')
    ->orderBy('modalidad','asc')->get();

    $mediospagos = DB::TABLE('medios_pagos')->where('medios_pagos.IdEmpresa',Auth::user()->IdEmpresa)->get();

         // consultar tipos de documentos de identidad
    $docidentidad = DB::table('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

        //consultar tipo de documento 
    $doccomprobante = DB::table('tipo_documento')->where('tdoest','=','Activo')->orderBy('tdocod','asc')->get();

         // consultar tipos de monedas
    $monedas = DB::table('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

    $categorias = DB::TABLE('categorias')
      //  ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
    ->orderby('cat_nom','asc')
    ->get();

    $clientes = DB::TABLE('cliente')->where('rucemp',$rucemp)->orderby('clinom','asc')->get();

    $comprobante = DB::TABLE('tipo_documento')->where('ventas','1')->orderby('tdodes','asc')->get();

    $comprobantes = DB::tABLE('tipo_documento')->where('ventas','1')->get();

    $documentos = DB::tABLE('tipo_documento_identidad')->orderby('orden','asc')->get();

    $estadopagos = DB::tABLE('credito_dias')->get();

    $mediospagos = DB::tABLE('medios_pagos')->get();

    $tipodocumento = DB::TABLE('tipo_documento_identidad')->get();



    $unidades = DB::table('unidad_medida')
    ->where('umeest','=','Activo')
    ->orderBy('umecod','asc')->get();

    $vendedores = DB::TABLE('users')
    ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
    ->where('IdEmpresa',Auth::user()->IdEmpresa)
        //->where('role_id','5')
    ->get();

    $mediospagos = DB::TABLE('medios_pagos')->where('medios_pagos.IdEmpresa',Auth::user()->IdEmpresa)->get();

    $sucursal = DB::TABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

    $almacen = DB::TABLE('almacenes')
    ->where('predeterminado','1')
    ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
    ->first();

    $gastos = DB::TABLE('tipo_gastos')->get();

    $users = DB::TABLE('users')
    ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
    ->where('role_id','2')
    ->get();

    $procesos = DB::TABLE('procesos')->get();
    
    return view('empresas.puntosventas.pos_movil',compact('comprobantes','users','codfact','categorias','comprobante','tipodocumento','unidades','unidades','vendedores','creditos','mediospagos','clientes','documentos','datos','monedas','senudoc','motivos','modalidades','docidentidad','doccomprobante','monedas','almacenes','gastos','combustible','marcas','modelos','tecnicos','tipos_igv','empresa','procesos','ubigeos'));
}



public function editar_medio_pago(Request $request,$id){


        $rucemp = Auth::user()->IdEmpresa;

        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);

        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

        $categorias = DB::tABLE('categorias')->get();

        $comprobantes = DB::tABLE('tipo_documento')->where('ventas','1')->get();

        $documentos = DB::tABLE('tipo_documento_identidad')->orderby('orden','asc')->get();

        $estadopagos = DB::tABLE('credito_dias')->get();

        $mediospagos = DB::tABLE('medios_pagos')->get();

        $mozos = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        //->where('role_id','8')
        ->get();

        $cabecera = DB::tABLE('cpe_cabecera')
        ->where('IdCpe_cabecera',$id)
        ->first();

        $detalle = DB::tABLE('cpe_detalle')
        ->where('IdCpe_cabecera',$cabecera->IdCpe_cabecera)
        ->get();

        
        $cuotas = DB::tABLE('ventas_cuotas')
        ->where('IdCpe_cabecera',$id)
        ->orderby('ven_cuo_id','asc')
        ->get();

        $ventas_medios = DB::tABLE('venta_medio_pago')
        ->join('medios_pagos','medios_pagos.id_med_pag','venta_medio_pago.id_med_pag')
        ->where('IdCpe_cabecera',$cabecera->IdCpe_cabecera)
        ->get();


       /* if($cabecera->tdocod =='01' || $cabecera->tdocod=='03'){

            if($cabecera->ccacodsun ==='0'){


              return Redirect::to('/SisFact')->with('danger','EL COMPROBANTE YA FUE ENVIADO Y ACEPADO POR SUNAT, NO SE PUEDE MODIFICAR'); 




          }elseif($cabecera->ccacodsun >= 2000 && $cabecera->ccacodsun <= 3999){

           return Redirect::to('/SisFact')->with('danger','EL COMPROBANTE YA FUE ENVIADO Y SE ENCUENTRA ANULADO O RECHAZADO, NO SE PUEDE MODIFICAR'); 



       }


   }*/

        $gastos = DB::tABLE('tipo_gastos')->get();

         $unidades = DB::table('unidad_medida')
    ->where('umeest','=','Activo')
    ->orderBy('umecod','asc')->get();

    $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)
            ->first(); 

   return view('empresas.puntosventas.editar_medio_pago',compact('mozos','categorias','comprobantes','documentos','estadopagos','mediospagos','detalle','cabecera','ventas_medios','empresa','gastos','unidades','datos'));


}




public function actualizar_venta_mp(Request $request){

  $rucemp = trim(Auth::user()->IdEmpresa);
  $empresa = Empresa::findOrFail($rucemp);

//ID VENTA
  $venta = $request->get('IdCpe_cabecera');

  $med_pag = $request->get('med_pag');

  $tdicod = $request->get('tdicod');
  $imprimir = $request->get('imprimir');
  $consumo = $request->get('consumo');

  $clinum = $request->get('clinum');
  $clinom = $request->get('clinom');
  $clidir = $request->get('clidir');
  $clicor = $request->get('clicor');

  $mondoc = 'PEN';
  $observaciones = $request->get('observaciones');


  $ped_id = $request->get('ped_id');
  $total_venta = $request->get('total_venta');
  $vuelto = $request->get('vuelto');

  $tdocod = $request->get('tdocod');
  $estadopago = $request->get('estadopago');
  $fecEmi = $request->get('fecEmi');
  $fecVen = $request->get('fecVen');
  $consumo = $request->get('consumo');

  $id_med_pag = $request->input('id_med_pag', []);
  $mon_med_pag = $request->input('mon_med_pag', []);

  if(!is_array($id_med_pag) && !empty($id_med_pag)){
      $id_med_pag = [$id_med_pag];
  }

  if(!is_array($mon_med_pag) && !empty($mon_med_pag)){
      $mon_med_pag = [$mon_med_pag];
  }

  $consumo = $request->get('consumo');


  $items = $request->get('txt_id_producto');
  $cantidades = $request->get('txt_cantidad');
  $precios = $request->get('precios');

  $buscre = DB::tABLE('credito_dias')->where('cre_dia_id',$estadopago)->first();

  $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

  $bus_alm= DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();

  $cont_carac = strlen($clinum);
  $obt_dig = substr(trim($clinum), 0, 2);
 

$cabecera = cpe_cabecera::findOrFail($venta);


$med_pag = $request->get('med_pag');
  

DB::tABLE('venta_medio_pago')->where('IdCpe_cabecera',$venta)->delete();


if(!empty($id_med_pag)){
    foreach($id_med_pag as $index_mp =>$mp){

        DB::tABLE('venta_medio_pago')
        ->insert(['id_turno'=>Auth::user()->id_turno,
            'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
            'id_med_pag'=>$mp,
            'monto'=>$mon_med_pag[$index_mp]]);
    }
    
}else{

 
   DB::tABLE('venta_medio_pago')
   ->insert(['id_turno'=>Auth::user()->id_turno,
    'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
    'id_med_pag'=>$med_pag,
    'monto'=>$total_venta]);
}




return response()->json(['estado'=>'success','codfact' =>$venta,'tdocod'=>$cabecera->tdocod,'mensaje'=>'Comprobante Emitido']);

}

}


