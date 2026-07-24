<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\TipoIGV;
use MasterSoft\Empresa;
use MasterSoft\Cliente;
use MasterSoft\cpe_cabecera;
use MasterSoft\documento_relacionado;
use MasterSoft\unidad_medida;
use MasterSoft\cpe_detalle;
use MasterSoft\cpe_baja;
use MasterSoft\Comprobante;
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
use DB;
use Excel;

class ReportesGeneralesController extends Controller
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
	
    public function index(){

        return view('empresas.reportesgenerales.index');
    }

    public function reportepantalla(){

        return view('empresas.reportespantalla.index');
    }

    public function ReporteComprobantes(Request $request)
    {

    
        $razsoc = $request->get('searchText');
        $respse = $request->get('tiper');
        $tipdoc = $request->get('docomp');
        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');
        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
        $IdEmpresa = Auth::user()->IdEmpresa;
		
        $nomempresa = Empresa::FindOrFail($IdEmpresa);
        $datoempresa = $nomempresa->NomEmpresa;
          $nom_comp = 'FACTURAS';
        
            $nom_comp_not = 'NOTAS';

            $nom_comp_bol = 'BOLETAS';
        
         $nom_comp_tickets = 'TICKETS';

		switch ($tipdoc){
			case 1:
	

                    $tickets = cpe_cabecera::select('ccafem as FECHA','cpe_cabecera.serdoc as SERIE','cpe_cabecera.numdoc as NUMERO','cpe_cabecera.ccanom as CLIENTE','det.cdedes as CONCEPTO','monnom as Moneda','cdecan as CANTIDAD','cdepuni as PRECIO_UNITARIO','det.costo as COSTO_UNITARIO','det.cdevve as VENTA_TOTAL',DB::raw('ROUND((det.costo * det.cdecan),2) as COSTO_TOTAL'),DB::raw('ROUND((cdevve),2) - ROUND((det.costo * det.cdecan),2) as UTILIDAD'))
                    ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                    ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                     ->where('cpe_cabecera.moncod','=','PEN')
                    ->whereNull('cpe_cabecera.ccabaj')
                    ->orderBy('numdoc','asc')->get();

                    $totaltickets = cpe_cabecera::leftjoin('cpe_detalle','cpe_cabecera.IdCpe_cabecera','cpe_detalle.IdCpe_cabecera')
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.moncod','=','PEN')
                    ->whereNull('cpe_cabecera.ccabaj')
                    ->sum(DB::raw('ROUND(cdevve,2)'));

                    $totalticketscosto = cpe_cabecera::
                    leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                     ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.moncod','=','PEN')
                    ->whereNull('cpe_cabecera.ccabaj')
                    ->sum(DB::raw('ROUND((det.costo * det.cdecan),2)'));


               
        Excel::create('Reporte Comprobantes', function($excel) use($datoempresa,$tickets,$totaltickets,$totalticketscosto,$nom_comp_tickets,$fecin,$fecfin) {

       

        $excel->sheet('Comprobantes', function($sheet2) use($datoempresa,$tickets,$totaltickets,$totalticketscosto,$nom_comp_tickets,$fecin,$fecfin) {
        


        $sheet2->fromArray($tickets);
        $sheet2->setColumnFormat(array(
            'G' => '0.00'
        ));

       
        $sheet2->prependRow(1, array(
                $datoempresa.' '.'COMPROBANTES DESDE  '.$fecin.'  AL  '.$fecfin
        ));
            
        $sheet2->mergeCells('A1:G1');
        $sheet2->setAllBorders('thin');
        $sheet2->cells('A1:G1', function($cell) {
                $cell->setAlignment('center');
           
        });

     
        $sheet2->appendRow(array(
            'Total Venta Soles',$totaltickets
        ));

        $sheet2->appendRow(array(
            'Total Costo Soles',$totalticketscosto
        ));

         $sheet2->appendRow(array(
            'TOTAL UTILIDAD SOLES',$totaltickets-$totalticketscosto
        ));

       $sheet2->setColumnFormat(array(
            'G' => '0.00'
        ));

        });






        })->export('xlsx');
        
        break;

        case 2:
          $comprobantes = cpe_cabecera::select('cpe_cabecera.IdEmpresa as RUC Empresa Emisora','ccafem as Fecha Emision','tdodes as Tipo Comprobante','cpe_cabecera.serdoc as Serie','cpe_cabecera.numdoc as Numero','tdides as Tipo Doc. Cliente','cpe_cabecera.ccandi as N Doc. Cliente','cpe_cabecera.ccanom as Razon Social','monnom as Moneda','cpe_cabecera.ccaitv as Importe Total')

                    ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->whereNull('cpe_cabecera.ccabaj')->get();

                    $gastos = gastos_cabecera::select('tipo_movimiento','gast_fec as Fecha de Movimiento','det_gasto as Detalle','gast_obs as Observaciones','total as Total')
                    ->leftjoin('gastos_detalle as gd','gd.gast_cab_id','=','gastos_cabecera.gast_cab_id')
                    ->where('gastos_cabecera.gast_fec','>=',$fecin)
                    ->where('gastos_cabecera.gast_fec','<=',$fecfin)
                    ->where('gastos_cabecera.est_gasto','!=','Eliminado')
                    ->orderby('tipo_movimiento','asc')->get();

                    
                     $totalgastos = gastos_cabecera::select('gast_fec as Fecha Gasto','gast_obs as Observaciones','total_gast as Total','tipo_movimiento as Movimiento')
                    ->where('gastos_cabecera.gast_fec','>=',$fecin)
                    ->where('gastos_cabecera.gast_fec','<=',$fecfin)
                     ->where('gastos_cabecera.tipo_movimiento','=','GASTO')
                     
                    ->where('gastos_cabecera.est_gasto','!=','Eliminado')->sum('total_gast');

                     $totalingreso = gastos_cabecera::select('gast_fec as Fecha Gasto','gast_obs as Observaciones','total_gast as Total','tipo_movimiento as Movimiento')
                    ->where('gastos_cabecera.gast_fec','>=',$fecin)
                    ->where('gastos_cabecera.gast_fec','<=',$fecfin)
                     ->where('gastos_cabecera.tipo_movimiento','=','INGRESO')
                    ->where('gastos_cabecera.est_gasto','!=','Eliminado')
                    ->sum('total_gast');

                   $total = cpe_cabecera::where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.moncod','=','PEN')
                    ->whereNull('cpe_cabecera.ccabaj')
                    ->sum('cpe_cabecera.ccaitv');

                    $totalefectivo = cpe_cabecera::where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.moncod','=','PEN')
                     ->where('cpe_cabecera.tipo_venta','=','0')
                    ->whereNull('cpe_cabecera.ccabaj')
                    ->sum('cpe_cabecera.efectivo');
             
                    
                    $totalvisa = cpe_cabecera::where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.moncod','=','PEN')
                     ->where('cpe_cabecera.tipo_venta','=','0')
                    ->whereNull('cpe_cabecera.ccabaj')->sum('cpe_cabecera.visa');
                    
             
                    $totalmaster = cpe_cabecera::where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.moncod','=','PEN')
                     ->where('cpe_cabecera.tipo_venta','=','0')
                    ->whereNull('cpe_cabecera.ccabaj')->sum('cpe_cabecera.mastercard');
                    
                     
                     Excel::create('Reporte Comprobantes', function($excel) use($comprobantes,$total,$totalefectivo,$totalvisa,$totalmaster,$gastos,$totalgastos,$totalingreso) {

       

        $excel->sheet('Comprobantes', function($sheet) use($comprobantes,$total,$totalefectivo,$totalvisa,$totalmaster,$gastos,$totalgastos,$totalingreso) {
    
        $sheet->fromArray($comprobantes);

      

        

  
        
        $sheet->appendRow(array(

            'TOTAL DE VENTAS ',$total
        ));

         $sheet->appendRow(array(
                    
                    ' ',''
        ));


       
            
        $sheet->appendRow(array(
                    'TOTAL DE GASTOS',$totalgastos
                ));

         $sheet->appendRow(array(
                    'TOTAL DE INGRESO',$totalingreso
                ));


        $sheet->appendRow(array(
                    '(VENTAS + INGRESOS) - GASTOS',($total + $totalingreso) - $totalgastos
                ));

         });

         $excel->sheet('Ingresos-Gastos', function($sheet) use($comprobantes,$total,$totalefectivo,$totalvisa,$totalmaster,$gastos,$totalgastos,$totalingreso) {

            $sheet->fromArray($gastos);

             $sheet->appendRow(array(
                    'TOTAL INGRESOS ',$totalingreso
                ));

             $sheet->appendRow(array(
                    'TOTAL GASTOS ',$totalgastos
                ));


           });


        })->export('xlsx');
             
                
                break;
			
			case 5:
               $comprobantes = cpe_cabecera::select('ccafem as FECHA','cpe_cabecera.serdoc as SERIE','cpe_cabecera.numdoc as NUMERO','cpe_cabecera.ccanom as CLIENTE','monnom as Moneda','cpe_cabecera.ccatvg as SUBTOTAL','cpe_cabecera.ccaigv as IGV','cpe_cabecera.ccaitv as TOTAL','cpe_cabecera.ccabaj as Fecha Baja')
                    
                    ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.tdocod', '=', '01')
                    ->whereNull('cpe_cabecera.ccabaj')->where(function ($query) {
                        $query->where('cpe_cabecera.ccacodsun','101')
                              ->orWhere('cpe_cabecera.ccacodsun','102');
                    })
                    ->orderBy('numdoc','asc')->get();

                   $total = cpe_cabecera::where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.tdocod', '=', '01')
                    ->where('cpe_cabecera.moncod','=','PEN')
                    ->whereNull('cpe_cabecera.ccabaj')
                    ->where(function ($query) {
                        $query->where('cpe_cabecera.ccacodsun','101')
                              ->orWhere('cpe_cabecera.ccacodsun','102');
                    })->sum('cpe_cabecera.ccaitv');

                    $totaldol = cpe_cabecera::where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.tdocod', '=', '01')
                    ->where('cpe_cabecera.moncod','=','USD')
                    ->whereNull('cpe_cabecera.ccabaj')
                    ->where(function ($query) {
                        $query->where('cpe_cabecera.ccacodsun','101')
                              ->orWhere('cpe_cabecera.ccacodsun','102');
                    })
                    ->sum('cpe_cabecera.ccaitv');

                    $igvsol = cpe_cabecera::where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.tdocod', '=', '01')
                    ->where('cpe_cabecera.moncod','=','PEN')
                    ->whereNull('cpe_cabecera.ccabaj')->where(function ($query) {
                        $query->where('cpe_cabecera.ccacodsun','101')
                              ->orWhere('cpe_cabecera.ccacodsun','102');
                    })->sum('cpe_cabecera.ccaigv');

                    $igvdol = cpe_cabecera::where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.tdocod', '=', '01')
                    ->where('cpe_cabecera.moncod','=','USD')
                    ->whereNull('cpe_cabecera.ccabaj')->where(function ($query) {
                        $query->where('cpe_cabecera.ccacodsun','101')
                              ->orWhere('cpe_cabecera.ccacodsun','102');
                    })->sum('cpe_cabecera.ccaigv');

                    $subdol = cpe_cabecera::where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.tdocod', '=', '01')
                    ->where('cpe_cabecera.moncod','=','USD')
                    ->whereNull('cpe_cabecera.ccabaj')->where(function ($query) {
                        $query->where('cpe_cabecera.ccacodsun','101')
                              ->orWhere('cpe_cabecera.ccacodsun','102');
                    })->sum('cpe_cabecera.ccatvg');

                    $subsol = cpe_cabecera::where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.tdocod', '=', '01')
                    ->where('cpe_cabecera.moncod','=','PEN')
                    ->whereNull('cpe_cabecera.ccabaj')->where(function ($query) {
                        $query->where('cpe_cabecera.ccacodsun','101')
                              ->orWhere('cpe_cabecera.ccacodsun','102');
                    })->sum('cpe_cabecera.ccatvg');
              
                    // BOLETAS DE VENTAS

                    $boletas = cpe_cabecera::select('ccafem as FECHA','cpe_cabecera.serdoc as SERIE','cpe_cabecera.numdoc as NUMERO','cpe_cabecera.ccanom as CLIENTE','monnom as Moneda','cpe_cabecera.ccatvg as SUBTOTAL','cpe_cabecera.ccaigv as IGV','cpe_cabecera.ccaitv as TOTAL','cpe_cabecera.ccabaj as Fecha Baja')

                   
                    ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->Where('cpe_cabecera.tdocod', '=', '03')
                    ->whereNull('cpe_cabecera.ccabaj')->where(function ($query) {
                        $query->where('cpe_cabecera.ccacodsun','101')
                              ->orWhere('cpe_cabecera.ccacodsun','102');
                    })
                    ->orderBy('numdoc','asc')->get();

                    $tickets = cpe_cabecera::select('ccafem as FECHA','cpe_cabecera.serdoc as SERIE','cpe_cabecera.numdoc as NUMERO','cpe_cabecera.ccanom as CLIENTE','monnom as Moneda','cpe_cabecera.ccatvg as SUBTOTAL','cpe_cabecera.ccaigv as IGV','cpe_cabecera.ccaitv as TOTAL','cpe_cabecera.ccabaj as Fecha Baja')

                    
                    ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->Where('cpe_cabecera.tdocod', '=', '13')
                    ->whereNull('cpe_cabecera.ccabaj')->where(function ($query) {
                        $query->where('cpe_cabecera.ccacodsun','101')
                              ->orWhere('cpe_cabecera.ccacodsun','102');
                    })
                    ->orderBy('numdoc','asc')->get();

                    $totaltickets = cpe_cabecera::where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.tdocod', '=', '13')
                    ->where('cpe_cabecera.moncod','=','PEN')
                    ->whereNull('cpe_cabecera.ccabaj')->where(function ($query) {
                        $query->where('cpe_cabecera.ccacodsun','101')
                              ->orWhere('cpe_cabecera.ccacodsun','102');
                    })->sum('cpe_cabecera.ccaitv');

               

                    $totalbol = cpe_cabecera::where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.tdocod', '=', '03')
                    ->where('cpe_cabecera.moncod','=','PEN')
                    ->whereNull('cpe_cabecera.ccabaj')->where(function ($query) {
                        $query->where('cpe_cabecera.ccacodsun','101')
                              ->orWhere('cpe_cabecera.ccacodsun','102');
                    })->sum('cpe_cabecera.ccaitv');

                    $totaldolbol = cpe_cabecera::where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.tdocod', '=', '03')
                    ->where('cpe_cabecera.moncod','=','USD')
                    ->whereNull('cpe_cabecera.ccabaj')->where(function ($query) {
                        $query->where('cpe_cabecera.ccacodsun','101')
                              ->orWhere('cpe_cabecera.ccacodsun','102');
                    })->sum('cpe_cabecera.ccaitv');

                    $igvsolbol = cpe_cabecera::where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.tdocod', '=', '03')
                    ->where('cpe_cabecera.moncod','=','PEN')
                    ->whereNull('cpe_cabecera.ccabaj')->where(function ($query) {
                        $query->where('cpe_cabecera.ccacodsun','101')
                              ->orWhere('cpe_cabecera.ccacodsun','102');
                    })->sum('cpe_cabecera.ccaigv');

                    $igvdolbol = cpe_cabecera::where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.tdocod', '=', '03')
                    ->where('cpe_cabecera.moncod','=','USD')
                    ->whereNull('cpe_cabecera.ccabaj')->where(function ($query) {
                        $query->where('cpe_cabecera.ccacodsun','101')
                              ->orWhere('cpe_cabecera.ccacodsun','102');
                    })->sum('cpe_cabecera.ccaigv');

                    $subdolbol = cpe_cabecera::where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.tdocod', '=', '03')
                    ->where('cpe_cabecera.moncod','=','USD')
                    ->whereNull('cpe_cabecera.ccabaj')->where(function ($query) {
                        $query->where('cpe_cabecera.ccacodsun','101')
                              ->orWhere('cpe_cabecera.ccacodsun','102');
                    })->sum('cpe_cabecera.ccatvg');

                    $subsolbol = cpe_cabecera::where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.tdocod', '=', '03')
                    ->where('cpe_cabecera.moncod','=','PEN')
                    ->whereNull('cpe_cabecera.ccabaj')->where(function ($query) {
                        $query->where('cpe_cabecera.ccacodsun','101')
                              ->orWhere('cpe_cabecera.ccacodsun','102');
                    })->sum('cpe_cabecera.ccatvg');

                    // Notas de Crédito

                    $notas = cpe_nota::select('cpe_nota.ccafem as FECHA','cpe_nota.serdoc as SERIE','cpe_nota.numdoc as NUMERO','cpe_nota.ccanom as CLIENTE','det.cdedes as CONCEPTO','monnom as Moneda','det.cdepve as SUBTOTAL','det.cdeigv as IGV','det.cdevve as TOTAL','cpe_nota.ccabaj as Fecha Baja')
                    ->leftjoin('cpe_cabecera as cpe_c','cpe_nota.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
                    ->leftjoin('cpe_nota_detalle as det','cpe_nota.IdCpe_nota','det.IdCpe_nota')
                    ->leftjoin('moneda as m','cpe_nota.moncod','=','m.moncod')
                    ->leftjoin('cliente as cl','cpe_nota.clicod','=','cl.clicod')
                    ->leftjoin('empresa as e','cpe_nota.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','cpe_nota.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
                    
                    ->where('cpe_nota.ccafem','>=',$fecin)
                    ->where('cpe_nota.ccafem','<=',$fecfin)
                    ->where(function ($query) {
                    $query->where('cpe_nota.tdocod', '=', '07')
                       ->orWhere('cpe_nota.tdocod', '=', '08');
                    })
                    ->where(function ($query) {
                        $query->where('cpe_nota.ccacodsun','101')
                              ->orWhere('cpe_nota.ccacodsun','102');
                    })
                    ->whereNull('cpe_nota.ccabaj')
                    ->get();

                    $totalnot = cpe_nota::where('cpe_nota.ccafem','>=',$fecin)
                    ->where('cpe_nota.ccafem','<=',$fecfin)
                    ->where(function ($query) {
                    $query->where('cpe_nota.tdocod', '=', '07')
                       ->orWhere('cpe_nota.tdocod', '=', '08');
                    })
                    ->where(function ($query) {
                        $query->where('cpe_nota.ccacodsun','101')
                              ->orWhere('cpe_nota.ccacodsun','102');
                    })
                    ->where('cpe_nota.moncod','=','PEN')
                    ->whereNull('cpe_nota.ccabaj')->sum('cpe_nota.ccaitv');

                   $totaldolnot = cpe_nota::where('cpe_nota.ccafem','>=',$fecin)
                    ->where('cpe_nota.ccafem','<=',$fecfin)
                     ->where(function ($query) { 
                     $query->where('cpe_nota.tdocod', '=', '07')
                       ->orWhere('cpe_nota.tdocod', '=', '08');
                    })
                      ->where('cpe_nota.moncod','=','USD')
                    ->whereNull('cpe_nota.ccabaj')->sum('cpe_nota.ccaitv');

                    $igvsolnot = cpe_nota::where('cpe_nota.ccafem','>=',$fecin)
                    ->where('cpe_nota.ccafem','<=',$fecfin)
                    ->where(function ($query) {
                    $query->where('cpe_nota.tdocod', '=', '07')
                       ->orWhere('cpe_nota.tdocod', '=', '08');
                    })
                    ->where(function ($query) {
                        $query->where('cpe_nota.ccacodsun','101')
                              ->orWhere('cpe_nota.ccacodsun','102');
                    })
                    ->where('cpe_nota.moncod','=','PEN')
                    ->whereNull('cpe_nota.ccabaj')->sum('cpe_nota.ccaigv');

                    $igvdolnot = cpe_nota::where('cpe_nota.ccafem','>=',$fecin)
                    ->where('cpe_nota.ccafem','<=',$fecfin)
                    ->where(function ($query) {
                    $query->where('cpe_nota.tdocod', '=', '07')
                       ->orWhere('cpe_nota.tdocod', '=', '08');
                    })
                    ->where(function ($query) {
                        $query->where('cpe_nota.ccacodsun','101')
                              ->orWhere('cpe_nota.ccacodsun','102');
                    })
                    ->where('cpe_nota.moncod','=','USD')
                    ->whereNull('cpe_nota.ccabaj')->sum('cpe_nota.ccaigv');

                    $subdolnot = cpe_nota::where('cpe_nota.ccafem','>=',$fecin)
                    ->where('cpe_nota.ccafem','<=',$fecfin)
                    ->where(function ($query) {
                    $query->where('cpe_nota.tdocod', '=', '07')
                       ->orWhere('cpe_nota.tdocod', '=', '08');
                    })
                    ->where(function ($query) {
                        $query->where('cpe_nota.ccacodsun','101')
                              ->orWhere('cpe_nota.ccacodsun','102');
                    })
                    ->where('cpe_nota.moncod','=','USD')
                    ->whereNull('cpe_nota.ccabaj')->sum('cpe_nota.ccatvg');

                    $subsolnot = cpe_nota::where('cpe_nota.ccafem','>=',$fecin)
                    ->where('cpe_nota.ccafem','<=',$fecfin)
                    ->where(function ($query) {
                    $query->where('cpe_nota.tdocod', '=', '07')
                       ->orWhere('cpe_nota.tdocod', '=', '08');
                    })
                    ->where(function ($query) {
                        $query->where('cpe_nota.ccacodsun','101')
                              ->orWhere('cpe_nota.ccacodsun','102');
                    })
                    ->where('cpe_nota.moncod','=','PEN')
                    ->whereNull('cpe_nota.ccabaj')->sum('cpe_nota.ccatvg');

                    $bajas = cpe_baja::select('cpe_baja.cbdfco as Fecha Baja','cpe_baja.cbacor as N Baja','cpe_baja.cbamot as Motivo de Baja','tdodes as Tipo Comprobante','cpe_baja.cbafec as Fecha Doc. Referencia','serdoc as Serie Doc. Baja','cpe_baja.cbanum as Doc. Referencia','ccanom as Cliente','ccandi as RUC/DNI/Otros')
                    ->join('tipo_documento as td','cpe_baja.tdocod','=','td.tdocod')
                    ->join('cpe_cabecera as c','cpe_baja.IdCpe_cabecera','=','c.IdCpe_cabecera')
                    
                    ->where('cpe_baja.cbdfco','>=',$fecin)
                    ->where('cpe_baja.cbdfco','<=',$fecfin)->get();

        Excel::create('Reporte Comprobantes', function($excel) use($datoempresa,$bajas,$comprobantes,$notas,$boletas,$total,$totaldol,$totaldolnot,$totalnot,$totalbol,$totaldolbol,$nom_comp,$nom_comp_not,$nom_comp_bol,$fecin,$fecfin,$igvsol,$igvsolnot,$igvsolbol,$igvdol,$igvdolnot,$igvdolbol,$subdol,$subdolnot,$subdolbol,$subsolnot,$subsol,$subsolbol,$totaltickets,$tickets,$nom_comp_tickets) {


        $excel->sheet('Facturas', function($sheet) use($datoempresa,$comprobantes,$totaldol,$total,$nom_comp,$fecin,$fecfin,$igvsol,$igvdol,$subdol,$subsol) {
        
        $sheet->fromArray($comprobantes);
        
        $sheet->setColumnFormat(array(
            'G' => '0.00'
        ));


        $sheet->prependRow(1, array(
                $datoempresa.' '.$nom_comp.' DESDE  '.$fecin.'  AL  '.$fecfin
        ));
            
        $sheet->mergeCells('A1:G1');
       
        $sheet->cells('A1:G1', function($cell) {
                $cell->setAlignment('center');
           
        });

        $sheet->prependRow(1, array(
               
        ));

        $sheet->appendRow(array(
            'Subtotal Soles',$subsol
          ));
    
        $sheet->appendRow(array(
             'IGV Soles',$igvsol
        ));

        $sheet->appendRow(array(
            'Total Soles',$total
        ));
    
        $sheet->appendRow(array(
           'Subtotal Dolares',$subdol
        ));
        
        $sheet->appendRow(array(
           'IGV Dolares',$igvdol
        ));

        $sheet->appendRow(array(
            'Total Dolares',$totaldol
        ));

        $sheet->setColumnFormat(array(
            'G' => '0.00'
        ));

        });


        $excel->sheet('Boletas', function($sheet2) use($datoempresa,$boletas,$totaldolbol,$totalbol,$nom_comp_bol,$fecin,$fecfin,$igvsolbol,$igvdolbol,$subdolbol,$subsolbol) {
        
        $sheet2->fromArray($boletas);
        $sheet2->setColumnFormat(array(
            'G' => '0.00'
        ));

       
        $sheet2->prependRow(1, array(
                $datoempresa.' '.$nom_comp_bol.' DESDE  '.$fecin.'  AL  '.$fecfin
        ));
            
        $sheet2->mergeCells('A1:G1');
        $sheet2->setAllBorders('thin');
        $sheet2->cells('A1:G1', function($cell) {
                $cell->setAlignment('center');
           
        });

        $sheet2->prependRow(1, array(
               
        ));

        $sheet2->appendRow(array(
            'Subtotal Soles',$subsolbol
          ));
    
        $sheet2->appendRow(array(
             'IGV Soles',$igvsolbol
        ));

        $sheet2->appendRow(array(
            'Total Soles',$totalbol
        ));
    
        

        $sheet2->setColumnFormat(array(
            'G' => '0.00'
        ));

        });



      



        $excel->sheet('NDC', function($sheet1) use($datoempresa,$notas,$totaldolnot,$totalnot,$nom_comp_not,$fecin,$fecfin,$igvsolnot,$igvdolnot,$subdolnot,$subsolnot) {
        
        $sheet1->fromArray($notas);
        $sheet1->setColumnFormat(array(
            'G' => '0.00'
        ));

       
        $sheet1->prependRow(1, array(
                $datoempresa.' '.$nom_comp_not.' DESDE  '.$fecin.'  AL  '.$fecfin
        ));
            
        $sheet1->mergeCells('A1:G1');
        $sheet1->setAllBorders('thin');
        $sheet1->cells('A1:G1', function($cell) {
                $cell->setAlignment('center');
           
        });

        $sheet1->prependRow(1, array(
               
        ));

        $sheet1->appendRow(array(
            'Subtotal Soles',$subsolnot
          ));
    
        $sheet1->appendRow(array(
             'IGV Soles',$igvsolnot
        ));

        $sheet1->appendRow(array(
            'Total Soles',$totalnot
        ));
    
       

        $sheet1->setColumnFormat(array(
            'G' => '0.00'
        ));

        });

        $excel->sheet('Bajas', function($sheet3) use($datoempresa,$bajas,$fecin,$fecfin) {
                
            $sheet3->fromArray($bajas);
            $sheet3->setColumnFormat(array(
                'G' => '0.00'
            ));

               
            $sheet3->prependRow(1, array(
                $datoempresa.' '. 'BAJAS DE COMPROBANTES DESDE  '.$fecin.'  AL  '.$fecfin
            ));
                    
            $sheet3->mergeCells('A1:G1');
            $sheet3->setAllBorders('thin');
            $sheet3->cells('A1:G1', function($cell) {
            $cell->setAlignment('center');
                   
            });

            $sheet3->prependRow(1, array(
                       
            ));

                
            });


        })->export('xlsx');
        

                break;
		
			
		}
			
	

        
   
    }


     public function MostrarVentas(Request $request)
    {


      
        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');
        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
        $IdEmpresa = Auth::user()->IdEmpresa;
        $tiporeporte = $request->get('tiporeporte');
        $nomempresa = Empresa::FindOrFail($IdEmpresa);
        $datoempresa = $nomempresa->NomEmpresa;

        switch ($tiporeporte){
            case 1:
                    $tickets = cpe_cabecera::select('ccafem as FECHA','cpe_cabecera.serdoc as SERIE','cpe_cabecera.numdoc as NUMERO','cpe_cabecera.ccanom as CLIENTE','det.cdedes as CONCEPTO','monnom as Moneda','cdecan as CANTIDAD','cdepuni as PRECIO_UNITARIO','det.costo as COSTO_UNITARIO','det.cdevve as VENTA_TOTAL',DB::raw('ROUND((det.costo * det.cdecan),2) as COSTO_TOTAL'),DB::raw('ROUND((cdevve),2) - ROUND((det.costo * det.cdecan),2) as UTILIDAD'))
                    ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                    ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->whereNull('cpe_cabecera.ccabaj')
                    ->orderBy('numdoc','asc')->paginate(500);

                    $totaltickets = cpe_cabecera::where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.moncod','=','PEN')
                    ->whereNull('cpe_cabecera.ccabaj')->sum(DB::raw('ROUND(cpe_cabecera.ccaitv,2)'));


                    $totalticketscosto = cpe_cabecera::leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.moncod','=','PEN')
                    ->whereNull('cpe_cabecera.ccabaj')->sum(DB::raw('ROUND((det.costo * det.cdecan),2)'));

                    return view('empresas.reportespantalla.index',compact('tickets','totaltickets','totalticketscosto','fecin','fecfin'));
            break;

            case 2:
                $productos = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan) as cantidad'),'cdedes','cdepuni','procod','umecod')
                       ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                        ->where('cpe_cabecera.ccafem','>=',$fecin)
                        ->where('cpe_cabecera.ccafem','<=',$fecfin)
                        ->where('cpe_cabecera.moncod','=','PEN')

                        ->whereNull('cpe_cabecera.ccabaj')
                        ->orderby('cantidad','desc')
                        ->groupby('procod','cdepuni','cdedes','umecod')->paginate(100);

                 $totalproductos = cpe_cabecera::leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                        ->where('cpe_cabecera.ccafem','>=',$fecin)
                        ->where('cpe_cabecera.ccafem','<=',$fecfin)
                        ->where('cpe_cabecera.moncod','=','PEN')

                        ->whereNull('cpe_cabecera.ccabaj')->sum('cdecan');

                     
                    return view('empresas.reportespantalla.productosmasvendidos',compact('productos','fecin','fecfin','totalproductos'));

            break;
        }

    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function VentasTotal(Request $request){
            
        $comprobantes = cpe_cabecera::select('cpe_cabecera.IdEmpresa as RUC Empresa Emisora','ccafem as Fecha Emision','tdodes as Tipo Comprobante','cpe_cabecera.serdoc as Serie','cpe_cabecera.numdoc as Numero','tdides as Tipo Doc. Cliente','cpe_cabecera.ccandi as N Doc. Cliente','cpe_cabecera.ccanom as Razon Social','monnom as Moneda','cpe_cabecera.ccaitv as Importe Total')
                    ->join('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->join('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->join('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->join('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->join('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->whereNull('cpe_cabecera.ccabaj')->get();


                    $total = cpe_cabecera::where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->whereNull('cpe_cabecera.ccabaj')->sum('cpe_cabecera.ccaitv');

                Excel::create('Reporte Comprobantes', function($excel) use($comprobantes,$total) {
                $excel->sheet('Comprobantes', function($sheet) use($comprobantes,$total) {
                $sheet->fromArray($comprobantes);
                $sheet->fromArray($total);
              
         
                       });
                })->export('xlsx');
    }

    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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

     public function ExportarStock(Request $request)
    {

          $rucemp = trim(Auth::user()->IdEmpresa); 
     //   $stock = DB::table("movimientos")->select("IdProducto",DB::raw("(SELECT SUM(cantidad) FROM movimientos GROUP BY IdProducto) as Ingresos"),DB::raw("(SELECT SUM(cantidad) FROM movimientos GROUP BY IdProducto) as Egresos"))->get();

        $stock= DB::table("productos")
          ->select("productos.*",
                    DB::raw("(SELECT SUM(cantidad) FROM movimientos
                                WHERE movimientos.IdProducto = productos.IdProducto
                                AND mov_tip='I'
                                GROUP BY movimientos.IdProducto) as Ingresos"),
                    DB::raw("(SELECT SUM(cantidad) FROM movimientos
                                WHERE movimientos.IdProducto = productos.IdProducto
                                AND mov_tip='E'
                                GROUP BY movimientos.IdProducto) as Egresos"))->where('IdEmpresa',$rucemp)->get();
            
            
        Excel::create('Stock', function($excel) use($stock) {
        $excel->sheet('Stock', function($sheet) use($stock) {
    
        $sheet->fromArray($stock);
     });
        })->export('xlsx');





    }

    public function ExportarStockProductos(Request $request)
    {

          $rucemp = trim(Auth::user()->IdEmpresa); 
     
         $productos= productos::select('productos.procod as CODIGO PRODUCTO','productos.pronom AS PRODUCTO','u.umenom as UNIDAD MEDIDA','productos.stockinicial as STOCK INICIAL','productos.stock as STOCK ACTUAL')
                ->leftjoin('unidad_medida as u','productos.umecod','=','u.umecod')
                ->where('productos.IdEmpresa','=',$rucemp)
                
                ->orderby('pronom','asc')
                ->get();
        

            
        Excel::create('Stock', function($excel) use($productos) {
        $excel->sheet('Stock', function($sheet) use($productos) {
    
        $sheet->fromArray($productos);
     });
        })->export('xlsx');





    }

     public function ExportarStockValorizado(Request $request)
    {

          $rucemp = trim(Auth::user()->IdEmpresa); 
     
         $productos= productos::select('productos.procod as CODIGO PRODUCTO','productos.pronom AS PRODUCTO','u.umenom as UNIDAD MEDIDA','productos.stockinicial as STOCK INICIAL','productos.stock as STOCK ACTUAL','productos.propun as PRECIO UNITARIO','productos.costo as COSTO',DB::raw('ROUND((productos.costo * productos.stock),2) as VALOR_INVENTARIO'))
                ->leftjoin('unidad_medida as u','productos.umecod','=','u.umecod')
                ->where('productos.IdEmpresa','=',$rucemp)
                
                ->orderby('pronom','asc')
                ->get();
        
          $total = productos::where('IdEmpresa','=',$rucemp)
                   
					->sum(DB::raw('ROUND((productos.costo * productos.stock),2)'));

            
        Excel::create('Stock Valorizado', function($excel) use($productos,$total) {
        $excel->sheet('Stock Valorizado', function($sheet) use($productos,$total) {
    
        $sheet->fromArray($productos);

         $sheet->appendRow(array(
             'TOTAL VALOR INVENTARIO',$total
        ));


     });
        })->export('xlsx');





    }
   

}
