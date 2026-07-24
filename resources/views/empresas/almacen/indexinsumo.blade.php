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
						<table id=""  class="table table-bordered table-hover">
							<thead>
								<tr>
									<th>Fecha Mov.</th>
									<th>Comprobante</th>				
									<th>Cod. Producto</th>
									<th>Tipo Mov.</th>
									<th>Motivo</th>
									<th>Producto</th>
								
									
									<th>Cantidad</th>
									<th>Medida Cant.</th>
									<th>U. ME</th>
									<th>Total Medida Cant.</th>
									<th>Stock Insumo Medida Cant.</th>
									<th>Observacion</th>
									<th>P.U Compra</th>
									<th>Total</th>
									<th>OPCIONES</th>
								</tr>
							</thead>
							<tbody>
								@foreach($movimientos as $mov)
								<tr>
								 	<td>{{$mov->mov_fec}}</td>
								 	<td>{{$mov->comprobante}}</td>
									<td>{{$mov->procod}}</td>
								       
									@if($mov->mov_tip=='I')
										<td>Ingreso</td>
									@elseif($mov->mov_tip=='E')
										<td>Egreso</td>
									@elseif($mov->mov_tip=='A')
										<td>Anulado</td>
									@elseif($mov->mov_tip=='EI')
										<td>Stock Inicial</td>
									@endif
								 	<td>{{$mov->mov_mot}}</td>
								 	<td>{{$mov->pronom}}</td>
								   
									<td>{{$mov->cantidad}}</td>
									<td>{{$mov->medida}}</td>
									 <td>{{$mov->umenom}}</td>
									<td>{{$mov->totalmedida}}</td>
									<td>{{$mov->totalmedidastock}}</td>
									<td>{{$mov->observacion}}</td>
									<td>{{$mov->preciounitario}}</td>
									<td>{{$mov->totalfactura}}</td>
									<td>
										@if($mov->mov_tip=='A' || $mov->mov_tip=='EI' || $mov->mov_mot=='Venta')
										 <a href=""><button disabled="disabled" class="btn btn-danger">Eliminar</button></a>
										 @else
										 	 <a href="" data-target="#modal-delete-{{$mov->mov_id_insumo}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a>
										 @endif

									</td>
								</tr>
								@include('empresas.almacen.modalinsumo')
								@endforeach
							</tbody>
						</table><br>
					</div>
					{{$movimientos->render()}}	
				</div>	
			</div>
		</div>
	</section>

@endsection