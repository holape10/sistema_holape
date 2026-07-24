

<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-pedidos-albergue">
{!!Form::open(array('url'=>'/gastos','autocomplete'=>'off','method'=>'POST','id'=>'formactualizarpedido','role'=>'form','files'=>'true'))!!}
{{Form::token()}}
	<div class="modal-dialog modal-lg" style="width:90%">
		<div class="modal-content">
			<div class="box-header" style="background-color:blue;">
				<font color="white"><center><strong>REGISTRA INGRESO / GASTO</strong></center></font>
			
			</div>
			<center><img style="display:none;" width="80px" height="80px" src="/img/load.gif" name="imgloadgasto" id="imgloadgasto"></center>
			<div class="modal-body" id="botonesgasto" >
				<div class="box">
					<div class="box-header" style="background-color:gray;">
						<font color="white"><center><strong>DATOS COMPROBANTE</strong></center></font>
					</div>
					<div class="box-body">
						<div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
							<div class="form-group form-group-sm">
								<label>DOCUMENTO</label>
								<select name="cmbTdo" id="cmbTdo" class="form-control">
									<option></option>
									@foreach($comprobante as $doc)
										<option value='{{$doc->tdocod}}' @if(old('tdocod') == $doc->tdocod) {{ 'selected' }} @endif >{{$doc->tdodes}}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
							<div class="form-group form-group-sm">
								<label>Serie</label>
								<input name="serdoc"  id="serdoc" class="form-control" >
							</div>
						</div>
						<div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
							<div class="form-group form-group-sm">
								<label>N° Doc.</label>
								<input type="number"  id="numdoc" name="numdoc"  class="form-control" >
							</div>
						</div>
						<div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
							<div class="form-group form-group-sm">
								<label>Fecha Emision</label>
								<input type="date" id="fecEmi" name="fecEmi" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">
							</div>
						</div>
						<div class="col-lg-2 col-md-4 col-sm-12 col-xs-12">
							<div class="form-group form-group-sm">
								<label>Fecha Vencimiento</label>
								<input type="date" name="fecVen" value="{{Carbon::now()->format('Y-m-d')}}"  class="form-control">

							</div>
						</div>
						<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
							<div class="form-group form-group-sm">
								<label>TIPO MOVIMIENTO</label>
								<select name="cmbmovimiento" id="cmbmovimiento" class="form-control">
									<option value='GASTO'>GASTO</option>
									<option value='INGRESO'>INGRESO</option>
								</select>
							</div>
						</div>
						<div style="display:none;" class="col-lg-2 col-md-4 col-sm-12 col-xs-12">
							<div class="form-group form-group-sm">
								<label>Moneda</label>
								<select name="mondoc" id="mondoc" class="form-control">
									@foreach ($monedas as $mon)
									<option value='{{$mon->moncod}}' @if(old('mondoc') == $mon->moncod) {{ 'selected' }} @endif >{{$mon->monnom}}</option>
									@endforeach
								</select>
							</div>
						</div>
					</div>

					<div class="box-header" style="background-color:gray;">
						<font color="white"><center><strong>DATOS COMPROBANTE</strong></center></font>
					</div>
					<div class="box-body">
                        <table id="detgasto" class="table table-border table-striped">
                            <thead>
                                <th>Detalle</th>
								<th>Monto</th>
                                <th><button type="button" onClick="agregarlineagasto();"  class="btn btn-success btn-sm add"><span class="glyphicon glyphicon-plus"></span></button></th>
                            </thead>
                            <tbody id="">
                             
                            </tbody>
                        </table>

                        <div class="col-lg-2">
                        	 <div class="form-group form-group-sm">
                        	  <label>TOTAL</label>
                        <input class="form-control" type="number" min="0"  step="any" style="text-align:right;" id="total_gasto" name="total_gasto" value='0.00' readonly="readonly">
                        </div>
                      
                        </div>
                       
                   
					</div>
					<div class="box-header" style="background-color:gray;">
						<font color="white"><center><strong>OBSERVACIONES</strong></center></font>
					</div>
					<div class="box-body">
						<div class="form-group">
                                <textarea class="form-control" id="obser" name="obser" rows="3"></textarea>
                        </div>
					</div>
				
				</div>
				
			</div>
			<div class="modal-footer">
				<button type="button" id="btnActPed" name="btnActPed"  class="btn btn-sm btn-primary"><strong>Actualizar</strong></button>
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Cerrar</button>
		    </div>

		</div>
	</div>
	 {!!Form::close()!!}  
</div>
