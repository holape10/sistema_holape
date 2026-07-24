@extends('layouts.empresas')
@section('contenido')

<section class="content">	
	<div class="row">
        <div class="col-xs-12">
        	<div class="box">
        		<div class="box-header box-success" style="background-color:#585858;">
        			<strong><font color="white"><center>REPORTES DE COMPROBANTES - ESTACIONAMIENTOSS</center></font> </strong>
        		</div>
	           	<div class="box-body">
	           		@include('empresas.reportes.buscarutilidad')
	           	</div>
	         </div>
	    </div>
	</div> 
	@php

		$totalgastos =0;
		$totalventas = $ventascontado + $ventastarjeta + $ventascredito + $transferencias;
		foreach($gastos as $total){

			$totalgastos = $totalgastos + $total->total;
		}


	@endphp
		<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
	            		
	            		<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm">
						
							<tbody>
								<tr>
									<td style="width:800px"><FONT><STRONG>TOTAL VENTAS  S/. {{number_format($totalventas/1.1055,'2','.',',')}}</STRONG></FONT></td>
							
									<td style="width:800px"><FONT><STRONG>TOTOAL GASTOS  S/. {{$totalgastos}}</STRONG></FONT></td>
								
									
							
									<td style="width:800px"><FONT><STRONG>UTILIDAD  S/. {{number_format(($totalventas/1.1055)-$totalgastos,'2','.',',')}}</STRONG></FONT></td>
									<td></td>
								
								</tr>
							
							</tbody>
						</table>

						<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm">

							<thead>
								<tr>
									<th colspan="2"><center><strong>REPORTE DE VENTAS DESDE: {{$fecin}} HASTA {{$fecfin}}</strong></center></th>
	
								</tr>
								
								
							</thead>
							<tbody>
								<tr>
									<td style="width:800px">TOTAL VENTAS AL CONTADO SIN IGV</td>
									<td>S/. {{number_format($ventascontado/1.1055,'2','.',',')}}</td>
									
								</tr>
								<tr>
									<td style="width:800px">TOTAL VENTAS CON TARJETA SIN IGV</td>
									<td>S/. {{number_format($ventastarjeta/1.1055,'2','.',',')}}</td>
								
								</tr>
								<tr>
									<td style="width:800px">TOTAL VENTAS TRANSFERENCIAS SIN IGV</td>
									<td>S/. {{number_format($transferencias/1.1055,'2','.',',')}}</td>
								
								</tr>
								<tr>
									<td style="width:800px">TOTAL VENTAS AL CREDITO SIN IGV</td>
									<td>S/. {{number_format($ventascredito/1.1055,'2','.',',')}}</td>
								
								</tr>
							
							</tbody>
						</table>
						
						<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm">
							<thead>
								<tr>
									<th colspan="2"><center><strong>TOTAL GASTOS: {{$fecin}} HASTA {{$fecfin}}</strong></center></th>
	
								</tr>
								
								
							</thead>
							<tbody>
								@foreach($gastos as $gasto)
								<tr>
									<td style="width:800px">{{$gasto->det_gasto}}</td>
									<td>S/. {{$gasto->total}}</td>
									
								</tr>
							
								@endforeach
							</tbody>
						</table>

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