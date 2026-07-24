@extends('layouts.empresas')

@section('contenido')
<div class="container-fluid mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 mt-2">
            <h4 class="text-dark">Programar Nuevo Viaje</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('viajes.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label for="vehiculo_id" class="font-weight-bold">Vehículo Asignado</label>
                        <select name="vehiculo_id" class="form-control" required>
                            <option value="">Seleccione una unidad activa...</option>
                            @foreach($vehiculos as $vehiculo)
                                <option value="{{ $vehiculo->id }}">{{ $vehiculo->placa }} - {{ $vehiculo->marca }} (Cap: {{ $vehiculo->capacidad_carga }}Kg)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="origen" class="font-weight-bold">Punto de Origen</label>
                        <input type="text" name="origen" class="form-control" placeholder="Ej: Almacén Central" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="destino" class="font-weight-bold">Punto de Destino</label>
                        <input type="text" name="destino" class="form-control" placeholder="Ej: Sucursal Norte" required>
                    </div>
                </div>
                
                <div class="row mt-2">
                    <div class="col-md-4 form-group">
                        <label for="fecha_salida" class="font-weight-bold">Fecha y Hora de Salida</label>
                        <input type="datetime-local" name="fecha_salida" class="form-control" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="costo_estimado" class="font-weight-bold">Costo Estimado del Flete (S/)</label>
                        <input type="number" step="0.01" name="costo_estimado" class="form-control" value="0.00" required>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-12 text-right">
                        <a href="{{ route('viajes.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-success shadow-sm"><i class="fas fa-save"></i> Guardar Programación</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection