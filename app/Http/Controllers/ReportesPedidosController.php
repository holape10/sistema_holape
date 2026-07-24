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

class ReportesPedidosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     
  
  
  	public function buscar_reporte_pedido(Request $request)
  	{
  		
  		$fec_ini = $request->get('fec_ini');
  		$fec_fin = $request->get('fec_fin');

  		if(empty($fec_ini)){

          $fec_ini = now()->modify('first day of this month')->format('Y-m-d');
          $fec_fin = now()->modify('last day of this month')->format('Y-m-d');

        }

  		$pedidos = DB::TABLE('pedidos')
  		->join('pedidos_detalle','pedidos_detalle.ped_id','pedidos.ped_id')
  		->where('ped_fec','>=',$fec_ini)
  		->where('ped_fec','<=',$fec_fin)
  		->orderby('item_obs','asc')
  		->get();

  		return view('empresas.reportes.pedido_observacion',compact('pedidos','fec_ini','fec_fin'));

  	}
      
     	public function buscar_reporte_pedido_excel(Request $request)
  	{
  		
  		$fec_ini = $request->get('fec_ini');
  		$fec_fin = $request->get('fec_fin');


  		$pedidos = DB::TABLE('pedidos')
  		->join('pedidos_detalle','pedidos_detalle.ped_id','pedidos.ped_id')
  		->where('ped_fec','>=',$fec_ini)
  		->where('ped_fec','<=',$fec_fin)
  		->orderby('item_obs','asc')
  		->get();

  		
        Excel::create('COMANDAS_OBSERVACIONES', function($excel) use ($pedidos,$fec_ini,$fec_fin) {

                        $excel->sheet('COMANDAS_OBSERVACIONES', function($sheet) use ($pedidos,$fec_ini,$fec_fin) {

                       
                            
                                  $sheet->loadView('empresas.reportes.comandas_observaciones_excel',compact('pedidos','fec_ini','fec_fin'));
                          
                                

                        });

                    })->export('xlsx'); 

  	}
      

            
                


           

           




}
