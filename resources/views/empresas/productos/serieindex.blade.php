@extends('layouts.empresas')
@section('contenido')

<section class="content">
	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
	            	 <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
				<h4><i class='glyphicon glyphicon-search'></i> CONSULTAR PRESENTACIONES </h4>
			
			</div>
				     	</div>
	            </div>
	        </div>
	</div>

<div class="row">
    <div class="col-xs-12">
    	<div class="box">
	       	<div class="box-body">
		<table id='tblseries' class="table table-striped table-bordered table-condensed table-hover">
				<thead>
					<th>PRESENTACION</th>
					<th>DESCRIPCION</th>
		            <th>OPCIONES</th>
				</thead>
				
				@foreach ($series as $pro)
				<tr>
					<td>{{$pro->Presentacion}}</td>
					<td>{{$pro->Descripcion}}</td>
					<td><a href="" data-target="#modal-delete-{{$pro->IdPresentacion}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a></td>
				
				</tr>
			    @include('empresas.productos.eliminarpresentacion')
				@endforeach
			</table>
		</div>
	
	</div>
</div>
</div>
</section>
@endsection
