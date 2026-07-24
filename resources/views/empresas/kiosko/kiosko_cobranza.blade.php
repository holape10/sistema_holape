@extends('layouts.empresas') {{-- Asegúrate de que tu layout principal sea el correcto --}}

@section('contenido')

<style>
    /* Estilos base del sistema, puedes adaptarlos o usar Tailwind/Bootstrap */
    body {
        font-family: 'Source Sans Pro', sans-serif;
        background-color: #f8f9fa;
        color: #333;
    }
    .box {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .box-header {
        background-color: #E8E8E8;
        padding: 15px;
        border-bottom: 1px solid #ddd;
        font-weight: bold;
        font-size: 1.1em;
        text-align: center;
        display: flex;
        justify-content: space-between; /* Para alinear el título y el pull-right */
        align-items: center;
    }
    .box-body {
        padding: 20px;
    }
    .form-group label {
        font-weight: 600;
        margin-bottom: 5px;
    }
    .form-control {
        border-radius: 5px;
        border: 1px solid #ced4da;
        padding: 8px 12px;
        width: 100%;
    }
    .input-group-btn .btn {
        border-radius: 5px;
    }
    .table th, .table td {
        vertical-align: middle;
    }
    .table thead th {
        background-color: orange;
        color: white;
        text-align: center;
    }
    .btn-success { background-color: #28a745; border-color: #28a745; color: white; }
    .btn-danger { background-color: #dc3545; border-color: #dc3545; color: white; }
    .btn-primary { background-color: #007bff; border-color: #007bff; color: white; }
    .btn-block { display: block; width: 100%; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .alert-danger {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 5px;
    }
    .loader {
        display: none;
        text-align: center;
        margin-top: 20px;
    }
    .loader img {
        width: 50px;
        height: 50px;
    }
    .split-account-item {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
    }
    .split-account-item input[type="checkbox"] {
        margin-right: 10px;
        transform: scale(1.5); /* Aumenta el tamaño del checkbox */
    }
    .split-account-item label {
        margin-bottom: 0;
        font-weight: normal;
        flex-grow: 1;
    }
    .split-account-controls {
        display: flex;
        gap: 5px;
        margin-left: auto;
    }
    .account-summary {
        background-color: #f2f2f2;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 10px;
    }
    .account-summary strong {
        font-size: 1.1em;
    }
</style>

<div class="container-fluid">
    <a href="/kiosko/seleccion" class="btn btn-primary mb-4">
        <strong><i class="fa fa-arrow-left"></i> Volver a Mesas</strong>
    </a>
    <h2 class="text-center mb-6">Cobranza de Pedido #{{ $cabecera->ped_id }}</h2>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <form id="frm_cobranzas" method="POST" action="{{ route('restaurante.registrar_cobro_kiosko') }}">
        @csrf
        <input type="hidden" name="ped_id" value="{{ $cabecera->ped_id }}">
        <input type="hidden" name="mesa_id" value="{{ $cabecera->mes_id }}">
        <input type="hidden" name="pis_id" value="{{ $cabecera->pis_id }}">
        <input type="hidden" name="tipo" value="{{ $cabecera->ped_tip }}">
        <input type="hidden" name="mozo" value="{{ $cabecera->mozo }}">
        <input type="hidden" name="icbper_val" id="icbper_val" value="{{ $empresa->icbper ?? 0 }}">

        <div class="row">
            <div class="col-lg-6 col-md-12">
                <div class="box">
                    <div class="box-header">
                        <strong>DATOS DEL COMPROBANTE</strong>
                        <div class="pull-right">
                            <label class="form-check-label">IMPRIMIR</label>
                            <input class="form-check-input" name="imprimir" type="checkbox" value="1" checked>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-lg-4 col-md-6">
                                <div class="form-group">
                                    <label>Comprobante</label>
                                    <select class="form-control" name="tdocod" id="tdocod">
                                        @foreach($comprobantes as $comp)
                                            <option value="{{ $comp->tdocod }}" {{ ($negocio->tdocod_pred ?? '03') == $comp->tdocod ? 'selected' : '' }}>
                                                {{ $comp->tdodes }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <div class="form-group">
                                    <label>Estado Pago</label>
                                    <select class="form-control" name="estadopago" id="estadopago">
                                        @foreach($estadopagos as $est_pag)
                                            <option value="{{ $est_pag->cre_dia_id }}" data-medio="{{ $est_pag->cre_dia_tip }}" data-dias="{{ $est_pag->cre_dia_fac }}">
                                                {{ $est_pag->cre_dia_nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <div class="form-group">
                                    <label>F. Emisión</label>
                                    <input type="date" id="fecEmi" name="fecEmi" value="{{ Carbon\Carbon::now()->format('Y-m-d') }}" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6" id="divfecVen" style="display:none;">
                                <div class="form-group">
                                    <label>F. Vencim.</label>
                                    <input type="date" name="fecVen" id="fecVen" value="{{ Carbon\Carbon::now()->format('Y-m-d') }}" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <div class="form-group">
                                    <label>X CONSUMO</label>
                                    <select class="form-control" name="consumo">
                                        <option value="0">NO</option>
                                        <option value="1">SI</option>
                                    </select>
                                </div>
                            </div>
                             <div class="col-lg-1">
                                <div class="form-group form-group-sm">
                                    <center><img style="display:none;" width="80px" height="80px" src="{{ asset('img/load.gif') }}" name="imgloadcliente" id="imgloadcliente"></center>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>Tipo Doc. Identidad</label>
                                    <select name="tdicod" id="tdicod" class="form-control">
                                        @foreach($documentos as $doc)
                                            <option value="{{ $doc->tdicod }}" {{ ($cabecera->tdicod ?? '1') == $doc->tdicod ? 'selected' : '' }}>
                                                {{ $doc->tdides }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="form-group">
                                    <label>DNI / RUC</label>
                                    <div class="input-group">
                                        <input name="clinum" id="clinum" value="{{ $cabecera->ped_num_doc ?? '00000000' }}" class="form-control" onkeypress="if(event.keyCode == 13) buscarclienteruc();">
                                        <input type="hidden" name="clicod" id="clicod">
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-primary" onclick="buscarclienteruc();"><i class="fa fa-search"></i></button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Teléfono</label>
                                    <input name="clitel" id="clitel" value="{{ $cabecera->ped_tel }}" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Nombre o Razón Social</label>
                            <div class="input-group">
                                <input name="clinom" id="clinom" value="{{ $cabecera->ped_cli_nom ?? 'VENTA AL PORTADOR' }}" class="form-control" onkeypress="if(event.keyCode == 13) buscarclientenombre();">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-primary" onclick="buscarclientenombre();"><i class="fa fa-search"></i></button>
                                </span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Dirección</label>
                            <div class="input-group">
                                <input name="clidir" id="clidir" value="{{ $cabecera->ped_dir ?? '--' }}" class="form-control">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-primary" id="clidiradic" onclick="seleccionardireccion();"><i class="fa fa-search"></i></button>
                                </span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Correo</label>
                            <input name="clicor" id="clicor" value="{{ $cabecera->clicorcli ?? '' }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Observaciones:</label>
                            <textarea class="form-control" rows="3" name="observaciones">{{ $cabecera->ped_obs }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-12">
                <div class="box">
                    <div class="box-header">
                        <strong>DETALLE DEL PEDIDO
                            @if($cabecera->ped_tip != 'Salon')
                                {{ strtoupper($cabecera->ped_tip) }} - {{ strtoupper($cabecera->ped_cli_nom) }}
                            @else
                                @if(!empty($dat_pis)) {{ $dat_pis->pis_nom }} / @endif @if(!empty($dat_mes)) {{ $dat_mes->mes_nom }} @endif
                            @endif
                        </strong>
                        <div class="pull-right">
                            <button type="button" class="btn btn-sm btn-info" id="btn_split_accounts">Cuentas Separadas</button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="alert alert-danger" id="alertitem" style="display:none;">
                            <strong>¡Error!</strong> No se pueden registrar cobros sin productos.
                        </div>

                        <div id="original_order_details">
                            {{-- Detalles del pedido original (se llenará con JS) --}}
                            <table class="table table-striped table-hover table-bordered table-condensed" id="tbl_detalle">
                                <thead style="background:orange;">
                                    <tr style="text-align:center;font-weight:bold;">
                                        <td hidden>ID</td>
                                        <td>PRODUCTO</td>
                                        <td>CANT.</td>
                                        <td>PRECIO</td>
                                        <td>OBSERVACIÓN</td>
                                        <td>ELIMINAR</td>
                                    </tr>
                                </thead>
                                <tbody id="items_pedidos">
                                    {{-- Se llenará con JavaScript --}}
                                </tbody>
                            </table>
                        </div>
                        
                        <div id="split_accounts_section" style="display:none;">
                            <h4>Cuentas Separadas</h4>
                            <div id="accounts_container">
                                {{-- Aquí se generarán las secciones de cuentas --}}
                            </div>
                            <button type="button" class="btn btn-success btn-sm mt-3" id="add_new_account">Agregar Cuenta</button>
                            <hr>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="box">
                    <div class="box-header">
                        <strong>PAGO</strong>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>TOTAL S/.</label>
                                    <input type="number" step="any" readonly class="form-control input-lg" style="height:60px; font-size:22pt; font-weight:bold;" name="total_venta" id="total_venta" value="{{ number_format($cabecera->ped_tot, 2, '.', '') }}">
                                </div>
                                <div class="form-group" style="display:none;">
                                    <label>ICBPER S/.</label>
                                    <input type="number" step="any" readonly class="form-control input-lg" style="height:60px; font-size:22pt; font-weight:bold;" name="icbper_tot" id="icbper_tot" value="{{ number_format($cabecera->icbper_tot, 2, '.', '') }}">
                                </div>
                            </div>

                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>MEDIOS PAGO</label>
                                    <select class="form-control" name="med_pag" id="med_pag">
                                        @foreach($mediospagos as $medpag)
                                            <option value="{{ $medpag->id_med_pag }}" data-nom="{{ $medpag->nom_med_pag }}" data-predeterminado="{{ $medpag->predeterminado }}">
                                                {{ $medpag->nom_med_pag }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <div class="input-group">
                                        <input name="mon_med_pag" id="mon_med_pag" value="0.00" class="form-control">
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-primary btn-flat" onclick="agregar_medio_pago();">
                                                <i class="fa fa-plus-square"></i> Agregar Pago
                                            </button>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <table class="table table-responsive table-striped table-hover" id="tbl_med_pag">

                                        <tbody id="tbody_med_pag">
                                            
                                        </tbody>
                                    </table>
                                </div>

                            </div>


                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>PAGA CON:</label>
                                    <input type="number" step="any" class="form-control input-lg" style="height:60px; font-size:22pt; font-weight:bold;" id="pagar" name="pagar" value="0.00" onkeyup="calcular_vuelto();">
                                </div>
                                <div class="form-group">
                                    <label>VUELTO</label>
                                    <input type="text" class="form-control input-lg" style="height:60px; font-size:22pt; font-weight:bold;" id="vuelto" name="vuelto" value="0.00" readonly>
                                </div>
                            </div>

                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>OPERACIONES</label>
                                    <button type="submit" id="btnRegistrar" class="btn btn-success btn-lg btn-block botones">REGISTRAR - COBRAR</button>
                                </div>
                                <div class="form-group">
                                    <a href="/kiosko/seleccion" class="btn btn-danger btn-lg btn-block botones">SALIR</a>
                                </div>
                                <div class="loader" id="imgload">
                                    <img src="{{ asset('img/load.gif') }}" alt="Cargando...">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Modals para búsqueda de clientes/direcciones --}}
@include('empresas.puntosventas.modalclientes')
@include('empresas.puntosventas.modaldirecciones')

@endsection

@push('scripts')
<script>
    // Variables globales o inicializaciones
    // currentPedidoDetails contendrá los objetos de detalle, cada uno con `pronom` (del producto) y `descripcion` (del detalle del pedido)
    // Inicializamos con los detalles pasados desde el controlador, que ya incluye item_facturado
    let currentPedidoDetails = @json($detalle);
    
    let currentAccountCount = 0; // Se inicializa a 0 al cargar la página

    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Asegurarse de que el campo de fecha de vencimiento tenga la fecha de emisión por defecto
        // si el método de pago es "CONTADO" al cargar la página.
        const initialPaymentMethod = $('#estadopago').find(':selected').data('medio');
        if (initialPaymentMethod === 'CONTADO') {
            $('#fecVen').val($('#fecEmi').val());
            // Si es contado y tiene un medio de pago predeterminado, se llena el campo "Paga Con"
            const predeterminadoId = $('#med_pag option[data-predeterminado="1"]').val();
            if (predeterminadoId) {
                $('#med_pag').val(predeterminadoId);
                $('#mon_med_pag').val($('#total_venta').val());
                $('#pagar').val($('#total_venta').val());
                window.calcular_vuelto(); // Usar window. para asegurar acceso global
            }
        }

        // *** LLAMADA DIRECTA A renderOrderDetails si los datos ya están disponibles desde PHP ***
        // renderOrderDetails ya maneja si el array está vacío o no.
        renderOrderDetails(currentPedidoDetails);
        setupPaymentMethodLogic(); // Configura la lógica de pago una vez que el total esté calculado
        // *****************************************************************************************

        // Lógica de "cuentas separadas"
        $('#btn_split_accounts').on('click', function() {
            if ($('#split_accounts_section').is(':visible')) {
                // Si ya está visible, volvemos a la vista original
                $('#split_accounts_section').hide();
                $('#original_order_details').show();
                
                // Habilitar y resetear pagos globales
                $('#med_pag, #mon_med_pag, #pagar, #vuelto').prop('disabled', false);
                $('#tbody_med_pag').empty(); 
                $('#mon_med_pag').val('0.00'); // Resetea el monto
                renderOrderDetails(currentPedidoDetails); // Recarga los detalles originales
                setupPaymentMethodLogic(); // Re-aplica la lógica de método de pago (para poner el total en pagar si es contado)
                window.calcular_total(); // Usar window. para asegurar acceso global
            } else {
                // Si no está visible, activamos las cuentas separadas
                $('#original_order_details').hide();
                $('#split_accounts_section').show();
                // Deshabilitar pagos globales al activar cuentas separadas
                $('#med_pag, #mon_med_pag, #pagar, #vuelto').prop('disabled', true);
                $('#tbody_med_pag').empty(); // Limpiar pagos globales
                
                currentAccountCount = 0; // Reiniciar contador de cuentas
                $('#accounts_container').empty(); // Limpiar contenedores de cuentas
                addNewAccount(true); // Crea la primera cuenta con los ítems disponibles

                // Ajustar el total de la venta a 0 porque cada cuenta tendrá su propio total
                $('#total_venta').val('0.00');
                $('#icbper_tot').val('0.00');
                $('#vuelto').val('0.00'); // Asegura que el vuelto global sea 0
            }
        });

        $('#add_new_account').on('click', function() {
            addNewAccount();
        });

        // Función para agregar una nueva sección de cuenta
        function addNewAccount(isInitialLoad = false) {
            currentAccountCount++;
            const accountId = `account_${currentAccountCount}`;
            const accountHtml = `
                <div class="account-card box mb-4" data-account-id="${accountId}">
                    <div class="box-header">
                        <strong>Cuenta #${currentAccountCount}</strong>
                        <div class="pull-right">
                            <button type="button" class="btn btn-danger btn-sm remove-account" data-account-id="${accountId}">X</button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <label>Nombre de la cuenta (Opcional):</label>
                            <input type="text" class="form-control account-name" name="account_names[${accountId}]" data-account-id="${accountId}" placeholder="Ej. Cliente 1">
                        </div>
                        <h5>Items de esta cuenta:</h5>
                        <div class="selected-items-list" id="selected_items_${accountId}">
                            <p class="text-center text-muted">No hay ítems seleccionados para esta cuenta.</p>
                        </div>
                        <div class="form-group mt-3">
                            <button type="button" class="btn btn-info btn-sm select-items-for-account" data-account-id="${accountId}">Seleccionar Ítems</button>
                        </div>
                        <div class="account-summary mt-3">
                            <strong>Total Cuenta: <span class="float-right" id="total_account_${accountId}">S/. 0.00</span></strong>
                            <input type="hidden" class="account-total-input" name="account_totals[${accountId}]" value="0.00">
                            <input type="hidden" class="account-icbper-input" name="account_icbper[${accountId}]" value="0.00">
                            <input type="hidden" class="account-items-input" name="account_items[${accountId}]" value="">
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Paga con:</label>
                                    <input type="number" step="any" class="form-control account-pay-amount" data-account-id="${accountId}" value="0.00" onkeyup="window.calculateAccountChange('${accountId}');">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Vuelto:</label>
                                    <input type="text" readonly class="form-control account-change-amount" data-account-id="${accountId}" value="0.00">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Medios de Pago:</label>
                            <select class="form-control account-payment-method-select" data-account-id="${accountId}">
                                @foreach($mediospagos as $medpag)
                                    <option value="{{ $medpag->id_med_pag }}" data-nom="{{ $medpag->nom_med_pag }}" data-predeterminado="{{ $medpag->predeterminado }}">
                                        {{ $medpag->nom_med_pag }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="input-group mt-2">
                                <input type="number" step="any" class="form-control account-payment-amount-input" data-account-id="${accountId}" value="0.00">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-primary add-account-payment" data-account-id="${accountId}">
                                        <i class="fa fa-plus-square"></i> Add
                                    </button>
                                </span>
                            </div>
                            <table class="table table-sm table-striped mt-2">
                                <tbody id="account_payments_${accountId}"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;
            $('#accounts_container').append(accountHtml);

            if (isInitialLoad) {
                // Solo para la primera cuenta, rellenar con todos los items disponibles
                let initialItems = [];
                currentPedidoDetails.forEach(item => {
                    const availableQty = parseFloat(item.ped_det_can) - parseFloat(item.item_facturado); // Asegura que sean números
                    if (availableQty > 0) {
                        initialItems.push({
                            id: item.IdProducto,
                            nombre: item.pronom, // Usar pronom del producto
                            cantidad: availableQty,
                            precio: parseFloat(item.ped_det_pre), // Asegura que sea número
                            icbper: parseInt(item.icbper_ind),
                            observaciones: item.item_obs,
                            ped_det_id: item.ped_det_id // Necesario para actualizar item_facturado
                        });
                    }
                });
                updateAccountItems(accountId, initialItems);
            }
        }

        // Eliminar cuenta
        $(document).on('click', '.remove-account', function() {
            const accountId = $(this).data('account-id');
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Se eliminará esta cuenta. Los ítems volverán a estar disponibles.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Recuperar items antes de eliminar la cuenta
                    const itemsInThisAccount = JSON.parse($(`[name="account_items[${accountId}]"]`).val() || '[]');
                    itemsInThisAccount.forEach(item => {
                        // Aquí, en lugar de "devolver", ajustamos la cantidad "facturable" en currentPedidoDetails
                        const originalItem = currentPedidoDetails.find(pItem => pItem.IdProducto === item.id);
                        if (originalItem) {
                            // Sumamos lo que estaba en esta cuenta de nuevo al "disponible" (aunque no esté facturado)
                            originalItem.item_facturado -= item.cantidad; 
                            if (originalItem.item_facturado < 0) originalItem.item_facturado = 0; // Evitar negativos
                        }
                    });
                    $(`.account-card[data-account-id="${accountId}"]`).remove();
                    recalculateAllAccountTotals();
                    Swal.fire('Eliminado!', 'La cuenta ha sido eliminada.', 'success');
                }
            });
        });

        // Seleccionar ítems para una cuenta
        $(document).on('click', '.select-items-for-account', function() {
            const accountId = $(this).data('account-id');
            const currentAccountItems = JSON.parse($(`[name="account_items[${accountId}]"]`).val() || '[]');
            
            // Generar checkboxes para los ítems disponibles globalmente (no facturados aún)
            let optionsHtml = '<p>Marca los ítems y cantidades para esta cuenta:</p><ul style="list-style: none; padding: 0;">';
            currentPedidoDetails.forEach(item => {
                // Calcula la cantidad ya asignada de este producto en OTRAS cuentas
                let alreadyAssignedInOtherAccounts = 0;
                $('.account-card').not(`[data-account-id="${accountId}"]`).find('.account-items-input').each(function() {
                    const otherAccountItems = JSON.parse($(this).val() || '[]');
                    const foundItem = otherAccountItems.find(aItem => aItem.id === item.IdProducto);
                    if (foundItem) {
                        alreadyAssignedInOtherAccounts += foundItem.cantidad;
                    }
                });

                // La cantidad máxima disponible para esta cuenta es la cantidad original del pedido
                // menos lo ya facturado Y menos lo que ya está en otras cuentas separadas.
                const totalOriginalInPedido = parseFloat(item.ped_det_can);
                const totalFacturadoActual = parseFloat(item.item_facturado);
                
                // La cantidad de este item que ya está en la cuenta actual
                const currentlyInThisAccount = currentAccountItems.find(aItem => aItem.id === item.IdProducto)?.cantidad || 0;

                // La cantidad realmente disponible para SELECCIONAR en esta ventana para CUALQUIER cuenta
                const remainingAvailableGlobally = totalOriginalInPedido - totalFacturadoActual;

                // Lo que puedo añadir a esta cuenta es lo que queda globalmente disponible,
                // más lo que esta cuenta ya tiene (para permitir ajustar hacia abajo sin perderlo).
                const maxQtyForThisInput = remainingAvailableGlobally + currentlyInThisAccount - alreadyAssignedInOtherAccounts;
                
                if (maxQtyForThisInput > 0) {
                    const selectedInThisAccount = currentAccountItems.find(aItem => aItem.id === item.IdProducto);
                    const currentQtyForInput = selectedInThisAccount ? selectedInThisAccount.cantidad : 0;

                    optionsHtml += `
                        <li class="split-account-item">
                            <input type="checkbox" id="item_${item.IdProducto}_${accountId}" data-item-id="${item.IdProducto}" data-item-name="${item.pronom}" data-item-price="${parseFloat(item.ped_det_pre)}" data-item-icbper="${parseInt(item.icbper_ind)}" data-item-obs="${item.item_obs || ''}" data-max-qty="${maxQtyForThisInput}" ${selectedInThisAccount ? 'checked' : ''}>
                            <label for="item_${item.IdProducto}_${accountId}">${item.pronom} (Disp: ${maxQtyForThisInput})</label>
                            <div class="split-account-controls">
                                <button type="button" class="btn btn-sm btn-outline-secondary minus-qty" data-item-id="${item.IdProducto}" data-account-id="${accountId}">-</button>
                                <input type="number" class="form-control-sm item-qty-input" id="qty_${item.IdProducto}_${accountId}" value="${currentQtyForInput}" min="0" max="${maxQtyForThisInput}" style="width: 70px; text-align: center;">
                                <button type="button" class="btn btn-sm btn-outline-secondary plus-qty" data-item-id="${item.IdProducto}" data-account-id="${accountId}">+</button>
                            </div>
                        </li>
                    `;
                }
            });
            optionsHtml += '</ul>';

            Swal.fire({
                title: 'Seleccionar Ítems',
                html: optionsHtml,
                showCancelButton: true,
                confirmButtonText: 'Aplicar',
                cancelButtonText: 'Cancelar',
                didOpen: () => {
                    // Inicializar cantidades de inputs
                    currentAccountItems.forEach(aItem => {
                        $(`#qty_${aItem.id}_${accountId}`).val(aItem.cantidad);
                    });

                    // Sincronizar checkbox y cantidad inicial
                    $(document).on('change', `input[type="checkbox"][data-account-id="${accountId}"]`, function() {
                        const itemId = $(this).data('item-id');
                        const qtyInput = $(`#qty_${itemId}_${accountId}`);
                        if ($(this).is(':checked')) {
                            if (parseFloat(qtyInput.val()) === 0 || isNaN(parseFloat(qtyInput.val()))) { // Si estaba en 0 o vacío y se marca
                                qtyInput.val(1);
                            }
                        } else {
                            qtyInput.val(0);
                        }
                        qtyInput.trigger('input'); // Trigger input para re-validar
                    });

                    // Controladores de cantidad
                    $(document).on('click', `.minus-qty[data-account-id="${accountId}"]`, function() {
                        const itemId = $(this).data('item-id');
                        const qtyInput = $(`#qty_${itemId}_${accountId}`);
                        let currentVal = parseFloat(qtyInput.val() || 0);
                        if (currentVal > 0) {
                            qtyInput.val(currentVal - 1).trigger('input');
                        }
                        if (qtyInput.val() == 0) {
                            $(`#item_${itemId}_${accountId}`).prop('checked', false);
                        }
                    });
                    $(document).on('click', `.plus-qty[data-account-id="${accountId}"]`, function() {
                        const itemId = $(this).data('item-id');
                        const qtyInput = $(`#qty_${itemId}_${accountId}`);
                        const maxQty = parseFloat(qtyInput.attr('max'));
                        let currentVal = parseFloat(qtyInput.val() || 0);
                        if (currentVal < maxQty) {
                            qtyInput.val(currentVal + 1).trigger('input');
                        }
                        if (qtyInput.val() > 0) {
                            $(`#item_${itemId}_${accountId}`).prop('checked', true);
                        }
                    });
                    $(document).on('input', `.item-qty-input[data-account-id="${accountId}"]`, function() {
                        let val = parseFloat($(this).val());
                        const max = parseFloat($(this).attr('max'));
                        if (isNaN(val)) val = 0;

                        if (val > max) {
                            $(this).val(max);
                            Swal.showValidationMessage(`No puedes exceder la cantidad disponible (${max.toFixed(2)}).`);
                        } else if (val < 0) {
                            $(this).val(0);
                        } else {
                             Swal.resetValidationMessage(); // Limpiar mensaje si la cantidad es válida
                        }
                        const itemId = $(this).data('item-id');
                        if ($(this).val() > 0) {
                            $(`#item_${itemId}_${accountId}`).prop('checked', true);
                        } else {
                            $(`#item_${itemId}_${accountId}`).prop('checked', false);
                        }
                    });
                },
                preConfirm: () => {
                    const selectedItems = [];
                    let validationErrors = [];

                    $('input[type="checkbox"][data-account-id="' + accountId + '"]').each(function() {
                        const itemId = $(this).data('item-id');
                        const qtyInput = $(`#qty_${itemId}_${accountId}`);
                        let qty = parseFloat(qtyInput.val());
                        const maxQty = parseFloat($(this).data('max-qty'));
                        const itemName = $(this).data('item-name');
                        
                        if ($(this).is(':checked') || (qty > 0 && !isNaN(qty))) { // Procesar si está marcado o si hay cantidad (incluso sin marcar)
                            if (isNaN(qty) || qty < 0) {
                                qty = 0; // Normaliza a 0 si es inválido
                            }
                            if (qty > maxQty) {
                                validationErrors.push(`La cantidad para ${itemName} (${qty.toFixed(2)}) excede la disponible (${maxQty.toFixed(2)}).`);
                            } else if (qty > 0) {
                                selectedItems.push({
                                    id: itemId,
                                    nombre: itemName,
                                    precio: parseFloat($(this).data('item-price')),
                                    cantidad: qty,
                                    icbper: parseInt($(this).data('item-icbper')),
                                    observaciones: $(this).data('item-obs'),
                                });
                            }
                        }
                    });

                    if (validationErrors.length > 0) {
                        Swal.showValidationMessage(validationErrors.join('<br>'));
                        return false;
                    }

                    updateAccountItems(accountId, selectedItems);
                    return true; // Confirmar y cerrar el modal
                }
            });
        });

        // Actualizar los ítems mostrados en la cuenta y sus totales
        function updateAccountItems(accountId, items) {
            const $selectedItemsList = $(`#selected_items_${accountId}`);
            const $accountTotalInput = $(`[name="account_totals[${accountId}]"]`);
            const $accountIcbperInput = $(`[name="account_icbper[${accountId}]"]`);
            const $accountItemsInput = $(`[name="account_items[${accountId}]"]`);
            const $displayTotal = $(`#total_account_${accountId}`);
            
            let total = 0;
            let totalIcbper = 0;
            const icbperVal = parseFloat($('#icbper_val').val() || 0);

            if (items.length === 0) {
                $selectedItemsList.html('<p class="text-center text-muted">No hay ítems seleccionados para esta cuenta.</p>');
            } else {
                let itemsHtml = '<ul>';
                items.forEach(item => {
                    let itemSubtotal = item.cantidad * item.precio;
                    itemsHtml += `<li>${item.cantidad.toFixed(2)}x ${item.nombre} (S/. ${itemSubtotal.toFixed(2)})`;
                    if (item.observaciones) {
                        itemsHtml += ` <small>(Obs: ${item.observaciones})</small>`;
                    }
                    itemsHtml += `</li>`;
                    total += itemSubtotal;
                    if (item.icbper == 1) { 
                        totalIcbper += item.cantidad * icbperVal;
                    }
                });
                itemsHtml += '</ul>';
                $selectedItemsList.html(itemsHtml);
            }
            
            total += totalIcbper;
            $displayTotal.text(`S/. ${total.toFixed(2)}`);
            $accountTotalInput.val(total.toFixed(2));
            $accountIcbperInput.val(totalIcbper.toFixed(2));
            $accountItemsInput.val(JSON.stringify(items)); 

            window.calculateAccountChange(accountId); // Usar window. para asegurar acceso global
            recalculateAllAccountTotals(); // Actualiza el total global
        }

        // Definir las funciones globales en el objeto window
        // Esto es CRUCIAL para evitar "not defined" errors cuando se llaman desde el HTML inline o de forma asíncrona.

        window.calculateAccountChange = function(accountId) {
            const total = parseFloat($(`[name="account_totals[${accountId}]"]`).val() || 0);
            const paga = parseFloat($(`.account-pay-amount[data-account-id="${accountId}"]`).val() || 0);
            const vuelto = paga - total;
            $(`.account-change-amount[data-account-id="${accountId}"]`).val(vuelto.toFixed(2));
        };

        window.agregar_medio_pago = function() {
            const med_pag_id = $("#med_pag").val();
            const mon_med_pag = parseFloat($("#mon_med_pag").val() || 0);
            const nom_med_pag = $("#med_pag").find(':selected').data('nom');

            if (mon_med_pag <= 0 || isNaN(mon_med_pag)) {
                Swal.fire('Error', 'Ingresa un monto válido para el medio de pago.', 'error');
                return;
            }

            const $tbody_med_pag = $("#tbody_med_pag");
            
            let existingRow = $tbody_med_pag.find(`tr[data-med-pag-id="${med_pag_id}"]`);
            if (existingRow.length > 0) {
                let currentAmount = parseFloat(existingRow.data('amount') || 0);
                let newAmount = currentAmount + mon_med_pag;
                existingRow.find('span.monto-display').text(newAmount.toFixed(2));
                existingRow.find('input[name="mon_med_pag[]"]').val(newAmount.toFixed(2));
                existingRow.data('amount', newAmount);
            } else {
                const newRow = `
                    <tr data-med-pag-id="${med_pag_id}" data-amount="${mon_med_pag.toFixed(2)}">
                        <td>
                            <button type="button" class="btn btn-success btn-sm btn-block">
                                ${nom_med_pag} S/ <span class="monto-display">${mon_med_pag.toFixed(2)}</span>
                            </button>
                        </td>
                        <td>
                            <button type="button" onclick="window.ElimMedPag(this);" class="btn btn-danger btn-sm remove">
                                <span class="fa fa-minus"></span>
                            </button>
                        </td>
                        <input type="hidden" name="id_med_pag[]" value="${med_pag_id}">
                        <input type="hidden" name="mon_med_pag[]" value="${mon_med_pag.toFixed(2)}">
                    </tr>
                `;
                $tbody_med_pag.append(newRow);
            }
            
            $("#mon_med_pag").val('0.00'); 
            window.calcular_vuelto(); 
        };


        window.ElimMedPag = function(btn) {
            $(btn).closest('tr').remove();
            window.calcular_vuelto();
        };

        window.calcular_vuelto = function() {
            const total_venta = parseFloat($("#total_venta").val() || 0);
            let total_pagado = 0;

            $("#tbody_med_pag tr").each(function() {
                total_pagado += parseFloat($(this).find("input[name='mon_med_pag[]']").val() || 0);
            });

            $("#pagar").val(total_pagado.toFixed(2));

            const vuelto = total_pagado - total_venta;
            if (vuelto < 0) {
                $("#vuelto").val('0.00');
            } else {
                $("#vuelto").val(vuelto.toFixed(2));
            }
        };

        window.eliminar_item_registrado = function(btn, item_id, ped_id) {
            Swal.fire({
                title: '¿Eliminar producto?',
                text: "¿Estás seguro de eliminar este producto del pedido?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/eliminaritem/${item_id}/${ped_id}`,
                        type: "GET",
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire('Eliminado!', response.mensaje, 'success');
                                if (response.action === 'reload_page') {
                                    window.location.href = "/kiosko/seleccion"; // Redirige a la selección de servicio
                                } else {
                                    $(btn).closest('tr').remove();
                                    // Actualizar currentPedidoDetails para reflejar la eliminación
                                    currentPedidoDetails = currentPedidoDetails.filter(item => item.IdProducto !== item_id);
                                    if (currentPedidoDetails.length === 0) {
                                        $('#items_pedidos').html('<tr><td colspan="6" class="text-center">No hay productos pendientes de cobro en este pedido.</td></tr>');
                                        $('#btnRegistrar').prop('disabled', true);
                                    }
                                    window.calcular_total(); // Recalcular total
                                }
                            } else {
                                Swal.fire('Error', response.mensaje, 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error al eliminar item:", error);
                            Swal.fire('Error', 'Hubo un error al eliminar el producto. Intenta de nuevo.', 'error');
                        }
                    });
                }
            });
        };

        window.buscarclienteruc = function() {
            const clinum = $("#clinum").val();
            $("#imgloadcliente").show();
            $.ajax({
                type: "GET",
                dataType: 'json',
                url: `/autocomplete/${clinum}`,
                success: function(respuesta) {
                    $("#imgloadcliente").hide();
                    if (respuesta.error) {
                        Swal.fire('Error', respuesta.error, 'error');
                    } else if (respuesta.length > 0) {
                        $('#clinom').val(respuesta[0].nom);
                        $('#clidir').val(respuesta[0].dir);
                        $("#tdicod").val(respuesta[0].tdicod).trigger('change');
                        $('#clicod').val(respuesta[0].clicod || '');
                        $('#clicor').val(respuesta[0].cor || '');
                        $('#clitel').val(respuesta[0].tel || '');
                    } else {
                        Swal.fire('Información', 'No se encontraron datos para el RUC/DNI.', 'info');
                    }
                },
                error: function(xhr, status, error) {
                    $("#imgloadcliente").hide();
                    console.error("Error en autocomplete:", error);
                    Swal.fire('Error', 'Error al buscar cliente/RUC.', 'error');
                }
            });
        };

        window.buscarclientenombre = function() {
            const clinom = $("#clinom").val();
            if (clinom.length < 3) {
                Swal.fire('Advertencia', 'Ingresa al menos 3 caracteres para buscar por nombre.', 'warning');
                return;
            }
            $("#modal-lista-clientes").modal("show");
            $.ajax({
                type: "GET",
                dataType: 'json',
                url: `/buscarclientenombre/${clinom}`,
                success: function(respuesta) {
                    if (respuesta.vista) {
                        $('#clientes').html(respuesta.vista);
                    } else {
                        $('#clientes').html('<tr><td colspan="4" class="text-center">No se encontraron clientes.</td></tr>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error al buscar cliente por nombre:", error);
                    Swal.fire('Error', 'Error al buscar clientes por nombre.', 'error');
                }
            });
        };

        window.agregarcliente = function(clicod, clinum, clinom, clidir, tdicod, clicor, clitel) {
            $('#clinom').val(clinom);
            $('#clinum').val(clinum);
            $('#clidir').val(clidir);
            $('#clicor').val(clicor); 
            $('#clicod').val(clicod);
            $('#clitel').val(clitel || '');
            $("#tdicod").val(tdicod).trigger('change');
            if($('#tdicod').val() === '6' ){
                $("#tdocod").val('01').trigger('change');
            } else if($('#tdicod').val() === '1' ){
                $("#tdocod").val('03').trigger('change');
            }
            $('#modal-lista-clientes').modal('hide');
        };

        window.seleccionardireccion = function() {
            const clicod = $("#clicod").val();
            if (!clicod) {
                Swal.fire('Advertencia', 'Primero busca o selecciona un cliente.', 'warning');
                return;
            }
            $("#modal-direcciones").modal("show");
            $.ajax({
                type: "GET",
                dataType: 'json',
                url: `/seleccionardireccion/${clicod}`,
                success: function(respuesta) {
                    if (respuesta.vista) {
                        $('#direcciones').html(respuesta.vista);
                    } else {
                        $('#direcciones').html('<tr><td colspan="2" class="text-center">No hay direcciones registradas.</td></tr>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error al seleccionar dirección:", error);
                    Swal.fire('Error', 'Error al cargar direcciones.', 'error');
                }
            });
        };
        window.agregardireccion = function(direccion) {
            $("#clidir").val(direccion);
            $("#modal-direcciones").modal('hide');
        };

        // Enviar formulario principal
        $('#frm_cobranzas').on('submit', function(e) {
            e.preventDefault();

            if (!$('#split_accounts_section').is(':visible') && $('#items_pedidos >tbody >tr').length === 0){
                $('#alertitem').show();
                return;
            }
            if ($('#split_accounts_section').is(':visible') && $('#accounts_container').children('.account-card').length === 0){
                 Swal.fire('Error', 'Debes agregar al menos una cuenta separada.', 'error');
                 return;
            }

            if ($('#split_accounts_section').is(':visible')) {
                let totalItemsAssigned = 0;
                $('.account-items-input').each(function() {
                    const items = JSON.parse($(this).val() || '[]');
                    items.forEach(item => totalItemsAssigned += item.cantidad);
                });

                let totalOriginalItems = 0;
                currentPedidoDetails.forEach(item => {
                    const availableQtyInPedido = parseFloat(item.ped_det_can || 0) - parseFloat(item.item_facturado || 0);
                    if(availableQtyInPedido > 0) {
                        totalOriginalItems += availableQtyInPedido;
                    }
                });
                
                const tolerance = 0.001; 

                if (Math.abs(totalItemsAssigned - totalOriginalItems) > tolerance) {
                    Swal.fire('Error', `Debes asignar todos los ítems pendientes del pedido original a las cuentas separadas para poder cobrar. Cantidad pendiente: ${(totalOriginalItems - totalItemsAssigned).toFixed(2)}.`, 'error');
                    return;
                }

                let totalPaidInSplitAccounts = 0;
                $('.account-pay-amount').each(function() {
                    totalPaidInSplitAccounts += parseFloat($(this).val() || 0);
                });

                let totalItemsAssignedValue = 0; 
                $('.account-total-input').each(function() {
                    totalItemsAssignedValue += parseFloat($(this).val() || 0);
                });

                if (Math.abs(totalPaidInSplitAccounts - totalItemsAssignedValue) > tolerance) {
                    Swal.fire('Error', 'El total pagado en las cuentas separadas no coincide con el total de los ítems asignados. Asegúrate de que los pagos cubran los montos de las cuentas.', 'error');
                    return;
                }
            }


            const form = $(this);
            const formData = form.serializeArray();
            
            $("#btnRegistrar").prop('disabled', true);
            $("#imgload").show();

            $.ajax({
                type: "POST",
                dataType: 'json',
                url: form.attr('action'),
                data: formData,
                success: function(response) {
                    $("#imgload").hide();
                    if (response.estado === 'success') {
                        Swal.fire('Éxito', response.mensaje, 'success').then(() => {
                             window.location.href = "{{ route('kiosko.seleccion_servicio') }}";
                        });
                    } else {
                        Swal.fire('Error', response.mensaje, 'error');
                        $("#btnRegistrar").prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    $("#imgload").hide();
                    $("#btnRegistrar").prop('disabled', false);
                    console.error("Error en el cobro:", error);
                    Swal.fire('Error', 'Hubo un error al procesar el cobro. Intenta de nuevo. Detalles: ' + (xhr.responseJSON ? xhr.responseJSON.message : xhr.responseText), 'error');
                }
            });
        });
    });

    // Cargar detalles del pedido en la tabla al iniciar la página (como respaldo)
    $(window).on('load', function() {
        if (!Array.isArray(currentPedidoDetails) || currentPedidoDetails.length === 0) {
            const pedidoId = {{ $cabecera->ped_id }};
            $.ajax({
                url: "{{ route('kiosko.get_pedido_details', '') }}/" + pedidoId,
                type: "GET",
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.detalles.length > 0) {
                        currentPedidoDetails = response.detalles; 
                        renderOrderDetails(currentPedidoDetails);
                        window.calcular_total(); 
                    } else {
                        $('#items_pedidos').html('<tr><td colspan="6" class="text-center text-danger">No hay productos pendientes de cobro en este pedido.</td></tr>');
                        $('#btnRegistrar').prop('disabled', true);
                        window.calcular_total(); 
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error al cargar detalles del pedido en onload (AJAX):", error);
                    $('#items_pedidos').html('<tr><td colspan="6" class="text-center text-danger">Error al cargar los detalles del pedido.</td></tr>');
                    $('#btnRegistrar').prop('disabled', true);
                    window.calcular_total();
                }
            });
        }
    });

</script>
@endpush