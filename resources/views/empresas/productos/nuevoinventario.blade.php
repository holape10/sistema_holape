@extends('layouts.empresas')
@section('contenido')

<script>
$(document).ready(function()
{       

	  
        

     $("#sucursal").change(function() {
         
                var sucursal = $("#sucursal").val();
                $("#divalmacen").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
                $.ajax({
                  type: "GET",
                  dataType: 'json',
                  url: "/buscaralmacen/"+sucursal,

                }).done(function(respuesta){
                $("#divalmacen").html(respuesta.vista);
               
                });

      });


 });

function buscarinventario(id){

	 var suc = $("#sucursal").val();
	 var almacen = $("#almacen").val();
	 window.location.href = "/inventarios/"+suc+"/"+almacen+"/"+id;
}

function crearinventario(id){

	 var suc = $("#sucursal").val();
	 var almacen = $("#almacen").val();
	 window.location.href = "/inventarios/"+suc+"/"+almacen+"/"+id;
}


</script>
<section class="content">

<div class="row">
    <div class="col-xs-12">
    	<div class="box">
    		
    		<div class="box-header" style="background-color:blue;">
          		<font size="2" color="white"><strong><center>INVENTARIOS</center></strong></font>
          	</div>
        
	       	<div class="box-body">
			<table  id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm">
				<thead>
					<th colspan="10"><center>INVENTARIO DE PRODUCTOS {{Carbon::now()->format('d-m-Y')}} @if(!empty($datos)) - {{$datos->tipo_negocio}} @endif @if(!empty($datosalm)) - <br>{{$datosalm->descripcion}} @endif</center></th>
				</thead>
				<thead>
					<th>CODIGO</th>
					<th>PRODUCTO</th>
					<th>UM</th>
					<th>CANTIDAD</th>
					<th>COSTO</th>					

				</thead>

				@if(!empty($productos))
				@foreach ($productos as $pro)

		
					<tr>
						<td>{{$pro->procod}} <input type="hidden" step="any" name="id[]" class="form-control input-sm" value="{{$pro->IdProducto}}"></td>
						<td>{{$pro->pronom}}</td>
						<td>{{$pro->umenom}}</td>
						<td><input type="number" step="any" name="stock[]" class="form-control input-sm"></td>
						<td><input type="number" step="any" name="costo[]" class="form-control input-sm"></td>
						<!--<td>{{number_format($pro->costo*$pro->stock,2,'.','')}}</td>
						<td><a href="" data-target="#modal-ajuste-{{$pro->IdProducto}}" data-toggle="modal"><button class="btn btn-primary">Ajustes</button></a></td>-->
					</tr>
				@include('empresas.productos.modalajuste')
				@endforeach
				@endif
			</table>
		</div>

		@if(!empty($productos))
		{{$productos->render()}}
		@endif
	</div>
</div>
</div>
</section>
@endsection
