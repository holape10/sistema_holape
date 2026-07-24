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
          				<font color="white"><center><strong>REGISTRO DE VALES INGRESOS</strong></center></font>
          			</div>
	            	<div class="box-body">
	            		@include('empresas.salidasingresosproductos.buscarvalesingresos')
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
									<th>A4</th>
									
									<th>ANULAR</th>
									<th>DETALLE</th>
									<th>EDITAR</th>
									
									
									
								
								</tr>
							</thead>
							
							<tbody>
								@foreach($comprobantes as $comp)
								<tr>
									
								 	<td>{{Carbon::parse($comp->ccafem)->format('d-m-Y')}}</td>
									<td>{{$comp->tdodes}}</td>
									<td>{{$comp->serdoc}}</td>
									<td>{{$comp->numdoc}}</td>
									
								
								
									<td><a href="/descargar/{{$comp->IdCpe_cabecera}}/pdf"><center><i class="fa fa-file-excel-o fa-lg"></i></center></a></td>
									
									
									
								
										<td>
										 @if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin') )
											 <a href="" data-target="#modal-baja-{{$comp->IdCpe_cabecera}}" data-toggle="modal"><img src="/icon/error.png" title="ANULADO" height="20px" width="20px"></a>
										@endif
										</td>
											<td>
												<a href="/detalleventa/{{$comp->IdCpe_cabecera}}"><button class="btn btn-success btn-sm">Detalle</button></a>
										</td>
									

										
									
											<td>
												<a href="/editarvaleingreso/{{$comp->IdCpe_cabecera}}"><center><button class="btn btn-primary btn-sm">EDITAR</button></center></a>
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