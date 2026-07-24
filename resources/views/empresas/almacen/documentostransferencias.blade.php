@extends('layouts.empresas')
@section('contenido')
<script>
$(document).ready(function()
{       


      $("#promocion").change(function() {
         
              $('#formalmacen').submit();
        

        });



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
			  <strong>Información!</strong> {{ session('success') }}
			</div>
	    @endif
	</div>
</div>

	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
          			<div class="box-header" style="background-color:#337ab7;">
          				<font color="white"><CENTER><strong>Movimientos de Productos</strong></CENTER></font>
          				  <div class="box-tools pull-right">
				             <a  href="/transferir"><button type="button" class="btn btn-success btn-sm"><strong>Transferir Terceros</strong></button></a>
				               <a  href="/transferiralmacenes"><button type="button" class="btn btn-success btn-sm"><strong>Transferir Establecimientos</strong></button></a>
				          </div>
				    </div>
	            	<div class="box-body">
	            		

{!! Form::model(Request::all(),['Route'=>'/transferencias','method'=>'GET','autocomplete'=>'off','id'=>'formalmacen'])!!}


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
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
			 <label class="control-label" for="fecin">Desde </label>

		
				 <input type="date" name="fecin" value="{{$fecin}}" class="form-control">
	

		
		</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
		 	<label class="control-label" for="fecfin">Hasta </label>
		<!--	<input for="fecfin" name="fecfin" id="fecfin" class="form-control input-sm" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control" type="date">-->

		
				 <input type="date" name="fecfin" value="{{$fecfin}}" class="form-control">
		
			
		</div>
	</div>
	 <div  class="col-lg-2">
	<div class="form-group form-group-sm form-group form-group-sm-sm">
		<label>Documento</label>
		<select name="tipo" id="tipo" class="form-control">
		@foreach($tipodocumentos as $tp)
			@if($tp->tdocod =='81' || $tp->tdocod=='82')
				@if($tipo == $tp->tdocod)
					<option selected="selected" value="{{$tp->tdocod}}">{{$tp->tdodes}}</option>
				@else
					<option value="{{$tp->tdocod}}">{{$tp->tdodes}}</option>
				@endif
			
			@endif
		@endforeach
		</select>
	</div>
</div>
</div>
<div class="row">
	<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
	<div class="form-group form-group-sm form-group form-group-sm-sm">
		
	
			<span class="input-group-btn">
				<button type="submit" id="enviar" class="btn btn-sm btn-primary">Buscar</button>
			</span>
		
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
						<table id=""  class="table table-bordered table-hover">
							<thead>
								<th colspan="11" style="background:#337ab7;">
									<font color="white"><strong><center>TRANSFERENCIAS DESDE: {{$fecin}} - HASTA {{$fecfin}}</center></strong></font>
								</th>
							</thead>
							<thead>
								<tr>
									<th>Fecha</th>
									<th>Documento</th>
									<th>Serie-Numero</th>
									<th>Guia Remision</th>
									<th>Empresa Origen</th>
									<th>Almacen Origen</th>
									<th>Empresa Destino</th>
									<th>Almacen Destino</th>
									<th>Opciones</th>
									<th>Estado</th>
									
								</tr>
							</thead>
							<tbody>

								@foreach($documentos as $mov)
								<tr>
								 	<td>{{$mov->fecha}}</td>
								 	<td>{{$mov->tdodes}}</td>
								 	<td>{{$mov->serieguia}}-{{$mov->numeroguia}}</td>
								 	<td><a href="/descargarguia/{{$mov->IdCpe_guia}}/pdf"><center><i class="fa fa-file-pdf-o fa-lg"></i></center></a></td>
									<td>{{$mov->suc_origen}}</td>
									<td>{{$mov->alm_origen}}</td>
									<td>{{$mov->suc_destino}}</td>
									<td>{{$mov->alm_destino}}</td>
								    <td>
									
									@if($mov->estado =='ANULADO')
										<button type="button" disabled="disabled" class="btn btn-sm btn-info">Editar</button></a>
									
									    <button type="button" disabled="disabled" class="btn btn-sm btn-danger">Eliminar</button></a>

									@else

									 @if(empty($mov->suc_destino))
										<a href="/editarmovimiento/{{$mov->mov_cab_id}}"><button type="button" class="btn btn-sm btn-info">Editar</button></a>
									@else
										<a href="/editarmovimientoalmacenes/{{$mov->mov_cab_id}}"><button type="button" class="btn btn-sm btn-info">Editar</button></a>

									@endif
									
									<a href="" data-target="#modal-eliminar-{{$mov->mov_cab_id}}" data-toggle="modal"><button type="button" class="btn btn-sm btn-danger">Eliminar</button></a>

									@endif
									
									</td>
									
									@if($mov->estado =='RECEPCIONAR')
										<td><a href="/recepcionar/{{$mov->mov_cab_id}}"><button class="btn btn-danger btn-sm btn-block">Por Recepcionar</button></a></td>
									@elseif($mov->estado =='ANULADO')
												<td><button class="btn btn-danger btn-sm btn-block" disabled="disabled">Anulado</button></td>
									@else
										<td><button class="btn btn-success btn-sm btn-block" disabled="disabled">Registrado</button></td>
									@endif
									
									
								</tr>
								@include('empresas.almacen.modal')
								@endforeach
							</tbody>
						</table><br>
					</div>	
					{{$documentos->render()}}
				</div>	
			</div>
		</div>
	</section>

@endsection