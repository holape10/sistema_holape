<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\FidelizacionConfig;

class FidelizacionController extends Controller
{
    public function index()
    {
        // Traemos todas las configuraciones
        $reglas = FidelizacionConfig::all();
        //$configs = FidelizacionConfig::all()->pluck('valor', 'clave');
        return view('empresas.fidelizacion.index', compact('reglas'));
    }

    public function store(Request $request)
    {
        // Usamos except('_token') para que no intente guardar el código de seguridad en la DB
        FidelizacionConfig::create($request->except('_token'));

        return redirect()->back()->with('success', '¡Regla creada correctamente!');
    }

    public function destroy($id)
    {
        FidelizacionConfig::find($id)->delete();
        return redirect()->back()->with('success', 'Regla eliminada');
    }

    public function actualizarRegla(\Illuminate\Http\Request $request, $id)
    {
        // Limpiamos la fecha por si la mandan vacía
        $fecha = $request->fecha_vencimiento;
        if(empty($fecha)){
            $fecha = null;
        }

        \DB::table('fidelizacion_configs')
            ->where('id', $id)
            ->update([
                'descripcion' => $request->descripcion,
                'valor_sol' => $request->valor_sol,
                'puntos_minimos' => $request->puntos_minimos,
                'premio' => $request->premio,
                'fecha_vencimiento' => $fecha,
                'updated_at' => \Carbon\Carbon::now()
            ]);

        // Opcional: Aquí puedes poner tu notificación de éxito (SweetAlert, Toastr, etc)
        return back()->with('success', '¡La regla y la fecha se actualizaron correctamente!');
    }

    public function update(Request $request, $id) {
        // Aquí editas la regla
        $regla = FidelizacionConfig::find($id);
        $regla->update($request->all());
        return redirect()->back()->with('success', 'Regla actualizada');
    }

    public function canjearPremio(\Illuminate\Http\Request $request)
    {
        $cliente = \DB::table('cliente')->where('clicod', $request->cliente_id)->first();
        $regla = \DB::table('fidelizacion_configs')->where('id', $request->regla_id)->first();

        if($cliente && $regla && $cliente->puntos >= $regla->puntos_minimos) {
            
            $saldo_antes = $cliente->puntos;
            $saldo_despues = $saldo_antes - $regla->puntos_minimos;

            // 1. Restamos los puntos al cliente
            \DB::table('cliente')
                ->where('clicod', $cliente->clicod)
                ->update(['puntos' => $saldo_despues]);

            // 2. Guardamos en el historial que se "quemaron" esos puntos
            \DB::table('puntos_historial')->insert([
                'cliente_id' => $cliente->clicod,
                'venta_id' => null, // Es null porque es un canje directo en caja, no una venta nueva
                'puntos_ganados' => 0,
                'puntos_canjeados' => $regla->puntos_minimos,
                'saldo_antes' => $saldo_antes,
                'saldo_despues' => $saldo_despues,
                'motivo' => 'Premio: ' . $regla->premio,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]);

            return response()->json([
                'estado' => 'success', 
                'mensaje' => 'El canje se procesó correctamente. Puntos restantes: ' . $saldo_despues
            ]);
        }

        return response()->json(['estado' => 'error', 'mensaje' => 'Puntos insuficientes o regla no encontrada.']);
    }

    public function consultarPuntos($id)
    {
        // Buscamos al cliente
        $cliente = \DB::table('cliente')->where('clicod', $id)->first();
        
        // Traemos TODAS las reglas activas ordenadas por puntos necesarios
        $reglas = \DB::table('fidelizacion_configs')
                    ->where('activo', 1)
                    ->orderBy('puntos_minimos', 'asc')
                    ->get();

        return response()->json([
            'puntos' => $cliente ? ($cliente->puntos ?? 0) : 0,
            'reglas' => $reglas
        ]);
    }

    public function toggleEstado($id)
    {
        $regla = FidelizacionConfig::find($id);
        $regla->activo = !$regla->activo; // Si es 1 pasa a 0, y viceversa
        $regla->save();

        return redirect()->back()->with('success', 'Estado actualizado');
    }
}