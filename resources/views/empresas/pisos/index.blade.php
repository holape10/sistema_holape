@extends('layouts.empresas')
@section('contenido')


<section class="content">
	
	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
          			<div class="box-header" style="background-color:blue;">
          				<font color="white"><center><strong>CONSULTAR PISO</strong></center></font>
          				<div class="box-tools pull-right">
                			<a href="/pisos/create"><button  class="btn btn-success btn-sm"> Nuevo</button></a>
              			</div>
          			</div>
	            	<div class="box-body">
	            	 <div class="col-lg-12 col-md-12 col-sm-8 col-xs-8">
		
				@include('empresas.pisos.search')
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
					<th>PISO</th>
					<th>OPCIONES</th>
				</thead>
				@foreach ($pisos as $piso)
				<tr>
					<!--<td>{{$piso->pis_id}}</td>-->
					<td>{{$piso->pis_nom}}</td>
					<td>
						<a href="{{URL::action('PisosController@edit',$piso->pis_id)}}"><button class="btn btn-info">Editar</button></a>
                        <a href="" data-target="#modal-delete-{{$piso->pis_id}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a>

					</td>
				</tr>
				@include('empresas.pisos.modal')
				@endforeach
			</table>
		</div>
		{{$pisos->render()}}
	</div>
</div>
</div>
</section>
@endsection
