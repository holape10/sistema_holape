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
          			<div class="box-header" style="background-color:#3c8dbc;">
          				<font color="white"><center><strong>REGISTRO DE PEDIDOS</strong></center></font>
          			</div>
	            	<div class="box-body">
	            		@include('empresas.comprobantes.buscarpedidos')
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
									<th style="text-align:center;vertical-align:middle;color: white;background-color:#3c8dbc;">Estado</th>
									<th style="text-align:center;vertical-align:middle;color: white;background-color:#3c8dbc;">Fecha</th>
									<th style="text-align:center;vertical-align:middle;color: white;background-color:#3c8dbc;">Documento</th>
									<th style="text-align:center;vertical-align:middle;color: white;background-color:#3c8dbc;">RUC / DNI / Otros</th>
									<th style="text-align:center;vertical-align:middle;color: white;background-color:#3c8dbc;" style="width:210px;">Cliente</th>
									<th style="text-align:center;vertical-align:middle;color: white;background-color:#3c8dbc;">Moneda</th>
									<th style="text-align:center;vertical-align:middle;color: white;background-color:#3c8dbc;">Total</th>
									<th style="text-align:center;vertical-align:middle;color: white;background-color:#3c8dbc;">TICKET</th>
									<th style="text-align:center;vertical-align:middle;color: white;background-color:#3c8dbc;">ANULAR</th>
									<th style="text-align:center;vertical-align:middle;color: white;background-color:#3c8dbc;">DETALLE</th>
								
									
								
								</tr>
							</thead>
							
							<tbody>
								@foreach($comprobantes as $comp)
								<tr>
									<td>@if($comp->facturado=='0') <button class="btn btn-sm btn-block btn-danger">PENDIENTE</button> @else <button class="btn btn-sm btn-block btn-success">COBRADO</button>  @endif</td>
								 	<td>{{Carbon::parse($comp->ccafem)->format('d-m-Y')}}</td>
									<td>{{$comp->tdodes}} | {{$comp->serdoc}}-{{$comp->numdoc}}</td>
								
									<td title='{{$comp->tdides}}'>{{$comp->ccandi}}</td>
									<td style="width:210px;">{{$comp->ccanom}}</td>
									<td>{{$comp->monnom}}</td>
									<td align="right">{{number_format($comp->ccaitv,'2','.',',')}}</td>
									<td><a id="btnPrint" href="/imprimir/{{$comp->IdCpe_cabecera}}/{{$comp->tdocod}}" target="_blank"><center><i class="fa fa-file-pdf-o fa-lg"></i></center></a></td>
									<td>
									<a href="" data-target="#modal-baja-{{$comp->IdCpe_cabecera}}" data-toggle="modal"><img src="/icon/error.png" title="ANULADO" height="20px" width="20px"></a>
								</td>
								<td>
												<a href="/detalleventa/{{$comp->IdCpe_cabecera}}"><button class="btn btn-success btn-sm">Detalle</button></a>
										</td>

								
									

										
								</tr>
									@include('empresas.comprobantes.modal')
									@include('empresas.comprobantes.modalbaja')
								@endforeach
							</tbody>
						</table><br>
					</div>	
					{{$comprobantes->render()}}
				</div>	
			</div>
		</div>
	</section>

@endsection