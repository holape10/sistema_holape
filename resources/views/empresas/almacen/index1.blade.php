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
	            		@include('empresas.almacen.buscar')
	            	</div>
	            </div>
	        </div>
	</div>            
    	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
						<table id="tblCompra"  class="table table-bordered table-hover">
							<thead>
								<tr>
									<th>Fec. Movimiento</th>
									<th>Tipo Movimiento</th>
									<th>Documento</th>
									<th>Serie</th>
									<th>N°</th>
									<th>Código Producto</th>
									<th style="width:210px;">Descripción</th>
									<th>Unidad</th>
									<th>Cantidad</th>
									
								</tr>
							</thead>
							<tbody>

								@foreach($movimientos as $mov)
								<tr>
								 	<td>{{$mov->mov_fec}}</td>
								 	<td>{{$mov->mov_mot}}</td>
								 	<td>{{$mov->comprobante}}</td>
								 	<td>{{$mov->serie}}</td>
									<td>{{$mov->numero}}</td>
									<td>{{$mov->codpro}}</td>
									<td>{{$mov->descripcion}}</td>
									<td>{{$mov->unidad}}</td>
									<td>{{$mov->cantidad}}</td>
								
									
								</tr>
								@endforeach
							</tbody>
						</table><br>
					</div>	
				</div>	
			</div>
		</div>
	</section>

@endsection