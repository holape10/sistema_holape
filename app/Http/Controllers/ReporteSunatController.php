<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\TipoIGV;
use MasterSoft\ReportesVentas;
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

class ReporteSunatController extends Controller
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


    public function index(Request $request){

        return view('empresas.reportes_sunat.index');
    }
    
    public function generar_venta_txt(Request $request){

        $mes = $request->get('mes');

        $ano = substr($mes,0,4);
        $mes = substr($mes,5,2);

        $ventas = DB::tABLE('cpe_cabecera')
        ->whereMonth('ccafem','=',$mes)
        ->whereYear('ccafem','=',$ano)
           ->where(function ($query) {
          $query->where('tdocod','01')
              ->orWhere('tdocod','03')
              ->orWhere('tdocod','07')
              ->orWhere('tdocod','08');
          })

        ->where(function ($query) {
                $query->where('ccanot','')
                ->orWhere('ccanot','!=','')
;
        })  
        ->get();

        $rutapdf = public_path().'/txt/';
        
        $nompdffile = 'ventas'.$mes.$ano.'.txt';

        if(file_exists($rutapdf.$nompdffile)){

            unlink($rutapdf.$nompdffile);
        }

         $archivo = fopen($rutapdf.$nompdffile,'a');  

        foreach($ventas as $ven){

                if(empty($ven->ccabaj)){
                    $estado ='1';
                }else{
                    $estado = '2';
                }

              $contenido = $ano.$mes.'00'.'|'.$mes.str_pad($ven->IdCpe_cabecera,7,'0',STR_PAD_LEFT).'|M'.$mes.str_pad($ven->IdCpe_cabecera,7,'0',STR_PAD_LEFT).'|'.Carbon::parse($ven->ccafem)->format('d/m/Y').'|'.Carbon::parse($ven->ccafve)->format('d/m/Y').'|'.$ven->tdocod.'|'.$ven->serdoc.'|'.$ven->numdoc.'||'.$ven->tdicod.'|'.$ven->ccandi.'|'.$ven->ccanom.'|0.00|'.$ven->ccatvg.'|0.00|'.$ven->ccaigv.'|||||||0.00||'.$ven->ccaitv.'|'.$ven->moncod.'|1.000|'.$ven->fecha_ref.'|'.$ven->tdocod_ref.'|'.$ven->serie_ref.'|'.$ven->num_ref.'||||'.$estado.'|'."\r\n";

               fputs($archivo,$contenido); 

        }
       

        
        
         fclose($archivo);

        if (file_exists($rutapdf.$nompdffile))
        {
            $headers = array(
              'Content-Type: application/pdf',
            );

            return response()->download($rutapdf.$nompdffile);

        }

    }



    public function generar_compra_txt(Request $request){

        $mes = $request->get('mes');

        $ano = substr($mes,0,4);
        $mes = substr($mes,5,2);


        $compras = DB::tABLE('compras_cabecera')
        ->leftjoin('proveedor','proveedor.prov_id','compras_cabecera.prov_id')
        ->whereMonth('com_fec','=',$mes)
        ->whereYear('com_fec','=',$ano)
        ->where('est_compra','Registrado')
        ->get();

        $rutapdf = public_path().'/txt/';
        
        $nompdffile = 'compras'.$mes.$ano.'.txt';

        if(file_exists($rutapdf.$nompdffile)){

            unlink($rutapdf.$nompdffile);
        }

          $archivo = fopen($rutapdf.$nompdffile,'a');  

        foreach($compras as $comp){
             if($comp->est_compra=='Registrado'){
                    if($comp->tdocod=='03'){
                        $estado ='0';
                    }else{
                        $estado ='1';
                    }
                    
                }else{
                    $estado = '2';
                }
             $contenido = $ano.$mes.'00'.'|'.$mes.str_pad($comp->com_cab_id,7,'0',STR_PAD_LEFT).'|M'.$mes.str_pad($comp->com_cab_id,7,'0',STR_PAD_LEFT).'|'.Carbon::parse($comp->com_fec)->format('d/m/Y').'|'.Carbon::parse($comp->com_fec_ven)->format('d/m/Y').'|'.$comp->tdocod.'|'.$comp->com_doc_ser.'||'.$comp->com_doc_num.'||'.$comp->tdicod.'|'.$comp->prov_ruc.'|'.$comp->prov_raz.'|'.number_format($comp->com_grav,'2','.','').'|'.number_format($comp->com_cab_igv,'2','.','').'|||||'.number_format($comp->com_exo+$comp->com_inaf,'2','.','').'||0.00||'.number_format($comp->total_com,'2','.','').'|'.$comp->mon_id.'|||||||||||||||||'.$estado.'|'."\r\n";


               fputs($archivo,$contenido); 

        }
       

        
        
         fclose($archivo);
        if (file_exists($rutapdf.$nompdffile))
        {
            $headers = array(
              'Content-Type: application/pdf',
            );

            return response()->download($rutapdf.$nompdffile);

        }   
    }


}
