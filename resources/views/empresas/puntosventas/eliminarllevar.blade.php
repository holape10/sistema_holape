<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-delete-{{$pedido->ped_id}}">
	{{Form::open(array('action'=>array('POSRestaurantController@destroy',$pedido->ped_id),'method'=>'delete'))}}
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					<h4 class="modal-title">Eliminar El Pedido</h4>
				</div>
				<div class="modal-body">
					<div class="row">
					<div class="col-lg-12">
                    <div class="panel panel-default">
                        <div  class="panel-heading">
                            <strong>Motivo - Autorizado Por:</strong>
                        </div>
                        <div  class="panel-body">
                            <div class="form-group">
                                <textarea class="form-control" id="obser" name="obser" rows="4"></textarea>
                             </div>
                        </div>
                    </div>
                  
                </div>
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