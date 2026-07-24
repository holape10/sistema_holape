<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migración a Hola P</title>
    <!-- Bootstrap CSS y FontAwesome -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body style="background-color: #f4f6f9; padding: 20px;">

<div class="container mt-4">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-database"></i> Panel de Migración Base de Datos </h3>
        </div>
        <div class="panel-body">
            <p>Usa este panel para traer la información de las tablas de la base de datos antigua a la nueva estructura vacía.</p>
            
            <button class="btn btn-primary" id="btn-importar-todo">
                <i class="fa fa-play"></i> Iniciar Migración Completa
            </button>

            <!-- Aquí mostraremos el progreso -->
            <div class="mt-3" style="margin-top: 20px;">
                <h4 id="status-texto" class="text-info" style="display: none;">Preparando...</h4>
                <ul id="lista-resultados" class="list-unstyled mt-2" style="max-height: 400px; overflow-y: auto; background: #222; color: #0f0; padding: 10px; border-radius: 5px; font-family: monospace; display: none;"></ul>
            </div>
        </div>
    </div>
</div>

<!-- jQuery y SweetAlert -->
<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Tu lista completa de tablas
    const tablasAMigrar = [
        'academicos', 'almacenes', 'categorias', 'combos', 'combustible', 'compras_cabecera', 'compras_detalle',
        'configuracion_impresoras', 'credito_dias', 'cuentas_cobrar', 'cuentas_cobrar_detalle', 'cuentas_cobrar_medios',
        'cuentas_pagar', 'cuentas_pagar_detalle', 'cuentas_pagar_medios', 'cuentasbancarias', 'documento_relacionado',
        'empleado', 'empresa', 'empresa_negocios', 'fidelizacion_configs', 'gastos_cabecera', 'gastos_detalle',
        'guias_remision', 'guias_remision_detalle', 'inventario_cabecera', 'inventario_detalle', 'marcas',
        'medios_pagos', 'mesas', 'mesas_union', 'meses', 'movimientos', 'movimientos_productos', 'movimientosbancarios',
        'movimientoscaja', 'pedidos', 'pedidos_detalle', 'pisos', 'productos', 'producto_stock', 'producto_empresa',
        'producto_codigo', 'precios_dia_semana', 'proveedor', 'puntos_historial', 'recetas', 'resumenes', 'role_user',
        'saldos_arqueo', 'subcategorias', 'cliente', 'cpe_detalle', 'cpe_cabecera', 'tipo_producto', 'tipocambio',
        'tipos_vehiculos', 'turno_medio_pago', 'turnos', 'users', 'usuario_facturacion', 'usuario_gastos',
        'usuario_modificar', 'usuario_pedidos', 'usuario_sucursal', 'venta_medio_pago', 'ventas_cuotas'
    ];

    $('#btn-importar-todo').on('click', function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Migrando...');
        $('#status-texto').show();
        $('#lista-resultados').show().empty();

        procesarTabla(0, btn);
    });

    function procesarTabla(index, btn) {
        if (index >= tablasAMigrar.length) {
            Swal.fire('¡Migración Finalizada!', 'Todas las tablas han sido procesadas.', 'success');
            btn.prop('disabled', false).html('<i class="fa fa-database"></i> Migración Completada');
            $('#status-texto').text('Migración finalizada.');
            return;
        }

        let tablaActual = tablasAMigrar[index];
        $('#status-texto').text(`Migrando tabla (${index + 1}/${tablasAMigrar.length}): ${tablaActual}...`);

        $.ajax({
            url: "{{ route('importar.tabla') }}",
            method: 'POST',
            data: { 
                _token: "{{ csrf_token() }}",
                tabla: tablaActual
            },
            success: function(data) {
                let color = data.res === 'success' ? '#0f0' : 'orange';
                $('#lista-resultados').prepend(`<li style="color: ${color};"><i class="fa fa-check"></i> [OK] ${data.msg}</li>`);
                procesarTabla(index + 1, btn);
            },
            error: function(xhr) {
                let errorMsg = xhr.responseJSON ? xhr.responseJSON.msg : 'Error de servidor';
                $('#lista-resultados').prepend(`<li style="color: red;"><i class="fa fa-times"></i> [ERROR] en ${tablaActual}: ${errorMsg}</li>`);
                procesarTabla(index + 1, btn);
            }
        });
    }
</script>
</body>
</html>