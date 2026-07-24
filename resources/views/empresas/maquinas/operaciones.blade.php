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
          			<div class="box-header" style="background-color:blue;">
          				<font color="white"><center><strong>CONTROL DE TRABAJOS</strong></center></font>
          			</div>
	            	<div class="box-body">
	            		@include('empresas.procesos.buscarcomprobantes')
	            	</div>
	            </div>
	        </div>
	</div> 
             
    	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body table-responsive" >
							<table id=""  class="table table-bordered table-hover table-responsive">
							<thead>
								<tr>
									
									<th>Fec. Emision</th>
									<th>Tipo</th>
									<th>Serie</th>
									<th>N°</th>
									<th style="width:210px;">Cliente</th>
								
									<th>INICIAR</th>
									<th>FECHA INICIO</th>
									<th>FECHA FIN</th>
									<th>PROCESOS</th>
									<th>DETALLE</th>
								
								
								</tr>
							</thead>
							
							<tbody>
								@foreach($comprobantes as $comp)
								<tr>
									
								 	<td>{{Carbon::parse($comp->ccafem)->format('d-m-Y')}}</td>
									<td>{{$comp->des_doc}}</td>
									<td>{{$comp->serdoc}}</td>
									<td>{{$comp->numdoc}}</td>
									<td style="width:210px;">{{$comp->ccanom}}</td>
								
									<td>
									@if($comp->est_ope=='1' || $comp->est_ope=='2')
										<button disabled="disabled" class="btn btn-success btn-sm">INICIAR</button>
									@else
										<a href="/iniciarprocesos/{{$comp->IdCpe_cabecera}}"><button class="btn btn-success btn-sm">INICIAR</button></a>
									@endif
									</td>
									<td>{{$comp->fec_ini_proc}}</td>
									<td>{{$comp->fec_fin_proc}}</td>
									
									<td>
									@if($comp->est_ope=='1' || $comp->est_ope=='2' )
										<a href="/mostrarprocesos/{{$comp->IdCpe_cabecera}}"><button class="btn btn-primary btn-sm">PROCESOS</button></a>
									@else
										<button class="btn btn-primary btn-sm" disabled="disabled">PROCESOS</button>
									@endif
									</td>
									<td><a href="/detalleorden/{{$comp->IdCpe_cabecera}}"><button class="btn btn-success btn-sm">DETALLE</button></a></td>

										
									

										
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