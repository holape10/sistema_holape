
<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-2" id="modal-orden-{{$comp->IdCpe_cabecera}}">
		{!!Form::open(array('url'=>'/actualizarestado','autocomplete'=>'off','method'=>'POST','name'=>'formestado','id'=>'formestado','role'=>'form','files'=>'true'))!!}
		{{Form::token()}}
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header" style="background-color:blue;">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					<h4 class="modal-title"><font color="white"><center><strong>CAMBIO DE ESTADO</strong></center></font></h4>
				</div>
				<div class="modal-body">
					 <div class="row">
                     	 <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Código de Orden</label>
                                <input type="text" name="orden" readonly="readonly" value="OS-{{$comp->numdoc}}"  class="form-control">
                              	<input type="hidden" name="id" value="{{$comp->IdCpe_cabecera}}">
                            </div>
                        </div>
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Estados</label>
                                <select name="est_equ_id" id="est_equ_id" class="form-control">
                                	@foreach($estados as $est)
                                	@if($est->est_equ_id == $comp->est_equ_id)
                                    	<option selected="selected" value="{{$est->est_equ_id}}">{{$est->est_equ_nom}}</option>
                                    @else
                                    	<option value="{{$est->est_equ_id}}">{{$est->est_equ_nom}}</option>
                                    @endif
                                    @endforeach
                                </select>
                              
                            </div>
                        </div>

                     

                      
                  
					</div>
				</div>
				<div class="modal-footer">
					<cente><button type="submit" class="btn btn-primary btn-block">GUARDAR</button></center>
					
				</div>
			</div>
		</div>
	</div>
		{!!Form::close()!!}
</div>
