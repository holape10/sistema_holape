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

			
         $("#clinum").autocomplete({
                  source: '{!!URL::route('autocomplete')!!}',
                  dataType: "json",
                  minLength: 3,
                  autoFocus:true,
                  select: function(event,ui) {   
                     $('#clinom').val(ui.item.nom);
                     $('#clidir').val(ui.item.dir);
                     $('#clicor').val(ui.item.cor);
                     $('#clicod').val(ui.item.clicod);
                     $("#tdicod").val(ui.item.tdicod).attr('selected', 'selected');
                     $('#clinum').prop("readonly",true);
                     $('#clinom').prop("readonly",true);
               
                     $('#clicod').prop("readonly",true);
                     $('.codpro').focus();
                    

                  }
                })
		
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
		
        var comprobante = $("#comprobante").val();
        var documento = $("#documento").val();
        $("#btnPrint").printPage({

          url: "/imprimir/"+comprobante+"/"+documento,
          attr: "href",
          messageBox:false
          
        })

      $('#boleta').attr('checked', 'checked');
      $('#soles').attr('checked', 'checked');
      $('#efectivo').attr('checked', 'checked');

      if($('#efectivo').is(':checked')){
         $('#txtTipPag').val('Efectivo');
      }

      if($('#tarjeta').is(':checked')){
         $('#txtTipPag').val('Tarjeta');
      }

        $("#tarjeta").on('change', function (){

         if($('#tarjeta').is(':checked')){
              $('#txtTipPag').val('Tarjeta');
          }

      })


    $("#efectivo").on('change', function (){

         if($('#efectivo').is(':checked')){
         
               $('#txtTipPag').val('Efectivo');
          }

      })


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
            $('#key').val('0.00');

      }

      if($('#dolares').is(':checked')){
          $('#key').prop('disabled',false);
            $('#moncod').val('2');
      }

       $("#soles").on('change', function (){

         if($('#soles').is(':checked')){
              $('#key').prop('disabled',true);
               $('#moncod').val('1');
                 $('#key').val('0.00');
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
        <strong>InformaciÃ³n!</strong> {{ session('success') }}
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
    {!!Form::open(array('url'=>'/restaurant','autocomplete'=>'off','method'=>'POST','name'=>'formfact','id'=>'formfact','role'=>'form','files'=>'true'))!!}
    {{Form::token()}}
     <input type="hidden" name="txtMesaId" value="{{$mesas->mes_id}}">
     <input type="hidden" name="txtPedId" value="{{$totales->ped_id}}">
      <div class="row">
            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                    <label>Tipo Documento</label>
                    <select name="tdicod" id="tdicod" class="form-control">
                            @foreach($tipodocumento as $doc)
                                 @if($doc->tdicod =='6')
                                    <option selected="selected"  value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                                 @else
                                    <option value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                                @endif
                            @endforeach
                                      </select>
                    @if ($errors->has('tdicod'))
                            <span class="help-block"><strong><font color="red">{{ $errors->first('tdicod') }}</font></strong></span>
                    @endif
                </div>
            </div>
           <input  type="date" id="fecEmi" name="fecEmi" value="{{Carbon::now()->format('Y-m-d')}}" style="display:none;" class="form-control">
           <input  type="hidden" readonly="readonly" id="tdocod" name="tdocod" class="form-control">
           <input  type="hidden" readonly="readonly" id="moncod" name="moncod" class="form-control">
           <input type="date" name="fecVen" value="{{Carbon::now()->format('Y-m-d')}}" style="display:none;" class="form-control">

            <div class="col-lg-3 col-md-2 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                    <label for="clinum">N° Doc</label>
                    <input type="text"  name="clinum" id="clinum" value="{{old('clinum')}}"  placeholder="" class="form-control" >
                    @if ($errors->has('clinum'))
                            <span class="help-block"><strong><font color="red">{{ $errors->first('clinum') }}</font></strong></span>
                    @endif
                </div>
            </div>

            <div class="col-lg-5 col-md-6 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                    <label>Nombre ó Razón Social</label>
                    <input type="text" name="clinom" id="clinom" value="{{old('clinom')}}" class="form-control">
                    @if ($errors->has('clinom'))
                         <span class="help-block"><strong><font color="red">{{ $errors->first('clinom') }}</font></strong></span>
                    @endif
                </div>
            </div>
            <div class="col-lg-5 col-md-4 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                    <label>Dirección</label>
                    <input name="clidir" id="clidir" value="--" class="form-control">
                    @if ($errors->has('clidir'))
                            <span class="help-block"><strong><font color="red">{{ $errors->first('clidir') }}</font></strong></span>
                    @endif
                </div>
            </div>
            <div class="col-lg-5 col-md-3 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                    <label>Correo Electrónico</label>
                    <input name="clicor" id="clicor" value="{{old('clicor')}}" class="form-control">
                    @if ($errors->has('clicor'))
                            <span class="help-block"><strong><font color="red">{{ $errors->first('clicor') }}</font></strong></span>
                    @endif
                </div>
            </div>
            <div class="col-lg-3">
              <div class="btn-group btn-group-toggle" data-toggle="buttons">
                  <label class="btn btn-primary">
                    <input type="radio" name="options" id="boleta" value="2" autocomplete="off" checked> BOLETA
                  </label>
                  <label class="btn btn-success">
                    <input type="radio" name="options" id="factura" value="1" autocomplete="off"> FACTURA
                  </label>
              </div>
            </div>
            <div class="col-lg-3">
              <div class="btn-group btn-group-toggle" data-toggle="buttons">
                <label class="btn btn-primary">
                  <input type="radio" name="rdbmon" id="soles"  autocomplete="off" checked> SOLES
                </label>
                <label class="btn btn-success">
                  <input type="radio" name="rdbmon" id="dolares" autocomplete="off"> DOLARES
                </label>
              </div>
            </div>
            <div class="col-lg-4">
              <div class="btn-group btn-group-toggle" data-toggle="buttons">
                <label class="btn btn-primary">
                  <input type="radio" name="efectivo" id="efectivo"  autocomplete="off" checked> EFECTIVO
                </label>
                <label class="btn btn-success">
                  <input type="radio" name="tarjeta" id="tarjeta" autocomplete="off"> TARJETA
                </label>
              </div>
            </div>
            <div class="col-lg-2">
              <div class="form-group">
                  <input type="text" name="camdoc" class="form-control" id="key" placeholder="Tip. Cambio">
              </div>
            </div>

      </div>
      <div class="row">
             <BR><table class="table table-hover" id="grdet">
      <thead>

        <th>Producto</th>
        <th>Cantidad</th>
        <th>Unidad</th>
        <th hidden="hidden">VU</th>
        <th>PU</th>
        <th>Total</th>

      </thead>

      <tbody>
      @foreach($pedidos as $pedido)

      <tr>

      <td>
        <input type='text' class='form-control' name='pronom[]' value='{{$pedido->pronom}}' readonly='readonly'>
      </td>
      <td>
        <input type='text' value='{{$pedido->cantidad}}' name='cant[]' onChange='Calcular(this);' class='form-control input-sm keyboard' id='font-size' style='width:60px'>
      </td>
      <td>
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
             <input type='text' class='form-control' name='provun[]'  value='{{$pedido->provunitem}}' readonly='readonly' style='width:130px' >
       </td>
      <td>
        <input type='text' class='form-control' name='propun[]'  value='{{$pedido->propunitem}}' readonly='readonly' style='width:130px' >
      </td>
      <td>
        <input type='text' class='form-control' name='itemtotal[]'  value='{{$pedido->totalitem}}' readonly='readonly' style='width:130px' >
      </td>
      <td hidden='hidden'>
        <input type='text' class='form-control' name='proid[]'  value='{{$pedido->IdProducto}}' readonly='readonly' style='width:130px' >
      </td>
      <td>
        <button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button>
      </td>
    </tr>
  @endforeach
      </tbody>
    </table>

         <BR><table class="table table-hover" >
       <tr>
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
      <tr>
        <td><a href="/factmesa"><button type="button" class=" btn btn-block btn-danger btn-lg" >Cancelar</button></a></td>
        <td><button type="submit" id="btnRegComp" class=" btn btn-block btn-success btn-lg">Procesar Venta</button></td>
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
    <div class="box-header with-border">
      <h3 class="box-title">Lista de Categorías</h3>

<!-- /.box-tools -->
    </div>
<!-- /.box-header -->
    <div class="box-body" id="detmenu"  style="min-height:770px;min-width:500px  ">
      <?php $i=0; ?>
      @foreach($categorias as $categoria)
        <?php $i=$i+1; ?>
        <div class="col-sm-2 col-xs-4">
          <button id='cat<?php echo $i; ?>' value='{{$categoria->cat_id}}' onclick="mostrar(this)" style="background:#5499C7  ;width: 120px; height: 120px; border-radius:10px">
          <p>{{$categoria->cat_nom}}</p>
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
