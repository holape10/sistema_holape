@extends('layouts.empresas')
@section('contenido')

<?php 

		$total =0;

	?>
	@foreach($comprobantes as $comprobante)
		<?php 
			if($comprobante->baja != 'NULL'){
				$total = $total+$comprobante->total;
			
			}
			
		?>
	@endforeach

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
	
	<div class="row">
		<div class="col-xs-12">
			<div class="box">
	            	<div class="box-body">
						<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm">
							<thead>
								<tr>
									<th colspan="8"><center><strong>REPORTE DE VENTAS DESDE: {{$fecin}} HASTA {{$fecfin}} @if(!empty($dat_ven)) - Vendedor: {{$dat_ven->name}} {{$dat_ven->apeusu}} @endif </strong></center>
									<th>TOTAL VENTAS </th>
								</tr>
								<tr>
									<th colspan="8"></th>
									<th> {{$total}}</th>
								
								
								</tr>

								<tr>
									
									<th>FECHA</th>
									<th>COMPROBANTE</th>
									<th>SERIE</th>
									<th>N°</th>
									<th>DOC. IDENTIDAD</th>
									<th>N° DOC. IDENTIDAD</th>
									<th style="width:210px;">CLIENTE</th>
									<th>MONEDA</th>
			
									<th>VENTA TOTAL</th>
								</tr>
							</thead>
							
							<tbody>
									
									@foreach($comprobantes as $comprobante)
								<tr>
								
								 	<td>{{Carbon::parse($comprobante->fecha)->format('d-m-Y')}}</td>
								 	<td>{{$comprobante->comprobante}}</td>
									<td>{{$comprobante->serie}}</td>
									<td>{{$comprobante->numero}}</td>
									<td>{{$comprobante->documentoidentidad}}</td>
									<td>{{$comprobante->numerodocumento}}</td>
									<td>{{$comprobante->cliente}}</td>
									<td>{{$comprobante->moneda}}</td>
							
									<td>{{$comprobante->total}}</td>
									
								</tr>
								@endforeach
							</tbody>
						</table><br>
					</div>	
				
				</div>
		</div>
	</div>
</section>



@endsection