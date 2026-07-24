@extends('layouts.empresas')

@section('contenido')
<style>
    .card { border-radius: 10px; border: none; }
    .card-header { border-radius: 10px 10px 0 0 !important; font-weight: bold; }
    .form-control-lg { border-radius: 8px; font-size: 1.2rem; text-align: center; font-weight: bold; letter-spacing: 2px;}
    .table-responsive { background: white; border-radius: 8px; padding: 10px; }
    .reloj-vivo { font-family: monospace; font-size: 1.1rem; font-weight: bold; color: #dc3545; }
    
    @media print {
        body * { visibility: hidden; } 
        #ticket-para-imprimir, #ticket-para-imprimir * { visibility: visible; }
        #ticket-para-imprimir { position: absolute; left: 0; top: 0; }
    }
</style>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white text-center">
                    <h5 class="mb-0">Registrar Ingreso</h5>
                </div>
                <div class="card-body text-center p-4">
                    <label class="text-muted mb-2">Placa del Vehículo</label>
                    <input type="text" id="placa" class="form-control form-control-lg mb-3" placeholder="ABC-123" style="text-transform: uppercase;">
                    <button onclick="registrarIngreso()" class="btn btn-primary btn-lg btn-block" style="border-radius: 8px;">
                        Generar e Imprimir
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white text-center">
                    <h5 class="mb-0">Registrar Salida (Lector)</h5>
                </div>
                <div class="card-body text-center p-4">
                    <label class="text-muted mb-2">Pase el código de barras aquí</label>
                    <input type="text" id="codigo_scan" class="form-control form-control-lg mb-3" placeholder="Escanee aquí..." autofocus>
                    
                    <div id="error_salida" class="alert alert-danger mt-3" style="display:none;"></div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card shadow-sm bg-dark text-white text-center">
                <div class="card-body p-4">
                    <h6 class="text-uppercase text-muted">Vehículos Adentro</h6>
                    <h1 class="display-4 font-weight-bold" id="contador-activos">{{ count($activos) }}</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="row" id="panel-comprobante" style="display: none;">
        <div class="col-12 mb-4">
            <div class="card border-success shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fa fa-file-text-o"></i> Detalle de Operación y Comprobante de Pago</h5>
                </div>
                <div class="card-body bg-light">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Vehículo / Placa</p>
                            <h3 class="font-weight-bold text-dark" id="comp-placa">--</h3>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Tiempo Transcurrido</p>
                            <h3 class="font-weight-bold text-danger" id="comp-tiempo">0 min</h3>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Monto Cobrado</p>
                            <h1 class="font-weight-bold text-success" style="margin-top:-10px;">S/ <span id="comp-total">0.00</span></h1>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Acción del Sistema</p>
                            <a href="#" id="btn-cobrar-ticket" class="btn btn-primary btn-block mb-1">
                                <i class="fa fa-money"></i> Procesar Cobro
                            </a>
                            <button class="btn btn-outline-secondary btn-block btn-sm" onclick="$('#panel-comprobante').slideUp();">
                                <i class="fa fa-times"></i> Cancelar / Ocultar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fa fa-car"></i> Monitoreo de Vehículos en Tiempo Real</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover valigned" id="tabla-estacionados">
                            <thead>
                                <tr>
                                    <th>Placa</th>
                                    <th>Código de Barras</th>
                                    <th>Hora de Ingreso</th>
                                    <th>Tiempo Transcurrido (En Vivo)</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-activos">
                                @foreach($activos as $item)
                                <tr id="fila-{{ $item->codigo_barras }}">
                                    <td><span class="badge badge-warning" style="font-size:1rem; padding:6px 12px;">{{ $item->placa }}</span></td>
                                    <td><code>{{ $item->codigo_barras }}</code></td>
                                    <td>{{ date('d/m/Y h:i A', strtotime($item->hora_ingreso)) }}</td>
                                    <td>
                                        <span class="reloj-vivo" data-ingreso="{{ $item->hora_ingreso }}">Calculando...</span>
                                    </td>
                                    <td class="text-center">
                                        <button onclick="reimprimirTicket('{{ $item->placa }}', '{{ $item->codigo_barras }}')" class="btn btn-sm btn-info">
                                            <i class="fa fa-print"></i> Reimprimir Ticket
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="ticket-para-imprimir" style="display:none; width: 250px; font-family: 'Courier New', Courier, monospace; font-size: 13px; text-align: center; color: #000;">
    <h3 style="margin: 0; font-weight: bold;">HolaPE</h3>
    <p style="margin: 5px 0;">Control de Estacionamiento</p>
    <hr style="border-top: 1px dashed black; margin: 10px 0;">
    <h2 style="margin: 0;" id="lbl-placa-ticket"></h2>
    <div style="margin: 10px 0;"><svg id="barcode" style="width: 100%; max-width: 220px;"></svg></div>
    <p style="margin: 5px 0;"><strong>Ingreso:</strong> <span id="lbl-fecha-ticket"></span></p>
    <hr style="border-top: 1px dashed black; margin: 10px 0;">
    <p style="margin: 5px 0;">Conserve este ticket para su salida.</p>
    <p style="margin: 5px 0;">¡Gracias por su preferencia!</p>
    <br><br><br>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

<script>
    function obtenerFechaActual() {
        var d = new Date();
        return ("0" + d.getDate()).slice(-2) + "/" + ("0" + (d.getMonth() + 1)).slice(-2) + "/" + d.getFullYear() + " " + ("0" + d.getHours()).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2);
    }

    // 1. Registrar Ingreso
    function registrarIngreso() {
        var placa = $('#placa').val().toUpperCase().trim();
        if(placa === '') { alert('¡Debes ingresar una placa!'); $('#placa').focus(); return; }
        
        $.post('/estacionamiento/ingreso', { placa: placa, _token: '{{ csrf_token() }}' }, function(res) {
            imprimirAccion(res.ticket.placa, res.ticket.codigo_barras, obtenerFechaActual());
            $('#placa').val('').focus();
            actualizarTabla();
        }).fail(function(xhr) {
            if(xhr.status === 422) {
                alert(xhr.responseJSON.message);
            } else {
                alert('Ocurrió un error al registrar el ingreso.');
            }
        });
    }

    function reimprimirTicket(placa, codigo) {
        imprimirAccion(placa, codigo, obtenerFechaActual());
    }

    function imprimirAccion(placa, codigo, fecha) {
        $('#lbl-placa-ticket').text(placa);
        $('#lbl-fecha-ticket').text(fecha);
        JsBarcode("#barcode", codigo, { format: "CODE128", width: 1.5, height: 45, displayValue: true, fontSize: 14, margin: 0 });
        $('#ticket-para-imprimir').show();
        window.print();
        $('#ticket-para-imprimir').hide();
    }

    // 2. Salida (Escaneo)
    $('#codigo_scan').keypress(function (e) {
        if (e.which == 13) { 
            var codigo = $(this).val().trim();
            if(codigo === '') return;

            $('#error_salida').hide();

            $.post('/estacionamiento/salida/' + codigo, { _token: '{{ csrf_token() }}' }, function(res) {
                if(res.success) {
                    $('#comp-placa').text(res.placa);
                    $('#comp-tiempo').text(res.tiempo + ' min');
                    $('#comp-total').text(res.total.toFixed(2));

                    $('#btn-cobrar-ticket').attr('href', '/estacionamiento/cobrar/' + codigo);
                    
                    $('#panel-comprobante').slideDown(); 

                    // Ya no ocultamos la fila de la tabla aquí, permitiendo que persista si el usuario cancela.
                    actualizarTabla();
                } else {
                    $('#error_salida').text(res.message).show();
                }
                $('#codigo_scan').val('').focus();
            }).fail(function() {
                $('#error_salida').text('Error de comunicación o ticket inválido.').show();
                $('#codigo_scan').val('').focus();
            });
        }
    });

    // 3. Relojes en Tiempo Real
    function iniciarRelojes() {
        $('.reloj-vivo').each(function() {
            var stringFechaIngreso = $(this).data('ingreso'); 
            if (!stringFechaIngreso) return;

            var t = stringFechaIngreso.split(/[- :]/);
            var fechaIngreso = new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);
            var ahora = new Date();

            var diferenciaSg = Math.floor((ahora - fechaIngreso) / 1000);
            if (diferenciaSg < 0) diferenciaSg = 0;

            var horas = Math.floor(diferenciaSg / 3600);
            var minutos = Math.floor((diferenciaSg % 3600) / 60);
            var segundos = diferenciaSg % 60;

            var formato = (horas < 10 ? "0" + horas : horas) + "h " + 
                          (minutos < 10 ? "0" + minutos : minutos) + "m " + 
                          (segundos < 10 ? "0" + segundos : segundos) + "s";

            $_this = $(this);
            $_this.text(formato);

            if(horas >= 2){
                $_this.removeClass('text-danger').css('color', '#ffc107');
            }
        });
    }

    setInterval(iniciarRelojes, 1000);

    function actualizarTabla() {
        $.get('/estacionamiento/activos', function(data) {
            $('#contador-activos').text(data.length);
            var html = '';
            data.forEach(function(item) {
                var fechaJS = new Date(item.hora_ingreso);
                var fStr = ("0" + fechaJS.getDate()).slice(-2) + "/" + ("0" + (fechaJS.getMonth() + 1)).slice(-2) + "/" + fechaJS.getFullYear() + " " + ("0" + fechaJS.getHours()).slice(-2) + ":" + ("0" + fechaJS.getMinutes()).slice(-2);

                html += '<tr>' +
                    '<td><span class="badge badge-warning" style="font-size:1rem; padding:6px 12px;">' + item.placa + '</span></td>' +
                    '<td><code>' + item.codigo_barras + '</code></td>' +
                    '<td>' + fStr + '</td>' +
                    '<td><span class="reloj-vivo" data-ingreso="' + item.hora_ingreso + '">Calculando...</span></td>' +
                    '<td class="text-center">' +
                        '<button onclick="reimprimirTicket(\''+item.placa+'\', \''+item.codigo_barras+'\')" class="btn btn-sm btn-info"><i class="fa fa-print"></i> Reimprimir Ticket</button>' +
                    '</td>' +
                '</tr>';
            });
            $('#tbody-activos').html(html);
            iniciarRelojes(); 
        });
    }

    $(document).ready(function() {
        iniciarRelojes();
    });
</script>
@endsection