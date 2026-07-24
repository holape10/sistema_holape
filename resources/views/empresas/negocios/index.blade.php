@extends('layouts.empresas')
@section('contenido')

<section class="content">
    <!-- CABECERA Y BÚSQUEDA -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary shadow">
                <div class="box-header with-border" style="background: #2c3e50; color: white;">
                    <h3 class="box-title"><i class="fa fa-building"></i> <strong>REGISTRO DE SUCURSALES</strong></h3>
                    <div class="box-tools pull-right">
                        <a href="/negocios/create" class="btn btn-success btn-sm btn-flat shadow">
                            <i class="fa fa-plus"></i> <strong>NUEVA SUCURSAL</strong>
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-lg-6 col-md-8 col-sm-10 col-xs-12">
                            @include('empresas.negocios.search')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA DE RESULTADOS -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-solid shadow">
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover table-striped table-bordered mb-0">
                        <thead class="bg-gray-light">
                            <tr>
                                <th class="text-center" style="width: 120px;">RUC</th>
                                <th class="text-center">TIPO NEGOCIO</th>
                                <th>NOMBRE COMERCIAL</th>
                                <th>DIRECCIÓN</th>
                                <th>CORREO</th>
                                <th class="text-center">TELÉFONO</th>
                                <th class="text-center">LOGO</th>
                                <th class="text-center">WEB</th>
                                <th class="text-center" style="width: 100px;">ESTADO</th>
                                <th class="text-center" style="width: 150px;">OPCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($negocios as $negocio)
                            <tr>
                                <td class="text-center">{{$negocio->IdEmpresa}}</td>
                                <td hidden="hidden">{{$negocio->cod_suc}}</td>
                                <td class="text-center">{{$negocio->tipo_negocio}}</td>
                                <td><strong>{{$negocio->nombre_comercial}}</strong></td>
                                <td><small>{{$negocio->direccion}}</small></td>
                                <td>{{$negocio->correo}}</td>
                                <td class="text-center">{{$negocio->telefono}}</td>
                                <td class="text-center">
                                    {{-- Validamos que el campo no esté vacío y que el archivo exista en public/ --}}
                                    @if(!empty($negocio->logosuc) && file_exists(public_path($negocio->logosuc)))
                                        <img src="{{ asset($negocio->logosuc) }}" 
                                             alt="Logo {{ $negocio->nombre_comercial }}" 
                                             style="height: 50px; width: 50px; object-fit: contain;" 
                                             class="img-thumbnail shadow-sm">
                                    @else
                                        {{-- Icono de respaldo si no hay imagen o no se encuentra --}}
                                        <div class="img-thumbnail bg-gray shadow-sm" 
                                             style="height: 50px; width: 50px; display: flex; align-items: center; justify-content: center; margin: auto;">
                                            <i class="fa fa-home text-muted" style="font-size: 20px;" title="Sin Logo"></i>
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($negocio->web)
                                        <a href="{{$negocio->web}}" target="_blank" class="btn btn-xs btn-default" title="Visitar Web">
                                            <i class="fa fa-globe text-primary"></i>
                                        </a>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(strtolower($negocio->estado) == 'activo')
                                        <span class="label label-success shadow-sm">Activo</span>
                                    @else
                                        <span class="label label-danger">{{$negocio->estado}}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="{{URL::action('EmpresaNegociosController@edit',$negocio->id_empresa_negocio)}}" 
                                           class="btn btn-info btn-sm btn-flat" title="Editar">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <a href="" data-target="#modal-delete-{{$negocio->id_empresa_negocio}}" 
                                           data-toggle="modal" class="btn btn-danger btn-sm btn-flat" title="Eliminar">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @include('empresas.negocios.modal')
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- PAGINACIÓN -->
                <div class="box-footer clearfix text-right">
                    <div class="pull-right">
                        {{$negocios->render()}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection