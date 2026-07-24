@extends('layouts.empresas')

@section('contenido')
<style>
    /* Espaciado para móviles */
    .form-group { margin-bottom: 15px; }
</style>

<section class="content" style="padding-top: 20px;">
    <div class="row">
        <div class="col-md-12 col-xs-12">
            <div class="box shadow-box">
                <div class="box-header custom-header">
                    <h3 class="box-title" style="color: white; font-weight: bold;">
                        <center><i class="fa fa-filter"></i> FILTRO DE COMPROBANTES A VALIDAR</center>
                    </h3>
                </div>
                
                <div class="box-body" style="padding: 25px;">
                    <form method="GET" action="{{ url('consulta-multiple-v2') }}">
                        <div class="row">
                            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 form-group">
                                <label><i class="fa fa-calendar"></i> Fecha Inicio:</label>
                                <input type="date" name="fecha_inicio" class="form-control" value="{{ $fecha_inicio }}" required>
                            </div>
                            
                            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 form-group">
                                <label><i class="fa fa-calendar"></i> Fecha Fin:</label>
                                <input type="date" name="fecha_fin" class="form-control" value="{{ $fecha_fin }}" required>
                            </div>
                            
                            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 form-group">
                                <label class="hidden-xs">&nbsp;</label> <button type="submit" class="btn btn-primary btn-block btn-elegant" style="padding: 8px; font-weight: bold;">
                                    <i class="fa fa-search"></i> Consultar SUNAT
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row" style="margin-top: 10px;">
        <div class="col-md-12 col-xs-12">
            <div class="box shadow-box">
                <div class="box-body" style="padding: 20px;">
                    
                    @if(isset($ventas) && $ventas->count() == 0)
                        <div class="alert alert-warning alert-elegant">
                            <h4><i class="fa fa-exclamation-triangle"></i> Sin resultados</h4>
                            No hay comprobantes registrados entre el <strong>{{ $fecha_inicio }}</strong> y el <strong>{{ $fecha_fin }}</strong>.
                        </div>
                    @elseif(isset($resultado) && $resultado->success)
                        <div class="alert alert-info alert-elegant">
                            <h4 style="margin: 0;"><i class="fa fa-info-circle"></i> Resultados de validación para <strong>{{ $resultado->data->cantidad_de_comprobantes }}</strong> documentos.</h4>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-vertical-align">
                                <thead class="custom-header">
                                    <tr>
                                        <th class="text-center">RUC Emisor</th>
                                        <th class="text-center">Documento</th>
                                        <th class="text-center">Fecha</th>
                                        <th class="text-right">Total</th>
                                        <th class="text-center">Estado SUNAT (Semáforo)</th>
                                        <th>Contribuyente</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($resultado->data->comprobantes as $cpe)
                                    @php
                                        $descripcion = strtoupper($cpe->comprobante_estado_descripcion);
                                        
                                        // DEFINICIÓN DEL SEMÁFORO (Mantenida intacta)
                                        if (strpos($descripcion, 'ACEPTADO') !== false) {
                                            $colorFondo = '#dff0d8'; // Verde suave para la fila
                                            $colorTexto = '#3c763d'; // Verde oscuro para el texto
                                            $badgeColor = 'label-success'; // Clase AdminLTE para verde
                                            $icon = 'fa-check';
                                        } elseif (strpos($descripcion, 'NO EXISTE') !== false) {
                                            $colorFondo = '#fcf8e3'; // Naranja/Amarillo suave
                                            $colorTexto = '#8a6d3b'; // Marrón/Naranja oscuro
                                            $badgeColor = 'label-warning'; // Clase AdminLTE para naranja
                                            $icon = 'fa-warning';
                                        } else {
                                            // RECHAZADO, ANULADO o errores
                                            $colorFondo = '#f2dede'; // Rojo suave
                                            $colorTexto = '#a94442'; // Rojo oscuro
                                            $badgeColor = 'label-danger'; // Clase AdminLTE para rojo
                                            $icon = 'fa-times';
                                        }
                                    @endphp
                                    <tr style="background-color: {{ $colorFondo }} !important; color: {{ $colorTexto }}; font-weight: bold;">
                                        <td class="text-center">{{ $cpe->ruc_emisor }}</td>
                                        <td class="text-center">{{ $cpe->serie_documento }}-{{ $cpe->numero_documento }}</td>
                                        <td class="text-center">{{ $cpe->fecha_de_emision }}</td>
                                        <td class="text-right">S/ {{ number_format($cpe->total, 2) }}</td>
                                        <td class="text-center">
                                            {{-- Usamos 'label' que es más común en AdminLTE --}}
                                            <span class="label {{ $badgeColor }} shadow-sm" style="font-size: 13px; padding: 6px 10px; display: inline-block; border-radius: 4px;">
                                                <i class="fa {{ $icon }}"></i> {{ $descripcion }}
                                            </span>
                                        </td>
                                        <td>
                                            <small style="line-height: 1.2; display: block;">
                                                <strong><i class="fa fa-caret-right"></i> {{ $cpe->empresa_estado_descripcion }}</strong><br>
                                                <span class="text-muted">{{ $cpe->empresa_condicion_descripcion }}</span>
                                            </small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-danger alert-elegant">
                            <h4><i class="fa fa-times-circle"></i> Error de conexión</h4>
                            No se pudo conectar con la API o el Token es incorrecto.
                        </div>
                    @endif
                    
                </div>
            </div>
        </div>
    </div>
</section>
@endsection