@extends('layouts.empresas')
@section('contenido')


<section class="content">
	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
	            	 <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
				<h4><i class='glyphicon glyphicon-search'></i>FICHAS DE VEHICULOS <a href="tiposvehiculos/create"><button class="btn btn-success"> Nuevo</button></a></h4>
				@include('empresas.tiposvehiculos.search')
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
					
					<th>PLACA</th>
					<th>OPCIONES</th>
				</thead>
				@foreach ($vehiculos as $vehiculo)
				<tr>
					
					<td>{{$vehiculo->placa}}</td>
					<td>
						<a href="{{URL::action('TiposVehiculosController@edit',$vehiculo->id_tipo_vehiculo)}}"><button class="btn btn-info">Editar</button></a>
                        <a href="" data-target="#modal-delete-{{$vehiculo->id_tipo_vehiculo}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a>

					</td>
				</tr>
				@include('empresas.tiposvehiculos.modal')
				@endforeach
			</table>
		</div>
		{{$vehiculos->render()}}
	</div>
</div>
</div>
</section>
@endsection
