@extends('layouts.empresas')
@section('contenido')


	<section class="content">
	

		<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
          			<div class="box-header with-border" style="background-color:blue;">
                        <center><font color="white"><strong>REGISTRO DE TECNICOS</strong></font></center>
                        <div class="box-tools pull-right">
                			<a href="/tecnicos/create"><button  class="btn btn-success btn-sm"> Nuevo</button></a>
              			</div>
                	</div>
	            	<div class="box-body">
	            	 	<div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
							@include('empresas.tecnicos.search')
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
					<th>N° Doc.</th>
					<th>T&eacute;cnito</th>
					<th>Direcci&oacute;n</th>
					<th>Tel&eacute;fono</th>
					
					<th>Opciones</th>
				</thead>
				@foreach ($tecnicos as $tec)
				<tr>
					<td>{{$tec->tecnum}}</td>
					<td>{{$tec->tecnom}}</td>
					<td>{{$tec->tecdir}}</td>
					<td>{{$tec->tectel}}</td>
				
					<td>
						<a href="{{URL::action('TecnicosController@edit',$tec->tec_id)}}"><button class="btn btn-info">Editar</button></a>
                         <a href="" data-target="#modal-delete-{{$tec->tec_id}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a><br>

					</td>
				</tr>
				@include('empresas.tecnicos.modal')
				@endforeach
			</table>
		</div>	
		{{$tecnicos->render()}}
	</div>	
</div>
</div>
</section>
@endsection