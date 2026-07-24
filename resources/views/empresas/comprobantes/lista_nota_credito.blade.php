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

	function imprimir_ticket(id,tdocod){

		$.ajax({
			type: "GET",
			dataType: 'json',
			url: '/imprimir/'+id+'/'+tdocod,
		}).done(function(respuesta){

					
		});

	}

	function imprimir_voucher(id){


    	$("<iframe>")                             
        .hide()                              
        .attr("src", "/voucher/"+id) 
        .appendTo("body");                   
	

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
          				<font color="white"><center><strong>REGISTRO DE NOTAS DE CRÉDITOS</strong></center></font>
          			</div>
	            	<div class="box-body">
	            		@include('empresas.comprobantes.buscar_nota_credito')
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
								<tr style="font-size:10pt;font-weight:bold;">
									
									<th>Fec. Emision</th>
									<th>Tipo</th>
									<th>Serie</th>
									<th>N°</th>
									<th>RUC / DNI / Otros</th>
									<th style="width:210px;">Cliente</th>
									<th>Moneda</th>
									<th>Total</th>
									<th>TICKET </th>
									
									<th>WHASTAPP</th>
									<th>A4</th>
									<th>XML</th>
									<th>CDR</th>
									
									<th>NOTAS</th>
									<th>BAJAS</th>
									<th>OPCIONES</th>
									<th>ANULAR</th>
									<th>EDITAR</th>
									<th>DETALLE</th>
									<th>CORREOS</th>
									<th>ENVIAR CORREO</th>
									<th>Estado SUNAT</th>
									
								
								</tr>
							</thead>
							
							<tbody>
								@foreach($comprobantes as $comp)
								<tr>
									
								 	<td>{{Carbon::parse($comp->ccafem)->format('d-m-Y')}}</td>
									<td>{{$comp->des_doc}}</td>
									<td>{{$comp->serdoc}}</td>
									<td>{{$comp->numdoc}}</td>
									<td title='{{$comp->tdides}}'>{{$comp->ccandi}}</td>
									<td style="width:210px;">{{$comp->ccanom}}</td>
									<td>{{$comp->monnom}}</td>
									<td align="right">{{number_format($comp->ccaitv,'2','.',',')}}</td>
									<td><a   onclick="imprimir_voucher('{{$comp->IdCpe_cabecera}}');"><center><i class="fa fa-file-pdf-o fa-lg"></i></center></a></td>
									
									
									<td><a href="" data-target="#modal-whastapp-{{$comp->IdCpe_cabecera}}" data-toggle="modal" ><center><img src="/icon/WHATS.png" title="ANULADO" height="20px" width="20px"></center></a></td>

									<td><a href="/descargar/{{$comp->IdCpe_cabecera}}/pdf"><center><i class="fa fa-file-excel-o fa-lg"></i></center></a></td>
									
									 @if(empty($comp->ccaenlace))
										<td><a href="/descargar/{{$comp->IdCpe_cabecera}}/xml"><center><i class="fa fa-file-excel-o fa-lg"></i></center></a></td>
									@else
										<td><a href="{{$comp->ccaenlace}}.xml"><center><i class="fa fa-file-excel-o fa-lg"></i></center></a></td>
									@endif


									@if(empty($comp->ccaenlace))
										<td><a href="/descargar/{{$comp->IdCpe_cabecera}}/cdr"><center><i class="fa fa-file-pdf-o fa-lg"></i></center></a></td>
									@else
										<td><a href="{{$comp->ccaenlace}}.cdr"><center><i class="fa fa-file-pdf-o fa-lg"></i></center></a></td>
									@endif

									
										@if($comp->ccanot=="")
										   <td><center>---</center></td>
										@else
											<td><a href="/listarnotas/{{$comp->IdCpe_cabecera}}">{{$comp->ccanot}}</a></td>
										@endif

										@if($comp->ccabaj=="")
										    <td><center>---</center></td>
										@else
										 	<td><a href="/consultarticketbaja/{{$comp->IdCpe_cabecera}}">{{$comp->ccabaj}}</a></td>
										@endif
									

									
									<td> 
										
									<div class="dropdown">
									  <button class="btn btn-default btn-xs dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
									    OPCIONES
									    <span class="caret"></span>
									  </button>
									  <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
									   		    @if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin') )

									   		 @if($comp->tdocod=='03' ||  $comp->tdocod=='01')
									   		 	<li><a class="dropdown-item" href="/tiponota/{{$comp->tdocod}}/{{$comp->IdCpe_cabecera}}/{{'07'}}">Nota de crédito</a></li>
									   		 	<li><a class="dropdown-item" href="/tiponota/{{$comp->tdocod}}/{{$comp->IdCpe_cabecera}}/{{'08'}}">Nota de Débito</a></li>
									   		 @endif
									  		
					                     	@endif
											<!--<li><a class="dropdown-item" href="/tiponota/{{$comp->tdocod}}/{{$comp->IdCpe_cabecera}}/{{'4'}}">Nota de débito</a></li>-->
										
	
									  </ul>
									</div>
										</td>
										<td>
										 @if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin') )
											 <a href="" data-target="#modal-baja-{{$comp->IdCpe_cabecera}}" data-toggle="modal"><img src="/icon/error.png" title="ANULADO" height="20px" width="20px"></a>
										@endif
										</td>

										@if(is_null($comp->ccacodsun) || $comp->ccacodsun=='0306' || $comp->ccacodsun=='1061' | $comp->ccacodsun=='2017' && $comp->ccacodsun=='2104')
											<td>
												  @if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin')  )
												<a href="/editarventa/{{$comp->IdCpe_cabecera}}"><center><button class="btn btn-primary btn-sm">EDITAR</button></center></a>
												@endif
											</td>
										@else
										  
											<td>
												@if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin')  )
												<center><button class="btn btn-primary btn-sm" disabled="disabled">EDITAR</button></center>
												@endif
											</td>
											
										@endif
											<td>
												<a href="/detalleventa/{{$comp->IdCpe_cabecera}}"><button class="btn btn-success btn-sm">Detalle</button></a>
										</td>

											<td>
										@if(empty($comp->clicorcli))
											{{$comp->clicor}}<br>
										@else
											{{$comp->clicorcli}}<br>
										@endif
										
										@if(empty($comp->clicorcli2))
											{{$comp->clicor2}}<br>
										@else
											{{$comp->clicorcli2}}<br>
										@endif


										@if(empty($comp->clicorcli3))
											{{$comp->clicor3}}<br>
										@else
											{{$comp->clicorcli3}}<br>
										@endif

										@if(empty($comp->clicorcli4))
											{{$comp->clicor4}}<br>
										@else
											{{$comp->clicorcli4}}<br>
										@endif
									</td>

										<td>
											<a href="" data-target="#modal-correo-{{$comp->IdCpe_cabecera}}" data-toggle="modal"><center><img src="/img/mail.jpg"  height="40px" width="40px"></center></a>
										</td>

									
										
										@if($comp->ccacodsun=='0')
										<td>
											<a><center><img src="/icon/check.png" title="{{$comp->ccadessun}}" height="20px" width="20px"></center></a>
										</td>
										@elseif($comp->ccacodsun=='8')
										<td>
											<a><center><img src="/icon/error.png" title="{{$comp->ccadessun}}"  height="20px" width="20px"></center></a>
										</td>
										@elseif($comp->ccacodsun >'100' && $comp->ccacodsun <'1999')
										<td>
											<a href="/enviarcomprobante/{{$comp->IdCpe_cabecera}}"><center><img title="{{$comp->ccadessun}}" src="/icon/iconwarning.png" height="20px" width="20px"></center></a>
										</td>

										@elseif($comp->ccacodsun > '2000' && $comp->ccacodsun <'3999')
										<td>
											<a><center><img src="/icon/error.png" title="{{$comp->ccadessun}}"  height="20px" width="20px"></center></a>
										</td>
										@elseif($comp->ccacodsun > '4000')
										<td>
											<a><center><img src="/icon/checkobs.png" title="{{$comp->ccadessun}}" height="20px" width="20px"></center></a>
										</td>
										@endif
										

										
								</tr>
									@include('empresas.comprobantes.modal')
									@include('empresas.puntosventas.modal_whatsapp')
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