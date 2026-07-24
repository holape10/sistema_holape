@extends('layouts.empresas')
@section('contenido')

<script>

	var href = $('#btnPrint').attr('href');
	
	$("#btnPrint").printPage({
		
		 
		  url: href,
		  attr: "href",
		  messageBox:false,
		  
	})
</script>

	
	<section class="content">



	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
          			<div class="box-header" style="background-color:blue;">
          				<font color="white"><center><strong>REGISTRO DE GASTOS E INGRESOS DE CAJA <br>CAJERO: {{$usuario->name}} {{$usuario->apeusu}} <Br>{{$datos->apertura}}   -   {{$datos->cierre}} </strong></center></font>
          			</div>
	            	
	            </div> 
	        </div>
	</div>            
    	<div class="row">
        	<div class="col-xs-6">
          		<div class="box">
          			<div class="box-header" style="background-color:green;">
          				<font color="white"><center><strong>GASTOS CAJA</strong></center></font>
          			</div>
	            	<div class="box-body">
						<table  class="table table-bordered table-hover table-striped">
							<thead>
								<tr>
									
									<th>Fecha</th>
									<th>Detalle</th>
									<th>Observaciones</th>
									<th>Total</th>
									<th>Eliminar</th>
								</tr>
							</thead>
							<tbody>
								@foreach($gastos as $comp)
								<tr>
									
								 	<td>{{$comp->gast_fec}}</td>
								 	<td>{{$comp->det_gasto}}</td>
									<td>{{$comp->gast_obs}}</td>
									<td>{{number_format($comp->total,'2','.',',')}}</td>
									<td>
									@if($comp->est_gasto =='Eliminado')
											<img src="/icon/error.png" title="ANULADO" height="20px" width="20px"><br>
										@else
											 <a href="" data-target="#modal-delete-{{$comp->gast_cab_id}}" data-toggle="modal"><img src="/icon/error.png" title="ANULADO" height="20px" width="20px"></a><br>
										@endif
									</td>
								</tr>
								@include('empresas.gastos.modal')
								@endforeach
								<tr>
									<td colspan="4">
										<font size='3'><strong>TOTAL</strong></font>
									</td>
									<td>
										<font size='3'><strong>S/. {{$totalgas}}</strong></font>
									</td>
								</tr>
							</tbody>
						</table><br>
					</div>	
				</div>	
			</div>

				<div class="col-xs-6">
          		<div class="box">
          			<div class="box-header" style="background-color:green;">
          				<font color="white"><center><strong>INGRESOS CAJA</strong></center></font>
          			</div>
	            	<div class="box-body">
						<table  class="table table-bordered table-hover table-striped">
							<thead>
								<tr>
									
									<th>Fecha</th>
									<th>Detalle</th>
									<th>Observaciones</th>
									<th>Total</th>
									<th>Imprimir</th>
								</tr>
							</thead>
							<tbody>
								@foreach($ingresos as $ing)
								<tr>
									
								 	<td>{{$ing->gast_fec}}</td>
								 	<td>{{$ing->det_gasto}}</td>
									<td>{{$ing->gast_obs}}</td>
									<td>{{number_format($ing->total,'2','.',',')}}</td>
									<td>
										@if($ing->est_gasto =='Eliminado')
											<img disabled="disabled" src="/icon/error.png" title="ANULADO" height="20px" width="20px">
										@else
											 <a href="" data-target="#modal-delete-{{$ing->gast_cab_id}}" data-toggle="modal"><img src="/icon/error.png" title="ANULADO" height="20px" width="20px"></a><br>
										@endif
									</td>
								</tr>
								@include('empresas.gastos.modalingreso')
								@endforeach
								<tr>
									<td colspan="3">
										<font size='3'><strong>TOTAL</strong></font>
									</td>
									<td>
										<font size='3'><strong>S/. {{$totaling}}</strong></font>
									</td>
								</tr>
							</tbody>
						</table><br>
					</div>	
				</div>	
			</div>

		</div>

		<div class="row">
        	<div class="col-xs-6">
          		<div class="box">
          			<div class="box-header" style="background-color:red;">
          				<font color="white"><center><strong>GASTOS DE CAJA ANULADOS</strong></center></font>
          			</div>
	            	<div class="box-body">
						<table  class="table table-bordered table-hover table-striped">
							<thead>
								<tr>
									
									<th>Fecha</th>
									<th>Detalle</th>
									<th>Observaciones</th>
									<th>Total</th>
								</tr>
							</thead>
							<tbody>
								@foreach($gastoseliminados as $comp)
								<tr>
									
								 	<td>{{$comp->gast_fec}}</td>
								 	<td>{{$comp->det_gasto}}</td>
									<td>{{$comp->gast_obs}}</td>
									<td>{{number_format($comp->total,'2','.',',')}}</td>
								</tr>
								@endforeach
								<tr>
									<td colspan="3">
										<font size='3'><strong>TOTAL</strong></font>
									</td>
									<td>
										<font size='3'><strong>S/. {{$totalgaseli}}</strong></font>
									</td>
								</tr>
							</tbody>
						</table><br>
					</div>	
				</div>	
			</div>

				<div class="col-xs-6">
          		<div class="box">
          			<div class="box-header" style="background-color:red;">
          				<font color="white"><center><strong>INGRESOS DE CAJA ANULADOS</strong></center></font>
          			</div>
	            	<div class="box-body">
						<table  class="table table-bordered table-hover table-striped">
							<thead>
								<tr>
									
									<th>Fecha</th>
									<th>Detalle</th>
									<th>Observaciones</th>
									<th>Total</th>

								</tr>
							</thead>
							<tbody>
								@foreach($ingresoseliminados as $ing)
								<tr>
									
								 	<td>{{$ing->gast_fec}}</td>
								 	<td>{{$ing->det_gasto}}</td>
									<td>{{$ing->gast_obs}}</td>
									<td>{{number_format($ing->total,'2','.',',')}}</td>
								</tr>
								@endforeach
								<tr>
									<td colspan="3">
										<font size='3'><strong>TOTAL</strong></font>
									</td>
									<td>
										<font size='3'><strong>S/. {{$totalingeli}}</strong></font>
									</td>
								</tr>
							</tbody>
						</table><br>
					</div>	
				</div>	
			</div>

		</div>

	</section>

@endsection