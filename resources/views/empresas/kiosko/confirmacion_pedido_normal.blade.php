<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Pedido - DEVSOFT</title>
    <link rel="shortcut icon" href="img/icono_hp.ico">
    <link rel="stylesheet" href="{{ asset('adminlte/bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/font-awesome6/css/all.min.css') }}">
    <style>
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
            max-width: 700px;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h1 {
            color: #dc3545; /* Rojo para la advertencia */
            font-size: 2.8em;
            margin-bottom: 20px;
        }
        .order-summary {
            text-align: left;
            margin-bottom: 30px;
            border: 1px solid #e9ecef;
            padding: 15px;
            border-radius: 8px;
            background-color: #f8f9fa;
        }
        .order-summary p {
            margin: 5px 0;
            font-size: 1.1em;
        }
        .order-summary .total {
            font-weight: bold;
            font-size: 1.2em;
            color: #28a745;
        }
        .form-group {
            text-align: left;
            margin-bottom: 15px;
        }
        /* Estilos específicos para el autocompletado */
        .autocomplete-wrapper {
            position: relative;
        }
        #suggestions_container {
            position: absolute;
            width: 100%;
            z-index: 1000;
            max-height: 250px;
            overflow-y: auto;
            border: 1px solid #ced4da;
            border-top: none;
            border-radius: 0 0 4px 4px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <h1><i class="fas fa-exclamation-triangle"></i> ¡Importante!</h1>
        <p class="lead">Antes de confirmar tu pedido, necesitamos algunos datos de contacto y la dirección de entrega.</p>
        
        <hr>

        <form id="formConfirmarPedido">
            {{-- Aquí iría la información básica del cliente --}}
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="cliente_nombre">Nombre Cliente (*)</label>
                        <input type="text" class="form-control" id="cliente_nombre" name="cliente_nombre" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="cliente_telefono">Teléfono (*)</label>
                        <input type="text" class="form-control" id="cliente_telefono" name="cliente_telefono" required>
                    </div>
                </div>
            </div>

            <h4 class="mt-4 mb-3">Detalles de Entrega</h4>
            
            <div class="autocomplete-wrapper">
                <div class="form-group">
                    <label for="direccion_input">Dirección de Entrega (*)</label>
                    <input type="text" class="form-control" id="direccion_input" name="direccion" placeholder="Escribe la dirección de entrega" required>
                    <div id="suggestions_container" class="list-group">
                        {{-- Aquí se mostrarán las sugerencias del autocompletado --}}
                    </div>
                    <small class="form-text text-muted">Empieza a escribir y selecciona la opción correcta para el cálculo.</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="referencia">Referencia (ej. "Casa verde frente al parque")</label>
                        <input type="text" class="form-control" id="referencia" name="referencia">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="costo_delivery_display">Costo de Delivery</label>
                        <input type="text" class="form-control" id="costo_delivery_display" value="S/ 0.00" readonly>
                    </div>
                </div>
            </div>
            
            {{-- CAMPOS OCULTOS PARA GUARDAR EN LA DB --}}
            <input type="hidden" id="latitud" name="latitud">
            <input type="hidden" id="longitud" name="longitud">
            <input type="hidden" id="costo_delivery" name="costo_delivery" value="0.00">
            <input type="hidden" id="distancia_km" name="distancia_km">
            
            <hr>

            <div class="row mt-4">
                <div class="col-md-6">
                    <button type="button" class="btn btn-block btn-danger btn-lg" onclick="window.history.back();">
                        <i class="fas fa-arrow-left"></i> Modificar Pedido
                    </button>
                </div>
                <div class="col-md-6">
                    <button type="submit" class="btn btn-block btn-success btn-lg" id="btnConfirmar">
                        <i class="fas fa-check"></i> Confirmar y Enviar a Cocina
                    </button>
                </div>
            </div>
            
        </form>
        {{-- Aquí se cerraría el formulario que confirma el pedido a KioskoController --}}

    </div>

    <script src="{{ asset('adminlte/bower_components/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('adminlte/bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>
    
    <script>
        // ⚠️ Coordenadas de tu restaurante (ORIGEN - DEBES REEMPLAZAR CON LAS COORDENADAS EXACTAS DE TU LOCAL)
        const ORIGEN_LAT = -12.046373; // Latitud de tu restaurante
        const ORIGEN_LNG = -77.042793; // Longitud de tu restaurante 

        const direccionInput = document.getElementById('direccion_input');
        const suggestionsContainer = document.getElementById('suggestions_container');

        // ----------------------------------------------------
        // 1. FUNCIÓN DE AUTOSUGERENCIA (NOMINATIM API - OPENSTREETMAP)
        // ----------------------------------------------------
        let debounceTimer;
        direccionInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const query = this.value;
            
            if (query.length < 3) { 
                suggestionsContainer.innerHTML = '';
                return;
            }

            debounceTimer = setTimeout(() => {
                // Endpoint de Nominatim
                // Filtramos por Perú ('countrycodes=pe') y limitamos a 5 resultados.
                const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&limit=5&countrycodes=pe`;
                
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        suggestionsContainer.innerHTML = '';
                        // Usamos un div con list-group para Bootstrap
                        data.forEach(item => {
                            const suggestion = document.createElement('a');
                            suggestion.classList.add('list-group-item', 'list-group-item-action', 'text-left');
                            suggestion.innerHTML = item.display_name;
                            
                            // Guardamos los datos en el elemento para usarlos al hacer clic
                            suggestion.dataset.lat = item.lat;
                            suggestion.dataset.lng = item.lon;
                            suggestion.dataset.address = item.display_name;

                            suggestion.addEventListener('click', onSuggestionClick);
                            suggestionsContainer.appendChild(suggestion);
                        });
                    })
                    .catch(error => {
                        console.error('Error en Nominatim:', error);
                        // Puedes usar Swal para notificar un error de red o API
                        // Swal.fire('Error de Búsqueda', 'No se pudo contactar al servidor de direcciones.', 'error');
                    });
            }, 300); // Espera 300ms
        });

        function onSuggestionClick(event) {
            event.preventDefault();
            const element = event.currentTarget;
            
            // 1. Rellena el campo de dirección
            direccionInput.value = element.dataset.address;
            
            // 2. Limpia las sugerencias
            suggestionsContainer.innerHTML = ''; 
            
            // 3. Guarda las coordenadas
            const lat = parseFloat(element.dataset.lat);
            const lng = parseFloat(element.dataset.lng);
            document.getElementById('latitud').value = lat;
            document.getElementById('longitud').value = lng;
            
            // 4. Calcula la distancia y el costo
            calcularDistanciaYCosto(lat, lng);
        }
        
        // Ocultar sugerencias si se hace clic fuera del campo
        document.addEventListener('click', function(e) {
            if (!direccionInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
                suggestionsContainer.innerHTML = '';
            }
        });
        

        // ----------------------------------------------------
        // 2. FUNCIÓN DE CÁLCULO DE DISTANCIA (Fórmula Haversine)
        // ----------------------------------------------------
        function calcularDistanciaYCosto(destinoLat, destinoLng) {
            // Usa el radio de la Tierra en kilómetros
            const R = 6371; 
            
            const dLat = deg2rad(destinoLat - ORIGEN_LAT);
            const dLng = deg2rad(destinoLng - ORIGEN_LNG);

            const a = 
                Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(deg2rad(ORIGEN_LAT)) * Math.cos(deg2rad(destinoLat)) * Math.sin(dLng / 2) * Math.sin(dLng / 2);

            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

            // Distancia en línea recta (KM)
            const distanciaKM = (R * c).toFixed(2); 
            document.getElementById('distancia_km').value = distanciaKM;

            // ** Lógica de Costos (AJUSTA ESTAS TARIFAS A TU NEGOCIO) **
            let costo = 0;
            const km = parseFloat(distanciaKM);
            
            if (km <= 3.00) {
                costo = 5.00; // Tarifa fija hasta 3 km
            } else if (km <= 7.00) {
                costo = 8.00; // Tarifa intermedia hasta 7 km
            } else if (km <= 10.00) {
                costo = 12.00; // Tarifa larga hasta 10 km
            } else {
                costo = 15.00; // Tarifa máxima o rechazo
                Swal.fire('Advertencia', `Esta dirección está a ${km}km. Supera nuestra zona de cobertura estándar.`, 'warning');
            }
            
            // 4. Mostrar y guardar el costo
            document.getElementById('costo_delivery_display').value = 'S/ ' + costo.toFixed(2);
            document.getElementById('costo_delivery').value = costo.toFixed(2);
        }
        
        function deg2rad(deg) {
            return deg * (Math.PI/180)
        }

    </script>
    
    {{-- Tu script para generar la precuenta (si aplica) --}}
    <script>
        $(document).ready(function() {
            // Este es el script que ya tenías para la precuenta (si está en este archivo)
            // Asegúrate de que tu lógica de envío de formulario (POST) se ejecute correctamente aquí
            
            // Ejemplo de cómo se vería la función existente que usa lastPedidoId:
            /*
            $('#btnPreCuenta').click(function() {
                var lastPedidoId = /* ... lógica para obtener el ID ... ;

                if (lastPedidoId) {
                    Swal.fire({
                        title: '¿Generar Pre-Cuenta?',
                        text: "Se enviará la orden a caja para el cobro.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, Generar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        // ... tu código de AJAX para generar precuenta ...
                    });
                } else {
                    Swal.fire('Información', 'Para solicitar una pre-cuenta, primero debes enviar tu pedido.', 'info');
                }
            });
            */
            
            // Lógica para enviar el formulario de confirmación (debes asegurarte de que esta ruta esté definida)
            $('#formConfirmarPedido').on('submit', function(e) {
                e.preventDefault();
                
                // Aquí deberías hacer la llamada AJAX para guardar el pedido en tu KioskoController
                // y enviar los datos de delivery (direccion, latitud, longitud, costo_delivery, etc.)
                
                console.log('Datos listos para enviar:', $(this).serialize());
                
                // Si la validación es correcta, lanza tu Swal de éxito y procede con el AJAX
                // Swal.fire('Éxito', 'Pedido confirmado y datos de delivery guardados.', 'success');
            });
            
        });
    </script>
</body>
</html>