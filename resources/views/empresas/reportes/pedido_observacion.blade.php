@extends('layouts.empresas')
@section('contenido')
<script type="text/javascript">
	$(document).ready(function(){


	   $("#btnExportar").click(function() {
        var accion = $(this).attr('dir');

        $('#frmReporte').attr('action', accion);
  
        $('#frmReporte').submit();
    	});

	});
</script>





	<section class="content">	
		<div class="row">
			<div class="col-xs-12">
				<div class="box">
					<div class="box-header box-success" style="background-color: #3c8dbc;">
						<font color="white" size="4"><center><strong>REPORTES - OBSERVACIONES DE COMANDAS</strong></center></font>
					</div>
					<div class="box-body">
						{!!Form::open(array('url'=>'/reportepedido','autocomplete'=>'off','method'=>'POST','id'=>'frmReporte','role'=>'form','files'=>'true'))!!}
						{{Form::token()}}

						<div class="row">
							<div class="col-lg-2">
								<div class="form-group form-group-sm">
									<label class="control-label" for="fecin">Desde </label>
									<input type="date" name="fec_ini" value="{{Carbon::now()->startOfMonth()->format('Y-m-d')}}" class="form-control">

								</div>
							</div>
							<div class="col-lg-2">
								<div class="form-group form-group-sm">
									<label class="control-label" for="fecfin">Hasta </label>
									<input type="date" name="fec_fin" value="{{Carbon::now()->endOfMonth()->format('Y-m-d')}}" class="form-control">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-6">
								<div class="btn-group" >
									<button type="button" id="btnBuscar" class=" btn btn-primary btn-sm">BUSCAR</button>
								</div>
								<div class="btn-group">
									<button type="button" id="btnExportar" dir="/reportepedidoexcel" class="btn btn-primary btn-sm">Exportar Excel</button>
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
						<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm">
							<thead>
								<tr style="background-color: #3c8dbc;color:white;">
									<th colspan="6"><center><strong>REPORTE DE OBSERVACIONES DE COMANDAS DESDE: {{$fec_ini}} HASTA {{$fec_fin}}</strong></center>
									
									</tr>
								

									<tr>

										<th>PRODUCTO</th>
										<th>OBSERVACIONES</th>
									</tr>
								</thead>

								<tbody>
										@foreach($pedidos as $ped)
											@if(!empty($ped->item_obs))
											<tr>
												<td>{{$ped->descripcion}}</td>
												<td>{{$ped->item_obs}}</td>
											</tr>
											@endif
										@endforeach
								</tbody>
							</table><br>
						</div>	

					</div>
				</div>
			</div>
		</section>



		@endsection