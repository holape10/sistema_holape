@extends('layouts.empresas')

@section('contenido')
<style>
    /* Estilos Premium Hola P - Mejorados */
    .cat-pill {
        border-radius: 20px;
        padding: 6px 16px;
        font-weight: 600;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .cat-pill:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        opacity: 0.9;
    }
    
    .producto-card {
        border-radius: 12px;
        border: 1px solid #eaeaea;
        transition: all 0.2s ease;
        background: #fff;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        overflow: hidden;
    }
    .producto-card:hover {
        border-color: #2c3e50;
        box-shadow: 0 5px 15px rgba(44,62,80,0.15);
        transform: scale(1.03);
    }
    .nombre-producto {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.3;
        font-size: 0.85rem;
        color: #333;
        font-weight: bold;
    }
    .precio-producto {
        color: #28a745;
        font-weight: bold;
        font-size: 0.9rem;
        margin-top: auto;
    }

    #contenedor_productos::-webkit-scrollbar { width: 6px; }
    #contenedor_productos::-webkit-scrollbar-thumb { background-color: #2c3e50; border-radius: 10px; }
    
    .input-observacion {
        font-size: 13px !important;
        height: 34px !important;
        border: 1px solid #ccd1d9 !important;
        border-radius: 4px !important;
        background-color: #ffffff !important;
        color: #333333 !important;
        box-shadow: inset 0 1px 1px rgba(0,0,0,0.075);
        transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
    }

    .input-observacion:focus {
        border-color: #3498db !important;
        box-shadow: inset 0 1px 1px rgba(0,0,0,0.075), 0 0 8px rgba(52,152,219,0.6);
        background-color: #fff !important;
    }

    /* Estilos globales Hola P integrados localmente */
    .shadow-box { 
        box-shadow: 0 4px 15px rgba(0,0,0,0.1); 
        border-radius: 8px; 
        border-top: none !important; 
        background: #fff;
    }
    .custom-header { 
        background-color: #2c3e50 !important; 
        color: white !important; 
        border-radius: 8px 8px 0 0; 
    }
    .btn-elegant {
        box-shadow: 0 2px 5px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
        border-radius: 4px;
    }
    .btn-elegant:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
</style>

<section class="content" style="padding-top: 20px;">
    <div class="row">
        <div class="col-md-6 col-xs-12">
            <div class="box shadow-box">
                <div class="box-header custom-header" style="padding: 15px;">
                    <h3 class="box-title" style="color: white; font-weight: bold; margin: 0;">
                        <i class="fa fa-calendar-check-o"></i> NUEVA RESERVA
                    </h3>
                    <button type="button" class="btn btn-xs btn-default pull-right btn-elegant" onclick="abrirModalCliente()">+ Crear Nuevo</button>
                </div>
                
                <div class="box-body" style="background-color: #f8f9fa; padding: 20px;">
                    <form id="form-reserva" action="{{ route('reservas.store') }}" method="POST" target="_blank">
                        @csrf
                        
                        <div class="form-group" style="background: white; padding: 15px; border-radius: 8px; border: 1px solid #ddd; box-shadow: 0 2px 4px rgba(0,0,0,0.05); position: relative;">
                            <label style="color: #666; font-size: 12px; text-transform: uppercase;">
                                <i class="fa fa-user"></i> Buscar Cliente (Escribe Nombre, DNI o RUC)
                            </label>
                            
                            <div style="position: relative;">
                                <input type="text" class="form-control input-lg" id="buscar_documento" placeholder="Escriba aquí para buscar cliente..." autocomplete="off">
                                
                                <div id="lista_sugerencias_cliente" style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 1050; background: white; border: 1px solid #ccc; border-radius: 0 0 8px 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); max-height: 250px; overflow-y: auto;">
                                </div>
                            </div>
                            
                            <input type="text" class="form-control" style="border: none; background: transparent; padding-left: 0; font-weight: bold; color: #2980b9; box-shadow: none; margin-top: 5px; display: none;" id="nombre_cliente" readonly required>
                            <input type="hidden" name="clicod" id="clicod_seleccionado" required>
                            
                            <div id="mensaje_cliente" class="alert alert-warning" style="display: none; padding: 10px; margin-top: 10px; border-radius: 5px; overflow: hidden;">
                                <i class="fa fa-warning"></i> ¿Cliente nuevo? Registra sus datos aquí: 
                                <button type="button" class="btn btn-xs btn-dark btn-elegant pull-right" style="background-color: #2c3e50; color: white;" onclick="abrirModalCliente()">+ Crear Nuevo</button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 col-xs-12 form-group">
                                <label style="color: #666; font-size: 12px; text-transform: uppercase;"><i class="fa fa-calendar"></i> Fecha</label>
                                <input type="date" class="form-control" name="fecha_reserva" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-3 col-xs-6 form-group">
                                <label style="color: #666; font-size: 12px; text-transform: uppercase;"><i class="fa fa-clock-o"></i> Inicio</label>
                                <input type="time" class="form-control" name="hora_inicio" required>
                            </div>
                            <div class="col-md-3 col-xs-6 form-group">
                                <label style="color: #666; font-size: 12px; text-transform: uppercase;"><i class="fa fa-clock-o"></i> Fin</label>
                                <input type="time" class="form-control" name="hora_fin" required>
                            </div>
                            <div class="col-md-2 col-xs-12 form-group">
                                <label style="color: #666; font-size: 12px; text-transform: uppercase;"><i class="fa fa-users"></i> Cant.</label>
                                <input type="number" class="form-control text-center" name="cantidad_personas" value="1" min="1" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-xs-12 form-group">
                                <label style="color: #666; font-size: 12px; text-transform: uppercase;"><i class="fa fa-map-marker"></i> Zona / Piso</label>
                                <select class="form-control" name="pis_id" id="select_piso" required>
                                    <option value="">Seleccione zona...</option>
                                    @foreach($pisos as $piso)
                                        <option value="{{ $piso->pis_id }}">{{ $piso->pis_nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-xs-12 form-group">
                                <label style="color: #666; font-size: 12px; text-transform: uppercase;"><i class="fa fa-cutlery"></i> Mesa</label>
                                <select class="form-control" name="mes_id" id="select_mesa" disabled required>
                                    <option value="">Primero seleccione zona</option>
                                </select>
                            </div>
                        </div>

                        <hr style="border-top: 1px solid #ddd; margin: 10px 0 20px 0;">

                        <label style="color: #2c3e50; font-size: 14px; text-transform: uppercase; font-weight: bold;"><i class="fa fa-shopping-cart"></i> Productos Solicitados</label>
                        
                        <div class="table-responsive" style="background: white; border-radius: 8px; border: 1px solid #ddd; padding: 10px; margin-bottom: 15px;">
                            <table class="table table-hover" id="tabla_carrito" style="margin-bottom: 0;">
                                <thead>
                                    <tr style="border-bottom: 2px solid #2c3e50;">
                                        <th style="color: #666; font-size: 12px;">Producto</th>
                                        <th style="color: #666; font-size: 12px; text-align: center;">Precio</th>
                                        <th style="color: #666; font-size: 12px; text-align: center; width: 80px;">Cant.</th>
                                        <th style="color: #666; font-size: 12px; text-align: center;">Subtotal</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr style="background-color: #f9f9f9;">
                                        <td colspan="3" style="text-align: right; font-weight: bold; font-size: 16px;">TOTAL:</td>
                                        <td style="text-align: center; font-weight: bold; font-size: 18px; color: #27ae60;">
                                            S/ <span id="total_reserva_span">0.00</span>
                                        </td>
                                        <td><input type="hidden" name="total_reserva" id="total_reserva_input" value="0.00"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="form-group">
                            <label style="color: #666; font-size: 12px; text-transform: uppercase;"><i class="fa fa-pencil"></i> Observación General</label>
                            <textarea class="form-control" name="observacion_general" rows="2" placeholder="Ej: Celebración de cumpleaños, requiere silla de bebé..." style="resize: vertical;"></textarea>
                        </div>

                        <button type="submit" class="btn btn-success btn-block btn-lg btn-elegant" style="font-weight: bold;">
                            <i class="fa fa-print"></i> Guardar e Imprimir
                        </button>

                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xs-12">
            <div class="box shadow-box" style="background: transparent; box-shadow: none !important;">
                <div class="box-body" style="padding: 0;">
                    
                    <div class="input-group" style="margin-bottom: 15px;">
                        <span class="input-group-addon" style="background: white; border-radius: 8px 0 0 8px; border-right: none;"><i class="fa fa-search text-muted"></i></span>
                        <input type="text" id="buscador_productos" class="form-control input-lg" placeholder="Buscar plato o bebida..." style="border-radius: 0 8px 8px 0; border-left: none; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    </div>
                    
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 15px;">
                        <button class="cat-pill" style="background-color: #2c3e50; color: white;" onclick="filtrarCategoria('todas')">Todas</button>
                        @foreach($categorias as $cat)
                            <button class="cat-pill text-white" style="background-color: {{ $cat->color ?? '#6c757d' }};" onclick="filtrarCategoria({{ $cat->cat_id }})">
                                {{ $cat->cat_nom }}
                            </button>
                        @endforeach
                    </div>

                    <div class="row" id="contenedor_productos" style="max-height: 700px; overflow-y: auto; overflow-x: hidden; padding-right: 5px;">
                        @foreach($productos as $prod)
                            <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6 mb-3 producto-item" style="margin-bottom: 15px;" data-categoria="{{ $prod->cat_id }}" data-nombre="{{ strtolower($prod->pronom) }}">
                                <div class="producto-card p-2" style="cursor: pointer; min-height: 100px; padding: 10px; text-align: center;" onclick="agregarAlCarrito({{ $prod->IdProducto }}, '{{ addslashes($prod->pronom) }}', {{ $prod->propun }})" title="{{ $prod->pronom }}">
                                    <span class="nombre-producto">{{ $prod->pronom }}</span>
                                    <span class="precio-producto">S/ {{ number_format($prod->propun, 2) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="modalNuevoCliente" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header custom-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" style="color: white;">&times;</span>
                </button>
                <h4 class="modal-title" style="color: white; font-weight: bold;"><i class="fa fa-user-plus"></i> Registrar Cliente</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label style="color: #666; font-size: 12px; text-transform: uppercase;">DNI / RUC</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="modal_doc" placeholder="Número de documento">
                        <span class="input-group-btn">
                            <button class="btn btn-info btn-elegant" type="button" id="btn_api_peru" title="Buscar en RENIEC/SUNAT">
                                <i class="fa fa-cloud-download"></i> Buscar API
                            </button>
                        </span>
                    </div>
                    <small id="api_mensaje" class="form-text text-muted" style="display: block; margin-top: 5px;">Si no hay internet, digita los datos abajo.</small>
                </div>
                <div class="form-group">
                    <label style="color: #666; font-size: 12px; text-transform: uppercase;">Nombre / Razón Social</label>
                    <input type="text" class="form-control text-uppercase" id="modal_nom" placeholder="Escribir nombre...">
                </div>
                <div class="form-group">
                    <label style="color: #666; font-size: 12px; text-transform: uppercase;">Dirección (Opcional)</label>
                    <input type="text" class="form-control text-uppercase" id="modal_dir" placeholder="Escribir dirección...">
                </div>
            </div>
            <div class="modal-footer" style="background-color: #f8f9fa;">
                <button type="button" class="btn btn-default btn-elegant pull-left" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success btn-elegant" id="btn_guardar_cliente_ajax"><i class="fa fa-save"></i> Guardar y Seleccionar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    
    // REDIRECCIÓN TRAS GUARDAR LA RESERVA
    $('#form-reserva').on('submit', function(e) {
        if(this.checkValidity()) {
            setTimeout(function() {
                window.location.href = "{{ url('reservas/historial') }}"; 
            }, 500);
        }
    });

    // ==========================================
    // 1. BUSCADOR PREDICTIVO DE CLIENTE (DNI/NOMBRE)
    // ==========================================
    $('#buscar_documento').on('input', function() {
        let selectFlotante = $('#lista_sugerencias_cliente');
        let texto = $(this).val();

        if (texto.length < 2) {
            selectFlotante.hide().empty();
            $('#clicod_seleccionado').val('');
            $('#nombre_cliente').hide().val(''); // Se oculta
            $('#mensaje_cliente').hide();
            return;
        }

        $.get('/api/buscar-cliente', { term: texto }, function(data) {
            selectFlotante.empty();

            if (data.length > 0) {
                $('#mensaje_cliente').hide();
                $.each(data, function(index, cliente) {
                    let item = `
                        <div class="item-cliente-sugerido" 
                             data-id="${cliente.clicod}" 
                             data-nombre="${cliente.clinom}" 
                             data-doc="${cliente.clinum}"
                             style="padding: 10px 15px; cursor: pointer; border-bottom: 1px solid #f4f4f4; font-size: 13px; transition: background 0.2s;">
                            <i class="fa fa-user text-muted"></i> <strong>${cliente.clinum}</strong> - ${cliente.clinom}
                        </div>
                    `;
                    selectFlotante.append(item);
                });
                selectFlotante.show();
            } else {
                selectFlotante.hide();
                $('#clicod_seleccionado').val('');
                $('#nombre_cliente').hide().val('');
                $('#mensaje_cliente').show(); // Muestra el botón de "Crear Nuevo"
            }
        });
    });

    // Resaltar opciones de búsqueda
    $(document).on('mouseenter', '.item-cliente-sugerido', function() {
        $(this).css('background-color', '#3498db').css('color', 'white');
    }).on('mouseleave', '.item-cliente-sugerido', function() {
        $(this).css('background-color', 'white').css('color', '#333');
    });

    // SELECCIONAR CLIENTE DEL DESPLEGABLE
    $(document).on('click', '.item-cliente-sugerido', function() {
        let id = $(this).data('id');
        let nombre = $(this).data('nombre');
        let documento = $(this).data('doc');

        $('#clicod_seleccionado').val(id);
        $('#nombre_cliente').val(nombre).show(); // AQUÍ SE MUESTRA EL NOMBRE
        $('#buscar_documento').val(documento + " - " + nombre);

        $('#lista_sugerencias_cliente').hide().empty();
        $('#mensaje_cliente').hide();
    });

    $(document).click(function(e) {
        if (!$(e.target).closest('#buscar_documento, #lista_sugerencias_cliente').length) {
            $('#lista_sugerencias_cliente').hide();
        }
    });

    // ==========================================
    // 2. MODAL: BÚSQUEDA EXTERNA (API PERU)
    // ==========================================
    $('#btn_api_peru').click(function() {
        let doc = $('#modal_doc').val();
        if(doc.length !== 8 && doc.length !== 11) {
            alert("El documento debe tener 8 (DNI) o 11 (RUC) dígitos.");
            return;
        }

        let btn = $(this);
        btn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);
        $('#api_mensaje').text('Consultando en SUNAT/RENIEC...');

        $.get('/api/buscar-externa/' + doc, function(res) {
            btn.html('<i class="fa fa-cloud-download"></i> Buscar API').prop('disabled', false);
            
            if(res.success) {
                $('#api_mensaje').text('Datos encontrados con éxito.').css('color', 'green');
                if(doc.length == 8) {
                    $('#modal_nom').val(res.data.nombres + ' ' + res.data.apellido_paterno + ' ' + res.data.apellido_materno);
                } else {
                    $('#modal_nom').val(res.data.nombre_o_razon_social);
                    $('#modal_dir').val(res.data.direccion);
                }
            } else {
                $('#api_mensaje').text('No encontrado en API. Puede digitar manualmente.').css('color', 'red');
            }
        }).fail(function() {
            btn.html('<i class="fa fa-cloud-download"></i> Buscar API').prop('disabled', false);
            $('#api_mensaje').text('Error de conexión. Digite manualmente.').css('color', 'red');
        });
    });

    // ==========================================
    // 3. MODAL: GUARDAR CLIENTE (LA MAGIA AQUÍ)
    // ==========================================
    $('#btn_guardar_cliente_ajax').click(function() {
        let doc = $('#modal_doc').val();
        let nom = $('#modal_nom').val();
        let dir = $('#modal_dir').val();

        if(doc === '' || nom === '') { 
            alert("El número de documento y el Nombre son obligatorios."); 
            return; 
        }

        let btn = $(this);
        btn.html('<i class="fa fa-spinner fa-spin"></i> Guardando...').prop('disabled', true);

        $.ajax({
            url: '/api/guardar-cliente-ajax',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                documento: doc,
                nombre: nom,
                direccion: dir
            },
            success: function(res) {
                btn.html('<i class="fa fa-save"></i> Guardar y Seleccionar').prop('disabled', false);
                
                if(res.success) {
                    // AQUÍ ESTÁ LA CORRECCIÓN: Usamos .show() para que el input sea visible
                    $('#clicod_seleccionado').val(res.cliente.clicod);
                    $('#nombre_cliente').val(res.cliente.clinom).show(); 
                    
                    // Mostramos DNI y Nombre juntos para que se vea genial
                    $('#buscar_documento').val(res.cliente.clinum + " - " + res.cliente.clinom);
                    
                    // Ocultamos la alerta amarilla y cerramos la ventana
                    $('#mensaje_cliente').hide();
                    $('#modalNuevoCliente').modal('hide');
                }
            },
            error: function() {
                btn.html('<i class="fa fa-save"></i> Guardar y Seleccionar').prop('disabled', false);
                alert("Ocurrió un error al intentar guardar en la Base de Datos.");
            }
        });
    });

    // ==========================================
    // 4. CARGAR MESAS DEPENDIENDO DE LA ZONA
    // ==========================================
    $('#select_piso').change(function() {
        let pis_id = $(this).val();
        let selectMesa = $('#select_mesa');

        if(pis_id === '') {
            selectMesa.html('<option value="">Primero seleccione zona</option>').prop('disabled', true);
            return;
        }

        selectMesa.html('<option value="">Cargando mesas...</option>').prop('disabled', true);

        $.get('/api/mesas-por-piso/' + pis_id, function(data) {
            let opciones = '<option value="">Seleccione Mesa...</option>';
            $.each(data, function(index, mesa) {
                opciones += `<option value="${mesa.mes_id}">${mesa.mes_nom}</option>`;
            });
            selectMesa.html(opciones).prop('disabled', false);
        });
    });

    // ==========================================
    // 5. BUSCADOR RÁPIDO DE PRODUCTOS (GRILLA)
    // ==========================================
    $('#buscador_productos').on('keyup', function() {
        let texto = $(this).val().toLowerCase();
        $('.producto-item').each(function() {
            let nombreProd = $(this).data('nombre');
            $(this).toggle(nombreProd.includes(texto));
        });
    });

    // ==========================================
    // 6. ACTUALIZAR SUBTOTALES AL CAMBIAR CANTIDAD
    // ==========================================
    $(document).on('input', '.input-cantidad', function() {
        let id = $(this).data('id');
        recalcularSubtotal(id);
    });

}); // FIN DEL DOCUMENT.READY

// ==========================================
// FUNCIONES GLOBALES 
// ==========================================

function abrirModalCliente() {
    let docBuscado = $('#buscar_documento').val();
    $('#modal_doc').val(docBuscado);
    $('#modal_nom').val('');
    $('#modal_dir').val('');
    $('#api_mensaje').text('Si no hay internet, digita los datos abajo.').css('color', '#777');
    $('#modalNuevoCliente').modal('show');
}

function filtrarCategoria(id_cat) {
    if(id_cat === 'todas') {
        $('.producto-item').fadeIn(200);
    } else {
        $('.producto-item').hide();
        $(`.producto-item[data-categoria="${id_cat}"]`).fadeIn(200);
    }
}

function agregarAlCarrito(id, nombre, precio) {
    if ($(`#fila_prod_${id}`).length > 0) {
        let inputCant = $(`#cant_prod_${id}`);
        inputCant.val(parseInt(inputCant.val()) + 1);
        recalcularSubtotal(id);
        return;
    }

    // Doble fila para que la nota tenga todo el ancho (Mejora visual)
    let fila = `
        <tr id="fila_prod_${id}" style="border-top: 1px solid #ddd; background-color: #fff;">
            <td style="vertical-align: middle; font-weight: bold; color: #2c3e50; font-size: 14px;">
                <input type="hidden" name="productos[${id}][id]" value="${id}">
                <input type="hidden" name="productos[${id}][precio]" value="${precio}">
                ${nombre}
            </td>
            <td style="vertical-align: middle; text-align: center; color: #666; font-size: 13px;">
                S/ ${precio.toFixed(2)}
            </td>
            <td style="vertical-align: middle;">
                <input type="number" id="cant_prod_${id}" name="productos[${id}][cantidad]" class="form-control input-sm text-center input-cantidad" data-id="${id}" data-precio="${precio}" value="1" min="1" required style="border-radius: 4px; font-weight: bold;">
            </td>
            <td style="vertical-align: middle; text-align: center; font-weight: bold; color: #333; font-size: 14px;" class="subtotal-fila" id="subtotal_prod_${id}" data-subtotal="${precio}">
                S/ ${precio.toFixed(2)}
            </td>
            <td style="vertical-align: middle; text-align: right;">
                <button type="button" class="btn btn-xs text-danger" style="background: transparent; border: none; font-size: 18px; padding: 0;" onclick="eliminarDelCarrito(${id})"><i class="fa fa-times-circle"></i></button>
            </td>
        </tr>
        <tr id="fila_nota_${id}" style="background-color: #fff; border-bottom: 2px solid #ddd;">
            <td colspan="5" style="padding-top: 0; padding-bottom: 10px;">
                <div class="input-group">
                    <span class="input-group-addon" style="background-color: #f5f7fa; border-color: #ccd1d9; color: #555;"><i class="fa fa-pencil"></i> Indicación:</span>
                    <input type="text" name="productos[${id}][observacion]" class="form-control input-observacion" placeholder="Ej: Bien frito, sin ensalada, salsa aparte...">
                </div>
            </td>
        </tr>
    `;
    
    $('#tabla_carrito tbody').prepend(fila);
    actualizarTotalGeneral();
}

function recalcularSubtotal(id) {
    let cant = parseInt($(`#cant_prod_${id}`).val()) || 0;
    let precio = parseFloat($(`#cant_prod_${id}`).data('precio'));
    let subtotal = cant * precio;
    
    $(`#subtotal_prod_${id}`).data('subtotal', subtotal);
    $(`#subtotal_prod_${id}`).text(`S/ ${subtotal.toFixed(2)}`);
    
    actualizarTotalGeneral();
}

function eliminarDelCarrito(id) {
    $(`#fila_prod_${id}`).remove();
    $(`#fila_nota_${id}`).remove(); 
    actualizarTotalGeneral();
}

function actualizarTotalGeneral() {
    let total = 0;
    
    $('.subtotal-fila').each(function() {
        total += parseFloat($(this).data('subtotal'));
    });
    
    $('#total_reserva_span').text(total.toFixed(2));
    $('#total_reserva_input').val(total.toFixed(2));
}
</script>
@endsection