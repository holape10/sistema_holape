@extends('layouts.empresas')

@section('contenido')
<div class="container-fluid mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-calendar-alt"></i> Tareo de Asistencia - {{ $empresa->NomEmpresa ?? 'Empresa' }}</h4>
            <small class="font-weight-bold">RUC: {{ $empresa->IdEmpresa ?? '' }}</small>
        </div>
        <div class="card-body">
            
            <form action="{{ route('asistencia.tareo') }}" method="GET" class="mb-4">
                <div class="row align-items-end">
                    <div class="col-md-2 mb-2">
                        <label class="font-weight-bold text-dark">Desde:</label>
                        <input type="date" name="fecha_inicio" class="form-control" value="{{ $fecha_inicio }}">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="font-weight-bold text-dark">Hasta:</label>
                        <input type="date" name="fecha_fin" class="form-control" value="{{ $fecha_fin }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="font-weight-bold text-dark">Empleado:</label>
                        <select name="emp_id" class="form-control">
                            <option value="todos">Todos los colaboradores</option>
                            @foreach($todos_empleados as $emp)
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

            <div class="mb-3 p-3 bg-light border rounded">
                <h6 class="font-weight-bold text-secondary mb-2">Leyenda de Asistencia:</h6>
                @php
                    $leyendas = \MasterSoft\Attendance::getLeyendas();
                @endphp
                
                @foreach($leyendas as $key => $item)
                    <span class="d-inline-block px-3 py-1 mr-2 mb-2 border rounded shadow-sm" style="background-color: {{ $item['bg'] }}; color: {{ $item['color'] }}; font-weight: bold; font-size: 0.9rem;">
                        {{ $item['texto'] }} : {{ $item['label'] }}
                    </span>
                @endforeach
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm text-center table-hover" style="white-space: nowrap; font-size: 0.85rem;">
                    <thead class="thead-dark">
                        <tr>
                            <th rowspan="2" class="align-middle">Nro.</th>
                            <th rowspan="2" class="align-middle text-left" style="min-width: 250px;">Nombres y Apellidos</th>
                            <th rowspan="2" class="align-middle">D.N.I.</th>
                            
                            @foreach($fechas as $f)
                                <th style="width: 35px;" class="{{ $f['dia_letra'] == 'D' ? 'text-warning' : '' }}">{{ $f['dia_letra'] }}</th>
                            @endforeach
                            
                            <th colspan="{{ count($leyendas) }}" class="bg-secondary text-white border-left">RESUMEN</th>
                        </tr>
                        <tr>
                            @foreach($fechas as $f)
                                <th style="width: 35px;" class="{{ $f['dia_letra'] == 'D' ? 'text-warning' : '' }}">{{ $f['dia_numero'] }}</th>
                            @endforeach
                            
                            @foreach($leyendas as $key => $item)
                                <th class="border-left" style="background-color: {{ $item['bg'] }}; color: {{ $item['color'] }};" title="{{ $item['label'] }}">
                                    {{ $item['texto'] }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($matriz as $index => $row)
                        <tr>
                            <td class="font-weight-bold align-middle">{{ $index + 1 }}</td>
                            <td class="text-left font-weight-bold align-middle">{{ $row['empleado']->emp_nom }} {{ $row['empleado']->emp_ape_pat }}</td>
                            <td class="align-middle">{{ $row['empleado']->emp_num_doc }}</td>
                            
                            @foreach($fechas as $f)
                                @php 
                                    $letra = $row['dias'][$f['fecha_sql']];
                                    $info = $leyendas[$letra] ?? null;
                                    
                                    $colorFondo = $info ? $info['bg'] : 'transparent';
                                    $colorTexto = $info ? $info['color'] : '#333';
                                    $textoMostrar = $info ? $info['texto'] : $letra;
                                @endphp
                                <td class="align-middle" style="background-color: {{ $colorFondo }} !important; color: {{ $colorTexto }} !important; font-weight: bold; border: 1px solid #dee2e6;">
                                    {{ $textoMostrar }}
                                </td>
                            @endforeach
                            
                            @foreach($leyendas as $key => $item)
                                <td class="align-middle font-weight-bold border-left" style="color: {{ $item['bg'] }}; font-size: 1rem; background-color: #f8f9fa;">
                                    {{ $row['totales'][$key] ?? 0 }}
                                </td>
                            @endforeach
                        </tr>
                        @empty
                        <tr>
                            <td colspan="100%" class="text-muted py-4 font-weight-bold">No hay registros para este rango de fechas.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
        </div>
    </div>
</div>
@endsection