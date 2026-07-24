@extends('layouts.empresas')
@section('contenido')


<section class="content">
	<div class="row">
        	<div class="col-xs-12">
        		<div class="box">
        			<div class="box-header" style="background-color:blue;">
        				<font color="white"><center><strong>Consultar Modelo</strong></center></font>
        				<div class="box-tools pull-right">
                			<a href="modelos/create"><button class="btn btn-sm btn-success"> Nuevo</button></a>
              			</div>
        			</div>

        			<div class="box-body">
        					@include('empresas.modelos.search')
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
					<th style="width:90%">MODELO</th>
					<th>OPCIONES</th>
				</thead>
				@foreach ($modelos as $modelo)
				<tr>
					<!--<td>{{$modelo->mod_id}}</td>-->
					<td>{{$modelo->mod_nom}}</td>
					<td>
						<a href="{{URL::action('ModelosController@edit',$modelo->mod_id)}}"><button class="btn btn-info">Editar</button></a>
                        <a href="" data-target="#modal-delete-{{$modelo->mod_id}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a>

					</td>
				</tr>
				@include('empresas.modelos.modal')
				@endforeach
			</table>
		</div>
		{{$modelos->render()}}
	</div>
</div>
</div>
</section>
@endsection
