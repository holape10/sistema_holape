
{!! Form::open(array('url'=>'/generararqueodiarioresumen','method'=>'POST','autocomplete'=>'off','role'=>'buscar','id'=>'frmReporte'))!!}

<style>
input[type=date]::-webkit-inner-spin-button, 
input[type=date]::-webkit-clear-button,
input[type=date]::-webkit-outer-spin-button { 
	-webkit-appearance: none; 
	margin: 0; 
}

</style>
<div class="row">
	
	<div class="col-lg-5">
		<div class="form-group form-group-sm">
			<label class="control-label" for="fecin">Fecha</label>
			
			{!!Form::date('fecin',Carbon::now()->format('Y-m-d'),['class'=>'form-control input-sm','id'=>'fecin']);!!}
		</div>
	</div>



</div>
<div class="row">
	
	<div class="col-lg-12">
		<div class="btn-toolbar" role="toolbar" aria-label="...">
		<div class="btn-group">

				<button type="submit" class=" btn btn-primary btn-sm">GENERAR ARQUEO DIARIO</button>
		
		
		</div>
		<div class="btn-group">

				<button type="button" id="btnExportar_adr" dir="/arqueodiarioresumenexcel"  class=" btn btn-primary btn-sm">EXCEL ARQUEO DIARIO</button>
		
		
		</div>

		
			
	</div>
	</div>
</div>


{{Form::close()}}
