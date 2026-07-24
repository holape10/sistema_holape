@extends('layouts.empresas')
@section('contenido')


@if(isset($codfact))

@php
$pdf = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$codfact)->first();
@endphp

@endif

@include('empresas.puntosventas.modalpdf')
<style type="text/css">
	#modal-pdf{
		z-index: 99999 !important;
	}
</style>

<script>

	
	$(document).ready(function(){



    $("#btnAsigCoor").on("click", function() {
      var formulario = $("#formOrdenes").serializeArray();
       $("#imgload").show();
      $(".botones").hide();
      $.ajax({
        type: "POST",
        dataType: 'json',
        url: '/asignarcoordinador',
        data: formulario,
      }).done(function(respuesta){

 	    if(respuesta.estado =='error'){

            alert(respuesta.mensaje);

            $("#imgload").hide();
            $(".botones").show();

        }else{
        	  window.location.href = "/ordenescoor";
        }
      
        $("#imgload").hide();
        $(".botones").show();
        
      });



    });

      $("#btnAsigTec").on("click", function() {
      var formulario = $("#formOrdenes").serializeArray();
       $("#imgload").show();
      $(".botones").hide();
      $.ajax({
        type: "POST",
        dataType: 'json',
        url: '/asignartecnico',
        data: formulario,
      }).done(function(respuesta){

      	    if(respuesta.estado =='error'){

            alert(respuesta.mensaje);

            $("#imgload").hide();
            $(".botones").show();

        }else{
        	  window.location.href = "/ordenestec";
        }
      

        $("#imgload").hide();
        $(".botones").show();
        
      });



    });






		$("#selectall").on("click", function() {  


			$(".items").prop("checked", this.checked);  
		});  

		// if all checkbox are selected, check the selectall checkbox and viceversa  
		$(".items").on("click", function() { 

			if ($(".items").length == $(".items:checked").length) {  
				$("#selectall").prop("checked", true);  
			} else {  
				$("#selectall").prop("checked", false);  
			}  
		});
		

		$("#btnreg").on("click", function() {

			var placa = $("#placa").val();

			window.location.href = "/cotizaciones/"+placa;





		});

		$("#btnregot").on("click", function() {

			var placa = $("#placa").val();

			window.location.href = "/nuevaordenelectro";





		});


		$("#btnregop").on("click", function() {

			var placa = $("#placa").val();

			window.location.href = "/ordenpedido/"+placa;





		});




	});

</script>

@if(!empty($codfact))
<script>

	$(document).ready(function()
	{

		$("#modal-pdf").modal("show");
	});
</script>
@endif


<section class="content">


	<div class="row">
		<div class="col-xs-12">
			<div class="box">
				<div class="box-header" style="background-color:blue;">
					<font color="white"><center><strong>ORDENES DE TRABAJO</strong></center></font>
				
				</div>
				<div class="box-body">
					@include('empresas.taller_electrodomesticos.buscarordenes')
				</div>
			</div>
		</div>
	</div> 



  {!!Form::open(array('url'=>'/','autocomplete'=>'off','method'=>'POST','name'=>'formOrdenes','id'=>'formOrdenes','role'=>'form','files'=>'true'))!!}
  {{Form::token()}}


	<div class="row">
		<div class="col-xs-12">

			<div class="box">
				<div class="box-body table-responsive">
					<table id=""  class="table table-bordered table-hover" style="font-size:8pt;">
						<thead style="background-color:blue;color:white;">
							<tr >
								<th style="vertical-align:middle;text-align:center;"><input type="checkbox" name="selectall" id="selectall"  ></th>
								<th style="width:210px;vertical-align:middle;text-align:center;">Fecha Ingreso</th>
								<th style="vertical-align:middle;text-align:center;">Estado</th>
								<th style="width:150px;vertical-align:middle;text-align:center;">N° Orden</th>
								<th style="vertical-align:middle;text-align:center;" style="width:250px;">Nombre o Razón Social</th>
								<th style="vertical-align:middle;text-align:center;">Tel&eacute;fono</th>
								<th style="vertical-align:middle;text-align:center;">Modelo</th>
								<th style="vertical-align:middle;text-align:center;">Serie</th>
								<th style="vertical-align:middle;text-align:center;">S&iacute;ntoma</th>
								<th style="vertical-align:middle;text-align:center;">PDF</th>
								<th style="vertical-align:middle;text-align:center;">Supervisor</th>
								<th style="vertical-align:middle;text-align:center;">Coordinador</th>
								<th style="vertical-align:middle;text-align:center;">T&eacute;cnico</th>
							
								<th style="vertical-align:middle;text-align:center;" colspan="3">Opciones</th>
							</tr>
						</thead>

						<tbody>
							@foreach($comprobantes as $comp)
							<tr>
								<td style="vertical-align:middle;text-align:center;" ><input type="checkbox" name="items[]" class="items" value="{{$comp->IdCpe_cabecera}}"</td>
								<td>{{Carbon::parse($comp->fechacot)->format('d-m-Y')}}</td>
								<td><span class=" btn-primary btn-sm btn-block" style="text-align:center;">{{$comp->est_ord_cod}}</span></td>
								<td>{{$comp->serdoc}}-{{$comp->numdoc}}</td>
								<td style="width:210px;vertical-align:middle;">{{$comp->ccanom}}</td>
								<td style="width:210px;vertical-align:middle;">{{$comp->telefono}}</td>
								<td style="width:210px;vertical-align:middle;">{{$comp->mod_nom}}</td>
								<td style="width:210px;vertical-align:middle;">{{$comp->equi_ser}}</td>
								<td style="width:210px;vertical-align:middle;">{{$comp->observaciones}}</td>

								<td style="vertical-align:middle;"><a href="/descargar/{{$comp->IdCpe_cabecera}}/pdf"><center><i class="fa fa-file-excel-o fa-lg"></i></center></a></td>
								
								<td style="vertical-align:middle;">{{$comp->nom_sup}} {{$comp->ape_sup}}</td>
								<td style="vertical-align:middle;">{{$comp->nom_coor}} {{$comp->ape_coor}}</td>
								<td style="vertical-align:middle;">{{$comp->nom_tec}} {{$comp->nom_tec}}</td>
							
								<td style="vertical-align:middle;"><a href="/editarordencompu/{{$comp->IdCpe_cabecera}}"><button type="button" class="btn btn-sm btn-warning btn-block">Editar</button></a></td>
								

								<!--@if($comp->estado =='COBRADO')
								<td style="vertical-align:middle;"><button type="button" disabled="disabled" class="btn btn-sm btn-block btn-primary">COBRAR</button></td>
								@else
								<td style="vertical-align:middle;"><a href="/cobrar/{{$comp->IdCpe_cabecera}}"><button type="button"  class="btn btn-sm btn-primary btn-block">COBRAR</button></a></td>
								@endif-->

								

								<!--<td style="vertical-align:middle;"><a href="/eliminar/{{$comp->IdCpe_cabecera}}"><button type="button" class="btn btn-sm btn-danger">Eliminar</button></a></td>-->

							</tr>
							@endforeach
						</tbody>
					</table><br>
				</div>	
				{{$comprobantes->render()}}
			</div>	
		</div>
	</div>
{!!Form::close()!!}


</section>

@endsection