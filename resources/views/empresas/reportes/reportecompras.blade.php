@extends('layouts.empresas')
@section('contenido')
<script type="text/javascript">


   $(document).ready(function()
    {


	  $("#btnexport").on("click", function() {

        $("#opbtn").val('2');

        $("#frmcompras").submit();

     })

	  	$("#btnbuscar").on("click", function() {

        $("#opbtn").val('1');
         $("#frmcompras").submit();a

     })

	});

</script>
<script>

	var href = $('#btnPrint').attr('href');
	
	$("#btnPrint").printPage({
		

		url: href,
		attr: "href",
		messageBox:false,

	})
</script>
<style>
input[type=date]::-webkit-inner-spin-button, 
input[type=date]::-webkit-clear-button,
input[type=date]::-webkit-outer-spin-button { 
	-webkit-appearance: none; 
	margin: 0; 
}

</style>

<section class="content">

	<div class="row">
		<div class="col-xs-12">
			<div class="box">
				<div class="box-header" style="background-color:blue;">
					<font color="white"><strong><center>REPORTE DE COMPRAS</center></strong></font>
				</div>
				<div class="box-body">
					{!! Form::model(Request::all(),['Route'=>'/reportecompra','method'=>'GET','autocomplete'=>'off','id'=>'frmcompras'])!!}
					<div class="row">
						<div class="col-lg-2">
							<div class="form-group form-group-sm">
								<label class="control-label" for="fecin">Desde </label>

								<input type="date" name="fecin" value="{{$fecin}}" class="form-control input-sm">
							</div>
						</div>
						<div class="col-lg-2">
							<div class="form-group form-group-sm">
								<label class="control-label" for="fecfin">Hasta </label>
								<input type="date" name="fecfin" value="{{$fecfin}}" class="form-control input-sm">

							</div>
						</div>
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
								<label>Tipo</label>
								<select class="form-control" name="tipo">
								
									<option value="1">Compras</option>
									<option value="2">Compras Detallado</option>
									<option value="3">Promedio Compras</option>
								</select>
							</div>
						</div>
						<div class="col-lg-3">
							<div class="form-group form-group-sm">
								<label>Proveedores</label>
								
								<select name="proveedor" id="proveedor" class="form-control">
									<option value="0">Todos</option>
									@foreach($proveedores as $prov)
									@if($prov->prov_id == $proveedor)
									<option selected="selected" value='{{$prov->prov_id}}'>{{$prov->prov_raz}}</option>

									@else
									<option value='{{$prov->prov_id}}'>{{$prov->prov_raz}}</option>
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
									<button type="button" id="btnbuscar" class=" btn btn-primary btn-sm">Buscar</button>
								</div>
								<div class="btn-group" >
									<button type="button" id="btnexport"   class=" btn btn-success btn-sm"> Excel</button>
								</div>
								<input type="hidden" readonly="readonly" name="opbtn" id="opbtn">


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
				<div class="box-body">
					@if($tipo =='1')
					<table id="tblCompra"  class="table table-bordered table-hover">
						<thead>
							<tr>
								<th>Fec. Compra</th>
								<th>Fec. Vencimiento</th>
								<th>Documento</th>
								<th>Serie</th>
								<th>N°</th>
								<th>RUC PROVEEDOR</th>
								<th style="width:210px;">Nombre o Razón Social</th>
								<th>Moneda</th>
								<th>Total</th>
								<th>Estado</th>
							
							</tr>
						</thead>
						<tbody>
							@foreach($compras as $comp)
							<tr>
								<td>{{$comp->com_fec}}</td>
								<td>{{$comp->com_fec_ven}}</td>
								<td>{{$comp->tdodes}}</td>
								<td>{{$comp->com_doc_ser}}</td>
								<td>{{$comp->com_doc_num}}</td>
								<td>{{$comp->prov_ruc}}</td>
								<td>{{$comp->prov_raz}}</td>
								<td>{{$comp->monnom}}</td>
								<td>{{number_format($comp->total_com,'2','.',',')}}</td>
								<td>{{$comp->est_compra}}</td>
								
							</tr>
							@include('empresas.compras.modal')
							@endforeach
						</tbody>
					</table><br>
					@elseif($tipo=='2')
					<table id="tblCompra"  class="table table-bordered table-hover">
						<thead>
							<tr>
								<th>Fec. Compra</th>
								<th>Fec. Vencimiento</th>
								<th>Documento</th>
								<th>Serie</th>
								<th>N°</th>
								<th>RUC PROVEEDOR</th>
								<th style="width:210px;">Proveedor</th>
								<th>Producto</th>
								<th>P.U</th>
								<th>Moneda</th>
								<th>Total</th>
								<th>Estado</th>
							
							</tr>
						</thead>
						<tbody>
							@foreach($compras as $comp)
							<tr>
								<td>{{$comp->com_fec}}</td>
								<td>{{$comp->com_fec_ven}}</td>
								<td>{{$comp->tdodes}}</td>
								<td>{{$comp->com_doc_ser}}</td>
								<td>{{$comp->com_doc_num}}</td>
								<td>{{$comp->prov_ruc}}</td>
								<td>{{$comp->prov_raz}}</td>
								<td>{{$comp->pronom}}</td>
								<td>{{number_format($comp->pre_uni,'2','.',',')}}</td>
								<td>{{$comp->monnom}}</td>
								<td>{{number_format($comp->total,'2','.',',')}}</td>
								<td>{{$comp->est_compra}}</td>
								
							</tr>
							@include('empresas.compras.modal')
							@endforeach
						</tbody>
					</table><br>
					@elseif($tipo=='3')
					<table id="tblCompra"  class="table table-bordered table-hover">
						<thead>
							<tr>
								<th>Producto</th>
								<th>Cantidad</th>
								<th>Promedio</th>
								
							
							</tr>
						</thead>
						<tbody>
							@foreach($compras as $comp)
							<tr>
								<td>{{$comp->pronom}}</td>
								<td>{{$comp->cantidad}}</td>
								<td>{{$comp->cantidad/$diferencia}}</td>
								
								
							</tr>
						
							@endforeach
						</tbody>
					</table><br>

					@endif
				</div>	
			</div>	
		</div>
	</div>
</section>

@endsection