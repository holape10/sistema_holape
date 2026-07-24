@extends('layouts.empresas')
@section('contenido')


<section class="content">
	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
          			<div class="box-header with-border" style="background-color:blue;">
                        <center><font color="white"><strong>PROCESOS</strong></font></center>
                        <div class="box-tools pull-right">
                			<a href="/procesos/create"><button  class="btn btn-success btn-sm"> Nuevo</button></a>
              			</div>
                	</div>
	            	<div class="box-body">
	            	 	<div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
							@include('empresas.procesos.search')
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
					
					<th>PROCESOS</th>
					<th>OPCIONES</th>
				</thead>
				@foreach ($procesos as $proceso)
				<tr>
					<td>{{$proceso->proc_cod}}</td>
					<td>{{$proceso->proc_nom}}</td>
					<td>
						
						<a href="{{URL::action('ProcesosController@edit',$proceso->proc_id)}}"><button class="btn btn-info">Editar</button></a>
                        <a href="" data-target="#modal-delete-{{$proceso->proc_id}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a>

					</td>
				</tr>
				@include('empresas.procesos.modal')
				@endforeach
			</table>
		</div>
		{{$procesos->render()}}
	</div>
</div>
</div>
</section>
@endsection
