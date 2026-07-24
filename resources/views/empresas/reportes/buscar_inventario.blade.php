@extends('layouts.empresas')
@section('contenido')


<script type="text/javascript">
	
	$(document).ready(function()
   {


   	  	  $("#btnBuscar").click(function() {
        var accion = $(this).attr('dir');

        $('#formstock').attr('action', accion);
        $('#formstock').submit();
    });


   	  $("#btnGenPdf").click(function() {
        var accion = $(this).attr('dir');

        $('#formstock').attr('action', accion);
       	$('#formstock').attr('target', '_blank');
        $('#formstock').submit();
    });



   	   $("#btnExportar").click(function() {
        var accion = $(this).attr('dir');

        $('#formstock').attr('action', accion);
  	
        $('#formstock').submit();
    });
});
</script>
<section class="content">	
	<div class="row">
		<div class="col-xs-12">
			<div class="box">
				<div class="box-header box-success" style="background-color:#337ab7;">
        			<font color="white" size="3"><center><strong>REPORTE DE INVENTARIO</strong></center></font>
        		</div>

				<div class="box-body">
					{!! Form::open(array('url'=>'/reporteinventario','method'=>'POST','autocomplete'=>'off','role'=>'search','id'=>'formstock'))!!}
				<div class="row">
					<div class="col-lg-2">
						<div class="form-group form-group-sm">
							<label>Empresas</label>
							<select name="sucursal" id="sucursal" class="form-control">
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

					<div class="col-lg-2" id="divalmacen">
						<div class="form-group form-group-sm">
							<label>Almacenes</label>
							<select name="almacen" id="almacen" class="form-control">

								@foreach($almacenes as $alm)
								@if($alm->id_almacen == $almacen)
								<option selected="selected" value="{{$alm->id_almacen}}">{{$alm->descripcion}}</option>
								@else
								<option value="{{$alm->id_almacen}}">{{$alm->descripcion}}</option>
								@endif
								@endforeach
							</select>
						</div>
					</div>

					<div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
						<div class="form-group form-group-sm">
							<label>Período</label>
							<input type="month" class="form-control" name='periodo'>
							</div>
						</div>  
						
					
					</div>
					<div class="row">
						<div class="col-lg-6">
							<div class="btn-group" >
									<button type="button" id="btnBuscar" dir="/reporteinventario" class=" btn btn-primary btn-sm">BUSCAR</button>
							</div>
							<div class="btn-group">
								<button type="button" id="btnExportar" dir="/reporteinventarioexcel" class="btn btn-primary btn-sm">Exportar Excel</button>
							</div>
							<div class="btn-group">
								<button type="button" id="btnGenPdf" dir="/reporteinventariopdf" class="btn btn-primary btn-sm">GENERAR REPORTE</button>	
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
			<div class="box table table-responsive">
	            	<div class="box-body" id="divreporte">
							@include('empresas.reportes.reporte_inventario')
					</div>	
				
				</div>
		</div>
	</div>
		</section>

		@endsection