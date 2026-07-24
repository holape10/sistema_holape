@extends('layouts.empresas')
@section('contenido')

<script>

 $(document).ready(function()
 {

 	$("#exportarclientes").click(function(e){
     
       window.location.href = "/exportarclientes";
   
    });

});

</script>
	<section class="content">

<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
          			<div class="box-header" style="background-color:blue;">
          				<font color="white"><center><strong>REGISTRO DE CLIENTES</strong></center></font>
          				<div class="box-tools pull-right">
                			<a href="clientes/create"><button  class="btn btn-success btn-sm"> Nuevo</button></a>
              			</div>
          			</div>
	            	<div class="box-body">
	            	 <div class="col-lg-12 col-md-12 col-sm-8 col-xs-8">
	
					@include('empresas.clientes.search')
			</div>
				     	</div>
	            </div>
	        </div>
	</div>

<div class="row">
    <div class="col-xs-12">
    	<div class="box">
    	
	       	<div class="box-body">
			<table id="table" class="table table-striped table-bordered table-condensed table-hover">
				<thead>
					<th>RUC</th>
					<th>Razón Social</th>
					<th>Dirección</th>

					<th>Vendedor</th>
					<th>Estado</th>
					<th>Opciones</th>
				</thead>
				@foreach ($clientes as $cli)
				<tr>
					<td>{{$cli->clinum}}</td>
					<td>{{$cli->clinom}}</td>
					<td>{{$cli->name}} {{$cli->apeusu}}</td>
					<td>{{$cli->cliest}}</td>
					<td>
						<a href="{{URL::action('ClientesController@edit',$cli->clicod)}}"><button class="btn btn-info">Editar</button></a>
                         <a href="" data-target="#modal-delete-{{$cli->clicod}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a><br>

					</td>
				</tr>
				@include('empresas.clientes.modal')
				@endforeach
			</table>
		</div>	
		
	</div>	
</div>
</div>
</section>
@endsection