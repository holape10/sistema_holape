@extends('layouts.empresas')
@section('contenido')

<section class="content">	
	<div class="row">
        <div class="col-xs-12">
        	<div class="box">
        		<div class="box-header box-success" style="background-color:#585858;">
        			<strong><font color="white"><center>REPORTES DE AUDITORIA</center></font> </strong>
        		</div>
	           	<div class="box-body">
	           		@include('empresas.auditoria.buscarauditoria')
	           	</div>
	         </div>
	    </div>
	</div> 
</section>


<script>

$( document ).ready(function() {
	$("#fecin").change(function(){
	  var fechain = $("#fecin").val();
	 $('#fechain').val(fechain);
	 
    
	});

	$("#fecfin").change(function(){
		var fechafin = $("#fecfin").val();
		$('#fechafin').val(fechafin);
	});

	
});

$('#btnPrint').click(function(){
		
		var fecfin = $("#fechafin").val();
        var fecin = $("#fechain").val();
		 
		$("#btnPrint").printPage({
		  
          url: "/imprimircierre/"+fecfin+"/"+fecin,
          attr: "href",
          messageBox:false
          
        })
		
	});

</script>

@endsection