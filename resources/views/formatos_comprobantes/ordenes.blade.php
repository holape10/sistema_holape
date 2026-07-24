<!doctype html>
	<html lang="es">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<style>


			.page-break {
				page-break-after: always;
			}


			.cabecera {
				position:absolute;
				top: -30px;
				width:90%;

			}

			.logo{
				position:absolute;
				top:0px;
				left:0px;
				width:90%;

			}

			.panel-logo{
				position:relative;
				top:0px;
				left:170px;
				width:90%;


			}


			.panel-cliente{
				position: relative;
				top:90px;
				border: 0px 0px 0px 0px solid  black;
				padding: 10px;
				border-radius: 0px 0px 0px 0px;
			}

			.detalle-cliente{
				position: relative;
				top:120px;
				border: 1px 1px 1px 1px solid  black;
				padding: 10px;
				border-radius: 10px 10px 10px 10px;
			}


			.detalle{
				position: relative;
				margin-left:-35px;
				margin-right:-20px;
				width:100%;
				top:20px;
			}


			.montoletras{
				position: absolute;
				top:730px;
				border: 1px 1px 1px 1px solid  black;
				padding: 10px;
				border-radius: 5px 5px 5px 5px;
				width: 100%;
			}

			.totales{

				position: relative;
				top:170px;
				width:90%;
				height:150px;
			}

			.hash{
				position: relative;
				top:120px;
			}


			.div-observacion{
				position:absolute;
				padding: 10px;
				top:0px;
				left:0px;
				width:30%;
				height:140px;

			}


			.border-table {
				border: 1px solid black;
				padding:5px;
			}

			table {

				border-collapse: collapse;
			}

			.div-aceptacion{
				position:absolute;
				text-align:center;
				align-content: center;
				padding: 10px;
				top:10px;
				left:163px;
				width:45%;
				height:140px;

			}

			.div-totales{
				position:relative;
				padding: 10px;
				top:190px;
				left: 465px;
				width:15%;
				height:140px;

			}


			.table-detalle{
				position: relative;
				border:0px 0px 0px 0px solid  black;
				border-radius:0px;
				width: 100%;
				table-layout:fixed;
				min-height:900px;
			}



			.comprobante{
				text-align:center;
				background-color:gray;
				width:90%;
				height:40px;

			}

			.qr{
				position:absolute;
				top:25px;
				left:5px;
				width:50%;
			}


			.letras{
				position:absolute;
				top:10px;
				left:5px;
				width:90%;
			}

			.tipo-comprobante{
				position:absolute;
				text-align:center;
				align-content: center;
				padding: 10px;
				top:120px;
				left:140px;
				width:60%;
				height:20px;
			}


			.observacion{
				position:absolute;
				top:25px;
				left:120px;
				width:60%;
				text-align:justify;			
			}




		</style>
	</head>

	<body  style="font-family:Helvética,sans-serif;font-size:8pt;">



		<div class="cabecera">
	<!--	<script type="text/php">
			if ( isset($pdf) ) {
			$pdf->page_script('
			$font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
			$pdf->text(270, 10, "Pagina $PAGE_NUM de $PAGE_COUNT", $font, 10);
			$pdf->text(480, 10, "{{now()->format('d/m/Y H:i:s')}}", $font, 10);
			');
		}
	</script>-->



	<table style="width:100%;">
		<thead>
	
			<tr>
				<th  style="text-align:left;width:100px;padding-bottom:5px;">{{$empresa->NomEmpresa}}</th>
			 	<th  style="text-align:left;width:100px;padding-bottom:5px;">{{$sucursal->web}}</th>
			 
			</tr>
			<tr >
				<th  style="text-align:left;width:100px;padding-bottom:5px;">{{$sucursal->direccion}}</th>
				<th  style="text-align:left;width:100px;padding-bottom:5px;">{{$sucursal->correo}}</th>
			
			</tr>

		</thead>
	</table>

 

 
	<br><br>
	<hr style="width:770px;margin-left:-30px;">
	<br>
	
	<table style="width:100%;">
		<thead>
				<tr>
				<th  style="text-align:left;padding-bottom:5px;width:350px;">N° Orden: {{$cabpdf->serdoc}}-{{$cabpdf->numdoc}} </th>
				<th  style="text-align:left;padding-bottom:5px;width:200px;">Fecha: {{$cabpdf->fechacot}}</th>
				<th  style="text-align:left;padding-bottom:5px;width:200px;"></th>
			</tr>
			<tr>
				<th  style="text-align:left;padding-bottom:5px;width:350px;">Cliente: {{$cabpdf->ccanom}} </th>
				<th  style="text-align:left;padding-bottom:5px;width:200px;">Tipo Documeto: {{$cabpdf->tdides}}</th>
				<th  style="text-align:left;padding-bottom:5px;width:200px;">N° Documento: {{$cabpdf->ccandi}}</th>
			</tr>
			<tr >
				<th  style="text-align:left;padding-bottom:5px;width:350px;">Direcci&oacute;n : {{$cabpdf->direccion}}</th>
				<th  style="text-align:left;padding-bottom:5px;width:200px;">Tel&eacute;fono : {{$cabpdf->telefono}}</th>
				<th  style="text-align:left;padding-bottom:5px;width:200px;">Contacto:</th>
			</tr>
		</tr>
		<tr>
			<th  style="text-align:left;padding-bottom:5px;width:350px;">Comprobante Cliente : {{$cabpdf->doc_gar}}</th>
			<th  style="text-align:left;padding-bottom:5px;width:200px;">Fecha: {{$cabpdf->fec_doc_gar}}</th>
			<th  style="text-align:left;padding-bottom:5px;width:200px;"></th>
		</tr>
		<tr>
			<th  style="text-align:left;padding-bottom:5px;width:350px;">Fecha Visita :</th>
			<th  style="text-align:left;padding-bottom:5px;width:200px;">Hora Visita:</th>
			<th  style="text-align:left;padding-bottom:5px;width:200px;"></th>
		</tr>
	</thead>
</table>

<br>
<hr style="width:770px;margin-left:-30px;">
<br>



<table style="width:100%;">
	<thead>
		<tr>
			<th  style="text-align:left;padding-bottom:5px;width:150px;">Marca </th>
			<th  style="text-align:left;padding-bottom:5px;width:150px;">Modelo</th>
			<th  style="text-align:left;padding-bottom:5px;width:150px;">Serie</th>
			<th  style="text-align:left;padding-bottom:5px;width:150px;">Indicador</th>
			<th  style="text-align:left;padding-bottom:5px;width:150px;">Linea</th>
		</tr>
		<tr >
			<td  style="text-align:left;padding-bottom:5px;width:150px;">{{$cabpdf->mar_nom}} </td>
			<td  style="text-align:left;padding-bottom:5px;width:150px;">{{$cabpdf->mod_nom}}</td>
			<td  style="text-align:left;padding-bottom:5px;width:150px;">{{$cabpdf->equi_ser}}</td>
			<td  style="text-align:left;padding-bottom:5px;width:150px;">{{$cabpdf->situ_des}}</td>
			<td  style="text-align:left;padding-bottom:5px;width:150px;">{{$cabpdf->cat_nom}}</td>
		</tr>

	</thead>
</table>

<br>
<hr style="width:770px;margin-left:-30px;">
<br>

<table style="width:100%;">
	<thead>
		<tr>
			<th  style="text-align:left;padding-bottom:5px;width:150px;">Aperturado por: </th>
			<th  style="text-align:left;padding-bottom:5px;width:600px;">{{$cabpdf->nom_tec}} {{$cabpdf->ape_tec}}</th>

		</tr>

	</thead>
</table>

<br>
<hr style="width:770px;margin-left:-30px;">
<br>


<table style="width:100%;">
	<thead>
		<tr>
			<th  style="text-align:left;padding-bottom:5px;width:750px;">S&iacute;ntoma: </th>
		</tr>
		<tr>
			<th  style="text-align:left;padding-bottom:5px;width:750px;font-weight:normal;">{{$cabpdf->observaciones}}</th>
		</tr>
		<tr>
			<th  style="text-align:left;padding-bottom:5px;width:750px;">Estado F&iacute;sico: </th>
		</tr>
		<tr>
			<th  style="text-align:left;padding-bottom:5px;width:750px;font-weight:normal;">{{$cabpdf->est_fis}}</th>
		</tr>
	</thead>
</table>
<br>
<hr style="width:770px;margin-left:-30px;">
<br>

<table style="width:100%;">
	<thead>
		<tr>
			<th  style="text-align:left;padding-bottom:5px;width:750px;">T&eacute;cnico: </th>


		</tr>
		<tr>

			<th  style="text-align:left;padding-bottom:5px;width:750px;font-weight:normal;">{{$cabpdf->nom_tec}} {{$cabpdf->ape_tec}}</th>

		</tr>

	</thead>
</table>
<br>
<hr style="width:770px;margin-left:-30px;">
<br>


<table style="width:100%;">
	<thead>
		<tr>
			<th  style="text-align:left;padding-bottom:5px;width:750px;">Informe T&eacute;cnico: </th>
		</tr>
		<tr>
			<th  style="text-align:left;padding-bottom:5px;width:750px;font-weight:normal;">{{$cabpdf->obs_cli}}</th>

		</tr>

	</thead>
</table>

<br>
<hr style="width:770px;margin-left:-30px;">
<br>


<table style="width:100%;" class="table-repuestos">
	<tbody>
		<tr>
			<td  style="text-align:left;padding-bottom:5px;width:250px;color:white;background:gray;font-weight: bold;">Mano de Obra </td>
			<td  style="text-align:left;padding-bottom:5px;width:150px;color:white;background:gray;font-weight: bold;">Cantidad </td>
			<td  style="text-align:left;padding-bottom:5px;width:150px;color:white;background:gray;font-weight: bold;">Precio Unitario</td>
			<td  style="text-align:left;padding-bottom:5px;width:150px;color:white;background:gray;font-weight: bold;">Valor Venta </td>
		</tr>
		@foreach($servicios as $serv)
		<tr>
			<td>{{$serv->pronom}}</td>
			<td>{{$serv->cdecan}}</td>
			<td>{{$serv->cdepuni}}</td>
			<td>{{$serv->cdevve}}</td>
		</tr>
		@endforeach

	</tbody>
</table>

<br><br>

<table style="width:100%;" class="table-repuestos">
	<tbody>
		<tr>
			<td  style="text-align:left;padding-bottom:5px;width:250px;color:white;background:gray;font-weight: bold;">Repuesto</td>
			<td  style="text-align:left;padding-bottom:5px;width:150px;color:white;background:gray;font-weight: bold;">Cantidad </td>
			<td  style="text-align:left;padding-bottom:5px;width:150px;color:white;background:gray;font-weight: bold;">Precio Unitario</td>
			<td  style="text-align:left;padding-bottom:5px;width:150px;color:white;background:gray;font-weight: bold;">Valor Venta </td>
		</tr>
		@foreach($servicios as $serv)
		<tr>
			<td>{{$serv->pronom}}</td>
			<td>{{$serv->cdecan}}</td>
			<td>{{$serv->cdepuni}}</td>
			<td>{{$serv->cdevve}}</td>
		</tr>
		@endforeach

	</tbody>
</table>
<br><br>
<table style="width:100%;" class="table-repuestos">
	<tbody>
		<tr>
			<td  style="text-align:left;padding-bottom:5px;width:250px;color:white;background:gray;font-weight: bold;">TOTAL VALOR DE VENTA</td>
			<td  style="text-align:left;padding-bottom:5px;width:150px;color:white;background:gray;font-weight: bold;">IGV </td>
			<td  style="text-align:left;padding-bottom:5px;width:150px;color:white;background:gray;font-weight: bold;">TOTAL</td>

		</tr>

		<tr>
			<td>{{$cabpdf->ccatvg}}</td>
			<td>{{$cabpdf->ccaigv}}</td>
			<td>{{$cabpdf->ccaitv}}</td>
		</tr>
		
	</tbody>
</table>

<table style="width:100%;" class="table-repuestos">
	<tbody>
		<tr>
			<td  style="text-align:left;padding-bottom:5px;width:250px;color:white;background:gray;font-weight: bold;">TOTAL VALOR DE VENTA</td>
			<td  style="text-align:left;padding-bottom:5px;width:150px;color:white;background:gray;font-weight: bold;">IGV </td>
			<td  style="text-align:left;padding-bottom:5px;width:150px;color:white;background:gray;font-weight: bold;">TOTAL</td>

		</tr>

		<tr>
			<td>{{$cabpdf->ccatvg}}</td>
			<td>{{$cabpdf->ccaigv}}</td>
			<td>{{$cabpdf->ccaitv}}</td>
		</tr>
		
	</tbody>
</table>


<br><br><br>
<table style="width:100%;" class="table-repuestos">
	<tbody>
	
			<tr>
			<td  style="text-align:left;padding-bottom:5px;width:200px;">Nombre:<BR>DNI:</FONT></td>
			<td  style="text-align:left;padding-bottom:5px;width:250px;"></FONT></td>
			<td  style="text-align:left;padding-bottom:5px;width:250px;"></FONT></td>
		</tr>
			<tr>
				<td  style="text-align:left;padding-bottom:5px;width:200px;"></FONT></td>
			<td  style="text-align:left;padding-bottom:5px;width:250px;text-align:center;">___________________________<BR>Firma del Cliente</td>
			<td  style="text-align:left;padding-bottom:5px;width:250px;text-align:center;">___________________________<BR>Por {{$empresa->NomEmpresa}}</td>
		</tr>
	</tbody>
</table>

</div>





</body>
</html>