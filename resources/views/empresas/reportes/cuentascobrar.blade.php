@extends('layouts.empresas')
@section('contenido')
 <style type="text/css">
        body
        {
            font-family: Arial;
            font-size: 10pt;
        }
        table
        {
            border: 1px solid #ccc;
            border-collapse: collapse;
        }
        table th
        {
            background-color: #F7F7F7;
            color: #333;
            font-weight: bold;
        }
        table th, table td
        {
            padding: 5px;
            border: 1px solid #ccc;
        }
    </style>
<script type="text/javascript">

	$( document ).ready(function() {
   
	$("#checkAll").click(function () {
    	$('.items').not(this).prop('checked', this.checked);
	});

});

</script>

<section class="content">
	<div class="row">
		<div class="col-xs-6">
			<div class="box">
				<div class="box-header" style="background:#337ab7;">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<font style="color:white;font-size:10pt;"><strong><center>CUENTAS POR COBRAR - VENDEDOR</center></strong></font>
						</div>
				</div>
				<div class="box-body">
					@include('empresas.reportes.buscarcuentacobrarvendedores')
				</div>
						
						
				
			</div>
		</div>
		<div class="col-xs-6">
			<div class="box">
				<div class="box-header" style="background:#337ab7;">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<font style="color:white;font-size:10pt;"><strong><center>CUENTAS POR COBRAR - CLIENTE</center></strong></font>
						</div>
				</div>
				<div class="box-body">
					@include('empresas.reportes.buscarcuentacobrarclientes')
				</div>
						
						
				
			</div>
		</div>
		<div class="col-xs-6">
			<div class="box">
				<div class="box-header" style="background:#337ab7;">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<font style="color:white;font-size:10pt;"><strong><center>REPORTE DE COBRANZAS POR VENDEDOR</center></strong></font>
						</div>
				</div>
				<div class="box-body">
					@include('empresas.reportes.buscarcobranzasvendedores')
				</div>
						
						
				
			</div>
		</div>
		<div class="col-xs-6">
			<div class="box">
				<div class="box-header" style="background:#337ab7;">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<font style="color:white;font-size:10pt;"><strong><center>REPORTE DE COBRANZAS POR CLIENTE</center></strong></font>
						</div>
				</div>
				<div class="box-body">
					@include('empresas.reportes.buscarcobranzasclientes')
				</div>
						
						
				
			</div>
		</div>

	</div>

</section>
@endsection
