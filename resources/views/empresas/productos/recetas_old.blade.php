@extends('layouts.empresas')
@section('contenido')

<style>
#b1 { white-space: normal; }
#scroll {
    max-height: 50vh; 
    width: 100%;
    overflow-y: auto;
    overflow-x: auto;
}
.fila-activa { background-color: #e8f4f8 !important; }
.col-activar { min-width: 70px; text-align: center; }
.col-insumo { min-width: 250px; }
.col-cantidad { min-width: 100px; }
.col-unidad { min-width: 120px; }
.col-costo { min-width: 100px; }
.table>tbody>tr>td { vertical-align: middle !important; }
.panel-totales {
    background-color: #f9f9f9;
    border-radius: 5px;
    padding: 15px 5px;
    margin-top: 15px;
    border: 1px solid #ddd;
    display: flex;
    align-items: center;
}
.caja-precio-final {
    background-color: #e8f5e9;
    border: 2px dashed #4caf50;
    border-radius: 8px;
    padding: 10px;
}
</style>

<body>
    <script>
        $(document).ready(function() {
            // Buscador rápido
            $("#buscador_insumos").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#grdet tbody tr").each(function() {
                    var nombreInsumo = $(this).find("td:eq(1) input").val().toLowerCase();
                    if (nombreInsumo.indexOf(value) > -1) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Calcular costos iniciales al cargar la página
            calcularCostos();
        });

        function habilitarFila(id) {
            var checkbox = $('#check_' + id);
            var fila = $('#fila_' + id);

            if (checkbox.is(':checked')) {
                $('#cant_' + id).removeAttr('disabled');
                $('#und_' + id).removeAttr('disabled');
                $('#factor_' + id).removeAttr('disabled');
                $('#input_costo_' + id).removeAttr('disabled');
                fila.addClass('fila-activa');
                $('#cant_' + id).focus();
            } else {
                $('#cant_' + id).attr('disabled', 'disabled');
                $('#und_' + id).attr('disabled', 'disabled');
                $('#factor_' + id).attr('disabled', 'disabled');
                $('#input_costo_' + id).attr('disabled', 'disabled');
                fila.removeClass('fila-activa');
            }
            calcularCostos();
        }

        function calcularCostos() {
            let costoTotalPlato = 0;

            $("#grdet tbody tr").each(function() {
                let id = $(this).attr('id').split('_')[1];
                let isChecked = $('#check_' + id).is(':checked');

                if (isChecked) {
                    let cantidad = parseFloat($('#cant_' + id).val()) || 0;
                    let factor = parseFloat($('#factor_' + id).val()) || 1;
                    let costoBase = parseFloat($('#costobase_' + id).val()) || 0;

                    let costoInsumo = (cantidad / factor) * costoBase;

                    $('#text_costo_' + id).text(costoInsumo.toFixed(3));
                    $('#input_costo_' + id).val(costoInsumo.toFixed(3));

                    costoTotalPlato += costoInsumo;
                } else {
                    $('#text_costo_' + id).text('0.000');
                    $('#input_costo_' + id).val(0);
                }
            });

            // Costo Total Insumos
            $('#total_costo_plato').text(costoTotalPlato.toFixed(2));

            // Precio Sugerido
            let porcentajeEsperado = parseFloat($('#porcentaje_esperado').val()) || 30;
            let precioSugerido = 0;
            if(porcentajeEsperado > 0) {
                precioSugerido = costoTotalPlato / (porcentajeEsperado / 100);
            }
            $('#precio_sugerido').text(precioSugerido.toFixed(2));
        }
    </script>
</body>

<br/>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
            @if(session()->has('info'))
            <div class="alert alert-danger">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <strong>Alerta!</strong> {{ session('info') }}
            </div>
            @endif

            @if(session()->has('success'))
            <div class="alert alert-success">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <strong>Información!</strong> {{ session('success') }}
            </div>
            @endif
        </div>
    </div>
    
    <div class="row">
        {!!Form::open(array('url'=>'/registrarreceta','autocomplete'=>'off','method'=>'POST','name'=>'formreceta','id'=>'formreceta','role'=>'form','files'=>'true'))!!}
        {{Form::token()}}
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="box">
                <div style="background-color:#0040FF;" class="box-header with-border">
                    <center><font color="white"><strong>REGISTRAR INSUMOS Y PRECIO - {{$producto->pronom}}</strong></font></center>
                    <div class="box-tools pull-right">   
                        <button class="btn btn-sm btn-success" type="submit" id="btnRegComp" name="btnRegComp"><strong>Registrar Todo</strong></button>
                        <a href="/productos"><button type="button" class="btn btn-sm btn-danger"><strong>Cancelar</strong></button></a>
                    </div>
                </div>
                
                <div class="box-body">
                    <div class="row" style="margin-bottom: 15px;">
                        <input type="hidden" name="producto" value="{{$prod_id}}">
                        <div class="col-lg-6 col-md-8 col-sm-12 col-xs-12 form-group">
                            <input type="text" id="buscador_insumos" class="form-control" placeholder="Buscar insumo rápidamente...">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 table-responsive" id="scroll">
                            <table class="table table-hover table-bordered" id="grdet">
                                <thead>
                                    <tr>
                                        <th class="col-activar">Activar</th>
                                        <th class="col-insumo">Insumo</th>
                                        <th class="col-cantidad">Cantidad</th>
                                        <th class="col-unidad">Unidad Medida</th>
                                        <th class="col-costo">Costo Base</th>
                                        <th class="col-costo">Subtotal (S/)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $insumosOrdenados = $insumos->sortByDesc(function ($insumo) use ($receta) {
                                            return $receta->contains('prod_insu', $insumo->IdProducto) ? 1 : 0;
                                        });
                                    @endphp

                                    @foreach($insumosOrdenados as $insumo)
                                        @php
                                            $itemReceta = $receta->firstWhere('prod_insu', $insumo->IdProducto);
                                            $checked = $itemReceta ? 'checked' : '';
                                            $cantidad = $itemReceta ? $itemReceta->rec_cant : '1';

                                            $unidad = !empty($insumo->umecod_cons) ? $insumo->umecod_cons : $insumo->umecod;
                                            $factor_ins = !empty($insumo->factor_cons) ? $insumo->factor_cons : $insumo->factor;
                                            $costo_base_insumo = $insumo->costo ?? 0; 
                                        @endphp

                                        <tr id="fila_{{ $insumo->IdProducto }}" class="{{ $checked ? 'fila-activa' : '' }}">
                                            <td class="col-activar">
                                                <input type="checkbox" name="prod_ins_id[]" value="{{ $insumo->IdProducto }}" id="check_{{ $insumo->IdProducto }}" onchange="habilitarFila({{ $insumo->IdProducto }})" {{ $checked }} style="transform: scale(1.3);">
                                            </td>
                                            <td class="col-insumo">
                                                <input type="text" class="form-control input-sm" value="{{ $insumo->pronom }}" readonly style="width:100%; border:none; background:transparent; box-shadow:none;">
                                            </td>
                                            <td class="col-cantidad"> 
                                                <input type="number" step="any" name="cantidad[{{ $insumo->IdProducto }}]" id="cant_{{ $insumo->IdProducto }}" value="{{ $cantidad }}" class="form-control input-sm" onkeyup="calcularCostos()" onchange="calcularCostos()" {{ $checked ? '' : 'disabled' }}>
                                            </td>
                                            <td class="col-unidad"> 
                                                <input type="text" name="unidadmedida[{{ $insumo->IdProducto }}]" id="und_{{ $insumo->IdProducto }}" value="{{ $unidad }}" class="form-control input-sm" readonly {{ $checked ? '' : 'disabled' }}>
                                            </td>
                                            
                                            <td class="col-costo">
                                                <input type="text" id="costobase_{{ $insumo->IdProducto }}" value="{{ $costo_base_insumo }}" class="form-control input-sm" readonly style="border:none; background:transparent; box-shadow:none;">
                                            </td>
                                            <td class="col-costo" style="font-weight: bold; color: #333;">
                                                <span id="text_costo_{{ $insumo->IdProducto }}">0.00</span>
                                                <input type="hidden" name="costo_insumo[{{ $insumo->IdProducto }}]" id="input_costo_{{ $insumo->IdProducto }}" value="0" {{ $checked ? '' : 'disabled' }}>
                                            </td>

                                            <td hidden="hidden">
                                                <input type="text" name="factor_ins[{{ $insumo->IdProducto }}]" id="factor_{{ $insumo->IdProducto }}" value="{{ $factor_ins }}" readonly {{ $checked ? '' : 'disabled' }}>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row panel-totales">
                        <div class="col-md-3 col-sm-6 text-center">
                            <h5 style="margin-bottom: 5px; color:#555;">Costo Insumos</h5>
                            <h3 style="margin: 0; color: #d9534f; font-weight: bold;">S/ <span id="total_costo_plato">0.00</span></h3>
                        </div>
                        <div class="col-md-3 col-sm-6 text-center" style="border-left: 1px solid #ccc;">
                            <h5 style="margin-bottom: 5px; color:#555;">% Margen Deseado</h5>
                            <div style="display: flex; justify-content: center; align-items: center; gap: 5px;">
                                <input type="number" id="porcentaje_esperado" class="form-control text-center" value="30" onkeyup="calcularCostos()" onchange="calcularCostos()" style="width: 80px; font-weight: bold; height: 35px;">
                                <span style="font-size: 1.2em; font-weight: bold;">%</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 text-center" style="border-left: 1px solid #ccc;">
                            <h5 style="margin-bottom: 5px; color:#555;">Precio Sugerido</h5>
                            <h3 style="margin: 0; color: #f0ad4e; font-weight: bold;">S/ <span id="precio_sugerido">0.00</span></h3>
                        </div>
                        
                        <div class="col-md-3 col-sm-6 text-center">
                            <div class="caja-precio-final">
                                <h5 style="margin-top: 0; margin-bottom: 8px; color: #2e7d32; font-weight: bold;">PRECIO FINAL VENTA</h5>
                                <div style="display: flex; justify-content: center; align-items: center; gap: 5px;">
                                    <span style="font-size: 1.5em; font-weight: bold; color: #2e7d32;">S/</span>
                                    <input type="number" step="any" name="precio_venta" id="precio_venta" class="form-control text-center" value="{{ $precio_actual }}" style="width: 100px; font-weight: bold; font-size: 1.2em; color: #2e7d32; border: 1px solid #4caf50; height: 40px;">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>  
            </div>
            {!!Form::close()!!}
        </div>
    </div>
</div>
@endsection