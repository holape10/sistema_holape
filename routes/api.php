<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use MasterSoft\Http\Controllers\DashboardController; // Asegúrate de que el namespace sea correcto

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "api" middleware group. Enjoy building your API!
|
*/

// Agrega estas rutas para tu Dashboard
Route::middleware('auth:api')->group(function () {
    // Si usas autenticación API, descomenta la línea anterior y envuelve las rutas
    // O si no usas autenticación API por ahora, simplemente deja las rutas como están directamente aquí.

    Route::get('/dashboard/summary', [DashboardController::class, 'getSummaryData']); // Este ya no es necesario si lo cargas con Blade, pero lo mantengo por si quieres refactorizarlo a futuro.
    Route::get('/dashboard/monthly-sales', [DashboardController::class, 'getMonthlySales']);
    Route::get('/dashboard/sales-by-doc-type', [DashboardController::class, 'getSalesByDocType']);
    Route::get('/dashboard/top-products', [DashboardController::class, 'getTopProducts']);
    Route::get('/dashboard/low-stock-products', [DashboardController::class, 'getLowStockProducts']); // Este lo estamos cargando directo en Blade, pero si lo quieres API, así sería.
    Route::get('/dashboard/top-clients', [DashboardController::class, 'getTopClients']);
});

// Ruta de ejemplo existente, si tienes una
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//Route::get('/api/v1/dni/{dni}', 'SunatController@consultarDni');
Route::get('/v1/dni/{dni}', 'SunatController@consultarDni');

// Ruta para pedir tickets
Route::get('/impresion/pendiente', function(Request $request) {
    if ($request->query('token') !== 'HOLAPE10') {
        return response()->json(['error' => 'No autorizado'], 401);
    }

    $pedido = DB::table('cola_impresion')->where('estado', '0')->orderBy('id', 'asc')->first();
    return response()->json($pedido);
});

Route::get('/v1/ruc/{ruc}', 'SunatController@consultar');

// Ruta para marcar impreso
Route::get('/impresion/marcar-impreso/{id}', function(Request $request, $id) {
    if ($request->query('token') !== 'HOLAPE10') {
        return response()->json(['error' => 'No autorizado'], 401);
    }

    DB::table('cola_impresion')->where('id', $id)->update(['estado' => '1']);
    return response()->json(['success' => true]);
});