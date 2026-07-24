@extends('layouts.empresas')
@section('contenido')

<section class="content">	
	<div class="row">
        <div class="col-xs-12">
        	<div class="box">
        		<div class="box-header box-success" style="background-color:#00a65a;">
        			<font color="white" size="4"><center><strong>REPORTES DE COMPROBANTES</strong></center></font>
        		</div>
	           	<div class="box-body">
	           		@include('empresas.reportes.buscarcomprobantes')
	           	</div>
	         </div>
	    </div>
	</div> 

	<?php
		$total =0;
	?>
	@foreach($comprobantes as $comprobante)

	<?php
		if($comprobante->estado =='Registrado'){
			$total = $total + $comprobante->total;	
		}
		
	?>

	@endforeach
	<div class="row">
		<div class="col-xs-12">
			<div class="box">
	            	<div class="box-body">
						<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm">
							<thead>
								<tr>
									<th colspan="7"><center><strong>REPORTE DE VENTAS DESDE: {{$fecin}} HASTA {{$fecfin}}</strong></center>
									<th>TOTAL COMPRAS</th>
								</tr>
								<tr>
									<th colspan="7"></th>
									<th>{{number_format($total,'2','.',',')}}</th>
									
								</tr>

								<tr>
									<th>ESTADO</th>
									<th>FECHA</th>
									<th>COMPROBANTE</th>
									<th>SERIE</th>
									<th>N°</th>
									<th style="width:210px;">PROVEEDOR</th>
									<th>MONEDA</th>
									<th>TOTAL</th>
												
								</tr>
							</thead>
							
							<tbody>
								@foreach($comprobantes as $comprobante)
								<tr>
									<td>{{$comprobante->estado}}</td>
								 	<td>{{Carbon::parse($comprobante->FECHA)->format('d-m-Y')}}</td>
									<td>{{$comprobante->comprobante}}</td>
									<td>{{$comprobante->serie}}</td>
									<td>{{$comprobante->numero}}</td>
									<td>{{$comprobante->cliente}}</td>
									<td>{{$comprobante->moneda}}</td>
									<td>{{number_format($comprobante->total,'2','.',',')}}</td>
								
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