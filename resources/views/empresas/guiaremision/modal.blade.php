<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-delete-{{$comp->IdCpe_nota}}">
	{{Form::open(array('action'=>array('ComprobantesController@destroy',$comp->IdCpe_nota),'method'=>'delete'))}}
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					<h4 class="modal-title">MODIFICAR TOTALES</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label>Pagado</label>
						<input type="text" readonly="readonly" class="form-control"  value="{{$comp->efectivo}}" id="efectivo1" name="efectivo1" placeholder="Efectivo"> 
					</div>
					<!--<div class="form-group">
						<label>Visa</label>
						<input type="text" class="form-control"  value="{{$comp->visa}}" id="visa" name="visa"  placeholder="Visa"> 
					</div>-->
					<div class="form-group">
						<label>Cobrar</label>
						<input type="text" class="form-control"  value="{{$comp->mastercard}}" id="mastercard" name="mastercard" placeholder="Mastercard">
					</div>
				        
				         
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
					<button type="submit" class="btn btn-primary">Confirmar</button>
				</div>
			</div>
		</div>
	{{Form::Close()}}
</div>
