

  {!!Form::open(array('url'=>'/imprimirreporte','autocomplete'=>'off','method'=>'POST','id'=>'formfact','role'=>'form','files'=>'true'))!!}
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
	<div class="col-lg-2">
		<div class="form-group">
			 <label class="control-label" for="fecin">Desde </label>
			
			{!!Form::date('fecin',Carbon::now()->startOfMonth()->format('Y-m-d'),['class'=>'form-control input-sm','id'=>'fecin']);!!}
		</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group">
		 	<label class="control-label" for="fecfin">Hasta </label>
			{!!Form::date('fecfin',Carbon::now()->endOfMonth()->format('Y-m-d'),['class'=>'form-control input-sm','id'=>'fecfin']);!!}
		</div>
	</div>

    <div class="col-lg-2">
		<div class="form-group">
			<label class="control-label">Tipo Comprobante</label>
			{!! Form::select('docomp',['1'=>'VENTAS','2'=>'Ranking Productos','3'=>'Stock Productos'],null,['class'=>'docomp form-control input-sm','id'=>'docomp']); !!}
		
		</div>
	</div>

</div>
<div class="row">
	<div class="col-lg-2">
		<div class="btn-group" >
				<button type="submit" class=" btn btn-primary btn-sm">Imprimir</button>
		</div>
	</div>
</div>



{{Form::close()}}
