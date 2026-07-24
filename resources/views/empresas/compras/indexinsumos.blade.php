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
	            		@include('empresas.compras.buscarcomprasinsumos')
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
									<th>Fec. Compra</th>
									<th>Fec. Vencimiento</th>
									<th>Documento</th>
									<th>Serie</th>
									<th>N°</th>
									<th>RUC PROVEEDOR</th>
									<th style="width:210px;">Nombre o Razón Social</th>
									<th>Moneda</th>
									<th>Total</th>
									<th>Estado</th>
									<th>OPCIONES</th>
								</tr>
							</thead>
							<tbody>
								@foreach($compras as $comp)
								<tr>
								 	<td>{{$comp->com_fec}}</td>
								 	<td>{{$comp->com_fec_ven}}</td>
								 	<td>{{$comp->tdodes}}</td>
								 	<td>{{$comp->com_doc_ser}}</td>
									<td>{{$comp->com_doc_num}}</td>
									<td>{{$comp->prov_ruc}}</td>
									<td>{{$comp->prov_raz}}</td>
									<td>{{$comp->monnom}}</td>
									<td>{{number_format($comp->total_com,'2','.',',')}}</td>
									<td>{{$comp->est_compra}}</td>
									<td>
										<a href="/detallecompras/{{$comp->com_cab_id}}/2"><button class="btn btn-success">Detalle</button></a>
										<!--<a href="{{URL::action('ComprasController@edit',$comp->com_cab_id)}}"><button class="btn btn-info">Editar</button></a>-->
				                         <a href="" data-target="#modal-delete-{{$comp->com_cab_id}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a><br>

									</td>
								</tr>
								@include('empresas.compras.modal')
								@endforeach
							</tbody>
						</table><br>
					</div>	
				</div>	
			</div>
		</div>
	</section>

@endsection