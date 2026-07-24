@extends('layouts.empresas')
@section('contenido')


<section class="content">
	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
          			<div class="box-header with-border" style="background-color:blue;">
                        <center><font color="white"><strong>programas</strong></font></center>
                        <div class="box-tools pull-right">
                			<a href="/programas/create"><button  class="btn btn-success btn-sm"> Nuevo</button></a>
              			</div>
                	</div>
	            	<div class="box-body">
	            	 	<div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
							@include('empresas.programas.search')
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
					<th>PROGRAMA</th>
					<th>OPCIONES</th>
				</thead>
				@foreach ($programas as $programa)
				<tr>
					<td>{{$programa->prog_cod}}</td>
					<td>{{$programa->prog_nom}}</td>
					<td>
						
						<a href="/asignarplatos/{{$programa->prog_id}}"><button class="btn btn-warning">Asignar Platos</button></a>
						<a href="{{URL::action('ProgramasController@edit',$programa->prog_id)}}"><button class="btn btn-info">Editar</button></a>
                        <a href="" data-target="#modal-delete-{{$programa->prog_id}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a>

					</td>
				</tr>
				@include('empresas.programas.modal')
				@endforeach
			</table>
		</div>
		{{$programas->render()}}
	</div>
</div>
</div>
</section>
@endsection
