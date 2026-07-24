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
            url: '/regpedidollevar',
            data: formulario,
          }).done(function(respuesta){


            window.location.href = "/listallevar/"+respuesta.pedido;

  
          $("#imgload").hide();
     
          });

          
          
        });

           

         $("#buscarproducto").keyup(function() {
            var val = $(this).val();
            var contarcarateres = $(this).val().length;

            if(contarcarateres >0){
                $("#detmenu").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
                $.ajax({
                  type: "GET",
                  dataType: 'json',
                  url: "/busquedaproductocomanda/"+val,

                }).done(function(respuesta){
                $("#detmenu").html(respuesta.vista);
               
                });
            }
          

        });


         $("#buscarproducto").keypress(function(e) {
       
      var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){

                  var valor = $(this).val();
                  var cont = 0, cantidad=0,total=0;
                   $.ajax({
                        type: 'get',
                        url: '/consultarprod',
                        dataType: 'json',
                        data: {'value' : $(this).val() },
                        success : function(data) {
                         
                         if(data[0].contar =='1'){


                         $("#buscarproducto").val('');

                          if ($('#grdet >tbody >tr').length > 0){

                              $("#grdet tbody tr").each(function(){
                                 var codigo = $(this).find("td:eq(3) > input").val();
                                 if( valor == codigo){
                                    cont = cont+1;
                                    cantidad = parseFloat($(this).find("td:eq(0) > input").val())+1;
                                    totalitem = parseFloat($(this).find("td:eq(5) > input").val())*cantidad;
                                    subtotalitem = totalitem/1.1055;
                                    igvitem = totalitem-subtotalitem;
                                    presigv = subtotalitem/cantidad;
                                    
                                 }
                                if(cont >0){
                                        $(this).find("td:eq(1) > input").val(cantidad);
                                       $(this).find("td:eq(3) > input").val(presigv.toFixed(2));
                                  
                                      $(this).find("td:eq(5) > input").val(totalitem.toFixed(2));
                                      calculartotal();
                                      $("#buscarproducto").focus();
                                      return false;
                                  }
                              })

                                  if(cont == 0){
                                      var igvitem = data[0].propun -data[0].provun;

                                       $('#grdet').append("<tr><td><input type='text' class='form-control' name='pronom[]' value='"+data[0].pronom+"' readonly='readonly'></td><td> <input type='text' value='1' name='cant[]' onkeyup='Calcular(this);' onChange='Calcular(this);' class='form-control input-sm' id='font-size' style='width:60px'> </td><td hidden='hidden'><select style='width:100px' name='unid[]'  class='form-control input-sm'> @foreach($unidades as $und) @if($und->umecod == 'UNI') <option  selected='selected' value='{{$und->umecod}}'>{{$und->umenom}}</option> @else <option  value='{{$und->umecod}}'>{{$und->umenom}}</option> @endif @endforeach </select></td><td hidden='hidden'><input type='text' class='form-control' name='provun[]'  value='"+data[0].provun+"' readonly='readonly' style='width:130px' ></td><td><input type='text' class='form-control' name='propun[]' onkeyup='Calcular(this);'   value='"+data[0].propun+"' readonly='readonly' style='width:130px' ></td><td hidden='hidden'><input type='text' class='form-control' name='itemtotal[]'  value='"+data[0].propun+"' readonly='readonly' style='width:130px' ></td><td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+data[0].proid+"' readonly='readonly' style='width:130px' ></td><td style='width:200px;'><input type='text' class='form-control input-sm' name='detalle[]'></td><td hidden='hidden' style='width:200px;'><input type='text' class='form-control input-sm' name='iddetalle[]' value=''></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");

                                     
                                     }

                                }else{

                                    var igvitem = data[0].propun -data[0].provun;
                                        $('#grdet').append("<tr><td><input type='text' class='form-control' name='pronom[]' value='"+data[0].pronom+"' readonly='readonly'></td><td> <input type='text' value='1' name='cant[]' onkeyup='Calcular(this);' onChange='Calcular(this);' class='form-control input-sm' id='font-size' style='width:60px'> </td><td hidden='hidden'><select style='width:100px' name='unid[]'  class='form-control input-sm'> @foreach($unidades as $und) @if($und->umecod == 'UNI') <option  selected='selected' value='{{$und->umecod}}'>{{$und->umenom}}</option> @else <option  value='{{$und->umecod}}'>{{$und->umenom}}</option> @endif @endforeach </select></td><td hidden='hidden'><input type='text' class='form-control' name='provun[]'  value='"+data[0].provun+"' readonly='readonly' style='width:130px' ></td><td><input type='text' class='form-control' name='propun[]' onkeyup='Calcular(this);'   value='"+data[0].propun+"' readonly='readonly' style='width:130px' ></td><td hidden='hidden'><input type='text' class='form-control' name='itemtotal[]'  value='"+data[0].propun+"' readonly='readonly' style='width:130px' ></td><td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+data[0].proid+"' readonly='readonly' style='width:130px' ></td><td style='width:200px;'><input type='text' class='form-control input-sm' name='detalle[]'></td>><td hidden='hidden' style='width:200px;'><input type='text' class='form-control input-sm' name='iddetalle[]' value=''></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");
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
      

          if ($('#grdet >tbody >tr').length == 0){
              $('#alertitem').show();
              event.preventDefault(); 
          }

       $('#clinum').on('dblclick', function() {
            $('#clinum').prop("readonly",false);
            $('#clinom').prop("readonly",false);
            $('#clidir').prop("readonly",false);
            $('#clicor').prop("readonly",false);
            $('#clinum').val("");
            $('#clinom').val("");
            $('#clidir').val("--");
            $('#clicor').val("");
        })
    
      $('#boleta').attr('checked', 'checked');;
      $('#soles').attr('checked', 'checked');;
       if($('#boleta').is(':checked')){
            $('#clinum').val('00000000');
            $('#clinom').val('Varios');
            $("#tdicod").val('1');
             $('#tdocod').val('2');
          }

          if($('#factura').is(':checked')){
              $('#clinum').val('');
            $('#clinom').val('');
            $("#tdicod").val('6');
            $('#tdocod').val('1');
          }

           if($('#soles').is(':checked')){
               $('#key').prop('disabled',true);
             $('#moncod').val('PEN');
          }

          if($('#dolares').is(':checked')){
              $('#key').prop('disabled',false);
            $('#moncod').val('USD');
          }


       $("#soles").on('change', function (){

         if($('#soles').is(':checked')){
              $('#key').prop('disabled',true);
               $('#moncod').val('PEN');
          }

      })


         $("#dolares").on('change', function (){

         if($('#dolares').is(':checked')){
              $('#key').prop('disabled',false);
               $('#moncod').val('USD');
          }

      })


       $("#factura").on('change', function (){

         if($('#factura').is(':checked')){
              $('#clinum').val('');
            $('#clinom').val('');
            $("#tdicod").val('6');
            $('#tdocod').val('1');
          }

      })

      $("#boleta").on('change', function (){

          if($('#boleta').is(':checked')){

            $('#clinum').val('00000000');
            $('#clinom').val('Varios');
            $("#tdicod").val('1');
            $('#tdocod').val('2');
          }
      })

 /*$('#btnRegComp').on('click',function(){
            var formData = {
               "_token": "{{ csrf_token() }}",
              'cant' :   $('#formfact').find( "input[name='cant[]']" ).val(),


            }

              $.ajax({
                type: "POST",
                dataType: 'json',
                url: "/pos",
                data: formData,
              }).done(function(respuesta){
                $("#detmenu").html(respuesta.mensaje);
              });

              });

*/
    });


    function deleteRow(btn) {
      var row = btn.parentNode.parentNode;
      row.parentNode.removeChild(row);
      calculartotal();
    };

      function mostrar(comp){
      var id = comp.id;
      var val = comp.value;
      $("#detmenu").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
      $.ajax({
        type: "GET",
        dataType: 'json',
        url: "/consultarmenucomanda/"+val,

      }).done(function(respuesta){
        $("#detmenu").html(respuesta.vista);
      });

    }

    $(function(){
      $('#key').keyboard();
    });

  function calculartotal(){

   var totigv = 0,totgrav=0 ,subtotal=0;

    $("#grdet tbody tr").each(function(){

        totgrav = totgrav + ($(this).find("td:eq(1) > input").val() *parseFloat($(this).find("td:eq(4)  > input").val()));

        subtotal = subtotal + ($(this).find("td:eq(1) > input").val() *parseFloat(($(this).find("td:eq(4) > input").val()))/(1.18));

        totigv = totgrav - subtotal;

        $('#total').val(totgrav.toFixed(2));
       $('#igv').val(totigv.toFixed(2));
       $('#subtotal').val(subtotal.toFixed(2));
    })



     if ($('#grdet >tbody >tr').length == 0){
        $('#total').val('0.00');
       $('#igv').val('0.00');
       $('#subtotal').val('0.00');
     }


};

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

  <div class="col-lg-5">
  <div class="box">
   <div class="box-header with-border">
    <h2 class="box-title">Pedido Para Llevar</h2><BR>

    <div class="box-body">
    {!!Form::open(array('url'=>'/regpedidollevar','autocomplete'=>'off','method'=>'POST','name'=>'formfact','id'=>'formfact','role'=>'form','files'=>'true'))!!}
    {{Form::token()}}
      <div class="row">
          <div class="col-lg-12 form-group form-group-sm">
            <label>MOZO</label>
            <select class="form-control" name="mozo">
              @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('caja'))
                <option></option>
                @foreach($mozos as $mozo)
                  <option value="{{$mozo->IdUsuario}}">{{$mozo->name}} {{$mozo->apeusu}}</option>
                @endforeach
              @else
                <option></option>
              @endif
            </select>
          </div>
      </div>
      <div class="row">
        <div class="col-lg-8">
            <label>Cliente</label>
            <input type="text" class="form-control input-sm" name="cliente" id="cliente">
        </div>
        <div class="col-lg-4">
             <label>Pedido para: </label>
             <select name="tipo_pedido" class="form-control">
               <option selected="selected" value="Llevar">Llevar</option>
               <option  value="Delivery">Delivery</option>
             </select>
        </div>
         <div class="col-lg-7">
            <label>Direcci&oacute;n</label>
            <input type="text" class="form-control input-sm" name="direccion" id="direccion">
        </div>
         <div class="col-lg-5">
            <label>Tel&eacute;fono</label>
            <input type="text" class="form-control input-sm" name="telefono" id="telefono">
        </div>
        <div style="display:none;" class="col-lg-4">
            <label>Fecha</label>
            <input type="date" class="form-control input-sm" value="{{Carbon::now()->format('Y-m-d')}}" name="fecha" id="fecha">
        </div>

      </div>
      <div class="row">
             <BR><table class="table table-hover" id="grdet">
      <thead>

        <th>Producto</th>
        <th>Cantidad</th>
        <th hidden="hidden">Unidad</th>
        <th hidden="hidden">VU</th>
        <th >PU</th>
        <th hidden="hidden">Total</th>
        <th >Obser.</th>
      </thead>

      <tbody>
     
      </tbody>
    </table>

         <BR><table class="table table-hover" >
       <tr>
         <th hidden="hidden">Sub Total </th>
         <th hidden="hidden">IGV </th>
         <th>Total </th>
       </tr>

       <tr>
         <th hidden="hidden"><input type="text" class="form-control"  id="subtotal" name="subtotal" value='@if(isset($totales->subtotal)){{$totales->subtotal}}@else 0.00 @endif' readonly="readonly"> </th>
         <th hidden="hidden"><input type="text" class="form-control"  id="igv" name="igv" value='@if(isset($totales->igv)){{$totales->igv}}@else 0.00 @endif' readonly="readonly"> </th>
         <th><input type="text" class="form-control"  id="total" name="total" value='@if(isset($totales->total)){{$totales->total}}@else 0.00 @endif' readonly="readonly"> </th>
       </tr>

    </table>
    <center><img style="display:none;" width="80px" height="80px" src="/img/load.gif" name="imgload" id="imgload"></center>
    <BR> <table class="table ">
     
      <tr>
        <td><a href="/mesas"><button type="button" class=" btn btn-block btn-primary btn-lg botones" >SALIR</button></a></td>
        <td><button type="button" id="btnRegComp" class=" btn btn-block btn-success btn-lg botones">REGISTRAR PEDIDO</button></td>
     </tr>
  

   </table>
      </div>
       {!!Form::close()!!}
    </div>
    <!-- /.box-tools -->
  </div>
  <!-- /.box-header -->

  <!-- box-footer -->
</div>
<!-- /.box -->
</div>
  {!!Form::close()!!}
<div class="col-lg-7">
  <div class="box">
    <div class="box-header with-border form-group-sm">
      <input class="form-control" name="buscarproducto" id="buscarproducto" placeholder="Código Barras o descripcion">

<!-- /.box-tools -->
    </div>
<!-- /.box-header -->
    <div class="box-body" id="detmenu"  style="min-height:770px;min-width:500px  ">
      <?php $i=0; ?>
      @foreach($categorias as $categoria)
        <?php $i=$i+1; ?>
        <div class="col-lg-3 col-md-3 col-sm-2 col-xs-4">
          <button id='cat<?php echo $i; ?>' value='{{$categoria->cat_id}}' onclick="mostrar(this)" style="background:#5499C7  ;width: 120px; height: 120px; border-radius:10px">
          <p><font color="white">{{$categoria->cat_nom}}</font></p>
          </button></br></br>
        </div>
      @endforeach

    </div>
<!-- /.box-body -->

<!-- box-footer -->
</div>
<!-- /.box -->
</div>
</div>
</div>

@endsection
