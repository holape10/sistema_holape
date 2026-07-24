<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-vehiculos">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					<h4 class="modal-title"><CENTER><strong>ESTACIONAMIENTOS OCUPADOS</strong></CENTER></h4>
				</div>
				<div class="modal-body">
					<table class="table">
						<tr>
							<td><STRONG>TIPO VEHICULO</STRONG></td>
							<td><STRONG>TOTAL VEHICULOS</STRONG></td>
						</tr>
					
						@foreach($estacionamientos as $est)
						<tr>
							
								<td><div class="form-group form-group-sm"><input class="form-control" readonly="readonly" type="text" name="tipovehiculo" value="{{$est->descripcion}}"></div></td>
								<td><div class="form-group form-group-sm"><input  class="form-control" readonly="readonly" type="text" name="cantidad" value="{{$est->vehiculos}}"></div></td>
							
						</tr>					
						@endforeach

					</table>

					<table class="table">
						<tr>
							<td><STRONG>TOTAL OCUPADOS</STRONG></td>
							<td><STRONG>TOTAL LIBRES</STRONG></td>
						</tr>
						<tr>
							<td><div class="form-group form-group-sm"><input class="form-control" readonly="readonly" type="text" name="tipovehiculo" value="{{$totalocupados}}"></div></td>
							<td><div class="form-group form-group-sm"><input  class="form-control" readonly="readonly" type="text" name="cantidad" value="{{$totallibres}}"></div></td>
						</tr>					
					</table>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
		
				</div>
			</div>
		</div>
</div>