@extends('layouts.empresas')
@section('contenido')
@include('empresas.productos.modalimportarinventario')

<script>
$(document).ready(function()
{       

	  
        

     $("#suc_id").change(function() {
         
                var sucursal = $("#suc_id").val();
                $("#divalmacen").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
                $.ajax({
                  type: "GET",
                  dataType: 'json',
                  url: "/buscaralmacen/"+sucursal,

                }).done(function(respuesta){
                $("#divalmacen").html(respuesta.vista);
               
                });

      });

          $("#sucursalimport").change(function() {
         
                var sucursal = $("#sucursalimport").val();
                $("#divalmacenimport").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
                $.ajax({
                  type: "GET",
                  dataType: 'json',
                  url: "/buscaralmacen/"+sucursal,

                }).done(function(respuesta){
                $("#divalmacenimport").html(respuesta.vista);
               
                });

      });

        $("#buscardescripcion").keyup(function() {
      var val = $(this).val();
      var contarcarateres = $(this).val().length;
      var sucursal = $('#sucursal').val();
      var almacen = $('#almacen').val();
      if(contarcarateres >0){
        $("#detmenu").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
        $.ajax({
          type: "GET",
          dataType: 'json',
          url: "/busquedaproductoinventario/"+val+"/"+sucursal+"/"+almacen,

        }).done(function(respuesta){
          $("#detmenu").html(respuesta.vista);

        });
      }


    });







   /*   $("#cmbCatId").change(function() {
         	  
         	  	$('#promocion').val('Todos');
         	$('#buspro').val('');
              $('#formstock').submit();
        

        });*/

 });

function buscarinventario(id){

	 var suc = $("#suc_id").val();
	 var almacen = $("#alm_id").val();
	 window.location.href = "/inventarios/"+suc+"/"+almacen+"/"+id;
}

function exportarinventario(){

	 var suc = $("#suc_id").val();
	 var almacen = $("#alm_id").val();
	 window.location.href = "/inventariosexcel/"+suc+"/"+almacen;
}


function crearinventario(id){

	 var suc = $("#suc_id").val();
	 var almacen = $("#alm_id").val();
	 window.location.href = "/inventarios/"+suc+"/"+almacen+"/"+id;
}

function deleteRow(btn) {
  var row = btn.parentNode.parentNode;
  row.parentNode.removeChild(row);

};

           
function mostrar(comp){
  var id = comp.id;
  var val = comp.value;
  var sucursal = $('#suc_id').val();
   var almacen = $('#alm_id').val();
  $("#detmenu").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
  $.ajax({
    type: "GET",
    dataType: 'json',
    url: "/consultarmenuinventario/"+val+"/"+sucursal+"/"+almacen,

  }).done(function(respuesta){
    $("#detmenu").html(respuesta.vista);
  });

}

 function costeo(btn){
     var id = btn.id;
     var suc = $('#sucursal').val();

 
       $("#modal-costeo").modal("show");

       $("#costeo").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');

        $.ajax({
          type: "GET",
          dataType: 'json',
          url: "/costeoproductos/"+id+"/"+suc,

        }).done(function(respuesta){
          $("#costeo").html(respuesta.vista);
        });



  }


 function presentaciones(id){
     var id = id;
     var suc = $('#sucursal').val();
     var almacen = $('#almacen').val();

 
       $("#modal-presentaciones").modal("show");

       $("#presentaciones").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');

        $.ajax({
          type: "GET",
          dataType: 'json',
          url: "/presentacionesproductoinventario/"+id+"/"+suc+"/"+almacen,

        }).done(function(respuesta){
          $("#presentaciones").html(respuesta.vista);
        });



  }
 


</script>
<section class="content">

<div class="row">
    <div class="col-xs-12">
    	<div class="box">
    		
    		<div class="box-header" style="background-color:blue;">
          		<font size="2" color="white"><strong><center>INVENTARIOS</center></strong></font>
          	</div>
          	

	       	<div class="box-body">

	       	
	       		 <div class="row">

                             <div class="col-lg-12">
                                <div  class="col-lg-3">
                                <input class="form-control" name="buscarproducto" id="buscarproducto" placeholder="Código Barras">
                              </div>
                              <div  class="col-lg-3">
                                  <input class="form-control" name="buscardescripcion" id="buscardescripcion" placeholder="Descripción">
                              </div>
                               <div  class="col-lg-6">
                              <button type="button" id="btnCategorias" name="btnCategorias" class="btn btn-block btn-success btn-sm" style="background:#2d572c ">CATEGORÍAS</button>
                            </div>
                            </div>

                            <div class="box-body table-responsive" id="detmenu"  style="max-height:200px;min-width:500px  ">
                              <?php $i=0; ?>
                              @foreach($categorias as $categoria)
                              <?php $i=$i+1; ?>
                              <div class="col-sm-3 col-xs-3">
                                <button id='cat<?php echo $i; ?>' type="button" value='{{$categoria->cat_id}}' onclick="mostrar(this)" style="background:{{$categoria->color}};width: 180px; height: 120px; border-radius:10px">
                                  <p><font color="white">{{$categoria->cat_nom}}</font></p>
                                </button><br><br>
                              </div>
                              @endforeach
                        
                        </div>
                      </div>

	


	       	{!!Form::open(array('url'=>'inventarioeditarstock','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    		{{Form::token()}}  
    		<input type="hidden" name="suc_id" id="suc_id" value="{{$sucursal}}">
    		<input type="hidden" name="alm_id" id="alm_id" value="{{$almacen}}">
    		<input type="hidden" name="inv_fec" value="{{$fecha}}">
    	
				<table style="background-color:#D5DBDB;" id="detFact"   class="table table-responsive table-striped table-bordered">
				<thead>
					<tr>
						<th colspan="5" style="background:blue;">
						<font color="white"><center>STOCK DE PRODUCTOS {{Carbon::now()->format('d-m-Y')}} @if(!empty($datos)) - {{$datos->tipo_negocio}} @endif @if(!empty($datosalm)) - <br>{{$datosalm->descripcion}} @endif</center></font>
					</th>

					<th style="background:blue;">
						
						<button type="submit" class="btn btn-sm btn-block btn-success">ACTUALIZAR STOCK</button>
						
					</th>
						
					</tr>
					<tr>
					<th >CODIGO</th>
					<th width="1000px;">PRODUCTO</th>
					<th>UNIDAD MEDIDA</th>
					<th>CANTIDAD</th>
					<th>COSTO</th>				
				</tr>
				</thead>
				<tbody >
					@foreach($productos as $pro)
							<tr> 
						<td>{{$pro->procod}} <input type="hidden" step="any" name="id[]" class="form-control input-sm" value="{{$pro->IdProducto}}"></td>
						<td>{{$pro->pronom}}</td>
						<td>{{$pro->umenom}}</td>
						<td><input type="number" step="any" name="stock[]" value="{{$pro->inv_can}}" class="form-control input-sm"></td>
						<td><input type="number" step="any" name="costo[]" value="{{$pro->inv_costo}}" class="form-control input-sm"></td>
						<td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td>
				
					</tr>
					@endforeach
				
				</tbody>
			</table>
			
    {!!Form::close()!!} 
		</div>

	</div>
</div>
</div>
</section>
@endsection
