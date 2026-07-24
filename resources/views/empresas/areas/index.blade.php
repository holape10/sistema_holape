@extends('layouts.empresas')
@section('contenido')


<section class="content">
	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
          			<div class="box-header with-border" style="background-color:blue;">
                        <center><font color="white"><strong>AREAS</strong></font></center>
                        <div class="box-tools pull-right">
                			<a href="/areas/create"><button  class="btn btn-success btn-sm"> Nuevo</button></a>
              			</div>
                	</div>
	            	<div class="box-body">
	            	 	<div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
							@include('empresas.areas.search')
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
					
					<th>AREA</th>
					<th>OPCIONES</th>
				</thead>
				@foreach ($areas as $area)
				<tr>
					<td>{{$area->are_emp_cod}}</td>
					<td>{{$area->are_emp_des}}</td>
					<td>
						
						<a href="{{URL::action('AreasController@edit',$area->are_emp_id)}}"><button class="btn btn-info">Editar</button></a>
                        <a href="" data-target="#modal-delete-{{$area->are_emp_id}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a>

					</td>
				</tr>
				@include('empresas.areas.modal')
				@endforeach
			</table>
		</div>
		{{$areas->render()}}
	</div>
</div>
</div>
</section>
@endsection
