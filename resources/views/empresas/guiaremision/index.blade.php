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
	        url: '/anularguiaremision/'+comp+'/'+motivo,
	      }).done(function(respuesta){

	          window.location.href = "/guiasremision";
	    	
	      });
	}

	function consultarYDescargarPdf(e, idCpe, numTicket) {
	e.preventDefault();

	// Puedes mostrar tu loader aquí para que el usuario sepa que está cargando
	$(".imgloadanular").show(); 

	$.ajax({
		type: "GET",
		url: '/consultarticketgre/' + idCpe + '/' + numTicket,
	}).done(function(respuesta) {
		// Ocultar el loader
		$(".imgloadanular").hide();

		// 1. Forzar la descarga del PDF ahora que el QR ya debería estar lleno
		window.location.href = '/descargarguia/' + idCpe + '/pdf';

		// 2. Recargar la página después de un par de segundos para actualizar la vista (el botón cambiará a rojo)
		setTimeout(function() {
			window.location.reload();
		}, 2000);

	}).fail(function() {
		$(".imgloadanular").hide();
		alert("Ocurrió un error al intentar consultar el ticket en la SUNAT.");
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
			  <strong>Información!</strong> {{ session('success') }}
			</div>
	    @endif
	</div>
</div>

	<div class="row">

        	<div class="col-xs-12">
          		<div class="box">
          			<div class="box-header" style="background-color:blue;">
          				<font color="white"><center><strong>GUÍAS DE REMISIÓN</strong></center></font>
          			</div>
	            	<div class="box-body">
	            		@include('empresas.guiaremision.buscarcomprobantes')
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
								<tr style="font-size:10pt;font-weight:bold;color:white;background: #808080;">
									<th style="text-align:center;vertical-align:middle;width:100px;">Fec. Emision</th>
									<th style="text-align:center;vertical-align:middle;">Serie</th>
									<th style="text-align:center;vertical-align:middle;width:40px;">N°</th>
									<th style="text-align:center;vertical-align:middle;width:200px;">RUC / DNI / Otros</th>
									<th style="width:210px;">Nombre o Razón Social</th>
									<th style="text-align:center;vertical-align:middle;">Fecha Traslado</th>
									<th style="text-align:center;vertical-align:middle;">Direcci&oacute;n Llegada</th>
									<th style="text-align:center;vertical-align:middle;">Motivo</th>
									<th style="text-align:center;vertical-align:middle;">A4 PDF</th>
									<th style="text-align:center;vertical-align:middle;">XML</th>
								
									<th style="text-align:center;vertical-align:middle;">Estado SUNAT</th>
									<th style="text-align:center;vertical-align:middle;">Fecha de Anulación</th>
									<th style="text-align:center;vertical-align:middle;">Anular</th>
										<th style="text-align:center;vertical-align:middle;">Consultar Ticket</th>
									<th style="text-align:center;vertical-align:middle;">CDR</th>
									<th style="text-align:center;vertical-align:middle;">Reenviar</th>
								
								</tr>
							</thead>
							
							<tbody>
								@foreach($comprobantes as $comp)
								<tr>
								 	<td>{{Carbon::parse($comp->fechaemision)->format('d-m-Y')}}</td>
									<td>{{$comp->serieguia}}</td>
									<td>{{$comp->numeroguia}}</td>
									<td title='{{$comp->tdides}}'>{{$comp->ruccliente}}</td>
									<td style="width:210px;">{{$comp->nomcliente}}</td>
									<td>{{Carbon::parse($comp->fechatraslado)->format('d-m-Y')}}</td>
									<td>{{$comp->direccionllegada}}</td>
									<td>{{$comp->motivo}}</td>

									@if(empty($comp->ccaenlace))
										@if(empty($comp->cadena_qr) && !empty($comp->numTicket))
											<td>
												<a href="#" onclick="consultarYDescargarPdf(event, '{{$comp->IdCpe_guia}}', '{{$comp->numTicket}}')" title="Consultar Ticket y Descargar">
													<center><i class="fa fa-file-pdf-o fa-lg text-warning"></i></center>
												</a>
											</td>
										@else
											<td>
												<a href="/descargarguia/{{$comp->IdCpe_guia}}/pdf">
													<center><i class="fa fa-file-pdf-o fa-lg text-danger"></i></center>
												</a>
											</td>
										@endif
									@else
										<td>
											<a href="{{$comp->ccaenlace}}.pdf">
												<center><i class="fa fa-file-pdf-o fa-lg text-danger"></i></center>
											</a>
										</td>
									@endif

									@if(empty($comp->ccaenlace))
										<td><a href="/descargarguia/{{$comp->IdCpe_guia}}/xml"><center><i class="fa fa-file-pdf-o fa-lg"></i></center></a></td>
									@else
										<td><a href="{{$comp->ccaenlace}}.xml"><center><i class="fa fa-file-excel-o fa-lg"></i></center></a></td>
									@endif

								
								




									<td>{{$comp->ccadessun}}</td>
									<td>@if(!empty($comp->ccabaj))<button type="button" class="btn btn-sm btn-block btn-danger">ANULADO</button>@endif</td>
									<td style="text-align:center;">@if(empty($comp->ccabaj))<a href="" data-target="#modal-baja-{{$comp->IdCpe_guia}}" data-toggle="modal"><img src="/icon/error.png" title="ANULADO" height="20px" width="20px"></a>@else <img src="/icon/error.png" title="ANULADO" height="20px" width="20px"> @endif</td>

								
										<td style="text-align:center;"><a href="/consultarticketgre/{{$comp->IdCpe_guia}}/{{$comp->numTicket}}"><button class="btn btn-sm btn-success btn-block">Consultar Ticket</button></a></td>
											<td style="text-align:center;"><a href="/descargarcdrguia/{{$comp->IdCpe_guia}}"><button class="btn btn-sm btn-success btn-block">CDR</button></a></td>
												<td style="text-align:center;"><a href="/enviar_guia_sunat/{{$comp->IdCpe_guia}}"><button class="btn btn-sm btn-danger btn-block">Reenviar</button></a></td>
									
								</tr>
								@include('empresas.guiaremision.modal_anular_guia')
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