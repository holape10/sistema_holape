@extends('layouts.empresas')
@section('contenido')


<section class="content">
<div class="row">
    <div class="col-xs-12">
    	<div class="box">
    			<div class="box-header" style="background:blue;">
	            
						<font color="white"><center><strong> REGISTRO DE TIPOS DE EQUIPOS</strong></center></font>
							<div class="box-tools pull-right">
                			<a href="tipoequipo/create"><button class="btn btn-sm btn-success"> Nuevo</button></a>
              			</div>
		
				</div>
				<div class="box-body">
					@include('empresas.tipo_equipo.search')
				</div>
		</div>
	<div class="box">
	       	<div class="box-body">
	       		
			<table class="table table-striped table-bordered table-condensed table-hover">
				<thead>
				
					<th style="width:90%;">TIPO DE EQUIPO</th>
					<th>OPCIONES</th>
				</thead>
				@foreach ($tipo_equipo as $te)
				<tr>
					<td>{{$te->nom_tip_equi}}</td>
					<td>
						<a href="{{URL::action('TipoEquipoController@edit',$te->id_tip_equi)}}"><button class="btn btn-info">Editar</button></a>
                        <a href="" data-target="#modal-delete-{{$te->id_tip_equi}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a>

					</td>
				</tr>
				@include('empresas.tipo_equipo.modal')
				@endforeach
			</table>
		</div>
		{{$tipo_equipo->render()}}
	</div>
</div>
</div>
</section>
@endsection
