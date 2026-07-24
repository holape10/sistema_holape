@extends('layouts.empresas')
@section('contenido')

<style>
#b1
{
	/*sirve para los caracteres cuando es una palabra grande se salte a la otra linea */
	white-space: normal;
}
#scroll
{
	height: 650px;
	width: 800px;
	overflow: scroll;
}
</style>

<body>


	<script>

		$(document).ready(function()
		{



			calculartotal();
			$("#btnAgregar").on('click',function(){
				var opcion = $('#ins_id  option:selected').val();
				var cont = 0, cantidad=0,total=0;
				$.ajax({
						type: 'get',
						url: '/buscarproducto',
						dataType: 'json',
						data: {'value' : opcion },
						success : function(data) {

							if($('#grdet >tbody >tr').length > 0){
								$("#grdet tbody tr").each(function(){
									var codigo = $(this).find("td:eq(0) > input").val();
									if( opcion == codigo){
										cont = cont+1;
										cantidad = parseFloat($(this).find("td:eq(2) > input").val())+1;
										
									}

									if(cont >0){
										$(this).find("td:eq(2) > input").val(cantidad);
									
										
										return false;
									}


								})

								if(cont == 0){
									
									$('#grdet').append("<tr><td hidden='hidden'><input type='text' class='form-control' name='prod_comb_id[]'  value='"+data[0].prod_id+"' readonly='readonly' style='width:20px' ></td><td><input type='text' class='form-control input-sm' name='prod_nom[]' value='"+data[0].value+"' style='width:600px' readonly='readonly'></td><td width='60'> <input type='text' value='1' name='cantidad[]' onChange='calculartotal();' onkeyup='calculartotal();' class='form-control input-sm' id='font-size' style='width:60px'> </td><td width='60'> <input type='text' value='"+data[0].unidad+"' readonly='readonly' name='unidadmedida[]' onChange='calculartotal();' onkeyup='calculartotal();' class='form-control readonly='readonly' input-sm' id='font-size' style='width:150px'> </td><td width='60'> <input type='text' value='"+data[0].costo+"' readonly='readonly' name='costo[]' onChange='calculartotal();' onkeyup='calculartotal();' class='form-control readonly='readonly' input-sm' id='font-size' style='width:80px'> </td><td width='60'> <input type='text' value='"+data[0].precio+"' readonly='readonly' name='precio[]' onChange='calculartotal();' onkeyup='calculartotal();' class='form-control readonly='readonly' input-sm' id='font-size' style='width:80px'> </td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");

								
								}
								calculartotal();
							}else{

								$('#grdet').append("<tr><td hidden='hidden'><input type='text' class='form-control' name='prod_comb_id[]'  value='"+data[0].prod_id+"' readonly='readonly' style='width:20px' ></td><td><input type='text' class='form-control input-sm' name='prod_nom[]' style='width:600px' value='"+data[0].value+"' readonly='readonly'></td><td width='60'> <input type='text' value='1' name='cantidad[]' onChange='calculartotal();' onkeyup='calculartotal();' class='form-control input-sm' id='font-size' style='width:60px'> </td><td width='80'> <input type='text' value='"+data[0].unidad+"' name='unidadmedida[]' onChange='calculartotal();' onkeyup='calculartotal();' class='form-control input-sm' id='font-size' readonly='readonly' style='width:150px'> </td><td width='60'> <input type='text' value='"+data[0].costo+"' readonly='readonly' name='costo[]' onChange='calculartotal();' onkeyup='calculartotal();' class='form-control  readonly='readonly' input-sm' id='font-size' style='width:80px'> </td><td width='60'> <input type='text' value='"+data[0].precio+"' readonly='readonly' name='precio[]' onChange='calculartotal();' onkeyup='calculartotal();' class='form-control readonly='readonly' input-sm' id='font-size' style='width:80px'> </td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");
								calculartotal();
							}



						

						}


					})

					calculartotal();
				
			});

			
		});

			function deleteRow(btn) {
			var row = btn.parentNode.parentNode;
			row.parentNode.removeChild(row);
			calculartotal();
		};


	function calculartotal(){

   

   var totalcosto=0,totalprecio=0;
   var $svalor=0;
    $("#grdet tbody tr").each(function(){

       totalcosto = totalcosto + parseFloat($(this).find("td:eq(2)  > input").val() * $(this).find("td:eq(4)  > input").val());
        totalprecio = totalprecio + parseFloat($(this).find("td:eq(2)  > input").val() * $(this).find("td:eq(5)  > input").val());
   	 
      
      
   });

     $('#totalcosto').val(totalcosto.toFixed(2));
          $('#preciosugerido').val(totalprecio.toFixed(2));

    if ($('#grdet >tbody >tr').length == 0){

       $('#totalcosto').val($svalor.toFixed(2));
        $('#preciofinal').val($svalor.toFixed(2));
       $('#preciosugerido').val($svalor.toFixed(2));
      
    };

 

}

	</script>


</br>


<div class="container-fluid">
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
				<strong>Información!</strong> {{ session('success') }}
			</div>
			@endif
		</div>
	</div>
	
	<div class="row">
		{!!Form::open(array('url'=>'/registrarcombo','autocomplete'=>'off','method'=>'POST','name'=>'formreceta','id'=>'formreceta','role'=>'form','files'=>'true'))!!}
		{{Form::token()}}
		<div class="col-lg-12">
			<div class="box">
				<div style="background-color:#0040FF;" class="box-header with-border">
					<center><font color="white"><strong>REGISTRAR COMBO</strong></font></center>
					<div class="box-tools pull-right">   
						<button class="btn btn-sm btn-success btn-sm" type="submit" id="btnRegComp" name="btnRegComp"><strong>Registrar</strong></button></a>
						<a href="/ventas"><button class="btn btn-sm btn-danger btn-sm"><strong>Cancelar</strong></button></a>
					</div>
				</div>
				
				<div class="box-body">
					<div class="row">
						<input type="hidden" name="producto" value="{{$prod_id}}">
						<div class="col-lg-4 form-group form-group-sm">
						
							<select class="form-control selectpicker" data-show-subtext="true" data-live-search="true" id="ins_id">
								@foreach($insumos as $insumo)
								<option value="{{$insumo->IdProducto}}">{{$insumo->pronom}}</option>
								@endforeach
							</select>
						</div>
				
						<div class="col-lg-3">
							<button id="btnAgregar" class="btn btn-sm btn-primary" type="button">Agregar</button>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12 table-responsive">
							<table class="table table-hover" >
								<tr>
									<th>PRECIO SUGERIDO</th>
									<th>PRECIO FINAL</th>
									<th>COSTO TOTAL</th>
								</tr>
								<tbody>
									<tr>
										<td><input class="form-control" readonly="readonly" type="text" id="preciosugerido" value="" name="preciosugerido"></td>
										<td><input class="form-control" type="text" id="preciofinal" value="{{$producto->propun}}" name="preciofinal"></td>
										<td><input class="form-control"  readonly="readonly" type="text" value="{{$producto->costo}}" id="totalcosto" name="totalcosto"></td>
									</tr>
								</tbody>
							</table>
							<table class="table table-hover" id="grdet">
								
								<thead>
									<th width="20" hidden="hidden">Codigo</th>
									<th width="600">combo</th>
									<th width="60">Cantidad</th>
									<th width="150">Unidad Medida</th>
									<th width="80">Costo Unitario</th>
									<th width="80">Precio Unitario</th>
								</thead>
								<tbody >
									@foreach($combos as $rec)
										<tr>
											<td hidden='hidden'>
												<input type='text' class='form-control' name='prod_comb_id[]'  value='{{$rec->prod_combo}}' readonly='readonly' style='width:20px' >
											</td>
											<td>
												<input type='text' class='form-control input-sm' name='prod_nom[]' value='{{$rec->pronom}}' style='width:600px' readonly='readonly'>
											</td>
											<td width='60'> 
												<input type='text' value='{{$rec->comb_cant}}' name='cantidad[]' class='form-control input-sm' id='font-size' style='width:60px'>
											</td>
											<td width='60'> 
												<input type='text' value='{{$rec->umenom}}' name='unidadmedida[]' class='form-control input-sm' readonly='readonly' input-sm' id='font-size' style='width:150px'>
											</td>
											<td width='80'> 
												<input type='text' value='{{$rec->costo}}' name='costo[]' class='form-control input-sm' readonly='readonly' input-sm' id='font-size' style='width:80px'>
											</td>
											<td width='80'> 
												<input type='text' value='{{$rec->propun}}' name='precio[]' class='form-control input-sm' readonly='readonly' input-sm' id='font-size' style='width:80px'>
											</td>
											<td>
												<button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button>
											</td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
				</div>	
			</div>
			{!!Form::close()!!}
		</div>
	</div>
</div>
</div>
@endsection
