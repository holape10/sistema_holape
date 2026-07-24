@extends('layouts.administrador')
@section('contenido')

<section class="content">
		<div class="row">
				<div class="col-xs-12">
					<div class="box">
						<div class="box-body">
							<h3>Listado Rutas del Sistema <a href="/rutas/crear"><button class="btn btn-success">Nuevo</button></a></h3>
							
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
								<th>DESCRIPCION</th>
								<th>RUTA</th>
								<th colspan="3">Opciones</th>
							</thead>
							@foreach ($rutas as $ruta)
							<tr>
								<td>{{$ruta->descripcion}}</td>
								<td>{{$ruta->ruta}}</td>
								<td>
									<a href="/rutas/editar/{{$ruta->Id}}"><button class="btn btn-info">Editar</button></a>
									<a href="" data-target="#modal-delete-{{$ruta->Id}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a>
								</td>
							
							</tr>
							@include('administrador.configuracionsistema.modaleliminarrutasistema')
							@endforeach
						</table>
						</div>	
			{{$rutas->render()}}
				</div>	
			</div>
		</div>
</section>
@endsection