<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\TipoIGV;
use MasterSoft\Empresa;
use MasterSoft\Modelos\Almacen;
use MasterSoft\Cliente;
use MasterSoft\cpe_cabecera;
use MasterSoft\documento_relacionado;
use MasterSoft\unidad_medida;
use MasterSoft\cpe_detalle;
use MasterSoft\cpe_baja;
use MasterSoft\Comprobante;
use MasterSoft\usuario_facturacion;
use MasterSoft\cpe_nota_detalle;
use MasterSoft\cpe_nota;
use MasterSoft\tipos_vehiculos;
use MasterSoft\cuentascobrar;
use MasterSoft\EmpresaNegocios;
use MasterSoft\movimientosbancarios;
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
use IlluminateSupportFacadesStorage;
use DB;

class POSGaleriaController extends Controller
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


    public function indexcotizaciones(Request $request,$codfact=0)
    {

        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');
        $tdocod = $request->get('documento');

        $vehi = $request->get('placa');

        if(!empty($fecin)){

          $buscarplaca = DB::tABLE('tipos_vehiculos')->where('id_tipo_vehiculo',$request->get('placa'))->first();
          if(empty($buscarplaca->placa)){
            $placa = "";
          }else{
            $placa=$buscarplaca->placa;

          }


  
          $comprobantes = DB::tABLE('cpe_cabecera as cpe_c')->select('fechacot','cpe_c.estadocobrar','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','estadopago','estado','referencia','ccadessun')
          ->leftjoin('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
          ->leftjoin('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
          ->leftjoin('moneda as mon','cpe_c.moncod','=','mon.moncod')
          ->where('cpe_c.id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->Where(function($query) use($fecin,$fecfin,$tdocod,$placa,$vehi) {
            if($vehi!='0'){

                $query->where('cpe_c.fechacot','>=',$fecin)
                      ->where('cpe_c.fechacot','<=',$fecfin)
                      ->where('cpe_c.tdocod',$tdocod)
                      ->where('cpe_c.placa',$placa);
            }else{
                 $query->where('cpe_c.fechacot','>=',$fecin)
                      ->where('cpe_c.fechacot','<=',$fecfin)
                      ->where('cpe_c.tdocod',$tdocod);
            }
          })
          ->orderby('IdCpe_cabecera','desc')
          ->paginate(1000);

        }else{

          $comprobantes = DB::tABLE('cpe_cabecera as cpe_c')->select('fechacot','cpe_c.estadocobrar','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','estadopago','estado','referencia','ccadessun')
          ->leftjoin('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
          ->leftjoin('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
          ->leftjoin('moneda as mon','cpe_c.moncod','=','mon.moncod')
          ->where('cpe_c.id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->orderby('IdCpe_cabecera','desc')
           ->where('cpe_c.tdocod','80')
          ->orwhere('cpe_c.tdocod','70')
          ->orwhere('cpe_c.tdocod','90')
          ->paginate(1000);
        }
        

        $vehiculos=tipos_vehiculos::leftjoin('cliente','cliente.clicod','tipos_vehiculos.clicod')
                 ->leftjoin('tipo_documento_identidad','tipo_documento_identidad.tdicod','cliente.tdicod')
                 ->leftjoin('marcas','marcas.mar_id','tipos_vehiculos.mar_id')
                 ->leftjoin('modelos','modelos.mod_id','tipos_vehiculos.mod_id')
                 ->leftjoin('combustible','combustible.comb_id','tipos_vehiculos.comb_id')->get();


        return view('empresas.puntosventas.indexcotizacion',compact('comprobantes','vehiculos','fecin','fecfin','codfact'));
    }

        public function cotizaciones($placa=0)
    {
        $rucemp = trim(Auth::user()->IdEmpresa);
        $categorias = DB::tABLE('categorias')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();
        $comprobante = DB::tABLE('tipo_documento')->get();
        $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();
         $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();
      
        $combustible = DB::tABLE('combustible')->get();
        $marcas = DB::tABLE('marcas')->get();
        $modelos = DB::tABLE('modelos')->get();
        $tecnicos = DB::tABLE('tecnicos')->get();
        $clientes = DB::tABLE('cliente')->get();
        $condiciones = DB::tABLE('credito_dias')->get();


        $monedas = DB::tABLE('moneda')->get();

      

           $bus_alm = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();

            $productos = DB::tABLE('productos')
        ->select('umenom','pro_rel','costo','mar_nom','procod','pronom','propun','productos.IdProducto','productos.umecod','promocion','color','imagenproducto','precio2','precio3',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND id_almacen='".$bus_alm->id_almacen."' AND pro.id_empresa_negocio='".$bus_alm->id_empresa_negocio."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE pro.pro_rel =  productos.IdProducto) as cont_pre"))
        ->leftjoin('unidad_medida','unidad_medida.umecod','productos.umecod')
            ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
          //  ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
          ->leftjoin('marcas','marcas.mar_id','productos.marca')
        /*->where(function ($query) use($search){
                $query->where('pronom','like','%'.$search.'%')
          ->orwhere('procod',$search);
          })*/
            ->where('tipo','1')
            ->where('promocion','!=','2')
           // ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
           // ->where('id_almacen',$almacen->id_almacen)
            ->groupby('productos.IdProducto')
            ->orderby('productos.pronom')
            ->orderby('productos.umecod')
            ->get();



        if($placa==0){
            return view('empresas.puntosventas.cotizacionesnuevo',compact('categorias','comprobante','tipodocumento','igv','unidades','unidades','productos','marcas','modelos','clientes','combustible','tecnicos','condiciones','monedas'));
        }else{

             $vehiculos=tipos_vehiculos::leftjoin('cliente','cliente.clicod','tipos_vehiculos.clicod')
                 ->leftjoin('tipo_documento_identidad','tipo_documento_identidad.tdicod','cliente.tdicod')
                 ->leftjoin('marcas','marcas.mar_id','tipos_vehiculos.mar_id')
                 ->leftjoin('modelos','modelos.mod_id','tipos_vehiculos.mod_id')
                 ->leftjoin('combustible','combustible.comb_id','tipos_vehiculos.comb_id')
                 ->where('id_tipo_vehiculo',$placa)
                 ->first();
             return view('empresas.puntosventas.cotizaciones',compact('categorias','comprobante','tipodocumento','igv','unidades','unidades','productos','marcas','modelos','clientes','combustible','tecnicos','condiciones','monedas'));
        }
     

       
    }




     public function nuevaorden($tdocod=0,$cpe=0)
    { 

         $documentos = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderby('tdicod')->get();

          $clientes = DB::tABLE('cliente')->get();


        $creditos = DB::tABLE('credito_dias')->get();

        $rucemp = trim(Auth::user()->IdEmpresa);

        $equipos = DB::tABLE('tipo_equipo')->get();

        $categorias = DB::tABLE('categorias')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->get();

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

        $tecnicos = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        ->where('role_id','5')
        ->get();

        $mediospagos = DB::tABLE('medios_pagos')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();

       

        return view('empresas.puntosventas.nuevaorden',compact('categorias','comprobante','tipodocumento','igv','unidades','unidades','tdocod','cpe','mozos','creditos','mediospagos','equipos','tecnicos','documentos','clientes'));

    }


        public function puntoventagrifos($codfact=0){

       $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first(); 

          $creditos = DB::tABLE('credito_dias')->get();

            $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();

            $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);

        $creditos = DB::tABLE('credito_dias')->get();

        $documentos = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderby('tdicod')->get();
		
		 $vendedores = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
      
        ->get();
		
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

        $mediospagos = DB::tABLE('medios_pagos')->get();

        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

        $almacen = DB::tABLE('almacenes')
        ->where('predeterminado','1')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();


         $productos = DB::tABLE('productos')
        ->select('pro_rel','costo','mar_nom','procod','pronom','propun','productos.IdProducto','productos.umecod','promocion','color','imagenproducto','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.pro_rel = pro.IdProducto AND id_almacen='".$almacen->id_almacen."' AND id_empresa_negocio='".Auth::user()->id_empresa_negocio."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE pro.pro_rel =  productos.pro_rel) as cont_pre"))
        ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
      //  ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
          ->leftjoin('marcas','marcas.mar_id','productos.marca')
    /*    /*->where(function ($query) use($search){
            $query->where('pronom','like','%'.$search.'%');
      })*/
        
        //->where('cont_pre','1')

        ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
       // ->where('id_almacen',$almacen->id_almacen)
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();


        return view('empresas.puntosventas.puntoventagrifos',compact('vendedores','codfact','categorias','comprobante','tipodocumento','igv','unidades','unidades','mozos','creditos','mediospagos','clientes','documentos','datos','sucursal','almacen','productos','almacenes','creditos','empresa'));

    }
	

    public function index($codfact=0)
    {   


        
         
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
    
        return view('empresas.puntosventas.galeria',compact('habitaciones','comprobantes','users','codfact','categorias','comprobante','tipodocumento','unidades','unidades','vendedores','creditos','mediospagos','clientes','documentos','datos','monedas','senudoc','motivos','modalidades','docidentidad','doccomprobante','monedas','almacenes','gastos','combustible','marcas','modelos','tecnicos','tipos_igv','empresa','procesos','ubigeos'));
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

      //Datos del cliente
        $tdicod = $request->get('tdicod');
        $cliruc = $request->get('clinum');
        $clinom = $request->get('clinom');
        $clidir = $request->get('clidir');
        $clicor = $request->get('clicor');
        $grav = $request->get('subtotal');
        $igv = $request->get('igv');
        $total = $request->get('total');
        $mondoc = $request->get('moncod');
        $tdocod = $request->get('tdocod');
        $fecemi = $request->get('fecEmi');
        $fecven = $request->get('fecVen');
        $tippago = $request->get('txtTipPag');
    $efectivo = $request->get('efectivo1');
    $visa = $request->get('visa');
    $mastercard = $request->get('mastercard');
        $topcod = '1';


        if($tdocod == '1'){
          $senudoc = DB::tABLE('empresa')->select('FseEmpresa','FnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $numcomp =  $senudoc->FnuEmpresa+1;
          $sercomp =  $senudoc->FseEmpresa;
        }elseif ($tdocod =='2') {
          $senudoc = DB::tABLE('empresa')->select('BseEmpresa','BnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $numcomp =  $senudoc->BnuEmpresa+1;
          $sercomp =  $senudoc->BseEmpresa;
        }


      //Cabecera del comprobante
      //$numcomp= $request->get('numdoc');
      //  $sercomp= $request->get('serdoc');

      //Detalle del comprobante

        //Número de comprobante rellenado de ceros a la izquierda


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
        $cabecera->tipo_pago = $tippago;
        $cabecera->tipcambio = $camdoc;
        $cabecera->ccatvg =  $grav;
        $cabecera->ccaigv = $igv;
        $cabecera->ccaitv = $total;
    $cabecera->visa =  $visa;
        $cabecera->efectivo = $efectivo;
        $cabecera->mastercard = $mastercard;
        $cabecera->clicod = $cliente->clicod;

        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->IdUsuario = Auth::user()->IdEmpresa;
        $cabecera->IdEmpresa =  $rucemp;

        $empresa = Empresa::findOrFail($rucemp);
        if($tdocod=='1'){
          if( $empresa->FnuEmpresa == $numcomp){
              $modnumcomp = $numcomp+1;
          }else{
              $modnumcomp = $numcomp;
          }

          $empresa->FseEmpresa = $sercomp;
          $empresa->FnuEmpresa = $modnumcomp;
         // $empresa->update();

          $numdoc = $modnumcomp;
          $cabecera->serdoc= $sercomp;
          $cabecera->numdoc = $numdoc;
          //$cabecera->save();
        }elseif($tdocod=='2'){
          if( $empresa->FnuEmpresa == $numcomp){
              $modnumcomp = $numcomp+1;
          }else{
              $modnumcomp = $numcomp;
          }

          $empresa->BseEmpresa = $sercomp;
          $empresa->BnuEmpresa = $modnumcomp;
          //$empresa->update();

          $numdoc = $modnumcomp;
          $cabecera->serdoc= $sercomp;
          $cabecera->numdoc = $numdoc;
          //$cabecera->save();
        }
        
        $unidades = $request->get('unid');
        $proid = $request->get('proid');
        $detpro = $request->get('pronom');
        $vunit = $request->get('provun');


        //Generar el detalle del comprobante
        foreach($unidades as $index => $ume ) {

            $codpro = productos::findOrFail($proid[$index]);
            $codproducto = $codpro->procod;

            $detalle = new cpe_detalle;
            $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
            $detalle->umecod = $ume;
            $detalle->cdecan = $cantidades[$index];

           
            $detalle->procod = $codproducto;
            $detalle->IdProducto = $codpro->IdProducto;
            $detalle->cdepsu = "";
            $detalle->cdedes = $detpro[$index];
            $detalle->cdevun = $vunit[$index];
            $detalle->cdepuni = $puni[$index];

            $detalle->tigcod = '20';
            $vsub = $vunit[$index] * $cantidades[$index];
            $detalle->cdevve = $vtot[$index];
            $detalle->cdepve = $vsub;
            $vigv = $vtot[$index] - $vsub;
            $detalle->cdeigv = $vigv;
            $detalle->save();
            
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
                      $movimiento->save();

                  }
                 
            }
          
          

        }

        // Monto en letras
        $totalletras= MontoLetras::convertir(number_format($cabecera->ccaitv,'2','.',''),$moneda->monnom,'Centimos');


      
         if($tdocod !='13'){
         

            $result = self::generarcomprobante($codfact);
            $comp = cpe_cabecera::findOrFail($codfact);


            if($result =='success'){

              self::generarcomprobante($codfact);
              
              self::imprimir($codfact,$tdocod);

              return response()->json(['estado'=>$result,'mensaje'=>$comp->ccadessun,'codfact' =>$codfact,'tdocod'=>$tdocod]);

            }elseif($result == 'error'){

              return response()->json(['estado'=>$result,'mensaje'=>$comp->ccadessun]);
            }
          
          }else{

             $comp = cpe_cabecera::findOrFail($codfact);
             $comp->ccasunrescod='0';
             $comp->update();
            
             self::imprimir($codfact,$tdocod);
             return response()->json(['estado'=>'success','codfact' =>$codfact,'tdocod'=>$tdocod,'mensaje'=>'Comprobante Emitido']);
          }

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

    }

      $impresoras = DB::tABLE('configuracion_impresoras')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('descripcion','CAJA')->first();

    
    try { 
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
      }catch (\Exception $e) {

        
      }
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


     
     public function eliminar($id){

      $cabecera = cpe_cabecera::findOrFail($id);
      $cabecera->estado='ELIMINADO';
      $cabecera->update();

      return Redirect::to('/indexcotizaciones');

    }

    
    public function ordentrabajo($placa=0)
    {
        $rucemp = trim(Auth::user()->IdEmpresa);
        $categorias = DB::tABLE('categorias')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();
        $comprobante = DB::tABLE('tipo_documento')->get();
        $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();
       
        $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();

      
        $combustible = DB::tABLE('combustible')->get();
        $marcas = DB::tABLE('marcas')->get();
        $modelos = DB::tABLE('modelos')->get();
        $tecnicos = DB::tABLE('tecnicos')->get();
        $clientes = DB::tABLE('cliente')->get();
        $condiciones = DB::tABLE('credito_dias')->get();


        $monedas = DB::tABLE('moneda')->get();

         $bus_alm = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();
        
        $productos = DB::tABLE('productos')
        ->select('umenom','pro_rel','costo','mar_nom','procod','pronom','propun','productos.IdProducto','productos.umecod','promocion','color','imagenproducto','precio2','precio3',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND id_almacen='".$bus_alm->id_almacen."' AND pro.id_empresa_negocio='".$bus_alm->id_empresa_negocio."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE pro.pro_rel =  productos.IdProducto) as cont_pre"))
        ->leftjoin('unidad_medida','unidad_medida.umecod','productos.umecod')
            ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
          //  ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
          ->leftjoin('marcas','marcas.mar_id','productos.marca')
        /*->where(function ($query) use($search){
                $query->where('pronom','like','%'.$search.'%')
          ->orwhere('procod',$search);
          })*/
            ->where('tipo','1')
            ->where('promocion','!=','2')
           // ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
           // ->where('id_almacen',$almacen->id_almacen)
            ->groupby('productos.IdProducto')
            ->orderby('productos.pronom')
            ->orderby('productos.umecod')
            ->get();



        if($placa==0){
            return view('empresas.puntosventas.ordentrabajonuevo',compact('almacenes','categorias','comprobante','tipodocumento','igv','unidades','unidades','productos','marcas','modelos','clientes','combustible','tecnicos','condiciones','monedas'));
        }else{

             $vehiculos=tipos_vehiculos::leftjoin('cliente','cliente.clicod','tipos_vehiculos.clicod')
                 ->leftjoin('tipo_documento_identidad','tipo_documento_identidad.tdicod','cliente.tdicod')
                 ->leftjoin('marcas','marcas.mar_id','tipos_vehiculos.mar_id')
                 ->leftjoin('modelos','modelos.mod_id','tipos_vehiculos.mod_id')
                 ->leftjoin('combustible','combustible.comb_id','tipos_vehiculos.comb_id')
                 ->where('id_tipo_vehiculo',$placa)
                 ->first();
             return view('empresas.puntosventas.ordentrabajo',compact('almacenes','categorias','comprobante','tipodocumento','igv','unidades','unidades','productos','marcas','modelos','clientes','combustible','tecnicos','condiciones','vehiculos','monedas'));
        }
     

       
    }

     public function editarordentrabajo($id)
    {
        $rucemp = trim(Auth::user()->IdEmpresa);
        $categorias = DB::tABLE('categorias')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();
        $comprobante = DB::tABLE('tipo_documento')->get();
        $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();
         $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();
        

        $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();

        $combustible = DB::tABLE('combustible')->get();
        $marcas = DB::tABLE('marcas')->get();
        $modelos = DB::tABLE('modelos')->get();
        $tecnicos = DB::tABLE('tecnicos')->get();
        $clientes = DB::tABLE('cliente')->get();
        $condiciones = DB::tABLE('credito_dias')->get();
        $monedas = DB::tABLE('moneda')->get();

         $bus_alm = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();
        
        $productos = DB::tABLE('productos')
        ->select('umenom','pro_rel','costo','mar_nom','procod','pronom','propun','productos.IdProducto','productos.umecod','promocion','color','imagenproducto','precio2','precio3',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND id_almacen='".$bus_alm->id_almacen."' AND pro.id_empresa_negocio='".$bus_alm->id_empresa_negocio."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE pro.pro_rel =  productos.IdProducto) as cont_pre"))
        ->leftjoin('unidad_medida','unidad_medida.umecod','productos.umecod')
            ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
          //  ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
          ->leftjoin('marcas','marcas.mar_id','productos.marca')
        /*->where(function ($query) use($search){
                $query->where('pronom','like','%'.$search.'%')
          ->orwhere('procod',$search);
          })*/
            ->where('tipo','1')
            ->where('promocion','!=','2')
           // ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
           // ->where('id_almacen',$almacen->id_almacen)
            ->groupby('productos.IdProducto')
            ->orderby('productos.pronom')
            ->orderby('productos.umecod')
            ->get();


        $cotizacion = DB::tABLE('cpe_cabecera')
        ->join('cliente','cpe_cabecera.clicod','cliente.clicod')
        ->where('IdCpe_cabecera',$id)
        ->first();

        $detalles = DB::tABLE('cpe_detalle')
        ->leftjoin('productos','cpe_detalle.IdProducto','productos.IdProducto')
        ->leftjoin('unidad_medida','unidad_medida.umecod','cpe_detalle.umecod')
        ->where('IdCpe_cabecera',$id)
        ->get();
     
         $vehiculos=tipos_vehiculos::leftjoin('cliente','cliente.clicod','tipos_vehiculos.clicod')
         ->leftjoin('tipo_documento_identidad','tipo_documento_identidad.tdicod','cliente.tdicod')
         ->leftjoin('marcas','marcas.mar_id','tipos_vehiculos.mar_id')
         ->leftjoin('modelos','modelos.mod_id','tipos_vehiculos.mod_id')
         ->leftjoin('combustible','combustible.comb_id','tipos_vehiculos.comb_id')
         ->where('placa',$cotizacion->placa)
         ->first();

     
             return view('empresas.puntosventas.editarot',compact('categorias','comprobante','tipodocumento','igv','unidades','unidades','productos','marcas','modelos','clientes','combustible','tecnicos','condiciones','vehiculos','monedas','cotizacion','detalles','id','almacenes'));
        
     

       
    }

       public function editarordenpedido($id)
    {
        $rucemp = trim(Auth::user()->IdEmpresa);
        $categorias = DB::tABLE('categorias')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();
        $comprobante = DB::tABLE('tipo_documento')->get();
        $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();
         $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();
      
        $combustible = DB::tABLE('combustible')->get();
        $marcas = DB::tABLE('marcas')->get();
        $modelos = DB::tABLE('modelos')->get();
        $tecnicos = DB::tABLE('tecnicos')->get();
        $clientes = DB::tABLE('cliente')->get();
        $condiciones = DB::tABLE('credito_dias')->get();
        $monedas = DB::tABLE('moneda')->get();

         $bus_alm = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();
        
        $productos = DB::tABLE('productos')
        ->select('umenom','pro_rel','costo','mar_nom','procod','pronom','propun','productos.IdProducto','productos.umecod','promocion','color','imagenproducto','precio2','precio3',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND id_almacen='".$bus_alm->id_almacen."' AND pro.id_empresa_negocio='".$bus_alm->id_empresa_negocio."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE pro.pro_rel =  productos.IdProducto) as cont_pre"))
        ->leftjoin('unidad_medida','unidad_medida.umecod','productos.umecod')
            ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
          //  ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
          ->leftjoin('marcas','marcas.mar_id','productos.marca')
        /*->where(function ($query) use($search){
                $query->where('pronom','like','%'.$search.'%')
          ->orwhere('procod',$search);
          })*/
            ->where('tipo','1')
            ->where('promocion','!=','2')
           // ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
           // ->where('id_almacen',$almacen->id_almacen)
            ->groupby('productos.IdProducto')
            ->orderby('productos.pronom')
            ->orderby('productos.umecod')
            ->get();


        $cotizacion = DB::tABLE('cpe_cabecera')
        ->join('cliente','cpe_cabecera.clicod','cliente.clicod')
        ->where('IdCpe_cabecera',$id)
        ->first();

        $detalles = DB::tABLE('cpe_detalle')
        ->leftjoin('productos','cpe_detalle.IdProducto','productos.IdProducto')
        ->leftjoin('unidad_medida','unidad_medida.umecod','cpe_detalle.umecod')
        ->where('IdCpe_cabecera',$id)
        ->get();
     
         $vehiculos=tipos_vehiculos::leftjoin('cliente','cliente.clicod','tipos_vehiculos.clicod')
         ->leftjoin('tipo_documento_identidad','tipo_documento_identidad.tdicod','cliente.tdicod')
         ->leftjoin('marcas','marcas.mar_id','tipos_vehiculos.mar_id')
         ->leftjoin('modelos','modelos.mod_id','tipos_vehiculos.mod_id')
         ->leftjoin('combustible','combustible.comb_id','tipos_vehiculos.comb_id')
         ->where('placa',$cotizacion->placa)
         ->first();

     
             return view('empresas.puntosventas.editarop',compact('categorias','comprobante','tipodocumento','igv','unidades','unidades','productos','marcas','modelos','clientes','combustible','tecnicos','condiciones','vehiculos','monedas','cotizacion','detalles','id'));
        
     

       
    }


    public function editarcotizacion($id)
    {
        $rucemp = trim(Auth::user()->IdEmpresa);
        $categorias = DB::tABLE('categorias')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();
        $comprobante = DB::tABLE('tipo_documento')->get();
        $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();
         $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();
      
        $combustible = DB::tABLE('combustible')->get();
        $marcas = DB::tABLE('marcas')->get();
        $modelos = DB::tABLE('modelos')->get();
        $tecnicos = DB::tABLE('tecnicos')->get();
        $clientes = DB::tABLE('cliente')->get();
        $condiciones = DB::tABLE('credito_dias')->get();
        $monedas = DB::tABLE('moneda')->get();

         $bus_alm = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();
        
        $productos = DB::tABLE('productos')
        ->select('umenom','pro_rel','costo','mar_nom','procod','pronom','propun','productos.IdProducto','productos.umecod','promocion','color','imagenproducto','precio2','precio3',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND id_almacen='".$bus_alm->id_almacen."' AND pro.id_empresa_negocio='".$bus_alm->id_empresa_negocio."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE pro.pro_rel =  productos.IdProducto) as cont_pre"))
        ->leftjoin('unidad_medida','unidad_medida.umecod','productos.umecod')
            ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
          //  ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
          ->leftjoin('marcas','marcas.mar_id','productos.marca')
        /*->where(function ($query) use($search){
                $query->where('pronom','like','%'.$search.'%')
          ->orwhere('procod',$search);
          })*/
            ->where('tipo','1')
            ->where('promocion','!=','2')
           // ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
           // ->where('id_almacen',$almacen->id_almacen)
            ->groupby('productos.IdProducto')
            ->orderby('productos.pronom')
            ->orderby('productos.umecod')
            ->get();


        $cotizacion = DB::tABLE('cpe_cabecera')
        ->join('cliente','cpe_cabecera.clicod','cliente.clicod')
        ->where('IdCpe_cabecera',$id)
        ->first();

        $detalles = DB::tABLE('cpe_detalle')
        ->leftjoin('productos','cpe_detalle.IdProducto','productos.IdProducto')
        ->leftjoin('unidad_medida','unidad_medida.umecod','cpe_detalle.umecod')
        ->where('IdCpe_cabecera',$id)
        ->get();
     
         $vehiculos=tipos_vehiculos::leftjoin('cliente','cliente.clicod','tipos_vehiculos.clicod')
         ->leftjoin('tipo_documento_identidad','tipo_documento_identidad.tdicod','cliente.tdicod')
         ->leftjoin('marcas','marcas.mar_id','tipos_vehiculos.mar_id')
         ->leftjoin('modelos','modelos.mod_id','tipos_vehiculos.mod_id')
         ->leftjoin('combustible','combustible.comb_id','tipos_vehiculos.comb_id')
         ->where('placa',$cotizacion->placa)
         ->first();

     
             return view('empresas.puntosventas.editarcotizacion',compact('categorias','comprobante','tipodocumento','igv','unidades','unidades','productos','marcas','modelos','clientes','combustible','tecnicos','condiciones','vehiculos','monedas','cotizacion','detalles','id'));
        
     

       
    }

    public function generarot($id)
    {
         $rucemp = trim(Auth::user()->IdEmpresa);
        $categorias = DB::tABLE('categorias')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();
        $comprobante = DB::tABLE('tipo_documento')->get();
        $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();
         $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();
      
        $combustible = DB::tABLE('combustible')->get();
        $marcas = DB::tABLE('marcas')->get();
        $modelos = DB::tABLE('modelos')->get();
        $tecnicos = DB::tABLE('tecnicos')->get();
        $clientes = DB::tABLE('cliente')->get();
        $condiciones = DB::tABLE('credito_dias')->get();
        $monedas = DB::tABLE('moneda')->get();

         $bus_alm = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();
        
        $productos = DB::tABLE('productos')
        ->select('umenom','pro_rel','costo','mar_nom','procod','pronom','propun','productos.IdProducto','productos.umecod','promocion','color','imagenproducto','precio2','precio3',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND id_almacen='".$bus_alm->id_almacen."' AND pro.id_empresa_negocio='".$bus_alm->id_empresa_negocio."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE pro.pro_rel =  productos.IdProducto) as cont_pre"))
        ->leftjoin('unidad_medida','unidad_medida.umecod','productos.umecod')
            ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
          //  ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
          ->leftjoin('marcas','marcas.mar_id','productos.marca')
        /*->where(function ($query) use($search){
                $query->where('pronom','like','%'.$search.'%')
          ->orwhere('procod',$search);
          })*/
            ->where('tipo','1')
            ->where('promocion','!=','2')
           // ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
           // ->where('id_almacen',$almacen->id_almacen)
            ->groupby('productos.IdProducto')
            ->orderby('productos.pronom')
            ->orderby('productos.umecod')
            ->get();


        $cotizacion = DB::tABLE('cpe_cabecera')
        ->join('cliente','cpe_cabecera.clicod','cliente.clicod')
        ->where('IdCpe_cabecera',$id)
        ->first();

        $detalles = DB::tABLE('cpe_detalle')
        ->leftjoin('productos','cpe_detalle.IdProducto','productos.IdProducto')
        ->leftjoin('unidad_medida','unidad_medida.umecod','cpe_detalle.umecod')
        ->where('IdCpe_cabecera',$id)
        ->get();
     
         $vehiculos=tipos_vehiculos::leftjoin('cliente','cliente.clicod','tipos_vehiculos.clicod')
         ->leftjoin('tipo_documento_identidad','tipo_documento_identidad.tdicod','cliente.tdicod')
         ->leftjoin('marcas','marcas.mar_id','tipos_vehiculos.mar_id')
         ->leftjoin('modelos','modelos.mod_id','tipos_vehiculos.mod_id')
         ->leftjoin('combustible','combustible.comb_id','tipos_vehiculos.comb_id')
         ->where('placa',$cotizacion->placa)
         ->first();

     
             return view('empresas.puntosventas.generarot',compact('categorias','comprobante','tipodocumento','igv','unidades','unidades','productos','marcas','modelos','clientes','combustible','tecnicos','condiciones','vehiculos','monedas','cotizacion','detalles','id'));
   


       
    }

     public function cobrar($id)
    {
        
        $rucemp = trim(Auth::user()->IdEmpresa);

        $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

        $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();

          $senudoc = DB::tABLE('empresa_negocios')
                    ->select('serieguia','numeroguia')
                    ->where('IdEmpresa','=',$rucemp)
                    ->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)
                    ->first(); 

        $motivos = DB::tABLE('motivo_traslado')
        ->orderBy('motivo','asc')->get();

             $ubigeos = DB::tABLE('cat_ubigeo')->get();


        $modalidades = DB::tABLE('modalidad_traslado')
        ->orderBy('modalidad','asc')->get();

        $creditos = DB::tABLE('credito_dias')->get();

        $vendedores = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        //->where('role_id','5')
        ->get();

         $docidentidad = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

            $comprobantes = DB::tABLE('tipo_documento')->get();
          $gastos = DB::tABLE('tipo_gastos')->get();
        $categorias = DB::tABLE('categorias')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();
        $comprobante = DB::tABLE('tipo_documento')->get();
         $documentos = DB::tABLE('tipo_documento_identidad')->get();
        $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();
         $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();
      
        $combustible = DB::tABLE('combustible')->get();
        $marcas = DB::tABLE('marcas')->get();
        $modelos = DB::tABLE('modelos')->get();
        $tecnicos = DB::tABLE('tecnicos')->get();
        $clientes = DB::tABLE('cliente')->get();
        $condiciones = DB::tABLE('credito_dias')->get();
        $monedas = DB::tABLE('moneda')->get();

         $mediospagos = DB::tABLE('medios_pagos')->get();

           // consultar tipo de operaciones
        $operaciones = DB::tABLE('tipo_operacion')->where('topest','=','Activo')
        ->orderBy('topcod','asc')->get();

         $bus_alm = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();
        
        $productos = DB::tABLE('productos')
        ->select('precio','umenom','pro_rel','costo','mar_nom','procod','pronom','propun','productos.IdProducto','productos.umecod','promocion','color','imagenproducto','precio2','precio3',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND id_almacen='".$bus_alm->id_almacen."' AND pro.id_empresa_negocio='".$bus_alm->id_empresa_negocio."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE pro.pro_rel =  productos.IdProducto) as cont_pre"))
        ->leftjoin('unidad_medida','unidad_medida.umecod','productos.umecod')
            ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
          //  ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
          ->leftjoin('marcas','marcas.mar_id','productos.marca')
        /*->where(function ($query) use($search){
                $query->where('pronom','like','%'.$search.'%')
          ->orwhere('procod',$search);
          })*/
            ->where('tipo','1')
            ->where('promocion','!=','2')
           // ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
           // ->where('id_almacen',$almacen->id_almacen)
            ->groupby('productos.IdProducto')
            ->orderby('productos.pronom')
            ->orderby('productos.umecod')
            ->get();


        $cotizacion = DB::tABLE('cpe_cabecera')
        ->join('cliente','cpe_cabecera.clicod','cliente.clicod')
        ->where('IdCpe_cabecera',$id)
        ->first();

        $detalles = DB::tABLE('cpe_detalle')
        ->leftjoin('productos','cpe_detalle.IdProducto','productos.IdProducto')
        ->leftjoin('unidad_medida','unidad_medida.umecod','cpe_detalle.umecod')
        ->where('IdCpe_cabecera',$id)
        ->get();
     
         $vehiculos=tipos_vehiculos::leftjoin('cliente','cliente.clicod','tipos_vehiculos.clicod')
         ->leftjoin('tipo_documento_identidad','tipo_documento_identidad.tdicod','cliente.tdicod')
         ->leftjoin('marcas','marcas.mar_id','tipos_vehiculos.mar_id')
         ->leftjoin('modelos','modelos.mod_id','tipos_vehiculos.mod_id')
         ->leftjoin('combustible','combustible.comb_id','tipos_vehiculos.comb_id')
         ->where('placa',$cotizacion->placa)
         ->first();

     
          //   return view('empresas.puntosventas.cobrar',compact('categorias','comprobante','tipodocumento','igv','unidades','unidades','productos','marcas','modelos','clientes','combustible','tecnicos','condiciones','vehiculos','monedas','cotizacion','detalles','id','operaciones','mediospagos'));

  return view('empresas.puntosventas.cobrar_orden',compact('comprobantes','ubigeos','docidentidad','senudoc','motivos','modalidades','documentos','categorias','comprobante','tipodocumento','igv','unidades','unidades','productos','marcas','modelos','clientes','combustible','tecnicos','condiciones','vehiculos','monedas','cotizacion','detalles','id','operaciones','mediospagos','gastos','datos','almacenes','vendedores','creditos'));
   

       
    }



    public function registrarcotizacion(Request $request){


          $senudoc = DB::tABLE('empresa_negocios')->select('sercot','numcot')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

          $numcomp =  $senudoc->numcot+1;
          $sercomp =  $senudoc->sercot;
       

       $buscar =  DB::tABLE('cliente')->where('clinum',$request->get('clinum'))->first();

       $buscarplaca = DB::tABLE('tipos_vehiculos')->where('placa',$request->get('placa'))->first();

        if(empty($buscar)){

            $cliente = new Cliente;
            $cliente->rucemp = trim(Auth::user()->IdEmpresa);
            $cliente->tdicod = $request->get('tdicod');
            $cliente->clinum = $request->get('clinum');
            $cliente->clidir = $request->get('clidir');
            $cliente->clinom = $request->get('clinom');
            $cliente->clicor = $request->get('clicor');
            $cliente->telefono = $request->get('telefono');
            $cliente->clicon = $request->get('clicon');
            $cliente->clicontel = $request->get('clicontel');
            $cliente->tar_id = $request->get('tarifa');
            $cliente->save();

        }else{
            $cliente = Cliente::findOrFail($buscar->clicod);
            $cliente->rucemp = trim(Auth::user()->IdEmpresa);
            $cliente->tdicod = $request->get('tdicod');
            $cliente->clinum = $request->get('clinum');
            $cliente->clidir = $request->get('clidir');
            $cliente->clinom = $request->get('clinom');
            $cliente->clicor = $request->get('clicor');
            $cliente->telefono = $request->get('telefono');
            $cliente->clicon = $request->get('clicon');
            $cliente->clicontel = $request->get('clicontel');
            $cliente->tar_id = $request->get('tarifa');
            $cliente->update();
        }

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
       


        $cabecera =  new cpe_cabecera;
        $cabecera->tdocod ='80';
        $cabecera->fechacot = $request->get('fecha');
        $cabecera->tdicod = $request->get('tdicod');
        $cabecera->ccandi = $request->get('clinum');
        $cabecera->grua = $request->get('grua');
        $cabecera->ccanom = $request->get('clinom');
        $cabecera->moncod = $request->get('moncod');

        $cabecera->ccatvg = $request->get('total');
         $cabecera->grua = $request->get('grua');
        $cabecera->ccaigv = $request->get('total') - $request->get('total');
        $cabecera->ccaitv = $request->get('total');
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->tipcambio = $request->get('tipcam');
        
         $empresanegocio = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);
     
                 
          if( $empresanegocio->numcot == $numcomp){
              $modnumcomp = $numcomp+1;
          }else{
              $modnumcomp = $numcomp;
          }

          $empresanegocio->sercot = $sercomp;
          $empresanegocio->numcot = $modnumcomp;
          

          $numdoc = $modnumcomp;
          
           $cabecera->serdoc = $sercomp;
       
          $cabecera->numdoc = $numdoc;
       
        
        $empresanegocio->update();


        $cabecera->clicod = $cliente->clicod;
        $cabecera->direccion = $request->get('clidir');
        $cabecera->estado = 'REGISTRADO';
        $cabecera->turno = Auth::user()->id_turno;
        $cabecera->topcod = '0101';
        $cabecera->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $cabecera->comb_id = $request->get('combustible');
        $cabecera->kilometros = $request->get('kilometros');
        $cabecera->observaciones = $request->get('observaciones');
        $cabecera->placa = $request->get('placa');
        $cabecera->cilindrada = $request->get('cilindrada');
        $cabecera->tec_id = $request->get('tecnico');
        $cabecera->bastidor = $request->get('bastidor');
        $cabecera->fecinspeccion = $request->get('fecinspeccion');
        $cabecera->fecsoat = $request->get('fecsoat');
        $cabecera->fecrevision = $request->get('fecrevision');
        $cabecera->color = $request->get('color');
        $cabecera->encargado = $request->get('encargado');
        $cabecera->encargadotel = $request->get('encargadotel');
        $cabecera->cre_dia_id = $request->get('condicionpago');
        $cabecera->IdEmpresa = Auth::user()->IdEmpresa;
        $cabecera->save();

   
        $productos = $request->get('IdProducto');
        $cantidad = $request->get('cantidad');
        $unidad = $request->get('unidad');
        $precio = $request->get('precio');
        $totalitem = $request->get('totalitem');
        $descuento = $request->get('descuento');
        
        foreach ($productos as $index => $producto) {
           
            $buscar = productos::findOrFail($producto);
            $detalle = new cpe_detalle;
            $detalle->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
            $detalle->IdProducto = $producto;
            $detalle->cdedes = $buscar->pronom;
            $detalle->umecod = $buscar->umecod;
            $detalle->cdecan = $cantidad[$index];
            $detalle->cdepuni = $precio[$index];
            $detalle->cdevun = $precio[$index];
            $detalle->cdepve = $totalitem[$index];
            $detalle->cdevve = $totalitem[$index];
            $detalle->tigcod = $buscar->tigcod;
            $detalle->cdeigv = $totalitem[$index]-($totalitem[$index]);
            $detalle->costo =  $buscar->costo;
            $detalle->por_des = $descuento[$index];
            $detalle->desc_mon = $precio[$index]*($descuento[$index]/100);
            $detalle->save();


        }


            $cabecera->generarpdfgeneral($cabecera->IdCpe_cabecera);
            return response()->json(['registrado']);
    }

     public function actualizarcotizacion(Request $request){

         $cotid = $request->get('cotid');



       $buscar =  DB::tABLE('cliente')->where('clinum',$request->get('clinum'))->first();

       $buscarplaca = DB::tABLE('tipos_vehiculos')->where('placa',$request->get('placa'))->first();

        if(empty($buscar)){

            $cliente = new Cliente;
            $cliente->rucemp = trim(Auth::user()->IdEmpresa);
            $cliente->tdicod = $request->get('tdicod');
            $cliente->clinum = $request->get('clinum');
            $cliente->clidir = $request->get('clidir');
            $cliente->clinom = $request->get('clinom');
            $cliente->clicor = $request->get('clicor');
            $cliente->telefono = $request->get('telefono');
            $cliente->clicon = $request->get('clicon');
            $cliente->clicontel = $request->get('clicontel');
            $cliente->tar_id = $request->get('tarifa');
            $cliente->save();

        }else{
            $cliente = Cliente::findOrFail($buscar->clicod);
            $cliente->rucemp = trim(Auth::user()->IdEmpresa);
            $cliente->tdicod = $request->get('tdicod');
            $cliente->clinum = $request->get('clinum');
            $cliente->clidir = $request->get('clidir');
            $cliente->clinom = $request->get('clinom');
            $cliente->clicor = $request->get('clicor');
            $cliente->telefono = $request->get('telefono');
            $cliente->clicon = $request->get('clicon');
            $cliente->clicontel = $request->get('clicontel');
            $cliente->tar_id = $request->get('tarifa');
            $cliente->update();
        }

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
       


        $cabecera =  cpe_cabecera::findOrFail($cotid);
        $cabecera->tdocod ='80';
        $cabecera->fechacot = $request->get('fecha');
        $cabecera->tdicod = $request->get('tdicod');
        $cabecera->ccandi = $request->get('clinum');
        $cabecera->ccanom = $request->get('clinom');
        $cabecera->moncod = $request->get('moncod');
        $cabecera->ccatvg = $request->get('total');
         $cabecera->grua = $request->get('grua');
        $cabecera->ccaigv = $request->get('total') - $request->get('total');
        $cabecera->ccaitv = $request->get('total');
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->tipcambio = $request->get('tipcam');
        $cabecera->clicod = $cliente->clicod;
        $cabecera->direccion = $request->get('clidir');
        $cabecera->estado = 'REGISTRADO';
        $cabecera->turno = Auth::user()->id_turno;
        $cabecera->topcod = '0101';
        $cabecera->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $cabecera->comb_id = $request->get('combustible');
        $cabecera->kilometros = $request->get('kilometros');
        $cabecera->observaciones = $request->get('observaciones');
        $cabecera->placa = $request->get('placa');
        $cabecera->cilindrada = $request->get('cilindrada');
        $cabecera->tec_id = $request->get('tecnico');
        $cabecera->bastidor = $request->get('bastidor');
        $cabecera->fecinspeccion = $request->get('fecinspeccion');
        $cabecera->fecsoat = $request->get('fecsoat');
        $cabecera->fecrevision = $request->get('fecrevision');
        $cabecera->color = $request->get('color');
        $cabecera->encargado = $request->get('encargado');
        $cabecera->encargadotel = $request->get('encargadotel');
        $cabecera->cre_dia_id = $request->get('condicionpago');
        $cabecera->update();

        $productos = $request->get('IdProducto');
        $cantidad = $request->get('cantidad');
        $unidad = $request->get('unidad');
        $precio = $request->get('precio');
        $totalitem = $request->get('totalitem');
        $descuento = $request->get('descuento');
        
        cpe_detalle::where('IdCpe_cabecera',$cotid)->delete();

        foreach ($productos as $index => $producto) {
            
            $buscar = productos::findOrFail($producto);
            $detalle = new cpe_detalle;
            $detalle->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
            $detalle->IdProducto = $producto;
            $detalle->cdedes = $buscar->pronom;
            $detalle->cdecan = $cantidad[$index];
            $detalle->umecod = $buscar->umecod;
            $detalle->cdepuni = $precio[$index];
            $detalle->cdevun = $precio[$index];
            $detalle->cdepve = $totalitem[$index];
            $detalle->cdevve = $totalitem[$index];
            $detalle->tigcod = $buscar->tigcod;
            $detalle->cdeigv = $totalitem[$index]-($totalitem[$index]);
            $detalle->costo =  $buscar->costo;
            $detalle->por_des = $descuento[$index];
             $detalle->desc_mon = $precio[$index]*($descuento[$index]/100);
            $detalle->save();


        }   

            $cabecera->generarpdfgeneral($cabecera->IdCpe_cabecera);

            return response()->json(['registrado']);
    }

      public function actualizarordentrabajo(Request $request){

         $cotid = $request->get('cotid');

            $id_almacen = $request->get('id_almacen');

         $bus_alm= DB::tABLE('almacenes')->where('id_almacen',$id_almacen)->first();

        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$bus_alm->id_empresa_negocio)->first();


       $buscar =  DB::tABLE('cliente')->where('clinum',$request->get('clinum'))->first();

       $buscarplaca = DB::tABLE('tipos_vehiculos')->where('placa',$request->get('placa'))->first();

        if(empty($buscar)){

            $cliente = new Cliente;
            $cliente->rucemp = trim(Auth::user()->IdEmpresa);
            $cliente->tdicod = $request->get('tdicod');
            $cliente->clinum = $request->get('clinum');
            $cliente->clidir = $request->get('clidir');
            $cliente->clinom = $request->get('clinom');
            $cliente->clicor = $request->get('clicor');
            $cliente->telefono = $request->get('telefono');
            $cliente->clicon = $request->get('clicon');
            $cliente->clicontel = $request->get('clicontel');
            $cliente->tar_id = $request->get('tarifa');
            $cliente->save();

        }else{
            $cliente = Cliente::findOrFail($buscar->clicod);
            $cliente->rucemp = trim(Auth::user()->IdEmpresa);
            $cliente->tdicod = $request->get('tdicod');
            $cliente->clinum = $request->get('clinum');
            $cliente->clidir = $request->get('clidir');
            $cliente->clinom = $request->get('clinom');
            $cliente->clicor = $request->get('clicor');
            $cliente->telefono = $request->get('telefono');
            $cliente->clicon = $request->get('clicon');
            $cliente->clicontel = $request->get('clicontel');
            $cliente->tar_id = $request->get('tarifa');
            $cliente->update();
        }

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
       


        $cabecera =  cpe_cabecera::findOrFail($cotid);
        $cabecera->fechacot = $request->get('fecha');
        $cabecera->tdicod = $request->get('tdicod');
        $cabecera->ccandi = $request->get('clinum');
        $cabecera->ccanom = $request->get('clinom');
        $cabecera->moncod = $request->get('moncod');
        $cabecera->ccatvg = $request->get('total');
         $cabecera->grua = $request->get('grua');
        $cabecera->ccaigv = $request->get('total') - $request->get('total');
        $cabecera->ccaitv = $request->get('total');
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->tipcambio = $request->get('tipcam');
        $cabecera->clicod = $cliente->clicod;
        $cabecera->direccion = $request->get('clidir');
        $cabecera->estado = 'REGISTRADO';
        $cabecera->turno = Auth::user()->id_turno;
        $cabecera->topcod = '0101';
        $cabecera->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $cabecera->comb_id = $request->get('combustible');
        $cabecera->kilometros = $request->get('kilometros');
        $cabecera->observaciones = $request->get('observaciones');
        $cabecera->placa = $request->get('placa');
        $cabecera->cilindrada = $request->get('cilindrada');
        $cabecera->tec_id = $request->get('tecnico');
        $cabecera->bastidor = $request->get('bastidor');
        $cabecera->fecinspeccion = $request->get('fecinspeccion');
        $cabecera->fecsoat = $request->get('fecsoat');
        $cabecera->fecrevision = $request->get('fecrevision');
        $cabecera->color = $request->get('color');
        $cabecera->encargado = $request->get('encargado');
        $cabecera->encargadotel = $request->get('encargadotel');
        $cabecera->cre_dia_id = $request->get('condicionpago');
        $cabecera->update();

        $productos = $request->get('IdProducto');
        $cantidad = $request->get('cantidad');
        $unidad = $request->get('unidad');
        $precio = $request->get('precio');
        $totalitem = $request->get('totalitem');
        $descuento = $request->get('descuento');
        
        cpe_detalle::where('IdCpe_cabecera',$cotid)->delete();

         $unidades = $request->get('unid');
        $proid = $request->get('proid');
        $detpro = $request->get('pronom');
        $vunit = $request->get('provun');
        $puni = $request->get('propun');
        $vtot = $request->get('itemtotal');
        $cantidades = $request->get('cant');
        $val_uni = $request->get('precio');

        foreach ($proid as $index => $producto) {

            $codpro = productos::findOrFail($producto);


            if(empty($codpro->pro_rel)){

                $id_prod = $codpro->IdProducto;

            }else{
              
                $id_prod = $codpro->pro_rel;

            }



            $detalle = new cpe_detalle;
            $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
            $detalle->umecod = $codpro->umecod;
            $detalle->cpe_det_factor = $codpro->factor;
            $detalle->cdecan = $cantidades[$index];
            $detalle->procod = $codpro->proccod;
            $detalle->IdProducto = $codpro->IdProducto;
            $detalle->IdProducto_rel = $id_prod;
            $detalle->cdepsu = "";
            $detalle->cdedes = $detpro[$index];
            $detalle->costo = $codpro->costo_total;
            $detalle->tigcod = $codpro->tigcod;
            $detalle->icbper = $codpro->icbper;
            $detalle->id_almacen_pro = $id_almacen;
           

            if($codpro->tigcod =='10'){

              $preciouni = $puni[$index]-$descuento[$index];;
              $valoruni = ($puni[$index])-($descuento[$index]);
              $valorunitario = $val_uni[$index];

              $valorsubtotal = $vtot[$index];
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
            $detalle->fecha_venta = $request->get('fecha');
            $detalle->flete = $codpro->flete;
            $detalle->save();


        }

            $cabecera->generarpdfgeneral($cabecera->IdCpe_cabecera);

            return response()->json(['registrado']);
    }

     public function actualizarordenpedido(Request $request){

         $cotid = $request->get('cotid');



       $buscar =  DB::tABLE('cliente')->where('clinum',$request->get('clinum'))->first();

       $buscarplaca = DB::tABLE('tipos_vehiculos')->where('placa',$request->get('placa'))->first();

        if(empty($buscar)){

            $cliente = new Cliente;
            $cliente->rucemp = trim(Auth::user()->IdEmpresa);
            $cliente->tdicod = $request->get('tdicod');
            $cliente->clinum = $request->get('clinum');
            $cliente->clidir = $request->get('clidir');
            $cliente->clinom = $request->get('clinom');
            $cliente->clicor = $request->get('clicor');
            $cliente->telefono = $request->get('telefono');
            $cliente->clicon = $request->get('clicon');
            $cliente->clicontel = $request->get('clicontel');
            $cliente->tar_id = $request->get('tarifa');
            $cliente->save();

        }else{
            $cliente = Cliente::findOrFail($buscar->clicod);
            $cliente->rucemp = trim(Auth::user()->IdEmpresa);
            $cliente->tdicod = $request->get('tdicod');
            $cliente->clinum = $request->get('clinum');
            $cliente->clidir = $request->get('clidir');
            $cliente->clinom = $request->get('clinom');
            $cliente->clicor = $request->get('clicor');
            $cliente->telefono = $request->get('telefono');
            $cliente->clicon = $request->get('clicon');
            $cliente->clicontel = $request->get('clicontel');
            $cliente->tar_id = $request->get('tarifa');
            $cliente->update();
        }

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
       


        $cabecera =  cpe_cabecera::findOrFail($cotid);
        $cabecera->fechacot = $request->get('fecha');
        $cabecera->tdicod = $request->get('tdicod');
        $cabecera->ccandi = $request->get('clinum');
        $cabecera->ccanom = $request->get('clinom');
        $cabecera->moncod = $request->get('moncod');
        $cabecera->ccatvg = $request->get('total');
         $cabecera->grua = $request->get('grua');
        $cabecera->ccaigv = $request->get('total') - $request->get('total');
        $cabecera->ccaitv = $request->get('total');
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->tipcambio = $request->get('tipcam');
        $cabecera->clicod = $cliente->clicod;
        $cabecera->direccion = $request->get('clidir');
        $cabecera->estado = 'REGISTRADO';
        $cabecera->turno = Auth::user()->id_turno;
        $cabecera->topcod = '0101';
        $cabecera->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $cabecera->comb_id = $request->get('combustible');
        $cabecera->kilometros = $request->get('kilometros');
        $cabecera->observaciones = $request->get('observaciones');
        $cabecera->placa = $request->get('placa');
        $cabecera->cilindrada = $request->get('cilindrada');
        $cabecera->tec_id = $request->get('tecnico');
        $cabecera->bastidor = $request->get('bastidor');
        $cabecera->fecinspeccion = $request->get('fecinspeccion');
        $cabecera->fecsoat = $request->get('fecsoat');
        $cabecera->fecrevision = $request->get('fecrevision');
        $cabecera->color = $request->get('color');
        $cabecera->encargado = $request->get('encargado');
        $cabecera->encargadotel = $request->get('encargadotel');
        $cabecera->cre_dia_id = $request->get('condicionpago');
        $cabecera->update();

        $productos = $request->get('IdProducto');
        $cantidad = $request->get('cantidad');
        $unidad = $request->get('unidad');
        $precio = $request->get('precio');
        $totalitem = $request->get('totalitem');
        $descuento = $request->get('descuento');
        
        cpe_detalle::where('IdCpe_cabecera',$cotid)->delete();

        foreach ($productos as $index => $producto) {
            
            $buscar = productos::findOrFail($producto);
            $detalle = new cpe_detalle;
            $detalle->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
            $detalle->IdProducto = $producto;
            $detalle->cdedes = $buscar->pronom;
            $detalle->cdecan = $cantidad[$index];
            $detalle->umecod = $buscar->umecod;
            $detalle->cdepuni = $precio[$index];
            $detalle->cdevun = $precio[$index];
            $detalle->cdepve = $totalitem[$index];
            $detalle->cdevve = $totalitem[$index];
            $detalle->tigcod = $buscar->tigcod;
            $detalle->cdeigv = $totalitem[$index]-($totalitem[$index]);
            $detalle->costo =  $buscar->costo;
            $detalle->por_des = $descuento[$index];
             $detalle->desc_mon = $precio[$index]*($descuento[$index]/100);
            $detalle->save();


        }

        $cabecera->generarpdfgeneral($cabecera->IdCpe_cabecera);

        return response()->json(['registrado']);
    }

     
     public function registrarordentrabajo(Request $request){

         if(empty($request->get('cotid'))){
          $buscarcot ="";
         }else{
              $buscarcot = cpe_cabecera::findOrFail($request->get('cotid'));
             $buscarcot->estado='ACEPTADO';
             $buscarcot->update();
         }
        
        $id_almacen = $request->get('id_almacen');

         $bus_alm= DB::tABLE('almacenes')->where('id_almacen',$id_almacen)->first();

        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$bus_alm->id_empresa_negocio)->first();


          $senudoc = DB::tABLE('empresa_negocios')->select('serot','numot')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
          $numcomp =  $senudoc->numot+1;
          $sercomp =  $senudoc->serot;
     

       
       $buscar =  DB::tABLE('cliente')->where('clinum',$request->get('clinum'))->first();

       $buscarplaca = DB::tABLE('tipos_vehiculos')->where('placa',$request->get('placa'))->first();

        if(empty($buscar)){

            $cliente = new Cliente;
            $cliente->rucemp = trim(Auth::user()->IdEmpresa);
            $cliente->tdicod = $request->get('tdicod');
            $cliente->clinum = $request->get('clinum');
            $cliente->clidir = $request->get('clidir');
            $cliente->clinom = $request->get('clinom');
            $cliente->clicor = $request->get('clicor');
            $cliente->telefono = $request->get('telefono');
            $cliente->clicon = $request->get('clicon');
            $cliente->clicontel = $request->get('clicontel');
            $cliente->tar_id = $request->get('tarifa');
            $cliente->save();

        }else{
            $cliente = Cliente::findOrFail($buscar->clicod);
            $cliente->rucemp = trim(Auth::user()->IdEmpresa);
            $cliente->tdicod = $request->get('tdicod');
            $cliente->clinum = $request->get('clinum');
            $cliente->clidir = $request->get('clidir');
            $cliente->clinom = $request->get('clinom');
            $cliente->clicor = $request->get('clicor');
            $cliente->telefono = $request->get('telefono');
            $cliente->clicon = $request->get('clicon');
            $cliente->clicontel = $request->get('clicontel');
            $cliente->tar_id = $request->get('tarifa');
            $cliente->update();
        }

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
       

        
        $cabecera =  new cpe_cabecera;
        $cabecera->tdocod ='70';
        $cabecera->fechacot = $request->get('fecha');
        $cabecera->tdicod = $request->get('tdicod');
        $cabecera->ccandi = $request->get('clinum');
        $cabecera->ccanom = $request->get('clinom');
        $cabecera->moncod = $request->get('moncod');
        $cabecera->ccatvg = $request->get('total');
         $cabecera->grua = $request->get('grua');
        $cabecera->ccaigv = $request->get('total') - $request->get('total');
        $cabecera->ccaitv = $request->get('total');
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->tipcambio = $request->get('tipcam');
        
        
         $empresanegocio = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);
     
            
          if( $empresanegocio->numot == $numcomp){
              $modnumcomp = $numcomp+1;
          }else{
              $modnumcomp = $numcomp;
          }

          $empresanegocio->serot = $sercomp;
          $empresanegocio->numot = $modnumcomp;
          

          $numdoc = $modnumcomp;
          
          $cabecera->serdoc = $sercomp;
          $cabecera->numdoc = $numdoc;
       
      

        $empresanegocio->update();


         $cabecera->clicod = $cliente->clicod;
        $cabecera->direccion = $request->get('clidir');
        $cabecera->estado = 'REGISTRADO';
        $cabecera->turno = Auth::user()->id_turno;
        $cabecera->topcod = '0101';
        $cabecera->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $cabecera->comb_id = $request->get('combustible');
        $cabecera->kilometros = $request->get('kilometros');
        $cabecera->observaciones = $request->get('observaciones');
        $cabecera->placa = $request->get('placa');
        $cabecera->cilindrada = $request->get('cilindrada');
        $cabecera->tec_id = $request->get('tecnico');
        $cabecera->bastidor = $request->get('bastidor');
        $cabecera->fecinspeccion = $request->get('fecinspeccion');
        $cabecera->fecsoat = $request->get('fecsoat');
        $cabecera->fecrevision = $request->get('fecrevision');
        $cabecera->color = $request->get('color');
        $cabecera->encargado = $request->get('encargado');
        $cabecera->encargadotel = $request->get('encargadotel');
        $cabecera->cre_dia_id = $request->get('condicionpago');
        $cabecera->IdEmpresa = Auth::user()->IdEmpresa;

        if(!empty($buscarcot)){
          $cabecera->referencia = $buscarcot->serdoc.'-'.$buscarcot->numdoc;
          $cabecera->IdCpe_cabecera_ref = $buscarcot->IdCpe_cabecera;
        }
        $cabecera->save();

        $productos = $request->get('IdProducto');
        $cantidad = $request->get('cantidad');
        $unidad = $request->get('unidad');
        $precio = $request->get('precio');
        $totalitem = $request->get('totalitem');
        $descuento = $request->get('descuento');
        
        $unidades = $request->get('unid');
        $proid = $request->get('proid');
        $detpro = $request->get('pronom');
        $vunit = $request->get('provun');
        $puni = $request->get('propun');
        $vtot = $request->get('itemtotal');
        $cantidades = $request->get('cant');
        $val_uni = $request->get('precio');

        foreach ($proid as $index => $producto) {

            $codpro = productos::findOrFail($producto);


            if(empty($codpro->pro_rel)){

                $id_prod = $codpro->IdProducto;

            }else{
              
                $id_prod = $codpro->pro_rel;

            }



            $detalle = new cpe_detalle;
            $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
            $detalle->umecod = $codpro->umecod;
            $detalle->cpe_det_factor = $codpro->factor;
            $detalle->cdecan = $cantidades[$index];
            $detalle->procod = $codpro->proccod;
            $detalle->IdProducto = $codpro->IdProducto;
            $detalle->IdProducto_rel = $id_prod;
            $detalle->cdepsu = "";
            $detalle->cdedes = $detpro[$index];
            $detalle->costo = $codpro->costo_total;
            $detalle->tigcod = $codpro->tigcod;
            $detalle->icbper = $codpro->icbper;
            $detalle->id_almacen_pro = $id_almacen;
           

            if($codpro->tigcod =='10'){

              $preciouni = $puni[$index]-$descuento[$index];;
              $valoruni = ($puni[$index])-($descuento[$index]);
              $valorunitario = $val_uni[$index];

              $valorsubtotal = $vtot[$index];
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
            $detalle->fecha_venta = $request->get('fecha');
            $detalle->flete = $codpro->flete;
            $detalle->save();


        }

        $cabecera->generarpdfgeneral($cabecera->IdCpe_cabecera);
         return response()->json(['mensaje'=>'registrado','codfact'=>$cabecera->IdCpe_cabecera]);
    }


     public function registrarcobro(Request $request){


          $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);

          $monto = $request->get('monto');
          $medio = $request->get('medio');

          $estadopago = $request->get('estadopago');
          $buscre = DB::tABLE('credito_dias')->where('cre_dia_id',$estadopago)->first();

          $buscond = DB::tABLE('credito_dias')->where('cre_dia_id',$request->get('condicionpago'))->first();

         if(empty($request->get('cotid'))){

          $buscarcot ="";
          $codref="";

         }else{
             
             $buscarcot = cpe_cabecera::findOrFail($request->get('cotid'));
             $buscarcot->estado='COBRADO';
             $buscarcot->update();
             $codref=$buscarcot->tdocod;
         }

         $tdocod = $request->get('tdocod');

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
        }


  
       $buscar =  DB::tABLE('cliente')->where('clinum',$request->get('clinum'))->first();

       $buscarplaca = DB::tABLE('tipos_vehiculos')->where('placa',$request->get('placa'))->first();

        if(empty($buscar)){

            $cliente = new Cliente;
            $cliente->rucemp = trim(Auth::user()->IdEmpresa);
            $cliente->tdicod = $request->get('tdicod');
            $cliente->clinum = $request->get('clinum');
            $cliente->clidir = $request->get('clidir');
            $cliente->clinom = $request->get('clinom');
            $cliente->clicor = $request->get('clicor');
            $cliente->telefono = $request->get('telefono');
            $cliente->clicon = $request->get('clicon');
            $cliente->clicontel = $request->get('clicontel');
            $cliente->tar_id = $request->get('tarifa');
            $cliente->save();

        }else{
            $cliente = Cliente::findOrFail($buscar->clicod);
            $cliente->rucemp = trim(Auth::user()->IdEmpresa);
            $cliente->tdicod = $request->get('tdicod');
            $cliente->clinum = $request->get('clinum');
            $cliente->clidir = $request->get('clidir');
            $cliente->clinom = $request->get('clinom');
            $cliente->clicor = $request->get('clicor');
            $cliente->telefono = $request->get('telefono');
            $cliente->clicon = $request->get('clicon');
            $cliente->clicontel = $request->get('clicontel');
            $cliente->tar_id = $request->get('tarifa');
            $cliente->update();
        }

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
       

        
        $cabecera =  new cpe_cabecera;
        $cabecera->tdocod = $tdocod;
        $cabecera->ccafem = $request->get('fecEmi');
        $cabecera->topcod = $request->get('topcod');
        $cabecera->fechacot = $request->get('fecha');
        $cabecera->tdicod = $request->get('tdicod');
        $cabecera->ccandi = $request->get('clinum');
        $cabecera->ccanom = $request->get('clinom');
        $cabecera->clicorcli = $request->get('clicor');
        $cabecera->ccades = $request->get('descuentomonto');
        $cabecera->ccadespor = $request->get('descuentoglobal');
 
          if($buscre->cre_dia_tip=='CONTADO' || $buscre->cre_dia_tip=='PERSONALIZADO'){
            $cabecera->ccafve = $request->get('fecVen');
        }else{
            $cabecera->ccafve = date('Y-m-d',strtotime($fecemi."+ ".$buscre->cre_dia_fac." days"));
        }

        if($buscre->cre_dia_tip=='CONTADO'){
            $cabecera->totalcontado = $request->get('total');
            $cabecera->totalcredito = '0';
        }else{
            $cabecera->totalcredito = $request->get('total');
            $cabecera->totalcontado = '0';
        }

         if($buscre->cre_dia_tip=='CONTADO'){
          $cabecera->estadopago = 'CONTADO';
        }else{
          $cabecera->estadopago = 'CREDITO';
        }
        
     

        $cabecera->nota = $request->get('nota');
        $cabecera->clidirfac = $request->get('clidir');
        $cabecera->moncod = $request->get('moncod');
        $cabecera->ccatvg = $request->get('total');
        $cabecera->grua = $request->get('grua');
        $cabecera->ccaigv = $request->get('total') - $request->get('total');
        $cabecera->ccaitv = $request->get('total');
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
          $cabecera->cre_dia_id = $estadopago;
        $cabecera->tipcambio = $request->get('tipcam');
        
        
         $empresanegocio = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);
     
        
        if($tdocod=='01'){
          if( $empresanegocio->FnuEmpresa == $numcomp){
              $modnumcomp = $numcomp+1;
          }else{
              $modnumcomp = $numcomp;
          }

          $empresanegocio->FseEmpresa = $sercomp;
          $empresanegocio->FnuEmpresa = $modnumcomp;
          $numdoc = $modnumcomp;
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
          $numdoc = $modnumcomp;
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
          $numdoc = $modnumcomp;
          $cabecera->serdoc= $sercomp;
          $cabecera->numdoc = $numdoc;

        }

          $empresanegocio->update();


        $cabecera->clicod = $cliente->clicod;
        $cabecera->direccion = $request->get('clidir');
        $cabecera->estado = 'REGISTRADO';
        $cabecera->turno = Auth::user()->id_turno;
        $cabecera->topcod = '0101';
        $cabecera->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $cabecera->comb_id = $request->get('combustible');
        $cabecera->kilometros = $request->get('kilometros');
        $cabecera->observaciones = $request->get('observaciones');
        $cabecera->placa = $request->get('placa');
        $cabecera->cilindrada = $request->get('cilindrada');
        $cabecera->tec_id = $request->get('tecnico');
        $cabecera->bastidor = $request->get('bastidor');
        $cabecera->fecinspeccion = $request->get('fecinspeccion');
        $cabecera->fecsoat = $request->get('fecsoat');
        $cabecera->fecrevision = $request->get('fecrevision');
        $cabecera->color = $request->get('color');
        $cabecera->encargado = $request->get('encargado');
        $cabecera->encargadotel = $request->get('encargadotel');
        $cabecera->cre_dia_id = $request->get('condicionpago');
        $cabecera->IdEmpresa = Auth::user()->IdEmpresa;
        if(!empty($buscarcot)){
          $cabecera->referencia = $buscarcot->serdoc.'-'.$buscarcot->numdoc;
          $cabecera->IdCpe_cabecera_ref = $buscarcot->IdCpe_cabecera;
        }
        $cabecera->save();


        $usuario_facturacion = new usuario_facturacion;
        $usuario_facturacion->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
        $usuario_facturacion->id_turno = Auth::user()->id_turno;
        $usuario_facturacion->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $usuario_facturacion->IdEmpresa = Auth::user()->IdEmpresa;
        $usuario_facturacion->referencia = "Registro";
        $usuario_facturacion->save();


         foreach ($medio as $index => $mp) {
             
             if($monto[$index] > '0.00'){

                DB::tABLE('venta_medio_pago')
                ->insert(['id_turno'=>Auth::user()->id_turno,'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,'id_med_pag'=>$mp,'monto'=>$monto[$index]]);

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
                $movimiento->id_empresa_negocio = $sucursal->id_empresa_negocio;
                $movimiento->save();

              }
             }
            
          }


        $productos = $request->get('proid');
        $cantidad = $request->get('cant');
        $unidad = $request->get('unid');
        $precio = $request->get('propun');
        $totalitem = $request->get('itemtotal');
        $descuento = $request->get('desc');
        $bus_alm = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();
        foreach ($productos as $index => $producto) {
            
                $codpro = productos::findOrFail($producto);
                $codproducto = $codpro->procod;

             $stockprod = DB::tABLE('producto_stock')
                ->where('IdProducto',$producto)
                ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
                ->where('id_almacen',$bus_alm->id_almacen)
                ->first();

               // dd($stockprod);
                if(empty($stockprod)){

                  $stock = 0-($cantidad[$index]*$codpro->factor);

                  $stockprod_act = DB::tABLE('producto_stock')
                  ->insert([
                    'stock'=>$stock,
                    'IdProducto'=>$producto,
                    'id_empresa_negocio'=>Auth::user()->id_empresa_negocio,
                    'id_almacen'=>$bus_alm->id_almacen]
                  );

                  $sto_ini = '0';
                  
                }else{

                   $stock = $stockprod->stock-($cantidad[$index]*$codpro->factor);

                  $stockprod_act = DB::tABLE('producto_stock')
                  ->where('pro_sto_id',$stockprod->pro_sto_id)
                  ->update(['stock'=>$stockprod->stock-($cantidad[$index]*$codpro->factor)]);

                  $sto_ini = $stockprod->stock_inicial;


                }


            $detalle = new cpe_detalle;
            $detalle->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
            $detalle->IdProducto = $producto;
            $detalle->cdedes = $codpro->pronom;
            $detalle->umecod = $codpro->umecod;
            $detalle->cdecan = $cantidad[$index];
            $detalle->cdepuni = $precio[$index];
            $detalle->cdevun = $precio[$index];
            $detalle->valor_unitario = $precio[$index];
            $detalle->cdepve = $totalitem[$index];
            $detalle->cdevve = $totalitem[$index];
            $detalle->tigcod = '20';
            $detalle->cdeigv = $totalitem[$index]-($totalitem[$index]);
            $detalle->costo =  $codpro->costo;
            $detalle->por_des = $descuento[$index];
            $detalle->desc_mon = $precio[$index]*($descuento[$index]/100);
            if(isset($stockprod)){
                      $detalle->cpe_det_stock_inicial = $stockprod->stock_inicial;
            }
            $detalle->save();




               if(isset($stockprod)){

                   DB::tABLE('movimientos_productos')->insert([

                    'precio'=>$precio[$index],
                    'cantidad'=>$cantidad[$index]*$codpro->factor,
                    'costo'=>$codpro->costo,
                    'mov_cab_id'=>'',
                    'stock'=>$stock,
                    'IdProducto_rel'=>$producto,
                    'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
                    'com_cab_id'=>'',
                    'stock_inicial'=>$sto_ini,
                    'serie'=>$cabecera->serdoc,
                    'numero'=>$cabecera->numdoc,
                    'tdocod'=>$cabecera->tdocod,
                    'tipo'=>'',
                    'id_empresa_negocio'=>$bus_alm->id_empresa_negocio,
                    'id_almacen'=>$bus_alm->id_almacen,
                    'fecha_mov'=>$cabecera->ccafem,
                   

            ]);
        }


        }

          $buscond = DB::tABLE('credito_dias')->where('cre_dia_id',$request->get('condicionpago'))->first();

          if($buscre->cre_dia_fac !='0'){

            $cuentacobrar = new cuentascobrar;
            $cuentacobrar->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
            $cuentacobrar->clicod = $cabecera->clicod;
            $cuentacobrar->fec_ven = date('Y-m-d',strtotime($request->get('fecemi')."+ ".$buscond->cre_dia_fac." days"));
            $cuentacobrar->abono = '0.00';
            $cuentacobrar->estado_cob = 'pendiente';
            $cuentacobrar->total = $cabecera->ccaitv;
            $cuentacobrar->placa = $request->get('placa');
            $cuentacobrar->saldo = $cabecera->ccaitv;
            $cuentacobrar->id_empresa_negocio = Auth::user()->id_empresa_negocio;
            $cuentacobrar->save();

          }
         



        $cabecera->generar_nuevo_qr($cabecera->IdCpe_cabecera);
        $cabecera->generarpdfgeneral($cabecera->IdCpe_cabecera);

        $cabecera->generar_xml_boleta_factura($cabecera->IdCpe_cabecera);
          

        if($empresa->tipo_envio =='1'){
            $cabecera->enviar_sunat($cabecera->IdCpe_cabecera);
        }

        if(!empty($request->get('clicor'))){

            $cabecera->enviar_comprobante_correo($cabecera->IdCpe_cabecera,$request->get('clicor'));
        }

         return response()->json(['mensaje'=>'registrado','codfact' =>$cabecera->IdCpe_cabecera]);
    }



     public function registrarordenpedido(Request $request){

         if(empty($request->get('cotid'))){
          $buscarcot ="";
         }else{
              $buscarcot = cpe_cabecera::findOrFail($request->get('cotid'));
             $buscarcot->estado='ACEPTADO';
             $buscarcot->update();
         }
     


          $senudoc = DB::tABLE('empresa_negocios')->select('serop','numop')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
          $numcomp =  $senudoc->numop+1;
          $sercomp =  $senudoc->serop;
     

       
       $buscar =  DB::tABLE('cliente')->where('clinum',$request->get('clinum'))->first();

       $buscarplaca = DB::tABLE('tipos_vehiculos')->where('placa',$request->get('placa'))->first();

        if(empty($buscar)){

            $cliente = new Cliente;
            $cliente->rucemp = trim(Auth::user()->IdEmpresa);
            $cliente->tdicod = $request->get('tdicod');
            $cliente->clinum = $request->get('clinum');
            $cliente->clidir = $request->get('clidir');
            $cliente->clinom = $request->get('clinom');
            $cliente->clicor = $request->get('clicor');
            $cliente->telefono = $request->get('telefono');
            $cliente->clicon = $request->get('clicon');
            $cliente->clicontel = $request->get('clicontel');
            $cliente->tar_id = $request->get('tarifa');
            $cliente->save();

        }else{
            $cliente = Cliente::findOrFail($buscar->clicod);
            $cliente->rucemp = trim(Auth::user()->IdEmpresa);
            $cliente->tdicod = $request->get('tdicod');
            $cliente->clinum = $request->get('clinum');
            $cliente->clidir = $request->get('clidir');
            $cliente->clinom = $request->get('clinom');
            $cliente->clicor = $request->get('clicor');
            $cliente->telefono = $request->get('telefono');
            $cliente->clicon = $request->get('clicon');
            $cliente->clicontel = $request->get('clicontel');
            $cliente->tar_id = $request->get('tarifa');
            $cliente->update();
        }

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
       

        
        $cabecera =  new cpe_cabecera;
        $cabecera->tdocod ='90';
        $cabecera->fechacot = $request->get('fecha');
        $cabecera->tdicod = $request->get('tdicod');
        $cabecera->ccandi = $request->get('clinum');
        $cabecera->ccanom = $request->get('clinom');
        $cabecera->moncod = $request->get('moncod');
        $cabecera->ccatvg = $request->get('total');
         $cabecera->grua = $request->get('grua');
        $cabecera->ccaigv = $request->get('total') - $request->get('total');
        $cabecera->ccaitv = $request->get('total');
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->tipcambio = $request->get('tipcam');
        
        
         $empresanegocio = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);
     
            
          if( $empresanegocio->numop == $numcomp){
              $modnumcomp = $numcomp+1;
          }else{
              $modnumcomp = $numcomp;
          }

          $empresanegocio->serop = $sercomp;
          $empresanegocio->numop = $modnumcomp;
          

          $numdoc = $modnumcomp;
          
          $cabecera->serdoc = $sercomp;
          $cabecera->numdoc = $numdoc;
       
      

        $empresanegocio->update();


         $cabecera->clicod = $cliente->clicod;
        $cabecera->direccion = $request->get('clidir');
        $cabecera->estado = 'REGISTRADO';
        $cabecera->turno = Auth::user()->id_turno;
        $cabecera->topcod = '0101';
        $cabecera->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $cabecera->comb_id = $request->get('combustible');
        $cabecera->kilometros = $request->get('kilometros');
        $cabecera->observaciones = $request->get('observaciones');
        $cabecera->placa = $request->get('placa');
        $cabecera->cilindrada = $request->get('cilindrada');
        $cabecera->tec_id = $request->get('tecnico');
        $cabecera->bastidor = $request->get('bastidor');
        $cabecera->fecinspeccion = $request->get('fecinspeccion');
        $cabecera->fecsoat = $request->get('fecsoat');
        $cabecera->fecrevision = $request->get('fecrevision');
        $cabecera->color = $request->get('color');
        $cabecera->encargado = $request->get('encargado');
        $cabecera->encargadotel = $request->get('encargadotel');
        $cabecera->cre_dia_id = $request->get('condicionpago');

        if(!empty($buscarcot)){
          $cabecera->referencia = $buscarcot->serdoc.'-'.$buscarcot->numdoc;
          $cabecera->IdCpe_cabecera_ref = $buscarcot->IdCpe_cabecera;
        }
        $cabecera->save();

        $productos = $request->get('IdProducto');
        $cantidad = $request->get('cantidad');
        $unidad = $request->get('unidad');
        $precio = $request->get('precio');
        $totalitem = $request->get('totalitem');
        $descuento = $request->get('descuento');
        
        foreach ($productos as $index => $producto) {
            
            $buscar = productos::findOrFail($producto);
            $detalle = new cpe_detalle;
            $detalle->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
            $detalle->IdProducto = $producto;
            $detalle->cdedes = $buscar->pronom;
            $detalle->umecod = $buscar->umecod;
            $detalle->cdecan = $cantidad[$index];
            $detalle->cdepuni = $precio[$index];
            $detalle->cdevun = $precio[$index];
            $detalle->cdepve = $totalitem[$index];
            $detalle->cdevve = $totalitem[$index];
            $detalle->tigcod = $buscar->tigcod;
            $detalle->cdeigv = $totalitem[$index]-($totalitem[$index]);
            $detalle->costo =  $buscar->costo;
            $detalle->por_des = $descuento[$index];
             $detalle->desc_mon = $precio[$index]*($descuento[$index]/100);
            $detalle->save();


        }

         $cabecera->generarpdfgeneral($cabecera->IdCpe_cabecera);
         return response()->json(['registrado']);
    }


       public function indexcontingencia($codfact=0)
    {   


        $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

        $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();

        $combustible = DB::tABLE('combustible')->get();

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

        $procesos = DB::tABLE('procesos')->get();
    
        return view('empresas.puntosventas.contingencia',compact('users','codfact','categorias','comprobante','tipodocumento','unidades','unidades','vendedores','creditos','mediospagos','clientes','documentos','datos','monedas','senudoc','motivos','modalidades','docidentidad','doccomprobante','monedas','almacenes','gastos','combustible','marcas','modelos','tecnicos','tipos_igv','empresa','procesos'));
    }





}
