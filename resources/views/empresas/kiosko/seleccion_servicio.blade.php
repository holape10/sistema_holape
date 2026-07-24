<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISTEMA DE GESTION COMERCIAL</title>
    <link rel="shortcut icon" href="img/icono_hp.ico">
    <link rel="shortcut icon" href="{{ asset('img/icono.ico') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="{{ asset('adminlte/bower_components/jquery/dist/jquery.min.js') }}"></script>

    <link rel="stylesheet" href="{{ asset('adminlte/bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/font-awesome6/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/bower_components/Ionicons/css/ionicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/AdminLTE.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/skins/_all-skins.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/bower_components/morris.js/morris.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/bower_components/jvectormap/jquery-jvectormap.css') }}">
    <link rel="stylesheet"
        href="{{ asset('adminlte/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('adminlte/bower_components/bootstrap-daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css') }}">

    <link rel="stylesheet" href="{{ asset('css/sweetalert2/sweetalert2.min.css') }}">
    <!--<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>-->


    <!--<link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">-->
    <style>
        /* Tus estilos CSS existentes (los que ya tenías) */
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }

        .container-fluid {
            padding-top: 15px;
            padding-bottom: 15px;
        }

        h2 {
            text-align: center;
            color: #3498db;
            margin-bottom: 20px;
            font-size: 2.5em;
        }

        .pisos-selector {
            display: flex;
            overflow-x: auto;
            white-space: nowrap;
            padding: 10px 0;
            margin-bottom: 20px;
            background-color: #e9ecef;
            border-radius: 8px;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        /* Efecto de pulso para solicitudes de Cuentas Separadas */
        @keyframes pulse-separadas {
            0% { box-shadow: 0 0 0 0 rgba(111, 66, 193, 0.7); }
            70% { box-shadow: 0 0 0 15px rgba(111, 66, 193, 0); }
            100% { box-shadow: 0 0 0 0 rgba(111, 66, 193, 0); }
        }

        .mesa-solicitud-cs {
            animation: pulse-separadas 1.5s infinite;
            border: 4px solid #6f42c1 !important; /* El color púrpura de tus botones */
        }

        /* Color para el botón de Cuentas Separadas */
        .swal2-styled.btn-separadas {
            background-color: #6f42c1 !important; /* Color Púrpura */
            color: white !important;
        }

        .swal2-styled.btn-separadas:hover {
            background-color: #59359a !important;
        }

        /* Ajuste para que 4 botones quepan mejor en la fila si la pantalla es pequeña */
        .button-row-top {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 5px; 
        }

        .pisos-selector .btn {
            flex-shrink: 0;
            margin: 0 5px;
            font-size: 1.2em;
            padding: 12px 25px;
            border-radius: 8px;
            background-color: #6c757d;
            color: white;
            transition: background-color 0.2s ease;
        }

        .pisos-selector .btn.active,
        .pisos-selector .btn:hover {
            background-color: #007bff;
        }

        .mesas-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            padding: 10px;
        }

        .btn-mesa-kiosko {
        width: 150px;
        height: 100px;
        font-size: 1.3em; /* Le bajé un pelito el tamaño para que encaje mejor */
        font-weight: bold;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: white;
        transition: transform 0.1s ease, box-shadow 0.1s ease;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        cursor: pointer;
        
        /* ESTOS 3 SON LA CLAVE PARA QUE NO SE SALGA EL TEXTO */
        white-space: normal; 
        word-wrap: break-word; 
        line-height: 1.1; 
        padding: 5px;
    }

        .btn-mesa-kiosko.libre {
            background-color: #52BE80;
        }

        .btn-mesa-kiosko.ocupado,
        .btn-mesa-kiosko.cuenta {
            background-color: #E74C3C;
            cursor: pointer;
        }

        /*.btn-mesa-kiosko.cuenta {
            background-color: #F4D03F;
            color: #333;
        }*/

        /* Agrega esto dentro de tus estilos en seleccion_servicio.blade.php */
        .btn-mesa-kiosko.cuenta {
            background-color: #ffbf00 !important; /* Color Ámbar */
            border-color: #e6ac00 !important;
            color: white !important;
        }

        /* Opcional: un efecto cuando pasas el mouse */
        .btn-mesa-kiosko.cuenta:hover {
            background-color: #e6ac00 !important;
        }

        .btn-mesa-kiosko:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        .button-group-bottom { /* Nuevo contenedor para los botones de abajo */
            text-align: center;
            margin-top: 30px;
            margin-bottom: 30px;
            display: flex; /* Usar flexbox para centrar y espaciar */
            justify-content: center; /* Centrar horizontalmente */
            gap: 20px; /* Espacio entre los botones */
            flex-wrap: wrap; /* Permitir que los botones se envuelvan en pantallas pequeñas */
        }

        .btn-llevar-kiosko, .btn-delivery-kiosko { /* Estilos comunes para ambos botones */
            color: white;
            font-size: 2.2em;
            padding: 20px 40px;
            border-radius: 10px;
            transition: background-color 0.3s ease, transform 0.1s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            border: none;
            cursor: pointer;
            flex-shrink: 0; /* Evitar que se encojan en flexbox */
        }

        .btn-llevar-kiosko {
            background-color: #007bff; /* Naranja */
        }

        .btn-llevar-kiosko:hover {
            background-color: #066d0a;
            transform: translateY(-3px);
        }
        
        .btn-delivery-kiosko {
            background-color: #28a745; /* Un verde, puedes elegir otro color si prefieres */
        }

        .btn-delivery-kiosko:hover {
            background-color: #218838;
            transform: translateY(-3px);
        }

        /* SweetAlert custom styles for multi-button dialog */
        /* Importante: Ocultamos las acciones nativas de SweetAlert para gestionarlas manualmente */
        .swal2-actions {
            display: none !important;
        }

        /* Contenedor principal para los botones que queremos controlar */
        .swal2-html-container + .swal2-footer { /* Selecciona el footer si está justo después del html-container */
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            display: flex;
            flex-direction: column; /* Queremos las dos filas apiladas verticalmente */
            align-items: center; /* Centra las filas */
        }

        /* Fila superior de botones (AGREGAR, PRECUENTA, COBRAR) */
        .button-row-top {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 8px; /* Espacio entre los botones */
            margin-bottom: 10px; /* Espacio entre la fila superior y la inferior */
            width: 100%; /* Ocupa todo el ancho del footer */
        }

        /* Fila inferior de botones (Cambiar Mesa, Unir Mesa, Cerrar) */
        .button-row-bottom {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 8px; /* Espacio entre los botones */
            width: 100%;
        }

        /* Estilos generales para todos los botones personalizados del SweetAlert */
        .swal2-styled.custom-swal-button {
            min-width: 120px;
            padding: 10px 20px;
            font-size: 1.1em;
            border-radius: 5px;
            cursor: pointer;
            border: none; /* Quitamos el borde por defecto */
            color: white; /* Color de texto por defecto para botones personalizados */
            transition: background-color 0.2s ease, transform 0.1s ease;
        }

        .swal2-styled.custom-swal-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        /* Colores específicos para cada tipo de botón */
        .swal2-styled.btn-agregar {
            background-color: #28a745 !important; /* Verde */
        }
        .swal2-styled.btn-precuenta {
            background-color: #007bff !important; /* Azul */
        }
        .swal2-styled.btn-cobrar {
            background-color: #dc3545 !important; /* Rojo */
        }
        .swal2-styled.btn-cambiar-mesa {
            background-color: #6c757d !important; /* Gris oscuro */
        }
        .swal2-styled.btn-unir-mesa {
            background-color: #17a2b8 !important; /* Teal/Azul claro */
        }
        .swal2-styled.btn-cerrar {
            background-color: #ffc107 !important; /* Amarillo */
            color: #333 !important; /* Texto oscuro para que resalte */
        }
        /* NUEVO: Estilo para el botón de Eliminar Pedido Completo */
        .swal2-styled.btn-eliminar-pedido {
            background-color: #dc3545 !important; /* Rojo, similar a Cobrar */
        }


        .takeaway-delivery-orders-section {
            margin-top: 30px;
            background-color: #f2f2f2;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .takeaway-delivery-orders-section h3 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
            font-size: 1.8em;
            font-weight: bold;
        }

        .takeaway-delivery-orders-table {
            width: 100%;
            border-collapse: collapse;
        }
        .takeaway-delivery-orders-table th, .takeaway-delivery-orders-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            vertical-align: middle;
        }
        .takeaway-delivery-orders-table th {
            background-color: #6c757d;
            color: white;
        }
        .takeaway-delivery-orders-table .order-type-label {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: bold;
        }
        .takeaway-delivery-orders-table .order-type-label.llevar {
            background-color: #007bff;
            color: white;
        }
        .takeaway-delivery-orders-table .order-type-label.delivery {
            background-color: #066d0a;
            color: white;
        }
        .takeaway-delivery-orders-table .btn-cobrar-directo {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9em;
        }
        .takeaway-delivery-orders-table .btn-cobrar-directo:hover {
            opacity: 0.9;
        }

        /* Mejora los estilos de los botones de editar y cobrar */
        .btn-editar-directo {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 0.9em;
            margin-right: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .btn-editar-directo:hover {
            background-color: #0056b3;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .btn-cobrar-directo {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 0.9em;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .btn-cobrar-directo:hover {
            background-color: #218838;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        /* Iconos en los botones */
        .btn-editar-directo i,
        .btn-cobrar-directo i {
            margin-right: 5px;
        }

        /* Media queries para responsividad */
        @media (max-width: 768px) {
            h2 {
                font-size: 2em;
            }

            .pisos-selector .btn {
                font-size: 1em;
                padding: 10px 15px;
            }

            .btn-mesa-kiosko {
                width: 120px;
                height: 80px;
                font-size: 1.2em;
            }

            .button-group-bottom {
                flex-direction: column; /* Apila los botones en columnas en móviles */
                gap: 10px; /* Ajusta el espacio para columna */
            }

            .btn-llevar-kiosko, .btn-delivery-kiosko {
                font-size: 1.8em;
                padding: 15px 30px;
                width: 100%; /* Ocupa todo el ancho disponible */
            }
             /* Ajustes para SweetAlert en pantallas pequeñas */
            .swal2-styled.custom-swal-button {
                font-size: 1em;
                padding: 8px 15px;
                min-width: unset;
                flex-grow: 1; /* Permite que crezcan para ocupar el espacio disponible */
            }
            .takeaway-delivery-orders-table thead {
                display: none;
            }
            .takeaway-delivery-orders-table, .takeaway-delivery-orders-table tbody, .takeaway-delivery-orders-table tr, .takeaway-delivery-orders-table td {
                display: block;
                width: 100%;
            }
            .takeaway-delivery-orders-table tr {
                margin-bottom: 15px;
                border: 1px solid #ddd;
                border-radius: 8px;
            }
            .takeaway-delivery-orders-table td {
                text-align: right;
                padding-left: 50%;
                position: relative;
            }
            .takeaway-delivery-orders-table td::before {
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
        }

        @media (max-width: 480px) {
            .btn-mesa-kiosko {
                width: 90px;
                height: 70px;
                font-size: 1em;
            }

            .btn-llevar-kiosko, .btn-delivery-kiosko {
                font-size: 1.5em;
                padding: 12px 25px;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid">


        
        <h2 style="position: relative; padding-bottom: 10px;">
            Selecciona tu Mesa o Tipo de Pedido 
            
            <div style="position: absolute; right: 15px; top: 0; display: flex; gap: 10px;">
                <button class="btn btn-warning" id="btn_abrir_reservas" style="font-weight: bold; border-radius: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <i class="fa fa-calendar-check-o"></i> Reservas <span class="badge" id="badge_reservas" style="background: red;">0</span>
                </button>

                @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('caja'))
                <a href="/consolacaja" class="btn btn-info" style="font-weight: bold; border-radius: 20px;">
                    CONSOLA CAJA
                </a>
                @endif 

                @if(Auth::user()->hasRole('mozo'))
                <a href="/logout" class="btn btn-danger" style="font-weight: bold; border-radius: 20px;">
                    SALIR
                </a>
                @endif
            </div>
        </h2>

        <p class="text-center" style="font-size: 1.2em; color: #555; margin-bottom: 20px;">
            <strong>Usuario conectado:</strong> {{Auth::user()->name}} {{Auth::user()->apeusu}}
        </p>

        <div class="modal fade" id="modal-reservas-hoy" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
                    <div class="modal-header" style="background-color: #f39c12; color: white;">
                        <h4 class="modal-title" style="font-weight: bold;"><i class="fa fa-calendar"></i> Reservas Pendientes de Hoy</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 1;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="background-color: #f8f9fa;">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="tabla_reservas_hoy" style="background: white;">
                                <thead style="background: #2c3e50; color: white;">
                                    <tr>
                                        <th class="text-center">Hora</th>
                                        <th>Cliente</th>
                                        <th class="text-center">Zona / Mesa</th>
                                        <th class="text-center">Personas</th>
                                        <th class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td colspan="5" class="text-center text-muted">Cargando reservas...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        


        <div class="pisos-selector">
            @foreach($pisos as $piso)
            <button type="button" class="btn btn-secondary piso-btn {{ $piso->pis_id == $primerPisoId ? 'active' : '' }}"
                data-piso-id="{{ $piso->pis_id }}">
                <strong>{{ $piso->pis_nom }}</strong>
            </button>
            @endforeach
        </div>

        <div id="mesas_container" class="mesas-grid">
            {{-- Las mesas se cargarán aquí inicialmente y vía AJAX --}}
            @include('empresas.kiosko.partials.mesas_grid', ['mesas' => $mesas])
        </div>

        <div class="button-group-bottom"> {{-- Nuevo contenedor para agrupar los botones de abajo --}}
            <button type="button" class="btn btn-llevar-kiosko" id="btn_para_llevar">
                <strong>PARA LLEVAR</strong>
            </button>
            
            @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('caja'))
            <button type="button" class="btn btn-delivery-kiosko" id="btn_delivery"> {{-- Nuevo botón DELIVERY --}}
                <strong>DELIVERY</strong>
            </button>
            @endif
            
        </div>
        <div class="takeaway-delivery-orders-section">
            <h3>Pedidos PARA LLEVAR y DELIVERY Activos</h3>
            <div id="takeaway_delivery_orders_container">
                <p class="text-center text-muted">Cargando pedidos...</p>
            </div>
        </div>
    </div>

    {{-- MODAL PARA CAMBIAR MESA --}}
    <div class="modal fade" id="modal-cambiar-mesa" tabindex="-1" role="dialog" aria-labelledby="modalCambiarMesaLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modalCambiarMesaLabel">Cambiar Mesa</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="frm_cambiar_mesa" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="mes_id_act" id="mes_id_act_modal">
                        <input type="hidden" name="ped_id_act" id="ped_id_act_modal">
                        <p>Mesa Actual: <strong id="mes_act_modal"></strong></p>
                        <div class="form-group">
                            <label for="mesas_desocupadas_list">Selecciona nueva mesa:</label>
                            <select name="mesas" id="mesas_desocupadas_list" class="form-control">
                                {{-- Las opciones se cargarán aquí vía AJAX --}}
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btnConfirmarCambioMesa">Confirmar
                            Cambio</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL PARA UNIR MESAS --}}
    <div class="modal fade" id="modal-unir-mesas" tabindex="-1" role="dialog" aria-labelledby="modalUnirMesasLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modalUnirMesasLabel">Unir Mesas</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="frm_unir_mesas" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="mes_id_act_unir" id="mes_id_act_unir_modal">
                        <input type="hidden" name="ped_id_act_unir" id="ped_id_act_unir_modal">
                        <p>Mesa Principal: <strong id="mes_act_unir_modal"></strong></p>
                        <div class="form-group">
                            <label for="mesas_para_unir_list">Selecciona la mesa libre a unir:</label>
                            <select name="mes_unir" id="mesas_para_unir_list" class="form-control">
                                {{-- Las opciones se cargarán aquí vía AJAX --}}
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success" id="btnConfirmarUnirMesas">Unir Mesa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('adminlte/bower_components/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('adminlte/bower_components/jquery-ui/jquery-ui.min.js') }}"></script>
    <script>
        $.widget.bridge('uibutton', $.ui.button);
    </script>
    <script src="{{ asset('adminlte/bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>

    <script src="{{ asset('adminlte/bower_components/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('adminlte/bower_components/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset('adminlte/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}">
    </script>
    <script src="{{ asset('adminlte/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js') }}"></script>
    <script src="{{ asset('adminlte/bower_components/jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
    <script src="{{ asset('adminlte/bower_components/fastclick/lib/fastclick.js') }}"></script>
    <script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>    

    <script src="{{ asset('js/sweetalert2/sweetalert2.min.js') }}"></script>
    

    <script>
        // FUNCIÓN GLOBAL PARA EDITAR PEDIDOS LLEVAR/DELIVERY
        /*function editarPedidoLlevarDelivery(pedidoId, tipoServicio) {
            console.log('Editando pedido:', pedidoId, 'Tipo:', tipoServicio);
            
            Swal.fire({
                title: 'Confirmar Edición',
                html: `
                    <p>¿Deseas editar el pedido ID <strong>${pedidoId}</strong>?</p>
                    <p>Tipo: <strong>${tipoServicio.toUpperCase()}</strong></p>
                    <p>Serás redirigido al menú de edición del pedido.</p>
                `,
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#007bff',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, Editar',
                cancelButtonText: 'Cancelar',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar loading
                    Swal.fire({
                        title: 'Cargando...',
                        text: 'Preparando edición del pedido',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Determinar el tipo de servicio
                    const orderType = tipoServicio.toLowerCase() === 'delivery' ? 'delivery' : 'llevar';
                    
                    console.log('Enviando datos:', {
                        order_type: orderType,
                        pedido_id: pedidoId
                    });

                    // Guardar en sesión y redireccionar
                    $.ajax({
                        url: "{{ route('kiosko.set_service_data') }}",
                        type: "POST",
                        data: {
                            order_type: orderType,
                            mesa_id: null,
                            mesa_nombre: orderType.toUpperCase(),
                            pedido_id: pedidoId,
                            _token: '{{ csrf_token() }}'
                        },
                        dataType: 'json',
                        success: function(response) {
                            console.log('Respuesta del servidor:', response);
                            Swal.close();
                            if (response.success) {
                                // Redireccionar al menú de edición
                                window.location.href = "{{ route('kiosko.menu_pedido') }}";
                            } else {
                                Swal.fire('Error', response.message || 'Error desconocido', 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error AJAX al editar pedido:", error);
                            console.log("Respuesta XHR:", xhr);
                            Swal.close();
                            Swal.fire({
                                title: 'Error',
                                html: `
                                    <p>Hubo un problema al editar el pedido.</p>
                                    <p><strong>Detalle:</strong> ${error}</p>
                                    <p>Intenta de nuevo o contacta al administrador.</p>
                                `,
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        }*/

        function editarPedidoLlevarDelivery(pedidoId, tipoServicio) {
    console.log('Editando pedido:', pedidoId, 'Tipo:', tipoServicio);
    
    // Mostrar loading inmediatamente sin confirmación
    Swal.fire({
        title: 'Cargando...',
        text: 'Preparando edición del pedido',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Determinar el tipo de servicio
    const orderType = tipoServicio.toLowerCase() === 'delivery' ? 'delivery' : 'llevar';
    
    console.log('Enviando datos:', {
        order_type: orderType,
        pedido_id: pedidoId
    });

    // Guardar en sesión y redireccionar directamente
    $.ajax({
        url: "{{ route('kiosko.set_service_data') }}",
        type: "POST",
        data: {
            order_type: orderType,
            mesa_id: null,
            mesa_nombre: orderType.toUpperCase(),
            pedido_id: pedidoId,
            _token: '{{ csrf_token() }}'
        },
        dataType: 'json',
        success: function(response) {
            console.log('Respuesta del servidor:', response);
            Swal.close();
            if (response.success) {
                // Redireccionar directamente al menú de edición
                window.location.href = "{{ route('kiosko.menu_pedido') }}";
            } else {
                Swal.fire('Error', response.message || 'Error desconocido', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error("Error AJAX al editar pedido:", error);
            console.log("Respuesta XHR:", xhr);
            Swal.close();
            Swal.fire({
                title: 'Error',
                html: `
                    <p>Hubo un problema al editar el pedido.</p>
                    <p><strong>Detalle:</strong> ${error}</p>
                    <p>Intenta de nuevo o contacta al administrador.</p>
                `,
                icon: 'error'
            });
        }
    });
}

        $(document).ready(function() {
            // Set CSRF token for all AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Función para recargar las mesas del piso activo
            function refreshMesas() {
                var activePisoId = $('.piso-btn.active').data('piso-id');
                if (activePisoId) {
                    $.ajax({
                        url: "{{ route('kiosko.get_mesas_por_piso', '') }}/" + activePisoId,
                        type: "GET",
                        dataType: 'json',
                        success: function(response) {
                            $('#mesas_container').html(response.vista);
                        },
                        error: function(xhr, status, error) {
                            console.error("Error al refrescar mesas:", error);
                        }
                    });
                }
            }

            // Iniciar la actualización automática cada 5 segundos (5000 ms)
            setInterval(refreshMesas, 5000);

            // Manejar clic en botones de piso
            $('.piso-btn').on('click', function() {
                $('.piso-btn').removeClass('active');
                $(this).addClass('active');
                var pisoId = $(this).data('piso-id');
                refreshMesas(); // Llama a refreshMesas para el piso seleccionado
            });

            function cargarReservas() {
            $.get("{{ route('kiosko.reservas_hoy') }}", function(data) {
                if (data.success) {
                    let cant = data.reservas.length;
                    $('#badge_reservas').text(cant);
                    
                    if (cant > 0) {
                        $('#btn_abrir_reservas').removeClass('btn-default').addClass('btn-warning');
                    } else {
                        $('#btn_abrir_reservas').removeClass('btn-warning').addClass('btn-default');
                    }

                    let tbody = '';
                    if (cant === 0) {
                        tbody = '<tr><td colspan="5" class="text-center text-muted" style="padding: 20px;">No hay reservas pendientes para hoy.</td></tr>';
                    } else {
                        data.reservas.forEach(function(res) {
                            // Validar visualmente si la mesa ya se la agarró otro cliente
                            let alertaMesa = res.mes_est !== 'Libre' ? `<br><small class="text-danger"><i class="fa fa-warning"></i> Mesa ocupada actualmente</small>` : '';
                            
                            // Formatear Hora
                            let horaStr = res.hora_inicio.substring(0, 5);

                            tbody += `
                                <tr>
                                    <td class="text-center" style="font-weight: bold; font-size: 16px; color: #2c3e50; vertical-align: middle;">
                                        <i class="fa fa-clock-o text-muted"></i> ${horaStr}
                                    </td>
                                    <td style="font-weight: bold; vertical-align: middle;">${res.clinom}</td>
                                    <td class="text-center" style="vertical-align: middle;">
                                        <span class="label bg-gray">${res.pis_nom} - ${res.mes_nom}</span>
                                        ${alertaMesa}
                                    </td>
                                    <td class="text-center" style="vertical-align: middle;"><i class="fa fa-users text-muted"></i> ${res.cantidad_personas}</td>
                                    <td class="text-center" style="vertical-align: middle;">
                                        <button class="btn btn-success btn-sm btn-usar-reserva" data-id="${res.res_id}" style="font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                            <i class="fa fa-check-circle"></i> USAR RESERVA
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    }
                    $('#tabla_reservas_hoy tbody').html(tbody);
                }
            });
        }

        // Cargar reservas al iniciar y actualizar el badge cada cierto tiempo (ej: cada 30 seg)
        cargarReservas();
        setInterval(cargarReservas, 30000); 

        // 2. ABRIR EL MODAL DE RESERVAS
        $('#btn_abrir_reservas').click(function() {
            cargarReservas(); // Forzar actualización fresca al abrir
            $('#modal-reservas-hoy').modal('show');
        });

        // 3. PROCESAR LA RESERVA (BOTÓN "USAR RESERVA")
        $(document).on('click', '.btn-usar-reserva', function() {
            let resId = $(this).data('id');
            ejecutarProcesoReserva(resId, null);
        });

        function ejecutarProcesoReserva(resId, nuevaMesaId) {
            Swal.fire({
                title: 'Procesando Reserva...',
                text: 'Aperturando mesa y cargando los productos...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.post("{{ route('kiosko.procesar_reserva') }}", {
                res_id: resId,
                nueva_mes_id: nuevaMesaId,
                _token: '{{ csrf_token() }}'
            }, function(response) {
                
                // ESCENARIO 1: LA MESA RESERVADA ESTÁ OCUPADA, PEDIR CAMBIO
                if (response.requiere_cambio) {
                    Swal.close();
                    
                    let opcionesMesas = {};
                    // Verificamos que el arreglo no venga vacío para evitar errores
                    if (response.mesas_libres && response.mesas_libres.length > 0) {
                        response.mesas_libres.forEach(function(m) {
                            opcionesMesas[m.mes_id] = m.mes_nom;
                        });
                    }

                    if (Object.keys(opcionesMesas).length === 0) {
                        Swal.fire('Zona Llena', `La mesa ${response.mesa_original} está ocupada y NO HAY mesas libres en esta zona. Libere una mesa primero.`, 'error');
                        return;
                    }

                    Swal.fire({
                        title: 'Mesa Ocupada',
                        text: `La mesa original (${response.mesa_original}) ha sido tomada. Seleccione una mesa libre para trasladar la reserva:`,
                        icon: 'warning',
                        input: 'select',
                        inputOptions: opcionesMesas,
                        inputPlaceholder: 'Seleccione nueva mesa',
                        showCancelButton: true,
                        confirmButtonText: 'Trasladar y Usar',
                        cancelButtonText: 'Cancelar',
                        inputValidator: (value) => {
                            if (!value) { return 'Debe seleccionar una mesa libre' }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Recursividad: volvemos a llamar a la función pero ahora mandando la mesa de reemplazo
                            ejecutarProcesoReserva(resId, result.value); 
                        }
                    });
                    return;
                }

                // ESCENARIO 2: ÉXITO TOTAL (La mesa estaba libre o ya se reemplazó)
                if (response.success) {
                    $('#modal-reservas-hoy').modal('hide');
                    Swal.fire('¡Mesa Aperturada!', `La reserva se aplicó correctamente en la mesa ${response.mesa_nom}.`, 'success').then(() => {
                        // Opcional: Redirigir directamente al menú del pedido para seguir editando o ir al cobro
                        selectServiceAndRedirect('salon', null, response.mesa_nom, response.pedido_id);
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }).fail(function() {
                Swal.fire('Error', 'Error de conexión al procesar la reserva.', 'error');
            });
        }

            // Función para guardar los datos de servicio en sesión y redirigir
            function selectServiceAndRedirect(orderType, mesaId = null, mesaNombre = null, pedidoId = null) {
                $.ajax({
                    url: "{{ route('kiosko.set_service_data') }}",
                    type: "POST",
                    data: {
                        order_type: orderType,
                        mesa_id: mesaId,
                        mesa_nombre: mesaNombre,
                        pedido_id: pedidoId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            window.location.href = "{{ route('kiosko.menu_pedido') }}";
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error al seleccionar servicio:", error);
                        Swal.fire('Error', 'Hubo un problema al iniciar el pedido. Intenta de nuevo.', 'error');
                    }
                });
            }

            // Manejar clic en botones de mesa (Delegación de eventos para contenido AJAX)
            $(document).on('click', '.btn-mesa-kiosko', function() {
        var mesaId = $(this).data('mesa-id');
        var mesaNombre = $(this).data('mesa-nombre');
        var mesaEstado = $(this).data('mesa-estado');
        var pedidoId = $(this).data('pedido-id');
        
        // 1. CAPTURAMOS AL DUEÑO REAL DE LA BASE DE DATOS
        var usuarioQueAbrioLaMesa = $(this).data('usuario-id'); 
        // OBLIGAR USUARIO MOZO A ELEGIR SOLO SU MESA
        // 2. INTERRUPTOR BLOQUEAR USUARIO O DESBLOQUEAR USUARIO
        const EXCLUSIVIDAD_ACTIVADA = false; 
        
        // 3. ID DEL MOZO ACTUAL USANDO EL HELPER DE LARAVEL
        const ID_USUARIO_ACTUAL = "{{ Auth::id() }}"; 

        // Puedes descomentar esta línea de abajo si quieres ver los IDs en la consola (F12)
        // console.log("Mesa:", mesaNombre, "| Dueño BD:", usuarioQueAbrioLaMesa, "| Logueado:", ID_USUARIO_ACTUAL);

        // 4. LÓGICA DE BLOQUEO SUPER ESTRICTA
        if (EXCLUSIVIDAD_ACTIVADA && mesaEstado !== 'Libre' && pedidoId) {
            
            // Excepción: Admin y Caja se saltan la regla
            @if(!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('caja'))
                
                // Si el dueño existe y NO es igual al mozo actual, lo bloqueamos
                if (usuarioQueAbrioLaMesa && String(usuarioQueAbrioLaMesa) !== String(ID_USUARIO_ACTUAL)) {
                    Swal.fire({
                        title: 'Mesa Bloqueada', 
                        text: 'Esta mesa está siendo atendida por otro mozo. No puedes editarla.', 
                        icon: 'error',
                        confirmButtonColor: '#d33'
                    });
                    return false; // Aborta la función
                }
                
            @endif
        }
    // ❌ NO uses este total, viene de la tabla pedidos
    // var pedidoTotal = $(this).data('ped-tot');

    if (mesaEstado === 'Libre') {
        selectServiceAndRedirect('salon', mesaId, mesaNombre, null);
    } else {
        if (pedidoId) {
            // ✅ MOSTRAR MODAL CON TOTAL TEMPORAL MIENTRAS CARGA
            let topButtonsHtml = `
            <div class="button-row-top">
                <button type="button" class="swal2-styled custom-swal-button btn-agregar" id="swal2-add-btn">EDITAR</button>
                <button type="button" class="swal2-styled custom-swal-button btn-precuenta" id="swal2-precuenta-btn">PRECUENTA</button>
                <button type="button" class="swal2-styled custom-swal-button btn-separadas" id="swal2-separadas-btn">SEPARADAS</button>
                
                @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('caja'))
                    <button type="button" class="swal2-styled custom-swal-button btn-cobrar" id="swal2-cobrar-mesa-btn">COBRAR</button>
                @endif
            </div>
            `;

            let bottomButtonsHtml = `
                <div class="button-row-bottom">
                    <button type="button" class="swal2-styled custom-swal-button btn-cambiar-mesa" id="swal2-cambiar-mesa-btn">Cambiar Mesa</button>
                    <button type="button" class="swal2-styled custom-swal-button btn-unir-mesa" id="swal2-unir-mesa-btn">Unir Mesa</button>
                    <button type="button" class="swal2-styled custom-swal-button btn-cerrar" id="swal2-close-btn-footer">Cerrar</button>
                </div>
            `;

            // ✅ TÍTULO INICIAL SIN TOTAL
            Swal.fire({
                title: `Mesa ${mesaNombre} (Cargando total...)`,
                html: '<div id="pedido-details-content" style="text-align: left; max-height: 300px; overflow-y: auto; margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px;">Cargando detalles del consumo...</div>',
                icon: 'info',
                showConfirmButton: false,
                showDenyButton: false,
                showCancelButton: false,
                buttonsStyling: false,
                showLoaderOnConfirm: false,
                showLoaderOnDeny: false,
                footer: topButtonsHtml + bottomButtonsHtml,
                didOpen: (modalElement) => {
                    // ✅ CARGAR DETALLES Y ACTUALIZAR TÍTULO CON TOTAL CORRECTO

// -----------------------------------------------------------------


    $.ajax({
    url: "{{ route('kiosko.get_pedido_details', '') }}/" + pedidoId,
    type: "GET",
    dataType: 'json',
    success: function(response) {
        if (response.success) {
            // ✅ NUEVO: Variable para sumar solo lo que falta pagar
            let totalPendienteReal = 0; 
            
            let detailsHtml = '<h4 style="border-bottom: 2px solid #eee; padding-bottom: 5px; margin-bottom: 10px;">Detalles del Consumo:</h4><ul style="list-style: none; padding: 0;">';
            
            if (response.detalles.length > 0) {
                response.detalles.forEach(item => {
                    let cantidadPendiente = parseFloat(item.cantidad_pendiente);
                    let subtotalProducto = cantidadPendiente * parseFloat(item.ped_det_pre);
                    
                    // Verificamos si el ítem está pagado
                    let estaPagado = (item.pagado == 1 || item.pagado === '1'); 
                    
                    if (estaPagado) {
                        // --- DISEÑO PARA ÍTEM PAGADO ---
                        detailsHtml += `<li style="background-color: #d4edda; padding: 8px; border-radius: 5px; margin-bottom: 5px; border-left: 4px solid #28a745;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="text-decoration: line-through; color: #155724; opacity: 0.8;"><strong>${cantidadPendiente}x</strong> ${item.descripcion}</span>
                                <div>
                                    <span style="text-decoration: line-through; color: #155724; margin-right: 10px; opacity: 0.8;">S/. ${subtotalProducto.toFixed(2)}</span>
                                    <span style="background-color: #28a745; color: white; padding: 3px 8px; border-radius: 12px; font-size: 0.8em; font-weight: bold;">PAGADO</span>
                                </div>
                            </div>`;
                    } else {
                        // --- DISEÑO PARA ÍTEM PENDIENTE (SE SUMA AL TOTAL) ---
                        totalPendienteReal += subtotalProducto;
                        detailsHtml += `<li style="padding: 8px; border-bottom: 1px solid #eee; margin-bottom: 5px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span><strong>${cantidadPendiente}x</strong> ${item.descripcion}</span>
                                <strong>S/. ${subtotalProducto.toFixed(2)}</strong>
                            </div>`;
                    }
                    
                    // Mostrar info si hay items parcialmente facturados
                    if (parseFloat(item.item_facturado) > 0) {
                        let totalOriginal = parseFloat(item.ped_det_can);
                        detailsHtml += `<div style="color: #ffc107; font-size: 0.85em; margin-top: 2px;">[${item.item_facturado} de ${totalOriginal} ya facturado(s)]</div>`;
                    }
                    
                    // Mostrar observaciones si existen
                    if (item.item_obs) {
                        let obsColor = estaPagado ? '#155724' : '#6c757d';
                        detailsHtml += `<div style="font-style: italic; font-size: 0.9em; color: ${obsColor}; margin-top: 2px;">Obs: ${item.item_obs}</div>`;
                    }
                    
                    detailsHtml += `</li>`;
                });
            } else {
                detailsHtml += '<li style="color: #28a745; font-weight: bold;">✓ Todos los items ya han sido facturados</li>';
            }
            
            detailsHtml += `</ul>`;
            
            // ✅ ACTUALIZAR TÍTULO CON EL TOTAL PENDIENTE CALCULADO (Solo lo que no está pagado)
            $('.swal2-title').text(`Mesa ${mesaNombre} (Total a Pagar: S/. ${totalPendienteReal.toFixed(2)})`);
            
            // Mostrar resumen general
            if (response.total_original && parseFloat(response.total_original) != parseFloat(response.total)) {
                let yaFacturado = (parseFloat(response.total_original) - parseFloat(response.total)).toFixed(2);
                detailsHtml += `<div style="background-color: #e7f3ff; padding: 10px; border-radius: 5px; margin-top: 15px; border-left: 3px solid #007bff;">
                    <small style="color: #004085;">
                        <strong>📊 Resumen de la Mesa:</strong><br>
                        <strong>Total Original:</strong> S/. ${parseFloat(response.total_original).toFixed(2)}<br>
                        <strong>Ya Facturado en Caja:</strong> S/. ${yaFacturado}<br>
                        <strong>Total Pendiente Actual:</strong> S/. ${totalPendienteReal.toFixed(2)}
                    </small>
                </div>`;
            }
            
            $('#pedido-details-content').html(detailsHtml);
        } else {
            $('.swal2-title').text(`Mesa ${mesaNombre} (Error)`);
            $('#pedido-details-content').html(`<p class="text-danger">${response.message}</p>`);
        }
    },
    error: function(xhr, status, error) {
        console.error("Error al cargar detalles del pedido:", error);
        $('.swal2-title').text(`Mesa ${mesaNombre} (Error)`);
        $('#pedido-details-content').html('<p class="text-danger">Error al cargar los detalles. Intenta de nuevo.</p>');
    }
});

                    // Resto de event listeners permanecen igual...
                    $(modalElement).find('#swal2-add-btn').on('click', function() {
                        Swal.close();
                        selectServiceAndRedirect('salon', mesaId, mesaNombre, pedidoId);
                    });

                    $(modalElement).find('#swal2-precuenta-btn').on('click', function() {
                        Swal.close();
                        generatePrecuenta(pedidoId);
                    });

                    $(modalElement).find('#swal2-separadas-btn').on('click', function() {
                        // Si el usuario es ADMIN o CAJA, entra directo a la pantalla de cobrar
                        @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('caja'))
                            Swal.close();
                            window.location.href = "/cseparadas/" + pedidoId;
                        @else
                            // Si es MOZO, solo envía la notificación a la pantalla de caja
                            solicitarCuentasSeparadas(pedidoId, mesaNombre);
                        @endif
                    });

                                @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('caja'))
                                //PRE CUENTA PRECUENTA OBLIGATORIO true precuenta obligatorio false no es obligatorio
                                const OBLIGAR_PRECUENTA = false;
                                    $(modalElement).find('#swal2-cobrar-mesa-btn').on('click', function() {
                                        // Si la restricción está activada
                                        if (OBLIGAR_PRECUENTA) {
                                            // Verificamos si la mesa NO tiene el estado 'Cuenta' (que es el color ámbar)
                                            // Usamos la variable mesaEstado que ya capturaste arriba
                                            if (mesaEstado !== 'Cuenta') {
                                                Swal.fire({
                                                    title: '¡Precuenta Requerida!',
                                                    text: 'No se puede cobrar sin antes haber enviado la PRECUENTA a la mesa.',
                                                    icon: 'warning',
                                                    confirmButtonText: 'Entendido'
                                                });
                                                return false; // Bloquea el paso al cobro
                                            }
                                        }

                                        // Si la restricción es false o si ya imprimió precuenta, procede normal:
                                        Swal.close();
                                        window.location.href = "{{ url('/cobrarmesa') }}/" + pedidoId;
                                    });
                                    /*$(modalElement).find('#swal2-cobrar-mesa-btn').on('click', function() {
                                        Swal.close();
                                        window.location.href = "{{ url('/cobrarmesa') }}/" + pedidoId;
                                    });*/

                                    // NUEVO: Manejador para el botón ELIMINAR PEDIDO
                                    $(modalElement).find('#swal2-eliminar-pedido-btn').on('click', function() {
                                        Swal.close(); // Cerrar el primer SweetAlert
                                        
                                        Swal.fire({
                                            title: 'Eliminar Pedido Completo',
                                            html: `
                                                <p>¿Estás seguro de que deseas eliminar el pedido completo de la mesa <strong>${mesaNombre}</strong> (ID: ${pedidoId})?</p>
                                                <p>Esta acción revertirá el stock de todos los ítems y liberará la mesa. Se imprimirá una comanda de anulación.</p>
                                                <div class="form-group" style="text-align: left;">
                                                    <label for="admin_user">Usuario (Admin/Caja):</label>
                                                    <input type="text" id="admin_user" class="swal2-input" placeholder="Usuario" value="{{ Auth::user()->email }}">
                                                </div>
                                                <div class="form-group" style="text-align: left;">
                                                    <label for="admin_password">Contraseña:</label>
                                                    <input type="password" id="admin_password" class="swal2-input" placeholder="Contraseña">
                                                </div>
                                                <div class="form-group" style="text-align: left;">
                                                    <label for="eliminar_razon">Motivo de eliminación:</label>
                                                    <textarea id="eliminar_razon" class="swal2-textarea" placeholder="Ej: Error al tomar el pedido, cliente se fue sin pagar, etc."></textarea>
                                                </div>
                                            `,
                                            icon: 'warning',
                                            showCancelButton: true,  // <--- CORREGIDO: Mostrar botón de cancelar
                                            showConfirmButton: true, // <--- CORREGIDO: Mostrar botón de confirmación
                                            confirmButtonColor: '#d33',
                                            cancelButtonColor: '#6c757d',
                                            confirmButtonText: 'Sí, Eliminar Pedido',
                                            cancelButtonText: 'Cancelar',
                                            preConfirm: () => {
                                                const user = Swal.getPopup().querySelector('#admin_user').value;
                                                const password = Swal.getPopup().querySelector('#admin_password').value;
                                                const reason = Swal.getPopup().querySelector('#eliminar_razon').value;

                                                if (!user || !password || !reason) {
                                                    Swal.showValidationMessage(`Por favor, ingresa usuario, contraseña y el motivo.`);
                                                    return false;
                                                }
                                                return { user: user, password: password, reason: reason };
                                            }
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                Swal.fire({
                                                    title: 'Eliminando Pedido...',
                                                    text: 'Por favor, espera.',
                                                    allowOutsideClick: false,
                                                    didOpen: () => {
                                                        Swal.showLoading();
                                                    }
                                                });

                                                $.ajax({
                                                    url: "{{ route('kiosko.eliminar_pedido_completo') }}", // Nueva ruta
                                                    type: "POST",
                                                    data: {
                                                        pedido_id: pedidoId,
                                                        mesa_id: mesaId, // También pasamos la mesa_id por si acaso
                                                        auth_user: result.value.user,
                                                        auth_password: result.value.password,
                                                        reason: result.value.reason,
                                                        _token: $('meta[name="csrf-token"]').attr('content') // Asegúrate de enviar el token CSRF
                                                    },
                                                    dataType: 'json',
                                                    success: function(response) {
                                                        Swal.close();
                                                        if (response.success) {
                                                            Swal.fire('Eliminado!', response.message, 'success')
                                                                .then(() => {
                                                                    refreshMesas(); // Recargar las mesas para reflejar el cambio de estado
                                                                });
                                                        } else {
                                                            Swal.fire('Error', response.message, 'error');
                                                        }
                                                    },
                                                    error: function(xhr, status, error) {
                                                        Swal.close();
                                                        console.error("Error al eliminar pedido completo:", error);
                                                        Swal.fire('Error', 'Hubo un problema al eliminar el pedido. Intenta de nuevo. Detalle: ' + (xhr.responseJSON ? xhr.responseJSON.message : error), 'error');
                                                    }
                                                });
                                            }
                                        });
                                    });
                                @endif

                                $(modalElement).find('#swal2-cambiar-mesa-btn').on('click', function() {
                                    Swal.close();
                                    $('#mes_id_act_modal').val(mesaId);
                                    $('#ped_id_act_modal').val(pedidoId);
                                    $('#mes_act_modal').text(mesaNombre);
                                    $.ajax({
                                        url: "{{ route('kiosko.buscar_mesas_desocupadas') }}",
                                        type: "GET",
                                        dataType: 'json',
                                        success: function(response) {
                                            $('#mesas_desocupadas_list').html('');
                                            if (response.vista) {
                                                $('#mesas_desocupadas_list').html(response.vista);
                                            } else {
                                                $('#mesas_desocupadas_list').html('<option value="">No hay mesas disponibles</option>');
                                            }
                                            $('#modal-cambiar-mesa').modal('show');
                                        },
                                        error: function(xhr, status, error) {
                                            console.error("Error al cambiar mesa:", error);
                                            Swal.fire('Error', 'No se pudieron cargar las mesas disponibles.', 'error');
                                        }
                                    });
                                });

                                $(modalElement).find('#swal2-unir-mesa-btn').on('click', function() {
                                    Swal.close();
                                    $('#mes_id_act_unir_modal').val(mesaId);
                                    $('#ped_id_act_unir_modal').val(pedidoId);
                                    $('#mes_act_unir_modal').text(mesaNombre);
                                    $.ajax({
                                        url: "{{ route('kiosko.buscar_mesas_libres_para_unir') }}",
                                        type: "GET",
                                        dataType: 'json',
                                        success: function(response) {
                                            $('#mesas_para_unir_list').html('');
                                            if (response.vista) {
                                                $('#mesas_para_unir_list').html(response.vista);
                                            } else {
                                                $('#mesas_para_unir_list').html('<option value="">No hay mesas libres para unir</option>');
                                            }
                                            $('#modal-unir-mesas').modal('show');
                                        },
                                        error: function(xhr, status, error) {
                                            console.error("Error al unir mesas:", error);
                                            Swal.fire('Error', 'Hubo un error al unir las mesas.', 'error');
                                        }
                                    });
                                });

                                $(modalElement).find('#swal2-close-btn-footer').on('click', function() {
                                    Swal.close();
                                });
                            }
                        });
                    } else {
                        Swal.fire('Atención', `La mesa ${mesaNombre} está ocupada pero no tiene un pedido asociado. Por favor, selecciona otra o llama a un asistente.`, 'warning');
                    }
                }
            });

            // Manejar clic en botón "Para Llevar"
            $('#btn_para_llevar').on('click', function() {
                selectServiceAndRedirect('llevar', null, 'PARA LLEVAR', null);
            });

            // NUEVO: Manejar clic en botón "DELIVERY"
            $('#btn_delivery').on('click', function() {
                selectServiceAndRedirect('delivery', null, 'DELIVERY', null); // 'delivery' es el nuevo tipo de orden
            });

            // Función para generar la pre-cuenta
            function generatePrecuenta(pedidoId) {
                Swal.fire({
                    title: 'Solicitando Pre-Cuenta...',
                    text: 'Esto puede tardar un momento.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: "{{ route('kiosko.generar_precuenta') }}",
                    type: "POST",
                    data: {
                        pedido_id: pedidoId,
                        _token: '{{ csrf_token() }}'
                    },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            Swal.fire('Éxito', response.message, 'success');
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        console.error("Error al generar precuenta:", error);
                        Swal.fire('Error', 'Hubo un error al solicitar la pre-cuenta. Intenta de nuevo.', 'error');
                    }
                });
            }

            // NUEVO: Función para cargar y mostrar pedidos de Llevar/Delivery
            function loadTakeAwayDeliveryOrders() {
                $.ajax({
                    url: "{{ route('kiosko.get_take_away_and_delivery_orders_for_display') }}",
                    type: "GET",
                    dataType: 'json',
                    success: function(response) {
                        //console.log('Pedidos cargados:', response);
                        if (response.success && response.pedidos.length > 0) {
                            let html = `<table class="table table-striped table-hover takeaway-delivery-orders-table">
                                        <thead>
                                            <tr>
                                                <th data-label="ID">ID Pedido</th>
                                                <th data-label="Tipo">Tipo</th>
                                                <th data-label="Cliente/Mesa">Cliente/Mesa</th>
                                                <th data-label="Hora">Hora</th>
                                                <th data-label="Total">Total S/.</th>
                                                <th data-label="Acciones">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>`;
                            response.pedidos.forEach(pedido => {
                                const orderTime = new Date(pedido.fecha_hora).toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', hour12: true });
                                const orderTypeLabelClass = pedido.ped_tip.toLowerCase() === 'llevar' ? 'llevar' : 'delivery';
                                const clientInfo = pedido.ped_cli_nom && pedido.ped_cli_nom !== 'VENTA AL PORTADOR' ? pedido.ped_cli_nom : 'N/A';

                                html += `<tr>
                                            <td data-label="ID">${pedido.ped_id}</td>
                                            <td data-label="Tipo"><span class="order-type-label ${orderTypeLabelClass}">${pedido.ped_tip.toUpperCase()}</span></td>
                                            <td data-label="Cliente/Mesa">${clientInfo}</td>
                                            <td data-label="Hora">${orderTime}</td>
                                            <td data-label="Total">${parseFloat(pedido.ped_tot).toFixed(2)}</td>
                                            <td data-label="Acciones">
                                                <button type="button" class="btn btn-editar-directo" onclick="editarPedidoLlevarDelivery(${pedido.ped_id}, '${pedido.ped_tip}')">
                                                    <i class="fa fa-edit"></i> Editar
                                                </button>
                                                @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('caja'))
                                                <button type="button" class="btn btn-cobrar-directo" onclick="window.location.href = '{{ url('/cobrarmesa') }}/${pedido.ped_id}'">
                                                    <i class="fa fa-money-bill"></i> Cobrar
                                                </button>
                                                @endif
                                            </td>
                                        </tr>`;
                            });
                            html += `</tbody></table>`;
                            $('#takeaway_delivery_orders_container').html(html);
                        } else {
                            $('#takeaway_delivery_orders_container').html('<p class="text-center text-muted">No hay pedidos PARA LLEVAR o DELIVERY activos.</p>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error al cargar pedidos Llevar/Delivery:", error);
                        $('#takeaway_delivery_orders_container').html('<p class="text-center text-danger">Error al cargar los pedidos. Recarga la página.</p>');
                    }
                });
            }

            // NUEVO: Función para chequear y notificar nuevos pedidos (para admin/caja)
            function checkForNewOrdersNotification() {
                @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('caja'))
                $.ajax({
                    url: "{{ route('kiosko.check_new_take_away_orders') }}",
                    type: "GET",
                    dataType: 'json',
                    success: function(response) {
                        if (response.new_orders) {
                            Swal.fire({
                                title: '¡NUEVO PEDIDO!',
                                text: `Tienes ${response.count} nuevo(s) pedido(s) PARA LLEVAR/DELIVERY pendientes.`,
                                icon: 'info',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 5000,
                                timerProgressBar: true,
                                didOpen: (toast) => {
                                    toast.addEventListener('mouseenter', Swal.stopTimer)
                                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                                }
                            });
                            // Recargar la lista de pedidos de Llevar/Delivery para mostrar los nuevos
                            loadTakeAwayDeliveryOrders();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error al chequear nuevas órdenes:", error);
                    }
                });
                @endif
            }

            // Manejar envío de formulario de cambiar mesa (para el modal de Bootstrap)
            $('#frm_cambiar_mesa').on('submit', function(e) {
                e.preventDefault();
                const formData = $(this).serialize();
                $.ajax({
                    url: "{{ route('kiosko.cambiar_mesa') }}",
                    type: "POST",
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#modal-cambiar-mesa').modal('hide');
                            Swal.fire('Éxito', response.message, 'success');
                            refreshMesas();
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error al cambiar mesa:", error);
                        Swal.fire('Error', 'Hubo un error al cambiar la mesa.', 'error');
                    }
                });
            });

            // Manejar envío de formulario de unir mesas (para el modal de Bootstrap)
            $('#frm_unir_mesas').on('submit', function(e) {
                e.preventDefault();
                const formData = $(this).serialize();
                $.ajax({
                    url: "{{ route('kiosko.unir_mesas') }}",
                    type: "POST",
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#modal-unir-mesas').modal('hide');
                            Swal.fire('Éxito', response.message, 'success');
                            refreshMesas();
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error al unir mesas:", error);
                        Swal.fire('Error', 'Hubo un error al unir las mesas.', 'error');
                    }
                });
            });

            // Iniciar la carga de pedidos Llevar/Delivery y el chequeo de notificaciones
            loadTakeAwayDeliveryOrders();
            setInterval(loadTakeAwayDeliveryOrders, 50000);
            setInterval(checkForNewOrdersNotification, 50000);
        });

        function solicitarCuentasSeparadas(pedidoId, mesaNombre) {
            $.ajax({
                url: "{{ route('kiosko.solicitar_cs') }}", // Debes crear esta ruta
                type: "POST",
                data: {
                    pedido_id: pedidoId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Solicitud Enviada',
                        text: 'Se ha notificado a CAJA que la Mesa ' + mesaNombre + ' solicita cuentas separadas.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        }
    </script>
</body>

</html>