@extends('layouts.empresas')
@section('contenido')

<script type="text/javascript">
       $(document).ready(function()
    {

        $("#btnGenPdf").click(function() {

          
          var accion = $(this).attr('dir');

          $('#frmReporte').attr('action', accion);
          	$('#frmReporte').attr('target', '_blank');
          $('#frmReporte').submit();
        });

             $("#btnGenExcel").click(function() {

          
          var accion = $(this).attr('dir');

          $('#frmReporte').attr('action', accion);
          $('#frmReporte').submit();
        });
        

        $("#btnSubmit").click(function() {

          
          var accion = $(this).attr('dir');

          $('#frmReporte').attr('action', accion);
          $('#frmReporte').submit();
        });
          

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
	<div class="row">
        <div class="col-xs-12">
        	<div class="box">
        		<div class="box-header box-success" style="background-color:#3c8dbc;">
        			<font color="white" style="font-size:10pt;font-weight:bold;"><center><strong>CONSULTAR AJUSTES</strong></center></font>
        		</div>
	           	<div class="box-body">
	           		{!!Form::open(array('url'=>'/reporteajustes','autocomplete'=>'off','method'=>'POST','id'=>'frmReporte','role'=>'form','files'=>'true'))!!}
    {{Form::token()}}
<style>
	input[type=date]::-webkit-inner-spin-button, 
	input[type=date]::-webkit-clear-button,
    input[type=date]::-webkit-outer-spin-button { 
      -webkit-appearance: none; 
      margin: 0; 
    }

</style>

<div class="row">
		 <div class="col-lg-2" >
		<div class="form-group form-group-sm">
			<label class="control-label">Sucursal</label>
			<select class="form-control" name="sucursal" id="sucursal">
				
				@foreach($negocios as $negocio)
				
				
					<option value="{{$negocio->id_empresa_negocio}}">{{$negocio->IdEmpresa}} - {{$negocio->tipo_negocio}}</option>
			
				
				@endforeach
			</select>
			</div>
	</div>
	    <div class="col-lg-2" id="divalmacen">
                            <div class="form-group form-group-sm">
                              <label>Almacenes</label>
                              <select class="form-control" name="almacen" id="almacen">
                               <option value="Todos">Todos</option>
                                @foreach($almacenes as $alma)
                                	
                                   	 <option value="{{$alma->id_almacen}}">{{$alma->descripcion}}</option>
                                
                                @endforeach
                              </select>
                            </div>
                          </div>
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
			 <label class="control-label" for="fecin">Desde </label>
			 <input type="text" name="fecin" value="{{Carbon::now()->startOfYear()->format('Y-m-d')}}" class="form-control">
			
		</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
		 	<label class="control-label" for="fecfin">Hasta </label>
		 	<input type="text" name="fecfin" value="{{Carbon::now()->endOfMonth()->format('Y-m-d')}}" class="form-control">
		</div>
	</div>




          <div class="col-lg-2" >
		<div class="form-group form-group-sm">
			<label class="control-label">Productos</label>
			<select class="form-control selectpicker input-sm" data-show-subtext="true" data-live-search="true" name="IdProducto" id="IdProducto">
				<option value="Todos">Todos</option>
				@foreach($productos as $pro)
				
					
					<option value="{{$pro->IdProducto}}">{{$pro->procod}} - {{$pro->pronom}} </option>
				
				
				@endforeach
			</select>
			</div>
	</div>

</div>
<div class="row">
	<div class="col-lg-6">
		<div class="btn-group" >
				<button type="button" id='btnSubmit' dir="/reporteajustes"  class=" btn btn-primary btn-sm">BUSCAR</button>
	
		</div>
		<!--	<div class="btn-group">
			
		
				<button type="button" id="btnGenExcel" dir="/generarkardexexcel" class="btn btn-primary btn-sm">Exportar Excel</button>
		</div>
		
		<div class="btn-group">
			
		
				<button type="button" id="btnGenPdf" dir="/generarkardexpdf" class="btn btn-primary btn-sm">GENERAR KARDEX</button>	
			</div>
	
		-->
	</div>
</div>



{{Form::close()}}

	           	</div>
	         </div>
	    </div>
	</div> 
	
	<div class="row">
		<div class="col-xs-12">
			<div class="box">
	            	<div class="box-body">
						<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm">
							<thead style="background:#3c8dbc;color:white;">
								  <th style="font-size:10pt;font-weight: bold;text-align:center;vertical-align:middle;">FECHA</th>
									<th style="font-size:10pt;font-weight: bold;text-align:center;vertical-align:middle;">CODIGO</th>
									<th style="font-size:10pt;font-weight: bold;text-align:center;vertical-align:middle;">PRODUCTO</th>
									<th style="font-size:10pt;font-weight: bold;text-align:center;vertical-align:middle;">UNIDAD_MEDIDA</th>
									<th style="font-size:10pt;font-weight: bold;text-align:center;vertical-align:middle;">MOVIMIENTO</th>
									<th style="font-size:10pt;font-weight: bold;text-align:center;vertical-align:middle;">CANTIDAD</th>
									<th style="font-size:10pt;font-weight: bold;text-align:center;vertical-align:middle;">ELIMINAR</th>

									
							</thead>
							<tbody>
						
							@if(!empty($movimientos))
							@foreach($movimientos as $mov)

								<tr>
									<td style="text-align:center;vertical-align:middle;"><button class="btn btn-sm {{$mov->cat_mov_col}} btn-block">{{$mov->fecha_mov}}</button></td></td>
									<td>{{$mov->procod}}</td>
									<td>{{$mov->pronom}}</td>
									<td>{{$mov->umecod}}</td>
									<td style="text-align:center;vertical-align:middle;"><button class="btn btn-sm {{$mov->cat_mov_col}} btn-block">{{$mov->cat_mov_des}}</button></td>
									<td class="text-number">{{number_format($mov->cantidad,'2','.','')}}</td>
										<td ><a href="/eliminar/ajuste/"><button></button></a></td>
								</tr>
							@endforeach
							@endif
							

							</tbody>
						</table><br>
					</div>	
				
				</div>
		</div>
	</div>
</section>



@endsection