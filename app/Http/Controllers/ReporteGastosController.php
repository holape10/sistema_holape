<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\Modelos\Gastos;
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

class ReporteGastosController extends Controller
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


    public function reporteGastos(Request $request)
    {
        
        $negocios = DB::tABLE('empresa_negocios')->get();
        $sucursal = $request->get('suc_id');

        return view('empresas.reportes.gastos.buscar_gastos',compact('negocios','sucursal'));
    }


    public function generarReporteGastos(Request $request){

    	$suc_id = $request->get('suc_id');
    	$fec_ini = $request->get('fec_ini');
    	$fec_fin = $request->get('fec_fin');
    	$tip_rep = $request->get('tip_rep');

    	$sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$suc_id)->first();

    	$cons_gast = new Gastos();
    	$gastos = $cons_gast->obtenerGastos($suc_id,$fec_ini,$fec_fin);
    	$gastos_elim = $cons_gast->obtenerGastosEliminados($suc_id,$fec_ini,$fec_fin);
    	$gastos_deta = $cons_gast->obtenerGastosDetallado($suc_id,$fec_ini,$fec_fin);
    	$gastos_deta_elim = $cons_gast->obtenerGastosDetalladoEliminados($suc_id,$fec_ini,$fec_fin);

    	if($tip_rep=='1'){

    		 $vista = view('empresas.reportes.gastos.formatos.reporte_gastos',compact('fec_ini','fec_fin','gastos','gastos_elim','gastos_deta','gastos_deta_elim','sucursal'))->render();

    	}elseif($tip_rep=='2'){

    		 $vista = view('empresas.reportes.gastos.formatos.reporte_gastos_detallado',compact('fec_ini','fec_fin','gastos','gastos_elim','gastos_deta','gastos_deta_elim','sucursal'))->render();

    	}


        if($request->ajax()){
            return response()->json(['vista'=>$vista]);

        }

    }

    
     public function generarExcelGastos(Request $request){
        
       
        $suc_id = $request->get('suc_id');
        $fec_ini = $request->get('fec_ini');
        $fec_fin = $request->get('fec_fin');
        $tip_rep = $request->get('tip_rep');

        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$suc_id)->first();

        $cons_gast = new Gastos();
        $gastos = $cons_gast->obtenerGastos($suc_id,$fec_ini,$fec_fin);
        $gastos_elim = $cons_gast->obtenerGastosEliminados($suc_id,$fec_ini,$fec_fin);
        $gastos_deta = $cons_gast->obtenerGastosDetallado($suc_id,$fec_ini,$fec_fin);
        $gastos_deta_elim = $cons_gast->obtenerGastosDetalladoEliminados($suc_id,$fec_ini,$fec_fin);

        if($tip_rep=='1'){

            Excel::create('Reporte_Gastos', function($excel) use ($fec_ini,$fec_fin,$gastos,$gastos_elim,$gastos_deta,$gastos_deta_elim,$sucursal) {
                        $excel->sheet('Reporte_Ventas', function($sheet) use ($fec_ini,$fec_fin,$gastos,$gastos_elim,$gastos_deta,$gastos_deta_elim,$sucursal) {
                                  $sheet->loadView('empresas.reportes.gastos.formatos.reporte_gastos',compact('fec_ini','fec_fin','gastos','gastos_elim','gastos_deta','gastos_deta_elim','sucursal'));
                        });

            })->export('xlsx'); 

           

        }elseif($tip_rep=='2'){

              Excel::create('Reporte_Gastos_Detallado', function($excel) use ($fec_ini,$fec_fin,$gastos,$gastos_elim,$gastos_deta,$gastos_deta_elim,$sucursal) {
                        $excel->sheet('Reporte_Gastos_Detallado', function($sheet) use ($fec_ini,$fec_fin,$gastos,$gastos_elim,$gastos_deta,$gastos_deta_elim,$sucursal) {
                                  $sheet->loadView('empresas.reportes.gastos.formatos.reporte_gastos_detallado',compact('fec_ini','fec_fin','gastos','gastos_elim','gastos_deta','gastos_deta_elim','sucursal'));
                        });

            })->export('xlsx'); 

        }




        if($request->ajax()){
            return response()->json(['vista'=>$vista]);

        }

        
  }
   


  public function generarPDFGastos(Request $request){

          
        $suc_id = $request->get('suc_id');
        $fec_ini = $request->get('fec_ini');
        $fec_fin = $request->get('fec_fin');
        $tip_rep = $request->get('tip_rep');

        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$suc_id)->first();

        $cons_gast = new Gastos();
        $gastos = $cons_gast->obtenerGastos($suc_id,$fec_ini,$fec_fin);
        $gastos_elim = $cons_gast->obtenerGastosEliminados($suc_id,$fec_ini,$fec_fin);
        $gastos_deta = $cons_gast->obtenerGastosDetallado($suc_id,$fec_ini,$fec_fin);
        $gastos_deta_elim = $cons_gast->obtenerGastosDetalladoEliminados($suc_id,$fec_ini,$fec_fin);


        if($tip_rep=='1'){

          $pdf = PDF::loadView('empresas.reportes.gastos.formatos.reporte_gastos',compact('fec_ini','fec_fin','gastos','gastos_elim','gastos_deta','gastos_deta_elim','sucursal'));
             return $pdf->stream('document.pdf');

                $headers = array(
                        'Content-Type: application/pdf',
                );


        }elseif($tip_rep=='2'){

          $pdf = PDF::loadView('empresas.reportes.gastos.formatos.reporte_gastos_detallado',compact('fec_ini','fec_fin','gastos','gastos_elim','gastos_deta','gastos_deta_elim','sucursal'));
             return $pdf->stream('document.pdf');

                $headers = array(
                        'Content-Type: application/pdf',
                );

        }


        if($request->ajax()){
            return response()->json(['vista'=>$vista]);

        }

        
  }



}
