<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-baja-{{$comp->IdCpe_guia}}">
		<div class="modal-dialog">


	    {!!Form::open(array('url'=>'/anularguiaremision','autocomplete'=>'off','method'=>'GET','class'=>'formbaja','name'=>'formbaja','id'=>'formbaja{{$comp->IdCpe_guia}}','role'=>'form','files'=>'true'))!!}
	    {{Form::token()}}
			<div class="modal-content">
				<div class="modal-header" style="background:blue;">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					<center><font color="white"><strong>Anular Guía de Remisión {{$comp->serieguia}}-{{$comp->numeroguia}}</strong></font></center>
				</div>
				<div class="modal-body">
					<div class="row">
				    	<div class="col-lg-12 col-md-12 col-sm-6 col-xs-6">
				    		<div class="form-group form-group-sm">
						       	<label for="motivo">Motivo</label>
						        <input type="text" id="motivo{{$comp->IdCpe_guia}}" value="" class="form-control input-sm" placeholder="">
				                <input type="hidden" readonly="readonly" id="comprobante{{$comp->IdCpe_guia}}" value="{{$comp->IdCpe_guia}}"> 
				           </div>
				    	</div>
				    </div>
				</div>
				<div class="modal-footer imgloadanular" style="display:none;">
					 <center><img  width="80px" height="80px" src="/img/load.gif" name="imgload" id="imgload"></center>
				</div>
				<div class="modal-footer botonesanular">
					<button type="button" id="{{$comp->IdCpe_guia}}"  onclick="enviarbaja({{$comp->IdCpe_guia}});" class="btn btn-danger">Anular</button>
					<button type="button" class="btn btn-primary" data-dismiss="modal">Cerrar</button>
					
				</div>
			</div>
		   {!!Form::close()!!}  
		</div>
</div>