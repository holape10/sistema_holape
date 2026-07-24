@extends('layouts.empresas')
@section('contenido')


<section class="content">
	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
	            	 <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
				<h4><i class='glyphicon glyphicon-search'></i> CONSULTAR TARIFAS <a href="tarifas/create"><button class="btn btn-success"> Nuevo</button></a></h4>
				@include('empresas.tarifas.search')
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
					<th>TARIFA POR</th>
					<th>DESCRIPCION</th>
					<th>VEHICULO</th>
					<th>PRECIO U.</th>
					<th>VALOR U.</th>
					<th>TOLERANCIA </th>
					<th>OPCIONES</th>
				</thead>
				@foreach ($tarifas as $tarifa)
				<tr>
					<td>{{$tarifa->nom_uni_tie}}</td>
					<td>{{$tarifa->descripcion}}</td>
					<td>{{$tarifa->nomvehiculo}}</td>
					<td>{{$tarifa->precio}}</td>
					<td>{{$tarifa->preciosinigv}}</td>
					<td>{{$tarifa->tolerancia}}</td>
					<td>
						<a href="/editartarifa/{{$tarifa->id_tarifa}}"><button class="btn btn-info">Editar</button></a>
                        <a href="" data-target="#modal-delete-{{$tarifa->id_tarifa}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a>

					</td>
				</tr>
				@include('valetparking.tarifas.modal')
				@endforeach
			</table>
		</div>
		{{$tarifas->render()}}
	</div>
</div>
</div>
</section>
@endsection
