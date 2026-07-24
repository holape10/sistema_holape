<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-delete-{{$series->IdSerie}}-{{$series->Estado}}">
	{{Form::open(array('action'=>array('SeriesController@destroy',$series->IdSerie,$series->Estado),'method'=>'delete'))}}
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					@if($series->Estado == '1')
					<h4 class="modal-title">Desactivar La Serie</h4>
					@else
					<h4 class="modal-title">Activar La Serie</h4>
					@endif
				</div>
				<div class="modal-body">
					@if($series->Estado == '1')
					<p>Confirme si desea desactivar la serie</p>
					@else
					<p>Confirme si desea activar la serie</p>
					@endif
					
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary">Confirmar</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
				</div>
			</div>
		</div>
	{{Form::Close()}}
</div>