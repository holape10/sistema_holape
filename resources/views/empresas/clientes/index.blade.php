@extends('layouts.empresas')
@section('contenido')

<style>
    .table-vcenter td { vertical-align: middle !important; }
    .badge-status { padding: 5px 10px; border-radius: 4px; font-size: 11px; text-transform: uppercase; font-weight: bold; }
</style>

<section class="content-header">
    <h1>
        Gestión de Clientes        
    </h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Filtros de búsqueda</h3>
                    <div class="box-tools pull-right">
                        <button type="button" id="exportarclientes" class="btn btn-default btn-sm">
                            <i class="fa fa-file-excel-o text-success"></i> Exportar Excel
                        </button>
                        <a href="clientes/create" class="btn btn-success btn-sm">
                            <i class="fa fa-plus"></i> Nuevo Cliente
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            @include('empresas.clientes.search')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-body table-responsive no-padding">
                    <table id="table" class="table table-hover table-vcenter">
                        <thead class="bg-gray">
                            <tr style="font-size: 13px;">
                                <th>Documento</th>
                                <th>Razón Social / Nombre</th>
                                <th>Cta. Contable</th>
                                <th>Contacto</th>
                                <th>Cumpleaños</th>
                                <th>Correo</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Opciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($clientes as $cli)
                            <tr>
                                <td style="font-weight: bold;">
                                    <small class="text-muted">{{ $cli->tdides ?? 'DNI' }}:</small><br>
                                    {{$cli->clinum}}
                                </td>
                                <td>{{$cli->clinom}}</td>
                                <td>{{$cli->cuenta12}}</td>
                                <td>
                                    @if($cli->telefono)
                                        <a href="https://wa.me/51{{$cli->telefono}}" target="_blank" class="text-success">
                                            <i class="fa fa-whatsapp"></i> {{$cli->telefono}}
                                        </a>
                                    @else
                                        <span class="text-muted">---</span>
                                    @endif
                                </td>
                                <td>
                                    @if($cli->fecha_nacimiento)
                                        <i class="fa fa-birthday-cake text-danger"></i> 
                                        {{ date('d/m/Y', strtotime($cli->fecha_nacimiento)) }}
                                    @else
                                        <span class="text-muted">No reg.</span>
                                    @endif
                                </td>
                                <td><small>{{$cli->clicor}}</small></td>
                                <td class="text-center">
                                    @if($cli->cliest == 'Activo')
                                        <span class="label label-success badge-status">Activo</span>
                                    @else
                                        <span class="label label-danger badge-status">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="{{URL::action('ClientesController@edit',$cli->clicod)}}" class="btn btn-info btn-xs" title="Editar">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <a href="" data-target="#modal-delete-{{$cli->clicod}}" data-toggle="modal" class="btn btn-danger btn-xs" title="Eliminar">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @include('empresas.clientes.modal')
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="box-footer clearfix">
                    <!-- Muestra los enlaces de paginación -->
                    <div class="pull-right">
                        {{ $clientes->links() }}
                    </div>
                </div>
            </div>
            </div>
    </div>
</section>

<script>
$(document).ready(function() {
    $("#exportarclientes").click(function(e){
        window.location.href = "/exportarclientes";
    });
});
</script>

@endsection