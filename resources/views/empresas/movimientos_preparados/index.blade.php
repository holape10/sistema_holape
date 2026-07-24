@extends('layouts.empresas')
@section('contenido')
<div class="container-fluid">
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-lg-5 mb-4"> 
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white font-weight-bold d-flex align-items-center">
                    <span>Registrar Preparación del Día</span>
                    
                    
                </div>
                <div class="card-body">
                    
                    <div class="form-group mb-3">
                        <input type="text" id="buscadorProductos" class="form-control" placeholder="🔍 Buscar plato (Ej: Chaufa)...">
                        <small class="text-muted">Ingresa números positivos para sumar stock o negativos (ej. -2) para restar.</small>
                    </div>

                    <form action="{{ url('/movimientos_preparados/ingreso-diario') }}" method="POST" id="formIngresoDiario">
                        {{ csrf_field() }} 
                        
                        <div class="table-responsive" style="max-height: 50vh; overflow-y: auto; border: 1px solid #dee2e6;">
                            <table class="table table-sm table-hover mb-0" id="tablaProductos">
                                <thead class="thead-light sticky-top" style="background: #e9ecef; z-index: 1;">
                                    <tr>
                                        <th>Producto</th>
                                        <th width="80px" class="text-center" title="Stock Actual">Stock Actual</th> 
                                        <th width="120px" class="text-center">Cant (+ / -)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($productos as $prod)
                                    <tr class="fila-producto">
                                        <td class="nombre-producto align-middle">{{ $prod->pronom }}</td>
                                        
                                        <td class="align-middle">
                                            <input type="text" 
                                                   class="form-control form-control-sm text-center font-weight-bold text-secondary bg-light" 
                                                   value="{{ rtrim(rtrim(number_format($prod->stock_preparados ?? 0, 2), '0'), '.') }}" 
                                                   readonly 
                                                   tabindex="-1" 
                                                   style="border: 1px dashed #ccc; cursor: not-allowed;">
                                        </td>

                                        <td class="align-middle">
                                            <input type="number" step="0.01" 
                                                   name="cantidades[{{ $prod->IdProducto }}]" 
                                                   class="form-control form-control-sm text-center input-cantidad" 
                                                   placeholder="0">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" id="btnGuardar" class="btn btn-success btn-block py-2 font-weight-bold">
                                <span id="txtGuardar">Guardar Movimientos</span>
                                <span id="spnGuardar" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white font-weight-bold d-flex justify-content-between align-items-center">
                    <span>Historial (Del {{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }})</span>
                    
                    <button class="btn btn-sm btn-outline-light" type="button" data-toggle="collapse" data-target="#contenedorHistorial" aria-expanded="false" aria-controls="contenedorHistorial">
                        <i class="fas fa-list"></i> Ver Historial
                    </button>

                    <a href="{{ url('/movimientos_preparados/reporte-stock') }}" class="btn btn-sm btn-light text-primary font-weight-bold ml-auto" title="Ver reporte de stock actual">
                        <i class="fas fa-clipboard-list"></i> Reportes Stock Preparados
                    </a>
                </div>

                <div class="collapse {{ (request()->has('fecha_inicio') || request()->has('fecha_fin')) ? 'show' : '' }}" id="contenedorHistorial">
                    <div class="card-body">
                        <form method="GET" action="{{ url('/movimientos_preparados') }}" class="form-inline mb-3">
                            <label class="mr-2 font-weight-bold">Desde:</label>
                            <input type="date" name="fecha_inicio" class="form-control form-control-sm mr-2 mb-2" value="{{ $fecha_inicio }}">
                            
                            <label class="mr-2 font-weight-bold">Hasta:</label>
                            <input type="date" name="fecha_fin" class="form-control form-control-sm mr-2 mb-2" value="{{ $fecha_fin }}">
                            
                            <label class="mr-2 font-weight-bold">Producto:</label>
                            <select name="producto_id" id="filtroProducto" class="form-control form-control-sm mr-2 mb-2" style="width: 200px;">
                                <option value="">Todos</option>
                                @foreach($productos as $prod)
                                    <option value="{{ $prod->IdProducto }}" {{ (isset($producto_id) && $producto_id == $prod->IdProducto) ? 'selected' : '' }}>
                                        {{ $prod->pronom }}
                                    </option>
                                @endforeach
                            </select>
                            
                            <button type="submit" class="btn btn-secondary btn-sm mb-2 mr-3">
                                <i class="fas fa-search"></i> Filtrar
                            </button>

                            <div class="btn-group mb-2">
                                <button type="button" class="btn btn-success btn-sm" onclick="exportar('excel')">
                                    <i class="fas fa-file-excel"></i> Excel
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="exportar('ticket')">
                                    <i class="fas fa-file-pdf"></i> Ticket / PDF
                                </button>
                            </div>
                        </form>

                        <script>
                            function exportar(formato) {
                                let fecha_inicio = document.querySelector('input[name="fecha_inicio"]').value;
                                let fecha_fin = document.querySelector('input[name="fecha_fin"]').value;
                                let producto = document.querySelector('select[name="producto_id"]').value;
                                
                                let url = `{{ url('/movimientos_preparados/exportar') }}?fecha_inicio=${fecha_inicio}&fecha_fin=${fecha_fin}&producto_id=${producto}&formato=${formato}`;
                                window.open(url, '_blank');
                            }
                        </script>

                        <div class="table-responsive" style="max-height: 50vh; overflow-y: auto; border: 1px solid #dee2e6;">
                            <table class="table table-bordered table-striped table-sm text-center mb-0">
                                <thead class="thead-light sticky-top" style="background: #e9ecef; z-index: 1;">
                                    <tr>
                                        <th>Fecha y Hora</th>
                                        <th>Usuario</th>
                                        <th class="text-left">Producto</th>
                                        <th>Movimiento</th>
                                        <th>Cant.</th>
                                        <th>Stock Result.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($movimientos as $mov)
                                    <tr>
                                        <td class="align-middle">{{ \Carbon\Carbon::parse($mov->created_at)->format('d/m/Y H:i') }}</td>
                                        <td class="align-middle text-info font-weight-bold">
                                            <small><i class="fas fa-user mr-1"></i> {{ $mov->nombre_usuario ?? 'Sistema' }}</small>
                                        </td>
                                        <td class="text-left align-middle">{{ $mov->pronom }}</td>
                                        <td class="align-middle">
                                            @if($mov->tipo_movimiento == 'preparacion_diaria')
                                                <span class="badge badge-success px-2 py-1">Ingreso <small>(Preparación)</small></span>
                                            @elseif($mov->tipo_movimiento == 'correccion_resta')
                                                <span class="badge badge-warning px-2 py-1">Ajuste <small>(Resta)</small></span>
                                            @elseif(in_array($mov->tipo_movimiento, ['devolucion_comanda', 'anulacion_pedido', 'anulacion_item']))
                                                <span class="badge badge-success px-2 py-1">Ingreso <small>(Anulación)</small></span>
                                            @elseif($mov->tipo_movimiento == 'venta_comanda')
                                                <span class="badge badge-danger px-2 py-1">Salida <small>(Venta)</small></span>
                                            @else
                                                <span class="badge badge-secondary px-2 py-1">{{ ucfirst(str_replace('_', ' ', $mov->tipo_movimiento)) }}</span>
                                            @endif
                                        </td>
                                        <td class="font-weight-bold align-middle 
                                            {{ $mov->cantidad < 0 ? 'text-danger' : 'text-success' }}">
                                            {{ $mov->cantidad > 0 ? '+' : '' }}{{ number_format($mov->cantidad, 2) }}
                                        </td>
                                        <td class="align-middle">{{ number_format($mov->stock_resultante, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No hay registros para este día.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    /* Pequeño ajuste para que Select2 se vea igual que Bootstrap */
    .select2-container .select2-selection--single {
        height: calc(1.5em + .5rem + 2px);
        border: 1px solid #ced4da;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 1.5;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: calc(1.5em + .5rem + 2px);
    }
</style>

<script>
    // Inicializamos el buscador en el select usando jQuery (que ya viene con Bootstrap)
    $(document).ready(function() {
        $('#filtroProducto').select2({
            placeholder: "Escribe para buscar...",
            allowClear: true,
            language: {
                noResults: function() {
                    return "No se encontró el producto";
                }
            }
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- 1. PROTECCIÓN CONTRA LATENCIA (DOBLE CLIC) CORREGIDA ---
        const form = document.getElementById('formIngresoDiario');
        form.addEventListener('submit', function(e) {
            const btn = document.getElementById('btnGuardar');
            
            if (btn.disabled) {
                e.preventDefault(); 
                return;
            }
            
            // Le damos 10 milisegundos de ventaja al navegador para que envíe el form
            // antes de bloquear el botón visualmente.
            setTimeout(() => {
                btn.disabled = true;
                document.getElementById('txtGuardar').innerText = 'Procesando...';
                document.getElementById('spnGuardar').classList.remove('d-none');
            }, 10);
        });

        // --- 2. DEBOUNCE PARA EL BUSCADOR (RENDIMIENTO) ---
        const buscador = document.getElementById('buscadorProductos');
        const filas = document.querySelectorAll('.fila-producto');
        let timeoutBuscador = null;

        buscador.addEventListener('input', function(e) {
            clearTimeout(timeoutBuscador);
            
            // Retrasamos la búsqueda 300ms para no saturar navegadores en equipos lentos
            timeoutBuscador = setTimeout(() => {
                const textoBusqueda = e.target.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, ""); 

                filas.forEach(function(fila) {
                    const nombreProducto = fila.querySelector('.nombre-producto').textContent.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                    const inputCantidad = fila.querySelector('.input-cantidad').value;
                    
                    // Si coincide con la búsqueda o si ya tiene un valor digitado (positivo o negativo), lo mostramos
                    if (nombreProducto.includes(textoBusqueda) || (inputCantidad !== '' && parseFloat(inputCantidad) !== 0)) {
                        fila.style.display = '';
                    } else {
                        fila.style.display = 'none';
                    }
                });
            }, 300);
        });
    });
</script>
@endsection