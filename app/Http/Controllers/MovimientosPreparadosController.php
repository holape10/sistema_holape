<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use MasterSoft\MovimientoPreparado;

class MovimientosPreparadosController extends Controller
{
    public function index(Request $request)
    {
        // 1. Capturamos las dos fechas (por defecto hoy)
        $fecha_inicio = $request->get('fecha_inicio', Carbon::today()->toDateString());
        $fecha_fin = $request->get('fecha_fin', Carbon::today()->toDateString());
        $producto_id = $request->get('producto_id', ''); 

        $productos = DB::table('productos')
            ->where('promocion', '2')
            ->where('proest', 'Activo')
            ->get();

        $query = DB::table('movimientos_preparados')
            ->join('productos', 'movimientos_preparados.producto_id', '=', 'productos.IdProducto')
            ->leftJoin('users', 'movimientos_preparados.usuario_id', '=', 'users.IdUsuario')
            ->select(
                'movimientos_preparados.*', 
                'productos.pronom', 
                DB::raw("CONCAT(users.name, ' ', users.apeusu) as nombre_usuario")
            )
            // 2. Cambiamos el where por un whereBetween para buscar en el rango de fechas
            ->whereBetween('movimientos_preparados.fecha_proceso', [$fecha_inicio, $fecha_fin]);

        if (!empty($producto_id)) {
            $query->where('movimientos_preparados.producto_id', $producto_id);
        }

        $movimientos = $query->orderBy('movimientos_preparados.created_at', 'desc')->get();

        // 3. Pasamos las nuevas variables a la vista
        return view('empresas.movimientos_preparados.index', compact('productos', 'movimientos', 'fecha_inicio', 'fecha_fin', 'producto_id'));
    }

    public function exportarStock(Request $request)
    {
        $filtro_stock = $request->get('filtro_stock', 'todos');
        $formato = $request->get('formato', 'excel');
        $tipo_producto_id = $request->get('tipo_producto_id', '');
        $categoria_id = $request->get('categoria_id', '');

        // 1. Obtenemos los mismos datos filtrados para la exportación
        $query = DB::table('productos')
            ->where('promocion', '2')
            ->where('proest', 'Activo')
            ->select('IdProducto', 'pronom', 'stock_preparados');

        if ($filtro_stock == 'con_stock') {
            $query->where('stock_preparados', '>', 0);
        } elseif ($filtro_stock == 'sin_stock') {
            $query->where(function($q) {
                $q->where('stock_preparados', '=', 0)
                  ->orWhereNull('stock_preparados');
            });
        } elseif ($filtro_stock == 'negativos') {
            $query->where('stock_preparados', '<', 0);
        }

        if (!empty($tipo_producto_id)) {
            $query->where('tip_pro_id', $tipo_producto_id);
        }

        if (!empty($categoria_id)) {
            $query->where('cat_id', $categoria_id);
        }

        $productos = $query->orderBy('pronom', 'asc')->get();

        // 2. Lógica para descargar EXCEL
        if ($formato == 'excel') {
            $fileName = "Reporte_Stock_" . ucfirst($filtro_stock) . ".xls";
            $headers = [
                "Content-type" => "application/vnd.ms-excel; charset=utf-8",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            ];
            
            $html = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
            $html .= '<table border="1">
                        <tr>
                            <th style="background-color:#343a40; color:white;">ID</th>
                            <th style="background-color:#343a40; color:white;">Producto</th>
                            <th style="background-color:#343a40; color:white;">Stock Actual</th>
                            <th style="background-color:#343a40; color:white;">Estado</th>
                        </tr>';
            
            foreach ($productos as $producto) {
                $stock = $producto->stock_preparados ?? 0;
                $estado = 'Sin Stock';
                if ($stock > 0) $estado = 'Disponible';
                elseif ($stock < 0) $estado = 'Negativo';

                $html .= '<tr>
                            <td>' . $producto->IdProducto . '</td>
                            <td>' . $producto->pronom . '</td>
                            <td>' . $stock . '</td>
                            <td>' . $estado . '</td>
                          </tr>';
            }
            $html .= '</table>';

            return response($html, 200, $headers);
        }

        // 3. Lógica para PDF y TICKET
        return view('empresas.movimientos_preparados.imprimir_stock', compact('productos', 'filtro_stock', 'formato'));
    }
    /*public function exportarStock(Request $request)
    {
        $filtro_stock = $request->get('filtro_stock', 'todos');
        $formato = $request->get('formato', 'excel');

        // 1. Obtenemos los mismos datos filtrados
        $query = DB::table('productos')
            ->where('promocion', '2')
            ->where('proest', 'Activo')
            ->select('IdProducto', 'pronom', 'stock_preparados');

        if ($filtro_stock == 'con_stock') {
            $query->where('stock_preparados', '>', 0);
        } elseif ($filtro_stock == 'sin_stock') {
            $query->where(function($q) {
                $q->where('stock_preparados', '=', 0)
                  ->orWhereNull('stock_preparados');
            });
        } elseif ($filtro_stock == 'negativos') {
            $query->where('stock_preparados', '<', 0);
        }

        $productos = $query->orderBy('pronom', 'asc')->get();

        // 2. Lógica para descargar EXCEL (Usando la misma técnica de tu historial)
        if ($formato == 'excel') {
            $fileName = "Reporte_Stock_" . ucfirst($filtro_stock) . ".xls";
            $headers = [
                "Content-type" => "application/vnd.ms-excel; charset=utf-8",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            ];
            
            $html = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
            $html .= '<table border="1">
                        <tr>
                            <th style="background-color:#343a40; color:white;">ID</th>
                            <th style="background-color:#343a40; color:white;">Producto</th>
                            <th style="background-color:#343a40; color:white;">Stock Actual</th>
                            <th style="background-color:#343a40; color:white;">Estado</th>
                        </tr>';
            
            foreach ($productos as $producto) {
                $stock = $producto->stock_preparados ?? 0;
                $estado = 'Sin Stock';
                if ($stock > 0) $estado = 'Disponible';
                elseif ($stock < 0) $estado = 'Negativo';

                $html .= '<tr>
                            <td>' . $producto->IdProducto . '</td>
                            <td>' . $producto->pronom . '</td>
                            <td>' . $stock . '</td>
                            <td>' . $estado . '</td>
                          </tr>';
            }
            $html .= '</table>';

            return response($html, 200, $headers);
        }

        // 3. Lógica para PDF y TICKET (Retornamos a una vista optimizada para imprimir)
        return view('empresas.movimientos_preparados.imprimir_stock', compact('productos', 'filtro_stock', 'formato'));
    }*/

    public function exportarHistorial(Request $request)
    {
        // 4. Hacemos lo mismo para la exportación
        $fecha_inicio = $request->get('fecha_inicio', Carbon::today()->toDateString());
        $fecha_fin = $request->get('fecha_fin', Carbon::today()->toDateString());
        $producto_id = $request->get('producto_id', '');
        $formato = $request->get('formato', 'ticket');

        $query = DB::table('movimientos_preparados')
            ->join('productos', 'movimientos_preparados.producto_id', '=', 'productos.IdProducto')
            ->leftJoin('users', 'movimientos_preparados.usuario_id', '=', 'users.IdUsuario')
            ->select(
                'movimientos_preparados.*', 
                'productos.pronom', 
                DB::raw("CONCAT(users.name, ' ', users.apeusu) as nombre_usuario")
            )
            ->whereBetween('movimientos_preparados.fecha_proceso', [$fecha_inicio, $fecha_fin]);

        if (!empty($producto_id)) {
            $query->where('movimientos_preparados.producto_id', $producto_id);
        }

        $movimientos = $query->orderBy('movimientos_preparados.created_at', 'asc')->get();

        if ($formato == 'excel') {
            $fileName = "Historial_Preparados_{$fecha_inicio}_al_{$fecha_fin}.xls";
            $headers = [
                "Content-type" => "application/vnd.ms-excel; charset=utf-8",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            ];
            
            $html = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
            $html .= '<table border="1">
                        <tr>
                            <th style="background-color:#343a40; color:white;">Hora</th>
                            <th style="background-color:#343a40; color:white;">Usuario</th>
                            <th style="background-color:#343a40; color:white;">Producto</th>
                            <th style="background-color:#343a40; color:white;">Movimiento</th>
                            <th style="background-color:#343a40; color:white;">Cant.</th>
                            <th style="background-color:#343a40; color:white;">Stock Resultante</th>
                        </tr>';
            foreach ($movimientos as $mov) {
                $html .= '<tr>
                            <td>' . \Carbon\Carbon::parse($mov->created_at)->format('d/m/Y H:i:s') . '</td>
                            <td>' . ($mov->nombre_usuario ?? 'SISTEMA') . '</td>
                            <td>' . $mov->pronom . '</td>
                            <td>' . $mov->tipo_movimiento . '</td>
                            <td>' . $mov->cantidad . '</td>
                            <td>' . $mov->stock_resultante . '</td>
                          </tr>';
            }
            $html .= '</table>';

            return response($html, 200, $headers);
        }

        return view('empresas.movimientos_preparados.ticket', compact('movimientos', 'fecha_inicio', 'fecha_fin'));
    }

    public function reporteStock(Request $request)
    {
        // 1. Capturamos los filtros
        $filtro_stock = $request->get('filtro_stock', 'todos');
        $tipo_producto_id = $request->get('tipo_producto_id', '');
        $categoria_id = $request->get('categoria_id', '');

        // Traemos las listas para poblar los selects en la vista
        $tipos_producto = DB::table('tipo_producto')->orderBy('tip_pro_nom', 'asc')->get();
        $categorias = DB::table('categorias')->orderBy('cat_nom', 'asc')->get();

        // 2. Base de la consulta: Solo productos activos y que son preparados (promocion = 2)
        $query = DB::table('productos')
            ->where('promocion', '2')
            ->where('proest', 'Activo')
            ->select('IdProducto', 'pronom', 'stock_preparados', 'tip_pro_id', 'cat_id');

        // 3. Aplicamos la lógica de los filtros
        if ($filtro_stock == 'con_stock') {
            $query->where('stock_preparados', '>', 0);
        } elseif ($filtro_stock == 'sin_stock') {
            $query->where(function($q) {
                $q->where('stock_preparados', '=', 0)
                  ->orWhereNull('stock_preparados');
            });
        } elseif ($filtro_stock == 'negativos') {
            $query->where('stock_preparados', '<', 0);
        }

        if (!empty($tipo_producto_id)) {
            $query->where('tip_pro_id', $tipo_producto_id);
        }

        if (!empty($categoria_id)) {
            $query->where('cat_id', $categoria_id);
        }

        // 4. Obtenemos los resultados ordenados alfabéticamente
        $productos = $query->orderBy('pronom', 'asc')->get();

        // 5. Retornamos a la vista con todas las variables necesarias
        return view('empresas.movimientos_preparados.reporte_stock', compact(
            'productos', 'filtro_stock', 'tipos_producto', 'categorias', 'tipo_producto_id', 'categoria_id'
        ));
    }
    
    public function reporteGeneralStock(Request $request)
    {
        // 1. Manejo de Filtros y Fechas predeterminadas
        $fecha_inicio = $request->get('fecha_inicio', Carbon::today()->toDateString());
        $fecha_fin = $request->get('fecha_fin', Carbon::today()->toDateString());
        $tipo_item = $request->get('tipo_item', 'todos'); // todos, insumos, productos, preparados
        $tipo_producto_id = $request->get('tipo_producto_id', '');
        $categoria_id = $request->get('categoria_id', '');
        $filtro_stock = $request->get('filtro_stock', 'todos');

        // Combos para la vista
        $tipos_producto = DB::table('tipo_producto')->orderBy('tip_pro_nom', 'asc')->get();
        $categorias = DB::table('categorias')->orderBy('cat_nom', 'asc')->get();

        // 2. Subconsulta para INSUMOS (4) y PRODUCTOS (0) (Desde producto_stock)
        $queryProductosInsumos = DB::table('productos as p')
            ->leftJoin('producto_stock as ps', 'p.IdProducto', '=', 'ps.IdProducto')
            ->leftJoin('tipo_producto as tp', 'p.tip_pro_id', '=', 'tp.tip_pro_id')
            ->leftJoin('categorias as c', 'p.cat_id', '=', 'c.cat_id')
            ->select(
                'p.IdProducto',
                'p.pronom',
                'tp.tip_pro_nom',
                'c.cat_nom',
                'p.tip_pro_id',
                'p.cat_id',
                DB::raw("CASE WHEN p.promocion = '4' THEN 'insumos' ELSE 'productos' END as tipo_origen"),
                DB::raw("COALESCE(SUM(ps.stock), 0) as stock_actual"),
                // Subconsulta para restar movimientos del rango seleccionado y obtener el stock inicial
                DB::raw("(COALESCE(SUM(ps.stock), 0) - COALESCE((
                    SELECT SUM(CASE WHEN mp.tipo_movimiento LIKE '%ingreso%' OR mp.tipo_movimiento = 'preparacion_diaria' THEN mp.cantidad ELSE -mp.cantidad END)
                    FROM movimientos_preparados as mp 
                    WHERE mp.producto_id = p.IdProducto 
                    AND mp.fecha_proceso BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}'
                ), 0)) as stock_inicial")
            )
            ->where('p.proest', 'Activo')
            ->whereIn('p.promocion', ['0', '4']) // 0 = PRODUCTOS, 4 = INSUMOS
            ->groupBy('p.IdProducto', 'p.pronom', 'tp.tip_pro_nom', 'c.cat_nom', 'p.tip_pro_id', 'p.cat_id', 'p.promocion');

        // 3. Subconsulta para PREPARADOS (2) (Desde productos.stock_preparados)
        $queryPreparados = DB::table('productos as p')
            ->leftJoin('tipo_producto as tp', 'p.tip_pro_id', '=', 'tp.tip_pro_id')
            ->leftJoin('categorias as c', 'p.cat_id', '=', 'c.cat_id')
            ->select(
                'p.IdProducto',
                'p.pronom',
                'tp.tip_pro_nom',
                'c.cat_nom',
                'p.tip_pro_id',
                'p.cat_id',
                DB::raw("'preparados' as tipo_origen"),
                DB::raw("COALESCE(p.stock_preparados, 0) as stock_actual"),
                // Subconsulta para calcular el Stock Inicial de preparados
                DB::raw("(COALESCE(p.stock_preparados, 0) - COALESCE((
                    SELECT SUM(CASE WHEN mp.tipo_movimiento LIKE '%ingreso%' OR mp.tipo_movimiento = 'preparacion_diaria' THEN mp.cantidad ELSE -mp.cantidad END)
                    FROM movimientos_preparados as mp 
                    WHERE mp.producto_id = p.IdProducto 
                    AND mp.fecha_proceso BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}'
                ), 0)) as stock_inicial")
            )
            ->where('p.proest', 'Activo')
            ->where('p.promocion', '2'); // 2 = PREPARADOS

        // 4. Aplicar el Filtro Principal (Tipo de Item)
        if ($tipo_item == 'insumos') {
            $unionQuery = $queryProductosInsumos->where('p.promocion', '4'); 
        } elseif ($tipo_item == 'productos') {
            $unionQuery = $queryProductosInsumos->where('p.promocion', '0'); 
        } elseif ($tipo_item == 'preparados') {
            $unionQuery = $queryPreparados;
        } else {
            // "todos" junta las dos consultas
            $unionQuery = $queryProductosInsumos->unionAll($queryPreparados);
        }

        // 5. Crear una consulta externa a partir del UNION para poder filtrar
        $rawQuery = DB::table(DB::raw("({$unionQuery->toSql()}) as unificado"))
            ->mergeBindings($unionQuery);

        // Filtro por Línea y Sub Línea
        if (!empty($tipo_producto_id)) {
            $rawQuery->where('tip_pro_id', $tipo_producto_id);
        }
        if (!empty($categoria_id)) {
            $rawQuery->where('cat_id', $categoria_id);
        }

        // Filtro de Stock Actual
        if ($filtro_stock == 'con_stock') {
            $rawQuery->where('stock_actual', '>', 0);
        } elseif ($filtro_stock == 'sin_stock') {
            $rawQuery->where('stock_actual', '=', 0);
        } elseif ($filtro_stock == 'negativos') {
            $rawQuery->where('stock_actual', '<', 0);
        }

        $items = $rawQuery->orderBy('pronom', 'asc')->get();

        return view('empresas.movimientos_preparados.reporte_general_stock', compact(
            'items', 'fecha_inicio', 'fecha_fin', 'tipo_item', 'tipos_producto', 'categorias', 'tipo_producto_id', 'categoria_id', 'filtro_stock'
        ));
    }


    public function exportarGeneralStock(Request $request)
    {
        $fecha_inicio = $request->get('fecha_inicio', Carbon::today()->toDateString());
        $fecha_fin = $request->get('fecha_fin', Carbon::today()->toDateString());
        $tipo_item = $request->get('tipo_item', 'todos');
        $tipo_producto_id = $request->get('tipo_producto_id', '');
        $categoria_id = $request->get('categoria_id', '');
        $filtro_stock = $request->get('filtro_stock', 'todos');
        $formato = $request->get('formato', 'excel');

        // Misma lógica para la exportación
        $queryProductosInsumos = DB::table('productos as p')
            ->leftJoin('producto_stock as ps', 'p.IdProducto', '=', 'ps.IdProducto')
            ->leftJoin('tipo_producto as tp', 'p.tip_pro_id', '=', 'tp.tip_pro_id')
            ->leftJoin('categorias as c', 'p.cat_id', '=', 'c.cat_id')
            ->select(
                'p.IdProducto', 'p.pronom', 'tp.tip_pro_nom', 'c.cat_nom', 'p.tip_pro_id', 'p.cat_id',
                DB::raw("CASE WHEN p.promocion = '4' THEN 'insumos' ELSE 'productos' END as tipo_origen"),
                DB::raw("COALESCE(SUM(ps.stock), 0) as stock_actual"),
                DB::raw("(COALESCE(SUM(ps.stock), 0) - COALESCE((SELECT SUM(CASE WHEN mp.tipo_movimiento LIKE '%ingreso%' OR mp.tipo_movimiento = 'preparacion_diaria' THEN mp.cantidad ELSE -mp.cantidad END) FROM movimientos_preparados as mp WHERE mp.producto_id = p.IdProducto AND mp.fecha_proceso BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}'), 0)) as stock_inicial")
            )
            ->where('p.proest', 'Activo')->whereIn('p.promocion', ['0', '4']) // 0 = PRODUCTOS, 4 = INSUMOS
            ->groupBy('p.IdProducto', 'p.pronom', 'tp.tip_pro_nom', 'c.cat_nom', 'p.tip_pro_id', 'p.cat_id', 'p.promocion');

        $queryPreparados = DB::table('productos as p')
            ->leftJoin('tipo_producto as tp', 'p.tip_pro_id', '=', 'tp.tip_pro_id')
            ->leftJoin('categorias as c', 'p.cat_id', '=', 'c.cat_id')
            ->select(
                'p.IdProducto', 'p.pronom', 'tp.tip_pro_nom', 'c.cat_nom', 'p.tip_pro_id', 'p.cat_id',
                DB::raw("'preparados' as tipo_origen"),
                DB::raw("COALESCE(p.stock_preparados, 0) as stock_actual"),
                DB::raw("(COALESCE(p.stock_preparados, 0) - COALESCE((SELECT SUM(CASE WHEN mp.tipo_movimiento LIKE '%ingreso%' OR mp.tipo_movimiento = 'preparacion_diaria' THEN mp.cantidad ELSE -mp.cantidad END) FROM movimientos_preparados as mp WHERE mp.producto_id = p.IdProducto AND mp.fecha_proceso BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}'), 0)) as stock_inicial")
            )
            ->where('p.proest', 'Activo')->where('p.promocion', '2'); // 2 = PREPARADOS

        // Filtro Principal
        if ($tipo_item == 'insumos') { $unionQuery = $queryProductosInsumos->where('p.promocion', '4'); }
        elseif ($tipo_item == 'productos') { $unionQuery = $queryProductosInsumos->where('p.promocion', '0'); }
        elseif ($tipo_item == 'preparados') { $unionQuery = $queryPreparados; }
        else { $unionQuery = $queryProductosInsumos->unionAll($queryPreparados); }

        $rawQuery = DB::table(DB::raw("({$unionQuery->toSql()}) as unificado"))->mergeBindings($unionQuery);

        if (!empty($tipo_producto_id)) $rawQuery->where('tip_pro_id', $tipo_producto_id);
        if (!empty($categoria_id)) $rawQuery->where('cat_id', $categoria_id);

        if ($filtro_stock == 'con_stock') $rawQuery->where('stock_actual', '>', 0);
        elseif ($filtro_stock == 'sin_stock') $rawQuery->where('stock_actual', '=', 0);
        elseif ($filtro_stock == 'negativos') $rawQuery->where('stock_actual', '<', 0);

        $items = $rawQuery->orderBy('pronom', 'asc')->get();

        // EXCEL TRADICIONAL RÁPIDO
        if ($formato == 'excel') {
            $fileName = "Reporte_General_Stock_" . date('Ymd') . ".xls";
            $headers = [
                "Content-type" => "application/vnd.ms-excel; charset=utf-8",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma" => "no-cache", "Expires" => "0"
            ];
            
            $html = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
            $html .= '<table border="1">
                        <tr>
                            <th style="background-color:#007bff; color:white;">ID</th>
                            <th style="background-color:#007bff; color:white;">Línea</th>
                            <th style="background-color:#007bff; color:white;">Sub Línea</th>
                            <th style="background-color:#007bff; color:white;">Tipo</th>
                            <th style="background-color:#007bff; color:white;">Producto</th>
                            <th style="background-color:#007bff; color:white;">Stock Inicial</th>
                            <th style="background-color:#007bff; color:white;">Stock Actual</th>
                        </tr>';
            foreach ($items as $item) {
                $html .= "<tr>
                            <td>{$item->IdProducto}</td>
                            <td>{$item->tip_pro_nom}</td>
                            <td>{$item->cat_nom}</td>
                            <td>" . strtoupper($item->tipo_origen) . "</td>
                            <td>{$item->pronom}</td>
                            <td>" . number_format($item->stock_inicial, 2) . "</td>
                            <td>" . number_format($item->stock_actual, 2) . "</td>
                          </tr>";
            }
            $html .= '</table>';
            return response($html, 200, $headers);
        }

        // Para PDF o Ticket, derivamos a las vistas de impresión correspondientes
        return view('empresas.movimientos_preparados.imprimir_general_stock', compact(
            'items', 'filtro_stock', 'formato', 'fecha_inicio', 'fecha_fin'
        ));
    }

    
    public function storeIngreso(Request $request)
    {
        $cantidades = $request->input('cantidades'); 
        $fecha_proceso = Carbon::today()->toDateString();
        
        // 1. VALIDACIÓN VITAL: Si no enviaron nada, lo regresamos con un aviso.
        if (empty($cantidades) || !is_array($cantidades)) {
            return redirect()->back()->with('error', 'No se ingresó ninguna cantidad para procesar.');
        }

        $usuario_id = auth()->check() ? auth()->id() : null;

        DB::beginTransaction();
        try {
            foreach ($cantidades as $producto_id => $cantidad) {
                if (is_numeric($cantidad) && $cantidad != 0) {
                    
                    $producto = DB::table('productos')
                                ->where('IdProducto', $producto_id)
                                ->lockForUpdate()
                                ->first();

                    // Si el producto no existe en BD por algún motivo, saltamos al siguiente
                    if (!$producto) continue;

                    $stockActual = $producto->stock_preparados ?? 0;
                    $nuevoStock = $stockActual + $cantidad;

                    $tipoMovimiento = $cantidad > 0 ? 'preparacion_diaria' : 'correccion_resta';
                    $observacion = $cantidad > 0 ? 'Ingreso de preparación matutina' : 'Corrección/resta de stock';

                    MovimientoPreparado::create([
                        'producto_id'      => $producto_id,
                        'pedido_id'        => null,
                        'usuario_id'       => $usuario_id,
                        'tipo_movimiento'  => $tipoMovimiento,
                        'cantidad'         => $cantidad,
                        'stock_resultante' => $nuevoStock, 
                        'observacion'      => $observacion,
                        'fecha_proceso'    => $fecha_proceso
                    ]);

                    DB::table('productos')
                        ->where('IdProducto', $producto_id)
                        ->update(['stock_preparados' => $nuevoStock]);
                }
            }
            DB::commit();
            return redirect()->back()->with('success', 'Los movimientos se registraron correctamente.');

        // 2. EL CAMBIO CLAVE: Usamos \Throwable para atrapar CUALQUIER tipo de error o caída del servidor
        } catch (\Throwable $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Hubo un error interno al guardar: ' . $e->getMessage());
        }
    }
}