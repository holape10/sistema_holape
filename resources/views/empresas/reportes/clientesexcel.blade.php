
@extends('layouts.reportes')
@section('contenido')



<section class="content">

        
	<div class="row">
		<div class="col-xs-12">
			<div class="box">
				<div class="box-body">
					<table id="tblCompra"  class="table table-bordered table-hover">
				<thead>
					<tr>
						<th colspan="6" style="font-size:14pt;"><center>{{$empresa->NomEmpresa}}</center></th>
					</tr>
					<tr>
						<th></th>
					</tr>
					<tr>
						<th colspan="6" style="font-size:12pt;"><center>REPORTE DE CLIENTES</center></th>
					</tr>
					<tr>
						<th></th>
					</tr>
					<tr>
					<th>RUC</th>
					<th>Razón Social</th>
					<th>Dirección</th>
					<th>Teléfono</th>
					<th>Correo</th>

					<th>Vendedor</th>
				</tr>
				</thead>
				@foreach ($clientes as $cli)
				<tr>
					<td>{{$cli->clinum}}</td>
					<td>{{$cli->clinom}}</td>
					<td>{{$cli->clidir}}</td>
					<td>{{$cli->telefono}}</td>
					<td>{{$cli->clicor}}</td>
					<td>{{$cli->name}} {{$cli->apeusu}}</td>
					
				
				</tr>
		
				@endforeach
					</table><br>
				</div>	
			</div>	
		</div>
	</div>
</section>

@endsection