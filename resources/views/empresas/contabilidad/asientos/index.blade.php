@extends('layouts.empresas')

@section('contenido')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Libro Diario - Asientos Contables</h2>
        <div>
            <a href="{{ route('asientos.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Nuevo Manual</a>
        </div>
    </div>
    

    {{-- BARRA DE FILTROS Y REPORTES --}}
    <div class="card mb-3 bg-light">
        <div class="card-body py-2">
            <form action="{{ route('asientos.index') }}" method="GET" class="form-inline d-flex justify-content-between">
                <div class="form-group">
                    <label class="mr-2 font-weight-bold">Periodo:</label>
                    <select name="mes" class="form-control mr-2">
                        @for($i=1; $i<=12; $i++)
                            @php $m = str_pad($i, 2, '0', STR_PAD_LEFT); @endphp
                            <option value="{{ $m }}" {{ $mes == $m ? 'selected' : '' }}>Mes {{ $m }}</option>
                        @endfor
                    </select>
                    <select name="anio" class="form-control mr-3">
                        @for($i = date('Y'); $i >= date('Y')-3; $i--)
                            <option value="{{ $i }}" {{ $anio == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                    <button type="submit" class="btn btn-dark"><i class="fa fa-search"></i> Filtrar</button>
                </div>

                <div class="form-group">
                    <a href="{{ route('asientos.excel', ['mes'=>$mes, 'anio'=>$anio]) }}" class="btn btn-success mr-2">
                        <i class="fa fa-file-excel"></i> Exportar Oficial (Excel)
                    </a>
                    <a href="{{ route('asientos.pdf', ['mes'=>$mes, 'anio'=>$anio]) }}" target="_blank" class="btn btn-danger">
                        <i class="fa fa-file-pdf"></i> Imprimir / PDF
                    </a>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-bordered table-hover mb-0">
                <thead class="bg-dark text-white">
                    <tr>
                        <th style="width: 100px;">Fecha</th>
                        <th style="width: 120px;">Tipo Asiento</th>
                        <th>Glosa / Descripción</th>
                        <th style="width: 150px;" class="text-right">Total Debe</th>
                        <th style="width: 150px;" class="text-right">Total Haber</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($asientos as $asiento)
                        <tr class="table-secondary">
                            <td><strong>{{ date('d/m/Y', strtotime($asiento->fecha)) }}</strong></td>
                            <td><span class="badge badge-primary">{{ strtoupper($asiento->tipo_asiento) }}</span></td>
                            <td><strong>{{ $asiento->glosa }}</strong></td>
                            <td class="text-right font-weight-bold">{{ number_format($asiento->detalles->sum('debe'), 2) }}</td>
                            <td class="text-right font-weight-bold">{{ number_format($asiento->detalles->sum('haber'), 2) }}</td>
                        </tr>
                        {{-- Detalles de cada asiento --}}
                        @foreach($asiento->detalles as $detalle)
                            <tr>
                                <td colspan="2" class="text-right text-muted" style="font-size: 0.9em;">
                                    {{ $detalle->cuenta->codigo }}
                                </td>
                                <td class="text-muted" style="font-size: 0.9em; padding-left: 20px;">
                                    {{ $detalle->cuenta->nombre }}
                                </td>
                                <td class="text-right text-info">{{ $detalle->debe > 0 ? number_format($detalle->debe, 2) : '-' }}</td>
                                <td class="text-right text-success">{{ $detalle->haber > 0 ? number_format($detalle->haber, 2) : '-' }}</td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No se han registrado asientos en este periodo.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Paginación automática de Laravel --}}
    <div class="mt-3 d-flex justify-content-center">
        {{ $asientos->links() }}
    </div>
</div>
@endsection