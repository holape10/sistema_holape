@extends('layouts.empresas')

@section('contenido')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="container text-dark">
    <h2 class="mt-4">Gestión de Cuenta Activa - {{ $brazalete->numero_casillero }}</h2>
    <hr>

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

    <div class="row">
        <div class="col-md-4">
            <div class="card border-danger mb-3">
                <div class="card-header bg-danger text-white">Estado de Estadía</div>
                <div class="card-body">
                    <h5 class="card-title mb-1">{{ $visitaActiva->clinom }}</h5>
                    <p><strong>Ingreso:</strong> {{ date('h:i A', strtotime($visitaActiva->fecha_ingreso)) }}</p>
                    <p><strong>Tiempo Dentro:</strong> <span class="badge badge-warning p-2">{{ $tiempoTranscurrido }}</span></p>
                </div>
            </div>
            <a href="{{ route('sauna.recepcion') }}" class="btn btn-secondary btn-block mb-2">← Volver a Recepción</a>
        </div>

        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header bg-dark text-white">Cargar Productos al Pedido (Barra)</div>
                <div class="card-body">
                    
                    <form action="{{ route('sauna.agregar-consumo') }}" method="POST" class="mb-4">
                        @csrf
                        <input type="hidden" name="visita_sauna_id" value="{{ $visitaActiva->id }}">
                        <input type="hidden" name="cantidad" value="1">
                        
                        <label><strong>1. Carga Rápida (Pistola de código)</strong></label>
                        <div class="input-group">
                            <input type="text" name="codigo_barra" class="form-control" placeholder="Pase el lector..." autofocus autocomplete="off">
                            <div class="input-group-append"><button type="submit" class="btn btn-info">Escanear</button></div>
                        </div>
                    </form>

                    <hr>

                    <form action="{{ route('sauna.agregar-consumo') }}" method="POST" class="row">
                        @csrf
                        <input type="hidden" name="visita_sauna_id" value="{{ $visitaActiva->id }}">

                        <div class="col-md-12 mb-2"><label><strong>2. Carga Manual (Escriba para buscar)</strong></label></div>
                        
                        <div class="form-group col-md-7">
                            <select name="producto_id" class="form-control select2-buscador" style="width: 100%;">
                                <option value="">-- Escriba el nombre del producto --</option>
                                @foreach($productosVenta as $p)
                                    <option value="{{ $p->IdProducto }}">{{ $p->pronom }} (S/. {{ number_format($p->propun, 2) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-2">
                            <input type="number" name="cantidad" class="form-control" value="1" min="1">
                        </div>

                        <div class="form-group col-md-3">
                            <button type="submit" class="btn btn-success btn-block">Cargar</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light"><strong>Consumos Registrados</strong></div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Cant.</th>
                                <th class="text-right">Precio Un.</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $granTotal = 0; @endphp
                            @foreach($consumosActuales as $c)
                                @php 
                                    $subtotal = $c->cantidad * $c->precio;
                                    $granTotal += $subtotal; 
                                @endphp
                                <tr>
                                    <td>{{ $c->nombre }}</td>
                                    <td class="text-center">{{ $c->cantidad }}</td>
                                    <td class="text-right">S/. {{ number_format($c->precio, 2) }}</td>
                                    <td class="text-right font-weight-bold">S/. {{ number_format($subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                            <tr class="bg-light">
                                <td colspan="3" class="text-right font-weight-bold">Subtotal Consumos:</td>
                                <td class="text-right font-weight-bold text-danger">S/. {{ number_format($granTotal, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Esto convierte tu lista larga en un buscador inteligente
$(document).ready(function() {
    $('.select2-buscador').select2({
        placeholder: "Seleccione o escriba un producto...",
        allowClear: true
    });
});
</script>
@endsection