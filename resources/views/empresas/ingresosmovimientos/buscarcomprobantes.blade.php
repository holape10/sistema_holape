

{!! Form::model(Request::all(),['Route'=>'/movingresos','method'=>'GET','autocomplete'=>'off'])!!}

<!--<div class="col-lg-3">
	<div class="form-group">
		<h4><i class='glyphicon glyphicon-search'></i> CONSULTAR COMPROBANTES</h4>
	</div>
</div>-->
<style>
	input[type=date]::-webkit-inner-spin-button, 
	input[type=date]::-webkit-clear-button,
    input[type=date]::-webkit-outer-spin-button { 
      -webkit-appearance: none; 
      margin: 0; 
    }

</style>
<div class="row">
	<div class="col-lg-2">
		<div class="form-group">
			 <label class="control-label" for="fecin">Desde </label>
			
			{!!Form::date('fecin',Carbon::now()->startOfMonth()->format('Y-m-d'),['class'=>'form-control input-sm','id'=>'fecin']);!!}
		</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group">
		 	<label class="control-label" for="fecfin">Hasta </label>
		<!--	<input for="fecfin" name="fecfin" id="fecfin" class="form-control input-sm" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control" type="date">-->
			
			{!!Form::date('fecfin',Carbon::now()->endOfMonth()->format('Y-m-d'),['class'=>'form-control input-sm','id'=>'fecfin']);!!}
		</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group">
			<label  class="control-label">Razon Social</label>
			<!--<input type="text" class="form-control input-sm" name="searchText" id="searchText" placeholder="Razón Social o Ruc">-->
			{!!Form::text('searchText',null,['class'=>'form-control input-sm','id'=>'searchText','placeholder'=>'Razón Social o Ruc']);!!}
		</div>
	</div>
    <div class="col-lg-2">
		<div class="form-group">
			<label class="control-label">Tipo Comprobante</label>
			{!! Form::select('docomp',['0'=>'Todos','1'=>'FACTURA','2'=>'BOLETA DE VENTA','3'=>'NOTA DE CRÉDITO','4'=>'NOTA DE DÉBITO'],null,['class'=>'docomp form-control input-sm','id'=>'docomp']); !!}
			<!--<select name="docomp" id="docomp" class="docomp form-control input-sm">
			</select>-->
		</div>
	</div>
	
	<!--<div class="col-lg-1">
		<div class="form-group">
			<label class="control-label">Numero</label>
			{!!Form::text('numdoc',null,['class'=>'form-control input-sm','id'=>'numdoc','placeholder'=>'Número']);!!}
		</div>
	</div>-->
	<div class="col-lg-2">
		<div class="form-group">
			<label class="control-label">Estado</label>
			{!! Form::select('tiper',['1'=>'Todos','0'=>'Enviado y Aceptado','2'=>'En Proceso'],null,['class'=>'tiper form-control input-sm','id'=>'tiper']); !!}
		</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group">
			<label class="control-label">Comprobante</label>
			{!!Form::text('comp','',['class'=>'form-control input-sm','id'=>'comp','placeholder'=>'Serie-Número']);!!}
		</div>
	</div>	
</div>
<div class="row">
	<div class="col-lg-12">
		<div class="btn-toolbar" role="toolbar" aria-label="...">
		<div class="btn-group">

				<button type="submit" class=" btn btn-primary btn-sm">Buscar</button>
		
		
		</div>
		<div class="btn-group" >
	
				<a href="/movgastos/create"><button type="button"  class=" btn btn-success btn-sm"><span class="glyphicon glyphicon-plus"></span> Nuevo Registro</button></a>
		</div>
	
	</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group">
		<span class="input-group-btn">
				
		</span>
		</div>
	</div>
	
</div>
<input type="hidden" readonly class="form-control" name="searchIdEmp" placeholder="Buscar..." value="{{Auth::user()->IdEmpresa}}">

{{Form::close()}}