@extends('layouts.externo')
@section('contenido')

<script>
$(document).ready(function()
{       

 $("#frmReporte").keypress(function(e) {
    if (e.which == 13) {
      return false;
    }
  })

	  

     $("#btnExcel").click(function() {

          
          var accion = $(this).attr('dir');

          $('#frmReporte').attr('action', accion);
          $('#frmReporte').submit();
        });
        
          $("#btnPDF").click(function() {

          
          var accion = $(this).attr('dir');

          $('#frmReporte').attr('action', accion);
          $('#frmReporte').submit();
        });
        


     $("#btnExcelPro").click(function() {

          
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
    

     $("#btnBuscar").on("click", function() {
      	
   
      var formulario = $("#frmReporte").serializeArray();
      $("#imgload").show();
      $(".botones").hide();
      $.ajax({
        type: "POST",
        dataType: 'json',
        url: '/consultastock',
        data: formulario,
      }).done(function(respuesta){

      	$("#divreporte").html(respuesta.vista);


         

      });



       

    });


  



   /*   $("#cmbCatId").change(function() {
         	  
         	  	$('#promocion').val('Todos');
         	$('#buspro').val('');
              $('#formstock').submit();
        

        });*/

 });

</script>
<section class="content">
<div class="row">
    <div class="col-xs-12">
    	<div class="box">
    		
    		<div class="box-header" style="background-color:blue;">
          		<font size="2" color="white"><strong><center>CONSULTAR STOCK PRODUCTOS</center></strong></font>
          </div>
          
          <div class="box-body">
          		@include('empresas.productos.searchconsultastock') 
          </div>

	       	<div class="box-body table-responsive" id="divreporte" >
			
		      </div>
		
	</div>
</div>
</div>
</section>
@endsection
