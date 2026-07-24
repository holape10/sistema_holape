@extends('layouts.empresas')
@section('contenido')

<section class="content">
	<div class="row">
		<div class="col-xs-12">
			<div class="box">
				<div class="box-header with-border" style="background-color:blue;">
					<center><font color="white"><strong>SUBFAMILIA</strong></font></center>
					<div class="box-tools pull-right">
						<a href="/subcategorias/create"><button class="btn btn-success btn-sm"> Nuevo</button></a>
					</div>
				</div>
				<div class="box-body">
					<div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
						@include('empresas.subcategorias.search')
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
					<th>LINEA</th> <th>FAMILIA</th>
					<th>SUBFAMILIA</th>
					<th>OPCIONES</th>
				</thead>
				@foreach ($subcategorias as $subcat)
				<tr>
					<td><strong>{{ $subcat->tip_pro_nom }}</strong></td>
					<td>{{ $subcat->cat_nom }}</td>
					<td>{{ $subcat->subcat_nom }}</td>
					<td>
						<a href="{{URL::action('SubcategoriasController@edit',$subcat->subcat_id)}}"><button class="btn btn-info">Editar</button></a>
						<a href="" data-target="#modal-delete-{{$subcat->subcat_id}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a>
					</td>
				</tr>
				@include('empresas.subcategorias.modal')
				@endforeach
			</table>
		</div>
		{{$subcategorias->render()}}
	</div>
</div>
</div>
</section>
@endsection