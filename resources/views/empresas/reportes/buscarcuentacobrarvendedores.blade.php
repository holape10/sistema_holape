
{!! Form::open(array('url'=>'/reportecobranzavendedor','method'=>'POST','autocomplete'=>'off','role'=>'buscar'))!!}

<style>
input[type=date]::-webkit-inner-spin-button, 
input[type=date]::-webkit-clear-button,
input[type=date]::-webkit-outer-spin-button { 
	-webkit-appearance: none; 
	margin: 0; 
}

</style>
<div class="row">
	<div hidden="hidden" class="col-lg-2">
		<div class="form-group form-group-sm">
			<label class="control-label" for="fecin">Tipo Fecha</label>
			<select class="form-control" name="tipfec">
				@if($tipo =='1')
					<option value="0">Fecha Vencimiento</option>
					<option selected="selected" value="1">Fecha Emisi&oacute;n</option>
				@else
					<option selected="selected" value="0">Fecha Vencimiento</option>
				    <option value="1">Fecha Emisi&oacute;n</option>
				@endif
				
			</select>
		</div>
	</div>
	<div hidden="hidden" class="col-lg-2">
		<div class="form-group form-group-sm">
			<label class="control-label" for="fecin">Desde </label>
			
			{!!Form::date('fecin',Carbon::now()->startOfMonth()->format('Y-m-d'),['class'=>'form-control input-sm','id'=>'fecin']);!!}
		</div>
	</div>
	<div hidden="hidden" class="col-lg-2">
		<div class="form-group form-group-sm">
			<label class="control-label" for="fecfin">Hasta </label>
			{!!Form::date('fecfin',Carbon::now()->endOfMonth()->format('Y-m-d'),['class'=>'form-control input-sm','id'=>'fecfin']);!!}
		</div>
	</div>


	<div class="col-lg-6">
		<div class="form-group form-group-sm">
			<label class="control-label">VENDEDORES</label>
			<select name="vendedor" class="form-control selectpicker" data-show-subtext="true" data-live-search="true">
				
				@foreach($vendedores as $vendedor)
					<option value="{{$vendedor->IdUsuario}}">{{$vendedor->name}} {{$vendedor->apeusu}}</option>
				@endforeach
			</select>
		</div>
	</div>
	

	
	


</div>
<div class="row">
	
	<div class="col-lg-12">
		<div class="btn-toolbar" role="toolbar" aria-label="...">
		<div class="btn-group">

				<button type="submit" class=" btn btn-primary btn-sm">GENERAR REPORTE</button>
		
		
		</div>

		<!--<div class="btn-group">

				<button type="button" id="btnExport"  class="btn btn-success btn-sm">EXPORTAR EXCEL</button>
		
		
		</div>

		<div class="btn-group">

				 <button type="button" id="btnExportar"  class="btn btn-success btn-sm">EXPORTAR PDF</button>
		
		
		</div>-->

		 	

		   


		
			
	</div>
	</div>
</div>


{{Form::close()}}
