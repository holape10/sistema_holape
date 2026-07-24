@extends('layouts.empresas')

@section('contenido')
<div class="container-fluid mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 mt-2">
            <h4 class="text-dark">Editar Viaje</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('viajes.update', $viaje->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label for="vehiculo_id" class="font-weight-bold">Vehículo Asignado</label>
                        <select name="vehiculo_id" class="form-control" required>
                            @foreach($vehiculos as $vehiculo)
                                <option value="{{ $vehiculo->id }}" {{ $viaje->vehiculo_id == $vehiculo->id ? 'selected' : '' }}>
                                    {{ $vehiculo->placa }} - {{ $vehiculo->marca }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="origen" class="font-weight-bold">Punto de Origen</label>
                        <input type="text" name="origen" class="form-control" value="{{ $viaje->origen }}" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="destino" class="font-weight-bold">Punto de Destino</label>
                        <input type="text" name="destino" class="form-control" value="{{ $viaje->destino }}" required>
                    </div>
                </div>
                
                <div class="row mt-2">
                    <div class="col-md-4 form-group">
                        <label for="fecha_salida" class="font-weight-bold">Fecha y Hora de Salida</label>
                        <input type="datetime-local" name="fecha_salida" class="form-control" value="{{ \Carbon\Carbon::parse($viaje->fecha_salida)->format('Y-m-d\TH:i') }}" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="costo_estimado" class="font-weight-bold">Costo Estimado del Flete (S/)</label>
                        <input type="number" step="0.01" name="costo_estimado" class="form-control" value="{{ $viaje->costo_estimado }}" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="estado" class="font-weight-bold">Estado del Viaje</label>
                        <select name="estado" class="form-control">
                            <option value="pendiente" {{ $viaje->estado == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="en_ruta" {{ $viaje->estado == 'en_ruta' ? 'selected' : '' }}>En Ruta</option>
                            <option value="completado" {{ $viaje->estado == 'completado' ? 'selected' : '' }}>Completado</option>
                            <option value="cancelado" {{ $viaje->estado == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                        </select>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-12 text-right">
                        <a href="{{ route('viajes.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-success shadow-sm"><i class="fas fa-save"></i> Actualizar Programación</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection