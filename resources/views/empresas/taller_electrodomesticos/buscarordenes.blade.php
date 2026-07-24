

{!! Form::model(Request::all(),['Route'=>'/ordeneselectro','method'=>'GET','autocomplete'=>'off','name'=>'formfact'])!!}

<!--<div class="col-lg-3">
	<div class="form-group form-group-sm">
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
		<div class="form-group form-group-sm">
			 <label class="control-label" for="fecin">Desde </label>
			
			{!!Form::date('fecin',Carbon::now()->startOfMonth()->format('Y-m-d'),['class'=>'form-control input-sm','id'=>'fecin']);!!}
		</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
		 	<label class="control-label" for="fecfin">Hasta </label>
			{!!Form::date('fecfin',Carbon::now()->endOfMonth()->format('Y-m-d'),['class'=>'form-control input-sm','id'=>'fecfin']);!!}
		</div>
	</div>

	<div hidden="hidden" class="col-lg-2">
		<div class="form-group form-group-sm">
			<label class="control-label">Cliente</label>
			{!!Form::text('cliente','',['class'=>'form-control input-sm','id'=>'cliente','placeholder'=>'cliente']);!!}
		</div>
	</div>	

		<div hidden="hidden" class="col-lg-2">
             <label>DOCUMENTO</label>

           <select class="form-control selectpicker" data-show-subtext="true" data-live-search="true" name="documento" id="documento" >
            	<!--<option value="80">COTIZACION</option>-->
            	<option value="70">ORDEN TRABAJO</option>
            	<!--<option value="90">ORDEN PEDIDO</option>-->
            
          </select>
          </div>

<div class="col-lg-3">
		<div class="form-group form-group-sm">
		 <label>N° ORDEN</label>
		   <input type="text"  name="num_ord" id="num_ord" value=""  class="form-control">
	</div>
	</div>
	
</div>

<div class="row">
	<div class="col-lg-12">
		<div class="btn-toolbar" role="toolbar" aria-label="...">
		<div class="btn-group">

				<button type="submit" class=" btn btn-primary btn-sm">Buscar</button>
				
		
		
		</div>
	<!--	<div class="btn-group">	
			<button type="button" id="btnreg" class="btn btn-success btn-sm">Crear Cotizaci&oacute;n</button>
	   </div>-->

	     @if(Auth::user()->hasRole('recepcion') || Auth::user()->hasRole('administrador') )

		<div class="btn-group">
			<button type="button" id="btnregot" class="btn btn-success btn-sm">Crear Orden Trabajo</button>
		</div>
		@endif
		<!--<div class="btn-group">
			<button type="button" id="btnregop" class="btn btn-success btn-sm">Crear Orden Pedido</button>
		</div>-->
			
	</div>
	</div>

	
</div>
<input type="hidden" readonly class="form-control" name="searchIdEmp" placeholder="Buscar..." value="{{Auth::user()->IdEmpresa}}">

{{Form::close()}}