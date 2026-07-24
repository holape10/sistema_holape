@extends('layouts.empresas')
@section('contenido')
<script>

	var href = $('#btnPrint').attr('href');
	
	$("#btnPrint").printPage({
		
		 
		  url: href,
		  attr: "href",
		  messageBox:false,
		  
	})
</script>

	<section class="content">
	<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
	    @if(session()->has('info'))
	    	<div class="alert alert-danger">
	    	  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			  <strong>Alerta!</strong> {{ session('info') }}
			</div>
	    @endif


	    @if(session()->has('success'))
	    	<div class="alert alert-success">
	    	  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			  <strong>Información!</strong> {{ session('success') }}
			</div>
	    @endif
	</div>
</div>

	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
	            	 	
	            	 <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
				<h4><i class='glyphicon glyphicon-search'></i> STOCK DE PRODUCTOS</h4>
				@include('empresas.productos.buscarstock')
			</div>
				
	            		 <!-- <div class="btn-group" >
	            		  	{!! Form::open(['route'=>'Reportes.ExportarStock'])!!}
            				<button type="submit" class=" btn btn-success btn-sm">EXPORTAR</button>
            				{{Form::close()}}
      
        					</div>-->

	            	</div>
	            </div>
	        </div>
	</div>            
    	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
						<table id=""  class="table table-bordered table-hover">
							<thead>
								<tr>
									<th>CODIGO PRODUCTO</th>
									<th>MARCA</th>
									<th>DESCRIPCIÓN</th>
									<th>MODELO</th>
									<th>UNIDAD</th>
									<th>INGRESOS</th>
									<th>SALIDAS</th>
									<th>STOCK</th>
									<!--<th>OPCIONES</th>-->
								</tr>
							</thead>
							<tbody>
								@foreach($stock as $st)
								<tr>
								 	<td>{{$st->procod}}</td>
								 	<td>{{$st->marca}}</td>
								 	<td>{{$st->pronom}}</td>
								 	<td>{{$st->modelo}}</td>
								 	<td>{{$st->umecod}}</td>
								 	<td>@if($st->Ingresos == '')
								 			0
								 		@else
								 			{{$st->Ingresos}}
								 		@endif
								 	</td>
									<td>@if($st->Egresos == '')
								 			0
								 		@else
								 			{{$st->Egresos}}
								 		@endif
								 	</td>
									<td>{{$st->Ingresos-$st->Egresos}}</td>
									<!--<td>
										<a href=""><button class="btn btn-success">Detalle</button></a>
										<a href=""><button class="btn btn-info">Editar</button></a>
				                         <a href="" data-target="" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a><br>
									</td>-->
								</tr>
								@endforeach
							</tbody>
						</table><br>
					</div>	
					{{$stock->render()}}	
				</div>	
			</div>
		</div>
	</section>

@endsection