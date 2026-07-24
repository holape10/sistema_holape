@extends('layouts.empresas')
@section('contenido')


<section class="content">
	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
	            	 <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
				<h4><i class='glyphicon glyphicon-search'></i> CONSULTAR CUENTA BANCARIA <a href="cuentasbancarias/create"><button class="btn btn-success"> Nuevo</button></a></h4>
				@include('empresas.cuentasbancarias.search')
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
					
					<th>BANCO</th>
					<th>TIPO CUENTA</th>
					<th>MONEDA</th>
					<th>NRO CUENTA</th>
					<th>OPCIONES</th>
				</thead>
				@foreach ($cuentasbancarias as $cuentabancaria)
				<tr>
				
					<td>{{$cuentabancaria->ban_nom}}</td>
					<td>{{$cuentabancaria->tip_cuen_nom}}</td>
					<td>{{$cuentabancaria->monnom}}</td>
					<td>{{$cuentabancaria->cuen_ban_num}}</td>
					<td>
						<a href="{{URL::action('CuentasBancariasController@edit',$cuentabancaria->cuen_ban_id)}}"><button class="btn btn-info">Editar</button></a>
                        <a href="" data-target="#modal-delete-{{$cuentabancaria->cuen_ban_id}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a>

					</td>
				</tr>
				@include('empresas.cuentasbancarias.modal')
				@endforeach
			</table>
		</div>
		{{$cuentasbancarias->render()}}
	</div>
</div>
</div>
</section>
@endsection
