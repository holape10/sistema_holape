@extends('layouts.empresas')
@section('contenido')


<section class="content">
	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
	            	 <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
				<h4><i class='glyphicon glyphicon-search'></i> CONSULTAR BANCO<a href="bancos/create"><button class="btn btn-success"> Nuevo</button></a></h4>
				@include('empresas.bancos.search')
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
					<!--<th>CODIGO</th>-->
					<th>BANCO</th>
					<th>OPCIONES</th>
				</thead>
				@foreach ($bancos as $banco)
				<tr>
					<!--<td>{{$banco->ban_id}}</td>-->
					<td>{{$banco->ban_nom}}</td>
					<td>
						<a href="{{URL::action('MesasController@edit',$banco->ban_id)}}"><button class="btn btn-info">Editar</button></a>
                        <a href="" data-target="#modal-delete-{{$banco->ban_id}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a>

					</td>
				</tr>
				@include('empresas.bancos.modal')
				@endforeach
			</table>
		</div>
		{{$bancos->render()}}
	</div>
</div>
</div>
</section>
@endsection
