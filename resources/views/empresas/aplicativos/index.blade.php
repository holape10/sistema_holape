@extends('layouts.empresas')
@section('contenido')


<section class="content">
	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
	            	 <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
				<h4><i class='glyphicon glyphicon-search'></i> CONSULTAR APLICATIVOS <a href="/aplicativos/create"><button class="btn btn-success"> Nuevo</button></a></h4>
				@include('empresas.aplicativos.search')
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
					
					<th>APLICATIVOS</th>
					<th>OPCIONES</th>
				</thead>
				@foreach ($aplicativos as $apli)
				<tr>
					
					<td>{{$apli->apli_nom}}</td>
					<td>
						@if($apli->apli_nom=='SALON' || $apli->apli_nom=="LLEVAR")
						<a href=""><button disabled="disabled" class="btn btn-info">Editar</button></a>

						
                        <a href="" data-toggle="modal"><button disabled="disabled" class="btn btn-danger">Eliminar</button></a>
                       	@else
                       	<a href="{{URL::action('AplicativosController@edit',$apli->apli_id)}}"><button class="btn btn-info">Editar</button></a>

						
                        <a href="" data-target="#modal-delete-{{$apli->apli_id}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a>
                        @endif

					</td>
				</tr>
				@include('empresas.aplicativos.modal')
				@endforeach
			</table>
		</div>
		{{$aplicativos->render()}}
	</div>
</div>
</div>
</section>
@endsection
