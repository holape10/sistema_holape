<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-anular-{{$comp->IdCpe_cabecera}}">
		<div class="modal-dialog">

	    {!!Form::open(array('url'=>'/registrarbajacomprobante','autocomplete'=>'off','method'=>'GET','class'=>'formbaja','name'=>'formbaja','id'=>'formbaja{{$comp->IdCpe_cabecera}}','role'=>'form','files'=>'true'))!!}
	    {{Form::token()}}
			<div class="modal-content">
				<div class="modal-header" style="background:blue;">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					<center><font color="white"><strong>Anular Comprobante {{$comp->serdoc}}-{{$comp->numdoc}}</strong></font></center>
				</div>
				<div class="modal-body">
					
					  

					<div class="row">
				    	<div class="col-lg-12 col-md-12 col-sm-6 col-xs-6">
				    		<div class="form-group form-group-sm">
						       	<label for="motivo">Motivo</label>
						        <input type="text" id="motivo{{$comp->IdCpe_cabecera}}" value="" class="form-control input-sm" placeholder="">
				                <input type="hidden" readonly="readonly" id="comprobante{{$comp->IdCpe_cabecera}}" value="{{$comp->IdCpe_cabecera}}"> 
				           </div>
				    	</div>
				    	
				    </div>
				</div>
				<div class="modal-footer imgloadanular" style="display:none;">
					 <center><img  width="80px" height="80px" src="/img/load.gif" name="imgload" id="imgload"></center>
				</div>
				<div class="modal-footer botonesanular" >
					<button type="button" id="{{$comp->IdCpe_cabecera}}"  onclick="enviarbaja({{$comp->IdCpe_cabecera}});" class="btn btn-primary">Anular</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
					
				</div>
			</div>
		   {!!Form::close()!!}  
		</div>
</div>