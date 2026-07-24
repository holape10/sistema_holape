@extends('layouts.empresas')
@section('contenido')

<section class="content">
	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
	            	 <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
				<h4><i class='glyphicon glyphicon-search'></i>  TURNOS </h4>
				@if(Auth::user()->hasrole('admin'))
					@include('empresas.turnos.search')
				@endif
			</div>
				     	</div>
	            </div>
	        </div>
	</div>

<div class="row">
    <div class="col-xs-12">
    	<div class="box">
	       	<div class="box-body">
			<table class="table table-striped table-bordered table-condensed table-hover">
				<thead>
					
					<th colspan="5"><STRONG><center>Cajero : {{$datosusuario->name}} {{$datosusuario->apeusu}}</center></STRONG></th>
					
				</thead>
				<thead>
				
					<th>Fecha - Hora Apertura</th>
					<th>Fecha - Hora Cierre</th>
					<th>Opciones</th>
				</thead>
				@foreach ($turnos as $turno)
				<tr>
				
					<td>{{$turno->apertura}}</td>
					<td>{{$turno->cierre}}</td>
					
					<td>
						@if($turno->estado=='Cerrado')
						
						<a id="btnPrint" href="/imprimircaja/{{$turno->id_turno}}" target="_blank"><button class="btn btn-info">Imprimir</button></a>
								
						@else
						<a href=""><button disabled="disabled" class="btn btn-info">Imprimir</button></a>
                     	@endif
					</td>
				</tr>
	
				@endforeach
			</table>
		</div>
		{{$turnos->render()}}
	</div>
</div>
</div>
</section>
@endsection
