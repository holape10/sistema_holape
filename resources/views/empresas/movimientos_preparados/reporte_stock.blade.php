@extends('layouts.empresas')

@section('contenido')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Reporte de Stock de Preparados</h6>
            <a href="{{ url('movimientos_preparados') }}" class="btn btn-secondary btn-sm">Volver al Historial</a>
        </div>
        
        <div class="card-body">
            <div class="d-flex justify-content-between flex-wrap mb-4">
                <form method="GET" action="{{ url('movimientos_preparados/reporte-stock') }}" class="form-inline w-100 mb-3" id="formFiltros">
                    
                    <div class="form-group mr-3 mb-2">
                        <label for="tipo_producto_id" class="mr-2"><strong>Línea:</strong></label>
                        <select name="tipo_producto_id" id="tipo_producto_id" class="form-control">
                            <option value="">Todas las Líneas</option>
                            @foreach($tipos_producto as $tp)
                                <option value="{{ $tp->tip_pro_id }}" {{ $tipo_producto_id == $tp->tip_pro_id ? 'selected' : '' }}>
                                    {{ $tp->tip_pro_nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mr-3 mb-2">
                        <label for="categoria_id" class="mr-2"><strong>Sub Línea:</strong></label>
                        <select name="categoria_id" id="categoria_id" class="form-control">
                            <option value="">Todas las Sub Líneas</option>
                            </select>
                    </div>

                    <div class="form-group mr-3 mb-2">
                        <label for="filtro_stock" class="mr-2"><strong>Filtrar por Stock:</strong></label>
                        <select name="filtro_stock" id="filtro_stock" class="form-control" onchange="this.form.submit()">
                            <option value="todos" {{ $filtro_stock == 'todos' ? 'selected' : '' }}>Todos los productos</option>
                            <option value="con_stock" {{ $filtro_stock == 'con_stock' ? 'selected' : '' }}>Con Stock (> 0)</option>
                            <option value="sin_stock" {{ $filtro_stock == 'sin_stock' ? 'selected' : '' }}>Sin Stock (= 0)</option>
                            <option value="negativos" {{ $filtro_stock == 'negativos' ? 'selected' : '' }}>Negativos (< 0)</option>
                        </select>
                    </div>
                </form>

                <div class="w-100 text-right">
                    @php
                        $paramsExport = 'filtro_stock='.$filtro_stock.'&tipo_producto_id='.$tipo_producto_id.'&categoria_id='.$categoria_id;
                    @endphp
                    <a href="{{ url('movimientos_preparados/exportar-stock?formato=excel&'.$paramsExport) }}" class="btn btn-success btn-sm" title="Descargar Excel">
                        Excel
                    </a>
                    <a href="{{ url('movimientos_preparados/exportar-stock?formato=pdf&'.$paramsExport) }}" target="_blank" class="btn btn-danger btn-sm" title="Imprimir PDF / A4">
                        PDF
                    </a>
                    <a href="{{ url('movimientos_preparados/exportar-stock?formato=ticket&'.$paramsExport) }}" target="_blank" class="btn btn-info btn-sm text-white" title="Imprimir Ticket 80mm">
                        Ticket 80mm
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Producto</th>
                            <th>Stock Actual</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productos as $producto)
                            @php 
                                $stock = $producto->stock_preparados ?? 0; 
                                $badgeClass = 'badge-secondary';
                                $textoEstado = 'Sin Stock';

                                if($stock > 0) {
                                    $badgeClass = 'badge-success';
                                    $textoEstado = 'Disponible';
                                } elseif($stock < 0) {
                                    $badgeClass = 'badge-danger';
                                    $textoEstado = 'Negativo';
                                }
                            @endphp
                            <tr>
                                <td>{{ $producto->IdProducto }}</td>
                                <td>{{ $producto->pronom }}</td>
                                <td>
                                    <span class="badge {{ $badgeClass }}" style="font-size: 14px;">
                                        {{ $stock }}
                                    </span>
                                </td>
                                <td>{{ $textoEstado }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No se encontraron productos para este filtro.</td>
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
            
            // Limpiamos las opciones actuales
            categoriaSelect.innerHTML = '<option value="">Todas las Sub Líneas</option>';

            todasCategorias.forEach(cat => {
                // Si no hay línea seleccionada o si la categoría pertenece a la línea seleccionada
                if (!tipProId || cat.tip_pro_id == tipProId) {
                    const selected = cat.cat_id == currentCatId ? 'selected' : '';
                    categoriaSelect.innerHTML += `<option value="${cat.cat_id}" ${selected}>${cat.cat_nom}</option>`;
                }
            });
        }

        tipoProductoSelect.addEventListener('change', function() {
            // Al cambiar la línea, quitamos la categoría anterior para que no interfiera y enviamos
            categoriaSelect.value = "";
            document.getElementById('formFiltros').submit();
        });

        categoriaSelect.addEventListener('change', function() {
            document.getElementById('formFiltros').submit();
        });

        // Se ejecuta al cargar la página para pintar las opciones correctamente
        actualizarCategorias();
    });
</script>
@endsection