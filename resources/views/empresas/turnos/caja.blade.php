@extends('layouts.empresas')
@section('contenido')

<!--<style>
    /* ========== ESTILOS GENERALES ========== */
    * {
        box-sizing: border-box;
    }

    body {
        background-color: #f4f6f9;
    }

    /* ========== ALERTAS MEJORADAS ========== */
    .alert-container {
        margin-bottom: 20px;
    }

    .alert {
        border-radius: 10px;
        border: none;
        padding: 15px 20px;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        animation: slideInDown 0.4s ease-out;
    }

    .alert-danger {
        background: linear-gradient(135deg, #E74C3C 0%, #C0392B 100%);
        color: white;
        border-left: 5px solid #A93226;
    }

    .alert-success {
        background: linear-gradient(135deg, #27AE60 0%, #229954 100%);
        color: white;
        border-left: 5px solid #1e7e34;
    }

    .alert strong {
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 12px;
    }

    .alert .close {
        opacity: 0.8;
        transition: opacity 0.3s ease;
    }

    .alert .close:hover {
        opacity: 1;
    }

    /* ========== BOX STYLES ========== */
    .box {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        border: none;
        background-color: white;
        margin-bottom: 20px;
        transition: box-shadow 0.3s ease;
    }

    .box:hover {
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
    }

    .box-header {
        background: linear-gradient(135deg, #34495E 0%, #2C3E50 100%);
        color: white;
        padding: 18px 25px;
        font-weight: 700;
        font-size: 14px;
        text-transform: uppercase;
        border-radius: 12px 12px 0 0;
        letter-spacing: 0.8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .box-header.with-border {
        border-bottom: 4px solid #3498DB;
    }

    .box-body {
        padding: 25px;
        background-color: #f9fafb;
    }

    .box-tools {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    /* ========== HEADER SECTION ========== */
    .header-title {
        flex: 1;
        min-width: 200px;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .header-title i {
        font-size: 20px;
        opacity: 0.9;
    }

    .header-subtitle {
        font-size: 12px;
        opacity: 0.85;
        font-weight: 500;
        letter-spacing: 0.5px;
    }

    /* ========== BUTTON STYLES ========== */
    .btn {
        border-radius: 6px;
        font-weight: 700;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        padding: 10px 16px;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .btn:active {
        transform: translateY(0);
    }

    .btn-success {
        background: linear-gradient(135deg, #27AE60 0%, #229954 100%);
        color: white;
    }

    .btn-success:hover {
        background: linear-gradient(135deg, #229954 0%, #1e7e34 100%);
    }

    .btn-danger {
        background: linear-gradient(135deg, #E74C3C 0%, #C0392B 100%);
        color: white;
    }

    .btn-danger:hover {
        background: linear-gradient(135deg, #C0392B 0%, #a93226 100%);
    }

    .btn-info {
        background: linear-gradient(135deg, #3498DB 0%, #2980B9 100%);
        color: white;
    }

    .btn-info:hover {
        background: linear-gradient(135deg, #2980B9 0%, #1f618d 100%);
    }

    .btn-primary {
        background: linear-gradient(135deg, #9B59B6 0%, #8E44AD 100%);
        color: white;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #8E44AD 0%, #7d3c98 100%);
    }

    .btn-warning {
        background: linear-gradient(135deg, #F39C12 0%, #E67E22 100%);
        color: white;
    }

    .btn-warning:hover {
        background: linear-gradient(135deg, #E67E22 0%, #d35400 100%);
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 10px;
    }

    .btn:disabled,
    .btn[disabled="disabled"] {
        background: linear-gradient(135deg, #BDC3C7 0%, #95A5A6 100%);
        color: white;
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    /* ========== TABLE STYLES ========== */
    .table-responsive {
        border-radius: 10px;
        overflow: hidden;
    }

    .table {
        background-color: white;
        border-collapse: collapse;
        margin-bottom: 0;
        font-size: 12px;
    }

    .table thead {
        background: linear-gradient(135deg, #34495E 0%, #2C3E50 100%);
        color: white;
    }

    .table thead th {
        padding: 15px 12px;
        font-weight: 700;
        text-align: center;
        border: none;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 11px;
    }

    .table thead tr:first-child th {
        background: linear-gradient(135deg, #3498DB 0%, #2980B9 100%);
        padding: 12px;
        font-size: 13px;
        color: white;
    }

    .table tbody tr {
        border-bottom: 1px solid #ecf0f1;
        transition: all 0.3s ease;
        background-color: white;
    }

    .table tbody tr:hover {
        background-color: #f0f7ff;
        box-shadow: inset 0 0 8px rgba(52, 152, 219, 0.1);
    }

    .table tbody td {
        padding: 14px 12px;
        vertical-align: middle;
        color: #2c3e50;
    }

    .table tbody td:first-child {
        font-weight: 600;
        color: #2980B9;
    }

    /* ========== STATUS STYLES ========== */
    .badge-status {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-status.abierto {
        background: linear-gradient(135deg, #27AE60 0%, #229954 100%);
        color: white;
    }

    .badge-status.cerrado {
        background: linear-gradient(135deg, #E74C3C 0%, #C0392B 100%);
        color: white;
    }

    /* ========== DROPDOWN STYLES ========== */
    .dropdown-menu {
        border-radius: 8px;
        border: none;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
        padding: 8px 0;
        animation: slideDown 0.3s ease;
    }

    .dropdown-menu li a {
        padding: 10px 20px;
        color: #2c3e50;
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .dropdown-menu li a:hover {
        background: linear-gradient(135deg, #ECF0F1 0%, #D5DBDB 100%);
        color: #2980B9;
        padding-left: 25px;
    }

    .dropdown-menu li.divider {
        height: 1px;
        background: linear-gradient(to right, transparent, #ecf0f1, transparent);
        margin: 8px 0;
    }

    /* ========== BUTTONS GROUP ========== */
    .btn-group-table {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .btn-group-table .btn {
        margin-bottom: 4px;
    }

    /* ========== PAGINATION ========== */
    .pagination {
        display: flex;
        justify-content: center;
        gap: 5px;
        margin-top: 20px;
        flex-wrap: wrap;
    }

    .pagination li a,
    .pagination li span {
        padding: 8px 12px;
        border-radius: 6px;
        border: 2px solid #ecf0f1;
        color: #2c3e50;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-block;
    }

    .pagination li a:hover {
        background: linear-gradient(135deg, #3498DB 0%, #2980B9 100%);
        color: white;
        border-color: #2980B9;
        transform: translateY(-2px);
    }

    .pagination li.active span {
        background: linear-gradient(135deg, #3498DB 0%, #2980B9 100%);
        color: white;
        border-color: #2980B9;
    }

    /* ========== SEARCH SECTION ========== */
    .search-box {
        background: white;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 15px;
    }

    .search-box .form-control {
        border-radius: 6px;
        border: 2px solid #ecf0f1;
        padding: 10px 15px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .search-box .form-control:focus {
        border-color: #3498DB;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        outline: none;
    }

    /* ========== ANIMACIONES ========== */
    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ========== RESPONSIVE ========== */
    @media (max-width: 1200px) {
        .box-header {
            padding: 15px 20px;
            flex-direction: column;
            align-items: flex-start;
        }

        .table {
            font-size: 11px;
        }

        .table thead th {
            padding: 12px 8px;
            font-size: 10px;
        }

        .table tbody td {
            padding: 12px 8px;
        }

        .btn-group-table {
            gap: 4px;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 9px;
        }
    }

    @media (max-width: 768px) {
        .box-body {
            padding: 15px;
        }

        .box-header {
            padding: 12px 15px;
            font-size: 12px;
        }

        .table {
            font-size: 10px;
        }

        .table thead th {
            padding: 10px 6px;
            font-size: 9px;
        }

        .table tbody td {
            padding: 10px 6px;
        }

        .alert {
            padding: 12px 15px;
        }

        .btn-group-table {
            flex-direction: column;
            width: 100%;
        }

        .btn-group-table .btn {
            width: 100%;
            justify-content: center;
            margin-bottom: 6px;
        }
    }

    @media (max-width: 576px) {
        .box-header {
            padding: 10px 12px;
            font-size: 11px;
        }

        .box-body {
            padding: 12px;
        }

        .table {
            font-size: 9px;
        }

        .table thead th {
            padding: 8px 4px;
            font-size: 8px;
        }

        .table tbody td {
            padding: 8px 4px;
        }

        .btn-sm {
            padding: 4px 8px;
            font-size: 8px;
        }

        .alert {
            padding: 10px 12px;
            font-size: 11px;
        }

        .alert strong {
            font-size: 10px;
        }
    }

    /* ========== UTILIDADES ========== */
    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }

    .mb-20 {
        margin-bottom: 20px;
    }

    .mt-20 {
        margin-top: 20px;
    }

    .hidden-element {
        display: none;
    }
</style>-->

<section class="content">
    <div class="container-fluid">
        
        <!-- ALERTAS MEJORADAS -->
        <div class="alert-container">
            @if(session()->has('danger'))
                <div class="alert alert-danger" role="alert">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    <strong>⚠ Alerta!</strong> {{ session('danger') }}
                </div>
            @endif

            @if(session()->has('success'))
                <div class="alert alert-success" role="alert">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    <strong>✓ Información!</strong> {{ session('success') }}
                </div>
            @endif
        </div>

        <!-- CAJA PRINCIPAL -->
        <div class="box">
            <div class="box-header with-border">
                <div class="header-title">
                    <i class="fa fa-cash"></i>
                    <div>
                        <strong>GESTIÓN DE CAJAS Y TURNOS</strong>
                        <div class="header-subtitle">{{ $datosusuario->name }} {{ $datosusuario->apeusu }}</div>
                    </div>
                </div>
                <div class="box-tools">
                    @if(Auth::User()->turno == 'Cerrado')
                        <a href="" data-target="#modal-aperturar" data-toggle="modal">
                            <button class="btn btn-success">
                                <i class="fa fa-unlock"></i> Aperturar Turno
                            </button>
                        </a>
                    @else
                        <a href="" data-target="#modal-cerrar" data-toggle="modal">
                            <button class="btn btn-danger">
                                <i class="fa fa-lock"></i> Cerrar Turno
                            </button>
                        </a>
                    @endif
                </div>
            </div>

            <div class="box-body">
                <!-- BÚSQUEDA (Solo Admin) -->
                @if(Auth::user()->hasRole('admin'))
                    <div class="search-box">
                        @include('empresas.turnos.search')
                    </div>
                @endif

                <!-- TABLA DE TURNOS -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th colspan="10" class="text-center">
                                    <strong>📊 TURNOS REGISTRADOS</strong>
                                </th>
                            </tr>
                            <tr>
                                <th>Ubicación</th>
                                <th>Nombre - Apellidos</th>
                                <th>Usuario</th>
                                <th>Estado</th>
                                <th>Apertura</th>
                                <th>Monto Apertura</th>
                                <th>Cierre</th>
                                <th>Monto Cierre</th>
                                <th>Arqueo</th>
                                <th>Opciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($turnos as $turno)
                                <tr>
                                    <td>{{ $turno->tipo_negocio }}</td>
                                    <td><strong>{{ $turno->name }} {{ $turno->apeusu }}</strong></td>
                                    <td>{{ $turno->email }}</td>
                                    <td>
                                        <span class="badge-status {{ strtolower($turno->estado) === 'cerrado' ? 'cerrado' : 'abierto' }}">
                                            {{ $turno->estado }}
                                        </span>
                                    </td>
                                    <td>{{ $turno->apertura }}</td>
                                    <td class="text-right">
                                        <strong>{{ $turno->monto }}</strong>
                                    </td>
                                    <td>{{ $turno->cierre ?? '---' }}</td>
                                    <td class="text-right">
                                        <strong>{{ $turno->montocierre ?? '---' }}</strong>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-success btn-sm dropdown-toggle" type="button" 
                                                    id="dropdownMenu-{{ $turno->id_turno }}" 
                                                    data-toggle="dropdown" 
                                                    aria-haspopup="true" 
                                                    aria-expanded="false">
                                                <i class="fa fa-list"></i> Opciones
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenu-{{ $turno->id_turno }}">
                                                <li><a href="/ventasturno/{{ $turno->id_turno }}"><i class="fa fa-eye"></i> Ver Arqueo</a></li>
                                                <li><a href="/ventasturnoexcel/{{ $turno->id_turno }}"><i class="fa fa-file-excel-o"></i> Excel Arqueo</a></li>
                                                <li class="divider"></li>
                                                <li hidden="hidden"><a href="/arqueoresumen/{{ $turno->id_turno }}" target="_blank"><i class="fa fa-download"></i> Resumen</a></li>
                                                <li hidden="hidden"><a href="/arqueodetallado/{{ $turno->id_turno }}" target="_blank"><i class="fa fa-download"></i> Detallado</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group-table">
                                            @if($turno->estado == 'Cerrado')
                                                @if(Auth::user()->hasRole('admin'))
                                                    <a href="/imprimircaja/{{ $turno->id_turno }}" target="_blank">
                                                        <button class="btn btn-info btn-sm">
                                                            <i class="fa fa-print"></i> Reporte
                                                        </button>
                                                    </a>
                                                    <a href="/imprimircajaproductos/{{ $turno->id_turno }}" target="_blank">
                                                        <button class="btn btn-info btn-sm">
                                                            <i class="fa fa-box"></i> Productos
                                                        </button>
                                                    </a>
                                                    <a href="{{ route('reporte.categorias.productos.turno', ['id' => $turno->id_turno]) }}" target="_blank">
                                                        <button class="btn btn-info btn-sm">
                                                            <i class="fa fa-list-ul"></i> Categorías
                                                        </button>
                                                    </a>
                                                    <a href="{{ route('reporte.autoconsumo.turno', ['id' => $turno->id_turno]) }}" target="_blank">
                                                        <button class="btn btn-info btn-sm">
                                                            <i class="fa fa-gift"></i> Cortesías
                                                        </button>
                                                    </a>
                                                    <a href="{{ route('reporte.denominaciones.turno', ['turno' => $turno->id_turno]) }}" target="_blank">
                                                        <button class="btn btn-warning btn-sm">
                                                            <i class="fa fa-calculator"></i> Denominaciones
                                                        </button>
                                                    </a>
                                                @elseif(Auth::user()->hasRole('caja'))
                                                    <a href="{{ route('reporte.denominaciones.turno', ['turno' => $turno->id_turno]) }}" target="_blank">
                                                        <button class="btn btn-warning btn-sm">
                                                            <i class="fa fa-calculator"></i> Denominaciones
                                                        </button>
                                                    </a>
                                                @endif

                                                <!--<a href="" data-target="#modal-actualizarcerrar-{{ $turno->id_turno }}" data-toggle="modal">
                                                    <button class="btn btn-warning btn-sm">
                                                        <i class="fa fa-edit"></i> Modificar
                                                    </button>
                                                </a>-->
                                            @else
                                                <button class="btn btn-info btn-sm" disabled="disabled">
                                                    <i class="fa fa-print"></i> Reporte
                                                </button>
                                                <button class="btn btn-info btn-sm" disabled="disabled">
                                                    <i class="fa fa-box"></i> Productos
                                                </button>
                                                <button class="btn btn-info btn-sm" disabled="disabled">
                                                    <i class="fa fa-list-ul"></i> Categorías
                                                </button>
                                                <!--<button class="btn btn-warning btn-sm" disabled="disabled">
                                                    <i class="fa fa-edit"></i> Modificar
                                                </button>-->
                                            @endif
                                            <!--<a href="/movimientosturno/{{ $turno->id_turno }}">
                                                <button class="btn btn-primary btn-sm">
                                                    <i class="fa fa-exchange"></i> Movimientos
                                                </button>
                                            </a>-->
                                        </div>
                                    </td>
                                </tr>
                                @include('empresas.turnos.modalactualizarcerrar')
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center" style="padding: 30px;">
                                        <strong style="color: #95a5a6; font-size: 14px;">
                                            <i class="fa fa-inbox"></i> No hay turnos registrados
                                        </strong>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- PAGINACIÓN -->
                <div class="mt-20">
                    {{ $turnos->render() }}
                </div>
            </div>
        </div>

    </div>
</section>

<!-- MODALES (Sin cambios en la funcionalidad) -->
@include('empresas.turnos.turno')
@include('empresas.turnos.cierre')

@endsection