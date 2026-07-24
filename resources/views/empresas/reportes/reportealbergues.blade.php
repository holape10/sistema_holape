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
				<div class="box-header" style="background-color:#337ab7;">
					<font color="white"><strong><center>REPORTE DE ALBERGUES</center></strong></font>
				</div>
				<div class="box-body">
					{!! Form::model(Request::all(),['Route'=>'/reportealbergues','method'=>'GET','autocomplete'=>'off','id'=>'frmcompras'])!!}
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
								
									<option value="1">Reporte por Servicio</option>
									<!--<option value="2">Reporte por Tipo de Plato</option>-->
							
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
								<!--<div class="btn-group" >
									<button type="button" id="btnexport"   class=" btn btn-success btn-sm"> Excel</button>
								</div>-->
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
					
					<table id=""  class="table table-bordered table-hover">
						<thead>
							<tr style="background:#337ab7;">
								<th colspan="3" ><font color="white"><strong><CENTER>AGRUPADO POR FECHA - SERVICIO</CENTER></strong></font></th></th>
							</tr>
							<tr>
								<th>Fecha</th>
								<th>Servicio</th>
								<th>Total</th>
								
							</tr>
						</thead>
						<tbody>
						
							@foreach($grup_serv as $gs)
							<tr>
								<td>{{$gs->ped_ser_fec}}</td>
								<td>{{$gs->ser_nom}}</td>
								<td>{{$gs->cantidad}}</td>
							</tr>
							@endforeach
						</tbody>
					</table><br>

					<table id=""  class="table table-bordered table-hover">
						<thead>
							<tr style="background:#337ab7;">
								<th colspan="4" ><font color="white"><strong><CENTER>AGRUPADO POR FECHA - PRODUCTO</CENTER></strong></font></th></th>
							</tr>
							<tr>
								<th>Fecha</th>
								<th>Servicio</th>
								<th>Producto</th>
								<th>Total</th>
								
							</tr>
						</thead>
						<tbody>
						
							@foreach($grup_plat as $gp)
							<tr>
								<td>{{$gp->ped_ser_fec}}</td>
								<td>{{$gp->ser_nom}}</td>
								<td>{{$gp->pronom}}</td>
								<td>{{$gp->cantidad}}</td>
							</tr>
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