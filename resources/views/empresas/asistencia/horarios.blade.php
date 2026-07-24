@extends('layouts.empresas')

@section('contenido')
<style>
    .tabla-horarios th { background-color: #007bff; color: white; text-align: center; vertical-align: middle !important; font-weight: bold; text-transform: uppercase; }
    .tabla-horarios td { vertical-align: middle !important; padding: 5px !important; }
    .select-turno { width: 100%; border: 1px solid #ced4da; border-radius: 4px; padding: 4px; text-align: center; font-weight: bold; }
    .select-turno:invalid, .select-turno option[value=""] { background-color: #f8d7da; color: #721c24; }
    .columna-fija { position: sticky; left: 0; background-color: #f4f6f9; z-index: 10; border-right: 2px solid #dee2e6; font-weight: bold; font-size: 0.9rem; }
</style>

<div class="container-fluid mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-calendar-alt"></i> Matriz de Turnos</h4>
            <div>
                <a href="{{ route('asistencia.horarios', ['fecha_inicio' => $fechaInicio, 'action' => 'excel']) }}" class="btn btn-success font-weight-bold mr-1 shadow-sm">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
                <a href="{{ route('asistencia.horarios', ['fecha_inicio' => $fechaInicio, 'action' => 'pdf']) }}" target="_blank" class="btn btn-danger font-weight-bold mr-3 shadow-sm">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>

                <a href="{{ route('asistencia.turnos') }}" class="btn btn-warning font-weight-bold mr-3 shadow-sm">
                    <i class="fas fa-plus-circle"></i> Gestionar Turnos
                </a>
                
                <a href="{{ route('asistencia.horarios', ['fecha_inicio' => $semanaAnterior]) }}" class="btn btn-sm btn-light font-weight-bold">
                    <i class="fas fa-chevron-left"></i> Semana Ant.
                </a>
                <span class="mx-3 font-weight-bold" style="font-size: 1.1rem;">Semana del {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }}</span>
                <a href="{{ route('asistencia.horarios', ['fecha_inicio' => $semanaSiguiente]) }}" class="btn btn-sm btn-light font-weight-bold">
                    Semana Sig. <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>

        <div class="card-body p-0">
            @if(session('success'))
                <div class="alert alert-success m-3">
                    <i class="fa fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('asistencia.horarios.guardar') }}" method="POST">
                @csrf
                <input type="hidden" name="fecha_inicio" value="{{ $fechaInicio }}">

                <div class="table-responsive">
                    <table class="table table-bordered table-hover tabla-horarios mb-0" style="min-width: 1000px;">
                        <thead>
                            <tr>
                                <th class="columna-fija" style="width: 250px; background-color: #343a40;">COLABORADOR</th>
                                @foreach($fechas as $fecha)
                                    <th>
                                        {{ $fecha['dia_nombre'] }}<br> {{ $fecha['vista'] }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($empleados as $emp)
                                <tr>
                                    <td class="columna-fija">{{ $emp->emp_nom }} {{ $emp->emp_ape_pat }} {{ $emp->emp_ape_mat }}</td>
                                    
                                    @foreach($fechas as $fecha)
                                        @php
                                            $turnoGuardado = isset($matriz[$emp->emp_id][$fecha['sql']]) ? $matriz[$emp->emp_id][$fecha['sql']] : null;
                                        @endphp
                                        <td>
                                            <select name="horario[{{ $emp->emp_id }}][{{ $fecha['sql'] }}]" class="select-turno">
                                                <option value="">DESCANSO</option>
                                                @foreach($turnos as $turno)
                                                    <option value="{{ $turno->id }}" {{ $turnoGuardado == $turno->id ? 'selected' : '' }}>
                                                        {{ $turno->codigo }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3 bg-light text-right border-top">
                    <button type="submit" class="btn btn-success btn-lg font-weight-bold shadow">
                        <i class="fas fa-save"></i> Guardar Matriz Semanal
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="mt-5 mb-5">
        <h4 class="font-weight-bold text-secondary mb-4"><i class="fas fa-info-circle"></i> Leyenda de Turnos Registrados</h4>
        <div class="row">
            @foreach($turnos as $turno)
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card p-3 border-left-primary shadow h-100" style="border-left-width: 6px !important; border-radius: 10px;">
                        <h4 class="font-weight-bold text-primary mb-2">{{ $turno->codigo }} - <span class="text-dark">{{ $turno->descripcion }}</span></h4> 
                        <div style="font-size: 1.15rem; color: #444;">
                            <i class="fas fa-clock text-info"></i> {{ \Carbon\Carbon::parse($turno->hora_entrada_1)->format('H:i') }} a {{ \Carbon\Carbon::parse($turno->hora_salida_1)->format('H:i') }}
                            
                            @if($turno->hora_entrada_2)
                                <hr style="margin: 8px 0;">
                                <i class="fas fa-clock text-warning"></i> {{ \Carbon\Carbon::parse($turno->hora_entrada_2)->format('H:i') }} a {{ \Carbon\Carbon::parse($turno->hora_salida_2)->format('H:i') }}
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection