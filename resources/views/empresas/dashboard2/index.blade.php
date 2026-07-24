<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard General Moderno - {{ config('app.name', 'Laravel') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f4f6f9; /* Un gris muy claro para el fondo */
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background-color: #343a40; /* Gris oscuro para el sidebar */
            color: #ffffff;
            padding: 20px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            position: fixed; /* Fijo para que no se desplace con el scroll */
            height: 100%; /* Ocupa toda la altura */
            overflow-y: auto; /* Permite scroll si hay muchos ítems */
        }
        .sidebar.toggled {
            margin-left: -250px; /* Oculta el sidebar */
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.75);
            padding: 12px 15px;
            margin-bottom: 5px;
            border-radius: 8px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: #007bff; /* Azul primario */
            color: #ffffff;
            font-weight: bold;
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        .main-content {
            flex-grow: 1;
            margin-left: 250px; /* Espacio para el sidebar */
            transition: all 0.3s ease;
            padding: 0; /* Reiniciar padding, el contenido lo tendrá */
        }
        .main-content.toggled {
            margin-left: 0;
        }
        .navbar {
            background-color: #ffffff;
            border-bottom: 1px solid #e9ecef;
            box-shadow: 0 2px 5px rgba(0,0,0,.05);
            padding: 15px 20px;
        }
        .content-area {
            padding: 30px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,.08);
            margin-bottom: 30px;
            overflow: hidden; /* Para que el gráfico no se salga */
        }
        .card-header {
            background-color: #ffffff; /* Fondo blanco */
            border-bottom: 1px solid #f0f2f5; /* Línea divisoria suave */
            padding: 20px 25px;
            font-weight: bold;
            color: #343a40;
            font-size: 1.1em;
        }
        .card-body {
            padding: 25px;
        }
        .metric-card .card-body {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 30px;
        }
        .metric-card .icon {
            font-size: 3.5rem;
            color: #007bff; /* Icono azul */
            opacity: 0.7;
        }
        .metric-card .value {
            font-size: 3rem;
            font-weight: bold;
            color: #212529; /* Texto oscuro */
        }
        .metric-card .label {
            font-size: 1.1rem;
            color: #6c757d;
            margin-top: -5px;
        }
        .table-responsive {
            margin-top: 20px;
        }
        .table thead th {
            background-color: #e9ecef;
            color: #495057;
            border-top: none;
            font-weight: 600;
        }
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        .table td, .table th {
            padding: 12px 18px;
        }

        /* Estilos para el estado de las tablas */
        .badge.bg-success-subtle { background-color: #d1e7dd; color: #0f5132; }
        .badge.bg-warning-subtle { background-color: #fff3cd; color: #664d03; }
        .badge.bg-danger-subtle { background-color: #f8d7da; color: #842029; }
        .badge.bg-info-subtle { background-color: #cff4fc; color: #055160; }


        /* Media Queries para responsividad */
        @media (max-width: 992px) {
            .sidebar {
                position: relative; /* Quitar fijo para móviles */
                width: 100%;
                min-height: auto;
                padding-top: 0;
            }
            .sidebar.toggled {
                margin-left: 0; /* No se oculta sino que se expande */
            }
            .main-content {
                margin-left: 0;
            }
            .sidebar .nav-link {
                padding: 10px 15px;
            }
        }
        @media (max-width: 768px) {
            .metric-card .value {
                font-size: 2.2rem;
            }
            .metric-card .icon {
                font-size: 2.8rem;
            }
            h2 {
                font-size: 1.8rem;
            }
            .content-area {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <div class="sidebar d-flex flex-column p-3" id="sidebar">
            <h3 class="text-white text-center mb-4 border-bottom border-secondary pb-3">
                <img src="{{ asset('logo_devsoft_engranaje.png') }}" alt="Logo" style="height: 40px; margin-right: 10px;">
                DEVSOFT
            </h3>
            <ul class="nav nav-pills flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('dashboard2.index') }}">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-users"></i> Clientes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-boxes"></i> Productos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-receipt"></i> Ventas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-clipboard-list"></i> Pedidos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-warehouse"></i> Almacén
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-cogs"></i> Configuración
                    </a>
                </li>
                {{-- Puedes añadir más enlaces aquí según los módulos que tengas --}}
                <li class="nav-item mt-auto">
                    <a class="nav-link" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </div>

        <div id="page-content-wrapper" class="flex-grow-1 main-content">
            <nav class="navbar navbar-expand-lg">
                <div class="container-fluid">
                    <button class="btn btn-outline-secondary d-lg-none" id="sidebarToggleMobile">
                        <i class="fas fa-bars"></i>
                    </button>
                    <span class="navbar-brand ms-3 d-none d-lg-block">Panel de Control General</span>
                    <div class="ms-auto">
                        <ul class="navbar-nav">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUser" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user-circle me-1"></i> {{ Auth::user()->name }}
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownUser">
                                    <li><a class="dropdown-item" href="#">Mi Perfil</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();">Cerrar Sesión</a></li>
                                </ul>
                            </li>
                        </ul>
                        <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </div>
            </nav>

            <div class="container-fluid content-area">
                <h2 class="mb-4 text-secondary">Resumen del Negocio</h2>

                <div class="card mb-4">
                    <div class="card-header">
                        Información General (Mes Actual)
                    </div>
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label for="establishment" class="form-label">ESTABLECIMIENTO</label>
                                <select class="form-select" id="establishment" name="establishment" disabled>
                                    <option value="{{ $id_empresa_negocio ?? '' }}">{{ $nombre_negocio ?? 'Cargando...' }}</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <p class="form-control-plaintext text-muted">Datos mostrados para el mes actual.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                        <div class="card metric-card bg-white">
                            <div class="card-body">
                                <div>
                                    <div class="value">S/. <span id="total-notas-ventas">0.00</span></div>
                                    <div class="label">Total Notas Ventas</div>
                                </div>
                                <div class="icon text-primary">
                                    <i class="fas fa-file-invoice"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                        <div class="card metric-card bg-white">
                            <div class="card-body">
                                <div>
                                    <div class="value">S/. <span id="total-facturas">0.00</span></div>
                                    <div class="label">Total Facturas</div>
                                </div>
                                <div class="icon text-info">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                        <div class="card metric-card bg-white">
                            <div class="card-body">
                                <div>
                                    <div class="value">S/. <span id="total-boletas">0.00</span></div>
                                    <div class="label">Total Boletas</div>
                                </div>
                                <div class="icon text-success">
                                    <i class="fas fa-receipt"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                     <div class="col-xl-3 col-md-6 col-sm-6 mb-4">
                        <div class="card metric-card bg-white">
                            <div class="card-body">
                                <div>
                                    <div class="value">S/. <span id="total-sales-month">0.00</span></div>
                                    <div class="label">Total Ventas (Mes)</div>
                                </div>
                                <div class="icon text-primary">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 col-sm-6 mb-4">
                        <div class="card metric-card bg-white">
                            <div class="card-body">
                                <div>
                                    <div class="value">S/. <span id="total-utility">0.00</span></div> {{-- Placeholder --}}
                                    <div class="label">Total Utilidad</div>
                                </div>
                                <div class="icon text-success">
                                    <i class="fas fa-hand-holding-usd"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card metric-card bg-white">
                            <div class="card-body">
                                <div>
                                    <div class="value"><span id="active-orders">0</span></div>
                                    <div class="label">Pedidos Activos</div>
                                </div>
                                <div class="icon text-warning">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card metric-card bg-white">
                            <div class="card-body">
                                <div>
                                    <div class="value"><span id="occupied-tables">0</span></div>
                                    <div class="label">Mesas Ocupadas</div>
                                </div>
                                <div class="icon text-info">
                                    <i class="fas fa-chair"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card metric-card bg-white">
                            <div class="card-body">
                                <div>
                                    <div class="value"><span id="free-tables">0</span></div>
                                    <div class="label">Mesas Libres</div>
                                </div>
                                <div class="icon text-secondary">
                                    <i class="fas fa-chair"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                Tendencia de Ventas (Últimos 6 Meses)
                            </div>
                            <div class="card-body">
                                <canvas id="salesChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                Productos con Stock Bajo
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush" id="low-stock-list">
                                    <li class="list-group-item text-muted">Cargando productos...</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                Top 5 Clientes Más Frecuentes (Mes Actual)
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>Cliente</th>
                                                <th>Doc.</th>
                                                <th>Pedidos</th>
                                                <th>Gasto Total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="top-customers-table-body">
                                            <tr><td colspan="4" class="text-center text-muted">Cargando clientes...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    Top 5 Productos Más Vendidos (Mes Actual)
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Producto</th>
                                                    <th>Cantidad Vendida</th>
                                                    <th>Valor Vendido</th>
                                                </tr>
                                            </thead>
                                            <tbody id="top-selling-products-table-body">
                                                <tr><td colspan="3" class="text-center text-muted">Cargando productos...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    Últimos Pedidos (Mes Actual)
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th>ID Pedido</th>
                                                    <th>Cliente / Destino</th>
                                                    <th>Tipo</th>
                                                    <th>Mesa</th>
                                                    <th>Total</th>
                                                    <th>Estado</th>
                                                    <th>Fecha/Hora</th>
                                                </tr>
                                            </thead>
                                            <tbody id="latest-orders-table-body">
                                                <tr><td colspan="7" class="text-center text-muted">Cargando pedidos...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Variable global para almacenar instancias de gráficos para poder destruirlos y recrearlos
            let salesChartInstance = null;

            // Envuelve todo el código en window.onload para asegurar que el DOM esté completamente cargado
            window.onload = function() {
                // Función para alternar el sidebar en móviles
                document.getElementById('sidebarToggleMobile').addEventListener('click', function() {
                    document.getElementById('sidebar').classList.toggle('toggled');
                    document.getElementById('page-content-wrapper').classList.toggle('toggled');
                });

                // Función para cargar los KPIs
                function loadKpiData() {
                    fetch('{{ route('dashboard2.kpis') }}') // Sin parámetros de fecha
                        .then(response => {
                            if (!response.ok) {
                                // Intenta leer el cuerpo de la respuesta para obtener más detalles del error.
                                return response.text().then(text => {
                                    console.error('Respuesta del servidor no OK (KPIs):', text);
                                    throw new Error('Error de red o el servidor devolvió un error inesperado al cargar KPIs. Revisa la consola para más detalles o inicia sesión de nuevo.');
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            // Asegúrate de que los IDs existan antes de intentar establecer innerText
                            const totalSalesMonthElement = document.getElementById('total-sales-month');
                            if (totalSalesMonthElement) {
                                totalSalesMonthElement.innerText = data.totalSalesMonth;
                            }

                            const totalFacturasElement = document.getElementById('total-facturas');
                            if (totalFacturasElement) {
                                totalFacturasElement.innerText = data.totalFacturas;
                            }

                            const totalBoletasElement = document.getElementById('total-boletas');
                            if (totalBoletasElement) {
                                totalBoletasElement.innerText = data.totalBoletas;
                            }

                            const totalNotasVentaElement = document.getElementById('total-notas-ventas');
                            if (totalNotasVentaElement) {
                                totalNotasVentaElement.innerText = data.totalNotasVenta;
                            }
                            // Como 'total-sales-period' usa el mismo valor que 'total-sales-month',
                            // asignamos directamente si existe
                            const totalSalesPeriodElement = document.getElementById('total-sales-period');
                            if (totalSalesPeriodElement) {
                                totalSalesPeriodElement.innerText = data.totalSalesMonth;
                            }

                            // Otros KPIs que no dependen del filtro de fecha
                            const activeOrdersElement = document.getElementById('active-orders');
                            if (activeOrdersElement) {
                                activeOrdersElement.innerText = data.activeOrders;
                            }

                            const lowStockCountElement = document.getElementById('low-stock-count');
                            if (lowStockCountElement) {
                                lowStockCountElement.innerText = data.lowStockCount;
                            }

                            const occupiedTablesElement = document.getElementById('occupied-tables');
                            if (occupiedTablesElement) {
                                occupiedTablesElement.innerText = data.occupiedTables;
                            }

                            const freeTablesElement = document.getElementById('free-tables');
                            if (freeTablesElement) {
                                freeTablesElement.innerText = data.freeTables;
                            }
                            // document.getElementById('total-utility').innerText = data.totalUtility; // Si lo calculas

                        })
                        .catch(error => {
                            console.error('Error al cargar KPIs:', error);
                            // Solo muestra la alerta si no es un error de "null" persistente de IDs no encontrados
                            if (!error.message.includes("Cannot set properties of null")) {
                                alert('Error al cargar los datos del resumen. Por favor, revisa la consola para más detalles o inicia sesión de nuevo.');
                            }
                        });
                }

                // Función para cargar el gráfico de ventas
                function loadSalesChart() {
                    fetch('{{ route('dashboard2.sales.chart') }}') // Sin parámetros de fecha
                        .then(response => {
                            if (!response.ok) {
                                return response.text().then(text => { throw new Error(text); });
                            }
                            return response.json();
                        })
                        .then(data => {
                            const ctx = document.getElementById('salesChart').getContext('2d');

                            if (salesChartInstance) {
                                salesChartInstance.destroy();
                            }

                            salesChartInstance = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: data.labels,
                                    datasets: [{
                                        label: 'Ventas (S/.)',
                                        data: data.data,
                                        borderColor: '#007bff',
                                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                                        fill: true,
                                        tension: 0.4
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    plugins: {
                                        legend: {
                                            display: false
                                        }
                                    },
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            title: {
                                                display: true,
                                                text: 'Monto (S/.)'
                                            }
                                        },
                                        x: {
                                            title: {
                                                display: true,
                                                text: 'Mes'
                                            }
                                        }
                                    }
                                }
                            });
                        })
                        .catch(error => console.error('Error al cargar datos del gráfico de ventas:', error));
                }

                // Función para cargar alertas de stock
                function loadStockAlerts() {
                    fetch('{{ route('dashboard2.stock.alerts') }}') // Sin parámetros de fecha
                        .then(response => {
                            if (!response.ok) {
                                return response.text().then(text => { throw new Error(text); });
                            }
                            return response.json();
                        })
                        .then(data => {
                            const list = document.getElementById('low-stock-list');
                            list.innerHTML = '';
                            if (data.products.length > 0) {
                                data.products.forEach(product => {
                                    const item = `<li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>${product.pronom}</strong> <br>
                                            <small class="text-muted">Almacén: ${product.almacen_nombre}</small>
                                        </div>
                                        <span class="badge bg-danger-subtle rounded-pill">Stock: ${product.stock} / Min: ${product.stock_min}</span>
                                    </li>`;
                                    list.innerHTML += item;
                                });
                            } else {
                                list.innerHTML = '<li class="list-group-item text-muted">No hay alertas de stock bajo.</li>';
                            }
                        })
                        .catch(error => console.error('Error al cargar alertas de stock:', error));
                }

                // Función para cargar los últimos pedidos
                function loadLatestOrders() {
                    fetch('{{ route('dashboard2.latest.orders') }}') // Sin parámetros de fecha
                        .then(response => {
                            if (!response.ok) {
                                return response.text().then(text => { throw new Error(text); });
                            }
                            return response.json();
                        })
                        .then(data => {
                            const tableBody = document.getElementById('latest-orders-table-body');
                            tableBody.innerHTML = '';
                            if (data.orders.length > 0) {
                                data.orders.forEach(order => {
                                    let statusClass = '';
                                    if (order.ped_est === 'Cerrado') {
                                        statusClass = 'bg-success-subtle';
                                    } else if (order.ped_est === 'Aperturado') {
                                        statusClass = 'bg-warning-subtle';
                                    } else if (order.ped_est === 'Anulado' || order.ped_est === 'Eliminado') {
                                        statusClass = 'bg-danger-subtle';
                                    } else {
                                        statusClass = 'bg-info-subtle';
                                    }

                                    const row = `
                                        <tr>
                                            <td>${order.ped_id}</td>
                                            <td>${order.ped_cli_nom || 'N/A'}</td>
                                            <td>${order.ped_tip}</td>
                                            <td>${order.mes_nom || 'Para Llevar'}</td>
                                            <td>S/. ${parseFloat(order.ped_tot).toFixed(2)}</td>
                                            <td><span class="badge ${statusClass}">${order.ped_est}</span></td>
                                            <td>${new Date(order.fecha_hora).toLocaleString()}</td>
                                        </tr>
                                    `;
                                    tableBody.innerHTML += row;
                                });
                            } else {
                                tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No hay pedidos recientes.</td></tr>';
                            }
                        })
                        .catch(error => console.error('Error al cargar últimos pedidos:', error));
                }

                // Función para cargar productos más vendidos
                function loadTopSellingProducts() {
                    fetch('{{ route('dashboard2.top.selling.products') }}') // Sin parámetros de fecha
                        .then(response => {
                            if (!response.ok) {
                                return response.text().then(text => { throw new Error(text); });
                            }
                            return response.json();
                        })
                        .then(data => {
                            const tableBody = document.getElementById('top-selling-products-table-body');
                            tableBody.innerHTML = '';
                            if (data.products.length > 0) {
                                data.products.forEach(product => {
                                    const row = `
                                        <tr>
                                            <td>${product.pronom}</td>
                                            <td>${product.total_cantidad_vendida}</td>
                                            <td>S/. ${parseFloat(product.total_valor_vendido).toFixed(2)}</td>
                                        </tr>
                                    `;
                                    tableBody.innerHTML += row;
                                });
                            } else {
                                tableBody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">No hay productos vendidos en el mes actual.</td></tr>';
                            }
                        })
                        .catch(error => console.error('Error al cargar productos más vendidos:', error));
                }

                // Función para cargar clientes más frecuentes
                function loadTopCustomers() {
                    fetch('{{ route('dashboard2.top.customers') }}') // Sin parámetros de fecha
                        .then(response => {
                            if (!response.ok) {
                                return response.text().then(text => { throw new Error(text); });
                            }
                            return response.json();
                        })
                        .then(data => {
                            const tableBody = document.getElementById('top-customers-table-body');
                            tableBody.innerHTML = '';
                            if (data.customers.length > 0) {
                                data.customers.forEach(customer => {
                                    const row = `
                                        <tr>
                                            <td>${customer.clinom}</td>
                                            <td>${customer.clinum}</td>
                                            <td>${customer.total_pedidos}</td>
                                            <td>S/. ${parseFloat(customer.total_gasto).toFixed(2)}</td>
                                        </tr>
                                    `;
                                    tableBody.innerHTML += row;
                                });
                            } else {
                                tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No hay clientes frecuentes en el mes actual.</td></tr>';
                            }
                        })
                        .catch(error => console.error('Error al cargar clientes frecuentes:', error));
                }

                // Función principal para cargar todos los datos
                function loadAllDashboardData() {
                    loadKpiData();
                    loadSalesChart();
                    loadStockAlerts();
                    loadLatestOrders();
                    loadTopSellingProducts();
                    loadTopCustomers();
                }

                // Cargar todos los datos al cargar la página por primera vez
                loadAllDashboardData();
            }; // Cierre de window.onload
        </script>
    </body>
    </html>