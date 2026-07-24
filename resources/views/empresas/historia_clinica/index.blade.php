@extends('layouts.empresas')
@section('contenido')
@include('empresas.historia_clinica.modal_nueva_atencion')

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

       
              window.location.href = "/historiaclinica";
          
 
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
          				<font color="white"><center><strong>Historias Clínicas</strong></center></font>
          			</div>
	            	<div class="box-body">
	            		@include('empresas.historia_clinica.buscar')
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

									<th>N° H.C</th>
									<th>Tipo<br>Documento</th>
									<th>N° Documento</th>
									<th>Cliente</th>
									<th>Fecha Nacimiento</th>
									<th>Sexo</th>
									<th>Estado Civil</th>
									<th>Opciones</th>

								</tr>
							</thead>
							
							<tbody>
								@foreach($historias as $comp)
								<tr>
									<td>{{$comp->his_cli_cod}}</td>
									<td>{{$comp->tdides}}</td>
									<td>{{$comp->clinum}}</td>
									<td>{{$comp->clinom}}</td>
									<td>{{Carbon::parse($comp->fecha_nacimiento)->format('d-m-Y')}}</td>
									<td>{{$comp->sex_nom}}</td>
									<td>{{$comp->est_civ_des}}</td>
									<td>
										 <a href="/historia/{{$comp->his_cli_id}}" ><img src="/icon/historias_clinicas.png"   title="HISTORIAS CLINICAS" height="45px" width="45px"></a>

											<a data-target="#modal-eliminar-historia-{{$comp->his_cli_id}}" data-toggle="modal"><img src="/icon/error.png"   title="ELIMINAR" height="35" width="35"></a>
									</td>


								</tr>
								@include('empresas.historia_clinica.modal_eliminar_historia')

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