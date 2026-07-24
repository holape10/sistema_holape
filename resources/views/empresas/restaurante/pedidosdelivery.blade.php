@extends('layouts.empresas')
@section('contenido')
<script>

	$(document).ready(function(){   

		setTimeout(refrescar, 60000);

	});

	var href = $('#btnPrint').attr('href');
	
	$("#btnPrint").printPage({
		
		url: href,
		attr: "href",
		messageBox:false,

	})

	function refrescar(){
		window.location.href = '/delivery';
	}

</script>

<section class="content">
	

	<div class="row">
		<div class="col-xs-12">
			<div class="box">
				<div class="box-header" style="background-color:blue;">
					<font color="white"><center><strong>LISTADO DE PEDIDOS POR DELIVERY</strong></center></font>
				</div>
				<div class="box-body">

					{!! Form::model(Request::all(),['Route'=>'/delivery','method'=>'GET','autocomplete'=>'off'])!!}

					<div class="row">
						<div class="col-lg-2">
							<div class="form-group form-group-sm">
								<label>ESTADO</label>
								<select class="form-control" name="estado">
									@foreach($estados as $est)
									<option value="{{$est->est_ped_id}}">{{$est->est_ped_nom}}</option>
									@endforeach
								</select>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<div class="btn-toolbar" role="toolbar" aria-label="...">
								<div class="btn-group">
									<button type="submit" class=" btn btn-primary btn-sm">Buscar</button>
								</div>
									
							</div>
						</div>
					</div>

					{{Form::close()}}
				</div>
			</div>
		</div>
	</div> 

	<div class="row">
		<div class="col-xs-12">
			<div class="box">
				<div class="box-body table-responsive" >
					<table id="tblpedidos"  class="table table-bordered table-hover">
						<thead>
							<tr style="font-size:10pt;font-weight:bold;color:white;background: #808080;">
								<!--@if(Auth::User()->hasRole('admin') ||   Auth::User()->hasRole('cajero'))
								<th style="text-align:center;vertical-align:middle;">Item</th>
								@endif-->
								<th style="text-align:center;vertical-align:middle;">Cliente</th>
								<th style="text-align:center;vertical-align:middle;">Fecha</th>
								<th style="text-align:center;vertical-align:middle;">Tipo</th>
								<th style="text-align:center;vertical-align:middle;">Estado</th>
								<th style="text-align:center;vertical-align:middle;">Detalle</th>
								<th style="text-align:center;vertical-align:middle;">Repartidor</th>
								<th style="text-align:center;vertical-align:middle;">Opciones</th>
							</tr>
						</thead>
						<tbody>
							@foreach($pedidos as $ped)
							<tr>
								<!--@if(Auth::User()->hasRole('admin') ||   Auth::User()->hasRole('cajero'))
								<td><input type="checkbox" name="item[]" value="{{$ped->ped_id}}"></td>
								@endif-->

								<td>@if(!empty($ped->ped_cli_nom))
									{{$ped->ped_cli_nom}}
									@endif
									@if(!empty($ped->ped_dir))
									| {{$ped->ped_dir}}
									@endif
									@if(!empty($ped->ped_tel))
									| {{$ped->ped_tel}}
									@endif
									@if(!empty($ped->mes_nom))
									{{$ped->mes_nom}}
								@endif</td>
								<td>{{$ped->fecha_hora}}</td>
								<td>{{$ped->ped_tip}}</td>

								<td>@if($ped->est_ped_id=='10')<button type="button" class="btn btn-sm btn-warning btn-block">{{$ped->est_ped_nom}} </button>@else <button type="button" class="btn btn-sm btn-success btn-block">{{$ped->est_ped_nom}} </button> @endif</td>

								<td>@foreach($detalles as $det)
									@if($ped->ped_id == $det->ped_id)
									<li>{{$det->pronom}} @if(!empty($det->detalle))| {{$det->detalle}} @endif</li>
									@endif
									@endforeach
								</td>
								<td>{{$ped->name}} {{$ped->apeusu}}</td>
								<td style="text-align:center;">
									@if($ped->est_ped_id=='10')
										<a href="" data-target="#modal-entregar-{{$ped->ped_id}}" data-toggle="modal" ><img title="Entregar" width="35px" height="35px" src="/icon/entregado.png"></a>
									@else
										<img style="opacity: 0.5;" title="Entregar" width="35px" height="35px" src="/icon/entregado.png">

									@endif	
									

									<!--<a href="" data-target="#modal-eliminar-{{$ped->ped_id}}" title="ANULAR PEDIDO" data-toggle="modal"><img width="35px" height="30px" src="/icon/anular.png"></a>-->
								</td>
							</tr>

							@include('empresas.puntosventas.modalentregar')
							@endforeach
						</tbody>
					</table>		
				</div>
			</div>
		</div>
	</div>


</section>

@endsection