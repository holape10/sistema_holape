@extends('layouts.empresas')

@section('contenido')
<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col-md-12">
            <h2 class="text-dark">Registrar Mantenimiento</h2>
            <p class="text-muted">Unidad seleccionada: <strong>{{ $vehiculo->placa }} ({{ $vehiculo->marca }} {{ $vehiculo->modelo }})</strong></p>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('mantenimientos.store') }}" method="POST">
                @csrf
                <input type="hidden" name="vehiculo_id" value="{{ $vehiculo->id }}">

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label for="fecha_mantenimiento" class="font-weight-bold">Fecha del Servicio</label>
                        <input type="date" name="fecha_mantenimiento" class="form-control" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" required>
                    </div>
                    
                    <div class="col-md-4 form-group">
                        <label for="tipo_mantenimiento" class="font-weight-bold">Tipo de Mantenimiento</label>
                        <select name="tipo_mantenimiento" class="form-control" required>
                            <option value="">Seleccione una opción...</option>
                            <option value="Cambio de Aceite">Cambio de Aceite</option>
                            <option value="Cambio de Llantas">Cambio de Llantas</option>
                            <option value="Frenos">Revisión/Cambio de Frenos</option>
                            <option value="Motor">Reparación de Motor</option>
                            <option value="Sistema Eléctrico">Sistema Eléctrico</option>
                            <option value="Otros">Otros</option>
                        </select>
                    </div>

                    <div class="col-md-4 form-group">
                        <label for="kilometraje_actual" class="font-weight-bold">Kilometraje Actual</label>
                        <input type="number" name="kilometraje_actual" class="form-control" placeholder="Ej: 55000" required>
                    </div>
                </div>
                
                <div class="row mt-2">
                    <div class="col-md-8 form-group">
                        <label for="descripcion" class="font-weight-bold">Descripción del Trabajo (Opcional)</label>
                        <textarea name="descripcion" class="form-control" rows="3" placeholder="Detalles de los repuestos usados o trabajos realizados..."></textarea>
                    </div>
                    
                    <div class="col-md-4 form-group">
                        <label for="costo" class="font-weight-bold">Costo Total (S/)</label>
                        <input type="number" step="0.01" name="costo" class="form-control" value="0.00" required>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-12 text-right">
                        <a href="{{ route('vehiculos.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-success shadow-sm">
                            <i class="fas fa-save"></i> Guardar Mantenimiento
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection