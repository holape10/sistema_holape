@extends('layouts.empresas')

@section('contenido')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Diario de Consultas Médicas</h6>
            <a href="{{ route('consultorio.create') }}" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> Nueva Consulta
            </a>
        </div>
        <div class="card-body">
            
            <form action="{{ route('consultorio.index') }}" method="GET" class="mb-4">
                <div class="input-group">
                    <input type="text" name="buscar" class="form-control" placeholder="Buscar consulta por nombre o DNI del paciente..." value="{{ $buscar ?? '' }}">
                    <div class="input-group-append">
                        <button class="btn btn-secondary" type="submit"><i class="fa fa-search"></i> Buscar</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Paciente</th>
                            <th>DNI/RUC</th>
                            <th>Motivo</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($consultas as $consulta)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($consulta->fecha_consulta)->format('d/m/Y H:i') }}</td>
                                <td>{{ $consulta->paciente->clinom ?? 'Desconocido' }}</td>
                                <td>{{ $consulta->paciente->clinum ?? '-' }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($consulta->motivo_consulta, 40) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('consultorio.show', $consulta->clicod) }}" class="btn btn-info btn-sm">
                                        <i class="fa fa-folder-open"></i> Historial
                                    </a>
                                    <a href="{{ route('consultorio.reporte', $consulta->id) }}" class="btn btn-success btn-sm" target="_blank">
                                        <i class="fa fa-print"></i> Imprimir
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $consultas->appends(['buscar' => $buscar])->links() }}
            </div>
        </div>
    </div>
</div>
@endsection


@if(session('abrir_reporte_id'))
<script>
    // Abre el reporte en una pestaña nueva automáticamente
    window.open("{{ route('consultorio.reporte', session('abrir_reporte_id')) }}", '_blank');
</script>
@endif