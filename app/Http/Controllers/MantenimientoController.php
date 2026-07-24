<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use MasterSoft\Mantenimiento;
use MasterSoft\Vehiculo;
use Auth;

class MantenimientoController extends Controller
{
    public function index()
    {
        // Solo mostramos la vista con el botón
        return view('empresas.mantenimiento.stock');
    }

    public function sincronizarStock()
    {
        // Obtenemos la sucursal actual para no afectar otras tiendas si tienes varias
        $sucursal = Auth::user()->id_empresa_negocio ?? 1;

        DB::beginTransaction();
        try {
            // EJECUTAMOS UN UPDATE CON JOIN (Ultra rápido, no consume memoria RAM de PHP)
            // Lógica: SUMA(I) - SUMA(E) agrupado por Producto y Almacén
            
            DB::update("
                UPDATE producto_stock ps
                LEFT JOIN (
                    SELECT 
                        IdProducto, 
                        id_empresa_negocio, 
                        id_almacen,
                        (SUM(CASE WHEN mov_tip = 'I' THEN COALESCE(cantidad, 0) ELSE 0 END) - 
                         SUM(CASE WHEN mov_tip = 'E' THEN COALESCE(cantidad, 0) ELSE 0 END)) as total_base,
                        (SUM(CASE WHEN mov_tip = 'I' THEN COALESCE(cantidad_equivalente, 0) ELSE 0 END) - 
                         SUM(CASE WHEN mov_tip = 'E' THEN COALESCE(cantidad_equivalente, 0) ELSE 0 END)) as total_equiv
                    FROM movimientos_productos
                    WHERE id_empresa_negocio = ?
                    GROUP BY IdProducto, id_empresa_negocio, id_almacen
                ) as kardex 
                ON ps.IdProducto = kardex.IdProducto 
                   AND ps.id_empresa_negocio = kardex.id_empresa_negocio 
                   AND ps.id_almacen = kardex.id_almacen
                SET 
                    ps.stock = COALESCE(kardex.total_base, 0),
                    ps.stock_equivalencia = COALESCE(kardex.total_equiv, 0)
                WHERE ps.id_empresa_negocio = ?
            ", [$sucursal, $sucursal]);

            DB::commit();
            return Redirect::to('/mantenimiento/stock')->with('success', '¡Excelente! Todos los stocks (Principales y Equivalentes) han sido recalculados y cuadrados con el Kardex exactamente.');
            
        } catch (\Exception $e) {
            DB::rollback();
            return Redirect::to('/mantenimiento/stock')->with('info', 'Ocurrió un error al sincronizar: ' . $e->getMessage());
        }
    }

    public function create($vehiculo_id)
    {
        $vehiculo = Vehiculo::findOrFail($vehiculo_id);
        return view('empresas.mantenimientos.create', compact('vehiculo'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehiculo_id' => 'required|exists:vehiculos,id',
            'tipo_mantenimiento' => 'required',
            'kilometraje_actual' => 'required|numeric',
            'fecha_mantenimiento' => 'required|date',
            'costo' => 'numeric'
        ]);

        Mantenimiento::create($request->all());

        return redirect()->route('vehiculos.index')->with('success', 'Mantenimiento registrado con éxito.');
    }
}