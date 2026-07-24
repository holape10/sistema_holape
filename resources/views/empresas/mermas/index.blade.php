@extends('layouts.empresas')
@section('contenido')
<div class="container-fluid">
    <div class="row" style="margin-bottom: 10px;">
        <div class="col-lg-12">
            <a href="/mermas/crear" class="btn btn-success"><i class="fa fa-plus"></i> Nueva Merma</a>
            <a href="/mermas/reporte-diario/pdf" target="_blank" class="btn btn-danger"><i class="fa fa-file-pdf-o"></i> PDF del Día</a>
            <a href="/mermas/reporte-diario/excel" class="btn btn-success" style="background-color: #217346; border-color: #1e6b41;"><i class="fa fa-file-excel-o"></i> Excel del Día</a>
            <a href="/motivos-merma" class="btn btn-warning" style="margin-left: 15px;"><i class="fa fa-tags"></i> Motivos de Merma</a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-12">
            @if(session()->has('success'))
            <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>
            @endif
            
            <div class="box box-primary">
                <div class="box-body table-responsive">
                    <table class="table table-hover table-bordered table-striped">
                        <thead style="background-color: #3c8dbc; color: white;">
                            <tr>
                                <th>Fecha</th>
                                <th>Producto</th>
                                <th>Ingresado</th>
                                <th>Descontado (Base)</th>
                                <th>Motivo</th>
                                <th>Pérdida (S/)</th>
                                <th>Opciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mermas as $m)
                            <tr>
                                <td>{{ date('d/m/Y H:i', strtotime($m->fecha_registro)) }}</td>
                                <td>{{ $m->pronom }}</td>
                                <td>{{ $m->cantidad }} <small>({{ $m->tipo_unidad }})</small></td>
                                <td><strong>{{ $m->cantidad_kardex }}</strong></td>
                                <td><span class="label label-danger">{{ $m->motivo }}</span></td>
                                <td>S/ {{ number_format($m->costo_total, 2) }}</td>
                                <td>
                                    <a href="/mermas/ticket/{{ $m->id }}" target="_blank" class="btn btn-xs btn-default" title="Imprimir Ticket"><i class="fa fa-print"></i></a>
                                    <a href="/mermas/eliminar/{{ $m->id }}" onclick="return confirm('¿Seguro que deseas anular esta merma y restaurar el stock?');" class="btn btn-xs btn-danger" title="Eliminar"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $mermas->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPT PARA ABRIR EL TICKET EN UNA PESTAÑA NUEVA -->
@if(session()->has('imprimir_ticket'))
<script>
    $(document).ready(function() {
        window.open('/mermas/ticket/{{ session('imprimir_ticket') }}', '_blank');
    });
</script>
@endif
@endsection