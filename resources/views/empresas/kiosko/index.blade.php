<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISTEMA DE RESTAURANTES HOLAPE</title>
    <link rel="shortcut icon" href="img/icono_hp.ico">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
    <style>
        /* Estilos Base para evitar scroll general del navegador */
        html, body { 
            background-color: #f0f2f5; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            height: 100%; 
            margin: 0; 
            overflow: hidden; 
        }

        .kiosko-wrapper { 
            display: flex; 
            height: 100vh; /* Ocupa exactamente el alto de la pantalla */
            gap: 10px; 
            padding: 10px; 
            box-sizing: border-box;
        }

        /* --- COLUMNA 1: CATEGORÍAS (Izquierda) --- */
        .panel-categorias {
            width: 180px;
            background: #fff;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            box-shadow: 2px 0 5px rgba(0,0,0,0.05);
            padding: 10px;
        }

        .btn-cat-sidebar {
            width: 100%;
            margin-bottom: 8px;
            padding: 12px 10px;
            border-radius: 10px;
            border: 1px solid rgba(0,0,0,0.1);
            color: white;
            font-weight: bold;
            font-size: 0.85rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 50px;
        }

        .btn-cat-sidebar.active { border: 3px solid #333; transform: scale(1.05); }

        /* --- COLUMNA 2: PRODUCTOS (Centro) --- */
        .panel-productos {
            flex: 1; 
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .search-box { position: relative; margin-bottom: 10px; }
        .search-box i { position: absolute; left: 15px; top: 12px; color: #aaa; }
        .search-box input { padding-left: 40px; border-radius: 20px; height: 40px; }

        .grid-prods { 
            display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; 
            overflow-y: auto; padding: 5px; padding-bottom: 60px; 
        }
        .card-prod-k { 
            background: white; 
            border: none; 
            border-radius: 12px; 
            padding: 10px; 
            cursor: pointer; 
            text-align: center; 
            height: 140px; 
            display: flex; 
            flex-direction: column; 
            justify-content: space-between;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05); transition: all 0.2s; position: relative; overflow: hidden;
        }

        .card-prod-k:hover { border-color: #007bff; }

        .prod-nombre { 
            font-weight: 700; 
            font-size: 0.85rem; 
            line-height: 1.2; 
            color: #333; 
            
            /* Soporte para 4 líneas con puntos suspensivos */
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 4; 
            -webkit-box-orient: vertical;
            
            /* Centrado total */
            display: flex;
            align-items: center;    /* Centra verticalmente */
            justify-content: center; /* Centra horizontalmente */
            text-align: center;
            
            /* Altura fija para que todos los cuadros sean iguales */
            height: 4.8em; 
            margin: auto 0; /* Empuja el nombre al centro del espacio disponible en la card */
        }

        .prod-precio { 
            color: #28a745; 
            font-weight: 800; 
            font-size: 1rem; /* Bajamos de 1.1 a 1.0 */
            background: #f8f9fa;
            border-radius: 5px;
            padding: 2px 0;
        }

        /* --- COLUMNA 3: MI PEDIDO (Derecha) --- */
        .panel-der {
            width: 380px;
            background: white;
            border-radius: 15px;
            box-shadow: -2px 0 10px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column; /* Importante para separar piezas */
            height: 100%;
            overflow: hidden;
        }

        /* 3.1 Cabecera del Pedido */
        .cart-header { padding: 15px; border-bottom: 1px solid #eee; flex-shrink: 0; }
        
        /* 3.2 Lista de Items (SCROLLABLE) */
        .lista-items { 
            flex: 1; /* Crece para ocupar el espacio central */
            overflow-y: auto; 
            padding: 10px 15px; 
        }

        /* 3.3 Footer del Pedido (FIJO ABAJO) */
        .cart-footer { 
            padding: 15px; 
            background: #f8f9fa; 
            border-top: 1px solid #dee2e6; 
            flex-shrink: 0; /* No se encoge */
        }

        .item-row { border-bottom: 1px solid #eee; padding: 10px 0; }
        .btn-caja-link { background: #ff9800; color: white !important; font-weight: bold; font-size: 0.8rem; padding: 5px 12px; border-radius: 6px; }
        .total-box { font-size: 1.8rem; font-weight: 900; text-align: right; margin-bottom: 5px; }
        .btn-cobrar-k { width: 100%; background: linear-gradient(45deg, #11998e, #38ef7d); color: white; padding: 12px; font-size: 1.2rem; font-weight: bold; border: none; border-radius: 10px; cursor: pointer; }
        .input-pago { font-size: 1.1rem; font-weight: bold; text-align: right; border: 1px solid #ccc; border-radius: 5px; width: 100%; }
        .btn-control { width: 25px; height: 25px; border-radius: 50%; border: none; font-weight: bold; }
    </style>
</head>
<body>

<div class="kiosko-wrapper">

    <div class="panel-categorias">
        <h6 class="text-muted text-center mb-3">CATEGORÍAS</h6>
        <div class="btn-cat-sidebar active" style="background-color: #333;" onclick="filtrar('todas', this)">
             TODOS
        </div>
        @foreach($categorias as $c)
            @php $colorFondo = !empty($c->color) ? $c->color : '#6c757d'; @endphp
            <div class="btn-cat-sidebar" style="background-color: {{ $colorFondo }};" onclick="filtrar('{{ $c->cat_id }}', this)">
                {{ $c->cat_nom }}
            </div>
        @endforeach
    </div>

    <div class="panel-productos">
        <div class="search-box">
            <i class="fa fa-search"></i>
            <input type="text" id="input_busqueda_kiosko" class="form-control" placeholder="Buscar producto...">
        </div>

        <div class="grid-prods">
            @foreach($productos as $p)
                @php
                    $id = $p->IdProducto; $nombre = $p->pronom;
                    $precio = $p->propun ?? $p->pre_ven ?? 0;
                    $cat = $p->cat_id ?? $p->cat_sig ?? 0;
                @endphp
                <div class="card-prod-k item-prod-k" data-cat="{{ $cat }}" data-nom="{{ strtolower($nombre) }}"
                     onclick="agregar('{{ $id }}', '{{ addslashes($nombre) }}', {{ $precio }})">
                    <div class="prod-nombre">{{ $nombre }}</div>
                    <div class="prod-precio">S/ {{ number_format($precio, 2) }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="panel-der">
        <div class="cart-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4 style="font-weight: 800; color: #444; margin: 0;">Mi Pedido</h4>
                <a href="{{ url('consolacaja') }}" class="btn-caja-link"><i class="fa fa-desktop"></i> CAJA</a>
            </div>
        </div>
        
        <div class="lista-items" id="div_carrito_items">
            <div class="text-center text-muted mt-5">
                <i class="fa fa-utensils fa-3x mb-3" style="opacity: 0.1"></i><br>
                Carrito vacío
            </div>
        </div>
        
        <div class="cart-footer">
            <div class="d-flex justify-content-between text-muted small">
                <span>TOTAL A PAGAR:</span>
            </div>
            <div class="total-box" id="div_total_kiosko">S/ 0.00</div>
            
            <div class="row no-gutters mb-2">
                <div class="col-6 pr-1">
                    <small class="text-muted">Cliente</small>
                    <!--<input type="text" id="input_cliente_kiosko" class="form-control form-control-sm" placeholder="Opcional">-->
                    <input type="text" id="input_cliente_kiosko" class="form-control form-control-sm" placeholder="Cliente + N° Beeper">
                    <small class="text-muted">* Obligatorio en consumos mayores a 20</small>
                </div>
                <div class="col-6 pl-1">
                    <small class="text-muted">Paga con</small>
                    <input type="number" id="input_pago_kiosko" class="input-pago py-1" placeholder="0.00">
                </div>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span id="lbl_vuelto" style="font-weight: bold; color: #aaa;">Vuelto: S/ 0.00</span>
                <button class="btn btn-sm btn-light" onclick="limpiarCarrito()"><i class="fa fa-trash"></i></button>
            </div>

            <button class="btn-cobrar-k" onclick="enviarPedidoFinal()">
                COMANDAR <i class="fa fa-check-circle"></i>
            </button>
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let carrito = [];
    let totalGlobal = 0;
    
    function agregar(id, nombre, precio) {
        let existe = carrito.find(i => i.id == id);
        if(existe) existe.cant++;
        else carrito.push({id: id, nombre: nombre, precio: parseFloat(precio), precio_original: parseFloat(precio), descuento_50: false, cant: 1, observacion: ''});
        render();
    }

    function render() {
        let html = '';
        totalGlobal = 0;
        
        if(carrito.length === 0) {
            $('#div_carrito_items').html('<div class="text-center text-muted mt-5"><i class="fa fa-shopping-cart fa-3x mb-3" style="opacity:0.1"></i><br>Carrito vacío</div>');
        } else {
            carrito.forEach((item, idx) => {
                let subtotal = item.precio * item.cant;
                totalGlobal += subtotal;
                html += `
                    <div class="item-row">
                        <div class="d-flex justify-content-between">
                            <div style="flex:1">
                                <strong style="font-size:0.85rem">${item.nombre}</strong>
                                
                                <div class="d-flex align-items-center mt-1">
                                    <div style="color:${item.descuento_50 ? '#d35400' : '#007bff'}; font-size:0.8rem; cursor:${item.descuento_50 ? 'not-allowed' : 'pointer'}; font-weight:bold; margin-right: 10px;" onclick="editarPrecio(${idx})">
                                        S/ ${item.precio.toFixed(2)}
                                    </div>
                                    <label style="font-size: 0.75rem; color: #d35400; margin: 0; cursor: pointer; font-weight: bold;">
                                        <input type="checkbox" ${item.descuento_50 ? 'checked' : ''} onchange="toggleDescuento(${idx}, this.checked)"> 
                                        50% Desc.
                                    </label>
                                </div>
                                
                            </div>
                            <div class="d-flex align-items-center">
                                <button class="btn-control btn-danger btn-sm" onclick="cambiarCant(${idx}, -1)" style="padding:0">-</button>
                                <span class="mx-2 font-weight-bold">${item.cant}</span>
                                <button class="btn-control btn-primary btn-sm" onclick="cambiarCant(${idx}, 1)" style="padding:0">+</button>
                            </div>
                            <div class="ml-2 font-weight-bold" style="width:50px; text-align:right">${subtotal.toFixed(2)}</div>
                        </div>
                        <input type="text" class="form-control form-control-sm mt-1" style="font-size:0.7rem; background:#fff9c4" 
                               placeholder="Observación..." value="${item.observacion}" onkeyup="cambiarObs(${idx}, this.value)">
                    </div>`;
            });
            $('#div_carrito_items').html(html);
        }
        $('#div_total_kiosko').text('S/ ' + totalGlobal.toFixed(2));
        calcularVuelto();
    }

    // NUEVO: Función que aplica o quita el 50%
    function toggleDescuento(idx, activo) {
        carrito[idx].descuento_50 = activo;
        if(activo) {
            carrito[idx].precio = carrito[idx].precio_original / 2;
        } else {
            carrito[idx].precio = carrito[idx].precio_original;
        }
        render();
    }

    function cambiarCant(idx, delta) {
        carrito[idx].cant += delta;
        if(carrito[idx].cant <= 0) carrito.splice(idx, 1);
        render();
    }

    function cambiarObs(idx, val) { carrito[idx].observacion = val; }

    function editarPrecio(idx) {
        // Bloqueamos la edición manual si el descuento está activo
        if(carrito[idx].descuento_50) {
            alert("Desactiva el 50% de descuento antes de cambiar el precio manualmente.");
            return;
        }
        
        let n = prompt("Nuevo precio:", carrito[idx].precio);
        if(n && !isNaN(n)) { 
            let nuevoPrecio = parseFloat(n);
            carrito[idx].precio = nuevoPrecio; 
            carrito[idx].precio_original = nuevoPrecio; // Actualizamos el original también
            render(); 
        }
    }

    function calcularVuelto() {
        let p = parseFloat($('#input_pago_kiosko').val()) || 0;
        let v = p - totalGlobal;
        $('#lbl_vuelto').text('Vuelto: S/ ' + (v > 0 ? v.toFixed(2) : '0.00')).css('color', v < 0 ? 'red' : 'green');
    }

    $('#input_pago_kiosko').on('input', calcularVuelto);

    function filtrar(catId, btn) {
        $('.btn-cat-sidebar').removeClass('active');
        $(btn).addClass('active');
        if(catId == 'todas') $('.item-prod-k').show();
        else { $('.item-prod-k').hide(); $('.item-prod-k[data-cat="'+catId+'"]').show(); }
    }

    $('#input_busqueda_kiosko').on('keyup', function(){
        let t = $(this).val().toLowerCase();
        $('.item-prod-k').each(function(){ $(this).toggle($(this).data('nom').indexOf(t) > -1); });
    });

    function limpiarCarrito() { if(confirm("¿Vaciar carrito?")) { carrito = []; render(); } }

    function enviarPedidoFinal() {
        if(carrito.length == 0) {
            return alert("El carrito está vacío. Agrega productos antes de comandar.");
        }

        let clienteBeeper = $('#input_cliente_kiosko').val().trim();

        if(totalGlobal > 20) {
            if(clienteBeeper.length < 3) {
                alert("¡Atención! Para pedidos mayores a 20, es obligatorio colocar el nombre del cliente y el número del BEEPER.");
                $('#input_cliente_kiosko').focus();
                return;
            }
        }

        $('.btn-cobrar-k').prop('disabled', true).text('PROCESANDO...');
        
        $.ajax({
            url: "{{ route('kiosko.enviar') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                nombre_cliente: clienteBeeper, 
                total_venta: totalGlobal,
                items: carrito.map(i => ({ 
                    id: i.id, 
                    nombre: i.nombre, 
                    cantidad: i.cant, 
                    precio: i.precio, 
                    observacion: i.observacion 
                }))
            },
            success: function(res) { 
                if(res.status == 'success') {
                    window.location.href = res.redirect_url; 
                } else { 
                    alert(res.message); 
                    $('.btn-cobrar-k').prop('disabled', false).text('COMANDAR');
                } 
            },
            error: function() { 
                alert("Error de conexión"); 
                $('.btn-cobrar-k').prop('disabled', false).text('COMANDAR'); 
            }
        });
    }
</script>

</body>
</html>