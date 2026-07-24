@extends('layouts.empresas')
@section('contenido')

@include('empresas.clientes.modalcrearcliente')
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

   	  
             /*var part_suc = $("#part_suc").val();

                $("#partida").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
                $.ajax({
                  type: "GET",
                  dataType: 'json',
                  url: "/buscaralmacen/"+part_suc,

                }).done(function(respuesta){
                $("#partida").html(respuesta.vista);
               
                });*/
        

                 $("#part_suc").change(function() {
                            
                            $("#btnCategorias").click();


                            var part_suc = $("#part_suc").val();
                            $("#partida").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
                            $.ajax({
                              type: "GET",
                              dataType: 'json',
                              url: "/buscaralmacen/"+part_suc,

                            }).done(function(respuesta){
                            $("#partida").html(respuesta.vista);
                           
                            });

                  });

                  $("#almacen").change(function() {
                    
                  
                    $("#btnCategorias").click();

                  });




            /*  var des_suc = $("#des_suc").val();
                $("#destino").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
                $.ajax({
                  type: "GET",
                  dataType: 'json',
                  url: "/buscaralmacendestino/"+des_suc,

                }).done(function(respuesta){
                $("#destino").html(respuesta.vista);
               
                });*/
        

                 $("#des_suc").change(function() {
                            

                            var des_suc = $("#des_suc").val();
                            $("#destino").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
                            $.ajax({
                              type: "GET",
                              dataType: 'json',
                              url: "/buscaralmacendestino/"+des_suc,

                            }).done(function(respuesta){
                            $("#destino").html(respuesta.vista);
                           
                            });

                  });




         
      $("#formfact").keypress(function(e) {
        if (e.which == 13) {
          return false;
        }
      })



    $("#buscarproducto").focus();



    $("#btnRegCompReg").on("click", function() {


      
      if ($('#grdet >tbody >tr').length == 0){
        $('#alertitem').show();
        event.preventDefault(); 
      }

      var formulario = $("#formfact").serializeArray();
      $("#imgload").show();
      $(".botones").hide();
      $.ajax({
        type: "POST",
        dataType: 'json',
        url: '/registrartransferencia',
        data: formulario,
      }).done(function(respuesta){

           if(respuesta.estado =='error'){
            alert(respuesta.mensaje);
            
            $("#imgload").hide();
            $(".botones").show();
        }else{
            window.location.href = "/transferencias";
            $("#imgload").hide();
 
        }

      });

    });



    $("#buscardescripcion").keyup(function() {
      var val = $(this).val();
      var alm = $("#almacen").val();
      var part_suc = $('#part_suc').val();
      var contarcarateres = $(this).val().length;

      if(contarcarateres >0){
        $("#detmenu").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
        $.ajax({
          type: "GET",
          dataType: 'json',
          url: "/busquedaproductoalm/"+val+"/"+alm+"/"+part_suc,

        }).done(function(respuesta){
          $("#detmenu").html(respuesta.vista);

        });
      }


    });

    function mostrarobservacion(ele){

    
     alert($(this).closest("td").siblings().find("input[name=pronomobs[]]").val());


  }



    $("#buscarproducto").keypress(function(e) {

      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){


        var sucursal = $("#part_suc").val();
        var almacen = $("#almacen").val();
        var valor = $(this).val();
        var cont = 0, cantidad=0,total=0;
        $.ajax({
          type: 'get',
          url: '/consultarprodalm'+'/'+sucursal+'/'+almacen,
          dataType: 'json',
          data: {'value' : $(this).val()},
          success : function(data) {

            var valornuevo = data[0].proid;

    

           if(data[0].contar =='1'){


             $("#buscarproducto").val('');

             if ($('#grdet >tbody >tr').length > 0){

              $("#grdet tbody tr").each(function(){
               var codigo = $(this).find("td:eq(2) > input").val();


               if( valornuevo == codigo){
                cont = cont+1;
                cantidad = parseFloat($(this).find("td:eq(1) > input").val())+1;
             
              

              }
              if(cont >0){
                $(this).find("td:eq(1) > input").val(cantidad);
     
                calculartotal();
                $("#buscarproducto").focus();
                return false;
              }
            })

              if(cont ==0){
                var igvitem = data[0].propun -data[0].provun;

                $('#grdet').append("<tr><td width='900px'><input type='text' class='form-control input-sm' name='pronom[]' value='"+data[0].pronom+"' readonly='readonly'</td><td> <input type='text' value='1' name='cant[]' onChange='Calcular(this);' onkeyup='Calcular(this);' onChange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'> </td><td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+data[0].proid+"' readonly='readonly' style='width:130px' ></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");


              }

            }else{

              var igvitem = data[0].propun -data[0].provun;
              $('#grdet').append("<tr><td width='900px'><input type='text' class='form-control input-sm' name='pronom[]' value='"+data[0].pronom+"' readonly='readonly'></td><td> <input type='text' value='1' name='cant[]' onChange='Calcular(this);' onkeyup='Calcular(this);' onChange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'> </td><td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+data[0].proid+"' readonly='readonly' style='width:130px' ></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");
            }



            if ($('#grdet >tbody >tr').length > 0){
             calculartotal();
             $("#buscarproducto").val('');
             $("#buscarproducto").focus();
           }

         }

       }

     })

}
});
});



</script>

<script>



function mostrar(comp){
  var id = comp.id;
  var val = comp.value;
   var alm = $("#almacen").val();
   var part_suc = $('#part_suc').val();

  $("#detmenu").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
  $.ajax({
    type: "GET",
    dataType: 'json',
    url: "/consultarmenualm/"+val+"/"+alm+"/"+part_suc,

  }).done(function(respuesta){
    $("#detmenu").html(respuesta.vista);
  });

}




function deleteRow(btn) {
  var row = btn.parentNode.parentNode;
  row.parentNode.removeChild(row);

};






</script>


</br>
 

<div class="container-fluid" id="general">
  


   {!!Form::open(array('url'=>'/registrartransferencia','autocomplete'=>'off','method'=>'POST','name'=>'formfact','id'=>'formfact','role'=>'form','files'=>'true'))!!}
            {{Form::token()}}
    <input type="hidden" name="mov_cab_id" value="{{$cabecera->mov_cab_id}}">

    <div class="row">
      <div class="col-lg-6">
        <div class="box">
         <div class="box-header" style="background-color:#337ab7;">
              <font color="white"><center><strong>UBICACION</strong></center></font>
         </div>
         <div class="box-body">
           <div class="row">
           	 <div class="col-lg-3">
                <div class="form-group form-group-sm">
                    <label>F. Traslado</label>
                    <input  type="date" id="fecEmi" name="fecEmi" disabled="disabled" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">
                </div>
                   
               </div>
            <div class="col-lg-3" >
                <div class="form-group form-group-sm">
                    <LABEL>SUCURSAL</LABEL>
                    <select name="part_suc" id="part_suc" disabled="disabled" class="form-control">
                      @foreach($negocios as $neg)
	                      @if($neg->id_empresa_negocio == $cabecera->part_suc)
	                      	<option selected="selected" value="{{$neg->id_empresa_negocio}}">{{$neg->IdEmpresa}} - {{$neg->tipo_negocio}}</option>
	                      @else
	                      	<option value="{{$neg->id_empresa_negocio}}">{{$neg->IdEmpresa}} - {{$neg->tipo_negocio}}</option>
	                      @endif
                      @endforeach
                    </select>
                </div>
            </div>

             <div class="col-lg-3" id="partida">
                <div class="form-group form-group-sm">
                    <LABEL>ALMACEN</LABEL>
                    <select name="almacen" id="almacen" disabled="disabled" class="form-control">
                      @foreach($alm_ori as $alm)
                        @if($alm->id_almacen == $cabecera->part_alm)
	                      	<option selected="selected" value="{{$alm->id_almacen}}"">{{$alm->descripcion}}</option>
	                      @else
	                      	<option value="{{$alm->id_almacen}}"">{{$alm->descripcion}}</option>
	                      @endif
                        
                      @endforeach
                    </select>
                </div>
            </div>

              

             	</div>
         	</div>
     	</div>
	</div>
	<div class="col-lg-6">
        <div class="box">
         <div class="box-header" style="background-color:#337ab7;">
              <font color="white"><center><strong>DESTINO</strong></center></font>
         </div>
         <div class="box-body">
           <div class="row">
           	 <div class="col-lg-3">
                <div class="form-group form-group-sm">
                    <label>F. Recepci&oacute;n</label>
                    <input  type="date" id="fecRec" name="fecRec"  value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">
                </div>
                   
               </div>
            <div class="col-lg-3">
                <div class="form-group form-group-sm">
                    <LABEL>SUCURSAL</LABEL>
                    <select name="des_suc" id="des_suc" disabled="disabled" class="form-control">
                      @foreach($negocios as $neg)
                         @if($neg->id_empresa_negocio == $cabecera->des_suc)
	                      	<option selected="selected" value="{{$neg->id_empresa_negocio}}">{{$neg->IdEmpresa}} - {{$neg->tipo_negocio}}</option>
	                      @else
	                      	<option value="{{$neg->id_empresa_negocio}}">{{$neg->IdEmpresa}} - {{$neg->tipo_negocio}}</option>
	                      @endif
                      @endforeach
                    </select>
                </div>
            </div>

            <div class="col-lg-3" id="destino">
              <div class="form-group form-group-sm">
                    <LABEL>ALMACEN</LABEL>
                    <select name="des_alm" id="des_alm" class="form-control">
                      @foreach($alm_des as $alm)
                         @if($alm->id_almacen == $cabecera->des_alm)
	                      	<option selected="selected" value="{{$alm->id_almacen}}"">{{$alm->descripcion}}</option>
	                      @else
	                      	<option value="{{$alm->id_almacen}}"">{{$alm->descripcion}}</option>
	                      @endif
                      @endforeach
                    </select>
                </div>
            </div>
            </div>
            

            

             	</div>
         	</div>
     	</div>
	</div>

    <div class="row" hidden="hidden">
      <div class="col-lg-12">
         <div class="box">
           <div class="box-header with-border  form-group form-group-sm" style="background-color:#337ab7">
            <div  class="col-lg-2">
                <a href="" data-target="#modalproductos" data-toggle="modal"><button class="btn btn-sm btn-warning"><strong>AGREGAR PRODUCTOS</strong></button></a>
            </div>
       
             <div  class="col-lg-3">
               <font color="white"><strong>{{$datos->tipo_negocio}}</strong></font>
                
            </div>
           

             
                   
           </div>
      </div>
    </div>
    </div>
    <div class="row" hidden="hidden">
            <div class="col-lg-12">
              <div class="box">
                 <div class="box-header" style="background-color:#337ab7;">
                   <font color="white"><strong><center>{{$datos->tipo_negocio}}</center></strong></font>
                 </div>
                <div class="box-header with-border form-group-sm">
                  
                    <div  class="col-lg-3">
                    <input class="form-control" name="buscarproducto" id="buscarproducto" placeholder="Código Barras">
                  </div>
                  <div  class="col-lg-3">
                      <input class="form-control" name="buscardescripcion" id="buscardescripcion" placeholder="Descripción">
                  </div>
                   <div  class="col-lg-6">
                  <button id="btnCategorias" type="button" name="btnCategorias" class="btn btn-block btn-success btn-sm" style="background:#2d572c ">CATEGORÍAS</button>
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
            </div>
          </div>

    <div class="row">
      <div class="col-lg-12">
              <div class="box">
                <div class="box-header" style="background-color:#337ab7;">
                   <font color="white"><center><strong>DETALLE</strong></center></font>
                </div>
                 <div class="box-body">
                   <table class="table table-hover" id="grdet">
                        <thead>

                      <th>Producto</th>
                      <th>Cantidad</th>
                      <th hidden="hidden">Unidad</th>
          

                    </thead>

                    <tbody>
                    	@foreach($detalle as $det)
                    		<tr>
                    			<td width='1200px'>
                    				<input type='text' class='form-control input-sm' name='pronom[]' value='{{$det->pronom}}' readonly='readonly'>
                    			</td>
                    			<td>
                    				<input type='number' step='any' min='0' value='{{$det->cantidad}}' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'>
                    			</td>
                    			<td hidden='hidden'>
                    				<input type='text' class='form-control' name='proid[]'  value='{{$det->IdProducto}}' readonly='readonly' style='width:130px' >
                    			</td>
                    			<td hidden='hidden'>
                    				<input type='text' class='form-control' name='mov_det_id[]'  value='{{$det->mov_det_id}}' readonly='readonly' style='width:130px' >
                    			</td>
                    			
                    		</tr>
                    	@endforeach
                    </tbody>
                  </table>
                   <table class="table table-hover">
              <thead>

                <th>OBSERVACIONES</th>
                
              </thead>

              <tbody>

                <tr>
                  <td>
                      <textarea class="form-control" rows="3" maxlength="250" name="observaciones"></textarea>
                  </td>
                </tr>
              </tbody>
            </table>
                </div>
            </div>
      </div>

      <div class="col-lg-12">
        <div class="box">
          <div class="box-body">
          <div class="row">           
            <center><img style="display:none;" width="80px" height="80px" src="/img/load.gif" name="imgload" id="imgload"></center>
          </div>
        
          <br>
          <div class="row">
         
             <div class="col-lg-6">
              <button type="button" id="btnRegCompReg" class=" btn btn-block btn-primary btn-lg botones">REGISTRAR</button><br>
            </div>
            <div class="col-lg-6">
              <a href="/transferencias"><button type="button" class=" btn btn-block btn-danger btn-lg botones">SALIR</button></a><br>
            </div>
          </div>
        </div>
      </div>
    </div>


</div>




{!!Form::close()!!}
</div>

@endsection
