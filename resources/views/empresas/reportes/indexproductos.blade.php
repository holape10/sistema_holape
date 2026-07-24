@extends('layouts.empresas')
@section('contenido')
<script type="text/javascript">
     $(document).ready(function()
    {
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
});
     </script>
<section class="content">	
	<div class="row">
        <div class="col-xs-12">
        	<div class="box">
        		<div class="box-header box-success" style="background-color:#00a65a;">
        			<font color="white" size="4"><center><strong>REPORTES DE COMPROBANTES</strong></center></font>
        		</div>
	           	<div class="box-body">
	           		@include('empresas.reportes.buscar')
	           	</div>
	         </div>
	    </div>
	</div> 

	<?php 
	$totalproductos=0;
	?>
	@foreach($productos as $producto)
		<?php
			$totalproductos = $totalproductos + $producto->cantidad;
		?>
	@endforeach
	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
	            			
					<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm">
							<thead>
								<tr>
									<th colspan="3"><center><strong>REPORTE DE PRODUCTOS MAS VENDIDOS DESDE: {{$fecin}} HASTA {{$fecfin}} @if(!empty($datocli)) - Cliente: {{$datocli->clinom}} @endif</strong></center></th>
									<th ><center><strong>TOTAL PRODUCTOS</strong></center></th>
								</tr>
								<tr>
									<th colspan="3"><center></center>
									<th><center><strong>{{$totalproductos}}</strong></center>
								</tr>
								<tr>
									<th colspan="2">DESCRIPCION</th>
									<th>UM</th>
									<th>CANTIDAD</th>
								</tr>
							</thead>
							<tbody>
								@foreach($productos as $producto)
								<tr>
									<td colspan="2">{{$producto->cdedes}}</td>
									<td>{{$producto->umecod}}</td>
									<td>{{$producto->cantidad}}</td>
								</tr>
								@endforeach
							</tbody>
						</table><br>
					</div>	
					
				</div>	
			</div>
		</div>
</section>


<script>

$( document ).ready(function() {
	$("#fecin").change(function(){
	  var fechain = $("#fecin").val();
	 $('#fechain').val(fechain);
	 
    
	});

	$("#fecfin").change(function(){
		var fechafin = $("#fecfin").val();
		$('#fechafin').val(fechafin);
	});

	
});

$('#btnPrint').click(function(){
		
		var fecfin = $("#fechafin").val();
        var fecin = $("#fechain").val();
		 
		$("#btnPrint").printPage({
		  
          url: "/imprimircierre/"+fecfin+"/"+fecin,
          attr: "href",
          messageBox:false
          
        })
		
	});

</script>

@endsection