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
          			<div class="box-header" style="background-color:blue;">
          				<font color="white"><strong><center>REGISTRO DE ORDEN DE COMPRA</center></strong></font>
          			</div>
	            	<div class="box-body">
	            		@include('empresas.ordenescompra.buscarcomprasproductos')
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
									<th>PDF</th>
									<th>RUC PROVEEDOR</th>
									<th style="width:210px;">Nombre o Razón Social</th>
									<th>Moneda</th>
									<th>Total</th>
									<th>Estado</th>
									<th >OPCIONES</th>
									<th>GENERAR COMPRA</th>
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
									<td><a href="/descargarorden/{{$comp->IdEmpresa}}-{{$comp->tdocod}}-{{$comp->com_doc_ser}}-{{$comp->com_doc_num}}"><center><i class="fa fa-file-excel-o fa-lg"></i></center></a></td>
									<td>{{$comp->prov_ruc}}</td>
									<td>{{$comp->prov_raz}}</td>
									<td>{{$comp->monnom}}</td>
									<td>{{number_format($comp->total_com,'2','.',',')}}</td>
									<td>@if($comp->est_compra=='Registrado')<buton class="btn btn-success btn-block btn-sm">{{$comp->est_compra}}</buton> @else <buton class="btn btn-danger btn-block btn-sm">{{$comp->est_compra}}</buton> @endif</td>
									<td>
										<a href="/detalleorden/{{$comp->com_cab_id}}/1"><img src="/icon/detalles.png" title="Detalle" height="30px" width="30px"></a>
										<a href="/editarorden/{{$comp->com_cab_id}}"><img src="/icon/editar.png" title="EDITAR" height="30px" width="30px"></a>
										
										<!--<a href="{{URL::action('ComprasController@edit',$comp->com_cab_id)}}"><button class="btn btn-info">Editar</button></a>-->
				                         <a href="" data-target="#modal-delete-{{$comp->com_cab_id}}" data-toggle="modal"><img src="/icon/error.png" title="ELIMINAR" height="30px" width="30px"><br>
				                       


									</td>
									<td>
										  <a href="/generarcompra/{{$comp->com_cab_id}}"><button class="btn btn-info">Generar Compra</button></a>
									</td>
								</tr>
								@include('empresas.ordenescompra.modal')
								@endforeach
							</tbody>
						</table><br>
					</div>	
				</div>	
			</div>
		</div>
	</section>

@endsection