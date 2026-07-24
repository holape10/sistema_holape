@extends('layouts.empresas')
@section('contenido')


<section class="content">
	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
          			<div class="box-header with-border" style="background-color:blue;">
                        <center><font color="white"><strong>LABORATORIOS</strong></font></center>
                        <div class="box-tools pull-right">
                			<a href="/laboratorio/create"><button  class="btn btn-success btn-sm"> Nuevo</button></a>
              			</div>
                	</div>
	            	<div class="box-body">
	            	 	<div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
							@include('empresas.laboratorio.search')
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
					
					<th>LABORATORIO</th>
					<th>OPCIONES</th>
				</thead>
				@foreach ($laboratorios as $laboratorio)
				<tr>
					<td>{{$laboratorio->lab_cod}}</td>
					<td>{{$laboratorio->lab_nom}}</td>
					<td>
						
						<a href="{{URL::action('LaboratorioController@edit',$laboratorio->lab_id)}}"><button class="btn btn-info">Editar</button></a>
                        <a href="" data-target="#modal-delete-{{$laboratorio->lab_id}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a>

					</td>
				</tr>
				@include('empresas.laboratorio.modal')
				@endforeach
			</table>
		</div>
		{{$laboratorios->render()}}
	</div>
</div>
</div>
</section>
@endsection
