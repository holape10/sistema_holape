@extends('layouts.empresas')

@section('contenido')
<div class="container-fluid mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-clipboard-list"></i> Reporte de Asistencia</h4>
        </div>
        <div class="card-body">
            
            <form action="{{ route('asistencia.reporte') }}" method="GET" class="mb-4">
                <div class="row align-items-end">
                    <div class="col-md-2 col-sm-6 mb-2">
                        <label class="font-weight-bold">Desde:</label>
                        <input type="date" name="fecha_inicio" class="form-control" value="{{ $fecha_inicio }}">
                    </div>
                    <div class="col-md-2 col-sm-6 mb-2">
                        <label class="font-weight-bold">Hasta:</label>
                        <input type="date" name="fecha_fin" class="form-control" value="{{ $fecha_fin }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="font-weight-bold">Empleado:</label>
                        <select name="emp_id" class="form-control">
                            <option value="todos">Todos los empleados</option>
                            @foreach($empleados as $emp)
                                <option value="{{ $emp->emp_id }}" {{ $emp_id == $emp->emp_id ? 'selected' : '' }}>
                                    {{ $emp->emp_nom }} {{ $emp->emp_ape_pat }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5 mb-2">
                        <button type="submit" name="action" value="ver" class="btn btn-primary font-weight-bold shadow-sm"><i class="fa fa-search"></i> Filtrar</button>
                        <button type="submit" name="action" value="excel" class="btn btn-success font-weight-bold shadow-sm"><i class="fa fa-file-excel-o"></i> Excel</button>
                        <button type="submit" name="action" value="ticket" formtarget="_blank" class="btn btn-dark font-weight-bold shadow-sm"><i class="fa fa-print"></i> Ticket</button>
                    </div>
                </div>
            </form>
            <hr>

            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>DNI</th>
                            <th>Empleado</th>
                            <th>Hora Entrada</th>
                            <th>Hora Salida</th>
                            <th>Tardanza / Justificación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($asistencias as $asistencia)
                        <tr>
                            <td class="align-middle font-weight-bold">{{ \Carbon\Carbon::parse($asistencia->date)->format('d/m/Y') }}</td>
                            <td class="align-middle">{{ $asistencia->emp_num_doc }}</td>
                            <td class="align-middle text-left pl-3">{{ $asistencia->emp_nom }} {{ $asistencia->emp_ape_pat }}</td>
                            
                            <!-- HORA DE ENTRADA -->
                            <td class="align-middle text-success font-weight-bold" style="font-size: 1.1rem;">
                                {{ $asistencia->check_in_1 ? \Carbon\Carbon::parse($asistencia->check_in_1)->format('H:i:s') : '--' }}
                            </td>
                            
                            <!-- HORA DE SALIDA -->
                            <td class="align-middle text-danger font-weight-bold" style="font-size: 1.1rem;">
                                {{ $asistencia->check_out_1 ? \Carbon\Carbon::parse($asistencia->check_out_1)->format('H:i:s') : 'Sin marcar' }}
                            </td>
                            
                            <!-- TARDANZA Y AUTORIZACIÓN -->
                            <td class="align-middle">
                                @if($asistencia->tardanza_minutos > 0)
                                    <!-- Si hay autorización, la tardanza se muestra en ámbar (justificada), si no, en rojo (injustificada) -->
                                    <span class="badge {{ !empty($asistencia->autorizado_por) ? 'badge-warning text-dark' : 'badge-danger' }} p-2 shadow-sm" style="font-size: 0.9rem;">
                                        <i class="fas fa-clock"></i> {{ $asistencia->tardanza_minutos }} min
                                    </span>
                                    
                                    <!-- Credencial de autorización -->
                                    @if(!empty($asistencia->autorizado_por))
                                        <br>
                                        <span class="badge badge-info text-white mt-1 shadow-sm" 
                                              style="font-size: 0.8rem; cursor: help; padding: 4px 8px;" 
                                              data-toggle="tooltip" 
                                              data-placement="left" 
                                              title="{{ $asistencia->motivo_tardanza }}">
                                            <i class="fas fa-user-check"></i> {{ $asistencia->autorizado_por }}
                                        </span>
                                    @endif
                                @else
                                    <span class="badge badge-success p-2 shadow-sm" style="font-size: 0.9rem;">
                                        <i class="fas fa-check-circle"></i> A tiempo
                                    </span>
                                    <!-- Por si el administrador autorizó un ingreso bloqueado que no generó minutos de tardanza -->
                                    @if(!empty($asistencia->autorizado_por))
                                        <br>
                                        <span class="badge badge-info text-white mt-1 shadow-sm" 
                                              style="font-size: 0.8rem; cursor: help; padding: 4px 8px;" 
                                              data-toggle="tooltip" 
                                              data-placement="left" 
                                              title="{{ $asistencia->motivo_tardanza }}">
                                            <i class="fas fa-user-check"></i> {{ $asistencia->autorizado_por }}
                                        </span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-muted py-4">No hay registros de asistencia para los filtros seleccionados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
        </div>
    </div>
</div>

<!-- Script para activar los Tooltips flotantes de Bootstrap -->
<script>
    $(document).ready(function(){
        $('[data-toggle="tooltip"]').tooltip();   
    });
</script>
@endsection