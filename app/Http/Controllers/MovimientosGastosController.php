<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\TipoIGV;
use MasterSoft\Empresa;
use MasterSoft\Cliente;
use MasterSoft\presentaciones;
use MasterSoft\cpe_cabecera_gasto;
use MasterSoft\documento_relacionado;
use MasterSoft\unidad_medida;
use MasterSoft\movimientos;
use MasterSoft\cpe_detalle_gasto;
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

class MovimientosGastosController extends Controller
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
        $razsoc = $request->get('searchText');
      //  $respse = $request->get('tiper');
      //  $tipdoc = $request->get('docomp');
        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');
      //  $serdoc=$request->get('serdoc');
      //  $comp=$request->get('comp');
      //  $numdoc = $request->get('numdoc');
        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
      //  $doccomprobante = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->orderBy('tdocod','asc')->pluck('tdodes', 'tdocod');

          
            
            $IdEmpresa = Auth::user()->IdEmpresa;
           // $ser = substr($comp,strpos($comp,'-')-4,4);
           // $num = substr($comp,strpos($comp,'-')+1,8);

           if(empty($razsoc) && empty($fecin) && empty($fecfin)){

            $comprobantes = DB::tABLE('cpe_cabecera_gasto as cpe_c')->select('ccaobs','estado','cpe_c.ccaenlace','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera_gasto','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.ccasunrescod')
               ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
               ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
               ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
               ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
               ->where('cpe_c.ccafem','>=',$fechaini)
               ->where('cpe_c.ccafem','<=',$fechafin)
               ->where('cpe_c.tipo','Gasto')
               ->orderby('IdCpe_cabecera_gasto','desc')
               ->paginate(100);
              

           }else{
                $comprobantes = DB::tABLE('cpe_cabecera_gasto as cpe_c')->select('ccaobs','estado','cpe_c.ccaenlace','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera_gasto','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.ccasunrescod')
                ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
                ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
                ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
                ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
                //->where('cpe_c.tdocod','=',$tipdoc)
                //->where('cpe_c.ccasunrescod','=',$respse)
                ->where('cpe_c.ccafem','>=',$fecin)
                ->where('cpe_c.ccafem','<=',$fecfin)
                ->where(function ($query) use ($razsoc) {
                $query->where('cpe_c.ccanom','like','%'.$razsoc.'%')
                      ->orWhere('cpe_c.ccandi','=',$razsoc);
                })
                ->where('cpe_c.tipo','Gasto')
				->orderby('IdCpe_cabecera_gasto','desc')
                ->paginate(100);

                
               
              }
           
        
        
              
          
            return view('empresas.gastosmovimientos.index',['comprobantes'=>$comprobantes,'empresa'=>$empresa]);

        
         
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

     
        $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();

        // consultar unidades de medida
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        // consultar tipo de operaciones
        $operaciones = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->where('formulario','3')
        ->orderBy('tdocod','asc')->get();

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
        $productos= DB::tABLE('productos')->where('IdEmpresa','=',$rucemp)->where('proest','=','Activo')
        ->orderby('pronom','asc')->get();

        // consultar tipos de monedas
        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

        $presentaciones = DB::tABLE('presentaciones')->orderby('Presentacion','asc')->where('IdEmpresa','=',$rucemp)->get();
        // consultar la serie y numero de factura

   
       return view('empresas.gastosmovimientos.nuevafactura',compact('presentaciones','igv','monedas','unidades','operaciones','docidentidad','clientes','fecha','senudoc','tdocod','productos','doccomprobante','cpe'));
     
    }

  public function crearboleta($tdocod,$cpe=0)
    {

        // consultar tipos de  IGV
        $ncdcod= $tdocod;
        $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();

        // consultar unidades de medida
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        // consultar tipo de operaciones
        $operaciones = DB::tABLE('tipo_operacion')->where('topest','=','Activo')
        ->orderBy('topcod','asc')->get();

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
        $productos= DB::tABLE('productos')->where('IdEmpresa','=',$rucemp)->where('proest','=','Activo')
        ->orderby('pronom','asc')->get();

        // consultar tipos de monedas
        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

        // consultar la serie y numero de factura

         $senudoc = DB::tABLE('empresa')->select('ccaobs','estado','BseEmpresa','BnuEmpresa')->where('IdEmpresa','=',$rucemp)->first(); 
         $presentaciones = DB::tABLE('presentaciones')->where('IdEmpresa','=',$rucemp)->orderby('Presentacion','asc')->get();

        $fecha = now()->format('m/d/Y');
        //return $senudoc;
             return view('empresas.comprobantes.boletadetalle',compact('presentaciones','igv','monedas','unidades','operaciones','docidentidad','clientes','fecha','senudoc','tdocod','productos','doccomprobante','cpe'));
        
       
    }
  
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

       $tdocod = $request->get('codigodoc');


      //Empresa que emite el comprobante
      $rucemp = trim(Auth::user()->IdEmpresa);

      //Datos del cliente
        $tdicod = $request->get('tdicod');
        $cliruc = $request->get('clinum');
        $clinom = $request->get('clinom');
        $clidir = $request->get('clidir');
        $clicor = $request->get('clicor');

      //Cabecera del comprobante
        $numcomp= $request->get('numdoc');
        $sercomp= $request->get('serdoc');
       
        $topcod = "2";
        $fecemi = $request->get('fecEmi');
        $fecven = $request->get('fecVen');
        $mondoc = $request->get('mondoc');
        $descglb = $request->get('totdesc');
        $exon = $request->get('exon');
        $inaf = $request->get('inaf');
        $grav = $request->get('grav');
        $igv = $request->get('igv');
        //$isc = $request->get('isc');
        $grat = $request->get('grat');
        $otrosc = $request->get('otrosc');
        $otros = $request->get('otros');
        $total = $request->get('total');
      
        $descitem = $request->get('descitem');
		
	
        $obser = $request->get('obser');
        $tipcambio = $request->get('camdoc');
        $detraccion = $request->get('detraccion');
        $tipago = $request->get('cmbMP');
        $efectivo = $request->get('dinero');
        $vuelto = $request->get('vuelto');
      //Detalle del comprobante
        $cantidades = $request->get('cant');
        $presentaciones = $request->get('presentacion');
        $unidades = $request->get('unid');
        $codpro = $request->get('codpro');
        $detpro = $request->get('detpro');
        $vunit = $request->get('vunit');
        $vigv = $request->get('vigv');
        $tigv = $request->get('tigv');
        $vsub = $request->get('vsub');
        $vtot = $request->get('vtot');
        //$exp=$request->get('exp');
        $puni = $request->get('preuni');


       

        //Número de comprobante rellenado de ceros a la izquierda
        $numdoc = str_pad($request->get('numdoc'),8,"0", STR_PAD_LEFT);
        
        $moneda = DB::tABLE('moneda')->where('moncod','=',$mondoc)->first();


      //Registrar el tipo de cambio enviado a través del formulario si no existe.
        if($mondoc !='1'){
          $camdoc = $request->get('camdoc');
          $tipcam = tipocambio::FirstOrCreate(['FecTipCambio'=>$fecemi],['CamCompra'=>$camdoc]);
        }else{
          $camdoc=0;
        }
        
      //Registrar el cliente enviado a través del formulario si no existe
        $cliente = Cliente::FirstOrCreate(['clinum'=>$cliruc,'rucemp'=>$rucemp],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$clicor,'rucemp'=>$rucemp,'tdicod'=>$tdicod]);
 
        
      //Crear un objeto de la cabecera del comprobante para registrar los datos de la cabecera
        $cabecera = new cpe_cabecera_gasto;
        $cabecera->tdocod = $tdocod;
        $cabecera->topcod = $topcod;
        $cabecera->ccafem = $request->get('fecEmi');
        $cabecera->ccafve = $request->get('fecVen');
        $cabecera->ccaobs = $request->get('obser');
        //$cabecera->ccacde = $request->get();
        $cabecera->tdicod = $request->get('tdicod');
        $cabecera->ccandi = $request->get('clinum');
        $cabecera->ccanom = $request->get('clinom');
        $cabecera->moncod = $request->get('mondoc');
        $cabecera->tipo_pago = $tipago;
        $cabecera->efectivo = $efectivo;
        $cabecera->vuelto = $vuelto;
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
        $cabecera->detraccion = $detraccion;
        $cabecera->clicod = $cliente->clicod;
        $cabecera->tipo = 'Gasto';
        $cabecera->numdoc = $numdoc;
        $cabecera->codunique = Auth::user()->IdEmpresa.''.$tdocod.''.$request->get('serdoc').''.$request->get('numdoc');
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->IdEmpresa = Auth::user()->IdEmpresa;
        //$cabecera->save();

    

        $empresa = Empresa::findOrFail($rucemp);
		$formato = $empresa->formato;
       
  

        //Generar el detalle del comprobante
        foreach( $unidades as $index => $ume ) {
            $detalle = new cpe_detalle_gasto;
            $detalle->IdCpe_cabecera_gasto =  $cabecera->IdCpe_cabecera_gasto; 
            $detalle->umecod = $ume;
            $detalle->cdecan = $cantidades[$index];
            $pos = strpos($codpro[$index],'|');
            $codproducto = $codpro[$index];
            $detalle->procod = $codproducto;
            $detalle->cdepsu = $codproducto;
            $detalle->cdedes = substr($detpro[$index],0,strpos($detpro[$index],'*'));
            $detalle->descuento = $descitem[$index];
            $detalle->cdevun = $vunit[$index];
            $detalle->cdepuni = $puni[$index];
            $detalle->cdeigv = $vigv[$index];
            $detalle->tigcod = $tigv[$index];
            $detalle->cdepve = $vsub[$index];
            $detalle->cdevve = $vtot[$index];
    
            //Guardar en una variable los items en el archivo .det
            $codumecin = unidad_medida::findOrFail($ume);
         
            
        
        }
        
         
   

        // Monto en letras
        $totalletras= MontoLetras::convertir(number_format($cabecera->ccaitv,'2','.',''),$moneda->monnom,'Centimos');

        //$cabfile es el nombre con el cual se guarda el archivo que contiene los datos del comproabnte
        $cabfile =  $cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.json'; 

        //Consultar los datos de la empresa emisora
        $datemp = DB::table("empresa")->where("IdEmpresa","=",$rucemp)->first();

	
 
      
          $empresa->update();
          $cabecera->save();
          $codfact = $cabecera->IdCpe_cabecera_gasto; 
           foreach( $unidades as $index => $ume ) {

            $detalle = new cpe_detalle_gasto;
            $detalle->IdCpe_cabecera_gasto =  $cabecera->IdCpe_cabecera_gasto; 
            $detalle->umecod = $ume;
            $detalle->cdecan = $cantidades[$index];
            $pos = strpos($codpro[$index],'|');
            $codproducto = $codpro[$index];
            $detalle->procod = $codproducto;
            $detalle->cdepsu = $codproducto;
            $detalle->cdedes = substr($detpro[$index],0,strpos($detpro[$index],'*'));;
            $detalle->cdevun = $vunit[$index];
            $detalle->cdepuni = $puni[$index];
            $detalle->cdeigv = $vigv[$index];
            $detalle->tigcod = $tigv[$index];
            $detalle->cdepve = $vsub[$index];
            $detalle->cdevve = $vtot[$index];
            $detalle->presentacion = $presentaciones[$index];
            $detalle->save();

        

          }
           
       
       return Redirect::to('/movgastos');
    
	  
      
       
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
      $comprobante = DB::tABLE('cpe_cabecera as cab')
      ->join('cliente as cli','cab.clicod','=','cli.clicod')
      ->join('tipo_documento as tdo','cab.tdocod','=','tdo.tdocod')
      ->join('tipo_documento_identidad as tdi','cab.tdicod','=','tdi.tdicod')
      ->join('moneda as mon','cab.moncod','=','mon.moncod')
      ->join('tipo_operacion as top','cab.topcod','=','top.topcod')
      ->where('serdoc','=',$ser)
      ->where('numdoc','=',$num)
      ->where('IdEmpresa','=',$rucemp)
      ->where('ccasunrescod','=','0')
      ->where('ccabaj','=',NULL)
      //->orwhere(DB::raw('substr(respse,1,2)'),'=','04')
      ->take(10)->get();

      $results = array();

      foreach($comprobante as $c => $comp){
        $sernum=$comp->serdoc.'-'.$comp->numdoc;
        $results[] = ['value'=>$sernum,'serdoc'=>$comp->serdoc,'numdoc'=>$comp->numdoc,'clinum'=>$comp->ccandi,'clinom'=>$comp->ccanom,'clidir'=>$comp->clidir,'clicor'=>$comp->clicor,'tdomod'=>$comp->tdodes,'tdides'=>$comp->tdides,'monnom'=>$comp->monnom,'tipcambio'=>$comp->tipcambio,'topdes'=>$comp->topdes,'tdicod'=>$comp->tdicod,'tdocod'=>$comp->tdocod,'moncod'=>$comp->moncod,'fecemi'=>$comp->ccafem,'idcabecera'=>$comp->IdCpe_cabecera_gasto];
      }
      return response()->json($results);
    }


     public function buscarcomprobantelista(Request $request){
      $search = $request->term;

      $ser = substr($search,strpos($search,'-')-4,4);
      $num = substr($search,strpos($search,'-')+1,8);

      $rucemp = trim(Auth::user()->IdEmpresa);

      $compcabecera = DB::tABLE('cpe_cabecera as cpe_c')->select('ccaobs','estado','serdoc','numdoc')
      ->where('serdoc','=',$ser)
      ->where('numdoc','=',$num)
      ->where('IdEmpresa','=',$rucemp);

      $comprobante = DB::tABLE('cpe_nota as cpe_n')->select('ccaobs','estado','cpe_n.serdoc','cpe_n.numdoc')
       ->join('cpe_cabecera as cpe_c','cpe_n.IdCpe_cabecera_gasto','=','cpe_c.IdCpe_cabecera_gasto')
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
      $comprobante = DB::tABLE('cpe_cabecera as cab')
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
        $results[] = ['value'=>$sernum,'serdoc'=>$comp->serdoc,'numdoc'=>$comp->numdoc,'tdomod'=>$comp->tdodes,'tdocod'=>$comp->tdocod,'fecemi'=>$comp->ccafem,'idcabecera'=>$comp->IdCpe_cabecera_gasto,'monnom'=>$comp->monnom,'ccaitv'=>$comp->ccaitv];
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
  

    public function consultarcambio(Request $request){
      $search = $request->term;
      $cambio = DB::tABLE('tipocambio')->where('FecTipCambio','=',$search)->take(10)->get();
      $results = array();

      foreach($cambio as $tc => $tcam){
        $results[] = ['value'=>$tcam->FecTipCambio,'cam'=>$tcam->CamCompra];
      }
       return response()->json($results);
    }

    public function consultartipcambio(Request $request){
         $search = $request->fecemi;
         $cambio = DB::tABLE('tipocambio')->where('FecTipCambio','=',$search)->first();

         $res = $cambio->CamCompra;
       
      return $res;
    }

    public function consultargasto(Request $request){
      $search = $request->term;       
       $rucemp = trim(Auth::user()->IdEmpresa);
      $productos= DB::tABLE('tipos_gastos')->where('codgasto', 'like','%'.$search.'%')->where('IdEmpresa','=',$rucemp)->where('estgasto','=','Activo')
      // $productos= DB::tABLE('productos')->where('procod', '=',$search)->where('IdEmpresa','=',$rucemp)->where('proest','=','Activo')
        ->orderby('descgasto','asc')->get();

      $results = array();
      foreach($productos as $pro){
        $codnom = $pro->codgasto;
        $results[] = ['value'=>$codnom,'pronom'=>$pro->descgasto,'provun'=>'0','propun'=>'0','umecod'=>'UNI'];
      }

      return response()->json($results);
    }

  
	   public function consultargastonom(Request $request){
      $search = $request->term;       
       $rucemp = trim(Auth::user()->IdEmpresa);
      $productos= DB::tABLE('tipos_gastos')->where('descgasto', 'like','%'.$search.'%')->where('IdEmpresa','=',$rucemp)->where('estgasto','=','Activo')
      // $productos= DB::tABLE('productos')->where('procod', 'like','%'.$search.'%')->where('IdEmpresa','=',$rucemp)->where('proest','=','Activo')
        ->orderby('descgasto','asc')->get();

      $results = array();
      foreach($productos as $pro){
        $codnom = $pro->descgasto;
        $results[] = ['value'=>$codnom,'codpro'=>$pro->codgasto,'provun'=>'0','propun'=>'0','umecod'=>'UNI'];
      }

      return response()->json($results);
    }

     public function consultarproductonomsinstock(Request $request){
      $search = $request->term;       
       $rucemp = trim(Auth::user()->IdEmpresa);
      $productos= DB::tABLE('productos')->where('pronom', 'like','%'.$search.'%')->where('IdEmpresa','=',$rucemp)->where('proest','=','Activo')
      // $productos= DB::tABLE('productos')->where('procod', 'like','%'.$search.'%')->where('IdEmpresa','=',$rucemp)->where('proest','=','Activo')
        ->orderby('pronom','asc')->get();

      $results = array();
      foreach($productos as $pro){
        $codnom = $pro->pronom." * LABORATORIO: ".$pro->prov_id." STOCK: ".$pro->stock;
        $results[] = ['value'=>$codnom,'codpro'=>$pro->procod,'provun'=>$pro->provun,'propun'=>$pro->propun,'umecod'=>$pro->umecod];
      }

      return response()->json($results);
    }



    public function consultartdi(Request $request){
      
      $search = $request->term;       
      
      $docidentidad = DB::tABLE('tipo_documento_identidad')->where('tdides', 'like','%'.$search.'%')->where('tdides','like','%'.$search.'%')->where('tdiest','=','Activo')->get();

       $results = array();
      foreach($docidentidad as $tdi){
        $results[] = ['id'=>$tdi->tdicod,'text'=>$tdi->tdides];
      }

      return response()->json($results);

    }

    public function verificarcomprobante(Request $request){
      $codunique = $request->get('codunique');

      $comprobante= DB::tABLE('cpe_cabecera')->where('codunique','=',$codunique)->first();
     
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


        //DATOS DOCUMENTO RELACIONADO

        $tdomod = $request->get('tdomod');
       // $tipnc = $request->get('tipnc');
       
  
        $docmod = DB::tABLE('cpe_cabecera')->select('ccaobs','estado','IdCpe_cabecera_gasto')->where('IdEmpresa','=',$rucemp)->where('serdoc','=',$serdocmod)->where('numdoc','=',$numdocmod)->first();

        $dat_cli = DB::tABLE('cliente')->where('clinum',$clinum)->where('rucemp',$rucemp)->first();


        $IdCpe_cabecera_gasto=$docmod->IdCpe_cabecera_gasto;
        //-----FIN DATOS DOCUMENTOS RELACIONADOS       
        
        //Registrar la cabecera de la factura

        $cabecera = new cpe_nota;
        $cabecera->tdocod = $tdocod;
        //$cabecera->tdocod = $tdocod;
        $cabecera->ccafem = $fecemi;
        $cabecera->ccaobs = $motivo;
        $cabecera->serdoc = $serdoc;
        $cabecera->numdoc = $numdoc;
        $cabecera->ccandi = $clinum;
        $cabecera->ccanom = $clinom;
        $cabecera->clicod = $dat_cli->clicod;
        $cabecera->tipcambio = $tipcambio;
        $cabecera->tipnot = $tipnot;
        $cabecera->moncod = $request->get('tipmon');
       // $cabecera->IdEmpresa = $rucemp;
        $cabecera->ccacar = $request->get('otrosc');
        $cabecera->ccatvg = $request->get('grav');
        $cabecera->ccatvgr = $request->get('grat');
        $cabecera->ccatvi = $request->get('inaf');
        $cabecera->ccatve = $request->get('exon');
        $cabecera->ccaigv = $request->get('igv');
        $cabecera->ccaisc = $request->get('isc');
        $cabecera->ccaotr = $request->get('otros');
        $cabecera->ccaitv = $request->get('total');
        $cabecera->serdoc= $request->get('serdoc');
		$cabecera->ccatde = $request->get('totdesc');
        $cabecera->IdEmpresa = $rucemp;
        $cabecera->IdCpe_cabecera_gasto = $IdCpe_cabecera_gasto;
        $cabecera->IdUsuario = Auth::user()->IdUsuario;

        $nota = $serdoc.'-'.$numdoc;
        $cabfactura = cpe_cabecera::findOrFail($IdCpe_cabecera_gasto);
        $cabfactura->ccanot = $nota;
        


        if ($tdocod =='3') {
            if($tipdoc =='1'){
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
                

            }elseif($tipdoc=='2'){
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
                

            }
        }elseif ($tdocod =='4') {
            if($tipdoc =='1'){
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
              
            }elseif($tipdoc=='2'){
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
        $puni = $request->get('preuni');
        $vigv = $request->get('vigv');
        $tigv = $request->get('tigv');
        $vsub = $request->get('vsub');
        $vtot = $request->get('vtot');


           foreach( $unidades as $index => $ume ) {
            $detalle = new cpe_nota_detalle;
            $detalle->IdCpe_nota =  $cabecera->IdCpe_nota; 
            $dpro = $detpro[$index];
            $detalle->cdedes = $dpro;
            
              $codproducto = $codpro[$index];
              $detalle->umecod = $ume;
              $detalle->cdecan = $cantidades[$index];
              $detalle->procod = $codproducto;
              $detalle->cdepsu = $codproducto;
              $detalle->cdevun = $vunit[$index];
              $detalle->cdepuni = $puni[$index];
              $detalle->cdeigv = $vigv[$index];
              $detalle->tigcod = $tigv[$index];
              $detalle->cdepve = $vsub[$index];
              $detalle->cdevve = $vtot[$index];
            
            //Guardar en una variable los items en el archivo .det
           $codumecin = unidad_medida::findOrFail($ume);
                    $detallejson[] = array( "unidad_de_medida"=> $codumecin->umecin,
                        "codigo"                    => $detalle->procod,
                        "descripcion"               => $detalle->cdedes,
                        "cantidad"                  => $detalle->cdecan,
                        "valor_unitario"            => $detalle->cdevun,
                        "precio_unitario"           => $detalle->cdepuni,
                        "descuento"                 => "",
                        "subtotal"                  => $detalle->cdepve,
                        "tipo_de_igv"               => $detalle->tigcod,
                        "igv"                       => $detalle->cdeigv,
                        "total"                     => $detalle->cdevve,
                        "anticipo_regularizacion"   => "false",
                        "anticipo_documento_serie"  => "",
                        "anticipo_documento_numero" => "");

          }
 
        //Guardar en una variable el nombre del archivo cab
           $cabfile =  $rucemp.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.NOT'; 


        //Crear el archivo cab e insertar el contenido
        //$archivo = fopen($raiz.$cabfile, "a");
        if($tdocod =='3'){
          $tipnotc=$tipnot;
          $tipnotd="";
        }elseif($tdocod=='4'){
           $tipnotd=$tipnot;
           $tipnotc="";
        }

           $data = array(
            "operacion"       => "generar_comprobante",
            "tipo_de_comprobante"               => $tdocod,
            "serie"                             => $sercomp,
            "numero"        => $numcomp,
            "sunat_transaction"     => '1',
            "cliente_tipo_de_documento"   => $tdicod,
            "cliente_numero_de_documento" => $clinum,
            "cliente_denominacion"              => $clinom,
            "cliente_direccion"                 => $clidir,
            "cliente_email"                     => $clicor,
            "cliente_email_1"                   => "",
            "cliente_email_2"                   => "",
            "fecha_de_emision"                  => date('d-m-Y', strtotime($fecemi)),
            "fecha_de_vencimiento"              => "",
            "moneda"                            => $mondoc,
            "tipo_de_cambio"                    => $tipcambio,
            "porcentaje_de_igv"                 => "18.00",
            "descuento_global"                  => $descglb,
            "total_descuento"                   => $descglb,
            "total_anticipo"                    => "",
            "total_gravada"                     => $grav,
            "total_inafecta"                    => $inaf,
            "total_exonerada"                   => $exon,
            "total_igv"                         => $igv,
            "total_gratuita"                    => $grat,
            "total_otros_cargos"                => $otros,
            "total"                             => $total,
            "percepcion_tipo"                   => "",
            "percepcion_base_imponible"         => "",
            "total_percepcion"                  => "",
            "total_incluido_percepcion"         => "",
            "detraccion"                        => "false",
            "observaciones"                     => $motivo,
            "documento_que_se_modifica_tipo"    => $tipdoc,
            "documento_que_se_modifica_serie"   => $serdocmod,
            "documento_que_se_modifica_numero"  => $numdocmod,
            "tipo_de_nota_de_credito"           => $tipnotc,
            "tipo_de_nota_de_debito"            => $tipnotd,
            "enviar_automaticamente_a_la_sunat" => "true",
            "enviar_automaticamente_al_cliente" => "true",
            "codigo_unico"                      => "",
            "condiciones_de_pago"               => "",
            "medio_de_pago"                     => "",
            "placa_vehiculo"                    => "",
            "orden_compra_servicio"             => "",
            "tabla_personalizada_codigo"        => "",
            "formato_de_pdf"                    => "A4",
            "items"                             =>$detallejson,
        );
    
        $data_json = json_encode($data);

        $leer_respuesta = self::webserviceonline($data_json);
           
        
        if (isset($leer_respuesta['errors'])) {
           
            if($leer_respuesta['codigo']=='23'){
              $leer_respuesta['errors']="El Documento ya existe.";
            }

            if($leer_respuesta['codigo']=='24'){
              $leer_respuesta['errors']="El documento indicado no existe o no fue enviado.";
            }

            return Redirect::to('/SisFact')->with('info',$leer_respuesta['errors']);

        } else {

          $cabecera->save();
          $cabfactura->update();
          
          foreach( $unidades as $index => $ume ) {
            $detalle = new cpe_nota_detalle;
            $detalle->IdCpe_nota =  $cabecera->IdCpe_nota; 
            $dpro = $detpro[$index];
            $detalle->cdedes = $dpro;
            
              $codproducto = $codpro[$index];
              $detalle->umecod = $ume;
              $detalle->cdecan = $cantidades[$index];
              $detalle->procod = $codproducto;
              $detalle->cdepsu = $codproducto;
              $detalle->cdevun = $vunit[$index];
              $detalle->cdeigv = $vigv[$index];
              $detalle->tigcod = $tigv[$index];
              $detalle->cdepve = $vsub[$index];
              $detalle->cdevve = $vtot[$index];
              $detalle->save();
         
          }


          $comp = cpe_nota::findOrFail($cabecera->IdCpe_nota);
          $comp->codhash = $leer_respuesta['codigo_hash'];
          $comp->ccacodsun = $leer_respuesta['aceptada_por_sunat'];
          $comp->ccadessun = substr($leer_respuesta['sunat_description'],0,250);
          $comp->ccasunrescod = substr($leer_respuesta['sunat_responsecode'],0,250);
          $comp->ccasunsoaperr = substr($leer_respuesta['sunat_soap_error'],0,250);
          $comp->ccaenlace = substr($leer_respuesta['enlace'],0,250);
          $comp->ccasunnot = substr($leer_respuesta['sunat_note'],0,250);
         // $comp->ccapdfzip = $leer_respuesta['pdf_zip_base64'];
         // $comp->ccaxmlzip = $leer_respuesta['xml_zip_base64'];
         // $comp->ccacdrzip = $leer_respuesta['cdr_zip_base64'];
          $comp->ccaqr = $leer_respuesta['cadena_para_codigo_qr'];

          $comp->update();
    
        return Redirect::to('/SisFact')->with('success',$leer_respuesta['sunat_description']);

      }
   
    }




    //NOTA DE CREDITO Y DÉBITO

    public function tiponotacd($tdocod=0,$idcabecera=0,$ncdcod){
      $rucemp = Auth::user()->IdEmpresa;
    //  $cabecera=DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera_gasto','=',$idcabecera)->where('IdEmpresa','=',$rucemp)->first();

       $cabecera = DB::tABLE('cpe_cabecera as cab')
      ->join('cliente as cli','cab.clicod','=','cli.clicod')
      ->join('tipo_documento as tdo','cab.tdocod','=','tdo.tdocod')
      ->join('tipo_documento_identidad as tdi','cab.tdicod','=','tdi.tdicod')
      ->join('moneda as mon','cab.moncod','=','mon.moncod')
      ->join('tipo_operacion as top','cab.topcod','=','top.topcod')
      ->where('IdCpe_cabecera_gasto','=',$idcabecera)->where('IdEmpresa','=',$rucemp)
      ->first();

     // $detalle=DB::tABLE('cpe_detalle as det')->join('unidad_medida as umed','det.umecod','=','umed.umecod')->where('IdCpe_cabecera_gasto','=',$idcabecera)->get();

      $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();

      

      // consultar unidades de medida
      $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
      ->orderBy('umecod','asc')->get();

      // consultar tipo de operaciones
      $operaciones = DB::tABLE('tipo_operacion')->where('topest','=','Activo')
      ->orderBy('topcod','asc')->get();

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
      $productos= DB::tABLE('productos')->where('IdEmpresa','=',$rucemp)->where('proest','=','Activo')
      ->orderby('pronom','asc')->get();

      // consultar tipos de monedas
      $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();


 

      if ($ncdcod =='3') {
        if($tdocod =='1'){
          $senuncd = DB::tABLE('empresa')->select('ccaobs','estado','FcseEmpresa','FcnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $nota = DB::tABLE('tipo_nota_credito')->where('ncest','=','Activo')->get();
        }elseif($tdocod=='2'){
          $senuncd = DB::tABLE('empresa')->select('ccaobs','estado','BcseEmpresa','BcnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $nota = DB::tABLE('tipo_nota_credito')->where('ncest','=','Activo')->get();
        }
      }elseif ($ncdcod =='4') {
          if($tdocod =='1'){
          $senuncd = DB::tABLE('empresa')->select('ccaobs','estado','FdseEmpresa','FdnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $nota = DB::tABLE('tipo_nota_debito')->where('ndest','=','Activo')->get();
        }elseif($tdocod=='2'){
          $senuncd = DB::tABLE('empresa')->select('ccaobs','estado','BdseEmpresa','BdnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $nota = DB::tABLE('tipo_nota_debito')->where('ndest','=','Activo')->get();
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
    //  $cabecera=DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera_gasto','=',$idcabecera)->where('IdEmpresa','=',$rucemp)->first();

       $cabecera = DB::tABLE('cpe_cabecera as cab')
      ->join('cliente as cli','cab.clicod','=','cli.clicod')
      ->join('tipo_documento as tdo','cab.tdocod','=','tdo.tdocod')
      ->join('tipo_documento_identidad as tdi','cab.tdicod','=','tdi.tdicod')
      ->join('moneda as mon','cab.moncod','=','mon.moncod')
      ->join('tipo_operacion as top','cab.topcod','=','top.topcod')
      ->where('IdCpe_cabecera_gasto','=',$idcabecera)->where('IdEmpresa','=',$rucemp)
      ->first();

      $detalle=DB::tABLE('cpe_detalle as det')->join('unidad_medida as umed','det.umecod','=','umed.umecod')->join('tipo_igv as ti','det.tigcod','=','ti.tigcod')->where('IdCpe_cabecera_gasto','=',$idcabecera)->get();

      $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();


      // consultar unidades de medida
      $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
      ->orderBy('umecod','asc')->get();

      // consultar tipo de operaciones
      $operaciones = DB::tABLE('tipo_operacion')->where('topest','=','Activo')
      ->orderBy('topcod','asc')->get();

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
      $productos= DB::tABLE('productos')->where('IdEmpresa','=',$rucemp)->where('proest','=','Activo')
      ->orderby('pronom','asc')->get();

      // consultar tipos de monedas
      $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();


      if ($ncdcod =='3') {
        if($tdocod =='1'){
          $senuncd = DB::tABLE('empresa')->select('ccaobs','estado','FcseEmpresa','FcnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $nota = DB::tABLE('tipo_nota_credito')->where('nccod','=',$tipnot)->first();
        }elseif($tdocod=='2'){
          $senuncd = DB::tABLE('empresa')->select('ccaobs','estado','BcseEmpresa','BcnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $nota = DB::tABLE('tipo_nota_credito')->where('nccod','=',$tipnot)->first();
        }
      }elseif ($ncdcod =='4') {
          if($tdocod =='1'){
          $senuncd = DB::tABLE('empresa')->select('ccaobs','estado','FdseEmpresa','FdnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $nota = DB::tABLE('tipo_nota_debito')->where('ndcod','=',$tipnot)->first();
        }elseif($tdocod=='2'){
          $senuncd = DB::tABLE('empresa')->select('ccaobs','estado','BdseEmpresa','BdnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $nota = DB::tABLE('tipo_nota_debito')->where('ndcod','=',$tipnot)->first();
        }
      }
      if($ncdcod=='3'){
        if($tipnot=='1' || $tipnot=='2' || $tipnot=='3'){
          
          return view('empresas.comprobantes.emitirnota',['cabecera'=>$cabecera,'detalle'=>$detalle,'senuncd'=>$senuncd,'igv'=>$igv,'monedas'=>$monedas,'unidades'=>$unidades,'operaciones'=>$operaciones,'docidentidad'=>$docidentidad,'clientes'=>$clientes,'tdocod'=>$tdocod,'ncdcod'=>$ncdcod,'productos'=>$productos,'doccomprobante'=>$doccomprobante,'nota'=>$nota]);
          
        }else{

          return view('empresas.comprobantes.emitirnota2',['cabecera'=>$cabecera,'detalle'=>$detalle,'senuncd'=>$senuncd,'igv'=>$igv,'monedas'=>$monedas,'unidades'=>$unidades,'operaciones'=>$operaciones,'docidentidad'=>$docidentidad,'clientes'=>$clientes,'tdocod'=>$tdocod,'ncdcod'=>$ncdcod,'productos'=>$productos,'doccomprobante'=>$doccomprobante,'nota'=>$nota]);
        }
      }elseif($ncdcod=='4'){

          return view('empresas.comprobantes.emitirnota2',['cabecera'=>$cabecera,'detalle'=>$detalle,'senuncd'=>$senuncd,'igv'=>$igv,'monedas'=>$monedas,'unidades'=>$unidades,'operaciones'=>$operaciones,'docidentidad'=>$docidentidad,'clientes'=>$clientes,'tdocod'=>$tdocod,'ncdcod'=>$ncdcod,'productos'=>$productos,'doccomprobante'=>$doccomprobante,'nota'=>$nota]);
      }
    }

    //Comunicación de baja desde el menú Comprobantes
    public function bajacomprobante(){
      $rucemp = Auth::user()->IdEmpresa;
      $fecact = date('Y-m-d');
      $numbaj = DB::tABLE('empresa')->select('ccaobs','estado','IdEmpresa','BanuEmpresa','fecbaja')->where('IdEmpresa','=',$rucemp)->first(); 
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
      $inicomp = substr($serdoc,0, 1);

      /*if($inicomp =='B'){
        $numbaj = DB::tABLE('empresa')->select('ccaobs','estado','IdEmpresa','RcnuEmpresa','FecRc')->where('IdEmpresa','=',$rucemp)->first(); 
        if($numbaj->FecRc==$fecact){
          $cor=$numbaj->RcnuEmpresa+1;
        }else{
          $cabcomp = empresa::findOrFail($numbaj->IdEmpresa);
          $cabcomp->FecRc = $fecact;
          $cabcomp->BanuEmpresa = 0;
          $cabcomp->update();
          $cor = $cabcomp->BanuEmpresa+1;
        }
      }elseif($inicomp == 'F'){
        $numbaj = DB::tABLE('empresa')->select('ccaobs','estado','IdEmpresa','BanuEmpresa','fecbaja')->where('IdEmpresa','=',$rucemp)->first(); 
        if($numbaj->fecbaja==$fecact){
          $cor=$numbaj->BanuEmpresa+1;
        }else{
          $cabcomp = empresa::findOrFail($numbaj->IdEmpresa);
          $cabcomp->fecbaja = $fecact;
          $cabcomp->BanuEmpresa = 0;
          $cabcomp->update();
          $cor = $cabcomp->BanuEmpresa+1;
        }
      }*/
      

      if($tdocod=="1" || $tdocod=="2" || $tdocod=="11"){
        $comp = DB::tABLE('cpe_cabecera as cab')
        ->join('moneda as mon','cab.moncod','=','mon.moncod')
        ->where('serdoc','=',$serdoc)
        ->where('numdoc','=',$numdoc)
        ->where('IdEmpresa','=',$rucemp)->first();
      }elseif($tdocod=="3" || $tdocod=="4"){
        $comp = DB::tABLE('cpe_nota as nota')
        ->join('moneda as mon','nota.moncod','=','mon.moncod')
        ->where('serdoc','=',$serdoc)
        ->where('numdoc','=',$numdoc)
        ->where('IdEmpresa','=',$rucemp)->first();
      }

      $sernumdoc = $serdoc.'-'.$numdoc;
      return view('empresas.comprobantes.emitirbaja',['sernumdoc'=>$sernumdoc,'tdodes'=>$tdodes,'tdocod'=>$tdocod,'fecemi'=>$fecemi,'monnom'=>$comp->monnom,'ccaitv'=>$comp->ccaitv]);

     
    }

     public function registrarbajacomprobante(Request $request){
      $rucemp = Auth::user()->IdEmpresa;
      $empresa = Empresa::findOrFail($rucemp);
      $serdocbaja = $request->get('serdocbaja');
      $fecbaj = $request->get('fecbaj');
     // $numbaj = $request->get('numbaj');
     // $numbajmod = str_pad($numbaj,3,"0", STR_PAD_LEFT);

      $obser = $request->get('obser');
      $fecemi = $request->get('fecemi');
      $tdomod = $request->get('tdomod');
      $tdocod = $request->get('tdo_cod');

       $cabfile =  'R-'.$rucemp.'-RA'.'-'.str_replace("-", "", $fecbaj).'.json';
       $filecons =  'CB-'.$rucemp.'-RA'.'-'.str_replace("-", "", $fecbaj).'.json';
      //$cabfile =  'R-'.$rucemp.'-RA'.'-'.str_replace("-", "", $fecbaj).'-'.$numbaj.'.json';
      //$filecons =  'CB-'.$rucemp.'-RA'.'-'.str_replace("-", "", $fecbaj).'-'.$numbaj.'.json';
      //$nompdffile =  $rucemp.'-'.str_replace("-", "", $fecbaj).'-'.$numbajmod.'.pdf'; 
   
        $docbaja = new cpe_baja;
        $ser = substr($serdocbaja,strpos($serdocbaja,'-')-4,4);
        $num = substr($serdocbaja,strpos($serdocbaja,'-')+1,8);
        $numdoc = str_pad($num,8,"0", STR_PAD_LEFT);

        $docbaja->cbanum =  $ser.'-'.$numdoc;
        //$docbaja->cbacor =  $numbaj; 
        $docbaja->cbamot =  $obser; 
        $docbaja->cbdfco =  $fecbaj; 
        $docbaja->cbafec =  $fecemi; 
        $docbaja->tdocod =  $tdocod; 
        $docbaja->IdEmpresa =  $rucemp; 
        
        if($tdocod =='1' || $tdocod =='2' || $tdocod =='11'){
          $cabecera= DB::tABLE('cpe_cabecera')->select('ccaobs','estado','IdCpe_cabecera_gasto','serdoc','numdoc')->where('tdocod','=',$tdocod)->where('serdoc','=',$ser)->where('numdoc','=',$numdoc)->where('IdEmpresa',$rucemp)->first();
          $docbaja->IdCpe_cabecera_gasto = $cabecera->IdCpe_cabecera_gasto; 
          $cabcomp = cpe_cabecera::findOrFail($cabecera->IdCpe_cabecera_gasto);
          $cabcomp->ccabaj = str_replace("-", "", $fecbaj);

          $detallecomp = DB::tABLE('cpe_detalle')->where('IdCpe_cabecera_gasto',$cabecera->IdCpe_cabecera_gasto)->get();

          foreach ($detallecomp as $detalle){

              if($empresa->mod_almacen =='1'){

                if($detalle->presentacion ==''){

                  $IdProducto = DB::tABLE('productos')->WHERE('procod',$detalle->procod)->where('IdEmpresa',$rucemp)->first();
                  $movimiento = new movimientos;
                  $movimiento->mov_fec = date('d-m-Y', strtotime($fecemi)); 
                  $movimiento->mov_tip = 'IA';
                  $movimiento->mov_mot = 'Anulación de Comprobante';
                  $movimiento->cantidad = $detalle->cdecan;
                  $movimiento->IdEmpresa = $rucemp;
                  $movimiento->unidad= $detalle->umecod;
                  $movimiento->IdProducto = $IdProducto->IdProducto;
                  $movimiento->save();

                   $stock= DB::table("productos")
                ->select('ccaobs','estado',"productos.*",
                    DB::raw("(SELECT SUM(cantidad) FROM movimientos
                                WHERE movimientos.IdProducto = productos.IdProducto
                                AND mov_tip='I' and IdEmpresa='".$rucemp."' and  procod='".$detalle->procod."'
                                GROUP BY movimientos.IdProducto) as Ingresos"),
                    DB::raw("(SELECT SUM(cantidad) FROM movimientos
                                WHERE movimientos.IdProducto = productos.IdProducto
                                AND mov_tip='E' and IdEmpresa='".$rucemp."' and  procod='".$detalle->procod."'
                                GROUP BY movimientos.IdProducto) as Egresos"),
                    DB::raw("(SELECT SUM(cantidad) FROM movimientos
                                WHERE movimientos.IdProducto = productos.IdProducto
                                AND mov_tip='IA' and IdEmpresa='".$rucemp."' and procod='".$detalle->procod."'
                                GROUP BY movimientos.IdProducto) as Anula"))
                    ->where('IdEmpresa',$rucemp)
                    ->where('procod',$detalle->procod)->first();

                     $stockprod= $stock->Ingresos - ($stock->Egresos-$stock->Anula);
                     $stock_prod =productos::findOrFail($IdProducto->IdProducto);
                    $stock_prod->stock = $stockprod;
                    $stock_prod->update();

                }else{

                  $IdProducto = DB::tABLE('productos')->WHERE('procod',$detalle->procod)->where('IdEmpresa',$rucemp)->first();
                  $movimiento = new movimientos;
                  $movimiento->mov_fec = date('d-m-Y', strtotime($fecemi)); 
                  $movimiento->mov_tip = 'IA';
                  $movimiento->mov_mot = 'Anulación de Comprobante';
                  $movimiento->cantidad = $detalle->cdecan*$detalle->presentacion;
                  $movimiento->IdEmpresa = $rucemp;
                  $movimiento->unidad= $detalle->umecod;
                  $movimiento->IdProducto = $IdProducto->IdProducto;
                  $movimiento->save();

                   $stock= DB::table("productos")
                ->select('ccaobs','estado',"productos.*",
                    DB::raw("(SELECT SUM(cantidad) FROM movimientos
                                WHERE movimientos.IdProducto = productos.IdProducto
                                AND mov_tip='I' and IdEmpresa='".$rucemp."' and  procod='".$detalle->procod."'
                                GROUP BY movimientos.IdProducto) as Ingresos"),
                    DB::raw("(SELECT SUM(cantidad) FROM movimientos
                                WHERE movimientos.IdProducto = productos.IdProducto
                                AND mov_tip='E' and IdEmpresa='".$rucemp."' and  procod='".$detalle->procod."'
                                GROUP BY movimientos.IdProducto) as Egresos"),
                    DB::raw("(SELECT SUM(cantidad) FROM movimientos
                                WHERE movimientos.IdProducto = productos.IdProducto
                                AND mov_tip='IA' and IdEmpresa='".$rucemp."' and procod='".$detalle->procod."'
                                GROUP BY movimientos.IdProducto) as Anula"))
                    ->where('IdEmpresa',$rucemp)
                    ->where('procod',$detalle->procod)->first();

                     $stockprod= $stock->Ingresos - ($stock->Egresos-$stock->Anula);
                     $stock_prod =productos::findOrFail($IdProducto->IdProducto);
                    $stock_prod->stock = $stockprod;
                    $stock_prod->update();


                }
              
            }

          }

   
          
        }elseif($tdocod =='3' || $tdocod =='4'){
          $cabecera= DB::tABLE('cpe_nota')->select('ccaobs','estado','IdCpe_nota','serdoc','numdoc')->where('tdocod','=',$tdocod)->where('serdoc','=',$ser)->where('numdoc','=',$numdoc)->where('IdEmpresa',$rucemp)->first();
          $docbaja->IdCpe_cabecera_gasto = $cabecera->IdCpe_nota; 
      
          $cabcomp = cpe_nota::findOrFail($cabecera->IdCpe_nota);
          $cabcomp->ccabaj = str_replace("-", "",$fecbaj);
          
        }

        $data = array("operacion" => "generar_anulacion",
                        "tipo_de_comprobante"   => $tdocod,
                        "serie"                 => $ser,
                        "numero"                => $num,
                        "motivo"                => $docbaja->cbamot,
                        "codigo_unico"          => ""
                      );

        $data_json = json_encode($data);

        $archivo = fopen($cabfile, "a");
        fputs($archivo,$data_json);
        fclose($archivo);


        $leer_respuesta = self::webserviceonline($data_json);
           
       if( $tdocod !='11'){
        if (isset($leer_respuesta['errors'])) {
           
            if($leer_respuesta['codigo']=='23'){
              $leer_respuesta['errors']="El Documento ya existe.";
            }

            if($leer_respuesta['codigo']=='24'){
              $leer_respuesta['errors']="El documento indicado no existe o no fue enviado.";
            }

            return Redirect::to('/SisFact')->with('info',$leer_respuesta['errors']);

        } else {

          $docbaja->save();
          $cabcomp->update();

          $empresa = Empresa::findOrFail($rucemp);
          //$empresa->BanuEmpresa = $numbaj;
          $empresa->update();

          //Consultar anulaciones
          $consult = array("operacion" => "consultar_anulacion",
                        "tipo_de_comprobante"   => $docbaja->tdocod,
                        "serie"                 => $ser,
                        "numero"                => $num,
                      );

        $data_json_consult = json_encode($consult);

        $archcons = fopen($filecons, "a");
        fputs($archcons,$data_json_consult);
        fclose($archcons);

        $leer_respuesta_cons = self::webserviceonline($data_json_consult);
          
          $idbaja = DB::tABLE('cpe_baja')->select('ccaobs','estado','IdCpe_baja')->where('IdCpe_cabecera_gasto','=',$docbaja->IdCpe_cabecera_gasto)->first();
          $comp = cpe_baja::findOrFail($idbaja->IdCpe_baja);

          $comp->cbacor = $leer_respuesta_cons['numero'];
          $comp->ccaenlace = $leer_respuesta_cons['enlace'];
          $comp->ccasuntick = $leer_respuesta_cons['sunat_ticket_numero'];
          $comp->ccacodsun = $leer_respuesta_cons['aceptada_por_sunat'];
          $comp->ccadessun = substr($leer_respuesta_cons['sunat_description'],0,250);
          $comp->ccasunrescod = substr($leer_respuesta_cons['sunat_responsecode'],0,250);
          $comp->ccasunsoaperr = substr($leer_respuesta_cons['sunat_soap_error'],0,250);
          $comp->ccasunnot = $leer_respuesta_cons['sunat_note'];
          //$comp->ccapdfzip = $leer_respuesta_cons['pdf_zip_base64'];
          //$comp->ccaxmlzip = $leer_respuesta_cons['xml_zip_base64'];
          //$comp->ccacdrzip = $leer_respuesta_cons['cdr_zip_base64'];
          $comp->update();

        

          }

        return Redirect::to('/SisFact')->with('success',$leer_respuesta_cons['sunat_description']);

      }else{

          $docbaja->save();
          $cabcomp->update();

          $empresa = Empresa::findOrFail($rucemp);
          //$empresa->BanuEmpresa = $numbaj;
          $empresa->update();
         return Redirect::to('/SisFact');
      }
        
      
    }
    

    public function listarnotas($idcabecera){

      $rucemp =Auth::user()->IdEmpresa;
      $notas = DB::tABLE('cpe_nota as n')->select('ccaobs','estado','n.ccaenlace','n.ccafem','n.serdoc','n.numdoc','tdodes','c.ccandi','c.ccanom','mn.monnom','n.ccaitv','n.tdocod','c.IdEmpresa','n.IdCpe_nota','n.tdocod','n.codhash','n.ccasunrescod','n.ccabaj')
      ->join('tipo_documento as td','n.tdocod','=','td.tdocod')
      ->join('cpe_cabecera as c','n.IdCpe_cabecera_gasto','=','c.IdCpe_cabecera_gasto')
      ->join('moneda as mn','c.moncod','=','mn.moncod')
      ->where('n.IdCpe_cabecera_gasto','=',$idcabecera)
      ->where('c.IdEmpresa','=',$rucemp)
      ->orderby('n.IdCpe_nota','desc')
      ->paginate(10);
       $empresa = Empresa::findOrFail($rucemp);

       $sndocmod = DB::tABLE('cpe_cabecera')->select('ccaobs','estado','serdoc','numdoc')->where('IdCpe_cabecera_gasto','=',$idcabecera)->first();

        return view('empresas.comprobantes.listarnotas',['notas'=>$notas,'empresa'=>$empresa,'sndocmod'=>$sndocmod]);

    }

    public function ingresarpanel($idempresa){

      $user =Auth::user()->IdUsuario;
      $regemp = User::findOrFail($user);
      $regemp->IdEmpresa = $idempresa;
      $regemp->update();

      return Redirect::to('/SisFact');

    }

	pubLic function ConsultarEstado ($tdocod,$serie,$numero){
		
		$data = array
        (
          "operacion"=> "consultar_comprobante",
		  "tipo_de_comprobante"=> $tdocod,
		  "serie"=>  $serie,
		  "numero"=> $numero,
				
		);
		
	  $data_json = json_encode($data,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

	  $rucemp =Auth::user()->IdEmpresa;
      $empresa = Empresa::findOrFail($rucemp);
      // RUTA para enviar documentos
      $ruta = $empresa->wsurl;

      //TOKEN para enviar documentos
      $token = $empresa->wscontrasena;
    
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
	  
	 
		 $codfact = DB::tABLE('cpe_cabecera')->where('serdoc',$serie)->where('tdocod',$tdocod)->where('numdoc',$numero)->where('IdEmpresa',$rucemp)->first();
	
	      $comp = cpe_cabecera::findOrFail($codfact->IdCpe_cabecera_gasto);
          $comp->codhash = $leer_respuesta['codigo_hash'];
          $comp->ccacodsun = $leer_respuesta['aceptada_por_sunat'];
          $comp->ccadessun = $leer_respuesta['sunat_description'];
          $comp->ccasunrescod = $leer_respuesta['sunat_responsecode'];
          $comp->ccasunsoaperr = $leer_respuesta['sunat_soap_error'];
          $comp->ccaenlace = $leer_respuesta['enlace'];
          $comp->ccasunnot = $leer_respuesta['sunat_note'];
          //$comp->ccapdfzip = $leer_respuesta['pdf_zip_base64'];
          //$comp->ccaxmlzip = $leer_respuesta['xml_zip_base64'];
          //$comp->ccacdrzip = $leer_respuesta['cdr_zip_base64'];
          $comp->ccaqr = $leer_respuesta['cadena_para_codigo_qr'];
          $comp->update();
		  
        return Redirect::to('/SisFact');
	}
	
    public function webserviceonline($data_json){

      $rucemp =Auth::user()->IdEmpresa;
      $empresa = Empresa::findOrFail($rucemp);
      // RUTA para enviar documentos
      $ruta = $empresa->wsurl;

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



    public function webservicepdf($tipdoc,$serdoc,$numdoc){

      $rucemp = trim(Auth::user()->IdEmpresa);
      $dataent = array (

          "user"=>array(
            "username"=> "20422559711mertra02",
            "password"=> "Mertra2018*"
          ),

          "codCPE"=>$tipdoc,
          "numSerieCPE"=>$serdoc,
          "numCPE"=>$numdoc
      );

      $ent = json_encode($dataent,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

      //return $ent;

      $ruta = "http://calidad.escondatagate.net/wsBackend/clients/getPdfURL";
  
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
          $archivo = $rucemp.'-'.$tipdoc.'-'.$serdoc.'-'.$numdoc.'.pdf';
          $tempArchivo = tempnam(sys_get_temp_dir(), $archivo);
      
      if(!empty($leer_respuesta['pdfURL'])){
      copy($leer_respuesta['pdfURL'], $tempArchivo);
      return response()->download($tempArchivo, $archivo);  
      }else{
      return Redirect::to('/SisFact');
      }
        
         // return response()->download($leer_respuesta['pdfURL']);
                
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

    public function destroy(Request $request, $id)
    {

     $motivo = $request->get('motivo');
      $rucemp = trim(Auth::user()->IdEmpresa);
      $gastosmovimientos= cpe_cabecera_gasto::findOrFail($id);
      $gastosmovimientos->estado = 'Eliminado';
       $gastosmovimientos->ccaobs = $motivo;
      $gastosmovimientos->update();

        return Redirect::to('/movgastos');
    }

}
