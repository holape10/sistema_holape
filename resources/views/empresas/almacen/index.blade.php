@extends('layouts.empresas')
@section('contenido')
<script>
$(document).ready(function()
{       


      $("#promocion").change(function() {
         
              $('#formalmacen').submit();
        

        });



 });

</script>
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
			  <strong>Información!</strong> {{ session('success') }}
			</div>
	    @endif
	</div>
</div>

	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
          			<div class="box-header" style="background-color:#337ab7;">
          				<font color="white"><CENTER><strong>Movimientos de Productos</strong></CENTER></font>
          				<!--  <div class="box-tools pull-right">
				             <a  href="/transferir"><button type="button" class="btn btn-success btn-sm"><strong>Transferir Productos</strong></button></a>
				          </div>-->
				    </div>
	            	<div class="box-body">
	            		@include('empresas.almacen.buscar')
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
								<th colspan="11" style="background:#337ab7;">
									<font color="white"><strong><center>MOVIMIENTOS DESDE: {{$fecin}} - HASTA {{$fecfin}}</center></strong></font>
								</th>
							</thead>
							<thead>
								<tr>
									<th>Fec. Movimiento</th>
									<th>Tipo Movimiento</th>
									<th>Documento</th>
									<th style="width:210px;">Descripci&oacute;n</th>
									<th>Cantidad</th>
									<th>Presentaci&oacute;n</th>
									<th>Factor</th>
									<th>Stock</th>
									<th>Origen</th>
									<th>Destino</th>
									<th>Estado</th>
									
								</tr>
							</thead>
							<tbody>

								@foreach($movimientos as $mov)
								<tr>
								 	<td>{{$mov->mov_fec}}</td>
								 	<td>{{$mov->mov_mot}}</td>
								 	<td>{{$mov->comprobante}}</td>
									<td>@if(!empty($mov->codpro)) {{$mov->codpro}} - @endif  {{$mov->pronom}}</td>
									<td>{{$mov->cantidad}}</td>
									<td>{{$mov->unidad}}</td>
									<td>{{$mov->factor}}</td>
									<td>{{$mov->stockmov}}</td>
									<td>{{$mov->suc_origen}} @if(!empty($mov->alm_origen)) - {{$mov->alm_origen}} @endif</td>
									<td>{{$mov->suc_destino}} @if(!empty($mov->alm_destino))- {{$mov->alm_destino}}@endif</td>
									@if($mov->est_mov =='RECEPCIONAR')
										<td><button class="btn btn-danger btn-sm btn-block">Por Recepcionar</button></td>
									@else
										<td><button class="btn btn-success btn-sm btn-block" disabled="disabled">Registrado</button></td>
									@endif
									
									
								</tr>
								@endforeach
							</tbody>
						</table><br>
					</div>	
					{{$movimientos->render()}}
				</div>	
			</div>
		</div>
	</section>

@endsection