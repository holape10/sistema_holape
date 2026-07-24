@extends('layouts.empresas')
@section('contenido')
<div class="container-fluid">
    <div class="row" style="margin-bottom: 10px;">
        <div class="col-lg-12">
            <button class="btn btn-success" data-toggle="modal" data-target="#modal-nuevo"><i class="fa fa-plus"></i> Nuevo Motivo</button>
            <a href="/mermas" class="btn btn-default"><i class="fa fa-arrow-left"></i> Volver a Mermas</a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-12">
            @if(session()->has('success'))
            <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>
            @endif
            @if(session()->has('info'))
            <div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('info') }}</div>
            @endif
            
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Gestión de Motivos de Merma</h3>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-hover table-bordered table-striped">
                        <thead style="background-color: #3c8dbc; color: white;">
                            <tr>
                                <th>ID</th>
                                <th>Descripción del Motivo</th>
                                <th>Estado</th>
                                <th>Opciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($motivos as $m)
                            <tr>
                                <td>{{ $m->id }}</td>
                                <td>{{ $m->descripcion }}</td>
                                <td>
                                    @if($m->estado == 1)
                                        <span class="label label-success">Activo</span>
                                    @else
                                        <span class="label label-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-xs btn-warning" data-toggle="modal" data-target="#modal-editar-{{ $m->id }}" title="Editar"><i class="fa fa-edit"></i></button>
                                    <a href="/motivos-merma/eliminar/{{ $m->id }}" onclick="return confirm('¿Seguro que deseas eliminar este motivo?');" class="btn btn-xs btn-danger" title="Eliminar"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>

                            <div class="modal fade" id="modal-editar-{{ $m->id }}">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header" style="background-color: #f39c12; color: white;">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            <h4 class="modal-title">Editar Motivo</h4>
                                        </div>
                                        {!! Form::open(['url' => '/motivos-merma/actualizar', 'method' => 'POST', 'autocomplete' => 'off']) !!}
                                        {{ Form::token() }}
                                        <div class="modal-body">
                                            <input type="hidden" name="id" value="{{ $m->id }}">
                                            <div class="form-group">
                                                <label>Descripción:</label>
                                                <input type="text" name="descripcion" class="form-control" value="{{ $m->descripcion }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Estado:</label>
                                                <select name="estado" class="form-control" required>
                                                    <option value="1" {{ $m->estado == 1 ? 'selected' : '' }}>Activo</option>
                                                    <option value="0" {{ $m->estado == 0 ? 'selected' : '' }}>Inactivo</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                                            <button type="submit" class="btn btn-warning">Actualizar</button>
                                        </div>
                                        {!! Form::close() !!}
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-nuevo">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #00a65a; color: white;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Registrar Nuevo Motivo</h4>
            </div>
            {!! Form::open(['url' => '/motivos-merma/guardar', 'method' => 'POST', 'autocomplete' => 'off']) !!}
            {{ Form::token() }}
            <div class="modal-body">
                <div class="form-group">
                    <label>Descripción (Ej. Vencimiento en Almacén):</label>
                    <input type="text" name="descripcion" class="form-control" required placeholder="Ingrese motivo...">
                </div>
                <div class="form-group">
                    <label>Estado:</label>
                    <select name="estado" class="form-control" required>
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-success">Guardar</button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
@endsection