@extends('layouts.empresas')
@section('contenido')

<style>
/* Estilos modernos para la tabla */
.modern-table {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}
.modern-table thead th {
    background-color: #3f51b5; /* Un azul primario */
    color: white;
    font-weight: 600;
}
.modern-table tbody tr:nth-child(even) {
    background-color: #f8f9fa; /* Ligeramente gris para filas pares */
}
.modern-table tbody tr:hover {
    background-color: #e9ecef;
    cursor: default;
}
.table-responsive {
    margin-top: 20px;
}
</style>

<div class="row">
    <div class="col-lg-12">
        <h2 class="mb-2">{{ $title ?? 'Documentos de Venta' }}</h2>
        <p class="text-muted">Lista de Documentos del {{ \Carbon\Carbon::parse($fec_ini)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fec_fin)->format('d/m/Y') }}.</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table table-hover table-striped modern-table">
                <thead>
                    <tr>
                        <th style="width: 15%;">Fecha</th>
                        <th style="width: 20%;">Documento</th>
                        <th style="width: 50%;">Cliente </th>
                        <th class="text-right" style="width: 15%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documents as $doc)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($doc->ccafem)->format('d/m/Y') }}</td>
                            <td>{{ $doc->tdocod }} - {{ $doc->serie_numero }}</td>
                            <td>{{ $doc->cliente_completo }}</td>
                            <td class="text-right">S/ {{ number_format($doc->ccaitv, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <i class="fa fa-info-circle"></i> No se encontraron documentos para este filtro en el período seleccionado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                <tfoot>
                    <tr style="background-color: #f1f1f1; font-weight: bold; border-top: 2px solid #333;">
                        <td colspan="3" class="text-right">TOTAL SUMA:</td>
                        <td class="text-right">
                            S/ {{ number_format($total_suma ?? 0, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-lg-12">
        {{-- Enlace para volver al dashboard, usa 'dashboard.index' que es tu ruta de recurso --}}
        <a href="{{ route('dashboard.index', ['fec_ini' => $fec_ini, 'fec_fin' => $fec_fin]) }}" class="btn btn-primary">
            <i class="fa fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
</div>

@endsection