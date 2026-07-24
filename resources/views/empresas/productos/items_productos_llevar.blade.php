<script type="text/javascript">
		
	function buscar_producto_categoria(id){

		var tipo = $("#tipo").val();

		
		var producto=0;	
		$.ajax({
			type: "GET",
			dataType: 'json',
			url: "/buscarcartallevar/"+producto+"/"+id,
		}).done(function(respuesta){
			$("#items_productos").html(respuesta.vista);
		});


	}


		function agregar_item(){

			var id_ = $('#_id').val();
			var producto_ = $('#_producto').val();
			var precio_ = $('#_precio').val();
			var acompa_ = $('#_acompa').val();

			var validar = checkId(id_);

			/*if (validar==true){
				$("#tbl_detalle  > tbody  > tr").each(function(){
					if(id==$(this).find("td:eq(0) > input").val()){
						var calcular_cantidad = parseFloat($(this).find("td:eq(2) > input").val())+1;

						$(this).find("td:eq(2) > input").val(calcular_cantidad);
					}
				});

			}else{*/

				$('#items_pedidos').append('<tr><td hidden="hidden" id="'+id_+'"><input type="text" readonly="readonly" class="form-control" name="txt_id_producto[]" value="'+id_+'"></td>'+
				'<td><input class="form-control" name="descripcion[]" type="hidden"  value="'+producto_+' - '+acompa_+'">'+producto_+' - '+acompa_+'</td>'+
				'<td><input type="number" style="text-align:center;" step="any" onkeyup="calcular_total();" onChange="calcular_total();" class="cantidad form-control" name="txt_cantidad[]" value="1" min="1"></td>'+
				'<td style="text-align:right;" hidden="hidden">'+precio_+'</td>'+
				'<td style="text-align:right;" ><input  class="form-control" type="number"  readonly="readonly"   step="any" onkeyup="calcular_total();" onChange="calcular_total();" name="precios[]" value="'+precio_+'"></td>'+
				'<td  style="text-align:center;"><button type="button" onClick="eliminar_item(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');
			//}

			$('#_id').val('');
			$('#_producto').val('');
			$('#_precio').val('');
			$('#_acompa').val('');

			$(".cantidad").focus();
     		$(".cantidad").select();
	

		calcular_total();

	}

	function elegir_acompanamiento(id,producto,precio,cat_sig,acom){

		var cat_acom = $('#cat_acom').val();

		if(cat_acom=='1'){

			$('#_acompa').val(producto);

			agregar_item();

			$('#_id').val('');
			$('#_producto').val('');
			$('#_precio').val('');
			$('#_acompa').val('');
		

		}else{

			
			$('#_id').val(id);
			$('#_producto').val(producto);
			$('#_precio').val(precio);
			$("#btnAgregarItem").hide();	

		}
		


		if(cat_sig !=''){

	
		  		buscar_producto_categoria(cat_sig);

		  		
			  	
		  	

		}else{


			var id_ = $('#_id').val();
			var producto_ = $('#_producto').val();
			var precio_ = $('#_precio').val();
		
			var validar = checkId(id_);

		/*	if (validar==true){
				$("#tbl_detalle  > tbody  > tr").each(function(){
					if(id==$(this).find("td:eq(0) > input").val()){
						var calcular_cantidad = parseFloat($(this).find("td:eq(2) > input").val())+1;

						$(this).find("td:eq(2) > input").val(calcular_cantidad);
					}
				});

			}else{*/

				$('#items_pedidos').append('<tr><td hidden="hidden" id="'+id_+'"><input type="text" readonly="readonly" class="form-control" name="txt_id_producto[]" value="'+id_+'"></td>'+
				'<td><input class="form-control" name="descripcion[]" type="hidden"  value="'+producto_+'">'+producto_+'</td>'+
				'<td><input type="number" style="text-align:center;" step="any" onkeyup="calcular_total();" onChange="calcular_total();" class="cantidad form-control" name="txt_cantidad[]" value="1" min="1"></td>'+
				'<td style="text-align:right;" hidden="hidden">'+precio_+'</td>'+
				'<td style="text-align:right;" ><input class="form-control" type="number" step="any" readonly="readonly" onkeyup="calcular_total();" onChange="calcular_total();" name="precios[]" value="'+precio_+'"></td>'+
				'<td  style="text-align:center;"><button type="button" onClick="eliminar_item(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');
			//}

				$('#_id').val('');
				$('#_producto').val('');
				$('#_precio').val('');
				$('#_acompa').val('');

				$(".cantidad").focus();
     			$(".cantidad").select();
				
		}
		
		/*var validar = checkId(id);

		if (validar==true){
			$("#tbl_detalle  > tbody  > tr").each(function(){
				if(id==$(this).find("td:eq(0) > input").val()){
					var calcular_cantidad = parseFloat($(this).find("td:eq(2) > input").val())+1;

					$(this).find("td:eq(2) > input").val(calcular_cantidad);
				}
			});

		}else{
			$('#items_pedidos').append('<tr><td hidden="hidden" id="'+id+'"><input type="text" readonly="readonly" class="form-control" name="txt_id_producto[]" value="'+id+'"></td>'+
				'<td>'+producto+'</td>'+
				'<td><input type="number" style="text-align:center;" step="any" onkeyup="calcular_total();" onChange="calcular_total();" class="form-control" name="txt_cantidad[]" value="1" min="1"></td>'+
				'<td style="text-align:right;">'+precio+'</td>'+
				'<td style="text-align:right;" hidden="hidden"><input type="number" readonly="readonly" step="any" name="precios[]" value="'+precio+'"></td>'+
				'<td  style="text-align:center;"><button type="button" onClick="eliminar_item(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');
		}*/

		calcular_total();

	}
</script>

<table class="table table-hover table-bordered table-condensed" >	
  <tr>
  	<td colspan="5" style="background-color:gray;color: white;">
  		 <center><strong>@if(!empty($data_cat)){{$data_cat->cat_nom}}@else  @endif</strong></center>
  	</td>
  </tr>				
	@foreach($productos as $pro)
	<tr>
		<td style="text-align:center;font-weight:bold;" width="10%;">
				@if($pro->precio2>0)
				<button type="button" onclick="elegir_acompanamiento('{{$pro->IdProducto}}','{{$pro->pronom}}','{{$pro->precio2}}','{{$pro->cat_sig}}','{{$pro->acom}}','{{$pro->icbper}}')"  name="add" id="add" class="btn btn-success btn-sm add">
				<span class="glyphicon glyphicon-plus"></span></button>
				@else
				<button type="button" onclick="elegir_acompanamiento('{{$pro->IdProducto}}','{{$pro->pronom}}','{{$pro->precio}}','{{$pro->cat_sig}}','{{$pro->acom}}','{{$pro->icbper}}')"  name="add" id="add" class="btn btn-success btn-sm add">
				<span class="glyphicon glyphicon-plus"></span></button>
				@endif
			
		</td>
		<td style="padding-left:20px;font-weight:bold;" width="50%;">{{$pro->pronom}}</td>
		<td  hidden="hidden"style="padding-left:20px;font-weight:bold;" width="10%;">{{$pro->umecod}}</td>
		<td  hidden="hidden" style="padding-left:20px;font-weight:bold;text-align:right;" width="10%;">{{$pro->factor}}</td>
		@if($pro->precio2>0)
			<td style="text-align:right;padding-right:20px;font-weight:bold;" width="40%;">S/. {{$pro->precio2}}</td>
		@else
			<td style="text-align:right;padding-right:20px;font-weight:bold;" width="40%;">S/. {{$pro->precio}}</td>
		@endif
		
	</tr>
	@endforeach
	<input type="hidden" readonly="readonly"  name="cat_acom" id="cat_acom" value="@if(!empty($data_cat)){{$data_cat->cat_acom}}@else 0 @endif">
	

</table>