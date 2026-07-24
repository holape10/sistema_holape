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
          			<div class="box-header" style="background-color:#337ab7;">
          				<font color="white"><center><strong>REGISTRO DE AUTOCONSUMOS</strong></center></font>
          			</div>
	            	<div class="box-body">
	            		@include('empresas.comprobantes.buscarcomprobantes', ['rutaBusqueda' => '/autoconsumos'])
	            	</div>
	            </div>
	        </div>
	</div> 
             
    	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body table-responsive" >
							<table id=""  class="table table-bordered table-hover table-responsive">
							<thead >
								<tr style="background:#337ab7;color:white;">
									<th colspan="28"><center><strong>REGISTRO DE AUTOCONSUMOS</strong></center></th>
								</tr>
								<tr style="font-size:10pt;font-weight:bold;color:white;background: #808080;">
									
									<th style="text-align:center;vertical-align:middle;">Fecha y Hora</th>
									<th style="text-align:center;vertical-align:middle;" hidden='hidden'>Tipo</th>
									<th style="text-align:center;vertical-align:middle;" hidden='hidden'>Serie</th>
									<th style="text-align:center;vertical-align:middle;" hidden='hidden'>N°</th>
									<th style="text-align:center;vertical-align:middle;">N° Mesa</th>
									<th style="text-align:center;vertical-align:middle;">Mozo</th>
									<th style="text-align:center;vertical-align:middle;" hidden='hidden'>Pedido</th>
									<th style="text-align:center;vertical-align:middle;">DNI/RUC</th>
									<th style="text-align:center;vertical-align:middle;" style="width:210px;">Cliente</th>
									<th style="text-align:center;vertical-align:middle;" hidden='hidden'>Moneda</th>
									<th style="text-align:center;vertical-align:middle;">Total</th>
									<th style="text-align:center;vertical-align:middle;">Ticket </th>									
									<th style="text-align:center;vertical-align:middle;" hidden='hidden'>Whatsapp</th>
									<th style="text-align:center;vertical-align:middle;">A4</th>
									<th style="text-align:center;vertical-align:middle;" hidden='hidden'>XML</th>
									<th style="text-align:center;vertical-align:middle;" hidden='hidden'>CDR</th>									
									<th style="text-align:center;vertical-align:middle;" hidden='hidden'>Notas</th>
									<th style="text-align:center;vertical-align:middle;" hidden='hidden'>Baja</th>
									<th style="text-align:center;vertical-align:middle;" hidden='hidden'>Opciones</th>
									<th style="text-align:center;vertical-align:middle;">Anular</th>
									<th style="text-align:center;vertical-align:middle;" hidden='hidden'>Editar<br>Comprobante</th>
									<th style="text-align:center;vertical-align:middle;" hidden='hidden'>Editar<br>Medio Pago</th>
									<th style="text-align:center;vertical-align:middle;">Detalle</th>
									<th style="text-align:center;vertical-align:middle;" hidden='hidden'>G. Remisión</th>
									<th style="text-align:center;vertical-align:middle;" hidden='hidden'>Correos</th>
									<th style="text-align:center;vertical-align:middle;" hidden='hidden'>Enviar Correo</th>
									<th style="text-align:center;vertical-align:middle;" hidden='hidden'>SUNAT</th>
									
								
								</tr>
							</thead>
							
							<tbody>
								@foreach($comprobantes as $comp)
								<tr>
									
								 	<!--<td>{{Carbon::parse($comp->ccafem)->format('d-m-Y')}}</td>-->
								 	<td>{{Carbon::parse($comp->fecha_hora)->format('d-m-Y H:i:s')}}</td>
									<td hidden='hidden'>{{$comp->tdocod}} </td>
									<td hidden='hidden'>{{$comp->serdoc}}</td>
									<td hidden='hidden'>{{$comp->numdoc}}</td>
									<td>{{$comp->mes_nom}}</td>
									<td>{{$comp->name}} {{$comp->apeusu}}</td>
									<td style="font-weight:bold;text-align:right;" hidden='hidden'>{{$comp->pedido}}</td>
									<td title='{{$comp->tdides}}'>{{$comp->ccandi}}</td>
									<td style="width:210px;" >{{$comp->ccanom}}</td>
									<td hidden='hidden'>{{$comp->monnom}}</td>
									<td align="right">{{number_format($comp->ccaitv,'2','.',',')}}</td>
									

									@if($dat_suc->ticket_pantalla=='1')
											<td><a   onclick="imprimir_voucher('{{$comp->IdCpe_cabecera}}');"  ><center><i class="fa fa-file-pdf-o fa-lg"></i></center></a></td>
									@else
										<td><a   onclick="imprimir_ticket('{{$comp->IdCpe_cabecera}}','{{$comp->tdocod}}');"  ><center><i class="fa fa-file-pdf-o fa-lg"></i></center></a></td>

									@endif
									
									
									
									
									<td hidden='hidden'><a href="" data-target="#modal-whastapp-{{$comp->IdCpe_cabecera}}" data-toggle="modal" ><center><img src="/icon/WHATS.png" title="ANULADO" height="20px" width="20px"></center></a></td>

									<td><a href="/descargar/{{$comp->IdCpe_cabecera}}/pdf"><center><i class="fa fa-file-excel-o fa-lg"></i></center></a></td>
									
									 @if(empty($comp->ccaenlace))
										<td hidden='hidden'><a href="/descargar/{{$comp->IdCpe_cabecera}}/xml"><center><i class="fa fa-file-excel-o fa-lg"></i></center></a></td>
									@else
										<td hidden='hidden'><a href="{{$comp->ccaenlace}}.xml"><center><i class="fa fa-file-excel-o fa-lg"></i></center></a></td>
									@endif


									@if(empty($comp->ccaenlace))
										<td hidden='hidden'><a href="/descargar/{{$comp->IdCpe_cabecera}}/cdr"><center><i class="fa fa-file-pdf-o fa-lg"></i></center></a></td>
									@else
										<td hidden='hidden'><a href="{{$comp->ccaenlace}}.cdr"><center><i class="fa fa-file-pdf-o fa-lg"></i></center></a></td>
									@endif

									
										@if($comp->ccanot=="")
										   <td hidden='hidden'><center>---</center></td>
										@else
											<td hidden='hidden'><a href="/listarnotas/{{$comp->IdCpe_cabecera}}">{{$comp->ccanot}}</a></td>
										@endif

										@if($comp->ccabaj=="")
										    <td hidden='hidden'><center>---</center></td>
										@else
										 	<td hidden='hidden'><a href="/consultarticketbaja/{{$comp->IdCpe_cabecera}}">{{$comp->ccabaj}}</a></td>
										@endif
									

									
									<td hidden='hidden'> 
										
									<div class="dropdown" hidden='hidden'>
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
											<td hidden='hidden'>
												@if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin'))
												<a href="/editarventa/{{$comp->IdCpe_cabecera}}"><center><button class="btn btn-primary btn-sm">EDITAR</button></center></a>

												<!--<a href="/editarventapos/{{$comp->IdCpe_cabecera}}"><center><button class="btn btn-primary btn-sm">EDITAR</button></center></a>-->
												@endif
											</td>
										@else
										  
											<td hidden='hidden'>
												@if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin')  )
												<center><button class="btn btn-primary btn-sm" disabled="disabled">EDITAR</button></center>
												@endif
											</td>
											
										@endif

										<td hidden='hidden'>
											@if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin'))
												<a href="/editarmp/{{$comp->IdCpe_cabecera}}"><center><button class="btn btn-primary btn-sm">EDITAR</button></center></a>

												<!--<a href="/editarmppos/{{$comp->IdCpe_cabecera}}"><center><button class="btn btn-primary btn-sm">EDITAR</button></center></a>-->

											@endif
										</td>
								

										<td>
											<a href="/detalleventa/{{$comp->IdCpe_cabecera}}"><button class="btn btn-success btn-sm">Detalle</button></a>
										</td>

										<td hidden='hidden'>
											<a  href="/venta/crearguia/{{$comp->IdCpe_cabecera}}"><button class="btn btn-success btn-sm">G. Remisión</button></a>
										</td>

											<td hidden='hidden'>
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

										<td hidden='hidden'>
											<a href="" data-target="#modal-correo-{{$comp->IdCpe_cabecera}}" data-toggle="modal"><center><img src="/img/mail.jpg"  height="40px" width="40px"></center></a>
										</td>

									
										
										@if($comp->ccacodsun=='0')
										<td hidden='hidden'>
											<a><center><img src="/icon/check.png" title="{{$comp->ccadessun}}" height="20px" width="20px"></center></a>
										</td>
										@elseif($comp->ccacodsun=='8')
										<td hidden='hidden'>
											<a><center><img src="/icon/error.png" title="{{$comp->ccadessun}}"  height="20px" width="20px"></center></a>
										</td>
										@elseif($comp->ccacodsun >'100' && $comp->ccacodsun <'1999')
										<td hidden='hidden'>
											<a href="/enviarcomprobante/{{$comp->IdCpe_cabecera}}"><center><img title="{{$comp->ccadessun}}" src="/icon/iconwarning.png" height="20px" width="20px"></center></a>
										</td>

										@elseif($comp->ccacodsun > '2000' && $comp->ccacodsun <'3999')
										<td hidden='hidden'>
											<a><center><img src="/icon/error.png" title="{{$comp->ccadessun}}"  height="20px" width="20px"></center></a>
										</td>
										@elseif($comp->ccacodsun > '4000')
										<td hidden='hidden'>
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