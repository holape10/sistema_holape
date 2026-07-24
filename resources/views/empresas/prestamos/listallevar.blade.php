@extends('layouts.empresas')
@section('contenido')
<script>

	function imprimirpedido($pedido,$tipoimp){

		 $("#btnPrint").printPage({

          url: "/imprimirpedidollevar/"+pedido+"/"+tipoimp,
          attr: "href",
          messageBox:false
          
         
        })

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
          			 <div class="box-header with-border">
				        	<div class="btn-group">
			                   <div class="form-group form-group-sm"><a href="/pedidollevar"><button  class="btn btn-sm btn-success" ><strong>REGISTRAR PEDIDO - LLEVAR</strong></button></a></div>
			                </div>
			           		<div class="btn-group">
			                   <div class="form-group form-group-sm"><a href="/mesas"><button  class="btn btn-lg btn-warning" ><strong>MESAS</strong></button></a></div>
			                </div>
				     </div>
	            	<div class="box-body"> 
						<table id=""  class="table table-bordered table-hover">
							<thead>
								<tr>
									<th>Fecha Pedido</th>
									<th>Cliente</th>
									<th>Total</th>
									<th>Estado</th>
									<th>Opciones</th>
								</tr>
							</thead>
							<tbody>
								@foreach($pedidos as $pedido)
								<tr>
								 	<td>{{$pedido->fecha}}</td>
									<td>{{$pedido->cliente}}</td>
									<td>{{$pedido->total}}</td>
									<td>{{$pedido->ped_est}}</td>
									<td>
										@if($pedido->ped_est =='Aperturado')
											<a href="/editarpedidollevar/{{$pedido->ped_id}}"><button   class="btn btn-info">Editar Pedido</button></a>
										@else
										<a target="_blank" href="}"><button disabled="disabled"   class="btn btn-info">Editar Pedido</button></a>
										@endif
								
									
										@if($pedido->ped_est =='Entregado')
										 @if(Auth::User()->hasRole('supervisor') || Auth::User()->hasRole('admin') || Auth::User()->hasRole('caja'))
											<a ><button disabled="disabled" class="btn btn-primary">Cobrar</button></a>
				                        @endif
				                        		<a  href="/imprimirpedidollevar/{{$pedido->ped_id}}/0" target="blank" ><button  class="btn btn-success">Comanda</button></a>
										@elseif($pedido->ped_est =='Eliminado')
											 @if(Auth::User()->hasRole('supervisor') || Auth::User()->hasRole('admin') || Auth::User()->hasRole('caja'))
											<a ><button disabled="disabled" class="btn btn-primary">Cobrar</button></a>
				                        	@endif
				                        		<a  href="/imprimirpedidollevar/{{$pedido->ped_id}}/0" target="blank" ><button  class="btn btn-success">Comanda</button></a>
				                        @else
				                        	 @if(Auth::User()->hasRole('supervisor') || Auth::User()->hasRole('admin') || Auth::User()->hasRole('caja'))
				                        	<a href="/cobrarllevar/{{$pedido->ped_id}}"><button class="btn btn-primary">Cobrar</button></a>
				                        	@endif
				                        	<a  href="/imprimirpedidollevar/{{$pedido->ped_id}}/0" target="blank" ><button  class="btn btn-success">Comanda</button></a>
										@endif	

									</td>	
								</tr>
								@include('empresas.puntosventas.eliminarllevar')
								@endforeach
							</tbody>
						</table><br>
						{{$pedidos->render()}}
					</div>	
					
				</div>	
			</div>
		</div>
	
	</section>

@endsection