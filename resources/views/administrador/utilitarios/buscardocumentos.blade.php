@extends('layouts.empresas')
@section('contenido')
<script>

  $(document).ready(function()
  {


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


    $("#btnenviar").on("click", function() {


          var formulario = $("#frmfacturacion").serializeArray();
        $("#divdetalle").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
          $.ajax({
            type: "POST",
            dataType: 'json',
            url: '/enviarsunat',
            data: formulario,
          }).done(function(respuesta){


            window.location.href = "/utilitarios/buscarcomprobantes";

  
          $("#imgload").hide();
     
          });

          
          
        });


     $("#btnxml").on("click", function() {


          var formulario = $("#frmfacturacion").serializeArray();
        $("#divdetalle").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
          $.ajax({
            type: "POST",
            dataType: 'json',
            url: '/generarxmlmasivo',
            data: formulario,
          }).done(function(respuesta){


            window.location.href = "/utilitarios/buscarcomprobantes";

  
          $("#imgload").hide();
     
          });

          
          
        });


      $("#btnpdf").on("click", function() {


          var formulario = $("#frmfacturacion").serializeArray();
        $("#divdetalle").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
          $.ajax({
            type: "POST",
            dataType: 'json',
            url: '/generarpdfmasivo',
            data: formulario,
          }).done(function(respuesta){


            window.location.href = "/utilitarios/buscarcomprobantes";

  
          $("#imgload").hide();
     
          });

          
          
        });


         $("#btnestado").on("click", function() {


          var formulario = $("#frmfacturacion").serializeArray();
        $("#divdetalle").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
          $.ajax({
            type: "POST",
            dataType: 'json',
            url: '/cambiarestadosunat',
            data: formulario,
          }).done(function(respuesta){


            window.location.href = "/utilitarios/buscarcomprobantes";

  
          $("#imgload").hide();
     
          });

          
          
        });
        

    $("#btnresumen").on("click", function() {

          var formulario = $("#frmfacturacion").serializeArray();
    

          $.ajax({
            type: "POST",
            dataType: 'json',
            url: '/enviarresumen',
            data: formulario,
          }).done(function(respuesta){


         
           window.location.href = "/utilitarios/buscarcomprobantes";
     
          });


          

  
        });


});

	var href = $('#btnPrint').attr('href');
	
	$("#btnPrint").printPage({
		
		 
		  url: href,
		  attr: "href",
		  messageBox:false,
		  
	})



</script>

						<style>
							input[type=date]::-webkit-inner-spin-button, 
							input[type=date]::-webkit-clear-button,
						    input[type=date]::-webkit-outer-spin-button { 
						      -webkit-appearance: none; 
						      margin: 0; 
						    }

						</style>

	<section class="content">
   {!!Form::open(array('url'=>'/utilitarios/buscarcomprobantes','autocomplete'=>'off','method'=>'POST','name'=>'frmfacturacion','id'=>'frmfacturacion','role'=>'form','files'=>'true'))!!}
    {{Form::token()}}
    	<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
	    @if(session()->has('info'))
	    	<div class="alert alert-danger">
	    	  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			  <strong>Alerta!</strong> {{ session('info') }}
			</div>
	    @endif


	    @if(session()->has('success'))
	    	<div class="alert alert-success">
	    	  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			  <strong>InformaciÃ³n!</strong> {{ session('success') }}
			</div>
	    @endif
	</div>
</div>

	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
          			<div class="box-header" style="background-color:blue;">
          				<font color="white"><strong><CENTER>REGISTRO DE COMPROBANTES</CENTER></strong></font>
          			</div>
	            	<div class="box-body">
	            		

						<div class="row">
							<div class="col-lg-2">
								<div class="form-group form-group-sm">
									 <label class="control-label" for="fecin">Desde </label>
									 <input type="date" name="fecin" class="form-control" value="{{$fecin}}">
									
								</div>
							</div>
							<div class="col-lg-2">
								<div class="form-group form-group-sm">

								 	<label class="control-label" for="fecfin">Hasta </label>
								 	 <input type="date" name="fecfin" class="form-control" value="{{$fecfin}}">
								
								</div>
							</div>	
							<div class="col-lg-2">
								<div class="form-group form-group-sm">
								 	<label class="control-label" for="fecfin">Documentos </label>
									<select class="form-control" name="tipdoc">
										<option value="0">Todos</option>
										@foreach($documentos as $doc)
											@if($doc->tdocod =='01' || $doc->tdocod=='03')
											 @if($doc->tdocod == $tipdoc)
											 	<option selected="selected" value="{{$doc->tdocod}}">{{$doc->tdodes}}</option>
											 @else
											 	<option value="{{$doc->tdocod}}">{{$doc->tdodes}}</option>
											 @endif
											
											@endif
										@endforeach
									</select>
								
								</div>
							</div>	
							<div hidden="hidden" class="col-lg-2">
								<div class="form-group form-group-sm">
								 	<label class="control-label" for="fecfin">ESTADO</label>
									<select class="form-control" name="estado">
										
										<option value="2">TODOS</option>
										<option value="0">No Enviados</option>
										<option value="1">Enviados</option>
									</select>
								
								</div>
							</div>
							<div class="col-lg-2">
								<div class="form-group form-group-sm">
								 	<label class="control-label" for="fecfin">ESTADO SUNAT</label>
									<select class="form-control" name="estado_sunat">
										<option value="2">Todos</option>

										<option value="0">ACEPTADOS</option>
										<option value="1">OTROS ESTADOS</option>
										
									</select>
								
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-lg-12">
								<div class="btn-toolbar" role="toolbar" aria-label="...">
								<div class="btn-group">
									<button type="submit" class=" btn btn-primary btn-sm">BUSCAR</button>
								</div>
								<div class="btn-group">
									<button type="button" name="btnenviar" id="btnenviar" class="btn btn-primary btn-sm">ENVIAR COMPROBANTES</button>
								</div>
								
								<!--<div class="btn-group">
									<button type="button" class=" btn btn-primary btn-sm">Consultar Tickets</button>
								</div>-->
							</div>
							</div>

						</div>
						
	            	</div>
	            </div>
	        </div>
	  
	</div> 
              
    	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body table-responsive" >
							<table id=""  class="table table-bordered table-hover">
							<thead>
								<tr>
									<th colspan="16">
										<div class="btn-group">
									<button type="button" name="btnxml" id="btnxml" class="btn btn-primary btn-sm">GENERAR XML</button>
								</div>
								<div class="btn-group">
									<button type="button" name="btnpdf" id="btnpdf" class="btn btn-primary btn-sm">GENERAR PDF</button>
								</div>
								<div class="btn-group">
									<button type="button" name="btnestado" id="btnestado" class="btn btn-primary btn-sm">CAMBIAR ESTADO</button>
								</div>
									</th>
								</tr>
								<tr>
									<th><input type="checkbox" name="selectall" id="selectall"  ></th>
									<th colspan="15">COMPROBANTES ELECTRONICOS</th>
								</tr>
								<tr>
									<th>Selec.</th>
									<th>CAMBIAR ESTADO</th>
									<th>Fec. Emision</th>
									<th>Tipo</th>
									<th>Serie</th>
									<th>N°</th>
									<th>RUC / DNI / Otros</th>
									<th style="width:210px;">Cliente</th>
									<th>Correo</th>
									<th>Moneda</th>
									<th>Total</th>
									<th>TICKET</th>
									<th>A4</th>
									<th>XML</th>
									<th>CDR</th>
									<th>Enviar Correo</th>
									<th>BAJAS</th>
									<th>TICKET RESUMEN</th>
									<th>TICKET BAJA</th>
	
								
									<th>SUNAT</th>
									<th></th>
								
							
									
								
								</tr>
							</thead>
							
							<tbody id="divdetalle">
								@foreach($comprobantes as $comp)
								<tr>
									<td><input type="checkbox" name="items[]" class="items" value="{{$comp->IdCpe_cabecera}}"></td>
									<td>
											<select name="codigos_sunat[]" class="form-control selectpicker input-sm" data-show-subtext="true" data-live-search="true" >
												<option value="SE">---</option>
												@foreach($codigos_sunat as $cs)
													@if($cs->codigo == $comp->ccacodsun)
														<option selected="selected" value="{{$cs->codigo}}">{{$cs->descripcion}}</option>
													@else
														<option value="{{$cs->codigo}}">{{$cs->descripcion}}</option>
													@endif
												@endforeach
											
									</select>
										</td>
								 	<td>{{Carbon::parse($comp->ccafem)->format('d-m-Y')}}</td>
									<td>{{$comp->des_doc}}</td>
									<td>{{$comp->serdoc}}</td>
									<td>{{$comp->numdoc}}</td>
									<td title='{{$comp->tdides}}'>{{$comp->ccandi}}</td>
									<td style="width:210px;">{{$comp->ccanom}}</td>
									<td>
										@if(empty($comp->clicorcli))
											{{$comp->clicor}}<br>
										@else
											{{$comp->clicorcli}}<br>
										@endif
										
										@if(empty($comp->clicorcli2))
											{{$comp->clicor2}}<br>
										@else
											{{$comp->clicorcli2}}<br>
										@endif


										@if(empty($comp->clicorcli3))
											{{$comp->clicor3}}<br>
										@else
											{{$comp->clicorcli3}}<br>
										@endif

										@if(empty($comp->clicorcli4))
											{{$comp->clicor4}}<br>
										@else
											{{$comp->clicorcli4}}<br>
										@endif
									</td>
									<td>{{$comp->monnom}}</td>
									<td align="right">{{number_format($comp->ccaitv,'2','.',',')}}</td>
									<td><a id="btnPrint" href="/imprimir/{{$comp->IdCpe_cabecera}}/{{$comp->tdocod}}" target="_blank"><center><i class="fa fa-file-pdf-o fa-lg"></i></center></a></td>
										
											<td><a href="/descargar/{{$comp->IdCpe_cabecera}}/pdf"><center><i class="fa fa-file-excel-o fa-lg"></i></center></a></td>

										<td><a href="/descargar/{{$comp->IdCpe_cabecera}}/xml"><center><i class="fa fa-file-excel-o fa-lg"></i></center></a></td>
								
										<td><a href="/descargar/{{$comp->IdCpe_cabecera}}/cdr"><center><i class="fa fa-file-pdf-o fa-lg"></i></center></a></td>
								
										<td>
											<a href="" data-target="#modal-correo-{{$comp->IdCpe_cabecera}}" data-toggle="modal"><center><img src="/img/mail.jpg" title="Aceptado con Observaciones" height="40px" width="40px"></center></a>
										</td>




										@if($comp->ccabaj=="")
										    <td><center>---</center></td>
										@else
										 	<td><a href="/consultarticketbaja/{{$comp->IdCpe_cabecera}}">{{$comp->ccabaj}}</a></td>
										@endif
									

									
										<td>
											
										</td>

										<td>
											
										</td>

									
										<!--<td >{{substr($comp->ccadessun,0,254)}}</td>-->
										@if($comp->ccacodsun=='0')
										<td>
											<a><center><img src="/icon/check.png" title="{{$comp->ccadessun}}" height="20px" width="20px"></center></a>
										</td>
										@elseif($comp->ccacodsun=='8')
										<td>
											<a><center><img src="/icon/error.png" title="ANULADO" height="20px" width="20px"></center></a>
										</td>

										@elseif($comp->ccacodsun >'100' && $comp->ccacodsun <'1999')

										<td>
											<a href="/enviarcomprobante/{{$comp->IdCpe_cabecera}}"><center><img src="/icon/iconwarning.png" title="{{$comp->ccadessun}}" height="20px" width="20px"></center></a>
										</td>

										@elseif($comp->ccacodsun > '2000' && $comp->ccacodsun <'3999')
										<td>
											<a><center><img src="/icon/error.png" title="{{$comp->ccadessun}}" height="20px" width="20px"></center></a>
										</td>
										@elseif($comp->ccacodsun > '4000')
										
										<td>
											<a><center><img src="/icon/checkobs.png" title="{{$comp->ccadessun}}" height="20px" width="20px"></center></a>
										</td>
										@else

										<td>
											
										</td>
										@endif
										
									
										
										
								</tr>
									@include('empresas.comprobantes.modal')
								@endforeach
							</tbody>
						</table><br>
					</div>	
					{{$comprobantes->render()}}
				</div>	
			</div>
		</div>
	 {{Form::close()}}
	</section>

@endsection