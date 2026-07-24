@extends ('layouts.empresas')
@section ('contenido')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Nueva Operación de Transporte (Flujo Veloz)</h3>
                </div>
                
                <form action="{{ route('transportes.store') }}" method="POST">
                    {{ csrf_field() }}
                    
                    <div class="card-body">
                        <h4>1. Datos de la Carga / Orden</h4>
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label>Doc. Cliente (RUC/DNI)</label>
                                <input type="text" name="cliente_documento" class="form-control" required placeholder="Ej. 20123456789">
                            </div>
                            <div class="form-group col-md-5">
                                <label>Razón Social / Nombre</label>
                                <input type="text" name="cliente_nombre" class="form-control" required placeholder="Nombre del cliente">
                            </div>
                            <div class="form-group col-md-2">
                                <label>Precio Total (S/.)</label>
                                <input type="number" step="0.01" name="precio" class="form-control" required placeholder="0.00">
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>Punto de Origen</label>
                                <input type="text" name="origen" class="form-control" required placeholder="Dirección de partida">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Punto de Destino</label>
                                <input type="text" name="destino" class="form-control" required placeholder="Dirección de llegada">
                            </div>
                            <div class="form-group col-md-2">
                                <label>Peso (KG)</label>
                                <input type="number" step="0.01" name="peso" class="form-control" value="0.00">
                            </div>
                            <div class="form-group col-md-2">
                                <label>Bultos</label>
                                <input type="number" name="bultos" class="form-control" value="1">
                            </div>
                        </div>

                        <hr>

                        <div class="row bg-light p-3 rounded mb-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="text-primary">¿Generar Guía de Remisión ahora?</label>
                                    <select name="auto_guia" id="auto_guia" class="form-control" onchange="toggleGuiaFields()">
                                        <option value="NO">NO, solo guardar orden</option>
                                        <option value="SI" selected>SÍ, procesar de inmediato</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 guia-field">
                                <div class="form-group">
                                    <label>Placa Vehículo</label>
                                    <input type="text" name="placa_vehiculo" class="form-control" placeholder="Ej. ABC-123">
                                </div>
                            </div>
                            <div class="col-md-4 guia-field">
                                <div class="form-group">
                                    <label>Licencia Conducir</label>
                                    <input type="text" name="licencia_conductor" class="form-control" placeholder="Ej. Q12345678">
                                </div>
                            </div>
                        </div>

                        <div class="row bg-light p-3 rounded guia-field" id="comprobante-section">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="text-success">¿Emitir Comprobante Electrónico?</label>
                                    <select name="auto_comprobante" id="auto_comprobante" class="form-control" onchange="toggleComprobanteFields()">
                                        <option value="NO">NO, solo la guía</option>
                                        <option value="SI" selected>SÍ, Factura directa</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 comprobante-field">
                                <div class="form-group">
                                    <label>Tipo Comprobante</label>
                                    <select name="tipo_comprobante" class="form-control">
                                        <option value="01">01 - FACTURA ELECTRÓNICA</option>
                                        <option value="03">03 - BOLETA ELECTRÓNICA</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-block btn-lg btn-success">
                            <i class="fa fa-flash"></i> EJECUTAR OPERACIÓN INTEGRAL (1-CLICK)
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // JavaScript sencillo para ocultar o mostrar campos dinámicamente sin recargar
    function toggleGuiaFields() {
        var autoGuia = document.getElementById('auto_guia').value;
        var fields = document.querySelectorAll('.guia-field');
        fields.forEach(function(field) {
            field.style.display = (autoGuia === 'SI') ? '' : 'none';
        });
        if(autoGuia === 'NO') {
            document.getElementById('auto_comprobante').value = 'NO';
            toggleComprobanteFields();
        }
    }

    function toggleComprobanteFields() {
        var autoComp = document.getElementById('auto_comprobante').value;
        var fields = document.querySelectorAll('.comprobante-field');
        fields.forEach(function(field) {
            field.style.display = (autoComp === 'SI') ? '' : 'none';
        });
    }
    
    // Ejecutar al cargar la vista
    window.onload = function() {
        toggleGuiaFields();
        toggleComprobanteFields();
    };
</script>
@endsection