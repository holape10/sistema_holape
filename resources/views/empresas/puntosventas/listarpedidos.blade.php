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
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
	    @if(session()->has('info'))
	    	<div class="alert alert-danger">
	    	  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			  <strong>Alerta!</strong> {{ session('info') }}
			</div>
	    @endif


	    @if(session()->has('success'))
	    	<div class="alert alert-success">
	    	  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			  <strong>InformaciÃ³n!</strong> {{ session('success') }}
			</div>
	    @endif
	</div>
</div>

	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
	            		Registro de Servicios
	            	</div>
	            </div>
	        </div>
	</div>            
    	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
						<table id="tblpedidos"  class="table table-bordered table-hover">
							<thead>
								<tr>
									<th>Cliente</th>
									<th>DNI/RUC</th>
									<th>Dirección</th>
									<th>Fecha Ingreso</th>
									<th>Fecha Salida</th>
									<th>Habitación</th>
									<th>Estado Habitación</th>
									<th>Total</th>
								</tr>
							</thead>
							<tbody>
								@foreach($pedidos as $ped)
								<tr>
									<td>{{$ped->clinom}}</td>
								 	<td>{{$ped->clinum}}</td>
								 	<td>{{$ped->clidir}}</td>
								 	<td>{{$ped->fecha_hora_ingreso}}</td>
								 	<td>{{$ped->fecha_hora_salida}}</td>
								 	<td>{{$ped->mes_nom}}</td>
								 	@if($ped->mes_est =='Ocupado')
								 	<td style="background:#E74C3C;"><font style="color:white;"><strong>{{$ped->mes_est}}</strong></font></td>
								 	@elseif($ped->mes_est =='Libre')
								 	<td style="background:#A8F2F5;"><strong>{{$ped->mes_est}}</strong></td>
								 	@elseif($ped->mes_est =='Limpieza')
								 	<td style="background:#E3EE15;"><strong>{{$ped->mes_est}}</strong></td>
								 	@endif
								 	
								 	<td>{{$ped->total}}</td>
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