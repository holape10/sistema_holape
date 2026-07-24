@extends('layouts.empresas')

@section('contenido')
<div class="container-fluid mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-chart-line"></i> Reporte de Tiempos y Jornadas Laborales</h4>
        </div>
        <div class="card-body">
            
     
            <form action="{{ route('asistencia.reporte_detallado') }}" method="GET" class="mb-4">
                <div class="row align-items-end">
                    <div class="col-md-2 col-sm-6 mb-2">
                        <label class="font-weight-bold text-dark">Desde:</label>
                        <input type="date" name="fecha_inicio" class="form-control" value="{{ $fecha_inicio }}">
                    </div>
                    <div class="col-md-2 col-sm-6 mb-2">
                        <label class="font-weight-bold text-dark">Hasta:</label>
                        <input type="date" name="fecha_fin" class="form-control" value="{{ $fecha_fin }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="font-weight-bold text-dark">Empleado:</label>
                        <select name="emp_id" class="form-control">
                            <option value="todos">Todos los colaboradores</option>
                            @foreach($empleados as $emp)
                                <option value="{{ $emp->emp_id }}" {{ $emp_id == $emp->emp_id ? 'selected' : '' }}>
                                    {{ $emp->emp_nom }} {{ $emp->emp_ape_pat }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    
                    <div class="col-md-5 mb-2 text-right">
                        <button type="submit" name="action" value="ver" class="btn btn-primary font-weight-bold shadow-sm px-3"><i class="fa fa-filter"></i> Analizar</button>
                        <button type="submit" name="action" value="excel" class="btn btn-success font-weight-bold shadow-sm px-3"><i class="fas fa-file-excel"></i> Excel</button>
                        <button type="submit" name="action" value="pdf" formtarget="_blank" class="btn btn-danger font-weight-bold shadow-sm px-3"><i class="fas fa-file-pdf"></i> PDF</button>
                    </div>
                </div>
            </form>
            <hr>

            
            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center table-hover">
                    <thead class="thead-dark" style="font-size: 1.05rem;">
                        <tr>
                            <th>Fecha</th>
                            <th>Colaborador</th>
                            <th>Turno</th>
                            <th>Tiempo Laborado <br><small class="text-warning">(Efectivo Oficial)</small></th>
                            <th style="min-width: 250px;">Tardanza Acumulada / Justificación</th>
                            <th>Tiempo Extra <br><small class="text-light">(A revisar)</small></th>
                            <th>Validación Legal</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 1.05rem; vertical-align: middle; color: #212529;">
                        @forelse($asistencias as $asistencia)
                        <tr>
                            
                            <td class="align-middle font-weight-bold">
                                {{ \Carbon\Carbon::parse($asistencia->date)->format('d/m/Y') }}
                            </td>
                            
                            
                            <td class="align-middle text-left pl-3 font-weight-bold">
                                {{ $asistencia->emp_nom }} {{ $asistencia->emp_ape_pat }}
                            </td>
                            
                            
                            <td class="align-middle">
                                <span class="badge badge-dark p-2" style="font-size:0.95rem;">{{ $asistencia->codigo ?? 'Sin Asignar' }}</span>
                            </td>
                            
                            
                            <td class="align-middle text-success font-weight-bold" style="font-size: 1.15rem;">
                                {{ $asistencia->tiempo_laborado }}
                            </td>
                            
                            
                            <td class="align-middle text-left pl-3">
                                <div class="font-weight-bold {{ $asistencia->tardanza_texto != '0 min' ? 'text-danger' : 'text-muted' }}" style="font-size: 1.1rem;">
                                    <i class="fas fa-clock"></i> {{ $asistencia->tardanza_texto }}
                                </div>
                                
                                
                                @if(!empty($asistencia->autorizado_por))
                                    <div class="mt-2 p-2 rounded shadow-sm" style="background-color: #fff8e1; border-left: 4px solid #ffc107; font-size: 0.9rem; line-height: 1.3;">
                                        <div class="text-dark font-weight-bold mb-1">
                                            <i class="fas fa-user-shield text-warning"></i> Auth: {{ $asistencia->autorizado_por }}
                                        </div>
                                        <div class="text-dark">
                                            <strong>Motivo:</strong> {{ $asistencia->motivo_tardanza }}
                                        </div>
                                    </div>
                                @endif
                            </td>
                            
                            
                            <td class="align-middle font-weight-bold text-info" style="font-size: 1.1rem;">
                                {{ $asistencia->extra_local }}
                            </td>
                            
                            
                            <td class="align-middle">
                                @if($asistencia->estado_jornada == 'Conforme')
                                    <span class="badge badge-success p-2 shadow-sm" style="font-size: 0.95rem; display: block;">
                                        <i class="fas fa-check-circle"></i> Conforme (8h min)
                                    </span>
                                @else
                                    <span class="badge badge-warning text-dark p-2 shadow-sm" style="font-size: 0.95rem; display: block;">
                                        <i class="fas fa-exclamation-circle"></i> Incompleto
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-muted py-4 font-weight-bold">No se encontraron jornadas registradas para los filtros seleccionados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
        </div>
    </div>
</div>
@endsection