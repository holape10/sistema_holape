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
          				<font color="white"><center><STRONG>EQUIPOS EN REPARACION</STRONG></center></font>
          			</div>
	            	<div class="box-body">
	            		@include('empresas.comprobantes.buscarequipos')
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
									<th style="background:blue"><font color="white"><center>N°</center></font></th>
									<th style="background:blue"><font color="white"><center>Cliente</center></font></th>
									<th style="background:blue"><font color="white"><center>Marca</center></font></th>
									<th style="background:blue"><font color="white"><center>Modelo</center></font></th>
									<th style="background:blue"><font color="white"><center>Fecha</center></font></th>
									<th style="background:blue"><font color="white"><center>Total a Pagar</center></font></th>
									<th style="background:blue"><font color="white"><center>Estado</center></font></th>
									<th style="background:blue"><font color="white"><center>Fallas</center></font></th>
									<th style="background:blue"><font color="white"><center>T&eacute;cnico</center></font></th>
									
								</tr>
							</thead>
							
							<tbody>
								@foreach($comprobantes as $comp)
								<tr>
									<td><STRONG>OS-{{$comp->numdoc}}</STRONG></td>
									<td>{{$comp->ccanom}}</td>
									<td>{{$comp->marca}}</td>
									<td>{{$comp->modelo}}</td>
									
									<td>{{$comp->fecha_hora}}</td>
									<td align="right">{{number_format($comp->ccaitv,'2','.',',')}}</td>
									<td><button class="btn btn-sm btn-block" style="background-color:{{$comp->est_equ_col}}"><FONT color="white"><STRONG>{{$comp->est_equ_nom}}</STRONG></FONT></button></td>
									<td>{{$comp->fallas}}</td>
									<td>{{$comp->nom_tec}} {{$comp->ape_tec}}</td>
	
								</tr>
							
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