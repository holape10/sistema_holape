<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Auth;

class MermaController extends Controller
{
    public function index()
    {
        $sucursal = Auth::user()->id_empresa_negocio ?? 1;

        $mermas = DB::table('mermas as m')
            ->join('productos as p', 'p.IdProducto', '=', 'm.IdProducto')
            ->join('motivos_merma as mm', 'mm.id', '=', 'm.id_motivo')
            ->select('m.*', 'p.pronom', 'mm.descripcion as motivo')
            ->where('m.id_empresa_negocio', $sucursal)
            ->orderBy('m.id', 'desc')
            ->paginate(50);

        return view('empresas.mermas.index', compact('mermas'));
    }

    public function create()
    {
        $productos = DB::table('productos as p')
            ->leftJoin('unidad_medida as um', 'p.umecod', '=', 'um.umecod')
            ->leftJoin('unidad_medida as umc', 'p.umecod_cons', '=', 'umc.umecod')
            ->where('p.tipo', '1')
            ->whereIn('p.promocion', ['0', '4'])
            ->select('p.IdProducto', 'p.pronom', 'p.factor_cons', 'p.factor', 'um.umenom as unidad_base', 'umc.umenom as unidad_cons')
            ->orderBy('p.pronom', 'asc')
            ->get();

        $motivos = DB::table('motivos_merma')->where('estado', 1)->get();

        return view('empresas.mermas.create', compact('productos', 'motivos'));
    }

    public function store(Request $request)
    {
        $sucursal = Auth::user()->id_empresa_negocio ?? 1;
        $id_almacen = 1; 
        
        $id_producto = $request->get('IdProducto');
        $cantidad_ingresada = $request->get('cantidad');
        $tipo_unidad_calculo = $request->get('tipo_unidad'); // Recibe 'base' o 'equivalente'
        $id_motivo = $request->get('id_motivo');
        $observacion = $request->get('observacion');

        // 1. Obtener producto con los NOMBRES reales de sus unidades
        $producto = DB::table('productos as p')
            ->leftJoin('unidad_medida as um', 'p.umecod', '=', 'um.umecod')
            ->leftJoin('unidad_medida as umc', 'p.umecod_cons', '=', 'umc.umecod')
            ->where('p.IdProducto', $id_producto)
            ->select('p.*', 'um.umenom as unidad_base', 'umc.umenom as unidad_cons')
            ->first();
            
        // 2. Obtener la descripción del motivo seleccionado
        $motivo_desc = DB::table('motivos_merma')->where('id', $id_motivo)->value('descripcion');

        // 3. Determinar el nombre real de la unidad para guardarlo y mostrarlo en la vista
        if ($tipo_unidad_calculo == 'equivalente') {
            $nombre_unidad_mostrar = $producto->unidad_cons ?? 'Equivalente';
        } else {
            $nombre_unidad_mostrar = $producto->unidad_base ?? 'Base';
        }

        // 4. Calcular las cantidades base y equivalentes matemáticamente
        $factor = ($producto->factor_cons > 0) ? $producto->factor_cons : 1;
        
        if ($tipo_unidad_calculo == 'equivalente') {
            $cantidad_kardex = $cantidad_ingresada / $factor; 
            $cantidad_equivalente = $cantidad_ingresada; 
        } else {
            $cantidad_kardex = $cantidad_ingresada; 
            $cantidad_equivalente = $cantidad_ingresada * $factor; 
        }

        $costo_unitario = $producto->costo ?? 0;
        $costo_total = $costo_unitario * $cantidad_kardex;

        DB::beginTransaction();
        try {
            $stock_actual = DB::table('producto_stock')
                ->where('IdProducto', $id_producto)
                ->where('id_empresa_negocio', $sucursal)
                ->where('id_almacen', $id_almacen)
                ->first();

            $stock_base_actual = $stock_actual ? $stock_actual->stock : 0;
            $stock_equiv_actual = $stock_actual ? $stock_actual->stock_equivalencia : 0;

            $nuevo_stock_base = $stock_base_actual - $cantidad_kardex;
            $nuevo_stock_equiv = $stock_equiv_actual - $cantidad_equivalente;

            // 5. Generar la descripción completa para el Kardex
            $texto_kardex = 'MERMA: ' . $motivo_desc;
            if(!empty($observacion)){
                $texto_kardex .= ' | Obs: ' . $observacion;
            }

            $id_movimiento = DB::table('movimientos_productos')->insertGetId([
                'IdProducto' => $id_producto,
                'IdProducto_rel' => $id_producto,
                'cantidad' => $cantidad_kardex,
                'cantidad_equivalente' => $cantidad_equivalente,
                'stock' => $nuevo_stock_base,
                'stock_equivalente' => $nuevo_stock_equiv,
                'costo' => $costo_unitario,
                'tipo' => '3', 
                'id_empresa_negocio' => $sucursal,
                'id_almacen' => $id_almacen,
                'fecha_mov' => date('Y-m-d'),
                'fecha_registro' => date('Y-m-d H:i:s'),
                'descripcion' => $texto_kardex, // Guardamos motivo + observación
                'mov_tip' => 'E'
            ]);

            $id_merma = DB::table('mermas')->insertGetId([
                'IdProducto' => $id_producto,
                'cantidad' => $cantidad_ingresada,
                'tipo_unidad' => $nombre_unidad_mostrar, // Guardamos "GRM", "MLT", etc.
                'cantidad_kardex' => $cantidad_kardex,
                'costo_unitario' => $costo_unitario,
                'costo_total' => $costo_total,
                'id_motivo' => $id_motivo,
                'id_movimiento' => $id_movimiento,
                'id_empresa_negocio' => $sucursal,
                'id_almacen' => $id_almacen,
                'observacion' => $observacion,
                'fecha_registro' => date('Y-m-d H:i:s')
            ]);

            if ($stock_actual) {
                DB::table('producto_stock')
                    ->where('pro_sto_id', $stock_actual->pro_sto_id)
                    ->update([
                        'stock' => $nuevo_stock_base,
                        'stock_equivalencia' => $nuevo_stock_equiv
                    ]);
            }

            DB::commit();
            return Redirect::to('/mermas')->with('success', 'Merma registrada correctamente.')->with('imprimir_ticket', $id_merma);
            
        } catch (\Exception $e) {
            DB::rollback();
            return Redirect::to('/mermas/crear')->with('info', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $merma = DB::table('mermas')->where('id', $id)->first();
            $producto = DB::table('productos')->where('IdProducto', $merma->IdProducto)->first();
            
            // Calcular equivalencia para retornar
            $factor = ($producto->factor_cons > 0) ? $producto->factor_cons : 1;
            if ($merma->tipo_unidad == 'equivalente') {
                $cantidad_equivalente_retorno = $merma->cantidad;
            } else {
                $cantidad_equivalente_retorno = $merma->cantidad * $factor;
            }

            // Revertir Stock Base y Equivalencia
            DB::table('producto_stock')
                ->where('IdProducto', $merma->IdProducto)
                ->where('id_empresa_negocio', $merma->id_empresa_negocio)
                ->where('id_almacen', $merma->id_almacen)
                ->update([
                    'stock' => DB::raw("stock + " . $merma->cantidad_kardex),
                    'stock_equivalencia' => DB::raw("stock_equivalencia + " . $cantidad_equivalente_retorno)
                ]);

            // Eliminar Movimiento del Kardex
            if($merma->id_movimiento) {
                DB::table('movimientos_productos')->where('mov_pro_id', $merma->id_movimiento)->delete();
            }

            // Eliminar Merma
            DB::table('mermas')->where('id', $id)->delete();

            DB::commit();
            return Redirect::to('/mermas')->with('success', 'Merma anulada. El stock (Base y Equivalente) fue restaurado.');
        } catch (\Exception $e) {
            DB::rollback();
            return Redirect::to('/mermas')->with('info', 'Error al anular: ' . $e->getMessage());
        }
    }

    public function ticket($id)
    {
        $merma = DB::table('mermas as m')
            ->join('productos as p', 'p.IdProducto', '=', 'm.IdProducto')
            ->join('motivos_merma as mm', 'mm.id', '=', 'm.id_motivo')
            ->select('m.*', 'p.pronom', 'mm.descripcion as motivo')
            ->where('m.id', $id)->first();

        return view('empresas.mermas.ticket', compact('merma'));
    }

    public function reporteDiarioExcel()
    {
        $sucursal = Auth::user()->id_empresa_negocio ?? 1;
        $hoy = date('Y-m-d');

        $mermas = DB::table('mermas as m')
            ->join('productos as p', 'p.IdProducto', '=', 'm.IdProducto')
            ->join('motivos_merma as mm', 'mm.id', '=', 'm.id_motivo')
            ->select('m.*', 'p.pronom', 'mm.descripcion as motivo')
            ->where('m.id_empresa_negocio', $sucursal)
            ->whereDate('m.fecha_registro', $hoy)
            ->get();

        return view('empresas.mermas.excel', compact('mermas', 'hoy'));
    }

    public function reporteDiarioPdf()
    {
        $sucursal = Auth::user()->id_empresa_negocio ?? 1;
        $hoy = date('Y-m-d');

        $mermas = DB::table('mermas as m')
            ->join('productos as p', 'p.IdProducto', '=', 'm.IdProducto')
            ->join('motivos_merma as mm', 'mm.id', '=', 'm.id_motivo')
            ->select('m.*', 'p.pronom', 'mm.descripcion as motivo')
            ->where('m.id_empresa_negocio', $sucursal)
            ->whereDate('m.fecha_registro', $hoy)
            ->get();

        $pdf = \PDF::loadView('empresas.mermas.pdf', compact('mermas', 'hoy'));
        return $pdf->stream('Reporte_Mermas_'.$hoy.'.pdf');
    }
}