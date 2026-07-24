<script type="text/javascript">
		$(document).ready(function(){
		//setTimeout(refrescar, 5000);
			//refrescar();
		setInterval(refrescar, 5000);
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
					url: "/buscarmesasmobil/"+piso,
				}).done(function(respuesta){
					$("#listar_mesas_mobil").html(respuesta.vista);
				});	
			}
			

	}

	
</script>

				<div id="listar_mesas_mobil" class="col-xs-12">
                    
                    <?php $i=0; ?>
                    @if(!empty($mesas))
                    @foreach($mesas as $mesa_item)
                    <?php $i=$i+1; ?>
                    <div class="col-xs-3 mesa-container">
                        <button type="button" class="btn btn-mesa @if($mesa_item->mes_est=='Ocupado') ocupado @elseif($mesa_item->mes_est=='Libre') libre @else cuenta @endif" onclick="elegir_mesa('{{$mesa_item->mes_nom}}','{{$mesa_item->mes_id}}','{{$mesa_item->mes_nom}}')">
                            <strong>{{$mesa_item->mes_nom}}</strong>
                        </button>
                    </div>
                    @endforeach
                    @endif
                </div>