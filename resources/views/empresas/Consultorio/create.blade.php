@extends('layouts.empresas')

@section('contenido')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Registrar Nueva Consulta</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('consultorio.store') }}" method="POST">
                @csrf
                
                <h5 class="text-secondary border-bottom pb-2 mb-3">1. Datos del Paciente</h5>
                
                <div class="row form-group">
                    <div class="col-md-12 mb-3">
                        <label for="buscar_paciente"><strong>Buscar Paciente (DNI o Nombre):</strong></label>
                        <input type="text" id="buscar_paciente" class="form-control form-control-lg border-primary" placeholder="Escribe para buscar en base de datos o API...">
                        <small class="text-muted">Si el paciente no existe, se buscará en la API de SUNAT/RENIEC automáticamente.</small>
                    </div>
                </div>

                <input type="hidden" id="clicod" name="clicod">
                <input type="hidden" id="tdicod" name="tdicod">
                
                <div class="row form-group bg-light p-3 rounded border">
                    <div class="col-md-4">
                        <label>DNI / Documento</label>
                        <input type="text" id="clinum" name="clinum" class="form-control" required readonly>
                    </div>
                    <div class="col-md-8">
                        <label>Nombre Completo</label>
                        <input type="text" id="clinom" name="clinom" class="form-control" required readonly>
                    </div>
                    <div class="col-md-12 mt-2">
                        <label>Dirección</label>
                        <input type="text" id="clidir" name="clidir" class="form-control" readonly>
                    </div>
                </div>

                <h5 class="text-secondary border-bottom pb-2 mb-3 mt-4">2. Datos de la Consulta</h5>

                <div class="row form-group">
                    <div class="col-md-6">
                        <label>Motivo de la Consulta</label>
                        <textarea name="motivo_consulta" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="col-md-6">
                        <label>Exploración Física</label>
                        <textarea name="exploracion_fisica" class="form-control" rows="3"></textarea>
                    </div>
                </div>

                <div class="row form-group">
                    <div class="col-md-6">
                        <label>Diagnóstico</label>
                        <textarea name="diagnostico" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label>Tratamiento / Receta</label>
                        <textarea name="tratamiento" class="form-control" rows="3"></textarea>
                    </div>
                </div>

                <hr>
                <div class="form-group text-right">
                    <a href="{{ route('consultorio.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success" id="btnGuardar" disabled>
                        <i class="fa fa-save"></i> Guardar Consulta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

<script>
$(document).ready(function() {
    // Configuración del autocompletado
    $("#buscar_paciente").autocomplete({
        source: function(request, response) {
            // Determinamos si está escribiendo números (DNI) o letras (Nombre)
            var fieldType = $.isNumeric(request.term) ? 'clinum' : 'clinom';
            
            $.ajax({
                url: "{{ route('cliente.autocomplete') }}", // Asegúrate que esta ruta apunte a tu función
                dataType: "json",
                data: {
                    query: request.term,
                    field: fieldType
                },
                success: function(data) {
                    response(data);
                }
            });
        },
        minLength: 3, // Empieza a buscar después de 3 caracteres
        select: function(event, ui) {
            // ui.item trae exactamente el array de tu función autocompleteClient
            
            // Rellenar campos visibles
            $("#clinum").val(ui.item.num);
            $("#clinom").val(ui.item.nom);
            $("#clidir").val(ui.item.dir);
            
            // Rellenar campos ocultos
            $("#clicod").val(ui.item.clicod); // Si es de la API, esto será null, y el controlador lo creará
            $("#tdicod").val(ui.item.tdicod);
            
            // Habilitamos el botón de guardar
            $("#btnGuardar").prop('disabled', false);
            
            // Ponemos el nombre en el buscador para que se vea bonito
            $("#buscar_paciente").val(ui.item.nom);
            
            return false;
        }
    }).autocomplete("instance")._renderItem = function(ul, item) {
        // Estilo personalizado para diferenciar si viene de BD o API
        var icon = item.clicod ? '<span style="color:green; font-size:10px;">[BD Local]</span>' : '<span style="color:blue; font-size:10px;">[API]</span>';
        return $("<li>")
            .append("<div>" + icon + " <b>" + item.num + "</b> - " + item.nom + "</div>")
            .appendTo(ul);
    };

    // Permitir ingreso manual desbloqueando los campos si borra el buscador
    $("#buscar_paciente").on('keyup', function() {
        if($(this).val() === '') {
            $("#clinum, #clinom, #clidir").val('').prop('readonly', false);
            $("#clicod").val(''); // Forzamos a que cree uno nuevo
            $("#btnGuardar").prop('disabled', false); // Le dejamos guardar manualmente
        }
    });
});
</script>
@endsection