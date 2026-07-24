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



		function deleteRow(btn) {
			var row = btn.parentNode.parentNode;
			row.parentNode.removeChild(row);
			calculartotal();
		};


		function agregarplato(){

		    var producto = $('#ins_id').find(':selected').attr('data-nombre');
		    var proid = $('#ins_id').find(':selected').attr('data-IdProducto');
		 
			$('#grdet').append("<tr><td hidden='hidden'><input type='text' class='form-control' name='plat_id[]'  value='"+proid+"' readonly='readonly' style='width:20px' ></td><td><input type='text' class='form-control input-sm' name='prod_nom[]' value='"+producto+"' style='width:600px' readonly='readonly'></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");

		}


	</script>


</br>


<div class="container-fluid">
>
	
	<div class="row">
		{!!Form::open(array('url'=>'/registrarplato','autocomplete'=>'off','method'=>'POST','name'=>'formplato','id'=>'formplato','role'=>'form','files'=>'true'))!!}
		{{Form::token()}}
		<div class="col-lg-12">
			<div class="box">
				<div style="background-color:#0040FF;" class="box-header with-border">
					<center><font color="white"><strong>ASIGNAR PLATOS</strong></font></center>
					<div class="box-tools pull-right">   
						<button class="btn btn-sm btn-success btn-sm" type="submit" id="btnRegComp" name="btnRegComp"><strong>Registrar</strong></button></a>
						<a href="/programas"><button type="button" class="btn btn-sm btn-danger btn-sm"><strong>Cancelar</strong></button></a>
					</div>
				</div>
				
				<div class="box-body">
					<div class="row">
						<input type="hidden" name="prog_id" value="{{$id}}">
						<div class="col-lg-4 form-group form-group-sm">
						
							<select class="form-control selectpicker" data-show-subtext="true" data-live-search="true" id="ins_id" onchange="agregarplato();" onclick="agregarplato();" onkeypress="if(event.keyCode == 13) agregarplato();">
								<option></option>
								@foreach($platos as $insumo)
								<option value="{{$insumo->IdProducto}}" data-IdProducto="{{$insumo->IdProducto}}" data-nombre="{{$insumo->pronom}}" data-unidad="{{$insumo->umecod}}" >{{$insumo->pronom}}</option>
								@endforeach
							</select>
						</div>
				
						<div class="col-lg-3">
							<button id="btnAgregar" onclick="agregarplato();" class="btn btn-sm btn-primary" type="button">Agregar</button>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12 table-responsive">
							<table class="table table-hover" id="grdet">
								<thead>
									<th width="20" hidden="hidden">Codigo</th>
									<th width="600">Plato</th>
									
								</thead>
								<tbody>
									@foreach($progplat as $rec)
										<tr>
											<td hidden='hidden'>
												<input type='text' class='form-control' name='plat_id[]'  value='{{$rec->IdProducto}}' readonly='readonly' style='width:20px' >
											</td>
											<td>
												<input type='text' class='form-control input-sm' name='plat_nom[]' value='{{$rec->pronom}}' style='width:600px' readonly='readonly'>
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
