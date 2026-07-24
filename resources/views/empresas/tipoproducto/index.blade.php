@extends('layouts.empresas')
@section('contenido')

<section class="content">
	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
          			<div class="box-header with-border" style="background-color:blue;">
                        <center><font color="white"><strong>TIPOS DE PRODUCTOS</strong></font></center>
                        <div class="box-tools pull-right">
                			<a href="/tipoproducto/create"><button  class="btn btn-success btn-sm"> Nuevo</button></a>
              			</div>
                	</div>
	            	<div class="box-body">
	            	 	<div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
							@include('empresas.tipoproducto.search')
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
                    <th>TIPO DE PRODUCTO</th>
                    <th>CTA. 70</th>
        			<th>CTA. 12</th>
					<th>OPCIONES</th>
				</thead>
				@foreach ($tipos as $tp)
				<tr>
					<td>{{$tp->tip_pro_nom}}</td>
					<td>{{$tp->cta_contable_70}}</td>
        			<td>{{$tp->cta_contable_12}}</td>
					<td>
						<a href="{{URL::action('TipoProductoController@edit',$tp->tip_pro_id)}}"><button class="btn btn-info">Editar</button></a>
                        <a href="" data-target="#modal-delete-{{$tp->tip_pro_id}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a>
					</td>
				</tr>
				@include('empresas.tipoproducto.modal')
				@endforeach
			</table>
			</div>
            {{$tipos->render()}}
		</div>
	</div>
</div>
</section>
@endsection