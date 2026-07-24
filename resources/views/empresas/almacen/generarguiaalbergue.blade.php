@extends('layouts.empresas')
@section('contenido')
@include('empresas.puntosventas.modalpresentaciones')
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

     var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');



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
        return {
          _token : CSRF_TOKEN,
          search: params.term
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
                  url: '/transferiralmacenes',
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

        function agregaritem_pre(button){
         var id = button.id;
         var precio = button.value;
         var producto = $('#'+id+'nom').val();
         var proid = $('#'+id+'id').val();
    // var provun = $('#'+id+'vun').val();
    var imagen = $('#'+id+'imagen').val();

    $('#grdet').append("<tr><td width='900px'><input type='text' class='form-control' name='pronom[]' value='"+producto+"'></td><td> <input type='number' step='any' min='0' value='1' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:150px'> </td><td hidden='hidden'><select style='width:100px' name='unid[]'  class='form-control input-sm'>  </select></td><td hidden='hidden'><input type='text' class='form-control' name='provun[]'  value='' readonly='readonly' style='width:130px' ></td><td hidden='hidden'><input  type='number' step='any' min='0' class='form-control input-sm' name='propun[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='"+precio+"' style='width:80px' ></td><td hidden='hidden'><input readonly='readonly' type='text' class='form-control' name='itemtotal[]'  value='"+precio+"' onkeyup='CalcularItem(this);' style='width:80px' ></td><td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+proid+"' readonly='readonly' ></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");

    

    $("#modal-presentaciones").modal("hide");

    //  $(function(){
    //     $('.keyboard').keyboard();
    //   });
  }



  function agregaritem(){




    var producto = $('#producto').select2('data')[0].producto;


    var precio =  $('#producto').select2('data')[0].propun;
    var proid =  $('#producto').select2('data')[0].id;
    var pro_rel =  $('#producto').select2('data')[0].pro_rel;
    var contar = $('#producto').select2('data')[0].presentacion;


    if(contar>0){
      presentaciones(proid);

      $("#modal-presentaciones").modal("show");
    }else{
      $('#grdet').append("<tr><td width='900px'><input type='text' class='form-control' name='pronom[]' value='"+producto+"'></td><td> <input type='number' step='any' min='0' value='1' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:150px'> </td><td hidden='hidden'><select style='width:100px' name='unid[]'  class='form-control input-sm'>  </select></td><td hidden='hidden'><input type='text' class='form-control' name='provun[]'  value='' readonly='readonly' style='width:130px' ></td><td hidden='hidden'><input  type='number' step='any' min='0' class='form-control input-sm' name='propun[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='"+precio+"' style='width:80px' ></td><td hidden='hidden'><input readonly='readonly' type='text' class='form-control' name='itemtotal[]'  value='"+precio+"' onkeyup='CalcularItem(this);' style='width:80px' ></td><td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+proid+"' readonly='readonly' ></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");

      

      $("#modal-presentaciones").modal("hide");
    }





    //  $(function(){
    //     $('.keyboard').keyboard();
    //   });
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
         <input  type="date" id="fecEmi" name="fecEmi" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">
       </div>

     </div>
             <div class="col-lg-3" >
      <div class="form-group form-group-sm">
        <LABEL>SUCURSAL</LABEL>
        <select name="part_suc" id="part_suc" class="form-control">
          @foreach($negocios as $neg)
          <option value="{{$neg->id_empresa_negocio}}">{{$neg->IdEmpresa}} - {{$neg->tipo_negocio}}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="col-lg-3" id="partida">
      <div class="form-group form-group-sm">
        <LABEL>ALMACEN</LABEL>
        <select name="almacen" id="almacen" class="form-control">
          @foreach($almacenes as $alm)
          <option value="{{$alm->id_almacen}}"">{{$alm->descripcion}}</option>
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
 <div class="box-body" >
       <div class="row">
            <div class="col-lg-3">
                <div class="form-group form-group-sm">
                    <LABEL>SUCURSAL</LABEL>
                    <select name="des_suc" id="des_suc" class="form-control">
                      @foreach($negocios as $neg)
                      	@if($neg->id_empresa_negocio == $cabecera->id_empresa_negocio)
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
                      @foreach($almacenesdes as $alm)
                      @if($alm->id_almacen == $cabecera->id_almacen)
                      		   <option selected="selected" value="{{$alm->id_almacen}}"">{{$alm->descripcion}}</option>
                      	@else
                      		  <option value="{{$alm->id_almacen}}"">{{$alm->descripcion}}</option>
                      	@endif
                      
                      @endforeach
                    </select>
                </div>
            </div>
            
  
 <div class="col-lg-3" id="destino">
      <div class="form-group form-group-sm">
        <LABEL>BULTOS</LABEL>
        <input type="number"  step="any" name="bultos" class="form-control input-sm">
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
                @if($doc->tdicod == '1')
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
              <input type="text"  name="transportistanum" id="transportistanum" onKeypress="if(event.keyCode == 13) buscartransportista();"    placeholder="" class="form-control">

            </div>
          </div>
          <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
              <label>Cliente</label>
              <input type="text" name="transportistanom" id="transportistanom"  class="form-control">

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
                @if($doc->tdicod == '1')
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
              <input type="text"  name="conductornum" id="conductornum" onKeypress="if(event.keyCode == 13) buscarconductor();"    placeholder="" class="form-control">

            </div>
          </div>
          <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
              <label>Cliente</label>
              <input type="text" name="conductornom" id="conductornom"  class="form-control">

            </div>
          </div>

          <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
              <label>Licencia</label>
              <input name="licencia" id="licencia" class="form-control">

            </div>
          </div>


          <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
              <label>Placa</label>
              <input name="placa" id="placa" class="form-control">

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
        <div class="form-group form-group-sm">
          <select style=" font-weight: bold;" class="form-control input-sm" onkeypress="if(event.keyCode == 13) agregaritem();" onchange="agregaritem();" name="producto" id="producto">

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

          <th>Producto</th>
          <th>Cantidad</th>
          <th hidden="hidden">Unidad</th>
          

        </thead>

        <tbody>
        	@foreach($listar as $lis)
        		<tr><td width='900px'><input type='text' class='form-control' name='pronom[]' value='{{$lis->pronom}}'></td><td> <input type='number' step='any' min='0' value='{{$lis->cant_ins}}' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:150px'> </td><td hidden='hidden'><select style='width:100px' name='unid[]'  class='form-control input-sm'>  </select></td><td hidden='hidden'><input type='text' class='form-control' name='provun[]'  value='' readonly='readonly' style='width:130px' ></td><td hidden='hidden'><input  type='number' step='any' min='0' class='form-control input-sm' name='propun[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='' style='width:80px' ></td><td hidden='hidden'><input readonly='readonly' type='text' class='form-control' name='itemtotal[]'  value='' onkeyup='CalcularItem(this);' style='width:80px' ></td><td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='{{$lis->prod_ins}}' readonly='readonly' ></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>
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
