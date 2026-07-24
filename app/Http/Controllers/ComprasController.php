<?php

namespace MasterSoft\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MasterSoft\compras_cabecera;
use MasterSoft\compras_detalle;
use MasterSoft\cuentaspagar;
use MasterSoft\cuentaspagardetalle;
use MasterSoft\movimientos;
use MasterSoft\EmpresaNegocios;
use MasterSoft\User;
use MasterSoft\Empresa;
use MasterSoft\Modelos\Almacen;
use MasterSoft\Proveedor;
use MasterSoft\productos;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;
use Excel;

class ComprasController extends Controller
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
        $rucemp = trim(Auth::user()->IdEmpresa);
        $negocios = EmpresaNegocios::get();
        $sucursal = $request->get('sucursal');
        $proveedores = DB::tABLE('proveedor')->get();
        $proveedor = $request->get('proveedor');
        $cod_mov = $request->get('cod_mov');
        $estado_mercaderia = $request->get('estado_mercaderia');
        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');

       /* $compraseliminadas = DB::tABLE('compras_cabecera')->where('est_compra','Eliminado')->get();

        foreach ($compraseliminadas as $key) {
           
           DB::tABLE('movimientos_productos')->where('com_cab_id',$key->com_cab_id)->delete();

         } */

        if(empty($fecin)){

          $fecin = now()->modify('first day of this month')->format('Y-m-d');
          $fecfin = now()->modify('last day of this month')->format('Y-m-d');

        }


        $compras = DB::tABLE('compras_cabecera')
        ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
        ->leftjoin('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
        
        ->where('est_compra','Registrado')
          ->where(function ($query) use ($sucursal){
            if(!empty($sucursal)){
                $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
            }
           
        })
        ->where(function ($query1) use ($proveedor){
            if($proveedor !='Todos' && !empty($proveedor) ){
                $query1->where('compras_cabecera.prov_id',$proveedor);
            }
            
        })
         ->where(function ($query1) use ($cod_mov){
            if(!empty($cod_mov)){
                $query1->where('cod_mov',$cod_mov);
            }
            
        })
          ->where('compras_cabecera.tdocod','!=','80')
        ->orderby('com_cab_id','desc')
        ->get();



        return view('empresas.compras.index',compact('compras','negocios','sucursal','proveedores','proveedor','fecin','fecfin'));
    }


      public function indexnotascreditos(Request $request)
    {
        $rucemp = trim(Auth::user()->IdEmpresa);
        $negocios = EmpresaNegocios::get();
        $sucursal = $request->get('sucursal');
        $proveedores = DB::tABLE('proveedor')->get();
        $proveedor = $request->get('proveedor');
        $cod_mov = $request->get('cod_mov');
        $estado_mercaderia = $request->get('estado_mercaderia');
        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');

       /* $compraseliminadas = DB::tABLE('compras_cabecera')->where('est_compra','Eliminado')->get();

        foreach ($compraseliminadas as $key) {
           
           DB::tABLE('movimientos_productos')->where('com_cab_id',$key->com_cab_id)->delete();

         } */

        if(empty($fecin)){

          $fecin = now()->modify('first day of this month')->format('Y-m-d');
          $fecfin = now()->modify('last day of this month')->format('Y-m-d');

        }


        $compras = DB::tABLE('compras_cabecera')
        ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
        ->leftjoin('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
        
        ->where('est_compra','Registrado')
          ->where(function ($query) use ($sucursal){
            if(!empty($sucursal)){
                $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
            }
           
        })
        ->where(function ($query1) use ($proveedor){
            if($proveedor !='Todos' && !empty($proveedor) ){
                $query1->where('compras_cabecera.prov_id',$proveedor);
            }
            
        })
         ->where(function ($query1) use ($cod_mov){
            if(!empty($cod_mov)){
                $query1->where('cod_mov',$cod_mov);
            }
            
        })
          ->where('compras_cabecera.tdocod','!=','80')
        ->orderby('com_cab_id','desc')
        ->get();



        return view('empresas.compras.indexnotascreditos',compact('compras','negocios','sucursal','proveedores','proveedor','fecin','fecfin'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
 
     public function editar_compra($id)
    {

         $negocios = EmpresaNegocios::get();

         $almacenes = DB::tABLE('almacenes')
         ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
         ->get();
        
        $creditos = DB::tABLE('credito_dias')->get();


        $tip_cam = DB::tABLE('tipocambio')->where('FecTipCambio',now()->format('Y-m-d'))->first();


        $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();

        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $docidentidad = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

       
        $doccomprobante = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->where('compras','1')->orderBy('tdocod','asc')->get();

         $tip_doc_notas= DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->where('nota_credito','1')->orderBy('tdocod','asc')->get();

        $rucemp = trim(Auth::user()->IdEmpresa);


        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();


        $fecha = now()->format('m/d/Y');

       
        $cabecera = DB::tABLE('compras_cabecera')
        ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
        ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
        ->where('com_cab_id',$id)
        ->first();

        $detalle= DB::tABLE('compras_detalle as cd')
        ->join('unidad_medida as um','um.umecod','cd.ume_cod')
        ->join('productos as p','p.IdProducto','cd.pro_id')
        ->where('com_cab_id',$id)
        ->get();

        $laboratorios = DB::tABLE('laboratorio')->get();

        $categorias = DB::tABLE('categorias')->get();

        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->first();

        return view('empresas.compras.editar_compra',compact('igv','monedas','unidades','docidentidad','fecha','doccomprobante','detalle','cabecera','negocios','creditos','almacenes','categorias','id','laboratorios','tip_doc_notas','sucursal'));

    }


     public function editarcompra($id)
    {

         $negocios = EmpresaNegocios::get();

         $almacenes = DB::tABLE('almacenes')
         ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
         ->get();
        
        $creditos = DB::tABLE('credito_dias')->get();


        $tip_cam = DB::tABLE('tipocambio')->where('FecTipCambio',now()->format('Y-m-d'))->first();


        $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();

        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $docidentidad = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

       
        $doccomprobante = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->where('compras','1')->orderBy('tdocod','asc')->get();

   
        $rucemp = trim(Auth::user()->IdEmpresa);


        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();


        $fecha = now()->format('m/d/Y');

       
        $cabecera = DB::tABLE('compras_cabecera')
        ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
        ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
        ->where('com_cab_id',$id)
        ->first();

        $detalle= DB::tABLE('compras_detalle as cd')
        ->select('pronom','cantidad','pre_uni','total','IdProducto','cd.lote','cd.vencimiento')
        ->leftjoin('unidad_medida as um','um.umecod','cd.ume_cod')
        ->leftjoin('productos as p','p.IdProducto','cd.pro_id')
        ->where('com_cab_id',$id)
        ->get();

        $categorias = DB::tABLE('categorias')->get();
	    
			      $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

		
		   $almacen = DB::tABLE('almacenes')
        ->where('predeterminado','1')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();
		
		
      $productos = DB::tABLE('productos')
        ->select('costo','procod','mar_nom as marca','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND tipo='1' AND id_almacen='".$almacen->id_almacen."' AND id_empresa_negocio='".Auth::user()->id_empresa_negocio."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE productos.IdProducto = pro.pro_rel AND tipo='2') as cont_pre"))
        ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
        ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
        ->leftjoin('marcas','marcas.mar_id','productos.marca')
        ->where('tipo','1')
        ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('id_almacen',$almacen->id_almacen)
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();
		
		
        return view('empresas.compras.editarcompra',compact('productos','sucursal','almacen','igv','monedas','unidades','docidentidad','fecha','doccomprobante','detalle','cabecera','negocios','creditos','almacenes','categorias'));

    }


   


    public function compraproductos()
    {

        $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();

        $negocios = DB::tABLE('empresa_negocios')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();

        $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();
        
        $creditos = DB::tABLE('credito_dias')->get();

        // consultar unidades de medida
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();


        // consultar tipos de documentos de identidad
        $docidentidad = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

        //consultar tipo de documento
        $doccomprobante = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->where('compras','1')->orderBy('tdocod','asc')->get();

         $tip_doc_notas= DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->where('nota_credito','1')->orderBy('tdocod','asc')->get();

        // obtener el ruc de la empresa en la cual se logueo
        $rucemp = trim(Auth::user()->IdEmpresa);


        // consultar tipos de monedas
        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

        //$negocios = EmpresaNegocios::where('estado','Activo')->get();
        // consultar la serie y numero de factura

        $fecha = now()->format('m/d/Y');

        $categorias = DB::tABLE('categorias')->get();
		
		$sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->first();

        $laboratorios = DB::tABLE('laboratorio')->get();
		
		$almacen = DB::tABLE('almacenes')
        ->where('predeterminado','1')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();
		
		
   
        return view('empresas.compras.compra',compact('tip_doc_notas','igv','monedas','unidades','docidentidad','fecha','doccomprobante','negocios','creditos','almacenes','categorias','almacen','sucursal','laboratorios'));

    }



    public function nota_credito_compra()
    {

        $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();

        $negocios = DB::tABLE('empresa_negocios')->get();

        $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();
        
        $creditos = DB::tABLE('credito_dias')->get();

        // consultar unidades de medida
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();


        // consultar tipos de documentos de identidad
        $docidentidad = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

        //consultar tipo de documento
        $doccomprobante = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->where('nota_credito','2')->orderBy('tdocod','asc')->get();

         $tip_doc_notas= DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->where('nota_credito','1')->orderBy('tdocod','asc')->get();

        // obtener el ruc de la empresa en la cual se logueo
        $rucemp = trim(Auth::user()->IdEmpresa);


        // consultar tipos de monedas
        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

        //$negocios = EmpresaNegocios::where('estado','Activo')->get();
        // consultar la serie y numero de factura

        $fecha = now()->format('m/d/Y');

        $categorias = DB::tABLE('categorias')->get();
        
        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

        $laboratorios = DB::tABLE('laboratorio')->get();
        
        $almacen = DB::tABLE('almacenes')
        ->where('predeterminado','1')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();
        
        $tipos_notas = DB::tABLE('tipo_nota_credito')->get();
   
        return view('empresas.compras.nota_credito_compra',compact('tip_doc_notas','igv','monedas','unidades','docidentidad','fecha','doccomprobante','negocios','creditos','almacenes','categorias','almacen','sucursal','laboratorios','tipos_notas'));

    }

         public function editar_nota_credito_compra($id)
    {

         $negocios = EmpresaNegocios::get();

         $almacenes = DB::tABLE('almacenes')
         ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
         ->get();
        
        $creditos = DB::tABLE('credito_dias')->get();


        $tip_cam = DB::tABLE('tipocambio')->where('FecTipCambio',now()->format('Y-m-d'))->first();


        $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();

        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $docidentidad = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

       
         //consultar tipo de documento
        $doccomprobante = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->where('nota_credito','2')->orderBy('tdocod','asc')->get();

         $tip_doc_notas= DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->where('nota_credito','1')->orderBy('tdocod','asc')->get();

       
        $rucemp = trim(Auth::user()->IdEmpresa);


        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();


        $fecha = now()->format('m/d/Y');

       
        $cabecera = DB::tABLE('compras_cabecera')
        ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
        ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
        ->where('com_cab_id',$id)
        ->first();

        $detalle= DB::tABLE('compras_detalle as cd')
        ->join('unidad_medida as um','um.umecod','cd.ume_cod')
        ->join('productos as p','p.IdProducto','cd.pro_id')
        ->where('com_cab_id',$id)
        ->get();

        $laboratorios = DB::tABLE('laboratorio')->get();

        $categorias = DB::tABLE('categorias')->get();

        $tipos_notas = DB::tABLE('tipo_nota_credito')->get();

        return view('empresas.compras.editar_nota_credito_compra',compact('igv','monedas','unidades','docidentidad','fecha','doccomprobante','detalle','cabecera','negocios','creditos','almacenes','categorias','id','laboratorios','tip_doc_notas','tipos_notas'));

    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   public function store(Request $request)
 {   


 DB::beginTransaction();

 try{

  
     if($request->get('mondoc')=='USD' && ($request->get('tip_cam')=='0' || empty($request->get('tip_cam')))){
         if($request->ajax()) {
           return response()->json(['error' => 'INGRESAR TIPO DE CAMBIO PARA COMPRAS EN DÓLARES']);
         }
     }

     $estado = $request->get('estado');
     $cantidades = $request->get('cant');
     $unidades = $request->get('unid');
     $igv = $request->get('igv');
     $subtotal = $request->get('subtotal');
     $subtotdet = $request->get('subtot');
     $codpro = $request->get('pro_id');
     $estado_mercaderia = $request->get('estado_mercaderia');
     $detpro = $request->get('detpro');
     $preuni = $request->get('preuni');       
     $vtot = $request->get('vtot');
     $pro_id = $request->get('pro_id');
     $cant_uni = $request->get('cant_uni');
     $vencimiento = $request->get('vencimiento');
     $val_uni =  $request->get('costosigv');
     $laboratorio = $request->get('laboratorio');
     $id_almacen_pro = $request->get('id_almacen_pro');
     $lote = $request->get('lote');
     $flete = $request->get('flete');
     $fleteund = $request->get('fleteund');
     $preciocosto = $request->get('preciocosto');
     $sucursal = $request->get('sucursal');
     $estadopago = $request->get('estadopago');
     $almacen = $request->get('almacen');
     $tdicod = $request->get('tdicod');
     $prov_num = $request->get('clinum');
     $prov_raz = $request->get('clinom');
     $prov_dir = $request->get('clidir');
     $prov_cor = $request->get('clicor');
     $moncod = $request->get('mondoc');
     $inc_igv = $request->get('inc_igv');
     $gravado = $request->get('gravado');
     $tigcod = $request->get('tigcod');
     $rucemp = trim(Auth::user()->IdEmpresa);

    // $tip_cam = DB::tABLE('tipocambio')->where('FecTipCambio',now()->format('Y-m-d'))->first();
     $tip_cam = $request->get('tip_cam');
     if(empty($codpro)){

         if($request->ajax()) {
           return response()->json(['error' => 'Agregar items a la compra']);
         }

     }

     $proveedor = Proveedor::UpdateOrCreate(['prov_ruc'=>$prov_num,'IdEmpresa'=>$rucemp],['prov_raz'=>$prov_raz,'tdicod'=>$tdicod,'prov_cor'=>$prov_cor,'prov_dir'=>$prov_dir,'IdEmpresa'=>$rucemp]);

     if($prov_num!='00000000'){

          if(!empty($request->get('serdoc')) && !empty($request->get('numdoc'))){

             $val_comp = DB::tABLE('compras_cabecera')
             ->where('com_doc_ser',$request->get('serdoc'))
             ->where('com_doc_num',$request->get('numdoc'))
             ->where('prov_id',$proveedor->prov_id)
             ->where('est_compra','Registrado')
             ->get();

             if(count($val_comp) > 0){
                 if($request->ajax()) {
                     return response()->json(['error' => 'EL DOCUMENTO YA EXISTE']);
                 }
             }
       
         }

     }
    

     $buscre = DB::tABLE('credito_dias')
     ->where('cre_dia_id',$estadopago)
     ->first();


     $compras = new compras_cabecera;
     $compras->com_doc_ser = $request->get('serdoc');
     $compras->com_doc_num = $request->get('numdoc');
     $compras->com_fec = $request->get('fecEmi');
     $compras->cre_dia_id = $estadopago;
     $compras->id_turno = Auth::user()->id_turno;
     $compras->com_fec_ing = $request->get('fecIng');
     $compras->com_cab_igv = $igv;
     $compras->subtot_com = $subtotal;
     $compras->cod_tip_ope ='02';
     $compras->inc_igv = $inc_igv;
     $compras->gravado = $gravado;
     $compras->tip_cam = $tip_cam;
     $compras->id_almacen = $almacen;
     $compras->mon_id = $request->get('mondoc');
     $compras->prov_id = $proveedor->prov_id;
     $compras->total_com = $request->get('total');
     $compras->com_grav = $request->get('gravada');
     $compras->com_exo = $request->get('exonerada');
     $compras->com_inaf = $request->get('inafecta');
     $compras->comp_obs = $request->get('obser');
     $compras->tdocod = $request->get('cmbTdo');
     $compras->tipocompra = $request->get('tipocompra');
     $compras->id_empresa_negocio = $sucursal;
     $compras->estado_mercaderia = $estado_mercaderia;
     $compras->IdEmpresa = $rucemp;
     $compras->ser_oc = $request->get('ser_oc');
     $compras->num_oc = $request->get('num_oc');
     $compras->prov_num = $prov_num;
     $compras->orden_compra = $request->get('orden_compra');

     if($buscre->cre_dia_tip=='CONTADO' || $buscre->cre_dia_tip=='PERSONALIZADO'){
         $compras->com_fec_ven = $request->get('fecVen');
     }else{
         $compras->com_fec_ven = date('Y-m-d',strtotime($request->get('fecEmi')."+ ".$buscre->cre_dia_fac." days"));
     }

     if($buscre->cre_dia_tip=='CONTADO'){
         $compras->tot_con = $request->get('total');
         $compras->tot_cre = '0';
     }else{
         $compras->tot_cre = $request->get('total');
         $compras->tot_con = '0';
     }

     $compras->save();

     $estado_sunat = $this->consultarSunat($compras);
        DB::table('compras_cabecera')
            ->where('com_cab_id', $compras->com_cab_id)
            ->update(['sunat_estado' => $estado_sunat]);

     self::generar_codigo_movimiento($compras->com_cab_id);

  foreach($codpro as $index => $pro ){

      // 1. Si la fila no tiene id de producto, saltamos a la siguiente
      if(empty($pro)){
          continue; 
      }

      // 2. Usamos find() en lugar de findOrFail(). 
      // Así, si no existe el código, Hola P simplemente lo ignora y procesa el resto.
      $buspro = productos::find($pro); 
      
      if(!$buspro){
          continue; 
      }

      // 3. Evitamos el "Undefined offset" validando que el índice exista en los arrays
      $venc       = $vencimiento[$index] ?? null;
      $lote_prod  = $lote[$index] ?? null;
      $lab_prod   = $laboratorio[$index] ?? null;
      $p_costo    = $preciocosto[$index] ?? 0;
      $f_und      = $fleteund[$index] ?? 0;
      $p_uni      = $preuni[$index] ?? 0;
      $cant       = $cantidades[$index] ?? 1; // Por defecto 1 para no multiplicar por cero
      $val_u      = $val_uni[$index] ?? 0;
      $v_tot      = $vtot[$index] ?? 0;
      $sub_det    = $subtotdet[$index] ?? 0;
      $t_igv      = $tigcod[$index] ?? null;
      $flete_val  = $flete[$index] ?? 0;

      // Actualizamos los datos del producto
      $buspro->vencimiento = $venc;
      $buspro->lote = $lote_prod;
      $buspro->lab_id = $lab_prod;

      if($request->get('mondoc') == 'USD'){
          $buspro->costo_total = $p_costo * $compras->tip_cam;
          $buspro->flete       = $f_und * $compras->tip_cam;
          $buspro->costo       = $p_uni * $compras->tip_cam;
      } else {
          $buspro->costo_total = $p_costo;
          $buspro->flete       = $f_und;
          $buspro->costo       = $p_uni;
      }

      if(empty($buspro->pro_rel)){
          $id = $pro;
      } else {
          $id = $buspro->pro_rel;
      }

      // ========== CALCULAR EQUIVALENCIA ==========
      $cantidad_principal = $cant * $buspro->factor;
      $cantidad_equivalente = 0;
      
      if(!empty($buspro->factor_cons) && $buspro->factor_cons > 0){
          $cantidad_equivalente = $cantidad_principal * $buspro->factor_cons;
      }

      if($estado_mercaderia == '1'){

          $contreg = DB::tABLE('producto_stock')
              ->where('IdProducto', $id)
              ->where('id_almacen', $almacen)
              ->where('id_empresa_negocio', $sucursal)
              ->count();

          $busprostock = DB::tABLE('producto_stock')
              ->where('IdProducto', $id)
              ->where('id_almacen', $almacen)
              ->where('id_empresa_negocio', $sucursal)
              ->first();

          if($contreg == '0'){

              $stock = $cantidad_principal;
              $stock_equivalencia = $cantidad_equivalente;  
              
              DB::tABLE('producto_stock')
                  ->insert([
                      'stock'              => $stock,
                      'stock_equivalencia' => $stock_equivalencia,  
                      'IdProducto'         => $id,
                      'id_almacen'         => $almacen,
                      'id_empresa_negocio' => $sucursal
                  ]);
                  
              $sto_ini = '0';

          } else {

              $stock = $busprostock->stock + $cantidad_principal;
              $stock_equivalencia = $busprostock->stock_equivalencia + $cantidad_equivalente;  

              DB::tABLE('producto_stock')
                  ->where('id_almacen', $almacen)
                  ->where('IdProducto', $id)
                  ->where('id_empresa_negocio', $sucursal)
                  ->update([
                      'stock'              => $stock,
                      'stock_equivalencia' => $stock_equivalencia  
                  ]);

              $sto_ini = $busprostock->stock_inicial;
          }
      }
      
      $buspro->update();

      $compras_det = new compras_detalle;
      $compras_det->pro_id         = $buspro->IdProducto;
      $compras_det->com_det_factor = $buspro->factor;
      $compras_det->pre_uni        = $p_uni;
      $compras_det->val_uni        = $val_u;
      $compras_det->total          = $v_tot;
      $compras_det->com_det_subtot = $sub_det;
      $compras_det->vencimiento    = $venc;
      $compras_det->lote           = $lote_prod;
      $compras_det->cantidad       = $cant;
      $compras_det->ume_cod        = $buspro->umecod;
      $compras_det->com_cab_id     = $compras->com_cab_id;
      $compras_det->tip_igv        = $t_igv;
      $compras_det->IdEmpresa      = $rucemp;
      $compras_det->IdProducto_rel = $id;
      $compras_det->id_almacen_pro = $almacen;
      $compras_det->flete          = $flete_val;
      $compras_det->flete_und      = $f_und;
      $compras_det->precio_costo   = $p_costo;
      $compras_det->save();

      if($estado_mercaderia == '1'){

          DB::tABLE('movimientos_productos')->insert([
              'IdProducto'           => $id,
              'precio'               => '',
              'cantidad'             => $cantidad_principal,                     
              'cantidad_equivalente' => $cantidad_equivalente,      
              'costo'                => $p_uni,
              'cliente'              => $proveedor->prov_raz,
              'cod_tip_ope'          => '02',
              'descripcion'          => 'COMPRA',
              'mov_cab_id'           => '',
              'mov_tip'              => 'I',
              'stock'                => $stock,
              'stock_equivalente'    => $cantidad_equivalente,         
              'IdProducto_rel'       => $id,
              'IdCpe_cabecera'       => '',
              'com_cab_id'           => $compras->com_cab_id,
              'stock_inicial'        => $sto_ini,
              'serie'                => $compras->com_doc_ser,
              'numero'               => $compras->com_doc_num,
              'tdocod'               => $compras->tdocod,
              'tipo'                 => '2',
              'id_empresa_negocio'   => $sucursal,
              'id_almacen'           => $almacen,
              'fecha_mov'            => $compras->com_fec,
          ]);

          $mov_cal_stock = new Almacen();
          $mov_cal_stock->movimiento_calcular_stock($id, $almacen);
      }
  }
      

     if($buscre->cre_dia_tip !='CONTADO'){
       self::registrarcuentaspagar($compras->com_cab_id);
     }

     DB::commit();

     if($request->ajax()) {
       return response()->json(['mensaje' => 'Compra Registrada']);
     }

  }catch(\Exception $e){

       DB::rollback();

       return response()->json(['error'=>$e->getMessage()]);

  }
  
}

    private function consultarSunat($compra) {
        $token = 'c7c656604942b0a6df5fa225835e99eb7376cf841d38781b91f651f72e03cc09';
        $url = "https://apiperu.dev/api/cpe";

        $params = json_encode([
            "ruc_emisor"            => $compra->prov_num, // RUC del proveedor
            "codigo_tipo_documento" => $compra->tdocod,
            "serie_documento"       => $compra->com_doc_ser,
            "numero_documento"      => $compra->com_doc_num,
            "fecha_de_emision"      => $compra->com_fec,
            "total"                 => number_format($compra->total_com, 2, '.', '')
        ]);

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
        $data = json_decode($response);
        curl_close($curl);

        return ($data && $data->success) ? $data->data->comprobante_estado_descripcion : 'ERROR CONEXION';
    }

    public function revalidarCompra($id) {
        $compra = DB::table('compras_cabecera')->where('com_cab_id', $id)->first();
        if($compra) {
            $nuevo_estado = $this->consultarSunat($compra);
            DB::table('compras_cabecera')
                ->where('com_cab_id', $id)
                ->update(['sunat_estado' => $nuevo_estado]);
                
            return back()->with('success', 'SUNAT responde: ' . $nuevo_estado);
        }
        return back()->with('info', 'No se encontró la compra');
    }

     public function registrar_nota_credito_compra(Request $request)
    {   

        $estado = $request->get('estado');
        $cantidades = $request->get('cant');
        $unidades = $request->get('unid');
        $igv = $request->get('igv');
        $subtotal = $request->get('subtotal');
        $subtotdet = $request->get('subtot');
        $codpro = $request->get('pro_id');
        $estado_mercaderia = $request->get('estado_mercaderia');
        $detpro = $request->get('detpro');
        $preuni = $request->get('preuni');       
        $vtot = $request->get('vtot');
        $pro_id = $request->get('pro_id');
        $cant_uni = $request->get('cant_uni');
        $vencimiento = $request->get('vencimiento');
        $val_uni =  $request->get('costosigv');
        $laboratorio = $request->get('laboratorio');
        $id_almacen_pro = $request->get('id_almacen_pro');
        $lote = $request->get('lote');
        $flete = $request->get('flete');
        $fleteund = $request->get('fleteund');
        $preciocosto = $request->get('preciocosto');
        $sucursal = $request->get('sucursal');
        $estadopago = $request->get('estadopago');
        $almacen = $request->get('almacen');
        $tdicod = $request->get('tdicod');
        $prov_num = $request->get('clinum');
        $prov_raz = $request->get('clinom');
        $prov_dir = $request->get('clidir');
        $prov_cor = $request->get('clicor');
        $moncod = $request->get('mondoc');
        $inc_igv = $request->get('inc_igv');
        $gravado = $request->get('gravado');
        $rucemp = trim(Auth::user()->IdEmpresa);

        $tip_cam = DB::tABLE('tipocambio')->where('FecTipCambio',now()->format('Y-m-d'))->first();

        if(empty($codpro)){

            if($request->ajax()) {
              return response()->json(['error' => 'Agregar items a la compra']);
            }

        }

        $proveedor = Proveedor::UpdateOrCreate(['prov_ruc'=>$prov_num,'IdEmpresa'=>$rucemp],['prov_raz'=>$prov_raz,'tdicod'=>$tdicod,'prov_cor'=>$prov_cor,'prov_dir'=>$prov_dir,'IdEmpresa'=>$rucemp]);

        if($prov_num!='00000000'){

             if(!empty($request->get('serdoc')) && !empty($request->get('numdoc'))){

                $val_comp = DB::tABLE('compras_cabecera')
                ->where('com_doc_ser',$request->get('serdoc'))
                ->where('com_doc_num',$request->get('numdoc'))
                ->where('prov_id',$proveedor->prov_id)
                ->where('est_compra','Registrado')
                ->get();

                if(count($val_comp) > 0){
                    if($request->ajax()) {
                        return response()->json(['error' => 'EL DOCUMENTO YA EXISTE']);
                    }
                }
          
            }

        }
       




        

        $buscre = DB::tABLE('credito_dias')
        ->where('cre_dia_id',$estadopago)
        ->first();

        

       


        $compras = new compras_cabecera;
        $compras->com_doc_ser = $request->get('serdoc');
        $compras->com_doc_num = $request->get('numdoc');
        $compras->com_fec = $request->get('fecEmi');
        //$compras->com_fec_ven = $request->get('fecVen');
        $compras->cre_dia_id = $estadopago;
        $compras->id_turno = Auth::user()->id_turno;
        $compras->com_fec_ing = $request->get('fecIng');
        $compras->com_cab_igv = $igv;
        $compras->subtot_com = $subtotal;
        $compras->cod_tip_ope ='99';
        $compras->inc_igv = $inc_igv;
        $compras->gravado = $gravado;
        $compras->tdocod_ref = $request->get('tdocod_ref');
        $compras->serie_ref = $request->get('serie_ref');
        $compras->num_ref = $request->get('num_ref');
        $compras->fec_ref = $request->get('fec_ref');
        $compras->nccod = $request->get('nccod');

        if(!empty($tip_cam)){
             $compras->tip_cam = $tip_cam->CamCompra;
        }
       
        $compras->id_almacen = $almacen;
   
        $compras->mon_id = $request->get('mondoc');

    
        $compras->prov_id = $proveedor->prov_id;
        $compras->total_com = $request->get('total');
        $compras->comp_obs = $request->get('obser');
        $compras->tdocod = $request->get('cmbTdo');
        $compras->tipocompra = $request->get('tipocompra');
        $compras->id_empresa_negocio = $sucursal;
        $compras->estado_mercaderia = $estado_mercaderia;
        $compras->IdEmpresa = $rucemp;
        $compras->ser_oc = $request->get('ser_oc');
        $compras->num_oc = $request->get('num_oc');
        $compras->orden_compra = $request->get('orden_compra');

        if($buscre->cre_dia_tip=='CONTADO' || $buscre->cre_dia_tip=='PERSONALIZADO'){
            $compras->com_fec_ven = $request->get('fecVen');
        }else{
            $compras->com_fec_ven = date('Y-m-d',strtotime($request->get('fecEmi')."+ ".$buscre->cre_dia_fac." days"));
        }

        if($buscre->cre_dia_tip=='CONTADO'){
            $compras->tot_con = $request->get('total');

            $compras->tot_cre = '0';
        }else{
            $compras->tot_cre = $request->get('total');

            $compras->tot_con = '0';
        }

        $compras->save();


        self::generar_codigo_movimiento($compras->com_cab_id);


   //     dd($codpro);

    foreach($codpro as $index => $pro ){

            $buspro = productos::findOrFail($pro); 
            $buspro->vencimiento = $vencimiento[$index];
            $buspro->lote = $lote[$index];
            $buspro->lab_id = $laboratorio[$index];
          //  $buspro->costo_total = $preciocosto[$index];
          //  $buspro->flete = $fleteund[$index];
           
          //  $buspro->costo = $preuni[$index];

            if(empty($buspro->pro_rel)){
                $id = $pro;
            }else{
                $id = $buspro->pro_rel;
            }

       

                 $contreg = DB::tABLE('producto_stock')
            ->where('IdProducto',$id)
            ->where('id_almacen',$almacen)
            ->where('id_empresa_negocio',$sucursal)
            ->count();

            $busprostock= DB::tABLE('producto_stock')
            ->where('IdProducto',$id)
            ->where('id_almacen',$almacen)
            ->where('id_empresa_negocio',$sucursal)
            ->first();


            if($request->get('nccod')=='01'){

                if($contreg == '0'){

                    $stock = ($cantidades[$index]*$buspro->factor);
                    DB::tABLE('producto_stock')
                    ->insert([
                        'stock'=>$stock,
                        'IdProducto'=>$id,
                        'id_almacen'=>$almacen,
                        'id_empresa_negocio'=>$sucursal
                    ]);
                    
                   $sto_ini ='0';

                }else{

                     $stock = $busprostock->stock-($cantidades[$index]*$buspro->factor);

                     DB::tABLE('producto_stock')
                     ->where('id_almacen',$almacen)
                     ->where('IdProducto',$id)
                     ->where('id_empresa_negocio',$sucursal)
                     ->update([
                        'stock'=>$stock,
                    ]);

                    $sto_ini = $busprostock->stock_inicial;
              
                }
       
            }
        


            $buspro->update();

            $compras_det = new compras_detalle;
            $compras_det->pro_id = $buspro->IdProducto;
            $compras_det->com_det_factor = $buspro->factor;
            $compras_det->pre_uni = $preuni[$index];
            $compras_det->val_uni = $val_uni[$index];
            $compras_det->total= $vtot[$index];
             $compras_det->com_det_subtot= $subtotdet[$index];
            $compras_det->vencimiento = $vencimiento[$index];
            $compras_det->lote = $lote[$index];
            $compras_det->cantidad= $cantidades[$index];
            $compras_det->pro_id= $buspro->IdProducto;
            $compras_det->ume_cod = $buspro->umecod;
            $compras_det->com_cab_id= $compras->com_cab_id;
            $compras_det->tip_igv = $buspro->tigcod;
            $compras_det->IdEmpresa= $rucemp;
            $compras_det->IdProducto_rel= $id;
            $compras_det->id_almacen_pro = $almacen;
            $compras_det->flete = $flete[$index];
            $compras_det->flete_und = $fleteund[$index];
            $compras_det->precio_costo = $preciocosto[$index];
         //   $compras_det->com_det_stock = $stock;
           // $compras_det->com_det_stock_inicial = $busprostock->stock_inicial;
            $compras_det->save();

            
            if($request->get('nccod')=='01' || $request->get('nccod')=='07' || $request->get('nccod')=='06'){

                DB::tABLE('movimientos_productos')->insert([

                    'IdProducto'=>$id,
                    'precio'=>'',
                    'cantidad'=>$cantidades[$index]*$buspro->factor,
                    'costo'=>$preuni[$index],
                    'cliente'=>$proveedor->prov_raz,
                    'cod_tip_ope'=>'99',
                    'descripcion'=>'NOTA_CREDITO_COMPRA',
                    'mov_cab_id'=>'',
                    'mov_tip'=>'E',
                    'stock'=>$stock,
                    'IdProducto_rel'=>$id,
                    'IdCpe_cabecera'=>'',
                    'com_cab_id'=>$compras->com_cab_id,
                    'stock_inicial'=>'',
                    'serie'=>$compras->com_doc_ser,
                    'numero'=>$compras->com_doc_num,
                    'tdocod'=>$compras->tdocod,
                    'tipo'=>'3',
                    'id_empresa_negocio'=>$sucursal,
                    'id_almacen'=>$almacen,
                    'fecha_mov'=>$compras->com_fec,
                   

            ]);

            }
        

    
        }

        

       /* if($buscre->cre_dia_tip !='CONTADO'){
          self::registrarcuentaspagar($compras->com_cab_id);
        }*/

        if($request->ajax()) {
          return response()->json(['mensaje' => 'Compra Registrada']);
        }

        //return Redirect::to('/compras');
    
    }


    public function registrarcuentaspagar($compra){

        $compra = DB::tABLE('compras_cabecera')->where('com_cab_id',$compra)->first();

        $cuentapagar = new cuentaspagar;
        $cuentapagar->com_cab_id = $compra->com_cab_id;
        $cuentapagar->fec_ven = $compra->com_fec_ven;
        $cuentapagar->abono = $compra->tot_con;
        $cuentapagar->estado_cob = 'pendiente';
        $cuentapagar->total = $compra->tot_cre;
        $cuentapagar->saldo = $compra->tot_cre;
        $cuentapagar->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $cuentapagar->save();

        return $cuentapagar;
    }

    public function actualizarcompra(Request $request)
    {   

        $id = $request->get('id');
        $rucemp = trim(Auth::user()->IdEmpresa);
        $tdicod = $request->get('tdicod');
        $prov_num = $request->get('clinum');
        $prov_raz = $request->get('clinom');
        $sucursal = $request->get('sucursal');
       
        $estadopago = $request->get('estadopago');
        $prov_dir = $request->get('clidir');
        $prov_cor = $request->get('clicor');
        $moncod = $request->get('mondoc');

        $compras = compras_cabecera::findOrFail($id);
        $compras->com_doc_ser = $request->get('serdoc');
        $compras->com_doc_num = $request->get('numdoc');
        $compras->com_fec = $request->get('fecEmi');
        $compras->com_fec_ven = $request->get('fecVen');
        $compras->mon_id = $request->get('mondoc');

    
        $proveedor = Proveedor::FirstOrCreate(['prov_ruc'=>$prov_num,'IdEmpresa'=>$rucemp],['prov_raz'=>$prov_raz,'tdicod'=>$tdicod,'prov_cor'=>$prov_cor,'prov_dir'=>$prov_dir,'IdEmpresa'=>$rucemp]);
    
        $compras->prov_id = $proveedor->prov_id;
    
        $compras->total_com = $request->get('total');
        $compras->comp_obs = $request->get('obser');
        $compras->tdocod = $request->get('cmbTdo');
        $compras->tipocompra = $request->get('tipocompra');
        $compras->id_empresa_negocio = $sucursal;
        $compras->cre_dia_id = $estadopago;
        $compras->IdEmpresa = $rucemp;
         //$compras->local = Auth::user()->local;
        $compras->update();


        $cantidades = $request->get('cant');
        $unidades = $request->get('unid');
        $codpro = $request->get('codpro');
        $detpro = $request->get('detpro');
        $preuni = $request->get('preuni');       
        $vtot = $request->get('vtot');
        $pro_id = $request->get('pro_id');
        $cant_uni = $request->get('cant_uni');
        $com_det_id = $request->get('com_det_id');


        DB::tABLE('movimientos')->where('com_cab_id',$compras->com_cab_id)->delete();

        //REDUCIR STOCK PARA RECALCULAR
        $registros = DB::tABLE('compras_detalle')->where('com_cab_id',$id)->get();

        foreach ($registros as $reg) {
                

            $stock_prod = DB::tABLE('producto_stock')->where('IdProducto',$reg->pro_id)->where('id_empresa_negocio',$sucursal)->first();

            $stock_prod_act = DB::tABLE('producto_stock')
            ->where('pro_sto_id',$stock_prod->pro_sto_id)
            ->update(['stock'=>$stock_prod->stock-($cantidades[$index]*$IdProducto->factor)]);
            
            //$stock = $stock_prod->stock-($cantidades[$index]*$IdProducto->factor);

           
            if(!in_array($reg->com_det_id,$com_det_id)){

                DB::tABLE('compras_detalle')->where('com_det_id',$reg->com_det_id)->delete();

            }

           

        }

    foreach($codpro as $index => $pro ) {

            $IdProducto = DB::tABLE('productos')
            ->WHERE('IdProducto',$pro)
            ->where('IdEmpresa',$rucemp)->first();

            if(!empty($com_det_id[$index])){

                $compras_det = compras_detalle::findOrFail($com_det_id[$index]);
                $compras_det->pro_id = $IdProducto->IdProducto;
                $compras_det->pre_uni = $preuni[$index];
                $compras_det->total= $vtot[$index];
                $compras_det->cantidad= $cantidades[$index];
                $compras_det->pro_id= $IdProducto->IdProducto;
                $compras_det->ume_cod = $IdProducto->umecod;
                $compras_det->com_cab_id= $compras->com_cab_id;
                $compras_det->tip_igv = $IdProducto->tigcod;
                $compras_det->IdEmpresa= $rucemp;
                $compras_det->update();

            }else{

                $compras_det = new compras_detalle;
                $compras_det->pro_id = $IdProducto->IdProducto;
                $compras_det->pre_uni = $preuni[$index];
                $compras_det->total= $vtot[$index];
                $compras_det->cantidad= $cantidades[$index];
                $compras_det->pro_id= $IdProducto->IdProducto;
                $compras_det->ume_cod = $IdProducto->umecod;
                $compras_det->com_cab_id= $compras->com_cab_id;
                $compras_det->tip_igv = $IdProducto->tigcod;
                $compras_det->IdEmpresa= $rucemp;
                $compras_det->save();

            }
           

            $stockprod = DB::tABLE('producto_stock')->where('IdProducto',$reg->pro_id)->where('id_empresa_negocio',$sucursal)->first();

            $stockprod_act = DB::tABLE('producto_stock')
            ->where('pro_sto_id',$stock_prod->pro_sto_id)
            ->update(['stock'=>$stock_prod->stock+($cantidades[$index]*$IdProducto->factor)]);
            
          //  $stock = $stock_prod->stock-($cantidades[$index]*$IdProducto->factor);


          //  $stock_prod =productos::findOrFail($IdProducto->IdProducto);
          //  $stock_prod->stock = $stock_prod->stock+$cantidades[$index];
          //  $stock_prod->update();


            $movimientos = new movimientos;
            $movimientos->com_cab_id = $compras->com_cab_id;
            $movimientos->IdProducto = $IdProducto->IdProducto;
            $movimientos->mov_fec = $compras->com_fec; 
            $movimientos->cantidad = $cantidades[$index];
            $movimientos->mov_tip = 'I';
            $movimientos->mov_mot ='COMPRA';
            $movimientos->id_empresa_negocio = $req;
            $movimientos->unidad = $IdProducto->umecod;
            $movimientos->codpro = $IdProducto->procod;
            $movimientos->descripcion = $detpro[$index];
            $movimientos->comprobante = $request->get('serdoc').'-'.$request->get('serdoc');
            $movimientos->serie =$request->get('serdoc');
            $movimientos->numero =$request->get('numdoc');
            $movimientos->IdUsuario = Auth::user()->IdUsuario;
            $movimientos->stockmov = $stock_prod->stock;
            $movimientos->id_empresa_negocio = Auth::user()->id_empresa_negocio;
            $movimientos->save();


        }

        return Redirect::to('/compras');


    }

      public function actualizar_compra(Request $request)
    {   


         DB::beginTransaction();

    try{


        if($request->get('mondoc')=='USD' && ($request->get('tip_cam')=='0' || empty($request->get('tip_cam')))){
            return response()->json(['mensaje' => 'INGRESAR TIPO DE CAMBIO PARA COMPRAS EN DÓLARES']);
        }

        $id = $request->get('id');
         $tigcod = $request->get('tigcod');
        $rucemp = trim(Auth::user()->IdEmpresa);
        $tdicod = $request->get('tdicod');
        $prov_num = $request->get('clinum');
        $prov_raz = $request->get('clinom');
        $sucursal = $request->get('sucursal');
        $almacen = $request->get('almacen');
        $estado_mercaderia = $request->get('estado_mercaderia');
        $vencimiento = $request->get('vencimiento');
        $val_uni =  $request->get('costosigv');
        $igv = $request->get('igv');
        $subtotal = $request->get('subtotal');
        $laboratorio = $request->get('laboratorio');
        $lote = $request->get('lote');
        $id_almacen_pro = $request->get('id_almacen_pro');
        $flete = $request->get('flete');
        $fleteund = $request->get('fleteund');
        $preciocosto = $request->get('preciocosto');
        $inc_igv = $request->get('inc_igv');
        $gravado = $request->get('gravado');
         $subtotdet = $request->get('subtot');

            $tip_cam = $request->get('tip_cam');

        $estadopago = $request->get('estadopago');

        $buscre = DB::tABLE('credito_dias')
        ->where('cre_dia_id',$estadopago)
        ->first();

        $prov_dir = $request->get('clidir');
        $prov_cor = $request->get('clicor');
        $moncod = $request->get('mondoc');

        $compras = compras_cabecera::findOrFail($id);

        $estado_mercaderia_ant = $compras->estado_mercaderia;
        $compras->com_doc_ser = $request->get('serdoc');
        $compras->id_almacen = $almacen;
        $compras->estado_mercaderia = $estado_mercaderia;
         $compras->com_grav = $request->get('gravada');
        $compras->com_exo = $request->get('exonerada');
        $compras->com_inaf = $request->get('inafecta');
      
        $compras->com_doc_num = $request->get('numdoc');
        $compras->com_fec = $request->get('fecEmi');
        $compras->com_fec_ing = $request->get('fecIng');
        $compras->cre_dia_id = $estadopago;
        $compras->com_cab_igv = $igv;
        $compras->subtot_com = $subtotal;
        $compras->cod_tip_ope = '02';
        $compras->tip_cam = $tip_cam;
        
        if($buscre->cre_dia_tip=='CONTADO' || $buscre->cre_dia_tip=='PERSONALIZADO'){
            $compras->com_fec_ven = $request->get('fecVen');
        }else{
            $compras->com_fec_ven = date('Y-m-d',strtotime($request->get('fecEmi')."+ ".$buscre->cre_dia_fac." days"));
        }

        if($buscre->cre_dia_tip=='CONTADO'){
            $compras->tot_con = $request->get('total');

            $compras->tot_cre = '0';
        }else{
            $compras->tot_cre = $request->get('total');

            $compras->tot_con = '0';
        }


        $compras->mon_id = $request->get('mondoc');

    
        $proveedor = Proveedor::FirstOrCreate(['prov_ruc'=>$prov_num,'IdEmpresa'=>$rucemp],['prov_raz'=>$prov_raz,'tdicod'=>$tdicod,'prov_cor'=>$prov_cor,'prov_dir'=>$prov_dir,'IdEmpresa'=>$rucemp]);
    
        $compras->prov_id = $proveedor->prov_id;
    
        $compras->total_com = $request->get('total');
        $compras->comp_obs = $request->get('obser');
        $compras->tdocod = $request->get('cmbTdo');
        $compras->tipocompra = $request->get('tipocompra');
        $compras->id_empresa_negocio = $sucursal;
        $compras->orden_compra = $request->get('orden_compra');
        $compras->IdEmpresa = $rucemp;
        $compras->inc_igv = $inc_igv;
        $compras->gravado = $gravado;

         //$compras->local = Auth::user()->local;
        $compras->update();


        $cantidades = $request->get('cant');
        $unidades = $request->get('unid');
        $codpro = $request->get('codpro');
        $detpro = $request->get('detpro');
        $preuni = $request->get('preuni');       
        $vtot = $request->get('vtot');
        $pro_id = $request->get('pro_id');
        $cant_uni = $request->get('cant_uni');
        $com_det_id = $request->get('com_det_id');


        if($estado_mercaderia =='1' ){


        

             DB::tABLE('movimientos_productos')->where('com_cab_id',$compras->com_cab_id)->delete();
      
        //REDUCIR STOCK PARA RECALCULAR


        $registros = DB::tABLE('compras_detalle')->where('com_cab_id',$id)->get();


            foreach ($registros as $index => $reg) {
                
            $buspro = DB::tABLE('productos')
            ->WHERE('IdProducto',$reg->pro_id)
            ->first();

          
            try{
                   if(empty($buspro->pro_rel)){

                $idpro = $buspro->IdProducto;
                
            }else{

                 $idpro = $buspro->pro_rel;

            }

            }catch(\Exception $e){
                dd($reg->pro_id);
            }
         

           if($estado_mercaderia_ant =='1' ){

            $stock_prod = DB::tABLE('producto_stock')
            ->where('IdProducto',$idpro)
            ->where('id_empresa_negocio',$sucursal)
            ->where('id_almacen',$reg->id_almacen_pro)
            ->first();


            $stock_prod_act = DB::tABLE('producto_stock')
              ->where('IdProducto',$idpro)
            ->where('id_empresa_negocio',$sucursal)
            ->where('id_almacen',$reg->id_almacen_pro)
            ->update(['stock'=>$stock_prod->stock-($reg->cantidad*$buspro->factor)]);
 
           }
        }

      
        

    }
     
       
    DB::tABLE('compras_detalle')->where('com_cab_id',$id)->delete();

    foreach($pro_id as $index1 => $pro ) {

            $IdProducto = productos::findOrFail($pro); 
            $IdProducto->vencimiento = $vencimiento[$index1];
            $IdProducto->lote = $lote[$index1];
            $IdProducto->lab_id = $laboratorio[$index1];


            if($request->get('mondoc')=='USD'){
                $IdProducto->costo_total = $preciocosto[$index]*$compras->tip_cam;
                $IdProducto->flete = $fleteund[$index]*$compras->tip_cam;
                $IdProducto->costo = $preuni[$index]*$compras->tip_cam;
            }else{
                $IdProducto->costo_total = $preciocosto[$index];
                $IdProducto->flete = $fleteund[$index];
                $IdProducto->costo = $preuni[$index];
            }
          
          
            
            if(empty($IdProducto->pro_rel)){

                $id_pro = $IdProducto->IdProducto;
                
            }else{

                 $id_pro = $IdProducto->pro_rel;

            }

                 if($estado_mercaderia=='1'){

                    
            $stockprod = DB::tABLE('producto_stock')
            ->where('IdProducto',$id_pro)
            ->where('id_empresa_negocio',$sucursal)
            ->where('id_almacen',$almacen)
            ->first();


            $sto_ini =$stockprod->stock_inicial;

            $stock = $stockprod->stock+($cantidades[$index1]*$IdProducto->factor);


            $stockprod_act = DB::tABLE('producto_stock')
             ->where('IdProducto',$id_pro)
            ->where('id_empresa_negocio',$sucursal)
            ->where('id_almacen',$almacen)
            ->update(['stock'=>$stockprod->stock+($cantidades[$index1]*$IdProducto->factor)]);
            
           
            try{

                   $costopromedio = (($IdProducto->costo * $stockprod->stock)+(($cantidades[$index1]*$IdProducto->factor)*$preuni[$index1])) / (($cantidades[$index1]*$IdProducto->factor)+$stockprod->stock); 
                   

            }catch(\Exception $e){

            }

           

           

        }
            $IdProducto->update();

         


                $compras_det = new compras_detalle;
                $compras_det->pro_id = $IdProducto->IdProducto;
                $compras_det->pre_uni = $preuni[$index1];
                $compras_det->val_uni = $val_uni[$index1];
                 $compras_det->com_det_subtot= $subtotdet[$index1];
                $compras_det->total= $vtot[$index1];
                $compras_det->cantidad= $cantidades[$index1];
                $compras_det->pro_id= $IdProducto->IdProducto;
                $compras_det->ume_cod = $IdProducto->umecod;
                $compras_det->com_cab_id= $compras->com_cab_id;
                $compras_det->tip_igv = $IdProducto->tigcod;
                $compras_det->IdEmpresa= $rucemp;
              // $compras_det->com_det_stock = $stock;
                $compras_det->com_det_factor = $IdProducto->factor;
                $compras_det->IdProducto_rel = $IdProducto->pro_rel;
                $compras_det->id_almacen_pro = $almacen;
                $compras_det->flete = $flete[$index1];
                $compras_det->flete_und = $fleteund[$index1];
                 $compras_det->tip_igv = $tigcod[$index1];
                $compras_det->precio_costo = $preciocosto[$index1];
               
                $compras_det->save();

            if($estado_mercaderia=='1'){
                  DB::tABLE('movimientos_productos')->insert([

                    'IdProducto'=>$id_pro,
                    'precio'=>'',
                    'cantidad'=>$cantidades[$index1]*$IdProducto->factor,
                    'costo'=>$preuni[$index1],
                    'cliente'=>$proveedor->prov_raz,
                    'cod_tip_ope'=>'02',
                    'descripcion'=>'COMPRA',
                    'mov_cab_id'=>'',
                    'mov_tip'=>'I',
                    'stock'=>$stock,
                    'IdProducto_rel'=>$id_pro,
                    'IdCpe_cabecera'=>'',
                    'com_cab_id'=>$compras->com_cab_id,
                    'stock_inicial'=>$sto_ini,
                    'serie'=>$compras->com_doc_ser,
                    'numero'=>$compras->com_doc_num,
                    'tdocod'=>$compras->tdocod,
                    'tipo'=>'2',
                    'id_empresa_negocio'=>$sucursal,
                    'id_almacen'=>$almacen,
                    'fecha_mov'=>$compras->com_fec,
                   

                    ]);

                    $mov_cal_stock = new Almacen();
                $mov_cal_stock->movimiento_calcular_stock($id_pro,$almacen);

              }

          



        }


        DB::commit();

          if($request->ajax()) {
              return response()->json(['mensaje' => 'Compra Modificada']);
            }



        }catch(\Exception $e){


         DB::rollback();

         return response()->json(['error'=>$e->getMessage()]);

    }
       


    }
    


         public function actualizar_nota_credito_compra(Request $request)
    {   

        $id = $request->get('id');
        $rucemp = trim(Auth::user()->IdEmpresa);
        $tdicod = $request->get('tdicod');
        $prov_num = $request->get('clinum');
        $prov_raz = $request->get('clinom');
        $sucursal = $request->get('sucursal');
        $almacen = $request->get('almacen');
        $estado_mercaderia = $request->get('estado_mercaderia');
        $vencimiento = $request->get('vencimiento');
        $val_uni =  $request->get('costosigv');
        $igv = $request->get('igv');
        $subtotal = $request->get('subtotal');
        $laboratorio = $request->get('laboratorio');
        $lote = $request->get('lote');
        $id_almacen_pro = $request->get('id_almacen_pro');
        $flete = $request->get('flete');
        $fleteund = $request->get('fleteund');
        $preciocosto = $request->get('preciocosto');
        $inc_igv = $request->get('inc_igv');
        $gravado = $request->get('gravado');
         $subtotdet = $request->get('subtot');

        $dat_comp = compras_cabecera::findOrFail($id);
        $ant_nccod = $dat_comp->nccod;
        
        $estadopago = $request->get('estadopago');

        $buscre = DB::tABLE('credito_dias')
        ->where('cre_dia_id',$estadopago)
        ->first();

        $prov_dir = $request->get('clidir');
        $prov_cor = $request->get('clicor');
        $moncod = $request->get('mondoc');

        $compras = compras_cabecera::findOrFail($id);

        $estado_mercaderia_ant = $compras->estado_mercaderia;
        $compras->com_doc_ser = $request->get('serdoc');
        $compras->id_almacen = $almacen;
        $compras->estado_mercaderia = $estado_mercaderia;
       
      
        $compras->com_doc_num = $request->get('numdoc');
        $compras->com_fec = $request->get('fecEmi');
        $compras->com_fec_ing = $request->get('fecIng');
        $compras->cre_dia_id = $estadopago;
        $compras->com_cab_igv = $igv;
        $compras->subtot_com = $subtotal;
        $compras->cod_tip_ope = '02';
        $compras->tdocod_ref = $request->get('tdocod_ref');
        $compras->serie_ref = $request->get('serie_ref');
        $compras->num_ref = $request->get('num_ref');
        $compras->fec_ref = $request->get('fec_ref');
        $compras->nccod = $request->get('nccod');
        
        if($buscre->cre_dia_tip=='CONTADO' || $buscre->cre_dia_tip=='PERSONALIZADO'){
            $compras->com_fec_ven = $request->get('fecVen');
        }else{
            $compras->com_fec_ven = date('Y-m-d',strtotime($request->get('fecEmi')."+ ".$buscre->cre_dia_fac." days"));
        }

        if($buscre->cre_dia_tip=='CONTADO'){
            $compras->tot_con = $request->get('total');

            $compras->tot_cre = '0';
        }else{
            $compras->tot_cre = $request->get('total');

            $compras->tot_con = '0';
        }


        $compras->mon_id = $request->get('mondoc');

    
        $proveedor = Proveedor::FirstOrCreate(['prov_ruc'=>$prov_num,'IdEmpresa'=>$rucemp],['prov_raz'=>$prov_raz,'tdicod'=>$tdicod,'prov_cor'=>$prov_cor,'prov_dir'=>$prov_dir,'IdEmpresa'=>$rucemp]);
    
        $compras->prov_id = $proveedor->prov_id;
    
        $compras->total_com = $request->get('total');
        $compras->comp_obs = $request->get('obser');
        $compras->tdocod = $request->get('cmbTdo');
        $compras->tipocompra = $request->get('tipocompra');
        $compras->id_empresa_negocio = $sucursal;
        $compras->orden_compra = $request->get('orden_compra');
        $compras->IdEmpresa = $rucemp;
        $compras->inc_igv = $inc_igv;
        $compras->gravado = $gravado;

         //$compras->local = Auth::user()->local;
        $compras->update();


        $cantidades = $request->get('cant');
        $unidades = $request->get('unid');
        $codpro = $request->get('codpro');
        $detpro = $request->get('detpro');
        $preuni = $request->get('preuni');       
        $vtot = $request->get('vtot');
        $pro_id = $request->get('pro_id');
        $cant_uni = $request->get('cant_uni');
        $com_det_id = $request->get('com_det_id');




        

        DB::tABLE('movimientos_productos')->where('com_cab_id',$compras->com_cab_id)->delete();
      
        //REDUCIR STOCK PARA RECALCULAR


        $registros = DB::tABLE('compras_detalle')->where('com_cab_id',$id)->get();


        if($request->get('nccod')=='01' || $request->get('nccod')=='07' || $request->get('nccod')=='06'){

              foreach ($registros as $index => $reg) {
                
                $buspro = DB::tABLE('productos')
                ->WHERE('IdProducto',$reg->pro_id)
                ->first();

              
                try{
                       if(empty($buspro->pro_rel)){

                    $idpro = $buspro->IdProducto;
                    
                }else{

                     $idpro = $buspro->pro_rel;

                }

                }catch(\Exception $e){
                    //dd($reg->pro_id);
                }
             


                $stock_prod = DB::tABLE('producto_stock')
                ->where('IdProducto',$idpro)
                ->where('id_empresa_negocio',$sucursal)
                ->where('id_almacen',$reg->id_almacen_pro)
                ->first();


                $stock_prod_act = DB::tABLE('producto_stock')
                  ->where('IdProducto',$idpro)
                ->where('id_empresa_negocio',$sucursal)
                ->where('id_almacen',$reg->id_almacen_pro)
                ->update(['stock'=>$stock_prod->stock+($reg->cantidad*$buspro->factor)]);
     
            
            }

 
        }
      
      
        


     
       
    DB::tABLE('compras_detalle')->where('com_cab_id',$id)->delete();

    foreach($pro_id as $index1 => $pro ) {

            $IdProducto = productos::findOrFail($pro); 
            $IdProducto->vencimiento = $vencimiento[$index1];
            $IdProducto->lote = $lote[$index1];
            $IdProducto->lab_id = $laboratorio[$index1];
           // $IdProducto->costo_total = $preciocosto[$index];
          //  $IdProducto->flete = $fleteund[$index];
          //  $IdProducto->costo = $preuni[$index];
          
         
            if(empty($IdProducto->pro_rel)){

                $id_pro = $IdProducto->IdProducto;
                
            }else{

                 $id_pro = $IdProducto->pro_rel;

            }

               

            if($request->get('nccod')=='01'){
                 $stockprod = DB::tABLE('producto_stock')
                ->where('IdProducto',$id_pro)
                ->where('id_empresa_negocio',$sucursal)
                ->where('id_almacen',$almacen)
                ->first();


                $sto_ini =$stockprod->stock_inicial;

                $stock = $stockprod->stock-($cantidades[$index1]*$IdProducto->factor);


                $stockprod_act = DB::tABLE('producto_stock')
                 ->where('IdProducto',$id_pro)
                ->where('id_empresa_negocio',$sucursal)
                ->where('id_almacen',$almacen)
                ->update(['stock'=>$stockprod->stock-($cantidades[$index1]*$IdProducto->factor)]);
                
            
            }      
          
    
            $IdProducto->update();

         


                $compras_det = new compras_detalle;
                $compras_det->pro_id = $IdProducto->IdProducto;
                $compras_det->pre_uni = $preuni[$index1];
                $compras_det->val_uni = $val_uni[$index1];
                 $compras_det->com_det_subtot= $subtotdet[$index1];
                $compras_det->total= $vtot[$index1];
                $compras_det->cantidad= $cantidades[$index1];
                $compras_det->pro_id= $IdProducto->IdProducto;
                $compras_det->ume_cod = $IdProducto->umecod;
                $compras_det->com_cab_id= $compras->com_cab_id;
                $compras_det->tip_igv = $IdProducto->tigcod;
                $compras_det->IdEmpresa= $rucemp;
              // $compras_det->com_det_stock = $stock;
                $compras_det->com_det_factor = $IdProducto->factor;
                $compras_det->IdProducto_rel = $IdProducto->pro_rel;
                $compras_det->id_almacen_pro = $almacen;
                $compras_det->flete = $flete[$index1];
                $compras_det->flete_und = $fleteund[$index1];
                $compras_det->precio_costo = $preciocosto[$index1];
               
                $compras_det->save();

                 if($request->get('nccod')=='01'){
                       DB::tABLE('movimientos_productos')->insert([

                    'IdProducto'=>$id_pro,
                    'precio'=>'',
                    'cantidad'=>$cantidades[$index1]*$IdProducto->factor,
                    'costo'=>$preuni[$index1],
                    'cliente'=>$proveedor->prov_raz,
                    'cod_tip_ope'=>'99',
                    'descripcion'=>'NOTA_CREDITO_COMPRA',
                    'mov_cab_id'=>'',
                    'mov_tip'=>'E',
                    'stock'=>$stock,
                    'IdProducto_rel'=>$id_pro,
                    'IdCpe_cabecera'=>'',
                    'com_cab_id'=>$compras->com_cab_id,
                    'stock_inicial'=>'',
                    'serie'=>$compras->com_doc_ser,
                    'numero'=>$compras->com_doc_num,
                    'tdocod'=>$compras->tdocod,
                    'tipo'=>'3',
                    'id_empresa_negocio'=>$sucursal,
                    'id_almacen'=>$almacen,
                    'fecha_mov'=>$compras->com_fec,
                   

                    ]);
                 }
               
            

          



        }

         if($request->ajax()) {
              return response()->json(['mensaje' => 'Compra Modificada']);
            }



    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
     
        $compras = compras_cabecera::findOrFail($id);
        $compras->est_compra = 'Eliminado';
        $compras->usu_elimino = Auth::user()->IdUsuario;
        $compras->update();

        $detalle = compras_detalle::where('com_cab_id',$id)->get();



        foreach ($detalle as $det) {
            

            $producto = productos::findOrFail($det->pro_id);


            $stock_prod = DB::tABLE('producto_stock')
            ->where('IdProducto',$det->pro_id)
            ->where('id_empresa_negocio',$compras->id_empresa_negocio)
            ->where('id_almacen',$compras->id_almacen)
            ->first();
        

            $stock_prod = DB::tABLE('producto_stock')
            ->where('pro_sto_id',$stock_prod->pro_sto_id)
            ->update(['stock'=>$stock_prod->stock-($det->cantidad*$producto->factor)]);

        }

       DB::tABLE('movimientos_productos')->where('com_cab_id',$id)->delete();


        return Redirect::to('/compras');
    }

    public function detallecompras($id,$tipo){
        $rucemp = trim(Auth::user()->IdEmpresa);

        if($tipo =='1'){

         $compra = DB::tABLE('compras_detalle as cd')
        ->join('unidad_medida as um','um.umecod','cd.ume_cod')
        ->join('productos as p','p.IdProducto','cd.pro_id')
        ->leftjoin('laboratorio','laboratorio.lab_id','p.lab_id')
        ->leftjoin('marcas','marcas.mar_id','p.marca')
        ->where('com_cab_id',$id)->get();

        }else{
            
         $compra = DB::tABLE('compras_detalle as cd')
        ->join('unidad_medida as um','um.umecod','cd.ume_cod')
        ->where('com_cab_id',$id)->get();
        }
     



        return view('empresas.compras.detalles',compact('compra'));
    }

    public function generar_codigo_movimiento($com_cab_id){

        $bus_cpe = DB::tABLE('compras_cabecera')->where('com_cab_id',$com_cab_id)->First();

        $bus_suc = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$bus_cpe->id_empresa_negocio)->first();

        $gen_cod = DB::tABLE('compras_cabecera')->where('com_cab_id',$com_cab_id)->update(['cod_mov'=>'MOV'.$bus_suc->cod_suc.$com_cab_id]);

        return $gen_cod;
    }


      public function descargar($id)
    {

      $cabecera = DB::tABLE('compras_cabecera')->where('com_cab_id',$id)->first();

      $file = $cabecera->com_cab_id.'-'.$cabecera->com_doc_ser.'-'.$cabecera->com_doc_num.'.pdf';

      $rutpdfile = public_path().'/pdf/';

      if(!file_exists($file)){

        self::generarpdfcompra($id);

         $headers = array(
              'Content-Type: application/pdf',
            );

        return response()->download($rutpdfile.$file);

      }elseif(file_exists($file)){

        unlink($file);
        self::generarpdfcompra($id);

        $headers = array(
              'Content-Type: application/pdf',
            );

        return response()->download($rutpdfile.$file);

      }
         return Redirect::to('/compras');
      
    }



      public function descargar_excel($id)
    {

          $cabpdf = DB::tABLE('compras_cabecera')
      ->leftjoin('moneda as mon','compras_cabecera.mon_id','=','mon.moncod')
      ->leftjoin('proveedor','proveedor.prov_id','compras_cabecera.prov_id')
      ->leftjoin('tipo_documento','tipo_documento.tdocod','compras_cabecera.tdocod')
      ->leftjoin('tipo_documento_identidad','tipo_documento_identidad.tdicod','proveedor.tdicod')
      ->leftjoin('credito_dias','credito_dias.cre_dia_id','compras_cabecera.cre_dia_id')
      ->where('com_cab_id',$id)
      ->first();

       $detpdf = DB::tABLE('compras_detalle')
       ->leftjoin('unidad_medida','unidad_medida.umecod','compras_detalle.ume_cod')
       ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
       ->where('com_cab_id',$id)
       ->get();

       $cab = DB::tABLE('compras_cabecera') ->where('com_cab_id',$id)
      ->first();

      $sucursal = DB::tABLE('empresa_negocios')
      ->where('id_empresa_negocio',$cab->id_empresa_negocio)
      ->first();
    
      $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);

      $nompdffile=$cabpdf->com_cab_id.'-'.$cabpdf->com_doc_ser.'-'.$cabpdf->com_doc_num.'.pdf'; 

      $moneda = DB::tABLE('moneda')->where('moncod','=',$cabpdf->mon_id)->first();


                 Excel::create('REGISTRO_COMPRA', function($excel) use ($cabpdf,$detpdf,$empresa,$sucursal,$moneda) {

                        $excel->sheet('REGISTRO_COMPRAS', function($sheet) use ($cabpdf,$detpdf,$empresa,$sucursal,$moneda) {

                  
                            
                                  $sheet->loadView('formatos_reportes_excel.registro_compra',compact('cabpdf','detpdf','empresa','sucursal','moneda'));
                          
                                

                        });

                    })->export('xlsx'); 

  

      
    }



   public function generarpdfcompra($id){


      $rutapdf = public_path().'/pdf/';

      $cabpdf = DB::tABLE('compras_cabecera')
      ->leftjoin('moneda as mon','compras_cabecera.mon_id','=','mon.moncod')
      ->leftjoin('proveedor','proveedor.prov_id','compras_cabecera.prov_id')
      ->leftjoin('tipo_documento','tipo_documento.tdocod','compras_cabecera.tdocod')
      ->leftjoin('tipo_documento_identidad','tipo_documento_identidad.tdicod','proveedor.tdicod')
      ->leftjoin('credito_dias','credito_dias.cre_dia_id','compras_cabecera.cre_dia_id')
      ->where('com_cab_id',$id)
      ->first();

       $detpdf = DB::tABLE('compras_detalle')
       ->leftjoin('unidad_medida','unidad_medida.umecod','compras_detalle.ume_cod')
       ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
       ->where('com_cab_id',$id)
       ->get();

       $cab = DB::tABLE('compras_cabecera') ->where('com_cab_id',$id)
      ->first();

      $sucursal = DB::tABLE('empresa_negocios')
      ->where('id_empresa_negocio',$cab->id_empresa_negocio)
      ->first();
    
     $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);

    $nompdffile=$cabpdf->com_cab_id.'-'.$cabpdf->com_doc_ser.'-'.$cabpdf->com_doc_num.'.pdf'; 

    $moneda = DB::tABLE('moneda')->where('moncod','=',$cabpdf->mon_id)->first();


    $view = \View::make('formatos_comprobantes.compras', compact('cabpdf','detpdf','empresa','sucursal','moneda'));
 
  
                  
      $pdf = \App::make('dompdf.wrapper');
      $contenido = $view->render();
      $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

      return $nompdffile;

}

}
