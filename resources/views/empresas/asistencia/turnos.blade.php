@extends('layouts.empresas')
@section('contenido')

@if(session('error'))
    <div class="alert alert-danger">
        <i class="fa fa-exclamation-triangle"></i> {{ session('error') }}
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success">
        <i class="fa fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-plus"></i> Crear Nuevo Turno</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('asistencia.turnos.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label class="font-weight-bold">Código (Ej: A, C, B1)</label>
                            <input type="text" name="codigo" class="form-control text-uppercase" required placeholder="A">
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Descripción (Opcional)</label>
                            <input type="text" name="descripcion" class="form-control" placeholder="Ej: Turno Mañana">
                        </div>
                        
                        <hr>
                        <h6 class="font-weight-bold text-primary">Horario Principal</h6>
                        <div class="row">
                            <div class="col-6">
                                <label>Entrada 1</label>
                                <input type="time" name="hora_entrada_1" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label>Salida 1</label>
                                <input type="time" name="hora_salida_1" class="form-control" required>
                            </div>
                        </div>

                        <hr>
                        <h6 class="font-weight-bold text-warning">Turno Partido (Solo si aplica)</h6>
                        <div class="row">
                            <div class="col-6">
                                <label>Entrada 2</label>
                                <input type="time" name="hora_entrada_2" class="form-control">
                            </div>
                            <div class="col-6">
                                <label>Salida 2</label>
                                <input type="time" name="hora_salida_2" class="form-control">
                            </div>
                        </div>

                        <hr>
                        <div class="form-group">
                            <label class="font-weight-bold">Tolerancia (Minutos)</label>
                            <input type="number" name="tolerancia_minutos" class="form-control" value="15" required>
                            <small class="text-muted">Tiempo de gracia antes de marcar tardanza.</small>
                        </div>

                        <button type="submit" class="btn btn-success btn-block font-weight-bold mt-3">Guardar Turno</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Turnos Registrados</h5>
                    <a href="{{ route('asistencia.horarios') }}" class="btn btn-sm btn-light font-weight-bold">
                        <i class="fas fa-arrow-left"></i> Volver a la Matriz
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            <i class="fa fa-check-circle"></i> {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-center">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Código</th>
                                    <th>Descripción</th>
                                    <th>Horario 1</th>
                                    <th>Horario 2</th>
                                    <th>Tolerancia</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($turnos as $turno)
                                <tr>
                                    <td class="font-weight-bold text-primary h5 align-middle">{{ $turno->codigo }}</td>
                                    <td class="align-middle">{{ $turno->descripcion }}</td>
                                    <td class="align-middle text-success font-weight-bold">
                                        {{ \Carbon\Carbon::parse($turno->hora_entrada_1)->format('H:i') }} - {{ \Carbon\Carbon::parse($turno->hora_salida_1)->format('H:i') }}
                                    </td>
                                    <td class="align-middle text-warning font-weight-bold">
                                        {{ $turno->hora_entrada_2 ? \Carbon\Carbon::parse($turno->hora_entrada_2)->format('H:i') . ' - ' . \Carbon\Carbon::parse($turno->hora_salida_2)->format('H:i') : '--' }}
                                    </td>
                                    <td class="align-middle">{{ $turno->tolerancia_minutos }} min</td>
                                    <td class="align-middle">
                                        <a href="{{ route('asistencia.turnos.edit', $turno->id) }}" class="btn btn-warning btn-sm mr-1">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('asistencia.turnos.destroy', $turno->id) }}" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection