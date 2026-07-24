@extends('layouts.empresas')
@section('contenido')

<script type="text/javascript">
	
$(document).ready(function(){


    $("#btnRegistrar").on("click", function() {


      var formulario = $("#frmAtencion").serializeArray();
      $("#imgload").show();
      $(".botones").hide();
      $.ajax({
        type: "POST",
        dataType: 'json',
        url: '/registraratencion',
        data: formulario,
      }).done(function(respuesta){


        if(respuesta.estado =='error'){

            alert(respuesta.mensaje);

            $("#imgload").hide();
            $(".botones").show();

        }else{

       
              window.location.href = "/atencionesmedicas";
          
 
        }

         

      });

    });

})

function  buscarcliente(){


  var formulario = $("#clinum").val();
  $("#imgloadcliente").show();

  $.ajax({
    type: "get",
    dataType: 'json',
    url: '/autocomplete/'+formulario,

  }).done(function(respuesta){



  if(respuesta.error){
      
    
      

    alert(respuesta.error);
      $("#imgloadcliente").hide();


 
  }else{

   $('#clinom').val(respuesta[0].nom);
   $('#clidir').val(respuesta[0].dir);
   $('#clitel').val(respuesta[0].telefono);
   $('#clicor').val(respuesta[0].cor);
   $('#clicod').val(respuesta[0].clicod);
   $("#tdicod").val(respuesta[0].tdicod).attr('selected', 'selected');
   $("#sex_id").val(respuesta[0].sex_id).attr('selected', 'selected');
   $("#est_civ_id").val(respuesta[0].est_civ_id).attr('selected', 'selected');

   $("#imgloadcliente").hide();
    $(".botones").show(); 

  }
   
          
  });

  

}


</script>
	<section class="content">
	
	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
          			<div class="box-header" style="background-color:blue;">
          				<font color="white"><center><strong>ESPECIALISTA : {{$doctor->name}} {{$doctor->apeusu}}</strong></center></font>
          			</div>
	            	
	            </div>
	        </div>
	</div> 
             

    	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body table-responsive" >
							<table id=""  class="table table-bordered table-hover table-responsive">
							<thead>
								<tr style="font-size:10pt;font-weight:bold;">

									<th>Fecha Cita</th>
									<th>Hora</th>
									<th>Especialidad</th>
									<th>Doctor</th>
									<th>Opciones</th>
									<th>ESTADO</th>

								</tr>
							</thead>
							
							<tbody>
								@foreach($historias as $comp)
								<tr>
									<td>{{$comp->ate_cli_fec}}</td>
									<td>{{$comp->ate_cli_hor}}</td>
									<td>{{$comp->esp_des}}</td>
									<td>{{$comp->name}} {{$comp->apeusu}}</td>
									<td>
										
										 @if($comp->ate_cli_est=='PENDIENTE')
											<a href="/atender/{{$comp->ate_cli_id}}"><img src="/icon/historiaclinica.png" title="ATENDER" height="50px" width="50px"></a>
										@elseif($comp->ate_cli_est=='ATENDIDO')
										 <img src="/icon/historiaclinica.png" style="opacity: 0.5;" title="ATENDER" height="50px" width="50px">
										@endif

									
											
										 	
										 @if($comp->ate_cli_est=='PENDIENTE')
											<a href=""><img src="/icon/editar.png" style="opacity: 0.5;"  title="EDITAR" height="45px" width="45px"></a>
										@elseif($comp->ate_cli_est=='ATENDIDO')
										 <a href=""><img src="/icon/editar.png" title="EDITAR" height="45px" width="45px"></a>
										@endif
									</td>
									<td style="width:20px;">
										@if($comp->ate_cli_est=='PENDIENTE')
											<button type="button" class="btn btn-warning btn-block btn-sm">PENDIENTE</button>
										@elseif($comp->ate_cli_est=='ATENDIDO')
										 <button type="button" class="btn btn-success btn-block btn-sm">ATENDIDO</button>
										@endif
										  
									</td>
								</tr>
								@endforeach
							</tbody>
						</table><br>
					</div>	
					{{$historias->render()}}
				</div>	
			</div>
		</div>
	</section>

@endsection