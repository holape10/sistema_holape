@extends('layouts.empresas')
@section('contenido')


<section class="content">
	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
	            	 <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
				<h4><i class='glyphicon glyphicon-search'></i> CONSULTAR TIPOS CAJA <a href="tiposcaja/create"><button class="btn btn-success"> Nuevo</button></a></h4>
				@include('empresas.tiposcaja.search')
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
					<th>TIPOS CAJA</th>
					<th>MOVIMIENTO</th>
					<th>OPCIONES</th>
				</thead>
				@foreach ($cajas as $caja)
				<tr>
					<td>{{$caja->tip_caj_id}}</td>
					<td>{{$caja->tip_caj_nom}}</td>
					<td>{{$caja->tipo}}</td>
					<td>
						<a href="{{URL::action('TiposCajaController@edit',$caja->tip_caj_id)}}"><button class="btn btn-info">Editar</button></a>
                        <a href="" data-target="#modal-delete-{{$caja->tip_caj_id}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a>

					</td>
				</tr>
				@include('empresas.tiposcaja.modal')
				@endforeach
			</table>
		</div>
		{{$cajas->render()}}
	</div>
</div>
</div>
</section>
@endsection
