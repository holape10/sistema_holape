

{!! Form::open(['route'=>'Reportes.ReporteComprobantes'])!!}

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

    <div class="col-lg-2">
		<div class="form-group form-group-sm">
			<label class="control-label">Tipo Comprobante</label>
			{!! Form::select('docomp',['4'=>'UTILIDAD'],null,['class'=>'docomp form-control input-sm','id'=>'docomp']); !!}
			<!--<select name="docomp" id="docomp" class="docomp form-control input-sm">
			</select>-->
		</div>
	</div>

</div>
<div class="row">
	<div class="col-lg-2">
		<div class="btn-group" >
				<button type="submit" class=" btn btn-primary btn-sm">BUSCAR</button>
	
		</div>
		
	
		
	</div>
</div>

<input type="hidden" readonly class="form-control" name="searchIdEmp" placeholder="Buscar..." value="{{Auth::user()->IdEmpresa}}">

{{Form::close()}}
