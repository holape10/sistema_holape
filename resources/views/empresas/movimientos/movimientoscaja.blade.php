@extends('layouts.empresas')
@section('contenido')

<section class="content">
	<div class="row">
		<div class="col-xs-12">
			<div class="box">
				<div class="box-header" style="background:blue;">
					<center><font color="white"><strong>MOVIMIENTOS CAJA</strong></font></center>
				</div>
				<div class="box-body">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						
						
						@include('empresas.movimientos.buscarcaja')
						
					</div>
				</div>
			</div>
		</div>
	</div>

<div class="row">
    <div class="col-xs-12">
    	<div class="box">
	       	<div class="box-body">
			<table class="table table-striped table-bordered table-condensed table-hover">
				<thead>
					
					<th colspan="7"><STRONG><center>MOVIMIENTOS CAJA</center></STRONG></th>
					
				</thead>
				<thead>
				
					<th>FECHA</th>
					<th>CONCEPTO</th>
					<TH>CODIGO</TH>
					<th>ENTRADAS</th>
					<th>SALIDAS</th>
					<th>SALDO</th>
					<th>ESTADO</th>
					<th>OPCIONES</th>
				</thead>
				@foreach($movimientos as $movimiento)
				<tr>
					<td>{{$movimiento->mov_fecha}}</td>
					<td>{{$movimiento->tip_caj_nom}}</td>
					<td>{{$movimiento->tip_caj_id}}</td>
					<td>
						@if($movimiento->tipo =='ENTRADA')
							{{$movimiento->importe}}
						@endif
					</td>
					<td>
						@if($movimiento->tipo =='SALIDA')
							{{$movimiento->importe}}
						@endif
					</td>
					<td>{{$movimiento->saldo}}</td>
					<td>{{$movimiento->registro}}</td>
					<td>
						<!--<a href="/movimientoscaja/editar/{{$movimiento->mov_caj_id}}"><button class="btn btn-sm btn-success">Editar Movimiento caja</button></a>-->
						<a href="" data-target="#modal-delete-{{$movimiento->mov_caj_id}}" data-toggle="modal"><button class="btn btn-danger btn-sm">Eliminar</button></a>
					</td>
				</tr>	
				@include('empresas.movimientos.movimientoscajamodal')
				@endforeach

			</table>
		</div>
	
	</div>
</div>
</div>
</section>
@endsection
