@extends('layouts.empresas')
@section('contenido')


<section class="content">
	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
	            	 <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
				<h4><i class='glyphicon glyphicon-search'></i> CONSULTAR PRINCIPIO ACTIVO <a href="/principioactivo/create"><button class="btn btn-success"> Nuevo</button></a></h4>
				@include('empresas.principioactivo.search')
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
				
					<th>PRINCIPIO ACTIVO</th>
					<th>OPCIONES</th>
				</thead>
				@foreach ($principioactivo as $tm)
				<tr>
					
					<td>{{$tm->pri_act_nom}}</td>
					<td>
						<a href="{{URL::action('PrincipioActivoController@edit',$tm->pri_act_id)}}"><button class="btn btn-info">Editar</button></a>
                        <a href="" data-target="#modal-delete-{{$tm->pri_act_id}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a>

					</td>
				</tr>
				@include('empresas.principioactivo.modal')
				@endforeach
			</table>
		</div>
		{{$principioactivo->render()}}
	</div>
</div>
</div>
</section>
@endsection
