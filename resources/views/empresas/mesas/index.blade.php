@extends('layouts.empresas')
@section('contenido')


<section class="content">

	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
          			<div class="box-header" style="background-color:blue;">
          				<font color="white"><center><strong>CONSULTAR MESA</strong></center></font>
          				<div class="box-tools pull-right">
                			<a href="/mesa/create"><button  class="btn btn-success btn-sm"> Nuevo</button></a>
              			</div>
          			</div>
	            	<div class="box-body">
	            	 <div class="col-lg-12 col-md-12 col-sm-8 col-xs-8">
		
					@include('empresas.mesas.search')
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
					<th>NOMBRE MESA</th>
					<th>MOZO</th>
					<th>ZONA</th>
					<th>OPCIONES</th>
				</thead>
				@foreach ($mesas as $mes)
				<tr>
					<!--<td>{{$mes->mes_id}}</td>-->
					<td>{{$mes->mes_nom}}</td>
					<td>{{$mes->name}} {{$mes->apeusu}}</td>
					<td>{{$mes->pis_nom}}</td>
					<td>
						<a href="{{URL::action('MesasController@edit',$mes->mes_id)}}"><button class="btn btn-info">Editar</button></a>
                        <a href="" data-target="#modal-delete-{{$mes->mes_id}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a>

					</td>
				</tr>
				@include('empresas.mesas.modal')
				@endforeach
			</table>
		</div>
		{{$mesas->render()}}
	</div>
</div>
</div>
</section>
@endsection
