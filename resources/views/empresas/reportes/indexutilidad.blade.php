@extends('layouts.empresas')
@section('contenido')
<script type="text/javascript">
     $(document).ready(function()
    {
     $("#sucursal").change(function() {
         
                var sucursal = $("#sucursal").val();
                alert(sucursal);
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
	           		@include('empresas.reportes.buscarcomprobantes')
	           	</div>
	         </div>
	    </div>
	</div> 
	<?php
		$totalventa =0;
		$totalcosto=0;
		$totalutilidad=0;
	?>
	@foreach($comprobantes as $comprobante)
		<?php
		if($comprobante->baja != 'NULL'){
		$totalventa = $totalventa + ($comprobante->precio*$comprobante->cantidad);
		$totalcosto = $totalcosto + ($comprobante->costo*$comprobante->cantidad);
		$totalutilidad = $totalventa - $totalcosto;
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
									<th colspan="9"><center><strong>REPORTE DE VENTAS DESDE: {{$fecin}} HASTA {{$fecfin}} @if(!empty($dat_ven)) - Vendedor: {{$dat_ven->name}} {{$dat_ven->apeusu}} @endif </strong></center>
									<th>TOTAL VENTAS</th>
									<th>TOTAL COSTO</th>
									<th colspan="2">UTILIDAD</th>
								</tr>
								<tr>
									<th colspan="9"></th>
									<th>{{number_format($totalventa,'2','.',',')}}</th>
									<th>{{number_format($totalcosto,'2','.',',')}}</th>
									<th colspan="2">{{number_format($totalutilidad,'2','.',',')}}</th>
								</tr>

								<tr>
									<th>ESTADO</th>
									<th>FECHA</th>
									<th>SERIE</th>
									<th>N°</th>
									<th style="width:210px;">CLIENTE</th>
									<th>PRODUCTO</th>
									<th>CANTIDAD</th>
									<th>PRECIO UNITARIO</th>
									<th>COSTO UNITARIO</th>
									<th>VENTA TOTAL</th>
									
									<th>COSTO TOTAL</th>
									<th>UTILIDAD</th>
								
								</tr>
							</thead>
							
							<tbody>
								@foreach($comprobantes as $comprobante)
								<tr>
										@if($comprobante->ccasunrescod=='0')
										<td>
											ACEPTADO
										</td>
										@elseif($comprobante->ccasunrescod=='8')
										<td>
											ANULADO
										</td>
										@elseif($comprobante->ccasunrescod=='7')
										<td>
											ANULACION EN PROCESO
										</td>

										@elseif($comprobante->ccasunrescod >'100' && $comprobante->ccasunrescod <'1999')

										<td>
											CORREGIR Y ENVIAR
										</td>

										@elseif($comprobante->ccasunrescod > '2000' && $comprobante->ccasunrescod <'3999')
										<td>
											RECHAZADO
										</td>
										@elseif($comprobante->ccasunrescod > '4000')
										<td>
											OBSERVADO
										</td>
										@else
										<td>
											
										</td>
										@endif
								 	<td>{{Carbon::parse($comprobante->FECHA)->format('d-m-Y')}}</td>
									<td>{{$comprobante->serie}}</td>
									<td>{{$comprobante->numero}}</td>
									<td>{{$comprobante->cliente}}</td>
									<td>{{$comprobante->producto}}</td>
									<td>{{number_format($comprobante->cantidad,'2','.',',')}}</td>
									<td>{{number_format($comprobante->precio,'2','.',',')}}</td>
									<td>{{number_format($comprobante->costo,'2','.',',')}}</td>
									<td>{{number_format($comprobante->precio*$comprobante->cantidad,'2','.',',')}}</td>
									<td>{{number_format($comprobante->costo*$comprobante->cantidad,'2','.',',')}}</td>
									<td>{{number_format(($comprobante->precio*$comprobante->cantidad)-($comprobante->costo*$comprobante->cantidad),'2','.',',')}}</td>
									
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