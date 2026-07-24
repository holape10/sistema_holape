<!doctype html>
<html lang="es">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
	       
	    <style>
			body {
				font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
				margin: 0;
				padding: 0;
				color: #2c3e50;
				background: #ffffff;
			}

			.page-break {
				page-break-after: always;
			}

			th, td {
			    padding: 6px;
			}

			/* Cabecera modernizada */
			.cabecera {
				position: absolute;
				top: -30px;
				width: 100%;
				background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
				border-bottom: 3px solid #007bff;
				padding: 15px 0;
			}

			.logo{
				position: absolute;
				top: 10px;
				left: 10px;
				width: 25%;
				filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
			}

			.panel-logo{
				position: absolute;
				top: 15px;
				left: 120px;
				width: 50%;
				text-align: center;
			}

			.panel-logo .empresa-principal {
				font-size: 16px;
				font-weight: 700;
				color: #2c3e50;
				margin-bottom: 5px;
			}

			.panel-logo .empresa-info {
				font-size: 9px;
				color: #6c757d;
				line-height: 1.4;
			}

			.panel-numeracion{
				position: absolute;
				top: 15px;
				right: 10px;
				width: 30%;
				text-align: center;
				background: white;
				border: 2px solid #007bff;
				border-radius: 15px;
				padding: 15px 10px;
				box-shadow: 0 4px 12px rgba(0,123,255,0.15);
			}

			.panel-numeracion .ruc-text {
				font-size: 11px;
				font-weight: 600;
				color: #495057;
				margin-bottom: 8px;
			}

			.comprobante{
				background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
				color: white !important;
				padding: 10px;
				border-radius: 8px;
				margin: 8px 0;
				font-weight: 600;
				font-size: 12px;
			}

			.numero-doc {
				font-size: 13px;
				font-weight: 700;
				color: #007bff;
				margin-top: 8px;
			}

			/* Cliente modernizado */
			.panel-cliente{
				position: relative;
				top: 120px;
				margin-bottom: 15px;
				padding: 12px;
				background: #f8f9fa;
				border-left: 4px solid #007bff;
				border-radius: 0 8px 8px 0;
			}

			.panel-cliente .cliente-label {
				font-weight: 600;
				color: #2c3e50;
				font-size: 8px;
			}

			.panel-cliente .cliente-value {
				color: #495057;
				font-size: 8px;
			}

			.detalle-cliente{
				position: relative;
				top: 120px;
				background: white;
				border: 2px solid #dee2e6;
				border-radius: 12px;
				padding: 15px;
				margin-bottom: 20px;
				box-shadow: 0 2px 8px rgba(0,0,0,0.08);
			}

			.detalle-cliente table {
				width: 100%;
				border-collapse: collapse;
			}

			.detalle-cliente th {
				background: #6c757d;
				color: white;
				padding: 10px 8px;
				font-size: 9px;
				font-weight: 600;
				border-radius: 6px;
			}

			.detalle-cliente td {
				padding: 10px 8px;
				font-size: 8px;
				text-align: center;
				border-bottom: 1px solid #dee2e6;
			}

			/* Tabla de detalles moderna */
			.detalle{
				position: relative;
				width: 100%;
				top: 120px;
				margin-bottom: 25px;
			}

			.table-detalle{
				border: 2px solid #dee2e6;
				border-radius: 12px;
				width: 100%;
				table-layout: fixed;
				min-height: 300px;
				overflow: hidden;
				box-shadow: 0 4px 12px rgba(0,0,0,0.08);
				border-collapse: separate;
				border-spacing: 0;
			}

			.table-detalle thead tr {
				background: linear-gradient(135deg, #495057 0%, #343a40 100%);
			}

			.table-detalle th {
				padding: 12px 8px;
				color: white !important;
				font-weight: 600;
				font-size: 10px;
				text-align: center;
				border-right: 1px solid rgba(255,255,255,0.2);
			}

			.table-detalle th:first-child {
				border-top-left-radius: 10px;
			}

			.table-detalle th:last-child {
				border-top-right-radius: 10px;
				border-right: none;
			}

			.table-detalle td {
				padding: 10px 8px;
				font-size: 8px;
				border-right: 1px solid #dee2e6;
				border-bottom: 1px solid #dee2e6;
				vertical-align: middle;
			}

			.table-detalle tbody tr:nth-child(even) {
				background-color: #f8f9fa;
			}

			.table-detalle tbody tr:hover {
				background-color: #e9ecef;
			}

			/* Sección de totales rediseñada */
			.totales{
				position: relative;
				top: 120px;
				background: white;
				border: 2px solid #dee2e6;
				border-radius: 15px;
				width: 100%;
				height: 180px;
				box-shadow: 0 6px 20px rgba(0,0,0,0.1);
				overflow: hidden;
			}

			.div-observacion{
				position: absolute;
				padding: 15px;
				top: 0px;
				left: 0px;
				width: 25%;
				height: 160px;
				border-right: 2px solid #dee2e6;
			}

			.letras{
				background: #f8f9fa;
				padding: 10px;
				border-radius: 8px;
				border-left: 4px solid #007bff;
				margin-bottom: 15px;
				font-size: 7px;
				color: #495057;
			}

			.letras-label {
				font-weight: 600;
				color: #2c3e50;
			}

			.qr{
				text-align: center;
				background: #f8f9fa;
				border: 1px solid #dee2e6;
				border-radius: 8px;
				padding: 8px;
			}

			.qr-label {
				font-size: 7px;
				font-weight: 600;
				color: #495057;
				margin-bottom: 5px;
			}

			.qr-subtitle {
				font-size: 6px;
				color: #6c757d;
				margin-top: 3px;
			}

			.div-aceptacion{
				position: absolute;
				text-align: center;
				padding: 0px;  /* Cambiar de -20px a 0px */
				top: 0px;      /* Cambiar de -30px a 0px */
				left: 25%;
				width: 45%;
				height: 160px;
				border-right: 2px solid #dee2e6;
				display: flex;
				flex-direction: column;
				justify-content: flex-start;
				align-items: center;
				padding-top: 5px;  /* Cambiar de -30px a 5px para un pequeño margen superior */
			}

			

			/* Sección QR de pago moderna */
			.qr-pago-section {
				background: white;
				border: 2px solid #007bff;
				border-radius: 12px;
				padding: 12px;
				text-align: center;
				box-shadow: 0 4px 12px rgba(0,123,255,0.15);
			}

			.qr-pago-title {
				font-size: 10px;
				font-weight: 700;
				color: #007bff;
				margin-bottom: 5px;
				text-transform: uppercase;
			}

			.qr-pago-subtitle {
				font-size: 7px;
				color: #6c757d;
				margin-bottom: 10px;
			}

			.qr-pago-info {
				font-size: 6px;
				color: #007bff;
				margin-top: 8px;
				font-weight: 600;
				line-height: 1.3;
			}

			.div-totales{
				position: absolute;
				padding: 15px;
				top: 0px;
				right: 0px;
				width: 30%;
				height: 160px;
				background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
			}

			.table-total {
				width: 100%;
				font-size: 9px;
			}

			.table-total tr {
				border-bottom: 1px solid #dee2e6;
			}

			.table-total tr:last-child {
				border-bottom: 2px solid #007bff;
				background: #f8f9fa;
			}

			.table-total td {
				padding: 6px 4px;
				vertical-align: middle;
			}

			.table-total .label-total {
				font-weight: 600;
				color: #2c3e50;
			}

			.table-total .monto-total {
				font-weight: 700;
				color: #007bff;
			}

			.tipo-comprobante{
				position: absolute;
				text-align: center;
				padding: 8px;
				top: 160px;
				left: 25%;
				width: 45%;
				background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
				color: black;
				border-radius: 8px;
				font-size: 7px;
				font-weight: 600;
				box-shadow: 0 2px 8px rgba(0,0,0,0.15);
			}


			.hash{
				position: relative;
				top: 100px;
				text-align: center;
				padding: 15px;
				background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
				border-radius: 8px;
				margin-top: 25px;
			}

			.hash p {
				font-size: 7px;
				color: #6c757d;
				margin: 0;
			}

			.hash a {
				color: #007bff;
				text-decoration: none;
				font-weight: 600;
			}

			/* Utilidades */
			.text-center { text-align: center; }
			.text-right { text-align: right; }
			.font-bold { font-weight: 600; }
	 	</style>
 	</head>
	<body>

		<div class="cabecera">
			<div class="logo">
				<img src="{{$sucursal->logosuc}}" style="padding-left:0px; height:auto;" width="140px">
			</div>
		
			<div class="panel-logo">
				@if(!empty($sucursal->nombre_comercial))
					<div class="empresa-principal">{{$sucursal->nombre_comercial}}</div>
					<div style="font-size:11px;color:#6c757d;margin-bottom:8px;font-weight:600;">{{$empresa->NomEmpresa}}</div>
				@else
					<div class="empresa-principal">{{$empresa->NomEmpresa}}</div>
				@endif

				@if(!empty($sucursal->descripcion1))
					<p class="empresa-info">{{$sucursal->descripcion1}}</p>
				@endif
				@if(!empty($sucursal->descripcion2))
					<p class="empresa-info">{{$sucursal->descripcion2}}</p>
				@endif

				<div class="empresa-info">
					{{$sucursal->direccion}}<br>
					{{$sucursal->departamento}} - {{$sucursal->provincia}} - {{$sucursal->distrito}}<br>
					@if(!empty($sucursal->celular)){{$sucursal->celular}}@endif 
					@if(!empty($sucursal->correo)){{$sucursal->correo}}@endif
				</div>
			</div>

			<div class="panel-numeracion">
				<div class="ruc-text">RUC {{$empresa->IdEmpresa}}</div>
				<div class="comprobante" style="background-color:#007bff;color:white;font-size:12px;font-weight:bold;">
					{{$cabpdf->tdodes}}
				</div>
				<div class="numero-doc">{{$cabpdf->serdoc}}-{{str_pad($cabpdf->numdoc,8,"0",STR_PAD_LEFT)}}</div>
			</div>
		</div>

		<div class="panel-cliente">
			<span class="cliente-label">Razón Social:</span> <span class="cliente-value">{{$cabpdf->ccanom}}</span>
			@if(!empty($cabpdf->clicorcli))
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<span class="cliente-label">Correo:</span> <span class="cliente-value">{{$cabpdf->clicorcli}}</span>
			@endif<br>
			<span class="cliente-label">{{$cabpdf->tdides}}:</span> <span class="cliente-value">{{$cabpdf->ccandi}}</span><br>
			<span class="cliente-label">Dirección:</span> <span class="cliente-value">{{$cabpdf->direccion}}</span>
		</div>

		<div class="detalle-cliente">
			<table>
				<thead>
					<tr>
						@if(!empty($referencia))
							<th>Doc. Ref.</th>
						@endif
						<th>Fecha Emisión</th>
						<th>Fecha Vencimiento</th>
						<th>Condición Pago</th>
						<th>Observacion</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						@if(!empty($referencia))
							<td>{{$referencia->des_doc}} - {{$referencia->serdoc}}-{{$referencia->numdoc}}</td>
						@endif
						<td>{{date('d-m-Y',strtotime($cabpdf->ccafem))}}</td>
						@if($cabpdf->tdocod !='07')
							<td>{{date('d-m-Y',strtotime($cabpdf->ccafve))}}</td>
						@else
							<td>-</td>
						@endif
						@if(!empty($cabpdf->estadopago))
							<td>{{$cabpdf->estadopago}}</td>
						@else
							<td>-</td>
						@endif
						<td>{{$cabpdf->ccaobs}}</td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="detalle">
			<table class="table-detalle">
				<thead>
					<tr style="background-color:#495057;">
						<th style="width:10%;color:white;font-size:10px;font-weight:bold;padding:12px 8px;text-align:center;">CANT.</th>
						<th style="width:50%;color:white;font-size:10px;font-weight:bold;padding:12px 8px;text-align:center;">DESCRIPCIÓN</th>
						<th style="width:10%;color:white;font-size:10px;font-weight:bold;padding:12px 8px;text-align:center;">U.D.M</th>
						<th style="width:15%;color:white;font-size:10px;font-weight:bold;padding:12px 8px;text-align:center;">P.U</th>	
						<th style="width:15%;color:white;font-size:10px;font-weight:bold;padding:12px 8px;text-align:center;">TOTAL</th>
					</tr>
				</thead>
				<tbody>
					@foreach($detpdf as $det)
					<tr>
						<td class="text-center">{{$det->cdecan}}</td>
						<td>{{$det->cdedes}}</td>
						<td class="text-center">{{$det->umecod}}</td>
						<td class="text-right">{{number_format($det->cdepuni,'2','.',',')}}</td>						
						<td class="text-right">{{number_format($det->cdevve,'2','.',',')}}</td>
					</tr>
					@endforeach
				</tbody>
			</table>
			
			@if(count($cuotas)>0)
				<br>
				<table class="table-detalle" style="width:60%">
					<thead>
						<tr style="background-color:#495057;">
							<th colspan="4" style="color:white;font-size:10px;font-weight:bold;padding:12px 8px;text-align:center;">CUOTAS</th>
						</tr>
						<tr style="background-color:#495057;">
							<th style="width:10%;color:white;font-size:10px;font-weight:bold;padding:12px 8px;text-align:center;">#</th>
							<th style="color:white;font-size:10px;font-weight:bold;padding:12px 8px;text-align:center;">FECHA VENCIMIENTO</th>
							<th style="color:white;font-size:10px;font-weight:bold;padding:12px 8px;text-align:center;">MONEDA</th>
							<th style="color:white;font-size:10px;font-weight:bold;padding:12px 8px;text-align:center;">MONTO</th>
						</tr>
					</thead>
					<tbody>
						@foreach($cuotas as $cuo)
						<tr>
							<td class="text-center">{{$cuo->ven_cuo_num}}</td>
							<td class="text-center">{{$cuo->ven_cuo_fec_ven}}</td>
							<td class="text-center">{{$cabpdf->moncod}}</td>
							<td class="text-right">{{number_format($cuo->ven_cuo_mon,'2','.',',')}}</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			@endif
		</div>

		<div class="totales">
			<div class="div-observacion">
				@if(!empty($totalletras))
				<div class="letras">
					<span class="letras-label">SON:</span> {{$totalletras}} {{$cabpdf->monnom}}
				</div>
				@endif
				
				<div class="qr">
					
					<img src="{{$imgqr}}" width="60px" height="60px" style="border:1px solid #ddd;border-radius:4px;">
					
				</div>
			</div>

			<div class="div-aceptacion">
				<div style="text-align:center;padding:8px;">
					
					
					
					
				</div>
			</div>

			<div class="tipo-comprobante">
				REPRESENTACIÓN IMPRESA DE LA {{$cabpdf->tdodes}}
			</div>

			<div class="div-totales">
				<table class="table-total">
					<tr>	
						<td class="label-total">SUBTOTAL</td>
						<td style="width:15%;">@if($cabpdf->moncod=='USD') $ @else S/ @endif</td>
						<td class="text-right">{{number_format($cabpdf->ccatexo+$cabpdf->ccatvg,'2','.',',')}}</td>
					</tr>
					<tr>	
						<td class="label-total">OP. GRAVADA</td>
						<td>@if($cabpdf->moncod=='USD') $ @else S/ @endif</td>
						<td class="text-right">{{number_format($cabpdf->ccatvg,'2','.',',')}}</td>
					</tr>
					<tr style="display:none;">	
						<td class="label-total">OP. EXONERADA</td>
						<td>@if($cabpdf->moncod=='USD') $ @else S/ @endif</td>
						<td class="text-right">{{number_format($cabpdf->ccatexo,'2','.',',')}}</td>
					</tr>
					<tr>	
						<td class="label-total">IGV</td>
						<td>@if($cabpdf->moncod=='USD') $ @else S/ @endif</td>
						<td class="text-right">{{number_format($cabpdf->ccaigv,'2','.',',')}}</td>
					</tr>
					<tr>
						<td class="label-total monto-total">IMPORTE TOTAL</td>
						<td class="monto-total">@if($cabpdf->moncod=='USD') $ @else S/ @endif</td>
						<td class="text-right monto-total">{{number_format($cabpdf->ccaitv,'2','.',',')}}</td>
					</tr>
				</table>
			</div>
		</div>

		<div class="hash">
			<p><strong>"BIENES TRANSFERIDOS EN LA AMAZONIA PARA SER CONSUMIDAS EN LA MISMA" - "SERVICIOS PRESTADOS EN LA AMAZONIA" <br> SISTEMA DESARROLLADO POR <a href="https://web.holape.app" target="_blank">HOLAPE</a> - 928 396 147 - SI DESEA EL SISTEMA LLAMAR AL 928 396 147</strong></p>
		</div>
	</body>
</html>