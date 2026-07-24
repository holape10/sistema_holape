@extends('layouts.empresas')
@section('contenido')
<?php
		$totalventa =0;
		$totaligv=0;
		$gravados=0;
		$exonerados=0;
	?>
	@foreach($comprobantes as $comprobante)
		<?php
		/*if(($comprobante->ccasunrescod >'100' && $comprobante->ccasunrescod <'1999') || ($comprobante->ccasunrescod > '4000') || $comprobante->ccasunrescod=='0' ){*/
		$totalventa = $totalventa + $comprobante->total;
		$totaligv = $totaligv + $comprobante->igv;
		$gravados = $gravados + $comprobante->gravado;
		$exonerados = $exonerados + $comprobante->ccatexo;
		/*}*/
		?>
	@endforeach
<section class="content">	
	<div class="row">
        <div class="col-xs-12">
        	<div class="box">
        		<div class="box-header box-success" style="background-color:#00a65a;">
        			<font color="white" size="4"><center><strong>REPORTES CONTABILIDAD DE COMPROBANTES</strong></center></font>
        		</div>
	           	<div class="box-body">
	           		@include('empresas.reportes.buscarcomprobantes')
	           	</div>
	         </div>
	    </div>
	</div> 

<div class="row">
		<div class="col-xs-12">
			<div class="box">
	            	<div class="box-body">
						<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm">
							<thead>
							
									<tr>
									<th colspan="8"><center><strong>REPORTE DE VENTAS DESDE: {{$fecin}} HASTA {{$fecfin}}</strong></center>
									<th>GRAVADOS</th>
									<!--<th>EXONERADOS</th>-->
									<th>IGV</th>
									<th >TOTAL</th>
								</tr>
								<tr>
									<th colspan="8"></th>
									<th>{{number_format($gravados,'2','.',',')}}</th>
									<!--<th>{{number_format($exonerados,'2','.',',')}}</th>-->
									<th>{{number_format($totaligv,'2','.',',')}}</th>
									<th>{{number_format($totalventa,'2','.',',')}}</th>
								</tr>

								<tr>
									<th>FECHA</th>
									<th>CODIGO</th>
									<th>SERIE</th>
									<th>NUMERO</th>
									<th>RUC/DNI</th>
									<th>RAZON SOCIAL</th>
									<th>MONEDA</th>
									<!--<th>ICBPER</th>-->
									<th>GRAVADO</th>
									<th>IGV</th>
									<!--<th>EXONERADO</th>-->
									<th>TOTAL</th>
									<th>ESTADO</th>
									
									
								</tr>
							</thead>
							
							<tbody>
								@foreach($comprobantes as $comprobante)
								<tr>
								
								 	<td>{{Carbon::parse($comprobante->fecha)->format('d-m-Y')}}</td>
								 	<td>{{$comprobante->tdocod}}</td>
									<td>{{$comprobante->serie}}</td>
									<td>{{$comprobante->numero}}</td>
									<!--<td>{{$comprobante->documentoidentidad}}</td>-->
									<td>{{$comprobante->numerodocumento}}</td>
									<td>{{$comprobante->cliente}}</td>
									<td>{{$comprobante->moneda}}</td>
									<td>{{number_format($comprobante->gravado,'2','.',',')}}</td>
									<td>{{number_format($comprobante->igv,'2','.',',')}}</td>
									<!--<td>{{number_format($comprobante->icbper,'2','.',',')}}</td>
									<td>{{number_format($comprobante->ccatexo,'2','.',',')}}</td>-->
									<td>{{number_format($comprobante->total,'2','.',',')}}</td>
										@if($comprobante->ccasunrescod=='0')
										<td style="background-color:green;">
											<font color="white"><strong>ACEPTADO</strong></font>
										</td>
										@elseif($comprobante->ccasunrescod=='8')
										<td style="background-color:red;">
											<font color="white"><strong>ANULADO</strong></font>
										</td>
										@elseif($comprobante->ccasunrescod=='7')
										<td style="background-color:red;">
											<font color="white"><strong>ANULACION EN PROCESO</strong></font>
										</td>

										@elseif($comprobante->ccasunrescod >'100' && $comprobante->ccasunrescod <'1999')

										<td style="background-color:orange;">
											<font color="white"><strong>CORREGIR Y ENVIAR</strong></font>
										</td>

										@elseif($comprobante->ccasunrescod > '2000' && $comprobante->ccasunrescod <'3999')
										<td style="background-color:red;">
											<font color="white"><strong>RECHAZADO</strong></font>
										</td>
										@elseif($comprobante->ccasunrescod > '4000')
										<td style="background-color:green;">
											<font color="white"><strong>OBSERVADO</strong></font>
										</td>
										@else
										<td>
											
										</td>
										@endif
									
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