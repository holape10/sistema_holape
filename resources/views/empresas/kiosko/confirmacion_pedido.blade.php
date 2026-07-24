<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISTEMA DE GESTION COMERCIAL</title>
    <link rel="shortcut icon" href="img/icono_hp.ico">
    <link rel="stylesheet" href="{{ asset('adminlte/bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/font-awesome6/css/all.min.css') }}"> 

    <script src="{{ asset('adminlte/bower_components/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('adminlte/bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="{{ asset('css/sweetalert2/sweetalert2.min.css') }}">

    <style>
        /* CSS proporcionado por el usuario */
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .confirmation-container {
            max-width: 950px; /* Aumentado para mejor visualización de 2 columnas */
            padding: 20px;
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 100%;
        }
        /* Nuevo estilo para agrupar 'Paga con' y 'Vuelto' */
        .payment-group {
            display: flex; /* Habilita el flexbox */
            gap: 15px; /* Espacio entre los elementos */
            align-items: flex-end; /* Alinea los ítems en la parte inferior si sus alturas difieren */
        }

        .payment-group .form-group {
            flex-grow: 1; /* Permite que cada grupo ocupe el espacio disponible */
            margin-bottom: 0; /* Elimina el margen inferior si es necesario */
        }
        h1 {
            color: #dc3545;
            font-size: 2.8em;
            margin-bottom: 15px;
        }
        h2 {
            color: #333;
            font-size: 1.8em;
            margin-bottom: 25px;
        }
        .order-summary {
            background-color: #f0f0f0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: left;
            min-height: 100%; /* Asegura que la altura coincida con la derecha */
            display: flex;
            flex-direction: column;
        }
        .order-summary h3 {
            font-size: 1.5em;
            color: #555;
            margin-top: 0;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            font-size: 1.1em;
            margin-bottom: 8px;
        }
        .order-item span:first-child {
            font-weight: bold;
        }
        .order-total {
            font-size: 2em;
            font-weight: bold;
            color: #28a745;
            margin-top: auto; /* Empuja el total hacia abajo */
            padding-top: 15px;
            text-align: right;
            border-top: 1px solid #ccc;
        }
        .action-buttons {
            display: flex;
            justify-content: space-around;
            margin-top: 30px;
            gap: 20px;
            padding: 0 15px;
        }
        .action-buttons .btn {
            font-size: 1.5em;
            padding: 20px 40px;
            border-radius: 10px;
            transition: background-color 0.3s ease, transform 0.1s ease;
            flex-grow: 1;
        }
        .btn-confirm {
            background-color: #28a745;
            color: white;
            border: none;
        }
        .btn-modify {
            background-color: #007bff;
            color: white;
            border: none;
        }
        .btn-confirm:hover, .btn-modify:hover {
            transform: translateY(-3px);
            opacity: 0.9;
        }
        .client-data-section {
            background-color: #f9f9f9;
            border: 1px dashed #ccc;
            border-radius: 8px;
            padding: 20px;
            text-align: left;
            margin-bottom: 30px;
        }
        .client-data-section h3 {
            font-size: 1.4em;
            color: #555;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .client-data-section .form-group {
            margin-bottom: 15px;
        }
        .client-data-section label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        .client-data-section .form-control {
            width: 100%;
            padding: 10px;
            font-size: 1.1em;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        
        /* Media Queries para la nueva estructura de 2 columnas */
        @media (min-width: 768px) {
            .confirmation-container {
                max-width: 950px;
            }
            .row {
                display: flex; /* Asegura que ambas columnas tengan la misma altura */
            }
            .col-md-6 {
                display: flex;
            }
        }
        @media (max-width: 767px) {
            h1 { font-size: 2em; }
            h2 { font-size: 1.5em; }
            .order-summary, .client-data-section { 
                margin-top: 20px; 
                margin-bottom: 20px;
            }
            .payment-group {
                flex-direction: column; /* Vuelve a apilar verticalmente en pantallas pequeñas */
                gap: 0;
            }
            .order-summary h3 { font-size: 1.3em; }
            .order-item { font-size: 1em; }
            .order-total { font-size: 1.6em; }
            .action-buttons { flex-direction: column; gap: 15px; padding: 0; }
            .action-buttons .btn { font-size: 1.5em; padding: 15px 30px; }
        }
        @media (max-width: 480px) {
            h1 { font-size: 1.8em; }
            h2 { font-size: 1.3em; }
            .action-buttons .btn { font-size: 1.3em; padding: 12px 25px; }
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <h1 hidden>¡ÚLTIMA REVISIÓN!</h1>
        <h2 hidden>Revise su pedido.</h2>

        <form id="enviarComandaForm" action="{{ route('kiosko.enviar_comanda') }}" method="POST">
            @csrf

            <div class="row">
                
                <div class="col-md-4">
                    <div class="order-summary">
                        <h3><i class="fas fa-shopping-cart"></i> Resumen de tu Pedido</h3>
                        
                        <p><strong>Tipo de Servicio:</strong> 
                            @if($order_type == 'salon' && $mesa_info)
                                Mesa {{ $mesa_info['nombre'] }}
                            @else
                                {{-- Aquí puedes diferenciar Llevar y Delivery si tu lógica lo permite --}}
                                {{ $order_type == 'delivery' ? 'Delivery' : 'Para Llevar' }}
                            @endif
                        </p>
                        <hr>
                        
                        <div>
                            @foreach($cart as $item)
                                <div class="order-item">
                                    <span>{{ $item['cantidad'] }}x {{ $item['nombre'] }}</span>
                                    <span>S/. {{ number_format($item['cantidad'] * $item['precio'], 2, '.', '') }}</span>
                                </div>
                                @if(!empty($item['observaciones']))
                                    <p style="font-style: italic; font-size: 0.9em; margin-left: 10px;">Obs: {{ $item['observaciones'] }}</p>
                                @endif
                            @endforeach
                        </div>
                        
                        <div class="order-total">
                            Total: S/. {{ number_format($total_venta, 2, '.', '') }}
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="client-data-section">
                        <h3><i class="fas fa-id-card"></i> Datos para - LLEVAR o DELIVERY</h3>

                        <div class="payment-group">
                            <div class="form-group">
                                <label for="cliente_tdicod">Tipo de Documento:</label>
                                <select name="cliente_tdicod" id="cliente_tdicod" class="form-control">
                                    @foreach($tipo_documentos_identidad as $tipo)
                                        <option value="{{ $tipo->tdicod }}" @if($tipo->tdicod == '1') selected @endif>{{ $tipo->tdides }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="cliente_num_doc">N° Documento:</label>
                                <div class="input-group"> 
                                    <input type="number" name="cliente_num_doc" id="cliente_num_doc" class="form-control" placeholder="DNI o RUC">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-primary btn-flat" id="btnBuscarCliente" onclick="buscarClienteDniRuc();" style="padding: 10px 15px;">
                                            <span class="fa fa-search"></span>
                                        </button>
                                    </span>
                                </div>
                            </div> 
                        </div> 

                        <div class="form-group">
                            <label for="cliente_nom">Nombre / Razón Social:</label>
                            <input type="text" name="cliente_nom" id="cliente_nom" class="form-control" placeholder="Escriba el nombre o teléfono del cliente">
                        </div>


                        <div class="payment-group">
                            <div class="form-group">
                                <label for="cliente_dir">Dirección:</label>
                                <input type="text" name="cliente_dir" id="cliente_dir" class="form-control" placeholder="Dirección">
                            </div>

                            <div class="form-group">
                                <label for="cliente_ref">Dirección Referencia:</label>
                                <input type="text" name="cliente_ref" id="cliente_ref" class="form-control" placeholder="Referencia de la Dirección ">
                            </div>
                        </div>

                        <div class="payment-group">
                            <div class="form-group">
                                <label for="cliente_tel">Celular - Telefono:</label>
                                <input type="number" name="cliente_tel" id="cliente_tel" class="form-control" placeholder="Celular Teléfono (Opcional)">
                            </div>
                            <div class="form-group">
                                <label for="Motorizado_delivery">Motorizado - Delivery:</label>
                                <input type="text" name="Motorizado_delivery" id="Motorizado_delivery" class="form-control" placeholder="Nombre del motorizado delivery ">
                            </div>
                        </div>
                        
                        <div class="payment-group">
                            <div class="form-group">
                                <label for="pagar">Paga con :</label>
                                {{-- CAMBIO: Usamos type="number" y añadimos step="0.01" --}}
                                <input type="number" name="pagar" id="pagar" class="form-control" placeholder="Monto con el que paga" step="0.01" min="0">
                            </div>
                            <div class="form-group">
                                <label for="vuelto">Vuelto :</label>
                                {{-- CAMBIO: Añadimos 'readonly' para que el usuario no pueda editarlo --}}
                                <input type="text" name="vuelto" id="vuelto" class="form-control" placeholder="Vuelto a entregar" readonly>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            
            <div class="action-buttons">
                <button type="submit" class="btn btn-confirm"><strong><i class="fas fa-paper-plane"></i> COMANDAR</strong></button>
                <button type="button" class="btn btn-modify" onclick="window.location.href='{{ route('kiosko.menu_pedido') }}'">
                    <strong><i class="fas fa-pencil-alt"></i> REVISAR</strong>
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/sweetalert2/sweetalert2.min.js') }}"></script>
    <script>
        // Buscar cliente por DNI o RUC
        function buscarClienteDniRuc() {
            var numDoc = $('#cliente_num_doc').val();
            var tdiCod = $('#cliente_tdicod').val(); 
            if (numDoc.length === 0) {
                Swal.fire('Atención', 'Por favor, ingresa un número de DNI o RUC.', 'warning');
                return;
            }
            if (tdiCod === '1' && numDoc.length !== 8) {
                Swal.fire('Atención', 'El DNI debe tener 8 dígitos.', 'warning');
                return;
            }
            if (tdiCod === '6' && numDoc.length !== 11) {
                Swal.fire('Atención', 'El RUC debe tener 11 dígitos.', 'warning');
                return;
            }

            Swal.fire({ title: 'Buscando datos...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

            $.ajax({
                url: '/autocomplete/' + numDoc, 
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    Swal.close();
                    if (response.error) {
                        Swal.fire('Error', response.error, 'error');
                    } else if (response.length > 0) {
                        var c = response[0];
                        $('#cliente_num_doc').val(c.value || '');
                        $('#cliente_nom').val(c.nom || '');
                        $('#cliente_dir').val(c.dir || '');
                        $('#cliente_tel').val(c.tel || '');
                        $('#cliente_tdicod').val(c.tdicod).change();
                        Swal.fire('Éxito', 'Datos del cliente cargados.', 'success');
                    } else {
                        Swal.fire('Información', 'No se encontraron datos.', 'info');
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire('Error', 'Hubo un problema al conectar con el servidor.', 'error');
                }
            });
        }

        // Autocompletado de nombre/teléfono de cliente
        $(document).ready(function() {


            var totalVenta = parseFloat("{{ $total_venta }}");
            var $inputPagar = $('#pagar');
            var $inputVuelto = $('#vuelto');

            // Función para calcular y mostrar el vuelto
            function calcularVuelto() {
                var montoPagado = parseFloat($inputPagar.val());
                
                // Si el monto pagado no es un número válido o está vacío, reinicia el vuelto
                if (isNaN(montoPagado) || montoPagado <= 0) {
                    $inputVuelto.val('');
                    return;
                }
                
                // Cálculo simple: Paga - Total
                var vuelto = montoPagado - totalVenta;
                
                if (vuelto < 0) {
                    // Si el vuelto es negativo (pago insuficiente)
                    $inputVuelto.val('FALTA S/. ' + Math.abs(vuelto).toFixed(2));
                    $inputVuelto.css('color', '#dc3545'); // Color rojo para indicar que falta dinero
                } else {
                    // Si el vuelto es cero o positivo
                    $inputVuelto.val(vuelto.toFixed(2));
                    $inputVuelto.css('color', '#28a745'); // Color verde
                }
            }

            // Atar la función al evento de entrada del campo 'Paga con'
            $inputPagar.on('input', calcularVuelto);


            $("#cliente_nom").autocomplete({
                source: "{{ route('buscar.cliente') }}",
                minLength: 2,
                select: function(event, ui) {
                    $("#cliente_nom").val(ui.item.nombre);
                    $("#cliente_num_doc").val(ui.item.documento);
                    $("#cliente_tel").val(ui.item.telefono);
                    $("#cliente_dir").val(ui.item.direccion);
                    return false;
                }
            });

            $('#enviarComandaForm').on('submit', function() {
                var btnConfirm = $('.btn-confirm');
                btnConfirm.prop('disabled', true); // Deshabilita el botón
                btnConfirm.html('<i class="fas fa-spinner fa-spin"></i> ENVIANDO...'); // Cambia el texto
            });
        });
    </script>
</body>
</html>