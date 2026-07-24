<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Vehiculo;
use MasterSoft\Chofer;
use DB;

class UnidadController extends Controller
{
    // Mostrar todo en una sola vista (Vehículos y Choferes juntos)
    public function index()
    {
        // Si usas Auth para sacar la empresa, lo reemplazas aquí
        $id_empresa = 1; 

        $vehiculos = Vehiculo::where('id_empresa_negocio', $id_empresa)->orderBy('id', 'desc')->get();
        $choferes = Chofer::where('id_empresa_negocio', $id_empresa)->orderBy('id', 'desc')->get();

        return view('empresas.transportes.unidades.index', compact('vehiculos', 'choferes'));
    }

    // Guardar Vehículo rápido
    public function storeVehiculo(Request $request)
    {
        $request->validate([
            'placa' => 'required|unique:vehiculos,placa'
        ]);

        Vehiculo::create([
            'placa'              => strtoupper($request->placa),
            'marca'              => strtoupper($request->marca),
            'modelo'             => strtoupper($request->modelo),
            'carga_util_kg'      => $request->carga_util_kg ?? 0,
            'inscripcion_mtc'    => strtoupper($request->inscripcion_mtc),
            'estado'             => 'ACTIVO',
            'id_empresa_negocio' => 1 // Cambiar dinámicamente según sesión
        ]);

        return back()->with('success', 'Vehículo registrado correctamente.');
    }

    // Guardar Chofer rápido
    public function storeChofer(Request $request)
    {
        $request->validate([
            'dni'      => 'required|unique:choferes,dni',
            'licencia' => 'required'
        ]);

        Chofer::create([
            'dni'                => $request->dni,
            'nombres_apellidos'  => strtoupper($request->nombres_apellidos),
            'licencia'           => strtoupper($request->licencia),
            'telefono'           => $request->telefono,
            'estado'             => 'ACTIVO',
            'id_empresa_negocio' => 1 // Cambiar dinámicamente según sesión
        ]);

        return back()->with('success', 'Chofer registrado correctamente.');
    }
}