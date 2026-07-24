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
        	<div class="col-xs-12">
          		<div class="box">
          			<div class="box-header" style="background-color:blue;">
          				<font color="white"><strong><center>REGISTRO DE ORDENES DE SERVICIOS</center></strong></font>
          			</div>
	            	<div class="box-body">
	            		@include('empresas.ordenesservicios.buscarordenesservicios')
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
									<th>Fec. O.S</th>
									<th>Fec. Vencimiento</th>
								
									<th>Serie Servicio</th>
									<th>N° Orden Servicio</th>
								
									<th>RUC PROVEEDOR</th>
									<th style="width:210px;">Nombre o Razón Social</th>
									<th>Moneda</th>
									<th>Total</th>
									<th>Estado</th>
									<th >OPCIONES</th>
									
								</tr>
							</thead>
							<tbody>
								@foreach($compras as $comp)
								<tr>
								 	<td>{{$comp->com_fec}}</td>
								 	<td>{{$comp->com_fec_ven}}</td>
								 	
								 	<td>{{$comp->com_doc_ser}}</td>
									<td>{{$comp->com_doc_num}}</td>
								
									<td>{{$comp->prov_ruc}}</td>
									<td>{{$comp->prov_raz}}</td>
									<td>{{$comp->monnom}}</td>
									<td>{{number_format($comp->total_com,'2','.',',')}}</td>
									<td>{{$comp->est_compra}}</td>
									<td>
										<!--<a href="/editarcompra/{{$comp->com_cab_id}}"><button class="btn btn-info">Editar</button></a>-->
										<a href="/detalleordenservicio/{{$comp->com_cab_id}}"><button class="btn btn-success">Detalle</button></a>
										<!--<a href="{{URL::action('ComprasController@edit',$comp->com_cab_id)}}"><button class="btn btn-info">Editar</button></a>-->
				                         <a href="" data-target="#modal-delete-{{$comp->com_cab_id}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a><br>
				                       

									</td>
								
								</tr>
								@include('empresas.ordenesservicios.modal')
								@endforeach
							</tbody>
						</table><br>
					</div>	
				</div>	
			</div>
		</div>
	</section>

@endsection