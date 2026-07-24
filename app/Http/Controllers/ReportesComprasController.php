<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\Modelos\ReportesCompras;
use MasterSoft\TipoIGV;
use MasterSoft\Empresa;
use MasterSoft\EmpresaNegocios;
use MasterSoft\Cliente;
use MasterSoft\cpe_cabecera;
use MasterSoft\documento_relacionado;
use MasterSoft\unidad_medida;
use MasterSoft\cpe_detalle;
use MasterSoft\cpe_baja;
use MasterSoft\Comprobante;
use MasterSoft\compras_cabecera;
use MasterSoft\cpe_nota_detalle;
use MasterSoft\gastos_cabecera;
use MasterSoft\cpe_nota;
use MasterSoft\User;
use MasterSoft\tipo_documento_identidad;
use MasterSoft\tipo_documento;
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
use DB;
use Carbon;
use Excel;
use PDF;

class ReportesComprasController extends Controller
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


    pubLic function generarReporteCompras(Request $request){

        $tip_rep = $request->get('tip_rep');
        $fec_ini = $request->get('fec_ini');
        $fec_fin = $request->get('fec_fin');
        $suc_id = $request->get('suc_id');
        $id_almacen = $request->get('almacen');
        $prov_id = $request->get('prov_id');
          $dat_prov ='';
        $dat_suc = EmpresaNegocios::findOrFail($suc_id);

        $empresa = Auth::user()->IdEmpresa;


        $cons_compras = new ReportesCompras();

        $factura = $cons_compras->obtenerFactura($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $boleta = $cons_compras->obtenerBoleta($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $otras_compras = $cons_compras->obtenerOtrasCompras($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $nota_credito = $cons_compras->obtenerNotaCredito($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $vales_comp = $cons_compras->obtenerValesCompras($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $total_vales_comp = $cons_compras->obtenerTotalValesCompras($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
      //  $venta_sunat = $cons_compras->obtenerVentaSunat($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $total_contado = $cons_compras->obtenerTotalContado($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $total_credito = $cons_compras->obtenerTotalCredito($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $total_compras = $cons_compras->obtenerTotalCompras($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $total_compras_bolfac = $cons_compras->obtenerTotalComprasBoletasFacturas($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $total_otras_compras = $cons_compras->obtenerTotalOtrasCompras($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $total_notas_creditos = $cons_compras->obtenerTotalNotasCreditos($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $igv_compras = $cons_compras->obtenerIGVCompras($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $igv_notas_creditos = $cons_compras->obtenerIGVNotasCreditos($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $compra = $cons_compras->obtenerCompra($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $detalle = $cons_compras->obtenerCompraDetalle($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $vent_res_prod = $cons_compras->obtenerResumenComprasProductos($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
       
        $comp_bolfac_res_prod = $cons_compras->obtenerResumenComprasBoletasFacturasProductos($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $not_cre_res_prod = $cons_compras->obtenerResumenNotasCreditosProductos($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $otr_comp_res_prod = $cons_compras->obtenerResumenOtrasComprasProductos($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $val_comp_res_prod = $cons_compras->obtenerResumenValesComprasProductos($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
       
        $compras_res_prov = $cons_compras->obtenerResumenComprasProveedor($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);

          if($tip_rep=='1'){

            $vista = view('empresas.reportes.compras.formatos.reporte_compras',compact('fec_ini','fec_fin','factura','boleta','otras_compras','nota_credito','vales_comp','total_contado','total_credito','total_compras','total_vales_comp','total_notas_creditos','total_compras_bolfac','total_otras_compras','dat_prov'))->render();

          }elseif($tip_rep=='2'){
              
            $vista = view('empresas.reportes.compras.formatos.reporte_compras_detalle',compact('fec_ini','fec_fin','factura','boleta','otras_compras','nota_credito','vales_comp','total_contado','total_credito','total_compras','total_vales_comp','total_notas_creditos','total_compras_bolfac','total_notas_creditos','total_otras_compras','dat_prov','val_comp_res_prod','comp_bolfac_res_prod','detalle','comp_res_prod','not_cre_res_prod','compra','otr_comp_res_prod'))->render();

          }elseif($tip_rep=='3'){

            $vista = view('empresas.reportes.compras.formatos.reporte_compras',compact('fec_ini','fec_fin','comprobantes','total','dat_prov'))->render();

          }elseif($tip_rep=='4'){
              
            $vista =view('empresas.reportes.compras.formatos.reporte_compras',compact('fec_ini','fec_fin','total','totalexoneradasfacturas','totalexoneradasboletas','totalgravadasfacturas','totalgravadasboletas','totalgravadasnotas','totalexoneradasnotas','empresa','sucursal'))->render();

          }


        if($request->ajax()){
            return response()->json(['vista'=>$vista]);

        }



    }



    pubLic function generarExcelCompras(Request $request){

        $tip_rep = $request->get('tip_rep');
        $fec_ini = $request->get('fec_ini');
        $fec_fin = $request->get('fec_fin');
        $suc_id = $request->get('suc_id');
        $id_almacen = $request->get('almacen');
        $prov_id = $request->get('prov_id');
        $dat_prov ='';
        $dat_suc = EmpresaNegocios::findOrFail($suc_id);

        $empresa = Auth::user()->IdEmpresa;


        $cons_compras = new ReportesCompras();

        $factura = $cons_compras->obtenerFactura($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $boleta = $cons_compras->obtenerBoleta($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $otras_compras = $cons_compras->obtenerOtrasCompras($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $nota_credito = $cons_compras->obtenerNotaCredito($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $vales_comp = $cons_compras->obtenerValesCompras($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $total_vales_comp = $cons_compras->obtenerTotalValesCompras($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
      //  $venta_sunat = $cons_compras->obtenerVentaSunat($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $total_contado = $cons_compras->obtenerTotalContado($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $total_credito = $cons_compras->obtenerTotalCredito($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $total_compras = $cons_compras->obtenerTotalCompras($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $total_compras_bolfac = $cons_compras->obtenerTotalComprasBoletasFacturas($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $total_otras_compras = $cons_compras->obtenerTotalOtrasCompras($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $total_notas_creditos = $cons_compras->obtenerTotalNotasCreditos($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $igv_compras = $cons_compras->obtenerIGVCompras($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $igv_notas_creditos = $cons_compras->obtenerIGVNotasCreditos($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $compra = $cons_compras->obtenerCompra($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $detalle = $cons_compras->obtenerCompraDetalle($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $vent_res_prod = $cons_compras->obtenerResumenComprasProductos($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
       
        $comp_bolfac_res_prod = $cons_compras->obtenerResumenComprasBoletasFacturasProductos($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $not_cre_res_prod = $cons_compras->obtenerResumenNotasCreditosProductos($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $otr_comp_res_prod = $cons_compras->obtenerResumenOtrasComprasProductos($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $val_comp_res_prod = $cons_compras->obtenerResumenValesComprasProductos($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
       
        $compras_res_prov = $cons_compras->obtenerResumenComprasProveedor($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);

          if($tip_rep=='1'){


           Excel::create('Reporte_Compras', function($excel) use ($fec_ini,$fec_fin,$factura,$boleta,$otras_compras,$nota_credito,$vales_comp,$total_contado,$total_credito,$total_compras,$total_vales_comp,$total_notas_creditos,$total_compras_bolfac,$total_otras_compras,$dat_prov) {

                        $excel->sheet('Reporte_Compras', function($sheet) use ($fec_ini,$fec_fin,$factura,$boleta,$otras_compras,$nota_credito,$vales_comp,$total_contado,$total_credito,$total_compras,$total_vales_comp,$total_notas_creditos,$total_compras_bolfac,$total_otras_compras,$dat_prov) {


                                  $sheet->loadView('empresas.reportes.compras.formatos.reporte_compras',compact('fec_ini','fec_fin','factura','boleta','otras_compras','nota_credito','vales_comp','total_contado','total_credito','total_compras','total_vales_comp','total_notas_creditos','total_compras_bolfac','total_otras_compras','dat_prov'));
                                 

                        });

          })->export('xlsx'); 



          }elseif($tip_rep=='2'){
                
                 Excel::create('Reporte_Compras_Detallado', function($excel) use ($fec_ini,$fec_fin,$factura,$boleta,$otras_compras,$nota_credito,$vales_comp,$total_contado,$total_credito,$total_compras,$total_vales_comp,$total_notas_creditos,$total_compras_bolfac,$total_otras_compras,$dat_prov,$val_comp_res_prod,$comp_bolfac_res_prod,$detalle,$comp_res_prod,$not_cre_res_prod,$compra,$otr_comp_res_prod) {

                        $excel->sheet('Reporte_Compras_Detallado', function($sheet) use ($fec_ini,$fec_fin,$factura,$boleta,$otras_compras,$nota_credito,$vales_comp,$total_contado,$total_credito,$total_compras,$total_vales_comp,$total_compras_bolfac,$total_notas_creditos,$total_otras_compras,$dat_prov,$val_comp_res_prod,$comp_bolfac_res_prod,$detalle,$comp_res_prod,$not_cre_res_prod,$compra,$otr_comp_res_prod) {


                                  $sheet->loadView('empresas.reportes.compras.formatos.reporte_compras_detalle',compact('fec_ini','fec_fin','factura','boleta','otras_compras','nota_credito','vales_comp','total_contado','total_credito','total_compras','total_vales_comp','total_notas_creditos','total_compras_bolfac','total_otras_compras','dat_prov','val_comp_res_prod','comp_bolfac_res_prod','detalle','comp_res_prod','not_cre_res_prod','compra','otr_comp_res_prod'));
                                 

                        });

              
          })->export('xlsx'); 

          }elseif($tip_rep=='3'){

              Excel::create('Reporte_Compras', function($excel) use ($fec_ini,$fec_fin,$comprobantes,$total,$dat_prov) {

                        $excel->sheet('Reporte_Compras', function($sheet) use ($fec_ini,$fec_fin,$comprobantes,$total,$dat_prov) {


                                  $sheet->loadView('empresas.reportes.compras.formatos.reporte_compras',compact('fec_ini','fec_fin','comprobantes','total','dat_prov'));
                                 

                        });


          })->export('xlsx'); 

          
          }elseif($tip_rep=='4'){
                
                    Excel::create('Reporte_Compras', function($excel) use ($fec_ini,$fec_fin,$total,$totalexoneradasfacturas,$totalexoneradasboletas,$totalgravadasfacturas,$totalgravadasboletas,$totalgravadasnotas,$totalexoneradasnotas,$empresa,$sucursal) {

                        $excel->sheet('Reporte_Compras', function($sheet) use ($fec_ini,$fec_fin,$total,$totalexoneradasfacturas,$totalexoneradasboletas,$totalgravadasfacturas,$totalgravadasboletas,$totalgravadasnotas,$totalexoneradasnotas,$empresa,$sucursal) {


                                  $sheet->loadView('empresas.reportes.compras.formatos.reporte_compras',compact('fec_ini','fec_fin','total','totalexoneradasfacturas','totalexoneradasboletas','totalgravadasfacturas','totalgravadasboletas','totalgravadasnotas','totalexoneradasnotas','empresa','sucursal'));
                                 

                        });

            
          })->export('xlsx'); 

          }





    }


    pubLic function generarPDFCompras(Request $request){

        $tip_rep = $request->get('tip_rep');
        $fec_ini = $request->get('fec_ini');
        $fec_fin = $request->get('fec_fin');
        $suc_id = $request->get('suc_id');
        $id_almacen = $request->get('almacen');
        $prov_id = $request->get('prov_id');
          $dat_prov ='';
        $dat_suc = EmpresaNegocios::findOrFail($suc_id);

        $empresa = Auth::user()->IdEmpresa;


        $cons_compras = new ReportesCompras();

        $factura = $cons_compras->obtenerFactura($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $boleta = $cons_compras->obtenerBoleta($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $otras_compras = $cons_compras->obtenerOtrasCompras($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $nota_credito = $cons_compras->obtenerNotaCredito($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $vales_comp = $cons_compras->obtenerValesCompras($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $total_vales_comp = $cons_compras->obtenerTotalValesCompras($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
      //  $venta_sunat = $cons_compras->obtenerVentaSunat($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $total_contado = $cons_compras->obtenerTotalContado($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $total_credito = $cons_compras->obtenerTotalCredito($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $total_compras = $cons_compras->obtenerTotalCompras($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $total_compras_bolfac = $cons_compras->obtenerTotalComprasBoletasFacturas($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $total_otras_compras = $cons_compras->obtenerTotalOtrasCompras($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $total_notas_creditos = $cons_compras->obtenerTotalNotasCreditos($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $igv_compras = $cons_compras->obtenerIGVCompras($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $igv_notas_creditos = $cons_compras->obtenerIGVNotasCreditos($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $compra = $cons_compras->obtenerCompra($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $detalle = $cons_compras->obtenerCompraDetalle($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $vent_res_prod = $cons_compras->obtenerResumenComprasProductos($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
       
        $comp_bolfac_res_prod = $cons_compras->obtenerResumenComprasBoletasFacturasProductos($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $not_cre_res_prod = $cons_compras->obtenerResumenNotasCreditosProductos($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $otr_comp_res_prod = $cons_compras->obtenerResumenOtrasComprasProductos($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
        $val_comp_res_prod = $cons_compras->obtenerResumenValesComprasProductos($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);
       
        $compras_res_prov = $cons_compras->obtenerResumenComprasProveedor($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id);

          if($tip_rep=='1'){


            $pdf = PDF::loadView('empresas.reportes.compras.formatos.reporte_compras',compact('fec_ini','fec_fin','factura','boleta','otras_compras','nota_credito','vales_comp','total_contado','total_credito','total_compras','total_vales_comp','total_notas_creditos','total_compras_bolfac','total_otras_compras','dat_prov'));

             return $pdf->stream('document.pdf');

                $headers = array(
                        'Content-Type: application/pdf',
                );

           

          }elseif($tip_rep=='2'){
              

            $pdf = PDF::loadView('empresas.reportes.compras.formatos.reporte_compras_detalle',compact('fec_ini','fec_fin','factura','boleta','otras_compras','nota_credito','vales_comp','total_contado','total_credito','total_compras','total_vales_comp','total_notas_creditos','total_compras_bolfac','total_notas_creditos','total_otras_compras','dat_prov','val_comp_res_prod','comp_bolfac_res_prod','detalle','comp_res_prod','not_cre_res_prod','compra','otr_comp_res_prod'));

             return $pdf->stream('document.pdf');

                $headers = array(
                        'Content-Type: application/pdf',
                );


          }elseif($tip_rep=='3'){

                   $pdf = PDF::loadView('empresas.reportes.compras.formatos.reporte_compras_detalle',compact('fec_ini','fec_fin','comprobantes','total','dat_prov'));

             return $pdf->stream('document.pdf');

                $headers = array(
                        'Content-Type: application/pdf',
                );



          }elseif($tip_rep=='4'){
              
              
              $pdf = PDF::loadView('empresas.reportes.compras.formatos.reporte_compras',compact('fec_ini','fec_fin','total','totalexoneradasfacturas','totalexoneradasboletas','totalgravadasfacturas','totalgravadasboletas','totalgravadasnotas','totalexoneradasnotas','empresa','sucursal'));

               return $pdf->stream('document.pdf');

                $headers = array(
                        'Content-Type: application/pdf',
                );


          }


        if($request->ajax()){
            return response()->json(['vista'=>$vista]);

        }



    }


   pubLic function reporte_compras_excel(Request $request){

      $opcion = $request->get('opcion');
      $fecin = $request->get('fecin');
      $fecfin = $request->get('fecfin');
      $sucursal = $request->get('sucursal');
      $almacen = $request->get('almacen');
      $data_sucursal = EmpresaNegocios::findOrFail($sucursal);

      $empresa = Empresa::findorFail(Auth::user()->IdEmpresa);

        switch ($opcion){
            case 11:

                 $comprobantes = DB::tABLE('compras_cabecera')
                  ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
                  ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
                  ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
                  ->where('compras_cabecera.com_fec','>=',$fecin)
                  ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where('est_compra','Registrado')
                    ->where(function ($query) use ($sucursal){
                      if(!empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                     
                  ->where(function ($query6) use ($almacen) {
                      if(!empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
                
                  ->where('compras_cabecera.tdocod','!=','80')
                  ->orderby('com_cab_id','desc')
                  ->get();
             

                  $total = $comprobantes->sum('total_com');


                   Excel::create('COMPRAS', function($excel) use ($data_sucursal,$empresa,$fecin,$fecfin,$total,$comprobantes) {

                        $excel->sheet('COMPRAS', function($sheet) use ($data_sucursal,$empresa,$fecin,$fecfin,$total,$comprobantes) {

                       
                            
                                  $sheet->loadView('formatos_reportes_excel.compras',compact('data_sucursal','empresa','fecin','fecfin','total','comprobantes'));
                          
                                

                        });

                    })->export('xlsx'); 

                

                break;

                      case 12:

                 $comprobantes = DB::tABLE('compras_cabecera')
                 ->leftjoin('compras_detalle','compras_detalle.com_cab_id','compras_cabecera.com_cab_id')
                 ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
                  ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
                  ->leftjoin('unidad_medida','unidad_medida.umecod','compras_detalle.ume_cod')
                  ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
                  ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
                  ->where('compras_cabecera.com_fec','>=',$fecin)
                  ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where('est_compra','Registrado')
                    ->where(function ($query) use ($sucursal){
                      if(!empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                     
                  ->where(function ($query6) use ($almacen) {
                      if(!empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
                
                  ->where('compras_cabecera.tdocod','!=','80')
                  ->orderby('compras_cabecera.com_cab_id','desc')
                  ->get();
             

                  $total = $comprobantes->sum('total_com');


                   Excel::create('COMPRAS DETALLADO', function($excel) use ($data_sucursal,$empresa,$fecin,$fecfin,$total,$comprobantes) {

                        $excel->sheet('COMPRAS_DETALLADO', function($sheet) use ($data_sucursal,$empresa,$fecin,$fecfin,$total,$comprobantes) {

                       
                            
                                  $sheet->loadView('formatos_reportes_excel.compras_detalladas',compact('data_sucursal','empresa','fecin','fecfin','total','comprobantes'));
                          
                                

                        });

                    })->export('xlsx'); 

  

               
                break;

                  case 3:

                   $comprobantes = DB::tABLE('compras_cabecera')
              ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
              ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
              ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
              ->where('est_compra','Registrado')
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where(function ($query) use ($sucursal){
                      if(!empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
            
                  ->where(function ($query6) use ($almacen) {
                      if(!empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
               ->where(function ($query){
                    $query->where('compras_cabecera.tdocod','!=','80');
                })
              ->orderby('compras_cabecera.com_cab_id','desc')
              ->get();
                 //dd($comprobantes);

                  $total = $comprobantes->sum('total_com');



                 Excel::create('REGISTRO COMPRAS SUNAT', function($excel) use ($data_sucursal,$empresa,$fecin,$fecfin,$total,$comprobantes) {

                        $excel->sheet('REGISTRO_COMPRAS_SUNAT', function($sheet) use ($data_sucursal,$empresa,$fecin,$fecfin,$total,$comprobantes) {

                          $sheet->setWidth(array(
                            'A'     =>  1000000000
                        ));

                            
                                  $sheet->loadView('formatos_reportes_excel.compras_contador',compact('fecin','fecfin','comprobantes','total','dat_ven','dat_cli'));
                          
                                

                        });

                    })->export('xlsx'); 



              break;
        };


    }


    pubLic function reporte_compras_pdf(Request $request){
      
      $opcion = $request->get('opcion');
      $fecin = $request->get('fecin');
      $fecfin = $request->get('fecfin');
      $sucursal = $request->get('sucursal');
      $almacen = $request->get('almacen');
      $data_sucursal = EmpresaNegocios::findOrFail($sucursal);

      $empresa = Empresa::findorFail(Auth::user()->IdEmpresa);

        switch ($opcion){
            case 1:

                 $comprobantes = DB::tABLE('compras_cabecera')
                  ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
                  ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
                  ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
                  ->where('compras_cabecera.com_fec','>=',$fecin)
                  ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where('est_compra','Registrado')
                    ->where(function ($query) use ($sucursal){
                      if(!empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                     
                  ->where(function ($query6) use ($almacen) {
                      if(!empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
                
                  ->where('compras_cabecera.tdocod','!=','80')
                  ->orderby('com_cab_id','desc')
                  ->get();
             

                  $total = $comprobantes->sum('total_com');


                 $rutapdf = public_path().'/pdfreportes/';

                  $nompdffile = 'Reporte_Compras_'.$fecin.'_'.$fecfin.'.pdf';

                 if(file_exists($rutapdf.$nompdffile)){

                      unlink($rutapdf.$nompdffile);
                  }

                
                  $view = \View::make('formatos_reportes.reporte_pdf_compras', compact('data_sucursal','empresa','fecin','fecfin','total','comprobantes'));

                            
                  $pdf = \App::make('dompdf.wrapper');
                  $contenido = $view->render();
                  $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

                 if (file_exists($rutapdf.$nompdffile)){
                    $headers = array(
                        'Content-Type: application/pdf',
                      );

                  return response()->download($rutapdf.$nompdffile);

                }

                break;

                      case 2:

                 $comprobantes = DB::tABLE('compras_cabecera')
                 ->leftjoin('compras_detalle','compras_detalle.com_cab_id','compras_cabecera.com_cab_id')
                 ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
                  ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
                  ->leftjoin('unidad_medida','unidad_medida.umecod','compras_detalle.ume_cod')
                  ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
                  ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
                  ->where('compras_cabecera.com_fec','>=',$fecin)
                  ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where('est_compra','Registrado')
                    ->where(function ($query) use ($sucursal){
                      if(!empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                     
                  ->where(function ($query6) use ($almacen) {
                      if(!empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
                
                  ->where('compras_cabecera.tdocod','!=','80')
                  ->orderby('compras_cabecera.com_cab_id','desc')
                  ->get();
             

                  $total = $comprobantes->sum('total_com');


                 $rutapdf = public_path().'/pdfreportes/';

                  $nompdffile = 'Reporte_Compras_'.$fecin.'_'.$fecfin.'.pdf';

                 if(file_exists($rutapdf.$nompdffile)){

                      unlink($rutapdf.$nompdffile);
                  }

                
                  $view = \View::make('formatos_reportes.reporte_pdf_compras_productos', compact('data_sucursal','empresa','fecin','fecfin','total','comprobantes'));

                            
                  $pdf = \App::make('dompdf.wrapper');
                  $contenido = $view->render();
                  $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

                 if (file_exists($rutapdf.$nompdffile)){
                    $headers = array(
                        'Content-Type: application/pdf',
                      );

                  return response()->download($rutapdf.$nompdffile);

                }

                break;



        };

    }




}
