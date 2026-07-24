@extends('layouts.empresas')
@section('contenido')
@include('empresas.puntosventas.modalpresentaciones')
@include('empresas.clientes.modalcrearcliente')
@include('empresas.almacen.modalingresarcantidad')
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

#modal-cantidad-precio{
  z-index: 99999 !important;
}

#table-presentaciones .btn:hover
{
    color: #fff;
    background-color:red;
    border-color: red;
}

#table-presentaciones .btn:focus
{
    color: #fff;
    background-color:red;
    border-color: red;
}


.green
{
    color: #fff;
    background-color: #398439;
    border-color: #255625;
}


</style>

<body>


  <script>

   $(document).ready(function()
   {

     var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');



        $('#modal-cantidad-precio').on('shown.bs.modal', function() { $("#can_producto").focus(); })
      $('#modal-presentaciones').on('shown.bs.modal', function() { $("#table-presentaciones .btn:first").focus(); })


    $("#can_producto").keypress(function(e){
       var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        
        $("#pre_producto").focus();
        $("#pre_producto").select();
      }
     


    });


     $("#can_producto").keypress(function(e){
        var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){
          agregaritem();
          $("#modal-cantidad-precio").modal("hide");
        }
      });


       $("#btnAgregarLista").on("click", function() {
           agregaritem();
          $("#modal-cantidad-precio").modal("hide");
       });

      $("#pre_producto").keypress(function(e){
          var code = (e.keyCode ? e.keyCode : e.which);
          if(code==13){
            agregaritem();
            $("#modal-cantidad-precio").modal("hide");
          }
      });




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


    


   $("#producto").select2({
    minimumInputLength: 2,
    tags: "true",
    allowClear: true,
    ajax: {
      url: "{{route('Productos.consultarproductos')}}",
      dataType: 'json',
      type: "POST",
      quietMillis: 50,
        data: function (params) {

           var id_almacen = $("#almacen").val();
           
            return {
                _token : CSRF_TOKEN,
                search: params.term,
                almacen: id_almacen,
            };
        },
      processResults: function (response) {

       /* $("#producto").html(response.vista);*/

       return {
        results: $.map(response, function(response){


          return {
        
             "text": response.textcompra,
            "id": response.id,
             "pro_rel": response.pro_rel,
            "presentacion": response.contar,
            "propun": response.propun,
            "producto": response.producto
          }

        })

      };
    },
    cache:false
  }

});

     $("#producto").select2('open');


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
                  url: '/actualizarmovimiento',
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

                        
                        $("#buscarproducto").focus();
                        return false;
                      }
                    })

                      if(cont ==0){
                        var igvitem = data[0].propun -data[0].provun;

                        $('#grdet').append("<tr><td width='900px'><input type='text' class='form-control input-sm' name='pronom[]' value='"+data[0].pronom+"' readonly='readonly'</td><td> <input type='text' value='1' name='cant[]' onChange='Calcular(this);' onkeyup='Calcular(this);' onChange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:150px'> </td><td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+data[0].proid+"' readonly='readonly' style='width:130px' ></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");


                      }

                    }else{

                      var igvitem = data[0].propun -data[0].provun;
                      $('#grdet').append("<tr><td width='900px'><input type='text' class='form-control input-sm' name='pronom[]' value='"+data[0].pronom+"' readonly='readonly'></td><td> <input type='text' value='1' name='cant[]' onChange='Calcular(this);' onkeyup='Calcular(this);' onChange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:150px'> </td><td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+data[0].proid+"' readonly='readonly' style='width:130px' ></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");
                    }



                    if ($('#grdet >tbody >tr').length > 0){
                     
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

          function presentaciones(id){
           var id = id;
           var suc = $('#part_suc').val();
           var almacen = $('#almacen').val();


           $("#modal-presentaciones").modal("show");

           $("#presentaciones").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');

           $.ajax({
            type: "GET",
            dataType: 'json',
            url: "/presentacionesproductocompra/"+id+"/"+suc+"/"+almacen,

          }).done(function(respuesta){
            $("#presentaciones").html(respuesta.vista);
          });



        }

   
     function ingresar_cantidad_precio_presentacion(button){
      
    
     var id = button.id;
     var precio = button.value;
     var producto = $('#'+id+'nom').val();
     var proid = $('#'+id+'id').val();
     var unidad= $('#'+id+'unidad').val();
     
     

        //$("#modal-cantidad-precio").modal("show");

       $('#modal-cantidad-precio').modal('show'); 
       $('#modal-cantidad-precio').on('shown', function(){ 
       $("#can_producto").focus();

        
      })



        $("#des_producto").val(producto);
        $("#id_producto").val(proid);
        $("#pre_producto").val(precio);
        $("#uni_producto").val(unidad);

        $("#can_producto").select(); 
      
        $("#modal-presentaciones").modal("hide");
           
        actualizarpro();

      
       

  }

  function ingresar_cantidad_precio(){
      
    
      var producto = $('#producto').select2('data')[0].producto;
      var precio =  $('#producto').select2('data')[0].propun;
      var costo =  $('#producto').select2('data')[0].costo;
      var pro_rel =  $('#producto').select2('data')[0].pro_rel;
      var proid =  $('#producto').select2('data')[0].id;
      var laboratorio =  $('#producto').select2('data')[0].laboratorio;
      var contar = $('#producto').select2('data')[0].presentacion;


      if(contar>0){

        presentaciones(proid);

        $("#modal-presentaciones").modal("show");

      }else{


       $('#modal-cantidad-precio').modal('show'); 
       $('#modal-cantidad-precio').on('shown', function(){ 
       $("#can_producto").focus();

        
      })


        $("#des_producto").val(producto);
        $("#id_producto").val(proid);
        $("#pre_producto").val(costo);
        $("#uni_producto").val();
        $("#lab_producto").val(laboratorio);

        $("#can_producto").select(); 
  
        actualizarpro();

      }
       

  }




 function actualizarpro(){

  
  
    $.ajax({
      type: "GET",
      dataType: 'json',
      url: "/actualizarpro/compra",

    }).done(function(respuesta){
  
      
        $("#divactpro").html(respuesta.vista);
    
     
    
    });

    


  }
  

 function agregaritem_pre(button){
     var id = button.id;
     var precio = button.value;
     var producto = $('#'+id+'nom').val();
     var proid = $('#'+id+'id').val();
     var laboratorio = $('#'+id+'lab').val();
     var imagen = $('#'+id+'imagen').val();
      var cantidad = $('#can_producto').val();
         var total = cantidad*precio;

  $('#grdet').append("<tr><td width='900px'><input type='text' class='form-control input-sm' name='detpro[]' value='"+producto+"' readonly='readonly'></td><td> <input type='number' step='any' min='0' value='"+cantidad+"' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'> </td><td hidden='hidden'><select style='width:100px' name='unid[]'  class='form-control input-sm'> @foreach($unidades as $und) @if($und->umecod == 'UNI') <option  selected='selected' value='{{$und->umecod}}'>{{$und->umenom}}</option> @else <option  value='{{$und->umecod}}'>{{$und->umenom}}</option> @endif @endforeach </select></td><td hidden='hidden'><input type='text' class='form-control' name='provun[]'  value='' readonly='readonly' style='width:130px' ></td><td hidden='hidden'><input  type='number' step='any' min='0' class='form-control input-sm' name=preuni[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='"+precio+"' style='width:80px' ></td><td hidden='hidden'><input type='text' class='form-control input-sm' name='vtot[]'  value='"+total+"' onkeyup='CalcularItem(this);' style='width:80px' ></td><td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+proid+"' readonly='readonly' style='width:130px' ></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");

      

      actualizarpro();
      $("#modal-presentaciones").modal("hide");

    //  $(function(){
    //     $('.keyboard').keyboard();
    //   });
  }

 
  function agregaritem(){
     
 

   
    var producto = $('#des_producto').val();
    var precio =  $('#pre_producto').val();
    var costo =  $('#pre_producto').val();
    var proid =  $('#id_producto').val();
    var  unidad =  $('#uni_producto').val();
    var cantidad = $('#can_producto').val();
    var laboratorio = $('#lab_producto').val();
    var total = cantidad*costo;


        $('#grdet').append("<tr><td><input type='text' class='form-control input-sm' name='detpro[]' value='"+producto+"' readonly='readonly'></td><td > <input type='number'  style='text-align:right;'  step='any' min='0' value='"+cantidad+"' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' id='font-size'> </td><td hidden='hidden'><select style='width:100px' name='unid[]'  class='form-control input-sm'> @foreach($unidades as $und) @if($und->umecod == 'UNI') <option  selected='selected' value='{{$und->umecod}}'>{{$und->umenom}}</option> @else <option  value='{{$und->umecod}}'>{{$und->umenom}}</option> @endif @endforeach </select></td><td hidden='hidden'><input type='text' class='form-control' name='provun[]'  value='' readonly='readonly' style='width:130px' ></td><td  hidden='hidden'><input  type='number' step='any' min='0' class='form-control input-sm' name=preuni[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='"+costo+"' style='width:80px' ></td><td hidden='hidden'><input type='text' class='form-control input-sm' name='vtot[]'  value='"+total+"' onkeyup='CalcularItem(this);' style='width:80px' ></td><td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+proid+"' readonly='readonly' style='width:130px' ></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");

        

       
        actualizarpro();
        $("#modal-presentaciones").modal("hide");
 

  }



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
      $('#clicor2').val($('#clicod').find(':selected').attr('data-correo2'));
      $('#clicor3').val($('#clicod').find(':selected').attr('data-correo3'));
      $('#clicor4').val($('#clicod').find(':selected').attr('data-correo4'));
      $("#clitel").val($('#clicod').find(':selected').attr('data-telefono'));

      if($('#tdicod').val() =='6' ){
       $('#factura').prop("checked",true);
     }

     if($('#tdicod').val() =='1' ){
       $('#boleta').prop("checked",true);
     }



   }




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
     $('#clicorn4').val(respuesta[0].cor4);
     $('#clicorn2').val(respuesta[0].cor2);
     $('#clicorn3').val(respuesta[0].cor3);
     $('#clicodn').val(respuesta[0].clicod);
     $("#tdicodn").val(respuesta[0].tdicod).attr('selected', 'selected');

     $("#imgloadcliente").hide();
     $(".botones").show();

   });



  }



</script>


</br>


<div class="container-fluid" id="general">



 {!!Form::open(array('url'=>'/restaurantpunto','autocomplete'=>'off','method'=>'POST','name'=>'formfact','id'=>'formfact','role'=>'form','files'=>'true'))!!}
 {{Form::token()}}
 <input type="hidden" name="opcion" id="opcion" value="0">

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
         <input  type="date" id="fecEmi" name="fecEmi" value="{{$movimiento->fechatraslado}}" class="form-control">
       </div>

     </div>
     <div class="col-lg-3" >
      <div class="form-group form-group-sm">
        <LABEL>SUCURSAL</LABEL>
        <select name="part_suc" id="part_suc" class="form-control">
          @foreach($negocios as $neg)

          @if($neg->id_empresa_negocio == $movimiento->part_suc)
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
        <select name="almacen" id="almacen" class="form-control">
          @foreach($almacenes as $alm)
           @if($alm->id_almacen == $movimiento->part_alm)
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
    <div class="box-tools pull-right">
      <input type="hidden" name="mov_id" value="{{$movimiento->mov_cab_id}}">
	   <input type="hidden" name="guia" value="{{$movimiento->IdCpe_guia}}">
   </div>
 </div>
 <div class="box-body" >
   <div class="row form-group form-group-sm">
    <div id="destino1">
      
 
    	 <div class="col-lg-3">
                <div class="form-group form-group-sm">
                    <LABEL>SUCURSAL</LABEL>
                    <select name="des_suc" id="des_suc" class="form-control">
                      @foreach($negocios as $neg)
                        @if($neg->id_empresa_negocio == $movimiento->des_suc)
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
                      @foreach($almacenesdest as $alm)
                         @if($alm->id_almacen == $movimiento->des_alm)
          	 <option selected="selected" value="{{$alm->id_almacen}}"">{{$alm->descripcion}}</option>
          @else
          	 <option value="{{$alm->id_almacen}}"">{{$alm->descripcion}}</option>
          @endif
         
                      @endforeach
                    </select>
                </div>
            </div>
</div>
 <div class="col-lg-3" >
      <div class="form-group form-group-sm">
        <LABEL>BULTOS</LABEL>
        <input type="number"  step="any" name="bultos" value="{{$movimiento->bultos}}" class="form-control input-sm">
      </div>
    </div>
  </div>


</div>
</div>
</div>
</div>

<div class="row">
  <div class="col-lg-6">
    <div class="box">
      <div class="box-header" style="background:#337ab7;"> 
        <font color="white"><strong>Datros de Transportista</strong></font>
      </div>

      <div class="box-body">
        <div class="row">
          <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
              <label>Documento</label>
              <select name="transportistatdicod" id="transportistatdicod" class="form-control">
                @foreach($documentos as $doc)
                @if($doc->tdicod == $movimiento->tdicodtransportista)
                <option  selected="selected" value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                @else
                <option value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                @endif
                @endforeach
              </select>

            </div>
          </div>

          <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
              <label for="transportistanum">N° Documento</label>
              <input type="text"  name="transportistanum" value="{{$movimiento->ructransportista}}" id="transportistanum" onKeypress="if(event.keyCode == 13) buscartransportista();"    placeholder="" class="form-control">

            </div>
          </div>
          <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
              <label>Cliente</label>
              <input type="text" name="transportistanom" id="transportistanom" value="{{$movimiento->nombretransportista}}"  class="form-control">

            </div>
          </div>

        </div>
      </div>
    </div>  
  </div>

  <div class="col-lg-6">
    <div class="box">
      <div class="box-header" style="background:#337ab7;"> 
        <font color="white"><strong>Datos de Conductor</strong></font>
      </div>
      <div class="box-body">
        <div class="row">
          <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
              <label>Documento</label>
              <select name="conductortdicod" id="conductortdicod" class="form-control">
                @foreach($documentos as $doc)
                @if($doc->tdicod == $movimiento->tdicodconductor)
                <option  selected="selected" value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                @else
                <option value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                @endif
                @endforeach
              </select>

            </div>
          </div>
          <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
              <label for="conductornum">N° Documento</label>
              <input type="text"  name="conductornum" id="conductornum" value="{{$movimiento->rucconductor}}" onKeypress="if(event.keyCode == 13) buscarconductor();"    placeholder="" class="form-control">

            </div>
          </div>
          <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
              <label>Cliente</label>
              <input type="text" name="conductornom" id="conductornom"  value="{{$movimiento->nomconductor}}"  class="form-control">

            </div>
          </div>

          <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
              <label>Licencia</label>
              <input name="licencia" id="licencia" value="{{$movimiento->licencia}}" class="form-control">

            </div>
          </div>


          <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
              <label>Placa</label>
              <input name="placa" id="placa" value="{{$movimiento->placa}}" class="form-control">

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

<div class="row">
  <div class="col-lg-12">
    <div class="box">
     <div class="box-header" style="background-color:#337ab7;">
       <font color="white"><strong><center>{{$datos->tipo_negocio}}</center></strong></font>
     </div>

     <div class="box-header with-border  form-group form-group-sm">
       <div  class="col-lg-2">
        <input class="form-control" name="buscarproducto" id="buscarproducto" placeholder="Código Barras">
      </div>



     
      <div  class="col-lg-10">
        <div class="form-group form-group-sm" id="divactpro">
                        <select style=" font-weight: bold;" class="form-control input-sm" onkeypress="if(event.keyCode == 13) ingresar_cantidad_precio();" onchange="ingresar_cantidad_precio();" onclick="ingresar_cantidad_precio();"  name="producto" id="producto">
                         
                     </select>
                    </div>
                   

      </div>



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
 <tr style="background:gray;">
          <th style="color:white;width:80%;">Producto</th>
          <th style="color:white;width:10%;">Cantidad</th>
          <th style="color:white;width:10%;">Eliminar</th>
          <th hidden="hidden">Unidad</th>
          </tr>


        </thead>

        <tbody>
			@foreach($detalle as $det)
				<tr>
					<td ><input type='text' readonly="readonly" class='form-control input-sm' name='pronom[]' value='{{$det->pronom}}'></td>
					<td> <input type='number' step='any' min='0' value='{{$det->cantidad}}' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' style="text-align:right;"> </td>
					<td hidden='hidden'><select style='width:100px' name='unid[]'  class='form-control input-sm'>  </select></td>
					<td hidden='hidden'><input type='text' class='form-control' name='provun[]'  value='' readonly='readonly' ></td>
					<td hidden='hidden'><input  type='number' step='any' min='0' class='form-control input-sm' name='propun[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='"+precio+"'></td>
					<td hidden='hidden'><input readonly='readonly' type='text' class='form-control' name='itemtotal[]'  value='"+precio+"' onkeyup='CalcularItem(this);' ></td>
					<td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='{{$det->IdProducto}}' readonly='readonly' ></td>
					<td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button>
					</td>
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
        <a href="/almacen"><button type="button" class=" btn btn-block btn-danger btn-lg botones">SALIR</button></a><br>
      </div>
    </div>
  </div>
</div>
</div>


</div>




{!!Form::close()!!}
</div>

@endsection
