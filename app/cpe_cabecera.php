<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use MasterSoft\Http\Middleware\CheckSystemTruncate;
use DB;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Cuota;
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
use Greenter\Model\Sale\FormaPagos\FormaPagoCredito;
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
use MasterSoft\cpe_cabecera;
use MasterSoft\cpe_detalle;
use MasterSoft\Cliente;
use Config;
use MasterSoft\Mail\FacturacionEmail;
use Illuminate\Support\Facades\Mail;

class cpe_cabecera extends Model
{
   protected $table = 'cpe_cabecera';

   protected $primaryKey = 'IdCpe_cabecera';

   public $timestamps = false;

   protected $fillable = [
        'tdocod',
        'topcod',
        'ccafem',
        'ccafve',
        'ccacde',
        'tdicod',
        'ccandi',
        'ccanom',
        'moncod',
        'ccades',
        'ccacar',
        'ccatde',
        'ccatvg',
        'ccatvi',
        'ccatve',
        'ccaigv',
        'ccaisc',
        'ccaotr',
        'ccaitv',
        'direccion',
        'IdUsuario',
        'ccaestbu',
        'ccaestse',
        'respse',
        'codunique',
        'detraccion',
        'mesa_id',
        'ped_id',
        'fecha_hora',
        'direccion',
        'external_id',
        'tipo_venta',
        'id_empresa_negocio'
   ];

   protected $guarded = [

   ];


   public function setIdCpe_cabecera($value){

      $this->attributes['IdCpe_cabecera']=$value;

  }

   public function setIdEmpresa($value){

       $this->attributes['IdEmpresa'] = $value;

   }

   public function setNumDoc($value){
       $this->attributes['numdoc'] = $value;
   }

   public function setSerDoc(){

     $this->attributes['serdoc'] = $value;

   }

   public function setTdoCod($value){

      $this->attributes['tdocod'] = $value;

   }

   public function generarpdfgeneral($venta){

      $rutapdf = public_path().'/pdf/';

         $cabpdf= DB::TABLE('cpe_cabecera as cpe_c')
        ->select('ccatinaf','ccaobs','IdCpe_cabecera_ref','cpe_c.id_empresa_negocio','cpe_c.id_almacen','serdoc','numdoc','ccanom','tdides','ccandi','clidir','cliente.telefono','clicor','cpe_c.cre_dia_id','cpe_c.moncod','ccaitv','cpe_c.tdicod','clinum','clinom','cpe_c.clicod','cpe_c.IdEmpresa','cpe_c.tdocod','direccion','ccatvg','ccaitv','ccaigv','tdodes','ccafem','ccafve','monnom','guia_remision','ccatexo','ccaitv','ccaigv','ccatvi','cpe_c.icbper','ncdes','estadopago','serie_ref','num_ref')
        ->leftjoin('cliente','cpe_c.clicod','cliente.clicod')        
        ->leftjoin('tipo_documento_identidad','tipo_documento_identidad.tdicod','cpe_c.tdicod')
         ->leftjoin('tipo_documento','tipo_documento.tdocod','cpe_c.tdocod')
         ->leftjoin('tipo_nota_credito','tipo_nota_credito.nccod','cpe_c.tipnot')        
         ->leftjoin('moneda as mon','cpe_c.moncod','=','mon.moncod')

        ->where('IdCpe_cabecera',$venta)
        ->first();

          $servicios = DB::TABLE('cpe_detalle')
        ->leftjoin('productos','cpe_detalle.IdProducto','productos.IdProducto')
        ->leftjoin('unidad_medida','unidad_medida.umecod','cpe_detalle.umecod')
        ->where('IdCpe_cabecera',$venta)
        ->where('productos.promocion','6')
        ->get();

           $repuestos = DB::TABLE('cpe_detalle')
        ->leftjoin('productos','cpe_detalle.IdProducto','productos.IdProducto')
        ->leftjoin('unidad_medida','unidad_medida.umecod','cpe_detalle.umecod')
        ->where('IdCpe_cabecera',$venta)
        ->where('productos.promocion','7')
        ->get();
     

        $bus_suc = DB::TABLE('cpe_cabecera')->where('Idcpe_cabecera',$venta)->first();
        
      $doc_ref = DB::TABLE('cpe_cabecera')->where('IdCpe_cabecera',$cabpdf->IdCpe_cabecera_ref)->first();

      $detpdf = DB::TABLE('cpe_detalle')->select('mar_nom','cdevve','ubicacion','cpe_detalle.procod','cpe_detalle.cdecan','cpe_detalle.umecod','cdedes','cdevun','cdeigv','cdepve','cdevve','cdepuni','cpe_detalle.IdProducto','id_almacen_pro','almacenes.descripcion','umenom','umecin')
       ->leftjoin('unidad_medida','unidad_medida.umecod','cpe_detalle.umecod')
      ->leftjoin('productos','productos.IdProducto','cpe_detalle.IdProducto')
      ->leftjoin('almacenes','almacenes.id_almacen','cpe_detalle.id_almacen_pro')
         ->leftjoin('marcas','marcas.mar_id','productos.marca')
      ->where('IdCpe_cabecera',$venta)
      ->orderby('IdCpe_detalle','asc')
      ->get();

      $sucursal = DB::TABLE('empresa_negocios')
      ->leftjoin('formatos_comprobantes','formatos_comprobantes.cod_for_com','empresa_negocios.cod_for_com')
      ->where('id_empresa_negocio',$bus_suc->id_empresa_negocio)
      ->first();
    
    
    $cuotas = DB::table('ventas_cuotas')->where('Idcpe_cabecera',$venta)->get();
    
    $cant_cuotas = count($cuotas);

      $cliente= DB::table('cliente as cli')
      ->leftjoin('cpe_cabecera as c','c.clicod','=','cli.clicod')
      ->where('IdCpe_cabecera','=',$venta)
      ->where('cli.clicod','=',$cabpdf->clicod)
      ->first();
               

      $empresa = Empresa::findOrFail($cabpdf->IdEmpresa);

      $nompdffile=$cabpdf->IdEmpresa.'-'.$cabpdf->tdocod.'-'.$cabpdf->serdoc.'-'.$cabpdf->numdoc.'.pdf'; 

      $moneda = DB::table('moneda')->where('moncod','=',$cabpdf->moncod)->first();

      $totalletras= MontoLetras::convertir(number_format($cabpdf->ccaitv,'2','.',''),$moneda->monnom,'Centimos');
      
         $vehiculo = DB::TABLE('tipos_vehiculos')
     ->leftjoin('marcas','marcas.mar_id','tipos_vehiculos.mar_id')
     ->leftjoin('modelos','modelos.mod_id','tipos_vehiculos.mod_id')
     ->first();

      $qrfile =  'QR-'.$cabpdf->IdEmpresa.'-'.$cabpdf->tdocod.'-'.$cabpdf->serdoc.'-'.$cabpdf->numdoc.'.png'; 
      $imgqr = public_path()."/qr/".$qrfile;

    if($cabpdf->tdocod =='15'){
        $view = \View::make('formatos_comprobantes.proforma', compact('cant_cuotas','cuotas','cabpdf','detpdf','cliente','totalletras','empresa','imgqr','sucursal','qrfile','doc_ref','vehiculo'));

      }elseif($cabpdf->tdocod=='80' || $cabpdf->tdocod=='70') {

          $view = \View::make('formatos_comprobantes.ordenes', compact('servicios','repuestos','cant_cuotas','cuotas','cabpdf','detpdf','cliente','totalletras','empresa','imgqr','sucursal','qrfile','vehiculo'));

    }elseif($cabpdf->tdocod=='03' ||  $cabpdf->tdocod=='13' ||$cabpdf->tdocod=='01' || $cabpdf->tdocod=='07' || $cabpdf->tdocod=='08'  || $cabpdf->tdocod=='85' || $cabpdf->tdocod=='86' ){

         $view = \View::make('formatos_comprobantes.'.$sucursal->descripcion, compact('cant_cuotas','cuotas','cabpdf','detpdf','cliente','totalletras','empresa','imgqr','sucursal','qrfile','doc_ref','vehiculo'));
 
    }
      
                  
      $pdf = \App::make('dompdf.wrapper');
      $contenido = $view->render();
      $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

      return $nompdffile;

}

 public function configuracion(){
  
      $rucemp = Auth::user()->IdEmpresa;

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

public function validar_codigos_sunat($codfact) {
    $detalles = DB::table('cpe_detalle as det')
        ->leftJoin('productos as prod', 'det.procod', '=', 'prod.procod')
        ->where('det.IdCpe_cabecera', $codfact)
        ->where('det.cdevve', '>', 0)
        ->select('det.procod', 'det.cdedes', 'prod.cod_producto_sunat')
        ->get();
    
    $sin_codigo = [];
    
    foreach ($detalles as $detalle) {
        if (empty($detalle->cod_producto_sunat)) {
            $sin_codigo[] = [
                'codigo' => $detalle->procod,
                'descripcion' => $detalle->cdedes
            ];
        }
    }
    
    if (!empty($sin_codigo)) {
        return [
            'valido' => false,
            'mensaje' => 'Los siguientes productos no tienen código SUNAT:',
            'productos' => $sin_codigo
        ];
    }
    
    return ['valido' => true];
}

public function generar_xml_boleta_factura($codfact){

    $cabecera = DB::TABLE('cpe_cabecera')->where('IdCpe_cabecera',$codfact)->first();

    // Obtenemos los detalles junto con el código SUNAT de la tabla productos
    $detalles = DB::TABLE('cpe_detalle as det')
        ->leftJoin('productos as prod', 'det.procod', '=', 'prod.procod')
        ->where('det.IdCpe_cabecera', $codfact)
        ->where('det.cdevve', '>', 0)
        ->select('det.*', 'prod.cod_producto_sunat')
        ->get();

    $cuotas = DB::TABLE('ventas_cuotas')->where('IdCpe_cabecera',$codfact)->get();
    $sucursal = DB::TABLE('empresa_negocios')->where('id_empresa_negocio',$cabecera->id_empresa_negocio)->first();
    $moneda = DB::TABLE('moneda')->where('moncod',$cabecera->moncod)->first();
    $empresa = DB::TABLE('empresa')->where('IdEmpresa',$sucursal->IdEmpresa)->first();

    $see = self::configuracion();
    
    $clidireccion = new Address();
    $clidireccion->setDireccion($cabecera->direccion);

    $client = new Client();
    $client->setTipoDoc($cabecera->tdicod)
        ->setNumDoc($cabecera->ccandi)
        ->setRznSocial($cabecera->ccanom)
        ->setAddress($clidireccion);

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

    // IGV estándar en Perú es 18%. Si hay exonerados/inafectos, la lógica de abajo lo maneja.
    if($cabecera->ccatexo > 0 || $cabecera->ccatinaf > 0){
      $porigv = 0;
    }else{
      $porigv = 10.5; 
    }

    $items = [];
    $nuevas_cuotas = [];

    if(!empty($cuotas)){
      foreach($cuotas as $cuota) {
         $nueva_cuota = new Cuota();
         $nueva_cuota->setMonto($cuota->ven_cuo_mon);
         $nueva_cuota->setFechaPago(new \DateTime($cuota->ven_cuo_fec_ven));
         $nuevas_cuotas[] = $nueva_cuota;
      } 
    }

    // Array para guardar los códigos SUNAT y usarlos en el parche XML
    $codigos_sunat_para_inyectar = [];

    foreach ($detalles as $detalle) {
        $item = new SaleDetail();
        $item->setCodProducto($detalle->procod);
        
        // Si existe el código SUNAT, lo guardamos para inyectarlo en el XML con la estructura correcta
        if (!empty($detalle->cod_producto_sunat)) {
            $codigos_sunat_para_inyectar[$detalle->procod] = $detalle->cod_producto_sunat;
            
            // Intentamos usar el método nativo de Greenter (funciona si actualizaste a v4.3+)
            if (method_exists($item, 'setCodProductoSunat')) {
                $item->setCodProductoSunat($detalle->cod_producto_sunat);
            }
        }

        $item->setUnidad($detalle->umecod);
        $item->setCantidad($detalle->cdecan);
        $item->setDescripcion($detalle->cdedes);
        
        // Prevenimos error de variable no definida si no existe en la tabla
        $desc_mon = isset($detalle->desc_mon) ? $detalle->desc_mon : 0; 

        if($desc_mon > 0){
            $item->setDescuentos([
                (new Charge())
                    ->setCodTipo('00') 
                    ->setMontoBase($detalle->valor_unitario * $detalle->cdecan) 
                    ->setFactor($detalle->por_des / 100) 
                    ->setMonto(number_format($desc_mon, '2', '.', ''))
            ]);
            $item->setMtoValorUnitario(number_format(($detalle->valor_unitario * $detalle->cdecan) / $detalle->cdecan, '10', '.', ''));
        }else{
            $item->setMtoValorUnitario(number_format($detalle->cdepve / $detalle->cdecan, '10', '.', ''));
        }

        $item->setMtoBaseIgv($detalle->cdepve);
        $item->setPorcentajeIgv($porigv); 
        $item->setIgv($detalle->cdeigv);
        $item->setTipAfeIgv($detalle->tigcod);
        $item->setMtoValorVenta($detalle->cdepve);
        $item->setMtoPrecioUnitario(number_format($detalle->cdevve / $detalle->cdecan, '10', '.', ''));
        $item->setIcbper($detalle->mon_icbper_det * $detalle->cdecan); 
        $item->setFactorIcbper($detalle->mon_icbper_det);
        $item->setTotalImpuestos($detalle->cdeigv + ($detalle->mon_icbper_det * $detalle->cdecan));

        $items[] = $item;
    }

    $invoice = new Invoice();
    
    if($cabecera->estadopago == 'CONTADO'){
       $invoice->setFormaPago(new FormaPagoContado());
    } elseif($cabecera->estadopago == 'CREDITO'){
       $invoice->setFormaPago(new FormaPagoCredito($cabecera->ccaitv));
       $invoice->setCuotas($nuevas_cuotas);
    }
   
    $invoice->setUblVersion('2.1');
    $invoice->setTipoOperacion($cabecera->topcod); 
    $invoice->setTipoDoc($cabecera->tdocod);
    $invoice->setSerie($cabecera->serdoc);
    $invoice->setCorrelativo($cabecera->numdoc);
    $invoice->setFechaEmision(new \DateTime($cabecera->ccafem));
    $invoice->setTipoMoneda($cabecera->moncod);
    $invoice->setClient($client);
    $invoice->setMtoOperGravadas($cabecera->ccatvg);
    $invoice->setMtoOperExoneradas($cabecera->ccatexo);
    $invoice->setMtoOperInafectas($cabecera->ccatinaf);
    $invoice->setIcbper($cabecera->tot_icbper);
    $invoice->setMtoIGV($cabecera->ccaigv);
    $invoice->setTotalImpuestos($cabecera->ccaigv);
    $invoice->setValorVenta($cabecera->ccatvg + $cabecera->ccatexo + $cabecera->ccatinaf);
    $invoice->setSubTotal($cabecera->ccaitv);
    $invoice->setMtoImpVenta($cabecera->ccaitv);
    $invoice->setCompany($company);

    $totalletras = MontoLetras::convertir(number_format($cabecera->ccaitv, '2', '.', ''), $moneda->monnom, 'Centimos');

    $legend = (new Legend())
        ->setCode('1000')
        ->setValue($totalletras);

    $invoice->setDetails($items)->setLegends([$legend]);

    $builder = new InvoiceBuilder();
    $xml = $builder->build($invoice);

    // 🔥🔥🔥 PARCHE DE INYECCIÓN XML CORREGIDO (ESTRUCTURA OFICIAL SUNAT) 🔥🔥🔥
    // SUNAT exige "CommodityClassification" para el Catálogo 25, NO "StandardItemIdentification"
    if (!empty($codigos_sunat_para_inyectar)) {
        foreach ($codigos_sunat_para_inyectar as $procod => $cod_sunat) {
            $buscar = '<cac:SellersItemIdentification><cbc:ID>' . $procod . '</cbc:ID></cac:SellersItemIdentification>';
            
            // ESTRUCTURA CORRECTA SEGÚN GUÍA UBL 2.1 DE SUNAT PARA CATÁLOGO 25
            $reemplazar = $buscar . '<cac:CommodityClassification><cbc:ItemClassificationCode listID="UNSPSC">' . $cod_sunat . '</cbc:ItemClassificationCode></cac:CommodityClassification>';
            //$reemplazar = $buscar . '<cac:StandardItemIdentification><cbc:ID schemeID="UNSPSC">' . $cod_sunat . '</cbc:ID></cac:StandardItemIdentification>';
            
            // Reemplazamos solo la primera coincidencia de este producto
            $xml = preg_replace('/' . preg_quote($buscar, '/') . '/', $reemplazar, $xml, 1);
        }
    }
    // 🔥🔥🔥 FIN DEL PARCHE 🔥🔥🔥

    $nom_xml = $invoice->getName();

    if(file_exists(public_path().'/xml/'.$nom_xml.'.xml')){
      unlink(public_path().'/xml/'.$nom_xml.'.xml');
    }

    file_put_contents(public_path().'/xml/'.$nom_xml.'.xml', $xml);

    self::firmar_xml($nom_xml);

    return $nom_xml;
}

  public function firmar_xml($archivo){

    $rucemp = substr($archivo,0,11);

    $xml = file_get_contents(public_path().'/xml/'.$archivo.'.xml');

    $certificado = public_path().'/certificados/'.$rucemp.'.pem';

    $certificadoprueba = public_path().'/certificados/prueba.pem';

    if(file_exists($certificado)){
      $cert = file_get_contents($certificado);
    }else{
      $cert = file_get_contents($certificadoprueba);
    }
     

    $signer = new SignedXml();
    $signer->setCertificate($cert);

    $xmlSigned = $signer->signXml($xml);

    file_put_contents(public_path().'/xml/'.$archivo.'.xml', $xmlSigned);

    return 'firmado';

  }


    public function generar_xml_nota($codfact){

    $cabecera = DB::TABLE('cpe_cabecera')->where('Idcpe_cabecera',$codfact)
    ->leftjoin('cliente','cliente.clicod','cpe_cabecera.clicod')->first();


    $comprobante = DB::TABLE('cpe_cabecera')
    ->where('IdCpe_cabecera',$cabecera->IdCpe_cabecera_ref)
    ->first();

    if(empty($comprobante)){
       $comprobante = DB::TABLE('cpe_cabecera')
       ->where('serdoc',$cabecera->serie_ref)
       ->where('numdoc',$cabecera->num_ref)
       ->where('tdocod',$cabecera->tdocod_ref)
       ->where('IdEmpresa',Auth::user()->IdEmpresa)
       ->first();
    }


    $detalles = DB::TABLE('cpe_detalle')
    ->where('Idcpe_cabecera',$codfact)
    ->where('cdevve','>','0')
    ->get();
    $sucursal = DB::TABLE('empresa_negocios')->where('id_empresa_negocio',$cabecera->id_empresa_negocio)->first();


    $moneda = DB::TABLE('moneda')->where('moncod',$cabecera->moncod)->first();
    $empresa = DB::TABLE('empresa')->where('IdEmpresa',$sucursal->IdEmpresa)->first();

   
    $numdoc = str_pad($cabecera->numdoc,8,"0", STR_PAD_LEFT);


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
        ->setNombreComercial($empresa->NomEmpresa)
        ->setAddress($address);


        $invoice = (new Note())
        ->setUblVersion('2.1')
        //->setTipoOperacion($cabecera->topcod) // Catalog. 51
        ->setTipoDoc($cabecera->tdocod)
        ->setSerie($cabecera->serdoc)
        ->setCorrelativo($cabecera->numdoc)
        ->setFechaEmision(new \DateTime($cabecera->ccafem))
        ->setTipDocAfectado($comprobante->tdocod) // Tipo Doc: Factura
        ->setNumDocfectado($comprobante->serdoc.'-'.$comprobante->numdoc) // Factura: Serie-Correlativo
        ->setCodMotivo($cabecera->tipnot) // Catalogo. 09
        ->setDesMotivo($cabecera->ccaobs)
        ->setTipoMoneda($cabecera->moncod)
        ->setClient($client)
        ->setMtoOperGravadas($cabecera->ccatvg)
        ->setMtoOperExoneradas($cabecera->ccatexo)
        ->setMtoIGV($cabecera->ccaigv)
        ->setTotalImpuestos($cabecera->ccaigv)
       // ->setValorVenta($cabecera->ccatvg)
      //  ->setSubTotal($cabecera->ccaitv)
        ->setMtoImpVenta($cabecera->ccaitv)
        ->setCompany($company);
     //  }

    

  
    $items = [];
    foreach ($detalles as $detalle) {

      $tipo_igv = DB::TABLE('tipo_igv')->where('tigcod',$detalle->tigcod)->first();

      $item = (new SaleDetail())
        ->setCodProducto($detalle->procod)
        ->setUnidad($detalle->umecod)
        ->setCantidad($detalle->cdecan)
        ->setDescripcion($detalle->cdedes)
        ->setMtoBaseIgv($detalle->cdepve)
        ->setPorcentajeIgv($tipo_igv->tigpor) // 18%
        ->setIgv($detalle->cdeigv)
        ->setTipAfeIgv($detalle->tigcod)
        ->setTotalImpuestos($detalle->cdeigv)
        ->setMtoValorVenta($detalle->cdepve)
        ->setMtoValorUnitario($detalle->cdepve/$detalle->cdecan)
        ->setMtoPrecioUnitario($detalle->cdevve/$detalle->cdecan);

     $items[] = $item;
    }


    $totalletras= MontoLetras::convertir(number_format($cabecera->ccaitv,'2','.',''),$moneda->monnom,'Centimos');

    $legend = (new Legend())
        ->setCode('1000')
        ->setValue($totalletras);

    $invoice->setDetails($items)
        ->setLegends([$legend]);

//dd($invoice);
    $builder = new NoteBuilder();
    $xml = $builder->build($invoice);

    $nom_xml = $invoice->getName();


    file_put_contents(public_path().'/xml/'.$nom_xml.'.xml',$xml);

    self::firmar_xml($nom_xml);

    return $nom_xml;

  }



     public function enviar_sunat($codfact){

    $cabecera = DB::TABLE('cpe_cabecera')->where('IdCpe_cabecera',$codfact)->first();

    $empresa = DB::TABLE('empresa')->where('IdEmpresa',$cabecera->IdEmpresa)->first();

    $usuario = $empresa->IdEmpresa.$empresa->wsusuario;
    $contrasena = $empresa->claveSunat;

    $nom_arch = $cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$cabecera->numdoc;

    if($empresa->tip_env_fac_id=='01'){

      if($empresa->produccion =='1'){

        $urlService = 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService';

      }elseif($empresa->produccion =='0'){

        $urlService = 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService';

      }


    }elseif($empresa->tip_env_fac_id =='02'){

      if($empresa->produccion =='1'){

        $urlService = 'https://ose.nubefact.com/ol-ti-itcpe/billService';

      }elseif($empresa->produccion =='0'){

        $urlService = 'https://ose-demo.nubefact.com/ol-ti-itcpe/billService';

      }

    }

    

    $soap = new SoapClient();
    $soap->setService($urlService);
    $soap->setCredentials($usuario, $contrasena);
   
      $sender = new BillSender();
      $sender->setClient($soap);
      $xml = file_get_contents(public_path().'/xml/'.$nom_arch.'.xml');
      $result = $sender->send($nom_arch, $xml);



      if(!$result->isSuccess()){
        // Error en la conexion con el servicio de SUNAT
       // var_dump($result->getError());

    
        $actualizar = cpe_cabecera::findOrFail($codfact);
        $actualizar->ccacodsun = $result->getError()->getCode();
        $actualizar->ccadessun = $result->getError()->getMessage();
        $actualizar->update();



        return;
      }

      $cdr = $result->getCdrResponse();
      file_put_contents(public_path().'/cdr/'.'R-'.$nom_arch.'.zip', $result->getCdrZip());

      $code = (int)$cdr->getCode();

     
      $actualizar = cpe_cabecera::findOrFail($codfact);
      if($code === 0){
        $dato = 'ACEPTADO';
        $flag_enviado = '1';
        if(count($cdr->getNotes()) > 0) {
          
          $dato = 'ACEPTADO CON OBSERVACIONES'; 

          foreach ($cdr->getNotes() as $obs){
              
          }
        }
      }elseif($code >= 2000 && $code <= 3999){
        $dato = "RECHAZADO";
        $flag_enviado ='1';
      }else{
        $flag_enviado ='0';
        $dato = 'Excepción';
      }

      $actualizar->est_sunat = $dato;
      $actualizar->ccacodsun = $code;
      $actualizar->enviado = $flag_enviado;
      $actualizar->ccadessun = $cdr->getDescription();
      $actualizar->update();


      
   
    

      return 'enviado';

  }

   
 

  public function generar_qr(){

      $qrfile =  'QR-'.$this->IdEmpresa.'-'.$this->tdocod.'-'.$this->serdoc.'-'.$this->numdoc.'.png'; 

      $ruta = public_path().'/qr/';
      $file = $ruta.$qrfile;

      if(file_exists($file)){
         unlink($file);
      }
      
      return \QRCode::text($this->ccaqr)->setMargin(1)->setSize(4)->setOutFile($file)->png();
  

  }

  public function generar_nuevo_qr($comprobante){

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


      $qrfile =  'QR-'.$cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$cabecera->numdoc.'.png'; 

      $ruta = public_path().'/qr/';
      $file = $ruta.$qrfile;

      if(file_exists($file)){
         unlink($file);
      }
      
      return \QRCode::text($cabecera->ccaqr)->setMargin(1)->setSize(4)->setOutFile($file)->png();

  }

  public function consultar_cdr($codfact){

    $cabecera = DB::TABLE('cpe_cabecera')->where('IdCpe_cabecera',$codfact)->first();

    $empresa = DB::TABLE('empresa')->where('IdEmpresa',$cabecera->IdEmpresa)->first();

      $usuario = $empresa->IdEmpresa.$empresa->wsusuario;
      $contrasena = $empresa->claveSunat;

      // URL del servicio.
      
      if($empresa->tip_env_fac_id=='01'){

        if($empresa->produccion =='1'){

           $urlService = 'https://e-factura.sunat.gob.pe/ol-it-wsconscpegem/billConsultService?wsdl';

        }elseif($empresa->produccion =='0'){

          $urlService = 'https://e-factura.sunat.gob.pe/ol-it-wsconscpegem/billConsultService?wsdl';

        }


      }elseif($empresa->tip_env_fac_id =='02'){

        if($empresa->produccion =='1'){

          $urlService = 'https://ose.nubefact.com/ol-ti-itcpe/billService?wsdl';

        }elseif($empresa->produccion =='0'){

          $urlService = 'https://ose-demo.nubefact.com/ol-ti-itcpe/billService';

        }

    }

    $soap = new SoapClient($urlService);
    $soap->setCredentials($usuario, $contrasena);

    $service = new ConsultCdrService();
    $service->setClient($soap);

    $rucEmisor = $cabecera->IdEmpresa;
    $tipoDocumento = $cabecera->tdocod; // 01: Factura, 07: Nota de Crédito, 08: Nota de Débito
    $serie = $cabecera->serdoc;
    $correlativo = $cabecera->numdoc;
    $result = $service->getStatusCdr($rucEmisor, $tipoDocumento, $serie, $correlativo);

    if (!$result->isSuccess()) {
        
        $cab_act = cpe_cabecera::findOrFail($codfact);
        $cab_act->des_cdr = $result->getError()->getMessage();
        $cab_act->cod_cdr = $result->getError()->getCode();
        $cab_act->update();

        return;

    }else{

       $cab_act = cpe_cabecera::findOrFail($codfact);
        $cab_act->ccacodsun = $result->getCdrResponse()->getCode();
        $cab_act->ccadessun = $result->getCdrResponse()->getDescription();
        if($result->getCdrResponse()->getCode()=='0'){
            $cab_act->enviado = '1';
        }
        $cab_act->update();

    }

    $cdr = $result->getCdrResponse();
    
    if ($cdr === null) {

        $cab_act = cpe_cabecera::findOrFail($codfact);
        $cab_act->des_cdr = 'CDR no encontrado, el comprobante no ha sido comunicado a SUNAT.';
        $cab_act->update();
       
        return;
    }

    file_put_contents(public_path().'/cdr/R-'.$cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$cabecera->numdoc.'.zip', $result->getCdrZip());

     return 'listo';
     
    }

    public function enviar_comprobante_correo($codfact,$clicor){

        $rucemp = trim(Auth::user()->IdEmpresa);
        $empresa = Empresa::findOrFail($rucemp);
        
        $correo_envio =env('MAIL_USERNAME',$empresa->correo_envio);
        $contrasena_envio = env('MAIL_PASSWORD', $empresa->contrasena_envio);

        $cabpdf = cpe_cabecera::findOrFail($codfact)
        ->where('IdCpe_cabecera',$codfact)
        ->first();

        $cabpdf->generarpdfgeneral($codfact);

        $sucursal = DB::TABLE('empresa_negocios')->where('id_empresa_negocio',$cabpdf->id_empresa_negocio)->first();

      
        $cliente = DB::TABLE('cliente')->where('clinum',$cabpdf->ccandi)->where('rucemp',$rucemp)->first();
      
    
        $ruta_pdf = public_path().'/pdf/';
        $ruta_xml = public_path().'/xml/';


        $numdoc = $cabpdf->numdoc;

        $nompdffile=$rucemp.'-'.$cabpdf->tdocod.'-'.$cabpdf->serdoc.'-'.$numdoc.'.pdf'; 
        $nomxmlfile=$rucemp.'-'.$cabpdf->tdocod.'-'.$cabpdf->serdoc.'-'.$numdoc.'.xml'; 


        $destino4 = "";
        $destino5 = "";

        Config::set('mail.username', $empresa->correo_envio);
        Config::set('mail.password', $empresa->contrasena_envio);

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

                
                if(!empty($clicor)){
                  try{
                     Mail::to($clicor)->send(new FacturacionEmail($objDemo,$destino4,$destino5,$empresa->correo_envio));
                  }catch(\Exception $e){
                   //dd($e);
                  }
                 
                }             

            return "enviado";     

    }

}
