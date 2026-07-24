<?php

namespace MasterSoft\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MasterSoft\compras_cabecera;
use MasterSoft\compras_detalle;
use MasterSoft\cpe_cabecera;
use MasterSoft\cpe_detalle;
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

class OrdenesServiciosController extends Controller
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
            if(!is_null($proveedor)){
                $query1->where('compras_cabecera.prov_id',$proveedor);
            }
            
        })
        ->where('com_fec','>=',$fecin)
        ->where('com_fec','<=',$fecfin)
        ->where('compras_cabecera.tdocod','87')
        ->orderby('com_cab_id','desc')
        ->get();



        return view('empresas.ordenesservicios.index',compact('compras','negocios','sucursal','proveedores','proveedor','fecin','fecfin'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
 
   
     public function editarcompra($id)
    {

        $categorias = DB::tABLE('categorias')->get();

        $negocios = EmpresaNegocios::get();

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
        ->leftjoin('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
        ->where('com_cab_id',$id)
        ->first();

        $detalle= DB::tABLE('compras_detalle as cd')
        ->leftjoin('unidad_medida as um','um.umecod','cd.ume_cod')
        ->leftjoin('productos as p','p.IdProducto','cd.pro_id')
        ->where('com_cab_id',$id)
        ->get();

        return view('empresas.ordenesservicios.editarordenservicio',compact('igv','monedas','unidades','docidentidad','fecha','doccomprobante','cpe','detalle','cabecera','negocios','categorias'));

    }


   


    public function ordenservicio()
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
		
		
        return view('empresas.ordenesservicios.ordenservicio',compact('almacen','sucursal','igv','monedas','unidades','docidentidad','fecha','doccomprobante','negocios','creditos','almacenes','categorias'));

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
        $serie = $request->get('serdoc');
        $numero = $request->get('numdoc');
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

        
          $senudoc = DB::tABLE('empresa_negocios')->select('SerOC','NumOC')->where('id_empresa_negocio','=',$sucursal)->first();
          $numcomp =  $senudoc->NumOC+1;
          $sercomp =  $senudoc->SerOC;
       

    
        $proveedor = Proveedor::FirstOrCreate(['prov_ruc'=>$prov_num,'IdEmpresa'=>$rucemp],['prov_raz'=>$prov_raz,'tdicod'=>$tdicod,'prov_cor'=>$prov_cor,'prov_dir'=>$prov_dir,'IdEmpresa'=>$rucemp]);
    
        $compras->prov_id = $proveedor->prov_id;
        $compras->tip_cam = $request->get('camdoc');
        $compras->total_com = $request->get('total');
        $compras->comp_obs = $request->get('obser');
        $compras->tdocod = '87';
        $compras->tipocompra = $request->get('tipocompra');
        $compras->id_empresa_negocio = $sucursal;
        $compras->IdEmpresa = $rucemp;
        $compras->tot_con = $request->get('total');
        $compras->tot_cre = '0';

        $empresanegocio = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);
     
     
          if($empresanegocio->NumOC == $numcomp){
              $modnumcomp = $numcomp+1;
          }else{
              $modnumcomp = $numcomp;
          }

          $empresanegocio->SerOC = $sercomp;
          $empresanegocio->NumOC = $modnumcomp;

          $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
          $compras->com_doc_ser= $serie;
          $compras->com_doc_num = $numero;
    
     
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

   //     dd($codpro);
if(!empty($detpro)){

   foreach($detpro as $index => $pro ) {

        if(!empty($pro)){

            $compras_det = new compras_detalle;
           
            $compras_det->pre_uni = $preuni[$index];
            $compras_det->total= $vtot[$index];
            $compras_det->cantidad= $cantidades[$index];
            $compras_det->detalle_orden = $detpro[$index];
            $compras_det->ume_cod = 'ZZ';
            $compras_det->com_cab_id= $compras->com_cab_id;
            $compras_det->tip_igv = '20';
            $compras->detalle_orden = $pro;
            $compras_det->IdEmpresa= $rucemp;
            $compras_det->save();

    
        }

      }

}
   

      //  self::generarpdfgeneral($compras->com_cab_id,$sucursal);


        if($request->ajax()) {
          return response()->json(['mensaje' => 'Orden de Servicio Registrada']);
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

      
      $view = \View::make('formatos_comprobantes.ordenservicio', compact('cabpdf','detpdf','totalletras','empresa','imgqr','sucursal','qrfile'));

                  
      $pdf = \App::make('dompdf.wrapper');
      $contenido = $view->render();
      $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

    
  
        return 'realizado';
    }


 

    public function actualizarordenservicio(Request $request)
    {   

        $id = $request->get('id');
        $rucemp = trim(Auth::user()->IdEmpresa);
        $tdicod = $request->get('tdicod');
        $prov_num = $request->get('clinum');
        $prov_raz = $request->get('clinom');
        $sucursal = $request->get('sucursal');
     
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
        $compras->tip_cam = $request->get('camdoc');
        $compras->total_com = $request->get('total');
        $compras->comp_obs = $request->get('obser');
        $compras->tdocod = $request->get('cmbTdo');
        $compras->tipocompra = $request->get('tipocompra');
        $compras->id_empresa_negocio = $sucursal;
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

    foreach($codpro as $index => $pro ) {

         
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

        return Redirect::to('/ordenesservicios');


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

 
        return Redirect::to('/ordenesservicios');
    }

    public function detalleordenservicio($id){
        
        $rucemp = trim(Auth::user()->IdEmpresa);

        $compra = DB::tABLE('compras_detalle as cd')
        ->leftjoin('unidad_medida','unidad_medida.umecod','cd.ume_cod')
        ->where('com_cab_id',$id)
        ->where('cd.IdEmpresa',$rucemp)
        ->get();

        return view('empresas.ordenesservicios.detalles',compact('compra'));
    }

}
