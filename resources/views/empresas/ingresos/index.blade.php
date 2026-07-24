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
          				<font color="white"><center><strong>REGISTRO DE INGRESOS</strong></center></font>
          			</div>
	            	<div class="box-body">
	            		@include('empresas.ingresos.buscarcomprobantes')
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
									<th>Registrado por:</th>
									<th>Fec. Movimiento</th>
									<th hidden="hidden">Documento</th>
									<th hidden="hidden">Serie</th>
									<th hidden="hidden">N°</th>
									<th hidden="hidden">RUC /DNI</th>
									<th hidden="hidden"  style="width:120px;">Nombre</th>
									<th>Observaciones</th>
									<th>Total</th>
									<th>Imprimir</th>
									<th>Estado</th>
									<th>OPCIONES</th>
								</tr>
							</thead>
							<tbody>
								@foreach($gastos as $comp)
								<tr>
									<td>{{$comp->name}} {{$comp->apeusu}}</td>
								
								 	<td>{{$comp->gast_fec}}</td>
								 	
								 	<td hidden="hidden">{{$comp->tdodes}}</td>
								 	<td hidden="hidden">{{$comp->gast_doc_ser}}</td>
									<td hidden="hidden">{{$comp->gast_doc_num}}</td>
									<td hidden="hidden">{{$comp->prov_ruc}}</td>
									<td hidden="hidden">{{$comp->prov_raz}}</td>
									<td>{{$comp->gast_obs}}</td>
									<td>{{number_format($comp->total_gast,'2','.',',')}}</td>
									<td><a id="btnPrint" href="/imprimirgasto/{{$comp->gast_cab_id}}" target="_blank"><center><i class="fa fa-file-pdf-o fa-lg"></i></center></a></td>
									<td>{{$comp->est_gasto}}</td>
									<td>
										<a href="/detallegastos/{{$comp->gast_cab_id}}"><button class="btn btn-success">Detalle</button></a>
									
										@if(Auth::user()->hasRole('admin'))
												<a href="{{URL::action('IngresosController@edit',$comp->gast_cab_id)}}"><button class="btn btn-info">Editar</button></a>
											@if($comp->est_gasto =='Eliminado')
												<button class="btn btn-danger" disabled="disabled">Eliminar</button><br>
											@else
											 	<a href="" data-target="#modal-delete-{{$comp->gast_cab_id}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a><br>
											@endif

										@endif
									
				                        

									</td>
								</tr>
								@include('empresas.gastos.modal')
								@endforeach
							</tbody>
						</table><br>
					</div>	
				</div>	
			</div>
		</div>
	</section>

@endsection