@extends('layouts.empresas')
@section('contenido')

<style>
    .shadow-box { box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-radius: 5px; }
    .section-header { 
        background: #f4f4f4; 
        padding: 8px 12px; 
        font-weight: bold; 
        border-left: 4px solid #3c8dbc; 
        margin-bottom: 15px;
        font-size: 14px;
    }
    .table-resumen th { background-color: #f9f9f9; text-align: center; color: #333; }
    
    /* Clase para filas procesadas correctamente */
    .row-success-sunat { color: #27ae60 !important; font-weight: bold; }
    .row-success-sunat code { color: #27ae60 !important; border-color: #27ae60; }

    input[type=date]::-webkit-inner-spin-button, 
    input[type=date]::-webkit-clear-button,
    input[type=date]::-webkit-outer-spin-button { 
        -webkit-appearance: none; margin: 0; 
    }
</style>

<script>
    $(document).ready(function() {
        // Enviar a SUNAT (Facturación)
        $("#btnenviar").on("click", function() {
            var formulario = $("#frmfacturacion").serializeArray();
            $("#divdetalle").html('<center><img src="/img/load.gif" width="80px"></center>');
            $.ajax({
                type: "POST",
                dataType: 'json',
                url: '/enviarsunat',
                data: formulario,
            }).done(function(respuesta){
                window.location.href = "/facturacionelectronica";
            });
        });

        // Generar Resumen Diario
        $("#btnresumen").on("click", function() {
            $("#botones").html('<center><img src="/img/load.gif" width="60px"></center>');
            var formulario = $("#frmfacturacion").serializeArray();
            
            $.ajax({
                type: "POST",
                dataType: 'json',
                url: '/enviarresumen',
                data: formulario,
            }).done(function(respuesta){
                if (respuesta.alerta) {
                    alert(respuesta.mensaje); 
                }
                window.location.href = "/listarresumenes";
            }).fail(function(jqXHR) {
                if (jqXHR.responseJSON && jqXHR.responseJSON.mensaje) {
                    alert('Error: ' + jqXHR.responseJSON.mensaje);
                } else {
                    alert('Ocurrió un error inesperado al procesar el resumen.');
                }
                $("#botones").html('<button type="button" id="btnresumen" class="btn btn-success btn-block shadow"><i class="fa fa-refresh"></i> Generar Resumen</button>');
            });
        });

        // Plugin de Impresión
        var href = $('#btnPrint').attr('href');
        $("#btnPrint").printPage({
            url: href,
            attr: "href",
            messageBox: false,
        });
    });
</script>

<section class="content">
    {!!Form::open(array('url'=>'/buscarresumenes','autocomplete'=>'off','method'=>'POST','name'=>'frmfacturacion','id'=>'frmfacturacion','role'=>'form','files'=>'true'))!!}
    {{Form::token()}}

    <div class="row">
        <div class="col-md-12">
            @if(session()->has('info'))
                <div class="alert alert-warning alert-dismissible shadow">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h4><i class="icon fa fa-warning"></i> ¡Alerta!</h4>
                    {{ session('info') }}
                </div>
            @endif
            @if(session()->has('success'))
                <div class="alert alert-success alert-dismissible shadow">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h4><i class="icon fa fa-check"></i> ¡Éxito!</h4>
                    {{ session('success') }}
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary shadow-box">
                <div class="box-header with-border" style="background: #2c3e50; color: white;">
                    <h3 class="box-title"><i class="fa fa-calendar-check-o"></i> <strong>GENERAR RESUMEN DIARIO - SUNAT</strong></h3>
                </div>
                
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6" style="border-right: 1px solid #eee;">
                            <div class="section-header text-primary">BUSCAR RESÚMENES ENVIADOS</div>
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group form-group-sm">
                                        <label><i class="fa fa-calendar"></i> Desde</label>
                                        {!!Form::date('fecin',Carbon::now()->startOfMonth()->format('Y-m-d'),['class'=>'form-control','id'=>'fecin']);!!}
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group form-group-sm">
                                        <label><i class="fa fa-calendar"></i> Hasta</label>
                                        {!!Form::date('fecfin',Carbon::now()->endOfMonth()->format('Y-m-d'),['class'=>'form-control','id'=>'fecfin']);!!}
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-sm btn-block shadow"><i class="fa fa-search"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="section-header text-success">GENERAR NUEVO RESUMEN</div>
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group form-group-sm">
                                        <label><i class="fa fa-file-text-o"></i> Fecha Comprobantes</label>
                                        {!!Form::date('fecresumen',Carbon::now()->format('Y-m-d'),['class'=>'form-control','id'=>'fecresumen']);!!}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group form-group-sm">
                                        <label><i class="fa fa-cog"></i> Acción</label>
                                        <select name="tipo" id="tipo" class="form-control">
                                            <option value="1">Enviar Comprobantes</option>
                                            <option value="3">Anular Comprobantes</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3" id="botones">
                                    <label>&nbsp;</label>
                                    <button type="button" id="btnresumen" class="btn btn-success btn-sm btn-block shadow">
                                        <i class="fa fa-send"></i> GENERAR
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="box box-solid shadow-box">
                <div class="box-header with-border bg-gray">
                    <h3 class="box-title text-dark"><i class="fa fa-list"></i> Historial de Resúmenes Diarios</h3>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover table-striped table-bordered table-resumen mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 150px;">Fec. Comprobantes</th>
                                <th class="text-center" style="width: 150px;">Fec. Envío (SUNAT)</th>
                                <th class="text-center">Ticket de Envío</th>
                                <th class="text-center">Estado SUNAT</th>
                                <th class="text-center">Código</th>
                                <th class="text-center" style="width: 180px;">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="divdetalle">
                            @foreach($comprobantes as $comp)
                            {{-- Aplicamos la clase verde si el código es 0 --}}
                            <tr class="{{ $comp->res_cod_est == '0' ? 'row-success-sunat' : '' }}">
                                <td class="text-center">{{Carbon::parse($comp->res_fec_com)->format('d-m-Y')}}</td>
                                <td class="text-center">
                                    <i class="fa fa-calendar"></i> {{Carbon::parse($comp->res_fec_gen)->format('d-m-Y')}}
                                </td>
                                <td class="text-center"><code>{{$comp->res_ticket}}</code></td>
                                <td class="text-left">
                                    @if($comp->res_cod_est == '0' || $comp->res_est == 'ACEPTADO')
                                        <i class="fa fa-check-circle"></i> {{ $comp->res_est }}
                                    @else
                                        <i class="fa fa-clock-o"></i> {{ $comp->res_est }}
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $comp->res_cod_est == '0' ? 'bg-green' : 'bg-orange' }}">
                                        {{$comp->res_cod_est}}
                                    </span>
                                </td>
                                <td class="text-center">
                                    {{-- Desactivar botón si es 0 --}}
                                    @if($comp->res_cod_est == '0')
                                        <button type="button" class="btn btn-xs btn-default btn-block" disabled title="Procesado Correctamente">
                                            <i class="fa fa-check"></i> Completado
                                        </button>
                                    @else
                                        <a href="/consultarticket/{{$comp->res_id}}" class="btn btn-xs btn-info btn-block shadow-sm">
                                            <i class="fa fa-search"></i> Consultar Ticket
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="box-footer clearfix">
                    <div class="pull-right">
                        {{$comprobantes->render()}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    {!!Form::close()!!}
</section>

@endsection