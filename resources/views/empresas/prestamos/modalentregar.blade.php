<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-entregar-{{$ped->ped_id}}">
	 {!!Form::open(array('action'=>array('POSRestaurantController@entregarpedido',$ped->ped_id),'autocomplete'=>'off','method'=>'GET','role'=>'form','files'=>'true'))!!}
    {{Form::token()}}
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					<h4 class="modal-title">Entregar Pedido</h4>
				</div>
				<div class="modal-body">
					<p>Confirme si desea entregar el pedido.</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
					<button type="submit" class="btn btn-primary">Confirmar</button>
				</div>
			</div>
		</div>
	{{Form::Close()}}
</div>
