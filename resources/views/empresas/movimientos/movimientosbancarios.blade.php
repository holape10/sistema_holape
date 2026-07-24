@extends('layouts.empresas')
@section('contenido')

<section class="content">
	<div class="row">
		<div class="col-xs-12">
			<div class="box">
				<div class="box-body">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<h4><i class='glyphicon glyphicon-search'></i>  MOVIMIENTOS BANCARIOS</h4>
						
						@include('empresas.movimientos.buscarbancarios')
						
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
							
							<th colspan="13" style="background:blue;"><STRONG><center><font color="white">MOVIMIENTOS BANCARIOS</font></center></STRONG></th>
							
						</thead>
						<thead>
							<th style="background:blue;"><font color="white">BANCO</font></th>
							<th>@if(!empty($cuenta)) {{$cuenta->ban_nom}} @endif</th>
							<th style="background:blue;"><font color="white">CUENTA N°</font></th>
							<th>@if(!empty($cuenta)) {{$cuenta->cuen_ban_num}} @endif</th>
							<th style="background:blue;"><font color="white">MONEDA</font></th>
							<th colspan="6">@if(!empty($cuenta)) {{$cuenta->monnom}} @endif</th>
						</thead>
						<thead>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th colspan="6"></th>
						</thead>
						<thead>
							
							<th>FECHA</th>
							<th>CLIENTE</th>
							<TH>CONCEPTO MOVIMIENTO BANCARIO</TH>
							<th>COMENTARIO</th>
							<th>TIPO DOCUMENTO</th>
							<th>N° DOC</th>
							<th>N° OPE.</th>
							<th>DEBE</th>
							<th>HABER</th>
							<th>SALDO</th>
							<th>ESTADO</th>
							<th>REGISTRO</th>
							<th>OPCIONES</th>
						</thead>

						 @if(!empty($movimientos))
						@foreach($movimientos as $movimiento)

						<tr>
							<td>{{$movimiento->mov_fecha}}</td>
							<td>{{$movimiento->clinom}}</td>
							<td>{{$movimiento->concepto_nom}}</td>
							<td>{{$movimiento->mov_com}}</td>
							<td>{{$movimiento->doc_nom}}</td>
							<td>{{$movimiento->mov_num_doc}}</td>
							<td>{{$movimiento->mov_num_oper}}</td>
							<td>
								@if($movimiento->mov_tip =='debe')
									{{$movimiento->importe}}
								@else
									0.00
								@endif
							</td>
							<td>
								@if($movimiento->mov_tip =='haber')
									{{$movimiento->importe}}
								@else
									0.00
								@endif
							</td>
							<td>{{$movimiento->saldo}}</td>
							<td>@if($movimiento->estado=='1') Validado @else Por Validar @endif </td>
							<td>{{$movimiento->registro}}</td>
							<td>
								<!--<a href="/movimientosbancarios/editar/{{$movimiento->mov_ban_id}}"><button class="btn btn-sm btn-success">Editar Movimiento Bancario</button></a>-->
								@if($movimiento->registro =='Eliminado')
									<a><button disabled="disabled" class="btn btn-danger">Eliminar</button></a>
								@else
									<a href="" data-target="#modal-delete-{{$movimiento->mov_ban_id}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a>
								@endif
								
							</td>
						</tr>
						@include('empresas.movimientos.movimientosbancariosmodal')
						@endforeach
						@endif
					</table>
				</div>
				
			</div>
		</div>
	</div>
</section>
@endsection
