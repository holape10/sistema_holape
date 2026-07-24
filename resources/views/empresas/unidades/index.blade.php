@extends('layouts.empresas')
@section('contenido')


<section class="content">
	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
	            	 <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
				<h4><i class='glyphicon glyphicon-search'></i> CONSULTAR UNIDADES DE MEDIDA<a href="/unidades/create"><button class="btn btn-success"> Nuevo</button></a></h4>
				@include('empresas.unidades.search')
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
					<th>CODIGO</th>
					<th>UNIDAD</th>
					<th>OPCIONES</th>
				</thead>
				@foreach ($unidades as $um)
				<tr>
					<td>{{$um->umecod}}</td>
					<td>{{$um->umenom}}</td>
					<td>
						<a href="{{URL::action('UnidadesController@edit',$um->ume_id)}}"><button class="btn btn-info">Editar</button></a>
                        <a href="" data-target="#modal-delete-{{$um->ume_id}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a>

					</td>
				</tr>
				@include('empresas.unidades.modal')
				@endforeach
			</table>
		</div>
		{{$unidades->render()}}
	</div>
</div>
</div>
</section>
@endsection
