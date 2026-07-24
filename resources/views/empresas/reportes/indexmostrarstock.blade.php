@extends('layouts.empresas')
@section('contenido')
<script type="text/javascript">
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
     </script>
<section class="content">
	<div class="container">
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                @if(session()->has('info'))
                   <div class="alert alert-danger">
                      <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                      <strong>Alerta!</strong> {{session('info')}}
                    </div>
                @endif


                @if(session()->has('success'))
                    <div class="alert alert-success">
                      <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                      <strong>Información!</strong> {{session('success') }}
                      
                    </div>
                @endif
             
            </div>
        </div>
    </div>
		<div class="row">
        <div class="col-xs-12">
        	<div class="box">
        		<div class="box-header box-success" style="background-color:#00a65a;">
        			<font color="white" size="4"><center><strong>REPORTES DE PRODUCTOS</strong></center></font>
        		</div>
	           	<div class="box-body">
	           		@include('empresas.reportes.buscar')
	           	</div>
	         </div>
	    </div>
	</div> 
<div class="row">
    <div class="col-xs-12">
    	<div class="box">
	       	<div class="box-body">
			<table  id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm">
				<thead>
					<th colspan="9"><center>STOCK DE PRODUCTOS {{Carbon::now()->format('d-m-Y')}}</center></th>
				</thead>
				<thead>
					<th>CODIGO</th>
					<th>PRODUCTO</th>
					<th>MARCA</th>
				
					<th>UM</th>
					<th>STOCK ACTUAL</th>
					<!--<th>PRECIO VENTA</th>-->
					<th>COSTO</th>					
					<th>VALOR INVENTARIO</th>
				
			
				</thead>
				@foreach ($productos as $pro)
				<tr>
					<td>{{$pro->procod}}</td>
					<td>{{$pro->pronom}}</td>
					<td>{{$pro->marca}}</td>
					<td>{{$pro->umenom}}</td>
					<td>{{$pro->stock}}</td>
					<!--<td>{{$pro->propun}}</td>-->
					<td>{{$pro->costo}}</td>
					<td>{{number_format($pro->costo*$pro->stock,2,'.','')}}</td>
				
					
				</tr>
				@include('empresas.productos.modalajuste')
				@endforeach
			</table>
		</div>

	</div>
</div>
</div>
</section>
@endsection
