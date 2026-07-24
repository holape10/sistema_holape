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

  @if(isset($codgast))
                        <input type="hidden" name="documento" id="documento" value="{{$codgast}}">

                    @endif
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
          				<font color="white"><center><strong>REGISTRO PAGOS DE PERSONAL</strong></center></font>
          			</div>
	            	<div class="box-body">
	            		@include('recursoshumanos.pagospersonal.buscarcomprobantes')
	            	</div>
	            </div>
	        </div>
	</div>            
    	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
						<table id=""  class="table table-bordered table-striped table-hover">
							<thead>
								<tr>
									<th>PERSONAL</th>
									<th>FECHA REGISTRO</th>
									<th>FECHA PAGO</th>
									<th>ESTADO DEUDA</th>
									<th>TOTAL</th>
									<th>IMPRIMIR</th>
									<th>ESTADO</th>
									<th>OPCIONES</th>
								</tr>
							</thead>
							<tbody>
								@foreach($gastos as $comp)
								<tr>
									<td>{{$comp->name}} {{$comp->apeusu}}</td>
									<td>{{$comp->gast_fec}}</td>
								 	<td>{{$comp->gast_fec_pag}}</td>
								 	<td> @if($comp->estado_pago=='PENDIENTE') <button class="btn btn-sm btn-danger btn-block">{{$comp->estado_pago}}</button>  @else <button class="btn btn-sm btn-success btn-block">{{$comp->estado_pago}}</button>   @endif</td>
									<td>{{number_format($comp->total_gast,'2','.',',')}}</td>
									<td><a id="btnPrint" href="/imprimirgasto/{{$comp->gast_cab_id}}" target="_blank"><center><i class="fa fa-file-pdf-o fa-lg"></i></center></a></td>
									<td>{{$comp->est_gasto}}</td>
									<td>
										<a href="/detallegastos/{{$comp->gast_cab_id}}"><button class="btn btn-success">Detalle</button></a>
										<!--<a href="{{URL::action('ComprasController@edit',$comp->gast_cab_id)}}"><button class="btn btn-info">Editar</button></a>-->
										@if($comp->est_gasto =='Eliminado')
											<button class="btn btn-danger" disabled="disabled">Eliminar</button><br>
										@else
											 <a href="" data-target="#modal-delete-{{$comp->gast_cab_id}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a><br>
										@endif
				                        

									</td>
								</tr>
								@include('recursoshumanos.pagospersonal.modal')
								@endforeach
							</tbody>
						</table><br>
					</div>	
				</div>	
			</div>
		</div>
	</section>

@endsection