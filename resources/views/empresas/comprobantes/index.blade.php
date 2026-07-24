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
        var url = "/voucher/" + id;
        window.open(url, '_blank');
    }
	var href = $('#btnPrint').attr('href');
	
	$("#btnPrint").printPage({
		  url: href,
		  attr: "href",
		  messageBox:false,
	})
</script>

<style>
    /* ESTILOS ELEGANTES HOLA P */
    .shadow-box { 
        box-shadow: 0 4px 15px rgba(0,0,0,0.1); 
        border-radius: 8px; 
        border-top: none !important; 
		background: #fff;
    }
    .custom-header { 
        background-color: #2c3e50 !important; 
        color: white !important; 
        border-radius: 8px 8px 0 0; 
    }
	.custom-subheader {
		background-color: #34495e !important;
		color: white !important;
		font-size: 10pt;
	}
    .btn-elegant {
        box-shadow: 0 2px 5px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
		border-radius: 4px;
    }
    .btn-elegant:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    /* Estilos mejorados para la tabla */
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transition: background-color 0.3s;
    }
	
	.table-vertical-align th, .table-vertical-align td {
		vertical-align: middle !important;
	}

	/* Estilos para alertas */
	.alert-elegant {
		border-radius: 8px;
		box-shadow: 0 4px 10px rgba(0,0,0,0.08);
		border: none;
	}
</style>

<section class="content">
	<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
	    @if(session()->has('info'))
	    	<div class="alert alert-danger alert-elegant alert-dismissible">
	    	  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			  <h4><i class="icon fa fa-ban"></i> Alerta!</h4> {{ session('info') }}
			</div>
	    @endif

	    @if(session()->has('success'))
	    	<div class="alert alert-success alert-elegant alert-dismissible">
	    	  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			  <h4><i class="icon fa fa-check"></i> Información!</h4> {{ session('success') }}
			</div>
	    @endif
	</div>
</div>

	<div class="row">
        	<div class="col-xs-12">
          		<div class="box shadow-box">
          			<div class="box-header custom-header">
          				<font color="white"><center><strong><i class="fa fa-list-alt"></i> REGISTRO DE VENTAS</strong></center></font>
          			</div>
	            	<div class="box-body">
	            		@include('empresas.comprobantes.buscarcomprobantes')
	            	</div>
	            </div>
	        </div>
	</div> 
             
    	<div class="row">
        	<div class="col-xs-12">
          		<div class="box shadow-box">
	            	<div class="box-body table-responsive" >
							<table id=""  class="table table-bordered table-hover table-vertical-align">
							<thead >
								<tr class="custom-header">
									<th colspan="30"><center><strong><i class="fa fa-shopping-cart"></i> REGISTRO DE VENTAS</strong></center></th>
								</tr>
								<tr class="custom-subheader">
									<th style="text-align:center;">Fecha y Hora</th>
									<th style="text-align:center;">Nro. de Documento</th>
									<th style="text-align:center;" hidden='hidden'>Serie</th>
									<th style="text-align:center;" hidden='hidden'> N°</th>
									<th style="text-align:center;" hidden='hidden'>Ubicacion</th>
									<th style="text-align:center;" hidden='hidden'>Mozo</th>
									<th style="text-align:center;" hidden='hidden'>Pedido</th>
									<th style="text-align:center;" hidden='hidden'>DNI/RUC</th>
									<th style="text-align:center; width:210px;">Datos del Cliente</th>
									<th style="text-align:center;" hidden='hidden'>Moneda</th>
									<th style="text-align:center;">Total</th>									
									<th style="text-align:center;vertical-align:middle;">Medio pago</th>
									<th style="text-align:center;">Ticket </th>
									<th style="text-align:center;vertical-align:middle;">Ticket Pantalla </th>
									<th style="text-align:center;">Whatsapp</th>
									<th style="text-align:center;">A4</th>
									<th style="text-align:center;">XML</th>
									<th style="text-align:center;">CDR</th>									
									<th style="text-align:center;" hidden='hidden'>Notas</th>
									<th style="text-align:center;">Baja</th>
									<th style="text-align:center;">Opciones</th>
									<th style="text-align:center;">Anular</th>
									<th style="text-align:center;">Editar<br>Venta</th>
									<th style="text-align:center;">Editar<br>Medio Pago</th>
									<th style="text-align:center;">Detalle</th>
									<th style="text-align:center;" hidden='hidden'>G. Remisión</th>
									<th style="text-align:center;" hidden='hidden'>Correos</th>
									<th style="text-align:center;" hidden='hidden'>Enviar Correo</th>
									<th style="text-align:center;">Validar<br>CPE</th>
									<th style="text-align:center;">SUNAT</th>
								</tr>
							</thead>
							
							<tbody>
								@foreach($comprobantes as $comp)
								<tr>
									
								 	<td><center>{{Carbon::parse($comp->fecha_hora)->format('d-m-Y H:i:s')}}</center></td>
									<td><center><strong>{{$comp->des_doc}}-{{$comp->serdoc}}-{{$comp->numdoc}}</strong></center></td>
									<td hidden='hidden'>{{$comp->serdoc}}</td>
									<td hidden='hidden'>{{$comp->numdoc}}</td>
									<td hidden='hidden'>{{$comp->mes_nom}} - {{$comp->name}} {{$comp->apeusu}}</td>
									<td hidden='hidden'>{{$comp->name}} {{$comp->apeusu}}</td>
									<td style="font-weight:bold;text-align:right;" hidden='hidden'>{{$comp->pedido}}</td>
									<td title='{{$comp->tdides}}' hidden='hidden'></td>
									<td style="width:210px;" >{{$comp->ccandi}} - <strong>{{$comp->ccanom}}</strong></td>
									<td hidden='hidden'>{{$comp->monnom}}</td>
									<td align="right"><strong>{{number_format($comp->ccaitv,'2','.',',')}}</strong></td>

									<td>
										@php
											$mediosPagos = DB::table('venta_medio_pago as vmp')
												->select('mp.nom_med_pag', 'vmp.monto')
												->join('medios_pagos as mp', 'vmp.id_med_pag', '=', 'mp.id_med_pag')
												->where('vmp.IdCpe_cabecera', $comp->IdCpe_cabecera)
												->get();
										@endphp

										@if($mediosPagos->isNotEmpty())
											@foreach($mediosPagos as $pago)
												{{ $pago->nom_med_pag }}: {{ number_format($pago->monto, 2, '.', ',') }}<br>
											@endforeach
										@else
											N/A
										@endif
									</td>
									
									@if($dat_suc->ticket_pantalla=='1')
											<td><a   onclick="imprimir_voucher('{{$comp->IdCpe_cabecera}}');" style="cursor:pointer;" ><center><i class="fa fa-file-pdf-o fa-lg text-danger"></i></center></a></td>
									@else
										<td><a   onclick="imprimir_ticket('{{$comp->IdCpe_cabecera}}','{{$comp->tdocod}}');" style="cursor:pointer;" ><center><i class="fa fa-file-pdf-o fa-lg text-danger"></i></center></a></td>
									@endif

									<td><a   onclick="imprimir_voucher('{{$comp->IdCpe_cabecera}}');" style="cursor:pointer;" ><center><i class="fa fa-file-pdf-o fa-lg text-danger"></i></center></a></td>
									
									<td><a href="" data-target="#modal-whastapp-{{$comp->IdCpe_cabecera}}" data-toggle="modal" ><center><img src="/icon/WHATS.png" title="WHATSAPP" height="20px" width="20px"></center></a></td>

									<td><a href="/descargar/{{$comp->IdCpe_cabecera}}/pdf"><center><i class="fa fa-file-pdf-o fa-lg text-danger"></i></center></a></td>
									
									 @if(empty($comp->ccaenlace))
										<td><a href="/descargar/{{$comp->IdCpe_cabecera}}/xml"><center><i class="fa fa-file-code-o fa-lg text-info"></i></center></a></td>
									@else
										<td><a href="{{$comp->ccaenlace}}.xml"><center><i class="fa fa-file-code-o fa-lg text-info"></i></center></a></td>
									@endif

									@if(empty($comp->ccaenlace))
										<td><a href="/descargar/{{$comp->IdCpe_cabecera}}/cdr"><center><i class="fa fa-file-archive-o fa-lg text-warning"></i></center></a></td>
									@else
										<td><a href="{{$comp->ccaenlace}}.cdr"><center><i class="fa fa-file-archive-o fa-lg text-warning"></i></center></a></td>
									@endif
									
									@if($comp->ccanot=="")
									   <td hidden='hidden'><center>---</center></td>
									@else
										<td hidden='hidden'><a href="/listarnotas/{{$comp->IdCpe_cabecera}}">{{$comp->ccanot}}</a></td>
									@endif

									@if($comp->ccabaj=="")
									    <td><center><span class="text-muted">---</span></center></td>
									@else
									 	<td><a href="/consultarticketbaja/{{$comp->IdCpe_cabecera}}">{{$comp->ccabaj}}</a></td>
									@endif
									
									<td> 
										<div class="dropdown">
										  <button class="btn btn-default btn-xs dropdown-toggle btn-elegant" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
										    OPCIONES <span class="caret"></span>
										  </button>
										  <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
										   		@if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin') )

										   		 @if($comp->tdocod=='03' ||  $comp->tdocod=='01')
										   		 	<li><a class="dropdown-item" href="/tiponota/{{$comp->tdocod}}/{{$comp->IdCpe_cabecera}}/{{'07'}}">Nota de crédito</a></li>
										   		 	<li hidden="hidden"><a class="dropdown-item" href="/tiponota/{{$comp->tdocod}}/{{$comp->IdCpe_cabecera}}/{{'08'}}">Nota de Débito</a></li>
										   		 @endif
										  		
						                     	@endif
										  </ul>
										</div>
									</td>
										
									<td style="text-align: center;">
										@if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin') )
											@if($comp->ccacodsun=='0') 
												 <a href="" data-target="#modal-baja-{{$comp->IdCpe_cabecera}}" data-toggle="modal" disabled="disabled">
												 	
												 </a>
											@else
												<a href="" data-target="#modal-baja-{{$comp->IdCpe_cabecera}}" data-toggle="modal">
													<img src="/icon/error.png" title="ANULAR" height="20px" width="20px">
												</a>
											@endif
										@else
											<center>---</center>
										@endif
									</td>

									<td>
										@if(is_null($comp->ccacodsun) || $comp->ccacodsun=='0306' || $comp->ccacodsun=='1061' || ($comp->ccacodsun=='2017' && $comp->ccacodsun=='2104'))
											  @if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin')  )
											<center><a href="/editarventa/{{$comp->IdCpe_cabecera}}" class="btn btn-primary btn-sm btn-elegant"><i class="fa fa-edit"></i> Editar</a></center>
											@endif
										@else
										  
											@if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin')  )
											<center><button class="btn btn-default btn-sm btn-elegant" disabled="disabled"><i class="fa fa-edit"></i> Editar</button></center>
											@endif
											
										@endif
									</td>

									<td>
										@if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin'))
											<center><a href="/editarmp/{{$comp->IdCpe_cabecera}}" class="btn btn-primary btn-sm btn-elegant">EDITAR</a></center>											
										@endif
									</td>
							
									<td>
										<center><a href="/detalleventa/{{$comp->IdCpe_cabecera}}" class="btn btn-success btn-sm btn-elegant"><i class="fa fa-eye"></i> Detalle</a></center>
									</td>

									<td hidden='hidden'>
										<a  href="/venta/crearguia/{{$comp->IdCpe_cabecera}}" class="btn btn-success btn-sm btn-elegant">G. Remisión</a>
									</td>

									<td hidden='hidden'>
										@if(empty($comp->clicorcli))
											{{$comp->clicor}}<br>
										@else
											{{$comp->clicorcli}}<br>
										@endif
									</td>

									<td hidden='hidden'>
										<a href="" data-target="#modal-correo-{{$comp->IdCpe_cabecera}}" data-toggle="modal"><center><img src="/img/mail.jpg"  height="40px" width="40px"></center></a>
									</td>

									<td style="text-align: center; vertical-align: middle;">
									    <a href="{{ url('consultar-cpe-venta/'.$comp->IdCpe_cabecera) }}" class="btn btn-info btn-sm btn-elegant" title="Consultar validez en API">
									        <i class="fa fa-search"></i> CPE
									    </a>
									</td>
									
									<td>
										@if($comp->ccacodsun=='0')
											<center><a><img src="/icon/check.png" title="{{$comp->ccadessun}}" height="20px" width="20px"></a></center>
										@elseif($comp->ccacodsun=='8')
											<center><a><img src="/icon/error.png" title="{{$comp->ccadessun}}"  height="20px" width="20px"></a></center>
										@elseif($comp->ccacodsun >'100' && $comp->ccacodsun <'1999')
											<center><a href="/enviarcomprobante/{{$comp->IdCpe_cabecera}}"><img title="{{$comp->ccadessun}}" src="/icon/iconwarning.png" height="20px" width="20px"></a></center>
										@elseif($comp->ccacodsun > '2000' && $comp->ccacodsun <'3999')
											<center><a><img src="/icon/error.png" title="{{$comp->ccadessun}}"  height="20px" width="20px"></a></center>
										@elseif($comp->ccacodsun > '4000')
											<center><a><img src="/icon/checkobs.png" title="{{$comp->ccadessun}}" height="20px" width="20px"></a></center>
										@else
											<center><span class="text-muted" title="Pendiente">-</span></center>
										@endif
									</td>

								</tr>
									@include('empresas.comprobantes.modal')
									@include('empresas.puntosventas.modal_whatsapp')
									@include('empresas.comprobantes.modalbaja')
								@endforeach
							</tbody>
						</table><br>
					</div>	
					<div class="box-footer text-right">
						{{$comprobantes->render()}}
					</div>
				</div>	
			</div>
		</div>
	</section>
@endsection