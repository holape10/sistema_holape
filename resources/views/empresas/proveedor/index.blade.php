@extends('layouts.empresas')
@section('contenido')


	<section class="content">
	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
	            		<div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
				<h4><i class='glyphicon glyphicon-search'></i> CONSULTAR PROVEEDOR <a href="/proveedor/create"><button class="btn btn-success"> Nuevo</button></a></h4>
				@include('empresas.proveedor.search')
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
					<th>RUC</th>
					<th>Razón Social</th>
					<th>Dirección</th>
					<th>Correo Electrónico</th>
					<th>Número Contacto</th>
				
					<th>Opciones</th>
				</thead>
				@foreach ($proveedores as $prov)
				<tr>
					<td>{{$prov->prov_ruc}}</td>
					<td>{{$prov->prov_raz}}</td>
					<td>{{$prov->prov_dir}}</td>
					<td>{{$prov->prov_cor}}</td>
					<td>{{$prov->prov_num_con}}</td>

					<td>
						<a href="{{URL::action('ProveedorController@edit',$prov->prov_id)}}"><button class="btn btn-info">Editar</button></a>
                         <a href="" data-target="#modal-delete-{{$prov->prov_id}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a><br>

					</td>
				</tr>
				@include('empresas.proveedor.modal')
				@endforeach
			</table>
		</div>
		{{$proveedores->render()}}
	</div>
</div>
</div>
</section>
@endsection
