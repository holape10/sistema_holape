
@extends('layouts.empresas')
@section('contenido')

	{!!Form::open(array('url'=>'modificarpedidoalbergue','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}} 
			
	<div class="container-fluid">
		<div class="box">
				<div class="box-header" style="background-color:blue;">
					<font color="white"><center><strong>MODIFICAR CANTIDAD DE PEDIDOS</strong></center></font>
				</div>
				<div class="box-body">
		<div class="row">
			  <input type="hidden" name="IdProducto" value="{{$buscar->IdProducto}}">
						<input type="hidden" name="ped_ser_id" value="{{$buscar->ped_ser_id}}">
						<div class="col-lg-2">
							<div class="form-group form-group-sm">
								<label>Movimiento</label>
								<select name="cmb_movimiento" class="form-control">
									<option value="Ingreso" selected="selected">AGREGAR</option>
									<option value="Salida">DISMINUIR</option>
								</select>
							</div>	
						</div>
						
						<div class="col-lg-2">
							<div class="form-group form-group-sm">
								<label>Producto</label>
								<input readonly="readonly" class="form-control" type="text" name="producto" value='{{$buscar->pronom}}'>
							</div>
						</div>
						
								<div class="col-lg-2">
							<div class="form-group form-group-sm">
								<label>Cantidad Actual</label>
								<input class="form-control" type="text" readonly="readonly"  value='{{$buscar->total}}'>
							</div>
				</div>
				
						<div class="col-lg-2">
							<div class="form-group form-group-sm">
								<label>Cantidad</label>
								<input class="form-control" type="text" name="cantidad" value='0'>
							</div>
				</div>
					
		</div>
			<div class="row">
				<DIV class="col-lg-12">
					<button type="submit" class="btn btn-primary">Registrar</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
				</DIV>
				
			</div>  


				</div>	
			</div>
	</div>
						
					
			
	{!!Form::close()!!}
		
	
@endsection
