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

        // Lógica para el botón "Revisar" (ya estaba, la mantengo)
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

        // ************************************************************
        // LÓGICA MODIFICADA PARA EL BOTÓN "ENVIAR COMPROBANTES"
        // ************************************************************
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


    // Lógica para el botón "GENERAR RESUMEN" (ya estaba, la mantengo)
    $("#btnresumen").on("click", function() {
        $("#botones").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
        var formulario = $("#frmfacturacion").serializeArray();
       
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: '/enviarresumen',
            data: formulario,
        }).done(function(respuesta){
            window.location.href = "/listarresumenes";
        });
    });

    var href = $('#btnPrint').attr('href');
    
    $("#btnPrint").printPage({
        
         
          url: href,
          attr: "href",
          messageBox:false,
          
    })
});

</script>

                        <style>
                            input[type=date]::-webkit-inner-spin-button, 
                            input[type=date]::-webkit-clear-button,
                            input[type=date]::-webkit-outer-spin-button { 
                              -webkit-appearance: none; 
                              margin: 0; 
                            }

                        </style>

    <section class="content">
   {!!Form::open(array('url'=>'/facturacionelectronica','autocomplete'=>'off','method'=>'POST','name'=>'frmfacturacion','id'=>'frmfacturacion','role'=>'form','files'=>'true'))!!}
    {{Form::token()}}
        <div class="row">
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
        @if(session()->has('info'))
            <div class="alert alert-danger">
              <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
              <strong>Alerta!</strong> {{ session('info') }}
            </div>
        @endif


        @if(session()->has('success'))
            <div class="alert alert-success">
              <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
              <strong>InformaciÃ³n!</strong> {{ session('success') }}
            </div>
        @endif
    </div>
</div>
    
    <div class="row">
            <div class="col-xs-9">
                <div class="box">
                    <div class="box-header" style="background-color:blue;">
                        <font color="white"><strong><CENTER>ENVIO INDIVIDUAL O MASIVO DE COMPROBANTES</CENTER></strong></font>
                    </div>

                    
      
                    <div class="box-body" style="height:120px;">
                        

                        <div class="row">
                            <div class="col-lg-2">
                                <div class="form-group form-group-sm">
                                     <label class="control-label" for="fecin">Desde </label>
                                     <input type="date" name="fecin" class="form-control" value="{{$fecin}}">
                                    
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group form-group-sm">

                                    <label class="control-label" for="fecfin">Hasta </label>
                                     <input type="date" name="fecfin" class="form-control" value="{{$fecfin}}">
                                
                                </div>
                            </div>  
                            <div class="col-lg-2">
                                <div class="form-group form-group-sm">
                                    <label class="control-label" for="fecfin">Documentos </label>
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
                                    <label class="control-label" for="fecfin">ESTADO</label>
                                    <select class="form-control" name="estado">
                                        
                                        <option value="2">TODOS</option>
                                        <option value="0">No Enviados</option>
                                        <option value="1">Enviados</option>
                                    </select>
                                
                                </div>
                            </div>

                            <div class="col-lg-2">
                                <div class="form-group form-group-sm">
                                    <label class="control-label" for="fecfin">ESTADO SUNAT</label>
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
                                    <button type="submit" class=" btn btn-primary btn-sm">Buscar</button>
                                </div>
                                <div class="btn-group">
                                    <button type="button" name="btnenviar" id="btnenviar" class="btn btn-primary btn-sm">Enviar Comprobantes</button>
                                </div>
                                <div>
                                    <button type="button" id="btnRevisar1033" class="btn btn-warning" style="margin-left: 10px;">Revisar</button>   
                                </div>                              
                                </div>
                            </div>

                        </div>
                        
                    </div>
                </div>
            </div>

            <div class="col-xs-3">
                <div class="box">
                    <div class="box-header" style="background-color:blue;">
                        <font color="white"><strong><CENTER>EXPIRACIÓN CERTIFICADO DIGITAL</CENTER></strong></font>
                    </div>
                    
                    @if($dias_vencimiento<='15')
                    <div class="box-body" style="height:120px;background:red">
                    
                        <div class="row">
                            <div class="col-lg-12">
                                 <div class="form-group form-group-sm">
                                    
                                          <font color="white" size="6"><strong><center>{{$dias_vencimiento}} DÍAS PARA EXPIRAR</center></strong></font>
                                        
                                 </div>
                            </div>
                        </div>

                        
                    </div>
                    @else
                    <div class="box-body" style="height:120px;background:green">
                    
                            <div class="row">
                            <div class="col-lg-12">
                                 <div class="form-group form-group-sm">
                                    
                                         <font color="white"  size="6"> <strong><center>{{$dias_vencimiento}} DÍAS PARA EXPIRAR</center></strong> </font>
                                        
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
                <div class="box">
                    <div class="box-body table-responsive" >
                            <table id=""  class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" name="selectall" id="selectall"  ></th>
                                    <th colspan="15"> < == SELECCIONAR CON UN CLICK</th>
                                </tr>
                                <tr>
                                    <th>Selec.</th>
                                    <th>Fec. Emision</th>
                                    <th>Tipo</th>
                                    <th>Serie</th>
                                    <th>N°</th>
                                    <th>RUC/DNI/Otros</th>
                                    <th style="width:210px;">Cliente</th>
                                    
                                    <th hidden='hidden'>Moneda</th>
                                    <th>Total</th>
                                    <th>A4</th>
                                    <th>XML</th>
                                    <th>CDR</th>
                                    
                                    <th>Detalle SUNAT</th>
                                    <th>SUNAT</th>
                                    <th hidden='hidden'>Web</th>
                                
                            
                                    
                                
                                </tr>
                            </thead>
                            
                            <tbody id="divdetalle">
                                @foreach($comprobantes as $comp)
                                <tr>
                                    <td>@if($comp->ccacodsun=='0')<input type="checkbox" disabled="disabled"  value="{{$comp->IdCpe_cabecera}}" >@else<input type="checkbox" name="items[]" class="items" value="{{$comp->IdCpe_cabecera}}" @endif</td>
                                    <td>{{Carbon::parse($comp->ccafem)->format('d-m-Y')}}</td>
                                    <td>{{$comp->des_doc}}</td>
                                    <td>{{$comp->serdoc}}</td>
                                    <td>{{$comp->numdoc}}</td>
                                    <td title='{{$comp->tdides}}'>{{$comp->ccandi}}</td>
                                    <td style="width:210px;">{{$comp->ccanom}}</td>
                                
                                    <td hidden='hidden'>{{$comp->monnom}}</td>
                                    <td align="right">{{number_format($comp->ccaitv,'2','.',',')}}</td>
                                    
                                        
                                            <td><a href="/descargar/{{$comp->IdCpe_cabecera}}/pdf"><center><i class="fa fa-file-excel-o fa-lg"></i></center></a></td>

                                        <td><a href="/descargar/{{$comp->IdCpe_cabecera}}/xml"><center><i class="fa fa-file-excel-o fa-lg"></i></center></a></td>
                                
                                        <td><a href="/descargar/{{$comp->IdCpe_cabecera}}/cdr"><center><i class="fa fa-file-pdf-o fa-lg"></i></center></a></td>
                                
                                    

                                        <td >{{substr($comp->ccadessun,0,254)}}</td>
                                        @if($comp->ccacodsun=='0')
                                        <td>
                                            <a><center><img src="/icon/check.png" title="{{$comp->ccadessun}}" height="20px" width="20px"></center></a>
                                        </td>
                                        @elseif($comp->ccacodsun=='8')
                                        <td>
                                            <a><center><img src="/icon/error.png" title="ANULADO" height="20px" width="20px"></center></a>
                                        </td>

                                        @elseif($comp->ccacodsun >'100' && $comp->ccacodsun <'1999')
                                        <td>
                                            <a href="/enviarcomprobante/{{$comp->IdCpe_cabecera}}"><center><img src="/icon/iconwarning.png" title="{{$comp->ccadessun}}" height="20px" width="20px"></center></a>
                                        </td>

                                        @elseif($comp->ccacodsun > '2000' && $comp->ccacodsun <'3999')
                                        <td>
                                            <a><center><img src="/icon/error.png" title="{{$comp->ccadessun}}" height="20px" width="20px"></center></a>
                                        </td>
                                        @elseif($comp->ccacodsun > '4000')
                                        
                                        <td>
                                            <a><center><img src="/icon/checkobs.png" title="{{$comp->ccadessun}}" height="20px" width="20px"></center></a>
                                        </td>
                                        @else

                                        <td>
                                            
                                        </td>
                                        @endif
                                        <td hidden='hidden'>
                                            <a href="/enviarservidor/{{$comp->IdCpe_cabecera}}"><center><img src="/icon/cloud.png" title="SUBIR COMPROBANTE A LA NUBE" height="20px" width="20px"></center></a>
                                        </td>
                                    
                                
                                        
                                </tr>
                                
                                @endforeach
                            </tbody>
                        </table><br>
                    </div>  
                    {{$comprobantes->render()}}
                </div>  
            </div>
        </div>
     {{Form::close()}}
    </section>

@endsection