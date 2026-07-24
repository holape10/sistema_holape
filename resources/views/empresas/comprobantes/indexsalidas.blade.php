@extends('layouts.empresas')
@section('contenido')
<script>

	$(document).ready(function()
 	{
		$(".formbaja").keypress(function(e) {
    		if (e.which == 13) {
      			return false;
    		}
   		})
	});

	
	function enviarbaja(id){


		var comp = $("#comprobante"+id).val();
		var motivo = $("#motivo"+id).val();

	   

	      $(".imgloadanular").show();
	      $(".botonesanular").hide();
	      $.ajax({
	        type: "GET",
	        dataType: 'json',
	        url: '/registraranulacion/'+comp+'/'+motivo,
	      }).done(function(respuesta){


	      	if(respuesta.mensaje =='orden'){
	            window.location.href = "/ordenes";
	    	}

	    	if(respuesta.mensaje =='cotizacion'){
	            window.location.href = "/ordenes";
	    	
	    	}


	    	if(respuesta.mensaje =='cpe'){

	            window.location.href = "/SisFact";
	    	
	    	}


	    	if(respuesta.mensaje =='salidas'){

	            window.location.href = "/salidas";
	    	
	    	}


	    
	      });
	}




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
			  <strong>InformaciÃ³n!</strong> {{ session('success') }}
			</div>
	    @endif
	</div>
</div>

	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
          			<div class="box-header" style="background-color:blue;">
          				<font color="white"><center><strong>SALIDAS DE PRODUCTOS</strong></center></font>
          			</div>
	            	<div class="box-body">
	            		@include('empresas.comprobantes.buscarsalidas')
	            	</div>
	            </div>
	        </div>
	</div> 
             
    	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
	            		@if($tipo=='1')
							<table id="dtHorizontalExample"  class="table table-bordered table-hover">
							<thead style="background:blue;color:white;">
								<tr>
									<th><center>Fec. Registro</center></th>
									<th><center>Documento</center></th>
									<th><center>Serie</center></th>
									<th><center>N°</center></th>
									<th><center>Area</center></th>
									<th><center>Colaborador</center></th>
									<th><center>Estado</center></th>
									<th><center>Opciones</center></th>
								
									
									
								
								</tr>
							</thead>
							
							<tbody>
								@foreach($comprobantes as $comp)
								<tr>
								 	<td>{{Carbon::parse($comp->ccafem)->format('d-m-Y')}}</td>
								 	<td>{{$comp->tdodes}}</td>
								 	<td>{{$comp->serdoc}}</td>
									<td>{{$comp->numdoc}}</td>
								 	<td>{{$comp->are_emp_des}}</td>
								 	<td>{{$comp->name}} {{$comp->apeusu}}</td>
								 	<td>@if(is_null($comp->ccabaj))
											<button type="button" class="btn btn-success btn-sm btn-block">Registrado</button>
										@else
											<button type="button" class="btn btn-danger btn-sm btn-block">Anulado</button>
										@endif</td>
								 	<td style="text-align:center;">	
										<a href="/detallesalidas/{{$comp->IdCpe_cabecera}}"><img src="/icon/detalles.png" title="DETALLE" height="30px" width="30px"></a>
										
										@if(is_null($comp->ccabaj))
										<a href="/editarsalidasproductos/{{$comp->IdCpe_cabecera}}"><img src="/icon/editar.png" title="EDITAR" height="30px" width="30px"></a>
										@else
										<img src="/icon/editar.png" title="EDITAR" height="30px" width="30px">
										@endif


									
				                         @if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin') ) 
				                         @if(is_null($comp->ccabaj))
										<a href="" data-target="#modal-anular-{{$comp->IdCpe_cabecera}}" data-toggle="modal"><img src="/icon/error.png" title="Eliminar" height="30px" width="30px"></a><br>
										@else
										<img src="/icon/error.png" title="Eliminar" height="30px" width="30px"><br>
										@endif
				                         
				                     	@endif
				                 	</td>
								</tr>
									@include('empresas.comprobantes.modal_anular_salida')
								@endforeach
									
							</tbody>
						</table><br>
						@elseif($tipo=='2')

								<table id="dtHorizontalExample"  class="table table-bordered table-hover">
							<thead>
								<tr>
								
									<th><center>Area</center></th>
									<th><center>Producto</center></th>
									<th><center>Cantidad</center></th>
								
									
									
								
								</tr>
							</thead>
							
							<tbody>
								@foreach($comprobantes as $comp)
								<tr>
								 
								 	<td>{{$comp->are_emp_des}}</td>
									<td>{{$comp->cdedes}}</td>
									<td style="text-align:right;">{{number_format($comp->cantidad,2,'.','')}}</td>
									
								</tr>
								
								@endforeach
								<tr>
									<td colspan="2" style="text-align:right;"><strong>TOTAL PRODUCTOS</strong></td>
									<td style="text-align:right;"><strong>{{number_format($cantidad,2,'.','')}}</strong></td>
								</tr>
							</tbody>
						</table><br>

						@endif
					</div>	
					
				</div>	
			</div>
		</div>
	</section>

@endsection