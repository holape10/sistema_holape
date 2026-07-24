@extends('layouts.empresas')

@section('contenido')
<section class="content-header">
    <h1>
        Panel de Fidelización
        <small>Configuración de puntos para Hola P</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
        <li class="active">Fidelización</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-gift text-blue"></i> Reglas de Puntos y Premios</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalCrearRegla">
                            <i class="fa fa-plus"></i> Nueva Regla
                        </button>
                    </div>
                </div>

                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr class="bg-navy" style="color: white;">
                                    <th>Descripción de la Promo</th>
                                    <th class="text-center">Soles x 1 Punto</th>
                                    <th class="text-center">Mínimo Canje</th>
                                    <th>Premio</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($reglas) && count($reglas) > 0)
                                    @foreach($reglas as $regla)
                                    <tr>
                                        <td>{{ $regla->descripcion }}</td>
                                        <td class="text-center">
                                            {{-- Aseguramos que sea número para evitar el error anterior --}}
                                            <b>S/ {{ number_format((float)$regla->valor_sol, 2) }}</b>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-purple">{{ $regla->puntos_minimos }} pts</span>
                                        </td>
                                        <td>
                                            <i class="fa fa-star text-yellow"></i> <b>{{ $regla->premio }}</b>
                                            <br>
                                            @if(!empty($regla->fecha_vencimiento))
                                                @if(\Carbon\Carbon::parse($regla->fecha_vencimiento)->endOfDay()->isPast())
                                                    <small class="text-danger" style="font-weight: 600;">
                                                        <i class="fa fa-calendar-times-o"></i> Venció el {{ \Carbon\Carbon::parse($regla->fecha_vencimiento)->format('d/m/Y') }}
                                                    </small>
                                                @else
                                                    <small class="text-warning" style="font-weight: 600;">
                                                        <i class="fa fa-clock-o"></i> Vence el {{ \Carbon\Carbon::parse($regla->fecha_vencimiento)->format('d/m/Y') }}
                                                    </small>
                                                @endif
                                            @else
                                                <small class="text-success">
                                                    <i class="fa fa-calendar-check-o"></i> Sin fecha de vencimiento
                                                </small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($regla->activo)
                                                <span class="label label-success">Activo</span>
                                            @else
                                                <span class="label label-danger">Inactivo</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                {{-- Botón Editar (abre el modal de edición si lo creas) --}}
                                                <button class="btn btn-info btn-xs" data-toggle="modal" data-target="#modalEditar{{$regla->id}}">
                                                    <i class="fa fa-edit"></i>
                                                </button>

                                                {{-- Botón Cambiar Estado --}}
                                                <a href="{{ route('fidelizacion.estado', $regla->id) }}" 
                                                   class="btn {{ $regla->activo ? 'btn-warning' : 'btn-success' }} btn-xs" 
                                                   title="{{ $regla->activo ? 'Desactivar' : 'Activar' }}">
                                                    <i class="fa fa-power-off"></i>
                                                </a>

                                                {{-- Botón Eliminar --}}
                                                <a href="{{ route('fidelizacion.destroy', $regla->id) }}" 
                                                   class="btn btn-danger btn-xs" 
                                                   onclick="return confirm('¿Seguro que deseas eliminar esta regla?')"
                                                   title="Eliminar">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </div>

                                            <div class="modal fade text-left" id="modalEditar{{$regla->id}}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ url('/fidelizacion/actualizar/'.$regla->id) }}" method="POST">
                {{ csrf_field() }}
                
                <div class="modal-header bg-info">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-edit"></i> Editar Regla: {{ $regla->descripcion }}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre de la Promoción:</label>
                        <input type="text" name="descripcion" class="form-control" value="{{ $regla->descripcion }}" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Soles por cada 1 punto:</label>
                                <div class="input-group">
                                    <span class="input-group-addon">S/</span>
                                    <input type="number" name="valor_sol" class="form-control" step="0.01" value="{{ $regla->valor_sol }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Puntos para el canje:</label>
                                <input type="number" name="puntos_minimos" class="form-control" value="{{ $regla->puntos_minimos }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Premio a entregar:</label>
                        <input type="text" name="premio" class="form-control" value="{{ $regla->premio }}" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fa fa-calendar"></i> Fecha de Vencimiento (Opcional):</label>
                        <input type="date" name="fecha_vencimiento" class="form-control" 
                               value="{{ empty($regla->fecha_vencimiento) ? '' : \Carbon\Carbon::parse($regla->fecha_vencimiento)->format('Y-m-d') }}" 
                               min="{{ date('Y-m-d') }}">
                        <small class="text-muted">Si quieres que deje de vencer, simplemente borra la fecha.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-info">Actualizar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center">No hay reglas configuradas. Haz clic en "Nueva Regla" para empezar.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="box-footer">
                    <small class="text-muted">Nota: Estas reglas se aplicarán automáticamente al momento de cobrar en el Punto de Venta.</small>
                </div>
            </div>

        </div>
    </div>
</section>

<div class="modal fade" id="modalCrearRegla" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('fidelizacion.store') }}" method="POST">
                {{ csrf_field() }}
                <div class="modal-header bg-green">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel"><i class="fa fa-plus"></i> Crear Nueva Regla de Puntos</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre de la Promoción:</label>
                        <input type="text" name="descripcion" class="form-control" placeholder="Ej: Promo Selva Verano" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Soles por cada 1 punto:</label>
                                <div class="input-group">
                                    <span class="input-group-addon">S/</span>
                                    <input type="number" name="valor_sol" class="form-control" step="0.01" value="1.00" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Puntos para el canje:</label>
                                <input type="number" name="puntos_minimos" class="form-control" value="50" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Premio a entregar:</label>
                        <input type="text" name="premio" class="form-control" placeholder="Ej: 1 Jarra de Refresco Gratis" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fa fa-calendar"></i> Fecha de Vencimiento (Opcional):</label>
                        <input type="date" name="fecha_vencimiento" class="form-control" min="{{ date('Y-m-d') }}">
                        <small class="text-muted">Si no seleccionas una fecha, el premio estará disponible de forma permanente.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-success">Guardar Regla</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection