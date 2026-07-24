{{-- Contenido de resources/views/empresas/productos/modal_precios_dinamicos.blade.php --}}

<input type="hidden" id="modal_product_id" value="{{ $productId }}">
<input type="hidden" id="modal_empresa_negocio_id" value="{{ $empresaNegocioId }}">

<div class="row">
    <div class="col-lg-12">
        <p>Define las reglas de precios especiales por día y hora para este producto. Si los rangos se superponen, el sistema priorizará la regla más específica o la última definida (dependiendo de la lógica de tu consulta).</p>
        <p class="text-info">**Nota:** Para definir un precio que va del Jueves a las 18:00 hasta el Viernes a las 16:00, deberás crear una sola regla con `Día: Jueves`, `Hora Inicio: 18:00`, `Hora Fin: 16:00`. La lógica de la base de datos interpretará esto como un cruce de medianoche.</p>
    </div>
</div>

<table id="tabla_precios_dinamicos_modal" class="table table-striped table-bordered table-condensed table-hover">
    <thead>
        <tr>
            <th style="width:20%;">Día de la Semana</th>
            <th style="width:15%;">Hora Inicio</th>
            <th style="width:15%;">Hora Fin</th>
            <th style="width:20%;">Precio Especial</th>
            <th style="width:10%;">Estado</th>
            <th style="width:10%;">Acciones <button type="button" class="btn btn-success btn-xs" id="add_precio_dinamico_modal"><span class="glyphicon glyphicon-plus"></span></button></th>
        </tr>
    </thead>
    <tbody>
        @if(isset($precios_dinamicos_existentes) && count($precios_dinamicos_existentes) > 0)
            @foreach($precios_dinamicos_existentes as $pd)
                <tr>
                    <td>
                        <input type="hidden" name="pd_id[]" value="{{ $pd->id_precio_dia }}">
                        <select name="pd_dia_semana[]" class="form-control input-sm">
                            <option value="0" @if($pd->dia_semana == 0) selected @endif>Domingo</option>
                            <option value="1" @if($pd->dia_semana == 1) selected @endif>Lunes</option>
                            <option value="2" @if($pd->dia_semana == 2) selected @endif>Martes</option>
                            <option value="3" @if($pd->dia_semana == 3) selected @endif>Miércoles</option>
                            <option value="4" @if($pd->dia_semana == 4) selected @endif>Jueves</option>
                            <option value="5" @if($pd->dia_semana == 5) selected @endif>Viernes</option>
                            <option value="6" @if($pd->dia_semana == 6) selected @endif>Sábado</option>
                        </select>
                    </td>
                    <td><input type="time" name="pd_hora_inicio[]" class="form-control input-sm" value="{{ date('H:i', strtotime($pd->hora_inicio_vigencia)) }}"></td>
                    <td><input type="time" name="pd_hora_fin[]" class="form-control input-sm" value="{{ date('H:i', strtotime($pd->hora_fin_vigencia)) }}"></td>
                    <td><input type="number" step="0.01" name="pd_precio_especial[]" class="form-control input-sm" value="{{ $pd->precio_especial }}"></td>
                    <td>
                        <select name="pd_estado[]" class="form-control input-sm">
                            <option value="Activo" @if($pd->estado == 'Activo') selected @endif>Activo</option>
                            <option value="Inactivo" @if($pd->estado == 'Inactivo') selected @endif>Inactivo</option>
                        </select>
                    </td>
                    <td><button type="button" class="btn btn-danger btn-xs remove-precio-dinamico-modal"><span class="glyphicon glyphicon-minus"></span></button></td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>

<div class="row">
    <div class="col-lg-12 text-right">
        <button type="button" class="btn btn-primary" id="guardar_precios_dinamicos">Guardar Cambios</button>
    </div>
</div>

<script>
// JavaScript específico para el modal, se ejecutará cada vez que se cargue el contenido
$(document).ready(function() {
    // Añadir nueva fila de precio dinámico dentro del modal
    $('#add_precio_dinamico_modal').click(function() {
        var newRow = `
            <tr>
                <td>
                    <input type="hidden" name="pd_id[]" value="0"> <select name="pd_dia_semana[]" class="form-control input-sm">
                        <option value="0">Domingo</option>
                        <option value="1">Lunes</option>
                        <option value="2">Martes</option>
                        <option value="3">Miércoles</option>
                        <option value="4">Jueves</option>
                        <option value="5">Viernes</option>
                        <option value="6">Sábado</option>
                    </select>
                </td>
                <td><input type="time" name="pd_hora_inicio[]" class="form-control input-sm" value="00:00"></td>
                <td><input type="time" name="pd_hora_fin[]" class="form-control input-sm" value="23:59"></td>
                <td><input type="number" step="0.01" name="pd_precio_especial[]" class="form-control input-sm" value="0.00"></td>
                <td>
                    <select name="pd_estado[]" class="form-control input-sm">
                        <option value="Activo">Activo</option>
                        <option value="Inactivo">Inactivo</option>
                    </select>
                </td>
                <td><button type="button" class="btn btn-danger btn-xs remove-precio-dinamico-modal"><span class="glyphicon glyphicon-minus"></span></button></td>
            </tr>
        `;
        $('#tabla_precios_dinamicos_modal tbody').append(newRow);
    });

    // Eliminar fila de precio dinámico dentro del modal
    $(document).on('click', '.remove-precio-dinamico-modal', function() {
        $(this).closest('tr').remove();
    });

    // Manejar el guardado de los precios dinámicos
    $('#guardar_precios_dinamicos').click(function() {
        var productId = $('#modal_product_id').val();
        var empresaNegocioId = $('#modal_empresa_negocio_id').val();
        
        var preciosDinamicosData = [];
        $('#tabla_precios_dinamicos_modal tbody tr').each(function() {
            var row = $(this);
            preciosDinamicosData.push({
                id_precio_dia: row.find('input[name="pd_id[]"]').val(),
                dia_semana: row.find('select[name="pd_dia_semana[]"]').val(),
                hora_inicio_vigencia: row.find('input[name="pd_hora_inicio[]"]').val(),
                hora_fin_vigencia: row.find('input[name="pd_hora_fin[]"]').val(),
                precio_especial: row.find('input[name="pd_precio_especial[]"]').val(),
                estado: row.find('select[name="pd_estado[]"]').val()
            });
        });

        $.ajax({
            type: "POST",
            url: "/productos/guardar-precios-dinamicos/" + productId, // Nueva ruta
            data: {
                _token: "{{ csrf_token() }}",
                precios_dinamicos: preciosDinamicosData,
                empresa_negocio_id: empresaNegocioId
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    $('#modalPreciosDinamicos').modal('hide'); // Cerrar el modal
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function(xhr) {
                alert('Error al guardar los precios dinámicos.');
                console.log(xhr.responseText);
            }
        });
    });
});
</script>