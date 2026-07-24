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

class ReportesController extends Controller
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

        return view('empresas.reportes.index');
    }

    public function ReporteComprobantes(Request $request)
    {

        //$fechaini = now()->modify('first day of this month');
        //$fechafin = now()->modify('last day of this month');
        //$serdoc=$request->get('serdoc');
        //$comp=$request->get('comp');
        //$numdoc = $request->get('numdoc');
        $razsoc = $request->get('searchText');
        $respse = $request->get('tiper');
        $tipdoc = $request->get('docomp');
        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');
        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
        $IdEmpresa = Auth::user()->IdEmpresa;
		//$doccomprobante = DB::table('tipo_documento')->where('tdoest','=','Activo')->orderBy('tdocod','asc')->pluck('tdodes', 'tdocod');

		switch ($tipdoc){
			case 1:
			case 2:
		if(!empty(trim($razsoc))){
					$comprobantes = cpe_cabecera::select('cpe_cabecera.IdEmpresa as RUC Empresa Emisora','ccafem as Fecha Emision','tdodes as Tipo Comprobante','cpe_cabecera.serdoc as Serie','cpe_cabecera.numdoc as Número','tdides as Tipo Doc. Cliente','cpe_cabecera.ccandi as N° Doc. Cliente','cpe_cabecera.ccanom as Razón Social','monnom as Moneda','cpe_cabecera.ccaitv as Importe Total')
					->join('moneda as m','cpe_cabecera.moncod','=','m.moncod')
					->join('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
					->join('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
					->join('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
					->join('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
					->where('cpe_cabecera.IdEmpresa','=',$IdEmpresa)
					->where(function ($query) use ($razsoc) {
						$query->where('cpe_cabecera.ccanom','like','%'.$razsoc.'%')
							  ->orWhere('cpe_cabecera.ccandi','=',$razsoc);
						})
					->where('cpe_cabecera.ccafem','>=',$fecin)
					->where('cpe_cabecera.ccafem','<=',$fecfin)
					->where('cpe_cabecera.tdocod','=',$tipdoc)
					->whereNull('cpe_cabecera.ccabaj')->get();

                    
              /*      $total = cpe_cabecera::where('cpe_cabecera.IdEmpresa','=',$IdEmpresa)
                    ->where(function ($query) use ($razsoc) {
                        $query->where('cpe_cabecera.ccanom','like','%'.$razsoc.'%')
                              ->orWhere('cpe_cabecera.ccandi','=',$razsoc);
                        })
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.tdocod','=',$tipdoc)
                    ->whereNull('cpe_cabecera.ccabaj')->sum('cpe_cabecera.ccaitv');*/
                    


				}else{
					$comprobantes = cpe_cabecera::select('cpe_cabecera.IdEmpresa as RUC Empresa Emisora','ccafem as Fecha Emision','tdodes as Tipo Comprobante','cpe_cabecera.serdoc as Serie','cpe_cabecera.numdoc as Número','tdides as Tipo Doc. Cliente','cpe_cabecera.ccandi as N° Doc. Cliente','cpe_cabecera.ccanom as Razón Social','monnom as Moneda','cpe_cabecera.ccaitv as Importe Total')
					->join('moneda as m','cpe_cabecera.moncod','=','m.moncod')
					->join('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
					->join('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
					->join('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
					->join('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
					->where('cpe_cabecera.IdEmpresa','=',$IdEmpresa)
					->where('cpe_cabecera.ccafem','>=',$fecin)
					->where('cpe_cabecera.ccafem','<=',$fecfin)
					->where('cpe_cabecera.tdocod','=',$tipdoc)
					->whereNull('cpe_cabecera.ccabaj')->get();


               /*     $total = cpe_cabecera::where('cpe_cabecera.IdEmpresa','=',$IdEmpresa)
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.tdocod','=',$tipdoc)
                    ->whereNull('cpe_cabecera.ccabaj')->sum('cpe_cabecera.ccaitv');*/

             
				}
				break;
			case 3:
			case 4:
				if(!empty($razsoc)){
					$comprobantes = cpe_nota::select('cpe_nota.IdEmpresa as RUC Empresa Emisora','cpe_nota.ccafem as Fecha Emision','tdodes as Tipo Comprobante','cpe_nota.serdoc as Serie','cpe_nota.numdoc as Número','tdides as Tipo Doc. Cliente','cpe_nota.ccandi as N° Doc. Cliente','cpe_nota.ccanom as Razón Social','monnom as Moneda','cpe_nota.ccaitv as Importe Total')
					->join('cpe_cabecera as cpe_c','cpe_nota.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
					->join('moneda as m','cpe_nota.moncod','=','m.moncod')
					->join('cliente as cl','cpe_nota.clicod','=','cl.clicod')
					->join('empresa as e','cpe_nota.IdEmpresa','=','e.IdEmpresa')
					->join('tipo_documento as tip_d','cpe_nota.tdocod','=','tip_d.tdocod')
					->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
					->where('cpe_nota.IdEmpresa','=',$IdEmpresa)
					->where(function ($query) use ($razsoc) {
						$query->where('cpe_n.ccanom','like','%'.$razsoc.'%')
							  ->orWhere('cpe_n.ccandi','=',$razsoc);
						}) 
					->where('cpe_nota.ccafem','>=',$fecin)
					->where('cpe_nota.ccafem','<=',$fecfin)
					->where('cpe_nota.tdocod','=',$tipdoc)
					->whereNull('cpe_cabecera.ccabaj')->get();
					
				}else{
					$comprobantes = cpe_nota::select('cpe_nota.IdEmpresa as RUC Empresa Emisora','cpe_nota.ccafem as Fecha Emision','tdodes as Tipo Comprobante','cpe_nota.serdoc as Serie','cpe_nota.numdoc as Número','tdides as Tipo Doc. Cliente','cpe_nota.ccandi as N° Doc. Cliente','cpe_nota.ccanom as Razón Social','monnom as Moneda','cpe_nota.ccaitv as Importe Total')
					->join('cpe_cabecera as cpe_c','cpe_nota.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
					->join('moneda as m','cpe_nota.moncod','=','m.moncod')
					->join('cliente as cl','cpe_nota.clicod','=','cl.clicod')
					->join('empresa as e','cpe_nota.IdEmpresa','=','e.IdEmpresa')
					->join('tipo_documento as tip_d','cpe_nota.tdocod','=','tip_d.tdocod')
					->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
					->where('cpe_nota.IdEmpresa','=',$IdEmpresa)
					->where('cpe_nota.ccafem','>=',$fecin)
					->where('cpe_nota.ccafem','<=',$fecfin)
					->where('cpe_nota.tdocod','=',$tipdoc)
					->whereNull('cpe_cabecera.ccabaj')->get();
				}
				break;
                
			case 5:
               if(!empty(trim($razsoc))){
                    $comprobantes = cpe_cabecera::select('cpe_cabecera.IdEmpresa as RUC Empresa Emisora','ccafem as Fecha Emision','tdodes as Tipo Comprobante','cpe_cabecera.serdoc as Serie','cpe_cabecera.numdoc as Número','tdides as Tipo Doc. Cliente','cpe_cabecera.ccandi as N° Doc. Cliente','cpe_cabecera.ccanom as Razón Social','monnom as Moneda','cpe_cabecera.ccaitv as Importe Total')
                    ->join('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->join('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->join('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->join('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->join('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where('cpe_cabecera.IdEmpresa','=',$IdEmpresa)
                    ->where(function ($query) use ($razsoc) {
                        $query->where('cpe_cabecera.ccanom','like','%'.$razsoc.'%')
                              ->orWhere('cpe_cabecera.ccandi','=',$razsoc);
                        })
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->whereNull('cpe_cabecera.ccabaj')->get();
                    
              /*      $total = cpe_cabecera::where('cpe_cabecera.IdEmpresa','=',$IdEmpresa)
                    ->where(function ($query) use ($razsoc) {
                        $query->where('cpe_cabecera.ccanom','like','%'.$razsoc.'%')
                              ->orWhere('cpe_cabecera.ccandi','=',$razsoc);
                        })
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.tdocod','=',$tipdoc)
                    ->whereNull('cpe_cabecera.ccabaj')->sum('cpe_cabecera.ccaitv');*/
                    


                }else{
                    $comprobantes = cpe_cabecera::select('cpe_cabecera.IdEmpresa as RUC Empresa Emisora','ccafem as Fecha Emision','tdodes as Tipo Comprobante','cpe_cabecera.serdoc as Serie','cpe_cabecera.numdoc as Número','tdides as Tipo Doc. Cliente','cpe_cabecera.ccandi as N° Doc. Cliente','cpe_cabecera.ccanom as Razón Social','monnom as Moneda','cpe_cabecera.ccaitv as Importe Total')
                    ->join('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->join('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->join('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->join('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->join('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where('cpe_cabecera.IdEmpresa','=',$IdEmpresa)
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->whereNull('cpe_cabecera.ccabaj')->get();


                   $total = cpe_cabecera::where('cpe_cabecera.IdEmpresa','=',$IdEmpresa)
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.tdocod','=',$tipdoc)
                    ->whereNull('cpe_cabecera.ccabaj')->sum('cpe_cabecera.ccaitv');

             
                }
                break;
                
			default:
					$comprobantes = cpe_baja::select('cpe_baja.IdEmpresa as RUC Empresa Emisoara','cpe_baja.cbdfco as Fecha Baja','cpe_baja.cbacor as N° Baja','cpe_baja.cbamot as Motivo de Baja','tdodes as Tipo Comprobante','cpe_baja.cbafec as Fecha Generación Doc. Referencia','serdoc as Serie Doc. Baja','cpe_baja.cbanum as N° Doc. Referencia','ccanom as Cliente','ccandi as RUC/DNI/Otros')
					->join('tipo_documento as td','cpe_baja.tdocod','=','td.tdocod')
					->join('cpe_cabecera as c','cpe_baja.IdCpe_cabecera','=','c.IdCpe_cabecera')
					->where('c.IdEmpresa','=',$IdEmpresa)
					->where(function ($query) use ($razsoc) {
						$query->where('cpe_n.ccanom','like','%'.$razsoc.'%')
							  ->orWhere('cpe_n.ccandi','=',$razsoc);
						})
					->where('cpe_baja.cbdfco','>=',$fecin)
					->where('cpe_baja.cbdfco','<=',$fecfin)->get();
			
				break;
			
		}
			
			
        Excel::create('Reporte Comprobantes', function($excel) use($comprobantes,$total) {
		$excel->sheet('Comprobantes', function($sheet) use($comprobantes,$total) {
		$sheet->fromArray($comprobantes);
        $sheet->fromArray($total);
      
 
               });
        })->export('xlsx');

        /*$fecini = $request->get('fecini');
        $fecfin = $request->get('fecfin');
        $tipdoc = $request->get('tipdoc');
        $estdoc = $request->get('estdoc');
        $rucemp =Auth::user()->IdEmpresa;
        
        Excel::create('Laravel Excel', function($excel) {

            $excel->sheet('Comprobantes', function($sheet) {

            $comprobantes = DB::table('cpe_cabecera as c')
            ->join('moneda as m','c.moncod','=','m.moncod')
            ->join('cliente as cl','c.ccandi','=','cl.clinum')
            ->join('empresa as e','c.IdEmpresa','=','e.IdEmpresa')
            ->join('tipo_documento as tip_d','c.tdocod','=','tip_d.tdocod')
            ->join('tipo_documento_identidad as tdi','c.tdicod','=','tdi.tdicod') ;

            
            $sheet->fromArray($comprobantes);

            });

        })->export('xlsx');*/

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function VentasTotal(Request $request){

        $comprobantes = cpe_cabecera::select('cpe_cabecera.IdEmpresa as RUC Empresa Emisora','ccafem as Fecha Emision','tdodes as Tipo Comprobante','cpe_cabecera.serdoc as Serie','cpe_cabecera.numdoc as Número','tdides as Tipo Doc. Cliente','cpe_cabecera.ccandi as N° Doc. Cliente','cpe_cabecera.ccanom as Razón Social','monnom as Moneda','cpe_cabecera.ccaitv as Importe Total')
                    ->join('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->join('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->join('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->join('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->join('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where('cpe_cabecera.IdEmpresa','=',$IdEmpresa)
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->whereNull('cpe_cabecera.ccabaj')->get();


                    $total = cpe_cabecera::where('cpe_cabecera.IdEmpresa','=',$IdEmpresa)
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
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
}
