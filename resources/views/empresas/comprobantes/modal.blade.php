<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-correo-{{$comp->IdCpe_cabecera}}">
	{!!Form::open(array('url'=>'enviarcorreo','method'=>'POST','autocomplete'=>'off','id'=>'frmenviarcorreo'))!!}
    {{Form::token()}}
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					<h4 class="modal-title">Enviar Comprobante al Cliente</h4>
				</div>
				<div class="modal-body">
					<div class="row">
				    	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
				    		<div class="form-group form-group-sm">
						       	<label for="txt_correo">Correo 1</label>
						        <input type="text" name="txt_correo" value="@if(!empty($comp->clicorcli)){{$comp->clicorcli}}@else {{$comp->clicor}}@endif" class="form-control input-sm" placeholder="">
				                  
				           </div>
				    	</div>
				    	
				    
				    	<input type="hidden"  name="comprobante" value="{{$comp->IdCpe_cabecera}}">
				    	
				    </div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary">Enviar Correo</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
					
				</div>
			</div>
		</div>
	{{Form::Close()}}
</div>