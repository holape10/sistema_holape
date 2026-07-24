<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // Para manejar fechas

class DashboardController2 extends Controller
{
    /**
     * Muestra la vista principal del dashboard.
     */
    public function index()
    {
        // No pasamos fechas específicas aquí, ya que los datos se cargan sin filtro inicial
        // o con rangos predefinidos en las funciones de datos.
        // Solo el nombre del negocio para el select (si es necesario mantenerlo).
        $id_empresa_negocio = auth()->user()->id_empresa_negocio;
        $negocio = DB::table('empresa_negocios')
                    ->where('id_empresa_negocio', $id_empresa_negocio)
                    ->first();
        $nombre_negocio = $negocio ? $negocio->nombre_comercial : 'Establecimiento Principal';

        return view('empresas.dashboard2.index', compact('nombre_negocio'));
    }

    /**
     * Obtiene los datos para las tarjetas KPI (versión sin filtros de fecha por parámetro).
     */
    public function getKpiData(Request $request)
    {
        $id_empresa_negocio = auth()->user()->id_empresa_negocio;
        $today = Carbon::now()->toDateString();
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();

        // Total de ventas del día
        $totalSalesToday = DB::table('cpe_cabecera')
            ->where('id_empresa_negocio', $id_empresa_negocio)
            ->whereDate('ccafem', $today)
            ->where('tdocod', '!=', '07') // Excluir notas de crédito
            ->where('tdocod', '!=', '08') // Excluir notas de débito
            ->sum('ccaitv');

        // Total de ventas del mes
        $totalSalesMonth = DB::table('cpe_cabecera')
            ->where('id_empresa_negocio', $id_empresa_negocio)
            ->whereBetween('ccafem', [$startOfMonth, $today])
            ->where('tdocod', '!=', '07')
            ->where('tdocod', '!=', '08')
            ->sum('ccaitv');

        // Total de facturas del mes (para mantener la consistencia del KPI con el filtro del mes)
        $totalFacturas = DB::table('cpe_cabecera')
            ->where('id_empresa_negocio', $id_empresa_negocio)
            ->whereBetween('ccafem', [$startOfMonth, $today])
            ->where('tdocod', '01')
            ->sum('ccaitv');

        // Total de boletas del mes
        $totalBoletas = DB::table('cpe_cabecera')
            ->where('id_empresa_negocio', $id_empresa_negocio)
            ->whereBetween('ccafem', [$startOfMonth, $today])
            ->where('tdocod', '03')
            ->sum('ccaitv');

        // Total de notas de venta del mes (si es tdocod 13)
        $totalNotasVenta = DB::table('cpe_cabecera')
            ->where('id_empresa_negocio', $id_empresa_negocio)
            ->whereBetween('ccafem', [$startOfMonth, $today])
            ->where('tdocod', '13')
            ->sum('ccaitv');


        // Número total de pedidos activos (sin filtro de fecha)
        $activeOrders = DB::table('pedidos')
            ->where('id_empresa_negocio', $id_empresa_negocio)
            ->where('ped_est', 'Aperturado')
            ->count();

        // Productos con stock bajo (sin filtro de fecha)
        $lowStockProducts = DB::table('productos as p')
            ->join('producto_stock as ps', 'p.IdProducto', '=', 'ps.IdProducto')
            ->where('p.id_empresa_negocio', $id_empresa_negocio)
            ->where('ps.stock', '<=', DB::raw('p.stock_min'))
            ->where('p.proest', 'Activo')
            ->select('p.pronom', 'ps.stock', 'p.stock_min', 'ps.id_almacen')
            ->get();
        $lowStockCount = $lowStockProducts->count();

        // Mesas ocupadas/libres (sin filtro de fecha)
        $occupiedTables = DB::table('mesas')
            ->where('id_empresa_negocio', $id_empresa_negocio)
            ->where('mes_est', 'Ocupado')
            ->count();
        $freeTables = DB::table('mesas')
            ->where('id_empresa_negocio', $id_empresa_negocio)
            ->where('mes_est', 'Libre')
            ->count();


        return response()->json([
            'totalSalesToday' => number_format($totalSalesToday, 2),
            'totalSalesMonth' => number_format($totalSalesMonth, 2),
            'totalFacturas' => number_format($totalFacturas, 2),
            'totalBoletas' => number_format($totalBoletas, 2),
            'totalNotasVenta' => number_format($totalNotasVenta, 2),
            'activeOrders' => $activeOrders,
            'lowStockCount' => $lowStockCount,
            'occupiedTables' => $occupiedTables,
            'freeTables' => $freeTables,
        ]);
    }

    /**
     * Obtiene los datos para el gráfico de ventas mensuales (siempre los últimos 6 meses).
     */
    public function getSalesChartData(Request $request)
    {
        $id_empresa_negocio = auth()->user()->id_empresa_negocio;
        $endDate = Carbon::now()->toDateString();
        $startDate = Carbon::now()->subMonths(5)->startOfMonth()->toDateString(); // Últimos 6 meses

        $salesByMonth = DB::table('cpe_cabecera')
            ->select(
                DB::raw('DATE_FORMAT(ccafem, "%Y-%m") as month'),
                DB::raw('SUM(ccaitv) as total_sales')
            )
            ->where('id_empresa_negocio', $id_empresa_negocio)
            ->whereBetween('ccafem', [$startDate, $endDate])
            ->where('tdocod', '!=', '07')
            ->where('tdocod', '!=', '08')
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        $labels = $salesByMonth->pluck('month')->toArray();
        $data = $salesByMonth->pluck('total_sales')->map(function ($val) {
            return round($val, 2);
        })->toArray();

        return response()->json([
            'labels' => $labels,
            'data' => $data,
        ]);
    }

    /**
     * Obtiene los productos con stock bajo (sin filtro de fecha).
     */
    public function getStockAlerts(Request $request)
    {
        $id_empresa_negocio = auth()->user()->id_empresa_negocio;

        $lowStockProducts = DB::table('productos as p')
            ->join('producto_stock as ps', 'p.IdProducto', '=', 'ps.IdProducto')
            ->leftJoin('almacenes as a', 'ps.id_almacen', '=', 'a.id_almacen')
            ->where('p.id_empresa_negocio', $id_empresa_negocio)
            ->where('ps.stock', '<=', DB::raw('p.stock_min'))
            ->where('p.proest', 'Activo')
            ->select('p.procod', 'p.pronom', 'ps.stock', 'p.stock_min', 'a.descripcion as almacen_nombre')
            ->orderBy('ps.stock', 'asc')
            ->limit(5)
            ->get();

        return response()->json([
            'products' => $lowStockProducts,
        ]);
    }

    /**
     * Obtiene los últimos pedidos (sin filtro de fecha, solo los 10 más recientes).
     */
    public function getLatestOrders(Request $request)
    {
        $id_empresa_negocio = auth()->user()->id_empresa_negocio;

        $latestOrders = DB::table('pedidos as p')
            ->leftJoin('mesas as m', 'p.mes_id', '=', 'm.mes_id')
            ->select('p.ped_id', 'p.ped_cli_nom', 'p.ped_tot', 'p.ped_est', 'p.ped_tip', 'p.fecha_hora', 'm.mes_nom')
            ->where('p.id_empresa_negocio', $id_empresa_negocio)
            ->orderBy('p.fecha_hora', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'orders' => $latestOrders,
        ]);
    }

    /**
     * Obtiene los productos más vendidos (por defecto, del mes actual).
     */
    public function getTopSellingProducts(Request $request)
    {
        $id_empresa_negocio = auth()->user()->id_empresa_negocio;
        $startDate = Carbon::now()->startOfMonth()->toDateString();
        $endDate = Carbon::now()->toDateString();

        $topProducts = DB::table('cpe_detalle as cd')
            ->join('cpe_cabecera as cc', 'cd.IdCpe_cabecera', '=', 'cc.IdCpe_cabecera')
            ->join('productos as p', 'cd.IdProducto', '=', 'p.IdProducto')
            ->select(
                'p.pronom',
                DB::raw('SUM(cd.cdecan) as total_cantidad_vendida'),
                DB::raw('SUM(cd.cdepve) as total_valor_vendido')
            )
            ->where('cc.id_empresa_negocio', $id_empresa_negocio)
            ->whereBetween('cc.ccafem', [$startDate, $endDate])
            ->where('cc.tdocod', '!=', '07') // Excluir notas de crédito
            ->where('cc.tdocod', '!=', '08') // Excluir notas de débito
            ->groupBy('p.pronom')
            ->orderBy('total_cantidad_vendida', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'products' => $topProducts,
        ]);
    }

    /**
     * Obtiene los clientes más frecuentes (por defecto, del mes actual).
     */
    public function getTopCustomers(Request $request)
    {
        $id_empresa_negocio = auth()->user()->id_empresa_negocio;
        $startDate = Carbon::now()->startOfMonth()->toDateString();
        $endDate = Carbon::now()->toDateString();

        $topCustomers = DB::table('cpe_cabecera as cc')
            ->join('cliente as c', 'cc.clicod', '=', 'c.clicod')
            ->select(
                'c.clinom',
                'c.clinum',
                DB::raw('COUNT(cc.IdCpe_cabecera) as total_pedidos'),
                DB::raw('SUM(cc.ccaitv) as total_gasto')
            )
            ->where('cc.id_empresa_negocio', $id_empresa_negocio)
            ->whereBetween('cc.ccafem', [$startDate, $endDate])
            ->where('cc.tdocod', '!=', '07')
            ->where('cc.tdocod', '!=', '08')
            ->where('c.clinom', '!=', 'VENTA AL PORTADOR') // Excluir ventas genéricas
            ->groupBy('c.clicod', 'c.clinom', 'c.clinum')
            ->orderBy('total_gasto', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'customers' => $topCustomers,
        ]);
    }
}