@extends('layouts.empresas')

@section('contenido')
<div class="container mt-4">
    <div class="card shadow mb-4">
        <div class="card-body bg-light">
            <form action="{{ url()->current() }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="buscar_usuario" class="form-label fw-bold"><i class="fas fa-user"></i> Buscar por Usuario</label>
                    <input type="text" name="buscar_usuario" id="buscar_usuario" class="form-control" placeholder="ID, nombre o apellido..." value="{{ request('buscar_usuario') }}">
                </div>
                <div class="col-md-5">
                    <label for="buscar_mesa" class="form-label fw-bold"><i class="fas fa-utensils"></i> Buscar por Mesa / Servicio</label>
                    <input type="text" name="buscar_mesa" id="buscar_mesa" class="form-control" placeholder="ID de mesa, nombre o servicio (Llevar/Delivery)..." value="{{ request('buscar_mesa') }}">
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary fw-bold"><i class="fas fa-search"></i> Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <h5 class="mb-0 me-3">📊 Panel de Monitoreo de Impresiones</h5>
                
                <!-- Botón de eliminar masivo (Oculto por defecto) -->
                <button type="button" id="btn-eliminar-masivo" class="btn btn-danger btn-sm fw-bold shadow-sm" style="display: none;">
                    <i class="fas fa-trash-alt"></i> Eliminar (<span id="contador-seleccionados">0</span>)
                </button>
            </div>
            <span class="badge bg-success">Sistema en la Nube</span>
        </div>
        <div class="card-body">
            
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <!-- Checkbox Principal -->
                            <th class="text-center" style="width: 40px;">
                                <input type="checkbox" id="check-todos" class="form-check-input shadow-none" style="cursor: pointer;">
                            </th>
                            <th>ID</th>                            
                            <th>Impresora</th>                            
                            <th>Estado</th>
                            <th class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($tickets->isEmpty())
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No se encontraron tickets con los filtros aplicados.</td>
                            </tr>
                        @endif
                        @foreach($tickets as $t)
                        <tr id="fila-{{ $t->id }}">
                            <!-- Checkbox Individual -->
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input check-individual shadow-none" value="{{ $t->id }}" style="cursor: pointer;">
                            </td>
                            <td>{{ $t->id }}</td>                            
                            <td><span class="badge bg-secondary text-uppercase">{{ $t->impresora }}</span></td>
                            
                          

                            <td class="estado-celda">
                                @if($t->estado == 'pendiente')
                                    <span class="badge bg-warning text-dark">⏳ Pendiente</span>
                                @else
                                    <span class="badge bg-success">✅ Impreso</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-primary btn-reimprimir" data-id="{{ $t->id }}">
                                    🔄 Reimprimir
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    
    // --- LÓGICA DE LOS CHECKBOXES ---

    // 1. Al hacer clic en el Checkbox Principal (Seleccionar/Deseleccionar todo)
    $('#check-todos').on('change', function() {
        var estado = $(this).prop('checked');
        $('.check-individual').prop('checked', estado);
        actualizarBotonEliminar();
    });

    // 2. Al hacer clic en un Checkbox Individual
    $(document).on('change', '.check-individual', function() {
        var total = $('.check-individual').length;
        var chequeados = $('.check-individual:checked').length;

        // Si todos están marcados, marca el principal. Si falta uno, desmárcalo.
        $('#check-todos').prop('checked', (total === chequeados && total > 0));
        actualizarBotonEliminar();
    });

    // Función que muestra u oculta el botón rojo de eliminar según si hay selecciones
    function actualizarBotonEliminar() {
        var seleccionados = $('.check-individual:checked').length;
        $('#contador-seleccionados').text(seleccionados);

        if (seleccionados > 0) {
            $('#btn-eliminar-masivo').fadeIn(200);
        } else {
            $('#btn-eliminar-masivo').fadeOut(200);
        }
    }

    // --- LÓGICA DE ELIMINACIÓN ---

    $('#btn-eliminar-masivo').on('click', function() {
        var seleccionados = $('.check-individual:checked').length;
        
        if (seleccionados === 0) return;

        if (!confirm('¿Estás seguro de que deseas eliminar ' + seleccionados + ' ticket(s) seleccionado(s)? Esta acción no se puede deshacer.')) {
            return;
        }

        // Recopilamos todos los IDs marcados en un array
        var ids = [];
        $('.check-individual:checked').each(function() {
            ids.push($(this).val());
        });

        var boton = $(this);
        var textoOriginal = boton.html();
        boton.prop('disabled', true).html('⏳ Eliminando...');

        $.ajax({
            url: "{{ url('/eliminar-tickets') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                ids: ids
            },
            success: function(response) {
                if (response.success) {
                    // Eliminamos visualmente las filas de la tabla con una animación
                    $.each(ids, function(index, id) {
                        $('#fila-' + id).fadeOut(400, function() {
                            $(this).remove();
                        });
                    });
                    
                    // Reset de controles en la interfaz
                    $('#check-todos').prop('checked', false);
                    boton.fadeOut(200);
                    alert(response.message);
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Hubo un problema de conexión para eliminar los tickets.');
            },
            complete: function() {
                boton.prop('disabled', false).html(textoOriginal);
            }
        });
    });

    // --- LÓGICA DE REIMPRESIÓN ---
    $('.btn-reimprimir').click(function() {
        var boton = $(this);
        var ticketId = boton.data('id');
        var fila = $('#fila-' + ticketId);

        boton.prop('disabled', true).text('⏳ Enviando...');

        $.ajax({
            url: "{{ url('/reimprimir-ticket') }}/" + ticketId,
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                if(response.success) {
                    fila.find('.estado-celda').html('<span class="badge bg-warning text-dark">⏳ Pendiente</span>');
                    alert(response.message);
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Hubo un problema de conexión con el VPS.');
            },
            complete: function() {
                boton.prop('disabled', false).text('🔄 Reimprimir');
            }
        });
    });
});
</script>
@endsection