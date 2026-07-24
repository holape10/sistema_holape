@extends('layouts.empresas')

@section('contenido')
<style>
    /* Pequeños ajustes para la vista móvil y el formato JSON */
    .form-group { margin-bottom: 15px; }
    .json-preview {
        background-color: #2c3e50;
        color: #a6e22e; /* Color estilo consola/hacker para el JSON */
        border-radius: 8px;
        padding: 15px;
        max-height: 300px;
        overflow-y: auto;
        overflow-x: auto; /* Evita que el JSON rompa la pantalla del celular */
        font-family: monospace;
        box-shadow: inset 0 2px 5px rgba(0,0,0,0.5);
    }
</style>

<section class="content" style="padding-top: 20px;">
    <div class="row">
        <div class="col-md-12 col-xs-12">
            <div class="box shadow-box">
                <div class="box-header custom-header">
                    <h3 class="box-title" style="color: white; font-weight: bold;">
                        <center><i class="fa fa-search"></i> CONSULTA INDIVIDUAL DE CPE (SUNAT)</center>
                    </h3>
                </div>
                
                <div class="box-body" style="padding: 25px;">
                    <form action="{{ url('consultar-cpe') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 form-group">
                                <label><i class="fa fa-building"></i> RUC Emisor</label>
                                <input type="text" name="ruc_emisor" class="form-control" required maxlength="11" placeholder="Ej. 20123456789">
                            </div>
                            
                            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 form-group">
                                <label><i class="fa fa-file-text-o"></i> Tipo Comprobante</label>
                                <select name="tipo_documento" class="form-control">
                                    <option value="01">Factura (01)</option>
                                    <option value="03">Boleta (03)</option>
                                    <option value="07">Nota de Credito (07)</option>
                                    <option value="08">Nota de Debito (08)</option>
                                </select>
                            </div>
                            
                            <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6 form-group">
                                <label><i class="fa fa-barcode"></i> Serie</label>
                                <input type="text" name="serie" class="form-control" placeholder="Ej. F001" required>
                            </div>
                            
                            <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6 form-group">
                                <label><i class="fa fa-hashtag"></i> Número</label>
                                <input type="text" name="numero" class="form-control" placeholder="Ej. 123" required>
                            </div>
                        </div>

                        <div class="row" style="margin-top: 10px;">
                            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 form-group">
                                <label><i class="fa fa-calendar"></i> Fecha Emisión</label>
                                <input type="date" name="fecha" class="form-control" required>
                            </div>
                            
                            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 form-group">
                                <label><i class="fa fa-money"></i> Monto Total</label>
                                <input type="number" step="0.01" name="total" class="form-control" placeholder="0.00" required>
                            </div>
                            
                            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 form-group">
                                <label class="hidden-xs">&nbsp;</label> <button type="submit" class="btn btn-primary btn-block btn-elegant" style="padding: 8px; font-weight: bold;">
                                    <i class="fa fa-cloud-download"></i> Consultar a SUNAT
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if(isset($resultado))
    <div class="row" style="margin-top: 10px;">
        <div class="col-md-12 col-xs-12">
            <div class="box shadow-box">
                <div class="box-header" style="background-color: #f4f4f4; border-radius: 8px 8px 0 0; border-bottom: 1px solid #ddd;">
                    <h4 style="margin: 0; font-weight: bold; color: #333;"><i class="fa fa-info-circle"></i> Resultado de la Consulta</h4>
                </div>
                
                <div class="box-body" style="padding: 20px;">
                    @if($resultado->success && isset($resultado->data))
                        <div class="alert alert-success alert-elegant">
                            <h4 style="margin-bottom: 15px;"><i class="icon fa fa-check-circle"></i> ¡Comprobante encontrado!</h4>
                            <div style="font-size: 15px; line-height: 1.8;">
                                {{-- Corregimos el nombre de la propiedad a español como pide la API --}}
                                <i class="fa fa-caret-right"></i> <strong>Estado Comprobante:</strong> <span class="label label-success" style="font-size: 13px;">{{ $resultado->data->comprobante_estado_descripcion ?? 'N/A' }}</span> <br>
                                <i class="fa fa-caret-right"></i> <strong>Estado Empresa:</strong> {{ $resultado->data->empresa_estado_descripcion ?? 'N/A' }} <br>
                                <i class="fa fa-caret-right"></i> <strong>Condición Empresa:</strong> {{ $resultado->data->empresa_condicion_descripcion ?? 'N/A' }}
                            </div>
                        </div>
                        
                        <hr style="border-top: 1px dashed #ccc;">
                        
                        <label><i class="fa fa-code"></i> Detalle técnico (JSON de respuesta):</label>
                        <pre class="json-preview">{{ json_encode($resultado->data, JSON_PRETTY_PRINT) }}</pre>
                    @else
                        <div class="alert alert-danger alert-elegant">
                            <h4><i class="icon fa fa-ban"></i> Error en la consulta</h4>
                            {{ $resultado->message ?? 'No se pudo obtener respuesta de la API de SUNAT. Verifique los datos o su conexión.' }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</section>
@endsection