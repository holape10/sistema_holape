@extends('layouts.empresas')
@section('contenido')

<style>
    .shadow-box { box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-radius: 5px; }
    .table-vert-align td { vertical-align: middle !important; }
    .btn-export { background-color: #27ae60; color: white; transition: all 0.3s; }
    .btn-export:hover { background-color: #219150; color: white; transform: translateY(-1px); }
</style>

<script>
    $(document).ready(function() {
        $("#exportarempleados").click(function(e){
            window.location.href = "/exportarempleados";
        });
    });
</script>

<section class="content">
    <!-- CABECERA Y ACCIONES -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary shadow-box">
                <div class="box-header with-border" style="background: #2c3e50; color: white;">
                    <h3 class="box-title"><i class="fa fa-users"></i> <strong>REGISTRO DE EMPLEADOS</strong></h3>
                    <div class="box-tools pull-right">
                        <!--<button id="exportarempleados" class="btn btn-sm btn-flat btn-export shadow">
                            <i class="fa fa-file-excel-o"></i> EXPORTAR EXCEL
                        </button>-->
                        <a href="{{url('empleado/create')}}" class="btn btn-success btn-sm btn-flat shadow">
                            <i class="fa fa-plus"></i> NUEVO EMPLEADO
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            @include('empresas.empleado.search')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- LISTADO DE EMPLEADOS -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-solid shadow-box">
                <div class="box-body table-responsive no-padding">
                    <table id="table" class="table table-hover table-striped table-bordered table-vert-align mb-0">
                        <thead class="bg-gray-light">
                            <tr>
                                <th class="text-center" hidden="hidden">DNI</th>
                                <th class="text-center" style="width: 100px;">ROL</th>
                                <th>USUARIO / EMAIL</th>
                                <th class="text-center">CÓD. MÓVIL</th>
                                <th>NOMBRE COMPLETO</th>
                                <th hidden="hidden">AP. PATERNO</th>
                                <th hidden="hidden">AP. MATERNO</th>
                                <th>DIRECCIÓN</th>
                                <th hidden="hidden">TELÉFONO</th>
                                <th class="text-center">CELULAR</th>
                                <th class="text-center">ASISTENCIA</th>
                                <th class="text-center" style="width: 120px;">ESTADO</th>
                                <th class="text-center" style="width: 150px;">OPCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($empleados as $emp)
                            <tr>
                                <td hidden="hidden">{{$emp->emp_num_doc}}</td>
                                <td class="text-center">
                                    @if(!empty($emp->rol_nombre))
                                        @php
                                            $claseRol = [
                                                'Mozo' => 'info',
                                                'Administrador' => 'danger',
                                                'Cajero' => 'warning'
                                            ][$emp->rol_nombre] ?? 'primary';
                                        @endphp
                                        <span class="label label-{{$claseRol}} shadow-sm">{{$emp->rol_nombre}}</span>
                                    @else
                                        <span class="label label-default">Sin Rol</span>
                                    @endif
                                </td>
                                <td><i class="fa fa-envelope-o text-muted"></i> {{$emp->email}}</td>
                                <td class="text-center">
                                    <span class="badge bg-gray">{{$emp->codigo_movil}}</span>
                                </td>
                                <td><strong>{{$emp->emp_nom}} {{$emp->emp_ape_pat}} {{$emp->emp_ape_mat}}</strong></td>
                                <td hidden="hidden">{{$emp->emp_ape_pat}}</td>
                                <td hidden="hidden">{{$emp->emp_ape_mat}}</td>
                                <td><small class="text-muted">{{$emp->emp_dir}}</small></td>
                                <td hidden="hidden">{{$emp->emp_tel}}</td>                                
                                <td class="text-center"><i class="fa fa-mobile text-success"></i> {{$emp->emp_cel}}</td>
                                <td class="text-center">
                                    @if($emp->asistencia == 1)
                                        <span class="label label-success shadow-sm" style="padding: 5px 10px; display: block;">SI</span>
                                    @else
                                        <span class="label label-danger shadow-sm" style="padding: 5px 10px; display: block;">NO</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="label shadow-sm" style="background-color:{{$emp->est_color}}; color:white; padding: 5px 10px; display: block;">
                                        {{$emp->est_des}}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="{{URL::action('EmpleadoController@edit',$emp->emp_id)}}" 
                                           class="btn btn-xs btn-info btn-flat" title="Editar">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <a href="" data-target="#modal-delete-{{$emp->emp_id}}" 
                                           data-toggle="modal" class="btn btn-xs btn-danger btn-flat" title="Eliminar">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @include('empresas.empleado.modal')
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- PAGINACIÓN (Si la tienes implementada) -->
                @if(method_exists($empleados, 'render'))
                <div class="box-footer clearfix">
                    <div class="pull-right">
                        {{$empleados->render()}}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

@endsection