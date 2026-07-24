<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\TipoIGV;
use MasterSoft\Empresa;
use MasterSoft\Cliente;
use MasterSoft\cpe_cabecera;
use MasterSoft\documento_relacionado;
use MasterSoft\unidad_medida;
use MasterSoft\cpe_detalle;
use MasterSoft\cpe_baja;
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

use PDF;
use DB;

class VentasController extends Controller
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
		$IdEmpresa = Auth::user()->IdEmpresa;
		$fechaini = now()->modify('first day of this month');
        $fechafin = now()->modify('last day of this month');
			
		$ventas = DB::table('cpe_cabecera as cpe_c')->select('tdodes','serdoc','numdoc','ccafem','ccafve','ccaitv','monnom','clinom')
               ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
               ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
			   ->join('cliente as c','cpe_c.ccandi','=','c.clinum')
               ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
               ->where('cpe_c.ccafem','>=',$fechaini)
               ->where('cpe_c.ccafem','<=',$fechafin)
               ->where('cpe_c.IdEmpresa','=',$IdEmpresa)->get();
				
        return view('empresas.ventas.index',['ventas'=>$ventas]);

        
         
    }

     public function listarpagos(Request $request)
    {
    $IdEmpresa = Auth::user()->IdEmpresa;
    $fechaini = now()->modify('first day of this month');
        $fechafin = now()->modify('last day of this month');
      
    $ventas = DB::table('cpe_cabecera as cpe_c')->select('tdodes','serdoc','numdoc','ccafem','ccafve','ccaitv','monnom','clinom')
               ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
               ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
         ->join('cliente as c','cpe_c.ccandi','=','c.clinum')
               ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
               ->where('cpe_c.ccafem','>=',$fechaini)
               ->where('cpe_c.ccafem','<=',$fechafin)
               ->where('cpe_c.IdEmpresa','=',$IdEmpresa)->get();
        
        return view('empresas.ventas.pagos',['ventas'=>$ventas]);
         
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($tdocod)
    {

        // consultar tipos de  IGV
        $ncdcod= $tdocod;
        $igv = DB::table('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();

        // consultar unidades de medida
        $unidades = DB::table('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        // consultar tipo de operaciones
        $operaciones = DB::table('tipo_operacion')->where('topest','=','Activo')
        ->orderBy('topcod','asc')->get();

        // consultar tipos de documentos de identidad
        $docidentidad = DB::table('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

        //consultar tipo de documento 
        $doccomprobante = DB::table('tipo_documento')->where('tdoest','=','Activo')->orderBy('tdocod','asc')->get();

        // obtener el ruc de la empresa en la cual se logueo
        $rucemp = trim(Auth::user()->IdEmpresa);

        // consultar los clientes que le pertenece a la empresa
        $clientes= DB::table('cliente')->where('rucemp','=',$rucemp)->where('cliest','=','Activo')
        ->orderby('clinom','asc')->get();

        //consultar productos que le pertenece a la empresa
        $productos= DB::table('productos')->where('IdEmpresa','=',$rucemp)->where('proest','=','Activo')
        ->orderby('pronom','asc')->get();

        // consultar tipos de monedas
        $monedas = DB::table('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

        // consultar la serie y numero de factura

        if($tdocod == '01'){
          $senudoc = DB::table('empresa')->select('FseEmpresa','FnuEmpresa')->where('IdEmpresa','=',$rucemp)->first(); 
        }elseif ($tdocod =='03') {
         $senudoc = DB::table('empresa')->select('BseEmpresa','BnuEmpresa')->where('IdEmpresa','=',$rucemp)->first(); 
        }elseif ($tdocod =='07') {
         $nota = DB::table('tipo_nota_credito')->where('ncest','=','Activo')
        ->orderBy('nccod','asc')->get();
        }elseif ($tdocod =='08') {
         $nota = DB::table('tipo_nota_debito')->where('ndest','=','Activo')
        ->orderBy('ndcod','asc')->get(); 
        }
        

        $fecha = now()->format('m/d/Y');
        //return $senudoc;
        if($tdocod=='01'){
            return view('empresas.comprobantes.nuevafactura',['igv'=>$igv,'monedas'=>$monedas,'unidades'=>$unidades,'operaciones'=>$operaciones,'docidentidad'=>$docidentidad,'clientes'=>$clientes,'fecha'=>$fecha,'senudoc'=>$senudoc,'tdocod'=>$tdocod,'productos'=>$productos,'doccomprobante'=>$doccomprobante]);
        }elseif($tdocod=='03'){
             return view('empresas.comprobantes.nuevaboleta',['igv'=>$igv,'monedas'=>$monedas,'unidades'=>$unidades,'operaciones'=>$operaciones,'docidentidad'=>$docidentidad,'clientes'=>$clientes,'fecha'=>$fecha,'senudoc'=>$senudoc,'tdocod'=>$tdocod,'productos'=>$productos,'doccomprobante'=>$doccomprobante]);
          }elseif($tdocod=='07'){
             return view('empresas.comprobantes.nuevanotacredito',['igv'=>$igv,'monedas'=>$monedas,'unidades'=>$unidades,'operaciones'=>$operaciones,'docidentidad'=>$docidentidad,'clientes'=>$clientes,'fecha'=>$fecha,'tdocod'=>$tdocod,'productos'=>$productos,'doccomprobante'=>$doccomprobante,'nota'=>$nota,'ncdcod'=> $ncdcod]);
           }elseif($tdocod=='08'){
             return view('empresas.comprobantes.nuevanotadebito',['igv'=>$igv,'monedas'=>$monedas,'unidades'=>$unidades,'operaciones'=>$operaciones,'docidentidad'=>$docidentidad,'clientes'=>$clientes,'fecha'=>$fecha,'tdocod'=>$tdocod,'productos'=>$productos,'doccomprobante'=>$doccomprobante,'nota'=>$nota,'ncdcod'=> $ncdcod]);
          }
      
       
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

      // Guardar en variables los datos que se enviaron a través del formulario
        $tdicod = $request->get('tdicod');
        $cliruc = $request->get('clinum');
        $numcomp= $request->get('numdoc');
        $sercomp= $request->get('serdoc');
        $topcod = $request->get('topcod');
        $clinom = $request->get('clinom');
        $fecemi = $request->get('fecEmi');
        $fecven = $request->get('fecVen');
        $clidir = $request->get('clidir');
        $clicor = $request->get('clicor');
        $mondoc = $request->get('mondoc');
        $descglb = $request->get('totdesc');
        $exon = $request->get('exon');
        $inaf = $request->get('inaf');
        $grav = $request->get('grav');
        $igv = $request->get('igv');
        $isc = $request->get('isc');
        $grat = $request->get('grat');
        $otrosc = $request->get('otrosc');
        $otros = $request->get('otros');
        $total = $request->get('total');
        $tdocod = $request->get('txt_tdocod');
        $obser = $request->get('obser');
        $tipcambio = $request->get('camdoc');
        $cantidades = $request->get('cant');
        $unidades = $request->get('unid');
        $codpro = $request->get('codpro');
        $detpro = $request->get('detpro');
        $vunit = $request->get('vunit');
        $vigv = $request->get('vigv');
        $tigv = $request->get('tigv');
        $vsub = $request->get('vsub');
        $vtot = $request->get('vtot');
        $rucemp = trim(Auth::user()->IdEmpresa);
        $numdoc = str_pad($request->get('numdoc'),8,"0", STR_PAD_LEFT);
        $exp=$request->get('exp');
        $puni = $request->get('preuni');

        $moneda = DB::table('moneda')->where('moncod','=',$mondoc)->first();


      //Registrar el tipo de cambio enviado a través del formulario si no existe.
        if($mondoc !='1'){
          $camdoc = $request->get('camdoc');
          $tipcam = tipocambio::FirstOrCreate(['FecTipCambio'=>$fecemi],['CamCompra'=>$camdoc]);
        }else{
          $camdoc=0;
        }
        
      //Registrar el cliente enviado a través del formulario si no existe
        $cliente = Cliente::FirstOrCreate(['clinum'=>$cliruc],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$clicor,'rucemp'=>$rucemp,'tdicod'=>$tdicod]);
        
      //Crear un objeto de la cabecera del comprobante para registrar los datos de la cabecera
        $cabecera = new cpe_cabecera;
        $cabecera->tdocod = $request->get('tdocod');
        $cabecera->topcod = $request->get('topcod');
        $cabecera->ccafem = $request->get('fecEmi');
        $cabecera->ccafve = $request->get('fecVen');
        $cabecera->ccaobs = $request->get('obser');
        //$cabecera->ccacde = $request->get();
        $cabecera->tdicod = $request->get('tdicod');
        $cabecera->ccandi = $request->get('clinum');
        $cabecera->ccanom = $request->get('clinom');
        $cabecera->moncod = $request->get('mondoc');
        $cabecera->tipcambio = $camdoc;
        $cabecera->ccacar = $request->get('otrosc');
        $cabecera->ccatde = $request->get('desc');
        $cabecera->ccatvg = $request->get('grav');
        $cabecera->ccatvgr = $request->get('grat');
        $cabecera->ccatvi = $request->get('inaf');
        $cabecera->ccatve = $request->get('exon');
        $cabecera->ccaigv = $request->get('igv');
        $cabecera->ccaisc = $request->get('isc');
        $cabecera->ccaotr = $request->get('otros');
        $cabecera->ccaitv = $request->get('total');
        $cabecera->serdoc= $request->get('serdoc');
        $cabecera->numdoc = $numdoc;
        $cabecera->codunique = Auth::user()->IdEmpresa.''.$request->get('tdocod').''.$request->get('serdoc').''.$request->get('numdoc');
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->IdEmpresa = Auth::user()->IdEmpresa;
        //$cabecera->save();

         //Actualizar la serie y/o el nÃºmero del documento a emitir
      /*  if($tdocod=='01'){
          $empresa = Empresa::findOrFail($rucemp);
          $empresa->FseEmpresa = $request->get('serdoc');
          $empresa->FnuEmpresa = $request->get('numdoc');
          $empresa->update();
        }elseif($tdocod=='03'){
          $empresa = Empresa::findOrFail($rucemp);
          $empresa->BseEmpresa = $request->get('serdoc');
          $empresa->BnuEmpresa = $request->get('numdoc');
          $empresa->update();
        } */

        $empresa = Empresa::findOrFail($rucemp);
        if($tdocod=='01'){
          if( $empresa->FnuEmpresa == $numcomp){
              $modnumcomp = $numcomp+1;
          }else{
              $modnumcomp = $numcomp;
          }

          $empresa->FseEmpresa = $sercomp;
          $empresa->FnuEmpresa = $modnumcomp;
          $empresa->update();

          $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
          $cabecera->serdoc= $sercomp;
          $cabecera->numdoc = $numdoc;
          $cabecera->save();
        }elseif($tdocod=='03'){
          if( $empresa->FnuEmpresa == $numcomp){
              $modnumcomp = $numcomp+1;
          }else{
              $modnumcomp = $numcomp;
          }

          $empresa->BseEmpresa = $sercomp;
          $empresa->BnuEmpresa = $modnumcomp;
          $empresa->update();

          $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
          $cabecera->serdoc= $sercomp;
          $cabecera->numdoc = $numdoc;
          $cabecera->save();
        }
        //Ruta donde se guardarán los archivos cab y det.
        $raiz = '/opt/fs/'.$cabecera->IdEmpresa.'/sunat_archivos/sfs/DATA/';
        
     


        //Generar el detalle del comprobante
        $i=0;
        foreach( $unidades as $index => $ume ) {
            $i=$i+1;
            $imod=str_pad($i,3,"0", STR_PAD_LEFT);
            $detalle = new cpe_detalle;
            $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera; 
            $detalle->umecod = $ume;
            $detalle->cdecan = $cantidades[$index];
            $pos = strpos($codpro[$index],'|');
            $codproducto = substr($codpro[$index], 0, $pos);
            $detalle->procod = $codproducto;
            $detalle->cdepsu = $codproducto;
            $detalle->cdedes = $detpro[$index];
            $detalle->cdevun = $vunit[$index];
            $detalle->cdeigv = $vigv[$index];
            $detalle->tigcod = $tigv[$index];
            $detalle->cdepve = $vsub[$index];
            $detalle->cdevve = $vtot[$index];
            $detalle->cdepuni = $puni[$index];
            
            
            if($detalle->tigcod=='10'){
              $tiptri='1000'; //IGV Impuesto Generarl a las ventas
            }elseif($detalle->tigcod=='20'){
               $tiptri='9997'; //exonerado
            }elseif($detalle->tigcod=='30'){
              $tiptri ='9998'; //inafecto
            }elseif($detalle->tigcod=='17'){
              $tiptri ='1016'; //Impuesto a la venta de arroz pilado
            }elseif($detalle->tigcod=='40'){
              $tiptri ='9995'; //exportación
            }else{
              $tiptri='9996'; //Gratutito
            }
            //Guardar en una variable los items en el archivo .det
            $codumecin = unidad_medida::findOrFail($ume);
         
            $detallejson[] = array( "numeroItem"        =>$imod,
                        "codigoProducto"            => $detalle->procod,
                        "descripcionProducto"       => $detalle->cdedes,
                        "cantidadItems"             => $detalle->cdecan,
                        "unidad"                    => $codumecin->umecin,
                        "valorUnitario"             => $detalle->cdevun,
                        "precioVentaUnitario"       => $detalle->cdepuni,
                        "totalImpuestos"            => array(
                          array(
                            "idImpuesto"            => $tiptri,
                            "montoImpuesto"         => $detalle->cdeigv,
                            "tipoAfectacion"        => $detalle->tigcod
                          ),
                        ),
                        "valorVenta"                => $detalle->cdepve);  

            $detalle->tiptri = $tiptri;
            $detalle->save();
        
        
        }
        
         
        //Registrar los documentos relacionados del comprobante
        $docrel = $request->get('tdr');
        $docrser = $request->get('tdrser');
        $docrnum = $request->get('tdrnum');

        if(!empty($docrel) && !empty($docrser) && !empty($docrnum)){
          foreach( $docrel as $index => $tdr ) 
          {
            if(!empty($docrel[$index]) && !empty($docrser[$index]) && !empty($docrnum[$index])){
               $docrenum = str_pad($docrnum[$index],8,"0", STR_PAD_LEFT);
               $drsernum = "$docrser[$index]-$docrnum[$index]";
               
               $guia = DB::table("documento_relacionado")->where('tdocod','=',$docrel[$index])->where('dorser','=',$docrser[$index])->where('dornum','=',$docrenum)->first();

               if($guia==''){

                    $docurela[] = array( "tipoDocRelacionado"=> $docrel[$index],
                    "numeroDocRelacionado"=> $drsernum
                    );
               } 
            }
          }

        }else{
          $docurela[] = array( "tipoDocRelacionado"=> "",
                    "numeroDocRelacionado"=> ""
                    );
        }
        //FIN DOC RELACIONADO

        // Monto en letras
        $totalletras= MontoLetras::convertir(number_format($cabecera->ccaitv,'2','.',''),$moneda->monnom,'Centimos');

        //$cabfile es el nombre con el cual se guarda el archivo que contiene los datos del comproabnte
        $cabfile =  $cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.json'; 

        //Consultar los datos de la empresa emisora
        $datemp = DB::table("empresa")->where("IdEmpresa","=",$rucemp)->first();

        if($tdocod=='01'){
           $nomcomp = "factura";
        }elseif($tdocod=='03'){
           $nomcomp = "boleta";
        }

        //inicio json
        $data = array ( $nomcomp => array(
          "IDE"=> array(
              "numeracion" => $sercomp.'-'.$numdoc, 
              "fechaEmision"=> $fecemi,
              "codTipoDocumento"=> $tdocod,
              "tipoMoneda"=> $mondoc,
              "fechaVencimiento"=> $fecven
          ),
          "EMI"=> array(
              "tipoDocId"=> $datemp->TipDoc,
              "numeroDocId"=> $datemp->IdEmpresa,
              "razonSocial"=> $datemp->NomEmpresa,
              "direccion"=> $datemp->DirEmpresa,
              "telefono"=> $datemp->TelEmpresa,
              "correoElectronico"=> $datemp->CorEmpresa
          ),
      
          "REC" => array( 
            "tipoDocId"=> $tdicod,
            "numeroDocId"=> $cliruc,
            "razonSocial"=> $clinom,
            "direccion"=> $clidir,
            "correoElectronico"=> $clicor
          ),

          "DRF" => $docurela,
        
           "CAB" => array (
                "gravadas" => array(
                   "codigo"=> "1001",
                   "totalVentas"=> $grav
                ),
                "inafectas" => array(
                   "codigo"=> "1002",
                   "totalVentas"=> $inaf
                ),
                "exoneradas" => array(
                   "codigo"=> "1003",
                   "totalVentas"=> $exon
                ),
                "gratuitas" => array(
                   "codigo"=> "1004",
                   "totalVentas"=> $grat
                ),
                "exportadas" => array(
                   "codigo"=> "1000",
                   "totalVentas"=> $exp
                ),
                "totalImpuestos"=>array(
                   array(
                   "idImpuesto"=>"1000",
                   "montoImpuesto"=> $igv
                   )
                ),      
                "importeTotal"=> $total,
                "descuentosGlobales"=>$descglb,
                "tipoOperacion"=> $topcod,
                "leyenda"=>array(
                  array(
                   "codigo"=> "1000",
                   "descripcion"=> $totalletras
                  )

                )
                
            ),

            "DET" => $detallejson,
            "ADI" => array(
                array(
                   "tituloAdicional"=> "Observaciones", 
                   "valorAdicional"=> $obser
                )
                
            )
      )
      ); 
      //fin json

        //Generar el archivo JSON del comprobante que se enviará al OSE
        $data_json = json_encode($data,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        //Crear el archivo cab e insertar el contenido y cerrar el archivo
        //$archivo = fopen($raiz.$cabfile, "a");
        $archivo = fopen($cabfile, "a");
        fputs($archivo,$data_json);
        fclose($archivo);
      
       $leer_respuesta = self::webserviceonline($data_json,$cabfile);
           
        
       /* if (isset($leer_respuesta['errors'])) {
            unlink($cabfile);
            if($leer_respuesta['codigo']=='23'){
              $leer_respuesta['errors']="El Documento ya existe.";
            }

            if($leer_respuesta['codigo']=='24'){
              $leer_respuesta['errors']="El documento indicado no existe o no fue enviado.";
            }*/

          $codfact = $cabecera->IdCpe_cabecera; 
        
          //Consultar la cabecera del comprobante para obtener los datos que llevará el comprobante pdf
		      $cabpdf= DB::table('cpe_cabecera as c')->join('moneda as m','c.moncod','=','m.moncod')->join('empresa as e','c.IdEmpresa','=','e.IdEmpresa')->where('IdCpe_cabecera','=',$codfact)->where('c.IdEmpresa','=',$rucemp)->first();
          
          //Consultar los datos del cliente para el comprobante digital
          $cliente= DB::table('cliente as cli')->join('cpe_cabecera as c','c.ccandi','=','cli.clinum')->where('IdCpe_cabecera','=',$codfact)->where('cli.rucemp','=',$rucemp)->where('cli.clinum','=',$cliruc)->first();

          //consultar el detalle del comprobante para que aparesca en comprobante digital
          $detpdf= DB::table('cpe_detalle as d')->join('cpe_cabecera as c','d.IdCpe_cabecera','=','c.IdCpe_cabecera')->where('c.IdCpe_cabecera','=',$codfact)->get();
          
          //Nombre que tendrá el comprobante pdf
          $nompdffile =  $cabpdf->IdEmpresa.'-'.$cabpdf->tdocod.'-'.$cabpdf->serdoc.'-'.$numdoc.'.pdf'; 

          //Ruta donde se guardará el pdf
          $rutpdfile = public_path().'/'.$rucemp.'/';

		if (file_exists($rutpdfile))
      {
          //De acuerdo al tipo de documento se generará el comprobante digital Boleta=>01 - Factura=>01
          if($tdocod=='01'){
           $view = \View::make('empresas.comprobantes.'.$rucemp.'.facturapdf', compact('cabpdf','detpdf','cliente'));
          }elseif($tdocod=='03'){
            $view = \View::make('empresas.comprobantes.'.$rucemp.'.boletapdf', compact('cabpdf','detpdf','cliente')); 
          }

          //Crear el pdf y guardar
          $pdf = \App::make('dompdf.wrapper');
          $contenido = $view->render();
          $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutpdfile.$nompdffile);
          //$pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($nompdffile);
	  }
       return Redirect::to('/SisFact')->with('success',$leer_respuesta['responseContent'].'-'.$leer_respuesta['responseCode']);

       
    }


    public function facturapdf($codfact,$doccod,$idcabecera)
    {

      $rucemp = trim(Auth::user()->IdEmpresa);
      $rutpdfile = public_path().'/'.$rucemp.'/';
     // $file= $rutpdfile.$codfact.'.pdf';
      //$file= $codfact.'.pdf';
      $file= $rutpdfile.$codfact.'.pdf';

      if (file_exists($file))
      {
        $headers = array(
              'Content-Type: application/pdf',
            );

        return response()->download($file);
      }

      if($doccod=='07' || $doccod=='08'){
        return Redirect::to('/listarnotas/'.$idcabecera);
      }elseif($doccod=='0'){
        return Redirect::to('/listarbajas/'.$idcabecera);
      }else{
         return Redirect::to('/SisFact');
      }
    }


    public function buscarcomprobante(Request $request){
      $search = $request->term;

      $ser = substr($search,strpos($search,'-')-4,4);
      $num = substr($search,strpos($search,'-')+1,8);

      $rucemp = trim(Auth::user()->IdEmpresa);
      $comprobante = DB::table('cpe_cabecera as cab')
      ->join('cliente as cli','cab.ccandi','=','cli.clinum')
      ->join('tipo_documento as tdo','cab.tdocod','=','tdo.tdocod')
      ->join('tipo_documento_identidad as tdi','cab.tdicod','=','tdi.tdicod')
      ->join('moneda as mon','cab.moncod','=','mon.moncod')
      ->join('tipo_operacion as top','cab.topcod','=','top.topcod')
      ->where('serdoc','=',$ser)
      ->where('numdoc','=',$num)
      ->where('IdEmpresa','=',$rucemp)
      ->where('codhash','<>',NULL)
      //->orwhere(DB::raw('substr(respse,1,2)'),'=','04')
      ->take(10)->get();

      $results = array();

      foreach($comprobante as $c => $comp){
        $sernum=$comp->serdoc.'-'.$comp->numdoc;
        $results[] = ['value'=>$sernum,'serdoc'=>$comp->serdoc,'numdoc'=>$comp->numdoc,'clinum'=>$comp->ccandi,'clinom'=>$comp->ccanom,'clidir'=>$comp->clidir,'clicor'=>$comp->clicor,'tdomod'=>$comp->tdodes,'tdides'=>$comp->tdides,'monnom'=>$comp->monnom,'tipcambio'=>$comp->tipcambio,'topdes'=>$comp->topdes,'tdicod'=>$comp->tdicod,'tdocod'=>$comp->tdocod,'moncod'=>$comp->moncod,'fecemi'=>$comp->ccafem,'idcabecera'=>$comp->IdCpe_cabecera];
      }
      return response()->json($results);
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
      ->join('cliente as cli','cab.ccandi','=','cli.clinum')
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
      $ruc = Cliente::where('clinum','like','%'.$search.'%')->where('cliest','=','Activo')->where('rucemp','=',$rucemp)->orwhere('clinom','like','%'.$search.'%')->where('rucemp','=',$rucemp)->where('cliest','=','Activo')->take(10)->get();
      $results = array();

      foreach($ruc as $cli => $cliente){
        $numnom=$cliente->clinum.'|'.$cliente->clinom;
        $results[] = ['value'=>$numnom,'num'=>$cliente->clinum,'nom'=>$cliente->clinom,'dir'=>$cliente->clidir,'cor'=>$cliente->clicor,'tdicod'=>$cliente->tdicod];
      }
      return response()->json($results);
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


    public function consultarproducto(Request $request){
      $search = $request->term;       
       $rucemp = trim(Auth::user()->IdEmpresa);
      $productos= DB::table('productos')->where('pronom', 'like','%'.$search.'%')->where('IdEmpresa','=',$rucemp)->where('proest','=','Activo')
        ->orderby('pronom','asc')->get();

      $results = array();
      foreach($productos as $pro){
        $codnom = $pro->procod.'|'.$pro->pronom;
        $results[] = ['value'=>$codnom,'pronom'=>$pro->pronom,'provun'=>$pro->provun,'umecod'=>$pro->umecod];
      }

      return response()->json($results);
    }


    public function consultartdi(Request $request){
      
      $search = $request->term;       
      
      $docidentidad = DB::table('tipo_documento_identidad')->where('tdides', 'like','%'.$search.'%')->where('tdides','like','%'.$search.'%')->where('tdiest','=','Activo')->get();

       $results = array();
      foreach($docidentidad as $tdi){
        $results[] = ['id'=>$tdi->tdicod,'text'=>$tdi->tdides];
      }

      return response()->json($results);

    }

    public function verificarcomprobante(Request $request){
      $codunique = $request->get('codunique');

      $comprobante= DB::table('cpe_cabecera')->where('codunique','=',$codunique)->first();
     
     if($comprobante!= ''){
        $respuesta = 'false';
     }else {
        $respuesta = 'true';
      }
      return $respuesta;
    }

  
  

  public function registrarnota(Request $request){

        $rucemp = trim(Auth::user()->IdEmpresa);
        $serdoc = $request->get('serdoc');
        $numdoc = str_pad($request->get('numdoc'),8,"0", STR_PAD_LEFT);
        //$sercomp = $request->get('serdoc');
        $numcomp = $request->get('numdoc');
        $serdocmod = $request->get('serdocmod');
        $numdocmod = str_pad($request->get('numdocmod'),8,"0", STR_PAD_LEFT);
        $tdicod= $request->get('tdicod');
        $tipdoc = $request->get('tdo_cod');
        $tipnot = $request->get('tipnot');
        $tipmon = $request->get('tipmon');
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
        $exp=$request->get('exp');
        $puni = $request->get('preuni');
        $descglb = $request->get('desc');
        $tdomod = $request->get('tdomod');
        $nota = $serdoc.'-'.$numdoc;


        $docmod = DB::table('cpe_cabecera')->select('IdCpe_cabecera')->where('IdEmpresa','=',$rucemp)->where('serdoc','=',$serdocmod)->where('numdoc','=',$numdocmod)->first();

        $IdCpe_cabecera=$docmod->IdCpe_cabecera;

        $cabecera = new cpe_nota;
        $cabecera->tdocod = $tdocod;
        $cabecera->ccafem = $fecemi;
        $cabecera->ccaobs = $motivo;
        $cabecera->serdoc = $serdoc;
        $cabecera->numdoc = $numdoc;
        $cabecera->tipcambio = $tipcambio;
        $cabecera->tipnot = $tipnot;
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->ccacar = $request->get('otrosc');
        $cabecera->ccatvg = $request->get('grav');
        $cabecera->ccanom = $clinom;
        $cabecera->ccandi = $clinum;
        $cabecera->ccatvgr = $request->get('grat');
        $cabecera->ccatvi = $request->get('inaf');
        $cabecera->ccatexp= $request->get('exp');
        $cabecera->ccatve = $request->get('exon');
        $cabecera->ccaigv = $request->get('igv');
        $cabecera->ccaisc = $request->get('isc');
        $cabecera->ccaotr = $request->get('otros');
        $cabecera->ccaitv = $request->get('total');
        $cabecera->serdoc= $request->get('serdoc');
        $cabecera->ccatexp= $request->get('exp');
        $cabecera->moncod = $tipmon;
        $cabecera->IdEmpresa = $rucemp;
        $cabecera->IdCpe_cabecera = $IdCpe_cabecera;
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->save();

       
        $cabfactura = cpe_cabecera::findOrFail($IdCpe_cabecera);
        $cabfactura->ccanot = $nota;
        $cabfactura->update();
        

         //Actualizar la serie y/o el nÃºmero del documento a emitir


       /* if ($tdocod =='07') {
            if($tipdoc =='01'){
                $empresa = Empresa::findOrFail($rucemp);
                $empresa->FcseEmpresa = $request->get('serdoc');
                $empresa->FcnuEmpresa = $request->get('numdoc');
                $empresa->update();
            }elseif($tipdoc=='03'){
                $empresa = Empresa::findOrFail($rucemp);
                $empresa->BcseEmpresa = $request->get('serdoc');
                $empresa->BcnuEmpresa = $request->get('numdoc');
                $empresa->update();
            }

        }elseif ($tdocod =='08') {
            if($tipdoc =='01'){
                $empresa = Empresa::findOrFail($rucemp);
                $empresa->FdseEmpresa = $request->get('serdoc');
                $empresa->FdnuEmpresa = $request->get('numdoc');
                $empresa->update();
            }elseif($tipdoc=='03'){
                $empresa = Empresa::findOrFail($rucemp);
                $empresa->BdseEmpresa = $request->get('serdoc');
                $empresa->BdnuEmpresa = $request->get('numdoc');
                $empresa->update();
            }
        } */


        if ($tdocod =='07') {
            if($tipdoc =='01'){
                $empresa = Empresa::findOrFail($rucemp);

                if( $empresa->FcnuEmpresa == $numcomp){
                    $modnumdoc= $numcomp+1;
                }else{
                    $modnumdoc = $numcomp;
                }

                $empresa->FcseEmpresa = $serdoc;
                $empresa->FcnuEmpresa = $modnumdoc;
                $empresa->update();

                $numdoc = str_pad($modnumdoc,8,"0", STR_PAD_LEFT);
                $cabecera->serdoc= $serdoc;
                $cabecera->numdoc = $numdoc;
                $cabecera->save();

            }elseif($tipdoc=='03'){
                $empresa = Empresa::findOrFail($rucemp);
                
                if( $empresa->BcnuEmpresa == $numcomp){
                    $modnumdoc= $numcomp+1;
                }else{
                    $modnumdoc = $numcomp;
                }

                $empresa->BcseEmpresa = $serdoc;
                $empresa->BcnuEmpresa = $modnumdoc;
                $empresa->update();

                $numdoc = str_pad($modnumdoc,8,"0", STR_PAD_LEFT);
                $cabecera->serdoc= $serdoc;
                $cabecera->numdoc = $numdoc;
                $cabecera->save();

            }
        }elseif ($tdocod =='08') {
            if($tipdoc =='01'){
                $empresa = Empresa::findOrFail($rucemp);
                if( $empresa->FdnuEmpresa == $numcomp){
                    $modnumdoc= $numcomp+1;
                }else{
                    $modnumdoc = $numcomp;
                }

                $empresa->FdseEmpresa = $serdoc;
                $empresa->FdnuEmpresa = $modnumdoc;
                $empresa->update();

                $numdoc = str_pad($modnumdoc,8,"0", STR_PAD_LEFT);
                $cabecera->serdoc= $serdoc;
                $cabecera->numdoc = $numdoc;
                $cabecera->save();

            }elseif($tipdoc=='03'){
                $empresa = Empresa::findOrFail($rucemp);
              
                if( $empresa->BdnuEmpresa == $numcomp){
                    $modnumdoc= $numcomp+1;
                }else{
                    $modnumdoc = $numcomp;
                }

                $empresa->BdseEmpresa = $serdoc;
                $empresa->BdnuEmpresa = $modnumdoc;
                $empresa->update();

                $numdoc = str_pad($modnumdoc,8,"0", STR_PAD_LEFT);
                $cabecera->serdoc= $serdoc;
                $cabecera->numdoc = $numdoc;
                $cabecera->save();
            }
        }
      
        
        //Ruta donde se guardarán los archivos cab y det.
        $raiz = '/opt/fs/'.$rucemp.'/sunat_archivos/sfs/DATA/';
        
       //Registrar el detalle de la factura
        $cantidades = $request->get('cant');
        $unidades = $request->get('unid');
        $codpro = $request->get('codpro');
        $detpro = $request->get('detpro');
        $vunit = $request->get('vunit');
        $vigv = $request->get('vigv');
        $tigv = $request->get('tigv');
        $vsub = $request->get('vsub');
        $vtot = $request->get('vtot');

           
        $docrel = $request->get('tdr');
        $docrser = $request->get('tdrser');
        $docrnum = $request->get('tdrnum');
        $docurela[] =array(
                          "tipoDocRelacionado"=>$tipdoc,
                          "numeroDocRelacionado"=> $serdocmod.'-'.$numdocmod,
                          "codigoMotivo"=> $tipnot,
                          "descripcionMotivo"=> $motivo
                        );
        if(!empty($docrel) && !empty($docrser) && !empty($docrnum)){
          foreach( $docrel as $index => $tdr ) 
          {
            if(!empty($docrel[$index]) && !empty($docrser[$index]) && !empty($docrnum[$index])){
               $docrenum = str_pad($docrnum[$index],8,"0", STR_PAD_LEFT);
               $drsernum = "$docrser[$index]-$docrnum[$index]";
               
               $guia = DB::table("documento_relacionado")->where('tdocod','=',$docrel[$index])->where('dorser','=',$docrser[$index])->where('dornum','=',$docrenum)->first();

               if($guia==''){

                    $docurela[] = array( "tipoDocRelacionado"=> $docrel[$index],
                    "numeroDocRelacionado"=> $drsernum
                    );
               } 
            }
          }

        }else{
          $docurela[] = '';
        }
		
		$newdocurela=array_filter($docurela);

          $i=0;
          foreach( $unidades as $index => $ume ) {
            $i=$i+1;
            $imod=str_pad($i,3,"0", STR_PAD_LEFT);
            $detalle = new cpe_nota_detalle;
            $detalle->IdCpe_nota =  $cabecera->IdCpe_nota; 
            $dpro = $detpro[$index];
            $detalle->cdedes = $dpro;
              
              
			   if(($tdocod=='07' && ($tipnot=='04' || $tipnot=='08' || $tipnot=='10'  )) || ($tdocod=='08' && ($tipnot=='01' || $tipnot=='03' ))){
				 $pos = strpos($codpro[$index],'|');
			     $codproducto = substr($codpro[$index], 0, $pos);
			  }else{
				 $codproducto = $codpro[$index];
			  }
			 
              $detalle->umecod = $ume;
              $detalle->cdecan = $cantidades[$index];
              $detalle->procod = $codproducto;
              $detalle->cdepsu = $codproducto;
              $detalle->cdevun = $vunit[$index];
              $detalle->cdeigv = $vigv[$index];
              $detalle->tigcod = $tigv[$index];
              $detalle->cdepve = $vsub[$index];
              $detalle->cdevve = $vtot[$index];
              $detalle->cdepuni = $puni[$index];
            
            if($detalle->tigcod=='10'){
              $tiptri='1000'; //IGV Impuesto Generarl a las ventas
            }elseif($detalle->tigcod=='20'){
               $tiptri='9997'; //exonerado
            }elseif($detalle->tigcod=='30'){
              $tiptri ='9998'; //inafecto
            }elseif($detalle->tigcod=='17'){
              $tiptri ='1016'; //Impuesto a la venta de arroz pilado
            }elseif($detalle->tigcod=='40'){
              $tiptri ='9995'; //exportación
            }else{
              $tiptri='9996'; //Gratutito
            }

            if($grav > 0){
            	$gravadas =array(
                   "codigo"=> "1001",
                   "totalVentas"=> $grav
                );
            }else{
			 $gravadas = "";
			}

            if($inaf > 0){
            	$inafectas = array(
                   "codigo"=> "1002",
                   "totalVentas"=> $inaf
                );
            }else{
			 $inafectas = "";
			}

            if($exon>0){
            	 $exoneradas =  array(
                   "codigo"=> "1003",
                   "totalVentas"=> $exon
                );
            }else{
			 $exoneradas= "";
			}

            if($grat>0){
            	$gratuitas =  array(
                   "codigo"=> "1004",
                   "totalVentas"=> $grat
                );
            }else{
			 $gratuitas = "";
			}

            if($exp>0){
            	$exportadas = array(
                   "codigo"=> "1000",
                   "totalVentas"=> $exp
                  );
            }else{
				$exportadas = "";
			}
              
                
			


            //Guardar en una variable los items en el archivo .det
            $codumecin = unidad_medida::findOrFail($ume);
         
            $detallejson[] = array( "numeroItem"        =>$imod,
                        "codigoProducto"            => $detalle->procod,
                        "descripcionProducto"       => $detalle->cdedes,
                        "cantidadItems"             => $detalle->cdecan,
                        "unidad"                    => $codumecin->umecin,
                        "valorUnitario"             => $detalle->cdevun,
                        "precioVentaUnitario"       => $detalle->cdepuni,
                        "totalImpuestos"            => array(
                          array(
                            "idImpuesto"            => $tiptri,
                            "montoImpuesto"         => $detalle->cdeigv,
                            "tipoAfectacion"        => $detalle->tigcod
                          ),
                        ),
                        "valorVenta"                => $detalle->cdepve);  

            $detalle->tiptri = $tiptri;
            $detalle->save();

          }

          //Monto total en letras
         $totalletras= MontoLetras::convertir(number_format($cabecera->ccaitv,'2','.',','), $monnom,'CENTIMOS');
 
        //Guardar en una variable el nombre del archivo cab
        $cabfile =  $rucemp.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.json'; 

        //consultar datos de la empresa emisora
        $datemp = DB::table("empresa")->where("IdEmpresa","=",$rucemp)->first();
        //Crear el archivo cab e insertar el contenido
        //$archivo = fopen($raiz.$cabfile, "a");
        if($tdocod =='07'){
          $nomcomp = "notaCredito";
        }elseif($tdocod=='08'){
           $nomcomp="notaDebito";
        }
		
		$cabecera_array = array (
                "gravadas" => $gravadas,

                "inafectas" => $inafectas,

                "exoneradas"=>$exoneradas,

                "grautitas"=>$gratuitas,

                "exportadas"=>$exportadas,
                
                "totalImpuestos"=>array(
                  array(
                   "idImpuesto"=>"1000",
                   "montoImpuesto"=> $igv
                   )
                ),    
                "importeTotal"=> $total,
                "leyenda"=>array(
                  array(
                   "codigo"=> "1000",
                   "descripcion"=> $totalletras
                  )
                )
                
         );

		$new_cabecera_array=array_filter($cabecera_array);
		
        $data = array ($nomcomp => array( //inicio json
          "IDE"=> array(
              "numeracion" => $serdoc.'-'.$numdoc, 
              "fechaEmision"=> $fecemi,
              //"horaEmision"=>"",
              "tipoMoneda"=> $mondoc
          ),
          "EMI"=> array(
               "tipoDocId"=> $datemp->TipDoc,
              "numeroDocId"=> $datemp->IdEmpresa,
              //"nombreComercial"=>"",
              "razonSocial"=> $datemp->NomEmpresa,
              //"ubigeo"=> "",
              "direccion"=> $datemp->DirEmpresa,
              //"urbanizacion"=> "",
              //"provincia"=> "",
              //"departamento"=> "",
              //"distrito"=> "",
              //"codigoPais"=> "",
              "telefono"=> $datemp->TelEmpresa,
              "correoElectronico"=> $datemp->CorEmpresa
          ),


          "REC" => array( 
            "tipoDocId"=> $tdicod,
            "numeroDocId"=> $clinum,
            "razonSocial"=> $clinom,
            "direccion"=> $clidir,
            //"departamento"=> "",
            //"provincia"=> "",
            //"distrito"=> "",
            //"codigoPais"=> "",
            //"telefono"=> "",        
            "correoElectronico"=> $clicor
          ),

          "DRF"=>$newdocurela,
      
         //inicio cab
           "CAB" => $new_cabecera_array,

            "DET" => $detallejson,
            /*"ADI" => array(
                array(
                   "tituloAdicional"=> "", 
                   "valorAdicional"=> ""
                ),
                
                array(
                   "tituloAdicional"=> "", 
                   "valorAdicional"=> ""
                )
             )*/
      )

      ); //fin json
    
        $data_json = json_encode($data,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $archivo = fopen($cabfile, "a");
        fputs($archivo,$data_json);
        fclose($archivo);

        $leer_respuesta = self::webserviceonline($data_json,$cabfile);
		  
		    $codfact = $cabecera->IdCpe_nota;
		    $hash= DB::table('cpe_nota')->select('codhash')->where('IdCpe_nota','=',$codfact)->first();

      
        $detpdf= DB::table('cpe_nota_detalle as d')->join('cpe_nota as n','n.IdCpe_nota','=','d.IdCpe_nota')->where('n.IdCpe_nota','=',$codfact)->get();

        $nompdffile =  $rucemp.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.pdf'; 
        $rutpdfile = public_path().'/'.$rucemp.'/';
	if (file_exists($rutpdfile))
		{
        if($tdocod=='07'){
          $view = \View::make('empresas.comprobantes.'.$rucemp.'.notacreditopdf', compact('serdoc','numdoc','clinom','clinum','fecemi','clidir','monnom','hash','motivo','detpdf','empresa','cabecera','desnota','serdocmod','numdocmod','tdomod'));
        }elseif($tdocod=='08'){
          $view = \View::make('empresas.comprobantes.'.$rucemp.'.notadebitopdf', compact('serdoc','numdoc','clinom','clinum','fecemi','clidir','monnom','hash','motivo','detpdf','empresa','cabecera','desnota','serdocmod','numdocmod','tdomod'));
        }

        $pdf = \App::make('dompdf.wrapper');
        $contenido = $view->render();
        $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutpdfile.$nompdffile);
        //$pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($nompdffile);

		}
    
        return Redirect::to('/SisFact')->with('success',$leer_respuesta['responseContent'].'-'.$leer_respuesta['responseCode']);


    }



    //NOTA DE CREDITO Y DÉBITO

    public function tiponotacd($tdocod,$idcabecera,$ncdcod){
      $rucemp = Auth::user()->IdEmpresa;
    //  $cabecera=DB::table('cpe_cabecera')->where('IdCpe_cabecera','=',$idcabecera)->where('IdEmpresa','=',$rucemp)->first();

       $cabecera = DB::table('cpe_cabecera as cab')
      ->join('cliente as cli','cab.ccandi','=','cli.clinum')
      ->join('tipo_documento as tdo','cab.tdocod','=','tdo.tdocod')
      ->join('tipo_documento_identidad as tdi','cab.tdicod','=','tdi.tdicod')
      ->join('moneda as mon','cab.moncod','=','mon.moncod')
      ->join('tipo_operacion as top','cab.topcod','=','top.topcod')
      ->where('IdCpe_cabecera','=',$idcabecera)->where('IdEmpresa','=',$rucemp)
      ->first();

     // $detalle=DB::table('cpe_detalle as det')->join('unidad_medida as umed','det.umecod','=','umed.umecod')->where('IdCpe_cabecera','=',$idcabecera)->get();

      $igv = DB::table('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();

      

      // consultar unidades de medida
      $unidades = DB::table('unidad_medida')->where('umeest','=','Activo')
      ->orderBy('umecod','asc')->get();

      // consultar tipo de operaciones
      $operaciones = DB::table('tipo_operacion')->where('topest','=','Activo')
      ->orderBy('topcod','asc')->get();

      // consultar tipos de documentos de identidad
      $docidentidad = DB::table('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

      //consultar tipo de documento 
      $doccomprobante = DB::table('tipo_documento')->where('tdoest','=','Activo')->orderBy('tdocod','asc')->get();

      // obtener el ruc de la empresa en la cual se logueo
      $rucemp = trim(Auth::user()->IdEmpresa);

      // consultar los clientes que le pertenece a la empresa
      $clientes= DB::table('cliente')->where('rucemp','=',$rucemp)->where('cliest','=','Activo')
      ->orderby('clinom','asc')->get();

      //consultar productos que le pertenece a la empresa
      $productos= DB::table('productos')->where('IdEmpresa','=',$rucemp)->where('proest','=','Activo')
      ->orderby('pronom','asc')->get();

      // consultar tipos de monedas
      $monedas = DB::table('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();


  /*    if ($ncdcod =='07') {
        $senuncd = DB::table('empresa')->select('CseEmpresa','CnuEmpresa')->where('IdEmpresa','=',$rucemp)->first(); 
        $nota = DB::table('tipo_nota_credito')->where('ncest','=','Activo')
        ->orderBy('nccod','asc')->get();


      }elseif ($ncdcod =='08') {
        $senuncd = DB::table('empresa')->select('DseEmpresa','DnuEmpresa')->where('IdEmpresa','=',$rucemp)->first(); 
         $nota = DB::table('tipo_nota_debito')->where('ndest','=','Activo')
        ->orderBy('ndcod','asc')->get();
      }*/

      if ($ncdcod =='07') {
        if($tdocod =='01'){
          $senuncd = DB::table('empresa')->select('FcseEmpresa','FcnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $nota = DB::table('tipo_nota_credito')->where('ncest','=','Activo')->get();
        }elseif($tdocod=='03'){
          $senuncd = DB::table('empresa')->select('BcseEmpresa','BcnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $nota = DB::table('tipo_nota_credito')->where('ncest','=','Activo')->get();
        }
      }elseif ($ncdcod =='08') {
          if($tdocod =='01'){
          $senuncd = DB::table('empresa')->select('FdseEmpresa','FdnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $nota = DB::table('tipo_nota_debito')->where('ndest','=','Activo')->get();
        }elseif($tdocod=='03'){
          $senuncd = DB::table('empresa')->select('BdseEmpresa','BdnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $nota = DB::table('tipo_nota_debito')->where('ndest','=','Activo')->get();
        }
      }
        

      return view('empresas.comprobantes.tiponota',['cabecera'=>$cabecera,'senuncd'=>$senuncd,'igv'=>$igv,'monedas'=>$monedas,'unidades'=>$unidades,'operaciones'=>$operaciones,'docidentidad'=>$docidentidad,'clientes'=>$clientes,'tdocod'=>$tdocod,'ncdcod'=>$ncdcod,'productos'=>$productos,'doccomprobante'=>$doccomprobante,'nota'=>$nota]);
         

    }

    public function emitirnota(Request $request){

     $idcabecera = $request->get('idcabecera');
     $tdocod = $request->get('tdo_cod');
     $ncdcod = $request->get('txt_tdocod');
     $tipnot = $request->get('tipnot');
     // datos $tdocod,$idcabecera,$ncdcod,$tipncd

      $rucemp = Auth::user()->IdEmpresa;
    //  $cabecera=DB::table('cpe_cabecera')->where('IdCpe_cabecera','=',$idcabecera)->where('IdEmpresa','=',$rucemp)->first();

       $cabecera = DB::table('cpe_cabecera as cab')
      ->join('cliente as cli','cab.ccandi','=','cli.clinum')
      ->join('tipo_documento as tdo','cab.tdocod','=','tdo.tdocod')
      ->join('tipo_documento_identidad as tdi','cab.tdicod','=','tdi.tdicod')
      ->join('moneda as mon','cab.moncod','=','mon.moncod')
      ->join('tipo_operacion as top','cab.topcod','=','top.topcod')
      ->where('IdCpe_cabecera','=',$idcabecera)->where('IdEmpresa','=',$rucemp)
      ->first();

      $detalle=DB::table('cpe_detalle as det')->join('unidad_medida as umed','det.umecod','=','umed.umecod')->join('tipo_igv as ti','det.tigcod','=','ti.tigcod')->where('IdCpe_cabecera','=',$idcabecera)->get();

      $igv = DB::table('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();


      // consultar unidades de medida
      $unidades = DB::table('unidad_medida')->where('umeest','=','Activo')
      ->orderBy('umecod','asc')->get();

      // consultar tipo de operaciones
      $operaciones = DB::table('tipo_operacion')->where('topest','=','Activo')
      ->orderBy('topcod','asc')->get();

      // consultar tipos de documentos de identidad
      $docidentidad = DB::table('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

      //consultar tipo de documento 
      $doccomprobante = DB::table('tipo_documento')->where('tdoest','=','Activo')->orderBy('tdocod','asc')->get();

      // obtener el ruc de la empresa en la cual se logueo
      $rucemp = trim(Auth::user()->IdEmpresa);

      // consultar los clientes que le pertenece a la empresa
      $clientes= DB::table('cliente')->where('rucemp','=',$rucemp)->where('cliest','=','Activo')
      ->orderby('clinom','asc')->get();

      //consultar productos que le pertenece a la empresa
      $productos= DB::table('productos')->where('IdEmpresa','=',$rucemp)->where('proest','=','Activo')
      ->orderby('pronom','asc')->get();

      // consultar tipos de monedas
      $monedas = DB::table('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();


      if ($ncdcod =='07') {
        if($tdocod =='01'){
          $senuncd = DB::table('empresa')->select('FcseEmpresa','FcnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $nota = DB::table('tipo_nota_credito')->where('nccod','=',$tipnot)->first();
        }elseif($tdocod=='03'){
          $senuncd = DB::table('empresa')->select('BcseEmpresa','BcnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $nota = DB::table('tipo_nota_credito')->where('nccod','=',$tipnot)->first();
        }
      }elseif ($ncdcod =='08') {
          if($tdocod =='01'){
          $senuncd = DB::table('empresa')->select('FdseEmpresa','FdnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $nota = DB::table('tipo_nota_debito')->where('ndcod','=',$tipnot)->first();
        }elseif($tdocod=='03'){
          $senuncd = DB::table('empresa')->select('BdseEmpresa','BdnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $nota = DB::table('tipo_nota_debito')->where('ndcod','=',$tipnot)->first();
        }
      }
	if($ncdcod=='07'){
		if($tipnot=='04' || $tipnot=='08' || $tipnot=='10'){
			
		  return view('empresas.comprobantes.emitirnota2',['cabecera'=>$cabecera,'detalle'=>$detalle,'senuncd'=>$senuncd,'igv'=>$igv,'monedas'=>$monedas,'unidades'=>$unidades,'operaciones'=>$operaciones,'docidentidad'=>$docidentidad,'clientes'=>$clientes,'tdocod'=>$tdocod,'ncdcod'=>$ncdcod,'productos'=>$productos,'doccomprobante'=>$doccomprobante,'nota'=>$nota]);
		}else{
			return view('empresas.comprobantes.emitirnota',['cabecera'=>$cabecera,'detalle'=>$detalle,'senuncd'=>$senuncd,'igv'=>$igv,'monedas'=>$monedas,'unidades'=>$unidades,'operaciones'=>$operaciones,'docidentidad'=>$docidentidad,'clientes'=>$clientes,'tdocod'=>$tdocod,'ncdcod'=>$ncdcod,'productos'=>$productos,'doccomprobante'=>$doccomprobante,'nota'=>$nota]);
		}
	}elseif($ncdcod=='08'){
		if($tipnot=='01' || $tipnot=='03'){
			
		  return view('empresas.comprobantes.emitirnota2',['cabecera'=>$cabecera,'detalle'=>$detalle,'senuncd'=>$senuncd,'igv'=>$igv,'monedas'=>$monedas,'unidades'=>$unidades,'operaciones'=>$operaciones,'docidentidad'=>$docidentidad,'clientes'=>$clientes,'tdocod'=>$tdocod,'ncdcod'=>$ncdcod,'productos'=>$productos,'doccomprobante'=>$doccomprobante,'nota'=>$nota]);
		}else{
			return view('empresas.comprobantes.emitirnota',['cabecera'=>$cabecera,'detalle'=>$detalle,'senuncd'=>$senuncd,'igv'=>$igv,'monedas'=>$monedas,'unidades'=>$unidades,'operaciones'=>$operaciones,'docidentidad'=>$docidentidad,'clientes'=>$clientes,'tdocod'=>$tdocod,'ncdcod'=>$ncdcod,'productos'=>$productos,'doccomprobante'=>$doccomprobante,'nota'=>$nota]);
		}
	}
    }

    //Comunicación de baja desde el menú Comprobantes
    public function bajacomprobante(){
      $rucemp = Auth::user()->IdEmpresa;
      $fecact = date('Y-m-d');
      $numbaj = DB::table('empresa')->select('IdEmpresa','BanuEmpresa','fecbaja')->where('IdEmpresa','=',$rucemp)->first(); 
      if($numbaj->fecbaja==$fecact){
        $cor=$numbaj->BanuEmpresa+1;
      }else{
        $cabcomp = empresa::findOrFail($numbaj->IdEmpresa);
        $cabcomp->fecbaja = $fecact;
        $cabcomp->BanuEmpresa = 0;
        $cabcomp->update();
        $cor = $cabcomp->BanuEmpresa+1;
      }

       return view('empresas.comprobantes.comunicacionbaja',['cor'=>$cor]);
     
    }

    //Comunicación de baja desde el listado de comprobantes
    public function formbajacomprobante($serdoc,$numdoc,$tdocod,$fecemi,$tdodes){
      $rucemp = Auth::user()->IdEmpresa;
      $fecact = date('Y-m-d');
      $numbaj = DB::table('empresa')->select('IdEmpresa','BanuEmpresa','fecbaja')->where('IdEmpresa','=',$rucemp)->first(); 
      if($numbaj->fecbaja==$fecact){
        $cor=$numbaj->BanuEmpresa+1;
      }else{
        $cabcomp = empresa::findOrFail($numbaj->IdEmpresa);
        $cabcomp->fecbaja = $fecact;
        $cabcomp->BanuEmpresa = 0;
        $cabcomp->update();
        $cor = $cabcomp->BanuEmpresa+1;
      }

      if($tdocod=="01" || $tdocod=="03"){
        $comp = DB::table('cpe_cabecera as cab')
        ->join('moneda as mon','cab.moncod','=','mon.moncod')
        ->where('serdoc','=',$serdoc)
        ->where('numdoc','=',$numdoc)
        ->where('IdEmpresa','=',$rucemp)->first();
      }elseif($tdocod=="07" || $tdocod=="08"){
        $comp = DB::table('cpe_nota as nota')
        ->join('moneda as mon','nota.moncod','=','mon.moncod')
        ->where('serdoc','=',$serdoc)
        ->where('numdoc','=',$numdoc)
        ->where('IdEmpresa','=',$rucemp)->first();
      }

      $sernumdoc = $serdoc.'-'.$numdoc;
      return view('empresas.comprobantes.emitirbaja',['cor'=>$cor,'sernumdoc'=>$sernumdoc,'tdodes'=>$tdodes,'tdocod'=>$tdocod,'fecemi'=>$fecemi,'monnom'=>$comp->monnom,'ccaitv'=>$comp->ccaitv]);
    }


    public function registrarbajacomprobante(Request $request){
      $rucemp = Auth::user()->IdEmpresa;
      $serdocbaja = $request->get('serdocbaja');
      $fecbaj = $request->get('fecbaj');
      $numbaj = $request->get('numbaj');
      $numbajmod = str_pad($numbaj,3,"0", STR_PAD_LEFT);

      $obser = $request->get('obser');
      $fecemi = $request->get('fecemi');
      $tdomod = $request->get('tdomod');
      $tdocod = $request->get('tdo_cod');

      $cabfile =  $rucemp.'-RA'.'-'.str_replace("-", "", $fecbaj).'-'.$numbaj.'.json';
      $numeracion = 'RA'.'-'.str_replace("-", "", $fecbaj).'-'.$numbaj;
      $nompdffile =  $rucemp.'-'.str_replace("-", "", $fecbaj).'-'.$numbajmod.'.pdf'; 

     
    /*  $i=0;
      foreach( $serdocbaja as $index => $ser ) {
        $i=$i+1;
        $docbaja = new cpe_baja;
        $sernumbaja =$serdocbaja[$index]; 
        $ser = substr($sernumbaja,strpos($sernumbaja,'-')-4,4);
        $num = substr($sernumbaja,strpos($sernumbaja,'-')+1,8);
        $numdoc = str_pad($num,8,"0", STR_PAD_LEFT);

        $compbaj[] = array("numeroItem"=>$i,
                            "tipoComprobanteItem"=>$tdocod[$index],
                            "serieItem"=>$ser,
                            "correlativoItem"=>$numdoc,
                            "motivoBajaItem"=>$obser[$index]);
          $docbaja = new cpe_baja;
          $ser = substr($serdocbaja,strpos($serdocbaja,'-')-4,4);
          $num = substr($serdocbaja,strpos($serdocbaja,'-')+1,8);
          $numdoc = str_pad($num,8,"0", STR_PAD_LEFT);

          $docbaja->cbanum =  $ser.'-'.$numdoc;
          $docbaja->cbacor =  $numbaj; 
          $docbaja->cbamot =  $obser; 
          $docbaja->cbdfco =  $fecbaj; 
          $docbaja->cbafec =  $fecemi; 
          $docbaja->tdocod =  $tdocod; 
          $docbaja->IdEmpresa =  $rucemp; 

          if($tdocod=='01' || $tdocod=='03'){
            $cabecera= DB::table('cpe_cabecera')->select('IdCpe_cabecera','serdoc','numdoc')->where('serdoc','=',$ser)->where('numdoc','=',$numdoc)->first();
          $docbaja->IdCpe_cabecera = $cabecera->IdCpe_cabecera; 
           $cabcomp = cpe_cabecera::findOrFail($cabecera->IdCpe_cabecera);
          }elseif($tdocod=='07' || $tdocod=='08'){
            $cabecera= DB::table('cpe_nota')->select('IdCpe_nota','serdoc','numdoc')->where('serdoc','=',$ser)->where('numdoc','=',$numdoc)->first();
            $docbaja->IdCpe_cabecera = $cabecera->IdCpe_nota; 
            $cabcomp = cpe_nota::findOrFail($cabecera->IdCpe_nota);
          }


        }*/

        $docbaja = new cpe_baja;
        $sernumbaja =$serdocbaja; 
        $ser = substr($sernumbaja,strpos($sernumbaja,'-')-4,4);
        $num = substr($sernumbaja,strpos($sernumbaja,'-')+1,8);
        $numdoc = str_pad($num,8,"0", STR_PAD_LEFT);

        $docbaja->cbanum =  $ser.'-'.$numdoc;
        $docbaja->cbacor =  $numbaj; 
        $docbaja->cbamot =  $obser; 
        $docbaja->cbdfco =  $fecbaj; 
        $docbaja->cbafec =  $fecemi; 
        $docbaja->tdocod =  $tdocod; 
        $docbaja->IdEmpresa =  $rucemp; 


        if($tdocod=='01' || $tdocod=='03'){
            $cabecera= DB::table('cpe_cabecera')->select('IdCpe_cabecera','serdoc','numdoc')->where('serdoc','=',$ser)->where('numdoc','=',$numdoc)->first();
			$docbaja->IdCpe_cabecera = $cabecera->IdCpe_cabecera; 
           $cabcomp = cpe_cabecera::findOrFail($cabecera->IdCpe_cabecera);
		    $cabcomp->ccabaj = str_replace("-", "", $fecbaj).'-'.$numbajmod;
			$cabcomp->update();
          }elseif($tdocod=='07' || $tdocod=='08'){
            $cabecera= DB::table('cpe_nota')->select('IdCpe_nota','serdoc','numdoc')->where('serdoc','=',$ser)->where('numdoc','=',$numdoc)->first();
            $docbaja->IdCpe_cabecera = $cabecera->IdCpe_nota; 
            $cabcomp = cpe_nota::findOrFail($cabecera->IdCpe_nota);
		    $cabcomp->ccabaj = str_replace("-", "", $fecbaj).'-'.$numbajmod;
			$cabcomp->update();
          }



        $compbaj[] = array("numeroItem"=>"1",
                            "tipoComprobanteItem"=>$tdocod,
                            "serieItem"=>$ser,
                            "correlativoItem"=>$numdoc,
                            "motivoBajaItem"=>$obser);

        $datemp = DB::table("empresa")->where("IdEmpresa","=",$rucemp)->first();
   
        $data = array ("comunicacionBaja"=> array(
          "IDE"=> array (
              "numeracion" =>$numeracion,
              "fechaEmision"=> $fecbaj
          ),

		
          "EMI"=>array(
            "tipoDocId"=> $datemp->TipDoc,
            "numeroDocId"=> $datemp->IdEmpresa,
            //"nombreComercial"=> "",
            "razonSocial"=>$datemp->NomEmpresa,
            //"ubigeo"=> "",
            "direccion"=> $datemp->DirEmpresa,
            //"urbanizacion"=> "",
            //"provincia"=> "",
            //"departamento"=> "",
            //"distrito"=> "",
            //"codigoPais"=> "",
            "telefono"=> $datemp->TelEmpresa,
            "correoElectronico"=>$datemp->CorEmpresa,
          ),

          "CBR"=>array(
            "fechaReferencia"=> $fecemi
          ),

          "DBR"=>$compbaj,
        
          )
        );

        $data_json = json_encode($data,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $archivo = fopen($cabfile, "a");
        fputs($archivo,$data_json);
        fclose($archivo);

        
        $leer_respuesta = self::webserviceonline($data_json,$cabfile);
        
         
        $docbaja->save();
        $cabcomp->update();
        $empresa = Empresa::findOrFail($rucemp);
        $empresa->BanuEmpresa = $numbaj;
        $empresa->update();

       
          
        /*  $idbaja = DB::table('cpe_baja')->select('IdCpe_baja')->where('IdCpe_cabecera','=',$docbaja->IdCpe_cabecera)->first();
          $comp = cpe_baja::findOrFail($idbaja->IdCpe_baja);
          $comp->ccasuntick = $leer_respuesta_cons['sunat_ticket_numero'];
          $comp->ccacodsun = $leer_respuesta_cons['aceptada_por_sunat'];
          $comp->ccadessun = $leer_respuesta_cons['sunat_description'];
          $comp->ccasunrescod = $leer_respuesta_cons['sunat_responsecode'];
          $comp->ccasunsoaperr = $leer_respuesta_cons['sunat_soap_error'];
          $comp->ccaenlace = $leer_respuesta_cons['enlace'];
          $comp->ccasunnot = $leer_respuesta_cons['sunat_note'];
          $comp->ccapdfzip = $leer_respuesta_cons['pdf_zip_base64'];
          $comp->ccaxmlzip = $leer_respuesta_cons['xml_zip_base64'];
          $comp->ccacdrzip = $leer_respuesta_cons['cdr_zip_base64'];
          $comp->update();
*/
          $corbaja = $docbaja->cbacor;
        
          $dobaja= DB::table('cpe_baja as b')
            ->join('cpe_cabecera as c','b.IdCpe_cabecera','=','c.IdCpe_cabecera')
            ->join('moneda as m','c.moncod','=','m.moncod')
            ->join('tipo_documento as tp','b.tdocod','=','tp.tdocod')
            ->where('cbdfco','=',$fecbaj)
            ->where('cbacor','=',$corbaja)->where('b.IdEmpresa','=',$rucemp)->get();
   
		
            $rutpdfile = public_path().'/'.$rucemp.'/';
		if (file_exists($rutpdfile))
		{
            $view = \View::make('empresas.comprobantes.'.$rucemp.'.comunicacionbajapdf', compact('dobaja','fecbaj','numbaj','empresa'));

            $pdf = \App::make('dompdf.wrapper');
            $contenido = $view->render();
            $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutpdfile.$nompdffile);
            //$pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($nompdffile);
		}
            return Redirect::to('/SisFact')->with('success',$leer_respuesta['responseContent'].'-'.$leer_respuesta['responseCode']);
    }

    public function listarnotas($idcabecera){

      $rucemp =Auth::user()->IdEmpresa;
      $notas = DB::table('cpe_nota as n')->select('n.ccafem','n.serdoc','n.numdoc','tdodes','c.ccandi','c.ccanom','mn.monnom','n.ccaitv','n.tdocod','c.IdEmpresa','n.IdCpe_nota','n.tdocod','n.codhash','n.ccasunrescod')
      ->join('tipo_documento as td','n.tdocod','=','td.tdocod')
      ->join('cpe_cabecera as c','n.IdCpe_cabecera','=','c.IdCpe_cabecera')
      ->join('moneda as mn','c.moncod','=','mn.moncod')
      ->where('n.IdCpe_cabecera','=',$idcabecera)
      ->where('c.IdEmpresa','=',$rucemp)
      ->orderby('n.IdCpe_nota','desc')
      ->paginate(10);
       $empresa = Empresa::findOrFail($rucemp);

       $sndocmod = DB::table('cpe_cabecera')->select('serdoc','numdoc')->where('IdCpe_cabecera','=',$idcabecera)->first();

        return view('empresas.comprobantes.listarnotas',['notas'=>$notas,'empresa'=>$empresa,'sndocmod'=>$sndocmod]);

    }

    public function ingresarpanel($idempresa){

      $user =Auth::user()->IdUsuario;
      $regemp = User::findOrFail($user);
      $regemp->IdEmpresa = $idempresa;
      $regemp->update();

      return Redirect::to('/SisFact');

    }

    public function webserviceonline($data_json,$cabfile){

      $dataent = array (

          "customer"=>array(
            "username"=> "20557103920ad_escon",
            "password"=> "Escon2018*"
          ),

          "fileName"=>$cabfile,
          "fileContent"=>base64_encode($data_json)
      );

      $ent = json_encode($dataent,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

      //return $ent;

      $ruta = "http://calidad.escondatagate.net/wsParser/rest/parserWS";
  
      $ch = curl_init();

          // Establecer URL y otras opciones apropiadas
          curl_setopt($ch, CURLOPT_URL, $ruta);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array(
              'Content-Type: application/json',
            )
          );
         curl_setopt($ch, CURLOPT_POST, 1);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
         curl_setopt($ch, CURLOPT_POSTFIELDS,$ent);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          // Capturar la URL y pasarla al navegador
          $respuesta = curl_exec($ch);
          $leer_respuesta = json_decode($respuesta, true);
         
          // Cerrar el recurso cURL y liberar recursos del sistema
          curl_close($ch);
           return $leer_respuesta;
        }
}
