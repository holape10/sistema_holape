

<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-cuotas">
	<div class="modal-dialog modal-sm" style="width:90%">
		<div class="modal-content">
			<div class="box-header" style="background-color:blue;">
				<font color="white"><center><strong>REGISTRA CUOTAS</strong></center></font>
			
			</div>
		
			<div class="modal-body"  >
				<div class="box">
					
					<div class="box-body">
                        <table id="detcuotas"  class="table table-border table-striped">
                            <thead>
                            	<th width="400px;">Fecha Vencimiento</th>
                                <th width="400px;">Monto de Cuota</th>
								
                                <th><button type="button" onClick="agregarcuota();"  class="btn btn-success btn-sm add"><span class="glyphicon glyphicon-plus"></span></button></th>
                            </thead>
                            <tbody id="">
                              @if(!empty($cuotas))
                              	@foreach($cuotas as $cuo)
                              		<tr>
                              			<td>
                              				<input type="date" name="fec_cuo[]" class="form-control input-sm" value="{{$cuo->ven_cuo_fec_ven}}">
                              			</td>
                              			<td>
                              				<input name="mon_cuo[]" type="number" value="{{$cuo->ven_cuo_mon}}" step="any" class="form-control input-sm">
                              			</td>
                              			<td>
                              				<button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button>
                              			</td>
                              		</tr>
                              	@endforeach
                              @endif
                            </tbody>
                        </table>

					</div>
				
					
				
				</div>
				
			</div>
			<div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Cerrar</button>
		    </div>

		</div>
	</div>

</div>
