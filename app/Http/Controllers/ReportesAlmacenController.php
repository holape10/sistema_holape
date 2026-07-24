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
use MasterSoft\pedidos;
use MasterSoft\Comprobante;
use MasterSoft\cpe_nota_detalle;
use MasterSoft\cpe_nota;
use MasterSoft\User;
use MasterSoft\tipo_documento_identidad;
use MasterSoft\tipo_documento;
use MasterSoft\productos;
use MasterSoft\insumos;
use MasterSoft\tipocambio;
use MasterSoft\MontoLetras;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Http\Requests\FacturaCreateFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;
use Excel;

class ReportesAlmacenController extends Controller
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

        return view('empresas.reportesalmacen.index');
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
		//$doccomprobante = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->orderBy('tdocod','asc')->pluck('tdodes', 'tdocod');

		switch ($tipdoc){
			case 1:
			     
                  $productos = productos::select('procod as Codigo Producto','pronom as Descripcion Producto','stock as Stock','umecod as Unidad')
                    ->where('IdEmpresa','=',$IdEmpresa)
                    ->orderBy('stock','desc')->get();


               Excel::create('Reporte Almacen', function($excel) use($productos) {
            $excel->sheet('Almacen', function($sheet) use($productos) {
    
            $sheet->fromArray($productos);
          

            $sheet->prependRow(1, array(
                'REPORTE DE ALMACEN'
            ));
            
            $sheet->mergeCells('A1:G1');

            $sheet->cells('A1:G1', function($cell) {
                $cell->setBackground('#A8F2F5');
                $cell->setAlignment('center');
               $cell->setBorder('solid', 'solid', 'solid', 'solid');
            });

            $sheet->cells('A2:G2', function($cell) {
                $cell->setBackground('#A8F2F5');
                $cell->setAlignment('center');
                $cell->setBorder('solid', 'solid', 'solid', 'solid');

            });



      

             });
            })->export('xlsx');

        
                    
                break;

			case 2:
               $productos = insumos::select('procod as Codigo Insumo','pronom as Descripcion Insumo','stock as Cantidad','medida as Presentacion','umecod as Unidad','fraccion as Fraccion','totalmedida as Total')->where('IdEmpresa','=',$IdEmpresa)->orderBy('totalmedida','desc')->get();
                   
                Excel::create('Reporte Almacen', function($excel) use($productos) {
            $excel->sheet('Almacen', function($sheet) use($productos) {
    
            $sheet->fromArray($productos);
          

            $sheet->prependRow(1, array(
                'REPORTE DE ALMACEN'
            ));
            
            $sheet->mergeCells('A1:G1');

            $sheet->cells('A1:G1', function($cell) {
                $cell->setBackground('#A8F2F5');
                $cell->setAlignment('center');
               $cell->setBorder('solid', 'solid', 'solid', 'solid');
            });

            $sheet->cells('A2:G2', function($cell) {
                $cell->setBackground('#A8F2F5');
                $cell->setAlignment('center');
                $cell->setBorder('solid', 'solid', 'solid', 'solid');

            });



      

             });
            })->export('xlsx');

        

                break;
			
             case 3:
               
                   
                 $productos= productos::select("productos.pronom as Producto",
                    DB::raw("(SELECT SUM(cantidad) FROM movimientos
                                WHERE movimientos.IdProducto = productos.IdProducto
                                AND mov_tip='EI'
                                GROUP BY movimientos.IdProducto) as 'Stock Inicial'"),
                    DB::raw("(SELECT SUM(cantidad) FROM movimientos
                                WHERE movimientos.IdProducto = productos.IdProducto
                                AND mov_tip='I'  GROUP BY movimientos.IdProducto) as Ingresos"),
                    DB::raw("(SELECT SUM(cantidad) FROM movimientos
                                WHERE movimientos.IdProducto = productos.IdProducto
                                AND mov_tip='E'
                                GROUP BY movimientos.IdProducto) as Egresos"),
                    "productos.stock")->where('IdEmpresa',$IdEmpresa)->get();

                 Excel::create('Reporte Almacen', function($excel) use($productos) {
            $excel->sheet('Almacen', function($sheet) use($productos) {
    
            $sheet->fromArray($productos);
          

            $sheet->prependRow(1, array(
                'REPORTE DE ALMACEN'
            ));
            
            $sheet->mergeCells('A1:k1');

            $sheet->cells('A1:k1', function($cell) {
                $cell->setBackground('#A8F2F5');
                $cell->setAlignment('center');
               $cell->setBorder('solid', 'solid', 'solid', 'solid');
            });

            $sheet->cells('A2:k2', function($cell) {
                $cell->setBackground('#A8F2F5');
                $cell->setAlignment('center');
                $cell->setBorder('solid', 'solid', 'solid', 'solid');

            });



      

             });
            })->export('xlsx');

                break;

            case 4:
                   
                 $productos= insumos::select("insumos.pronom as Insumo",
                    DB::raw("(SELECT SUM(totalmedida) FROM movimientosinsumos
                                WHERE movimientosinsumos.IdInsumo = insumos.IdInsumo
                                AND mov_tip='EI'
                                GROUP BY movimientosinsumos.IdInsumo) as 'Stock Inicial'"),
                    DB::raw("(SELECT SUM(totalmedida) FROM movimientosinsumos
                                WHERE movimientosinsumos.IdInsumo = insumos.IdInsumo
                                AND mov_tip='I' GROUP BY movimientosinsumos.IdInsumo) as Ingresos"),
                    DB::raw("(SELECT SUM(totalmedida) FROM movimientosinsumos
                                WHERE movimientosinsumos.IdInsumo = insumos.IdInsumo
                                AND mov_tip='E'
                                GROUP BY movimientosinsumos.IdInsumo) as Egresos"),
                    "insumos.totalmedida")->where('IdEmpresa',$IdEmpresa)->get();

                 Excel::create('Reporte Almacen', function($excel) use($productos) {
            $excel->sheet('Almacen', function($sheet) use($productos) {
    
            $sheet->fromArray($productos);
          

            $sheet->prependRow(1, array(
                'REPORTE DE ALMACEN'
            ));
            
            $sheet->mergeCells('A1:k1');

            $sheet->cells('A1:k1', function($cell) {
                $cell->setBackground('#A8F2F5');
                $cell->setAlignment('center');
               $cell->setBorder('solid', 'solid', 'solid', 'solid');
            });

            $sheet->cells('A2:k2', function($cell) {
                $cell->setBackground('#A8F2F5');
                $cell->setAlignment('center');
                $cell->setBorder('solid', 'solid', 'solid', 'solid');

            });



      

             });
            })->export('xlsx');
                   

                break;
			
		}
			
	
            
      


    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function ReporteVentas(){
        $usuario = Auth::user()->IdUsuario;
        $rucemp = Auth::user()->IdEmpresa;
        $regventas = pedidos::where('IdEmpresa',$rucemp)->where('IdUsuario',$usuario)->where('ped_est','Aperturado')->get();

        Excel::create('Reporte Ventas', function($excel) use($regventas) {
        $excel->sheet('RegistroVentas', function($sheet) use($regventas) {
    
        $sheet->fromArray($regventas);
            
           
         });
        })->export('xlsx');

    }

    public function VentasTotal(Request $request){
            
        $comprobantes = cpe_cabecera::select('cpe_cabecera.IdEmpresa as RUC Empresa Emisora','ccafem as Fecha Emision','tdodes as Tipo Comprobante','cpe_cabecera.serdoc as Serie','cpe_cabecera.numdoc as Numero','tdides as Tipo Doc. Cliente','cpe_cabecera.ccandi as N Doc. Cliente','cpe_cabecera.ccanom as Raz. Social','monnom as Moneda','cdecan as Cantidad','det.cdedes as Item','det.cdepve as Precio')
        ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','=','det.IdCpe_cabecera')
                    ->join('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->join('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->join('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->join('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->join('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where('cpe_cabecera.IdEmpresa','=',$IdEmpresa)
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.estado','Emitido')->get();


                    $total = cpe_cabecera::where('cpe_cabecera.IdEmpresa','=',$IdEmpresa)
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where('cpe_cabecera.estado','Emitido')->sum('cpe_cabecera.ccaitv');

                Excel::create('Reporte Comprobantes', function($excel) use($comprobantes,$total) {
                $excel->sheet('Comprobantes', function($sheet) use($comprobantes,$total) {
                $sheet->fromArray($comprobantes);
                $sheet->fromArray($total); $sheet->setColumnFormat(array(
                
                'K' => '0.00'
            ));
              
         
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
