<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-concar">
	
		<div class="modal-dialog">
			 {!!Form::open(array('url'=>'/','autocomplete'=>'off','method'=>'POST','name'=>'formConcar','id'=>'formConcar','role'=>'form','files'=>'true'))!!}
  		{{Form::token()}}
			<div class="modal-content">
				<div class="modal-header" style="background:blue;">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					<font color="white"><center><strong>REGISTRO Y ASIENTO DE VENTAS A CONCAR</strong></center></font>
				</div>
				<div class="modal-body">
					 
					<div class="row">
							<div class="col-lg-6">
								<div class="form-group form-group-sm">
									 <label class="control-label" for="fecin">Desde </label>
									<input type="date" name="fecin" id="fecin" class="form-control" value="{{now()->modify('first day of this month')->format('Y-m-d')}}">
							
								</div>
							</div>
							<div class="col-lg-6">
								<div class="form-group form-group-sm">
								 	<label class="control-label" for="fecfin">Hasta </label>
								 	<input type="date" name="fecfin" id="fecfin" class="form-control" value="{{now()->modify('last day of this month')->format('Y-m-d')}}">
								
								</div>
							</div>
					</div>
					

					

				</div>
				<div class="modal-footer" id="div_ven_con">
					<button type="button" id="btnConCar" class="btn btn-primary">Confirmar</button>
					<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
					
				</div>
			</div>
			{!!Form::close()!!}
		</div>

</div>

