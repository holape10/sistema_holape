<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-catalogo">

		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header" style="background:#3c8dbc;">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					<h4 class="modal-title" style="color:white;font-weight:bold;"><center>CATALOGO PRODUCTOS</center></h4>
				</div>
				<div class="modal-body">
					{!!Form::open(array('url'=>'/buscar_catalogo','method'=>'POST','autocomplete'=>'off','files'=>'true','id'=>'frmCatalogo'))!!}
   					 {{Form::token()}}
					<div class="row">
						<div class="col-lg-12">
							<div class="form-group form-group-sm">
								<label>CODIGO O DESCRIPCION</label>
								<input type="text" name="bus_cata" id="bus_cata" class="form-control input-sm" onkeypress="if(event.keyCode == 13) consultar_catalogo();">
							</div>
						</div>
					</div>
						{{Form::Close()}}
					<div class="row">
						<div class="col-lg-12">
							 <div class="form-group form-group-sm">
							 	<table  class="table table-bordered table-hover table-responsive table-striped">
							 		<thead>
							 			<tr style="background:#3c8dbc;color:white;font-weight:bold;">
							 			<th style="text-align:center;font-weight:bold;">PRODUCTO</th>
							 			<th style="text-align:center;font-weight:bold;">PRECIO</th>
							 			<th style="text-align:center;font-weight:bold;">STOCK</th>
							 			<th style="text-align:center;font-weight:bold;">IMAGEN</th>
							 		</tr>
							 		</thead>
							 		
							 		<tbody id="detalle_catalogo">
							 		
							 	</tbody>
							 	</table>
							 	
							 </div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
					
				</div>
			</div>
		</div>

</div>
