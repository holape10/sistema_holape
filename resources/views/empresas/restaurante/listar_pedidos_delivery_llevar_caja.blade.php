<style>
    /* ========== RESETEAR Y CONTENEDOR PRINCIPAL ========== */
    #listar_mesas.mesas-section {
        display: block !important;
        padding: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        border-radius: 0 !important;
    }

    #listar_mesas {
        display: block !important;
        grid-template-columns: none !important;
        gap: 0 !important;
        min-height: auto !important;
        padding: 0 !important;
    }

    /* ========== CONTENEDOR DE PEDIDOS ========== */
    .pedidos-delivery-llevar {
        background: white;
        border-radius: 12px;
        padding: 0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        animation: slideInUp 0.4s ease-out;
        width: 100%;
        margin: 0;
    }

    /* ========== ENCABEZADO ========== */
    .pedidos-delivery-llevar .pedidos-header {
        background: linear-gradient(135deg, #34495E 0%, #2C3E50 100%);
        color: white;
        padding: 15px 20px;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 12px 12px 0 0;
    }

    .pedidos-delivery-llevar .pedidos-count {
        background: rgba(255, 255, 255, 0.2);
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    /* ========== TABLA DE PEDIDOS ========== */
    .table-pedidos-delivery {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
        background: white;
    }

    .table-pedidos-delivery thead {
        background: linear-gradient(135deg, #2C3E50 0%, #1a252f 100%);
        color: white;
    }

    .table-pedidos-delivery thead th {
        padding: 14px 12px;
        font-weight: 700;
        font-size: 11px;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: none;
        white-space: nowrap;
    }

    .table-pedidos-delivery tbody tr {
        border-bottom: 1px solid #ecf0f1;
        transition: all 0.3s ease;
    }

    .table-pedidos-delivery tbody tr:hover {
        background-color: #f8f9fa;
        box-shadow: inset 4px 0 0 0 #3498DB;
    }

    .table-pedidos-delivery tbody td {
        padding: 12px;
        vertical-align: middle;
        font-size: 12px;
        color: #2c3e50;
        text-align: center;
    }

    .table-pedidos-delivery tbody td:nth-child(1) {
        font-weight: 800;
        color: #3498DB;
        text-align: center;
    }

    .table-pedidos-delivery tbody td:nth-child(2) {
        font-weight: 600;
        text-align: left;
    }

    .table-pedidos-delivery tbody td:nth-child(3) {
        text-align: left;
        font-size: 11px;
    }

    .table-pedidos-delivery tbody td:nth-child(4),
    .table-pedidos-delivery tbody td:nth-child(5) {
        text-align: center;
        font-size: 11px;
    }

    .table-pedidos-delivery tbody td:nth-child(6) {
        text-align: center;
    }

    /* ========== ÍCONO EN TABLA ========== */
    .icon-cell {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .icon-cell i {
        font-size: 14px;
    }

    .icon-location {
        color: #E74C3C;
    }

    .icon-phone {
        color: #27AE60;
    }

    /* ========== BOTÓN COBRAR ========== */
    .btn-cobrar-pedido {
        background: linear-gradient(135deg, #3498DB 0%, #2980B9 100%);
        color: white;
        border: none;
        padding: 8px 14px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 6px rgba(52, 152, 219, 0.3);
        display: inline-flex;
        align-items: center;
        gap: 5px;
        text-decoration: none;
    }

    .btn-cobrar-pedido:hover {
        background: linear-gradient(135deg, #2980B9 0%, #1f618d 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(52, 152, 219, 0.4);
        color: white;
        text-decoration: none;
    }

    .btn-cobrar-pedido:active {
        transform: translateY(0);
    }

    .btn-cobrar-pedido i {
        font-size: 11px;
    }

    /* ========== ESTADO VACÍO ========== */
    .empty-pedidos-state {
        text-align: center;
        padding: 50px 20px;
        color: #95a5a6;
        background: white;
        border-radius: 0 0 12px 12px;
    }

    .empty-pedidos-state i {
        font-size: 48px;
        margin-bottom: 12px;
        opacity: 0.3;
        display: block;
    }

    .empty-pedidos-state p {
        font-size: 13px;
        margin: 0;
        font-weight: 500;
    }

    /* ========== SCROLL HORIZONTAL ========== */
    .table-responsive-custom {
        overflow-x: auto;
        width: 100%;
        border-radius: 0 0 12px 12px;
    }

    .table-responsive-custom::-webkit-scrollbar {
        height: 6px;
    }

    .table-responsive-custom::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 0 0 12px 0;
    }

    .table-responsive-custom::-webkit-scrollbar-thumb {
        background: #3498DB;
        border-radius: 3px;
    }

    .table-responsive-custom::-webkit-scrollbar-thumb:hover {
        background: #2980B9;
    }

    /* ========== ANIMACIONES ========== */
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .pedidos-delivery-llevar {
        animation: slideInUp 0.4s ease-out;
    }

    /* ========== RESPONSIVE ========== */
    @media (max-width: 1200px) {
        .table-pedidos-delivery thead th {
            padding: 11px 8px;
            font-size: 10px;
        }

        .table-pedidos-delivery tbody td {
            padding: 10px 8px;
            font-size: 11px;
        }

        .btn-cobrar-pedido {
            padding: 7px 12px;
            font-size: 9px;
        }

        .pedidos-delivery-llevar .pedidos-header {
            font-size: 12px;
            padding: 12px 16px;
        }
    }

    @media (max-width: 768px) {
        .pedidos-delivery-llevar .pedidos-header {
            flex-direction: column;
            gap: 10px;
            text-align: center;
            padding: 12px 14px;
            font-size: 12px;
        }

        .table-pedidos-delivery {
            font-size: 10px;
        }

        .table-pedidos-delivery thead th {
            padding: 8px 5px;
            font-size: 9px;
        }

        .table-pedidos-delivery tbody td {
            padding: 8px 5px;
            font-size: 10px;
        }

        .btn-cobrar-pedido {
            padding: 6px 10px;
            font-size: 8px;
        }

        .empty-pedidos-state {
            padding: 40px 15px;
        }
    }

    @media (max-width: 576px) {
        .table-pedidos-delivery {
            font-size: 9px;
        }

        .table-pedidos-delivery thead th {
            padding: 6px 4px;
            font-size: 8px;
        }

        .table-pedidos-delivery tbody td {
            padding: 6px 4px;
            font-size: 9px;
        }

        .btn-cobrar-pedido {
            padding: 5px 8px;
            font-size: 7px;
        }

        .pedidos-delivery-llevar .pedidos-header {
            font-size: 11px;
            padding: 10px 12px;
        }

        .empty-pedidos-state i {
            font-size: 36px;
        }

        .empty-pedidos-state p {
            font-size: 12px;
        }
    }
</style>

<div class="pedidos-delivery-llevar">
    <div class="pedidos-header">
        <strong><i class="fa fa-list"></i> Listado de Pedidos</strong>
        @if(!empty($pedidos))
            <div class="pedidos-count">
                <i class="fa fa-shopping-bag"></i> {{ count($pedidos) }} Pedido(s)
            </div>
        @endif
    </div>

    @if(!empty($pedidos))
        <div class="table-responsive-custom">
            <table class="table-pedidos-delivery">
                <thead>
                    <tr>
                        <th style="width: 8%;">N° PEDIDO</th>
                        <th style="width: 18%;">CLIENTE</th>
                        <th style="width: 20%;">DIRECCIÓN</th>
                        <th style="width: 12%;">TELÉFONO</th>
                        <th style="width: 22%;">REFERENCIA</th>
                        <th style="width: 20%;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pedidos as $ped)
                        <tr>
                            <td>#{{ $ped->ped_id }}</td>
                            <td>{{ $ped->ped_cli_nom }}</td>
                            <td>
                                <span class="icon-cell">
                                    <i class="fa fa-map-marker icon-location"></i>{{ $ped->ped_dir }}
                                </span>
                            </td>
                            <td>
                                <span class="icon-cell">
                                    <i class="fa fa-phone icon-phone"></i>{{ $ped->ped_tel }}
                                </span>
                            </td>
                            <td><small>{{ $ped->ped_ref }}</small></td>
                            <td>
                                <a href="/cobrarmesa/{{ $ped->ped_id }}" class="btn-cobrar-pedido" title="Cobrar este pedido">
                                    <i class="fa fa-credit-card"></i> Cobrar
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-pedidos-state">
            <i class="fa fa-inbox"></i>
            <p>No hay pedidos disponibles en este momento</p>
        </div>
    @endif
</div>