<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Vehiculo;

class VehiculoController extends Controller
{
    public function index()
    {
        $vehiculos = Vehiculo::orderBy('id', 'DESC')->get();
        $hoy = \Carbon\Carbon::now()->startOfDay();

        // Lógica del Semáforo
        foreach ($vehiculos as $v) {
            // Diferencia de días (positivo si es futuro, negativo si ya pasó)
            $dias_soat = $hoy->diffInDays(\Carbon\Carbon::parse($v->fecha_vencimiento_soat)->startOfDay(), false);
            $dias_rt = $hoy->diffInDays(\Carbon\Carbon::parse($v->fecha_vencimiento_rt)->startOfDay(), false);

            // Semáforo SOAT
            if ($dias_soat < 0) {
                $v->color_soat = 'danger'; $v->texto_soat = 'Vencido';
            } elseif ($dias_soat <= 15) {
                $v->color_soat = 'warning'; $v->texto_soat = $dias_soat . ' días';
            } else {
                $v->color_soat = 'success'; $v->texto_soat = 'Al día';
            }

            // Semáforo Revisión Técnica
            if ($dias_rt < 0) {
                $v->color_rt = 'danger'; $v->texto_rt = 'Vencida';
            } elseif ($dias_rt <= 15) {
                $v->color_rt = 'warning'; $v->texto_rt = $dias_rt . ' días';
            } else {
                $v->color_rt = 'success'; $v->texto_rt = 'Al día';
            }
        }

        return view('empresas.vehiculos.index', compact('vehiculos'));
    }

    public function create()
    {
        return view('empresas.vehiculos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'placa' => 'required|unique:vehiculos|max:15',
            'marca' => 'required|max:50',
            'modelo' => 'required|max:50',
            'capacidad_carga' => 'required|numeric',
            'anio' => 'required|integer',
            'fecha_vencimiento_soat' => 'required|date',
            'fecha_vencimiento_rt' => 'required|date',
        ]);

        Vehiculo::create($request->all());
        return redirect()->route('vehiculos.index')->with('success', 'Vehículo registrado correctamente.');
    }
    public function edit($id)
    {
        $vehiculo = Vehiculo::findOrFail($id);
        return view('empresas.vehiculos.edit', compact('vehiculo'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'placa' => 'required|max:15|unique:vehiculos,placa,'.$id,
            'marca' => 'required|max:50',
            'modelo' => 'required|max:50',
            'capacidad_carga' => 'required|numeric',
            'anio' => 'required|integer',
            'fecha_vencimiento_soat' => 'required|date',
            'fecha_vencimiento_rt' => 'required|date',
            'estado' => 'required|in:activo,mantenimiento,inactivo'
        ]);

        $vehiculo = Vehiculo::findOrFail($id);
        $vehiculo->update($request->all());
        return redirect()->route('vehiculos.index')->with('success', 'Vehículo actualizado correctamente.');
    }

    public function destroy($id)
    {
        $vehiculo = Vehiculo::findOrFail($id);
        $vehiculo->delete(); // Esto hace el SoftDelete automático

        return redirect()->route('vehiculos.index')->with('success', 'Vehículo anulado del sistema.');
    }
}