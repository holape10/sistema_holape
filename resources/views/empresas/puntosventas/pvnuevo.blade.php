@extends('layouts.empresas')

@section('contenido')

{{-- Incluir modales --}}

@include('empresas.puntosventas.modalpresentaciones')
@include('empresas.puntosventas.modaldirecciones')
@include('empresas.puntosventas.modalingresarcantidadprecio')

<style>
    /* Reset y variables */
    :root {
        --primary: #3c8dbc;
        --primary-dark: #2a6a8f;
        --primary-light: #e8f0fe;
        --success: #28a745;
        --danger: #dc3545;
        --warning: #ffc107;
        --gray-100: #f8f9fa;
        --gray-200: #e9ecef;
        --gray-300: #dee2e6;
        --gray-400: #ced4da;
        --gray-500: #adb5bd;
        --gray-600: #6c757d;
        --gray-700: #495057;
        --gray-800: #343a40;
        --gray-900: #212529;
        --shadow: 0 4px 6px rgba(0,0,0,0.07);
        --shadow-lg: 0 10px 25px rgba(0,0,0,0.15);
        --radius: 12px;
        --transition: all 0.25s ease;
    }

    .pos-wrapper {
        display: flex;
        gap: 20px;
        min-height: calc(100vh - 120px);
        padding: 15px;
        background: #f0f2f5;
    }

    .pos-products {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .pos-card {
        background: #fff;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        transition: var(--transition);
    }

    .pos-card:hover {
        box-shadow: var(--shadow-lg);
    }

    .pos-card-header {
        background: var(--primary);
        color: #fff;
        padding: 12px 20px;
        font-weight: 600;
        font-size: 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .pos-card-header .badge {
        background: rgba(255,255,255,0.2);
        color: #fff;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
    }

    .pos-card-body {
        padding: 15px 20px;
    }

    /* Buscador de productos */
    .product-search-wrapper {
        position: relative;
    }

    .product-search-wrapper .select2-container {
        width: 100% !important;
    }

    .product-search-wrapper .select2-selection--single {
        height: 52px !important;
        border-radius: 8px !important;
        border: 2px solid var(--gray-300) !important;
        font-size: 16px !important;
        padding: 4px 12px !important;
        transition: var(--transition) !important;
    }

    .product-search-wrapper .select2-selection--single:focus-within {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 3px rgba(60, 141, 188, 0.2) !important;
    }

    .product-search-wrapper .select2-selection__rendered {
        font-size: 16px !important;
        line-height: 44px !important;
        font-weight: 500 !important;
        color: var(--gray-800) !important;
    }

    .product-search-wrapper .select2-selection__arrow {
        height: 48px !important;
    }

    .select2-dropdown {
        border-radius: 8px !important;
        border: 2px solid var(--primary) !important;
        box-shadow: var(--shadow-lg) !important;
        margin-top: 4px !important;
        z-index: 999999 !important;
    }

    .select2-results__option {
        padding: 10px 16px !important;
        font-size: 15px !important;
        border-bottom: 1px solid var(--gray-200) !important;
        transition: var(--transition) !important;
    }

    .select2-results__option--highlighted {
        background: var(--primary-light) !important;
        color: var(--primary-dark) !important;
    }

    .select2-search__field {
        font-size: 15px !important;
        height: 44px !important;
        border-radius: 6px !important;
        border: 2px solid var(--gray-300) !important;
        padding: 0 12px !important;
    }

    .select2-search__field:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 3px rgba(60, 141, 188, 0.15) !important;
    }

    .pos-table-wrapper {
        max-height: 480px;
        overflow-y: auto;
    }

    .pos-table-wrapper::-webkit-scrollbar {
        width: 6px;
    }

    .pos-table-wrapper::-webkit-scrollbar-track {
        background: var(--gray-100);
        border-radius: 3px;
    }

    .pos-table-wrapper::-webkit-scrollbar-thumb {
        background: var(--gray-400);
        border-radius: 3px;
    }

    .pos-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    .pos-table thead th {
        background: var(--gray-100);
        color: var(--gray-700);
        font-weight: 600;
        padding: 10px 12px;
        text-align: left;
        border-bottom: 2px solid var(--gray-300);
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .pos-table tbody tr {
        border-bottom: 1px solid var(--gray-200);
        transition: var(--transition);
    }

    .pos-table tbody tr:hover {
        background: var(--primary-light);
    }

    .pos-table tbody td {
        padding: 8px 10px;
        vertical-align: middle;
    }

    .pos-table .product-name {
        font-weight: 500;
        color: var(--gray-800);
        min-width: 120px;
    }

    .pos-table .product-code {
        color: var(--gray-500);
        font-size: 12px;
    }

    .pos-table input[type="number"] {
        width: 70px;
        padding: 4px 8px;
        border: 2px solid var(--gray-300);
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        text-align: center;
        transition: var(--transition);
    }

    .pos-table input[type="number"]:focus {
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(60, 141, 188, 0.15);
    }

    .pos-table .item-total {
        font-weight: 700;
        color: var(--primary-dark);
        font-size: 15px;
    }

    .btn-remove-item {
        background: transparent;
        border: none;
        color: var(--danger);
        font-size: 18px;
        padding: 4px 8px;
        border-radius: 6px;
        transition: var(--transition);
        cursor: pointer;
    }

    .btn-remove-item:hover {
        background: #fee;
        color: #c0392b;
        transform: scale(1.1);
    }

    /* Panel Derecho */
    .pos-cart {
        width: 420px;
        min-width: 360px;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    @media (max-width: 1024px) {
        .pos-wrapper {
            flex-direction: column;
        }
        .pos-cart {
            width: 100%;
            min-width: unset;
        }
        .pos-table-wrapper {
            max-height: 300px;
        }
    }

    /* Cliente con Select2 */
    .client-section .select2-container {
        width: 100% !important;
    }

    .client-section .select2-selection--single {
        height: 42px !important;
        border-radius: 8px !important;
        border: 2px solid var(--gray-300) !important;
        padding: 4px 12px !important;
    }

    .client-section .select2-selection__rendered {
        line-height: 32px !important;
        font-size: 14px !important;
    }

    .client-section .select2-selection__arrow {
        height: 38px !important;
    }

    .client-section .btn-new-client {
        background: var(--success);
        color: #fff;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
    }

    .client-section .btn-new-client:hover {
        opacity: 0.85;
        transform: translateY(-1px);
    }

    /* Métodos de pago */
    .payment-section {
        flex: 1;
    }

    .payment-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-top: 10px;
    }

    .payment-item {
        background: var(--gray-100);
        border-radius: 8px;
        padding: 10px 14px;
        border: 2px solid transparent;
        transition: var(--transition);
        cursor: pointer;
    }

    .payment-item:hover {
        border-color: var(--primary);
        background: var(--primary-light);
    }

    .payment-item.active {
        border-color: var(--primary);
        background: var(--primary-light);
    }

    .payment-item .payment-name {
        font-weight: 600;
        font-size: 13px;
        color: var(--gray-700);
    }

    .payment-item .payment-mount {
        font-size: 16px;
        font-weight: 700;
        color: var(--gray-900);
    }

    .payment-item .payment-input {
        width: 100%;
        border: none;
        background: transparent;
        font-size: 16px;
        font-weight: 700;
        color: var(--gray-900);
        padding: 4px 0;
        text-align: right;
    }

    .payment-item .payment-input:focus {
        outline: none;
    }

    .payment-total-row {
        background: var(--primary);
        color: #fff;
        border-radius: 8px;
        padding: 12px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 12px;
    }

    .payment-total-row .label {
        font-size: 14px;
        opacity: 0.9;
    }

    .payment-total-row .amount {
        font-size: 22px;
        font-weight: 700;
    }

    .totals-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 10px;
        margin-top: 10px;
    }

    .total-box {
        background: var(--gray-100);
        border-radius: 8px;
        padding: 10px 14px;
        text-align: center;
    }

    .total-box .label {
        font-size: 11px;
        text-transform: uppercase;
        color: var(--gray-600);
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .total-box .value {
        font-size: 20px;
        font-weight: 700;
        color: var(--gray-900);
    }

    .total-box .value.success {
        color: var(--success);
    }

    .total-box .value.danger {
        color: var(--danger);
    }

    .total-box .value.primary {
        color: var(--primary);
    }

    .action-buttons {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-top: 10px;
    }

    .btn-pos {
        padding: 12px 20px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: var(--transition);
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-pos:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .btn-pos:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none !important;
    }

    .btn-pos-primary {
        background: var(--primary);
        color: #fff;
    }

    .btn-pos-primary:hover {
        background: var(--primary-dark);
    }

    .btn-pos-success {
        background: var(--success);
        color: #fff;
    }

    .btn-pos-success:hover {
        background: #218838;
    }

    .btn-pos-danger {
        background: var(--danger);
        color: #fff;
    }

    .btn-pos-danger:hover {
        background: #c0392b;
    }

    .btn-pos-warning {
        background: var(--warning);
        color: var(--gray-900);
    }

    .btn-pos-warning:hover {
        background: #e0a800;
    }

    .btn-pos-outline {
        background: transparent;
        color: var(--gray-700);
        border: 2px solid var(--gray-300);
    }

    .btn-pos-outline:hover {
        background: var(--gray-100);
        border-color: var(--gray-500);
    }

    .modal-modern .modal-content {
        border-radius: var(--radius);
        border: none;
        box-shadow: var(--shadow-lg);
    }

    .modal-modern .modal-header {
        background: var(--primary);
        color: #fff;
        border-radius: var(--radius) var(--radius) 0 0;
        padding: 16px 24px;
    }

    .modal-modern .modal-header .close {
        color: #fff;
        opacity: 0.8;
    }

    .modal-modern .modal-header .close:hover {
        opacity: 1;
    }

    .modal-modern .modal-body {
        padding: 24px;
    }

    .modal-modern .modal-footer {
        padding: 16px 24px;
        border-top: 1px solid var(--gray-200);
    }

    /* Puntos fidelización */
    .puntos-box {
        background: linear-gradient(135deg, #fff3cd, #ffe69c);
        border-radius: 8px;
        padding: 12px 16px;
        margin-top: 10px;
        display: none;
        border-left: 4px solid var(--warning);
    }

    .puntos-box .puntos-title {
        font-weight: 700;
        color: #856404;
        font-size: 14px;
    }

    .puntos-box .puntos-value {
        font-size: 20px;
        font-weight: 700;
        color: #856404;
    }

    .puntos-box .puntos-premios {
        margin-top: 8px;
        font-size: 13px;
    }

    .puntos-box .btn-canjear {
        background: var(--success);
        color: #fff;
        border: none;
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        margin-left: 8px;
    }

    .puntos-box .btn-canjear:hover {
        opacity: 0.85;
    }

    .spinner-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 999999;
        display: none;
        justify-content: center;
        align-items: center;
    }

    .spinner-overlay.show {
        display: flex;
    }

    .spinner-box {
        background: #fff;
        padding: 30px 40px;
        border-radius: var(--radius);
        text-align: center;
        box-shadow: var(--shadow-lg);
    }

    .spinner-box .spinner {
        width: 50px;
        height: 50px;
        border: 4px solid var(--gray-200);
        border-top-color: var(--primary);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        margin: 0 auto 15px;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .spinner-box .message {
        font-size: 16px;
        font-weight: 600;
        color: var(--gray-700);
    }

    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 999999;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .toast {
        padding: 14px 20px;
        border-radius: 8px;
        color: #fff;
        font-weight: 500;
        box-shadow: var(--shadow-lg);
        animation: slideIn 0.3s ease;
        min-width: 280px;
        max-width: 420px;
    }

    .toast-success { background: var(--success); }
    .toast-error { background: var(--danger); }
    .toast-warning { background: var(--warning); color: var(--gray-900); }
    .toast-info { background: var(--primary); }

    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    #alertaDiferencia {
        display: none;
        margin-top: 8px;
    }

    /* Select2 para clientes */
    .client-select2 .select2-results__option {
        padding: 8px 12px !important;
        font-size: 13px !important;
    }

    .client-select2 .select2-selection__rendered {
        font-weight: normal !important;
    }
</style>

{{-- Incluir JS --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

{{-- Spinner --}}
<div class="spinner-overlay" id="spinnerOverlay">
    <div class="spinner-box">
        <div class="spinner"></div>
        <div class="message">Procesando...</div>
    </div>
</div>

{{-- Toast container --}}
<div class="toast-container" id="toastContainer"></div>

{{-- Inicio POS --}}
<div class="pos-wrapper">

    {{-- PANEL IZQUIERDO: PRODUCTOS --}}
    <div class="pos-products">

        {{-- Buscador --}}
        <div class="pos-card">
            <div class="pos-card-header">
                <span><i class="fa fa-search"></i> Buscar Producto</span>
                <span class="badge">Escanear o escribir</span>
            </div>
            <div class="pos-card-body product-search-wrapper">
                <select class="form-control" id="productoSearch" name="productoSearch" style="width:100%;"></select>
                <div style="margin-top:8px; display:flex; gap:10px;">
                    <input type="text" class="form-control" id="barcodeInput" placeholder="📷 Código de barras" style="flex:1;border-radius:8px;border:2px solid var(--gray-300);padding:8px 12px;font-size:14px;">
                    <button type="button" class="btn btn-primary" id="btnBuscarBarra" style="border-radius:8px;padding:8px 20px;font-weight:600;">
                        <i class="fa fa-barcode"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Lista de productos --}}
        <div class="pos-card" style="flex:1;">
            <div class="pos-card-header">
                <span><i class="fa fa-list"></i> Productos Agregados</span>
                <span class="badge" id="itemCount">0 items</span>
            </div>
            <div class="pos-card-body pos-table-wrapper">
                <table class="pos-table" id="productTable">
                    <thead>
                        <tr>
                            <th style="width:40%;">Producto</th>
                            <th style="width:15%;text-align:center;">Cant.</th>
                            <th style="width:20%;text-align:right;">P.U.</th>
                            <th style="width:20%;text-align:right;">Total</th>
                            <th style="width:5%;text-align:center;"></th>
                        </tr>
                    </thead>
                    <tbody id="productTableBody">
                        {{-- Los productos se agregan dinámicamente --}}
                    </tbody>
                </table>
                <div id="emptyProducts" style="text-align:center;padding:30px 0;color:var(--gray-500);">
                    <i class="fa fa-shopping-bag" style="font-size:40px;display:block;margin-bottom:10px;opacity:0.3;"></i>
                    <span style="font-size:16px;">No hay productos agregados</span>
                    <span style="display:block;font-size:13px;margin-top:4px;">Busca y selecciona un producto para comenzar</span>
                </div>
            </div>
        </div>

        {{-- Observaciones --}}
        <div class="pos-card">
            <div class="pos-card-body" style="padding:10px 20px;">
                <textarea class="form-control" rows="2" id="observaciones" placeholder="Observaciones de la venta..." style="border-radius:8px;border:2px solid var(--gray-300);padding:8px 12px;font-size:14px;resize:vertical;width:100%;"></textarea>
            </div>
        </div>
    </div>

    {{-- PANEL DERECHO: CARRITO --}}
    <div class="pos-cart">

        {{-- Cliente --}}
        <div class="pos-card client-section">
            <div class="pos-card-header">
                <span><i class="fa fa-user"></i> Cliente</span>
                <button type="button" class="btn-new-client" data-toggle="modal" data-target="#modal-cliente">
                    <i class="fa fa-plus"></i> Nuevo
                </button>
            </div>
            <div class="pos-card-body">
                <div class="form-group" style="margin-bottom:8px;">
                    <select class="form-control client-select2" id="clienteSelect" style="width:100%;">
                        <option value="VENTA_AL_PORTADOR" data-ruc="00000000" data-dir="--" data-correo="" data-tel="">VENTA AL PORTADOR</option>
                        @foreach($clientes as $cli)
                            <option value="{{$cli->clicod}}" 
                                    data-ruc="{{$cli->clinum}}" 
                                    data-dir="{{$cli->clidir}}" 
                                    data-correo="{{$cli->clicor}}" 
                                    data-tel="{{$cli->telefono}}"
                                    data-tdoc="{{$cli->tdicod}}">
                                {{$cli->clinum}} - {{$cli->clinom}}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                    <input type="text" class="form-control" id="clienteRuc" placeholder="RUC/DNI" style="border-radius:6px;border:2px solid var(--gray-300);padding:6px 10px;font-size:13px;">
                    <input type="text" class="form-control" id="clienteDir" placeholder="Dirección" style="border-radius:6px;border:2px solid var(--gray-300);padding:6px 10px;font-size:13px;">
                </div>
                <input type="hidden" id="clienteTdoc" value="1">
            </div>
        </div>

        {{-- Puntos de fidelización --}}
        <div class="puntos-box" id="puntosBox">
            <div>
                <span class="puntos-title"><i class="fa fa-gift"></i> Puntos Hola P</span>
                <span class="puntos-value" id="puntosValue">0</span>
            </div>
            <div class="puntos-premios" id="puntosPremios"></div>
            <div id="puntosPremiosSeleccionados" style="margin-top:8px;"></div>
        </div>

        {{-- Métodos de pago --}}
        <div class="pos-card payment-section">
            <div class="pos-card-header">
                <span><i class="fa fa-credit-card"></i> Métodos de Pago</span>
                <span style="font-size:13px;font-weight:400;">Forma: 
                    <select id="formaPago" style="background:rgba(255,255,255,0.2);color:#fff;border:none;border-radius:4px;padding:2px 8px;font-weight:600;">
                        @foreach($creditos as $cre)
                            <option value="{{$cre->cre_dia_id}}" data-tipo="{{$cre->cre_dia_tip}}" style="color:#333;">{{$cre->cre_dia_nom}}</option>
                        @endforeach
                    </select>
                </span>
            </div>
            <div class="pos-card-body">
                {{-- Selector de medio de pago --}}
                <div style="display:flex;gap:8px;margin-bottom:10px;">
                    <select class="form-control" id="medioPagoSelect" style="flex:1;border-radius:8px;border:2px solid var(--gray-300);padding:8px 12px;font-size:14px;">
                        <option value="">Seleccionar medio...</option>
                        @foreach($mediospagos as $mp)
                            @php
                                $predeterminado = ($mp->predeterminado == '1') ? 'selected' : '';
                            @endphp
                            <option value="{{$mp->id_med_pag}}" data-comision="{{$mp->comision ?? 0}}" {{$predeterminado}}>
                                {{$mp->nom_med_pag}} @if(($mp->comision ?? 0) > 0) ({{$mp->comision}}%) @endif
                            </option>
                        @endforeach
                    </select>
                    <input type="number" step="0.01" min="0" class="form-control" id="montoMedioInput" placeholder="0.00" style="width:120px;border-radius:8px;border:2px solid var(--gray-300);padding:8px 12px;font-size:14px;">
                    <button type="button" class="btn btn-success" id="btnAddMedio" style="border-radius:8px;padding:8px 16px;font-weight:600;">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>

                {{-- Lista de medios --}}
                <div id="mediosPagoList" style="max-height:200px;overflow-y:auto;margin-bottom:10px;">
                    {{-- Se agregan dinámicamente --}}
                </div>

                {{-- Resumen de pagos --}}
                <div class="payment-total-row">
                    <span class="label">Total Pagado</span>
                    <span class="amount" id="totalPagado">S/ 0.00</span>
                </div>

                {{-- Alerta de diferencia --}}
                <div class="alert alert-warning" id="alertaDiferencia" style="margin-top:8px;padding:8px 12px;font-size:13px;">
                    <strong>¡Atención!</strong> 
                    <span id="textoFalta">Falta por pagar:</span>
                    <span id="textoExceso" style="display:none;">Excede el total por:</span>
                    <strong id="diferenciaMonto">S/ 0.00</strong>
                </div>

                {{-- Totales --}}
                <div class="totals-grid">
                    <div class="total-box">
                        <div class="label">Subtotal</div>
                        <div class="value" id="subtotalDisplay">S/ 0.00</div>
                    </div>
                    <div class="total-box">
                        <div class="label">Descuento</div>
                        <div class="value" id="descuentoDisplay" style="color:var(--danger);">S/ 0.00</div>
                    </div>
                    <div class="total-box" style="background:var(--primary);">
                        <div class="label" style="color:rgba(255,255,255,0.8);">Total</div>
                        <div class="value" style="color:#fff;font-size:24px;" id="totalDisplay">S/ 0.00</div>
                    </div>
                </div>

                {{-- Inputs ocultos para el formulario --}}
                <input type="hidden" id="totalHidden" name="total" value="0">
                <input type="hidden" id="descuentoGlobalHidden" name="descuento_global" value="0">
                <input type="hidden" id="comisionTotalHidden" name="comision" value="0">
                <input type="hidden" id="vueltoHidden" name="vuelto" value="0">
                <input type="hidden" id="pagarHidden" name="pagar" value="0">

                {{-- Botones de acción --}}
                <div class="action-buttons">
                    <button type="button" class="btn-pos btn-pos-success" id="btnImprimir">
                        <i class="fa fa-print"></i> Imprimir
                    </button>
                    <button type="button" class="btn-pos btn-pos-primary" id="btnRegistrar">
                        <i class="fa fa-check"></i> Registrar
                    </button>
                    <button type="button" class="btn-pos btn-pos-outline" id="btnCancelar" style="grid-column:1/3;">
                        <i class="fa fa-times"></i> Cancelar Venta
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Formulario oculto para envío --}}
<form id="posForm" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="tdocod" id="formTdocod" value="03">
    <input type="hidden" name="moncod" value="PEN">
    <input type="hidden" name="fecEmi" value="{{Carbon\Carbon::now()->format('Y-m-d')}}">
    <input type="hidden" name="estadopago" id="formEstadopago" value="1">
    <input type="hidden" name="vendedor" value="{{Auth::user()->IdUsuario}}">
    <input type="hidden" name="id_almacen" value="{{$almacenes->first()->id_almacen ?? 1}}">
    <input type="hidden" name="clinum" id="formClinum">
    <input type="hidden" name="clinom" id="formClinom">
    <input type="hidden" name="clidir" id="formClidir">
    <input type="hidden" name="clicor" id="formClicor">
    <input type="hidden" name="tdicod" id="formTdicod" value="1">
    <input type="hidden" name="opcion" id="formOpcion" value="0">
    <input type="hidden" name="observaciones" id="formObservaciones">
    <input type="hidden" name="tipo_desc" value="{{$datos->tipo_desc ?? 1}}">
</form>

{{-- Modal PDF --}}
@if(!empty($codfact) && $datos->ticket_pantalla == '1' && $datos->formato == 'A4')
<div class="modal fade" id="modalPdf" tabindex="-1" role="dialog" style="z-index:99999;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--primary);color:#fff;border-radius:12px 12px 0 0;">
                <h5 class="modal-title"><i class="fa fa-file-pdf-o"></i> Comprobante Generado</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
            </div>
            <div class="modal-body" style="padding:0;">
                <iframe src="{{asset('pdf/'.$pdfData->serdoc??'')}}" style="width:100%;height:600px;border:none;"></iframe>
            </div>
            <div class="modal-footer">
                <a href="{{asset('pdf/'.$pdfData->serdoc??'')}}" target="_blank" class="btn btn-primary"><i class="fa fa-download"></i> Descargar</a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- JavaScript principal --}}
<script>
$(document).ready(function() {

    // ============================================
    // 1. CONFIGURACIÓN DE SELECT2 PARA PRODUCTOS
    // ============================================
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

    $('#productoSearch').select2({
        minimumInputLength: 2,
        tags: true,
        allowClear: true,
        ajax: {
            url: "{{route('Productos.consultarproductos')}}",
            dataType: 'json',
            type: "POST",
            quietMillis: 50,
            data: function(params) {
                return {
                    _token: CSRF_TOKEN,
                    search: params.term,
                    almacen: $('input[name="id_almacen"]').val() || 1
                };
            },
            processResults: function(response) {
                return {
                    results: $.map(response, function(item) {
                        return {
                            text: item.text || item.producto,
                            id: item.id,
                            producto: item.producto,
                            propun: item.propun,
                            unidad: item.unidad,
                            codigo: item.codigo,
                            icbper: item.icbper || 0,
                            mon_icbper: item.mon_icbper || 0,
                            id_almacen_pro: item.id_almacen || 1,
                            pro_rel: item.pro_rel || 0,
                            presentacion: item.contar || 0
                        };
                    })
                };
            },
            cache: false
        }
    });

    // Al seleccionar un producto - ABRE EL MODAL
    $('#productoSearch').on('select2:select', function(e) {
        var data = e.params.data;
        
        // Verificar si tiene presentaciones
        if (data.presentacion > 0) {
            // Tiene presentaciones, abrir modal de presentaciones
            presentaciones(data.id);
        } else {
            // Abrir modal de cantidad/precio
            abrirModalCantidadPrecio(data);
        }
        
        $('#productoSearch').val(null).trigger('change');
    });

    // ============================================
    // 2. FUNCIÓN PARA ABRIR MODAL DE CANTIDAD/PRECIO
    // ============================================
    function abrirModalCantidadPrecio(data) {
        $('#des_producto').val(data.producto);
        $('#id_producto').val(data.id);
        $('#pre_producto').val(data.propun);
        $('#pre_producto_ref').val(data.propun);
        $('#uni_producto').val(data.unidad || 'NIU');
        $('#id_almacen_pro').val(data.id_almacen_pro || 1);
        $('#cod_producto').val(data.codigo || '');
        $('#icbper').val(data.icbper || 0);
        $('#mon_icbper').val(data.mon_icbper || 0);
        $('#can_producto').val(1);
        
        $('#modal-cantidad-precio').modal('show');
        setTimeout(function() {
            $('#can_producto').focus().select();
        }, 300);
    }

    // ============================================
    // 3. FUNCIÓN DE PRESENTACIONES
    // ============================================
    window.presentaciones = function(proid) {
        $('#modal-presentaciones').modal('show');
        $('#presentaciones').html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
        
        $.ajax({
            type: "GET",
            dataType: 'json',
            url: "/presentacionesproducto/" + proid,
        }).done(function(respuesta) {
            $("#presentaciones").html(respuesta.vista);
        });
    };

    // ============================================
    // 4. AGREGAR ITEM DESDE EL MODAL
    // ============================================
    // Botón "Agregar" en el modal
    $('#btnAgregarLista').on('click', function() {
        agregarItemDesdeModal();
    });

    // Enter en los campos del modal
    $('#can_producto, #pre_producto').on('keypress', function(e) {
        if (e.which == 13) {
            e.preventDefault();
            agregarItemDesdeModal();
        }
    });

    function agregarItemDesdeModal() {
        var producto = $('#des_producto').val();
        var precio = parseFloat($('#pre_producto').val()) || 0;
        var proid = $('#id_producto').val();
        var unidad = $('#uni_producto').val();
        var cantidad = parseFloat($('#can_producto').val()) || 1;
        var id_almacen_pro = $('#id_almacen_pro').val() || 1;
        var icbper = parseFloat($('#icbper').val()) || 0;
        var mon_icbper = parseFloat($('#mon_icbper').val()) || 0;
        var codigo = $('#cod_producto').val();

        if (!proid || !producto) {
            mostrarToast('Seleccione un producto válido', 'warning');
            return;
        }

        if (cantidad <= 0) {
            mostrarToast('Ingrese una cantidad válida', 'warning');
            return;
        }

        if (precio <= 0) {
            mostrarToast('Ingrese un precio válido', 'warning');
            return;
        }

        // Verificar si ya existe
        var existente = $('#productTableBody tr[data-proid="' + proid + '"]');
        if (existente.length > 0) {
            var input = existente.find('.cant-input');
            var nuevaCant = parseFloat(input.val()) + cantidad;
            input.val(nuevaCant).trigger('change');
            $('#modal-cantidad-precio').modal('hide');
            mostrarToast('Cantidad actualizada', 'info');
            return;
        }

        var total = cantidad * precio;

        var html = `
            <tr data-proid="${proid}" data-icbper="${icbper}" data-mon_icbper="${mon_icbper}" data-id_almacen="${id_almacen_pro}">
                <td>
                    <span class="product-name">${producto}</span>
                    <span class="product-code">${codigo || ''}</span>
                    <input type="hidden" class="proid-hidden" value="${proid}">
                    <input type="hidden" class="unid-hidden" value="${unidad || 'NIU'}">
                    <input type="hidden" class="pronom-hidden" value="${producto}">
                </td>
                <td style="text-align:center;">
                    <input type="number" step="any" min="0.01" value="${cantidad}" class="cant-input" style="width:65px;text-align:center;">
                </td>
                <td style="text-align:right;">
                    <span class="precio-unitario">${precio.toFixed(2)}</span>
                    <input type="hidden" class="precio-hidden" value="${precio}">
                </td>
                <td style="text-align:right;">
                    <span class="item-total">${total.toFixed(2)}</span>
                    <input type="hidden" class="itemtotal-hidden" value="${total.toFixed(2)}">
                </td>
                <td style="text-align:center;">
                    <button type="button" class="btn-remove-item" onclick="eliminarProducto(this)"><i class="fa fa-trash"></i></button>
                </td>
            </tr>
        `;

        $('#productTableBody').append(html);
        $('#emptyProducts').hide();

        var tr = $('#productTableBody tr:last');
        tr.find('.cant-input').on('change keyup', function() {
            recalcularFila($(this));
        });

        recalcularTodo();
        actualizarContadorItems();

        $('#modal-cantidad-precio').modal('hide');
        mostrarToast('Producto agregado: ' + producto, 'success');
    }

    // ============================================
    // 5. FUNCIONES DE PRODUCTOS (eliminar, recalcular)
    // ============================================
    window.eliminarProducto = function(btn) {
        $(btn).closest('tr').remove();
        if ($('#productTableBody tr').length == 0) {
            $('#emptyProducts').show();
        }
        recalcularTodo();
        actualizarContadorItems();
    };

    function recalcularFila(input) {
        var tr = input.closest('tr');
        var cantidad = parseFloat(input.val()) || 0;
        var precio = parseFloat(tr.find('.precio-hidden').val()) || 0;
        var total = cantidad * precio;
        tr.find('.item-total').text(total.toFixed(2));
        tr.find('.itemtotal-hidden').val(total.toFixed(2));
        recalcularTodo();
    }

    function recalcularTodo() {
        var subtotal = 0;
        var totalItems = 0;

        $('#productTableBody tr').each(function() {
            var total = parseFloat($(this).find('.itemtotal-hidden').val()) || 0;
            subtotal += total;
            totalItems++;
        });

        var descuentoPorcentaje = parseFloat($('#descuentoGlobalHidden').val()) || 0;
        var descuentoMonto = subtotal * (descuentoPorcentaje / 100);
        var totalFinal = subtotal - descuentoMonto;

        // Agregar comisiones de medios de pago
        var comisionTotal = calcularComisionesMedios();
        totalFinal = totalFinal + comisionTotal;

        $('#subtotalDisplay').text('S/ ' + subtotal.toFixed(2));
        $('#descuentoDisplay').text('S/ ' + descuentoMonto.toFixed(2));
        $('#totalDisplay').text('S/ ' + totalFinal.toFixed(2));
        $('#totalHidden').val(totalFinal.toFixed(2));
        $('#itemCount').text(totalItems + ' items');

        actualizarPuntos();
        actualizarMediosPago();
    }

    function actualizarContadorItems() {
        var count = $('#productTableBody tr').length;
        $('#itemCount').text(count + ' items');
    }

    // ============================================
    // 6. BÚSQUEDA POR CÓDIGO DE BARRAS
    // ============================================
    $('#btnBuscarBarra').on('click', function() {
        buscarPorBarra();
    });

    $('#barcodeInput').on('keypress', function(e) {
        if (e.which == 13) {
            e.preventDefault();
            buscarPorBarra();
        }
    });

    function buscarPorBarra() {
        var valor = $('#barcodeInput').val().trim();
        if (!valor) return;

        $.ajax({
            type: 'get',
            url: '/consultarproductosbarra',
            dataType: 'json',
            data: { value: valor },
            success: function(data) {
                if (data && data.length > 0) {
                    var producto = data[0];
                    // Verificar si ya existe
                    var existente = $('#productTableBody tr[data-proid="' + producto.id + '"]');
                    if (existente.length > 0) {
                        var input = existente.find('.cant-input');
                        var newVal = parseFloat(input.val()) + 1;
                        input.val(newVal).trigger('change');
                    } else {
                        // Abrir modal con los datos del producto
                        abrirModalCantidadPrecio({
                            id: producto.id,
                            producto: producto.producto,
                            propun: producto.propun,
                            unidad: producto.unidad,
                            codigo: producto.codigo,
                            icbper: producto.icbper || 0,
                            mon_icbper: producto.mon_icbper || 0,
                            id_almacen_pro: producto.id_almacen || 1
                        });
                    }
                } else {
                    mostrarToast('Producto no encontrado', 'warning');
                }
                $('#barcodeInput').val('');
            },
            error: function() {
                mostrarToast('Error al buscar producto', 'error');
            }
        });
    }

    // ============================================
    // 7. CLIENTE CON SELECT2
    // ============================================
    $('#clienteSelect').select2({
        placeholder: 'Buscar cliente...',
        allowClear: true,
        width: '100%',
        dropdownCssClass: 'client-select2'
    });

    $('#clienteSelect').on('change', function() {
        var selected = $(this).find(':selected');
        var ruc = selected.data('ruc') || '';
        var dir = selected.data('dir') || '';
        var tdoc = selected.data('tdoc') || '1';
        
        $('#clienteRuc').val(ruc);
        $('#clienteDir').val(dir);
        $('#clienteTdoc').val(tdoc);
        
        if (selected.val() != 'VENTA_AL_PORTADOR' && selected.val() != 'VENTA AL PORTADOR') {
            actualizarPuntos();
        } else {
            $('#puntosBox').hide();
        }
    });

    // Trigger inicial
    setTimeout(function() {
        $('#clienteSelect').trigger('change');
    }, 100);

    // ============================================
    // 8. MÉTODOS DE PAGO
    // ============================================
    var mediosPago = [];
    var medioEfectivoId = null;

    // Identificar el medio predeterminado
    @foreach($mediospagos as $mp)
        @if($mp->predeterminado == '1')
            medioEfectivoId = '{{$mp->id_med_pag}}';
        @endif
    @endforeach

    // Agregar medio predeterminado automáticamente si hay productos
    function agregarMedioPredeterminado() {
        if (mediosPago.length > 0) return;
        if (!medioEfectivoId) return;

        var total = parseFloat($('#totalHidden').val()) || 0;
        if (total <= 0) return;

        var option = $('#medioPagoSelect option[value="' + medioEfectivoId + '"]');
        if (!option.length) return;

        var nombre = option.text().trim();
        var comision = parseFloat(option.data('comision')) || 0;

        // Quitar el "(X%)" del nombre si existe
        nombre = nombre.replace(/\s*\([^)]*\)\s*$/, '').trim();

        mediosPago.push({
            id: medioEfectivoId,
            nombre: nombre,
            monto: total,
            comision: comision,
            esPredeterminado: true
        });
        renderizarMediosPago();
        recalcularTodo();
    }

    $('#btnAddMedio').on('click', function() {
        var medioId = $('#medioPagoSelect').val();
        var monto = parseFloat($('#montoMedioInput').val()) || 0;

        if (!medioId) {
            mostrarToast('Seleccione un medio de pago', 'warning');
            return;
        }

        if (monto <= 0) {
            mostrarToast('Ingrese un monto válido', 'warning');
            return;
        }

        // Si es el predeterminado y ya existe, actualizar monto
        if (medioId == medioEfectivoId) {
            var existente = mediosPago.find(m => m.id == medioId);
            if (existente) {
                existente.monto = monto;
                renderizarMediosPago();
                recalcularTodo();
                $('#montoMedioInput').val('');
                return;
            }
        }

        var option = $('#medioPagoSelect option:selected');
        var nombre = option.text().trim();
        var comision = parseFloat(option.data('comision')) || 0;

        // Quitar el "(X%)" del nombre
        nombre = nombre.replace(/\s*\([^)]*\)\s*$/, '').trim();

        // Verificar duplicado
        if (mediosPago.some(m => m.id == medioId)) {
            mostrarToast('Este medio ya está agregado', 'warning');
            return;
        }

        mediosPago.push({ id: medioId, nombre: nombre, monto: monto, comision: comision });
        renderizarMediosPago();
        recalcularTodo();

        $('#medioPagoSelect').val('').trigger('change');
        $('#montoMedioInput').val('');
    });

    function renderizarMediosPago() {
        var html = '';
        mediosPago.forEach(function(m, index) {
            var comisionMonto = m.monto * (m.comision / 100);
            var totalConComision = m.monto + comisionMonto;
            var esPred = m.esPredeterminado ? '🌟 ' : '';
            html += `
                <div class="payment-item active" data-index="${index}">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span class="payment-name">${esPred}${m.nombre}</span>
                        <button type="button" class="btn-remove-item" style="font-size:14px;" onclick="eliminarMedio(${index})" ${m.esPredeterminado ? 'disabled style="opacity:0.3;cursor:not-allowed;"' : ''}>
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:4px;">
                        <span>S/ </span>
                        <input type="number" step="any" min="0" class="payment-input" value="${m.monto.toFixed(2)}" 
                               onchange="actualizarMontoMedio(${index}, this.value)">
                        <input type="hidden" name="medio[]" value="${m.id}">
                        <input type="hidden" name="monto[]" value="${m.monto.toFixed(2)}">
                    </div>
                    ${m.comision > 0 ? `<div style="font-size:11px;color:var(--gray-600);">Comisión: ${m.comision}% (S/ ${comisionMonto.toFixed(2)})</div>` : ''}
                </div>
            `;
        });

        if (mediosPago.length === 0) {
            html = `<div style="text-align:center;padding:20px;color:var(--gray-500);">No hay medios de pago agregados</div>`;
        }

        $('#mediosPagoList').html(html);
        actualizarTotalPagado();
    }

    window.actualizarMontoMedio = function(index, valor) {
        var monto = parseFloat(valor) || 0;
        if (mediosPago[index]) {
            mediosPago[index].monto = monto;
            renderizarMediosPago();
            recalcularTodo();
        }
    };

    window.eliminarMedio = function(index) {
        if (mediosPago[index] && mediosPago[index].esPredeterminado) {
            mostrarToast('No se puede eliminar el medio predeterminado', 'warning');
            return;
        }
        mediosPago.splice(index, 1);
        renderizarMediosPago();
        recalcularTodo();
    };

    function calcularComisionesMedios() {
        var total = 0;
        mediosPago.forEach(function(m) {
            total += m.monto * (m.comision / 100);
        });
        $('#comisionTotalHidden').val(total.toFixed(2));
        return total;
    }

    function actualizarTotalPagado() {
        var total = 0;
        mediosPago.forEach(function(m) {
            total += m.monto + (m.monto * (m.comision / 100));
        });
        $('#totalPagado').text('S/ ' + total.toFixed(2));
    }

    function actualizarMediosPago() {
        actualizarTotalPagado();
        var totalVenta = parseFloat($('#totalHidden').val()) || 0;
        var totalPagado = 0;
        mediosPago.forEach(function(m) {
            totalPagado += m.monto + (m.monto * (m.comision / 100));
        });

        var diff = totalVenta - totalPagado;
        var alerta = $('#alertaDiferencia');
        
        if (Math.abs(diff) > 0.01) {
            alerta.show();
            $('#diferenciaMonto').text('S/ ' + Math.abs(diff).toFixed(2));
            if (diff > 0) {
                $('#textoFalta').show();
                $('#textoExceso').hide();
            } else {
                $('#textoFalta').hide();
                $('#textoExceso').show();
            }
        } else {
            alerta.hide();
        }
    }

    // ============================================
    // 9. PUNTOS DE FIDELIZACIÓN
    // ============================================
    function actualizarPuntos() {
        var clicod = $('#clienteSelect').val();
        if (!clicod || clicod == 'VENTA_AL_PORTADOR' || clicod == 'VENTA AL PORTADOR') {
            $('#puntosBox').hide();
            return;
        }

        var total = parseFloat($('#subtotalDisplay').text().replace('S/ ', '')) || 0;
        var puntosGanados = Math.floor(total / 1);

        $.ajax({
            type: "GET",
            dataType: "json",
            url: "/cliente/" + clicod + "/puntos"
        }).done(function(res) {
            if (res && res.puntos !== undefined) {
                $('#puntosBox').show();
                var puntosTotales = res.puntos + puntosGanados;
                $('#puntosValue').text(puntosTotales + ' pts');

                var html = '';
                if (res.reglas && res.reglas.length > 0) {
                    html += '<strong>Premios disponibles:</strong><br>';
                    $.each(res.reglas, function(i, regla) {
                        if (puntosTotales >= regla.puntos_minimos) {
                            html += `<span class="badge" style="background:var(--success);color:#fff;padding:4px 12px;margin:2px;display:inline-block;">
                                ${regla.premio} (${regla.puntos_minimos} pts)
                                <button type="button" class="btn-canjear" onclick="canjearPremio(${regla.id}, '${regla.premio}', ${regla.puntos_minimos})">
                                    <i class="fa fa-gift"></i> Canjear
                                </button>
                            </span>`;
                        } else {
                            html += `<span class="badge" style="background:var(--gray-300);color:var(--gray-600);padding:4px 12px;margin:2px;display:inline-block;">
                                ${regla.premio} (${regla.puntos_minimos} pts)
                            </span>`;
                        }
                    });
                }
                $('#puntosPremios').html(html);
            }
        }).fail(function() {
            // Silenciar error
        });
    }

    window.canjearPremio = function(reglaId, premio, costo) {
        var html = `<div class="alert alert-warning" style="padding:6px 12px;margin:4px 0;border-radius:4px;font-size:13px;display:flex;justify-content:space-between;align-items:center;">
            <span><i class="fa fa-gift"></i> ${premio} (-${costo} pts)</span>
            <button type="button" class="btn-remove-item" onclick="$(this).parent().remove(); recalcularTodo();">
                <i class="fa fa-times"></i>
            </button>
            <input type="hidden" name="premios_canjeados[]" value="${reglaId}">
        </div>`;
        $('#puntosPremiosSeleccionados').append(html);
        mostrarToast('Premio canjeado: ' + premio, 'success');
        actualizarPuntos();
    };

    // ============================================
    // 10. REGISTRAR VENTA
    // ============================================
    $('#btnRegistrar, #btnImprimir').on('click', function(e) {
        var esImprimir = $(this).attr('id') == 'btnImprimir';
        
        if ($('#productTableBody tr').length === 0) {
            mostrarToast('Agregue al menos un producto', 'warning');
            return;
        }

        // Verificar diferencia en medios de pago
        var totalVenta = parseFloat($('#totalHidden').val()) || 0;
        var totalPagado = 0;
        mediosPago.forEach(function(m) {
            totalPagado += m.monto + (m.monto * (m.comision / 100));
        });

        if (Math.abs(totalVenta - totalPagado) > 0.01) {
            Swal.fire({
                icon: 'warning',
                title: 'Diferencia en pagos',
                html: `El total de la venta es <b>S/ ${totalVenta.toFixed(2)}</b> y el total pagado es <b>S/ ${totalPagado.toFixed(2)}</b>.<br>¿Desea continuar?`,
                showCancelButton: true,
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Revisar'
            }).then((result) => {
                if (result.isConfirmed) {
                    registrarVenta(esImprimir);
                }
            });
        } else {
            registrarVenta(esImprimir);
        }
    });

    function registrarVenta(imprimir) {
        $('#spinnerOverlay').addClass('show');

        // Recolectar datos
        var productos = [];
        $('#productTableBody tr').each(function() {
            productos.push({
                proid: $(this).find('.proid-hidden').val(),
                pronom: $(this).find('.pronom-hidden').val(),
                cant: $(this).find('.cant-input').val(),
                propun: $(this).find('.precio-hidden').val(),
                itemtotal: $(this).find('.itemtotal-hidden').val(),
                unid: $(this).find('.unid-hidden').val(),
                icbper: $(this).data('icbper') || 0,
                mon_icbper: $(this).data('mon_icbper') || 0,
                id_almacen_pro: $(this).data('id_almacen') || 1,
                desc: 0
            });
        });

        var formData = {
            _token: CSRF_TOKEN,
            tdocod: '03',
            moncod: 'PEN',
            fecEmi: '{{Carbon\Carbon::now()->format("Y-m-d")}}',
            estadopago: $('#formaPago').val(),
            vendedor: '{{Auth::user()->IdUsuario}}',
            id_almacen: $('input[name="id_almacen"]').val() || 1,
            clinum: $('#clienteRuc').val() || '00000000',
            clinom: $('#clienteSelect option:selected').text() || 'VENTA AL PORTADOR',
            clidir: $('#clienteDir').val() || '--',
            clicor: '',
            tdicod: $('#clienteTdoc').val() || '1',
            observaciones: $('#observaciones').val(),
            total: $('#totalHidden').val(),
            descuento_global: $('#descuentoGlobalHidden').val(),
            comision: $('#comisionTotalHidden').val(),
            opcion: imprimir ? '1' : '0',
            proid: productos.map(p => p.proid),
            pronom: productos.map(p => p.pronom),
            cant: productos.map(p => p.cant),
            propun: productos.map(p => p.propun),
            itemtotal: productos.map(p => p.itemtotal),
            unid: productos.map(p => p.unid),
            icbper: productos.map(p => p.icbper),
            mon_icbper: productos.map(p => p.mon_icbper),
            id_almacen_pro: productos.map(p => p.id_almacen_pro),
            desc: productos.map(p => p.desc),
            medio: mediosPago.map(m => m.id),
            monto: mediosPago.map(m => m.monto.toFixed(2))
        };

        // Añadir premios canjeados
        var premios = [];
        $('#puntosPremiosSeleccionados input[name="premios_canjeados[]"]').each(function() {
            premios.push($(this).val());
        });
        if (premios.length > 0) {
            formData.premios_canjeados = premios;
        }

        $.ajax({
            type: "POST",
            dataType: 'json',
            url: "{{route('pvnuevo')}}",
            data: formData,
            success: function(response) {
                $('#spinnerOverlay').removeClass('show');
                if (response.estado == 'success') {
                    mostrarToast('✅ ' + response.mensaje, 'success');
                    if (response.pdf_url) {
                        if (imprimir) {
                            window.open(response.pdf_url, '_blank');
                        } else {
                            // Mostrar en modal
                            $('#modalPdf iframe').attr('src', response.pdf_url);
                            $('#modalPdf').modal('show');
                        }
                    }
                    // Recargar después de 1.5s
                    setTimeout(function() {
                        window.location.href = "{{route('vistaPvnuevo')}}/" + response.codfact;
                    }, 1500);
                } else {
                    mostrarToast('❌ ' + (response.mensaje || 'Error al registrar'), 'error');
                }
            },
            error: function(xhr) {
                $('#spinnerOverlay').removeClass('show');
                var msg = xhr.responseJSON?.mensaje || 'Error de conexión';
                mostrarToast('❌ ' + msg, 'error');
            }
        });
    }

    // ============================================
    // 11. CANCELAR VENTA
    // ============================================
    $('#btnCancelar').on('click', function() {
        Swal.fire({
            title: '¿Cancelar venta?',
            text: 'Se perderán todos los productos agregados',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'Continuar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{route('vistaPvnuevo')}}";
            }
        });
    });

    // ============================================
    // 12. FORMA DE PAGO
    // ============================================
    $('#formaPago').on('change', function() {
        var tipo = $(this).find(':selected').data('tipo');
        if (tipo == 'CONTADO') {
            $('.payment-section .pos-card-body').show();
        } else {
            $('.payment-section .pos-card-body').hide();
        }
    });

    // ============================================
    // 13. TOAST NOTIFICATIONS
    // ============================================
    function mostrarToast(mensaje, tipo) {
        var icon = tipo == 'success' ? '✅' : tipo == 'error' ? '❌' : tipo == 'warning' ? '⚠️' : 'ℹ️';
        var color = tipo == 'success' ? 'var(--success)' : tipo == 'error' ? 'var(--danger)' : tipo == 'warning' ? 'var(--warning)' : 'var(--primary)';
        var textColor = tipo == 'warning' ? 'var(--gray-900)' : '#fff';

        var toast = $(`
            <div class="toast" style="background:${color};color:${textColor};">
                ${icon} ${mensaje}
            </div>
        `);

        $('#toastContainer').append(toast);
        setTimeout(function() {
            toast.fadeOut(300, function() { $(this).remove(); });
        }, 4000);
    }

    // ============================================
    // 14. INICIALIZAR
    // ============================================
    recalcularTodo();

    // Agregar medio predeterminado después de 500ms
    setTimeout(function() {
        agregarMedioPredeterminado();
    }, 500);

    // Forzar actualización de puntos después de 1s
    setTimeout(function() {
        actualizarPuntos();
    }, 800);

    // Al cambiar el total, actualizar el medio predeterminado
    $(document).on('change keyup', '#totalHidden', function() {
        if (mediosPago.length === 0) {
            agregarMedioPredeterminado();
        } else {
            // Actualizar monto del predeterminado
            var pred = mediosPago.find(m => m.esPredeterminado);
            if (pred) {
                var total = parseFloat($('#totalHidden').val()) || 0;
                pred.monto = total;
                renderizarMediosPago();
                recalcularTodo();
            }
        }
    });

});
</script>

@endsection