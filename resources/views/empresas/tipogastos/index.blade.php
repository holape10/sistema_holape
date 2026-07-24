@extends('layouts.empresas')
@section('contenido')


<section class="content">
	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
          			<div class="box-header with-border" style="background-color:blue;">
                        <center><font color="white"><strong>TIPO DE GASTO</strong></font></center>
                        <div class="box-tools pull-right">
                			<a href="/tipogastos/create"><button  class="btn btn-success btn-sm"> Nuevo</button></a>
              			</div>
                	</div>
	            	<div class="box-body">
	            	 	<div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
							@include('empresas.tipogastos.search')
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
					
					<th>GASTO</th>
					<th>OPCIONES</th>
				</thead>
				@foreach ($tipogastos as $area)
				<tr>
			
					<td>{{$area->tip_gas_nom}}</td>
					<td>
						
						<a href="{{URL::action('TipoGastosController@edit',$area->tip_gas_id)}}"><button class="btn btn-info">Editar</button></a>
                        <a href="" data-target="#modal-delete-{{$area->tip_gas_id}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a>

					</td>
				</tr>
				@include('empresas.tipogastos.modal')
				@endforeach
			</table>
		</div>
		{{$tipogastos->render()}}
	</div>
</div>
</div>
</section>
@endsection
