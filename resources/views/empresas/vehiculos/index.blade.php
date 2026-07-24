@extends('layouts.empresas')

@section('contenido')
<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2 class="text-dark">Gestión de Flota</h2>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('vehiculos.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus"></i> Nuevo Vehículo
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
                            <th>Placa / Año</th>
                            <th>Marca / Modelo</th>
                            <th>SOAT</th>
                            <th>Rev. Técnica</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vehiculos as $vehiculo)
                        <tr>
                            <td>
                                <span class="font-weight-bold">{{ $vehiculo->placa }}</span><br>
                                <small class="text-muted">Año: {{ $vehiculo->anio }}</small>
                            </td>
                            <td>{{ $vehiculo->marca }} {{ $vehiculo->modelo }}<br><small>{{ $vehiculo->capacidad_carga }} Kg</small></td>
                            
                            <td>
                                <div>{{ \Carbon\Carbon::parse($vehiculo->fecha_vencimiento_soat)->format('d/m/Y') }}</div>
                                <span class="badge" style="background-color: {{ $vehiculo->color_soat == 'success' ? '#28a745' : ($vehiculo->color_soat == 'warning' ? '#ffc107' : '#dc3545') }}; color: {{ $vehiculo->color_soat == 'warning' ? '#212529' : '#fff' }}; font-size: 0.85em; padding: 5px 8px;">
                                    {{ $vehiculo->texto_soat }}
                                </span>
                            </td>
                            
                            <td>
                                <div>{{ \Carbon\Carbon::parse($vehiculo->fecha_vencimiento_rt)->format('d/m/Y') }}</div>
                                <span class="badge" style="background-color: {{ $vehiculo->color_rt == 'success' ? '#28a745' : ($vehiculo->color_rt == 'warning' ? '#ffc107' : '#dc3545') }}; color: {{ $vehiculo->color_rt == 'warning' ? '#212529' : '#fff' }}; font-size: 0.85em; padding: 5px 8px;">
                                    {{ $vehiculo->texto_rt }}
                                </span>
                            </td>

                            <td>
                                @if($vehiculo->estado == 'activo')
                                    <span class="badge" style="background-color: #28a745; color: white;">Activo</span>
                                @elseif($vehiculo->estado == 'mantenimiento')
                                    <span class="badge" style="background-color: #ffc107; color: black;">Mantenimiento</span>
                                @else
                                    <span class="badge" style="background-color: #dc3545; color: white;">Inactivo</span>
                                @endif
                            </td>
                            
                            <td class="text-center">
                                <a href="{{ route('mantenimientos.create', $vehiculo->id) }}" class="btn btn-sm btn-outline-success mb-1" title="Registrar Mantenimiento">
                                    <i class="fas fa-tools"></i> Mant.
                                </a>
                                <br>
                                <form action="{{ route('vehiculos.destroy', $vehiculo->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de anular este vehículo?');">
                                    <a href="{{ route('vehiculos.edit', $vehiculo->id) }}" class="btn btn-sm btn-outline-info" title="Editar">
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