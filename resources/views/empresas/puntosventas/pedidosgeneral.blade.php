@extends('layouts.empresas')
@section('contenido')
<script>

	$(document).ready(function(){   
       
      	setTimeout(refrescar, 60000);

     });

	var href = $('#btnPrint').attr('href');
	
	$("#btnPrint").printPage({
		
		  url: href,
		  attr: "href",
		  messageBox:false,
		  
	})
 
   function refrescar(){
   	window.location.href = '/listos';
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
          			<div class="box-header with-border" style="background-color:blue;">
                    	<font color="white"><center><strong>SEGUIMIENTO PEDIDOS</strong></center></font>
                    	 <div class="box-tools pull-right">
				    	<a href="/mesas"><button  class="btn btn-sm btn-success btn-sm"> MESAS</button></a> 
				    	
				    </div>
                	</div>
	            	<div class="box-body">
							<table id="tblpedidos"  class="table table-bordered table-hover">
							<thead>
								<tr>
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
								 				<li>{{$det->pronom}} @if(!empty($det->detalle))| {{$det->detalle}} @endif</li>
								 			@endif
								 		@endforeach

								 	</td>
								 	<td>
								 		
										<a href="" data-target="#modal-entregar-{{$ped->ped_id}}" data-toggle="modal"><img title="ENTREGADO" width="35px" height="35px" src="/icon/entregado.png"></a>
										<!--<a href="" data-target="#modal-eliminar-{{$ped->ped_id}}" title="ANULAR PEDIDO" data-toggle="modal"><img width="35px" height="30px" src="/icon/anular.png"></a>-->
								 	</td>
								</tr>
							
								@include('empresas.puntosventas.modalpreparado')
								@include('empresas.puntosventas.modalentregar')
								@endforeach
							</tbody>
						</table><br>
					</div>	
				</div>	
			</div>
		</div>
	</section>

@endsection