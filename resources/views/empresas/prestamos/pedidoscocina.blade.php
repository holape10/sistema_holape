@extends('layouts.empresas')
@section('contenido')
<script>
$(document).ready(function(){   
       
      setTimeout(refrescar, 60000);

});
	
	function refrescar(){

		window.location.href = '/cocina';

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
          			<div class="box-header with-border" style="background-color:blue;">
                    	<font color="white"><center><strong>PEDIDOS EN COCINA</strong></center></font>
                    	 <div class="box-tools pull-right">
				    	<!--<a href="/agregarpedido/delivery"><button  class="btn btn-sm btn-success btn-sm"> NUEVO PEDIDO</button></a> -->
				    </div>
                	</div>
	            	<div class="box-body">
						<table id="tblpedidos"  class="table table-bordered table-hover">
							<thead>
								<tr>
									<th>Pedido</th>
									<th>Cliente</th>
									<th>Fecha</th>
									<th>Tipo</th>
									<th>Estado</th>
									<th>Detalle</th>
									<th>Opciones</th>
								</tr>
							</thead>
							<tbody>
								@foreach($pedidos as $ped)
								<tr>
									<td>PEDIDO - {{$ped->ped_id}}</td>
									<td>@if(!empty($ped->cliente))
											{{$ped->cliente}}
										@endif
										@if(!empty($ped->direccion))
											| {{$ped->direccion}}
										@endif
										@if(!empty($ped->telefono))
											| {{$ped->telefono}}
										@endif
										@if(!empty($ped->mes_nom))
											{{$ped->mes_nom}}
										@endif</td>
								 	<td>{{$ped->fecha_hora}}</td>
								 	<td>{{$ped->ped_tip}}</td>
								 	<td>{{$ped->est_ped_nom}}</td>
								 	<td>@foreach($detalles as $det)
								 			@if($ped->ped_id == $det->ped_id)
								 				<li>Cantidad: {{$det->cantidad}} | {{$det->pronom}} @if(!empty($det->detalle))| {{$det->detalle}} @endif</li>
								 			@endif
								 		@endforeach

								 	</td>
								 	<td>
								 		
										<a href="" data-target="#modal-preparado-{{$ped->ped_id}}" data-toggle="modal"><img title="PREPARADO" width="35px" height="35px" src="/icon/preparado.png"></a>
									
								 	</td>
								</tr>
							
								@include('empresas.puntosventas.modalpreparado')
								@endforeach
							</tbody>
						</table><br>
					</div>	
				</div>	
			</div>
		</div>
	</section>

@endsection