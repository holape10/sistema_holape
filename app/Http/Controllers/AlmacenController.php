<?php

namespace MasterSoft\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MasterSoft\compras_cabecera;
use MasterSoft\compras_detalle;
use MasterSoft\proveedor;
use MasterSoft\movimientos;
use MasterSoft\User;
use MasterSoft\productos;
use MasterSoft\Almacenes;
use MasterSoft\Empresa;
use MasterSoft\EmpresaNegocios;
use MasterSoft\insumos;
use MasterSoft\movimientosinsumos;
use MasterSoft\guias_remision;
use MasterSoft\guias_remision_detalle;
use MasterSoft\Modelos\Almacen;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;
use Carbon;

class AlmacenController extends Controller
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

    public function migrar_ajustes(){

        $movimientos = DB::tABLE('movimientos')->where('mov_mot','Ingreso Ajuste')->orwhere('mov_mot','Salida Ajuste')->get();

        foreach ($movimientos as $mov) {

            if($mov->mov_tip=='E'){

                  DB::tABLE('movimientos_productos')->insert([

                    'IdProducto'=>$mov->IdProducto,
                    'precio'=>'',
                    'cantidad'=>$mov->cantidad,
                    'costo'=>'0',
                    'mov_cab_id'=>'',
                    'stock'=>'',
                    'IdProducto_rel'=>$mov->IdProducto,
                    'IdCpe_cabecera'=>'',
                    'com_cab_id'=>'',
                    'stock_inicial'=>'',
                    'serie'=>'',
                    'numero'=>'',
                    'tdocod'=>'84',
                    'tipo'=>'3',
                    'mov_tip'=>'E',
                    'descripcion'=>'EGRESO_AJUSTE',
                    'id_empresa_negocio'=>$mov->id_empresa_negocio,
                    'id_almacen'=>'37',
                    'fecha_mov'=>$mov->mov_fec,
                   

            ]);



            }elseif($mov->mov_tip=='I'){
                 DB::tABLE('movimientos_productos')->insert([

                    'IdProducto'=>$mov->IdProducto,
                    'precio'=>'',
                    'cantidad'=>$mov->cantidad,
                    'costo'=>'0',
                    'mov_cab_id'=>'',
                    'stock'=>'',
                    'IdProducto_rel'=>$mov->IdProducto,
                    'IdCpe_cabecera'=>'',
                    'com_cab_id'=>'',
                    'stock_inicial'=>'',
                    'serie'=>'',
                    'numero'=>'',
                    'tdocod'=>'83',
                    'tipo'=>'2',
                    'mov_tip'=>'I',
                    'descripcion'=>'INGRESO_AJUSTE',
                    'id_empresa_negocio'=>$mov->id_empresa_negocio,
                    'id_almacen'=>'37',
                    'fecha_mov'=>$mov->mov_fec,
                   

            ]);
            }   

        }
    }
    public function documentos_transferencias(Request $request){

        $negocios = DB::tABLE('empresa_negocios')->get();
        $sucursal = $request->get('sucursal');
        $tipo = $request->geT('tipo');
        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');
        $tipodocumentos = DB::tABLE('tipo_documento')->where('formulario','5')->get();


        if(empty($sucursal)){
            $sucursal = $negocios->first()->id_empresa_negocio;
        }

        if(empty($fecin)){

          $fecin = now()->modify('first day of this month')->format('Y-m-d');
          $fecfin = now()->modify('last day of this month')->format('Y-m-d');

        }

        if(empty($tipo)){
            $tipo = $tipodocumentos->first()->tdocod;
        }

    

        $documentos = DB::tABLE('movimientos_cabecera as mc')
        ->select('serdoc','numdoc','osuc.tipo_negocio as suc_origen','dsuc.tipo_negocio as suc_destino','oalm.descripcion as alm_origen','dalm.descripcion as alm_destino','tdodes','fecha','mc.estado','mov_cab_id','mc.IdCpe_guia','guias_remision.serieguia','guias_remision.numeroguia')
        ->leftjoin('tipo_documento','tipo_documento.tdocod','mc.tdocod')
        ->leftjoin('empresa_negocios as osuc','osuc.id_empresa_negocio','mc.part_suc')
        ->leftjoin('empresa_negocios as dsuc','dsuc.id_empresa_negocio','mc.des_suc')
        ->leftjoin('almacenes as oalm','oalm.id_almacen','mc.part_alm')
        ->leftjoin('almacenes as dalm','dalm.id_almacen','mc.des_alm') 
        ->leftjoin('guias_remision','guias_remision.IdCpe_guia','mc.IdCpe_guia')
        ->where(function ($query) use ($sucursal,$tipo) {
          if(!empty($sucursal)){
            if($tipo=='81'){

                $query->Where('mc.part_suc',$sucursal);

            }elseif($tipo=='82'){
                $query->Where('mc.des_suc',$sucursal);
            }
           
          }
        })
        ->where(function ($query1) use ($tipo) {
          if(!empty($tipo)){
             $query1->Where('mc.tdocod',$tipo);
           
          }
        })
        ->where('fecha','>=',$fecin)
        ->where('fecha','<=',$fecfin)
        ->orderby('mov_cab_id','desc')
        ->paginate(100);

    
        return view('empresas.almacen.documentostransferencias',compact('tipo','negocios','sucursal','documentos','fecin','fecfin','tipodocumentos'));
    }

    public function detalle_documentos_transferencias($id){

    }

     public function index(Request $request)
    {
     
        $rucemp = trim(Auth::user()->IdEmpresa);
        $buspro = $request->get('buspro');
        $tipo = $request->get('promocion');
        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');
        $fechaini = now()->modify('first day of this month')->format('Y-m-d');
        $fechafin = now()->modify('last day of this month')->format('Y-m-d');
        $negocios = DB::tABLE('empresa_negocios')->get();
        $sucursal = $request->get('sucursal');

        $tipos_productos = DB::tABLE('tipos_productos')->get();

        if(!isset($fecin) || !isset($fecfin)){

            $fecin = $fechaini;
            $fecfin = $fechafin;

           

        } 

            $movimientos = DB::tABLE('movimientos')
            ->select('mov_fec','mov_mot','comprobante','codpro','pronom','cantidad','unidad','factor','stockmov','IdCpe_cabecera','com_cab_id','est_mov','unidad','mov_id','osuc.tipo_negocio as suc_origen','dsuc.tipo_negocio as suc_destino','oalm.descripcion as alm_origen','dalm.descripcion as alm_destino','mov_cab_id')
            ->leftjoin('productos','movimientos.IdProducto','productos.IdProducto')
            ->leftjoin('unidad_medida as um','um.umecod','movimientos.unidad')
            ->leftjoin('empresa_negocios as osuc','osuc.id_empresa_negocio','movimientos.part_suc')
            ->leftjoin('empresa_negocios as dsuc','dsuc.id_empresa_negocio','movimientos.des_suc')
            ->leftjoin('almacenes as oalm','oalm.id_almacen','movimientos.part_alm')
            ->leftjoin('almacenes as dalm','dalm.id_almacen','movimientos.des_alm')
            ->where('mov_fec','>=',$fecin)
            ->where('mov_fec','<=',$fecfin)
             ->where('movimientos.id_empresa_negocio','=',$sucursal)
            ->where(function ($query) use ($buspro,$tipo) {

                 
                     $query->where('pronom','like','%'.$buspro.'%')
                        ->orWhere('procod','=',$buspro);

                  
                 
                  })
            ->orderby('mov_id','desc')
            ->paginate(100);
       
        $proveedores = DB::tABLE('proveedor')->get();

        return view('empresas.almacen.index',compact('sucursal','negocios','movimientos','proveedores','fecin','fecfin','tipos_productos','tipo','buspro'));
    }

    public function almacenpredeterminada(Request $request, $almacen,$sucursal){

       Almacenes::where('id_empresa_negocio',$sucursal)
        ->update(['predeterminado'=>0]);

        $predeterminada = Almacenes::FindOrFail($almacen);
        $predeterminada->predeterminado = '1';
        $predeterminada->update();

        if($request->ajax()) {
          return response()->json(['mensaje' => 'Registrado']);
        }

    }


    public function listaralmacenes(Request $request,$sucursal='0'){
        
        $negocios = DB::tABLE('empresa_negocios')->get();

        if($sucursal=='0'){
            $sucursal = $negocios->first()->id_empresa_negocio;
        }

        $almacenes = Almacenes::leftjoin('empresa_negocios','empresa_negocios.id_empresa_negocio','almacenes.id_empresa_negocio')
        ->where('almacenes.id_empresa_negocio',$sucursal)
        ->paginate(100);

        $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();

        return view('empresas.almacen.listaralmacenes',compact('almacenes','negocios','sucursal','datos'));
    }

    public function crearalmacenes(){

        $negocios = DB::tABLE('empresa_negocios')->get();

        $ubigeo = DB::tABLE('cat_ubigeo')->get();

        return view('empresas.almacen.crearalmacenes',compact('negocios','ubigeo'));
    }

      public function editaralmacenes($id){

        $negocios = DB::tABLE('empresa_negocios')->get();
        $almacen = Almacenes::FindOrFail($id);

          $ubigeo = DB::tABLE('cat_ubigeo')->get();

        return view('empresas.almacen.editaralmacenes',compact('almacen','negocios','ubigeo'));
    }


    public function registraralmacenes(Request $request){

        $almacen = new Almacenes;
        $almacen->descripcion = $request->get('descripcion');
        $almacen->id_empresa_negocio = $request->get('sucursal');
        $almacen->ubigeo = $request->get('ubigeo');
        $almacen->direccion = $request->get('direccion');
        $almacen->codigo = $request->get('codigo');
        $almacen->save();

        $productos = DB::tABLE('productos')->where('tipo','1')->get();

        foreach ($productos as $pro) {
            
            DB::tABLE('producto_stock')->insert(['IdProducto'=>$pro->IdProducto,'id_empresa_negocio'=>$request->get('sucursal'),'id_almacen'=>$almacen->id_almacen]);
        }

        return Redirect::to('/almacen/listaralmacenes');
    }

    public function actualizaralmacenes(Request $request){

        $almacen = Almacenes::FindOrFail($request->get('id_almacen'));
        $almacen->descripcion = $request->get('descripcion');
        $almacen->id_empresa_negocio = $request->get('sucursal');
        $almacen->ubigeo = $request->get('ubigeo');
        $almacen->direccion = $request->get('direccion');
        $almacen->codigo = $request->get('codigo');
        $almacen->update();

        return Redirect::to('/almacen/listaralmacenes/');
    }

    public function eliminaralmacenes(Request $request){

        $almacen = Almacenes::where('id_almacen',$request->get('id_almacen'))->delete();
        

        return Redirect::to('/almacen/listaralmacenes/');
    }

    public function movimientos(Request $request)
    {

        $searchText = trim($request->get('searchText'));
  
        $rucemp = trim(Auth::user()->IdEmpresa);
        $fecin = $request->get('fecin');
        $tipo1= $request->get('tipo');
        $fecfin = $request->get('fecfin');
        $proveedores = DB::tABLE('proveedor')->WHERE('IdEmpresa',$rucemp)->get();
        $distribuidor = DB::tABLE('distribuidor')->WHERE('IdEmpresa',$rucemp)->get();
        if(!isset($fecin) && !isset($fecfin) ){
            $fechaini = now()->modify('first day of this month')->format('Y-m-d');
            $fechafin = now()->modify('last day of this month')->format('Y-m-d');
        }else{
             $fechaini = $request->get('fecin');
             $fechafin = $request->get('fecfin');
        }
           
   

          if($tipo1 =='0' || empty($tipo1)){

                if(empty($searchText)){
                    $movimientos = DB::tABLE('movimientosinsumos as m')
                    ->join('insumos as p','m.IdInsumo','p.IdInsumo')
                    ->leftjoin('distribuidor as d','m.IdDistribuidor','=','d.IdDistribuidor')
                        ->join('unidad_medida as um','um.umecod','p.umecod')
                    ->select('m.mov_fec','m.comprobante','p.procod','m.mov_tip','m.mov_mot','p.pronom','umenom','p.stock','m.cantidad','observacion','preciounitario','totalfactura','mov_id_insumo','m.IdEmpresa','m.medida','m.totalmedida','p.totalmedida as totalmedidastock')
                    ->where('m.IdEmpresa',$rucemp)
                    ->where('mov_fec','>=',$fechaini)
                    ->where('mov_fec','<=',$fechafin)
                    ->orderby('mov_id_insumo','desc')
                    ->paginate(100);
                }else{
                    $movimientos = DB::tABLE('movimientosinsumos as m')
                    ->join('insumos as p','m.IdInsumo','p.IdInsumo')
                    ->leftjoin('distribuidor as d','m.IdDistribuidor','=','d.IdDistribuidor')
                    ->join('unidad_medida as um','um.umecod','p.umecod')
                    ->select('m.mov_fec','m.comprobante','p.procod','m.mov_tip','m.mov_mot','p.pronom','umenom','p.stock','m.cantidad','observacion','preciounitario','totalfactura','mov_id_insumo','m.IdEmpresa','m.medida','m.totalmedida','p.totalmedida as totalmedidastock')
                    ->where('m.IdEmpresa',$rucemp)
                    ->where('p.pronom','like', '%'.$searchText.'%')
                    ->where('mov_fec','>=',$fechaini)
                    ->where('mov_fec','<=',$fechafin) 
                    ->orwhere('p.procod',$searchText)
                    ->where('m.IdEmpresa',$rucemp)
                    ->where('mov_fec','>=',$fechaini)
                    ->where('mov_fec','<=',$fechafin)      
                    ->orderby('mov_id_insumo','desc')
                    ->paginate(100);
                }

          }else{

            if(empty($searchText)){
                $movimientos = DB::tABLE('movimientosinsumos as m')
                ->join('insumos as p','m.IdInsumo','p.IdInsumo')
                ->leftjoin('distribuidor as d','m.IdDistribuidor','=','d.IdDistribuidor')
                    ->join('unidad_medida as um','um.umecod','m.unidad')
                ->select('m.mov_fec','m.comprobante','p.procod','m.mov_tip','m.mov_mot','p.pronom','umenom','p.stock','m.cantidad','observacion','preciounitario','totalfactura','mov_id_insumo','m.IdEmpresa','m.medida','m.totalmedida','p.totalmedida as totalmedidastock')
                ->where('m.IdEmpresa',$rucemp)
                ->where('mov_fec','>=',$fechaini)
                ->where('mov_fec','<=',$fechafin)
                ->where('mov_mot','=',$tipo1)
                ->orderby('mov_id_insumo','desc')
                ->paginate(100);
            }else{
                $movimientos = DB::tABLE('movimientosinsumos as m')
                ->join('insumos as p','m.IdInsumo','p.IdInsumo')
                ->leftjoin('distribuidor as d','m.IdDistribuidor','=','d.IdDistribuidor')
                ->join('unidad_medida as um','um.umecod','m.unidad')
                ->select('m.mov_fec','m.comprobante','p.procod','m.mov_tip','m.mov_mot','p.pronom','umenom','p.stock','m.cantidad','observacion','preciounitario','totalfactura','mov_id_insumo','m.IdEmpresa','m.medida','m.totalmedida','p.totalmedida as totalmedidastock')
                ->where('m.IdEmpresa',$rucemp)
                ->where('p.pronom','like', '%'.$searchText.'%')
                   ->where('mov_mot','=',$tipo1)
                ->orwhere('p.procod',$searchText)
                ->where('m.IdEmpresa',$rucemp)
                   ->where('mov_mot','=',$tipo1)
                ->where('mov_fec','>=',$fechaini)
                ->where('mov_fec','<=',$fechafin)
                ->orderby('mov_id_insumo','desc')
                ->paginate(100);
             }
          }

        
        return view('empresas.almacen.indexinsumo',compact('movimientos','distribuidor','proveedores','searchText'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
   
    public function calcularstock(){

        $almacenes = DB::tABLE('almacenes')->get();

        $rucemp = trim(Auth::user()->IdEmpresa);

        foreach($almacenes as $almacen){

             $stock= DB::table("productos")
          ->select("productos.*",
                    DB::raw("(SELECT SUM(cantidad) FROM movimientos_productos
                                WHERE movimientos_productos.IdProducto = productos.IdProducto
                                AND mov_tip='I' and movimientos_productos.id_almacen='".$almacen->id_almacen."'
                                GROUP BY movimientos_productos.IdProducto) as Ingresos"),
                    DB::raw("(SELECT SUM(cantidad) FROM movimientos_productos
                                WHERE movimientos_productos.IdProducto = productos.IdProducto
                                AND mov_tip='E' and movimientos_productos.id_almacen='".$almacen->id_almacen."'
                                GROUP BY movimientos_productos.IdProducto) as Egresos"),
                    DB::raw("(SELECT SUM(cantidad) FROM movimientos_productos
                                WHERE movimientos_productos.IdProducto = productos.IdProducto
                                AND mov_tip='EI' and movimientos_productos.id_almacen='".$almacen->id_almacen."'
                                GROUP BY movimientos_productos.IdProducto) as Anula"))
          //->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->where('productos.tipo','1')
          ->get();

          
         foreach ($stock as $key => $value) {

                    $stockprod= ($value->Ingresos + $value->Anula)  - $value->Egresos;

                    DB::tABLE('producto_stock')
                    ->where('IdProducto',$value->IdProducto)
                    ->where('id_almacen',$almacen->id_almacen)
                    ->update(['stock'=>$stockprod]);
                    
                   
         }

        }
        

        return Redirect::to('/pos');
             //dd($stock);
         
    }

    
    public function recalcular_stock($almacen,$producto){

        $almacenes = DB::tABLE('almacenes')->get();

        $rucemp = trim(Auth::user()->IdEmpresa);

            $productos = DB::tABLE('productos')->where('IdProducto',$producto)->where('tipo','1')->get();

        foreach($productos as $pro){

            $movimientos = DB::TABLE('movimientos_productos')
             ->where(function ($query) use ($pro) {
                    $query->where('IdProducto',$pro->IdProducto)
                        ->orWhere('IdProducto_rel',$pro->IdProducto);
            })
                ->where('id_almacen',$almacen)
            //->where('fecha_mov','>=',$fecha)
            ->orderby('fecha_mov','asc')
            ->orderby('mov_tip','desc')
            ->orderby('tipo','asc')
            ->get();

            $stock = 0;
            $i=0;
            
          
           foreach($movimientos as $mov){
                if($i==0){

                     if($mov->descripcion=='STOCK_INICIAL' || $mov->descripcion=='SALDO_ANTERIOR'){
                             $stock = $mov->cantidad;
                       }else{
                            if($mov->mov_tip=='I'){
                                    $stock = $mov->cantidad;
                            }else{
                                    $stock = ($mov->cantidad*-1);
                            }
                        }
                    DB::TABLE('movimientos_productos')->where('mov_pro_id',$mov->mov_pro_id)->update(['stock'=>$stock]);

                }else{
                       
                       if($mov->descripcion=='STOCK_INICIAL' || $mov->descripcion=='SALDO_ANTERIOR'){
                             $stock = $mov->cantidad;
                       }else{
                          if($mov->mov_tip=='I'){
                            $stock =$stock + $mov->cantidad;
                           }else{
                             $stock =$stock - $mov->cantidad;
                           }
                       }
                      
                      

                      DB::TABLE('movimientos_productos')->where('mov_pro_id',$mov->mov_pro_id)->update(['stock'=>$stock]);
                }

                    $i = $i+1;

            }


        }

       
       return Redirect::to('/stockproductos');

        

          
         
    }





    public function generar_inventario(Request $request){

        $rucemp = trim(Auth::user()->IdEmpresa);
         $stock= DB::table("productos")
          ->select("productos.*",
                    DB::raw("(SELECT SUM(cantidad) FROM movimientos_productos
                                WHERE movimientos_productos.IdProducto = productos.IdProducto
                                AND mov_tip='I'
                                GROUP BY movimientos_productos.IdProducto) as Ingresos"),
                    DB::raw("(SELECT SUM(cantidad) FROM movimientos_productos
                                WHERE movimientos_productos.IdProducto = productos.IdProducto
                                AND mov_tip='E'
                                GROUP BY movimientos_productos.IdProducto) as Egresos"),
                    DB::raw("(SELECT SUM(cantidad) FROM movimientos_productos
                                WHERE movimientos_productos.IdProducto = productos.IdProducto
                                AND mov_tip='EI'
                                GROUP BY movimientos_productos.IdProducto) as Anula"))
          ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->where('productos.tipo','1')
          ->get();

          
         foreach ($stock as $key => $value) {

                    $stockprod= ($value->Ingresos + $value->Anula)  - $value->Egresos;

                    DB::tABLE('producto_stock')
                    ->where('IdProducto',$value->IdProducto)
                    ->update(['stock'=>$stockprod]);
                    
                   
         }

             dd($stock);
         
    }



       public function ingresoproductos()
    {

    $rucemp = trim(Auth::user()->IdEmpresa);

        $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();
        $distribuidor = DB::tABLE('distribuidor')->WHERE('IdEmpresa',$rucemp)->get();
         //$presentaciones = DB::tABLE('presentaciones')->orderby('Presentacion','asc')->where('IdEmpresa',$rucemp)->get();

        // consultar unidades de medida
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();


        // consultar tipos de documentos de identidad
        $docidentidad = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

        //consultar tipo de documento
        $doccomprobante = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->orderBy('tdocod','asc')->get();

        // obtener el ruc de la empresa en la cual se logueo
        $rucemp = trim(Auth::user()->IdEmpresa);


        // consultar tipos de monedas
        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

        $proveedores = DB::tABLE('proveedor')->WHERE('IdEmpresa',$rucemp)->get();
        // consultar la serie y numero de factura

        $fecha = now()->format('m/d/Y');

        return view('empresas.almacen.ingresoalmacen',compact('monedas','unidades','docidentidad','fecha','distribuidor','doccomprobante','proveedores'));

    }

      public function ingresoinsumos()
    {

    $rucemp = trim(Auth::user()->IdEmpresa);

        $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();
        $distribuidor = DB::tABLE('distribuidor')->WHERE('IdEmpresa',$rucemp)->get();
         //$presentaciones = DB::tABLE('presentaciones')->orderby('Presentacion','asc')->where('IdEmpresa',$rucemp)->get();

        // consultar unidades de medida
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();


        // consultar tipos de documentos de identidad
        $docidentidad = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

        //consultar tipo de documento
        $doccomprobante = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->orderBy('tdocod','asc')->get();

        // obtener el ruc de la empresa en la cual se logueo
        $rucemp = trim(Auth::user()->IdEmpresa);


        // consultar tipos de monedas
        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

        $proveedores = DB::tABLE('proveedor')->WHERE('IdEmpresa',$rucemp)->get();
        // consultar la serie y numero de factura

        $fecha = now()->format('m/d/Y');

        return view('empresas.almacen.ingresoalmaceninsumos',compact('monedas','unidades','docidentidad','fecha','distribuidor','doccomprobante','proveedores'));

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
        $proveedor = $request->get('prov_id');
        $tipo = $request->get('tipo');
        $fec_ing = $request->get('fecEmi');
        $observacion = $request->get('obser');
        $comprobante = $request->get('comprobante');

    
        $cantidades = $request->get('cant');
        $presentaciones = $request->get('presentacion');
        $unidades = $request->get('unid');
        $codpro = $request->get('codpro');
        $detpro = $request->get('detpro');
        $totalfactura= $request->get('totalfactura');
        $tipoinsumo= $request->get('tipoinsumo');

       foreach( $unidades as $index => $ume ){


        if(!empty($codpro[$index])){
            
            if($tipoinsumo == 'insumo'){

                 $producto = insumos::FirstOrCreate(['procod'=> $codpro[$index],'IdEmpresa'=>$rucemp],['procod'=>$codpro[$index],'pronom'=>$detpro[$index],'umecod'=>$ume,'IdEmpresa'=>$rucemp]);

                $IdProducto = DB::tABLE('insumos')->WHERE('procod',$codpro[$index])->where('IdEmpresa',$rucemp)->first();

                
                if($tipo == 'INGRESO'){

                    $movimiento = new movimientosinsumos;
                    $movimiento->mov_fec = $fec_ing; 
                    $movimiento->mov_tip = 'I';
                    $movimiento->mov_mot = 'compra';
                    $movimiento->cantidad = $cantidades[$index];
                    $movimiento->medida= $IdProducto->medida;
                    $movimiento->totalmedida = $IdProducto->medida*$cantidades[$index];
                    $movimiento->unidad = $ume;
                    $movimiento->comprobante = $comprobante;
                    $movimiento->IdEmpresa = $rucemp;
                    $movimiento->IdInsumo = $IdProducto->IdInsumo;
                    $movimiento->observacion = $observacion;
                    $movimiento->IdDistribuidor = $request->get('cmbDist');
                    $movimiento->IdUsuario = Auth::user()->IdUsuario;

                    $movimiento->preciounitario = $totalfactura[$index];
                    $movimiento->totalfactura = $totalfactura[$index]*$cantidades[$index];

                    $movimiento->save();

        

                    $stock_prod =insumos::findOrFail($IdProducto->IdInsumo);

                    $stock_fraccion =  $stock_prod->totalmedida+($IdProducto->medida*$cantidades[$index]);

                    $fraccion = $stock_fraccion % $stock_prod->medida;

                    $entero = ($stock_fraccion - $fraccion) / $stock_prod->medida;

                    $sumaunidades = ($entero*$stock_prod->medida)+$fraccion;

                    $stock_prod->stock = $entero;
                    $stock_prod->fraccion = $fraccion;
                    $stock_prod->totalmedida = $sumaunidades;
                

                    $stock_prod->IdDistribuidor = $request->get('cmbDist');
                    $stock_prod->propuncom = $totalfactura[$index];
                    $stock_prod->provuncom = $totalfactura[$index]/1.1055;
                    $stock_prod->update(); 

                }elseif($tipo == 'EGRESO'){
                    
                    $movimiento = new movimientosinsumos;
                    $movimiento->mov_fec = $fec_ing; 
                    $movimiento->mov_tip = 'E';
                    $movimiento->mov_mot = 'Salida con Guía';
                    $movimiento->cantidad = $cantidades[$index];
                    $movimiento->medida= $IdProducto->medida;
                    $movimiento->totalmedida = $IdProducto->medida*$cantidades[$index];
                    $movimiento->unidad = $ume;
                    $movimiento->comprobante = $comprobante;
                    $movimiento->IdEmpresa = $rucemp;
                    $movimiento->IdInsumo = $IdProducto->IdInsumo;
                    $movimiento->observacion = $observacion;
                    $movimiento->IdDistribuidor = $request->get('cmbDist');
                    $movimiento->IdUsuario = Auth::user()->IdUsuario;

                    $movimiento->preciounitario = $totalfactura[$index];
                    $movimiento->totalfactura = $totalfactura[$index]*$cantidades[$index];

                    $movimiento->save();

        

                    $stock_prod =insumos::findOrFail($IdProducto->IdInsumo);

                    $stock_fraccion =  $stock_prod->totalmedida-($IdProducto->medida*$cantidades[$index]);

                    $fraccion = $stock_fraccion % $stock_prod->medida;

                    $entero = ($stock_fraccion - $fraccion) / $stock_prod->medida;

                    $sumaunidades = ($entero*$stock_prod->medida)+$fraccion;

                    $stock_prod->stock = $entero;
                    $stock_prod->fraccion = $fraccion;
                    $stock_prod->totalmedida = $sumaunidades;
                

                    $stock_prod->IdDistribuidor = $request->get('cmbDist');
                   // $stock_prod->propuncom = $totalfactura[$index];
                   // $stock_prod->provuncom = $totalfactura[$index]/1.1055;
                    $stock_prod->update(); 

                }elseif($tipo =='INGRESOAJUSTE'){

                   

                }elseif($tipo =='EGRESOAJUSTE'){

                   
                }
                


            }else{

                $producto = productos::FirstOrCreate(['procod'=> $codpro[$index],'IdEmpresa'=>$rucemp],['procod'=>$codpro[$index],'pronom'=>$detpro[$index],'umecod'=>$ume,'IdEmpresa'=>$rucemp]);

                $IdProducto = DB::tABLE('productos')->WHERE('procod',$codpro[$index])->where('IdEmpresa',$rucemp)->first();

                if($tipo == 'INGRESO'){
                   $movimiento = new movimientos;
                    $movimiento->mov_fec = $fec_ing; 
                    $movimiento->mov_tip = 'I';
                    $movimiento->mov_mot = 'compra';
                    $movimiento->cantidad = $cantidades[$index];
                    $movimiento->unidad = $ume;
                    $movimiento->comprobante = $comprobante;
                    $movimiento->IdEmpresa = $rucemp;
                    $movimiento->IdProducto = $IdProducto->IdProducto;
                    $movimiento->observacion = $observacion;
                    $movimiento->IdDistribuidor = $request->get('cmbDist');
                    $movimiento->IdUsuario = Auth::user()->IdUsuario;

                    $movimiento->preciounitario = $totalfactura[$index];
                    $movimiento->totalfactura = $totalfactura[$index]*$cantidades[$index];

                    $movimiento->save();

                 
                    $stock_prod =productos::findOrFail($IdProducto->IdProducto);
                    $stock_prod->stock = $stock_prod->stock+$cantidades[$index];
                    $stock_prod->IdDistribuidor = $request->get('cmbDist');
                    $stock_prod->propuncom = $totalfactura[$index];
                    $stock_prod->provuncom = $totalfactura[$index]/1.1055;
                    $stock_prod->update();
  
                }elseif($tipo =='EGRESO'){

                    $movimiento = new movimientos;
                    $movimiento->mov_fec = $fec_ing; 
                    $movimiento->mov_tip = 'E';
                    $movimiento->mov_mot = 'Salida con guía';
                    $movimiento->cantidad = $cantidades[$index];
                    $movimiento->unidad = $ume;
                    $movimiento->comprobante = $comprobante;
                    $movimiento->IdEmpresa = $rucemp;
                    $movimiento->IdProducto = $IdProducto->IdProducto;
                    $movimiento->observacion = $observacion;
                    $movimiento->IdDistribuidor = $request->get('cmbDist');
                    $movimiento->IdUsuario = Auth::user()->IdUsuario;

                    $movimiento->preciounitario = $totalfactura[$index];
                    $movimiento->totalfactura = $totalfactura[$index]*$cantidades[$index];

                    $movimiento->save();

                 
                    $stock_prod =productos::findOrFail($IdProducto->IdProducto);
                    $stock_prod->stock = $stock_prod->stock-$cantidades[$index];
                    $stock_prod->IdDistribuidor = $request->get('cmbDist');
                    //$stock_prod->propuncom = $totalfactura[$index];
                    //$stock_prod->provuncom = $totalfactura[$index]/1.1055;
                    $stock_prod->update();

                }elseif($tipo =='INGRESOAJUSTE'){

                    $movimiento = new movimientos;
                    $movimiento->mov_fec = $fec_ing; 
                    $movimiento->mov_tip = 'I';
                    $movimiento->mov_mot = 'Ingreso con Guía - Ajuste';
                    $movimiento->cantidad = $cantidades[$index];
                    $movimiento->unidad = $ume;
                    $movimiento->comprobante = $comprobante;
                    $movimiento->IdEmpresa = $rucemp;
                    $movimiento->IdProducto = $IdProducto->IdProducto;
                    $movimiento->observacion = $observacion;
                    $movimiento->IdDistribuidor = $request->get('cmbDist');
                    $movimiento->IdUsuario = Auth::user()->IdUsuario;

                     $movimiento->save();



                     $stock= DB::table("productos")
          ->select("productos.*",
                    DB::raw("(SELECT SUM(cantidad) FROM movimientos
                                WHERE movimientos.IdProducto =".$IdProducto->IdProducto."
                                AND mov_tip='I'
                                GROUP BY movimientos.IdProducto) as Ingresos"),
                    DB::raw("(SELECT SUM(cantidad) FROM movimientos
                                WHERE movimientos.IdProducto = ".$IdProducto->IdProducto."
                                AND mov_tip='E'
                                GROUP BY movimientos.IdProducto) as Egresos"),
                    DB::raw("(SELECT SUM(cantidad) FROM movimientos
                                WHERE movimientos.IdProducto = ".$IdProducto->IdProducto."
                                AND mov_tip='EI' GROUP BY movimientos.IdProducto) as Anula"))->where('IdEmpresa',$rucemp)->where('IdProducto',$IdProducto->IdProducto)->first();

      
          
                   

                                if($stock->Anula < 0 ){

                                    $stockinicial = $stock->Anula * (-1);

                                    $idmov = DB::tABLE('movimientos')->where('IdProducto',$IdProducto->IdProducto)->where('mov_tip','EI')->first();
                                 
                                      $mov= movimientos::findOrFail($idmov->mov_id);
                                      $mov->cantidad=$stockinicial;
                                      $mov->update();


                                }else{
                                    $stockinicial = $stock->Anula;
                                }


                                

                                $stockprod= ($stock->Ingresos + $stockinicial)-$stock->Egresos;
                                $stock_prod =productos::findOrFail($stock->IdProducto);
                                $stock_prod->stock = $stockprod;
                                $stock_prod->update();
                  

                  //  $movimiento->preciounitario = $totalfactura[$index];
                  //  $movimiento->totalfactura = $totalfactura[$index]*$cantidades[$index];


                }elseif($tipo =='EGRESOAJUSTE'){

                    $movimiento = new movimientos;
                    $movimiento->mov_fec = $fec_ing; 
                    $movimiento->mov_tip = 'E';
                    $movimiento->mov_mot = 'Salida con guía - Ajuste';
                    $movimiento->cantidad = $cantidades[$index];
                    $movimiento->unidad = $ume;
                    $movimiento->comprobante = $comprobante;
                    $movimiento->IdEmpresa = $rucemp;
                    $movimiento->IdProducto = $IdProducto->IdProducto;
                    $movimiento->observacion = $observacion;
                    $movimiento->IdDistribuidor = $request->get('cmbDist');
                    $movimiento->IdUsuario = Auth::user()->IdUsuario;

                    $movimiento->preciounitario = $totalfactura[$index];
                    $movimiento->totalfactura = $totalfactura[$index]*$cantidades[$index];

                    $movimiento->save();

                    $stock= DB::table("productos")
                    ->select("productos.*",
                    DB::raw("(SELECT SUM(cantidad) FROM movimientos
                                WHERE movimientos.IdProducto = ".$IdProducto->IdProducto."
                                AND mov_tip='I'
                                GROUP BY movimientos.IdProducto) as Ingresos"),
                    DB::raw("(SELECT SUM(cantidad) FROM movimientos
                                WHERE movimientos.IdProducto = ".$IdProducto->IdProducto."
                                AND mov_tip='E'
                                GROUP BY movimientos.IdProducto) as Egresos"),
                    DB::raw("(SELECT SUM(cantidad) FROM movimientos
                                WHERE movimientos.IdProducto = ".$IdProducto->IdProducto."
                                AND mov_tip='EI'
                                GROUP BY movimientos.IdProducto) as Anula"))->where('IdEmpresa',$rucemp)->where('IdProducto',$IdProducto->IdProducto)->first();

      
                            
                                if($stock->Anula < 0){

                                    $stockinicial = $stock->Anula * (-1);

                                    $idmov = DB::tABLE('movimientos')->where('IdProducto',$IdProducto->IdProducto)->where('mov_tip','EI')->first();
                                 
                                      $mov= movimientos::findOrFail($idmov->mov_id);
                                      $mov->cantidad=$stockinicial;
                                      $mov->update();


                                }else{
                                    $stockinicial = $stock->Anula;
                                }

                                $idmov = DB::tABLE('movimientos')->where('IdProducto',$IdProducto->IdProducto)->where('mov_tip','EI')->first();
                                 
                                  $mov= movimientos::findOrFail($idmov->mov_id);
                                  $mov->cantidad=$stockinicial;
                                  $mov->update();


                                $stockprod= ($stock->Ingresos +  $stockinicial)-$stock->Egresos;
                                $stock_prod =productos::findOrFail($stock->IdProducto);
                                $stock_prod->stock = $stockprod;
                                $stock_prod->update();
              
                }
  
               
             

            }

       }

    }

    
        if($tipoinsumo == 'insumo'){
                return Redirect::to('/movimientos');
        }else{
            return Redirect::to('/almacen');
        }

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
    public function destroy($id,Request $request)
    {

        $fecemi = now()->format('Y-m-d');

        $rucemp = trim(Auth::user()->IdEmpresa);

       DB::tABLE('movimientos_cabecera')->where('mov_cab_id',$id)->update(['estado'=>'ANULADO']);
        
        $movimiento =  DB::tABLE('movimientos_cabecera')->where('mov_cab_id',$id)->first();
        $detalle = DB::tABLE('movimientos_detalle')->where('mov_cab_id',$id)->get();
        
        foreach($detalle as $det){
            
            $bus_stock = DB::tABLE('producto_stock')->where('id_almacen',$movimiento->part_alm)->where('IdProducto',$det->IdProducto)->first();
            
            DB::tABLE('producto_stock')
            ->where('id_almacen',$movimiento->part_alm)
            ->where('IdProducto',$det->IdProducto)
            ->update(['stock'=>$bus_stock->stock+$det->cantidad]);

             $mov_cal_stock = new Almacen();
            $mov_cal_stock->movimiento_calcular_stock($det->IdProducto,$fecemi);

        }
        
        DB::tABLE('movimientos_productos')->where('mov_cab_id',$id)->delete();
        
        return Redirect::to('/transferencias');
      
    }

    public function buscaralmacen($negocio,Request $request){

      
              $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocio)->get();

      

        $vista = view('empresas.almacen.divalmacenes',compact('almacenes'))->render();


        if($request->ajax()){
          return response()->json(['vista'=>$vista]);

        }

    }

       public function buscaralmacendestino($negocio,Request $request){

      
              $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocio)->get();

      

        $vista = view('empresas.almacen.divalmacenesdestino',compact('almacenes'))->render();


        if($request->ajax()){
          return response()->json(['vista'=>$vista]);

        }

    }

    public function detallecompras($id){
        $rucemp = trim(Auth::user()->IdEmpresa);

        $compra = DB::tABLE('compras_detalle as cd')
        ->join('unidad_medida as um','um.umecod','cd.ume_cod')
        ->join('productos as p','p.IdProducto','cd.pro_id')
        ->where('com_cab_id',$id)->where('cd.IdEmpresa',$rucemp)->get();


        return view('empresas.compras.detalles',compact('compra'));
    }

    
    public function editarmovimiento($id){
        
           $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first(); 

       
        $almacen = DB::tABLE('almacenes')
        ->where('predeterminado','1')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();



        $negocios = DB::tABLE('empresa_negocios')->get();

        $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();

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

      
        $mozos = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        ->where('role_id','5')
        ->get();

        $mediospagos = DB::tABLE('medios_pagos')->get();


        $laboratorios = DB::tABLE('laboratorio')->get();
          $unidades = DB::tABLE('unidad_medida')
        ->orderBy('umecod','asc')->get();


        
        $movimiento = DB::tABLE('movimientos_cabecera')
        ->leftjoin('guias_remision','guias_remision.IdCpe_guia','movimientos_cabecera.IdCpe_guia')
        ->where('mov_cab_id',$id)
        ->first();
        
        $detalle = DB::tABLE('movimientos_detalle')
        ->leftjoin('productos','productos.IdProducto','movimientos_detalle.IdProducto')
        ->where('mov_cab_id',$id)
        ->get();
        
        return view('empresas.almacen.editarmovimiento',compact('detalle','movimiento','negocios','documentos','categorias','comprobante','tipodocumento','mozos','creditos','mediospagos','clientes','documentos','datos','negocios','almacenes','laboratorios','unidades'));
    }

     public function editarmovimientoalmacenes($id){
        
           $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first(); 

       
        $almacen = DB::tABLE('almacenes')
        ->where('predeterminado','1')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();



        $negocios = DB::tABLE('empresa_negocios')->get();



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

      
        $mozos = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        ->where('role_id','5')
        ->get();

        $mediospagos = DB::tABLE('medios_pagos')->get();



        
        $movimiento = DB::tABLE('movimientos_cabecera')
        ->leftjoin('guias_remision','guias_remision.IdCpe_guia','movimientos_cabecera.IdCpe_guia')
        ->where('mov_cab_id',$id)
        ->first();


        
        $almacenes = DB::tABLE('almacenes')
        ->where('id_empresa_negocio',$movimiento->part_suc)
        ->get();

        $almacenesdest = DB::tABLE('almacenes')
        ->where('id_empresa_negocio',$movimiento->des_suc)
        ->get();

        $detalle = DB::tABLE('movimientos_detalle')
        ->leftjoin('productos','productos.IdProducto','movimientos_detalle.IdProducto')
        ->where('mov_cab_id',$id)
        ->get();
        

        $laboratorios = DB::tABLE('laboratorio')->get();
          $unidades = DB::tABLE('unidad_medida')
        ->orderBy('umecod','asc')->get();



        return view('empresas.almacen.editarmovimientoalmacenes',compact('detalle','movimiento','tipo','negocios','sucursal','documentos','fecin','fecfin','tipodocumentos','categorias','comprobante','tipodocumento','mozos','creditos','mediospagos','clientes','documentos','datos','negocios','almacenes','productos','laboratorios','unidades','almacenesdest'));
    
    
    }
    
    


    
    


    public function transferirproductos()
    { 


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


        $laboratorios = DB::tABLE('laboratorio')->get();
          $unidades = DB::tABLE('unidad_medida')
        ->orderBy('umecod','asc')->get();

        return view('empresas.almacen.transferirproductos',compact('comprobante','tipodocumento','unidades','clientes','documentos','datos','negocios','almacenes','clientes','laboratorios','unidades'));
    }

      public function transferirproductosalmacenes()
    { 


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

              $laboratorios = DB::tABLE('laboratorio')->get();
          $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

     

        return view('empresas.almacen.transferenciaalmacenes',compact('comprobante','tipodocumento','unidades','clientes','documentos','datos','negocios','almacenes','clientes','laboratorios','unidades'));
    }


      public function generarguiaalbergue($id)
    { 


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

        $listar = DB::tABLE('pedido_servicio_insu')
        ->join('productos','productos.IdProducto','pedido_servicio_insu.prod_ins')
        ->where('ped_ser_id',$id)
        ->get();     

        $cabecera = DB::tABLE('pedido_servicio')->where('ped_ser_id',$id)->first();

          $almacenesdes = DB::tABLE('almacenes')->where('id_empresa_negocio',$cabecera->id_empresa_negocio)->get();

    

        return view('empresas.almacen.generarguiaalbergue',compact('comprobante','tipodocumento','unidades','clientes','documentos','datos','negocios','almacenes','clientes','listar','cabecera','almacenesdes'));
    }

    public function recepcionartransferencia($transferencia)
    { 


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

        $mediospagos = DB::tABLE('medios_pagos')->get();

        $cabecera = DB::tABLE('movimientos_cabecera')->where('mov_cab_id',$transferencia)->first();

        $detalle = DB::tABLE('movimientos_detalle')
        ->leftjoin('productos','productos.IdProducto','movimientos_detalle.IdProducto')
        ->where('mov_cab_id',$transferencia)
        ->get();

        $neg_ori = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$cabecera->part_suc)->first();
        $alm_ori = DB::tABLE('almacenes')->where('id_empresa_negocio',$cabecera->part_suc)->get();

        $neg_des = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$cabecera->des_suc)->first();
        $alm_des = DB::tABLE('almacenes')->where('id_empresa_negocio',$cabecera->des_suc)->get();

        $negocios = DB::tABLE('empresa_negocios')->get();

        $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();

        return view('empresas.almacen.recepcionartransferencia',compact('categorias','comprobante','tipodocumento','unidades','unidades','mozos','creditos','mediospagos','clientes','documentos','datos','negocios','almacenes','cabecera','detalle','neg_ori','alm_ori','neg_des','alm_des'));
    }

    public function registrar_recepcion_transferencia(Request $request){

        $mov_cab_id = $request->get('mov_cab_id');
        $fecrec = $request->get('fecRec');
        $des_alm = $request->get('des_alm');
        $mov_det_id = $request->get('mov_det_id');
        $proid = $request->get('proid');
        $cant = $request->get('cant');

        $sucursal = DB::tABLE('almacenes')->where('id_almacen',$des_alm)->first();


        DB::tABLE('movimientos_cabecera')
        ->where('mov_cab_id',$mov_cab_id)
        ->update(['fecha_recep'=>$fecrec,
            'des_alm'=>$des_alm,
            'estado'=>'REGISTRADO']);

        foreach ($mov_det_id as $key => $md) {
            
            DB::tABLE('movimientos_detalle')
            ->where('mov_det_id',$md)
            ->update(['cantidad'=>$cant[$key]]);

        }

        foreach ($proid as $index => $pro) {
            

            $buspro = DB::tABLE('productos')->where('IdProducto',$pro)->first();

            if(empty($buspro->pro_rel)){

                $id_prod = $buspro->IdProducto;

            }else{

                $id_prod = $buspro->pro_rel;

            }

            $bus_stock =  DB::tABLE('producto_stock')
            ->where('id_almacen',$des_alm)
            ->where('IdProducto',$id_prod)
            ->first();

  
            DB::tABLE('movimientos_detalle')->where('mov_cab_id',$mov_cab_id)
            ->where('IdProducto_rel',$id_prod)
            ->update(['stock'=>$bus_stock->stock+$cant[$index]]);

            DB::tABLE('producto_stock')
            ->where('id_almacen',$des_alm)
            ->where('IdProducto',$id_prod)
            ->update(['stock'=>$bus_stock->stock+$cant[$index]]);

        }

        if($request->ajax()) {
          return response()->json(['mensaje' => 'Recepcionado']);
        }

    }


  public function transferir(request $request){

    

        
        $IdCpe_guia =  self::generarguia($request);

        $tdocod = '81';
        $tdocod_ing ='82';
        $fecemi = $request->get('fecEmi');
        $rucemp = trim(Auth::user()->IdEmpresa);
        $empresa = Empresa::findOrFail($rucemp);
        $unidades = $request->get('unid');
        $proid = $request->get('proid');
        $detpro = $request->get('pronom');
        $cantidades = $request->get('cant');
        $part_suc = $request->get('part_suc');
        $part_alm = $request->get('almacen');
        $des_suc = $request->get('des_suc');
        $des_alm = $request->get('des_alm');
        $observaciones = $request->get('observaciones');

        $senudoc = DB::tABLE('empresa_negocios')->select('SerNS','NumNS')->where('id_empresa_negocio',$part_suc)->first();
        $numdoc =  $senudoc->NumNS+1;
        $sercomp =  $senudoc->SerNS;

        $ser_not_ing = DB::tABLE('empresa_negocios')->select('SerNI','NumNI')->where('id_empresa_negocio',$des_suc)->first();
      

        $emp_nego = EmpresaNegocios::findOrFail($part_suc);
        $emp_nego->NumNS = $numdoc;
        $emp_nego->update();


        $reg_mov_cab = DB::tABLE('movimientos_cabecera')->insert([
            'tdocod'=>$tdocod,
            'serdoc'=>$sercomp,
            'numdoc'=>$numdoc,
            'part_suc'=>$part_suc,
            'des_suc'=>$des_suc,
            'part_alm'=>$part_alm,
            'des_alm'=>$des_alm,
            'fecha'=>$fecemi,
            'estado'=>"REGISTRADO",
             'IdCpe_guia'=>$IdCpe_guia,
            'observaciones'=>$observaciones
        ]);
        

        $mov_cab_id = DB::getPdo()->lastInsertId();
    

         foreach($proid as $index => $id) {

            $codpro = productos::findOrFail($id);

            if(empty($codpro->pro_rel)){

                $id_prod = $codpro->IdProducto;

            }else{

                $id_prod = $codpro->pro_rel;

            }

            $stockprod = DB::tABLE('producto_stock')
            ->where('IdProducto',$id_prod)
            ->where('id_empresa_negocio',$part_suc)
            ->where('id_almacen',$part_alm)
            ->first();

            if(empty($stockprod)){

                $stock = 0-($cantidades[$index]*$codpro->factor);

                $stockprod_act = DB::tABLE('producto_stock')
                ->insert(['stock'=>$stock,'IdProducto'=>$id_prod,
                    'id_empresa_negocio'=>$part_suc,
                    'id_almacen'=>$part_alm
                        ]);

                $sto_ini='0';
            }else{

                  $stockprod_act = DB::tABLE('producto_stock')
                  ->where('pro_sto_id',$stockprod->pro_sto_id)
                  ->update(['stock'=>$stockprod->stock-($cantidades[$index]*$codpro->factor)]);

                  $stock = $stockprod->stock-($cantidades[$index]*$codpro->factor);

                  $sto_ini = $stockprod->stock_inicial;

            }

            //REGISTRO DEL DETALLE DE SALIDA
            $reg_mov_det = DB::tABLE('movimientos_detalle')->insert([
                'mov_cab_id'=>$mov_cab_id,
                'IdProducto'=>$id,
                'IdProducto_rel'=>$id_prod,
                'precio'=>$codpro->propun,
                'cantidad'=>$cantidades[$index]*$codpro->factor,
                'costo'=>$codpro->costo,
                'stock'=>$stock,
                'stock_inicial_mov'=>$stockprod->stock_inicial,
                'mov_det_factor'=>$codpro->factor,
            ]);

            
            DB::tABLE('movimientos_productos')->insert([

                    'IdProducto'=>$id,
                    'precio'=>'',
                    'cantidad'=>$cantidades[$index]*$codpro->factor,
                    'costo'=>$codpro->costo,
                    'mov_cab_id'=>$mov_cab_id,
                    'stock'=>$stock,
                    'IdProducto_rel'=>$id,
                    'IdCpe_cabecera'=>'',
                    'com_cab_id'=>'',
                    'stock_inicial'=>$sto_ini,
                    'serie'=>$sercomp,
                    'numero'=>$numdoc,
                    'tdocod'=>$tdocod,
                    'tipo'=>'3',
                    'mov_tip'=>'E',
                    'id_empresa_negocio'=>$part_suc,
                    'id_almacen'=>$part_alm,
                    'fecha_mov'=>$fecemi,
                   

            ]);


            $mov_cal_stock = new Almacen;
            $mov_cal_stock->movimiento_calcular_stock($id,$fecemi);

            // FIN DE REGISTRO DE DETALLE DE SALIDA



        


          
        }

        

        return response()->json(['estado'=>'success','mensaje'=>'Comprobante Emitido']);

    }

      public function ajustar_stock($id_almacen,$IdProducto){

        $productos = productos::join('producto_stock','producto_stock.IdProducto','productos.IdProducto')->where('producto_stock.IdProducto',$IdProducto)->where('id_almacen',$id_almacen)->first();

        $almacen = DB::tABLE('almacenes')->where('id_almacen',$id_almacen)->first();

        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$almacen->id_empresa_negocio)->first();

        return view('empresas.productos.ajustar_stock',compact('productos','almacen','sucursal'));

    }
  


     public function transferiralmacenes(request $request){

    
       //Datos de cabecera
        
        $IdCpe_guia =  self::generarguia($request);

        $bus_guia = DB::tABLE('guias_remision')->where('IdCpe_guia',$IdCpe_guia)->first();

        $tdocod = '81';
        $tdocod_ing ='82';
        $fecemi = $request->get('fecEmi');
        $rucemp = trim(Auth::user()->IdEmpresa);
        $empresa = Empresa::findOrFail($rucemp);
        $unidades = $request->get('unid');
        $proid = $request->get('proid');
        $detpro = $request->get('pronom');
        $cantidades = $request->get('cant');
        $part_suc = $request->get('part_suc');
        $part_alm = $request->get('almacen');
        $des_suc = $request->get('des_suc');
        $des_alm = $request->get('des_alm');
        $observaciones = $request->get('observaciones');

        $senudoc = DB::tABLE('empresa_negocios')->select('SerNS','NumNS')->where('id_empresa_negocio',$part_suc)->first();
        $numdoc =  $senudoc->NumNS+1;
        $sercomp =  $senudoc->SerNS;

        $ser_not_ing = DB::tABLE('empresa_negocios')->select('SerNI','NumNI')->where('id_empresa_negocio',$des_suc)->first();
      
           $destino_almacen = "";
        if(!empty($des_alm)){

         $destino = DB::tABLE('almacenes')->where('id_almacen',$des_alm)->first();
         $destino_almacen = $destino->descripcion;
        }


        $emp_nego = EmpresaNegocios::findOrFail($part_suc);
        $emp_nego->NumNS = $numdoc;
        $emp_nego->NumNI = $ser_not_ing->NumNI+1;
        $emp_nego->update();

        $emp_nego = EmpresaNegocios::findOrFail($des_suc);
        $emp_nego->NumNI = $ser_not_ing->NumNI+1;
        $emp_nego->update();

        //Generar el detalle del comprobante

        $reg_mov_cab = DB::tABLE('movimientos_cabecera')->insert([
            'tdocod'=>$tdocod,
            'serdoc'=>$sercomp,
            'numdoc'=>$numdoc,
            'part_suc'=>$part_suc,
            'des_suc'=>$des_suc,
            'part_alm'=>$part_alm,
            'des_alm'=>$des_alm,
            'fecha'=>$fecemi,
            'estado'=>"REGISTRADO",
            'IdCpe_guia'=>$IdCpe_guia,
            'observaciones'=>$observaciones
        ]);
        

        $mov_cab_id = DB::getPdo()->lastInsertId();
    


        $reg_mov_cab_ing = DB::tABLE('movimientos_cabecera')->insert([
            'tdocod'=>$tdocod_ing,
            'serdoc'=>$ser_not_ing->SerNI,
            'numdoc'=>$ser_not_ing->NumNI+1,
            'part_suc'=>$part_suc,
            'des_suc'=>$des_suc,
            'part_alm'=>$part_alm,
            'des_alm'=>$des_alm,
            'fecha'=>$fecemi,
            'estado'=>"RECEPCIONAR",
            'IdCpe_guia'=>$IdCpe_guia,
            'observaciones'=>$observaciones

        ]);

         $mov_cab_ing_id = DB::getPdo()->lastInsertId();
        

         DB::tABLE('movimientos_cabecera')->where('mov_cab_id',$mov_cab_id)->update(['mov_cab_ref'=>$mov_cab_ing_id]);
         DB::tABLE('movimientos_cabecera')->where('mov_cab_id',$mov_cab_ing_id)->update(['mov_cab_ref'=>$mov_cab_id]);

         foreach($proid as $index => $id) {

            $codpro = productos::findOrFail($id);

            if(empty($codpro->pro_rel)){

                $id_prod = $codpro->IdProducto;

            }else{

                $id_prod = $codpro->pro_rel;

            }

            $stockprod = DB::tABLE('producto_stock')
            ->where('IdProducto',$id_prod)
            ->where('id_empresa_negocio',$part_suc)
            ->where('id_almacen',$part_alm)
            ->first();

            if(empty($stockprod)){

                $stock = 0-($cantidades[$index]*$codpro->factor);

                $stockprod_act = DB::tABLE('producto_stock')
                ->insert(['stock'=>$stock,'IdProducto'=>$id_prod,
                    'id_empresa_negocio'=>$part_suc,
                    'id_almacen'=>$part_alm
                        ]);

                $sto_ini ='0';

            }else{

                  $stockprod_act = DB::tABLE('producto_stock')
                  ->where('pro_sto_id',$stockprod->pro_sto_id)
                  ->update(['stock'=>$stockprod->stock-($cantidades[$index]*$codpro->factor)]);

                  $stock = $stockprod->stock-($cantidades[$index]*$codpro->factor);

                  $sto_ini = $stockprod->stock_inicial;

            }

            //REGISTRO DEL DETALLE DE SALIDA
            $reg_mov_det = DB::tABLE('movimientos_detalle')->insert([
                'mov_cab_id'=>$mov_cab_id,
                'IdProducto'=>$id,
                'IdProducto_rel'=>$id_prod,
                'precio'=>$codpro->propun,
                'cantidad'=>$cantidades[$index]*$codpro->factor,
                'costo'=>$codpro->costo,
                'stock'=>$stock,
                'stock_inicial_mov'=>$stockprod->stock_inicial,
                'mov_det_factor'=>$codpro->factor,
            ]);



                 DB::tABLE('movimientos_productos')->insert([

                    'IdProducto'=>$id,
                    'precio'=>'',
                    'cantidad'=>$cantidades[$index]*$codpro->factor,
                    'costo'=>$codpro->costo,
                    'mov_cab_id'=>$mov_cab_id,
                    'stock'=>$stock,
                    'IdProducto_rel'=>$id,
                    'IdCpe_cabecera'=>'',
                    'com_cab_id'=>'',
                    'stock_inicial'=>$sto_ini,
                    'serie'=>$sercomp,
                    'numero'=>$numdoc,
                    'tdocod'=>$tdocod,
                    'tipo'=>'3',
                    'mov_tip'=>'E',
                    'id_empresa_negocio'=>$part_suc,
                    'id_almacen'=>$part_alm,
                    'fecha_mov'=>$fecemi,
                    'id_almacen_destino'=>$des_alm,
                    'destino'=>$destino_almacen,
                    'serieguia'=>$bus_guia->serieguia,
                    'numeroguia'=>$bus_guia->numeroguia


            ]);
            

         


           // REGISTRO DE DETALLE DE INGRESO

            $stockdest = DB::tABLE('producto_stock')
            ->where('IdProducto',$id_prod)
            ->where('id_empresa_negocio',$des_suc)
            ->where('id_almacen',$des_alm)
            ->first();

             if(empty($stockdest)){

                $stock_dest = 0;

             }else{

                $stock_dest = $stockdest->stock;

             }

            $reg_mov_det_ing = DB::tABLE('movimientos_detalle')->insert([
                'mov_cab_id'=>$mov_cab_ing_id,
                'IdProducto'=>$id,
                'IdProducto_rel'=>$id_prod,
                'precio'=>$codpro->propun,
                'cantidad'=>$cantidades[$index]*$codpro->factor,
                'costo'=>$codpro->costo,
                'stock_inicial_mov'=>$stockdest->stock_inicial

            ]);

          

            $mov_cal_stock = new Almacen();
            $mov_cal_stock->movimiento_calcular_stock($id,$fecemi);
                




          
        }

        

        return response()->json(['estado'=>'success','mensaje'=>'Comprobante Emitido']);

    }
    
    


    public function actualizarmovimiento(request $request){

    
       //Datos de cabecera
        
        $mov_id= $request->get('mov_id');
        
        $guia = $request->get('guia');
        
        $bus_guia = DB::tABLE('guias_remision')->where('IdCpe_guia',$guia)->first();

        $tdocod = '81';
        $tdocod_ing ='82';
        $fecemi = $request->get('fecEmi');
        $rucemp = trim(Auth::user()->IdEmpresa);
        $empresa = Empresa::findOrFail($rucemp);
        $unidades = $request->get('unid');
        $proid = $request->get('proid');
        $detpro = $request->get('pronom');
        $cantidades = $request->get('cant');
        $part_suc = $request->get('part_suc');
        $part_alm = $request->get('almacen');
        $des_suc = $request->get('des_suc');
        $des_alm = $request->get('des_alm');
        $observaciones = $request->get('observaciones');
        
        $destino_almacen = "";
        if(!empty($des_alm)){

         $destino = DB::tABLE('almacenes')->where('id_almacen',$des_alm)->first();
         $destino_almacen = $destino->descripcion;
        }


        $detalle = DB::tABLE('movimientos_detalle')->where('mov_cab_id',$mov_id)->get();
        
        $movimiento = DB::tABLE('movimientos_cabecera')->where('mov_cab_id',$mov_id)->first();

        $elim_mov = DB::tABLE('movimientos_productos')->where('mov_cab_id',$mov_id)->delete();
        //dd($mov_id);
        
            
        foreach($detalle as $det){
            
            $codpro = productos::findOrFail($det->IdProducto);

            if(empty($codpro->pro_rel)){

                $id_producto = $codpro->IdProducto;

            }else{

                $id_producto = $codpro->pro_rel;

            }



            $bus_stock = DB::tABLE('producto_stock')
            ->where('id_almacen',$movimiento->part_alm)
            ->where('IdProducto',$id_producto)
            ->first();
            

            DB::tABLE('producto_stock')
                ->where('id_almacen',$movimiento->part_alm)
                ->where('IdProducto',$bus_stock->IdProducto)
                ->update(['stock'=>$bus_stock->stock+$det->cantidad]);
        }
        
        
        
        
        DB::tABLE('movimientos_detalle')->where('mov_cab_id',$mov_id)->delete();

        if(!empty($des_suc)){

             DB::tABLE('movimientos_detalle')->where('mov_cab_id',$movimiento->mov_cab_ref)->delete();

             DB::tABLE('movimientos_productos')->where('mov_cab_id',$mov_id)->delete();

        }
       

        $IdCpe_guia =  self::actualizarguia($request,$guia);


        //Generar el detalle del comprobante

        $reg_mov_cab = DB::tABLE('movimientos_cabecera')->where('mov_cab_id',$mov_id)->update([
            'tdocod'=>$tdocod,
            'part_suc'=>$part_suc,
            'des_suc'=>$des_suc,
            'part_alm'=>$part_alm,
            'des_alm'=>$des_alm,
            'fecha'=>$fecemi,
            'estado'=>"REGISTRADO",
             'IdCpe_guia'=>$IdCpe_guia,
            'observaciones'=>$observaciones
        ]);

        $movimientos_cabecera  = DB::tABLE('movimientos_cabecera')->where('mov_cab_id',$mov_id)->first();
        
          if(!empty($des_suc)){

              $reg_mov_cab_des= DB::tABLE('movimientos_cabecera')->where('mov_cab_id',$movimiento->mov_cab_ref)->update([
                    'tdocod'=>$tdocod_ing,
                    'part_suc'=>$part_suc,
                    'des_suc'=>$des_suc,
                    'part_alm'=>$part_alm,
                    'des_alm'=>$des_alm,
                    'fecha'=>$fecemi,
                    'estado'=>"REGISTRADO",
                     'IdCpe_guia'=>$IdCpe_guia,
                    'observaciones'=>$observaciones
                ]);

              $movimientos_cabecera_ref  = DB::tABLE('movimientos_cabecera')->where('mov_cab_id',$movimiento->mov_cab_ref)->first();

          }
      
        
         foreach($proid as $index => $id) {

            $codpro = productos::findOrFail($id);

            if(empty($codpro->pro_rel)){

                $id_prod = $codpro->IdProducto;

            }else{

                $id_prod = $codpro->pro_rel;

            }


            $stockprod = DB::tABLE('producto_stock')
            ->where('IdProducto',$id_prod)
            ->where('id_empresa_negocio',$part_suc)
            ->where('id_almacen',$part_alm)
            ->first();

            if(empty($stockprod)){

                $stock = 0-($cantidades[$index]*$codpro->factor);

                $stockprod_act = DB::tABLE('producto_stock')
                ->insert(['stock'=>$stock,'IdProducto'=>$id_prod,
                    'id_empresa_negocio'=>$part_suc,
                    'id_almacen'=>$part_alm
                        ]);

                $sto_ini = '0';

            }else{

                  $stockprod_act = DB::tABLE('producto_stock')
                  ->where('pro_sto_id',$stockprod->pro_sto_id)
                  ->update(['stock'=>$stockprod->stock-($cantidades[$index]*$codpro->factor)]);

                  $stock = $stockprod->stock-($cantidades[$index]*$codpro->factor);

                  $sto_ini = $stockprod->stock_inicial;

            }

            //REGISTRO DEL DETALLE DE SALIDA
            $reg_mov_det = DB::tABLE('movimientos_detalle')->insert([
                'mov_cab_id'=>$mov_id,
                'IdProducto'=>$id,
                'precio'=>$codpro->propun,
                'cantidad'=>$cantidades[$index]*$codpro->factor,
                'costo'=>$codpro->costo,
                'stock'=>$stock,
                'stock_inicial_mov'=>$stockprod->stock_inicial
            ]);


             DB::tABLE('movimientos_productos')->insert([

                    'IdProducto'=>$id,
                    'precio'=>$codpro->propun,
                    'cantidad'=>$cantidades[$index]*$codpro->factor,
                    'costo'=>$codpro->costo,
                    'mov_cab_id'=>$mov_id,
                    'stock'=>$stock,
                    'IdProducto_rel'=>$id,
                    'IdCpe_cabecera'=>'',
                    'com_cab_id'=>'',
                    'stock_inicial'=>$sto_ini,
                    'serie'=>$movimientos_cabecera->serdoc,
                    'numero'=>$movimientos_cabecera->numdoc,
                    'tdocod'=>$movimientos_cabecera->tdocod,
                    'tipo'=>'3',
                    'mov_tip'=>'E',
                    'id_empresa_negocio'=>$part_suc,
                    'id_almacen'=>$part_alm,
                    'fecha_mov'=>$fecemi,
                    'destino'=>$destino_almacen,
                    'serieguia'=>$bus_guia->serieguia,
                    'numeroguia'=>$bus_guia->numeroguia

            ]);

              $mov_cal_stock = new Almacen();
            $mov_cal_stock->movimiento_calcular_stock($id,$fecemi);


  if(!empty($des_suc)){
            $stockdest = DB::tABLE('producto_stock')
            ->where('IdProducto',$id_prod)
            ->where('id_empresa_negocio',$des_suc)
            ->where('id_almacen',$des_alm)
            ->first();

          

             if(empty($stockdest)){

                $stock_dest = 0;

             }else{

                $stock_dest = $stockdest->stock;

             }


           
            $reg_mov_det_ing = DB::tABLE('movimientos_detalle')->insert([
                'mov_cab_id'=> $movimientos_cabecera_ref->mov_cab_id,
                'IdProducto'=>$id_prod,
                'IdProducto_rel'=>$id_prod,
                'precio'=>$codpro->propun,
                'cantidad'=>$cantidades[$index]*$codpro->factor,
                'costo'=>$codpro->costo,
                'stock_inicial_mov'=>$stockdest->stock_inicial

            ]);
        }

          


        /*    $movimiento = new movimientos;
            $movimiento->mov_fec = $fecemi; 
            $movimiento->mov_tip = 'E';
            $movimiento->mov_mot = 'TRANSFERIR PRODUCTOS';
            $movimiento->cantidad = $cantidades[$index];
            $movimiento->unidad = $codpro->umecod;
            $movimiento->tdocod = $tdocod;
            $movimiento->comprobante = $sercomp.'-'.$numdoc;
            $movimiento->id_empresa_negocio = $request->get('part_suc');
            $movimiento->IdProducto = $id;
            $movimiento->observacion = "TRANSFERENCIA DE PRODUCTOS";
            $movimiento->IdUsuario = Auth::user()->IdUsuario;
            $movimiento->part_suc = $request->get('part_suc');
            $movimiento->part_alm = $request->get('almacen');
            $movimiento->des_alm = $request->get('des_alm');
            $movimiento->des_suc = $request->get('des_suc');
            $movimiento->est_mov = 'REGISTRADO';
            $movimiento->mov_cab_id = $mov_cab_id;
            //$movimiento->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
            $movimiento->stockmov = $stock;
            $movimiento->save();*/

            // FIN DE REGISTRO DE DETALLE DE SALIDA



          
        }

        

        return response()->json(['estado'=>'success','mensaje'=>'Comprobante Emitido']);

    }
    


    public function transferir1(request $request){

    
       //Datos de cabecera
        
        $IdCpe_guia =  self::generarguia($request);

        $tdocod = '81';
        $tdocod_ing ='82';
        $fecemi = $request->get('fecEmi');
        $rucemp = trim(Auth::user()->IdEmpresa);
        $empresa = Empresa::findOrFail($rucemp);
        $unidades = $request->get('unid');
        $proid = $request->get('proid');
        $detpro = $request->get('pronom');
        $cantidades = $request->get('cant');
        $part_suc = $request->get('part_suc');
        $part_alm = $request->get('almacen');
        $des_suc = $request->get('des_suc');
        $des_alm = $request->get('des_alm');
        $observaciones = $request->get('observaciones');

        $senudoc = DB::tABLE('empresa_negocios')->select('SerNS','NumNS')->where('id_empresa_negocio',$part_suc)->first();
        $numdoc =  $senudoc->NumNS+1;
        $sercomp =  $senudoc->SerNS;

        $ser_not_ing = DB::tABLE('empresa_negocios')->select('SerNI','NumNI')->where('id_empresa_negocio',$des_suc)->first();
      

        $emp_nego = EmpresaNegocios::findOrFail($part_suc);
        $emp_nego->NumNS = $numdoc;
        $emp_nego->NumNI = $ser_not_ing->NumNI+1;
        $emp_nego->update();

        $emp_nego = EmpresaNegocios::findOrFail($des_suc);
        $emp_nego->NumNI = $ser_not_ing->NumNI+1;
        $emp_nego->update();

        //Generar el detalle del comprobante

        $reg_mov_cab = DB::tABLE('movimientos_cabecera')->insert([
            'tdocod'=>$tdocod,
            'serdoc'=>$sercomp,
            'numdoc'=>$numdoc,
            'part_suc'=>$part_suc,
            'des_suc'=>$des_suc,
            'part_alm'=>$part_alm,
            'des_alm'=>$des_alm,
            'fecha'=>$fecemi,
            'estado'=>"REGISTRADO",
             'IdCpe_guia'=>$IdCpe_guia,
            'observaciones'=>$observaciones
        ]);
        

        $mov_cab_id = DB::getPdo()->lastInsertId();
    


        $reg_mov_cab_ing = DB::tABLE('movimientos_cabecera')->insert([
            'tdocod'=>$tdocod_ing,
            'serdoc'=>$ser_not_ing->SerNI,
            'numdoc'=>$ser_not_ing->NumNI+1,
            'part_suc'=>$part_suc,
            'des_suc'=>$des_suc,
            'part_alm'=>$part_alm,
            'des_alm'=>$des_alm,
            'fecha'=>$fecemi,
            'estado'=>"RECEPCIONAR",
            'IdCpe_guia'=>$IdCpe_guia,
            'observaciones'=>$observaciones

        ]);

         $mov_cab_ing_id = DB::getPdo()->lastInsertId();
        

         DB::tABLE('movimientos_cabecera')->where('mov_cab_id',$mov_cab_id)->update(['mov_cab_ref'=>$mov_cab_ing_id]);
         DB::tABLE('movimientos_cabecera')->where('mov_cab_id',$mov_cab_ing_id)->update(['mov_cab_ref'=>$mov_cab_id]);

         foreach($proid as $index => $id) {

            $codpro = productos::findOrFail($id);

            if(empty($codpro->pro_rel)){

                $id_prod = $codpro->IdProducto;

            }else{

                $id_prod = $codpro->pro_rel;

            }

            $stockprod = DB::tABLE('producto_stock')
            ->where('IdProducto',$id_prod)
            ->where('id_empresa_negocio',$part_suc)
            ->where('id_almacen',$part_alm)
            ->first();

            if(empty($stockprod)){

                $stock = 0-($cantidades[$index]*$codpro->factor);

                $stockprod_act = DB::tABLE('producto_stock')
                ->insert(['stock'=>$stock,'IdProducto'=>$id_prod,
                    'id_empresa_negocio'=>$part_suc,
                    'id_almacen'=>$part_alm
                        ]);

            }else{

                  $stockprod_act = DB::tABLE('producto_stock')
                  ->where('pro_sto_id',$stockprod->pro_sto_id)
                  ->update(['stock'=>$stockprod->stock-($cantidades[$index]*$codpro->factor)]);

                  $stock = $stockprod->stock-($cantidades[$index]*$codpro->factor);

            }

            //REGISTRO DEL DETALLE DE SALIDA
            $reg_mov_det = DB::tABLE('movimientos_detalle')->insert([
                'mov_cab_id'=>$mov_cab_id,
                'IdProducto'=>$id,
                'precio'=>$codpro->propun,
                'cantidad'=>$cantidades[$index]*$codpro->factor,
                'costo'=>$codpro->costo,
                'stock'=>$stock,
                'stock_inicial_mov'=>$stockprod->stock_inicial
            ]);

            

            $movimiento = new movimientos;
            $movimiento->mov_fec = $fecemi; 
            $movimiento->mov_tip = 'E';
            $movimiento->mov_mot = 'TRANSFERIR PRODUCTOS';
            $movimiento->cantidad = $cantidades[$index];
            $movimiento->unidad = $codpro->umecod;
            $movimiento->tdocod = $tdocod;
            $movimiento->comprobante = $sercomp.'-'.$numdoc;
            $movimiento->id_empresa_negocio = $request->get('part_suc');
            $movimiento->IdProducto = $id;
            $movimiento->observacion = "TRANSFERENCIA DE PRODUCTOS";
            $movimiento->IdUsuario = Auth::user()->IdUsuario;
            $movimiento->part_suc = $request->get('part_suc');
            $movimiento->part_alm = $request->get('almacen');
            $movimiento->des_alm = $request->get('des_alm');
            $movimiento->des_suc = $request->get('des_suc');
            $movimiento->est_mov = 'REGISTRADO';
            $movimiento->mov_cab_id = $mov_cab_id;
            //$movimiento->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
            $movimiento->stockmov = $stock;
            $movimiento->save();

            // FIN DE REGISTRO DE DETALLE DE SALIDA



            // REGISTRO DE DETALLE DE INGRESO

            $stockdest = DB::tABLE('producto_stock')
            ->where('IdProducto',$id_prod)
            ->where('id_empresa_negocio',$des_suc)
            ->where('id_almacen',$des_alm)
            ->first();

             if(empty($stockdest)){

                $stock_dest = 0;

             }else{

                $stock_dest = $stockdest->stock;

             }

            $reg_mov_det_ing = DB::tABLE('movimientos_detalle')->insert([
                'mov_cab_id'=>$mov_cab_ing_id,
                'IdProducto'=>$id,
                'precio'=>$codpro->propun,
                'cantidad'=>$cantidades[$index]*$codpro->factor,
                'costo'=>$codpro->costo,
                'stock_inicial_mov'=>$stockdest->stock_inicial

            ]);

          

                


                $movimiento = new movimientos;
                $movimiento->mov_fec = $fecemi; 
                $movimiento->mov_tip = 'I';
                $movimiento->mov_mot = 'INGRESO DE PRODUCTOS POR TRANSFERENCIA';
                $movimiento->cantidad = $cantidades[$index];
                $movimiento->unidad = $codpro->umecod;
                $movimiento->tdocod = $tdocod_ing;
                $movimiento->comprobante = $ser_not_ing->SerNI.'-';
                $movimiento->id_empresa_negocio = $request->get('des_suc');
                $movimiento->IdProducto = $id;
                $movimiento->observacion = "INGRESO DE PRODUCTOS POR TRANSFERENCIA";
                $movimiento->IdUsuario = Auth::user()->IdUsuario;
                $movimiento->part_suc = $request->get('part_suc');
                $movimiento->part_alm = $request->get('part_alm');
                $movimiento->des_alm = $request->get('des_alm');
                $movimiento->des_suc = $request->get('des_suc');
                $movimiento->est_mov = 'RECEPCIONAR';
                $movimiento->mov_cab_id = $mov_cab_ing_id;
                //$movimiento->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
                $movimiento->stockmov = $stock_dest;
                $movimiento->save();

          
        }

        

        return response()->json(['estado'=>'success','mensaje'=>'Comprobante Emitido']);

    }
    
    public function generarguia($request){

        
         
        $fecemi = $request->get('fecEmi');
        $rucemp = trim(Auth::user()->IdEmpresa);
        $empresa = Empresa::findOrFail($rucemp);
        $unidades = $request->get('unid');
        $proid = $request->get('proid');
        $detpro = $request->get('pronom');
        $cantidades = $request->get('cant');
       
        $part_suc = $request->get('part_suc');
        $part_alm = $request->get('almacen');
     
        $busalmpart = DB::tABLE('almacenes')
        ->where('id_almacen',$part_alm)
        ->leftjoin('cat_ubigeo','cat_ubigeo.ubi_cod','almacenes.ubigeo')
        ->first();

        $bussucpart = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$part_suc)->first();
        $emprepart = DB::tABLE('empresa')->where('IdEmpresa',$bussucpart->IdEmpresa)->first();

        $des_suc = $request->get('des_suc');
        $des_alm = $request->get('des_alm');

        

        if(!empty($des_suc)){
            $busalmdes = DB::tABLE('almacenes')
            ->where('id_almacen',$des_alm)
            ->leftjoin('cat_ubigeo','cat_ubigeo.ubi_cod','almacenes.ubigeo')
            ->first();

            $bussucdes = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$des_suc)->first();
            $empredes = DB::tABLE('empresa')->where('IdEmpresa',$bussucdes->IdEmpresa)->first();
        }
       

        $observaciones = $request->get('observaciones');

        $senudoc = DB::tABLE('empresa_negocios')->select('serieguia','numeroguia')->where('id_empresa_negocio',$part_suc)->first();

        $numcomp =  $senudoc->numeroguia+1;
        $sercomp =  $senudoc->serieguia;
       

        $cabecera = new guias_remision;
        $cabecera->IdEmpresa =  Auth::user()->IdEmpresa;
        $cabecera->tdocod = '09';
        $cabecera->fechaemision = $fecemi;

        if(!empty($des_suc)){
            $cabecera->tdicod = '6';
            $cabecera->ruccliente = $bussucdes->IdEmpresa;
            $cabecera->ubigeollegada = '';
            $cabecera->direccionllegada = $busalmdes->direccion;
        }else{
            $cabecera->tdicod = $request->get('tdicod');
            $cabecera->ruccliente = $request->get('clinum');
            $cabecera->ubigeollegada = '';
            $cabecera->direccionllegada = $request->get('clidir');  
        }


        $cabecera->IdMotivo = '04';
        $cabecera->pesobruto = '0.00';
        $cabecera->umecod = 'KMG';
        $cabecera->fechatraslado = $fecemi;
        $cabecera->clicod = $request->get('clicod');
   
        $cabecera->ructransportista = $request->get('transportistanum');
        $cabecera->nombretransportista = $request->get('transportistanom');
        $cabecera->tdicodtransportista = $request->get('transportistatdicod');
        $cabecera->desubigeollegada ='';

        $cabecera->rucconductor = $request->get('conductornum');
        $cabecera->nomconductor = $request->get('conductornom');
        $cabecera->tdicodconductor = $request->get('conductortdicod');

        $cabecera->ubigeopartida = $busalmpart->ubigeo;
        $cabecera->direccionpartida = $busalmpart->direccion;
        $cabecera->desubigeopartida = $busalmpart->ubi_des;
      
        $cabecera->bultos = $request->get('bultos');
        $cabecera->correo = "";
        $cabecera->placa = $request->get('placa');
         $cabecera->licencia = $request->get('licencia');
        $cabecera->id_empresa_negocio =  $part_suc;
      //  $cabecera->datajson = $request->get('tdocod');
        $cabecera->IdModalidad = '01';
        if(!empty($des_suc)){
             $cabecera->nomcliente =  $bussucdes->tipo_negocio;
        }else{
            $cabecera->nomcliente = $request->get('clinom');
        }
       

    
    
        $empresanegocio = EmpresaNegocios::findOrFail($part_suc);
     
 
     
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

        foreach( $proid as $index => $id ) {
            
            $i=$i+1;
           // $codproducto = $codpro[$index];
            
            $IdProducto = DB::tABLE('productos')
            ->WHERE('IdProducto',$id)
           // ->where('IdEmpresa',$rucemp)
            //->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)
            ->first();
          
          //  $codproducto = $codpro[$index];

            $detalle = new guias_remision_detalle;
            $detalle->IdProducto = $IdProducto->IdProducto;
              $detalle->IdProducto_rel = $IdProducto->pro_rel;
            $detalle->procod = $IdProducto->procod;
            $detalle->pronom = $IdProducto->pronom;
            $detalle->cantidad = $cantidades[$index];
            $detalle->peso ="0.00";
            $detalle->umecod = $IdProducto->umecod;
            $detalle->IdCpe_guia =  $cabecera->IdCpe_guia; 
     
            $detalle->save();


        }
        
        $documento = self::generarpdfguia($cabecera->IdCpe_guia);
        
        

       if(!empty($documento)){


          exec("c:\WINDOWS\system32\cmd.exe /c START powershell.exe -ExecutionPolicy Bypass Start-Process -FilePath ".public_path()."/pdf/".$documento." -Verb Print");
        
        }
       
     
     
        return $cabecera->IdCpe_guia;

    }
    
    
     public function actualizarguia($request,$guia){

        
         
        $fecemi = $request->get('fecEmi');
        $rucemp = trim(Auth::user()->IdEmpresa);
        $empresa = Empresa::findOrFail($rucemp);
        $unidades = $request->get('unid');
        $proid = $request->get('proid');
        $detpro = $request->get('pronom');
        $cantidades = $request->get('cant');
       
        $part_suc = $request->get('part_suc');
        $part_alm = $request->get('almacen');
     
        $busalmpart = DB::tABLE('almacenes')
        ->where('id_almacen',$part_alm)
        ->leftjoin('cat_ubigeo','cat_ubigeo.ubi_cod','almacenes.ubigeo')
        ->first();

        $bussucpart = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$part_suc)->first();
        $emprepart = DB::tABLE('empresa')->where('IdEmpresa',$bussucpart->IdEmpresa)->first();

        $des_suc = $request->get('des_suc');
        $des_alm = $request->get('des_alm');
        $busalmdes = DB::tABLE('almacenes')
        ->where('id_almacen',$des_alm)
        ->leftjoin('cat_ubigeo','cat_ubigeo.ubi_cod','almacenes.ubigeo')
        ->first();
       

        $bussucdes = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$des_suc)->first();
       // $empredes = DB::tABLE('empresa')->where('IdEmpresa',$bussucdes->IdEmpresa)->first();

        $observaciones = $request->get('observaciones');

        $senudoc = DB::tABLE('empresa_negocios')->select('serieguia','numeroguia')->where('id_empresa_negocio',$part_suc)->first();



        $cabecera = guias_remision::findOrFail($guia);
        $cabecera->IdEmpresa =  Auth::user()->IdEmpresa;
        $cabecera->tdocod = '09';
        $cabecera->fechaemision = $fecemi;

        $cabecera->tdicod = $request->get('tdicod');
        $cabecera->ruccliente = $request->get('clinum');
        $cabecera->ubigeollegada = '';



        if(!empty($des_suc)){
            $cabecera->tdicod = '6';
            $cabecera->ruccliente = $bussucdes->IdEmpresa;
            $cabecera->ubigeollegada = '';
            $cabecera->direccionllegada = $busalmdes->direccion;
        }else{
            $cabecera->tdicod = $request->get('tdicod');
            $cabecera->ruccliente = $request->get('clinum');
            $cabecera->ubigeollegada = '';
            $cabecera->direccionllegada = $request->get('clidir');  
        }




      /*  if(!empty($request->get('clidir'))){
            $cabecera->direccionllegada = $request->get('clidir');
        }else{
            $cabecera->direccionllegada = $bussucdes->direccion;  
        }*/
        

        $cabecera->IdMotivo = '04';
        $cabecera->pesobruto = '0.00';
        $cabecera->umecod = 'KMG';
        $cabecera->fechatraslado = $fecemi;
   $cabecera->clicod = $request->get('clicod');
   
        $cabecera->ructransportista = $request->get('transportistanum');
        $cabecera->nombretransportista = $request->get('transportistanom');
        $cabecera->tdicodtransportista = $request->get('transportistatdicod');
        $cabecera->desubigeollegada ='';

        $cabecera->rucconductor = $request->get('conductornum');
        $cabecera->nomconductor = $request->get('conductornom');
        $cabecera->tdicodconductor = $request->get('conductortdicod');

        $cabecera->ubigeopartida = $busalmpart->ubigeo;
        $cabecera->direccionpartida = $busalmpart->direccion;
        $cabecera->desubigeopartida = $busalmpart->ubi_des;
      
        $cabecera->bultos = $request->get('bultos');
        $cabecera->correo = "";
        $cabecera->placa = $request->get('placa');
         $cabecera->licencia = $request->get('licencia');
        $cabecera->id_empresa_negocio =  $part_suc;
      //  $cabecera->datajson = $request->get('tdocod');
        $cabecera->IdModalidad = '01';

         if(!empty($des_suc)){
             $cabecera->nomcliente =  $bussucdes->tipo_negocio;
        }else{
            $cabecera->nomcliente = $request->get('clinom');
        }

   
          $cabecera->update();
          
          $codfact = $cabecera->IdCpe_guia; 

            $i=0;
         DB::tABLE('guias_remision_detalle')->where('IdCpe_guia',$guia)->delete();
         
        foreach( $proid as $index => $id ) {
            
            $i=$i+1;
           // $codproducto = $codpro[$index];
            
            $IdProducto = DB::tABLE('productos')
            ->WHERE('IdProducto',$id)
           // ->where('IdEmpresa',$rucemp)
            //->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)
            ->first();
          
          //  $codproducto = $codpro[$index];

            $detalle = new guias_remision_detalle;
            $detalle->IdProducto = $IdProducto->IdProducto;
            $detalle->procod = $IdProducto->procod;
            $detalle->pronom = $IdProducto->pronom;
            $detalle->cantidad = $cantidades[$index];
            $detalle->peso ="0.00";
            $detalle->umecod = $IdProducto->umecod;
            $detalle->IdCpe_guia =  $cabecera->IdCpe_guia; 
     
            $detalle->save();


        }
        
        $documento = self::generarpdfguia($cabecera->IdCpe_guia);
        
        

      if(!empty($documento)){


          exec("c:\WINDOWS\system32\cmd.exe /c START powershell.exe -ExecutionPolicy Bypass Start-Process -FilePath ".public_path()."/pdf/".$documento." -Verb Print");
        
        }
       
     
     
        return $cabecera->IdCpe_guia;

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

    
  
        return $nompdffile;
    }


    public function registrar_stock_inicial(){

        $productos = DB::tABLE('productos')
        ->join('producto_stock','productos.IdProducto','producto_stock.IdProducto')
        ->where('tipo','1')
        ->where('stock_migrar','>','0')
        ->get();

        foreach($productos as $pro){

            $bus_alm = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();
            DB::tABLE('movimientos_productos')->insert([

                    'IdProducto'=>$pro->IdProducto,
                    'precio'=>'',
                    'cantidad'=>$pro->stock,
                    'costo'=>$pro->costo,
                    'mov_cab_id'=>'',
                    'stock'=>$pro->stock_migrar,
                    'IdProducto_rel'=>$pro->IdProducto,
                    'IdCpe_cabecera'=>'',
                    'com_cab_id'=>'',
                    'stock_inicial'=>$pro->stock,
                    'serie'=>'',
                    'numero'=>'',
                    'tdocod'=>'',
                    'tipo'=>'1',
                    'mov_tip'=>'I',
                    'descripcion'=>'STOCK_INICIAL',
                    'id_empresa_negocio'=>Auth::user()->id_empresa_negocio,
                    'id_almacen'=>$bus_alm->id_almacen,
                    'fecha_mov'=>now(),
                   

            ]); 
        }

        

    }



    public function registrar_compras(){

          $compras = DB::tABLE('compras_cabecera')
      ->join('compras_detalle','compras_detalle.com_cab_id','compras_cabecera.com_cab_id')

      ->leftjoin('proveedor','proveedor.prov_id','compras_cabecera.prov_id')
      ->where(function ($query) {
          $query->where('tdocod','01')
              ->orWhere('tdocod','03')
              ->orWhere('tdocod','13')
              ->orWhere('tdocod','14')
              ->orWhere('tdocod','50')
              ->orWhere('tdocod','09');
          })
      ->where('compras_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->where('est_compra','Registrado')
      ->orderby('com_fec','asc')
      ->get();



      foreach ($compras as $comp) {

                DB::tABLE('movimientos_productos')->insert([

                        'IdProducto'=>$comp->pro_id,
                        'precio'=>'',
                        'cliente'=>$comp->prov_raz,
                        'cantidad'=>$comp->cantidad,
                        'costo'=>$comp->pre_uni,
                        'mov_cab_id'=>'',
                        'stock'=>$comp->com_det_stock,
                        'IdProducto_rel'=>$comp->IdProducto_rel,
                        'IdCpe_cabecera'=>'',
                        'com_cab_id'=>$comp->com_cab_id,
                        'stock_inicial'=>$comp->com_det_stock_inicial,
                        'serie'=>$comp->com_doc_ser,
                        'numero'=>$comp->com_doc_num,
                        'tdocod'=>$comp->tdocod,
                        'tipo'=>'2',
                        'mov_tip'=>'I',
                        'descripcion'=>'COMPRAS',
                        'id_empresa_negocio'=>Auth::user()->id_empresa_negocio,
                        'id_almacen'=>$comp->id_almacen,
                        'fecha_mov'=>$comp->com_fec_ing,

                    ]);

      }



    }

    public function registrar_ventas(){



      $ventas = DB::tABLE('cpe_detalle')
      ->join('cpe_cabecera','cpe_cabecera.IdCpe_cabecera','cpe_detalle.IdCpe_cabecera')
      ->where(function ($query) {
          $query->where('tdocod','01')
              ->orWhere('tdocod','03')
              ->orWhere('tdocod','13')
              ->orWhere('tdocod','14');
          })
      ->where('cpe_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->orderby('ccafem','asc')
      ->whereNull('ccabaj')
      ->get();

        foreach ($ventas as $ven) {
        
        
               DB::tABLE('movimientos_productos')->insert([
                                'IdProducto'=>$ven->IdProducto,
                                'precio'=>$ven->cdepuni,
                                'cliente'=>$ven->ccanom,
                                'cantidad'=>$ven->cdecan,
                                'costo'=>$ven->costo,
                                'mov_cab_id'=>'',
                                'stock'=>$ven->cpe_det_stock,
                                'IdProducto_rel'=>$ven->IdProducto_rel,
                                'IdCpe_cabecera'=>$ven->IdCpe_cabecera,
                                'com_cab_id'=>'',
                                'stock_inicial'=>$ven->cpe_det_stock_inicial,
                                'serie'=>$ven->serdoc,
                                'numero'=>$ven->numdoc,
                                'tdocod'=>$ven->tdocod,
                                'tipo'=>'3',
                                'mov_tip'=>'E',
                                'descripcion'=>'VENTAS',
                                'id_empresa_negocio'=>Auth::user()->id_empresa_negocio,
                                'id_almacen'=>$ven->id_almacen,
                                'fecha_mov'=>$ven->ccafem,


                           ]);
     
      
        
        }




    }

    public function registrar_transferencias_salidas(){


      $trans_salidas = DB::tABLE('movimientos_cabecera')
      ->select('movimientos_cabecera.mov_cab_id','movimientos_detalle.IdProducto','movimientos_detalle.IdProducto_rel','movimientos_detalle.cantidad','movimientos_detalle.precio','movimientos_detalle.stock','guias_remision.tdocod','guias_remision.serieguia','guias_remision.numeroguia','guias_remision.tdocod','part_alm','fecha','serdoc','numdoc','des_alm')
      ->join('movimientos_detalle','movimientos_detalle.mov_cab_id','movimientos_cabecera.mov_cab_id')
      ->join('guias_remision','guias_remision.IdCpe_guia','movimientos_cabecera.IdCpe_guia')
      ->where('movimientos_cabecera.tdocod','81')
      ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->where('estado','Registrado')
      ->orderby('fecha','asc')
      ->get();


    

        foreach ($trans_salidas as $ts) {
        
        try{
               DB::tABLE('movimientos_productos')->insert([
                                'IdProducto'=>$ts->IdProducto,
                                'precio'=>'',
                                'cliente'=>'',
                                'cantidad'=>$ts->cantidad,
                                'costo'=>$ts->precio,
                                'mov_cab_id'=>$ts->mov_cab_id,
                                'stock'=>$ts->stock,
                                'IdProducto_rel'=>$ts->IdProducto_rel,
                                'IdCpe_cabecera'=>'',
                                'com_cab_id'=>'',
                                'serie'=>$ts->serdoc,
                                'numero'=>$ts->numdoc,
                                'serieguia'=>$ts->serieguia,
                                'numeroguia'=>$ts->numeroguia,
                                'tdocod'=>$ts->tdocod,
                                'tipo'=>'3',
                                'mov_tip'=>'E',
                                'descripcion'=>'TRANSFERENCIA_SALIDA',
                                'id_empresa_negocio'=>Auth::user()->id_empresa_negocio,
                                'id_almacen'=>$ts->part_alm,
                                 'id_almacen_destino'=>$ts->des_alm,
                                //'id_almacen_destino'=>$ts->des_suc,
                               // 'id_almacen_origen'=>$ts->part_alm,
                                'fecha_mov'=>$ts->fecha,


                           ]);

                



        }catch(\Exception $e){

                dd($e);

        }
      
        
        }


     

    }


    public function registrar_transferencias_salidas_guias(){


      $trans_salidas = DB::tABLE('guias_remision')
        ->select('mov_cab_id','guias_remision_detalle.IdProducto','guias_remision_detalle.IdProducto_rel','guias_remision_detalle.cantidad','guias_remision.tdocod','guias_remision.serieguia','guias_remision.numeroguia','guias_remision.tdocod','fechaemision')
      ->join('guias_remision_detalle','guias_remision_detalle.IdCpe_guia','guias_remision.IdCpe_guia')
      ->leftjoin('movimientos_cabecera','movimientos_cabecera.IdCpe_guia','guias_remision.IdCpe_guia')
      ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->where('estado','Registrado')

      ->get();

       $i=0;
     foreach ($trans_salidas as $ts){

        if(!empty($ts->mov_cab_id)){
            
            $i=$i+1;

            echo $i.'<br>';
        }
     }

     dd('asd');

        foreach ($trans_salidas as $ts) {
        
        try{
               DB::tABLE('movimientos_productos')->insert([
                                'IdProducto'=>$ts->IdProducto,
                                'precio'=>'',
                                'cliente'=>'',
                                'cantidad'=>$ts->cantidad,
                                'costo'=>'0.00',
                                'mov_cab_id'=>'',
                                'stock'=>'0.00',
                                'IdProducto_rel'=>$ts->IdProducto_rel,
                                'IdCpe_cabecera'=>'',
                                'com_cab_id'=>'',
                                'serie'=>$ts->serieguia,
                                'numero'=>$ts->numeroguia,
                                'tdocod'=>$ts->tdocod,
                                'tipo'=>'3',
                                'mov_tip'=>'E',
                                'descripcion'=>'TRANSFERENCIA_SALIDA',
                                'id_empresa_negocio'=>Auth::user()->id_empresa_negocio,
                                'id_almacen'=>$ts->part_alm,
                                //'id_almacen_destino'=>$ts->des_suc,
                               // 'id_almacen_origen'=>$ts->part_alm,
                                'fecha_mov'=>$ts->fechaemision,


                           ]);
        }catch(\Exception $e){

                dd($e);

        }
      
        
        }


     

    }

    public function registrar_transferencias_ingresos(){


      $trans_ingresos = DB::tABLE('movimientos_cabecera')
      ->select('movimientos_cabecera.mov_cab_id','movimientos_detalle.IdProducto','movimientos_detalle.IdProducto_rel','movimientos_detalle.cantidad','movimientos_detalle.precio','movimientos_detalle.stock','guias_remision.tdocod','guias_remision.serieguia','guias_remision.numeroguia','guias_remision.tdocod','part_alm','fecha','serdoc','numdoc','des_suc')
      ->join('movimientos_detalle','movimientos_detalle.mov_cab_id','movimientos_cabecera.mov_cab_id')
      ->join('guias_remision','guias_remision.IdCpe_guia','movimientos_cabecera.IdCpe_guia')
      ->where('movimientos_cabecera.tdocod','82')
      ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->where('estado','Registrado')
      ->orderby('fecha','asc')
      ->get();

         foreach ($trans_ingresos as $ti) {
        
        try{
                DB::tABLE('movimientos_productos')->insert([
                                'IdProducto'=>$ti->IdProducto,
                                'precio'=>'',
                                'cliente'=>'',
                                'cantidad'=>$ti->cantidad,
                                'costo'=>$ti->precio,
                                'mov_cab_id'=>$ti->mov_cab_id,
                                'stock'=>$ti->stock,
                                'IdProducto_rel'=>$ti->IdProducto_rel,
                                'IdCpe_cabecera'=>'',
                                'com_cab_id'=>'',
                                'serie'=>$ti->serdoc,
                                'numero'=>$ti->numdoc,
                                'serieguia'=>$ti->serieguia,
                                'numeroguia'=>$ti->numeroguia,
                                'tdocod'=>$ti->tdocod,
                                'tipo'=>'3',
                                'mov_tip'=>'E',
                                'descripcion'=>'TRANSFERENCIA_INGRESO',
                                'id_empresa_negocio'=>Auth::user()->id_empresa_negocio,
                                'id_almacen'=>$ti->des_suc,
                              //  'id_almacen_destino'=>$ti->des_suc,
                              //  'id_almacen_origen'=>$ti->part_alm,
                                'fecha_mov'=>$ti->fecha,


                           ]);

        }catch(\Exception $e){

            dd($e);
        }
      
        
        }


    
    }



     public function movimiento_producto(Request $request){

    $negocios = DB::tABLE('empresa_negocios')->get();

    $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();


    $almacen = $request->get('almacen');
    $sucursal = $request->get('sucursal');
    $tipo = $request->get('docomp');
    $fecin = $request->get('fecin');
    $fecfin = $request->get('fecfin');
     $IdProducto = $request->get('IdProducto');

     if(!empty($IdProducto)){

        $productoslista = DB::tABLE('productos')->select('IdProducto','pronom','procod','costo_total','flete','umecod')
    ->where('tipo','=','1')
    ->where('promocion','!=','2')
    //->where('id_empresa_negocio',$sucursal)
    ->orderby('pronom','asc')
    ->get();



      $saldo_anterior =0;


      $productos = DB::tABLE('productos')->select('IdProducto','pronom','procod','costo_total','flete','umecod',DB::raw("(SELECT COUNT(*) FROM movimientos_productos WHERE IdProducto_rel=productos.IdProducto Limit 1) as contar"))
      ->where('tipo','=','1')
      ->where(function ($query) use ($IdProducto){
          if($IdProducto!='Todos'){
             $query->where('IdProducto','=',$IdProducto); 
          }      
      }) 
      ->having('contar','>','0')
      ->where('id_empresa_negocio',$sucursal)
      ->orderby('IdProducto','asc')
      ->get();

      
    
      $dat_alm = DB::tABLE('almacenes')->where('id_almacen',$almacen)->first();
      $dat_suc = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();
      $dat_emp = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

      $saldos_actuales = [];
      $saldo_anterior=0;
      $kardex =[];
      $kardex_mov=[];
      $array_productos = [];


     
          foreach($productos as $p){

               $kardex_mov = [];
               $ingresos = 0;
               $salidas = 0;
               $saldo_anterior=0;

              $buspro = $p->IdProducto;


              $ingresos = DB::tABLE('movimientos_productos')->select('cantidad')
             ->where('mov_tip','I')
             ->WHERE('fecha_mov','<',$fecin)
             ->where('IdProducto_rel',$buspro)
             ->where('id_empresa_negocio',$sucursal)
             ->where(function ($query) use ($almacen) {
                    if($almacen!='Todos'){
                      $query->where('id_almacen','=',$almacen); 
                    }      
                }) 
             ->sum('cantidad');

             $salidas = DB::tABLE('movimientos_productos')->select('cantidad')
             ->where('mov_tip','E')
              ->WHERE('fecha_mov','<',$fecin)
             ->where('IdProducto_rel',$buspro)
             ->where('id_empresa_negocio',$sucursal)
             ->where(function ($query) use ($almacen) {
                    if($almacen!='Todos'){
                      $query->where('id_almacen','=',$almacen); 
                    }      
                }) 
             ->sum('cantidad');

    
              $saldo_anterior = $ingresos-$salidas;

              if($saldo_anterior!=0){
          
                   $kardex_mov[] = array(
                      'fecha'=>$fecin,
                      'tdocod'=>'',
                      'serie'=>'',
                      'numero'=>'',
                      'cliente'=>'SALDO_ANTERIOR',
                      'cantidad'=>$saldo_anterior,
                      'IdProducto'=>$buspro,
                      'IdProducto_rel'=>$buspro,
                      'destino'=>'',
                      'origen'=>'',
                      'descripcion'=>'SALDO ANTERIOR',
                      'cod_tip_ope'=>'16',
                      'mov_tip'=>'I',
                      'tipo'=>'0',
                      'costo'=>'',
                      'stock'=>'0',
                      'mov_lote'=>'',
                      'mov_vencimiento'=>''
                    );
              }


              $movimientos = DB::tABLE('movimientos_productos')
              ->select('mov_vencimiento','mov_lote','cod_tip_ope','tipo','fecha_mov','tdocod','serie','numero','cliente','stock_inicial','cantidad','stock','IdProducto_rel','IdProducto','descripcion','mov_tip','costo')
              ->where(function ($query) use ($almacen){
              if($almacen!='Todos'){
                  $query->where('id_almacen','=',$almacen); 
                }      
              }) 
               ->where(function ($query) use ($buspro){
                  $query->where('IdProducto','=',$buspro)
                  ->orWhere('IdProducto_rel',$buspro); 
                    
              }) 
              ->where('fecha_mov','>=',$fecin)
              ->where('fecha_mov','<=',$fecfin)
              ->orderby('fecha_mov','asc')
              ->orderby('tipo','asc')
              ->get();  

            

              foreach ($movimientos as $mov) {
                
                      $kardex_mov[] = array(
                      'fecha'=>$mov->fecha_mov,
                      'tdocod'=>$mov->tdocod,
                      'serie'=>$mov->serie,
                      'numero'=>$mov->numero,
                      'cliente'=>$mov->cliente,
                      'cantidad'=>$mov->cantidad,
                      'IdProducto'=>$mov->IdProducto,
                      'IdProducto_rel'=>$mov->IdProducto_rel,
                      'destino'=>'',
                      'origen'=>'',
                      'descripcion'=>$mov->descripcion,
                      'mov_tip'=>$mov->mov_tip,
                      'costo'=>$mov->costo,
                      'cod_tip_ope'=>$mov->cod_tip_ope,
                      'tipo'=>$mov->tipo,
                      'stock'=>'0',
                      'mov_lote'=>$mov->mov_lote,
                      'mov_vencimiento'=>$mov->mov_vencimiento

                    );
                
              }



           

           

              $array_productos[] = array(
                  'codigo'=> $p->procod,
                  'unidad'=>$p->umecod,
                  'producto'=>$p->pronom,
                  'movimientos' =>array(
                    $kardex_mov
                  )

              );


        }
 


   $id_producto = array_column($kardex_mov, 'IdProducto_rel');
   $fec_mov = array_column($kardex_mov, 'fecha');
   $num_mov  = array_column($kardex_mov, 'tipo');
   $tipo_mov = array_column($kardex_mov, 'mov_tip');

   array_multisort($id_producto, SORT_ASC,$fec_mov, SORT_ASC,$num_mov, SORT_ASC, $tipo_mov, SORT_DESC, $kardex_mov);


     
 

      if($tipo=='2'){
         return view('empresas.reportes.kardexfisico',compact('dat_alm','dat_suc','movimientos','productos','negocios','almacenes','sucursal','almacen','dat_suc','dat_alm','dat_emp','fecin','fecfin','productoslista','saldos_actuales','kardex','IdProducto','array_productos'));
      }elseif($tipo=='1'){
          return view('empresas.reportes.kardexvalorizado',compact('dat_alm','dat_suc','movimientos','productos','negocios','almacenes','sucursal','almacen','dat_suc','dat_alm','dat_emp','fecin','fecfin','productoslista','saldos_actuales','kardex','IdProducto','array_productos'));
      }
     

     }else{

         $productoslista = DB::tABLE('productos')
      ->where('tipo','=','1')
      ->where('promocion','!=','2')
      ->get();

      $productos = DB::tABLE('productos')
      ->where('tipo','=','1')
      ->get();
  
      $negocios = DB::tABLE('empresa_negocios')->get();
      $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();

          return view('empresas.almacen.movimientoproducto',compact('productos','negocios','almacenes','productoslista'));
     }
     
    



 }
 

public function calcular_movimientos($IdProducto){

    //$productos = DB::tABLE('productos')->where('IdProducto',$IdProducto)->get();

    $productos = DB::tABLE('productos')->where('tipo','1')->get();

    $almacenes = DB::tABLE('almacenes')->get();


    foreach($almacenes as $alm){

        foreach($productos as $pro){
      
            $movimientos = DB::TABLE('movimientos_productos')
            ->where(function ($query) use ($pro) {
                    $query->where('IdProducto',$pro->IdProducto)
                        ->orWhere('IdProducto_rel',$pro->IdProducto);
            })
            ->where('id_almacen',$alm->id_almacen)
            ->orderby('fecha_mov','asc')
            ->orderby('mov_tip','desc')
            ->orderby('tipo','asc')
            ->get();

            $stock = 0;
            $i=0;

           foreach($movimientos as $mov){
                if($i==0){

                     if($mov->descripcion=='STOCK_INICIAL' || $mov->descripcion=='SALDO_ANTERIOR'){
                             $stock = $mov->cantidad;
                       }else{
                            if($mov->mov_tip=='I'){
                                    $stock = $mov->cantidad;
                            }else{
                                    $stock = ($mov->cantidad*-1);
                            }
                        }
                    DB::TABLE('movimientos_productos')->where('mov_pro_id',$mov->mov_pro_id)->update(['stock'=>$stock]);

                }else{
                       
                       if($mov->descripcion=='STOCK_INICIAL' || $mov->descripcion=='SALDO_ANTERIOR'){
                             $stock = $mov->cantidad;
                       }else{
                          if($mov->mov_tip=='I'){
                            $stock =$stock + $mov->cantidad;
                           }else{
                             $stock =$stock - $mov->cantidad;
                           }
                       }
                      
                      

                      DB::TABLE('movimientos_productos')->where('mov_pro_id',$mov->mov_pro_id)->update(['stock'=>$stock]);
                }

                    $i = $i+1;

            }

            DB::tABLE('producto_stock')->where('IdProducto',$pro->IdProducto)->update(['stock'=>$stock]);


        }  

    }
   

    return Redirect::to('/stockproductos');


   
}

 public function registrar_ajustar_stock(Request $request)
    {   

            $sucursal = $request->get('suc_id');
            $almacen = $request->get('alm_id');
            $cant = $request->get('cantidad');
            
            $IdProducto = $request->get('IdProducto');

             
           $fecha_ajus = Carbon::now();

            $bus_stock = DB::tABLE('producto_stock')->where('IdProducto',$IdProducto)->where('id_almacen',$almacen)->first();

            $cantidad = $cant - $bus_stock->stock;

            $fecha = $fecha_ajus->subDay(30)->format('Y-m-d');
            

              if($cantidad<0){

                 $cantidad = $cantidad*(-1);
                 $mov_tipo = 'E';
                 $tipo = '2';

                $tdocod = '84';

                $senudoc = DB::tABLE('empresa_negocios')->select('AESer','AENum')->where('id_empresa_negocio',$sucursal)->first();
                $numdoc =  $senudoc->AENum+1;
                $sercomp =  $senudoc->AESer;


                $emp_nego = EmpresaNegocios::findOrFail($sucursal);
                $emp_nego->AENum = $numdoc;
                $emp_nego->update();


    
              }else{

                $cantidad = $cantidad;
                $mov_tipo = 'I';
                $tipo = '3';

                $tdocod = '83';
                $senudoc = DB::tABLE('empresa_negocios')->select('AISer','AINum')->where('id_empresa_negocio',$sucursal)->first();
                $numdoc =  $senudoc->AINum+1;
                $sercomp =  $senudoc->AISer;


                $emp_nego = EmpresaNegocios::findOrFail($sucursal);
                $emp_nego->AINum = $numdoc;
                $emp_nego->update();




              }


             DB::tABLE('producto_stock')
             ->where('IdProducto',$IdProducto)
             ->update(['stock'=>$cant,
              'stock_inicial'=>$cant]);



               DB::tABLE('movimientos_productos')->insert([
                    'IdProducto'=>$IdProducto,
                    'precio'=>'',
                    'cantidad'=>$cantidad,
                    'costo'=>'',
                    'mov_cab_id'=>'',
                    'stock'=>'',
                    'IdProducto_rel'=>$IdProducto,
                    'IdCpe_cabecera'=>'',
                    'com_cab_id'=>'',
                    'stock_inicial'=>'',
                    'serie'=>$sercomp,
                    'numero'=>$numdoc,
                    'tdocod'=>$tdocod,
                    'tipo'=>$tipo,
                    'mov_tip'=>$mov_tipo,
                    'descripcion'=>'AJUSTE',
                    'id_empresa_negocio'=>$sucursal,
                    'id_almacen'=>$almacen,
                    'fecha_mov'=>$fecha_ajus,
            ]);



            $mov_cal_stock = new Almacen();
            $mov_cal_stock->movimiento_calcular_stock($IdProducto,$fecha);


        return Redirect::to('/stockproductos');


    }

    public function generarinventario(Request $request){

        $fecha = '2022-08-01';

        $fechafin = '2022-08-31';

        $almacen = '1';
       

             $stock= DB::table("productos")
          ->select("productos.*",
                     DB::raw("(SELECT stock FROM movimientos_productos
                                WHERE movimientos_productos.IdProducto = productos.IdProducto
                                and movimientos_productos.id_almacen='".$almacen."'
                                and movimientos_productos.fecha_mov<'".$fecha."'
                                ORDER BY fecha_mov desc, mov_tip asc, tipo desc LIMIT 1) as stock_anterior"),

                    DB::raw("(SELECT SUM(cantidad) FROM movimientos_productos
                                WHERE movimientos_productos.IdProducto = productos.IdProducto
                                AND mov_tip='I' and movimientos_productos.id_almacen='".$almacen."'
                                and movimientos_productos.fecha_mov>='".$fecha."'
                                 and movimientos_productos.fecha_mov<='".$fechafin."'
                                GROUP BY movimientos_productos.IdProducto) as Ingresos"),

                    DB::raw("(SELECT SUM(cantidad) FROM movimientos_productos
                                WHERE movimientos_productos.IdProducto = productos.IdProducto
                                AND mov_tip='E' and movimientos_productos.id_almacen='".$almacen."'
                                 and movimientos_productos.fecha_mov>='".$fecha."'
                                 and movimientos_productos.fecha_mov<='".$fechafin."'
                                GROUP BY movimientos_productos.IdProducto) as Egresos"))
          //->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->where('productos.tipo','1')
          ->get();

          foreach($stock as $s){
                echo $s->pronom.'=> saldo:'.$s->stock_anterior.' | '.'ingresos: '.$s->Ingresos.'  '.'egresos: '.$s->Egresos.'<br>';
          }

    }
          /*  $movimientos = DB::TABLE('movimientos_productos')
            ->where('IdProducto_rel',$pro->IdProducto)
            ->where('id_almacen',$alm->id_almacen)
            ->orderby('fecha_mov','desc')
            ->orderby('mov_tip','asc')
            ->orderby('tipo','desc')
            ->get();*/


    public function buscar_almacen_predeterminado(Request $request, $id_empresa_negocio){

        $bus_alm = new Almacen;
        $almacen = $bus_alm->buscar_almacen_predeterminado($id_empresa_negocio);

        $vista = view('empresas.almacen.div_almacen',compact('almacen'))->render();

        if($request->ajax()){
          return response()->json(['vista'=>$vista]);

        }
    }

    public function buscar_almacenes(Request $request, $id_empresa_negocio){

        $bus_alm = new Almacen;
        $almacen = $bus_alm->buscar_almacenes($id_empresa_negocio);

        $vista = view('empresas.almacen.div_almacen',compact('almacen'))->render();

        if($request->ajax()){
          return response()->json(['vista'=>$vista]);

        }
    }


    public function corte_inventario($fecini,$fecfin,$fecsig)
    {
        


          $sucursal = Auth::user()->id_empresa_negocio;
          $bus_alm = DB::tABLE('almacenes')->where('id_empresa_negocio',$sucursal)->where('predeterminado','1')->first();

          $almacen = $bus_alm->id_almacen
          ;
          $fec_ini = $fecini;
          $fec_fin = $fecfin;
          $rucemp = trim(Auth::user()->IdEmpresa);
          $empresa = Empresa::findOrFail($rucemp);
          $saldos_actuales = [];

          $dat_neg = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();

        

          $dat_alm =  DB::tABLE('almacenes')->where('id_almacen',$almacen)->first(); 
  

          $inventario= DB::table("productos")
          ->select("productos.*",

                       DB::raw("(SELECT SUM(cantidad) FROM movimientos_productos
                                WHERE movimientos_productos.IdProducto = productos.IdProducto
                                AND mov_tip='I' and movimientos_productos.id_almacen='".$almacen."'
                                and movimientos_productos.fecha_mov>='".$fec_ini."'
                                and movimientos_productos.fecha_mov<='".$fec_fin."'
                                GROUP BY movimientos_productos.IdProducto) as Ingresos_ant"),

                      DB::raw("(SELECT SUM(cantidad) FROM movimientos_productos
                                WHERE movimientos_productos.IdProducto = productos.IdProducto
                                AND mov_tip='E' and movimientos_productos.id_almacen='".$almacen."'
                                and movimientos_productos.fecha_mov>='".$fec_ini."'
                                and movimientos_productos.fecha_mov<='".$fec_fin."'
                                GROUP BY movimientos_productos.IdProducto) as Egresos_ant"),

                    DB::raw("(SELECT SUM(cantidad) FROM movimientos_productos
                                WHERE movimientos_productos.IdProducto = productos.IdProducto
                                AND mov_tip='I' and movimientos_productos.id_almacen='".$almacen."'
                                and movimientos_productos.fecha_mov>='".$fec_ini."'
                                 and movimientos_productos.fecha_mov<='".$fec_fin."'
                                GROUP BY movimientos_productos.IdProducto) as Ingresos"),

                    DB::raw("(SELECT SUM(cantidad) FROM movimientos_productos
                                WHERE movimientos_productos.IdProducto = productos.IdProducto
                                AND mov_tip='E' and movimientos_productos.id_almacen='".$almacen."'
                                 and movimientos_productos.fecha_mov>='".$fec_ini."'
                                 and movimientos_productos.fecha_mov<='".$fec_fin."'
                                GROUP BY movimientos_productos.IdProducto) as Egresos"))
          //->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
              // ->having('contar','>','0')
          ->where('productos.tipo','1')
              ->where('productos.promocion','!=','2')
              ->orderby('pronom','asc')
          ->get();


 
        foreach($inventario as $inv){

                   DB::tABLE('movimientos_productos')->insert([

                    'IdProducto'=>$inv->IdProducto,
                    'precio'=>'',
                    'cantidad'=>$inv->Ingresos_ant-$inv->Egresos_ant,
                    'costo'=>'',
                    'mov_cab_id'=>'',
                    'stock'=>'',
                    'IdProducto_rel'=>$inv->IdProducto,
                    'IdCpe_cabecera'=>'',
                    'com_cab_id'=>'',
                    'stock_inicial'=>'',
                    'serie'=>'',
                    'numero'=>'',
                    'tdocod'=>'',
                    'tipo'=>'1',
                    'mov_tip'=>'I',
                    'descripcion'=>'SALDO_ANTERIOR',
                    'id_empresa_negocio'=>Auth::user()->id_empresa_negocio,
                    'id_almacen'=>$almacen,
                    'fecha_mov'=>$fecsig,
                   

            ]);

             $mov_cal_stock = new Almacen();
            $mov_cal_stock->movimiento_calcular_stock($inv->IdProducto,$fec_ini);

        }

        }
      

        Public function cierreMensualAlmacen(Request $request){

         
            $almacenes = new Almacen;
            $listAlm = $almacenes->buscar_almacenes(Auth::user()->id_empresa_negocio);

            return view('empresas.almacenes.cierre_mensual_almacen',compact('almacenes'));
}

}