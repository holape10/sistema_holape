@extends('layouts.empresas')

@section('contenido')
<div class="container-fluid" style="padding-top: 15px;">
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-ban"></i> Error!</h4>
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('puntoventa.procesarmasiva') }}" method="POST">
        @csrf

        <div class="box box-success border-success">
            <div class="box-header with-border">
                <h3 class="box-title" style="font-weight: bold;"><i class="fas fa-cog"></i> Datos de Emisión</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Fecha de Emisión:</label>
                            <input type="date" name="fecha_emision" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="form-group">
                            <label>Descripción del Detalle en el Comprobante:</label>
                            <input type="text" name="descripcion_general" class="form-control" value="SISTEMA DE FACTURACION ELECTRONICA" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="info-box bg-green">
                    <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text" style="font-size: 16px;">Total a Emitir (Soles)</span>
                        <span class="info-box-number" style="font-size: 32px;">S/ <span id="monto_total_vista">0.00</span></span>
                        <span class="progress-description">
                            Se recalcula automáticamente al seleccionar clientes
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-sm-6 col-xs-12" style="display: flex; align-items: center; justify-content: flex-end; height: 90px;">
                <button type="submit" class="btn btn-success btn-lg shadow" style="font-size: 18px; font-weight: bold; padding: 15px 25px; border-radius: 8px;" onclick="this.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Procesando... espere'; this.disabled=true; this.form.submit();">
                    <i class="fas fa-file-archive"></i> Emitir Comprobantes y Descargar ZIP
                </button>
            </div>
        </div>

        <!-- SECCIÓN DE TABLA Y BUSCADOR -->
        <div class="box box-primary border-primary">
            <div class="box-header with-border" style="background-color: #3c8dbc; color: white;">
                <h3 class="box-title" style="font-weight: bold;"><i class="fas fa-users"></i> Lista de Clientes Mensuales</h3>
            </div>
            
            <div class="box-body">
                <!-- BUSCADOR -->
                <div class="row mb-3" style="margin-bottom: 15px;">
                    <div class="col-md-6">
                        <div class="input-group input-group-lg">
                            <span class="input-group-addon bg-gray"><i class="fas fa-search"></i></span>
                            <input type="text" id="buscadorClientes" class="form-control" placeholder="Buscar por nombre, DNI o dirección...">
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="tablaClientes">
                        <thead class="bg-gray">
                            <tr>
                                <th width="5%" class="text-center" style="vertical-align: middle;">
                                    <input type="checkbox" id="checkAll" checked title="Marcar/Desmarcar visibles" style="transform: scale(1.5);">
                                </th>
                                <th>Cliente</th>
                                <th>DNI / RUC</th>
                                <th>Comprobante</th>
                                <th>Dirección</th>
                                <th class="text-right">Monto (S/)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($clientes as $cli)
                            <tr class="fila-cliente">
                                <td class="text-center" style="vertical-align: middle;">
                                    <input type="checkbox" name="clientes_seleccionados[]" class="chk-cliente" value="{{ $cli->clicod }}" data-monto="{{ $cli->monto }}" checked style="transform: scale(1.5);">
                                </td>
                                <td style="vertical-align: middle; font-weight: bold;">{{ $cli->clinom }}</td>
                                <td style="vertical-align: middle;">{{ $cli->clinum }}</td>
                                <td style="vertical-align: middle;">
                                    @if($cli->comprobante == '01') 
                                        <span class="label label-success" style="font-size: 12px;">FACTURA</span>
                                    @elseif($cli->comprobante == '13') 
                                        <span class="label label-info" style="font-size: 12px;">NOTA DE VENTA</span>
                                    @else 
                                        <span class="label label-default" style="font-size: 12px;">{{ $cli->comprobante }}</span>
                                    @endif
                                </td>
                                <td style="vertical-align: middle;">{{ $cli->clidir }}</td>
                                <td class="text-right text-success" style="vertical-align: middle; font-size: 16px; font-weight: bold;">
                                    {{ number_format($cli->monto, 2) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted" style="padding: 20px;">
                                    <i class="fas fa-info-circle fa-2x mb-2"></i><br>
                                    No hay clientes configurados para cobro mensual.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    // 1. Buscador dinámico
    document.getElementById('buscadorClientes').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('.fila-cliente');

        rows.forEach(row => {
            let text = row.textContent.toLowerCase();
            if(text.includes(filter)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // 2. Función para recalcular el total
    function recalcularTotal() {
        let total = 0;
        let checkboxes = document.querySelectorAll('.chk-cliente:checked');
        checkboxes.forEach(function(chk) {
            // Solo sumamos si la fila no está oculta por el buscador
            let fila = chk.closest('tr');
            if(fila.style.display !== 'none') {
                total += parseFloat(chk.getAttribute('data-monto'));
            }
        });
        document.getElementById('monto_total_vista').innerText = total.toFixed(2);
    }

    // 3. Check principal (ahora solo afecta a las filas visibles tras la búsqueda)
    document.getElementById('checkAll').addEventListener('change', function(e) {
        let isChecked = e.target.checked;
        let rows = document.querySelectorAll('.fila-cliente');
        
        rows.forEach(row => {
            if(row.style.display !== 'none') {
                let chk = row.querySelector('.chk-cliente');
                if(chk) chk.checked = isChecked;
            }
        });
        recalcularTotal();
    });

    // 4. Asignar evento de cambio a cada checkbox individual
    let clientChecks = document.querySelectorAll('.chk-cliente');
    clientChecks.forEach(function(chk) {
        chk.addEventListener('change', recalcularTotal);
    });

    // Calcular el total la primera vez que carga la página
    window.onload = function() {
        recalcularTotal();
    };
</script>
@endsection