@extends('layouts.empresas')

@section('contenido')
<div class="container">
    <h2 class="mt-4">Asignar Casillero al Cliente</h2>
    <hr>

    <div class="row">
        <div class="col-md-5">
            <div class="card border-primary mb-3">
                <div class="card-header bg-primary text-white">Brazalete RFID Detectado</div>
                <div class="card-body">
                    <h5 class="card-title text-success">{{ $brazalete->numero_casillero }}</h5>
                    <p class="card-text"><strong>Código Físico:</strong> {{ $brazalete->codigo_rfid }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3">Búsqueda Avanzada de Cliente (Nombre, DNI o RUC)</h5>
                    
                    <div class="form-group position-relative">
                        <input type="text" id="buscar_cliente" class="form-control form-control-lg" placeholder="Escriba DNI, RUC o Nombre..." autocomplete="off">
                        <div id="lista_resultados" class="list-group" style="position: absolute; width: 100%; z-index: 1000; display: none; max-height: 250px; overflow-y: auto;"></div>
                    </div>

                    <div id="zona_registro_rapido" class="card bg-light p-3 my-3" style="display: none;">
                        <span class="badge badge-warning mb-2">Este cliente no está en tu base de datos</span>
                        <h6>Datos obtenidos de la API Perú:</h6>
                        <input type="hidden" id="reg_tdicod">
                        <div class="form-group">
                            <label>Documento:</label>
                            <input type="text" id="reg_clinum" class="form-control" readonly>
                        </div>
                        <div class="form-group">
                            <label>Nombre / Razón Social:</label>
                            <input type="text" id="reg_clinom" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Dirección:</label>
                            <input type="text" id="reg_clidir" class="form-control">
                        </div>
                        <button type="button" id="btn_guardar_cliente_api" class="btn btn-info btn-sm btn-block">Registrar Cliente en Base de Datos</button>
                    </div>

                    <form action="{{ route('sauna.guardar-checkin') }}" method="POST" class="mt-4 pt-3 border-top">
                        @csrf
                        <input type="hidden" name="brazalete_id" value="{{ $brazalete->id }}">
                        
                        <input type="hidden" name="cliente_id" id="cliente_id_final" required>

                        <div class="form-group">
                            <label><strong>Cliente Seleccionado para Ingreso:</strong></label>
                            <input type="text" id="cliente_seleccionado_display" class="form-control form-control-lg bg-white text-dark font-weight-bold" readonly placeholder="Ninguno seleccionado" required>
                        </div>

                        <button type="submit" id="btn_confirmar_ingreso" class="btn btn-success btn-lg btn-block" disabled>Confirmar Ingreso (Check-In)</button>
                        <a href="{{ route('sauna.recepcion') }}" class="btn btn-secondary btn-block">Cancelar</a>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    var timeout = null;

    // Al escribir en el buscador con retardo (debounce) para no saturar el servidor
    $('#buscar_cliente').on('keyup', function() {
        var query = $(this).val();
        clearTimeout(timeout);

        if (query.length < 3) {
            $('#lista_resultados').hide();
            return;
        }

        timeout = setTimeout(function() {
            $.ajax({
                url: "{{ url('sauna/autocomplete-cliente') }}/" + query,
                type: "GET",
                success: function(data) {
                    var html = '';
                    if (data.length > 0) {
                        $.each(data, function(index, item) {
                            html += '<button type="button" class="list-group-item list-group-item-action opcion-cliente" ' +
                                    'data-clicod="'+item.clicod+'" ' +
                                    'data-num="'+item.num+'" ' +
                                    'data-nom="'+item.nom+'" ' +
                                    'data-dir="'+item.dir+'" ' +
                                    'data-tdicod="'+item.tdicod+'" ' +
                                    'data-nuevo="'+item.es_nuevo+'">' +
                                    item.label + '</button>';
                        });
                    } else {
                        html += '<div class="list-group-item text-muted">No se encontraron resultados ni en la API.</div>';
                    }
                    $('#lista_resultados').html(html).show();
                }
            });
        }, 400); 
    });

    // Al seleccionar una opción del listado
    $(document).on('click', '.opcion-cliente', function() {
        var esNuevo = $(this).data('nuevo');
        var clicod = $(this).data('clicod');
        var num = $(this).data('num');
        var nom = $(this).data('nom');
        var dir = $(this).data('dir');
        var tdicod = $(this).data('tdicod');

        $('#lista_resultados').hide();
        $('#buscar_cliente').val('');

        if (esNuevo === true || esNuevo === "true") {
            // No existe localmente: llenar el formulario de registro rápido
            $('#reg_clinum').val(num);
            $('#reg_clinom').val(nom);
            $('#reg_clidir').val(dir);
            $('#reg_tdicod').val(tdicod);
            $('#zona_registro_rapido').slideDown();
            
            // Limpiar selección previa
            $('#cliente_id_final').val('');
            $('#cliente_seleccionado_display').val('Registrando nuevo cliente...');
            $('#btn_confirmar_ingreso').prop('disabled', true);
        } else {
            // Ya existe: Asignar directo para enviar el Check-In
            $('#zona_registro_rapido').slideUp();
            $('#cliente_id_final').val(clicod);
            $('#cliente_seleccionado_display').val(nom + " (" + num + ")");
            $('#btn_confirmar_ingreso').prop('disabled', false);
        }
    });

    // Guardar mediante AJAX el cliente de la API Perú
    $('#btn_guardar_cliente_api').click(function() {
        var clinum = $('#reg_clinum').val();
        var clinom = $('#reg_clinom').val();
        var clidir = $('#reg_clidir').val();
        var tdicod = $('#reg_tdicod').val();

        $.ajax({
            url: "{{ route('sauna.registrar-cliente-rapido') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                clinum: clinum,
                clinom: clinom,
                clidir: clidir,
                tdicod: tdicod
            },
            success: function(response) {
                if (response.success) {
                    alert('¡Cliente guardado localmente con éxito!');
                    $('#zona_registro_rapido').slideUp();
                    
                    // Seleccionar de forma automática el cliente recién creado
                    $('#cliente_id_final').val(response.clicod);
                    $('#cliente_seleccionado_display').val(response.clinom + " (" + clinum + ")");
                    $('#btn_confirmar_ingreso').prop('disabled', false);
                }
            },
            error: function(xhr) {
                alert('Ocurrió un error al intentar guardar al cliente.');
                console.error(xhr.responseText);
            }
        });
    });
});
</script>
@endsection