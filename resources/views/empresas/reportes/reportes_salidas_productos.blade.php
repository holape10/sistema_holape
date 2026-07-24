@extends('layouts.empresas')
@section('contenido')
<script>

	$(document).ready(function()
	{
		$(".formbaja").keypress(function(e) {
			if (e.which == 13) {
				return false;
			}
		})

		$("#btnGenPdf").click(function() {
	        var accion = $(this).attr('dir');

	        $('#frmReporte').attr('action', accion);
	        $('#frmReporte').submit();
    	});

	});

	
	function enviarbaja(id){


		var comp = $("#comprobante"+id).val();
		var motivo = $("#motivo"+id).val();



		$(".imgloadanular").show();
		$(".botonesanular").hide();
		$.ajax({
			type: "GET",
			dataType: 'json',
			url: '/registraranulacion/'+comp+'/'+motivo,
		}).done(function(respuesta){


			if(respuesta.mensaje =='orden'){
				window.location.href = "/ordenes";
			}

			if(respuesta.mensaje =='cotizacion'){
				window.location.href = "/ordenes";

			}


			if(respuesta.mensaje =='cpe'){

				window.location.href = "/SisFact";

			}



		});
	}




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
				<div class="box-header" style="background-color:#337ab7;">
					<font color="white"><center><strong>SALIDAS DE PRODUCTOS</strong></center></font>
				</div>
				<div class="box-body">
					{!! Form::model(Request::all(),['Route'=>'/reportesalidas','method'=>'GET','autocomplete'=>'off','id'=>'frmReporte'])!!}

<!--<div class="col-lg-3">
	<div class="form-group form-group-sm">
		<h4><i class='glyphicon glyphicon-search'></i> CONSULTAR COMPROBANTES</h4>
	</div>
</div>-->
<style>
	input[type=date]::-webkit-inner-spin-button, 
	input[type=date]::-webkit-clear-button,
	input[type=date]::-webkit-outer-spin-button { 
		-webkit-appearance: none; 
		margin: 0; 
	}

</style>
<div class="row">
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
			<label>Empresa</label>
			<select class="form-control" name="sucursal">
				@foreach($negocios as $negocio)
				@if($negocio->id_empresa_negocio == $sucursal)
				<option selected="selected" value="{{$negocio->id_empresa_negocio}}">{{$negocio->IdEmpresa}} - {{$negocio->tipo_negocio}}</option>
				@else
				<option value="{{$negocio->id_empresa_negocio}}">{{$negocio->IdEmpresa}} - {{$negocio->tipo_negocio}}</option>
				@endif
				@endforeach
			</select>
		</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
			<label class="control-label" for="fecin">Desde </label>
			<input type="date" name="fecin" class="form-control" value="{{$fecin}}">

		</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
			<label class="control-label" for="fecfin">Hasta </label>
			<input type="date" name="fecfin" class="form-control" value="{{$fecfin}}">

		</div>
	</div>

	<div class="col-lg-2" >
		<div class="form-group">
			<label class="control-label">TIPO REPORTE</label>
			<select class="form-control selectpicker input-sm" data-show-subtext="true" data-live-search="true" name="tipo" id="tipo" >
				<option value="1">DETALLADO</option>
				<option value="2">POR PRODUCTO</option>

			</select>

		</div>
	</div>

	<div class="col-lg-2" >
		<div class="form-group">
			<label class="control-label">&Aacute;reas</label>
			<select class="form-control selectpicker input-sm" data-show-subtext="true" data-live-search="true" name="area" id="area">
				<option></option>
				@foreach($areas as $are)
				@if($are->are_emp_id == $area)
				<option selected="selected" value="{{$are->are_emp_id}}">{{$are->are_emp_des}}</option>
				@else
				<option value="{{$are->are_emp_id}}">{{$are->are_emp_des}}</option>
				@endif
				@endforeach
			</select>

		</div>
	</div>

	<div class="col-lg-2" >
		<div class="form-group">
			<label class="control-label">Productos</label>
			<select class="form-control selectpicker input-sm" data-show-subtext="true" data-live-search="true" name="IdProducto" id="IdProducto">
				<option></option>
				@foreach($productos as $pro)
				@if($pro->IdProducto == $IdProducto)
				<option selected="selected" value="{{$pro->IdProducto}}">{{$pro->pronom}}</option>
				@else
				<option value="{{$pro->IdProducto}}">{{$pro->pronom}}</option>
				@endif
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
			<div class="btn-group" >


				<button type="button" id="btnExport" class="btn btn-primary btn-sm">Exportar Excel</button>
			</div>

			<div class="btn-group" >


				<button type="button" id="btnGenPdf" dir="/reporte/salidas/pdf" class="btn btn-primary btn-sm">Exportar PDF</button>
			</div>

		</div>
	</div>

	
</div>
<input type="hidden" readonly class="form-control" name="searchIdEmp" placeholder="Buscar..." value="{{Auth::user()->IdEmpresa}}">

{{Form::close()}}
</div>
</div>
</div>
</div> 

<div class="row">
	<div class="col-xs-12">
		<div class="box">
			<div class="box-body">
				@if($tipo=='1')
				<table id="dtHorizontalExample"  class="table table-bordered table-hover">
					<thead style="background:#337ab7;color:white;">
						<tr>
							<th><center>Fec. Emision</center></th>
							<th><center>Colaborador</center></th>

							<th><center>Tipo</center></th>
							<th><center>Serie</center></th>
							<th><center>N°</center></th>
							<th><center>Producto</center></th>
							<th><center>Lote</center></th>
							<th><center>Vencimiento</center></th>
							<th><center>Cantidad</center></th>
							<th><center>Costo</center></th>
							<th><center>Total</center></th>




						</tr>
					</thead>

					<tbody>
						@php
							$totalgeneral=0;
						@endphp
						@foreach($areas as $ar)
							<tr>
								<td colspan="9"><font><center><strong>{{$ar->are_emp_des}}</strong></center></font></td>
							</tr>
							@php
									$total = 0;
									$totalv = 0;
								@endphp
							@foreach($comprobantes as $comp)
								
								@if($comp->are_emp_id == $ar->are_emp_id)
									@php
										$total = $total+$comp->cdecan;
										$totalv = $totalv+($comp->cdecan*$comp->costo);
									@endphp
								<tr>
									<td>{{Carbon::parse($comp->ccafem)->format('d-m-Y')}}</td>
									<td>{{$comp->name}} {{$comp->apeusu}}</td>
									<td>{{$comp->tdodes}}</td>
									<td>{{$comp->serdoc}}</td>
									<td>{{$comp->numdoc}}</td>
									<td>{{$comp->cdedes}}</td>
									<td>{{$comp->cpe_det_lote}}</td>
									<td>{{$comp->cpe_det_venc}}</td>
									<td style="text-align:right;">{{number_format($comp->cdecan,2,'.','')}}</td>
									<td style="text-align:right;">{{number_format($comp->costo,2,'.','')}}</td>
									<td style="text-align:right;">{{number_format($comp->cdecan*$comp->costo,2,'.','')}}</td>
								</tr>
								@endif
								
							@endforeach
							<tr style="background:gray;color:white;">
									<td colspan="8" style="text-align:right;"><strong>TOTAL PRODUCTOS - {{$ar->are_emp_des}}</strong></td>
									<td style="text-align:right;"><strong>{{number_format($total,2,'.','')}}</strong></td>
									<td style="text-align:right;"><strong></strong></td>
									<td style="text-align:right;"><strong>{{number_format($totalv,2,'.','')}}</strong></td>
								</tr>
								@php
									$totalgeneral = $totalgeneral + $totalv;
								@endphp
						@endforeach
						
						<tr style="background:#337ab7;color:white;">
							<td colspan="8" style="text-align:right;"><strong>TOTAL PRODUCTOS</strong></td>
								<td style="text-align:right;"><strong>{{number_format($cantidad,2,'.','')}}</strong></td>
									<td style="text-align:right;"><strong></strong></td>
							<td style="text-align:right;"><strong>{{number_format($totalgeneral,2,'.','')}}</strong></td>
						</tr>
					</tbody>
				</table><br>
				@elseif($tipo=='2')

				<table id="dtHorizontalExample"  class="table table-bordered table-hover">
					<thead style="background:#337ab7;color:white;">
						<tr>

							<th><center>Area</center></th>
							<th><center>Producto</center></th>
							<th><center>Cantidad</center></th>
							<th><center>Costo</center></th>
							<th><center>Total</center></th>



						</tr>
					</thead>

					<tbody>
						@php
						$totalgeneral =0;
						@endphp
						@foreach($areas as $ar)
							<tr>
								<td colspan="7"><font><center><strong>{{$ar->are_emp_des}}</strong></center></font></td>
							</tr>
							@php
								$total = 0;
								$totalca=0;
								$totalcu = 0;
							@endphp

							@foreach($comprobantes as $comp)
								@if($comp->are_emp_id == $ar->are_emp_id)
									@php
										$total = $total+$comp->cantidad;
										$totalca = $totalca + ($comp->cantidad*($comp->costo_total/$comp->cantidad));
										$totalcu = $totalcu + ($comp->costo_total/$comp->cantidad);
									@endphp
								<tr>
									<td>{{$comp->are_emp_des}}</td>
									<td>{{$comp->cdedes}}</td>
									<td style="text-align:right;">{{number_format($comp->cantidad,2,'.','')}}</td>
									<td style="text-align:right;">{{number_format($comp->costo_total/$comp->cantidad,4,'.','')}}</td>
									<td style="text-align:right;">{{number_format($comp->cantidad*($comp->costo_total/$comp->cantidad),3,'.','')}}</td>
									
								</tr>
								@endif
							@endforeach
							<tr style="background:gray;color:white;">
									<td colspan="2" style="text-align:right;"><strong>TOTAL PRODUCTOS - {{$ar->are_emp_des}}</strong></td>
									<td style="text-align:right;"><strong>{{number_format($total,2,'.','')}}</strong></td>
									<td style="text-align:right;"><strong></strong></td>
									<td style="text-align:right;"><strong>{{number_format($totalca,3,'.','')}}</strong></td>
							</tr>

							@php
							 $totalgeneral = $totalgeneral + $totalca;
							@endphp
							@endforeach
						<tr>
							<td colspan="2" style="text-align:right;"><strong>TOTAL PRODUCTOS</strong></td>
							<td style="text-align:right;"><strong>{{number_format($cantidad,2,'.','')}}</strong></td>
							<td style="text-align:right;"><strong></strong></td>
							<td style="text-align:right;"><strong>{{number_format($totalgeneral,2,'.','')}}</strong></td>
						</tr>
					</tbody>
				</table><br>

				@endif
			</div>	

		</div>	
	</div>
</div>
</section>

@endsection