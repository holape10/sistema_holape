<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISTEMA DE GESTION COMERCIAL</title>
    <link rel="shortcut icon" href="img/icono.ico">
    <link rel="stylesheet" href="{{ asset('adminlte/bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/font-awesome6/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sweetalert2/sweetalert2.min.css') }}">
    <style>
    /* ... TODOS TUS ESTILOS CSS EXACTAMENTE IGUALES ... */
    body {
        font-family: Arial, sans-serif;
        background-color: #f8f9fa;
    }
    .header-kiosko {
        background-color: #3498db;
        color: white;
        padding: 8px;
        text-align: center;
        font-size: 1.0em;
        font-weight: bold;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .header-kiosko .back-button {
        background: none;
        border: none;
        color: white;
        font-size: 1em;
        cursor: pointer;
        padding: 6px;
    }
    .main-content {
        display: flex;
        flex-wrap: wrap;
    }
    .menu-section {
        flex: 2;
        padding: 10px;
        border-right: 1px solid #eee;
    }
    .cart-section {
        flex: 1;
        padding: 10px;
        background-color: #e9ecef;
        border-radius: 8px;
    }
    .category-buttons-container-kiosko {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-start;
        gap: 8px;
        padding: 2px;
        margin-bottom: 10px;
    }
    .btn-category-kiosko {
        height: 40px;
        padding: 0 10px;
        font-size: 1.0em;
        font-weight: bold;
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: transform 0.2s, box-shadow 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        white-space: nowrap;
        min-width: 60px;
        border: none;
    }
    .btn-category-kiosko:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 10px rgba(0,0,0,0.15);
        opacity: 0.9;
    }
    .btn-category-kiosko.active {
        border: 3px solid #f0ad4e;
        box-shadow: 0 4px 10px rgba(0,0,0,0.25);
    }

    .product-search-box-kiosko {
        padding: 10px;
        background-color: #6c757d;
        color: white;
        font-weight: bold;
        text-align: center;
        border-radius: 5px;
        margin-bottom: 15px;
    }
    .product-search-box-kiosko input {
        border-radius: 5px;
        padding: 10px;
        font-size: 1.1em;
        border: none;
        width: 100%;
    }

    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 15px;
        padding: 5px;
        max-height: calc(100vh - 350px);
        overflow-y: auto;
    }
    .product-item-kiosko {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 5px;
        background-color: #fff;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        transition: transform 0.1s ease, box-shadow 0.1s ease;
        cursor: pointer;
        overflow: hidden;
        height: 140px;
    }
    .product-item-kiosko:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 10px rgba(0,0,0,0.15);
    }
    .product-item-kiosko .product-img-container {
        flex-grow: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 50%;
    }
    .product-item-kiosko .product-img {
        max-width: 80%;
        max-height: 80%;
        object-fit: contain;
    }
    .product-item-kiosko .product-details-kiosko {
        flex-shrink: 0;
        width: 100%;
        padding-top: 5px;
    }
    .product-item-kiosko .product-name-kiosko {
        font-size: 0.95em;
        font-weight: bold;
        line-height: 1.2;
        max-height: 5.5em;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 4;
        -webkit-box-orient: vertical;
        white-space: normal;
        margin-bottom: 2px;
        color: #333;
    }
    .product-item-kiosko .product-price-kiosko {
        font-size: 1.1em;
        font-weight: bold;
        color: #007bff;
    }
    .product-item-kiosko .product-stock-kiosko {
        font-size: 0.75em;
        color: #28a745;
    }
    .product-item-kiosko.agotado {
        opacity: 0.6;
        cursor: not-allowed;
        background-color: #f2f2f2;
        filter: grayscale(100%);
    }
    .product-item-kiosko.agotado .product-stock-kiosko {
        color: #E74C3C;
        font-weight: bold;
    }

    .cart-header {
        font-size: 1.6em;
        font-weight: bold;
        color: #333;
        margin-bottom: 15px;
        text-align: center;
        position: sticky;
        top: 0;
        background-color: #e9ecef;
        z-index: 10;
        padding-bottom: 10px;
    }
    .cart-items-container {
        max-height: calc(100vh - 350px);
        overflow-y: auto;
        border-bottom: 1px solid #ccc;
        padding-bottom: 10px;
        margin-bottom: 10px;
    }
    .cart-item {
        display: flex;
        flex-wrap: wrap; 
        align-items: center;
        margin-bottom: 10px;
        background-color: #fff;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .cart-item-name {
        flex: 1; 
        font-size: 1.1em;
        font-weight: bold;
        color: #333;
        min-width: 150px; 
    }
    .cart-item-details {
        display: flex;
        flex-direction: row;
        align-items: center;
        margin-left: 10px;
    }
    .cart-item-qty-control {
        display: flex;
        align-items: center;
        margin-bottom: 0px;
    }
    .cart-item-qty-control input {
        width: 50px;
        text-align: center;
        font-size: 1em;
        margin: 0 5px;
        border: 1px solid #ccc;
        border-radius: 4px;
        padding: 5px 0;
    }
    .cart-item-qty-control .btn-qty {
        padding: 3px 8px;
        font-size: 1.1em;
        line-height: 1;
        border-radius: 4px;
        background-color: #007bff;
        color: white;
        border: none;
    }
    .cart-item-unit-price {
        width: 70px;
        text-align: center;
        font-size: 1em;
        margin-left: 5px;
        border: 1px solid #ccc;
        border-radius: 4px;
        padding: 5px 0;
        box-sizing: border-box;
    }
    .cart-item-total-price {
        font-weight: bold;
        font-size: 1.1em;
        white-space: nowrap;
        margin-left: 10px;
        color: #007bff;
    }
    .cart-item-remove {
        background-color: #dc3545;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 5px;
        margin-left: auto;
        align-self: center;
    }
    .cart-item-remove.disabled-old-item {
        background-color: #f0ad4e;
        cursor: pointer;
        opacity: 0.8;
    }
    .cart-item-obs {
        flex-basis: 100%; 
        width: 100%;
        margin-top: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        padding: 5px;
    }

    .cart-total {
        font-size: 1.8em;
        font-weight: bold;
        text-align: right;
        margin-top: 15px;
        color: #28a745;
    }
    .cart-actions {
        text-align: center;
        margin-top: 20px;
    }
    .cart-actions .btn {
        font-size: 1.5em;
        padding: 15px 30px;
        margin: 5px;
        border-radius: 8px;
    }
    .cart-actions .btn-send-order {
        background-color: #28a745;
        color: white;
    }
    .cart-actions .btn-clear-cart {
        background-color: #dc3545;
        color: white;
    }

    input[type="number"] {
        -moz-appearance: textfield;
    }
    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .swal2-input, .swal2-textarea {
        margin-top: 10px !important;
        margin-bottom: 10px !important;
    }
    .swal2-label {
        font-weight: bold;
        display: block;
        text-align: left;
        margin-top: 10px;
    }

    @media (max-width: 991px) {
        .menu-section, .cart-section {
            flex-basis: 100%;
            border-right: none;
            margin-bottom: 20px;
        }
        .menu-section {
            order: 2;
        }
        .cart-section {
            order: 1;
        }
        .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            max-height: 400px;
        }
        .product-item-kiosko {
            height: 160px;
        }
        .cart-items-container {
            max-height: 300px;
        }
    }
    
    @media (max-width: 576px) {
        body, html {
            overflow-x: hidden;
            width: 100%;
            max-width: 100vw;
        }
        .menu-section {
            width: 100%;
            min-width: 0; 
        }
        .header-kiosko {
            font-size: 0.9em;
            padding: 2px;
        }
        .category-buttons-container-kiosko {
            display: flex;
            flex-wrap: nowrap; 
            overflow-x: auto;  
            gap: 10px;
            padding: 5px 2px;
            margin-bottom: 10px;
            -webkit-overflow-scrolling: touch; 
            scrollbar-width: none; 
            width: 100%;
        }
        .category-buttons-container-kiosko::-webkit-scrollbar {
            display: none;
        }
        .btn-category-kiosko {
            flex: 0 0 auto; 
            height: 38px;
            font-size: 0.85em;
            padding: 0 15px;
            border-radius: 20px; 
            white-space: nowrap; 
            margin: 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .btn-category-kiosko.active {
            transform: scale(1.05); 
            border: 2px solid #fff; 
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
        }
        .product-item-kiosko {
            height: 140px;
            padding: 6px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .product-item-kiosko .product-img-container {
            max-height: 40px; 
            margin-bottom: 5px;
        }
        .product-item-kiosko .product-details-kiosko {
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .product-item-kiosko .product-name-kiosko {
            font-size: 0.8em;
            -webkit-line-clamp: 3;
            max-height: 3.6em; 
            white-space: normal;
            margin-bottom: 6px;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: center;
        }
        .product-item-kiosko .product-price-kiosko {
            font-size: 0.85em;
            margin: 0;
            line-height: 1.1;
        }
        .product-item-kiosko .product-stock-kiosko {
            font-size: 0.65em;
            margin-top: 3px;
        }   
        .cart-header {
            font-size: 1.0em;
        }
        .cart-item {
            flex-wrap: wrap;
            justify-content: space-between;
        }
        .cart-item-name {
            flex-basis: 100%;
            margin-bottom: 5px;
        }
        .cart-item-details {
            flex-basis: auto;
            flex-grow: 1;
            margin-left: 0;
            justify-content: flex-start;
            order: 2;
            flex-wrap: wrap; 
            gap: 6px; 
            margin-bottom: 5px;
        }
        .cart-item-qty-control {
            margin-left: 0;
        }
        .cart-item-unit-price {
            margin-left: 0px;
        }
        .cart-item-total-price {
            flex-basis: auto;
            margin-left: 10px;
            order: 3;
        }
        .cart-item-remove {
            margin-left: auto;
            align-self: center;
            order: 1;
        }
        .cart-item-obs {
            flex-basis: 100%;
            margin-top: 10px;
            order: 4;
        }
        .cart-actions {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            gap: 5px;
            margin-top: 15px;
        }
        .cart-actions .btn {
            font-size: 0.8em; 
            padding: 10px 5px; 
            margin: 0;
            border-radius: 6px;
            flex-grow: 1; 
            width: 50%;
            min-width: unset;
        }
    }
    </style>
</head>
<body>
    <div class="header-kiosko">
        <button class="back-button" onclick="window.location.href='{{ route('kiosko.seleccion_servicio') }}'">
            <i class="fas fa-arrow-left"></i>
        </button>
        <span>
            TU PEDIDO -
            @if($order_type == 'salon' && isset($mesa_info['nombre']))
                MESA: {{ $mesa_info['nombre'] }}
            @elseif($order_type == 'llevar')
                PARA LLEVAR
            @elseif($order_type == 'delivery')
                DELIVERY
            @else
                ERROR TIPO PEDIDO
            @endif
        </span>
        <span></span>
    </div>

    <div class="container-fluid main-content">
        <div class="menu-section">
            <div class="category-buttons-container-kiosko">
                @foreach($categorias as $cat)
                    <button type="button"
                            class="btn btn-category-kiosko {{ $cat->cat_id == $cat_default_id ? 'active' : '' }}"
                            data-category-id="{{ $cat->cat_id }}"
                            onclick="loadProductsByCategory({{ $cat->cat_id }})"
                            style="background-color:{{$cat->color}}; border-color:{{$cat->color}};">
                        {{ $cat->cat_nom }}
                    </button>
                @endforeach
            </div>

            <div class="product-search-box-kiosko">
                PRODUCTOS
                <input type="text" name="txt_bus_pro_kiosko" id="txt_bus_pro_kiosko" class="form-control" placeholder="BUSCAR PRODUCTO o CÓDIGO DE BARRAS">
            </div>

            <div id="products_grid_container" class="products-grid">
            </div>
        </div>

        <div class="cart-section">
            <div class="cart-header">
                Tu Carrito
            </div>
            {{-- 🟢 NUEVO: CAMPO DE CLIENTE SOLO PARA LLEVAR --}}
            @if($order_type == 'llevar')
                <div class="form-group" style="padding: 10px; background-color: #fff; border-radius: 5px; margin-bottom: 10px; border: 1px solid #ccc;">
                    <label for="nombre_cliente_llevar" style="font-weight: bold; color: #333; margin-bottom: 5px; display: block;">
                        <i class="fas fa-user"></i> Nombre del Cliente:
                    </label>
                    <input type="text" id="nombre_cliente_llevar" class="form-control" placeholder="Escribe el nombre aquí..." style="font-size: 1.1em; font-weight: bold; text-transform: uppercase;">
                </div>
            @endif
            <div id="cart_items_list" class="cart-items-container">
                @include('empresas.kiosko.partials.cart_details', ['cart' => $cart, 'icbper_val' => $icbper_val])
            </div>
            <div class="cart-total" id="cart_total_display">
                Total: S/. 0.00
            </div>
            <div class="cart-actions">
                @if($order_type == 'salon')
                    {{-- Para MESA: Envío directo de comanda --}}
                    <button type="button" class="btn btn-primary btn-block btn-send-order" id="btn_send_order_direct">
                        <strong>ENVIAR COMANDA</strong>
                    </button>
                @else
                    {{-- Para LLEVAR/DELIVERY: Ir a confirmación --}}
                    <button type="button" class="btn btn-primary btn-block btn-send-order" id="btn_send_order_to_confirm">
                        <strong>CONTINUAR</strong>
                    </button>
                @endif
                
                <button type="button" class="btn btn-danger btn-block btn-clear-cart" id="btn_clear_cart">
                    <strong>VACIAR PEDIDO</strong>
                </button>

                @if(session()->has('kiosko_last_pedido_id') && Auth::user()->hasRole('admin'))
                <button type="button" class="btn btn-block btn-delete-order" id="btn_delete_order" style="background-color: #343a40; color: white; margin-top: 10px;">
                    <strong><i class="fas fa-trash-alt"></i> ELIMINAR PEDIDO</strong>
                </button>
            @endif
            </div>
        </div>
    </div>

    @php
        $mostrar_descuento_50 = $mostrar_descuento_50 ?? false;
    @endphp

    <!-- MODAL 1: ENTRADAS (Elige 1) -->
    <div class="modal fade" id="modalEntradas" tabindex="-1" role="dialog" aria-labelledby="modalEntradasLabel" data-backdrop="static" data-keyboard="false">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #f0ad4e; color: white;">
            <h4 class="modal-title text-center w-100" id="modalEntradasLabel" style="font-weight: bold;">SELECCIONE UNA ENTRADA</h4>
          </div>
          <div class="modal-body text-center">
            <div class="list-group" id="lista-entradas">
                {{-- Llenado por AJAX --}}
            </div>
          </div>
          <div class="modal-footer" style="justify-content: center;">
            <button type="button" class="btn btn-secondary btn-lg" id="btn-sin-entrada" style="width: 100%; font-weight: bold;">SIN ENTRADA (SALTAR)</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL 2: COMBOS (Elige hasta 3) -->
    <div class="modal fade" id="modalCombos" tabindex="-1" role="dialog" aria-labelledby="modalCombosLabel" data-backdrop="static" data-keyboard="false">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #28a745; color: white;">
            <h4 class="modal-title text-center w-100" id="modalCombosLabel" style="font-weight: bold;">SELECCIONA HASTA 3 OPCIONES</h4>
          </div>
          <div class="modal-body text-center">
            <p class="text-muted mb-2">Marca los platos que conformarán el combo.</p>
            <div class="list-group" id="lista-combos">
                {{-- Llenado por AJAX --}}
            </div>
          </div>
          <div class="modal-footer" style="display: flex; justify-content: space-between; flex-direction: column;">
            <button type="button" class="btn btn-success btn-lg mb-2" id="btn-confirmar-combo" style="width: 100%; font-weight: bold;">CONFIRMAR SELECCIÓN (0/3)</button>
            <button type="button" class="btn btn-secondary btn-lg" id="btn-sin-combo" style="width: 100%; font-weight: bold;">SALTAR SELECCIÓN</button>
          </div>
        </div>
      </div>
    </div>

    <script src="{{ asset('adminlte/bower_components/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('adminlte/bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/sweetalert2/sweetalert2.min.js') }}"></script>
    
    <script>

        //ACTIVAR CONTROL DE STOCK STOCK STOCK OBLIGAR STOCK

        const CONTROLAR_STOCK_ESTRICTO = false;

        const sonidoComanda = new Audio("{{ asset('sounds/notificacion.mp3') }}");
        const mostrarDescuento50 = {{ $mostrar_descuento_50 ? 'true' : 'false' }};


        function reproducirPitido() {
            sonidoComanda.play().catch(function(error) {
                console.log("El navegador bloqueó el sonido inicialmente: ", error);
            });

            if (navigator.vibrate) {
                navigator.vibrate(200); 
            }
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        let originalPrices = {};
        let activeDiscounts = {};
        let activePagados = {};

        window.peticionesAjax = {};

        // Variables para Combos
        let seleccionesComboGlobal = [];

        function updateCartTotals() {
            let total = 0;
            const icbper_val = parseFloat("{{ $icbper_val ?? 0.00 }}") || 0.00;

            $('#cart_items_list .cart-item').each(function() {
                const $item = $(this);
                const qty = parseFloat($item.find('.cart-item-qty').val()) || 0;
                const unitPriceInput = $item.find('.cart-item-unit-price');
                const unitPrice = parseFloat(unitPriceInput.val()) || 0;
                const icbper_applies = unitPriceInput.data('icbper-applies') == 1;

                const lineTotal = (qty * unitPrice) + (icbper_applies ? qty * icbper_val : 0);
                
                $item.find('.cart-item-total-price').text('S/. ' + lineTotal.toFixed(2));
                total += lineTotal;
            });

            $('#cart_total_display').text('Total: S/. ' + total.toFixed(2));
        }

        let peticionBusquedaAjax = null;

        function loadProducts(categoryId = 0, searchText = '') {
            $('#products_grid_container').html('<center><img src="{{ asset('img/load.gif') }}" width="50px" height="50px"></center>');

            let dataToSend = { search_text: searchText };
            if (!searchText) {
                dataToSend.category_id = categoryId;
            }

            // --- MAGIA AQUÍ: Matamos la búsqueda anterior si el cliente sigue escribiendo ---
            if (peticionBusquedaAjax) {
                peticionBusquedaAjax.abort();
            }

            // Guardamos la nueva petición en la variable
            peticionBusquedaAjax = $.ajax({
                url: "{{ route('kiosko.search_products_kiosko') }}",
                type: "GET",
                data: dataToSend,
                dataType: 'json',
                success: function(response) {
                    $('#products_grid_container').html(response.vista);
                },
                error: function(xhr, status, error) {
                    // 🟢 IMPORTANTE: Solo mostramos error si NO fuimos nosotros quienes cancelamos la búsqueda
                    if (status !== 'abort') {
                        console.error("Error al cargar productos:", error);
                        // Opcional: Puedes comentar la siguiente línea si no quieres que salga el recuadro de error por microcortes de internet
                        // Swal.fire('Error', 'No se pudieron cargar los productos. Intenta de nuevo.', 'error');
                    }
                }
            });
        }

        function loadProductsByCategory(categoryId) {
            $('.btn-category-kiosko').removeClass('active');
            $(`.btn-category-kiosko[data-category-id="${categoryId}"]`).addClass('active');
            $('#txt_bus_pro_kiosko').val('');
            loadProducts(categoryId);

            $('html, body').animate({
                scrollTop: $('#products_grid_container').offset().top - 20
            }, 100);
        }

        function addItemToCart(productId, productName, productPrice, productIcbper, productStock, productAcomp, entrada = null) {
            $.ajax({
                url: "{{ route('kiosko.add_to_cart') }}",
                type: "POST",
                data: {
                    id: productId,
                    producto: productName,
                    precio: productPrice,
                    icbper: productIcbper,
                    //stock: 99999,
                    stock: productStock, // 🟢 AQUI ESTABA EL ERROR (antes decia 99999)
                    acompa: productAcomp,
                    entrada: entrada 
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        loadCartDetails();
                    } else {
                        Swal.fire('Atención', response.message, 'warning');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error al añadir al carrito:", error);
                    Swal.fire('Error', 'No se pudo añadir el producto al carrito. Intenta de nuevo.', 'error');
                }
            });
        }

        function updateCartItem(productId, newQuantity, itemObs, isOldItem, newUnitPrice = null) {
            if (newUnitPrice !== null) {
                newUnitPrice = parseFloat(newUnitPrice);
                if (isNaN(newUnitPrice) || newUnitPrice < 0.01) {
                    newUnitPrice = 0.01;
                }
            }

            if (isOldItem && newQuantity <= 0) {
                Swal.fire('Atención', 'Para eliminar completamente un ítem del pedido original, usa el botón de eliminar y solicita autorización.', 'info');
                loadCartDetails();
                return $.Deferred().reject().promise();
            }

            let estadoPagado = activePagados[productId] ? 1 : 0;
            
            // 🟢 Rescatamos el stock real que tiene guardado el input del DOM
            let maxStockReal = parseFloat($('.cart-item[data-id="' + productId + '"]').find('.cart-item-qty').data('stock')) || 0;

            if (window.peticionesAjax[productId]) {
                window.peticionesAjax[productId].abort();
            }

            window.peticionesAjax[productId] = $.ajax({
                url: "{{ route('kiosko.update_cart_item') }}",
                type: "POST",
                data: {
                    id: productId,
                    cantidad: newQuantity,
                    //stock: 99999,
                    stock: maxStockReal, // 🟢 AQUI ESTABA EL ERROR TAMBIEN
                    observaciones: itemObs,
                    is_old_item: isOldItem,
                    new_unit_price: newUnitPrice,
                    pagado: estadoPagado
                },
                dataType: 'json',
                success: function(response) {
                    if (!response.success) {
                        Swal.fire('Atención', response.message, 'warning');
                        loadCartDetails(); 
                    }
                },
                error: function(xhr, status, error) {
                    if (status !== 'abort') {
                        console.error("Error al actualizar carrito:", error);
                        Swal.fire('Error', 'No se pudo actualizar el producto en el carrito. Intenta de nuevo.', 'error');
                        loadCartDetails();
                    }
                }
            });

            return window.peticionesAjax[productId];
        }

        function removeCartItem(productId) {
            $.ajax({
                url: "{{ route('kiosko.remove_cart_item') }}",
                type: "POST",
                data: { id: productId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        loadCartDetails();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error al eliminar del carrito:", error);
                    Swal.fire('Error', 'No se pudo eliminar el producto del carrito. Intenta de nuevo.', 'error');
                }
            });
        }

        function loadCartDetails() {
    let observacionesTemporales = {};
    
    // Guardamos las observaciones antes de recargar
    $('.cart-item').each(function() {
        let id = String($(this).data('id')); 
        let obs = $(this).find('.cart-item-obs').val(); 
        if (obs && obs.trim() !== "") {
            observacionesTemporales[id] = obs;
        }
    });

    // Bloqueamos el botón de enviar mientras se comunica con el servidor
    $('.btn-send-order').prop('disabled', true);

    $.ajax({
        url: "{{ route('kiosko.get_cart_details') }}",
        type: "GET",
        dataType: 'json',
        cache: false,
        success: function(response) {
            $('#cart_items_list').html(response.vista);
            
             // ✅ FIX CRÍTICO: Normalizar is-old-item a booleano real
            // El blade emite strings 'true'/'false', pero 'false' es truthy en JS
            $('.cart-item').each(function() {
                let raw = $(this).data('is-old-item');
                $(this).data('is-old-item', raw === true || raw === 'true');
            });
            // Restaurar observaciones
            $.each(observacionesTemporales, function(idProd, textoObs) {
                let $itemRow = $('.cart-item[data-id="' + idProd + '"]');
                if ($itemRow.length > 0) {
                    let $inputObs = $itemRow.find('.cart-item-obs');
                    if($inputObs.val() === "") {
                        $inputObs.val(textoObs);
                    }
                }
            });

            // Iteramos sobre cada producto cargado
            $('.cart-item').each(function() {
                let idProd = String($(this).data('id'));
                let $priceInput = $(this).find('.cart-item-unit-price');
                let $removeBtn = $(this).find('.cart-item-remove'); 
                //let isOldItem = $(this).data('is-old-item') === true || $(this).data('is-old-item') === 'true';
                let isOldItem = $(this).data('is-old-item') === true;
                let dbPagado = $(this).data('pagado') == 1 || $(this).data('pagado') == '1';
                
                if (typeof activePagados[idProd] === 'undefined') {
                    activePagados[idProd] = dbPagado;
                }

                if (isOldItem) {
                    $priceInput.prop('readonly', true).css({'background-color': '#f2dede', 'cursor': 'not-allowed'});
                } else {
                    if (typeof originalPrices[idProd] === 'undefined') {
                        let precioLimpio = $priceInput.val().toString().replace(',', '.');
                        originalPrices[idProd] = parseFloat(precioLimpio) || 0;
                    }

                    if (typeof mostrarDescuento50 !== 'undefined' && mostrarDescuento50) {
                        if ($(this).find('.check-descuento-50').length === 0) {
                            let isChecked = activeDiscounts[idProd] ? true : false;
                            let bgColor = isChecked ? '#28a745' : '#f39c12'; 
                            let textVal = isChecked ? '50% ON' : '50%';
                            
                            let checkHtml = `
                            <span class="check-descuento-50 badge-descuento" 
                                  data-id="${idProd}" 
                                  data-active="${isChecked ? '1' : '0'}"
                                  data-processed="0"
                                  style="background-color: ${bgColor}; color: white; padding: 4px 10px; border-radius: 5px; font-weight: bold; font-size: 0.9em; height: 32px; display: inline-flex; align-items: center; cursor: pointer; transition: all 0.2s; margin-left: 5px; user-select: none; -webkit-user-select: none;">
                                ${textVal}
                            </span>`;
                            
                            $priceInput.after(checkHtml);

                            if (isChecked) {
                                $priceInput.prop('readonly', true).css({'background-color': '#f2dede', 'cursor': 'not-allowed'});
                                if (originalPrices[idProd]) {
                                    $priceInput.val((parseFloat(originalPrices[idProd]) / 2).toFixed(2));
                                }
                            } else {
                                $priceInput.prop('readonly', false).css({'background-color': '', 'cursor': 'text'});
                                if (originalPrices[idProd]) {
                                    $priceInput.val(parseFloat(originalPrices[idProd]).toFixed(2));
                                }
                            }
                        }
                    } else {
                        $(this).find('.check-descuento-50').remove();
                    }
                }

                // Lógica de botón cobrar (AHORA SÍ DENTRO DEL BUCLE CORRECTO)
                let MOSTRAR_BOTON_COBRAR = false; 

                if (MOSTRAR_BOTON_COBRAR) {
                    if ($(this).find('.btn-pagado-item').length === 0 && $(this).find('.badge-pagado').length === 0) {
                        let isPagado = activePagados[idProd];
                        let pagadoColor = isPagado ? '#28a745' : '#6c757d';
                        let pagadoText = isPagado ? 'PAGADO' : 'COBRAR';

                        if (isPagado) {
                            $(this).css('background-color', '#d4edda');
                        }

                        let isAdmin = {{ Auth::user()->hasRole('admin') ? 'true' : 'false' }};
                        let pagadoHtml = '';

                        if (isAdmin || !isOldItem) {
                            pagadoHtml = `
                            <button type="button" class="btn-pagado-item" data-id="${idProd}" style="margin-right: 10px; background-color: ${pagadoColor}; color: white; border: none; padding: 5px 10px; border-radius: 5px; font-weight: bold; font-size: 0.85em; cursor: pointer; height: 32px;">
                                ${pagadoText}
                            </button>`;
                        } else {
                            if (isPagado) {
                                pagadoHtml = `
                                <span class="badge badge-pagado" style="margin-right: 10px; background-color: #28a745; color: white; padding: 6px 10px; font-size: 0.85em; height: 32px; display: flex; align-items: center;">
                                    PAGADO
                                </span>`;
                            } else {
                                pagadoHtml = `
                                <span class="badge badge-pagado" style="margin-right: 10px; background-color: #6c757d; color: white; padding: 6px 10px; font-size: 0.85em; height: 32px; display: flex; align-items: center;">
                                    POR COBRAR
                                </span>`;
                            }
                        }

                        if (pagadoHtml !== '') {
                            $removeBtn.before(pagadoHtml);
                        }
                    }
                }
            }); // FIN DEL BUCLE .cart-item.each

            updateCartTotals(); 
            
            // La carga fue exitosa, rehabilitamos el botón
            $('.btn-send-order').prop('disabled', false);
        },
        error: function(xhr, status, error) {
            console.error("Error al cargar carrito:", error);
            // Protección contra cortes de señal
            Swal.fire({
                title: '¡Red Inestable!',
                text: 'No se pudieron sincronizar los pedidos de la mesa. Por favor, recarga la página para evitar que se borren pedidos anteriores.',
                icon: 'error',
                confirmButtonText: 'Recargar Página',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    location.reload();
                }
            });
        }
    });
}

        function enviarComandaDirecta(btnObj, originalHtml) {
        // Ya no hay Swal.fire de "Enviando comanda..." aquí
        
        $.ajax({
            url: "{{ route('kiosko.enviar_comanda') }}",
            type: "POST",
            data: {
                _token: '{{ csrf_token() }}',
                envio_directo: true,
                order_type: "{{ $order_type ?? 'salon' }}", // 🟢 AÑADIR ESTA LÍNEA
                mesa_id: "{{ isset($mesa_info['id']) ? $mesa_info['id'] : '' }}" // ESTA LÍNEA ES LA SALVACIÓN
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    reproducirPitido();
                    // 🟢 ÚNICO MENSAJE QUE VERÁ EL MOZO
                    Swal.fire({
                        icon: 'success',
                        title: '¡Comanda Enviada!',
                        text: response.message || 'La comanda ha sido enviada a cocina exitosamente.',
                        timer: 1500, // Le bajé el tiempo para que sea más ágil
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = "{{ route('kiosko.seleccion_servicio') }}";
                    });
                } else {
                    // Si hay error, regresamos el botón a la normalidad
                    if (btnObj) btnObj.prop('disabled', false).html(originalHtml);
                    Swal.fire('Error', response.message || 'No se pudo enviar la comanda.', 'error');
                }
            },
            error: function(xhr, status, error) {
                if (btnObj) btnObj.prop('disabled', false).html(originalHtml);
                console.error("Error al enviar comanda directa:", error);
                let errorMessage = 'Hubo un error al enviar la comanda. Intenta de nuevo.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                Swal.fire('Error', errorMessage, 'error');
            }
        });
    }

        $(document).ready(function() {
            loadProducts({{ $cat_default_id ?? 0 }});
            loadCartDetails();
            
            let updatePromises = [];

            // NUEVA LÓGICA CON CHECKBOX NATIVO
// NUEVO EVENTO — funciona perfecto en móvil y PC
$(document).on('click touchend', '.check-descuento-50', function(e) {
    e.preventDefault();
    e.stopPropagation();

    // Evitar doble ejecución en móviles (touchend + click)
    if ($(this).data('processed') == '1') return;
    $(this).data('processed', '1');
    setTimeout(() => $(this).data('processed', '0'), 300);

    let $badge = $(this);
    let idProd = String($badge.data('id'));
    let cartItemElement = $badge.closest('.cart-item');
    let $priceInput = cartItemElement.find('.cart-item-unit-price');

    // Toggle del estado
    let isChecked = $badge.data('active') == '1';
    isChecked = !isChecked; // invertimos
    $badge.data('active', isChecked ? '1' : '0');
    activeDiscounts[idProd] = isChecked;

    // Rescatamos precio original
    if (typeof originalPrices[idProd] === 'undefined' || isNaN(originalPrices[idProd])) {
        let inputVal = $priceInput.val().toString().replace(/[^0-9.,]/g, '').replace(',', '.');
        originalPrices[idProd] = parseFloat(inputVal) || 0;
    }

    let finalPrice = parseFloat(originalPrices[idProd]);

    if (isChecked) {
        finalPrice = finalPrice / 2;
        $priceInput.prop('readonly', true).css({'background-color': '#f2dede', 'cursor': 'not-allowed'});
        $badge.css('background-color', '#28a745').text('50% ON');
    } else {
        $priceInput.prop('readonly', false).css({'background-color': '', 'cursor': 'text'});
        $badge.css('background-color', '#f39c12').text('50%');
    }

    let precioEnviado = finalPrice.toFixed(2);
    $priceInput.val(precioEnviado);

    const qtyInput = cartItemElement.find('.cart-item-qty');
    let currentQuantity = parseInt(qtyInput.val()) || 1;
    const itemObs = cartItemElement.find('.cart-item-obs').val() || '';
    const isOldItem = cartItemElement.data('is-old-item');

    const promise = updateCartItem(idProd, currentQuantity, itemObs, isOldItem, precioEnviado);
    updatePromises.push(promise);

    promise.always(function() {
        const index = updatePromises.indexOf(promise);
        if (index > -1) updatePromises.splice(index, 1);
        updateCartTotals();
    });
});


            // LÓGICA PRINCIPAL AL HACER CLICK EN UN PRODUCTO DE LA GRILLA
            $(document).on('click', '.product-item-kiosko:not(.agotado)', function(e) {
                e.preventDefault();
                $(':focus').blur(); 
                
                const $card = $(this);
                const productId = String($card.data('id'));
                const productName = $card.data('name');
                const productPrice = $card.data('price');
                const productIcbper = $card.data('icbper');
                const productAcomp = $card.data('acompa');
                const productStock = parseFloat($card.data('stock')) || 0; // 🟢 LEEMOS EL STOCK AQUI
                
                const tieneEntrada = parseInt($card.data('tiene-entrada')) || 0; 
                const promocionProd = parseInt($card.data('promocion')) || 0;

                const procesarAgregado = function(entradaSeleccionada = null) {
                    let $itemExistente = $('#cart_items_list .cart-item[data-id="' + productId + '"]');
                    
                    if ($itemExistente.length > 0 && $itemExistente.data('is-old-item') !== true) {
                        // Si ya existe, le hace click al botón +, el cual YA TIENE nuestra validación de stock
                        $itemExistente.find('.btn-qty-plus').click();
                        return; 
                    }
                    
                    // Si no existía, pasamos la variable productStock real, no el 99999
                    addItemToCart(productId, productName, productPrice, productIcbper, productStock, productAcomp, entradaSeleccionada);
                };

                const ejecutarAgregado = function(entrada = null) {
                    setTimeout(function() {
                        if (updatePromises.length > 0) {
                            $.when(...updatePromises).always(() => procesarAgregado(entrada));
                        } else {
                            procesarAgregado(entrada);
                        }
                    }, 50);
                };

                // LÓGICA DEL MODAL SEGÚN PROMOCIÓN
                if (tieneEntrada === 1 && promocionProd === 6) {
                    // === FLUJO DE COMBOS (Elige hasta 3) ===
                    let $listaCombos = $('#lista-combos');
                    seleccionesComboGlobal = []; // Limpiamos selecciones previas
                    $('#btn-confirmar-combo').text(`CONFIRMAR SELECCIÓN (0/3)`);
                    
                    $listaCombos.html('<h5 class="text-center mt-3 mb-3">Cargando opciones del combo... <i class="fas fa-spinner fa-spin"></i></h5>');
                    $('#modalCombos').modal('show');

                    $.ajax({
                        url: "{{ route('kiosko.get_combos') }}",
                        type: "GET",
                        dataType: "json",
                        success: function(combos) {
                            $listaCombos.empty();

                            if(combos.length === 0) {
                                $listaCombos.html('<h5 class="text-center text-danger mt-3 mb-3">No hay combos configurados.</h5>');
                            } else {
                                combos.forEach(function(c) {
                                    let btnHtml = `
                                        <button type="button" class="list-group-item list-group-item-action btn-seleccionar-combo" 
                                                data-nombre="${c.nombre}" 
                                                style="font-size: 1.1em; font-weight: bold; margin-bottom: 5px; border-radius: 8px; border: 2px solid #ddd;">
                                            ${c.nombre} <i class="fas fa-check-circle icono-check" style="color: #28a745; float: right; display: none;"></i>
                                        </button>
                                    `;
                                    $listaCombos.append(btnHtml);
                                });
                            }

                            $('.btn-seleccionar-combo').off('click').on('click', function() {
                                let comboName = $(this).data('nombre');
                                let index = seleccionesComboGlobal.indexOf(comboName);

                                if (index > -1) {
                                    // Si ya estaba seleccionado, lo deseleccionamos
                                    seleccionesComboGlobal.splice(index, 1);
                                    $(this).removeClass('active').css({'border-color': '#ddd', 'background-color': '#fff', 'color': '#000'});
                                    $(this).find('.icono-check').hide();
                                } else {
                                    // Seleccionamos nuevo, validando límite de 3
                                    if (seleccionesComboGlobal.length >= 3) {
                                        Swal.fire('Atención', 'Solo puedes elegir hasta un máximo de 3 opciones.', 'warning');
                                        return;
                                    }
                                    seleccionesComboGlobal.push(comboName);
                                    $(this).addClass('active').css({'border-color': '#28a745', 'background-color': '#f8fff9', 'color': '#28a745'});
                                    $(this).find('.icono-check').show();
                                }
                                
                                $('#btn-confirmar-combo').text(`CONFIRMAR SELECCIÓN (${seleccionesComboGlobal.length}/3)`);
                            });
                        },
                        error: function() {
                            $listaCombos.html('<h5 class="text-center text-danger mt-3 mb-3">Error al cargar combos.</h5>');
                        }
                    });

                    $('#btn-confirmar-combo').off('click').on('click', function() {
                        if (seleccionesComboGlobal.length === 0) {
                            Swal.fire('Atención', 'Debes seleccionar al menos 1 opción para el combo.', 'warning');
                            return;
                        }
                        $('#modalCombos').modal('hide');
                        // Unimos lo seleccionado con un " + " y lo enviamos en la variable 'entrada'
                        let textoCombos = seleccionesComboGlobal.join(' + ');
                        ejecutarAgregado(textoCombos);
                    });

                    $('#btn-sin-combo').off('click').on('click', function() {
                        $('#modalCombos').modal('hide');
                        ejecutarAgregado(null);
                    });

                } else if (tieneEntrada === 1) {
                    // === FLUJO DE ENTRADAS NORMAL (Elige solo 1) ===
                    let $listaEntradas = $('#lista-entradas');
                    
                    $listaEntradas.html('<h5 class="text-center mt-3 mb-3">Cargando entradas... <i class="fas fa-spinner fa-spin"></i></h5>');
                    $('#modalEntradas').modal('show');

                    $.ajax({
                        url: "{{ route('kiosko.get_entradas') }}",
                        type: "GET",
                        dataType: "json",
                        success: function(entradas) {
                            $listaEntradas.empty();

                            if(entradas.length === 0) {
                                $listaEntradas.html('<h5 class="text-center text-danger mt-3 mb-3">No hay entradas configuradas.</h5>');
                            } else {
                                entradas.forEach(function(ent) {
                                    // Soportamos si te llega nombre_producto o nombre
                                    let nomMostrar = ent.nombre_producto ? ent.nombre_producto : ent.nombre; 
                                    let btnHtml = `
                                        <button type="button" class="list-group-item list-group-item-action btn-seleccionar-entrada" 
                                                data-nombre="${nomMostrar}" 
                                                style="font-size: 1.2em; font-weight: bold; margin-bottom: 5px; border-radius: 8px; border: 2px solid #ddd;">
                                            ${nomMostrar}
                                        </button>
                                    `;
                                    $listaEntradas.append(btnHtml);
                                });
                            }

                            $('.btn-seleccionar-entrada').off('click').on('click', function() {
                                let entradaName = $(this).data('nombre');
                                $('#modalEntradas').modal('hide');
                                ejecutarAgregado(entradaName);
                            });
                        },
                        error: function() {
                            $listaEntradas.html('<h5 class="text-center text-danger mt-3 mb-3">Error al cargar entradas.</h5>');
                        }
                    });

                    $('#btn-sin-entrada').off('click').on('click', function() {
                        $('#modalEntradas').modal('hide');
                        ejecutarAgregado(null);
                    });

                } else {
                    // Flujo normal sin entrada ni combo
                    ejecutarAgregado(null);
                }
            });

            $(document).on('click', '.btn-qty-minus', function() {
            const cartItemElement = $(this).closest('.cart-item');
            const productId      = String(cartItemElement.data('id'));
            const qtyInput       = cartItemElement.find('.cart-item-qty');
            let newQuantity      = parseInt(qtyInput.val()) - 1;
            const itemObs        = cartItemElement.find('.cart-item-obs').val();
            const isOldItem      = cartItemElement.data('is-old-item') === true;
            const unitPriceInput = cartItemElement.find('.cart-item-unit-price');
            let currentUnitPrice = parseFloat(unitPriceInput.val().toString().replace(/[^0-9.,]/g, '').replace(',', '.')) || 0.01;
            let originalQty      = parseInt(qtyInput.attr('data-original-qty')) || 0;
            let isAdmin          = {{ Auth::user()->hasRole('admin') ? 'true' : 'false' }};

            // Mozo: no puede reducir ítems viejos
            if (isOldItem && !isAdmin && newQuantity < originalQty) {
                Swal.fire('Atención', 'No puedes reducir la cantidad de un producto ya enviado a cocina. Solicita a un administrador.', 'warning');
                return;
            }

            if (newQuantity < 1) return;

            // ✅ NUEVO: Admin que reduce ítem viejo debe confirmar
            if (isOldItem && isAdmin && newQuantity < originalQty) {
                Swal.fire({
                    title: '¿Reducir cantidad?',
                    html: `Vas a reducir este ítem de <strong>${originalQty}</strong> a <strong>${newQuantity}</strong>.<br>¿Confirmas?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, reducir',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        qtyInput.val(newQuantity);
                        const promise = updateCartItem(productId, newQuantity, itemObs, isOldItem, currentUnitPrice);
                        updatePromises.push(promise);
                        promise.always(function() {
                            const index = updatePromises.indexOf(promise);
                            if (index > -1) updatePromises.splice(index, 1);
                            updateCartTotals();
                        });
                    }
                    // Si cancela, no hace nada, la cantidad se queda igual
                });
                return; // Salimos aquí, el Swal maneja el flujo
            }

            qtyInput.val(newQuantity);
            const promise = updateCartItem(productId, newQuantity, itemObs, isOldItem, currentUnitPrice);
            updatePromises.push(promise);
            promise.always(function() {
                const index = updatePromises.indexOf(promise);
                if (index > -1) updatePromises.splice(index, 1);
                updateCartTotals();
            });
        });

            // 🟢 VARIABLE PARA LIMITAR VENTA AL STOCK (true = Bloquea, false = Libre)
            

            $(document).on('click', '.btn-qty-plus', function() {
                const cartItemElement = $(this).closest('.cart-item');
                const productId = String(cartItemElement.data('id'));
                const qtyInput = cartItemElement.find('.cart-item-qty');
                
                let currentQuantity = parseInt(qtyInput.val()) || 0;
                let maxStock = parseFloat(qtyInput.data('stock')) || 0;

                // --- INICIO LÓGICA DE STOCK ---
                if (CONTROLAR_STOCK_ESTRICTO && currentQuantity >= maxStock) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Stock Insuficiente',
                        text: 'Solo hay ' + maxStock + ' unidades en stock. Avisar al administrador.'
                    });
                    return; // Detenemos la ejecución aquí, no suma nada
                }
                // --- FIN LÓGICA DE STOCK ---

                let newQuantity = currentQuantity + 1;
                const itemObs = cartItemElement.find('.cart-item-obs').val();
                //const isOldItem = cartItemElement.data('is-old-item');
                const isOldItem = cartItemElement.data('is-old-item') === true;
                const unitPriceInput = cartItemElement.find('.cart-item-unit-price');
                let currentUnitPrice = parseFloat(unitPriceInput.val().toString().replace(/[^0-9.,]/g, '').replace(',', '.')) || 0.01;

                qtyInput.val(newQuantity);
                const promise = updateCartItem(productId, newQuantity, itemObs, isOldItem, currentUnitPrice);
                updatePromises.push(promise);
                promise.always(function() {
                    const index = updatePromises.indexOf(promise);
                    if (index > -1) updatePromises.splice(index, 1);
                    updateCartTotals(); 
                });
            });

            $(document).on('change', '.cart-item-qty', function() {
                const cartItemElement = $(this).closest('.cart-item');
                const productId = String(cartItemElement.data('id'));
                let newQuantity = parseInt($(this).val());
                const itemObs = cartItemElement.find('.cart-item-obs').val();
                const isOldItem = cartItemElement.data('is-old-item') === true;
                const unitPriceInput = cartItemElement.find('.cart-item-unit-price');
                let currentUnitPrice = parseFloat(unitPriceInput.val().toString().replace(/[^0-9.,]/g, '').replace(',', '.')) || 0.01;
                
                // 🟢 VARIABLES DE SEGURIDAD
                let originalQty = parseInt($(this).attr('data-original-qty')) || 0;
                let isAdmin = {{ Auth::user()->hasRole('admin') ? 'true' : 'false' }};

                if (isNaN(newQuantity) || newQuantity < 1) {
                    if (!isOldItem) {
                        newQuantity = 1;
                        $(this).val(newQuantity);
                    } else {
                        if (newQuantity <= 0) {
                            newQuantity = 1;
                            $(this).val(newQuantity);
                        }
                    }
                }

                // 🛡️ BLOQUEO MANUAL: Si el Mozo usa el teclado para bajar la cantidad
                if (isOldItem && !isAdmin && newQuantity < originalQty) {
                    Swal.fire('Acción Bloqueada', 'No puedes reducir la cantidad de un plato que ya está en preparación. Llama a un administrador.', 'error');
                    newQuantity = originalQty; // Lo regresamos a la cantidad original a la fuerza
                    $(this).val(newQuantity);
                    return; // Abortamos la ejecución, el AJAX nunca se dispara
                }

                // 🛡️ ALERTA PARA EL ADMIN: Si el Admin usa el teclado para bajar la cantidad
                if (isOldItem && isAdmin && newQuantity < originalQty) {
                    Swal.fire({
                        title: '¿Confirmar reducción?',
                        html: `Vas a reducir este ítem de <strong>${originalQty}</strong> a <strong>${newQuantity}</strong>.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sí, aplicar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $(this).val(newQuantity);
                            const promise = updateCartItem(productId, newQuantity, itemObs, isOldItem, currentUnitPrice);
                            updatePromises.push(promise);
                            promise.always(() => { updateCartTotals(); });
                        } else {
                            // Si cancela, lo regresamos a lo que estaba
                            $(this).val(originalQty);
                        }
                    });
                    return; 
                }

                // --- INICIO LÓGICA DE STOCK MANUAL ---
                let maxStock = parseFloat($(this).data('stock')) || 0;
                
                if (CONTROLAR_STOCK_ESTRICTO && newQuantity > maxStock) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Stock Insuficiente',
                        text: 'Solo hay ' + maxStock + ' unidades en stock. Avisar al administrador.'
                    });
                    newQuantity = maxStock > 0 ? maxStock : 1; 
                    $(this).val(newQuantity);
                }
                // --- FIN LÓGICA DE STOCK MANUAL ---

                const promise = updateCartItem(productId, newQuantity, itemObs, isOldItem, currentUnitPrice);
                updatePromises.push(promise);
                promise.always(function() {
                    const index = updatePromises.indexOf(promise);
                    if (index > -1) updatePromises.splice(index, 1);
                    updateCartTotals();
                });
            });

            $(document).on('change', '.cart-item-obs', function() {
                const cartItemElement = $(this).closest('.cart-item');
                const productId = String(cartItemElement.data('id'));
                const qtyInput = cartItemElement.find('.cart-item-qty');
                let currentQuantity = parseInt(qtyInput.val());
                const itemObs = $(this).val();
                //const isOldItem = cartItemElement.data('is-old-item');
                const isOldItem = cartItemElement.data('is-old-item') === true;
                const unitPriceInput = cartItemElement.find('.cart-item-unit-price');
                let currentUnitPrice = parseFloat(unitPriceInput.val().toString().replace(/[^0-9.,]/g, '').replace(',', '.')) || 0.01;

                const promise = updateCartItem(productId, currentQuantity, itemObs, isOldItem, currentUnitPrice);
                updatePromises.push(promise);
                promise.always(function() {
                    const index = updatePromises.indexOf(promise);
                    if (index > -1) updatePromises.splice(index, 1);
                    updateCartTotals();
                });
            });

            $(document).on('change', '.cart-item-unit-price', function(e) {
                const cartItemElement = $(this).closest('.cart-item');
                const productId = String(cartItemElement.data('id'));

                if (activeDiscounts[productId]) {
                    e.preventDefault();
                    return;
                }

                if ($(this).prop('readonly')) {
                    Swal.fire('Información', 'No se puede modificar el precio de este ítem, ya forma parte del pedido original.', 'info');
                    loadCartDetails();
                    return;
                }

                const qtyInput = cartItemElement.find('.cart-item-qty');
                let currentQuantity = parseInt(qtyInput.val());
                const itemObs = cartItemElement.find('.cart-item-obs').val();
                //const isOldItem = cartItemElement.data('is-old-item');
                const isOldItem = cartItemElement.data('is-old-item') === true;
                
                let newUnitPriceStr = $(this).val().toString().replace(/[^0-9.,]/g, '').replace(',', '.');
                let newUnitPrice = parseFloat(newUnitPriceStr) || 0.01;

                originalPrices[productId] = newUnitPrice;

                const promise = updateCartItem(productId, currentQuantity, itemObs, isOldItem, newUnitPrice);
                updatePromises.push(promise);
                promise.always(function() {
                    const index = updatePromises.indexOf(promise);
                    if (index > -1) updatePromises.splice(index, 1);
                    updateCartTotals();
                });
            });

            $(document).on('click', '.cart-item-remove', function() {
                const cartItemElement = $(this).closest('.cart-item');
                const productId = cartItemElement.data('id');
                const isOldItem = cartItemElement.data('is-old-item') === true;
                const productName = cartItemElement.find('.cart-item-name').text();

                if (isOldItem) {
                    Swal.fire({
                        title: 'Eliminar ítem de pedido existente',
                        html: `
                            <p>Estás a punto de eliminar <strong>${productName}</strong> del pedido. Esta acción requiere autorización de un administrador/caja y un motivo.</p>
                            <label for="swal-input-user" class="swal2-label">Usuario:</label>
                            <input type="text" id="swal-input-user" class="swal2-input" placeholder="Usuario (Mozo/Admin)" value="{{ Auth::user()->email }}">
                            <label for="swal-input-password" class="swal2-label">Contraseña de Admin/Caja:</label>
                            <input type="password" id="swal-input-password" class="swal2-input" placeholder="Contraseña">
                            <label for="swal-input-reason" class="swal2-label">Motivo de Eliminación:</label>
                            <textarea id="swal-input-reason" class="swal2-textarea" placeholder="Describe brevemente el motivo"></textarea>
                        `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        focusConfirm: false,
                        preConfirm: () => {
                            const user = Swal.getPopup().querySelector('#swal-input-user').value;
                            const password = Swal.getPopup().querySelector('#swal-input-password').value;
                            const reason = Swal.getPopup().querySelector('#swal-input-reason').value;

                            if (!user || !password || !reason) {
                                Swal.showValidationMessage('Por favor, ingresa el usuario, contraseña y un motivo.');
                                return false;
                            }
                            return { user: user, password: password, reason: reason };
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const { user, password, reason } = result.value;
                            Swal.fire({
                                title: 'Eliminando ítem...',
                                text: 'Por favor, espera.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            $.ajax({
                                url: "{{ route('kiosko.remove_old_cart_item') }}",
                                type: "POST",
                                data: {
                                    id: productId,
                                    auth_user: user,
                                    auth_password: password,
                                    reason: reason,
                                },
                                dataType: 'json',
                                success: function(response) {
                                    Swal.close();
                                    if (response.success) {
                                        if (response.redirect_to_selection) {
                                            Swal.fire({
                                                title: 'Pedido Anulado',
                                                text: response.message + ' Redirigiendo a la selección de servicio.',
                                                icon: 'success',
                                                timer: 2000,
                                                timerProgressBar: true,
                                                showConfirmButton: false
                                            }).then(() => {
                                                window.location.href = "{{ route('kiosko.seleccion_servicio') }}";
                                            });
                                        } else {
                                            loadCartDetails();
                                            Swal.fire('Eliminado!', response.message, 'success');
                                        }
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                        loadCartDetails();
                                    }
                                },
                                error: function(xhr, status, error) {
                                    Swal.close();
                                    console.error("Error al eliminar ítem antiguo:", error);
                                    let errorMessage = 'Hubo un error al procesar la solicitud. Intenta de nuevo.';
                                    if (xhr.responseJSON && xhr.responseJSON.message) {
                                        errorMessage = xhr.responseJSON.message;
                                    }
                                    Swal.fire('Error', errorMessage, 'error');
                                    loadCartDetails();
                                }
                            });
                        }
                    });
                } else {
                    Swal.fire({
                        title: '¿Eliminar producto?',
                        text: "Estás a punto de quitar este producto de tu pedido.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            removeCartItem(productId);
                        }
                    });
                }
            });

            $('#btn_clear_cart').on('click', function() {
                let currentTotalNewItems = 0;
                $('#cart_items_list .cart-item').each(function() {
                    if ($(this).data('is-old-item') === false) {
                        const qty = parseFloat($(this).find('.cart-item-qty').val());
                        const unitPriceInput = $(this).find('.cart-item-unit-price');
                        const unitPrice = parseFloat(unitPriceInput.val());
                        const icbper_applies = unitPriceInput.data('icbper-applies');
                        const icbper_val = parseFloat("{{ $icbper_val ?? 0.00 }}");
                        currentTotalNewItems += (qty * unitPrice) + (icbper_applies == 1 ? qty * icbper_val : 0);
                    }
                });
                if (currentTotalNewItems <= 0 && $('#cart_items_list .cart-item[data-is-old-item="true"]').length > 0) {
                    Swal.fire('Atención', 'No hay productos nuevos para vaciar. Si desea modificar un pedido existente, ajuste las cantidades directamente.', 'warning');
                    return;
                }
                Swal.fire({
                    title: '¿Vaciar solo los nuevos productos?',
                    text: "Se eliminarán los productos que no son parte del pedido original de tu carrito.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, vaciar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('kiosko.clear_cart') }}",
                            type: "POST",
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    loadCartDetails();
                                    Swal.fire('Listo', 'Los productos nuevos del carrito han sido vaciados.', 'success');
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("Error al vaciar carrito:", error);
                                Swal.fire('Error', 'No se pudo vaciar el carrito. Intenta de nuevo.', 'error');
                            }
                        });
                    }
                });
            });

            $('#btn_delete_order').on('click', function() {
                // Recuperamos los IDs desde la sesión de Laravel
                let pedidoId = "{{ session('kiosko_last_pedido_id') }}";
                let mesaId = "{{ session('kiosko_mesa_id') ?? '' }}"; 

                Swal.fire({
                    title: 'ELIMINAR PEDIDO COMPLETO',
                    html: `
                        <p style="font-size: 1.1em; margin-bottom: 15px;">Estás a punto de <strong>ANULAR</strong> todo el pedido actual. Esta acción requiere autorización.</p>
                        <label for="swal-input-user-del" class="swal2-label">Usuario (Mozo/Admin):</label>
                        <input type="text" id="swal-input-user-del" class="swal2-input" placeholder="Ej: admin@devsoft.com" value="{{ Auth::user()->email }}">
                        <label for="swal-input-password-del" class="swal2-label">Contraseña de Autorización:</label>
                        <input type="password" id="swal-input-password-del" class="swal2-input" placeholder="Contraseña">
                        <label for="swal-input-reason-del" class="swal2-label">Motivo de Eliminación:</label>
                        <textarea id="swal-input-reason-del" class="swal2-textarea" placeholder="Describe brevemente el motivo de la anulación"></textarea>
                    `,
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#343a40',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar todo el pedido',
                    cancelButtonText: 'Cancelar',
                    focusConfirm: false,
                    preConfirm: () => {
                        const user = Swal.getPopup().querySelector('#swal-input-user-del').value;
                        const password = Swal.getPopup().querySelector('#swal-input-password-del').value;
                        const reason = Swal.getPopup().querySelector('#swal-input-reason-del').value;

                        if (!user || !password || !reason) {
                            Swal.showValidationMessage('Por favor, ingresa el usuario, la contraseña y un motivo.');
                            return false;
                        }
                        return { user: user, password: password, reason: reason };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const { user, password, reason } = result.value;

                        Swal.fire({
                            title: 'Eliminando pedido...',
                            text: 'Por favor, espera.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: "{{ route('kiosko.eliminar_pedido_completo') }}",
                            type: "POST",
                            data: {
                                pedido_id: pedidoId,
                                mesa_id: mesaId,
                                auth_user: user,
                                auth_password: password,
                                reason: reason
                            },
                            dataType: 'json',
                            success: function(response) {
                                Swal.close();
                                if (response.success) {
                                    Swal.fire({
                                        title: '¡Pedido Eliminado!',
                                        text: response.message,
                                        icon: 'success',
                                        timer: 2500,
                                        timerProgressBar: true,
                                        showConfirmButton: false
                                    }).then(() => {
                                        // Redirige a la pantalla de selección de mesas/delivery
                                        window.location.href = "{{ route('kiosko.seleccion_servicio') }}";
                                    });
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            },
                            error: function(xhr, status, error) {
                                Swal.close();
                                let errorMessage = 'Hubo un error al intentar eliminar el pedido. Intenta de nuevo.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }
                                Swal.fire('Error', errorMessage, 'error');
                            }
                        });
                    }
                });
            });

            // ============================================================================
            // BOTÓN REIMPRIMIR ÍTEM (SOLO ADMIN) - PEGAR AQUÍ
            // ============================================================================
            $(document).on('click', '.btn-reimprimir-item', function() {
                var cartItemElement = $(this).closest('.cart-item');
                var productId = cartItemElement.data('id');
                var productName = cartItemElement.find('.cart-item-name').text().trim();

                Swal.fire({
                    title: '¿Reimprimir producto?',
                    html: '<p>Se enviará nuevamente a su impresora asignada el ticket de:</p><strong>' + productName + '</strong>',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#17a2b8',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-print"></i> Sí, reimprimir',
                    cancelButtonText: 'Cancelar'
                }).then(function(result) {
                    if (result.value) { 
                        Swal.fire({
                            title: 'Enviando a impresora...',
                            allowOutsideClick: false,
                            onBeforeOpen: function() { 
                                Swal.showLoading(); 
                            }
                        });

                        $.ajax({
                            url: "{{ route('kiosko.reimprimir_item') }}",
                            type: "POST",
                            data: { producto_id: productId },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: '¡Enviado!',
                                        text: response.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                } else {
                                    Swal.fire('Atención', response.message, 'warning');
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'Problemas de conexión con la tiquetera o el servidor.', 'error');
                            }
                        });
                    }
                });
            });
            // ================= FIN BOTÓN REIMPRIMIR =====================================

            $('#btn_send_order_direct').off('click').on('click', function(e) {
                e.preventDefault();
                let $btn = $(this);

                // 🛡️ BLOQUEO ANTI DOBLE CLIC: Si ya está procesando, ignora el segundo clic
                if ($btn.prop('disabled')) { return; }

                const currentCartTotal = parseFloat($('#cart_total_display').text().replace('Total: S/. ', ''));

                if (currentCartTotal <= 0) {
                    Swal.fire('Atención', 'No puedes enviar un pedido vacío.', 'warning');
                    return;
                }

                let originalHtml = $btn.html();
                
                // 🟢 EL TRUCO: Bloqueamos el botón INMEDIATAMENTE
                $btn.prop('disabled', true).html('<strong><i class="fas fa-spinner fa-spin"></i> PROCESANDO...</strong>');

                if (updatePromises.length > 0) {
                    $.when(...updatePromises).done(function() {
                        enviarComandaDirecta($btn, originalHtml);
                    }).fail(function() {
                        $btn.prop('disabled', false).html(originalHtml);
                        Swal.fire('Error', 'Hubo un problema al guardar algunos cambios. Por favor, revisa el carrito e intenta de nuevo.', 'error');
                    });
                } else {
                    enviarComandaDirecta($btn, originalHtml);
                }
            });

            $('#btn_send_order_to_confirm').off('click').on('click', function(e) {
                e.preventDefault();

                const currentCartTotal = parseFloat($('#cart_total_display').text().replace('Total: S/. ', ''));

                if (currentCartTotal <= 0) {
                    Swal.fire('Atención', 'No puedes continuar con un pedido vacío.', 'warning');
                    return;
                }

                let nombreClienteLlevar = '';
                @if($order_type == 'llevar')
                    nombreClienteLlevar = $('#nombre_cliente_llevar').val().trim();
                    if (nombreClienteLlevar === '') {
                        Swal.fire('Atención', 'Por favor ingresa el nombre del cliente.', 'warning');
                        $('#nombre_cliente_llevar').focus();
                        return;
                    }
                @endif

                let $btn = $(this);
                let originalHtml = $btn.html();
                
                // 🟢 EL TRUCO: Bloqueamos el botón y ponemos spinner
                $btn.prop('disabled', true).html('<strong><i class="fas fa-spinner fa-spin"></i> PROCESANDO...</strong>');

                if (updatePromises.length > 0) {
                    $.when(...updatePromises).done(function() {
                        ejecutarAccionContinuar(nombreClienteLlevar, $btn, originalHtml);
                    }).fail(function() {
                        $btn.prop('disabled', false).html(originalHtml);
                        Swal.fire('Error', 'Hubo un problema al guardar algunos cambios. Por favor, revisa el carrito e intenta de nuevo.', 'error');
                    });
                } else {
                    ejecutarAccionContinuar(nombreClienteLlevar, $btn, originalHtml);
                }
            });

            // 🟢 FUNCIÓN QUE SEPARA LLEVAR VS DELIVERY
            function ejecutarAccionContinuar(nombreCliente, btnObj, originalHtml) {
            @if($order_type == 'llevar')
                // Ya no hay Swal.fire de "Enviando comanda..." aquí
                
                $.ajax({
                    url: "{{ route('kiosko.enviar_comanda') }}",
                    type: "POST",
                    data: {
                        _token: '{{ csrf_token() }}',
                        envio_directo: true,
                        order_type: "{{ $order_type ?? 'llevar' }}",
                        nombre_cliente: nombreCliente
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            reproducirPitido();
                            // 🟢 ÚNICO MENSAJE
                            Swal.fire({
                                icon: 'success',
                                title: '¡Comanda Enviada!',
                                text: 'Redirigiendo a caja...',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                if(response.redirect) {
                                    window.location.href = response.redirect;
                                } else {
                                    window.location.href = "{{ route('kiosko.seleccion_servicio') }}";
                                }
                            });
                        } else {
                            if (btnObj) btnObj.prop('disabled', false).html(originalHtml);
                            Swal.fire('Error', response.message || 'No se pudo enviar la comanda.', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        if (btnObj) btnObj.prop('disabled', false).html(originalHtml);
                        console.error("Error al enviar comanda llevar:", error);
                        Swal.fire('Error', 'Hubo un error al procesar la comanda.', 'error');
                    }
                });
            @else
                window.location.href = "{{ route('kiosko.confirmacion_pedido') }}";
            @endif
        }

            let typingTimer;
            const doneTypingInterval = 400; // Tiempo en milisegundos (0.3 segundos)

            $('#txt_bus_pro_kiosko').on('keyup', function() {
                clearTimeout(typingTimer); // Limpia el temporizador anterior
                
                var searchText = $(this).val().trim();
                var currentCategoryId = $('.btn-category-kiosko.active').data('category-id');

                // Iniciamos la espera
                typingTimer = setTimeout(function() {
                    if (searchText.length > 0) {
                        loadProducts(0, searchText);
                    } else {
                        loadProducts(currentCategoryId);
                    }
                }, doneTypingInterval);
            });

            // También limpiamos el timer si el usuario deja de presionar teclas rápido
            $('#txt_bus_pro_kiosko').on('keydown', function() {
                clearTimeout(typingTimer);
            });

            $(document).on('click', '.btn-pagado-item', function(e) {
                let $btn = $(this);
                let idProd = String($btn.data('id'));
                let cartItemElement = $btn.closest('.cart-item');

                activePagados[idProd] = !activePagados[idProd];

                if (activePagados[idProd]) {
                    $btn.css('background-color', '#28a745').text('PAGADO');
                    cartItemElement.css('background-color', '#d4edda'); 
                } else {
                    $btn.css('background-color', '#6c757d').text('COBRAR');
                    cartItemElement.css('background-color', '#fff');
                }

                const qty = parseInt(cartItemElement.find('.cart-item-qty').val()) || 1;
                const obs = cartItemElement.find('.cart-item-obs').val();
                const isOld = cartItemElement.data('is-old-item');
                let priceStr = cartItemElement.find('.cart-item-unit-price').val().toString().replace(/[^0-9.,]/g, '').replace(',', '.');
                const price = parseFloat(priceStr) || 0.01;

                const promise = updateCartItem(idProd, qty, obs, isOld, price);
                updatePromises.push(promise);
                promise.always(function() {
                    const index = updatePromises.indexOf(promise);
                    if (index > -1) updatePromises.splice(index, 1);
                });
            });

            // ============================================================================
            // 🔄 HEARTBEAT PARA MANTENER SESIÓN ACTIVA - Previene timeout/anulación
            // ============================================================================
            // Se envía un ping cada 30 segundos para mantener la sesión viva
            // mientras el usuario está editando el pedido. Esto previene que
            // la sesión expire y cause problemas de "pedido anulado".
            setInterval(function() {
                $.ajax({
                    url: "/sanctum/csrf-cookie", // 🟢 LA MAGIA: Ruta nativa de Laravel súper ligera
                    type: "GET",
                    // OJO: Borré la línea "dataType: 'json'" porque esta ruta devuelve vacío
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        // Silencioso - solo mantiene la sesión activa
                        console.log('✓ Sesión renovada con 0 consumo de RAM/CPU');
                    },
                    error: function(xhr, status, error) {
                        // Si hay error, no hacer nada - es solo un heartbeat
                        if (status !== 'abort') {
                            console.warn('⚠ Heartbeat sin respuesta, pero sesión activa');
                        }
                    },
                    timeout: 5000 // Timeout de 5 segundos para no bloquear
                });
            }, 300000); // Cada 30 segundos
            // ============================================================================
        });
    </script>
</body>
</html>