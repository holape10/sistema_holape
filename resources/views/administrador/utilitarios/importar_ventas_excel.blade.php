@extends('layouts.empresas')
@section('contenido')


<section class="content">
	
{!!Form::open(array('url'=>'registrarventasexcel','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
   {{Form::token()}} 
<div class="row">
    <div class="col-xs-12">
    	<div class="box">
    		<div class="box-header" style="background-color:blue;">
          				<font color="white"><center><strong>IMPORTAR VENTAS</strong></center></font>
          				
          			</div>
	       			<div class="box-body">
					     
					<div class="row">
						<div class="col-lg-2">
							<div class="form-group form-group-sm">
								 <label class="control-label" for="fecin">Fecha </label>
							 <input type="date" name="fecha"  class="form-control">
							</div>
						</div>
						<div class="col-lg-4">
							<div class="form-group form-group-sm">
								<label>SUBIR ARCHIVO EXCEL</label>
								<input class="form-control" type="file" name="archivo" >
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-4">	
							<button type="submit" class="btn btn-primary">IMPORTAR</button>
							<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>

						</div>
					</div>

					</div>
	
	</div>
</div>
</div>
	{{Form::Close()}}
</section>
@endsection
