@extends('layouts.empresas')
@section('contenido')
<script>
  $(document).ready(function()
  {
        // Función para seleccionar/deseleccionar todos los checkboxes
        $("#selectall").on("click", function() {  
            $(".items").prop("checked", this.checked);  
        });  

        // Si todos los checkboxes están seleccionados, marca el "selectall" y viceversa
        $(".items").on("click", function() { 
            if ($(".items").length === $(".items:checked").length) {  
                $("#selectall").prop("checked", true);  
            } else {  
                $("#selectall").prop("checked", false);  
            }  
        });

        // ═══════════════════════════════════════════════════════════════════
        // BOTÓN "REVISAR" - VERIFICAR DOCUMENTOS PREVIAMENTE ACEPTADOS
        // ═══════════════════════════════════════════════════════════════════
        $("#btnRevisar1033").on("click", function() {
            var selectedComprobantes = [];
            $('.items:checked').each(function() {
                selectedComprobantes.push($(this).val());
            });

            if (selectedComprobantes.length === 0) {
                alert('Por favor, selecciona al menos un comprobante para revisar.');
                return;
            }

            if (confirm('Este proceso es para comprobar los documentos aceptados previamente a SUNAT y que por motivo del internet no recio la respuesta de aceptacion.')) {
                $("#divdetalle").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
                
                $.ajax({
                    type: "POST",
                    dataType: 'json',
                    url: '{{ route('comprobantes.revisar1033') }}',
                    data: {
                        _token: '{{ csrf_token() }}',
                        selected_comprobantes_ids: selectedComprobantes
                    },
                }).done(function(respuesta){
                    alert(respuesta.message);
                    window.location.href = "/facturacionelectronica";
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    alert('Error al procesar la solicitud: ' + textStatus + ' - ' + errorThrown);
                    console.error("AJAX Error: ", jqXHR.responseText);
                    window.location.href = "/facturacionelectronica";
                });
            }
        });

        // ═══════════════════════════════════════════════════════════════════
        // BOTÓN "ENVIAR COMPROBANTES" - CON VERIFICACIÓN DE TRUNCAR
        // ═══════════════════════════════════════════════════════════════════
        $("#btnenviar").on("click", function(e) {
            e.preventDefault(); // Evita el envío del formulario por defecto

            var selectedComprobantes = [];
            // Recorre todos los checkboxes con la clase 'items' que estén marcados
            $('.items:checked').each(function() {
                selectedComprobantes.push($(this).val()); // Obtiene el valor (IdCpe_cabecera)
            });

            // Si no hay ningún comprobante seleccionado, muestra un mensaje y detiene la ejecución
            if (selectedComprobantes.length === 0) {
                // Puedes usar un alert simple o un div de alerta más estilizado
                alert('Por favor, selecciona al menos un comprobante para enviar. Puedes usar el "checkbox" para seleccionar todos.');
                return; // Detiene la ejecución de la función
            }

            // Si hay comprobantes seleccionados, procede con la llamada AJAX
            $("#divdetalle").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
            $.ajax({
                type: "POST",
                dataType: 'json',
                url: '/enviarsunat',
                data: {
                    _token: '{{ csrf_token() }}', // Asegúrate de enviar el token CSRF
                    items: selectedComprobantes // Envía los IDs de los comprobantes seleccionados
                },
            }).done(function(respuesta){
                // Puedes añadir un mensaje de éxito antes de redirigir si `respuesta.mensaje` existe
                if (respuesta.message) { // Asumo que el backend puede devolver 'message' o 'mensaje'
                    alert(respuesta.message); 
                } else {
                    alert('Comprobantes enviados exitosamente.');
                }
                window.location.href = "/facturacionelectronica"; // Redirige después de un envío exitoso

            }).fail(function(jqXHR, textStatus, errorThrown) {
                // Manejo de errores de la petición AJAX
                let errorMessage = 'Error al enviar los comprobantes.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMessage = jqXHR.responseJSON.message;
                } else if (errorThrown) {
                    errorMessage += ' Detalles: ' + errorThrown;
                }
                alert(errorMessage);
                console.error("AJAX Error: ", jqXHR.responseText);
                $("#divdetalle").empty(); // Oculta el loader en caso de error
                // Considera si quieres redirigir o no en caso de fallo
                // window.location.href = "/facturacionelectronica"; 
            });
        });


        // ═══════════════════════════════════════════════════════════════════
        // BOTÓN "GENERAR RESUMEN"
        // ═══════════════════════════════════════════════════════════════════
        $("#btnresumen").on("click", function() {
            Swal.fire({
                title: '¿Generar resumen diario?',
                text: 'Se generará el resumen de boletas del día',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, generar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#botones").html('<center><img src="/img/load.gif" width="100px" height="100px"><br><p>Generando resumen...</p></center>');
                    var formulario = $("#frmfacturacion").serializeArray();
                    
                    $.ajax({
                        type: "POST",
                        dataType: 'json',
                        url: '/enviarresumen',
                        data: formulario,
                    }).done(function(respuesta){
                        window.location.href = "/listarresumenes";
                    }).fail(function(jqXHR, textStatus, errorThrown) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error al generar resumen',
                            text: 'No se pudo generar el resumen diario.',
                            confirmButtonText: 'Cerrar'
                        });
                    });
                }
            });
        });

        // ═══════════════════════════════════════════════════════════════════
        // CONFIGURACIÓN DE IMPRESIÓN
        // ═══════════════════════════════════════════════════════════════════
        var href = $('#btnPrint').attr('href');
        
        $("#btnPrint").printPage({
            url: href,
            attr: "href",
            messageBox: false,
        });

        // ═══════════════════════════════════════════════════════════════════
        // TOOLTIP PARA LOS ÍCONOS DE ESTADO SUNAT
        // ═══════════════════════════════════════════════════════════════════
        $('[data-toggle="tooltip"]').tooltip();
    });

</script>

<style>
    input[type=date]::-webkit-inner-spin-button, 
    input[type=date]::-webkit-clear-button,
    input[type=date]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
    }

    /* ESTILOS ELEGANTES AGREGADOS (Sin romper tu código) */
    .shadow-box { 
        box-shadow: 0 4px 15px rgba(0,0,0,0.1); 
        border-radius: 8px; 
        border-top: none !important; 
    }
    .custom-header { 
        background-color: #2c3e50 !important; 
        color: white !important; 
        border-radius: 8px 8px 0 0; 
    }
    .btn-elegant {
        box-shadow: 0 2px 5px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
    }
    .btn-elegant:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    /* Estilos mejorados para la tabla */
    .table-hover tbody tr:hover {
        background-color: #f5f5f5;
        transition: background-color 0.3s;
    }

    /* Estilos más suaves para comprobantes bloqueados/aceptados */
    .comprobante-bloqueado {
        background-color: #fef2f2 !important;
    }

    .comprobante-aceptado {
        background-color: #f0fdf4 !important;
    }

    /* Mejorar visibilidad de los botones */
    .btn-group {
        margin-right: 5px;
    }

    /* Estilo para el mensaje de vencimiento */
    .vencimiento-critico {
        animation: parpadeo 1.5s infinite;
    }

    @keyframes parpadeo {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }

    /* Mejorar el diseño del selectall */
    #selectall {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .items {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
</style>

<section class="content">
   {!!Form::open(array('url'=>'/facturacionelectronica','autocomplete'=>'off','method'=>'POST','name'=>'frmfacturacion','id'=>'frmfacturacion','role'=>'form','files'=>'true'))!!}
    {{Form::token()}}
    
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            @if(session()->has('info'))
                <div class="alert alert-danger alert-dismissible shadow-box">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h4><i class="icon fa fa-ban"></i> Alerta!</h4>
                    {{ session('info') }}
                </div>
            @endif

            @if(session()->has('success'))
                <div class="alert alert-success alert-dismissible shadow-box">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h4><i class="icon fa fa-check"></i> Información!</h4>
                    {{ session('success') }}
                </div>
            @endif
        </div>
    </div>
    
    <div class="row">
        <div class="col-xs-9">
            <div class="box shadow-box">
                <div class="box-header custom-header">
                    <font color="white"><strong><CENTER><i class="fa fa-send"></i> ENVIO INDIVIDUAL O MASIVO DE COMPROBANTES</CENTER></strong></font>
                </div>

                <div class="box-body" style="min-height:120px;">
                    <div class="row">
                        <div class="col-lg-2">
                            <div class="form-group form-group-sm">
                                <label class="control-label" for="fecin"><i class="fa fa-calendar"></i> Desde</label>
                                <input type="date" name="fecin" class="form-control" value="{{$fecin}}">
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group form-group-sm">
                                <label class="control-label" for="fecfin"><i class="fa fa-calendar"></i> Hasta</label>
                                <input type="date" name="fecfin" class="form-control" value="{{$fecfin}}">
                            </div>
                        </div>    
                        <div class="col-lg-2">
                            <div class="form-group form-group-sm">
                                <label class="control-label" for="tipdoc">Documentos</label>
                                <select class="form-control" name="tipdoc">
                                    <option value="0">Todos</option>
                                    @foreach($documentos as $doc)
                                        @if($doc->tdocod =='01' || $doc->tdocod=='03')
                                            @if($doc->tdocod == $tipdoc)
                                                <option selected="selected" value="{{$doc->tdocod}}">{{$doc->tdodes}}</option>
                                            @else
                                                <option value="{{$doc->tdocod}}">{{$doc->tdodes}}</option>
                                            @endif
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>    
                        <div hidden="hidden" class="col-lg-2">
                            <div class="form-group form-group-sm">
                                <label class="control-label" for="estado">ESTADO</label>
                                <select class="form-control" name="estado">
                                    <option value="2">TODOS</option>
                                    <option value="0">No Enviados</option>
                                    <option value="1">Enviados</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-2">
                            <div class="form-group form-group-sm">
                                <label class="control-label" for="estado_sunat">ESTADO SUNAT</label>
                                <select class="form-control" name="estado_sunat">
                                    <option value="2">Todos</option>
                                    <option value="0">ACEPTADOS</option>
                                    <option value="1">OTROS ESTADOS</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="btn-toolbar" role="toolbar" aria-label="...">
                                <div class="btn-group">
                                    <button type="submit" class="btn btn-primary btn-sm btn-elegant">
                                        <i class="fa fa-search"></i> Buscar
                                    </button>
                                </div>
                                <div class="btn-group">
                                    <button type="button" name="btnenviar" id="btnenviar" class="btn btn-success btn-sm btn-elegant">
                                        <i class="fa fa-paper-plane"></i> Enviar Comprobantes
                                    </button>
                                </div>
                                <div class="btn-group">
                                    <button type="button" id="btnRevisar1033" class="btn btn-warning btn-sm btn-elegant">
                                        <i class="fa fa-refresh"></i> Revisar
                                    </button>    
                                </div>                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xs-3">
            <div class="box shadow-box">
                <div class="box-header custom-header">
                    <font color="white"><strong><CENTER>CERTIFICADO DIGITAL</CENTER></strong></font>
                </div>
                
                @if($dias_vencimiento<='15')
                <div class="box-body vencimiento-critico" style="min-height:120px; background-color: #c0392b; border-radius: 0 0 8px 8px;">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group form-group-sm" style="margin-top: 15px;">
                                <font color="white" size="5">
                                    <strong>
                                        <center>
                                            <i class="fa fa-exclamation-triangle fa-2x"></i>
                                            <br>
                                            {{$dias_vencimiento}} DÍAS PARA EXPIRAR
                                        </center>
                                    </strong>
                                </font>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="box-body" style="min-height:120px; background-color: #27ae60; border-radius: 0 0 8px 8px;">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group form-group-sm" style="margin-top: 15px;">
                                <font color="white" size="5">
                                    <strong>
                                        <center>
                                            <i class="fa fa-check-circle fa-2x"></i>
                                            <br>
                                            {{$dias_vencimiento}} DÍAS PARA EXPIRAR
                                        </center>
                                    </strong>
                                </font>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div> 
              
    <div class="row">
        <div class="col-xs-12">
            <div class="box shadow-box">
                <div class="box-body table-responsive">
                    <table id="" class="table table-bordered table-hover">
                        <thead>
                            <tr style="background-color: #f4f4f4;">
                                <th style="text-align: center; vertical-align: middle;">
                                    <input type="checkbox" name="selectall" id="selectall" title="Seleccionar todos">
                                </th>
                                <th colspan="15" style="vertical-align: middle; color: #666;">
                                    <i class="fa fa-hand-o-left"></i> SELECCIONAR TODOS CON UN CLICK
                                </th>
                            </tr>
                            <tr class="custom-header">
                                <th style="text-align: center;">Selec.</th>
                                <th>Fec. Emisión</th>
                                <th>Tipo</th>
                                <th>Serie</th>
                                <th>N°</th>
                                <th>RUC/DNI/Otros</th>
                                <th style="width:210px;">Cliente</th>
                                <th hidden='hidden'>Moneda</th>
                                <th style="text-align: right;">Total</th>
                                <th style="text-align: center;">PDF</th>
                                <th style="text-align: center;">XML</th>
                                <th style="text-align: center;">CDR</th>
                                <th>Detalle SUNAT</th>
                                <th style="text-align: center;">Estado</th>
                                <th hidden='hidden'>Web</th>
                            </tr>
                        </thead>
                        
                        <tbody id="divdetalle">
                            @foreach($comprobantes as $comp)
                            <tr class="
                                @if($comp->ccacodsun == '0') comprobante-aceptado
                                @elseif($comp->ccacodsun == '9999') comprobante-bloqueado
                                @endif
                            ">
                                <td style="text-align: center; vertical-align: middle;">
                                    @if($comp->ccacodsun=='0')
                                        <input type="checkbox" disabled="disabled" value="{{$comp->IdCpe_cabecera}}" title="Ya fue aceptado por SUNAT">
                                    @else
                                        <input type="checkbox" name="items[]" class="items" value="{{$comp->IdCpe_cabecera}}">
                                    @endif
                                </td>
                                <td style="vertical-align: middle;">{{Carbon::parse($comp->ccafem)->format('d-m-Y')}}</td>
                                <td style="vertical-align: middle;">{{$comp->des_doc}}</td>
                                <td style="vertical-align: middle;"><strong>{{$comp->serdoc}}</strong></td>
                                <td style="vertical-align: middle;"><strong>{{$comp->numdoc}}</strong></td>
                                <td title='{{$comp->tdides}}' style="vertical-align: middle;">{{$comp->ccandi}}</td>
                                <td style="width:210px; vertical-align: middle;">{{$comp->ccanom}}</td>
                                <td hidden='hidden' style="vertical-align: middle;">{{$comp->monnom}}</td>
                                <td align="right" style="vertical-align: middle;"><strong>{{number_format($comp->ccaitv,'2','.',',')}}</strong></td>
                                
                                <td style="text-align: center; vertical-align: middle;">
                                    <a href="/descargar/{{$comp->IdCpe_cabecera}}/pdf" title="Descargar PDF">
                                        <i class="fa fa-file-pdf-o fa-lg" style="color: #d9534f;"></i>
                                    </a>
                                </td>

                                <td style="text-align: center; vertical-align: middle;">
                                    <a href="/descargar/{{$comp->IdCpe_cabecera}}/xml" title="Descargar XML">
                                        <i class="fa fa-file-code-o fa-lg" style="color: #5bc0de;"></i>
                                    </a>
                                </td>
                            
                                <td style="text-align: center; vertical-align: middle;">
                                    <a href="/descargar/{{$comp->IdCpe_cabecera}}/cdr" title="Descargar CDR">
                                        <i class="fa fa-file-archive-o fa-lg" style="color: #f0ad4e;"></i>
                                    </a>
                                </td>

                                <td style="vertical-align: middle;">
                                    @if($comp->ccacodsun == '9999')
                                        <span style="color: red; font-weight: bold;">
                                            <i class="fa fa-lock"></i> {{substr($comp->ccadessun,0,254)}}
                                        </span>
                                    @else
                                        {{substr($comp->ccadessun,0,254)}}
                                    @endif
                                </td>

                                @if($comp->ccacodsun=='0')
                                <td style="text-align: center; vertical-align: middle;">
                                    <a data-toggle="tooltip" title="{{$comp->ccadessun}}">
                                        <img src="/icon/check.png" height="20px" width="20px" alt="Aceptado">
                                    </a>
                                </td>
                                @elseif($comp->ccacodsun=='8')
                                <td style="text-align: center; vertical-align: middle;">
                                    <a data-toggle="tooltip" title="ANULADO">
                                        <img src="/icon/error.png" height="20px" width="20px" alt="Anulado">
                                    </a>
                                </td>
                                @elseif($comp->ccacodsun == '9999')
                                <td style="text-align: center; vertical-align: middle;">
                                    <a data-toggle="tooltip" title="SISTEMA BLOQUEADO - {{$comp->ccadessun}}">
                                        <img src="/icon/error.png" height="20px" width="20px" alt="Bloqueado">
                                    </a>
                                </td>
                                @elseif($comp->ccacodsun >'100' && $comp->ccacodsun <'1999')
                                <td style="text-align: center; vertical-align: middle;">
                                    <a href="/enviarcomprobante/{{$comp->IdCpe_cabecera}}" data-toggle="tooltip" title="{{$comp->ccadessun}}">
                                        <img src="/icon/iconwarning.png" height="20px" width="20px" alt="Observado">
                                    </a>
                                </td>
                                @elseif($comp->ccacodsun > '2000' && $comp->ccacodsun <'3999')
                                <td style="text-align: center; vertical-align: middle;">
                                    <a data-toggle="tooltip" title="{{$comp->ccadessun}}">
                                        <img src="/icon/error.png" height="20px" width="20px" alt="Rechazado">
                                    </a>
                                </td>
                                @elseif($comp->ccacodsun > '4000')
                                <td style="text-align: center; vertical-align: middle;">
                                    <a data-toggle="tooltip" title="{{$comp->ccadessun}}">
                                        <img src="/icon/checkobs.png" height="20px" width="20px" alt="Con observaciones">
                                    </a>
                                </td>
                                @else
                                <td style="text-align: center; vertical-align: middle;">
                                    <span title="Pendiente de envío">-</span>
                                </td>
                                @endif

                                <td hidden='hidden' style="text-align: center; vertical-align: middle;">
                                    <a href="/enviarservidor/{{$comp->IdCpe_cabecera}}" title="SUBIR COMPROBANTE A LA NUBE">
                                        <img src="/icon/cloud.png" height="20px" width="20px" alt="Nube">
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>    
                <div class="box-footer">
                    {{$comprobantes->render()}}
                </div>
            </div>    
        </div>
    </div>
    {{Form::close()}}
</section>

@endsection