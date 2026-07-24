@extends('layouts.empresas')
@section('contenido')

<section class="content">  
    <div class="row">
        <div class="col-md-12">
            <!-- CAJA DE BÚSQUEDA Y TÍTULO -->
            <div class="box box-primary shadow">
                <div class="box-header with-border" style="background: #2c3e50; color: white;">
                    <h3 class="box-title"><i class="fa fa-industry"></i> <strong>ADMINISTRACIÓN DE EMPRESAS</strong></h3>
                    <div class="box-tools pull-right">
                        @if(Auth::user()->hasRole('superadmin'))
                        <a href="/empresas/create" class="btn btn-success btn-sm btn-flat shadow">
                            <i class="fa fa-plus"></i> NUEVA EMPRESA
                        </a>
                        @endif
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-lg-6 col-md-8 col-sm-12">
                            @include('administrador.empresas.search')   
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLA DE RESULTADOS -->
            <div class="box box-solid shadow">
                <div class="box-header with-border bg-gray">
                    <h3 class="box-title text-dark"><i class="fa fa-list"></i> Lista de Empresas en el Sistema</h3>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover table-striped table-bordered mb-0">
                        <thead class="bg-gray-light">
                            <tr>
                                <th class="text-center" style="width: 120px;">RUC</th>
                                <th>RAZÓN SOCIAL</th>
                                <th>SUCURSAL</th>
                                <th>DIRECCIÓN</th>
                                <th class="text-center">LOGO</th>
                                <th class="text-center">ESTADO</th>
                                <th class="text-center" style="width: 280px;">OPCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($empresas as $emp)
                            <tr>
                                <td class="text-center"><strong>{{$emp->IdEmpresa}}</strong></td>
                                <td>{{$emp->NomEmpresa}}</td>
                                <td><span class="text-muted">{{$emp->tipo_negocio}}</span></td>
                                <td><small>{{$emp->direccion}}</small></td>
                                <td class="text-center">
								    {{-- Validamos que el nombre no esté vacío y que el archivo exista en public/ --}}
								    @if(!empty($emp->LogEmpresa) && file_exists(public_path($emp->LogEmpresa)))
								        <img src="{{ asset($emp->LogEmpresa) }}" 
								             alt="Logo {{ $emp->NomEmpresa }}" 
								             style="height: 65px; width: 65px; object-fit: contain;" 
								             class="img-thumbnail shadow-sm">
								    @else
								        {{-- Icono de respaldo si no hay imagen --}}
								        <div class="img-thumbnail bg-gray shadow-sm" 
								             style="height: 65px; width: 65px; display: flex; align-items: center; justify-content: center; margin: auto;">
								            <i class="fa fa-industry text-muted" style="font-size: 24px;" title="Sin Logo"></i>
								        </div>
								    @endif
								</td>
                                <td class="text-center">
                                    @if(strtolower($emp->EstEmpresa) == 'activo')
                                        <span class="label label-success shadow-sm">Activo</span>
                                    @else
                                        <span class="label label-danger">{{$emp->EstEmpresa}}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="/impresoras/listarimpresoras/{{$emp->id_empresa_negocio}}" class="btn btn-default btn-sm btn-flat" title="Gestionar Impresoras">
                                            <i class="fa fa-print"></i> Impresoras
                                        </a>
                                        <a href="{{URL::action('EmpresaController@edit',$emp->IdEmpresa)}}" class="btn btn-info btn-sm btn-flat" title="Editar Empresa">
                                            <i class="fa fa-pencil"></i> Editar
                                        </a>
                                        <a href="{{config('global.ruta')}}/PanelClientes/{{$emp->IdEmpresa}}/{{$emp->id_empresa_negocio}}" class="btn btn-success btn-sm btn-flat" title="Ir al Panel">
                                            <i class="fa fa-dashboard"></i> Panel
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @include('administrador.empresas.modal')
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- PAGINACIÓN -->
                <div class="box-footer clearfix">
                    <div class="pull-right">
                        {{$empresas->render()}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection