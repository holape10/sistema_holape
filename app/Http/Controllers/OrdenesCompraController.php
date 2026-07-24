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
use MasterSoft\Proveedor;
use MasterSoft\productos;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use MasterSoft\MontoLetras;
use DB;
use PDF;

class OrdenesCompraController extends Controller
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

        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');

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
            if(!is_null($sucursal)){
                $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
            }
           
        })
        ->where(function ($query1) use ($proveedor){
            if($proveedor!='Todos'){
                $query1->where('compras_cabecera.prov_id',$proveedor);
            }
            
        })
        ->where('com_fec','>=',$fecin)
        ->where('com_fec','<=',$fecfin)
        ->where('compras_cabecera.tdocod','80')
        ->orderby('com_cab_id','desc')
        ->get();



        return view('empresas.ordenescompra.index',compact('compras','negocios','sucursal','proveedores','proveedor','fecin','fecfin'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
 
   
     public function editar_orden_compra($id)
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

       
        $doccomprobante = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->where('formulario','3')->orderBy('tdocod','asc')->get();

   
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

        return view('empresas.ordenescompra.editarcompra',compact('igv','monedas','unidades','docidentidad','fecha','doccomprobante','detalle','cabecera','negocios','creditos','almacenes','categorias','id','laboratorios'));


    }


   


    public function crear_orden_compra()
    {

         $categorias = DB::tABLE('categorias')->get();

        $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();

        $negocios = DB::tABLE('empresa_negocios')->get();

        $almacenes = DB::tABLE('almacenes')->get();
        
        // consultar unidades de medida
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();


        // consultar tipos de documentos de identidad
        $docidentidad = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

        //consultar tipo de documento
        $doccomprobante = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->where('formulario','3')->orderBy('tdocod','asc')->get();

        // obtener el ruc de la empresa en la cual se logueo
        $rucemp = trim(Auth::user()->IdEmpresa);


        // consultar tipos de monedas
        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

        //$negocios = EmpresaNegocios::where('estado','Activo')->get();
        // consultar la serie y numero de factura

        $fecha = now()->format('m/d/Y');

        $creditos = DB::tABLE('credito_dias')->get();

           
        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

      
        
        $almacen = DB::tABLE('almacenes')
        ->where('predeterminado','1')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();
        
        
      $productos = DB::tABLE('productos')
        ->select('costo','procod','marca','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND tipo='1' AND id_almacen='".$almacen->id_almacen."' AND id_empresa_negocio='".Auth::user()->id_empresa_negocio."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE productos.IdProducto = pro.pro_rel AND tipo='2') as cont_pre"))
        ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
        ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
      
        ->where('tipo','1')
        ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('id_almacen',$almacen->id_almacen)
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();
        
        
        return view('empresas.ordenescompra.compra',compact('almacen','productos','sucursal','igv','monedas','unidades','docidentidad','fecha','doccomprobante','negocios','creditos','almacenes','categorias'));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   

       

       // $buscre = DB::tABLE('credito_dias')->where('cre_dia_id',$estadopago)->first();


        $rucemp = trim(Auth::user()->IdEmpresa);
        $tdicod = $request->get('tdicod');
        $prov_num = $request->get('clinum');
        $prov_raz = $request->get('clinom');
        $sucursal = $request->get('sucursal');
     
        $prov_dir = $request->get('clidir');
        $prov_cor = $request->get('clicor');
        $moncod = $request->get('mondoc');
        $compras = new compras_cabecera;
        $compras->com_doc_ser = $request->get('serdoc');
        $compras->com_doc_num = $request->get('numdoc');
        $compras->com_fec = $request->get('fecEmi');
        $compras->com_fec_ven = $request->get('fecVen');
        $compras->com_fec_ing = $request->get('fecIng');
        $compras->id_almacen = $request->get('almacen');
   
        $compras->mon_id = $request->get('mondoc');

        
          $senudoc = DB::tABLE('empresa_negocios')->select('SerOS','NumOS')->where('id_empresa_negocio','=',$sucursal)->first();
          $numcomp =  $senudoc->NumOS+1;
          $sercomp =  $senudoc->SerOS;
       

    
        $proveedor = Proveedor::FirstOrCreate(['prov_ruc'=>$prov_num,'IdEmpresa'=>$rucemp],['prov_raz'=>$prov_raz,'tdicod'=>$tdicod,'prov_cor'=>$prov_cor,'prov_dir'=>$prov_dir,'IdEmpresa'=>$rucemp]);
    
        $compras->prov_id = $proveedor->prov_id;
        $compras->tip_cam = $request->get('camdoc');
        $compras->total_com = $request->get('total');
        $compras->comp_obs = $request->get('obser');
        $compras->tdocod = '80';
        $compras->tipocompra = $request->get('tipocompra');
        $compras->id_empresa_negocio = $sucursal;
        $compras->IdEmpresa = $rucemp;
        $compras->tot_con = $request->get('total');
        $compras->tot_cre = '0';

        $empresanegocio = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);
     
     
          if($empresanegocio->NumOS == $numcomp){
              $modnumcomp = $numcomp+1;
          }else{
              $modnumcomp = $numcomp;
          }

          $empresanegocio->SerOS = $sercomp;
          $empresanegocio->NumOS = $modnumcomp;

          $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
          $compras->com_doc_ser= $sercomp;
          $compras->com_doc_num = $numdoc;
    
     
        $empresanegocio->update();

      
        $compras->save();


        $cantidades = $request->get('cant');
        $unidades = $request->get('unid');
        $codpro = $request->get('pro_id');
        $detpro = $request->get('detpro');
        $preuni = $request->get('preuni');       
        $vtot = $request->get('vtot');
        $pro_id = $request->get('pro_id');
        $cant_uni = $request->get('cant_uni');
        $lote = $request->get('lote');
        $vencimiento = $request->get('vencimiento');
  

    foreach($codpro as $index => $pro ) {

            $buspro = DB::tABLE('productos')
            ->WHERE('IdProducto',$pro)
            ->first();

            if(empty($buspro->pro_rel)){

                $id = $pro;

            }else{
          
                $id = $buspro->pro_rel;

            }


            $compras_det = new compras_detalle;
            $compras_det->pro_id = $buspro->IdProducto;
            $compras_det->pre_uni = $preuni[$index];
            $compras_det->total= $vtot[$index];
            $compras_det->lote= $lote[$index];
            $compras_det->vencimiento= $vencimiento[$index];
            $compras_det->cantidad= $cantidades[$index];
            $compras_det->pro_id= $buspro->IdProducto;
            $compras_det->ume_cod = $buspro->umecod;
            $compras_det->com_cab_id= $compras->com_cab_id;
            $compras_det->tip_igv = $buspro->tigcod;
            $compras_det->IdEmpresa= $rucemp;
            $compras_det->save();

    
        }

        self::generarpdfgeneral($compras->com_cab_id,$sucursal);


        if($request->ajax()) {
          return response()->json(['mensaje' => 'Orden de Compra Registrada']);
        }

    
    }

        
    public function descargarorden($file)
    {

   
      $rutpdfile = public_path().'/pdf/';
      
      $file= $rutpdfile.'/'.$file.'.pdf';

      if(file_exists($file))
      {
        $headers = array(
              'Content-Type: application/pdf',
            );

        return response()->download($file);

      }

 
    }
    
    public function generarpdfgeneral($orden,$id_empresa_negocio){


      $rucemp =Auth::user()->IdEmpresa;
      $rutapdf = public_path().'/pdf/';

      $empresa = Empresa::findOrFail($rucemp);

      $cabpdf = DB::tABLE('compras_cabecera')
      ->leftjoin('moneda as mon','compras_cabecera.mon_id','=','mon.moncod')
      ->leftjoin('tipo_documento','tipo_documento.tdocod','compras_cabecera.tdocod')
      ->leftjoin('proveedor','proveedor.prov_id','compras_cabecera.prov_id')
      ->where('com_cab_id',$orden)
      ->first();

      $sucursal = DB::tABLE('empresa_negocios')
      ->where('id_empresa_negocio',$id_empresa_negocio)
      ->first();
        
   
      $detpdf = DB::tABLE('compras_detalle')
      ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
      ->where('com_cab_id',$orden)
      ->get();

      
                  
      $nompdffile=$cabpdf->IdEmpresa.'-'.$cabpdf->tdocod.'-'.$cabpdf->com_doc_ser.'-'.str_pad($cabpdf->com_doc_num,8,"0", STR_PAD_LEFT).'.pdf'; 

      $moneda = DB::tABLE('moneda')->where('moncod','=',$cabpdf->mon_id)->first();


      $totalletras= MontoLetras::convertir(number_format($cabpdf->total_com,'2','.',''),$moneda->monnom,'Centimos');

      $numdoc = str_pad($cabpdf->com_doc_num,8,"0", STR_PAD_LEFT);
        
      $qrfile =  'QR-'.$cabpdf->IdEmpresa.'-'.$cabpdf->tdocod.'-'.$cabpdf->com_doc_ser.'-'.str_pad($cabpdf->com_doc_num,8,"0", STR_PAD_LEFT).'.png'; 

      $imgqr = "/qr/".$qrfile;

      
      $view = \View::make('empresas.comprobantes.general.ordencompra', compact('cabpdf','detpdf','totalletras','empresa','imgqr','sucursal','qrfile'));

                  
      $pdf = \App::make('dompdf.wrapper');
      $contenido = $view->render();
      $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

    
  
        return 'realizado';
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

    public function actualizar_orden_compra(Request $request)
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
        $laboratorio = $request->get('laboratorio');
        $lote = $request->get('lote');
         $id_almacen_pro = $request->get('id_almacen_pro');


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
        
        $compras->estado_mercaderia = $estado_mercaderia;
       
      
        $compras->com_doc_num = $request->get('numdoc');
        $compras->com_fec = $request->get('fecEmi');
        $compras->tdocod='80';
        $compras->com_fec_ing = $request->get('fecIng');
        $compras->cre_dia_id = $estadopago;
        
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
     
        $compras->tipocompra = $request->get('tipocompra');
        $compras->id_empresa_negocio = $sucursal;
        $compras->orden_compra = $request->get('orden_compra');
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


       
    DB::tABLE('compras_detalle')->where('com_cab_id',$id)->delete();

    foreach($pro_id as $index1 => $pro ) {

            $IdProducto = productos::findOrFail($pro); 
            $IdProducto->vencimiento = $vencimiento[$index1];
            $IdProducto->lote = $lote[$index1];
            $IdProducto->lab_id = $laboratorio[$index1];
          
         
            if(empty($IdProducto->pro_rel)){

                $id_pro = $IdProducto->IdProducto;
                
            }else{

                 $id_pro = $IdProducto->pro_rel;

            }

              
            $IdProducto->update();

         


                $compras_det = new compras_detalle;
                $compras_det->pro_id = $IdProducto->IdProducto;
                $compras_det->pre_uni = $preuni[$index1];
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
                $compras_det->id_almacen_pro = $id_almacen_pro[$index1];
                $compras_det->save();

    

          



        }

         self::generarpdfgeneral($compras->com_cab_id,$sucursal);

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
            ->first();
        

            $stock_prod = DB::tABLE('producto_stock')
            ->where('pro_sto_id',$stock_prod->pro_sto_id)
            ->update(['stock'=>$stock_prod->stock-($det->cantidad*$producto->factor)]);

        }

       


        return Redirect::to('/ordenescompra');
    }

    public function detallecompras($id,$tipo){
        $rucemp = trim(Auth::user()->IdEmpresa);

        if($tipo =='1'){

         $compra = DB::tABLE('compras_detalle as cd')
        ->join('unidad_medida as um','um.umecod','cd.ume_cod')
        ->join('productos as p','p.IdProducto','cd.pro_id')
        ->where('com_cab_id',$id)->where('cd.IdEmpresa',$rucemp)->get();

        }else{
            
         $compra = DB::tABLE('compras_detalle as cd')
        ->join('unidad_medida as um','um.umecod','cd.ume_cod')
        ->where('com_cab_id',$id)->where('cd.IdEmpresa',$rucemp)->get();
        }
     



        return view('empresas.ordenescompra.detalles',compact('compra'));
    }

}
