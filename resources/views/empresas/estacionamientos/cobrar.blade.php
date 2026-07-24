@extends('layouts.empresas')

@section('contenido')
<style>
    .main-header, .main-sidebar, .navbar, .sidebar, .nav { display: none !important; }
    .content-wrapper { margin-left: 0 !important; padding-top: 0 !important; background-color: #f4f6f9; }
    .resumen-caja { background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 5px solid #ffc107; margin-bottom: 20px; }
    
    @media print {
        body * { visibility: hidden; } 
        #comprobante-imprimir, #comprobante-imprimir * { visibility: visible; }
        
        #comprobante-imprimir { 
            position: absolute; 
            left: 0; 
            top: 0; 
            width: 72mm;
            margin: 0; 
            padding: 2mm; 
            font-family: 'Courier New', Courier, monospace; 
            font-size: 13px; 
            color: #000;
        }

        @page { size: 80mm auto; margin: 0mm; }
        .centrar { text-align: center; }
        .linea { border-top: 1px dashed #000; margin: 5px 0; }
        .margen-cero { margin: 2px 0; }
    }
</style>

<br>

{!!Form::open(array('url'=>'/estacionamiento/registrarcobro','autocomplete'=>'off','method'=>'POST','name'=>'frmcobro','id'=>'frmcobro'))!!}

<input type="hidden" name="ticket_id" value="{{ $ticket->id }}">

<!-- Datos Libres de Venta -->
<input type="hidden" name="descripcion[]" value="SERVICIO DE ESTACIONAMIENTO - PLACA: {{ $ticket->placa }}">
<input type="hidden" name="total_venta" id="total_venta" value="{{ number_format($ticket->monto_total, 2, '.', '') }}">
<input type="hidden" name="mozo" value="{{ Auth::user()->IdUsuario }}">

<div class="container">
    <div class="row">
        <div class="col-md-7">
            <div class="box shadow">
                <div class="box-header with-border bg-primary text-white">
                    <h3 class="box-title"><i class="fa fa-user"></i> Datos del Comprobante</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Comprobante</label>
                                <select class="form-control" name="tdocod" id="tdocod">
                                    @foreach($comprobantes as $comp)
                                        <option value="{{$comp->tdocod}}" {{ (isset($negocio->tdocod_pred) && $comp->tdocod == $negocio->tdocod_pred) ? 'selected' : '' }}>
                                            {{$comp->tdodes}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Condición Pago</label>
                                <select class="form-control" name="estadopago" id="estadopago">
                                    @foreach($estadopagos as $est_pag)
                                        <option value="{{$est_pag->cre_dia_id}}">{{$est_pag->cre_dia_nom}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tipo Doc.</label>
                                <select name="tdicod" id="tdicod" class="form-control">
                                    @foreach($documentos as $doc)
                                        <option value='{{$doc->tdicod}}'>{{$doc->tdides}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>DNI / RUC</label>
                                <div class="input-group">
                                    <input name="clinum" id="clinum" value="00000000" class="form-control" onkeypress="if(event.keyCode == 13) { event.preventDefault(); buscarclienteruc(); }">
                                    <input type="hidden" name="clicod" id="clicod">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-primary" onclick="buscarclienteruc();"><span class="fa fa-search"></span> API</button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Nombre o Razón Social</label>
                        <input name="clinom" id="clinom" value="CLIENTE VARIOS" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Dirección</label>
                        <input name="clidir" id="clidir" value="--" class="form-control">
                    </div>
                    <input type="hidden" name="fecEmi" value="{{ date('Y-m-d') }}">
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="box shadow">
                <div class="box-header with-border bg-success text-white">
                    <h3 class="box-title"><i class="fa fa-car"></i> Detalle de Salida</h3>
                </div>

                <div class="box-body">
                    <div class="form-group">
                        <label>Seleccionar Tarifa Aplicable</label>
                        <select class="form-control" name="tarifa_id" id="tarifa_id" onchange="calcularPrecio()">
                            @php $tarifaAsignada = $ticket->tarifa_id ?? null; @endphp
                            @foreach($tarifas as $t)
                                @php
                                    $esSeleccionada = false;
                                    if ($tarifaAsignada) {
                                        $esSeleccionada = ($tarifaAsignada == $t->id);
                                    } else {
                                        $esSeleccionada = (strtoupper($t->nombre) == 'FLAT');
                                    }
                                @endphp
                                <option value="{{$t->id}}" 
                                        data-primera="{{$t->precio_primera_hora}}" 
                                        data-adic="{{$t->precio_hora_adicional}}" 
                                        data-desc="{{$t->descuento_progresivo}}"
                                        {{ $esSeleccionada ? 'selected' : '' }}>
                                    {{$t->nombre}}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="resumen-caja text-center">
                        <h2 class="font-weight-bold">PLACA: {{ $ticket->placa }}</h2>
                        <hr style="border-top: 1px dashed #ccc;">
                        <div class="row">
                            <div class="col-xs-6 border-right">
                                <span class="text-muted">Tiempo:</span><br><b>{{ $tiempo }} Minutos</b>
                            </div>
                            <div class="col-xs-6">
                                <span class="text-muted">Total:</span><br><b class="text-danger" style="font-size: 1.4rem;">S/ <span id="lbl_total_vista">{{ number_format($ticket->monto_total, 2) }}</span></b>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Medio de Pago</label>
                        <select class="form-control input-lg" name="med_pag" id="med_pag">
                            @foreach($mediospagos as $medpag)
                                <option value="{{$medpag->id_med_pag}}">{{$medpag->nom_med_pag}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-xs-6">
                            <label>PAGA CON S/:</label>
                            <input type="number" step="any" class="form-control input-lg text-center font-weight-bold" id="pagar" name="pagar" value="{{ number_format($ticket->monto_total, 2, '.', '') }}" onkeyup="calcular_vuelto();">
                        </div>
                        <div class="col-xs-6">
                            <label>VUELTO S/:</label>
                            <input type="text" class="form-control input-lg text-center font-weight-bold text-danger" id="vuelto" name="vuelto" value="0.00" readonly>
                        </div>
                    </div>

                    <br>
                    <button type="button" id="btnRegistrar" class="btn btn-success btn-lg btn-block shadow"><i class="fa fa-print"></i> COBRAR E IMPRIMIR</button>
                    <a href="/estacionamiento/ingreso" class="btn btn-default btn-lg btn-block mt-2">CANCELAR</a>
                    
                    <center><img src="/img/load.gif" id="imgload" style="display:none; width: 40px; margin-top: 15px;"></center>
                </div>
            </div>
        </div>
    </div>
</div>
{!!Form::close()!!}

<div id="comprobante-imprimir" style="display:none;">
    <div class="centrar">
        <h2 class="margen-cero">{{ isset($negocio->nombre_comercial) ? $negocio->nombre_comercial : 'HOLA P E.I.R.L.' }}</h2>
        <p class="margen-cero">RUC: {{ isset($negocio->IdEmpresa) ? $negocio->IdEmpresa : '20610257705' }}</p>
        <p class="margen-cero">{{ isset($negocio->direccion) ? $negocio->direccion : 'Dirección no registrada' }}</p>
    </div>
    
    <div class="linea"></div>
    <div class="centrar">
        <h3 class="margen-cero" id="tkt-tipo-doc" style="text-transform: uppercase;">COMPROBANTE</h3>
        <h3 class="margen-cero" id="tkt-serie-numero">B001-000000</h3>
    </div>
    <div class="linea"></div>

    <div>
        <p class="margen-cero"><b>Fecha/Hora:</b> {{ date('d/m/Y H:i:s') }}</p>
        <p class="margen-cero"><b>Cliente:</b> <span id="tkt-cliente"></span></p>
        <p class="margen-cero"><b>Doc:</b> <span id="tkt-num-doc"></span></p>
        <p class="margen-cero"><b>Dirección:</b> <span id="tkt-direccion"></span></p>
        <p class="margen-cero"><b>Cajero:</b> {{ Auth::user()->name }}</p>
    </div>

    <div class="linea"></div>
    
    <table style="width: 100%; text-align: left; font-size: 12px;">
        <tr class="linea">
            <th style="width: 15%;">CANT</th>
            <th style="width: 60%;">DESCRIPCIÓN</th>
            <th style="width: 25%; text-align: right;">IMPORTE</th>
        </tr>
        <tr>
            <td valign="top">1.00</td>
            <td>SERVICIO DE ESTACIONAMIENTO<br>PLACA: <b>{{ $ticket->placa }}</b><br>TIEMPO: {{ $tiempo }} MIN</td>
            <td valign="top" style="text-align: right;">S/ <span id="tkt_total_item">{{ number_format($ticket->monto_total, 2) }}</span></td>
        </tr>
    </table>

    <div class="linea"></div>
    
    <table style="width: 100%; font-size: 13px;">
        <tr>
            <td><b style="font-size: 15px;">TOTAL A PAGAR:</b></td>
            <td style="text-align: right;"><b style="font-size: 15px;">S/ <span id="tkt_total_pagar">{{ number_format($ticket->monto_total, 2) }}</span></b></td>
        </tr>
        <tr>
            <td>Pagó con:</td>
            <td style="text-align: right;">S/ <span id="tkt-paga"></span></td>
        </tr>
        <tr>
            <td>Vuelto:</td>
            <td style="text-align: right;">S/ <span id="tkt-vuelto"></span></td>
        </tr>
    </table>

    <div class="linea"></div>
    <div class="centrar" style="margin-top: 10px;">
        <svg id="barcode-cpe" style="width: 100%; max-width: 180px;"></svg>
        <p class="margen-cero" style="font-size: 10px; margin-top: 5px;">¡Gracias por su preferencia!</p>
        <p class="margen-cero" style="font-size: 9px;">Representación impresa de comprobante electrónico</p>
    </div>
    <br><br>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    function calcular_vuelto(){
        var total = parseFloat($("#total_venta").val()) || 0; 
        var pagar = parseFloat($("#pagar").val()) || 0;
        var vuelto = pagar - total;
        if(vuelto < 0){ $("#vuelto").val("0.00"); }
        else{ $("#vuelto").val(vuelto.toFixed(2)); }
    }

    function calcularPrecio() {
        var tarifaId = $('#tarifa_id').val();
        if(!tarifaId) {
            actualizarMontosVisibles(0);
            return;
        }

        var opcion = $('#tarifa_id option:selected');
        var primeraHora = parseFloat(opcion.attr('data-primera')) || 0;
        var adicHora = parseFloat(opcion.attr('data-adic')) || 0;
        var descuento = parseFloat(opcion.attr('data-desc')) || 0; 
        
        var minutos = parseInt("{{ $tiempo }}") || 1; 
        var horas = Math.ceil(minutos / 60);
        
        var total = 0;
        if(horas <= 1) {
            total = primeraHora;
        } else {
            var precioConDesc = adicHora * (1 - (descuento / 100));
            total = primeraHora + ((horas - 1) * precioConDesc);
        }

        actualizarMontosVisibles(total);
    }

    function actualizarMontosVisibles(total) {
        var totalStr = total.toFixed(2);
        
        $('#lbl_total_vista').text(totalStr); 
        $('#pagar').val(totalStr);
        $('#total_venta').val(totalStr);
        $('#tkt_total_item').text(totalStr);
        $('#tkt_total_pagar').text(totalStr);

        calcular_vuelto(); 
    }

    function buscarclienteruc(){
        var doc = $("#clinum").val();
        if(doc.length !== 8 && doc.length !== 11) { 
            alert("El documento debe tener 8 (DNI) o 11 (RUC) dígitos."); 
            return; 
        }

        $("#imgload").show();

        $.ajax({
            type: "POST",
            url: "/consultardocumento",
            data: { documento: doc, _token: '{{ csrf_token() }}' },
            dataType: "json",
            success: function(res) {
                if(res.success) {
                    var nombre = res.data.nombre_completo || res.data.nombre_o_razon_social;
                    var direccion = res.data.direccion || "-";

                    $('#clinom').val(nombre); 
                    $('#clidir').val(direccion);
                    
                    if(doc.length === 8) { 
                        $("#tdicod").val('1'); 
                        $("#tdocod").val('03'); 
                    }
                    if(doc.length === 11) { 
                        $("#tdicod").val('6'); 
                        $("#tdocod").val('01'); 
                    }
                } else {
                    alert(res.error || res.message || "Documento no encontrado");
                }
                $("#imgload").hide();
            },
            error: function() {
                alert("Error al conectar con la API de consultas.");
                $("#imgload").hide();
            }
        });
    }

    $("#btnRegistrar").click(function(e){
        e.preventDefault();
        var pagar = parseFloat($("#pagar").val()) || 0;
        var total = parseFloat($("#total_venta").val()) || 0;

        if (pagar < total) {
            alert("El monto ingresado es menor al total.");
            $("#pagar").focus(); return;
        }

        $("#btnRegistrar").prop('disabled', true);
        $("#imgload").show();

        var formulario = $("#frmcobro").serializeArray();

        $.ajax({
            type: "POST",
            dataType: 'json',
            url: '/estacionamiento/registrarcobro',
            data: formulario,
        }).done(function(res){
            if(res.estado === 'error'){
                alert(res.mensaje);
                $("#btnRegistrar").prop('disabled', false);
                $("#imgload").hide();
            } else {
                var nom_doc = $("#tdocod option:selected").text();
                $('#tkt-tipo-doc').text(nom_doc);
                $('#tkt-serie-numero').text(res.serie_correlativo); 
                $('#tkt-cliente').text($("#clinom").val());
                $('#tkt-num-doc').text($("#clinum").val());
                $('#tkt-direccion').text($("#clidir").val());
                $('#tkt-paga').text(parseFloat($("#pagar").val()).toFixed(2));
                $('#tkt-vuelto').text($("#vuelto").val());

                JsBarcode("#barcode-cpe", res.serie_correlativo, {
                    format: "CODE128", width: 1.5, height: 40, displayValue: true, fontSize: 13, margin: 0
                });

                $('#comprobante-imprimir').show();
                window.print();
                $('#comprobante-imprimir').hide();

                window.location.href = "/estacionamiento/ingreso";
            }
        }).fail(function(err) {
            console.log(err);
            alert("Ocurrió un error interno en el servidor.");
            $("#btnRegistrar").prop('disabled', false);
            $("#imgload").hide();
        });
    });

    $(document).ready(function() {
        if($('#tarifa_id').val() !== "") {
            calcularPrecio();
        }
    });
</script>
@endsection