<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-desunir-mesas">

   {!!Form::open(array('url'=>'/desunirmesas','autocomplete'=>'off','method'=>'POST','name'=>'frmDesunirMesas','id'=>'frmUnirMesas','role'=>'form','files'=>'true'))!!}
    {{Form::token()}}
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					<h4 class="modal-title">DES UNIR MESAS</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-lg-4">
							<div class="form-group form-group-sm">
								<label>Mesa Actual</label>
								<input type="text" class="form-control input input-sm" readonly="readonly" name="mes_act_desunir" id="mes_act_desunir">
								<input type="hidden" readonly="readonly" name="mes_id_act_desunir" id="mes_id_act_desunir">
								<input type="hidden" readonly="readonly" name="ped_id_act_desunir" id="ped_id_act_desunir">
							</div>
						</div>

						<div class="col-lg-4" id="mesas_desocupadas_desunir">
							
						</div>	
					</div>
					
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary">Unir Mesa</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
				</div>
			</div>
		</div>
	
{!!Form::close()!!}
</div>
