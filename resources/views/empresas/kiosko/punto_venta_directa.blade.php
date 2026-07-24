<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DEVSOFT - PUNTO DE VENTA</title>
    <link rel="shortcut icon" href="{{ asset('img/icono.ico') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/font-awesome6/css/all.min.css') }}">
    {{-- Necesitamos jQuery UI para el autocompletado por nombre --}}
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    
    <style>
        /* Estilos base (para pantallas grandes - "desktop-first" approach) */
        html { font-size: 14px; }
        body, html { height: 100%; margin: 0; padding: 0; overflow: hidden; font-family: 'Segoe UI', sans-serif; }
        
        .main-container-pos { 
            display: flex; 
            height: 100vh; /* Ocupa la altura completa del viewport */
            flex-direction: row; /* Por defecto, en fila (para desktop) */
        }

        .products-section { 
            width: 70%; 
            padding: 15px; 
            display: flex; 
            flex-direction: column; 
            background-color: #fff; 
            overflow-y: auto; /* Permite scroll vertical si los productos exceden la altura */
        }
        
        .sale-section { 
            width: 30%; 
            background-color: #f8f9fa; 
            border-left: 1px solid #ddd;
            display: flex; 
            flex-direction: column;
            height: 100vh; /* Asegura que la sección de venta también ocupe la altura completa */
            overflow-y: auto; /* Permite scroll vertical en la sección de venta */
        }
        
        .sale-section-content { flex-grow: 1; padding: 15px; } /* Eliminar overflow-y aquí, el padre lo maneja */
        .sale-summary { flex-shrink: 0; padding: 15px; border-top: 1px solid #ddd; background-color: #f1f1f1; }
        
        .header-pos { background-color: #2c3e50; color: white; padding: 10px; font-size: 1.4em; font-weight: bold; border-radius: 5px; margin-bottom: 15px; text-align: center; }
        
        /* Nuevo contenedor para los botones de categoría */
        .category-buttons-container-pos {
            display: flex;
            flex-wrap: wrap; /* Permite que los elementos se envuelvan */
            justify-content: flex-start; /* Alinea los elementos al inicio */
            gap: 1px; /* Espacio entre los botones */
            padding: 1px; /* Un poco de padding alrededor */
            margin-bottom: 1px;
            max-height: 80px; /* Limita la altura para evitar que ocupe demasiado espacio vertical */
            overflow-y: auto; /* Agrega scroll si los botones exceden la altura */
            overflow-x: hidden; /* Oculta el scroll horizontal si flex-wrap funciona */
        }

        .category-buttons-container-pos::-webkit-scrollbar {
            width: 8px; /* Ancho del scrollbar vertical */
            height: 8px; /* Altura del scrollbar horizontal (si flex-wrap se desborda) */
        }

        .category-buttons-container-pos::-webkit-scrollbar-thumb {
            background: #bdc3c7;
            border-radius: 4px;
        }

        /* Estilos para los botones de categoría */
        .btn-category-pos {
            flex-shrink: 0; /* Evita que los botones se encojan en flexbox */
            min-width: 100px; /* Ancho mínimo para que no sean demasiado estrechos */
            max-width: 180px; /* Ancho máximo para que no se estiren demasiado */
            height: 55px; /* Altura consistente para todos los botones */
            padding: 8px 15px;
            font-size: 1.0em; /* Tamaño de fuente ligeramente más grande */
            font-weight: bold;
            border-radius: 8px; /* Bordes más redondeados */
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s, opacity 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            white-space: normal; /* Permite que el texto se envuelva dentro del botón */
            word-break: break-word; /* Rompe palabras largas si es necesario */
            line-height: 1.2; /* Espaciado de línea para texto envuelto */
            border: 2px solid transparent; /* Borde transparente por defecto */
            color: white !important; /* Asegura el color blanco del texto */
        }

        .btn-category-pos:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
            opacity: 0.9;
            color: white !important; /* Mantiene el color del texto en hover */
        }

        .btn-category-pos.active {
            border: 3px solid #ffeb3b; /* Borde amarillo brillante para la categoría activa */
            box-shadow: 0 5px 15px rgba(0,0,0,0.25);
            transform: translateY(-1px); /* Ligeramente elevado */
            color: white !important; /* Mantiene el color del texto en activo */
        }
        
        .products-grid-container { flex-grow: 1; overflow-y: auto; padding: 5px; }
        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; }
        .product-card { border: 1px solid #e0e0e0; border-radius: 8px; text-align: center; cursor: pointer; transition: all 0.2s; background: #fff; overflow: hidden; display: flex; flex-direction: column; height: auto; min-height: 140px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); justify-content: flex-start; padding: 10px; }
        .product-card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); background-color: #f8f9fa; }
        
        /* === MOSTRAR IMÁGENES === */
        .product-card .img-container { 
            display: block !important; 
            width: 100%;
            height: 80px;
            margin-bottom: 8px;
        }
        .product-card img { 
            display: block !important; 
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 4px;
        }
        /* ============================================== */

        .product-card-body { padding: 5px; flex-grow: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; }
        .product-name { 
            font-weight: 700; 
            font-size: 1.05rem; /* Aumentado para mejor legibilidad */
            line-height: 1.3; 
            height: auto; /* Permitir altura automática */
            overflow: hidden; 
            margin-bottom: 8px;
            text-align: center;
            display: -webkit-box;
            -webkit-line-clamp: 2; /* Máximo 2 líneas */
            -webkit-box-orient: vertical;
            word-break: break-word;
        }
        .product-price { font-size: 1.35rem; font-weight: 700; color: #27ae60; }
        .product-card.out-of-stock { opacity: 0.6; cursor: not-allowed; background-color: #f9f9f9; }
        .stock-label { font-size: 0.8rem; color: #7f8c8d; }
        
        .cart-items { min-height: 150px; }
        .cart-item { 
            display: flex; 
            align-items: center; 
            margin-bottom: 8px; 
            padding: 8px; 
            border-bottom: 1px solid #f0f0f0; 
            background-color: #fff; 
            border-radius: 4px; 
        }
        .cart-item-name { 
            flex-grow: 1; 
            font-weight: 600; 
            font-size: 0.9rem; 
            margin-right: 10px;
            cursor: pointer; /* Hace que el nombre sea clickeable para editar precio */
        }
        .cart-item-qty { display: flex; align-items: center; }
        .cart-item-qty input { width: 45px; text-align: center; border: 1px solid #ccc; border-radius: 4px; margin: 0 5px; height: 28px; padding: 5px; }
        .cart-item-qty .btn { padding: 3px 8px; }
        .cart-item-total { font-weight: 700; min-width: 70px; text-align: right; }
        .btn-remove-item { background: none; border: none; color: #e74c3c; font-size: 1.2em; padding: 0 8px; }
        
        .total-display { font-size: 2em; font-weight: bold; color: #2c3e50; text-align: right; margin-bottom: 15px; }

        /* Estilos adicionales para los medios de pago */
        .payment-method-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
            border-bottom: 1px dotted #ccc;
        }
        .payment-method-item:last-child {
            border-bottom: none;
        }

        /* Estilos para el modal de edición de precio */
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        /* Estilos para el contenedor de sugerencias de autocompletado */
        .ui-autocomplete {
            max-height: 200px;
            overflow-y: auto;
            overflow-x: hidden;
            background-color: #fff;
            border: 1px solid #ddd;
            list-style: none;
            padding: 0;
            margin: 0;
            z-index: 1050; /* Asegura que esté sobre otros elementos */
            border-radius: 4px;
        }
        .ui-menu-item {
            padding: 8px 12px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        .ui-menu-item:last-child {
            border-bottom: none;
        }
        .ui-menu-item:hover, .ui-state-active {
            background-color: #f0f0f0;
            color: #333;
        }

        /* --- MEDIA QUERIES PARA RESPONSIVE DESIGN --- */

        /* Para pantallas pequeñas (móviles) */
        @media (max-width: 768px) {
            html { font-size: 12px; } /* Reduce el tamaño de fuente base para móvil */
            
            .main-container-pos {
                flex-direction: column; /* Apila las secciones verticalmente */
                height: auto; /* La altura se ajusta al contenido */
                overflow-y: auto; /* Permite scroll en toda la página si es necesario */
            }

            .products-section, .sale-section {
                width: 100%; /* Ocupan todo el ancho disponible */
                height: auto; /* Altura se ajusta al contenido */
                border-left: none;
                border-bottom: 1px solid #ddd; /* Separador entre secciones */
            }

            .sale-section {
                order: -1; /* Mueve la sección de venta al principio en móviles */
            }

            .products-section {
                padding-top: 10px;
                padding-bottom: 10px;
            }

            .sale-section-content, .sale-summary {
                padding: 10px; /* Reduce padding */
            }

            .header-pos {
                font-size: 1.2em; /* Título más pequeño */
                margin-bottom: 10px;
            }

            .category-buttons-container-pos {
                gap: 5px; /* Reduce el espacio entre botones */
                max-height: 120px; /* Puede ser un poco menos alto en móvil */
                padding: 5px 0; /* Ajusta padding para móviles */
            }

            .btn-category-pos {
                min-width: 80px; /* Ancho mínimo para móvil */
                max-width: 120px; /* Ancho máximo para móvil */
                height: 50px; /* Altura ajustada */
                font-size: 0.85em; /* Fuente más pequeña */
                padding: 6px 10px;
                border-radius: 6px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }

            .btn-category-pos.active {
                border-width: 2px; /* Borde más delgado en móvil */
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); /* Más columnas pequeñas */
                gap: 10px; /* Menos espacio entre productos */
                padding: 5px;
            }

            .product-card {
                height: auto; min-height: 130px; /* Altura para tarjetas con imagen */
                padding: 8px;
                border-radius: 6px;
            }

            .product-card .img-container {
                display: block !important; 
            }
            .product-name {
                font-size: 0.95rem; /* Texto legible en móvil */
                font-weight: 700;
            }
            .product-price {
                font-size: 1.2rem; /* Precio más visible */
            }

            .cart-item {
                flex-wrap: wrap; /* Permite que los elementos del ítem se envuelvan */
                padding: 6px;
                margin-bottom: 6px;
            }
            .cart-item-name {
                flex-basis: 100%; /* Nombre ocupa toda la línea */
                font-size: 0.95rem;
                margin-bottom: 5px;
            }
            .cart-item-qty {
                flex-basis: auto; /* Permite que los controles de cantidad se ajusten */
            }
            .cart-item-qty input {
                width: 40px; /* Input de cantidad más pequeño */
                height: 25px;
                padding: 3px;
            }
            .cart-item-qty .btn {
                padding: 2px 6px;
                font-size: 0.9em;
            }
            .cart-item-total {
                font-size: 1.0rem;
                min-width: 60px; /* Ajusta el ancho mínimo */
                margin-left: auto; /* Empuja el total a la derecha */
            }
            .btn-remove-item {
                position: absolute; /* Permite posicionar el botón en la esquina */
                top: 5px;
                right: 5px;
            }
        }

        /* Para pantallas muy pequeñas (smartphones en modo vertical) */
        @media (max-width: 480px) {
            html { font-size: 11px; } /* Tamaño de fuente aún más pequeño */
            
            .products-section, .sale-section {
                padding: 8px;
            }

            .btn-category-pos {
                min-width: 70px;
                max-width: 100px;
                height: 45px;
                font-size: 0.8em;
                padding: 5px 8px;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); /* 2 o 3 columnas muy pequeñas */
                gap: 8px;
            }

            .product-card {
                height: auto; min-height: 120px; 
            }
            .product-card .img-container {
                display: block !important; 
            }
            .product-name {
                font-size: 0.9rem; /* Mantener legible incluso en pantallas pequeñas */
                font-weight: 700;
            }
            .product-price {
                font-size: 1.1rem; /* Precio visible */
            }

            .cart-item {
                flex-direction: column; /* Apila los elementos del carrito para aprovechar el espacio */
                align-items: flex-start; /* Alinea a la izquierda */
                padding: 5px;
            }
            .cart-item-name {
                width: 100%;
                margin-bottom: 3px;
            }
            .cart-item-qty {
                width: 100%;
                justify-content: flex-start;
                margin-bottom: 5px;
            }
            .cart-item-total {
                width: 100%;
                text-align: left;
                margin-left: 0;
            }
            .btn-remove-item {
                position: absolute; /* Permite posicionar el botón en la esquina */
                top: 5px;
                right: 5px;
            }
        }
    </style>
</head>
<body>

<div class="main-container-pos">
    <div class="products-section">
        <div class="header-pos">Sistema de Gestión Comercial - DEVSOFT</div>
        <a href="/caja" class="btn btn btn-md btn-primary">
            <strong>CERRAR</strong>
        </a>
        
        <div class="input-group" style="margin-bottom: 1rem;">
            <span class="input-group-addon"><i class="fa fa-search"></i></span>
            <input type="text" id="product-search" class="form-control" placeholder="Buscar producto...">
        </div>
        <div class="products-grid-container">
            <div id="products-grid"></div>
        </div>
    </div>
    
    <div class="sale-section">
        <div class="sale-section-content">
            <form id="form-direct-sale" autocomplete="off">
                @csrf
                <button type="submit" class="btn btn-success btn-lg btn-block" id="btn-register-sale"><i class="fa fa-check-circle"></i> IMPRIMIR</button>
                <h4>Detalles de la Venta</h4>
                <div class="form-group"><label for="tdocod">Tipo de Comprobante</label><select name="tdocod" id="tdocod" class="form-control">@foreach($comprobantes as $comp)<option value="{{ $comp->tdocod }}" {{ ($negocio->tdocod_pred ?? '') == $comp->tdocod ? 'selected' : '' }}>{{ $comp->tdodes }}</option>@endforeach</select></div>
                
                {{-- Sección para la búsqueda de clientes (INICIO) --}}
                <div class="form-group">
                    <label>DNI / RUC</label>
                    <div class="input-group input-group-sm">
                        <input name="clinum" id="clinum" value="00000000" class="form-control" placeholder="Buscar DNI/RUC">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-primary btn-flat" id="search-client-by-num-btn"><span class="fa fa-search"></span></button>
                            </span>
                    </div>
                    <div id="mensaje_cliente_num" class="alert alert-warning mt-3" style="display: none;"></div>
                </div>

                <div class="form-group">
                    <label>Nombre / Razón Social</label>
                    {{-- Este campo ahora tendrá autocompletado --}}
                    <input type="text" id="clinom" name="clinom" class="form-control" value="VENTA AL PORTADOR" required>
                    <div id="mensaje_cliente_nom" class="alert alert-warning mt-3" style="display: none;"></div>
                </div>

                <div class="form-group">
                    <label>Dirección</label>
                    <input type="text" id="clidir" name="clidir" class="form-control" value="--" required>
                </div>

                {{-- Campos ocultos para TDICOD, correo y teléfono --}}
                <input type="hidden" name="tdicod" id="tdicod" value="1">
                <input type="hidden" name="clicor" id="clicor" value="">
                <input type="hidden" name="clitel" id="clitel" value="">
                {{-- Sección para la búsqueda de clientes (FIN) --}}

                <div id="cart" class="cart-items"><p class="text-center text-muted" style="margin-top: 1rem;">Agregue productos</p></div>
            </form>
        </div>
        <div class="sale-summary">
            <div class="total-display">TOTAL: S/ <span id="cart-total">0.00</span></div>
            
            {{-- SECCIÓN DE MEDIOS DE PAGO SIMILAR A COBRAR_MESA (INICIO) --}}
            <div class="row">
                <div class="col-xs-12">
                    <div class="form-group">
                        <label for="med_pag_select">Medios de Pago</label>
                        <select class="form-control" name="med_pag_select" id="med_pag_select">
                            @foreach($mediospagos as $medpag)
                                <option value="{{$medpag->id_med_pag}}" data-nom="{{$medpag->nom_med_pag}}" data-predeterminado="{{$medpag->predeterminado}}">{{$medpag->nom_med_pag}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <div class="form-group">
                        <div class="input-group">
                            <input name="monto_medio_pago_input" id="monto_medio_pago_input" value="0.00" class="form-control" type="number" step="0.01">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-primary btn-flat" id="add-payment-method-btn"><i class="fa fa-plus-square"></i> Agregar Pago</button>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <div id="payment-methods-list">
                        {{-- Los medios de pago agregados se renderizarán aquí --}}
                    </div>
                </div>
            </div>
            {{-- SECCIÓN DE MEDIOS DE PAGO (FIN) --}}

            <div class="row">
                <div class="col-xs-6">
                    <div class="form-group">
                        <label for="paga_con">Paga con:</label>
                        <input type="number" step="0.01" id="paga_con" class="form-control" placeholder="0.00"> {{-- Se añade readonly aquí --}}
                    </div>
                </div>
                <div class="col-xs-6">
                    <h4 class="text-right" style="margin-top:0;">Vuelto: S/ <strong id="vuelto_display">0.00</strong></h4>
                </div>
            </div>
            
            <input type="hidden" name="total_venta" id="total_venta_hidden" form="form-direct-sale">
            <input type="hidden" name="vuelto" id="vuelto_hidden" form="form-direct-sale">
            <input type="hidden" name="fecEmi" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" form="form-direct-sale">
            <input type="hidden" name="fecVen" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" form="form-direct-sale"> {{-- Se usa fecEmi si no hay vencimiento específico --}}
            <input type="hidden" name="estadopago" value="1" form="form-direct-sale"> {{-- Valor por defecto para CONTADO --}}
            <input type="hidden" name="icbper_val" id="icbper_val" value="{{ $icbper_val ?? 0 }}" form="form-direct-sale">
            <input type="hidden" name="clicod" id="clicod_hidden_form" class="form-control" form="form-direct-sale">

            {{-- Añadir campos ocultos para la configuración del negocio --}}
            <input type="hidden" id="negocio_formato_impresion" value="{{ $negocio->formato ?? 'TICKET' }}">
            <input type="hidden" id="negocio_ticket_pantalla" value="{{ $negocio->ticket_pantalla ?? '0' }}">
            
        </div>
    </div>
</div>

{{-- Modal para editar precio del ítem --}}
<div class="modal fade" id="editPriceModal" tabindex="-1" role="dialog" aria-labelledby="editPriceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="editPriceModalLabel">Editar Precio de <span id="modal-product-name"></span></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="new-price-input">Nuevo Precio:</label>
                    <input type="number" step="0.01" class="form-control" id="new-price-input">
                    <input type="hidden" id="modal-product-id">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="save-new-price-btn">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('adminlte/bower_components/jquery/dist/jquery.min.js') }}"></script>
{{-- Incluir jQuery UI para el autocompletado --}}
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script> 
<script src="{{ asset('adminlte/bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let cart = {};
    let paymentMethods = {}; 
    const ICBPER_VALUE = parseFloat("{{ $icbper_val ?? 0 }}");
    const NEGOCIO_FORMATO_IMPRESION = $('#negocio_formato_impresion').val(); // 'TICKET', 'A4', etc.
    const NEGOCIO_TICKET_PANTALLA = $('#negocio_ticket_pantalla').val(); // '0' o '1'

    // --- Funciones de control de la vista ---

    function renderCart() {
        const cartContainer = $('#cart');
        cartContainer.empty();
        let total = 0;
        
        if (Object.keys(cart).length === 0) {
            cartContainer.html('<p class="text-center text-muted" style="margin-top:20px;">Agregue productos a la venta</p>');
        } else {
            for (const id in cart) {
                const item = cart[id];
                const itemTotal = item.cantidad * item.precio + (item.icbper == 1 ? item.cantidad * ICBPER_VALUE : 0);
                total += itemTotal;
                
                const cartItemHtml = `<div class="cart-item" data-id="${item.id}">
                                        <div class="cart-item-name" data-id="${item.id}" data-name="${item.nombre}" data-price="${item.precio}">${item.nombre}</div>
                                        <div class="cart-item-qty">
                                            <button type="button" class="btn btn-default btn-xs btn-qty-minus"><i class="fa fa-minus"></i></button>
                                            <input type="number" class="form-control input-sm cart-item-qty-input" value="${item.cantidad}" min="1" data-stock="${item.stock}">
                                            <button type="button" class="btn btn-default btn-xs btn-qty-plus"><i class="fa fa-plus"></i></button>
                                        </div>
                                        <div class="cart-item-total">S/ ${itemTotal.toFixed(2)}</div>
                                        <button type="button" class="btn-remove-item"><i class="fa fa-trash"></i></button>
                                    </div>`;
                cartContainer.append(cartItemHtml);
            }
        }
        $('#cart-total').text(total.toFixed(2));
        $('#total_venta_hidden').val(total.toFixed(2));
        handlePaymentUpdates(); // Llama a la función centralizada para pagos
    }

    // Función centralizada para manejar la lógica de auto-llenado y actualización de pagos
    function handlePaymentUpdates() {
        const totalVenta = parseFloat($('#total_venta_hidden').val()) || 0;
        const efectivoOption = $('#med_pag_select option[data-predeterminado="1"]');
        const efectivoId = efectivoOption.val();

        // Lógica para auto-llenar "Efectivo" si no hay otros pagos y el total es > 0
        if (efectivoOption.length > 0) {
            // Si no hay ningún medio de pago agregado, O si el único que hay es Efectivo
            const isEfectivoOnly = Object.keys(paymentMethods).length === 0 || 
                                   (Object.keys(paymentMethods).length === 1 && paymentMethods[efectivoId]);
            
            if (isEfectivoOnly) {
                if (totalVenta > 0) {
                    paymentMethods = {}; // Limpia para asegurar que solo quede Efectivo si se va a auto-llenar
                    paymentMethods[efectivoId] = { 
                        id: efectivoId, 
                        nombre: efectivoOption.data('nom') || "EFECTIVO", 
                        monto: totalVenta, 
                        predeterminado: '1' // Mantener la propiedad si es útil
                    };
                } else {
                    paymentMethods = {}; // Si el total es 0 (carrito vacío), limpiar pagos
                }
            }
        }

        // Siempre calcular el total de los pagos agregados
        let totalPagadoConMedios = 0;
        for (const id in paymentMethods) {
            totalPagadoConMedios += paymentMethods[id].monto;
        }
        $('#paga_con').val(totalPagadoConMedios.toFixed(2));
        
        renderPaymentMethodsList(); // Actualiza la lista HTML de medios de pago
        calculateChange(); // Calcula y muestra el vuelto
    }

    function calculateChange() {
        const total = parseFloat($('#total_venta_hidden').val()) || 0;
        const paid = parseFloat($('#paga_con').val()) || 0;
        let change = (paid > 0 && paid >= total) ? paid - total : 0;
        $('#vuelto_display').text(change.toFixed(2));
        $('#vuelto_hidden').val(change.toFixed(2));
    }

    // Esta función solo se encarga de renderizar la lista HTML de medios de pago
    function renderPaymentMethodsList() {
        const listContainer = $('#payment-methods-list');
        listContainer.empty(); // Limpia la lista HTML

        if (Object.keys(paymentMethods).length === 0) {
            listContainer.html('<p class="text-center text-muted" style="margin-top:1rem;">No se han agregado medios de pago.</p>');
        } else {
            for (const id in paymentMethods) {
                const method = paymentMethods[id];
                const itemHtml = `
                    <div class="payment-method-item" data-id="${method.id}">
                        <span>${method.nombre}: S/ ${method.monto.toFixed(2)}</span>
                        <button type="button" class="btn btn-danger btn-xs remove-payment-method-btn"><i class="fa fa-trash"></i></button>
                    </div>
                `;
                listContainer.append(itemHtml);
            }
        }
    }

    function loadProducts(categoryId = 0, searchText = '') {
        const container = $('#products-grid');
        container.html('<div style="display:flex; justify-content:center; align-items:center; height:100%;"><i class="fa fa-spinner fa-spin fa-3x"></i></div>');
        
        $.ajax({
            url: "{{ route('kiosko.search_products_directa') }}", 
            type: "GET",
            data: { category_id: categoryId, search_text: searchText },
            dataType: 'json',
            success: function(response) {
                container.html(response.vista);
            },
            error: function() { container.html('<p class="text-danger">Error al cargar productos.</p>'); }
        });
    }

    function resetSaleForm() {
        cart = {};
        paymentMethods = {}; // Limpiar todos los medios de pago para la nueva venta
        $('#form-direct-sale')[0].reset();
        $('#clinum').val('00000000'); 
        $('#clinom').val('VENTA AL PORTADOR');
        $('#clidir').val('--');
        $('#tdicod').val('1'); 
        $('#clicod_hidden').val(''); 
        $('#clicod_hidden_form').val(''); /* Nuevo: limpia el hidden para el formulario */
        $('#clicor').val(''); 
        $('#clitel').val(''); 
        
        $('#tdocod').val('{{ $negocio->tdocod_pred ?? "03" }}').trigger('change');
        $('#paga_con').val('0.00'); 
        $('#monto_medio_pago_input').val('0.00'); 

        // Inicializa la lista de pagos para que handlePaymentUpdates pueda auto-llenar Efectivo
        // handlePaymentUpdates() es llamado por renderCart(), por lo que al resetear, el flujo es correcto.
        renderCart(); 
    }

    // --- FUNCIÓN DE BÚSQUEDA DE CLIENTES (DNI/RUC) ---
    let clientNumSearchTimeout;
    function buscarclienterucPorNumero(){
        const query = $("#clinum").val();
        clearTimeout(clientNumSearchTimeout); 

        if (query.length < 2 && query !== '00000000') {
            $('#mensaje_cliente_num').hide();
            return;
        }

        if (query.length >= 2 || query === '00000000') {
            Swal.fire({
                title: 'Buscando cliente...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
        }
        
        clientNumSearchTimeout = setTimeout(() => {
            $.ajax({
                type: "GET", 
                dataType: 'json',
                url: '/autocomplete/' + query, // Ruta que consulta DNI/RUC en backend
                data: {field: 'clinum', query: query},
                success: function(respuesta){
                    Swal.close(); 
                    $('#mensaje_cliente_num').hide();

                    if(respuesta && respuesta.length > 0) {
                        var cliente = respuesta[0]; // Tomamos el primer resultado
                        $('#clinum').val(cliente.num || query); // El número de documento
                        $('#clinom').val(cliente.nom || 'VENTA AL PORTADOR');
                        $('#clidir').val(cliente.dir || '--');
                        $('#clicor').val(cliente.cor || ''); 
                        $('#clitel').val(cliente.tel || ''); 
                        $('#clicod_hidden').val(cliente.clicod || ''); 
                        $('#clicod_hidden_form').val(cliente.clicod || ''); /* Nuevo: para el form global */

                        $("#tdicod").val(cliente.tdicod).trigger('change'); 

                        if(cliente.tdicod === '6'){ 
                            $("#tdocod").val('01').trigger('change'); 
                        } else if (cliente.tdicod === '1' || cliente.tdicod === '4' || cliente.tdicod === '7') { 
                             if ($("#tdocod").val() === '13' || "{{ $negocio->tdocod_pred ?? '03' }}" === '03') {
                                $("#tdocod").val('03').trigger('change'); 
                            }
                        }
                    } else {
                        $('#mensaje_cliente_num').text('No encontrado. Verifique DNI/RUC o complete manualmente.').show();
                        if (query !== '00000000') {
                            $('#clinom').val('VENTA AL PORTADOR');
                            $('#clidir').val('--');
                            $('#clicod_hidden').val('');
                            $('#clicod_hidden_form').val(''); /* Nuevo: limpia el hidden para el form */
                            $('#tdicod').val('1').trigger('change');
                            $('#clicor').val('');
                            $('#clitel').val('');
                        }
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    Swal.close();
                    console.error("Error en AJAX (DNI/RUC):", textStatus, errorThrown, jqXHR.responseText);
                    Swal.fire('Error', 'Error al buscar por DNI/RUC.', 'error');
                    $('#mensaje_cliente_num').text('Error de conexión al buscar DNI/RUC.').show();
                }
            });
        }, 500); 
    }

    // --- FUNCIÓN DE BÚSQUEDA DE CLIENTES (Nombre/Razón Social) con Autocomplete UI ---
    $(function() {
        $("#clinom").autocomplete({
            minLength: 2, 
            source: function(request, response) {
                $.ajax({
                    url: '/autocomplete/' + encodeURIComponent(request.term), 
                    dataType: "json",
                    data: { field: 'clinom', query: request.term },
                    success: function(data) {
                        $('#mensaje_cliente_num').hide(); 
                        $('#mensaje_cliente_nom').hide();

                        if (data.length === 0 && request.term.length > 0) {
                             $('#mensaje_cliente_nom').text('No se encontraron clientes con ese nombre.').show();
                        }
                        
                        // AQUÍ ESTABA EL DETALLE: Aseguramos leer 'clinom' y 'clinum' de tu base de datos
                        response($.map(data, function(item) {
                            let nombreStr = item.clinom || item.nom || 'Sin Nombre';
                            let numStr = item.clinum || item.num || 'Sin RUC/DNI';
                            
                            return {
                                label: nombreStr + ' (' + numStr + ')', 
                                value: nombreStr, 
                                full_data: item 
                            };
                        }));
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error("Error en AJAX (Nombre/Razón Social):", textStatus, errorThrown, jqXHR.responseText);
                        $('#mensaje_cliente_nom').text('Error al buscar por nombre.').show();
                        response([]);
                    }
                });
            },
            select: function(event, ui) {
                const cliente = ui.item.full_data;
                
                // AQUÍ RELLENAMOS LOS CAMPOS CON LOS NOMBRES EXACTOS DE TU TABLA SQL
                $('#clinum').val(cliente.clinum || cliente.num || '00000000'); 
                $('#clinom').val(cliente.clinom || cliente.nom || 'VENTA AL PORTADOR');
                $('#clidir').val(cliente.clidir || cliente.dir || '--');
                
                // Extras
                $('#clicor').val(cliente.clicor || cliente.cor || ''); 
                $('#clitel').val(cliente.telefono || cliente.tel || ''); // En tu SQL vi que se llama "telefono"
                $('#clicod_hidden').val(cliente.clicod || ''); 
                $('#clicod_hidden_form').val(cliente.clicod || ''); 

                $("#tdicod").val(cliente.tdicod).trigger('change'); 

                if(cliente.tdicod === '6'){ 
                    $("#tdocod").val('01').trigger('change'); 
                } else if (cliente.tdicod === '1' || cliente.tdicod === '4' || cliente.tdicod === '7') { 
                    if ($("#tdocod").val() === '13' || "{{ $negocio->tdocod_pred ?? '03' }}" === '03') {
                        $("#tdocod").val('03').trigger('change'); 
                    }
                }
                $('#mensaje_cliente_num').hide();
                $('#mensaje_cliente_nom').hide();
            }
        });
    });
    // --- FIN FUNCIÓN DE BÚSQUEDA DE CLIENTES (Nombre/Razón Social) ---

    // Función para limpiar la información del cliente
    function limpiarClienteInfo(keepDniRuc = false) {
        if (!keepDniRuc) {
            $('#clinum').val('00000000');
        }
        $('#clinom').val('VENTA AL PORTADOR');
        $('#clidir').val('--');
        $('#clicod_hidden').val('');
        $('#clicod_hidden_form').val(''); /* Nuevo: limpia el hidden para el form */
        $('#tdicod').val('1').trigger('change');
        $('#clicor').val('');
        $('#clitel').val('');
        $('#mensaje_cliente_num').hide();
        $('#mensaje_cliente_nom').hide();
        // Restablecer el tipo de comprobante al predeterminado del negocio
        $('#tdocod').val('{{ $negocio->tdocod_pred ?? "03" }}').trigger('change');
    }


    $(document).ready(function() {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
        //loadProducts($('.btn-category-pos.active').data('id')); CARGA ID DE LA CATEGORIA EN LA BUSQUEDA DE PRODUCTOS
        resetSaleForm(); // Asegura que el formulario esté limpio y los pagos inicializados


        // Evento para buscar cliente por DNI/RUC al hacer clic en el botón de búsqueda
        $('#search-client-by-num-btn').on('click', function() {
            buscarclienterucPorNumero();
        });

        // Evento para el campo DNI/RUC al soltar una tecla (o al pegar)
        /*$('#clinum').on('keyup', function() {
            const queryVal = $(this).val();
            if (queryVal.length === 8 || queryVal.length === 11 || queryVal.length === 0 || queryVal === '00000000') {
                buscarclienterucPorNumero();
            } else {
                $('#mensaje_cliente_num').hide(); 
            }
        });*/


        // Evento de clic para los botones de categoría
        $('.category-buttons-container-pos').on('click', '.btn-category-pos', function() {
            $('.btn-category-pos').removeClass('active btn-primary').addClass('btn-default');
            $(this).addClass('active btn-primary').removeClass('btn-default');
            $('#product-search').val(''); // Limpia la búsqueda
            loadProducts($(this).data('id')); // Carga productos de la categoría seleccionada
        });
        
        let searchTimeout;
        $('#product-search').on('keyup', function() {
            clearTimeout(searchTimeout);
            const searchText = $(this).val();
            searchTimeout = setTimeout(() => { loadProducts(0, searchText); }, 300);
        });
        
        // Clic en tarjeta de producto para añadir al carrito
        $(document).on('click', '.product-card:not(.out-of-stock)', function() {
            const item = {
                id: $(this).data('id'),
                nombre: $(this).data('name'),
                precio: parseFloat($(this).data('price')),
                icbper: parseInt($(this).data('icbper')),
                stock: parseFloat($(this).data('stock')),
            };
            let currentCartQuantity = cart[item.id] ? cart[item.id].cantidad : 0;
            const newQuantityAfterAdd = currentCartQuantity + 1;

            if ({{ $negocio->ven_sin_sto ?? 0 }} == 0 && item.stock !== null && newQuantityAfterAdd > item.stock) {
                Swal.fire('Stock Insuficiente', `Solo hay ${item.stock} unidades disponibles de ${item.nombre}.`, 'warning');
                return;
            }

            if (cart[item.id]) {
                cart[item.id].cantidad++;
            } else {
                cart[item.id] = { ...item, cantidad: 1 };
            }
            renderCart();
        });

        // Controles de cantidad en el carrito (+/-)
        $(document).on('click', '.btn-qty-plus', function() {
            const id = $(this).closest('.cart-item').data('id');
            const currentItem = cart[id];
            const newQuantity = currentItem.cantidad + 1;

            if ({{ $negocio->ven_sin_sto ?? 0 }} == 0 && currentItem.stock !== null && newQuantity > currentItem.stock) {
                Swal.fire('Stock Insuficiente', `Solo hay ${currentItem.stock} unidades disponibles de ${currentItem.nombre}.`, 'warning');
                return;
            }
            currentItem.cantidad = newQuantity;
            renderCart();
        });

        $(document).on('click', '.btn-qty-minus', function() {
            const id = $(this).closest('.cart-item').data('id');
            if (cart[id].cantidad > 1) {
                cart[id].cantidad--;
            } else {
                delete cart[id];
            }
            renderCart();
        });

        // Botón de eliminar ítem del carrito
        $(document).on('click', '.btn-remove-item', function() {
            const id = $(this).closest('.cart-item').data('id');
            Swal.fire({
                title: '¿Eliminar producto?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    delete cart[id];
                    renderCart();
                    Swal.fire('Eliminado!', 'El producto ha sido eliminado del carrito.', 'success');
                }
            });
        });

        // Cambio manual de cantidad en el input del carrito
        $(document).on('change keyup', '.cart-item-qty-input', function() {
            const id = $(this).closest('.cart-item').data('id');
            let newQty = parseInt($(this).val());
            const currentItem = cart[id];

            if (isNaN(newQty) || newQty < 1) {
                newQty = 1;
                $(this).val(1);
            }

            if ({{ $negocio->ven_sin_sto ?? 0 }} == 0 && currentItem.stock !== null && newQty > currentItem.stock) {
                Swal.fire('Stock Insuficiente', `Solo puedes seleccionar ${currentItem.stock} unidades de ${currentItem.nombre}.`, 'warning');
                newQty = currentItem.stock; 
                $(this).val(newQty);
            }
            
            cart[id].cantidad = newQty;
            renderCart();
        });
        
        // Cálculo de vuelto al cambiar el campo "Paga con"
        $('#paga_con').on('keyup change', calculateChange);

        // Al hacer clic en "Agregar Pago"
        $('#add-payment-method-btn').on('click', function() {
            const selectedMedioId = $('#med_pag_select').val();
            const selectedMedioName = $('#med_pag_select option:selected').data('nom');
            let monto = parseFloat($('#monto_medio_pago_input').val());

            if (isNaN(monto) || monto <= 0) {
                Swal.fire('Monto Inválido', 'Por favor, ingrese un monto mayor a cero.', 'warning');
                return;
            }

            // Si el medio de pago ya existe en la lista, sumar el monto
            if (paymentMethods[selectedMedioId]) {
                paymentMethods[selectedMedioId].monto += monto;
            } else {
                // Si no existe, agregarlo como un nuevo medio de pago
                paymentMethods[selectedMedioId] = {
                    id: selectedMedioId,
                    nombre: selectedMedioName,
                    monto: monto
                };
            }
            handlePaymentUpdates(); // Vuelve a calcular y renderizar la lista
            $('#monto_medio_pago_input').val('0.00'); // Limpiar el input de monto
        });

        // Eliminar medio de pago de la lista
        $(document).on('click', '.remove-payment-method-btn', function() {
            const idToRemove = $(this).closest('.payment-method-item').data('id');
            delete paymentMethods[idToRemove];
            handlePaymentUpdates(); // Vuelve a calcular y renderizar la lista
        });

        // Evento doble clic en el nombre del item del carrito para editar precio
        $(document).on('dblclick', '.cart-item-name', function() {
            const productId = $(this).data('id');
            const productName = $(this).data('name');
            const currentPrice = parseFloat($(this).data('price'));

            $('#modal-product-name').text(productName);
            $('#new-price-input').val(currentPrice.toFixed(2));
            $('#modal-product-id').val(productId);
            $('#editPriceModal').modal('show');
        });

        // Guardar nuevo precio desde el modal
        $('#save-new-price-btn').on('click', function() {
            const productId = $('#modal-product-id').val();
            const newPrice = parseFloat($('#new-price-input').val());

            if (isNaN(newPrice) || newPrice < 0) {
                Swal.fire('Error', 'Por favor, ingrese un precio válido.', 'error');
                return;
            }

            if (cart[productId]) {
                cart[productId].precio = newPrice;
                // Actualizamos el data-price en el elemento HTML para que al volver a editar, tome el nuevo valor
                $(`.cart-item-name[data-id="${productId}"]`).data('price', newPrice);
                renderCart();
                $('#editPriceModal').modal('hide');
            } else {
                Swal.fire('Error', 'Producto no encontrado en el carrito.', 'error');
            }
        });


        $('#form-direct-sale').on('submit', function(e) {
            e.preventDefault();
            if (Object.keys(cart).length === 0) {
                Swal.fire('Carrito Vacío', 'Agregue productos para registrar la venta.', 'warning');
                return;
            }

            if (Object.keys(paymentMethods).length === 0) {
                Swal.fire('Medio de Pago Requerido', 'Por favor, agregue al menos un medio de pago.', 'warning');
                return;
            }

            const totalVenta = parseFloat($('#total_venta_hidden').val()) || 0;
            let totalPagado = 0;
            for (const id in paymentMethods) {
                totalPagado += paymentMethods[id].monto;
            }

            // Validación estricta: el monto pagado debe ser igual o mayor al total de la venta
            if (totalPagado < totalVenta) {
                Swal.fire('Monto Insuficiente', 'El monto pagado es menor al total de la venta.', 'warning');
                return;
            }
            // Opcional: Si no quieres permitir pagar mucho de más sin motivo,
            // puedes hacer una validación de igualdad estricta o con un pequeño margen.
            if (Math.abs(totalPagado - totalVenta) > 0.01) { // Permite una pequeña diferencia por redondeo
                Swal.fire('Descuadre de Pagos', `La suma de los pagos (${totalPagado.toFixed(2)}) no coincide con el Total de la venta (${totalVenta.toFixed(2)}). Por favor, ajuste los montos.`, 'warning');
                return;
            }
            
            const btn = $('#btn-register-sale');
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Procesando...');
            let formData = $(this).serializeArray();
            
            Object.values(cart).forEach(item => {
                formData.push({name: "txt_id_producto[]", value: item.id});
                formData.push({name: "txt_cantidad[]", value: item.cantidad});
                formData.push({name: "precios[]", value: item.precio}); // Usar el precio potencialmente modificado
                formData.push({name: "descripcion[]", value: item.nombre}); 
                formData.push({name: "item_obs[]", value: item.observaciones || ''});
                formData.push({name: "icbper_ind[]", value: item.icbper}); 
            });

            // Recopilar medios de pago, asegurando que solo se envíen los que tengan monto > 0
            const finalPaymentMethods = Object.values(paymentMethods).filter(method => method.monto > 0);
            
            if (finalPaymentMethods.length === 0) {
                 Swal.fire('Medio de Pago Requerido', 'El monto total en medios de pago es cero. Por favor, especifique un pago.', 'warning');
                 btn.prop('disabled', false).html('<i class="fa fa-check-circle"></i> REGISTRAR VENTA');
                 return;
            }

            // Aquí se envían los múltiples medios de pago
            finalPaymentMethods.forEach(method => {
                formData.push({name: "id_med_pag[]", value: method.id});
                formData.push({name: "mon_med_pag[]", value: method.monto});
            });

            formData.push({name: "paga", value: $('#paga_con').val()}); 
            formData.push({name: "vuelto", value: $('#vuelto_hidden').val()});
            formData.push({name: "mozo", value: {{ Auth::user()->IdUsuario }} }); 


            $.ajax({
                url: "{{ route('kiosko.registrar_venta_directa') }}", 
                type: "POST", 
                data: $.param(formData),
                success: (response) => {
                    if (response.estado === 'success') {
                        // --- Lógica de impresión condicional (como en galeria.blade.php) ---
                        if (NEGOCIO_FORMATO_IMPRESION === 'TICKET' && NEGOCIO_TICKET_PANTALLA === '1') {
                            // Mostrar en pantalla (nueva ventana/pestaña)
                            const urlComprobante = "{{ url('comprobantes/ver-ticket') }}/" + response.codfact;
                            const newWindow = window.open(urlComprobante, '_blank');
                            if (newWindow) {
                                newWindow.onload = function() {
                                    // Opcional: puedes intentar imprimir automáticamente aquí,
                                    // pero muchos navegadores bloquean window.print() si no es una interacción directa del usuario.
                                    // newWindow.print(); 
                                    // newWindow.close(); // Cierra después de imprimir si es necesario
                                };
                            } else {
                                Swal.fire('Advertencia', 'Por favor, permita pop-ups para ver el comprobante.', 'warning');
                            }
                        } else {
                            // Imprimir directamente a la ticketera (se maneja en el backend)
                            // La función `imprimirTicketVenta` ya se llama dentro de `registrarVentaDirecta`
                            // si las condiciones de tu backend lo permiten.
                            // Si tu `registrar_venta_directa` actual ya llama a imprimirTicketVenta,
                            // entonces este 'else' no necesita hacer nada más aquí.
                            // Si quisieras que el frontend mande la señal de impresión directa aquí:
                            // fetch(`{{ url('kiosko/imprimir-ticket-venta') }}/${response.codfact}`)
                            //     .then(res => res.json())
                            //     .then(data => {
                            //         if (!data.success) {
                            //             console.error("Error al enviar a impresora directa:", data.message);
                            //             Swal.fire('Error de Impresión', data.message, 'error');
                            //         }
                            //     })
                            //     .catch(err => console.error("Fallo al llamar la impresión directa:", err));
                        }
                        
                        Swal.fire('¡Venta Registrada!', `Comprobante generado.`, 'success');
                        resetSaleForm(); // Esto limpia paymentMethods y renderiza
                        loadProducts($('.btn-category-pos.active').data('id'));

                    } else {
                        Swal.fire('Error al Registrar', response.mensaje || 'Ocurrió un error.', 'error');
                    }
                },
                error: (xhr) => { 
                    console.error("Error en la solicitud AJAX:", xhr.responseText);
                    Swal.fire('Error de Conexión', 'No se pudo conectar con el servidor o hubo un error en la respuesta.', 'error'); 
                },
                complete: () => { btn.prop('disabled', false).html('<i class=\"fa fa-check-circle\"></i> IMPRIMIR'); }
            });
        });
    });
</script>
</body>
</html>