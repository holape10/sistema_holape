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
          var formulario = $("#formfact").serializeArray();
          $("#imgload").show();
          $(".botones").hide();
          $.ajax({
            type: "POST",
            dataType: 'json',
            url: '/mesas',
            data: formulario,
          }).done(function(respuesta){


            window.location.href = "/mostrarmesas/"+respuesta.id_ped;

         // if(respuesta.mensaje){
         //   $("#mensaje").show();
         //   $("#mensaje").html(respuesta.mensaje);
         // }

          $("#imgload").hide();
          //$(".botones").show();
          
          });

          
          
        });

      
      
    
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
             $('#moncod').val('1');
          }

          if($('#dolares').is(':checked')){
              $('#key').prop('disabled',false);
            $('#moncod').val('2');
          }


       $("#soles").on('change', function (){

         if($('#soles').is(':checked')){
              $('#key').prop('disabled',true);
               $('#moncod').val('1');
          }

      })


         $("#dolares").on('change', function (){

         if($('#dolares').is(':checked')){
              $('#key').prop('disabled',false);
               $('#moncod').val('2');
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
        url: "/consultarmenu/"+val,

      }).done(function(respuesta){
        $("#detmenu").html(respuesta.vista);
      });

    }

    $(function(){
      $('#key').keyboard();
    });

  function  buscarcliente(){

    
          var formulario = $("#clinum").val();
          $("#imgload").show();
          $(".botones").hide();
          $.ajax({
            type: "get",
            dataType: 'json',
            url: '/autocomplete/'+formulario,
            
          }).done(function(respuesta){



             $('#clinom').val(respuesta[0].nom);
                     $('#clidir').val(respuesta[0].dir);
                     $('#clicor').val(respuesta[0].cor);
                     $('#clicod').val(respuesta[0].clicod);
                     $("#tdicod").val(respuesta[0].tdicod).attr('selected', 'selected');

          $("#imgload").hide();
          //$(".botones").show();
          
          });

  

}

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
    <h2 class="box-title">Pedido {{$mesas->mes_nom}}</h2><BR>

    <div class="box-body">
    {!!Form::open(array('url'=>'/mesas','autocomplete'=>'off','method'=>'POST','name'=>'formfact','id'=>'formfact','role'=>'form','files'=>'true'))!!}
    {{Form::token()}}
       <input type="hidden" name="txtMesaId" value="{{$mesas->mes_id}}">

            <input style="display:none;" type="date" class="form-control input-sm" value="{{Carbon::now()->format('Y-m-d')}}" name="fecha" id="fecha">
        
      <div class="row">
             <BR><table class="table table-hover" id="grdet">
      <thead>

        <th>Producto</th>
        <th>Cantidad</th>
        <th hidden="hidden">Unidad</th>
        <th hidden="hidden">VU</th>
        <th hidden="hidden">PU</th>
        <th hidden='hidden' >Total</th>
        <th >Obser.</th>
      </thead>

      <tbody>
      @foreach($pedidos as $pedido)

      <tr>

      <td>
        <input type='text' class='form-control input-sm' name='pronom[]' value='{{$pedido->pronom}}' readonly='readonly'>
      </td>
      <td>
        <input type='text' value='{{$pedido->cantidad}}' name='cant[]' onChange='Calcular(this);' class='form-control input-sm keyboard' id='font-size' style='width:60px'>
      </td>
      <td hidden="hidden">
        <select style='width:100px' name='unid[]'  class='form-control input-sm'>
        @foreach($unidades as $und)
        @if($und->umecod == $pedido->unidad)
        <option  selected='selected' value='{{$und->umecod}}'>{{$und->umenom}}</option>
         @else
        <option  value='{{$und->umecod}}'>{{$und->umenom}}</option>
        @endif @endforeach
      </select>
    </td>
         <td hidden='hidden'>
             <input type='text' class='form-control input-sm' name='provun[]'  value='{{$pedido->provunitem}}' readonly='readonly' style='width:130px' >
       </td>
      <td hidden="hidden">
        <input type='text' class='form-control input-sm' name='propun[]'  value='{{$pedido->propunitem}}' readonly='readonly' style='width:70px' >
      </td>
      <td hidden="hidden">
        <input type='text' class='form-control input-sm' name='itemtotal[]'  value='{{$pedido->totalitem}}' readonly='readonly' style='width:130px' >
      </td>
      <td hidden='hidden'>
        <input type='text' class='form-control input-sm' name='proid[]'  value='{{$pedido->IdProducto}}' readonly='readonly' style='width:130px' >
      </td>
      <td style='width:200px;'>
        <input type='text' class='form-control input-sm' name='detalle[]' value='{{$pedido->detalle}}'>
      </td>
      <td>
        <button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button>
      </td>
    </tr>
  @endforeach
      </tbody>
    </table>

         <BR><table class="table table-hover" >
       <tr hidden="hidden">
         <th>Sub Total </th>
         <th>IGV </th>
         <th>Total </th>
       </tr>

       <tr>
         <th><input type="text" class="form-control"  id="subtotal" name="subtotal" value='@if(isset($totales->subtotal)){{$totales->subtotal}}@else 0.00 @endif' readonly="readonly"> </th>
         <th><input type="text" class="form-control"  id="igv" name="igv" value='@if(isset($totales->igv)){{$totales->igv}}@else 0.00 @endif' readonly="readonly"> </th>
         <th><input type="text" class="form-control"  id="total" name="total" value='@if(isset($totales->total)){{$totales->total}}@else 0.00 @endif' readonly="readonly"> </th>
       </tr>

    </table>
     <BR> <table class="table ">
      @if($mesas->mes_est !='Facturando')
      <tr>
        <td><a href="/mesas"><button type="button" class=" btn btn-block btn-primary btn-lg botones" >SALIR</button></a></td>
        <td><button type="button" id="btnRegComp" class=" btn btn-block btn-success btn-lg botones">REGISTRAR PEDIDO</button><center><img style="display:none;" width="50px" height="50px" src="/img/load.gif" name="imgload" id="imgload"></center></td>
       
     </tr>
  
     @else
       <div class="alert alert-danger">
          <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        <strong>EL PEDIDO SE ESTÁ FACTURANDO EN CAJA</strong>
      </div>
     @endif

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
    <p><strong><font color="white">{{$categoria->cat_nom}}</font></strong></p>
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
