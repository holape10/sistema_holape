@extends('layouts.empresas')
@section('contenido')

<script type="text/javascript">
	
	$(document).ready(function()
   {


   
   	  $("#btnGenPdf").click(function() {
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
        var accion = $(this).attr('dir');

        $('#frmReporte').attr('action', accion);
  
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
   	   
       $("#btnBuscar").on("click", function() {
      	
       	
      var formulario = $("#frmReporte").serializeArray();
      $("#imgload").show();
      $(".botones").hide();
      $.ajax({
        type: "POST",
        dataType: 'json',
        url: '/generarreporteordenes',
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
        url: '/reportecompras',
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
        		
	      
	           			@include('empresas.reportes.buscar_reportes_taller')
	           		
	           		
	           		
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