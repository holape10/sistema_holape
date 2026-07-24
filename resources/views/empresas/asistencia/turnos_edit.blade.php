@extends('layouts.empresas')

@section('contenido')
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0 font-weight-bold"><i class="fas fa-edit"></i> Editar Turno: {{ $turno->codigo }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('asistencia.turnos.update', $turno->id) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Código</label>
                        <input type="text" name="codigo" class="form-control" value="{{ $turno->codigo }}" required>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Descripción</label>
                        <input type="text" name="descripcion" class="form-control" value="{{ $turno->descripcion }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-6 form-group">
                        <label>Entrada 1</label>
                        <input type="time" name="hora_entrada_1" class="form-control" value="{{ date('H:i', strtotime($turno->hora_entrada_1)) }}" required>
                    </div>
                    <div class="col-6 form-group">
                        <label>Salida 1</label>
                        <input type="time" name="hora_salida_1" class="form-control" value="{{ date('H:i', strtotime($turno->hora_salida_1)) }}" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6 form-group">
                        <label>Entrada 2 (Opcional)</label>
                        <input type="time" name="hora_entrada_2" class="form-control" value="{{ $turno->hora_entrada_2 ? date('H:i', strtotime($turno->hora_entrada_2)) : '' }}">
                    </div>
                    <div class="col-6 form-group">
                        <label>Salida 2 (Opcional)</label>
                        <input type="time" name="hora_salida_2" class="form-control" value="{{ $turno->hora_salida_2 ? date('H:i', strtotime($turno->hora_salida_2)) : '' }}">
                    </div>
                </div>

                <div class="form-group">
                    <label>Tolerancia (Minutos)</label>
                    <input type="number" name="tolerancia_minutos" class="form-control" value="{{ $turno->tolerancia_minutos }}" required>
                </div>

                <a href="{{ route('asistencia.turnos') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-success font-weight-bold">Guardar Cambios</button>
            </form>
        </div>
    </div>
</div>
@endsection