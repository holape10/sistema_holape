@extends('layouts.empresas')

@section('contenido')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2>Módulo Contable: Centralizar Ventas (CPE) y SIRE</h2>
            <p class="text-muted mb-0">Aquí aparecen las últimas ventas listas para integrarse a la contabilidad general.</p>
        </div>

        <div class="card p-3 shadow-sm border-0 bg-light">
            <form action="{{ route('ventas.generarTxtSunat') }}" method="GET" class="form-inline">
                <label class="mr-2">Periodo:</label>
                <select name="mes" class="form-control mr-2">
                    @for($i=1; $i<=12; $i++)
                        <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" {{ date('m') == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                            {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                        </option>
                    @endfor
                </select>
                <select name="anio" class="form-control mr-3">
                    @for($i = date('Y'); $i >= date('Y')-2; $i--)
                        <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
                </select>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-file-alt"></i> Generar .TXT (SIRE)
                </button>
            </form>
        </div>
        
        {{-- NUEVO BOTÓN MASIVO --}}
        <form action="{{ route('ventas.centralizarMasivo') }}" method="POST" onsubmit="return confirm('¿Seguro que desea enviar todos los comprobantes pendientes al Libro Diario?');">
            @csrf
            <button type="submit" class="btn btn-success font-weight-bold shadow-sm">
                <i class="fa fa-bolt"></i> Centralizar Todo lo Pendiente
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-bordered table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Documento</th>
                        <th>Cliente</th>
                        <th>Fecha Emisión</th>
                        <th>Total</th>
                        <th class="text-center">Estado Contable</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ventas as $v)
                    <tr>
                        <td>{{ $v->IdCpe_cabecera }}</td>
                        <td>
                            @if($v->tdocod == '01') 
                                <span class="badge badge-primary">Factura</span>
                            @elseif($v->tdocod == '03') 
                                <span class="badge badge-info">Boleta</span>
                            @elseif($v->tdocod == '07') 
                                <span class="badge badge-warning">N. Crédito</span>
                            @else 
                                <span class="badge badge-secondary">CPE</span>
                            @endif
                            <br>
                            <strong>{{ $v->serdoc }}-{{ $v->numdoc }}</strong>
                        </td>
                        <td>
                            {{ $v->ccanom ?? 'Venta al Portador' }} <br>
                            <small class="text-muted">Doc: {{ $v->ccandi ?? 'S/N' }}</small>
                        </td>
                        <td>{{ date('d/m/Y', strtotime($v->ccafem)) }}</td>
                        <td>
                            <strong>{{ $v->moncod ?? 'PEN' }} {{ number_format($v->ccaitv, 2) }}</strong>
                        </td>
                        <td class="text-center align-middle">
                            
                            {{-- LÓGICA DE VALIDACIÓN VISUAL --}}
                            @if(in_array($v->IdCpe_cabecera, $centralizadas))
                                {{-- Si ya está en el diario, mostramos etiqueta y enlace de revisión --}}
                                <span class="badge badge-success px-3 py-2 mb-1">
                                    <i class="fa fa-check-circle"></i> En Diario
                                </span><br>
                                <a href="{{ route('asientos.index') }}" class="btn btn-sm btn-outline-info" style="font-size: 0.8rem;">
                                    Revisar Asiento
                                </a>
                            @else
                                {{-- Si NO está en el diario, mostramos el botón individual normal --}}
                                <form action="{{ route('ventas.centralizar', $v->IdCpe_cabecera) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-warning font-weight-bold">
                                        <i class="fa fa-sync"></i> Enviar a Diario
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
@endsection