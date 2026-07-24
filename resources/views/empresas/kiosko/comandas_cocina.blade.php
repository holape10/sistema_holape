<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DEVSOFT - Comandas Cocina</title>
    <link rel="shortcut icon" href="{{ asset('img/icono.ico') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/font-awesome6/css/all.min.css') }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
        }
        .header-cocina {
            background-color: #f39c12; /* Naranja para destacar la cocina */
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 2.2em;
            font-weight: bold;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .comandas-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .comanda-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .comanda-table th, .comanda-table td {
            border: 1px solid #ccc;
            padding: 12px;
            text-align: left;
            vertical-align: top;
            font-size: 1.1em;
        }
        .comanda-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 1.2em;
            color: #333;
        }
        .comanda-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .comanda-table tbody tr.pedido-separator {
            background-color: #e0e0e0;
            font-weight: bold;
            color: #555;
        }
        .comanda-table td.id-pedido,
        .comanda-table td.ubicacion,
        .comanda-table td.cantidad-col {
            font-weight: bold;
            text-align: center;
        }
        /* ESTILO PARA LA CELDA DEL TEMPORIZADOR */
        .comanda-table td.fecha-hora {
            font-weight: bold;
            text-align: center;
            font-size: 1.8em; /* Letra más grande para el temporizador */
            color: #d33; /* Color inicial rojo para destacar */
            white-space: nowrap; /* Evita que se rompa el texto */
        }
        .comanda-table td.fecha-hora.time-up {
            color: #dc3545; /* Rojo más fuerte si el tiempo se agotó */
            animation: blinker 1s linear infinite; /* Animación de parpadeo */
        }
        .comanda-table td.productos {
            font-size: 1.1em;
        }
        .btn-despachar {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.2s ease;
        }
        .btn-despachar:hover {
            background-color: #218838;
        }
        .item-despachado {
            text-decoration: line-through;
            color: #888;
        }
        /* Estilos para "PARA LLEVAR" y "DELIVERY" */
        .ubicacion-llevar {
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.9em;
            font-weight: bold;
            display: inline-block;
        }

        .ubicacion-delivery {
            background-color: #066d0a;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.9em;
            font-weight: bold;
            display: inline-block;
        }
        /* Estilos para resaltar filas de pedidos diferentes */
        .row-group-odd { background-color: #ffffff; }
        .row-group-even { background-color: #f0f0f0; }

        /* Animación de parpadeo */
        @keyframes blinker {
            50% { opacity: 0; }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .comanda-table, .comanda-table tbody, .comanda-table tr, .comanda-table td, .comanda-table th {
                display: block;
            }
            .comanda-table thead {
                display: none;
            }
            .comanda-table tr {
                margin-bottom: 15px;
                border: 1px solid #ddd;
                border-radius: 8px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            }
            .comanda-table td {
                text-align: right;
                padding-left: 50%;
                position: relative;
            }
            .comanda-table td::before {
                content: attr(data-label);
                position: absolute;
                left: 10px;
                width: calc(50% - 20px);
                padding-right: 10px;
                white-space: nowrap;
                text-align: left;
                font-weight: bold;
                color: #555;
            }
            /* Asegura que el contenido quede a la derecha para las columnas */
            .comanda-table td.id-pedido,
            .comanda-table td.fecha-hora,
            .comanda-table td.cantidad-col,
            .comanda-table td.ubicacion,
            .comanda-table td.productos,
            .comanda-table td.opciones {
                text-align: right;
            }
        }
    </style>
</head>
<body>
    <div class="header-cocina">
        <i class="fas fa-utensils"></i> REGISTRO DE COMANDAS <i class="fas fa-bell"></i>
    </div>

    <div class="comandas-container">
        <div id="comandas_table_container">
            {{-- Las comandas se cargarán aquí por AJAX --}}
        </div>
    </div>

    <script src="{{ asset('adminlte/bower_components/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('adminlte/bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const DEFAULT_COOK_TIME_SECONDS = 900; // 15 minutos por defecto (15 * 60)
        let countdownIntervals = {}; // Almacena los intervalos de cada temporizador

        // Función para formatear segundos a MM:SS
        function formatTime(totalSeconds) {
            if (totalSeconds <= 0) {
                return "00:00";
            }
            const minutes = Math.floor(totalSeconds / 60);
            const seconds = totalSeconds % 60;
            return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        }

        // Función para actualizar un temporizador individual
        function updateCountdown(pedDetId, startTimeISO) {
            const row = $(`tr[data-ped-det-id="${pedDetId}"]`);
            const timerCell = row.find('.timer-display');

            if (timerCell.length === 0) {
                clearInterval(countdownIntervals[pedDetId]);
                delete countdownIntervals[pedDetId];
                return;
            }

            const itemTime = new Date(startTimeISO);
            const now = new Date();

            const elapsedSeconds = Math.floor((now.getTime() - itemTime.getTime()) / 1000);
            const remainingSeconds = DEFAULT_COOK_TIME_SECONDS - elapsedSeconds;

            if (remainingSeconds <= 0) {
                timerCell.text("00:00").addClass('time-up');
            } else {
                timerCell.text(formatTime(remainingSeconds)).removeClass('time-up');
            }
        }


        let currentComandasData = []; // Para almacenar el estado actual y compararlo

        function loadComandas() {
            $.ajax({
                url: "{{ route('kiosko.get_comandas_cocina_json') }}", // Usamos la nueva ruta JSON
                type: "GET",
                dataType: 'json',
                success: function(response) {
                    // Solo actualiza si hay cambios significativos para evitar parpadeos
                    if (JSON.stringify(response) !== JSON.stringify(currentComandasData)) {
                        currentComandasData = response; // Actualizar el estado
                        renderComandas(response);

                        // Limpiar intervalos antiguos para evitar duplicados
                        for (const id in countdownIntervals) {
                            if (countdownIntervals.hasOwnProperty(id)) {
                                clearInterval(countdownIntervals[id]);
                                delete countdownIntervals[id];
                            }
                        }

                        // Iniciar nuevos temporizadores
                        response.forEach(comanda => {
                            if (comanda.fecha_hora_item && !comanda.fecha_hora_despacho) { // Solo iniciar si no está despachado
                                // Guardar el intervalo para poder limpiarlo más tarde
                                countdownIntervals[comanda.ped_det_id] = setInterval(() => {
                                    updateCountdown(comanda.ped_det_id, comanda.fecha_hora_item);
                                }, 1000); // Actualizar cada segundo
                                // Actualizar inmediatamente para que no haya un retraso inicial de 1 segundo
                                updateCountdown(comanda.ped_det_id, comanda.fecha_hora_item);
                            }
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error al cargar comandas:", error);
                }
            });
        }

        function renderComandas(comandas) {
        let html = `
            <table class="comanda-table">
                <thead>
                    <tr>
                        <th>ITEM</th>
                        <th>FECHA HORA</th>
                        <th>UBICACIÓN</th>
                        <th>CANT.</th>
                        <th>PRODUCTOS</th>
                        <th>OPCIONES</th>
                    </tr>
                </thead>
                <tbody>
        `;
        let lastPedId = null;
        let rowGroupClass = 'row-group-odd';

        comandas.forEach(comanda => {
            const originalTime = new Date(comanda.fecha_hora_item).toLocaleString('es-PE', { hour: '2-digit', minute: '2-digit', hour12: true });

            let ubicacionHtml = '';
            if (comanda.tipo_pedido === 'Llevar') {
                ubicacionHtml = `<span class="ubicacion-llevar">PARA LLEVAR</span> <br> ${comanda.cliente_nombre ? comanda.cliente_nombre : ''}`;
            } else if (comanda.tipo_pedido === 'Delivery') {
                ubicacionHtml = `<span class="ubicacion-delivery">DELIVERY</span> <br> ${comanda.cliente_nombre ? comanda.cliente_nombre : ''}`;
            } else {
                ubicacionHtml = comanda.mesa_nombre ? comanda.mesa_nombre : 'N/A';
            }

            const isDespachado = comanda.fecha_hora_despacho !== null;
            const itemClass = isDespachado ? 'item-despachado' : '';
            const buttonDisabled = isDespachado ? 'disabled' : '';
            const buttonText = isDespachado ? 'DESPACHADO' : 'DESPACHAR';

            // ✨ CORRECCIÓN DE COLORES: Cambia el tono de la fila de forma inteligente si el ID de pedido es diferente al anterior
            if (lastPedId !== comanda.ped_id) {
                if (lastPedId !== null) {
                    rowGroupClass = (rowGroupClass === 'row-group-odd') ? 'row-group-even' : 'row-group-odd';
                }
                lastPedId = comanda.ped_id;
            }

            html += `
                <tr class="${rowGroupClass}" data-ped-det-id="${comanda.ped_det_id}">
                    <td data-label="ID PEDIDO" class="id-pedido">${comanda.ped_id}</td>
                    <td data-label="FECHA HORA" class="fecha-hora">
                        <span class="original-time">${originalTime}</span>
                        <span class="timer-display" data-start-time="${comanda.fecha_hora_item}"></span>
                    </td>
                    <td data-label="UBICACIÓN" class="ubicacion">${ubicacionHtml}</td>
                    <td data-label="CANTIDAD" class="cantidad-col ${itemClass}">${comanda.cantidad}</td>
                    <td data-label="PRODUCTOS" class="productos ${itemClass}">
                        ${comanda.producto_nombre} ${comanda.producto_observacion ? `(${comanda.producto_observacion})` : ''}
                    </td>
                    <td data-label="OPCIONES" class="opciones">
                        <button type="button" class="btn btn-despachar" data-ped-det-id="${comanda.ped_det_id}" ${buttonDisabled}>
                            ${buttonText}
                        </button>
                    </td>
                </tr>
            `;
        });

        html += `
                </tbody>
            </table>
        `;
        $('#comandas_table_container').html(html);
    }

        // Delegación de eventos para el botón despachar
        $(document).on('click', '.btn-despachar:not(:disabled)', function() {
            const pedDetId = $(this).data('ped-det-id');
            const button = $(this);

            Swal.fire({
                title: '¿Marcar como despachado?',
                text: "Esta acción marcará el ítem como preparado.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, despachar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('kiosko.despachar_item_comanda') }}",
                        type: "POST",
                        data: { ped_det_id: pedDetId },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Despachado', 'El ítem ha sido marcado como despachado.', 'success');
                                // Forzamos la recarga para que el item desaparezca de la lista
                                loadComandas();
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error al despachar ítem:", error);
                            Swal.fire('Error', 'No se pudo despachar el ítem. Intenta de nuevo.', 'error');
                        }
                    });
                }
            });
        });

        $(document).ready(function() {
            loadComandas(); // Cargar las comandas al iniciar la página
            setInterval(loadComandas, 5000); // Actualizar cada 5 segundos
        });
    </script>
</body>
</html>