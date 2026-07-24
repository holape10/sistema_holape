<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-importar-productos">
	{!!Form::open(array('url'=>'importarproductos','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}   
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header" style="background:blue;">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					<font color="white" size="2"><strong><center>IMPORTAR PRODUCTOS</center></strong></font>
				</div>
				<div class="modal-body">

					
					   
					<div class="row">
						<div class="col-lg-4">
							<div class="form-group form-group-sm">
								<label>SUBIR ARCHIVO EXCEL</label>
								<input class="form-control" type="file" name="archivo" >
							</div>
						</div>
					</div>
					

				

				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary">IMPORTAR</button>
					<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
					
				</div>
			</div>
		</div>
	{{Form::Close()}}
</div>
