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
          				<font color="white"><center><strong>PROCESOS <BR> CLIENTE: {{$cabecera->ccanom}}<BR> COMPROBANTE: {{$cabecera->serdoc}}-{{$cabecera->numdoc}}</strong></center></font>
          			</div>
	            	<div class="box-body table-responsive" >
							<table id="" style="font-size:9pt;"  class="table table-bordered table-hover table-responsive table-striped">
								<thead>
									<tr>
									<td style="font-weight:bold;">
										N°
									</td>
									<td style="width:200px;text-align:center;font-weight:bold;">PROCESO</td>
									<td style="width:150px;text-align:center;font-weight:bold;">INICIO</td>
									<td style="width:150px;text-align:center;font-weight:bold;">FINALIZACIÓN</td>
									<td style="width:100px;text-align:center;font-weight:bold;">OPERADOR</td>
									<td style="width:100px;text-align:center;font-weight:bold;">MAQUINAS</td>
									<td style="width:450px;text-align:center;font-weight:bold;">OBSERVACION</td>
									<td colspan="3" style="width:100px;text-align:center;font-weight:bold;">OPCIONES</td>
								</tr>
								</thead>
							<tbody>

								@php
									$i=0;
								@endphp
								@foreach($procesos as $pro)
								@php
								$i=$i+1;
								$listar_maquinas = DB::TABLE('procesos_maquinas')->join('maquinas','maquinas.maq_id','procesos_maquinas.maq_id')->where('proc_comp_id',$pro->proc_comp_id)->get();
								@endphp


								<tr>
									
									<td style="font-weight:bold;text-align:center;">{{$i}}</td>
									<td style="font-weight:bold;">{{$pro->proc_nom}}</td>
									<td style="">{{$pro->proc_fec_ini}}</td>
									<td style="">{{$pro->proc_fec_fin}}</td>
									<td>{{$pro->name}} {{$pro->apeusu}}</td>
									<td><!--<a  data-target="#modal-maquinas-{{$pro->proc_comp_id}}" data-toggle="modal"><button  class="btn btn-warning btn-sm"><strong>MAQUINAS</strong></button></a>-->
										@foreach($listar_maquinas as $lis_maq)
											<li>{{$lis_maq->maq_nom}}</li>
										@endforeach
									</td>
									<td>{{$pro->proc_obs}}</td>
									<td  style="text-align:right;">
										@if($pro->proc_comp_est=='0')
											<a href="" data-target="#modal-iniciar-{{$pro->proc_comp_id}}" data-toggle="modal"><button class="btn btn-success btn-sm">INICIAR</button> </a>
										@else
											<button class="btn btn-success btn-sm" disabled="disabled">INICIAR</button>
										@endif
									
										@if($pro->proc_comp_est=='1')
										<a href=""  data-target="#modal-finalizar-{{$pro->proc_comp_id}}" data-toggle="modal"><button class="btn btn-danger btn-sm">FINALIZAR</button></a>
										
										@else
										<button class="btn btn-danger btn-sm" disabled="disabled">FINALIZAR</button>
										@endif

										<a  data-target="#modal-observacion-{{$pro->proc_comp_id}}" data-toggle="modal"><button  class="btn btn-primary btn-sm">OBSERVA.</button></a>
									</td>
									
								
								
								</tr>
								@include('empresas.procesos.modaliniciar')
									@include('empresas.procesos.modalfinalizar')
										@include('empresas.procesos.modalobservacion')
									@include('empresas.procesos.modalmaquinas')
								@endforeach
							</tbody>
						
						</table><br>
					</div>	
					
				</div>	
			</div>
		</div>
	</section>

@endsection