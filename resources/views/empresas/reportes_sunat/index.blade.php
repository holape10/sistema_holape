@extends('layouts.empresas')
@section('contenido')

<script type="text/javascript">
	
	$(document).ready(function()
   {


   
   	  $("#btnVentastxt").click(function() {
        var accion = $(this).attr('dir');

        $('#frmReporte').attr('action', accion);
        $('#frmReporte').submit();
    });


   	  $("#btnComprastxt").click(function() {
        var accion = $(this).attr('dir');

        $('#frmReporte').attr('action', accion);
        $('#frmReporte').submit();
    });

   






});
</script>


<section class="content">	
	<div class="row">
        <div class="col-xs-12">
        	<div class="box">
        		<div class="box-header box-success" style="background-color:#00a65a;">
        			<font color="white" size="4"><center><strong>SUNAT - GENERAR TXT</strong></center></font>
        		</div>
	           	<div class="box-body">
	           		
 {!!Form::open(array('url'=>'/buscarreportepdf','autocomplete'=>'off','method'=>'POST','id'=>'frmReporte','role'=>'form','files'=>'true'))!!}
    {{Form::token()}}

<div class="row">
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
			 <label class="control-label" for="mes">MES - AÑO </label>
			 <input type="month" name="mes" class="form-control">
			
		</div>
	</div>




</div>
<div class="row">
	<div class="col-lg-6">
	
		<div class="btn-group">
			<button type="button" id="btnVentastxt" dir="/reportesunat/venta/generar" class="btn btn-primary btn-sm">Ventas TXT</button>
		</div>
		<div class="btn-group">
			<button type="button" id="btnComprastxt" dir="/reportesunat/compra/generar" class="btn btn-primary btn-sm">Compras TXT</button>	
		</div>
			
</div>


{{Form::close()}}

	           	</div>
	         </div>
	    </div>
	</div> 

	
</section>



@endsection