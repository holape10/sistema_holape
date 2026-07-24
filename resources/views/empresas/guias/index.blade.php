@extends('layouts.empresas')

@section('contenido')
<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2 class="text-dark">Guías de Remisión</h2>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('guias.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus"></i> Emitir Nueva Guía
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
                            <th>Fecha</th>
                            <th>Comprobante</th>
                            <th>Cliente / Destinatario</th>
                            <th>Punto de Llegada</th>
                            <th>Peso Total</th>
                            <th>Estado SUNAT</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($guias as $guia)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($guia->fechaemision)->format('d/m/Y') }}</td>
                            <td class="font-weight-bold text-primary">
                                {{ $guia->serieguia }}-{{ str_pad($guia->numeroguia, 8, '0', STR_PAD_LEFT) }}
                            </td>
                            <td>
                                {{ $guia->nomcliente }}<br>
                                <small class="text-muted">RUC: {{ $guia->ruccliente }}</small>
                            </td>
                            <td>{{ $guia->direccionllegada }}</td>
                            <td>{{ $guia->pesobruto }} KGM</td>
                            <td>
                                @if($guia->ccasunrescod === '0')
                                    <span class="badge badge-success px-2 py-1"><i class="fas fa-check"></i> Aceptado</span>
                                @elseif(!empty($guia->error) || $guia->ccasunrescod !== null)
                                    <span class="badge badge-danger px-2 py-1" title="{{ $guia->error }}"><i class="fas fa-times"></i> Rechazado</span>
                                @else
                                    <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-clock"></i> Pendiente</span>
                                @endif
                                
                                @if(!empty($guia->ccasunnot))
                                    <br><small class="text-muted">{{ $guia->ccasunnot }}</small>
                                @endif
                            </td>
                            
                            <td class="text-center">
                                <a href="{{ route('guias.pdf', $guia->IdCpe_guia) }}" target="_blank" class="btn btn-sm btn-outline-danger" title="Ver PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>

                                @if($guia->ccasunrescod !== '0')
                                    <form action="{{ route('guias.reenviar', $guia->IdCpe_guia) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary" title="Reenviar a SUNAT">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </form>
                                @endif
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