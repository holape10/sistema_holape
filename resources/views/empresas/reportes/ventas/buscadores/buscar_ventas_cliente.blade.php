@extends('layouts.empresas')
@section('contenido')

<script type="text/javascript">
	
	$(document).ready(function()
   {


   
   	  $("#btnGenPdf").click(function() {
        var accion = $(this).attr('dir');

        $('#frmReporte').attr('action', accion);
        $('#frmReporte').submit();
    });


   	  $("#btnGenTicket").click(function() {
        var accion = $(this).attr('dir');

        $('#frmReporte').attr('action', accion);
        $('#frmReporte').attr('target', '_blank');
        $('#frmReporte').submit();
    });

   	   $("#btnExportar").click(function() {
        var accion = $(this).attr('dir');

        $('#frmReporte').attr('action', accion);
  
        $('#frmReporte').submit();
    });



   	  $("#sucursal").change(function() {
         
                var sucursal = $("#sucursal").val();
               
                $("#divalmacen").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
                $.ajax({
                  type: "GET",
                  dataType: 'json',
                  url: "/buscaralmacen/"+sucursal,

                }).done(function(respuesta){
                $("#divalmacen").html(respuesta.vista);
               
                });

      });


   	   $("#btnTicketVen").click(function() {
         
              
               
             
              
     		 var formulario = $("#frmReporte").serializeArray();
			 
			      $.ajax({
			        type: "POST",
			        dataType: 'json',
			        url: '/imprimirreporteventas',
				      data: formulario,
                }).done(function(respuesta){
               
                });

      });
   	   
       $("#btnBuscar").on("click", function() {
      	
       	
      var formulario = $("#frmReporte").serializeArray();
      $("#imgload").show();
      $(".botones").hide();
      $.ajax({
        type: "POST",
        dataType: 'json',
        url: '/generarreporteventas',
        data: formulario,
      }).done(function(respuesta){

      
      	$("#divreporte").html(respuesta.vista);


         

      });



       

    });


       $("#btnBuscarCompras").on("click", function() {
      	
       	
      var formulario = $("#frmReporte").serializeArray();
      $("#imgload").show();
      $(".botones").hide();
      $.ajax({
        type: "POST",
        dataType: 'json',
        url: '/reportecompras',
        data: formulario,
      }).done(function(respuesta){

      
      	$("#divreporte").html(respuesta.vista);


         

      });



       

    });

});
</script>



<section class="content">	
	<div class="row">
        <div class="col-xs-12">
        	<div class="box">	
        		<div class="box-header box-success" style="background-color:#337ab7;">
					<font color="white" size="3"><center><strong>REPORTE DE VENTAS</strong></center></font>
				</div>
<div class="box-body">
	{!!Form::open(array('url'=>'/reporteventas','autocomplete'=>'off','method'=>'POST','id'=>'frmReporte','role'=>'form','files'=>'true'))!!}
	{{Form::token()}}
	
	<div class="row">
		<div class="col-lg-2" >
			<div class="form-group form-group-sm">
				<label class="control-label">Negocios</label>
				<select class="form-control" name="suc_id" id="suc_id">

					@foreach($sucursal as $negocio)
				
					<option value="{{$negocio->id_empresa_negocio}}">{{$negocio->IdEmpresa}} - {{$negocio->tipo_negocio}}</option>
					

					@endforeach
				</select>
			</div>
		</div>
		<div class="col-lg-2">
			<div class="form-group form-group-sm">
				<label class="control-label" for="fecin">Desde </label>
				<input type="text" name="fec_ini" value="{{Carbon::now()->startOfMonth()->format('Y-m-d')}}" class="form-control">
			</div>
		</div>
		<div class="col-lg-2">
			<div class="form-group form-group-sm">
				<label class="control-label" for="fecfin">Hasta </label>
				<input type="text" name="fec_fin" value="{{Carbon::now()->endOfMonth()->format('Y-m-d')}}" class="form-control">
			</div>
		</div>

		<div hidden="hidden" class="col-lg-2">
			<div class="form-group form-group-sm">
				<label class="control-label">Tipo Reporte</label>
				<select name="tip_rep" class="form-control">
					<option value="4">VENTAS - CLIENTE</option>
				
				</select>
			</div>
		</div>
	


	</div>
	<div class="row">
		<div class="col-lg-6">
			<div class="btn-group" >
				<button type="button" id="btnBuscar" class=" btn btn-primary btn-sm">BUSCAR</button>
			</div>
			<div class="btn-group">
				<button type="button" id="btnExportar" dir="/reporteventasexcel" class="btn btn-primary btn-sm">Exportar Excel</button>
			</div>
			<div class="btn-group">
				<button type="button" id="btnGenPdf" dir="/generarreportepdf" class="btn btn-primary btn-sm">GENERAR REPORTE</button>	
			</div>
			<div class="btn-group">
				<button type="button" id="btnGenTicket" dir="/generar_reporte_ticket" class="btn btn-primary btn-sm">IMPRIMIR TICKET</button>		</div>
			</div>
		</div>






		{{Form::close()}}

	</div>
	        		
	           		
	           	</div>
	         </div>
	    </div>

	<div class="row">
		<div class="col-xs-12">
			<div class="box table table-responsive">
	            	<div class="box-body" id="divreporte">
							
					</div>	
				
				</div>
		</div>
	</div>
</section>




@endsection
