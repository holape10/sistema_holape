@extends('layouts.empresas')

@section('contenido')
<div class="container-fluid">
    
    <!-- Botón de regreso -->
    <div class="mb-3">
        <a href="{{ route('consultorio.index') }}" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Volver al listado
        </a>
    </div>

    <div class="row">
        <!-- Columna Izquierda: Datos del Paciente -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold"><i class="fa fa-user"></i> Ficha del Paciente</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img class="img-profile rounded-circle mb-2" src="https://ui-avatars.com/api/?name={{ urlencode($paciente->clinom) }}&background=random&size=100" alt="Avatar">
                        <h5 class="font-weight-bold">{{ $paciente->clinom }}</h5>
                        <span class="badge badge-success">Paciente Activo</span>
                    </div>
                    <hr>
                    <p><strong>Documento:</strong> {{ $paciente->clinum }}</p>
                    <p><strong>Teléfono:</strong> {{ $paciente->clicontel ?? 'No registrado' }}</p>
                    <p><strong>Dirección:</strong> {{ $paciente->clidir ?? 'No registrado' }}</p>
                    <p><strong>Total de Visitas:</strong> {{ $historial->count() }}</p>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Historial Médico Cronológico -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fa fa-stethoscope"></i> Historial de Consultas</h6>
                    <!-- Botón rápido por si el paciente está en el consultorio AHORA y se le quiere crear una consulta -->
                    <a href="{{ route('consultorio.create') }}" class="btn btn-sm btn-outline-primary">Nueva Consulta</a>
                </div>
                <div class="card-body">
                    
                    @if($historial->isEmpty())
                        <div class="alert alert-warning">Este paciente no tiene consultas registradas.</div>
                    @else
                        <!-- Iteramos sobre cada consulta -->
                        @foreach($historial as $index => $item)
                            <div class="card mb-3 border-left-info shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-dark">
                                        Consulta #{{ $historial->count() - $index }}
                                    </h6>
                                    <span class="text-muted small">
                                        <i class="fa fa-calendar-alt"></i> {{ \Carbon\Carbon::parse($item->fecha_consulta)->format('d/m/Y h:i A') }}
                                        ({{ \Carbon\Carbon::parse($item->fecha_consulta)->diffForHumans() }})
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <h6 class="font-weight-bold text-secondary">Motivo:</h6>
                                            <p class="text-dark">{{ $item->motivo_consulta }}</p>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <h6 class="font-weight-bold text-secondary">Exploración Física:</h6>
                                            <p class="text-dark">{{ $item->exploracion_fisica ?? 'No registrada' }}</p>
                                        </div>
                                    </div>
                                    <hr class="mt-0 mb-2">
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <h6 class="font-weight-bold text-danger">Diagnóstico:</h6>
                                            <p class="text-dark">{{ $item->diagnostico ?? 'No registrado' }}</p>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <h6 class="font-weight-bold text-success">Tratamiento / Receta:</h6>
                                            <p class="text-dark">{{ $item->tratamiento ?? 'Ninguno' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection