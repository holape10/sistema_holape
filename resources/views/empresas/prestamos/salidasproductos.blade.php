@extends('layouts.empresas')
@section('contenido')

@include('empresas.clientes.modalcrearcliente')
@include('empresas.puntosventas.modalpresentaciones')

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

.ui-autocomplete {
     z-index: 9999 !important;

}



</style>

<body>


  <script>

   $(document).ready(function()
   {


     $(".selectpicker").selectpicker({


     });

    

    $("#btnRegComp").on("click", function() {


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
        url: '/registrarsalida',
        data: formulario,
      }).done(function(respuesta){


        if(respuesta.estado =='error'){

            alert(respuesta.mensaje);
            
          $("#imgload").hide();
          $(".botones").show();


        }else{

            window.location.href = "/salidas";
           
 
        }

      });

    });



 


});



</script>

<script>

 $(document).ready(function()
 {

   $("#formfact").keypress(function(e) {
    if (e.which == 13) {
      return false;
    }
  })


  




            });

function mostrar(comp){
  var id = comp.id;
  var val = comp.value;
  $("#detmenu").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
  $.ajax({
    type: "GET",
    dataType: 'json',
    url: "/consultarmenu/"+val,

  }).done(function(respuesta){
    $("#detmenu").html(respuesta.vista);
  });

}

$(function(){
  $('#key').keyboard();
});


function deleteRow(btn) {
  var row = btn.parentNode.parentNode;
  row.parentNode.removeChild(row);
  calculartotal();
};




function  buscarcliente(){


  var formulario = $("#clinumn").val();
  $("#imgloadcliente").show();

  $.ajax({
    type: "get",
    dataType: 'json',
    url: '/autocomplete/'+formulario,

  }).done(function(respuesta){



   $('#clinomn').val(respuesta[0].nom);
   $('#clidirn').val(respuesta[0].dir);
   $('#cliteln').val(respuesta[0].telefono);
   $('#clicorn').val(respuesta[0].cor);
   $('#clicodn').val(respuesta[0].clicod);
   $("#tdicodn").val(respuesta[0].tdicod).attr('selected', 'selected');

   $("#imgloadcliente").hide();
    $(".botones").show();
          
  });

  

}




    $("#btnRegCliente").on("click", function() {


      var formulario = $("#frmcliente").serializeArray();
      $("#imgloadcliente").show();
      $(".botonescliente").hide();
      $.ajax({
        type: "POST",
        dataType: 'json',
        url: '/registrarcliente',
        data: formulario,
      }).done(function(respuesta){

        $("#divcliente").html(respuesta.vista);
        $("#imgloadcliente").hide();
     
         $(".botonescliente").show();
          $('#modal-cliente').modal('toggle');
      });



    });


function seleccionarcliente(){

  if($('#clicod').find(':selected').attr('data-clinum')==''){
      $('#clinum').val('00000000');
  }else{
      $('#clinum').val($('#clicod').find(':selected').attr('data-clinum'));
  }
  

  if($('#clicod').find(':selected').attr('data-documento')==''){
      $("#tdicod").val('1');
  }else{
      $("#tdicod").val($('#clicod').find(':selected').attr('data-documento'));
  }
  


   $('#clinom').val($('#clicod').find(':selected').attr('data-clinom'));
   $('#clidir').val($('#clicod').find(':selected').attr('data-direccion'));
   $('#clicor').val($('#clicod').find(':selected').attr('data-correo'));
   
   
    $("#clitel").val($('#clicod').find(':selected').attr('data-telefono'));
    
      if($('#tdicod').val() =='6' ){
             $('#factura').prop("checked",true);
      }

      if($('#tdicod').val() =='1' ){
         $('#boleta').prop("checked",true);
      }



}


 function presentaciones(id){
     var id = id;
   //  var suc = $('#sucursal').val();

 
       $("#modal-presentaciones").modal("show");

       $("#presentaciones").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');

        $.ajax({
          type: "GET",
          dataType: 'json',
          url: "/presentacionesproducto/"+id,

        }).done(function(respuesta){
          $("#presentaciones").html(respuesta.vista);
        });



  }

   function agregaritem_pre(button){
     var id = button.id;
     var precio = button.value;
     var producto = $('#'+id+'nom').val();
     var proid = $('#'+id+'id').val();
    // var provun = $('#'+id+'vun').val();
     var imagen = $('#'+id+'imagen').val();

  $('#grdet').append("<tr><td width='900px'><input type='text' class='form-control' name='pronom[]' value='"+producto+"'></td><td> <input type='number' step='any' min='0' value='1' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'> </td><td hidden='hidden'><select style='width:100px' name='unid[]'  class='form-control input-sm'> @foreach($unidades as $und) @if($und->umecod == 'UNI') <option  selected='selected' value='{{$und->umecod}}'>{{$und->umenom}}</option> @else <option  value='{{$und->umecod}}'>{{$und->umenom}}</option> @endif @endforeach </select></td><td hidden='hidden'><input type='text' class='form-control' name='provun[]'  value='' readonly='readonly' style='width:130px' ></td><td hidden='hidden'><input  type='number' step='any' min='0' class='form-control input-sm' name='propun[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='"+precio+"' style='width:80px' ></td><td hidden='hidden'><input readonly='readonly' type='text' class='form-control' name='itemtotal[]'  value='"+precio+"' onkeyup='CalcularItem(this);' style='width:80px' ></td><td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+proid+"' readonly='readonly' ></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");

      calculartotal();

      $("#modal-presentaciones").modal("hide");

    //  $(function(){
    //     $('.keyboard').keyboard();
    //   });
  }

  function agregaritem(){

 
    
    var producto = $('#producto').find(':selected').attr('data-pronom');
    var precio = $('#producto').find(':selected').attr('data-propun');
    var proid = $('#producto').find(':selected').attr('data-idproducto');
    var contar = $('#producto').find(':selected').attr('data-presentacion');


  if(contar>0){
        presentaciones(proid);

        $("#modal-presentaciones").modal("show");
  }else{
      $('#grdet').append("<tr><td width='900px'><input type='text' class='form-control' name='pronom[]' value='"+producto+"'></td><td> <input type='number' step='any' min='0' value='1' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'> </td><td hidden='hidden'><select style='width:100px' name='unid[]'  class='form-control input-sm'> @foreach($unidades as $und) @if($und->umecod == 'UNI') <option  selected='selected' value='{{$und->umecod}}'>{{$und->umenom}}</option> @else <option  value='{{$und->umecod}}'>{{$und->umenom}}</option> @endif @endforeach </select></td><td hidden='hidden'><input type='text' class='form-control' name='provun[]'  value='' readonly='readonly' style='width:130px' ></td><td hidden='hidden'><input  type='number' step='any' min='0' class='form-control input-sm' name='propun[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='"+precio+"' style='width:80px' ></td><td hidden='hidden'><input readonly='readonly' type='text' class='form-control' name='itemtotal[]'  value='"+precio+"' onkeyup='CalcularItem(this);' style='width:80px' ></td><td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+proid+"' readonly='readonly' ></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");

      calculartotal();

      $("#modal-presentaciones").modal("hide");
  }



     

    //  $(function(){
    //     $('.keyboard').keyboard();
    //   });
  }

</script>


</br>
 

<div class="container-fluid" id="general">
  


   {!!Form::open(array('url'=>'/restaurantpunto','autocomplete'=>'off','method'=>'POST','name'=>'formfact','id'=>'formfact','role'=>'form','files'=>'true'))!!}
            {{Form::token()}}
    <input type="hidden" name="opcion" id="opcion" value="0">
    <div class="row" hidden="hidden">
      <div class="col-lg-12">
         <div class="box">
           <div class="box-header with-border  form-group form-group-sm" style="background-color:blue">
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

<div class="row">
  <div class="col-lg-12">
        <div class="box">
    
         <div class="box-header" style="background-color:blue;">
              <font color="white"><center><strong>DATOS DE SALIDA</strong></center></font>
         </div>
         <div class="box-body">
             

             <div class="row">
          

               <div class="col-lg-3">
                <div class="form-group form-group-sm">
                   <label>FECHA</label>
                     <input  type="date" id="fecEmi" name="fecEmi" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">
                </div>
                   
               </div>

                 <div class="col-lg-3" >
              <div class="form-group">
                <label class="control-label">Colaboradores</label>
                <select class="form-control selectpicker input-sm" data-show-subtext="true" data-live-search="true" name="clicod" id="clicod" >
                  <option></option>
                  @foreach($colaboradores as $colab)
                    <option value="{{$colab->IdUsuario}}">{{$colab->name}} - {{$colab->apeusu}}</option>
                  @endforeach
                </select>
               
              </div>
            </div>


                 <div class="col-lg-3" >
              <div class="form-group">
                <label class="control-label">&Aacute;reas</label>
                <select class="form-control selectpicker input-sm" data-show-subtext="true" data-live-search="true" name="area" id="clicod" onchange="seleccionarcliente();">
                  <option></option>
                  @foreach($areas as $are)
                    <option value="{{$are->are_emp_id}}">{{$are->are_emp_des}}</option>
                  @endforeach
                </select>
               
              </div>
            </div>


             </div>
               
             
                   <div class="row" hidden="hidden">
              <div class="col-lg-4">
                <div class="form-group form-group-sm">
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                  
                 
                  <label >
                    <input type="radio" name="tdocod" id="nota" value="81" checked="checked"  > NS
                  </label>
                
                </div>
                </div>
               
              </div>

          </div>
         </div>
    

   
     </div>
</div>

</div>
    <div class="row">
     
            <div class="col-lg-12">
              <div class="box">
                 <div class="box-header" style="background-color:blue;">
                    <font color="white"><strong><center>{{$datos->tipo_negocio}}</center></strong></font>

                
                 </div>
                <div class="box-header with-border form-group-sm">
                  
                  <div  class="col-lg-2">
                    <input class="form-control" name="buscarproducto" id="buscarproducto" placeholder="Código Barras">
                  </div>



                  <div  class="col-lg-10">
                    <div class="form-group form-group-sm">
                        <select style=" font-weight: bold;" class="form-control selectpicker input-sm" onkeypress="if(event.keyCode == 13) agregaritem();" onchange="agregaritem();" data-show-subtext="true" data-live-search="true" name="producto" id="producto">
                          <option></option>
                          @foreach($productos as $pro)
                          <option style="font-weight:bold;color:black;font-size:14pt;" value="{{$pro->IdProducto}}" data-idproducto="{{$pro->IdProducto}}" data-pronom="{{$pro->pronom}}" data-marca="{{$pro->marca}}" data-propun="{{$pro->precio}}" data-presentacion="{{$pro->cont_pre}}">{{$pro->pronom}} {{$pro->marca}} | PRECIO:{{$pro->precio}} |Stock: {{$pro->stock}}</option>
                          @endforeach
                     </select>
                    </div>
                   
                  </div>
                
                </div>
               
              </div>
            </div>

     
 
      <div class="col-lg-12">
              <div class="box">
                <div class="box-header" style="background-color:blue;">
                   <font color="white"><center><strong>DETALLE</strong></center></font>
                </div>
                 <div class="box-body">

                
                   <table class="table table-hover" id="grdet">
                        <thead>

                      <th>Producto</th>
                      <th>Cantidad</th>
                      <th hidden="hidden">Unidad</th>
                      <th hidden="hidden">VU</th>
                      <th hidden='hidden'>PU</th>
                      <th hidden='hidden'>Total</th>

                    </thead>

                    <tbody>

                    </tbody>
                  </table>
               
                </div>

          <div class="box-body">
    
          <div class="row">
         
             <div class="col-lg-6">
              <button type="button" id="btnRegComp" class=" btn btn-block btn-primary btn-lg botones">REGISTRAR</button><br>
            </div>
            
          

             <div class="col-lg-6">
              <a href="/salidasproductos"><button type="button" class=" btn btn-block btn-danger btn-lg botones">SALIR</button></a><br>
            </div>
          </div>
        </div>
            </div>
      </div>
</div>

</div>


{!!Form::close()!!}
</div>
</div>


@endsection
