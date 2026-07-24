<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Viaje;
use MasterSoft\Vehiculo;
use Carbon\Carbon;

class ViajeController extends Controller
{
    public function index()
    {
        $viajes = Viaje::with('vehiculo')->orderBy('fecha_salida', 'DESC')->get();
        return view('empresas.viajes.index', compact('viajes'));
    }

    public function create()
    {
        $vehiculos = Vehiculo::where('estado', 'activo')->get();
        return view('empresas.viajes.create', compact('vehiculos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehiculo_id' => 'required|exists:vehiculos,id',
            'origen' => 'required|max:100',
            'destino' => 'required|max:100',
            'fecha_salida' => 'required',
            'costo_estimado' => 'numeric',
        ]);

        $data = $request->all();
        // SOLUCIÓN AL ERROR DE CARBON: Formateamos la fecha al estándar de MySQL
        $data['fecha_salida'] = Carbon::parse($request->fecha_salida)->format('Y-m-d H:i:s');

        Viaje::create($data);

        return redirect()->route('viajes.index')->with('success', 'Viaje programado correctamente.');
    }

    public function edit($id)
    {
        $viaje = Viaje::findOrFail($id);
        $vehiculos = Vehiculo::where('estado', 'activo')->get(); // Para poder cambiar de unidad si se requiere
        return view('empresas.viajes.edit', compact('viaje', 'vehiculos'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'vehiculo_id' => 'required|exists:vehiculos,id',
            'origen' => 'required|max:100',
            'destino' => 'required|max:100',
            'fecha_salida' => 'required',
            'costo_estimado' => 'numeric',
            'estado' => 'required|in:pendiente,en_ruta,completado,cancelado'
        ]);

        $viaje = Viaje::findOrFail($id);
        
        $data = $request->all();
        // Formateamos la fecha nuevamente al actualizar
        $data['fecha_salida'] = Carbon::parse($request->fecha_salida)->format('Y-m-d H:i:s');

        $viaje->update($data);

        return redirect()->route('viajes.index')->with('success', 'Viaje actualizado correctamente.');
    }

    public function destroy($id)
    {
        $viaje = Viaje::findOrFail($id);
        $viaje->delete(); // SoftDelete

        return redirect()->route('viajes.index')->with('success', 'Viaje anulado del sistema.');
    }
}