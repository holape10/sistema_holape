@extends('layouts.empresas')

@section('contenido')
<div class="container-fluid mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 mt-2">
            <h4 class="text-dark">Registrar Nuevo Vehículo</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('vehiculos.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label for="placa" class="font-weight-bold">Placa</label>
                        <input type="text" name="placa" class="form-control" placeholder="Ej: ABC-123" required>
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="marca" class="font-weight-bold">Marca</label>
                        <input type="text" name="marca" class="form-control" placeholder="Ej: Volvo" required>
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="modelo" class="font-weight-bold">Modelo</label>
                        <input type="text" name="modelo" class="form-control" placeholder="Ej: FH16" required>
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="capacidad_carga" class="font-weight-bold">Capacidad (Kg)</label>
                        <input type="number" step="0.01" name="capacidad_carga" class="form-control" required>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-4 form-group">
                        <label for="anio" class="font-weight-bold">Año de Fabricación</label>
                        <input type="number" name="anio" class="form-control" placeholder="Ej: 2020" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="fecha_vencimiento_soat" class="font-weight-bold">Vencimiento SOAT</label>
                        <input type="date" name="fecha_vencimiento_soat" class="form-control" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="fecha_vencimiento_rt" class="font-weight-bold">Vencimiento Rev. Técnica</label>
                        <input type="date" name="fecha_vencimiento_rt" class="form-control" required>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-12 text-right">
                        <a href="{{ route('vehiculos.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary shadow-sm">Guardar Vehículo</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection