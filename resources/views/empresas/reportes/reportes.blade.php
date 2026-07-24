@extends('layouts.empresas')
@section('contenido')

  <style>
    input[type=date]::-webkit-inner-spin-button, 
    input[type=date]::-webkit-clear-button,
    input[type=date]::-webkit-outer-spin-button { 
      -webkit-appearance: none; 
      margin: 0; 
    }

  </style>

<script type="text/javascript">
	
	$(document).ready(function()
   {


   
   	  $("#btnGenPdf").click(function() {
         $("#divreporte").empty();
        var accion = $(this).attr('dir');

        $('#frmReporte').attr('action', accion);
        $('#frmReporte').submit();
    });


   	  $("#btnGenTicket").click(function() {

        var accion = $(this).attr('dir');

        $('#frmReporte').attr('action', accion);
        $('#frmReporte').attr('target', '_blank');
        $('#frmReporte').submit();
    });

   	   $("#btnExportar").click(function() {

            $("#divreporte").empty();
        var accion = $(this).attr('dir');

        $('#frmReporte').attr('action', accion);
      $('#frmReporte').attr('target', '_blank'); 
        $('#frmReporte').submit();
    });



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


   	   $("#btnTicketVen").click(function() {
         
              
               
             
              
     		 var formulario = $("#frmReporte").serializeArray();
			 
			      $.ajax({
			        type: "POST",
			        dataType: 'json',
			        url: '/imprimirreporteventas',
				      data: formulario,
                }).done(function(respuesta){
               
                });

      });
   	   
  $("#btnBuscarVentas").on("click", function() {
      	
       	
      var formulario = $("#frmReporte").serializeArray();
      $("#imgload").show();
      $(".botones").hide();
      $.ajax({
        type: "POST",
        dataType: 'json',
        url: '/generarreporteventas',
        data: formulario,
      }).done(function(respuesta){

      	$("#divreporte").html(respuesta.vista);

      });

    });

     $("#btnBuscarCompras").on("click", function() {
      	
       	
      var formulario = $("#frmReporte").serializeArray();
      $("#imgload").show();
      $(".botones").hide();
      $.ajax({
        type: "POST",
        dataType: 'json',
        url: '/generarreportecompras',
        data: formulario,
      }).done(function(respuesta){

      	$("#divreporte").html(respuesta.vista);

      });

    });

       $("#btnBuscar").on("click", function() {
      	
       	
      var formulario = $("#frmReporte").serializeArray();
      $("#imgload").show();
      $(".botones").hide();
      $.ajax({
        type: "POST",
        dataType: 'json',
        url: '/buscarreporte',
        data: formulario,
      }).done(function(respuesta){

      
      	$("#divreporte").html(respuesta.vista);


         

      });



       

    });


        $("#btnBuscarGastos").on("click", function() {
      	
       	
      var formulario = $("#frmReporte").serializeArray();
      $("#imgload").show();
      $(".botones").hide();
      $.ajax({
        type: "POST",
        dataType: 'json',
        url: '/generarreportegastos',
        data: formulario,
      }).done(function(respuesta){

      	$("#divreporte").html(respuesta.vista);

      });

    });
        

   
});
</script>

  


<section class="content">	
	<div class="row">
        <div class="col-xs-12">
        	<div class="box">
        		
	      
	           		@if($tipo=='1')
	           			@include('empresas.reportes.buscar_ventas')
	           		@endif

	           		@if($tipo=='2')
	           			@include('empresas.reportes.buscar_ventas_vendedor')
	           		@endif

	           		@if($tipo=='3')
	           			@include('empresas.reportes.buscar_ventas_clientes')
	           		@endif

	           		@if($tipo=='4')
	           			@include('empresas.reportes.buscar_proformas')
	           		@endif

	           		@if($tipo=='5')
	           			@include('empresas.reportes.buscar_pedidos')
	           		@endif

	           		@if($tipo=='6')
	           			@include('empresas.reportes.buscar_ventas_productos')
	           		@endif

	           		@if($tipo=='7')
	           			@include('empresas.reportes.buscar_ranking_productos')
	           		@endif

	           		@if($tipo=='8')
	           			@include('empresas.reportes.buscar_rentabilidad')
	           		@endif
					     
               @if($tipo=='9')
	           			@include('empresas.reportes.buscar_comisiones_productos')
	           		@endif
	           		@if($tipo=='10')
	           			@include('empresas.reportes.buscar_comisiones_vendedor')
	           		@endif

	           		@if($tipo=='11')
	           			@include('empresas.reportes.buscar_compras')
	           		@endif

	           		@if($tipo=='12')
	           			@include('empresas.reportes.buscar_compras_proveedor')
	           		@endif
	           		
	           		@if($tipo=='13')
	           			@include('empresas.reportes.buscar_compras_producto')
	           		@endif

	           		@if($tipo=='14')
	           			@include('empresas.reportes.gastos.buscar_gastos')
	           		@endif

	           		@if($tipo=='20')
	           			@include('empresas.reportes.buscar_reporte_pdf_productos')
	           		@endif
	           		
	           		
	           	</div>
	         </div>
	    </div>

	<div class="row">
		<div class="col-xs-12">
			<div class="box table table-responsive">
	       <div class="box-body" id="divreporte">
							
					</div>	
				</div>
		</div>
	</div>
</section>




@endsection