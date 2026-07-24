@extends ('layouts.empresas')
@section ('contenido')
<section class="content">
     {!!Form::open(array('url'=>'/registrarajustarstock','method'=>'POST','autocomplete'=>'off','files'=>'true','id'=>'frmProducto'))!!}
    {{Form::token()}}   
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header with-border" style="background-color:blue;">
                    <center><font color="white"><strong>AJUSTAR STOCK</strong></font></center>
                </div>
                <div class="box-body">

					<div class="row">
					<input type="hidden" name="IdProducto" value="{{$productos->IdProducto}}">
					
				
					<div hidden='hidden' class="col-lg-4">
						<div class="form-group form-group-sm">
						<label>Fecha</label>
						<input class="form-control" type="date" name="fecha" value="{{Carbon::now()->format('Y-m-d')}}">
						</div>
					</div>

					<div class="col-lg-4">
						<div class="form-group form-group-sm">
						<label>Producto</label>
						<input readonly="readonly" class="form-control" type="text" name="producto" value='{{$productos->pronom}}'>
						</div>
					</div>
				
					<div class="col-lg-4">
						<div class="form-group form-group-sm">
						<label>STOCK</label>
						<input class="form-control" readonly="readonly" type="text" name="stock" value='{{$productos->stock}}'>
						</div>
					</div>
			

					<div class="col-lg-4">
						<div class="form-group form-group-sm">
						<label>Cantidad</label>
						<input class="form-control" type="text" name="cantidad" value='0'>
						</div>
					</div>
					<input type="hidden" readonly="readonly" name="suc_id" value="{{$sucursal->id_empresa_negocio}}">
					<input type="hidden" readonly="readonly" name="alm_id" value="{{$almacen->id_almacen}}">
				</div>
				<div class="row">
					<div class="col-lg-12">
						<button type="submit"  class="btn btn-primary">Registrar</button>
					<a href="/stockproductos"><button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button></a>
					
					</div>
						
				</div>
			</div>
		</div>
	</div>
</div>
{!!Form::close()!!}
</section>
		@endsection
		
				
			
			

