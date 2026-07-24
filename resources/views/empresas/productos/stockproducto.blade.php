@extends('layouts.empresas')
@section('contenido')

<script>
$(document).ready(function()
{
    // LÓGICA EXISTENTE: Botones de Exportación (PDF y Excel)
    $("#btnExcel").click(function() {
        var accion = $(this).attr('dir');
        $('#frmReporte').attr('action', accion);
        $('#frmReporte').submit();
    });
    
    $("#btnPDF").click(function() {
        var accion = $(this).attr('dir');
        $('#frmReporte').attr('action', accion);
        $('#frmReporte').attr('target', '_blank'); // <--- Abre en nueva pestaña
        $('#frmReporte').submit();
        $('#frmReporte').removeAttr('target'); // <--- Restaura el form
    });

    $("#btnTicketVPS").click(function() {
        var accion = $(this).attr('dir');
        $('#frmReporte').attr('action', accion);
        $('#frmReporte').attr('target', '_blank'); // <--- Abre en nueva pestaña
        $('#frmReporte').submit();
        $('#frmReporte').removeAttr('target'); // <--- Restaura el form
    });
    
    $("#btnExcelPro").click(function() {
        var accion = $(this).attr('dir');
        $('#frmReporte').attr('action', accion);
        $('#frmReporte').submit();
    });
    
    // LÓGICA EXISTENTE: Cambio de Sucursal (Carga Almacenes)
    $("#sucursal").change(function() {
        var sucursal = $("#sucursal").val();
        $("#divalmacen").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
        $.ajax({
            type: "GET",
            dataType: 'json',
            url: "/buscaralmacen/"+sucursal,
        }).done(function(respuesta){
            $("#divalmacen").html(respuesta.vista);
        });
    });

    // ------------------------------------------------------------------
    // LÓGICA PRINCIPAL: BOTÓN BUSCAR (EXISTENTE)
    // ------------------------------------------------------------------
    $("#btnBuscar").on("click", function() {
        var formulario = $("#frmReporte").serializeArray();
        formulario.push({name: 'accion', value: 'buscar'}); // Añadido para claridad, aunque el controlador asume 'buscar' por defecto.
        $("#imgload").show();
        $(".botones").hide();
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: '/buscarreporte',
            data: formulario,
        }).done(function(respuesta){
            $("#imgload").hide();
            $(".botones").show();
            $("#divreporte").html(respuesta.vista);
        }).fail(function(){
            $("#imgload").hide();
            $(".botones").show();
            alert('Ocurrió un error al buscar el reporte.');
        });
    });

    // ------------------------------------------------------------------
    // LÓGICA DE IMPRESIÓN TÉRMICA (CORREGIDA Y MOVIDA AQUÍ)
    // ------------------------------------------------------------------
    $("#btnImprimirTermica").on("click", function() {
        // Obtener los datos del formulario (filtros de reporte)
        var formulario = $("#frmReporte").serializeArray();
        
        // CLAVE: Añadir el parámetro de acción para la impresión
        formulario.push({name: 'accion', value: 'imprimir_termica'});
        
        // Mostrar carga y ocultar botones
        $("#imgload").show();
        $(".botones").hide(); 
        
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: '/buscarreporte', // Endpoint que maneja ReportesController
            data: formulario,
        }).done(function(respuesta){
            $("#imgload").hide();
            $(".botones").show();

            if (respuesta.success) {
                alert('Impresión enviada a la impresora térmica exitosamente.');
            } else {
                 alert('Error de impresión: ' + (respuesta.message || 'Verifique la conexión con la impresora.'));
            }
            
        }).fail(function(jqXHR, textStatus, errorThrown) {
            $("#imgload").hide();
            $(".botones").show();
            alert('Ocurrió un error en la solicitud de impresión.');
        });
    });       

 }); // CIERRE DEL $(document).ready
</script>

<section class="content">
<div class="row">
    <div class="col-xs-12">
      <div class="box">
        
        <div class="box-header" style="background-color:#337ab7;">
              <font size="2" color="white"><strong><center>CONSULTAR STOCK PRODUCTOS DE ALMACEN</center></strong></font>
          </div>
          
          <div class="box-body">
              @include('empresas.productos.searchstock') 
          </div>

          <div class="box-body table-responsive" id="divreporte" >
      
          </div>
    
  </div>
</div>
</div>
</section>
@endsection