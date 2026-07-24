<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
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

class ReportesTicketController extends Controller
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

    public function generar_reporte_ticket(Request $request){


    	  $fecin = $request->get('fec_ini');
      	$fecfin = $request->get('fec_fin');
      	$opcion = $request->get('tip_rep');
      	$vendedor = $request->get('vendedor');
      	$cliente = $request->get('cliente');
      	$producto = $request->get('producto');
      	$pro = $request->get('IdProducto');
      	$almacen = $request->get('almacen');
      	$sucursal = $request->get('suc_id');
      	$proveedor = $request->get('proveedor');
     
      	$data_sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();

      
      	$negocios = DB::tABLE('empresa_negocios')->get();

      	if(!empty($sucursal)){
        	$almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$sucursal)->get();
      	}else{
        	$almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();
      	}

      	$dat_ven = DB::tABLE('users')->where('IdUsuario',$request->get('vendedor'))->first();

      	$dat_cli = DB::tABLE('cliente')->where('clicod',$request->get('cliente'))->first();

       	$empresa = DB::tABLE('empresa')->where('IdEmpresa',$data_sucursal->IdEmpresa)->first();

      	$hora_rep = now()->format('Y-m-d H:i:s');
      	$dato_vendedor ="";
      	$dato_cliente ="";

      switch ($opcion){

        case 1:

                 $boletas = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod','ccatexo','ccatvg','ccatvi','des_doc')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','03');
                })
                ->whereNull('ccabaj')
                ->orderBy('IdCpe_cabecera','desc')->get();
                $facturas = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod','ccatexo','ccatvg','ccatvi','des_doc')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','01');
                })
                ->whereNull('ccabaj')
                ->orderBy('IdCpe_cabecera','desc')->get();

             

                $total_boletas = $boletas->sum('total');
                $total_facturas = $facturas->sum('total');

                return  view('formatos_reportes_tickets.ventas_general',compact('dat_cli','fecin','fecfin','boletas','facturas','total_boletas','total_facturas','dat_ven','empresa','data_sucursal'));

             


            break;

             case 2:

                 $boletas = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod','ccatexo','ccatvg','ccatvi','des_doc','cdedes','cdecan','cdepuni','cdevve')
                  ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','03');
                })
                ->whereNull('ccabaj')
                ->orderBy('IdCpe_cabecera','desc')->get();
                $facturas = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod','ccatexo','ccatvg','ccatvi','des_doc','cdedes','cdecan','cdepuni','cdevve')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','01');
                })
                ->whereNull('ccabaj')
                ->orderBy('IdCpe_cabecera','desc')->get();

             

                $total_boletas = $boletas->sum('cdevve');
                $total_facturas = $facturas->sum('cdevve');

                return  view('formatos_reportes_tickets.ventas_detallado',compact('dat_cli','fecin','fecfin','boletas','facturas','total_boletas','total_facturas','dat_ven','empresa','data_sucursal'));

             


            break;
         

            case 7:
            $sucursal_info = DB::table('empresa_negocios')->where('id_empresa_negocio', $sucursal)->first();
            $empresa = DB::table('empresa')->where('IdEmpresa', $sucursal_info->IdEmpresa)->first();

            $vent_res_prod = DB::table('cpe_cabecera')
                ->select(
                    'cpe_cabecera.ccafem as dia',
                    // Si el producto fue borrado, usamos el ID que quedó en el detalle
                    DB::raw('IFNULL(productos.procod, cpe_detalle.IdProducto) as codigo'),
                    // PRIORIDAD: Usamos el nombre que se guardó en la venta (cdedes) 
                    // por si el producto ya no existe en la tabla productos
                    'cpe_detalle.cdedes as producto',
                    DB::raw('SUM(cpe_detalle.cdecan) as cantidad'),
                    'cpe_detalle.cdepuni as precio',
                    DB::raw('SUM(cpe_detalle.cdevve) as total')
                )
                // CAMBIO CLAVE: Usamos leftJoin para no perder ventas de productos eliminados
                ->join('cpe_detalle', 'cpe_detalle.IdCpe_cabecera', '=', 'cpe_cabecera.IdCpe_cabecera')
                ->leftJoin('productos', 'productos.IdProducto', '=', 'cpe_detalle.IdProducto')
                ->whereIn('cpe_cabecera.tdocod', ['01', '03', '13'])
                ->whereBetween('cpe_cabecera.ccafem', [$fecin, $fecfin])
                ->whereNull('cpe_cabecera.ccabaj')
                // Agrupamos por el nombre guardado en la venta
                ->groupBy('cpe_cabecera.ccafem', 'codigo', 'cpe_detalle.cdedes', 'cpe_detalle.cdepuni')
                ->orderBy('cpe_cabecera.ccafem', 'asc')
                ->get();

            return view('formatos_comprobantes.ticket_resumen_productos', 
                compact('empresa', 'sucursal_info', 'vent_res_prod', 'fecin', 'fecfin'));
        break;

        case 8:
        // 1. Datos de empresa y sucursal
        $sucursal_info = DB::table('empresa_negocios')->where('id_empresa_negocio', $sucursal)->first();
        $empresa = DB::table('empresa')->where('IdEmpresa', $sucursal_info->IdEmpresa)->first();

        // 2. Consulta de VENTAS (Boletas, Facturas, Notas de Venta)
        $ventas = DB::table('cpe_cabecera')
            ->select(
                'cpe_cabecera.ccafem',
                'cpe_cabecera.serdoc',
                'cpe_cabecera.numdoc',
                'cpe_detalle.cdedes',
                'cpe_detalle.cdecan as cantidad',
                'cpe_detalle.cpe_det_factor',
                'cpe_detalle.cdevve'
            )
            ->join('cpe_detalle', 'cpe_detalle.IdCpe_cabecera', '=', 'cpe_cabecera.IdCpe_cabecera')
            ->whereIn('cpe_cabecera.tdocod', ['01', '03', '13', '14']) // Documentos de venta
            ->whereBetween('cpe_cabecera.ccafem', [$fecin, $fecfin])
            ->whereNull('cpe_cabecera.ccabaj') // Que no estén anulados
            ->where(function ($query) use ($sucursal, $pro) {
                if(!empty($sucursal)) $query->where('cpe_cabecera.id_empresa_negocio', $sucursal);
                if(!empty($pro)) $query->where('cpe_detalle.IdProducto', $pro);
            })
            ->orderBy('cpe_cabecera.ccafem', 'asc')
            ->get();

        // 3. Consulta de NOTAS DE CRÉDITO
        $notas = DB::table('cpe_cabecera')
            ->select(
                'cpe_cabecera.ccafem',
                'cpe_cabecera.serdoc',
                'cpe_cabecera.numdoc',
                'cpe_detalle.cdedes',
                'cpe_detalle.cdecan as cantidad',
                'cpe_detalle.cpe_det_factor',
                'cpe_detalle.cdevve'
            )
            ->join('cpe_detalle', 'cpe_detalle.IdCpe_cabecera', '=', 'cpe_cabecera.IdCpe_cabecera')
            ->where('cpe_cabecera.tdocod', '07') // Notas de Crédito
            ->whereBetween('cpe_cabecera.ccafem', [$fecin, $fecfin])
            ->whereNull('cpe_cabecera.ccabaj')
            ->where(function ($query) use ($sucursal, $pro) {
                if(!empty($sucursal)) $query->where('cpe_cabecera.id_empresa_negocio', $sucursal);
                if(!empty($pro)) $query->where('cpe_detalle.IdProducto', $pro);
            })
            ->orderBy('cpe_cabecera.ccafem', 'asc')
            ->get();

        // 4. Totales
        $total_final = $ventas->sum('cdevve') - $notas->sum('cdevve');

        return view('formatos_comprobantes.ticket_ventas_detalladas', 
            compact('empresa', 'sucursal_info', 'ventas', 'notas', 'total_final', 'fecin', 'fecfin'));
    break;

    case 14: // REPORTE 14: VENTAS POR CATEGORÍA (RESUMEN)
    $sucursal_info = DB::table('empresa_negocios')->where('id_empresa_negocio', $sucursal)->first();
    $empresa = DB::table('empresa')->where('IdEmpresa', $sucursal_info->IdEmpresa)->first();

    $ventas_categoria = DB::table('cpe_cabecera as cab')
        ->join('cpe_detalle as det', 'cab.IdCpe_cabecera', '=', 'det.IdCpe_cabecera')
        ->leftJoin('productos as p', 'det.IdProducto', '=', 'p.IdProducto')
        ->leftJoin('categorias as cat', 'p.cat_id', '=', 'cat.cat_id')
        ->select(
            DB::raw('IFNULL(cat.cat_nom, "SIN CATEGORÍA / OTROS") as categoria_nombre'),
            DB::raw('SUM(det.cdecan) as cantidad'),
            DB::raw('SUM(det.cdevve) as total')
        )
        ->whereBetween('cab.ccafem', [$fecin, $fecfin])
        ->whereNull('cab.ccabaj')
        ->whereIn('cab.tdocod', ['01', '03', '13'])
        ->where(function ($query) use ($sucursal) {
            if(!empty($sucursal)) $query->where('cab.id_empresa_negocio', $sucursal);
        })
        ->groupBy('categoria_nombre')
        ->orderBy('total', 'desc')
        ->get();

    return view('formatos_comprobantes.ticket_ventas_categoria', 
        compact('empresa', 'sucursal_info', 'ventas_categoria', 'fecin', 'fecfin'));
break;

case 15: // REPORTE 15: VENTAS POR CATEGORÍA (DETALLADO)
    $sucursal_info = DB::table('empresa_negocios')->where('id_empresa_negocio', $sucursal)->first();
    $empresa = DB::table('empresa')->where('IdEmpresa', $sucursal_info->IdEmpresa)->first();

    // Consultamos los productos agrupados por su categoría
    $productos = DB::table('cpe_cabecera as cab')
        ->join('cpe_detalle as det', 'cab.IdCpe_cabecera', '=', 'det.IdCpe_cabecera')
        ->leftJoin('productos as p', 'det.IdProducto', '=', 'p.IdProducto')
        ->leftJoin('categorias as cat', 'p.cat_id', '=', 'cat.cat_id')
        ->select(
            DB::raw('IFNULL(cat.cat_nom, "SIN CATEGORÍA") as categoria_nombre'),
            'det.cdedes as producto',
            DB::raw('SUM(det.cdecan) as cantidad'),
            DB::raw('SUM(det.cdevve) as total')
        )
        ->whereBetween('cab.ccafem', [$fecin, $fecfin])
        ->whereNull('cab.ccabaj')
        ->whereIn('cab.tdocod', ['01', '03', '13'])
        ->where(function ($query) use ($sucursal) {
            if(!empty($sucursal)) $query->where('cab.id_empresa_negocio', $sucursal);
        })
        ->groupBy('categoria_nombre', 'producto')
        ->orderBy('categoria_nombre', 'asc')
        ->get()
        ->groupBy('categoria_nombre'); // Lo agrupamos por categoría para la vista

    $total_general = DB::table('cpe_cabecera as cab')
        ->join('cpe_detalle as det', 'cab.IdCpe_cabecera', '=', 'det.IdCpe_cabecera')
        ->whereBetween('cab.ccafem', [$fecin, $fecfin])
        ->whereNull('cab.ccabaj')
        ->whereIn('cab.tdocod', ['01', '03', '13'])
        ->where(function ($query) use ($sucursal) {
            if(!empty($sucursal)) $query->where('cab.id_empresa_negocio', $sucursal);
        })
        ->sum('det.cdevve');

    return view('formatos_comprobantes.ticket_ventas_categoria_detallado', 
        compact('empresa', 'sucursal_info', 'productos', 'total_general', 'fecin', 'fecfin'));
break;

    case 16: // TOP 5 POR CATEGORÍA EN TICKET (CORREGIDO)
    $sucursal_info = DB::table('empresa_negocios')->where('id_empresa_negocio', $sucursal)->first();
    $empresa = DB::table('empresa')->where('IdEmpresa', $sucursal_info->IdEmpresa)->first();

    // 1. Consulta agrupada por CÓDIGO con el factor de cantidad
    $productos = DB::table('cpe_detalle as det')
        ->join('cpe_cabecera as cab', 'det.IdCpe_cabecera', '=', 'cab.IdCpe_cabecera')
        ->leftJoin('productos as p', 'det.IdProducto', '=', 'p.IdProducto')
        ->leftJoin('categorias as cat', 'p.cat_id', '=', 'cat.cat_id')
        ->select(
            DB::raw('IFNULL(cat.cat_id, 0) as cat_id'),
            DB::raw('IFNULL(cat.cat_nom, "BORRADOS / OTROS") as cat_nom'),
            // Usamos el código de la tabla productos que es más seguro
            DB::raw('IFNULL(p.procod, det.IdProducto) as codigo_prod'),
            DB::raw('MAX(det.cdedes) as nombre_producto'), 
            // AQUÍ ESTÁ EL TRUCO: Multiplicamos cantidad por factor para el total real
            DB::raw('SUM(det.cdecan * IFNULL(det.cpe_det_factor, 1)) as cantidad_total'),
            DB::raw('SUM(det.cdevve) as monto_total')
        )
        ->whereBetween('cab.ccafem', [$fecin, $fecfin])
        ->whereNull('cab.ccabaj')
        ->whereIn('cab.tdocod', ['01', '03', '13'])
        ->where(function ($query) use ($sucursal) {
            if(!empty($sucursal)) $query->where('cab.id_empresa_negocio', $sucursal);
        })
        ->groupBy('cat_id', 'cat_nom', 'codigo_prod')
        ->get();

    // 2. Filtramos el Top 5 de cada categoría
    $reporte_top = $productos->groupBy('cat_id')->map(function ($items) {
        return [
            'cat_nom' => $items->first()->cat_nom,
            'productos' => $items->sortByDesc('cantidad_total')->take(5)
        ];
    })->sortBy('cat_nom');

    return view('formatos_comprobantes.ticket_top_por_categoria', 
        compact('empresa', 'sucursal_info', 'reporte_top', 'fecin', 'fecfin'));
break;



    	case 80:

                $boletas = cpe_cabecera::select('ccafem as FECHA', 'tdodes as COMPROBANTE','serdoc as SERIE',DB::raw('MIN(numdoc) as INICIO'),DB::raw( 'MAX(numdoc) as FIN'),DB::raw('SUM(ccatvg) as VALOR_VENTA'),DB::raw('SUM(ccaigv) as IGV'),DB::raw('SUM(ccaitv) as TOTAL'),'des_doc')
                ->leftjoin('tipo_documento','tipo_documento.tdocod','cpe_cabecera.tdocod')
                ->whereNull('ccabaj')
                ->where('cpe_cabecera.tdocod','03')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                }) 
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->groupby('ccafem')
                ->groupby('tdodes')
                ->orderBy('FECHA','asc')
                ->orderBy('INICIO','asc')
                ->get();

                $facturas = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod','ccatexo','ccatvg','ccatvi','des_doc')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','01');
                })
                ->whereNull('ccabaj')
                ->orderBy('IdCpe_cabecera','desc')->get();

             

                $total_boletas = $boletas->sum('TOTAL');
                $total_facturas = $facturas->sum('total');

                return  view('formatos_reportes_tickets.ventas_consolidado',compact('dat_cli','fecin','fecfin','boletas','facturas','total_boletas','total_facturas','dat_ven','empresa','data_sucursal'));

             


            break;

         }


    }

    public function reporte_ranking_productos(Request $request){

          $fecin = $request->get('fecin');
          $fecfin = $request->get('fecfin');
          $almacen = $request->get('almacen');
          $sucursal = $request->get('sucursal');

              $empresa = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

              //$sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
          
                $productos = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan*cpe_det_factor) as cantidad'),'cdedes',DB::RAW('sum(cpe_detalle.cdevve) as precio'),'cpe_detalle.procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                 ->where(function ($query) {
                  $query->Where('cpe_cabecera.tdocod','03')
                        ->orWhere('cpe_cabecera.tdocod','01')
                        ->orWhere('cpe_cabecera.tdocod','13');
                      
                  })
               
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !='Todos'){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_detalle.id_almacen_pro',$almacen);
                  }                
                }) 
                ->orderby('cdedes','desc')
                ->groupby('procod')
                ->get();

                $totalproductos = $productos->sum('cantidad');

                $total =0;
                foreach ($productos as $pro) {
                   $total = $total + ($pro->precio);
                }


                    try{

            $impresoras = DB::tABLE('configuracion_impresoras')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();

            
            $connector = new WindowsPrintConnector("smb://".$impresoras->ruta);

               

            $printer = new Printer($connector);
          

              $printer->setJustification(Printer::JUSTIFY_CENTER);
              $printer->setFont(Printer::FONT_A);
         
            

              //$printer->text("RANKING DE PRODUCTOS DESDE ".$fecin." HASTA ".$fecfin." \n");
              $printer->text("RANKING DE PRODUCTOS"." \n");
              $printer->text("DESDE ".$fecin." HASTA ".$fecfin." \n");
             
        
              
              $printer->setJustification(Printer::JUSTIFY_LEFT);
              $printer->text("PRODUCTO  CANTIDAD          PU          IMPORTE"."\n");
              $printer->text("_______________________________________________"."\n");
              foreach ($productos as $producto) {
                 $primeralinea = substr($producto->cdedes,0,34);
                $printer->text($primeralinea."\n");

                $printer->text(str_pad(number_format($producto->cantidad,'2','.',''),14," ", STR_PAD_LEFT)."  ".str_pad(number_format($producto->precio/$producto->cantidad,'2','.',''),14," ", STR_PAD_LEFT)."  ".str_pad(number_format($producto->precio,'2','.',''),14," ", STR_PAD_LEFT)."\n");
                
              }

              $printer->text("_______________________________________________"."\n");
              
              $printer->text("\n");
              $printer->text("TOTAL: ". number_format($total,'2','.',',')."\n");
            
              $printer->feed();
              
              $printer->cut();
               
              $printer->pulse();
             
              $printer->close();


              }catch(\exception $e){

           
                  dd($e);
              }
           


    }

  


}
