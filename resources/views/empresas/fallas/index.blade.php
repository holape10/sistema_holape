@extends('layouts.empresas')
@section('contenido')


<section class="content">
	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
          			<div class="box-header with-border" style="background-color:blue;">
                        <center><font color="white"><strong>Fallas</strong></font></center>
                        <div class="box-tools pull-right">
                			<a href="/fallas/create"><button  class="btn btn-success btn-sm"> Nuevo</button></a>
              			</div>
                	</div>
	            	<div class="box-body">
	            	 	<div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
							@include('empresas.fallas.search')
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
					<th>C&Oacute;DIGO</th>
					<th>FALLAS</th>
					<th>OPCIONES</th>
				</thead>
				@foreach ($fallas as $falla)
				<tr>
					<td>{{$falla->fall_cod}}</td>
					<td>{{$falla->fall_nom}}</td>
					<td>
						
						<a href="{{URL::action('FallasController@edit',$falla->fall_id)}}"><button class="btn btn-info">Editar</button></a>
                        <a href="" data-target="#modal-delete-{{$falla->fall_id}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a>

					</td>
				</tr>
				@include('empresas.fallas.modal')
				@endforeach
			</table>
		</div>
		{{$fallas->render()}}
	</div>
</div>
</div>
</section>
@endsection
