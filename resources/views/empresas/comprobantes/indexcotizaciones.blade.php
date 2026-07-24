@extends('layouts.empresas')
@section('contenido')
<script>

	var href = $('#btnPrint').attr('href');
	
	$("#btnPrint").printPage({
		
		 
		  url: href,
		  attr: "href",
		  messageBox:false,
		  
	})

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
	            window.location.href = "/indexcotizacion";
	    	
	    	}


	    	if(respuesta.mensaje =='cpe'){

	            window.location.href = "/SisFact";
	    	
	    	}


	    
	      });
	}

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
          				<font color="white"><center><strong>REGISTRO DE COTIZACIONES</strong></center></font>
          			</div>
	            	<div class="box-body">
	            		@include('empresas.comprobantes.buscarcomprobantes')
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
									<th>Fec. Emision</th>
									<th>Tipo</th>
									<th>Serie</th>
									<th>N°</th>
									<th>RUC / DNI / Otros</th>
									<th style="width:210px;">Nombre o Razón Social</th>
									<th>Moneda</th>
									<th>Total</th>
									<th>A4</th>
									<th>BAJAS</th>
									<th>ESTADO</th>
									<th>OPCIONES</th>
									
									
								
								</tr>
							</thead>
							
							<tbody>
								@foreach($comprobantes as $comp)
								<tr>
								 	<td>{{Carbon::parse($comp->ccafem)->format('d-m-Y')}}</td>
									<td>{{$comp->tdodes}}</td>
									<td>{{$comp->serdoc}}</td>
									<td>{{$comp->numdoc}}</td>
									<td title='{{$comp->tdides}}'>{{$comp->ccandi}}</td>
									<td style="width:210px;">{{$comp->ccanom}}</td>
									<td>{{$comp->monnom}}</td>
									<td align="right">{{number_format($comp->ccaitv,'2','.',',')}}</td>
									
								
									<td><a href="/descargar/{{$comp->IdCpe_cabecera}}/pdf"><center><i class="fa fa-file-excel-o fa-lg"></i></center></a></td>
									
									

									@if($comp->ccabaj=="")
									    <td><center>---</center></td>
									@else
									 	<td><a href="/consultarticketbaja/{{$comp->IdCpe_cabecera}}">{{$comp->ccabaj}}</a></td>
									@endif
									

										
										<td>
											@if($comp->estado =='PENDIENTE')
												<button class="btn btn-sm btn-warning btn-block">{{$comp->estado}}</button>
											@elseif($comp->estado =='ACEPTADO')
												<button class="btn btn-sm btn-success  btn-block">{{$comp->estado}}</button>
											@elseif($comp->estado =='ANULADO')
												<button class="btn btn-sm btn-danger  btn-block">{{$comp->estado}}</button>
												
											@endif
											
										</td>
									<td> 

										@if($comp->estado =='ANULADO')

											<img src="/icon/error.png" title="ANULADO" height="20px" width="20px">
											<img src="/icon/editar.png" title="EDITAR" height="30px" width="30px">
											<img src="/icon/cobrar.png" title="COBRAR" height="30px" width="30px">
										@else
									
										 @if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin') )
											 <a href="" data-target="#modal-baja-{{$comp->IdCpe_cabecera}}" data-toggle="modal"><img src="/icon/error.png" title="ANULADO" height="20px" width="20px"></a>
										@endif
										

											<a href="/editarventa/{{$comp->IdCpe_cabecera}}"><img src="/icon/editar.png" title="EDITAR" height="30px" width="30px"></a>


											<a href="/cobrarcotizacion/{{$comp->IdCpe_cabecera}}"><img src="/icon/cobrar.png" title="COBRAR" height="30px" width="30px"></a>
										
										@endif
									
									</td>


									
										

										
								</tr>
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