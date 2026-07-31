<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use Greenter\See;
use Greenter\Ws\Services\SoapClient;
use Greenter\Ws\Services\BillSender;
use Greenter\Ws\Services\ExtService;
use Greenter\Xml\Builder\DespatchBuilder;
use Greenter\XMLSecLibs\Sunat\SignedXml;
use Greenter\Model\Despatch\AdditionalDoc;
use Greenter\Model\Client\Client;
use Greenter\Model\Despatch\Despatch;
use Greenter\Model\Despatch\DespatchDetail;
use Greenter\Model\Despatch\Direction;
use Greenter\Model\Despatch\Shipment;
use Greenter\Model\Despatch\Transportist;
use Greenter\Model\Response\BillResult;
use Greenter\Model\Response\CdrResponse;
use Greenter\Model\Sale\Document;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Despatch\Vehicle;
use Greenter\Model\Despatch\Driver;
use DOMDocument;
use MasterSoft\Modelos\Almacen;
use MasterSoft\TipoIGV;
use MasterSoft\Empresa;
use MasterSoft\Cliente;
use MasterSoft\cpe_cabecera;
use MasterSoft\documento_relacionado;
use MasterSoft\unidad_medida;
use MasterSoft\movimientos;
use MasterSoft\guias_remision;
use MasterSoft\guias_remision_detalle;
use MasterSoft\Comprobante;
use MasterSoft\EmpresaNegocios;
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
use File;
use ZipArchive;

use PDF;
use DB;

class GuiasRemisionController extends Controller
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
		    $fechaini = now()->modify('first day of this month')->format('Y-m-d');
        $fechafin = now()->modify('last day of this month')->format('Y-m-d');
        $razsoc = $request->get('cliente');
        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');
        $serdoc=$request->get('serdoc');
        $comp=$request->get('comp');
        $numdoc = $request->get('numdoc');
        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
        $IdEmpresa = Auth::user()->IdEmpresa;
        $ser = substr($comp,strpos($comp,'-')-4,4);
        $num = substr($comp,strpos($comp,'-')+1,8);

        if(empty($razsoc) &&  empty($fecin) && empty($fecfin) && empty($serdoc) && empty($numdoc) && empty($comp)){

            $comprobantes = DB::table('guias_remision as g')->select('IdCpe_guia','g.fechaemision','g.serieguia','g.numeroguia','ruccliente','nomcliente','fechatraslado','direccionllegada','motivo','ccabaj','codhash','ccasunrescod','ccacodsun','g.IdEmpresa','g.tdocod','tdides','tdodes','ccadessun','numTicket','desError')
               ->join('motivo_traslado as mt','mt.IdMotivo','g.IdMotivo')
               ->join('tipo_documento_identidad as ti','ti.tdicod','g.tdicod')
               ->join('tipo_documento as td','td.tdocod','g.tdocod')
               ->where('g.IdEmpresa','=',$IdEmpresa)
               ->where('g.fechaemision','>=',$fechaini)
               ->where('g.tdocod','09')
               ->where('g.fechaemision','<=',$fechafin)
               ->where('g.id_empresa_negocio',Auth::user()->id_empresa_negocio)
               ->where(function ($query) use ($razsoc) {
                $query->where('g.nomcliente','like','%'.$razsoc.'%')
                      ->orWhere('g.ruccliente','=',$razsoc);
                })
               ->orderby('IdCpe_guia','desc')
               ->paginate(100);
              

           }elseif(!empty($comp)){
    

                $comprobantes = DB::table('guias_remision as g')->select('IdCpe_guia','g.fechaemision','g.serieguia','g.numeroguia','ruccliente','nomcliente','fechatraslado','direccionllegada','motivo','ccabaj','codhash','ccasunrescod','ccacodsun','g.IdEmpresa','g.tdocod','tdides','tdodes','ccadessun','numTicket','desError')
               ->join('motivo_traslado as mt','mt.IdMotivo','g.IdMotivo')
               ->join('tipo_documento_identidad as ti','ti.tdicod','g.tdicod')
               ->join('tipo_documento as td','td.tdocod','g.tdocod')
               ->where('g.IdEmpresa','=',$IdEmpresa)
                 ->where('g.tdocod','09')
               ->where('g.id_empresa_negocio',Auth::user()->id_empresa_negocio)
               ->where('serieguia','=',$ser)
               ->where('numeroguia','=',$num)
                 ->orderby('IdCpe_guia','desc')
               ->paginate(100);
              


           }elseif (empty($razsoc)  && empty($serdoc) && empty($numdoc)) {
         

              $comprobantes = DB::table('guias_remision as g')->select('IdCpe_guia','g.fechaemision','g.serieguia','g.numeroguia','ruccliente','nomcliente','fechatraslado','direccionllegada','motivo','ccabaj','codhash','ccasunrescod','ccacodsun','g.IdEmpresa','g.tdocod','tdides','tdodes','ccadessun','numTicket','desError')
              ->join('motivo_traslado as mt','mt.IdMotivo','g.IdMotivo')
              ->join('tipo_documento_identidad as ti','ti.tdicod','g.tdicod')
              ->join('tipo_documento as td','td.tdocod','g.tdocod')
              ->where('g.IdEmpresa','=',$IdEmpresa)
                ->where('g.tdocod','09')
              ->where('g.id_empresa_negocio',Auth::user()->id_empresa_negocio)
              ->where('g.fechaemision','>=',$fecin)
              ->where('g.fechaemision','<=',$fecfin)
			        ->orderby('IdCpe_guia','desc')
               ->paginate(100);
            
            }elseif (!empty($razsoc)) {
             
                $comprobantes = DB::table('guias_remision as g')->select('IdCpe_guia','g.fechaemision','g.serieguia','g.numeroguia','ruccliente','nomcliente','fechatraslado','direccionllegada','motivo','ccabaj','codhash','ccasunrescod','ccacodsun','g.IdEmpresa','g.tdocod','tdides','tdodes','ccadessun','numTicket','desError')
                ->join('motivo_traslado as mt','mt.IdMotivo','g.IdMotivo')
                ->join('tipo_documento_identidad as ti','ti.tdicod','g.tdicod')
                ->join('tipo_documento as td','td.tdocod','g.tdocod')
                ->where('g.IdEmpresa','=',$IdEmpresa)
                  ->where('g.tdocod','09')
                ->where('g.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                ->where('g.fechaemision','>=',$fecin)
                ->where('g.fechaemision','<=',$fecfin)
                ->where(function ($query) use ($razsoc) {
                $query->where('g.nomcliente','like','%'.$razsoc.'%')
                      ->orWhere('g.ruccliente','=',$razsoc);
                })
				        ->orderby('IdCpe_guia','desc')
                ->paginate(100);

              
           }
              
          
            return view('empresas.guiaremision.index',compact('comprobantes','empresa'));

        
         
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
  public function create($comprobante=0)
    {

		 $sucursal = DB::tABLE('empresa_negocios')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();

        $bus_alm = new Almacen;
        $almacen = $bus_alm->buscar_almacen_predeterminado($sucursal->first()->id_empresa_negocio);
		
        $fecha = now()->format('m/d/Y');
        $items="";
        $cabecera="";

        $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();

        // consultar unidades de medida
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        // consultar tipo de operaciones
        $motivos = DB::tABLE('motivo_traslado')
        ->orderBy('motivo','asc')->get();

        $modalidades = DB::tABLE('modalidad_traslado')
        ->orderBy('modalidad','asc')->get();

        // consultar tipos de documentos de identidad
        $docidentidad = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

        //consultar tipo de documento 
        $doccomprobante = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->orderBy('tdocod','asc')->get();

        // obtener el ruc de la empresa en la cual se logueo
        $rucemp = trim(Auth::user()->IdEmpresa);

        // consultar los clientes que le pertenece a la empresa
        $clientes= DB::tABLE('cliente')->where('rucemp','=',$rucemp)->where('cliest','=','Activo')
        ->orderby('clinom','asc')->get();

        //consultar productos que le pertenece a la empresa
        $productos= DB::tABLE('productos')->where('IdEmpresa','=',$rucemp)->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)->where('proest','=','Activo')
        ->orderby('pronom','asc')->get();

        // consultar tipos de monedas
        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

     

        $ubigeos = DB::tABLE('cat_ubigeo')->get();

          $senudoc = DB::table('empresa_negocios')
                    ->select('serieguia', 'numeroguia', 'direccion', 'ubigeo', 'codigofiscal')
                    ->where('IdEmpresa', '=', $rucemp)
                    ->where('id_empresa_negocio', '=', Auth::user()->id_empresa_negocio)
                    ->first();
  
    
       
        $ubigeos = DB::tABLE('cat_ubigeo')->get();

        if($comprobante !=0){

          $cabecera = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$comprobante)->first();
          $items = DB::tABLE('cpe_detalle')->where('IdCpe_cabecera',$comprobante)->get();

        }
      

       

            return view('empresas.guiaremision.guiaremision',compact('sucursal','almacen','igv','monedas','unidades','motivos','modalidades','docidentidad','clientes','fecha','senudoc','productos','doccomprobante','items','cabecera','ubigeos'));
       
    }

   
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

      //SI ES TRANSPORTE PUBLICO  DATOS DE TRANSPORTISTA

      //SI ES TRASPORTE PRIVADO  DATOS DE CONDUCTOR

      //Empresa que emite el comprobante
      $rucemp = trim(Auth::user()->IdEmpresa);

      $sercomp= $request->get('serdoc');
      $numcomp= $request->get('numdoc');
      $fecemi = $request->get('fecEmi');
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

      $ubigeopartida = $request->get('ubigeopartida');
      $direccionpartida = $request->get('direccionpartida');
      $desubigeopartida = $request->get('desubigeopartida');

      $ubigeollegada = $request->get('ubigeollegada');
      $direccionllegada = $request->get('direccionllegada');
      $desubigeollegada = $request->get('desubigeollegada');
      $correo = $request->get('correo');

        $cantidades = $request->get('cant');
        $unidades = $request->get('unid');
        $codpro = $request->get('codpro');
        $detpro = $request->get('detpro');

        $busmotivo = DB::TABLE('motivo_traslado')->where('IdMotivo',$motivo)->first();
        
        $numdoc = str_pad($request->get('numdoc'),8,"0", STR_PAD_LEFT);
        

      //Registrar el cliente enviado a través del formulario si no existe
        $cliente = Cliente::FirstOrCreate(['clinum'=>$cliruc,'rucemp'=>$rucemp],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$correo,'rucemp'=>$rucemp,'tdicod'=>$tdicod]);
        
       
        
        
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
            
            /*$IdProducto = DB::TABLE('productos')
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


        self::enviar_guia_sunat($cabecera->IdCpe_guia);
        self::generarpdfguia($cabecera->IdCpe_guia);
     
       if($request->ajax()) {
          return response()->json(['mensaje' => 'Registrado']);
        }
     
  }



  public static function extractZipArchive($archive,$destination){
       
    
        $zip = new \ZipArchive;
    
        // Check if archive is readable.
        if($zip->open($archive) === TRUE){
            // Check if destination is writable
            if(is_writeable($destination )){
                $zip->extractTo($destination);
                $zip->close();
                $GLOBALS['status'] = array('success' => 'Files unzipped successfully');
                return true;
            }else{
                $GLOBALS['status'] = array('error' => 'Directory not writeable by webserver.');
                return false;
            }
        }else{
            $GLOBALS['status'] = array('error' => 'Cannot read .zip archive.');
            return false;
        }
    }

 public function descargar($venta,$tipo)
    {

      $cabecera = DB::TABLE('guias_remision')->where('IdCpe_guia',$venta)->first();

      $codfact = $cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serieguia.'-'.str_pad($cabecera->numeroguia,8,"0", STR_PAD_LEFT);

       $codfact1 = $cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serieguia.'-'.$cabecera->numeroguia;


      $rucemp = trim(Auth::user()->IdEmpresa);
      $rutpdfile = public_path().'/pdf/';
      $rutxmlfile = public_path().'/xml/';
      $rutcdrfile = public_path().'/cdr/';
     // $file= $rutpdfile.$codfact.'.pdf';
      //$file= $codfact.'.pdf';
      $file= $rutpdfile.$codfact.'.pdf';
      $xml= $rutxmlfile.$codfact1.'.xml';
      $cdr= $rutcdrfile.'R-'.$codfact1.'.zip';


    if($tipo =='pdf'){

 self::generarpdfguia($venta);
      if(file_exists($file))
      {
		 unlink($file);
		 self::generarpdfguia($venta);

		
        $headers = array(
              'Content-Type: application/pdf',
            );

       

      }
       return response()->download($file);

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

  public function facturapdf($codfact,$doccod,$idcabecera,$archivo)
    {

      $rucemp = trim(Auth::user()->IdEmpresa);
      $rutpdfile = '/opt/data/comprobantes/'.$rucemp.'/pdf/';
      $rutxmlfile = '/opt/data/comprobantes/'.$rucemp.'/xml/';
      $rutcdrfile = '/opt/data/comprobantes/'.$rucemp.'/cdr/';
     // $file= $rutpdfile.$codfact.'.pdf';
      //$file= $codfact.'.pdf';
      $file= $rutpdfile.$codfact.'.pdf';
      $xml= $rutxmlfile.$codfact.'.xml';
      $cdr= $rutcdrfile.'R-'.$codfact.'.zip';

    if($archivo =='pdf'){

       if (file_exists($file))
      {
        $headers = array(
              'Content-Type: application/pdf',
            );

        return response()->download($file);
      }

    }elseif($archivo =='xml'){
        if (file_exists($xml))
      {
        $headers = array(
              'Content-Type: application/xml',
            );

        return response()->download($xml);
      }

    }elseif($archivo =='cdr'){

      if (file_exists($cdr))
      {
        $headers = array(
              'Content-Type: application/zip',
            );

        return response()->download($cdr);
      }

    }
     

     
      if($doccod=='07' || $doccod=='08'){
        return Redirect::to('/listarnotas/'.$idcabecera);
      }elseif($doccod=='0'){
        return Redirect::to('/listarbajas/'.$idcabecera);
      }else{
         return Redirect::to('/SisFact');
      }
    }
  


     public function buscarcomprobantelista(Request $request){
      $search = $request->term;

      $ser = substr($search,strpos($search,'-')-4,4);
      $num = substr($search,strpos($search,'-')+1,8);

      $rucemp = trim(Auth::user()->IdEmpresa);

      $compcabecera = DB::table('cpe_cabecera as cpe_c')->select('serdoc','numdoc')
      ->where('serdoc','=',$ser)
      ->where('numdoc','=',$num)
      ->where('IdEmpresa','=',$rucemp);

      $comprobante = DB::table('cpe_nota as cpe_n')->select('cpe_n.serdoc','cpe_n.numdoc')
       ->join('cpe_cabecera as cpe_c','cpe_n.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
      ->where('cpe_n.serdoc','=',$ser)
      ->where('cpe_n.numdoc','=',$num)
      ->where('cpe_c.IdEmpresa','=',$rucemp)
      ->union($compcabecera)
      ->get();

      $results = array();

      foreach($comprobante as $c => $comp){
        $sernum=$comp->serdoc.'-'.$comp->numdoc;
        $results[] = ['value'=>$sernum];
      }
      return response()->json($results);
    }


     public function buscarcomprobantebaja(Request $request){
      $search = $request->term;

      $ser = substr($search,strpos($search,'-')-4,4);
      $num = substr($search,strpos($search,'-')+1,8);

      $rucemp = trim(Auth::user()->IdEmpresa);
      $comprobante = DB::table('cpe_cabecera as cab')
      ->join('cliente as cli','cab.clicod','=','cli.clicod')
      ->join('tipo_documento as tdo','cab.tdocod','=','tdo.tdocod')
      ->join('tipo_documento_identidad as tdi','cab.tdicod','=','tdi.tdicod')
      ->join('moneda as mon','cab.moncod','=','mon.moncod')
      ->join('tipo_operacion as top','cab.topcod','=','top.topcod')
      ->where('serdoc','=',$ser)
      ->where('numdoc','=',$num)
      ->where('IdEmpresa','=',$rucemp)
      ->where('ccasunrescod','=','0' )
      ->where('ccabaj','=',NULL)
      ->take(1)->get();

      $results = array();

      foreach($comprobante as $c => $comp){
        $sernum=$comp->serdoc.'-'.$comp->numdoc;
        $results[] = ['value'=>$sernum,'serdoc'=>$comp->serdoc,'numdoc'=>$comp->numdoc,'tdomod'=>$comp->tdodes,'tdocod'=>$comp->tdocod,'fecemi'=>$comp->ccafem,'idcabecera'=>$comp->IdCpe_cabecera,'monnom'=>$comp->monnom,'ccaitv'=>$comp->ccaitv];
      }
      return response()->json($results);
    }

    public function autocomplete(Request $request){
      $search = $request->term;
      $rucemp = trim(Auth::user()->IdEmpresa);
    
      $ruc = Cliente::where('clinum','=',$search)->where('rucemp','=',$rucemp)->where('cliest','=','Activo')->take(10)->get();
      $results = array();
      
 
      if($ruc->isEmpty()){
         $leer_respuesta = self::consultaruc($search);

         $results[] = ['value'=>$leer_respuesta['ruc'],'nom'=>$leer_respuesta['nombre_o_razon_social'],'dir'=>$leer_respuesta['direccion_completa'],'tdicod'=>'6'];
        
      }else{

        foreach($ruc as $cli => $cliente){
           $numnom=$cliente->clinum;
         
          $results[] = ['value'=>$numnom,'nom'=>$cliente->clinom,'dir'=>$cliente->clidir,'tdicod'=>$cliente->tdicod,'clicod'=>$cliente->clicod,'cor'=>$cliente->clicor];
        }
       
      }


      return response()->json($results);
    }

    public function consultarDocumento(Request $request) {
    $documento = trim($request->get('documento'));

    if (strlen($documento) === 8) {
        // ==========================================
        // 1. CONSULTA DNI (Usando apiperu.dev)
        // ==========================================
        $token = 'c7c656604942b0a6df5fa225835e99eb7376cf841d38781b91f651f72e03cc09'; 
        $params = json_encode(['dni' => $documento]);
        $url = "https://apiperu.dev/api/dni";

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) { 
            return response()->json(['error' => 'Error de conexión DNI: ' . $err]); 
        }

        $data = json_decode($response, true);

        if(isset($data['success']) && $data['success'] == true) {
            return response()->json([
                'nom' => $data['data']['nombre_completo']
            ]);
        } else {
            return response()->json(['error' => 'DNI no encontrado en RENIEC.']);
        }

    } elseif (strlen($documento) === 11) {
        // ==========================================
        // 2. CONSULTA RUC (Usando tu servidor consultas.holape.app)
        // ==========================================
        $url = "https://consultas.holape.app/api/v1/ruc/" . $documento;

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "GET", // Petición GET limpia
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) { 
            return response()->json(['error' => 'Error de conexión con tu servidor RUC: ' . $err]); 
        }

        $data = json_decode($response, true);

        if(isset($data['success']) && $data['success'] == true) {
            $ubigeo_cod = $data['data']['ubigeo'] ?? '000000';
            
            // Buscamos la descripción en tu tabla cat_ubigeo local para mantener compatibilidad
            $ubigeo_bd = \DB::table('cat_ubigeo')->where('ubi_cod', $ubigeo_cod)->first();
            $ubigeo_des = $ubigeo_bd ? trim($ubigeo_bd->ubi_des) : 'Autocompletado';

            return response()->json([
                'nom'        => $data['data']['razon_social'],
                'dir'        => $data['data']['direccion'],
                'ubigeo'     => $ubigeo_cod,
                'ubigeo_des' => $ubigeo_des
            ]);
        } else {
            return response()->json(['error' => 'RUC no encontrado en la base de datos.']);
        }

    } else {
        return response()->json(['error' => 'El documento debe tener 8 (DNI) u 11 dígitos (RUC).']);
    }
}
  

    public function consultarcambio(Request $request){
      $search = $request->term;
      $cambio = DB::table('tipocambio')->where('FecTipCambio','=',$search)->take(10)->get();
      $results = array();

      foreach($cambio as $tc => $tcam){
        $results[] = ['value'=>$tcam->FecTipCambio,'cam'=>$tcam->CamCompra];
      }
       return response()->json($results);
    }

    public function consultartipcambio(Request $request){
         $search = $request->fecemi;
         $cambio = DB::table('tipocambio')->where('FecTipCambio','=',$search)->first();

         $res = $cambio->CamCompra;
       
      return $res;
    }


   
	   public function buscarubigeo(Request $request){
      $search = $request->term;       
       $rucemp = trim(Auth::user()->IdEmpresa);
      $productos= DB::table('cat_ubigeo')
      ->where('ubi_des', 'like','%'.$search.'%')->get();

      $results = array();
      foreach($productos as $pro){

        $results[] = ['value'=>$pro->ubi_des,'codubigeo'=>$pro->ubi_cod];
      }

      return response()->json($results);
    }


  
    
      public function webserviceonlinebaja($data_json){

      $rucemp =Auth::user()->IdEmpresa;
      $empresa = Empresa::findOrFail($rucemp);
      // RUTA para enviar documentos
      $ruta = 'https://econosystems.proveedorpse.com/api/GuiaRemision.svc/LowGuia';

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
    

    

	
	pubLic function ConsultarEstado ($tdocod,$serie,$numero,$IdCpe_cabecera){
		
    $rucemp =Auth::user()->IdEmpresa;
    $empresa = Empresa::findOrFail($rucemp);
    

		$data = array
        (
          "TOKEN"=>$empresa->wscontrasena,
          "NUM_NIF_EMIS"=> $rucemp,
          "COD_TIP_CPE"=> $tdocod,
          "NUM_SERIE_CPE"=> $serie,
          "NUM_CORRE_CPE"=> $numero,				
		);
		
	  $data_json = json_encode($data,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

	 $leer_respuesta = self::webserviceonlineestado($data_json);
	  

	      $comp = cpe_cabecera::findOrFail($IdCpe_cabecera);
         $comp->codhash = $leer_respuesta['codigo_hash'];
          $comp->error = substr($leer_respuesta['errors'],2,250);
          $comp->ccacodsun = $leer_respuesta['estado_documento'];
          $comp->ccadessun = substr($leer_respuesta['sunat_description'],2,250);
          $comp->ccasunrescod = substr($leer_respuesta['sunat_responsecode'],2,250);
          $comp->ccaenlace = $leer_respuesta['url'];
          $comp->ccasunnot = $leer_respuesta['sunat_note'];
          $comp->ccaqr = $leer_respuesta['cadena_para_codigo_qr'];
          $comp->update();
		  
        return Redirect::to('/SisFact');
	}
	
  

    public function webserviceonline($data_json){

      $rucemp =Auth::user()->IdEmpresa;
      $empresa = Empresa::findOrFail($rucemp);
      // RUTA para enviar documentos
      $ruta = "https://econosystems.proveedorpse.com/api/GuiaRemision.svc/SendGuia";

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

   public function webserviceonlineestado($data_json){

      $rucemp =Auth::user()->IdEmpresa;
      $empresa = Empresa::findOrFail($rucemp);
      // RUTA para enviar documentos
      $ruta = 'https://econosystems.proveedorpse.com/api/GuiaRemision.svc/GetEstatusGuia';

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

    public function webserviceonlinepdf($data_json){

      $rucemp =Auth::user()->IdEmpresa;
      $empresa = Empresa::findOrFail($rucemp);
      // RUTA para enviar documentos
      $ruta = 'https://econosystems.proveedorpse.com/api/GuiaRemision.svc/GetGuia';

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

    public function webserviceonlinepdfguia($data_json){

      $rucemp =Auth::user()->IdEmpresa;
      $empresa = Empresa::findOrFail($rucemp);
      // RUTA para enviar documentos
      $ruta = 'https://econosystems.proveedorpse.com/api/GuiaRemision.svc/GetGuia';

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



    public function webservicepdf($tipdoc,$serdoc,$numdoc,$IdCpe_cabecera){

      $rucemp = trim(Auth::user()->IdEmpresa);
      $empresa = Empresa::findOrFail($rucemp);
      $data = array
        (
          "TOKEN"=> $empresa->wscontrasena,  
          "NUM_NIF_EMIS"=> $rucemp,
          "COD_TIP_GUR"=> $tipdoc,  
          "NUM_SERIE_GUR"=> $serdoc,
          "NUM_CORRE_GUR"=> $numdoc,
          "RETORNA_XML_ENVIO"=> "false",
          "RETORNA_XML_CDR"=> "true",
          "RETORNA_PDF"=> "true"
             
      );
    
      $numdocmod = str_pad($numdoc,8,"0", STR_PAD_LEFT);

      $data_json = json_encode($data,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

      $leer_respuesta = self::webserviceonlinepdf($data_json);
      
       $rutapdf = '/opt/data/comprobantes/'.$rucemp.'/pdf/';
       $filepdf=  $rucemp.'-'.$tipdoc.'-'.$serdoc.'-'.$numdocmod.'.pdf';

   
        
          $pdf_decoded = base64_decode ($leer_respuesta['pdf_bytes']);
          //Write data back to pdf file
          $pdf = fopen ($rutapdf.$filepdf,'w');
          fwrite ($pdf,$pdf_decoded);
          //close output file
          fclose ($pdf);

        return Redirect::to('/guiasremision');

                
    }

     public function webserviceguia($tipdoc,$serdoc,$numdoc,$IdCpe_cabecera){

      $rucemp = trim(Auth::user()->IdEmpresa);
      $empresa = Empresa::findOrFail($rucemp);
      $data = array
        (
          "TOKEN"=>$empresa->wscontrasena,
          "NUM_NIF_EMIS"=> $rucemp,
          "COD_TIP_CPE"=> $tipdoc,
          "NUM_SERIE_CPE"=> $serdoc,
          "NUM_CORRE_CPE"=> $numdoc,
          "RETORNA_XML_ENVIO"=> "false",
          "RETORNA_XML_CDR"=> "false",
          "RETORNA_PDF"=> "true"        
      );
    
      $numdocmod = str_pad($numdoc,8,"0", STR_PAD_LEFT);

      $data_json = json_encode($data,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

      $leer_respuesta = self::webserviceonlinepdf($data_json);
      
       $rutapdf = '/opt/data/comprobantes/'.$rucemp.'/pdf/';
       $filepdf=  $rucemp.'-'.$tipdoc.'-'.$serdoc.'-'.$numdocmod.'.pdf';

   
        
          $pdf_decoded = base64_decode ($leer_respuesta['pdf_bytes']);
          //Write data back to pdf file
          $pdf = fopen ($rutapdf.$filepdf,'w');
          fwrite ($pdf,$pdf_decoded);
          //close output file
          fclose ($pdf);

        return Redirect::to('/SisFact');

                
    }


      public function webservicepdf1($tipdoc,$serdoc,$numdoc,$IdCpe_cabecera){

      $rucemp = trim(Auth::user()->IdEmpresa);
      $empresa = Empresa::findOrFail($rucemp);
      $numdocmod = str_pad($numdoc,8,"0", STR_PAD_LEFT);

      $data = array
        (
          "TOKEN"=>$empresa->wscontrasena,
          "NUM_NIF_EMIS"=> $rucemp,
          "COD_TIP_CPE"=> $tipdoc,
          "NUM_SERIE_CPE"=> $serdoc,
          "NUM_CORRE_CPE"=> $numdoc,
          "RETORNA_XML_ENVIO"=> "false",
          "RETORNA_XML_CDR"=> "false",
          "RETORNA_PDF"=> "true"        
      );
    
      $data_json = json_encode($data,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

      $leer_respuesta = self::webserviceonlinepdf($data_json);
      
       $rutapdf = '/opt/data/comprobantes/'.$rucemp.'/pdf/';
       $filepdf=  $rucemp.'-'.$tipdoc.'-'.$serdoc.'-'.$numdocmod.'.pdf';

      
        
          $pdf_decoded = base64_decode($leer_respuesta['pdf_bytes']);
          //Write data back to pdf file
          $pdf = fopen ($rutapdf.$filepdf,'w');
          fwrite ($pdf,$pdf_decoded);
          //close output file
          fclose ($pdf);

        return Redirect::to('/SisFact');

                
    }

    public function generar_qr($qrfile,$contenido){

    	$rucemp = trim(Auth::user()->IdEmpresa);
      $ruta = public_path().'/qr/';
      $file = $ruta.$qrfile;
      
      if(!empty($contenido)){
           return \QRCode::text($contenido)->setMargin(1)->setSize(7)->setOutFile($file)->png();
      }else{
             return 'NO SE PUEDE GENERAR QR';
      } 
   

    }
  

    pubLic function enviar_correo($tdocod,$serie,$numero,$correo){
      
      $rucemp =Auth::user()->IdEmpresa;
      $empresa = Empresa::findOrFail($rucemp);

      $data = array
      (
          "TOKEN"=>$empresa->wscontrasena,
          "NUM_NIF_EMIS"=> $rucemp,
          "COD_TIP_CPE"=> $tdocod,
          "NUM_SERIE_CPE"=> $serie,
          "NUM_CORRE_CPE"=> $numero,
          "MailEnvio"=> $correo        
      );
      
      $data_json = json_encode($data,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
      $leer_respuesta = self::webserviceonline($data_json);


      return $leer_respuesta;

    }

    public function webserviceonlinejson($nomarchivo){

      $rucemp =Auth::user()->IdEmpresa;
      $empresa = Empresa::findOrFail($rucemp);
      // RUTA para enviar documentos
      $ruta = "https://econosystems.proveedorpse.com/api/GuiaRemision.svc/SendGuia";

      $data_json = file_get_contents("/opt/data/comprobantes/".$rucemp."/json/".$nomarchivo);
    

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



     
      
      public function consultaruc($ruc){
   
     $ruta = "https://ruc.com.pe/api/v1/ruc";
    $token = "a6299df2-16bf-4644-8a69-a267b97de88b-95705f7b-ad67-4372-8e63-bab14ab5f94f";

    $rucaconsultar = $ruc;

    $data = array(
      "token" => $token,
      "ruc"   => $rucaconsultar
    );
      
    $data_json = json_encode($data);

    // Invocamos el servicio a ruc.com.pe
    // Ejemplo para JSON
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ruta);
    curl_setopt(
      $ch, CURLOPT_HTTPHEADER, array(
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
    if (isset($leer_respuesta['errors'])) {
      //Mostramos los errores si los hay
      echo $leer_respuesta['errors'];
    } else {
      //Mostramos la respuesta
      //echo "Respuesta de la API:<br>";
      return $leer_respuesta;
    }
                  
    }

       public function generar_xml_guia($codfact){

        $cabecera = DB::TABLE('guias_remision')
        //->leftjoin('cliente','cliente.clicod','guias_remision.clicod')
        ->where('IdCpe_guia',$codfact)->first();
          
            $doc_ref = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$cabecera->IdCpe_cabecera)->first();

            if(!empty($doc_ref)){
               $doc_ref_ser_num = $doc_ref->serdoc.'-'.$doc_ref->numdoc;
             
            }else{
                $doc_ref_ser_num = $cabecera->serie_num_ref;
                
            }


        $detalles = DB::TABLE('guias_remision_detalle')->select('marca','modelo','guias_remision_detalle.pronom','guias_remision_detalle.umecod','guias_remision_detalle.procod','cantidad')
        ->leftjoin('productos','productos.IdProducto','guias_remision_detalle.IdProducto')
        ->where('IdCpe_guia',$codfact)
        ->get();


        $sucursal = DB::TABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

         
          $numdoc = str_pad($cabecera->numeroguia,8,"0", STR_PAD_LEFT);

        $empresa = DB::TABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

        $rutxmlfile = public_path().'/xml/';
        $rutcdrfile = public_path().'/cdr/';

        $nomxmlcdr=   Auth::user()->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serieguia.'-'.$numdoc;

        $motivo = DB::TABLE('motivo_traslado')->where('IdMotivo',$cabecera->IdMotivo)->First();
        
        $modalidad = DB::TABLE('modalidad_traslado')->where('IdModalidad',$cabecera->IdModalidad)->First();
          

        $filexml =  Auth::user()->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serieguia.'-'.$numdoc.'.xml';
        $filecdrzip =  'R-'.Auth::user()->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serieguia.'-'.$numdoc.'.zip';

    //  $see = self::configuracion();
        
         $rel = new Document();
        $rel->setTipoDoc('06') // Tipo: Numero de Orden de Entrega
        ->setNroDoc($doc_ref_ser_num);

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
            ->setChoferDoc($cabecera->rucconductor)
            ->setNroMtc('');

        $vehiculoPrincipal = (new Vehicle())
          ->setPlaca($cabecera->placa)
          ->setNroCirculacion('')
          ->setCodEmisor('01')
          ->setNroAutorizacion('');

         $chofer = (new Driver())
          ->setTipo('Principal')
          ->setTipoDoc($cabecera->tdicodconductor)
          ->setNroDoc($cabecera->rucconductor)
          ->setLicencia('')
          ->setNombres($cabecera->nomconductor)
          ->setApellidos('');

        $envio = new Shipment();
        $envio->setCodTraslado($cabecera->IdMotivo); // Cat.20

        if($cabecera->IdModalidad=='02'){
           $envio->setIndicador(['SUNAT_Envio_IndicadorTrasladoVehiculoM1L']);
        }
       
        $envio->setDesTraslado($motivo->motivo);
        $envio->setModTraslado($cabecera->IdModalidad);// Cat.18
        $envio->setFecTraslado(new \DateTime($cabecera->fechatraslado));
        $envio->setCodPuerto('');
        $envio->setIndTransbordo(false);
        $envio->setPesoTotal($cabecera->pesobruto);
        $envio->setUndPesoTotal('KGM');
        //    $envio->setNumBultos(2) // Solo válido para importaciones
        $envio->setNumContenedor('');


        if($cabecera->IdMotivo=='4'){

          $envio->setLlegada(new Direction($cabecera->ubigeollegada, $cabecera->direccionllegada))
          ->setRuc($cabecera->IdEmpresa)
          ->setCodLocal($cabecera->cod_local_part);
        

          $envio->setPartida(new Direction($cabecera->ubigeopartida, $cabecera->direccionpartida))
          ->setRuc($cabecera->IdEmpresa)
          ->setCodLocal($cabecera->cod_local_dest);

        }else{

          $envio->setLlegada(new Direction($cabecera->ubigeollegada, $cabecera->direccionllegada));
          $envio->setPartida(new Direction($cabecera->ubigeopartida, $cabecera->direccionpartida));

        }
     

        $envio->setTransportista($transp);

        $despatch = new Despatch();
        $despatch->setVersion('2022')
        ->setTipoDoc($cabecera->tdocod)
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

          self::firmar_xml($nom_xml);

          $obt_xml = file_get_contents(public_path().'/xml/'.$nom_xml.'.xml');

        //  self::enviar_sunat($codfact);


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

  
  public function enviar_sunat($codfact){

    $cabecera = DB::tABLE('guias_remision')->where('IdCpe_guia',$codfact)->first();

    $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$cabecera->id_empresa_negocio)->first();

    $empresa = DB::tABLE('empresa')->where('IdEmpresa',$sucursal->IdEmpresa)->first();

    $usuario = $empresa->IdEmpresa.$empresa->wsusuario;

    $contrasena = $empresa->claveSunat;

    $nom_arch = $empresa->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serieguia.'-'.$cabecera->numeroguia;

    if($empresa->tip_env_fac_id=='01'){

      if($empresa->produccion =='1'){

        $urlService = 'https://e-guiaremision.sunat.gob.pe/ol-ti-itemision-guia-gem/billService';

      }elseif($empresa->produccion =='0'){

        $urlService = 'https://e-beta.sunat.gob.pe/ol-ti-itemision-guia-gem-beta/billService';

      }


    }elseif($empresa->tip_env_fac_id =='02'){

      if($empresa->produccion =='1'){

        $urlService = 'https://e-guiaremision.sunat.gob.pe/ol-ti-itemision-guia-gem/billService?wsdl';

      }elseif($empresa->produccion =='0'){

        $urlService = 'https://ose-demo.nubefact.com/ol-ti-itcpe/billService';

      }

    }


    $soap = new SoapClient();
     $soap->setService(SunatEndpoints::GUIA_BETA);
    $soap->setCredentials($usuario, $contrasena);

      $sender = new BillSender();
      $sender->setClient($soap);
      $xml = file_get_contents(public_path().'/xml/'.$nom_arch.'.xml');
      $result = $sender->send($nom_arch, $xml);



      if(!$result->isSuccess()){
        // Error en la conexion con el servicio de SUNAT
       // var_dump($result->getError());

    
        $actualizar = guias_remision::findOrFail($codfact);
        $actualizar->ccacodsun = $result->getError()->getCode();
        $actualizar->ccadessun = $result->getError()->getMessage();
        $actualizar->update();

        return;
      }

      $cdr = $result->getCdrResponse();
  
      file_put_contents(public_path().'/cdr/'.'R-'.$nom_arch.'.zip', $result->getCdrZip());

      $code = (int)$cdr->getCode();

     
      $actualizar = guias_remision::findOrFail($codfact);
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



     
   
       public function generarpdfguia($venta){


      $rucemp =Auth::user()->IdEmpresa;
      $rutapdf = public_path().'/pdf/';;

      $empresa = Empresa::findOrFail($rucemp);

      $sucursal = DB::TABLE('empresa_negocios')
      ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
     

      $cabpdf = DB::TABLE('guias_remision')->select('IdCpe_cabecera','bultos','placa','licencia','nomconductor','motivo','modalidad','fechatraslado','nomcliente','nomcliente','direccionllegada','direccionpartida','ul.ubi_des as ubillegada','up.ubi_des as ubipartida','placa','pesobruto','rucconductor','ruccliente','guias_remision.tdocod','numeroguia','serieguia','codhash','tdodes','tdides','observacion','serie_num_ref','cadena_qr')
      //->leftjoin('moneda as mon','guias_remision.moncod','=','mon.moncod')
      ->leftjoin('tipo_documento','tipo_documento.tdocod','guias_remision.tdocod')
       ->leftjoin('tipo_documento_identidad','tipo_documento_identidad.tdicod','guias_remision.tdicod')
      ->leftjoin('modalidad_traslado','modalidad_traslado.IdModalidad','guias_remision.IdModalidad')
      ->leftjoin('cat_ubigeo as ul','ul.ubi_cod','guias_remision.ubigeopartida')
      ->leftjoin('cat_ubigeo as up','up.ubi_cod','guias_remision.ubigeollegada')
      ->leftjoin('motivo_traslado','motivo_traslado.IdMotivo','guias_remision.IdMotivo')
      ->where('IdCpe_guia',$venta)
      ->first();
	
	  $doc_ref ='';
	  
	  if(!empty($cabpdf->IdCpe_cabecera)){
		 $doc_ref = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$cabpdf->IdCpe_cabecera)->first();
     	
	  }

	 /* $vehiculo = DB::TABLE('tipos_vehiculos')
      ->leftjoin('marcas','marcas.mar_id','tipos_vehiculos.mar_id')
      ->leftjoin('modelos','modelos.mod_id','tipos_vehiculos.mod_id')
      ->where('placa',$cabpdf->placa)->first();*/

      $detpdf = DB::TABLE('guias_remision_detalle')
     // ->leftjoin('productos','productos.IdProducto','guias_remision_detalle.IdProducto')
      ->leftjoin('unidad_medida','unidad_medida.umecod','guias_remision_detalle.umecod')
      ->where('IdCpe_guia',$venta)->get();

      $cliente= DB::table('cliente as cli')
      ->leftjoin('guias_remision as c','c.ruccliente','=','cli.clinum')
      ->where('IdCpe_guia','=',$venta)
      ->where('cli.clinum','=',$cabpdf->ruccliente)
      ->first();
                  
      $nompdffile=$rucemp.'-'.$cabpdf->tdocod.'-'.$cabpdf->serieguia.'-'.str_pad($cabpdf->numeroguia,8,"0", STR_PAD_LEFT).'.pdf'; 


    //  $numdoc = str_pad($cabpdf->numeroguia,8,"0", STR_PAD_LEFT);
      $numdoc = $cabpdf->numeroguia;

      $qrfile =  'QR-'.$rucemp.'-'.$cabpdf->tdocod.'-'.$cabpdf->serieguia.'-'.$numdoc.'.png'; 

      $imgqr = public_path()."/qr/".$qrfile;

      self::generar_qr($qrfile,$cabpdf->cadena_qr);
        
        
      $view = \View::make('formatos_comprobantes.A4_guia', compact('doc_ref','cabpdf','detpdf','cliente','empresa','imgqr','sucursal','qrfile'));

                  
      $pdf = \App::make('dompdf.wrapper');
      $contenido = $view->render();
      $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

    
  
        return $nompdffile;
    }
	
      public function configuracion(){
    
      $rucemp = trim(Auth::user()->IdEmpresa);
      $empresa = Empresa::findOrFail($rucemp);
      $usuario = $rucemp.$empresa->usuariosunat;
      $contrasena = $empresa->contrasenasunat;

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
	
	public function crear_guia($comprobante=0)
    {

             $sucursal = DB::tABLE('empresa_negocios')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();

        $bus_alm = new Almacen;
        $almacen = $bus_alm->buscar_almacen_predeterminado($sucursal->first()->id_empresa_negocio);
    

   
        $fecha = now()->format('m/d/Y');
        $items="";
        $cabecera="";

        $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();

        // consultar unidades de medida
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        // consultar tipo de operaciones
        $motivos = DB::tABLE('motivo_traslado')
        ->orderBy('motivo','asc')->get();

        $modalidades = DB::tABLE('modalidad_traslado')
        ->orderBy('modalidad','asc')->get();

        // consultar tipos de documentos de identidad
        $docidentidad = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

        //consultar tipo de documento 
        $doccomprobante = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->orderBy('tdocod','asc')->get();

        // obtener el ruc de la empresa en la cual se logueo
        $rucemp = trim(Auth::user()->IdEmpresa);

        // consultar los clientes que le pertenece a la empresa
        $clientes= DB::tABLE('cliente')->where('rucemp','=',$rucemp)->where('cliest','=','Activo')
        ->orderby('clinom','asc')->get();

        //consultar productos que le pertenece a la empresa
        $productos= DB::tABLE('productos')->where('IdEmpresa','=',$rucemp)->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)->where('proest','=','Activo')
        ->orderby('pronom','asc')->get();

        // consultar tipos de monedas
        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

     

        $ubigeos = DB::tABLE('cat_ubigeo')->get();

          $senudoc = DB::tABLE('empresa_negocios')
                    ->select('serieguia','numeroguia')
                    ->where('IdEmpresa','=',$rucemp)
                    ->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)
                    ->first(); 
  
    
       
        $ubigeos = DB::tABLE('cat_ubigeo')->get();

        if($comprobante !=0){

          $cabecera = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$comprobante)->first();
          $items = DB::tABLE('cpe_detalle')->where('IdCpe_cabecera',$comprobante)->get();

        }
      

        return view('empresas.guiaremision.guiaremision_venta',compact('igv','monedas','unidades','motivos','modalidades','docidentidad','clientes','fecha','senudoc','productos','doccomprobante','items','cabecera','ubigeos','sucursal','almacen'));
       
    }
	
	
	
     public function generar_guia_venta(Request $request)
    {


    DB::beginTransaction();

    try{

        $id_doc_ref = $request->get('id_doc_ref');

        $dat_doc_ref = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$id_doc_ref)->first();
        if(!empty($id_doc_ref)){
          $act_est_doc_ref = cpe_cabecera::findOrFail($id_doc_ref);
          $act_est_doc_ref->estado ='ACEPTADO';
          $act_est_doc_ref->update();

           $id_negocio = $dat_doc_ref->id_empresa_negocio;
           $id_almacen = $dat_doc_ref->id_almacen;
        }else{
           $id_negocio = $request->get('id_empresa_negocio');
           $id_almacen = $request->get('id_almacen');
        }
		    


      //SI ES TRANSPORTE PUBLICO  DATOS DE TRANSPORTISTA

      //SI ES TRASPORTE PRIVADO  DATOS DE CONDUCTOR

      //Empresa que emite el comprobante
      $rucemp = trim(Auth::user()->IdEmpresa);

      $afec_stock= $request->get('afec_stock');
      $sercomp= $request->get('serdoc');
      $numcomp= $request->get('numdoc');
      $fecemi = $request->get('fecEmi');
      $fechatraslado = $request->get('fechatraslado');
      $motivo = $request->get('motivo');
      $serie_num_ref = $request->get('serie_num_ref');
      $modalidad = $request->get('modalidad');
      $bultos = $request->get('bultos');
      
      $tdicod = $request->get('tdicod');
      $cliruc = $request->get('clinum');
      $clinom = $request->get('clinom');
       $licencia = $request->get('licencia');
      $clidir = $request->get('clidir');

      

      $estadostock = $request->get('estadostock');

      $tdicodconductor = $request->get('conductortdicod');
      $rucconductor = $request->get('conductornum');
      $nombreconductor = $request->get('conductornom');
      $placa = $request->get('placa');

      $ubigeopartida = $request->get('ubigeopartida');
      $des_ubi_part = DB::tABLE('cat_ubigeo')->where('ubi_cod',$ubigeopartida)->first();
      $direccionpartida = $request->get('direccionpartida');
      

      $ubigeollegada = $request->get('ubigeollegada');
      $des_ubi_lleg = DB::tABLE('cat_ubigeo')->where('ubi_cod',$ubigeollegada)->first();
      $direccionllegada = $request->get('direccionllegada');

      $correo = $request->get('correo');

        $cantidades = $request->get('cant');
        $unidades = $request->get('unid');
        $codpro = $request->get('codpro');
        $IdProducto = $request->get('IdProducto');
        $detpro = $request->get('detpro');

        $busmotivo = DB::tABLE('motivo_traslado')->where('IdMotivo',$motivo)->first();
        
        $numdoc = $request->get('numdoc');
            

          if(empty(trim($direccionpartida))){
              return response()->json(['estado'=>'error','mensaje'=>'INGRESAR LA DIRECCION DE PARTIDA']);
          }
          

          if(empty(trim($direccionllegada))){
              return response()->json(['estado'=>'error','mensaje'=>'INGRESAR LA DIRECCION DE LLEGADA']);
          }



        if($modalidad=='02'){

          if(empty(trim($placa))){
              return response()->json(['estado'=>'error','mensaje'=>'INGRESAR LA PLACA DEL VEHICULO']);
          }

          $tdicodtransportista = '';
          $ructransportista = '';
          $nombretransportista = '';

        }else{

              if(empty(trim($licencia))){
              return response()->json(['estado'=>'error','mensaje'=>'INGRESAR LA LICENCIA']);
          }

          $tdicodtransportista = $request->get('transportistatdicod');
          $ructransportista = $request->get('transportistanum');
          $nombretransportista = $request->get('transportistanom');

        }

      //Registrar el cliente enviado a través del formulario si no existe
        $cliente = Cliente::FirstOrCreate(['clinum'=>$cliruc,'rucemp'=>$rucemp],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$correo,'rucemp'=>$rucemp,'tdicod'=>$tdicod]);
        
       
        
        
      //Crear un objeto de la cabecera del comprobante para registrar los datos de la cabecera
        $cabecera = new guias_remision;
        $cabecera->IdEmpresa =  Auth::user()->IdEmpresa;
        $cabecera->tdocod = '09';
        $cabecera->fechaemision = $fecemi;
        $cabecera->tdicod = $tdicod;
        $cabecera->afec_stock = $afec_stock;
        $cabecera->ruccliente = $cliruc;
        $cabecera->IdMotivo = $motivo;
		    $cabecera->IdCpe_cabecera= $id_doc_ref;
        $cabecera->pesobruto = $request->get('peso');
        $cabecera->observacion = $request->get('observacion');
        $cabecera->serie_num_ref = $serie_num_ref;
        $cabecera->umecod = 'KMG';
        $cabecera->fechatraslado = $fechatraslado;
        $cabecera->ructransportista = $ructransportista;
        $cabecera->nombretransportista = $nombretransportista;
        $cabecera->tdicodtransportista = $tdicodtransportista;
        $cabecera->rucconductor = $rucconductor;
        $cabecera->nomconductor = $nombreconductor;
        $cabecera->tdicodconductor = $tdicodconductor;
        $cabecera->id_almacen = $id_almacen;
        $cabecera->ubigeollegada = $ubigeollegada;
        $cabecera->direccionllegada = $direccionllegada;
        $cabecera->desubigeollegada = $des_ubi_lleg->ubi_des;

        $cabecera->ubigeopartida = $ubigeopartida;
        $cabecera->direccionpartida = $direccionpartida;
        $cabecera->desubigeopartida = $des_ubi_part->ubi_des;
      
      


    
      
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

          $numdoc = $modnumcomp;
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

            $detalle = new guias_remision_detalle;
            $detalle->IdProducto = $IdProducto[$index];
            $detalle->IdProducto_rel = $IdProducto[$index];
            $detalle->procod = $codproducto;
            $detalle->pronom = $detpro[$index];
            $detalle->cantidad = $cantidades[$index];
            $detalle->peso ="0.00";
            $detalle->umecod = $ume;
            $detalle->IdCpe_guia =  $cabecera->IdCpe_guia; 
            $detalle->save();

            if($codpro[$index]!='0'){

                $dat_pro = DB::tABLE('productos')->where('IdProducto',$IdProducto[$index])->first();

                if($afec_stock=='1'){
                       DB::tABLE('movimientos_productos')->insert([

                        'IdProducto'=>$IdProducto[$index],
                        'precio'=>'',
                        'cantidad'=>$cantidades[$index],
                        'costo'=>$dat_pro->costo,
                        'mov_cab_id'=>'',
                        'stock'=>'',
                        'IdProducto_rel'=>$IdProducto[$index],
                        'IdCpe_guia'=>$cabecera->IdCpe_guia,
                        'com_cab_id'=>'',
                        'stock_inicial'=>'',
                        'serie'=>$cabecera->serieguia,
                        'numero'=>$cabecera->numeroguia,
                        'tdocod'=>$cabecera->tdocod,
                        'tipo'=>'3',
                        'mov_tip'=>'E',
                        'id_empresa_negocio'=>$id_negocio,
                        'id_almacen'=>$id_almacen,
                        'fecha_mov'=>$fecemi,
                       

                    ]);


                  $mov_cal_stock = new Almacen;
                  $mov_cal_stock->movimiento_calcular_stock($IdProducto[$index],$fecemi);
              }

            }
        
         


        }



         
        
     

        self::enviar_guia_sunat($cabecera->IdCpe_guia);
        self::generarpdfguia($cabecera->IdCpe_guia);
     
     

      DB::commit();

        if($request->ajax()) {
          return response()->json(['mensaje' => 'Registrado']);
        }

    }catch (\Exception $e) {

    DB::rollback();
   //dd($e);
    return response()->json(['estado'=>'error','mensaje'=>'Revisar los datos de la guía de remisión']);

  }


     
  }


  public function anular_guia_remision(Request $request,$comp,$motivo){
      
      $rucemp = Auth::user()->IdEmpresa;

      $empresa = Empresa::findOrFail($rucemp);
      
      $fec_gen = now()->format('Y-m-d');

      $cabecera =  guias_remision::findOrFail($comp);
      $cabecera->ccacodsun = '8';
      $cabecera->ccabaj = $fec_gen;
      $cabecera->save();
      
      $detalle = guias_remision_detalle::where('IdCpe_guia',$comp)->get();

      if($cabecera->afec_stock=='1'){

        foreach($detalle as $det){

             DB::tABLE('movimientos_productos')->where('IdCpe_guia',$comp)->delete();
         
              if(!empty($det->IdProducto)){

                $producto = Productos::findOrFail($det->IdProducto);
                
                if(empty($producto->pro_rel)){

                  $id_prod = $producto->IdProducto;

                }else{
              
                  $id_prod = $producto->pro_rel;

                }

                $stockprod = DB::TABLE('producto_stock')
                ->where('IdProducto',$id_prod)
                ->where('id_empresa_negocio',$cabecera->id_empresa_negocio)
                ->where('id_almacen',$cabecera->id_almacen)
                ->first();


                $stockprod_act = DB::TABLE('producto_stock')
                ->where('pro_sto_id',$stockprod->pro_sto_id)
                ->update(['stock'=>$stockprod->stock+($det->cantidad*$producto->factor)]);

                $stock = $stockprod->stock+($det->cantidad*$producto->factor);
            
                
                $mov_cal_stock = new Almacen();
                $mov_cal_stock->movimiento_calcular_stock($id_prod,$fec_gen);

              }
          
  


        }

      }


       if($request->ajax()) {
          return response()->json(['mensaje' => 'anulado']);
        }


}



  public function enviar_guia_sunat($codfact){

    $nom_guia = self::generar_xml_guia($codfact);
    //self::enviar_sunat($codfact);

      $zip = new ZipArchive;
   
        $fileName = $nom_guia.'.zip';
   
        if ($zip->open(public_path($fileName), ZipArchive::CREATE) === TRUE)
        {
     
                $zip->addFile(public_path().'/xml/'.$nom_guia.'.xml',  $nom_guia.'.xml');
           
             
            $zip->close();
        }
      
      
     
        $token = self::generar_token_api_sunat();

        self::enviar_gre_sunat($fileName,$nom_guia,$token,$codfact);

        //return response()->download(public_path($fileName));

        return Redirect::to('/guiasremision');



  }


 public function generar_token_api_sunat(){

$access_token = '';

    try{

        $dat_emp = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

        $client_id = $dat_emp->client_id;
        $client_secret=  urlencode($dat_emp->client_secret);
        $username= $dat_emp->IdEmpresa.$dat_emp->wsusuario;
        $password= $dat_emp->claveSunat;

     


       // dd($client_secret);


        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api-seguridad.sunat.gob.pe/v1/clientessol/'.$client_id.'/oauth2/token/',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => 'grant_type=password&scope=https://api-cpe.sunat.gob.pe&client_id='.$client_id.'&client_secret='.$client_secret.'&username='.$username.'&password='.$password.'',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded',
            'Cookie: dtCookie=v_4_srv_6_sn_CFBCE2E70590A7603C0F575510D344F1_perc_100000_ol_0_mul_1_app-3Aea7c4b59f27d43eb_1; TS019e7fc2=014dc399cb4ced52141499a733c5610fd2c111a8b3d1cda763c9cf2013e704030d9239d3573f762febc0157967b9f3d2083cfaa442afe6d8b36d9d598ffc10bf70b3d5c40f'
          ),
        ));

        $response = curl_exec($curl);

       // dd($response);
        curl_close($curl);

        $respuesta = json_decode($response, true);

       // dd($respuesta);

        $access_token=$respuesta['access_token'];

         return $access_token;

    }catch(\Exception $e){

          return $access_token;
      
    }
   

 
  }


  public function enviar_gre_sunat($fileName,$nom_guia,$token,$codfact){



        $zip_base64 = base64_encode(file_get_contents(public_path($fileName)));
        
        $hash_file = hash_file('sha256', public_path($fileName));


       $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api-cpe.sunat.gob.pe/v1/contribuyente/gem/comprobantes/'.$nom_guia,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{ "archivo" : {
          "nomArchivo": "'.$fileName.'" ,
          "arcGreZip": "'.$zip_base64.'" ,
          "hashZip": "'.$hash_file.'" 
        }
        }',
        CURLOPT_HTTPHEADER => array(
          'Content-Type: application/json',
          'Authorization: Bearer '.$token.''
        ),
      ));

      $response = curl_exec($curl);

      curl_close($curl);

      $respuesta = json_decode($response, true);


      if(!empty($respuesta['numTicket'])){

        
          $bus_gui = DB::tABLE('guias_remision')->where('IdCpe_guia',$codfact)->first();

          iF(empty($bus_gui->numTicket) or $bus_gui->ccacodsun!='0'){
              DB::tABLE('guias_remision')->where('IdCpe_guia',$codfact)->update(['numTicket'=>$respuesta['numTicket']]);
              $ticket= $respuesta['numTicket'];
          }else{
            $ticket = $bus_gui->numTicket;
         }
      

          self::consultar_gre_ticket($ticket,$token,$codfact);

          return 'Consultado';

      }else{

           DB::tABLE('guias_remision')->where('IdCpe_guia',$codfact)->update(['ccadessun'=>$respuesta['message']]);
            return  $respuesta['message'];

      }
    

  }

  public function consultar_gre_ticket($ticket,$token,$codfact){


       $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api-cpe.sunat.gob.pe/v1/contribuyente/gem/comprobantes/envios/'.$ticket,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
          'Content-Type: application/json',
          'Authorization: Bearer '.$token.''
        ),
      ));

      $response = curl_exec($curl);

      curl_close($curl);


      $respuesta = json_decode($response, true);

      $codRespuesta ='';
      $numError ='';
      $desError = '';
      $arcCdr ='';
      $indCdrGenerado = '';
      $estado = '';

      if(isset($respuesta['codRespuesta'])){
         $codRespuesta =  $respuesta['codRespuesta'];

         if($codRespuesta=='0'){
          $estado='ACEPTADO';
         }
      }

      if(isset($respuesta['error']['numError'])){
         $numError =  $respuesta['error']['numError'];
      }

      if(isset($respuesta['error']['desError'])){
         $desError =  $respuesta['error']['desError'];
      }

      if(isset($respuesta['arcCdr'])){
         $arcCdr =  $respuesta['arcCdr'];
      }

      if(isset($respuesta['indCdrGenerado'])){
         $indCdrGenerado =  $respuesta['indCdrGenerado'];
      }
     
     
  
        DB::tABLE('guias_remision')->where('IdCpe_guia',$codfact)
        ->update([

          'codRespuesta'=>$codRespuesta,
          'ccacodsun'=>$codRespuesta,
          'numError'=>$numError,
          'desError'=>$desError,
          'ccadessun'=>$estado,
          'arcCdr'=>$arcCdr,
          'indCdrGenerado'=>$indCdrGenerado

          ]);

        //return Redirect::to('/guiasremision');

  }



  public function consultar_ticket_gre($codfact,$ticket=''){

      $token = self::generar_token_api_sunat();

   
      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api-cpe.sunat.gob.pe/v1/contribuyente/gem/comprobantes/envios/'.$ticket,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
          'Content-Type: application/json',
          'Authorization: Bearer '.$token.''
        ),
      ));

      $response = curl_exec($curl);

      curl_close($curl);


      $respuesta = json_decode($response, true);

      $codRespuesta ='';
      $numError ='';
      $desError = '';
      $arcCdr ='';
      $indCdrGenerado = '';
      $estado = '';

      if(isset($respuesta['codRespuesta'])){
         $codRespuesta =  $respuesta['codRespuesta'];

         if($codRespuesta=='0'){
          $estado='ACEPTADO';
         }
      }

      if(isset($respuesta['error']['numError'])){
         $numError =  $respuesta['error']['numError'];
      }

      if(isset($respuesta['error']['desError'])){
         $desError =  $respuesta['error']['desError'];
      }

      if(isset($respuesta['arcCdr'])){
         $arcCdr =  $respuesta['arcCdr'];
      }

      if(isset($respuesta['indCdrGenerado'])){
         $indCdrGenerado =  $respuesta['indCdrGenerado'];
      }
     
     
  
        DB::tABLE('guias_remision')->where('IdCpe_guia',$codfact)
        ->update([

          'codRespuesta'=>$codRespuesta,
          'ccacodsun'=>$codRespuesta,
          'numError'=>$numError,
          'desError'=>$desError,
          'ccadessun'=>$estado,
          'arcCdr'=>$arcCdr,
          'indCdrGenerado'=>$indCdrGenerado

          ]);

        if($codRespuesta=='0'){
          self::guardar_cdr_guia($codfact);
        }

        return Redirect::to('/guiasremision');

  }


  public function guardar_cdr_guia($codfact){

      try{

      $bus_guia = DB::tABLE('guias_remision')->where('IdCpe_guia',$codfact)->first();

      $nom_guia = Auth::user()->IdEmpresa.'-'.$bus_guia->tdocod.'-'.$bus_guia->serieguia.'-'.$bus_guia->numeroguia;

      $cdr_guia =public_path().'/R-'.$nom_guia.'.xml';

   
       $zip = new ZipArchive;
   
        $fileName = 'R-'.$nom_guia.'.zip';
      

      file_put_contents(public_path($fileName), base64_decode($bus_guia->arcCdr));

        /*if ($zip->open(public_path($fileName), ZipArchive::CREATE) === TRUE)
        {
                
                $zip->addFromString($fileName, base64_decode($bus_guia->arcCdr));

          
           
             
            $zip->close();
        }
      */
      
       $zip_cdr = new ZipArchive;

       if ($zip->open(public_path($fileName)) === TRUE)
        {
                
                 $zip->extractTo(public_path());

          
           
             
            $zip->close();
        }

      
       
            $DOM = new DOMDocument('1.0', 'utf-8');
        $DOM->loadXML(file_get_contents($cdr_guia));
        $cadena_qr = $DOM->getElementsByTagName('DocumentDescription')->item(0)->nodeValue;

        DB::tABLE('guias_remision')->where('IdCpe_guia',$codfact)->update(['cadena_qr'=>$cadena_qr]);

          return response()->download(public_path($fileName));
              }catch(\Exception $e){

       return Redirect::to('/guiasremision');

    }


  }


  public function generar_guia_transportista(Request $request)
  {
    

    //   $output=View::make('formato_xml.guia_trasportista_xml')->with(compact('proc','sites'))->render();
          

        $output= \View::make('formato_xml.guia_transportista_xml')->render();  
        // Usar helper view() y renderizar el XML
        $output = view('formato_xml.guia_transportista_xml')->render();

        //add xml header - blade does not seem to like it
        $xml = "<?xml version=\"1.0\" ?>\n" . $output;
        // Agregar encabezado XML con encoding
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" . $output;

        $archivo = fopen('archivo.xml','a');
        fputs($archivo,$xml); 
        fclose($archivo);  
        // Se recomienda usar el Storage de Laravel en lugar de fopen. 
        // Además, es mejor generar un nombre único por cada guía.
        $nombreArchivo = 'guia_transportista_' . now()->format('YmdHis') . '.xml';
        \Illuminate\Support\Facades\Storage::disk('local')->put('guias/' . $nombreArchivo, $xml);

        // Opcionalmente devolver el XML como respuesta al navegador
        return response($xml, 200)->header('Content-Type', 'text/xml');
  }


}
