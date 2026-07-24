@extends('layouts.empresas')
@section('contenido')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-6 col-md-8 col-sm-12 col-xs-12">
            <div class="box box-danger">
                <div class="box-header with-border">
                    <h3 class="box-title">Registrar Nueva Merma (Baja de Stock)</h3>
                </div>
                {!! Form::open(['url' => '/mermas/guardar', 'method' => 'POST', 'autocomplete' => 'off']) !!}
                {{ Form::token() }}
                
                <div class="box-body">
                    <div class="form-group">
                        <label>Producto o Insumo:</label>
                        <select name="IdProducto" id="selector_producto" class="form-control selectpicker" data-live-search="true" required>
                            <option value="">-- Seleccione un Producto --</option>
                            @foreach($productos as $p)
                                <option value="{{ $p->IdProducto }}" 
                                    data-ubase="{{ $p->unidad_base ?? 'Unidad' }}" 
                                    data-ucons="{{ $p->unidad_cons ?? 'N/A' }}"
                                    data-factor="{{ $p->factor_cons ?? 1 }}">
                                    {{ $p->pronom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>¿En qué unidad lo mediste?</label>
                                <select name="tipo_unidad" id="tipo_unidad" class="form-control" required>
                                    <option value="base" id="opt_base">Unidad Base</option>
                                    <!-- Se llena con JS -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Cantidad exacta:</label>
                                <input type="number" step="any" name="cantidad" class="form-control" placeholder="Ej: 200" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Motivo:</label>
                                <select name="id_motivo" class="form-control" required>
                                    <option value="">-- Seleccione Motivo --</option>
                                    @foreach($motivos as $motivo)
                                        <option value="{{ $motivo->id }}">{{ $motivo->descripcion }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Observación (Opcional):</label>
                        <textarea name="observacion" rows="2" class="form-control" placeholder="Detalle qué pasó con este producto..."></textarea>
                    </div>
                </div>
                
                <div class="box-footer">
                    <button type="submit" class="btn btn-danger">Confirmar Baja de Stock</button>
                    <a href="/mermas" class="btn btn-default">Cancelar</a>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.selectpicker').selectpicker();

    $('#selector_producto').on('change', function() {
        let selected = $(this).find('option:selected');
        let uBase = selected.data('ubase');
        let uCons = selected.data('ucons');
        let factor = parseFloat(selected.data('factor'));

        let comboUnidad = $('#tipo_unidad');
        comboUnidad.empty();

        // Siempre existe la unidad base
        comboUnidad.append('<option value="base">Principal (' + uBase + ')</option>');

        // Si tiene equivalencia (ej: KG y GR) y el factor es mayor a 1, habilitamos la opción
        if(uCons !== 'N/A' && uCons !== uBase && factor > 1) {
            comboUnidad.append('<option value="equivalente" selected>Equivalente (' + uCons + ')</option>');
        }
    });
});
</script>
@endsection