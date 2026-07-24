@extends('layouts.empresas')

@section('contenido')
<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2 class="text-dark">Control de Viajes</h2>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('viajes.create') }}" class="btn btn-success shadow-sm">
                <i class="fas fa-truck-loading"></i> Programar Nuevo Viaje
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle">
                    <thead class="thead-dark">
                        <tr>
                            <th>Fecha Salida</th>
                            <th>Vehículo (Placa)</th>
                            <th>Ruta (Origen - Destino)</th>
                            <th>Costo Est.</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($viajes as $viaje)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($viaje->fecha_salida)->format('d/m/Y H:i') }}</td>
                            <td class="font-weight-bold text-primary">{{ $viaje->vehiculo->placa ?? 'N/A' }}</td>
                            <td>{{ $viaje->origen }} <i class="fas fa-arrow-right text-muted mx-1"></i> {{ $viaje->destino }}</td>
                            <td>S/ {{ number_format($viaje->costo_estimado, 2) }}</td>
                            <td>
                                @if($viaje->estado == 'pendiente')
                                    <span class="badge badge-secondary px-2 py-1">Pendiente</span>
                                @elseif($viaje->estado == 'en_ruta')
                                    <span class="badge badge-primary px-2 py-1">En Ruta</span>
                                @elseif($viaje->estado == 'completado')
                                    <span class="badge badge-success px-2 py-1">Completado</span>
                                @else
                                    <span class="badge badge-danger px-2 py-1">Cancelado</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <form action="{{ route('viajes.destroy', $viaje->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de anular este viaje?');">
                                    <a href="{{ route('viajes.edit', $viaje->id) }}" class="btn btn-sm btn-outline-info" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection