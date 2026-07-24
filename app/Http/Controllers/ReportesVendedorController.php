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

class ReportesVendedorController extends Controller
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

        $IdEmpresa = Auth::user()->IdEmpresa;
        $usuarios = DB::tABLE('users')->where('IdEmpresa',$IdEmpresa)->get();
        return view('empresas.reportesvendedor.index',compact('usuarios'));
    }

    public function ReportesVendedorComprobantes(Request $request)
    {

        $razsoc = $request->get('searchText');
        $vendedor = $request->get('Vendedor');
        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');
        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
        $IdEmpresa = Auth::user()->IdEmpresa;
     

        switch ($vendedor){
            case 0:
  
                    $comprobantes = cpe_cabecera::select('cpe_cabecera.IdEmpresa as RUC Empresa Emisora','ccafem as Fecha Emision','tdodes as Tipo Comprobante','cpe_cabecera.serdoc as Serie','cpe_cabecera.numdoc as Número','tdides as Tipo Doc. Cliente','cpe_cabecera.ccandi as N° Doc. Cliente','cpe_cabecera.ccanom as Razón Social','det.cdedes as Producto','monnom as Moneda','cpe_cabecera.ccaitv as Importe Total','name as Nombre Vendedor','apeusu as Apellido Vendedor')
                     ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','=','det.IdCpe_cabecera')
                    ->join('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->join('users as u','cpe_cabecera.IdUsuario','u.IdUsuario')
                    ->join('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->join('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->join('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->join('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where('cpe_cabecera.IdEmpresa','=',$IdEmpresa)
                    ->where('cpe_cabecera.IdUsuario','=',$vendedor)
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.estado','!=','Anulado')->get();
                    
                       $total = cpe_cabecera::where('cpe_cabecera.IdEmpresa','=',$IdEmpresa)
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.estado','!=','Anulado')->sum('cpe_cabecera.ccaitv');
                    
                    
             break;
            default:

                   $comprobantes = cpe_cabecera::select('cpe_cabecera.IdEmpresa as RUC Empresa Emisora','ccafem as Fecha Emision','tdodes as Tipo Comprobante','cpe_cabecera.serdoc as Serie','cpe_cabecera.numdoc as Número','tdides as Tipo Doc. Cliente','cpe_cabecera.ccandi as N° Doc. Cliente','cpe_cabecera.ccanom as Razón Social','det.cdedes as Producto','monnom as Moneda','cpe_cabecera.ccaitv as Importe Total','name as Nombre Vendedor','apeusu as Apellido Vendedor')
                     ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','=','det.IdCpe_cabecera')
                    ->join('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->join('users as u','cpe_cabecera.IdUsuario','u.IdUsuario')
                    ->join('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->join('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->join('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->join('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where('cpe_cabecera.IdEmpresa','=',$IdEmpresa)
                    ->where('cpe_cabecera.IdUsuario','=',$vendedor)
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.estado','!=','Anulado')->get();
                    
                       $total = cpe_cabecera::where('cpe_cabecera.IdEmpresa','=',$IdEmpresa)
                       ->where('cpe_cabecera.IdUsuario','=',$vendedor)
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.estado','!=','Anulado')->sum('cpe_cabecera.ccaitv');
            
                break;
            
        }
            
            
        Excel::create('Reporte Comprobantes', function($excel) use($comprobantes,$total) {
        $excel->sheet('Comprobantes', function($sheet) use($comprobantes,$total) {
    
        $sheet->fromArray($comprobantes);
       $sheet->prependRow(1, array(
            'TOTAL DE VENTAS',$total
        ));
        
    $sheet->appendRow(array(
            'TOTAL DE VENTAS',$total
        ));
        
         });
        })->export('xlsx');


    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

   
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
   

}
