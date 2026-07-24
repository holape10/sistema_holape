<script type="text/javascript">
		$(document).ready(function(){


		setTimeout(refrescar, 5000);
	});

function refrescar(){
	  
	    	//$("#mes_id").val("");
			//$("#pis_id").val("");

			var piso = $("#piso").val();
			var tipo = $("#tipo").val();

			if(tipo=='1'){
					$.ajax({
					type: "GET",
					dataType: 'json',
					url: "/buscarmesas/"+piso,
				}).done(function(respuesta){
					$("#listar_mesas").html(respuesta.vista);
				});	
			}
			

	}

	
</script>

<?php $i=0; ?>
				@if(!empty($mesas))
					@foreach($mesas as $mesas)
						<?php $i=$i+1; ?>
						<div class="col-lg-4 col-md-2 col-sm-3 col-xs-3 form-group form-group-sm">
							@if($mesas->mes_est=='Ocupado')

							@if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin') ||  Auth::User()->hasRole('caja'))
									<button type="button" class="btn" style="width:90px;height:80px; background:#E74C3C;color:#FDFEFE;" onclick="elegir_mesa('{{$mesas->mes_nom}}','{{$mesas->mes_id}}','{{$mesas->mes_nom}}')">
									<strong >{{$mesas->mes_nom}}</strong>
								</button>
							@else								
							<button type="button" class="btn" style="width:80px;height:40px; background:#E74C3C;color:#FDFEFE;" onclick="elegir_mesa('{{$mesas->mes_nom}}','{{$mesas->mes_id}}','{{$mesas->mes_nom}}')">
									<strong >{{$mesas->mes_nom}}</strong>
								</button>
								@endif

							@elseif($mesas->mes_est=='Libre')									

							@if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin') ||  Auth::User()->hasRole('caja'))
									<button type="button" class="btn" style="width:90px;height:80px;background:#52BE80;color:#FDFEFE;" onclick="elegir_mesa('{{$mesas->mes_nom}}','{{$mesas->mes_id}}','{{$mesas->mes_nom}}')">
										<strong>{{$mesas->mes_nom}}</strong>
									</button>
									@else
									<button type="button" class="btn" style="width:80px;height:40px;background:#52BE80;color:#FDFEFE;" onclick="elegir_mesa('{{$mesas->mes_nom}}','{{$mesas->mes_id}}','{{$mesas->mes_nom}}')">
										<strong>{{$mesas->mes_nom}}</strong>
									</button>
									@endif
							@else			

							@if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin') ||  Auth::User()->hasRole('caja'))						
									<button type="button" class="btn" style="width:90px;height:80px;  background:#F4D03F;color:#FDFEFE;">
										<br><font style="color:#FDFEFE"><strong >{{$mesas->mes_nom}}</strong></font>
									</button>
									@else
									<button type="button" class="btn" style="width:80px;height:40px;  background:#F4D03F;color:#FDFEFE;">
										<br><font style="color:#FDFEFE"><strong >{{$mesas->mes_nom}}</strong></font>
									</button>
									@endif								
							@endif
							<BR>				
						</div>
					@endforeach
				@endif