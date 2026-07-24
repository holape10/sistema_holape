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

class ReportekardexController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     
    public function __construct()
    {
        
        $this->middleware('auth')->except(['consulta_stock_productos']);
    }

 


public function kardex(Request $request){

      $almacen = $request->get('almacen');
      $sucursal = $request->get('sucursal');
      $prod = $request->get('IdProducto');
      $docomp= $request->get('docomp');
      $fecin = $request->get('fecin');
      $fecfin = $request->get('fecfin');
  

      $productoslista = DB::tABLE('productos')
      ->where('tipo','=','1')
      ->where('promocion','!=','2')
      ->get();

      $productos = DB::tABLE('productos')
      ->where('tipo','=','1')
      ->get();
  
      $negocios = DB::tABLE('empresa_negocios')->get();
      $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();
     
       
    return view('empresas.reportes.kardex',compact('productos','negocios','almacenes','productoslista'));
     

    }




 public function buscarkardex(Request $request){

    $negocios = DB::tABLE('empresa_negocios')->get();

    $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();

    $almacen = $request->get('almacen');
    $sucursal = $request->get('sucursal');
    $tipo = $request->get('docomp');
    $fecin = $request->get('fecin');
    $fecfin = $request->get('fecfin');
    $IdProducto = $request->get('IdProducto');


     
    $productoslista = DB::tABLE('productos')->select('IdProducto','pronom','procod','costo_total','flete','umecod')
    ->where('tipo','=','1')
    ->where('promocion','!=','2')
    //->where('id_empresa_negocio',$sucursal)
    ->orderby('pronom','asc')
    ->get();



      $saldo_anterior =0;



      $productos = DB::tABLE('productos')->select('IdProducto','pronom','procod','costo_total','flete','umecod')
      ->where('tipo','=','1')
      ->where(function ($query) use ($IdProducto){
          if($IdProducto!='Todos'){
             $query->where('IdProducto','=',$IdProducto); 
          }      
      }) 

      ->orderby('IdProducto','asc')
      ->get();
      
      $dat_alm = DB::tABLE('almacenes')->where('id_almacen',$almacen)->first();
      $dat_suc = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();
      $dat_emp = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();



    if($tipo=='2'){

         return view('empresas.reportes.kardex.kardexfisico',compact('productos','dat_alm','dat_suc','negocios','almacenes','sucursal','almacen','dat_suc','dat_alm','dat_emp','fecin','fecfin','productoslista','IdProducto','fecin','fecfin'));
         
      }elseif($tipo=='1'){
          return view('empresas.reportes.kardex.kardexvalorizado',compact('productos','dat_alm','dat_suc','negocios','almacenes','sucursal','almacen','dat_suc','dat_alm','dat_emp','fecin','fecfin','productoslista','IdProducto','fecin','fecfin'));
      }

     
 



 }


 public function generarkardexexcel(Request $request){

  
     $negocios = DB::tABLE('empresa_negocios')->get();

    $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();

    $almacen = $request->get('almacen');
    $sucursal = $request->get('sucursal');
    $tipo = $request->get('docomp');
    $fecin = $request->get('fecin');
    $fecfin = $request->get('fecfin');
    $IdProducto = $request->get('IdProducto');


     
    $productoslista = DB::tABLE('productos')->select('IdProducto','pronom','procod','costo_total','flete','umecod')
    ->where('tipo','=','1')
    ->where('promocion','!=','2')
    //->where('id_empresa_negocio',$sucursal)
    ->orderby('pronom','asc')
    ->get();



      $saldo_anterior =0;


     $productos = DB::tABLE('productos')->select('IdProducto','pronom','procod','costo_total','flete','umecod')
      ->where('tipo','=','1')
      ->where(function ($query) use ($IdProducto){
          if($IdProducto!='Todos'){
             $query->where('IdProducto','=',$IdProducto); 
          }      
      }) 

      ->orderby('IdProducto','asc')
      ->get();
   
      $dat_alm = DB::tABLE('almacenes')->where('id_almacen',$almacen)->first();
      $dat_suc = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();
      $dat_emp = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

          ini_set("pcre.backtrack_limit", "50000000000");

     	if($tipo=='2'){

        Excel::create('kardex_fisico', function($excel) use ($productos,$dat_alm,$negocios,$almacenes,$sucursal,$almacen,$dat_suc,$dat_emp,$fecin,$fecfin,$IdProducto) {

                        $excel->sheet('kardex_fisico', function($sheet) use ($productos,$dat_alm,$negocios,$almacenes,$sucursal,$almacen,$dat_suc,$dat_emp,$fecin,$fecfin,$IdProducto) {

                       
                            
                                  $sheet->loadView('empresas.reportes.kardex.kardex_fisico_excel',compact('productos','dat_alm','negocios','almacenes','sucursal','almacen','dat_suc','dat_emp','fecin','fecfin','IdProducto'));
                          
                                

                        });

                    })->export('xlsx'); 

  

      }elseif($tipo=='1'){

          Excel::create('kardex_valorizado', function($excel) use ($productos,$dat_alm,$negocios,$almacenes,$sucursal,$almacen,$dat_suc,$dat_emp,$fecin,$fecfin,$IdProducto) {

                        $excel->sheet('kardex_valorizado', function($sheet) use ($productos,$dat_alm,$negocios,$almacenes,$sucursal,$almacen,$dat_suc,$dat_emp,$fecin,$fecfin,$IdProducto) {

                       
                            
                                  $sheet->loadView('empresas.reportes.kardex.kardex_valorizado_excel',compact('productos','dat_alm','dat_suc','negocios','almacenes','sucursal','almacen','dat_suc','dat_alm','dat_emp','fecin','fecfin','IdProducto','fecin','fecfin'));
                          
                                

                        });

                    })->export('xlsx'); 


      	}

 	}
  
  	 public function generarkardexpdf(Request $request){

    
     $negocios = DB::tABLE('empresa_negocios')->get();

    $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();

    $almacen = $request->get('almacen');
    $sucursal = $request->get('sucursal');
    $tipo = $request->get('docomp');
    $fecin = $request->get('fecin');
    $fecfin = $request->get('fecfin');
    $IdProducto = $request->get('IdProducto');


     
    $productoslista = DB::tABLE('productos')->select('IdProducto','pronom','procod','costo_total','flete','umecod')
    ->where('tipo','=','1')
    ->where('promocion','!=','2')
    //->where('id_empresa_negocio',$sucursal)
    ->orderby('pronom','asc')
    ->get();



      $saldo_anterior =0;


     
        $productos = DB::tABLE('productos')->select('IdProducto','pronom','procod','costo_total','flete','umecod')
      ->where('tipo','=','1')
      ->where(function ($query) use ($IdProducto){
          if($IdProducto!='Todos'){
             $query->where('IdProducto','=',$IdProducto); 
          }      
      }) 

      ->orderby('IdProducto','asc')
      ->get();

      $dat_alm = DB::tABLE('almacenes')->where('id_almacen',$almacen)->first();
      $dat_suc = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();
      $dat_emp = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();




      
      if($tipo=='2'){


            ini_set("pcre.backtrack_limit", "5000000");

             
             $pdf = PDF::loadView('empresas.reportes.kardex.kardex_fisico_pdf',compact('productos','dat_alm','negocios','almacenes','sucursal','almacen','dat_suc','dat_emp','fecin','fecfin','IdProducto'));
             return $pdf->stream('document.pdf');


                $headers = array(
                        'Content-Type: application/pdf',
                      );


  


      }elseif($tipo=='1'){

            ini_set("pcre.backtrack_limit", "5000000");

             
            $pdf = PDF::loadView('empresas.reportes.kardex.kardex_valorizado_pdf',compact('productos','dat_alm','dat_suc','negocios','almacenes','sucursal','almacen','dat_suc','dat_alm','dat_emp','fecin','fecfin','IdProducto','fecin','fecfin'));
            
            return $pdf->stream('document.pdf');


            $headers = array(
                'Content-Type: application/pdf',
            );

      }






 }


}	
