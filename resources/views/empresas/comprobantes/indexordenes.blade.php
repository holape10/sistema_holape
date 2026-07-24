@extends('layouts.empresas')
@section('contenido')

<script>

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
          			<div class="box-header" style="background:blue;">
          				<font color="white"><center><STRONG>ORDENES DE SERVICIOS</STRONG></center></font>
          			</div>
	            	<div class="box-body">
	            		@include('empresas.comprobantes.buscarordenes')
	            	</div>
	            </div>
	        </div>
	</div> 
              
    	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
							<table id=""  class="table table-bordered table-hover table-striped">
							<thead>
								<tr>
									<th colspan="6" style="background:blue"><font color="white"><center><strong>Datos del Equipo</strong></center></font></th>
									<th colspan="3" style="background:blue"><font  color="white"><center><strong>Datos de Pago</strong></center></font></th>
									<th colspan="3" style="background:blue"><font  color="white"><center><strong>ACCIONES</strong></center></font></th>
								</tr>
								<tr>
									<!--<th>Fec. Emision</th>-->
									<!--<th>Tipo</th>-->
									<!--<th>Serie</th>-->
									<th><center>N°</center></th>
									<!--<th><center>RUC / DNI / Otros</center></th>-->
									<th><center>Cliente</center></th>
									<th><center>Tecnico</center></th>
									<th><center>Marca</center></th>
									<th><center>Modelo</center></th>
									<th><center>Estado</center></th>
									<!--<th><center>Moneda</center></th>-->
									<th><center>Costo</center></th>
									<th><center>Abono</center></th>
									<th><center>Pendiente</center></th>
									<th><center>PDF</center></th>
									<th><center>BAJAS</center></th>
								
									<th><center>Acciones</center></th>
									
									
								
								</tr>
							</thead>
							
							<tbody>
								@foreach($comprobantes as $comp)
								<tr>
								 	<!--<td>{{Carbon::parse($comp->ccafem)->format('d-m-Y')}}</td>-->
									<!--<td>{{$comp->tdodes}}</td>-->
									<!--<td>{{$comp->serdoc}}</td>-->
									<td><STRONG>OS-{{$comp->numdoc}}</STRONG></td>
									<!--<td title='{{$comp->tdides}}'>{{$comp->ccandi}}</td>-->
									<td>{{$comp->ccanom}}</td>
									<td>{{$comp->nom_tec}} {{$comp->ape_tec}}</td>
									<td>{{$comp->marca}}</td>
									<td>{{$comp->modelo}}</td>
									<td> <a href="" data-target="#modal-orden-{{$comp->IdCpe_cabecera}}" data-toggle="modal"><button class="btn btn-sm btn-block" style="background-color:{{$comp->est_equ_col}}"><FONT color="white"><STRONG>{{$comp->est_equ_nom}}</STRONG></FONT></button></a></td>

									<!--<td>{{$comp->monnom}}</td>-->
									<td align="right">{{number_format($comp->ccaitv,'2','.',',')}}</td>
									<td align="right">{{number_format($comp->totalcontado,'2','.',',')}}</td>
									<td align="right">{{number_format($comp->totalcredito,'2','.',',')}}</td>
								
									<td><a href="/descargar/{{$comp->IdCpe_cabecera}}/pdf"><center><i class="fa fa-file-excel-o fa-lg"></i></center></a></td>
									

									@if($comp->ccabaj=="")
									    <td><center>---</center></td>
									@else
									 	<td><a href="/consultarticketbaja/{{$comp->IdCpe_cabecera}}">{{$comp->ccabaj}}</a></td>
									@endif
									
									<td><CENTER> 
										<a href="/formbajacomprobante/{{$comp->IdCpe_cabecera}}"><button class="btn btn-sm btn-danger">ANULAR</button></a>
										<a href="/modificarorden/{{$comp->IdCpe_cabecera}}"><button class="btn btn-sm btn-info">EDITAR</button></a> 
										<a href="https://api.whatsapp.com/send?phone=+51{{$comp->telefono}}&text={{$comp->mensaje_estado}}" target="_blank"><button class="btn btn-sm btn-success">WHATSAPP</button></a>
									    <a href="#" ><button class="btn btn-sm btn-primary">CODIGO BARRAS</button></a>
									</CENTER>
									</td>


									
										

										
								</tr>
								@include('empresas.comprobantes.modalorden');
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