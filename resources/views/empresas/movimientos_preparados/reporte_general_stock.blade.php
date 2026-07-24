@extends('layouts.empresas')

@section('contenido')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Reporte General de Stock (Insumos, Productos y Preparados)</h6>
            <a href="{{ url('movimientos_preparados') }}" class="btn btn-secondary btn-sm">Volver al Historial</a>
        </div>
        
        <div class="card-body">
            <form method="GET" action="{{ url('reportes_stock_general') }}" id="formFiltros" class="mb-4">
                <div class="row align-items-end">
                    <div class="col-md-2 col-sm-6 form-group">
                        <label for="fecha_inicio"><strong>Fecha Inicio:</strong></label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control form-control-sm" value="{{ $fecha_inicio }}">
                    </div>
                    <div class="col-md-2 col-sm-6 form-group">
                        <label for="fecha_fin"><strong>Fecha Fin:</strong></label>
                        <input type="date" name="fecha_fin" id="fecha_fin" class="form-control form-control-sm" value="{{ $fecha_fin }}">
                    </div>

                    <div class="col-md-2 col-sm-6 form-group">
                        <label for="tipo_item"><strong>Tipo Item:</strong></label>
                        <select name="tipo_item" id="tipo_item" class="form-control form-control-sm">
                            <option value="todos" {{ $tipo_item == 'todos' ? 'selected' : '' }}>[ TODOS ]</option>
                            <option value="insumos" {{ $tipo_item == 'insumos' ? 'selected' : '' }}>INSUMOS</option>
                            <option value="productos" {{ $tipo_item == 'productos' ? 'selected' : '' }}>PRODUCTOS</option>
                            <option value="preparados" {{ $tipo_item == 'preparados' ? 'selected' : '' }}>PREPARADOS</option>
                        </select>
                    </div>

                    <div class="col-md-2 col-sm-6 form-group">
                        <label for="tipo_producto_id"><strong>Línea:</strong></label>
                        <select name="tipo_producto_id" id="tipo_producto_id" class="form-control form-control-sm">
                            <option value="">Todas las Líneas</option>
                            @foreach($tipos_producto as $tp)
                                <option value="{{ $tp->tip_pro_id }}" {{ $tipo_producto_id == $tp->tip_pro_id ? 'selected' : '' }}>{{ $tp->tip_pro_nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 col-sm-6 form-group">
                        <label for="categoria_id"><strong>Sub Línea:</strong></label>
                        <select name="categoria_id" id="categoria_id" class="form-control form-control-sm">
                            <option value="">Todas las Sub Líneas</option>
                        </select>
                    </div>

                    <div class="col-md-2 col-sm-6 form-group">
                        <label for="filtro_stock"><strong>Stock Actual:</strong></label>
                        <select name="filtro_stock" id="filtro_stock" class="form-control form-control-sm">
                            <option value="todos" {{ $filtro_stock == 'todos' ? 'selected' : '' }}>Todos</option>
                            <option value="con_stock" {{ $filtro_stock == 'con_stock' ? 'selected' : '' }}>Con Stock (> 0)</option>
                            <option value="sin_stock" {{ $filtro_stock == 'sin_stock' ? 'selected' : '' }}>Sin Stock (= 0)</option>
                            <option value="negativos" {{ $filtro_stock == 'negativos' ? 'selected' : '' }}>Negativos (< 0)</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-2">
                    <button type="submit" class="btn btn-primary btn-sm px-4"><i class="fas fa-search"></i> Filtrar Tabla</button>
                    
                    <div>
                        @php
                            $urlParams = "formato=@formato&fecha_inicio=$fecha_inicio&fecha_fin=$fecha_fin&tipo_item=$tipo_item&tipo_producto_id=$tipo_producto_id&categoria_id=$categoria_id&filtro_stock=$filtro_stock";
                        @endphp
                        <a href="{{ url('movimientos_preparados/exportar-general-stock?'.str_replace('@formato', 'excel', $urlParams)) }}" class="btn btn-success btn-sm"><i class="fas fa-file-excel"></i> Excel</a>
                        <a href="{{ url('movimientos_preparados/exportar-general-stock?'.str_replace('@formato', 'pdf', $urlParams)) }}" target="_blank" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf"></i> PDF</a>
                        <a href="{{ url('movimientos_preparados/exportar-general-stock?'.str_replace('@formato', 'ticket', $urlParams)) }}" target="_blank" class="btn btn-info btn-sm text-white"><i class="fas fa-print"></i> Ticket 80mm</a>
                        <a href="{{ url('movimientos_preparados/exportar-general-stock?'.str_replace('@formato', 'blanco', $urlParams)) }}" target="_blank" class="btn btn-dark btn-sm"><i class="fas fa-edit"></i> Formato Control</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover sm-text" style="font-size: 13px;">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Línea</th>
                            <th>Sub Línea</th>
                            <th>Producto / Insumo</th>
                            <th class="text-center">Stock Inicial</th>
                            <th class="text-center">Stock Actual</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            @php
                                $badgeClass = $item->stock_actual > 0 ? 'badge-success' : ($item->stock_actual < 0 ? 'badge-danger' : 'badge-secondary');
                                $textoEstado = $item->stock_actual > 0 ? 'Disponible' : ($item->stock_actual < 0 ? 'Negativo' : 'Sin Stock');
                            @endphp
                            <tr>
                                <td>{{ $item->IdProducto }}</td>
                                <td><span class="text-muted">{{ $item->tip_pro_nom ?? 'N/A' }}</span></td>
                                <td><span class="text-muted">{{ $item->cat_nom ?? 'N/A' }}</span></td>
                                <td><strong>[{{ strtoupper($item->tipo_origen) }}]</strong> {{ $item->pronom }}</td>
                                <td class="text-center text-info font-weight-bold">{{ number_format($item->stock_inicial, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $badgeClass }}" style="font-size: 13px; width: 60px;">
                                        {{ number_format($item->stock_actual, 2) }}
                                    </span>
                                </td>
                                <td>{{ $textoEstado }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No se encontraron registros con los filtros seleccionados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tipoProductoSelect = document.getElementById('tipo_producto_id');
        const categoriaSelect = document.getElementById('categoria_id');
        const todasCategorias = @json($categorias);

        function actualizarCategorias() {
            const tipProId = tipoProductoSelect.value;
            const currentCatId = "{{ $categoria_id }}";
            
            categoriaSelect.innerHTML = '<option value="">Todas las Sub Líneas</option>';

            todasCategorias.forEach(cat => {
                if (!tipProId || cat.tip_pro_id == tipProId) {
                    const selected = cat.cat_id == currentCatId ? 'selected' : '';
                    categoriaSelect.innerHTML += `<option value="${cat.cat_id}" ${selected}>${cat.cat_nom}</option>`;
                }
            });
        }

        tipoProductoSelect.addEventListener('change', function() {
            categoriaSelect.value = "";
            actualizarCategorias();
        });

        // Cargar al inicio
        actualizarCategorias();
    });
</script>
@endsection