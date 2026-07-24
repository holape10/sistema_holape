<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-delete-{{$cat->cat_id}}">
    {!!Form::open(array('url'=>'subcategorias','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					<h4 class="modal-title">Agregar Subfamilia</h4>
				</div>
				<div class="modal-body">
					 <div class="row">
				        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
				            <div class="form-group form-group-sm">
				                <label for="subcat_nom">Subcategoría</label>
				                <input type="text" name="subcat_nom" value="" class="form-control" placeholder="">
				               
				           </div>
				        </div>
				      <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
				            <div class="form-group form-group-sm">
				                <label for="color">Color</label>
				                <input type="color" name="color" value="#3f4aee" class="form-control" placeholder="">
				                
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