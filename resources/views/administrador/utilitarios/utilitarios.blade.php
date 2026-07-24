@extends('layouts.empresas')
@section('contenido')

     @include('administrador.utilitarios.modalimportarproductos')
     @include('administrador.utilitarios.modalimportarpresentaciones')
     @include('administrador.utilitarios.modalimportarclientes')
     @include('administrador.utilitarios.modalimportarproveedores')

<script>
$(document).ready(function()
{       

		

	      		$("#cmbCatId").change(function() {
         
	                var cat_id = $("#cmbCatId").val();
	                $("#subcat_id").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
	                $.ajax({
	                  type: "GET",
	                  dataType: 'json',
	                  url: "/buscarsubcategorias/"+cat_id,

	                }).done(function(respuesta){
	                $("#subcat_id").html(respuesta.vista);
	               
	                });

	                var subcat_id = $("#subcat_id").val();
		                $("#tip_pro_id").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
		                $.ajax({
		                  type: "GET",
		                  dataType: 'json',
		                  url: "/buscartipos/"+subcat_id,

		                }).done(function(respuesta){
		                $("#tip_pro_id").html(respuesta.vista);
		               
		                });
		        
		        

                 });


                $("#subcat_id").change(function() {
         
                var subcat_id = $("#subcat_id").val();
                $("#tip_pro_id").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
                $.ajax({
                  type: "GET",
                  dataType: 'json',
                  url: "/buscartipos/"+subcat_id,

                }).done(function(respuesta){
                $("#tip_pro_id").html(respuesta.vista);
               
                });
        

                 });

          


   

 });

</script>
<section class="content">
	

<div class="row">
    <div class="col-xs-12">
    	<div class="box">
    		<div class="box-header" style="background-color:blue;">
          				<font color="white"><center><strong>UTILITARIOS DEL SISTEMA</strong></center></font>
          				
          			</div>
	       	<div class="box-body">
			<table class="table table-striped table-bordered table-condensed table-hover">
				<thead>
					<th><center>N°</center></th>
					<th><center>DESCRIPCION</center></th>
					
					<th><center>NOTA</center></th>
					<th><center>FORMATO</center></th>
					<th><center>OPCIONES</center></th>
				</thead>
				
				<tbody>
					<tr>
						<td><center><strong>1</strong></center></td>
						<td><FONT><strong>IMPORTAR PRODUCTOS</strong></FONT></td>
						
						<td width='40%'><p>AL EJECUTAR ESTA OPCION SE REGISTRARÁN TODOS LOS PRODUCTOS QUE SON IMPORTADOS EN EL EXCEL. ESTA OPCIÓN ES UTILIZADA PARA LA PRIMERA CARGA DE PRODUCTOS</p></td>
						<td><a class="btn btn-block btn-primary" href="/descargarformato/1">DESCARGAR</a></td>
						<td><a href=""  data-target="#modal-importar-productos" data-toggle="modal"><button class="btn btn-block btn-sm btn-warning">EJECUTAR</button></a></td>
					</tr>
					<tr>
						<td><center><strong>2</strong></center></td>
						<td><FONT><strong>IMPORTAR PRESENTACIONES</strong></FONT></td>
					
							<td width='40%'><p>AL EJECUTAR ESTA OPCION SE REGISTRARÁN LOS PRESENTACIONES DE LOS PRODUCTOS QUE SON IMPORTADOS EN EL EXCEL DE LA OPCION IMPORTAR PRODUCTOS. ESTA OPCIÓN ES UTILIZADA PARA LA PRIMERA CARGA DE PRESENTACIONES</p></td>
								<td><a class="btn btn-block btn-primary" href="/descargarformato/2">DESCARGAR</a></td>
						<td <a href="" data-target="#modal-importar-presentaciones" data-toggle="modal"><button class="btn btn-block btn-warning">EJECUTAR</button></a></td>
					</tr>
					<tr>
						<td><center><strong>3</strong></center></td>
						<td><FONT><strong>IMPORTAR CLIENTES</strong></FONT></td>
						
						<td width='40%'><p>AL EJECUTAR ESTA OPCION SE REGISTRARÁN LOS CLIENTES QUE SON IMPORTADOS EN EL EXCEL. ESTA OPCIÓN ES UTILIZADA PARA LA PRIMERA CARGA DE CLIENTES</p></td>
						<td><a class="btn btn-block btn-primary" href="/descargarformato/3">DESCARGAR</a></td>
						<td><a href="" data-target="#modal-importar-clientes" data-toggle="modal"><button class="btn btn-block btn-sm btn-warning">EJECUTAR</button></a></td>
					</tr>
					<tr>
						<td><center><strong>4</strong></center></td>
						<td><FONT><strong>IMPORTAR PROVEEDORES</strong></FONT></td>
					
							<td width='40%'><p>AL EJECUTAR ESTA OPCION SE REGISTRARÁN LOS PROVEEDORES QUE SON IMPORTADOS EN EL EXCEL. ESTA OPCIÓN ES UTILIZADA PARA LA PRIMERA CARGA DE PROVEEDORES</p></td>
								<td><a class="btn btn-block btn-primary" href="/descargarformato/4">DESCARGAR</a></td>
						<td><a href="" data-target="#modal-importar-proveedores" data-toggle="modal"><button class="btn btn-block btn-sm btn-warning">EJECUTAR</button></a></td>
					</tr>
					<tr>
						<td><center><strong>5</strong></center></td>
						<td><FONT><strong>REGISTRO DE COMPROBANTES</strong></FONT></td>
					
							<td width='40%'><p>ESTA OPCION TE PERMITIRÁ PODER ACTIVAR NUEVAMENTE EL COMPROBANTE ELECTRÓNICO PARA PODER REENVIAR A LA SUNAT EN CASO SE ENCUENTRE DESACTIVADO EN LA OPCIÓN DE ENVÍO DE COMPROBANTES.</p>
							<p>ESTA OPCION TE PERMITIRÁ PODER CAMBIAR EL ESTADO EN SUNAT DEL COMPROBANTE EN CASO SE REQUIERA. POR EJEMPLO: CUANDO LA SUNAT DEVUELVE EL CODIGO 1033 (EL COMPROBANTE FUE ENVIADO ANTERIORMENTE), ESTA OPCIÓN SE UTILIZARÁ PREVIAMENTE VERIFICANDO SI EL DOCUMENTO EXISTE EN SUNAT.</p>
						</td>
								<td></td>
						<td><a href="/utilitarios/buscarcomprobantes" ><button class="btn btn-block btn-sm btn-warning">EJECUTAR</button></a></td>
					</tr>0
				
				</tbody>
				

			
			</table>
		</div>
	
	</div>
</div>
</div>
</section>
@endsection
